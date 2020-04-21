<?php
/**
 * 2AM Awesome loginbar Settings Controls
 *
 * @version 1.0
 *
 */
if ( !class_exists('MAGE_WBBM_Setting_Controls' ) ):
class MAGE_WBBM_Setting_Controls {

    private $settings_api;

    function __construct() {
        $this->settings_api = new MAGE_Setting_API;

        add_action( 'admin_init', array($this, 'admin_init') );
        add_action( 'admin_menu', array($this, 'admin_menu') );
    }

    function admin_init() {

        //set the settings
        $this->settings_api->set_sections( $this->get_settings_sections() );
        $this->settings_api->set_fields( $this->get_settings_fields() );

        //initialize settings
        $this->settings_api->admin_init();
    }
 
    function admin_menu() {
        //add_options_page( 'Event Settings', 'Event Settings', 'delete_posts', 'mep_event_settings_page', array($this, 'plugin_page') );

         add_submenu_page('edit.php?post_type=wbbm_bus', __('General Settings','bus-booking-manager'), __('General Settings','bus-booking-manager'), 'manage_options', 'wbbm_gen_settings_page', array($this, 'plugin_page'));
    }

    function get_settings_sections() {

        $sections = array(
            array(
                'id' => 'wbbm_general_setting_sec',
                'title' => __( 'General Settings', 'bus-booking-manager' )
            ),
            array(
                'id' => 'wbbm_label_setting_sec',
                'title' => __( 'Translation Settings', 'bus-booking-manager' )
            ),
            array(
                'id' => 'wbbm_style_setting_sec',
                'title' => __( 'Style Settings', 'bus-booking-manager' )
            ) 
        );



        return $sections;
    }

