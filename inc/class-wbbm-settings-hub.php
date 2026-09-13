<?php

defined('ABSPATH') || exit;

/**
 * Settings hub: plugin settings and the guided quick setup in one screen.
 *
 * Both tabs delegate to the classes that already own those screens, so the
 * settings API, its sections, nonces and save handlers are untouched.
 */
final class WBBM_Settings_Hub extends WBBM_Admin_Hub
{
    const PAGE = 'wbbm-settings';

    public function page()
    {
        return self::PAGE;
    }

    public function title()
    {
        return __('Settings', 'bus-booking-manager');
    }

    protected function description()
    {
        return __('Configure booking behaviour, labels, styling and payments.', 'bus-booking-manager');
    }

    protected function icon()
    {
        return 'dashicons-admin-settings';
    }

    /**
     * Tabs swap in over AJAX (see WBBM_Admin_Hub::ajax_render_tab()), so
     * there's no per-tab page load to scope this to -- the Payments tab's
     * card styles (.wbbm-bm-card etc., shared with the per-bus payment
     * modal) need to be present whichever tab first renders.
     */
    public function enqueue_assets()
    {
        parent::enqueue_assets();

        if ($this->is_hub_screen()) {
            wp_enqueue_style('bus-edit-css', WBTM_PLUGIN_URL . 'assets/admin/bus-edit.css', array(), time());
        }
    }

    const PAYMENT_SECTION = 'wbbm_payment_setting_sec';

    /**
     * Adds Payments as a section inside General Settings (right after
     * Style Settings) instead of its own top-level hub tab, plus its three
     * instant-save AJAX endpoints.
     */
    public function boot()
    {
        parent::boot();

        add_filter('wbbm_settings_sec_reg', array($this, 'insert_payment_section'));
        add_action('wbbm_form_top_' . self::PAYMENT_SECTION, array($this, 'render_payment_section'));

        add_action('wp_ajax_wbbm_save_default_payment_mode', array($this, 'ajax_save_default_payment_mode'));
        add_action('wp_ajax_wbbm_toggle_wc_gateway', array($this, 'ajax_toggle_wc_gateway'));
        add_action('wp_ajax_wbbm_save_offline_message', array($this, 'ajax_save_offline_message'));
    }

    /**
     * Inserts the Payments section right after Style Settings in the
     * General Settings tab's own section list (MAGE_WBBM_Setting_Controls,
     * inc/wbbm_admin_settings.php), rather of using this hub's own
     * top-level tabs -- that list is exactly what the "after Style
     * Settings" position refers to.
     */
    public function insert_payment_section($sections)
    {
        $payment_section = array(
            'id'    => self::PAYMENT_SECTION,
            'title' => __('Payments', 'bus-booking-manager'),
        );

        $style_index = null;
        foreach ($sections as $index => $section) {
            if (isset($section['id']) && 'wbbm_style_setting_sec' === $section['id']) {
                $style_index = $index;
                break;
            }
        }

        if (null === $style_index) {
            $sections[] = $payment_section;
            return $sections;
        }

        array_splice($sections, $style_index + 1, 0, array($payment_section));
        return $sections;
    }

    /**
     * Instant-save for the "Choose your booking flow" cards -- mirrors
     * booking-and-rental-manager-for-woocommerce's mode switch: no Save
     * button, the choice takes effect the moment it's clicked.
     */
    public function ajax_save_default_payment_mode()
    {
        check_ajax_referer('wbbm_payment_ajax', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Insufficient permissions.', 'bus-booking-manager')));
        }

        $mode = isset($_POST['mode']) ? sanitize_key(wp_unslash($_POST['mode'])) : 'offline';
        if (!in_array($mode, array('offline', 'woocommerce'), true)) {
            $mode = 'offline';
        }

        $wc_ready = class_exists('MP_Global_Function') && MP_Global_Function::wbbm_use_wc();
        if ('woocommerce' === $mode && !$wc_ready) {
            // Can't select a flow that isn't actually available -- same
            // rule the per-bus save handler enforces.
            $mode = 'offline';
        }

        $settings = get_option('wbbm_payment_settings');
        $settings = is_array($settings) ? $settings : array();
        $settings['default_payment_method'] = $mode;
        update_option('wbbm_payment_settings', $settings);

        wp_send_json_success(array(
            'mode'         => $mode,
            'has_gateway'  => $this->mode_has_gateway($mode),
            'wc_ready'     => $wc_ready,
        ));
    }

    /** Enable/disable one WooCommerce payment gateway in place. */
    public function ajax_toggle_wc_gateway()
    {
        check_ajax_referer('wbbm_payment_ajax', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Insufficient permissions.', 'bus-booking-manager')));
        }

        if (!function_exists('WC') || !WC()->payment_gateways()) {
            wp_send_json_error(array('message' => __('WooCommerce is not active.', 'bus-booking-manager')));
        }

        $gateway_id = isset($_POST['gateway_id']) ? sanitize_key(wp_unslash($_POST['gateway_id'])) : '';
        $enabled = !empty($_POST['enabled']) && 'yes' === $_POST['enabled'] ? 'yes' : 'no';
        $gateways = WC()->payment_gateways()->payment_gateways();

        if (!$gateway_id || !isset($gateways[$gateway_id])) {
            wp_send_json_error(array('message' => __('Unknown payment gateway.', 'bus-booking-manager')));
        }

        $gateways[$gateway_id]->update_option('enabled', $enabled);

        wp_send_json_success(array(
            'enabled'     => $enabled,
            'has_gateway' => $this->mode_has_gateway('woocommerce'),
        ));
    }

