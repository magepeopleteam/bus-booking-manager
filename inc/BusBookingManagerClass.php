<?php
if (!defined('ABSPATH')) exit;  // if direct access

class BusBookingManagerClass
{
    public function __construct()
    {
        $this->load_dependencies();

        $this->define_all_hooks();
        $this->define_all_filters();
        $this->define_all_shortcode();

    }


    private function load_dependencies() {
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'inc/CommonClass.php';
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'inc/SearchClass.php';
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'inc/ShortCodeClass.php';
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'inc/FilterClass.php';
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'inc/NextDateClass.php';
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'inc/AdminMetaBoxClass.php';
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'inc/ActiveDataShowClass.php';
    }

    private function define_all_hooks() {
        $NextDateClass = new NextDateClass;
        $AdminMetaBoxClass = new AdminMetaBoxClass;
        $ActiveDataShowClass = new ActiveDataShowClass;
        $WbbmSearchClass = new SearchClass;

        add_action('mage_next_date', array($NextDateClass,'mage_next_date_suggestion_single'), 99, 3);
        //Save meta
        add_action('save_post', array($AdminMetaBoxClass, 'wbbm_single_settings_meta_save'));
        add_action('wbbm_active_date', array($ActiveDataShowClass,'active_date_picker'), 99, 3);

        add_action('mage_search_from_only',array($WbbmSearchClass,'search_from_only') ,10,2);
        add_action('wbbm_prevent_form_resubmission', array($WbbmSearchClass,'wbbm_prevent_form_resubmission_fun'));
        add_action('woocommerce_before_add_to_cart_button', function() {
            ?>
            <p class="form-field">
                <label for="my_custom_text"><?php esc_html_e('Custom Text', 'bus-booking-manager'); ?></label>
                <input type="text" id="my_custom_text" name="my_custom_text" />
            </p>
            <?php wp_nonce_field('add_to_cart_custom_action', 'add_to_cart_custom_nonce');
        });


    }

    private function define_all_filters() {

        $FilterClass = new FilterClass();
        add_filter('template_include',array($FilterClass, 'wbbm_load_bus_templates'), 20, 2);
        add_filter('woocommerce_add_cart_item_data', array($FilterClass, 'wbbm_add_custom_fields_text_to_cart_item'), 20, 2);
        add_filter('woocommerce_get_item_data', array($FilterClass, 'wbbm_display_custom_fields_text_cart'), 99, 2);

    }

    private function define_all_shortcode() {

        $ShortCodeClass = new ShortCodeClass();

        add_shortcode('bus-search-form', array($ShortCodeClass, 'mage_bus_search_form'));
        add_shortcode('bus-search', array($ShortCodeClass, 'mage_bus_search'));




    }

}

new BusBookingManagerClass();

