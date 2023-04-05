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
        $SearchClass = new SearchClass;

        add_action('mage_next_date', array($NextDateClass,'mage_next_date_suggestion_single'), 99, 3);
        //Save meta
        add_action('save_post', array($AdminMetaBoxClass, 'wbbm_single_settings_meta_save'));
        add_action('active_date', array($ActiveDataShowClass,'active_date_picker'), 99, 3);

        add_action('mage_search_from_only',array($SearchClass,'search_from_only') ,10,2);
        add_action('wbbm_prevent_form_resubmission', array($SearchClass,'wbbm_prevent_form_resubmission_fun'));


    }

    private function define_all_filters() {

        $FilterClass = new FilterClass();
        add_filter('single_template',array($FilterClass, 'wbbm_load_bus_templates'), 20, 2);
        add_filter('woocommerce_add_cart_item_data', array($FilterClass, 'wbbm_add_custom_fields_text_to_cart_item'), 20, 2);
        add_filter('woocommerce_get_item_data', array($FilterClass, 'wbbm_display_custom_fields_text_cart'), 20, 2);

    }

    private function define_all_shortcode() {

        $ShortCodeClass = new ShortCodeClass();

        add_shortcode('bus-search-form', array($ShortCodeClass, 'mage_bus_search_form'));
        add_shortcode('bus-search', array($ShortCodeClass, 'mage_bus_search'));




    }

}

new BusBookingManagerClass();