    /** Instant-save for the Offline Payment Message fields. */
    public function ajax_save_offline_message()
    {
        check_ajax_referer('wbbm_payment_ajax', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Insufficient permissions.', 'bus-booking-manager')));
        }

        $settings = get_option('wbbm_payment_settings');
        $settings = is_array($settings) ? $settings : array();
        $settings['offline_label'] = isset($_POST['offline_label']) ? sanitize_text_field(wp_unslash($_POST['offline_label'])) : '';
        $settings['offline_instructions'] = isset($_POST['offline_instructions']) ? sanitize_textarea_field(wp_unslash($_POST['offline_instructions'])) : '';
        update_option('wbbm_payment_settings', $settings);

        wp_send_json_success(array(
            'has_gateway' => $this->mode_has_gateway('offline'),
        ));
    }

    /**
     * Is there actually something that can take a payment for the given
     * flow right now? WooCommerce: at least one enabled gateway. Offline:
     * always true, it doesn't depend on anything external.
     */
    private function mode_has_gateway($mode)
    {
        if ('offline' === $mode) {
            $settings = get_option('wbbm_payment_settings');
            $label = is_array($settings) && isset($settings['offline_label']) ? trim($settings['offline_label']) : '';
            return '' !== $label;
        }

        if (!function_exists('WC') || !WC()->payment_gateways()) {
            return false;
        }

        // Deliberately not get_available_payment_gateways() -- that also
        // weighs currency/cart/SSL conditions meant for an actual frontend
        // checkout, which don't apply to "is a gateway enabled" from
        // wp-admin. Also deliberately not $gateway->enabled: a gateway just
        // toggled via update_option() in this same request doesn't refresh
        // that in-memory property, so it can read stale -- the
        // woocommerce_{id}_settings option itself is always current.
        foreach (WC()->payment_gateways()->payment_gateways() as $gateway) {
            $stored = get_option('woocommerce_' . $gateway->id . '_settings');
            $enabled = is_array($stored) && isset($stored['enabled']) ? $stored['enabled'] : $gateway->enabled;
            if ('yes' === $enabled) {
                return true;
            }
        }

        return false;
    }

    protected function register_tabs()
    {
        $tabs = array(
            'general' => array(
                'label'       => __('General Settings', 'bus-booking-manager'),
                'description' => __('Every plugin option, grouped by section -- including Payments, right after Style Settings.', 'bus-booking-manager'),
                'icon'        => 'dashicons-admin-generic',
                'capability'  => 'manage_options',
                'callback'    => array($this, 'render_settings'),
                'legacy'      => 'wbbm_quick_setup',
            ),
        );

        // Quick Setup only exists once WooCommerce is present.
        if (class_exists('WBTM_Quick_Setup') && WBTM_Quick_Setup::instance()) {
            $tabs['quick-setup'] = array(
                'label'       => __('Quick Setup', 'bus-booking-manager'),
                'description' => __('Guided first-run setup for pages, currency and a sample service.', 'bus-booking-manager'),
                'icon'        => 'dashicons-controls-play',
                'capability'  => 'manage_options',
                'callback'    => array($this, 'render_quick_setup'),
                'legacy'      => array('wbbm_init_quick_setup', 'wbtm_quick_setup'),
            );
        }

        if (!is_plugin_active('bus-booking-manager-pro/wbtm-pro.php')) {
            $tabs['go-pro'] = array(
                'label'      => __('Go PRO', 'bus-booking-manager'),
                'icon'       => 'dashicons-star-filled',
                'capability' => 'manage_options',
                'callback'   => array($this, 'render_go_pro'),
                'legacy'     => 'wbbm_go_pro_page',
            );
        }

        return $tabs;
    }

    public function render_settings()
    {
        $settings = class_exists('MAGE_WBBM_Setting_Controls') ? MAGE_WBBM_Setting_Controls::instance() : null;

        if (!$settings || !method_exists($settings, 'plugin_page')) {
            self::empty_state(
                __('Settings are unavailable', 'bus-booking-manager'),
                __('The settings controller could not be loaded. Try deactivating and reactivating the plugin.', 'bus-booking-manager'),
                'dashicons-warning'
            );
            return;
        }

        $settings->plugin_page();
    }

    public function render_quick_setup()
    {
        $quick = WBTM_Quick_Setup::instance();

        if (!$quick || !method_exists($quick, 'quick_setup')) {
            self::empty_state(
                __('Quick Setup is unavailable', 'bus-booking-manager'),
                __('WooCommerce must be active before the guided setup can run.', 'bus-booking-manager'),
                'dashicons-warning'
            );
            return;
        }

        $quick->quick_setup();
    }

