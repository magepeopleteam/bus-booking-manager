<?php

if (!defined('ABSPATH')) {
    die; // Cannot access pages directly.
}
require_once WBTM_PLUGIN_DIR . 'inc/MP_Global_Function.php';
require_once WBTM_PLUGIN_DIR . 'inc/MP_Global_Style.php';

if (!class_exists('WBTM_Quick_Setup')) {
    class WBTM_Quick_Setup
    {
        /** The instance booted at load time, for hub delegation. */
        private static $instance = null;

        public static function instance()
        {
            return self::$instance;
        }

        public function __construct()
        {
            if (null === self::$instance) {
                self::$instance = $this;
            }
            add_action('admin_enqueue_scripts', array($this, 'add_admin_scripts'), 10, 1);
            add_action('admin_menu', array($this, 'quick_setup_menu'));
            add_action('wp_ajax_wbbm_install_activate_woocommerce', array($this, 'ajax_install_activate_woocommerce'));
        }

        /**
         * Installs (if needed) and activates WooCommerce in place, for the
         * "Requires WooCommerce" buttons on the per-bus and Payments-tab
         * cards -- no redirect to Quick Setup, no page reload until it's
         * actually done. Used from JS via admin-ajax.php.
         */
        public function ajax_install_activate_woocommerce()
        {
            check_ajax_referer('wbbm_wc_install', 'nonce');

            if (!current_user_can('install_plugins') || !current_user_can('activate_plugins')) {
                wp_send_json_error(array('message' => __('You do not have permission to install or activate plugins.', 'bus-booking-manager')));
            }

            // Installing a plugin from wordpress.org can take longer than
            // PHP's default execution limit on a slow connection -- this is
            // the one request that's explicitly allowed to run long instead
            // of being cut off mid-download.
            if (function_exists('set_time_limit')) {
                // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged -- some hosts disable this; failing silently just means the default limit still applies.
                @set_time_limit(0);
            }
            // phpcs:ignore WordPress.PHP.IniSet.Risky, WordPress.PHP.NoSilencedErrors.Discouraged
            @ini_set('max_execution_time', '0');

            $status = MP_Global_Function::wbbm_check_woocommerce();

            if (1 === $status) {
                wp_send_json_success(array(
                    'message' => __('WooCommerce is already active.', 'bus-booking-manager'),
                    'step'    => 'done',
                ));
            }

            if (0 === $status) {
                include_once ABSPATH . 'wp-admin/includes/plugin-install.php';
                include_once ABSPATH . 'wp-admin/includes/file.php';
                include_once ABSPATH . 'wp-admin/includes/misc.php';
                include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';

                $api = plugins_api('plugin_information', array(
                    'slug'   => 'woocommerce',
                    'fields' => array(
                        'short_description' => false,
                        'sections'          => false,
                        'requires'          => false,
                        'rating'            => false,
                        'ratings'           => false,
                        'downloaded'        => false,
                        'last_updated'      => false,
                        'added'             => false,
                        'tags'              => false,
                        'compatibility'     => false,
                        'homepage'          => false,
                        'donate_link'       => false,
                    ),
                ));

                if (is_wp_error($api) || empty($api->download_link)) {
                    wp_send_json_error(array(
                        'message' => is_wp_error($api)
                            ? $api->get_error_message()
                            : __('Could not reach the WordPress.org plugin directory to download WooCommerce.', 'bus-booking-manager'),
                    ));
                }

                $upgrader = new Plugin_Upgrader(new Automatic_Upgrader_Skin());
                $result = $upgrader->install($api->download_link);

                if (is_wp_error($result) || !$result) {
                    wp_send_json_error(array(
                        'message' => is_wp_error($result) ? $result->get_error_message() : __('WooCommerce could not be installed.', 'bus-booking-manager'),
                    ));
                }
            }

            $activated = activate_plugin('woocommerce/woocommerce.php');
            if (is_wp_error($activated)) {
                wp_send_json_error(array('message' => $activated->get_error_message()));
            }

            wp_send_json_success(array(
                'message' => __('WooCommerce installed and activated.', 'bus-booking-manager'),
                'step'    => 'done',
            ));
        }

        /**
         * Small inline line-icons for the onboarding screen.
         *
         * Drawn as SVG (stroke="currentColor") instead of relying on the
         * bundled Font Awesome webfont, so they always render even if that
         * font fails to load/register on a given site -- no external
         * dependency, no version mismatch to chase.
         */
        public function icon($name)
        {
            $paths = array(
                'bus'   => '<rect x="3" y="4" width="18" height="12" rx="2"></rect><path d="M3 12h18"></path><circle cx="7" cy="19" r="1.5"></circle><circle cx="17" cy="19" r="1.5"></circle><path d="M7 4v4M17 4v4"></path>',
                'bolt'  => '<path d="M13 2 4 14h6l-1 8 9-12h-6l1-8z"></path>',
                'route' => '<circle cx="6" cy="6" r="2.5"></circle><circle cx="18" cy="18" r="2.5"></circle><path d="M8 7c4 1 4 9 8 10"></path>',
                'cart'  => '<circle cx="9" cy="20" r="1.5"></circle><circle cx="18" cy="20" r="1.5"></circle><path d="M2 3h2l2.6 12.4a2 2 0 0 0 2 1.6h7.8a2 2 0 0 0 2-1.6L21 7H6"></path>',
                'check' => '<circle cx="12" cy="12" r="9"></circle><path d="M8.5 12.5l2.5 2.5 5-5"></path>',
                'download' => '<path d="M12 3v12"></path><path d="M7 10l5 5 5-5"></path><path d="M4 21h16"></path>',
            );

            $inner = isset($paths[$name]) ? $paths[$name] : '';

            return '<svg class="wbbm-onboard-svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">' . $inner . '</svg>';
        }

        /**
         * Echoes icon(). Not run through wp_kses/esc_html: the markup is a
         * hardcoded constant from icon() above, never user input, and
         * wp_kses doesn't know the SVG tag set -- it would strip the icon
         * back out.
         */
        public function render_icon($name)
        {
            echo $this->icon($name); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- trusted, hardcoded SVG.
        }

        public function add_admin_scripts()
        {
            wp_enqueue_style('mp_plugin_global', WBTM_PLUGIN_URL . 'assets/helper/mp_style/mp_style.css', array(), time());
            wp_enqueue_script('mp_plugin_global', WBTM_PLUGIN_URL . '/assets/helper/mp_style/mp_script.js', array('jquery'), time(), true);
            wp_enqueue_style('mp_admin_settings', WBTM_PLUGIN_URL . '/assets/admin/mp_admin_settings.css', array(), time());
            wp_enqueue_script('mp_admin_settings', WBTM_PLUGIN_URL . '/assets/admin/mp_admin_settings.js', array('jquery'), time(), true);
            wp_enqueue_style('mpwpb_admin', WBTM_PLUGIN_URL . '/assets/admin/mpwpb_admin.css', array(), time());
            wp_enqueue_script('mpwpb_admin', WBTM_PLUGIN_URL . '/assets/admin/mpwpb_admin.js', array('jquery'), time(), true);
            wp_enqueue_style('wbbm-onboarding', WBTM_PLUGIN_URL . '/assets/admin/wbbm-onboarding.css', array(), time());
            // wp_enqueue_style('mp-font-awesome', plugin_dir_url( __FILE__ ) . 'assets/admin/fontawesome.min.css', array(), '5.2.0');
        }

        public function quick_setup_menu()
        {
            // The Bus post type (registered either way now, WooCommerce or
            // not) already provides its own top-level admin menu at
            // edit.php?post_type=wbbm_bus via BusListPageClass -- Quick
            // Setup just needs a submenu item under it, the same regardless
            // of WooCommerce status.
            add_submenu_page('edit.php?post_type=wbbm_bus', esc_html__('Quick Setup', 'bus-booking-manager'), '<span style="color:#10dd10">' . esc_html__('Quick Setup', 'bus-booking-manager') . '</span>', 'manage_options', 'wbbm_init_quick_setup', array($this, 'quick_setup'));
        }

        public function quick_setup()
        {

            // Safely get the nonce from $_POST
            $nonce = isset($_POST['welcome_setup_nonce']) ? sanitize_text_field(wp_unslash($_POST['welcome_setup_nonce'])) : '';

            // Verify the nonce
            if (! $nonce || ! wp_verify_nonce($nonce, 'welcome_setup_nonce_action')) {
                // wc_add_notice(__('Security check failed. Please try again.', 'bus-booking-manager'), 'error');
                // return false; // Stop add to cart
            }

            if (isset($_POST['active_woo_btn'])) {
                ?>
                <script>
                    dLoaderBody();
                </script>
                <?php
                activate_plugin('woocommerce/woocommerce.php');
                ?>
                <script>
                    let mpwpb_admin_location = window.location.href;
                    mpwpb_admin_location = mpwpb_admin_location.replace('admin.php?page=wbbm_bus', 'edit.php?post_type=wbbm_bus&page=mpwpb_quick_setup');
                    window.location.href = mpwpb_admin_location;
                </script>
                <?php
            }

            if (isset($_POST['install_and_active_woo_btn'])) {
                echo '<div style="display:none">';
                include_once(ABSPATH . 'wp-admin/includes/plugin-install.php'); // for plugins_api..
                $plugin = 'woocommerce';
                $api = plugins_api('plugin_information', array(
                    'slug' => $plugin,
                    'fields' => array(
                        'short_description' => false,
                        'sections' => false,
                        'requires' => false,
                        'rating' => false,
                        'ratings' => false,
                        'downloaded' => false,
                        'last_updated' => false,
                        'added' => false,
                        'tags' => false,
                        'compatibility' => false,
                        'homepage' => false,
                        'donate_link' => false,
                    ),
                ));
                // includes necessary for Plugin_Upgrade and Plugin_Installer_Skin
                include_once(ABSPATH . 'wp-admin/includes/file.php');
                include_once(ABSPATH . 'wp-admin/includes/misc.php');
                include_once(ABSPATH . 'wp-admin/includes/class-wp-upgrader.php');
                $woocommerce_plugin = new Plugin_Upgrader(new Plugin_Installer_Skin(compact('title', 'url', 'nonce', 'plugin', 'api')));
                $woocommerce_plugin->install($api->download_link);
                activate_plugin('woocommerce/woocommerce.php');
                echo '</div>';
                ?>
                <script>
                    let mpwpb_admin_location = window.location.href;
                    mpwpb_admin_location = mpwpb_admin_location.replace('admin.php?page=wbbm_bus', 'edit.php?post_type=wbbm_bus&page=mpwpb_quick_setup');
                    window.location.href = mpwpb_admin_location;
                </script>
                <?php
            }

            if (isset($_POST['wbbm_skip_setup'])) {
                update_option('wbbm_quick_setup_done', 'yes');
                wp_safe_redirect(esc_url(admin_url('edit.php?post_type=wbbm_bus')));
                exit;
            }

            if (isset($_POST['finish_quick_setup'])) {
                $wbbm_cpt_label = isset($_POST['mpwpb_label']) ? sanitize_text_field(wp_unslash($_POST['mpwpb_label'])) : 'Bus';
                $wbbm_cpt_slug = isset($_POST['mpwpb_slug']) ? sanitize_text_field(wp_unslash($_POST['mpwpb_slug'])) : 'Bus';

                $general_settings_data = get_option('wbbm_general_setting_sec');
                $update_general_settings_arr = [
                    'wbbm_cpt_label' => $wbbm_cpt_label,
                    'wbbm_cpt_slug' => $wbbm_cpt_slug,
                ];
                $new_general_settings_data = is_array($general_settings_data) ? array_replace($general_settings_data, $update_general_settings_arr) : $update_general_settings_arr;
                update_option('wbbm_general_setting_sec', $new_general_settings_data);
                update_option('wbbm_quick_setup_done', 'yes');
                flush_rewrite_rules();
                wp_safe_redirect(esc_url(admin_url('edit.php?post_type=wbbm_bus')));
                exit;
            }

            $next_disable = '';

            $status = MP_Global_Function::wbbm_check_woocommerce();
            if ($status != 1) {
                $next_disable = 'disabled';
            }

            // echo $next_disable;

            ?>
            <div class="mpStyle wbbm-onboard-page">
                <div class="wbbm-onboard-card">
                    <form method="post" action="">
                        <?php wp_nonce_field('welcome_setup_nonce_action', 'welcome_setup_nonce'); ?>
                        <div class="mpTabsNext">
                            <div class="tabListsNext _max_700_mAuto">
                                <div data-tabs-target-next="#mpwpb_qs_welcome" class="tabItemNext">
                                    <h4 class="circleIcon">1</h4>
                                    <h5 class="circleTitle"><?php esc_html_e('Welcome', 'bus-booking-manager'); ?></h5>
                                </div>
                                <div data-tabs-target-next="#mpwpb_qs_general" class="tabItemNext">
                                    <h4 class="circleIcon">2</h4>
                                    <h5 class="circleTitle"><?php esc_html_e('General', 'bus-booking-manager'); ?></h5>
                                </div>
                                <div data-tabs-target-next="#mpwpb_qs_done" class="tabItemNext">
                                    <h4 class="circleIcon">3</h4>
                                    <h5 class="circleTitle"><?php esc_html_e('Done', 'bus-booking-manager'); ?></h5>
                                </div>
                            </div>
                            <div class="tabsContentNext _infoLayout_mT">
                                <?php
                                $this->setup_welcome_content();
                                $this->setup_general_content();
                                $this->setup_content_done();


                                ?>
                            </div>
                            <div class="justifyBetween">
                                <button type="button" class="mpBtn nextTab_prev">
                                    <span>&longleftarrow;<?php esc_html_e('Previous', 'bus-booking-manager') . $status; ?></span>
                                </button>
                                <div></div>


                                <button type="button" class="themeButton nextTab_next" <?php echo esc_attr($next_disable); ?>>
                                    <span><?php esc_html_e('Next', 'bus-booking-manager'); ?>&longrightarrow;</span>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <?php
        }

        public function setup_welcome_content()
        {
            $status = MP_Global_Function::wbbm_check_woocommerce();
            $next_disabled = ($status != 1) ? 'disabled' : '';
            ?>
            <div data-tabs-next="#mpwpb_qs_welcome">
                <div class="wbbm-onboard-brand">
                    <span class="wbbm-onboard-logo"><?php $this->render_icon('bus'); ?></span>
                    <span class="wbbm-onboard-brand-name"><?php esc_html_e('Bus Booking Manager', 'bus-booking-manager'); ?></span>
                </div>

                <div class="wbbm-onboard-hero">
                    <h2><?php esc_html_e('Create your booking site in minutes', 'bus-booking-manager'); ?></h2>
                    <p><?php esc_html_e('Everything you need to sell bus tickets online, right on top of WooCommerce.', 'bus-booking-manager'); ?></p>
                </div>

                <div class="wbbm-onboard-features">
                    <div class="wbbm-onboard-feature">
                        <span class="wbbm-onboard-feature-icon"><?php $this->render_icon('bolt'); ?></span>
                        <h4><?php esc_html_e('Quick Setup', 'bus-booking-manager'); ?></h4>
                        <p><?php esc_html_e('Get started in minutes', 'bus-booking-manager'); ?></p>
                    </div>
                    <div class="wbbm-onboard-feature">
                        <span class="wbbm-onboard-feature-icon"><?php $this->render_icon('route'); ?></span>
                        <h4><?php esc_html_e('Routes & Seats', 'bus-booking-manager'); ?></h4>
                        <p><?php esc_html_e('Configure buses and seat maps', 'bus-booking-manager'); ?></p>
                    </div>
                    <div class="wbbm-onboard-feature">
                        <span class="wbbm-onboard-feature-icon"><?php $this->render_icon('cart'); ?></span>
                        <h4><?php esc_html_e('WooCommerce Payments', 'bus-booking-manager'); ?></h4>
                        <p><?php esc_html_e('Sell tickets, get paid', 'bus-booking-manager'); ?></p>
                    </div>
                </div>

                <div class="wbbm-onboard-woo">
                    <div class="wbbm-onboard-woo-label">
                        <span class="wbbm-onboard-woo-icon"><?php $this->render_icon('cart'); ?></span>
                        <span>
                            <strong><?php esc_html_e('WooCommerce', 'bus-booking-manager'); ?></strong>
                            <small>
                                <?php if ($status == 1) {
                                    esc_html_e('Already installed and activated', 'bus-booking-manager');
                                } elseif ($status == 0) {
                                    esc_html_e('Needs to be installed and activated', 'bus-booking-manager');
                                } else {
                                    esc_html_e('Already installed, please activate it', 'bus-booking-manager');
                                } ?>
                            </small>
                        </span>
                    </div>
                    <?php if ($status == 1) { ?>
                        <span class="textSuccess"><?php $this->render_icon('check'); ?><?php esc_html_e('Activated', 'bus-booking-manager'); ?></span>
                    <?php } elseif ($status == 0) { ?>
                        <button class="wbbm-onboard-woo-btn wbbm-onboard-woo-btn-install" type="submit" name="install_and_active_woo_btn">
                            <?php $this->render_icon('download'); ?>
                            <?php esc_html_e('Install & Activate Now', 'bus-booking-manager'); ?>
                        </button>
                    <?php } else { ?>
                        <button class="wbbm-onboard-woo-btn wbbm-onboard-woo-btn-activate" type="submit" name="active_woo_btn">
                            <?php $this->render_icon('bolt'); ?>
                            <?php esc_html_e('Activate Now', 'bus-booking-manager'); ?>
                        </button>
                    <?php } ?>
                </div>

                <div class="wbbm-onboard-actions">
                    <button type="submit" name="wbbm_skip_setup" class="wbbm-onboard-btn wbbm-onboard-btn-ghost"><?php esc_html_e('Skip & Explore', 'bus-booking-manager'); ?></button>
                    <button type="button" class="wbbm-onboard-btn wbbm-onboard-btn-primary nextTab_next" <?php echo esc_attr($next_disabled); ?>><?php esc_html_e("Let's set up your first bus", 'bus-booking-manager'); ?></button>
                </div>
            </div>
            <?php
        }

        public function setup_general_content()
        {
            $general_data = get_option('wbbm_general_setting_sec');
            $label = isset($general_data['wbbm_cpt_label']) ? sanitize_text_field($general_data['wbbm_cpt_label']) : 'Bus';
            $slug = isset($general_data['wbbm_cpt_slug']) ? sanitize_text_field($general_data['wbbm_cpt_slug']) : 'Bus';

            ?>
            <div data-tabs-next="#mpwpb_qs_general">
                <div class="section">
                    <h2><?php esc_html_e('General settings', 'bus-booking-manager'); ?></h2>
                    <p class="mTB_xs"><?php esc_html_e('Choose some general options.', 'bus-booking-manager'); ?></p>
                    <div class="_dLayout_mT">
                        <label class="fullWidth">
                            <span class="min_300"><?php esc_html_e('Bus Booking Manager Label:', 'bus-booking-manager'); ?></span>
                            <input type="text" class="formControl" name="mpwpb_label" value='<?php echo esc_attr($label); ?>' />
                        </label>
                        <i class="info_text">
                            <span class="fas fa-info-circle"></span>
                            <?php esc_html_e('It will change the Bus Booking Manager post type label on the entire plugin.', 'bus-booking-manager'); ?>
                        </i>
                        <div class="divider"></div>
                        <label class="fullWidth">
                            <span class="min_300"><?php esc_html_e('Bus Booking Manager Slug:', 'bus-booking-manager'); ?></span>
                            <input type="text" class="formControl" name="mpwpb_slug" value='<?php echo esc_attr($slug); ?>' />
                        </label>
                        <i class="info_text">
                            <span class="fas fa-info-circle"></span>
                            <?php esc_html_e('It will change the Bus Booking Manager slug on the entire plugin. Remember after changing this slug you need to flush permalinks. Just go to Settings->Permalinks hit the Save Settings button', 'bus-booking-manager'); ?>
                        </i>
                    </div>
                </div>
            </div>
            <?php
        }

        public function setup_content_done()
        {
            ?>
            <div data-tabs-next="#mpwpb_qs_done">
                <h2><?php esc_html_e('Finalize Setup', 'bus-booking-manager'); ?></h2>
                <p class="mTB_xs"><?php esc_html_e('You are about to finish & save the Bus Booking Manager For WooCommerce Plugin setup process', 'bus-booking-manager'); ?></p>
                <div class="mT allCenter">
                    <button type="submit" name="finish_quick_setup" class="themeButton"><?php esc_html_e('Finish & Save', 'bus-booking-manager'); ?></button>
                </div>
            </div>
            <?php
        }
    }
    new WBTM_Quick_Setup();
}
