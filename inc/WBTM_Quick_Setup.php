<?php
if (!defined('ABSPATH')) {
    die; // Cannot access pages directly.
}
require_once WBTM_PLUGIN_DIR . 'inc/MP_Global_Function.php';
require_once WBTM_PLUGIN_DIR . 'inc/MP_Global_Style.php';

if (!class_exists('WBTM_Quick_Setup')) {
    class WBTM_Quick_Setup {
        public function __construct() {
            add_action('admin_enqueue_scripts', array($this, 'add_admin_scripts'), 10, 1);
            add_action('admin_menu', array($this, 'quick_setup_menu'));
        }

        public function add_admin_scripts() {
            wp_enqueue_style('mp_plugin_global', WBTM_PLUGIN_URL . 'assets/helper/mp_style/mp_style.css', array(), time());
            wp_enqueue_script('mp_plugin_global', WBTM_PLUGIN_URL . '/assets/helper/mp_style/mp_script.js', array('jquery'), time(), true);
            wp_enqueue_style('mp_admin_settings', WBTM_PLUGIN_URL . '/assets/admin/mp_admin_settings.css', array(), time());
            wp_enqueue_script('mp_admin_settings', WBTM_PLUGIN_URL . '/assets/admin/mp_admin_settings.js', array('jquery'), time(), true);
            wp_enqueue_style('mpwpb_admin', WBTM_PLUGIN_URL . '/assets/admin/mpwpb_admin.css', array(), time());
            wp_enqueue_script('mpwpb_admin', WBTM_PLUGIN_URL . '/assets/admin/mpwpb_admin.js', array('jquery'), time(), true);
            // wp_enqueue_style('mp-font-awesome', plugin_dir_url( __FILE__ ) . 'assets/admin/fontawesome.min.css', array(), '5.2.0');
        }

        public function quick_setup_menu() {
            $status = MP_Global_Function::check_woocommerce();
            if ($status == 1) {
                add_submenu_page('edit.php?post_type=wbbm_bus', esc_html__('Quick Setup', 'bus-booking-manager'), '<span style="color:#10dd10">' . esc_html__('Quick Setup', 'bus-booking-manager') . '</span>', 'manage_options', 'wbbm_init_quick_setup', array($this, 'quick_setup'));
                add_submenu_page('wbbm_bus', esc_html__('Quick Setup', 'bus-booking-manager'), '<span style="color:#10dd10">' . esc_html__('Quick Setup', 'bus-booking-manager') . '</span>', 'manage_options', 'wbtm_quick_setup', array($this, 'quick_setup'));
            } else {
                add_menu_page(esc_html__('Bus', 'bus-booking-manager'), esc_html__('Bus', 'bus-booking-manager'), 'manage_options', 'wbbm_bus', array($this, 'quick_setup'), 'dashicons-slides', 6);
                add_submenu_page('wbbm_bus', esc_html__('Quick Setup', 'bus-booking-manager'), '<span style="color:#10dd17">' . esc_html__('Quick Setup', 'bus-booking-manager') . '</span>', 'manage_options', 'wbbm_init_quick_setup', array($this, 'quick_setup'));
            }
        }

        public function quick_setup() {
            // Safely get the nonce from $_POST
            $nonce = isset($_POST['welcome_setup_nonce']) ? sanitize_text_field(wp_unslash($_POST['welcome_setup_nonce'])) : '';

            // Verify the nonce
            if ( ! $nonce || ! wp_verify_nonce($nonce, 'welcome_setup_nonce_action') ) {
                wc_add_notice(__('Security check failed. Please try again.', 'bus-booking-manager'), 'error');
                return false; // Stop add to cart
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

            $status = MP_Global_Function::check_woocommerce();
            if ($status != 1) {
                $next_disable = 'disabled';
            }

            ?>
            <div class="mpStyle">
                <div class="_dShadow_6_adminLayout">
                    <form method="post" action="">
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
                                    <span>&longleftarrow;<?php esc_html_e('Previous', 'bus-booking-manager'); ?></span>
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

        public function setup_welcome_content() {
            $status = MP_Global_Function::check_woocommerce();
            wp_nonce_field('welcome_setup_nonce_action', 'welcome_setup_nonce');
            ?>
            <div data-tabs-next="#mpwpb_qs_welcome">
                <h2><?php esc_html_e('Bus Booking Manager For Woocommerce Plugin', 'bus-booking-manager'); ?></h2>
                <p class="mTB_xs"><?php esc_html_e('Bus Booking Manager Plugin for WooCommerce for your site, Please go step by step and choose some options to get started.', 'bus-booking-manager'); ?></p>
                <div class="_dLayout_mT_alignCenter justifyBetween">
                    <h5>
                        <?php if ($status == 1) {
                            esc_html_e('Woocommerce already installed and activated', 'bus-booking-manager');
                        } elseif ($status == 0) {
                            esc_html_e('Woocommerce needs to install and activate', 'bus-booking-manager');
                        } else {
                            esc_html_e('Woocommerce already installed, please activate it', 'bus-booking-manager');
                        } ?>
                    </h5>
                    <?php if ($status == 1) { ?>
                        <h5><span class="fas fa-check-circle textSuccess"></span></h5>
                    <?php } elseif ($status == 0) { ?>
                        <button class="warningButton" type="submit" name="install_and_active_woo_btn"><?php esc_html_e('Install & Activate Now', 'bus-booking-manager'); ?></button>
                    <?php } else { ?>
                        <button class="themeButton" type="submit" name="active_woo_btn"><?php esc_html_e('Activate Now', 'bus-booking-manager'); ?></button>
                    <?php } ?>
                </div>
            </div>
            <?php
        }

        public function setup_general_content() {
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
                            <input type="text" class="formControl" name="mpwpb_label" value='<?php echo esc_attr($label); ?>'/>
                        </label>
                        <i class="info_text">
                            <span class="fas fa-info-circle"></span>
                            <?php esc_html_e('It will change the Bus Booking Manager post type label on the entire plugin.', 'bus-booking-manager'); ?>
                        </i>
                        <div class="divider"></div>
                        <label class="fullWidth">
                            <span class="min_300"><?php esc_html_e('Bus Booking Manager Slug:', 'bus-booking-manager'); ?></span>
                            <input type="text" class="formControl" name="mpwpb_slug" value='<?php echo esc_attr($slug); ?>'/>
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

        public function setup_content_done() {
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