    /**
     * Payments section, rendered inside the General Settings tab's own
     * section list (right after Style Settings -- see
     * insert_payment_section()), not as a separate hub tab. Hooked to
     * wbbm_form_top_{section_id}, which fires inside that section's own
     * <form action="options.php">, so everything here has to be either
     * read-only or save over AJAX -- a second, nested <form> isn't valid
     * HTML, and the mode/gateway switches already needed instant-save AJAX
     * regardless.
     */
    public function render_payment_section()
    {
        $settings = get_option('wbbm_payment_settings');
        $settings = is_array($settings) ? $settings : array();
        $default_method = isset($settings['default_payment_method']) && 'woocommerce' === $settings['default_payment_method'] ? 'woocommerce' : 'offline';
        $offline_label = isset($settings['offline_label']) ? $settings['offline_label'] : __('Pay Offline', 'bus-booking-manager');
        $offline_instructions = isset($settings['offline_instructions']) ? $settings['offline_instructions'] : '';

        $wc_ready = class_exists('MP_Global_Function') && MP_Global_Function::wbbm_use_wc();
        $has_gateway = $this->mode_has_gateway($default_method);
        ?>
        <div class="wbbm-pay-intro">
            <h4><span class="dashicons dashicons-info-outline"></span> <?php esc_html_e('How payments work here', 'bus-booking-manager'); ?></h4>
            <ol>
                <li><span class="wbbm-pay-intro-num">1</span> <?php esc_html_e('Pick the booking flow every new bus should use by default.', 'bus-booking-manager'); ?></li>
                <li><span class="wbbm-pay-intro-num">2</span> <?php esc_html_e('Enable and configure the payment method(s) for that flow -- only its settings are shown.', 'bus-booking-manager'); ?></li>
                <li><span class="wbbm-pay-intro-num">3</span> <?php esc_html_e("That's it -- customers can now pay. You can switch flows anytime; every change here saves instantly.", 'bus-booking-manager'); ?></li>
            </ol>
        </div>

        <h3 style="margin-top:0;"><?php esc_html_e('Choose your default booking flow', 'bus-booking-manager'); ?></h3>
        <p class="description"><?php esc_html_e('Used for every new bus you create -- each bus can still be switched individually from its own edit screen.', 'bus-booking-manager'); ?></p>

        <div class="wbbm-bm-cards" id="wbbm-pay-mode-cards">
            <label class="wbbm-bm-card <?php echo 'offline' === $default_method ? 'is-selected' : ''; ?>" data-mode="offline">
                <input type="radio" name="wbbm_default_payment_method_display" value="offline" <?php checked($default_method, 'offline'); ?>>
                <?php if ('offline' === $default_method) : ?><span class="wbbm-bm-card-active"><?php esc_html_e('ACTIVE', 'bus-booking-manager'); ?></span><?php endif; ?>
                <span class="wbbm-bm-card-icon"><span class="dashicons dashicons-money-alt"></span></span>
                <strong><?php esc_html_e('Offline Payment', 'bus-booking-manager'); ?></strong>
                <small><?php esc_html_e('Manual / bank transfer, no WooCommerce needed', 'bus-booking-manager'); ?></small>
            </label>
            <label class="wbbm-bm-card <?php echo ('woocommerce' === $default_method ? 'is-selected' : '') . ($wc_ready ? '' : ' is-disabled'); ?>" data-mode="woocommerce">
                <input type="radio" name="wbbm_default_payment_method_display" value="woocommerce" <?php checked($default_method, 'woocommerce'); ?> <?php disabled(!$wc_ready); ?>>
                <?php if ('woocommerce' === $default_method) : ?><span class="wbbm-bm-card-active"><?php esc_html_e('ACTIVE', 'bus-booking-manager'); ?></span><?php endif; ?>
                <span class="wbbm-bm-card-icon"><span class="dashicons dashicons-cart"></span></span>
                <strong><?php esc_html_e('WooCommerce Checkout', 'bus-booking-manager'); ?></strong>
                <small><?php esc_html_e('Bookings go through the WooCommerce cart, checkout and orders.', 'bus-booking-manager'); ?></small>
                <?php if (!$wc_ready) : ?>
                    <button type="button" class="wbbm-bm-card-cta" data-wbbm-wc-install>
                        <span class="dashicons dashicons-download"></span>
                        <?php esc_html_e('Requires WooCommerce — Install & Activate', 'bus-booking-manager'); ?>
                    </button>
                <?php endif; ?>
            </label>
        </div>

        <div class="wbbm-pay-warning" id="wbbm-pay-warning" <?php echo $has_gateway ? 'style="display:none"' : ''; ?>>
            <span class="dashicons dashicons-warning"></span>
            <span id="wbbm-pay-warning-text">
                <?php if ('woocommerce' === $default_method) : ?>
                    <?php esc_html_e("WooCommerce mode is selected, but no WooCommerce payment gateway is enabled yet. Customers won't be able to complete a booking until you enable one below.", 'bus-booking-manager'); ?>
                <?php else : ?>
                    <?php esc_html_e('Offline Payment is selected but has no message configured yet -- add one below so customers know what to do.', 'bus-booking-manager'); ?>
                <?php endif; ?>
            </span>
        </div>

        <div class="wbbm-pay-configuring" id="wbbm-pay-configuring">
            <span class="dashicons <?php echo 'woocommerce' === $default_method ? 'dashicons-cart' : 'dashicons-money-alt'; ?>"></span>
            <?php esc_html_e("You're configuring:", 'bus-booking-manager'); ?>
            <strong id="wbbm-pay-configuring-label"><?php echo 'woocommerce' === $default_method ? esc_html__('WooCommerce Checkout', 'bus-booking-manager') : esc_html__('Offline Payment', 'bus-booking-manager'); ?></strong>
        </div>

        <div data-mode-section="woocommerce" <?php echo 'woocommerce' === $default_method ? '' : 'style="display:none"'; ?>>
            <?php self::render_wc_gateway_section(); ?>
        </div>

        <div data-mode-section="offline" <?php echo 'offline' === $default_method ? '' : 'style="display:none"'; ?>>
            <h3><?php esc_html_e('Offline Payment Message', 'bus-booking-manager'); ?></h3>
            <p class="description"><?php esc_html_e('Shown to customers who book a bus using Offline Payment. Saves instantly.', 'bus-booking-manager'); ?></p>

            <div class="form-group" style="margin-bottom:16px;">
                <label for="wbbm_offline_label" style="display:block;font-weight:600;margin-bottom:6px;"><?php esc_html_e('Heading', 'bus-booking-manager'); ?></label>
                <input type="text" id="wbbm_offline_label" class="form-control" style="width:100%;max-width:420px;" value="<?php echo esc_attr($offline_label); ?>" placeholder="<?php esc_attr_e('e.g. Pay Offline / Bank Transfer', 'bus-booking-manager'); ?>">
            </div>

            <div class="form-group" style="margin-bottom:12px;">
                <label for="wbbm_offline_instructions" style="display:block;font-weight:600;margin-bottom:6px;"><?php esc_html_e('Instructions', 'bus-booking-manager'); ?></label>
                <textarea id="wbbm_offline_instructions" class="form-control" rows="4" style="width:100%;max-width:420px;" placeholder="<?php esc_attr_e('e.g. Please transfer the fare to account #1234 and bring your receipt when boarding.', 'bus-booking-manager'); ?>"><?php echo esc_textarea($offline_instructions); ?></textarea>
            </div>

            <button type="button" class="btn btn-primary" id="wbbm-save-offline-message"><?php esc_html_e('Save Changes', 'bus-booking-manager'); ?></button>
            <span class="wbbm-pay-saved-msg" id="wbbm-offline-saved-msg"></span>
        </div>

        <style>
            #<?php echo esc_attr(self::PAYMENT_SECTION); ?> > form > p.submit { display: none; }
            /* Core's do_settings_sections() auto-prints the section title as an
               <h2> right after this hook's output -- redundant here since the
               "Payments" label is already the nav-tab itself. The id core
               generates has a wp_unique_id() counter suffix, so match on the
               fixed prefix instead of a literal id. */
            [id^="wp-settings-section-<?php echo esc_attr(self::PAYMENT_SECTION); ?>-"] { display: none; }
        </style>

        <?php /*
         * The install-progress modal has to be printed *before* the script
         * below that looks it up by getElementById() -- that script runs
         * synchronously as soon as the browser parses it, so if the modal
         * markup came after (as it used to), document.getElementById()
         * returned null, the "if (wcInstallModal && ...)" guard silently
         * failed, and the click handler was never attached at all: clicking
         * "Requires WooCommerce" did nothing, no modal, no progress.
         */ ?>
        <div class="wbbm-wc-install-modal" id="wbbm-wc-install-modal">
            <div class="wbbm-wc-install-box">
                <div class="wbbm-wc-install-icon"><span class="dashicons dashicons-download"></span></div>
                <h3><?php esc_html_e('Installing WooCommerce', 'bus-booking-manager'); ?></h3>
                <p><?php esc_html_e("This can take a moment on a slow connection -- don't close this window.", 'bus-booking-manager'); ?></p>
                <div class="wbbm-wc-install-progress-track"><div class="wbbm-wc-install-progress-bar" id="wbbm-wc-install-bar"></div></div>
                <div class="wbbm-wc-install-meta">
                    <span class="wbbm-wc-install-status" id="wbbm-wc-install-status"><?php esc_html_e('Contacting WordPress.org…', 'bus-booking-manager'); ?></span>
                    <span class="wbbm-wc-install-pct" id="wbbm-wc-install-pct">0%</span>
                </div>
            </div>
        </div>

        <script>
        (function () {
            var ajaxUrl = <?php echo wp_json_encode(admin_url('admin-ajax.php')); ?>;
            var nonce = <?php echo wp_json_encode(wp_create_nonce('wbbm_payment_ajax')); ?>;
            var cardsWrap = document.getElementById('wbbm-pay-mode-cards');
            var warning = document.getElementById('wbbm-pay-warning');
            var warningText = document.getElementById('wbbm-pay-warning-text');
            var configuringLabel = document.getElementById('wbbm-pay-configuring-label');
            var configuringIcon = document.querySelector('#wbbm-pay-configuring .dashicons');
            var copy = {
                offline: {
                    label: <?php echo wp_json_encode(__('Offline Payment', 'bus-booking-manager')); ?>,
                    icon: 'dashicons-money-alt',
                    warn: <?php echo wp_json_encode(__('Offline Payment is selected but has no message configured yet -- add one below so customers know what to do.', 'bus-booking-manager')); ?>
                },
                woocommerce: {
                    label: <?php echo wp_json_encode(__('WooCommerce Checkout', 'bus-booking-manager')); ?>,
                    icon: 'dashicons-cart',
                    warn: <?php echo wp_json_encode(__("WooCommerce mode is selected, but no WooCommerce payment gateway is enabled yet. Customers won't be able to complete a booking until you enable one below.", 'bus-booking-manager')); ?>
                }
            };

            if (cardsWrap) {
                cardsWrap.querySelectorAll('.wbbm-bm-card').forEach(function (card) {
                    card.addEventListener('click', function (e) {
                        var input = card.querySelector('input[type="radio"]');
                        if (!input || input.disabled || card.classList.contains('is-selected')) { return; }
                        e.preventDefault();

                        var mode = card.getAttribute('data-mode');
                        var xhr = new XMLHttpRequest();
                        xhr.open('POST', ajaxUrl, true);
                        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                        xhr.onload = function () {
                            var res;
                            try { res = JSON.parse(xhr.responseText); } catch (err) { return; }
                            if (!res || !res.success) { return; }

                            input.checked = true;
                            cardsWrap.querySelectorAll('.wbbm-bm-card').forEach(function (c) {
                                var active = c.querySelector('.wbbm-bm-card-active');
                                var isNowSelected = c.getAttribute('data-mode') === res.data.mode;
                                c.classList.toggle('is-selected', isNowSelected);
                                if (isNowSelected && !active) {
                                    active = document.createElement('span');
                                    active.className = 'wbbm-bm-card-active';
                                    active.textContent = <?php echo wp_json_encode(__('ACTIVE', 'bus-booking-manager')); ?>;
                                    c.insertBefore(active, c.querySelector('.wbbm-bm-card-icon'));
                                } else if (!isNowSelected && active) {
                                    active.remove();
                                }
                            });

                            document.querySelectorAll('[data-mode-section]').forEach(function (section) {
                                section.style.display = section.getAttribute('data-mode-section') === res.data.mode ? '' : 'none';
                            });

                            if (configuringLabel && copy[res.data.mode]) {
                                configuringLabel.textContent = copy[res.data.mode].label;
                                if (configuringIcon) { configuringIcon.className = 'dashicons ' + copy[res.data.mode].icon; }
                            }

                            if (warning) {
                                warning.style.display = res.data.has_gateway ? 'none' : '';
                                if (warningText && copy[res.data.mode]) { warningText.textContent = copy[res.data.mode].warn; }
                            }
                        };
                        xhr.send('action=wbbm_save_default_payment_mode&nonce=' + encodeURIComponent(nonce) + '&mode=' + encodeURIComponent(mode));
                    });
                });
            }

            // Gateway enable/disable toggles, the accordion collapse, and the
            // per-gateway "Configure" iframe modal all live inside
            // render_wc_gateway_section() itself now (it's called just below
            // and is fully self-contained) -- that same method is also
            // called from the per-bus Payment Method popup, so this used to
            // be duplicated per caller; it isn't wired up again here.

            var saveOfflineBtn = document.getElementById('wbbm-save-offline-message');
            var savedMsg = document.getElementById('wbbm-offline-saved-msg');
            if (saveOfflineBtn) {
                saveOfflineBtn.addEventListener('click', function () {
                    var label = document.getElementById('wbbm_offline_label').value;
                    var instructions = document.getElementById('wbbm_offline_instructions').value;

                    var xhr = new XMLHttpRequest();
                    xhr.open('POST', ajaxUrl, true);
                    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                    xhr.onload = function () {
                        var res;
                        try { res = JSON.parse(xhr.responseText); } catch (err) { return; }
                        if (savedMsg) {
                            savedMsg.textContent = (res && res.success)
                                ? <?php echo wp_json_encode(__('Saved.', 'bus-booking-manager')); ?>
                                : <?php echo wp_json_encode(__('Could not save -- try again.', 'bus-booking-manager')); ?>;
                            savedMsg.className = 'wbbm-pay-saved-msg' + (res && res.success ? ' is-ok' : ' is-error');
                            setTimeout(function () { savedMsg.textContent = ''; }, 2500);
                        }
                        if (res && res.success && warning) {
                            warning.style.display = res.data.has_gateway ? 'none' : '';
                        }
                    };
                    xhr.send('action=wbbm_save_offline_message&nonce=' + encodeURIComponent(nonce) +
                        '&offline_label=' + encodeURIComponent(label) +
                        '&offline_instructions=' + encodeURIComponent(instructions));
                });
            }

            var wcInstallModal = document.getElementById('wbbm-wc-install-modal');
            var wcInstallBar = document.getElementById('wbbm-wc-install-bar');
            var wcInstallStatus = document.getElementById('wbbm-wc-install-status');
            var wcInstallPct = document.getElementById('wbbm-wc-install-pct');
            if (wcInstallModal && wcInstallBar && wcInstallStatus) {
                document.querySelectorAll('[data-wbbm-wc-install]').forEach(function (btn) {
                    btn.addEventListener('click', function (e) {
                        e.preventDefault();
                        if (btn.disabled) { return; }
                        btn.disabled = true;

                        wcInstallModal.classList.add('is-open');
                        wcInstallBar.classList.remove('is-done');
                        wcInstallStatus.className = 'wbbm-wc-install-status';

                        // There's no chunked/byte-level progress available from
                        // one plain admin-ajax call, so this ticks a simulated
                        // percentage through named stages while the real
                        // request is in flight, holding just short of 100%
                        // until the actual response lands -- it never fakes
                        // "done" before the server says so.
                        var stages = [
                            { pct: 25, text: <?php echo wp_json_encode(__('Downloading WooCommerce…', 'bus-booking-manager')); ?> },
                            { pct: 55, text: <?php echo wp_json_encode(__('Installing WooCommerce…', 'bus-booking-manager')); ?> },
                            { pct: 80, text: <?php echo wp_json_encode(__('Activating WooCommerce…', 'bus-booking-manager')); ?> }
                        ];
                        var current = 4;
                        var stageIdx = 0;
                        wcInstallStatus.textContent = <?php echo wp_json_encode(__('Contacting WordPress.org…', 'bus-booking-manager')); ?>;
                        wcInstallBar.style.width = current + '%';
                        if (wcInstallPct) { wcInstallPct.textContent = current + '%'; }

                        var tick = setInterval(function () {
                            var ceiling = stageIdx < stages.length ? stages[stageIdx].pct : 92;
                            if (current < ceiling) {
                                current += 1;
                                wcInstallBar.style.width = current + '%';
                                if (wcInstallPct) { wcInstallPct.textContent = current + '%'; }
                            } else if (stageIdx < stages.length) {
                                wcInstallStatus.textContent = stages[stageIdx].text;
                                stageIdx++;
                            }
                        }, 80);

                        var installXhr = new XMLHttpRequest();
                        // No installXhr.timeout is set on purpose -- this can run long.
                        installXhr.open('POST', ajaxUrl, true);
                        installXhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                        installXhr.onload = function () {
                            clearInterval(tick);
                            var res = null;
                            try { res = JSON.parse(installXhr.responseText); } catch (err) { /* leave res null */ }
                            btn.disabled = false;
                            if (res && res.success) {
                                wcInstallBar.classList.add('is-done');
                                if (wcInstallPct) { wcInstallPct.textContent = '100%'; }
                                wcInstallStatus.className = 'wbbm-wc-install-status is-success';
                                wcInstallStatus.textContent = (res.data && res.data.message) || <?php echo wp_json_encode(__('Done! Reloading…', 'bus-booking-manager')); ?>;
                                setTimeout(function () { window.location.reload(); }, 900);
                            } else {
                                wcInstallStatus.className = 'wbbm-wc-install-status is-error';
                                wcInstallStatus.textContent = (res && res.data && res.data.message) || <?php echo wp_json_encode(__('Something went wrong. Please try again.', 'bus-booking-manager')); ?>;
                            }
                        };
                        installXhr.onerror = function () {
                            clearInterval(tick);
                            btn.disabled = false;
                            wcInstallStatus.className = 'wbbm-wc-install-status is-error';
                            wcInstallStatus.textContent = <?php echo wp_json_encode(__('Connection error. Please try again.', 'bus-booking-manager')); ?>;
                        };
                        installXhr.send('action=wbbm_install_activate_woocommerce&nonce=' + encodeURIComponent(<?php echo wp_json_encode(wp_create_nonce('wbbm_wc_install')); ?>));
                    });
                });
            }
        })();
        </script>
        <?php
    }