    /**
     * Returns all the settings fields
     *
     * @return array settings fields
     */
    function get_settings_fields() {
        $settings_fields = array(
            'wbbm_general_setting_sec' => array(

                array(
                    'name' => 'wbbm_cpt_label',
                    'label' => __( 'CPT Name', 'bus-booking-manager' ),
                    'desc' => __( 'Enter the name you want to display as post type name. Default name is Bus', 'bus-booking-manager' ),
                    'type' => 'text',
                    'default' => 'Bus'
                ),

                array(
                    'name' => 'wbbm_cpt_slug',
                    'label' => __( 'Slug', 'bus-booking-manager' ),
                    'desc' => __( 'Pease enter your SEO Friendly slug name. Default slug is bus, Please save your permalink settings after change this slug.', 'bus-booking-manager' ),
                    'type' => 'text',
                    'default' => 'bus'
                    
                ),  
                array(
                    'name' => 'wbbm_search_result_page',
                    'label' => __( 'Search Result Page', 'wedevs' ),
                    'desc' => __( 'Please select page where you want to show the search result page, By default search result page is bus-search. You can also set any page as search result page by put the page slug into the shortcode. Ex: [bus-search-form result-page="CUSTOM-PAGE-SLUG-HERE"]', 'wedevs' ),
                    'type' => 'select',
                    'default' => 'bus-search',
                    'options' => wbbm_get_page_list()
                )
                



            ),


  'wbbm_label_setting_sec' => array(

            array(
                'name' => 'wbbm_buy_ticket_text',
                'label' => __( 'BUY TICKET', 'bus-booking-manager' ),
                'desc' => __( 'Enter the text which you want to display as To Search form page.', 'bus-booking-manager' ),
                'type' => 'text',
                'default' => 'BUY TICKET'
            ),
            array(
                'name' => 'wbbm_from_text',
                'label' => __( 'From', 'bus-booking-manager' ),
                'desc' => __( 'Enter the text which you want to display as To Search form page.', 'bus-booking-manager' ),
                'type' => 'text',
                'default' => 'From:'
            ),
          array(
                'name' => 'wbbm_to_text',
                'label' => __( 'To:', 'bus-booking-manager' ),
                'desc' => __( 'Enter the text which you want to display as To Search form page.', 'bus-booking-manager' ),
                'type' => 'text',
                'default' => 'To:'
            ),
            
          array(
                'name' => 'wbbm_date_of_journey_text',
                'label' => __( 'Date of Journey:', 'bus-booking-manager' ),
                'desc' => __( 'Enter the text which you want to display as Date of Journey Search form page.', 'bus-booking-manager' ),
                'type' => 'text',
                'default' => 'Date of Journey:'
            ),

                array(
                'name' => 'wbbm_return_date_text',
                'label' => __( 'Return Date:', 'bus-booking-manager' ),
                'desc' => __( 'Enter the text which you want to display as Date of Journey Search form page.', 'bus-booking-manager' ),
                'type' => 'text',
                'default' => 'Return Date:'
            ),

          array(
                'name' => 'wbbm_one_way_text',
                'label' => __( 'One Way', 'bus-booking-manager' ),
                'desc' => __( 'Enter the text which you want to display as One Way Search form page.', 'bus-booking-manager' ),
                'type' => 'text',
                'default' => 'One Way'
            ),

          array(
                'name' => 'wbbm_return_text',
                'label' => __( 'Return', 'bus-booking-manager' ),
                'desc' => __( 'Enter the text which you want to display as Return Search form page.', 'bus-booking-manager' ),
                'type' => 'text',
                'default' => 'Return'
            ),

          array(
                'name' => 'wbbm_search_buses_text',
                'label' => __( 'SEARCH BUSES', 'bus-booking-manager' ),
                'desc' => __( 'Enter the text which you want to display as SEARCH BUSES button form page.', 'bus-booking-manager' ),
                'type' => 'text',
                'default' => 'SEARCH BUSES'
            ),
            array(
                'name' => 'wbbm_route_text',
                'label' => __( 'Route', 'bus-booking-manager' ),
                'desc' => __( 'Enter the text which you want to display as Route Search Result Page.', 'bus-booking-manager' ),
                'type' => 'text',
                'default' => 'Route'
            ),
            array(
                'name' => 'wbbm_date_text',
                'label' => __( 'Date:', 'bus-booking-manager' ),
                'desc' => __( 'Enter the text which you want to display as Date Search Result Page.', 'bus-booking-manager' ),
                'type' => 'text',
                'default' => 'Date:'
            ),
            array(
                'name' => 'wbbm_bus_name_text',
                'label' => __( 'Bus Name:', 'bus-booking-manager' ),
                'desc' => __( 'Enter the text which you want to display as Bus Name Search Result Page.', 'bus-booking-manager' ),
                'type' => 'text',
                'default' => 'Bus Name:'
            ),
             array(
                'name' => 'wbbm_departing_text',
                'label' => __( 'DEPARTING', 'bus-booking-manager' ),
                'desc' => __( 'Enter the text which you want to display as DEPARTING Search Result Page.', 'bus-booking-manager' ),
                'type' => 'text',
                'default' => 'DEPARTING'
            ),
             array(
                'name' => 'wbbm_coach_no_text',
                'label' => __( 'COACH NO', 'bus-booking-manager' ),
                'desc' => __( 'Enter the text which you want to display as COACH NO Search Result Page.', 'bus-booking-manager' ),
                'type' => 'text',
                'default' => 'COACH NO'
            ),
             array(
                'name' => 'wbbm_starting_text',
                'label' => __( 'STARTING TIME', 'bus-booking-manager' ),
                'desc' => __( 'Enter the text which you want to display as STARTING Search Result Page.', 'bus-booking-manager' ),
                'type' => 'text',
                'default' => 'STARTING TIME'
            ),
             array(
                'name' => 'wbbm_end_text',
                'label' => __( 'END TIME', 'bus-booking-manager' ),
                'desc' => __( 'Enter the text which you want to display as END Search Result Page.', 'bus-booking-manager' ),
                'type' => 'text',
                'default' => 'ARRIVAL TIME'
            ),
             array(
                'name' => 'wbbm_fare_text',
                'label' => __( 'FARE', 'bus-booking-manager' ),
                'desc' => __( 'Enter the text which you want to display as FARE Search Result Page.', 'bus-booking-manager' ),
                'type' => 'text',
                'default' => 'FARE'
            ),
             array(
                'name' => 'wbbm_ticket_text',
                'label' => __( 'Ticket', 'bus-booking-manager' ),
                'desc' => __( 'Enter the text which you want to display as Ticket Search Result Page.', 'bus-booking-manager' ),
                'type' => 'text',
                'default' => 'Ticket'
            ),
             array(
                'name' => 'wbbm_type_text',
                'label' => __( 'TYPE', 'bus-booking-manager' ),
                'desc' => __( 'Enter the text which you want to display as TYPE Search Result Page.', 'bus-booking-manager' ),
                'type' => 'text',
                'default' => 'TYPE'
            ),
             array(
                'name' => 'wbbm_arrival_text',
                'label' => __( 'ARRIVAL', 'bus-booking-manager' ),
                'desc' => __( 'Enter the text which you want to display as ARRIVAL Search Result Page.', 'bus-booking-manager' ),
                'type' => 'text',
                'default' => 'ARRIVAL'
            ),
             array(
                'name' => 'wbbm_seats_available_text',
                'label' => __( 'SEATS AVAILABLE ', 'bus-booking-manager' ),
                'desc' => __( 'Enter the text which you want to display as SEATS AVAILABLE Search Result Page.', 'bus-booking-manager' ),
                'type' => 'text',
                'default' => 'SEATS AVAILABLE'
            ),
             array(
                'name' => 'wbbm_item_in_cart_text',
                'label' => __( 'Item added to cart ', 'bus-booking-manager' ),
                'desc' => __( 'Enter the text which you want to display as Item has been added to cart Search Result Page.', 'bus-booking-manager' ),
                'type' => 'text',
                'default' => 'Item has been added to cart'
            ),
             array(
                'name' => 'wbbm_view_text',
                'label' => __( 'VIEW', 'bus-booking-manager' ),
                'desc' => __( 'Enter the text which you want to display as VIEW Search Result Page.', 'bus-booking-manager' ),
                'type' => 'text',
                'default' => 'VIEW'
            ),
            array(
                'name' => 'wbbm_view_seats_text',
                'label' => __( 'View Seats', 'bus-booking-manager' ),
                'desc' => __( 'Enter the text which you want to display as View Seats button Result Page.', 'bus-booking-manager' ),
                'type' => 'text',
                'default' => 'View Seats'
            ),

             array(
                'name' => 'wbbm_start_arrival_time_text',
                'label' => __( 'Start & Arrival Time', 'bus-booking-manager' ),
                'desc' => __( 'Enter the text which you want to display as Start & Arrival Time Details Page.', 'bus-booking-manager' ),
                'type' => 'text',
                'default' => 'Start & Arrival Time'
            ),

             array(
                'name' => 'wbbm_seat_no_text',
                'label' => __( 'Seat No', 'bus-booking-manager' ),
                'desc' => __( 'Enter the text which you want to display as Seat No Details Page.', 'bus-booking-manager' ),
                'type' => 'text',
                'default' => 'Seat No'
            ),

             array(
                'name' => 'wbbm_remove_text',
                'label' => __( 'Remove', 'bus-booking-manager' ),
                'desc' => __( 'Enter the text which you want to display as Remove Details Page.', 'bus-booking-manager' ),
                'type' => 'text',
                'default' => 'Remove'
            ),
             array(
                'name' => 'wbbm_total_text',
                'label' => __( 'Total', 'bus-booking-manager' ),
                'desc' => __( 'Enter the text which you want to display as Total Details Page.', 'bus-booking-manager' ),
                'type' => 'text',
                'default' => 'Total'
            ),
             array(
                'name' => 'wbbm_sub_total_text',
                'label' => __( 'Sub Total', 'bus-booking-manager' ),
                'desc' => __( 'Enter the text which you want to display as Sub Total Details Page.', 'bus-booking-manager' ),
                'type' => 'text',
                'default' => 'Sub Total'
            ),
             array(
                'name' => 'wbbm_book_now_text',
                'label' => __( 'BOOK NOW', 'bus-booking-manager' ),
                'desc' => __( 'Enter the text which you want to display as BOOK NOW button details Page.', 'bus-booking-manager' ),
                'type' => 'text',
                'default' => 'BOOK NOW'
            ),

             array(
                'name' => 'wbbm_bus_no_text',
                'label' => __( 'Bus No:', 'bus-booking-manager' ),
                'desc' => __( 'Enter the text which you want to display as Bus No single bus details Page.', 'bus-booking-manager' ),
                'type' => 'text',
                'default' => 'Bus No:'
            ),
            array(
                'name' => 'wbbm_total_seat_text',
                'label' => __('Total Seat:', 'bus-booking-manager' ),
                'desc' => __('Enter the text which you want to display as Total Seat  bus details Page.', 'bus-booking-manager' ),
                'type' => 'text',
                'default' => 'Total Seat:'
            ),
            array(
                'name' => 'wbbm_boarding_points_text',
                'label' => __('Boarding Points', 'bus-booking-manager' ),
             'desc' => __('Enter the text which you want to display as Boarding Points single bus details Page.', 'bus-booking-manager' ),
                'type' => 'text',
                'default' => 'Boarding'
            ),  
             array(
                'name' => 'wbbm_dropping_points_text',
                'label' => __('Dropping Points', 'bus-booking-manager' ),
             'desc' => __('Enter the text which you want to display as Dropping Points single bus details Page.', 'bus-booking-manager' ),
                'type' => 'text',
                'default' => 'Dropping'
            ),  
            
             array(
                'name' => 'wbbm_select_journey_date_text',
                'label' => __('Select Journey Date', 'bus-booking-manager' ),
             'desc' => __('Enter the text which you want to display as Select Journey Date single bus details Page.', 'bus-booking-manager' ),
                'type' => 'text',
                'default' => 'Select Journey Date'
            ),
  
          array(
                'name' => 'wbbm_search_text',
                'label' => __('Search', 'bus-booking-manager' ),
             'desc' => __('Enter the text which you want to display as search button single bus details Page.', 'bus-booking-manager' ),
                'type' => 'text',
                'default' => 'Search'
            ),

         array(
                'name' => 'wbbm_seat_list_text',
                'label' => __('Seat List:', 'bus-booking-manager' ),
             'desc' => __('Enter the text which you want to display as search button single bus seat list in cart Page.', 'bus-booking-manager' ),
                'type' => 'text',
                'default' => 'Seat List:'
            ),

        array(
                'name' => 'wbbm_total_passenger_text',
                'label' => __('Total Passenger:', 'bus-booking-manager' ),
             'desc' => __('Enter the text which you want to display text for Total Passenger.', 'bus-booking-manager' ),
                'type' => 'text',
                'default' => 'Total Passenger:'
            ),
         array(
                'name' => 'wbbm_adult_text',
                'label' => __('Adult:', 'bus-booking-manager' ),
             'desc' => __('Enter the text which you want to display text for Total Passenger.', 'bus-booking-manager' ),
                'type' => 'text',
                'default' => 'Adult:'
            ),
          array(
                'name' => 'wbbm_child_text',
                'label' => __('Child:', 'bus-booking-manager' ),
             'desc' => __('Enter the text which you want to display text for Total Passenger.', 'bus-booking-manager' ),
                'type' => 'text',
                'default' => 'Child:'
            ),

        ),
        
         'wbbm_style_setting_sec' => array(

                array(
                    'name' => 'wbbm_base_color',
                    'label' => __( 'Chose Base Color', 'bus-booking-manager' ),
                    'desc' => __( 'Chose Base color that will match your website color', 'bus-booking-manager' ),
                    'type' => 'color',
                ),

                         
            ),




        );

        return $settings_fields;
    }

    function plugin_page() {
        echo '<div class="wrap">';

        $this->settings_api->show_navigation();
        $this->settings_api->show_forms();

        echo '</div>';
    }

    /**
     * Get all the pages
     *
     * @return array page names with key value pairs
     */
    function get_pages() {
        $pages = get_pages();
        $pages_options = array();
        if ( $pages ) {
            foreach ($pages as $page) {
                $pages_options[$page->ID] = $page->post_title;
            }
        }

        return $pages_options;
    }

}
endif;

$settings = new MAGE_WBBM_Setting_Controls();


function wbbm_get_option( $option, $section, $default = '' ) {
    $options = get_option( $section );

    if ( isset( $options[$option] ) ) {
        return $options[$option];
    }
    
    return $default;
}