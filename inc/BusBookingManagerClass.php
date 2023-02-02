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
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'inc/NextDateClass.php';



    }

    private function define_all_hooks() {
        $NextDateClass = new NextDateClass;
        add_action('mage_next_date', array($NextDateClass,'mage_next_date_suggestion_single'), 99, 3);

    }

    private function define_all_filters() {







    }



}

new BusBookingManagerClass();

