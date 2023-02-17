<?php
if (!defined('ABSPATH')) exit;  // if direct access

class BusBookingManagerClass
{
    public function __construct()
    {
        $this->load_dependencies();

        $this->define_all_hooks();
        $this->define_all_filters();

    }


    private function load_dependencies() {
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'inc/CommonClass.php';
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'inc/FilterClass.php';
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'inc/NextDateClass.php';
        //require_once plugin_dir_path( dirname( __FILE__ ) ) . 'inc/ActiveDataShowClass.php';
    }

    private function define_all_hooks() {
        $NextDateClass = new NextDateClass;
        //$ActiveDataShowClass = new ActiveDataShowClass;

        add_action('mage_next_date', array($NextDateClass,'mage_next_date_suggestion_single'), 99, 3);
        //add_action('active_date', array($ActiveDataShowClass,'active_date_picker'), 99, 3);
    }

    private function define_all_filters() {

        $FilterClass = new FilterClass();

        add_filter('woocommerce_add_cart_item_data', array($FilterClass, 'wbbm_add_custom_fields_text_to_cart_item'), 20, 2);
        add_filter('woocommerce_get_item_data', array($FilterClass, 'wbbm_display_custom_fields_text_cart'), 20, 2);

    }

}

new BusBookingManagerClass();

