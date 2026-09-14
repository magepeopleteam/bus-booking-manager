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
        add_action('wp_ajax_wbbm_toggle_custom_gateway', array($this, 'ajax_toggle_custom_gateway'));
        add_action('wp_ajax_wbbm_save_stripe_settings', array($this, 'ajax_save_stripe_settings'));
        add_action('wp_ajax_wbbm_save_paypal_settings', array($this, 'ajax_save_paypal_settings'));
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

    /** Instant-save for the Offline Payment settings: enable toggle, Heading/Instructions, and the payment-types repeater. */
    public function ajax_save_offline_message()
    {
        check_ajax_referer('wbbm_payment_ajax', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Insufficient permissions.', 'bus-booking-manager')));
        }

        $settings = get_option('wbbm_payment_settings');
        $settings = is_array($settings) ? $settings : array();
        $settings['offline_enabled'] = !empty($_POST['offline_enabled']) && 'yes' === $_POST['offline_enabled'];
        $settings['offline_label'] = isset($_POST['offline_label']) ? sanitize_text_field(wp_unslash($_POST['offline_label'])) : '';
        $settings['offline_instructions'] = isset($_POST['offline_instructions']) ? sanitize_textarea_field(wp_unslash($_POST['offline_instructions'])) : '';

        // Payment-types repeater: posted as a JSON array of {label,
        // instructions, enabled} from the client (built fresh from whatever
        // rows are in the DOM at save time, added/removed rows included),
        // decoded and re-sanitized field-by-field here rather than trusted
        // as-is.
        $methods = array();
        $raw_methods = isset($_POST['offline_methods']) ? json_decode(wp_unslash($_POST['offline_methods']), true) : array();
        if (is_array($raw_methods)) {
            foreach ($raw_methods as $item) {
                if (!is_array($item)) {
                    continue;
                }
                $label = isset($item['label']) ? sanitize_text_field($item['label']) : '';
                if ('' === $label) {
                    continue;
                }
                $methods[] = array(
                    'slug'         => sanitize_title($label),
                    'label'        => $label,
                    'instructions' => isset($item['instructions']) ? sanitize_text_field($item['instructions']) : '',
                    'enabled'      => !empty($item['enabled']),
                );
            }
        }
        $settings['offline_methods'] = $methods;

        update_option('wbbm_payment_settings', $settings);

        wp_send_json_success(array(
            'configured'  => self::is_gateway_configured('offline'),
            'has_gateway' => $this->mode_has_gateway('offline'),
        ));
    }

    /**
     * Is this one gateway (offline/stripe/paypal) enabled AND has the
     * minimum config it needs to actually take a payment -- the per-row
     * counterpart to mode_has_gateway('offline'), which only answers "is
     * at least one of the three usable".
     *
     * Deliberately STRICTER than, and independent from,
     * wbbm_get_active_custom_gateways() (inc/wbbm-offline-booking.php) --
     * that one only gates on the enable toggle, on purpose, so the
     * frontend picker can be previewed before real API keys are entered.
     * This one still requires real keys, because its whole job is to warn
     * *this admin* when a gateway is switched on but not actually ready
     * to take a payment yet (the "NEEDS API KEYS" pill).
     */
    private static function is_gateway_configured($gateway)
    {
        $settings = get_option('wbbm_payment_settings');
        $settings = is_array($settings) ? $settings : array();

        if ('offline' === $gateway) {
            $enabled = !array_key_exists('offline_enabled', $settings) || !empty($settings['offline_enabled']);
            $label = isset($settings['offline_label']) ? trim($settings['offline_label']) : '';
            return $enabled && '' !== $label;
        }

        if ('stripe' === $gateway) {
            return !empty($settings['stripe_enabled']) && !empty($settings['stripe_secret_key']);
        }

        if ('paypal' === $gateway) {
            return !empty($settings['paypal_enabled']) && !empty($settings['paypal_client_id']) && !empty($settings['paypal_secret']);
        }

        return false;
    }

    /**
     * Enable/disable one of the three Custom Payment Method gateways
     * (offline/stripe/paypal) in place -- same instant-toggle pattern as
     * ajax_toggle_wc_gateway(), just against wbbm_payment_settings instead
     * of a WC_Payment_Gateway object.
     */
    public function ajax_toggle_custom_gateway()
    {
        check_ajax_referer('wbbm_payment_ajax', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Insufficient permissions.', 'bus-booking-manager')));
        }

        $gateway = isset($_POST['gateway']) ? sanitize_key(wp_unslash($_POST['gateway'])) : '';
        if (!in_array($gateway, array('offline', 'stripe', 'paypal'), true)) {
            wp_send_json_error(array('message' => __('Unknown payment gateway.', 'bus-booking-manager')));
        }

        $enabled = !empty($_POST['enabled']) && 'yes' === $_POST['enabled'];

        $settings = get_option('wbbm_payment_settings');
        $settings = is_array($settings) ? $settings : array();
        $settings[$gateway . '_enabled'] = $enabled;
        update_option('wbbm_payment_settings', $settings);

        wp_send_json_success(array(
            'enabled'     => $enabled ? 'yes' : 'no',
            'configured'  => self::is_gateway_configured($gateway),
            'has_gateway' => $this->mode_has_gateway('offline'),
        ));
    }

    /** Instant-save for the Stripe Configure modal. */
    public function ajax_save_stripe_settings()
    {
        check_ajax_referer('wbbm_payment_ajax', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Insufficient permissions.', 'bus-booking-manager')));
        }

        $settings = get_option('wbbm_payment_settings');
        $settings = is_array($settings) ? $settings : array();
        if (!empty($_POST['currency_code'])) {
            $settings['currency_code'] = strtoupper(sanitize_text_field(wp_unslash($_POST['currency_code'])));
        }
        $settings['stripe_publishable_key'] = isset($_POST['stripe_publishable_key']) ? sanitize_text_field(wp_unslash($_POST['stripe_publishable_key'])) : '';
        $settings['stripe_secret_key'] = isset($_POST['stripe_secret_key']) ? sanitize_text_field(wp_unslash($_POST['stripe_secret_key'])) : '';
        $settings['stripe_test_mode'] = !empty($_POST['stripe_test_mode']) && 'yes' === $_POST['stripe_test_mode'];
        update_option('wbbm_payment_settings', $settings);

        wp_send_json_success(array(
            'configured'  => self::is_gateway_configured('stripe'),
            'has_gateway' => $this->mode_has_gateway('offline'),
        ));
    }

    /** Instant-save for the PayPal Configure modal. */
    public function ajax_save_paypal_settings()
    {
        check_ajax_referer('wbbm_payment_ajax', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Insufficient permissions.', 'bus-booking-manager')));
        }

        $settings = get_option('wbbm_payment_settings');
        $settings = is_array($settings) ? $settings : array();
        $settings['paypal_client_id'] = isset($_POST['paypal_client_id']) ? sanitize_text_field(wp_unslash($_POST['paypal_client_id'])) : '';
        $settings['paypal_secret'] = isset($_POST['paypal_secret']) ? sanitize_text_field(wp_unslash($_POST['paypal_secret'])) : '';
        $settings['paypal_sandbox_mode'] = !empty($_POST['paypal_sandbox_mode']) && 'yes' === $_POST['paypal_sandbox_mode'];
        update_option('wbbm_payment_settings', $settings);

        wp_send_json_success(array(
            'configured'  => self::is_gateway_configured('paypal'),
            'has_gateway' => $this->mode_has_gateway('offline'),
        ));
    }

    /**
     * Is there actually something that can take a payment for the given
     * flow right now? WooCommerce: at least one enabled gateway. Custom
     * Payment Method ("offline" mode, despite the name -- see the class
     * docblock on render_offline_payment_section()): at least one of the
     * three gateways (Offline/Stripe/PayPal) is enabled and has the
     * minimum config it needs to actually take a payment.
     */
    private function mode_has_gateway($mode)
    {
        if ('offline' === $mode) {
            // True "is any gateway actually ready" check -- deliberately
            // stricter than the frontend picker's own gate
            // (wbbm_get_active_custom_gateways(), enabled-only by design so
            // it can be previewed before real keys exist). This banner's
            // job is the opposite: telling the admin nothing can really
            // take a payment yet, so it has to require real keys too.
            return self::is_gateway_configured('offline')
                || self::is_gateway_configured('stripe')
                || self::is_gateway_configured('paypal');
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
                <strong><?php esc_html_e('Custom Payment Method', 'bus-booking-manager'); ?></strong>
                <small><?php esc_html_e('Offline, Stripe and/or PayPal -- no WooCommerce needed', 'bus-booking-manager'); ?></small>
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
                    <?php esc_html_e('Custom Payment Method is selected but no gateway (Offline/Stripe/PayPal) is enabled and configured yet -- set one up below so customers can pay.', 'bus-booking-manager'); ?>
                <?php endif; ?>
            </span>
        </div>

        <div class="wbbm-pay-configuring" id="wbbm-pay-configuring">
            <span class="dashicons <?php echo 'woocommerce' === $default_method ? 'dashicons-cart' : 'dashicons-money-alt'; ?>"></span>
            <?php esc_html_e("You're configuring:", 'bus-booking-manager'); ?>
            <strong id="wbbm-pay-configuring-label"><?php echo 'woocommerce' === $default_method ? esc_html__('WooCommerce Checkout', 'bus-booking-manager') : esc_html__('Custom Payment Method', 'bus-booking-manager'); ?></strong>
        </div>

        <div data-mode-section="woocommerce" <?php echo 'woocommerce' === $default_method ? '' : 'style="display:none"'; ?>>
            <?php self::render_wc_gateway_section(); ?>
        </div>

        <div data-mode-section="offline" <?php echo 'offline' === $default_method ? '' : 'style="display:none"'; ?>>
            <?php self::render_offline_payment_section(); ?>
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
                    label: <?php echo wp_json_encode(__('Custom Payment Method', 'bus-booking-manager')); ?>,
                    icon: 'dashicons-money-alt',
                    warn: <?php echo wp_json_encode(__('Custom Payment Method is selected but no gateway (Offline/Stripe/PayPal) is enabled and configured yet -- set one up below so customers can pay.', 'bus-booking-manager')); ?>
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

            // Same story for the Offline side: the Enable toggle,
            // Heading/Instructions, the payment-types repeater, and its Save
            // are all inside render_offline_payment_section() now -- also
            // called from the per-bus Payment Method popup.

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

    /**
     * Offline Payment settings: Enable toggle, Heading/Instructions message,
     * and the Payment Types repeater (add/remove named methods like Bank
     * Transfer/Cash/Cheque, each with its own note). Static and
     * self-contained for the same reason as render_wc_gateway_section() --
     * used from both this Payments settings section and the per-bus Payment
     * Method popup, so they can't drift apart.
     */
    public static function render_offline_payment_section()
    {
        $settings = get_option('wbbm_payment_settings');
        $settings = is_array($settings) ? $settings : array();

        $offline_label = isset($settings['offline_label']) ? $settings['offline_label'] : __('Pay Offline', 'bus-booking-manager');
        $offline_instructions = isset($settings['offline_instructions']) ? $settings['offline_instructions'] : '';
        $offline_enabled = !array_key_exists('offline_enabled', $settings) || !empty($settings['offline_enabled']);
        $offline_methods = isset($settings['offline_methods']) && is_array($settings['offline_methods']) ? $settings['offline_methods'] : array();

        $stripe_enabled = !empty($settings['stripe_enabled']);
        $stripe_publishable_key = isset($settings['stripe_publishable_key']) ? $settings['stripe_publishable_key'] : '';
        $stripe_secret_key = isset($settings['stripe_secret_key']) ? $settings['stripe_secret_key'] : '';
        $stripe_test_mode = !array_key_exists('stripe_test_mode', $settings) || !empty($settings['stripe_test_mode']);

        $paypal_enabled = !empty($settings['paypal_enabled']);
        $paypal_client_id = isset($settings['paypal_client_id']) ? $settings['paypal_client_id'] : '';
        $paypal_secret = isset($settings['paypal_secret']) ? $settings['paypal_secret'] : '';
        $paypal_sandbox_mode = !array_key_exists('paypal_sandbox_mode', $settings) || !empty($settings['paypal_sandbox_mode']);

        // Shared by both real gateways (an ISO 4217 code, e.g. USD/EUR) --
        // there's no such setting anywhere else in the plugin today, since
        // Offline payment only ever needed a display symbol
        // (MP_Global_Function::format_price()), not a currency code an
        // external API can charge in. Edited once, in the Stripe modal, so
        // Stripe and PayPal can never disagree with each other about it.
        $currency_code = !empty($settings['currency_code']) ? strtoupper($settings['currency_code']) : 'USD';

        // Toggled "on" isn't the same as "has real keys yet" -- this pill
        // uses the STRICT check (is_gateway_configured(): enabled AND has
        // keys) on purpose, so the admin still gets warned a gateway won't
        // really take a payment, even though the frontend picker itself
        // now shows any *enabled* gateway regardless of keys (see
        // wbbm_get_active_custom_gateways()'s own docblock for why).
        $offline_configured = self::is_gateway_configured('offline');
        $stripe_configured = self::is_gateway_configured('stripe');
        $paypal_configured = self::is_gateway_configured('paypal');

        /**
         * @param bool $enabled    The row's own enable toggle.
         * @param bool $configured Enabled AND has the minimum fields it needs.
         */
        $wbbm_cgw_pill = function ($enabled, $configured) {
            if ($configured) {
                return array('is-on', __('ENABLED', 'bus-booking-manager'));
            }
            if ($enabled) {
                return array('is-needs-setup', __('NEEDS API KEYS', 'bus-booking-manager'));
            }
            return array('', __('DISABLED', 'bus-booking-manager'));
        };
        ?>
        <div class="wbbm-gw-accordion">
            <div class="wbbm-gw-accordion-header" id="wbbm-cgw-accordion-header">
                <?php esc_html_e('Custom Payment Methods', 'bus-booking-manager'); ?>
                <span class="dashicons dashicons-arrow-up-alt2"></span>
            </div>
            <div class="wbbm-gw-accordion-body" id="wbbm-cgw-accordion-body">
                <p class="description" style="margin-top:0;"><?php esc_html_e('When more than one of these is enabled, customers booking a Custom Payment Method bus choose which one to pay with.', 'bus-booking-manager'); ?></p>

                <div class="wbbm-gw-row">
                    <div class="wbbm-gw-row-top">
                        <label class="bus-switch">
                            <input type="checkbox" class="wbbm-cgw-toggle" data-gateway="offline" <?php checked($offline_enabled); ?>>
                            <span class="slider round"></span>
                        </label>
                        <strong><?php esc_html_e('Offline Payment', 'bus-booking-manager'); ?></strong>
                        <?php list($wbbm_offline_pill_class, $wbbm_offline_pill_text) = $wbbm_cgw_pill($offline_enabled, $offline_configured); ?>
                        <span class="wbbm-gw-status <?php echo esc_attr($wbbm_offline_pill_class); ?>"><?php echo esc_html($wbbm_offline_pill_text); ?></span>
                        <button type="button" class="btn btn-outline wbbm-cgw-configure" data-gateway="offline"><?php esc_html_e('Configure', 'bus-booking-manager'); ?></button>
                    </div>
                    <p class="wbbm-gw-desc"><?php esc_html_e('Manual payment -- bank transfer, cash, pay on boarding. No online charge; you confirm payment yourself in the Offline Bookings list.', 'bus-booking-manager'); ?></p>
                </div>

                <div class="wbbm-gw-row">
                    <div class="wbbm-gw-row-top">
                        <label class="bus-switch">
                            <input type="checkbox" class="wbbm-cgw-toggle" data-gateway="stripe" <?php checked($stripe_enabled); ?>>
                            <span class="slider round"></span>
                        </label>
                        <strong><?php esc_html_e('Stripe', 'bus-booking-manager'); ?></strong>
                        <?php list($wbbm_stripe_pill_class, $wbbm_stripe_pill_text) = $wbbm_cgw_pill($stripe_enabled, $stripe_configured); ?>
                        <span class="wbbm-gw-status <?php echo esc_attr($wbbm_stripe_pill_class); ?>"><?php echo esc_html($wbbm_stripe_pill_text); ?></span>
                        <button type="button" class="btn btn-outline wbbm-cgw-configure" data-gateway="stripe"><?php esc_html_e('Configure', 'bus-booking-manager'); ?></button>
                    </div>
                    <p class="wbbm-gw-desc"><?php esc_html_e('Real card payment via Stripe Checkout -- the customer pays on a secure Stripe page and the booking confirms automatically once paid.', 'bus-booking-manager'); ?></p>
                </div>

                <div class="wbbm-gw-row">
                    <div class="wbbm-gw-row-top">
                        <label class="bus-switch">
                            <input type="checkbox" class="wbbm-cgw-toggle" data-gateway="paypal" <?php checked($paypal_enabled); ?>>
                            <span class="slider round"></span>
                        </label>
                        <strong><?php esc_html_e('PayPal', 'bus-booking-manager'); ?></strong>
                        <?php list($wbbm_paypal_pill_class, $wbbm_paypal_pill_text) = $wbbm_cgw_pill($paypal_enabled, $paypal_configured); ?>
                        <span class="wbbm-gw-status <?php echo esc_attr($wbbm_paypal_pill_class); ?>"><?php echo esc_html($wbbm_paypal_pill_text); ?></span>
                        <button type="button" class="btn btn-outline wbbm-cgw-configure" data-gateway="paypal"><?php esc_html_e('Configure', 'bus-booking-manager'); ?></button>
                    </div>
                    <p class="wbbm-gw-desc"><?php esc_html_e('Real payment via PayPal Checkout -- the customer approves payment on PayPal and the booking confirms automatically once captured.', 'bus-booking-manager'); ?></p>
                </div>
            </div>
        </div>

        <?php /*
         * Each "Configure" button opens its own small modal with that
         * gateway's own fields -- unlike the WooCommerce gateway list's
         * Configure (an iframe onto an external WC screen), these are this
         * plugin's own settings, so a plain modal is enough. Reuses the
         * exact chrome already built for .wbbm-stop-modal (Add Stoppage),
         * plus a z-index bump (.wbbm-cgw-modal) so it always renders above
         * the per-bus Payment Method popup this section is also embedded
         * in (BusEditPageClass::render_payment_modal()).
         */ ?>

        <div class="wbbm-stop-modal wbbm-cgw-modal" id="wbbm-cgw-modal-offline">
            <div class="wbbm-stop-modal-box wbbm-stop-modal-wide">
                <div class="wbbm-stop-modal-head">
                    <h2><?php esc_html_e('Configure Offline Payment', 'bus-booking-manager'); ?></h2>
                    <button type="button" class="wbbm-stop-modal-close wbbm-cgw-modal-close" aria-label="<?php esc_attr_e('Close', 'bus-booking-manager'); ?>">&times;</button>
                </div>
                <div class="wbbm-stop-modal-body">
                    <div class="form-group" style="margin-bottom:16px;">
                        <label for="wbbm_offline_label" style="display:block;font-weight:600;margin-bottom:6px;"><?php esc_html_e('Heading', 'bus-booking-manager'); ?></label>
                        <input type="text" id="wbbm_offline_label" class="form-control" style="width:100%;" value="<?php echo esc_attr($offline_label); ?>" placeholder="<?php esc_attr_e('e.g. Pay Offline / Bank Transfer', 'bus-booking-manager'); ?>">
                    </div>
                    <div class="form-group" style="margin-bottom:20px;">
                        <label for="wbbm_offline_instructions" style="display:block;font-weight:600;margin-bottom:6px;"><?php esc_html_e('Instructions', 'bus-booking-manager'); ?></label>
                        <textarea id="wbbm_offline_instructions" class="form-control" rows="4" style="width:100%;" placeholder="<?php esc_attr_e('e.g. Please transfer the fare to account #1234 and bring your receipt when boarding.', 'bus-booking-manager'); ?>"><?php echo esc_textarea($offline_instructions); ?></textarea>
                    </div>
                    <div class="wbbm-pm-repeater">
                        <label style="display:block;font-weight:600;margin-bottom:6px;"><?php esc_html_e('Payment Types', 'bus-booking-manager'); ?></label>
                        <p class="description"><?php esc_html_e('The ways customers can pay you directly -- e.g. Bank Transfer, Cash, Cheque. Each can have its own note shown alongside it.', 'bus-booking-manager'); ?></p>
                        <div class="wbbm-pm-body" id="wbbm-offline-methods-body">
                            <?php foreach ($offline_methods as $method) : ?>
                                <?php
                                $m_label = isset($method['label']) ? $method['label'] : '';
                                $m_instructions = isset($method['instructions']) ? $method['instructions'] : '';
                                $m_enabled = !empty($method['enabled']);
                                ?>
                                <div class="wbbm-pm-row">
                                    <label class="bus-switch wbbm-pm-enabled-switch">
                                        <input type="checkbox" class="wbbm-pm-enabled" <?php checked($m_enabled); ?>>
                                        <span class="slider round"></span>
                                    </label>
                                    <input type="text" class="form-control wbbm-pm-label" value="<?php echo esc_attr($m_label); ?>" placeholder="<?php esc_attr_e('Label, e.g. Bank Transfer', 'bus-booking-manager'); ?>">
                                    <input type="text" class="form-control wbbm-pm-instructions" value="<?php echo esc_attr($m_instructions); ?>" placeholder="<?php esc_attr_e('Note shown with this option (optional)', 'bus-booking-manager'); ?>">
                                    <button type="button" class="wbbm-pm-remove" aria-label="<?php esc_attr_e('Remove', 'bus-booking-manager'); ?>">&times;</button>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <button type="button" class="btn btn-outline" id="wbbm-offline-methods-add">+ <?php esc_html_e('Add payment type', 'bus-booking-manager'); ?></button>
                    </div>
                </div>
                <div class="wbbm-stop-modal-foot">
                    <span class="wbbm-pay-saved-msg" id="wbbm-offline-saved-msg"></span>
                    <button type="button" class="btn btn-primary" id="wbbm-save-offline-message"><?php esc_html_e('Save', 'bus-booking-manager'); ?></button>
                </div>
            </div>
        </div>

        <div class="wbbm-stop-modal wbbm-cgw-modal" id="wbbm-cgw-modal-stripe">
            <div class="wbbm-stop-modal-box">
                <div class="wbbm-stop-modal-head">
                    <h2><?php esc_html_e('Configure Stripe', 'bus-booking-manager'); ?></h2>
                    <button type="button" class="wbbm-stop-modal-close wbbm-cgw-modal-close" aria-label="<?php esc_attr_e('Close', 'bus-booking-manager'); ?>">&times;</button>
                </div>
                <div class="wbbm-stop-modal-body">
                    <p class="description"><?php esc_html_e('From your Stripe Dashboard -- Developers > API keys.', 'bus-booking-manager'); ?></p>
                    <div class="form-group">
                        <label for="wbbm_cgw_currency_code"><?php esc_html_e('Currency code', 'bus-booking-manager'); ?></label>
                        <input type="text" id="wbbm_cgw_currency_code" class="form-control" style="max-width:100px;" maxlength="3" value="<?php echo esc_attr($currency_code); ?>" placeholder="USD">
                        <p class="description"><?php esc_html_e('3-letter ISO code (e.g. USD, EUR, GBP) -- also used for PayPal.', 'bus-booking-manager'); ?></p>
                    </div>
                    <div class="form-group">
                        <label for="wbbm_stripe_publishable_key"><?php esc_html_e('Publishable key', 'bus-booking-manager'); ?></label>
                        <input type="text" id="wbbm_stripe_publishable_key" class="form-control" value="<?php echo esc_attr($stripe_publishable_key); ?>" placeholder="pk_test_...">
                    </div>
                    <div class="form-group">
                        <label for="wbbm_stripe_secret_key"><?php esc_html_e('Secret key', 'bus-booking-manager'); ?></label>
                        <input type="password" id="wbbm_stripe_secret_key" class="form-control" value="<?php echo esc_attr($stripe_secret_key); ?>" placeholder="sk_test_...">
                    </div>
                    <div class="wbbm-offline-toggle-row">
                        <div>
                            <strong><?php esc_html_e('Test mode', 'bus-booking-manager'); ?></strong>
                            <p class="description"><?php esc_html_e('Off once you switch to live keys.', 'bus-booking-manager'); ?></p>
                        </div>
                        <label class="bus-switch">
                            <input type="checkbox" id="wbbm_stripe_test_mode" <?php checked($stripe_test_mode); ?>>
                            <span class="slider round"></span>
                        </label>
                    </div>
                </div>
                <div class="wbbm-stop-modal-foot">
                    <span class="wbbm-pay-saved-msg" id="wbbm-stripe-saved-msg"></span>
                    <button type="button" class="btn btn-primary" id="wbbm-save-stripe-settings"><?php esc_html_e('Save', 'bus-booking-manager'); ?></button>
                </div>
            </div>
        </div>

        <div class="wbbm-stop-modal wbbm-cgw-modal" id="wbbm-cgw-modal-paypal">
            <div class="wbbm-stop-modal-box">
                <div class="wbbm-stop-modal-head">
                    <h2><?php esc_html_e('Configure PayPal', 'bus-booking-manager'); ?></h2>
                    <button type="button" class="wbbm-stop-modal-close wbbm-cgw-modal-close" aria-label="<?php esc_attr_e('Close', 'bus-booking-manager'); ?>">&times;</button>
                </div>
                <div class="wbbm-stop-modal-body">
                    <p class="description"><?php esc_html_e('From your PayPal Developer Dashboard -- Apps & Credentials.', 'bus-booking-manager'); ?></p>
                    <div class="form-group">
                        <label for="wbbm_paypal_client_id"><?php esc_html_e('Client ID', 'bus-booking-manager'); ?></label>
                        <input type="text" id="wbbm_paypal_client_id" class="form-control" value="<?php echo esc_attr($paypal_client_id); ?>">
                    </div>
                    <div class="form-group">
                        <label for="wbbm_paypal_secret"><?php esc_html_e('Secret', 'bus-booking-manager'); ?></label>
                        <input type="password" id="wbbm_paypal_secret" class="form-control" value="<?php echo esc_attr($paypal_secret); ?>">
                    </div>
                    <div class="wbbm-offline-toggle-row">
                        <div>
                            <strong><?php esc_html_e('Sandbox mode', 'bus-booking-manager'); ?></strong>
                            <p class="description"><?php esc_html_e('Off once you switch to a live app.', 'bus-booking-manager'); ?></p>
                        </div>
                        <label class="bus-switch">
                            <input type="checkbox" id="wbbm_paypal_sandbox_mode" <?php checked($paypal_sandbox_mode); ?>>
                            <span class="slider round"></span>
                        </label>
                    </div>
                </div>
                <div class="wbbm-stop-modal-foot">
                    <span class="wbbm-pay-saved-msg" id="wbbm-paypal-saved-msg"></span>
                    <button type="button" class="btn btn-primary" id="wbbm-save-paypal-settings"><?php esc_html_e('Save', 'bus-booking-manager'); ?></button>
                </div>
            </div>
        </div>

        <script>
        (function () {
            var ajaxUrl = <?php echo wp_json_encode(admin_url('admin-ajax.php')); ?>;
            var nonce = <?php echo wp_json_encode(wp_create_nonce('wbbm_payment_ajax')); ?>;
            // Only present on the Payments settings section, not the per-bus
            // popup -- null-guarded below, so a no-op there.
            var warning = document.getElementById('wbbm-pay-warning');

            // Three states, not two: configured (green, actually usable),
            // enabled-but-missing-keys (amber -- won't show up for
            // customers yet), or disabled. Mirrors the PHP-rendered pill's
            // own $wbbm_cgw_pill() logic exactly, and is reused by both the
            // enable toggle and every Configure modal's Save button below
            // (saving valid keys can flip a row straight from amber to
            // green without a page reload).
            function updateGatewayPill(gateway, enabled, configured) {
                var toggle = document.querySelector('.wbbm-cgw-toggle[data-gateway="' + gateway + '"]');
                var row = toggle ? toggle.closest('.wbbm-gw-row') : null;
                var pill = row ? row.querySelector('.wbbm-gw-status') : null;
                if (!pill) { return; }

                var label, cls;
                if (configured) {
                    label = <?php echo wp_json_encode(__('ENABLED', 'bus-booking-manager')); ?>;
                    cls = 'is-on';
                } else if (enabled) {
                    label = <?php echo wp_json_encode(__('NEEDS API KEYS', 'bus-booking-manager')); ?>;
                    cls = 'is-needs-setup';
                } else {
                    label = <?php echo wp_json_encode(__('DISABLED', 'bus-booking-manager')); ?>;
                    cls = '';
                }
                pill.textContent = label;
                pill.className = 'wbbm-gw-status' + (cls ? ' ' + cls : '');
            }

            // Enable/disable toggles, shared by all three gateway rows.
            document.querySelectorAll('.wbbm-cgw-toggle').forEach(function (toggle) {
                toggle.addEventListener('change', function () {
                    var gateway = toggle.getAttribute('data-gateway');
                    var enabled = toggle.checked ? 'yes' : 'no';

                    var xhr = new XMLHttpRequest();
                    xhr.open('POST', ajaxUrl, true);
                    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                    xhr.onload = function () {
                        var res;
                        try { res = JSON.parse(xhr.responseText); } catch (err) { return; }
                        if (!res || !res.success) { return; }
                        updateGatewayPill(gateway, res.data.enabled === 'yes', !!res.data.configured);
                        if (warning) {
                            warning.style.display = res.data.has_gateway ? 'none' : '';
                        }
                    };
                    xhr.send('action=wbbm_toggle_custom_gateway&nonce=' + encodeURIComponent(nonce) + '&gateway=' + encodeURIComponent(gateway) + '&enabled=' + encodeURIComponent(enabled));
                });
            });

            var accHeader = document.getElementById('wbbm-cgw-accordion-header');
            var accBody = document.getElementById('wbbm-cgw-accordion-body');
            if (accHeader && accBody) {
                accHeader.addEventListener('click', function () {
                    var isOpen = accBody.style.display !== 'none';
                    accBody.style.display = isOpen ? 'none' : '';
                    accHeader.classList.toggle('is-collapsed', isOpen);
                });
            }

            // "Configure" opens the matching gateway's own modal.
            document.querySelectorAll('.wbbm-cgw-configure').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    var modal = document.getElementById('wbbm-cgw-modal-' + btn.getAttribute('data-gateway'));
                    if (modal) {
                        modal.classList.add('is-open');
                        document.body.classList.add('wbbm-modal-open');
                    }
                });
            });
            function closeCgwModal(modal) {
                modal.classList.remove('is-open');
                document.body.classList.remove('wbbm-modal-open');
            }
            document.querySelectorAll('.wbbm-cgw-modal-close').forEach(function (btn) {
                btn.addEventListener('click', function () { closeCgwModal(btn.closest('.wbbm-cgw-modal')); });
            });
            document.querySelectorAll('.wbbm-cgw-modal').forEach(function (modal) {
                modal.addEventListener('click', function (e) {
                    if (e.target === modal) { closeCgwModal(modal); }
                });
            });
            document.addEventListener('keydown', function (e) {
                if (e.key !== 'Escape') { return; }
                document.querySelectorAll('.wbbm-cgw-modal.is-open').forEach(closeCgwModal);
            });

            // --- Offline Configure modal: Heading/Instructions/Payment Types ---
            var methodsBody = document.getElementById('wbbm-offline-methods-body');
            var addMethodBtn = document.getElementById('wbbm-offline-methods-add');
            function newMethodRow() {
                var row = document.createElement('div');
                row.className = 'wbbm-pm-row';
                row.innerHTML = '<label class="bus-switch wbbm-pm-enabled-switch"><input type="checkbox" class="wbbm-pm-enabled" checked><span class="slider round"></span></label>' +
                    '<input type="text" class="form-control wbbm-pm-label" placeholder="<?php echo esc_js(__('Label, e.g. Bank Transfer', 'bus-booking-manager')); ?>">' +
                    '<input type="text" class="form-control wbbm-pm-instructions" placeholder="<?php echo esc_js(__('Note shown with this option (optional)', 'bus-booking-manager')); ?>">' +
                    '<button type="button" class="wbbm-pm-remove" aria-label="<?php echo esc_js(__('Remove', 'bus-booking-manager')); ?>">&times;</button>';
                return row;
            }
            if (addMethodBtn && methodsBody) {
                addMethodBtn.addEventListener('click', function () {
                    var row = newMethodRow();
                    methodsBody.appendChild(row);
                    var labelInput = row.querySelector('.wbbm-pm-label');
                    if (labelInput) { labelInput.focus(); }
                });
            }
            if (methodsBody) {
                methodsBody.addEventListener('click', function (e) {
                    var btn = e.target.closest('.wbbm-pm-remove');
                    if (!btn) { return; }
                    var row = btn.closest('.wbbm-pm-row');
                    if (row) { row.remove(); }
                });
            }

            function saveViaAjax(button, msgEl, action, gateway, extraFields) {
                button.addEventListener('click', function () {
                    var data = 'action=' + encodeURIComponent(action) + '&nonce=' + encodeURIComponent(nonce);
                    var fields = extraFields();
                    Object.keys(fields).forEach(function (key) {
                        data += '&' + key + '=' + encodeURIComponent(fields[key]);
                    });

                    var xhr = new XMLHttpRequest();
                    xhr.open('POST', ajaxUrl, true);
                    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                    xhr.onload = function () {
                        var res;
                        try { res = JSON.parse(xhr.responseText); } catch (err) { return; }
                        if (msgEl) {
                            msgEl.textContent = (res && res.success)
                                ? <?php echo wp_json_encode(__('Saved.', 'bus-booking-manager')); ?>
                                : <?php echo wp_json_encode(__('Could not save -- try again.', 'bus-booking-manager')); ?>;
                            msgEl.className = 'wbbm-pay-saved-msg' + (res && res.success ? ' is-ok' : ' is-error');
                            setTimeout(function () { msgEl.textContent = ''; }, 2500);
                        }
                        if (res && res.success) {
                            // Saving valid keys here is what can flip this
                            // row's pill from "NEEDS API KEYS" to "ENABLED"
                            // without a reload -- the toggle's own current
                            // checked state doesn't change from a Configure
                            // save, only res.data.configured can.
                            var toggle = document.querySelector('.wbbm-cgw-toggle[data-gateway="' + gateway + '"]');
                            updateGatewayPill(gateway, !!(toggle && toggle.checked), !!res.data.configured);
                        }
                        if (res && res.success && warning) {
                            warning.style.display = res.data.has_gateway ? 'none' : '';
                        }
                    };
                    xhr.send(data);
                });
            }

            var saveOfflineBtn = document.getElementById('wbbm-save-offline-message');
            if (saveOfflineBtn) {
                saveViaAjax(saveOfflineBtn, document.getElementById('wbbm-offline-saved-msg'), 'wbbm_save_offline_message', 'offline', function () {
                    var methods = [];
                    if (methodsBody) {
                        methodsBody.querySelectorAll('.wbbm-pm-row').forEach(function (row) {
                            var labelInput = row.querySelector('.wbbm-pm-label');
                            var instrInput = row.querySelector('.wbbm-pm-instructions');
                            var enabledCheck = row.querySelector('.wbbm-pm-enabled');
                            var rowLabel = labelInput ? labelInput.value.trim() : '';
                            if (!rowLabel) { return; } // blank rows are dropped, not saved
                            methods.push({
                                label: rowLabel,
                                instructions: instrInput ? instrInput.value : '',
                                enabled: !!(enabledCheck && enabledCheck.checked)
                            });
                        });
                    }
                    return {
                        offline_enabled: (document.querySelector('#wbbm-cgw-modal-offline .wbbm-cgw-toggle') || { checked: true }).checked ? 'yes' : 'no',
                        offline_label: document.getElementById('wbbm_offline_label').value,
                        offline_instructions: document.getElementById('wbbm_offline_instructions').value,
                        offline_methods: JSON.stringify(methods)
                    };
                });
            }

            var saveStripeBtn = document.getElementById('wbbm-save-stripe-settings');
            if (saveStripeBtn) {
                saveViaAjax(saveStripeBtn, document.getElementById('wbbm-stripe-saved-msg'), 'wbbm_save_stripe_settings', 'stripe', function () {
                    return {
                        currency_code: document.getElementById('wbbm_cgw_currency_code').value,
                        stripe_publishable_key: document.getElementById('wbbm_stripe_publishable_key').value,
                        stripe_secret_key: document.getElementById('wbbm_stripe_secret_key').value,
                        stripe_test_mode: document.getElementById('wbbm_stripe_test_mode').checked ? 'yes' : 'no'
                    };
                });
            }

            var savePaypalBtn = document.getElementById('wbbm-save-paypal-settings');
            if (savePaypalBtn) {
                saveViaAjax(savePaypalBtn, document.getElementById('wbbm-paypal-saved-msg'), 'wbbm_save_paypal_settings', 'paypal', function () {
                    return {
                        paypal_client_id: document.getElementById('wbbm_paypal_client_id').value,
                        paypal_secret: document.getElementById('wbbm_paypal_secret').value,
                        paypal_sandbox_mode: document.getElementById('wbbm_paypal_sandbox_mode').checked ? 'yes' : 'no'
                    };
                });
            }
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