    /**
     * WooCommerce Payment Methods list: every registered WC_Payment_Gateway,
     * each with an enable toggle (saved instantly) and a Configure link out
     * to WooCommerce's own settings screen for that gateway -- this plugin
     * doesn't re-implement WooCommerce's per-gateway settings forms, it
     * just gives a quick enable/disable + a shortcut to the real ones.
     */
    /**
     * WooCommerce gateway list + per-gateway "Configure" iframe modal.
     * Static and fully self-contained (markup, AJAX toggle wiring, accordion
     * collapse, and the Configure modal + its JS all in one call) because
     * it's used from two different screens -- this Payments settings
     * section and the per-bus Payment Method popup
     * (BusEditPageClass::render_payment_modal()) -- and needs to render and
     * work identically, standalone, on either one.
     */
    public static function render_wc_gateway_section()
    {
        $wc_ready = class_exists('MP_Global_Function') && MP_Global_Function::wbbm_use_wc();

        if (!$wc_ready) {
            ?>
            <p class="wbbm-payment-modal-empty"><?php esc_html_e('WooCommerce is not active, so there are no WooCommerce payment methods to configure yet.', 'bus-booking-manager'); ?></p>
            <?php
            return;
        }

        $gateways = WC()->payment_gateways() ? WC()->payment_gateways()->payment_gateways() : array();
        ?>
        <div class="wbbm-gw-accordion">
            <div class="wbbm-gw-accordion-header" id="wbbm-gw-accordion-header">
                <?php esc_html_e('WooCommerce Payment Methods', 'bus-booking-manager'); ?>
                <span class="dashicons dashicons-arrow-up-alt2"></span>
            </div>
            <div class="wbbm-gw-accordion-body" id="wbbm-gw-accordion-body">
                <p>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=wc-settings&tab=checkout')); ?>" target="_blank" rel="noopener noreferrer" class="btn btn-outline">
                        <?php esc_html_e('Open in WooCommerce', 'bus-booking-manager'); ?> <span class="dashicons dashicons-external"></span>
                    </a>
                </p>

                <?php if (empty($gateways)) : ?>
                    <p class="wbbm-payment-modal-empty"><?php esc_html_e('WooCommerce has no payment gateways registered yet.', 'bus-booking-manager'); ?></p>
                <?php endif; ?>

                <?php foreach ($gateways as $gateway) : ?>
                    <?php $enabled = 'yes' === $gateway->enabled; ?>
                    <div class="wbbm-gw-row">
                        <div class="wbbm-gw-row-top">
                            <label class="bus-switch">
                                <input type="checkbox" class="wbbm-gw-toggle" data-gateway-id="<?php echo esc_attr($gateway->id); ?>" <?php checked($enabled); ?>>
                                <span class="slider round"></span>
                            </label>
                            <strong><?php echo esc_html($gateway->get_method_title()); ?></strong>
                            <span class="wbbm-gw-status <?php echo $enabled ? 'is-on' : ''; ?>"><?php echo $enabled ? esc_html__('ENABLED', 'bus-booking-manager') : esc_html__('DISABLED', 'bus-booking-manager'); ?></span>
                            <button
                                type="button"
                                class="btn btn-outline wbbm-gw-configure"
                                data-wbbm-gw-configure
                                data-url="<?php echo esc_url(admin_url('admin.php?page=wc-settings&tab=checkout&section=' . $gateway->id)); ?>"
                                data-title="<?php echo esc_attr($gateway->get_method_title()); ?>"
                            ><?php esc_html_e('Configure', 'bus-booking-manager'); ?></button>
                        </div>
                        <p class="wbbm-gw-desc"><?php echo esc_html(wp_strip_all_tags($gateway->get_method_description())); ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <script>
        (function () {
            var ajaxUrl = <?php echo wp_json_encode(admin_url('admin-ajax.php')); ?>;
            var nonce = <?php echo wp_json_encode(wp_create_nonce('wbbm_payment_ajax')); ?>;
            // Only present on the Payments settings section, not the per-bus
            // popup -- every use below is null-guarded so this is simply a
            // no-op there.
            var warning = document.getElementById('wbbm-pay-warning');

            document.querySelectorAll('.wbbm-gw-toggle').forEach(function (toggle) {
                toggle.addEventListener('change', function () {
                    var gatewayId = toggle.getAttribute('data-gateway-id');
                    var enabled = toggle.checked ? 'yes' : 'no';
                    var row = toggle.closest('.wbbm-gw-row');
                    var pill = row ? row.querySelector('.wbbm-gw-status') : null;

                    var xhr = new XMLHttpRequest();
                    xhr.open('POST', ajaxUrl, true);
                    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                    xhr.onload = function () {
                        var res;
                        try { res = JSON.parse(xhr.responseText); } catch (err) { return; }
                        if (!res || !res.success) { return; }

                        if (pill) {
                            pill.textContent = res.data.enabled === 'yes' ? <?php echo wp_json_encode(__('ENABLED', 'bus-booking-manager')); ?> : <?php echo wp_json_encode(__('DISABLED', 'bus-booking-manager')); ?>;
                            pill.classList.toggle('is-on', res.data.enabled === 'yes');
                        }
                        if (warning) {
                            warning.style.display = res.data.has_gateway ? 'none' : '';
                        }
                    };
                    xhr.send('action=wbbm_toggle_wc_gateway&nonce=' + encodeURIComponent(nonce) + '&gateway_id=' + encodeURIComponent(gatewayId) + '&enabled=' + encodeURIComponent(enabled));
                });
            });

            var accHeader = document.getElementById('wbbm-gw-accordion-header');
            var accBody = document.getElementById('wbbm-gw-accordion-body');
            if (accHeader && accBody) {
                accHeader.addEventListener('click', function () {
                    var isOpen = accBody.style.display !== 'none';
                    accBody.style.display = isOpen ? 'none' : '';
                    accHeader.classList.toggle('is-collapsed', isOpen);
                });
            }
        })();
        </script>

        <?php /*
         * "Configure" used to be a plain link that navigated away (or opened
         * a new tab) to WooCommerce's own settings screen for that gateway.
         * Requested instead: stay on this page and show that same screen in
         * a modal. It's a same-origin admin URL, so an iframe can load the
         * real WooCommerce settings form as-is (its own Save button and all)
         * -- no separate embed API needed -- and once it loads we can reach
         * into it (still same-origin) to hide the outer wp-admin chrome
         * (admin bar/menu/footer) so it reads like a focused settings panel
         * rather than a page-within-a-page.
         */ ?>
        <div class="wbbm-gw-modal" id="wbbm-gw-modal">
            <div class="wbbm-gw-modal-box">
                <div class="wbbm-gw-modal-head">
                    <strong id="wbbm-gw-modal-title"><?php esc_html_e('Configure payment method', 'bus-booking-manager'); ?></strong>
                    <div class="wbbm-gw-modal-actions">
                        <a href="#" target="_blank" rel="noopener noreferrer" id="wbbm-gw-modal-open-tab" class="wbbm-gw-modal-open-tab" title="<?php esc_attr_e('Open in a new tab', 'bus-booking-manager'); ?>"><span class="dashicons dashicons-external"></span></a>
                        <button type="button" class="wbbm-gw-modal-close" id="wbbm-gw-modal-close" aria-label="<?php esc_attr_e('Close', 'bus-booking-manager'); ?>"><span class="dashicons dashicons-no-alt"></span></button>
                    </div>
                </div>
                <div class="wbbm-gw-modal-body">
                    <div class="wbbm-gw-modal-loading" id="wbbm-gw-modal-loading"><span class="spinner is-active"></span> <?php esc_html_e('Loading…', 'bus-booking-manager'); ?></div>
                    <iframe id="wbbm-gw-modal-iframe" title="<?php esc_attr_e('WooCommerce payment method settings', 'bus-booking-manager'); ?>"></iframe>
                </div>
            </div>
        </div>
        <script>
        (function () {
            var gwModal = document.getElementById('wbbm-gw-modal');
            var gwIframe = document.getElementById('wbbm-gw-modal-iframe');
            var gwTitle = document.getElementById('wbbm-gw-modal-title');
            var gwLoading = document.getElementById('wbbm-gw-modal-loading');
            var gwOpenTab = document.getElementById('wbbm-gw-modal-open-tab');
            var gwClose = document.getElementById('wbbm-gw-modal-close');
            if (!gwModal || !gwIframe) { return; }

            // Strips the outer wp-admin chrome inside the iframe once its
            // document is reachable (same origin) so only the settings form
            // itself is visible -- purely cosmetic, and silently skipped if
            // it ever can't reach the iframe's document for any reason.
            function trimChrome() {
                try {
                    var doc = gwIframe.contentDocument;
                    if (!doc || !doc.head) { return; }
                    if (doc.getElementById('wbbm-gw-modal-trim-style')) { return; }
                    var style = doc.createElement('style');
                    style.id = 'wbbm-gw-modal-trim-style';
                    style.textContent = [
                        'html.wp-toolbar{padding-top:0!important;}',
                        '#wpadminbar,#adminmenumain,#adminmenuback,#adminmenuwrap,#wpfooter,.wrap>h1.wp-heading-inline,.wrap>hr.wp-header-end{display:none!important;}',
                        /* Newer WooCommerce settings screens render their own
                           (non wp-heading-inline) React header bar instead --
                           ".woocommerce-layout__header" with the "Settings"
                           <h1> inside it -- which the selector above doesn't
                           touch. Hide the whole bar, not just the heading, so
                           its background/padding don't linger as empty gray
                           strip. */
                        '.woocommerce-layout__header{display:none!important;}',
                        '#wpcontent,#wpbody-content{margin-left:0!important;padding-top:12px!important;}',
                        '#wpbody{padding-top:0!important;}'
                    ].join('');
                    doc.head.appendChild(style);
                } catch (err) { /* cross-origin or not ready yet -- leave the page as-is */ }
                // Reveal the iframe and drop the cover in the same tick, so
                // the very first frame the admin sees already has the
                // sidebar/admin-bar stripped -- never the raw page first.
                gwIframe.classList.add('is-ready');
                gwLoading.style.display = 'none';
            }

            function openGwModal(url, title) {
                gwTitle.textContent = title || <?php echo wp_json_encode(__('Configure payment method', 'bus-booking-manager')); ?>;
                gwOpenTab.href = url;
                gwLoading.style.display = '';
                gwIframe.classList.remove('is-ready');
                gwIframe.src = url;
                gwModal.classList.add('is-open');
                document.body.classList.add('wbbm-modal-open');
            }
            function closeGwModal() {
                gwModal.classList.remove('is-open');
                document.body.classList.remove('wbbm-modal-open');
                gwIframe.src = 'about:blank';
            }

            document.querySelectorAll('[data-wbbm-gw-configure]').forEach(function (btn) {
                btn.addEventListener('click', function (e) {
                    e.preventDefault();
                    openGwModal(btn.getAttribute('data-url'), btn.getAttribute('data-title'));
                });
            });
            gwIframe.addEventListener('load', trimChrome);
            gwClose.addEventListener('click', closeGwModal);
            gwModal.addEventListener('click', function (e) {
                if (e.target === gwModal) { closeGwModal(); }
            });
            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape' && gwModal.classList.contains('is-open')) { closeGwModal(); }
            });
        })();
        </script>
        <?php
    }

    public function render_go_pro()
    {
        $settings = class_exists('MAGE_WBBM_Setting_Controls') ? MAGE_WBBM_Setting_Controls::instance() : null;

        if ($settings && method_exists($settings, 'wbbm_go_pro_page')) {
            $settings->wbbm_go_pro_page();
            return;
        }

        self::empty_state(__('Upgrade information is unavailable', 'bus-booking-manager'), '', 'dashicons-star-filled');
    }
}

// Boot the settings hub and the plugin-wide admin theme layer.
add_action('plugins_loaded', function () {
    if (class_exists('WBBM_Settings_Hub')) {
        ( new WBBM_Settings_Hub() )->boot();
    }
    if (class_exists('WBBM_Admin_Hub')) {
        WBBM_Admin_Hub::boot_theme();
    }
}, 20);
