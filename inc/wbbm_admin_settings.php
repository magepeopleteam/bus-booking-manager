<?php
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

         add_submenu_page('edit.php?post_type=wbbm_bus', __('Settings','bus-booking-manager'), '<span class="wbbm_color_green">'.__('Settings','bus-booking-manager').'</span>', 'manage_options', 'wbbm_quick_setup', array($this, 'plugin_page'));
        /**
         * Detect PRO plugin is activated
         */
        if ( !is_plugin_active( 'bus-booking-manager-pro/wbtm-pro.php' ) ) {
            /* Add Pro Submenu */
            add_submenu_page('edit.php?post_type=wbbm_bus', __('Go PRO','bus-booking-manager'), '<span class="wbbm_plugin_pro_menu">'.__('Go PRO','bus-booking-manager').'</span>', 'manage_options', 'wbbm_go_pro_page', array($this, 'wbbm_go_pro_page'));
        }          
    }

    function get_settings_sections() {
        $sections = array(
            array(
                'id' => 'wbbm_general_setting_sec',
                'title' => __( 'General Settings', 'bus-booking-manager' )
            ),
            array(
                'id' => 'wbbm_global_offday_sec',
                'title' => __( 'Global Offday Settings', 'bus-booking-manager' )
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
        return apply_filters('wbbm_settings_sec_reg',$sections);
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
                    'name' => 'wbbm_buffer_time',
                    'label' => __( 'Buffer time', 'bus-booking-manager' ),
                    'desc' => __( 'Please enter here vehicle buffer time in Minutes. By default is 0.', 'bus-booking-manager' ),
                    'type' => 'text',
                    'default' => '0',
                ),
                array(
                    'name' => 'wbbm_gutenbug_switch',
                    'label' => __( 'On/Off Gutenburg', 'bus-booking-manager' ),
                    'desc' => __( 'Enable/Disable gutenburg editor.', 'bus-booking-manager' ),
                    'type' => 'select',
                    'default' => 'on',
                    'options' => array(
                        'on' => 'On',
                        'off'  => 'Off'
                    )
                ),
                array(
                    'name' => 'wbbm_type_column_switch',
                    'label' => __( 'On/Off Type Column', 'bus-booking-manager' ),
                    'desc' => __( 'Enable/Disable type column for the minimal theme of the bus search list.', 'bus-booking-manager' ),
                    'type' => 'select',
                    'default' => 'on',
                    'options' => array(
                        'on' => 'On',
                        'off'  => 'Off'
                    )
                ),
                array(
                    'name' => 'wbbm_seat_column_switch',
                    'label' => __( 'On/Off Seat Column', 'bus-booking-manager' ),
                    'desc' => __( 'Enable/Disable seat column for the minimal theme of the bus search list.', 'bus-booking-manager' ),
                    'type' => 'select',
                    'default' => 'on',
                    'options' => array(
                        'on' => 'On',
                        'off'  => 'Off'
                    )
                ),
                array(
                    'name' => 'wbbm_entire_bus_booking_switch',
                    'label' => __( 'On/Off Entire Bus Booking Type', 'bus-booking-manager' ),
                    'desc' => __( 'Enable/Disable entire bus booking.', 'bus-booking-manager' ),
                    'type' => 'select',
                    'default' => 'off',
                    'options' => array(
                        'on' => 'On',
                        'off'  => 'Off'
                    )
                ),
                array(
                    'name' => 'discount_price_switch',
                    'label' => __( 'On/Off Discount Price', 'bus-booking-manager' ),
                    'desc' => __( 'Enable/Disable discount price in seat pricing.', 'bus-booking-manager' ),
                    'type' => 'select',
                    'default' => 'off',
                    'options' => array(
                        'on' => 'On',
                        'off'  => 'Off'
                    )
                ),
                array(
                    'name' => 'wbbm_entire_bus_booking_switch',
                    'label' => __( 'On/Off Entire bus', 'bus-booking-manager' ),
                    'desc' => __( 'Enable/Disable entire bus option.', 'bus-booking-manager' ),
                    'type' => 'select',
                    'default' => 'off',
                    'options' => array(
                        'on' => 'On',
                        'off'  => 'Off'
                    )
                ),
                array(
                    'name' => 'wbbm_cpt_label',
                    'label' => __( 'CPT Name', 'bus-booking-manager' ),
                    'desc' => __( 'Enter the name you want to display as post type name. Default is Bus.', 'bus-booking-manager' ),
                    'type' => 'text',
                    'default' => 'Bus'
                ),
                array(
                    'name'		=> 'wbbm_seat_booked_on_order_status',
                    'label'		=> __('Seat booked on status'),
                    'desc'		=> __('Seat will be booked in which state of seat order. <br> eg. If you want to showing seat as booked when seat status is "On hold" then check "On hold".','bus-booking-manager'),
                    'type'		=> 'multicheck',
                    'options'	=> array(
                        '3' => __('Pending payment', 'bus-booking-manager'),
                        '6' => __('On hold', 'bus-booking-manager'),
                        '1' => __('Processing', 'bus-booking-manager'),
                        '2' => __('Completed', 'bus-booking-manager')
                    ),
                ),
                array(
                    'name' => 'wbbm_cpt_slug',
                    'label' => __( 'Slug', 'bus-booking-manager' ),
                    'desc' => __( 'Pease enters your SEO Friendly slug name. Default slug is a bus, and Please save your permalinks setting after changing this slug. Go <strong>Settings->Permalinks</strong>.', 'bus-booking-manager' ),
                    'type' => 'text',
                    'default' => 'bus'
                    
                ),  
                array(
                    'name' => 'wbbm_search_result_page',
                    'label' => __( 'Search Result Page', 'bus-booking-manager' ),
                    'desc' => __( 'Please select the page where you want to show the search result page. By default search result page is bus-search. You can also set any page as a search result page by putting the page slug into the shortcode. Ex: <strong>[bus-search-form result-page="CUSTOM-PAGE-SLUG-HERE"]</strong>', 'bus-booking-manager' ),
                    'type' => 'select',
                    'default' => 'bus-search-list',
                    'options' => wbbm_get_page_list()
                )
            ),

            'wbbm_global_offday_sec' => array(
                array(
                    'name' => 'global_particular_onday',
                    'class' => 'global_particular_onday',
                    'label' => __( 'Global Off-Dates', 'bus-booking-manager' ),
                    'desc' => __( 'Please select global offdate', 'bus-booking-manager' ),
                    'type' => 'text',
                    'default' => '0',
                ),
                array(
                    'name' => 'bus_global_offdays',
                    'id' => 'bus_global_offdays',
                    'class' => '',
                    'label' => __( 'Global Off-Days ', 'bus-booking-manager' ),
                    'desc' => __( '', 'bus-booking-manager' ),
                    'type' => 'checkbox_multi',
                    'args' => array(
                        '7' => __('Sunday', 'bus-ticket-booking-with-seat-reservation'),
                        '1' => __('Monday', 'bus-ticket-booking-with-seat-reservation'),
                        '2' => __('Tuesday', 'bus-ticket-booking-with-seat-reservation'),
                        '3' => __('Wednesday', 'bus-ticket-booking-with-seat-reservation'),
                        '4' => __('Thursday', 'bus-ticket-booking-with-seat-reservation'),
                        '5' => __('Friday', 'bus-ticket-booking-with-seat-reservation'),
                        '6' => __('Saturday', 'bus-ticket-booking-with-seat-reservation'),
                    ),

                )
            ),


            'wbbm_label_setting_sec' => array(

            array(
                'name' => 'wbbm_buy_ticket_text',
                'label' => __( 'Buy Ticket', 'bus-booking-manager' ),
                'desc' => __( 'Enter the translated text of: <strong>Buy Ticket</strong>.', 'bus-booking-manager' ),
                'type' => 'text',
                'default' => 'Buy Ticket'
            ),
            array(
                'name' => 'wbbm_from_text',
                'label' => __( 'From', 'bus-booking-manager' ),
                'desc' => __( 'Enter the translated text of: <strong>From</strong>.', 'bus-booking-manager' ),
                'type' => 'text',
                'default' => 'From'
            ),
          array(
                'name' => 'wbbm_to_text',
                'label' => __( 'To', 'bus-booking-manager' ),
                'desc' => __( 'Enter the translated text of: <strong>To</strong>.', 'bus-booking-manager' ),
                'type' => 'text',
                'default' => 'To'
            ),
            
          array(
                'name' => 'wbbm_date_of_journey_text',
                'label' => __( 'Date of Journey', 'bus-booking-manager' ),
                'desc' => __( 'Enter the translated text of: <strong>Date of Journey</strong>.', 'bus-booking-manager' ),
                'type' => 'text',
                'default' => 'Date of Journey'
            ),

                array(
                'name' => 'wbbm_return_date_text',
                'label' => __( 'Return Date', 'bus-booking-manager' ),
                'desc' => __( 'Enter the translated text of: <strong>Return Date</strong>.', 'bus-booking-manager' ),
                'type' => 'text',
                'default' => 'Return Date'
            ),

          array(
                'name' => 'wbbm_one_way_text',
                'label' => __( 'One Way', 'bus-booking-manager' ),
                'desc' => __( 'Enter the translated text of: <strong>One Way</strong>.', 'bus-booking-manager' ),
                'type' => 'text',
                'default' => 'One Way'
            ),

          array(
                'name' => 'wbbm_return_text',
                'label' => __( 'Return', 'bus-booking-manager' ),
                'desc' => __( 'Enter the translated text of: <strong>Return</strong>.', 'bus-booking-manager' ),
                'type' => 'text',
                'default' => 'Return'
            ),

          array(
                'name' => 'wbbm_search_buses_text',
                'label' => __( 'Search Buses', 'bus-booking-manager' ),
                'desc' => __( 'Enter the translated text of: <strong>Search Buses</strong>.', 'bus-booking-manager' ),
                'type' => 'text',
                'default' => 'Search Buses'
            ),
            array(
                'name' => 'wbbm_route_text',
                'label' => __( 'Route', 'bus-booking-manager' ),
                'desc' => __( 'Enter the translated text of: <strong>Route</strong>.', 'bus-booking-manager' ),
                'type' => 'text',
                'default' => 'Route'
            ),
            array(
                'name' => 'wbbm_date_text',
                'label' => __( 'Date', 'bus-booking-manager' ),
                'desc' => __( 'Enter the translated text of: <strong>Date</strong>.', 'bus-booking-manager' ),
                'type' => 'text',
                'default' => 'Date'
            ),
            array(
                'name' => 'wbbm_bus_name_text',
                'label' => __( 'Bus Name', 'bus-booking-manager' ),
                'desc' => __( 'Enter the translated text of: <strong>Bus Name</strong>.', 'bus-booking-manager' ),
                'type' => 'text',
                'default' => 'Bus Name'
            ),
             array(
                'name' => 'wbbm_departing_text',
                'label' => __( 'Departing', 'bus-booking-manager' ),
                'desc' => __( 'Enter the translated text of: <strong>Departing</strong>.', 'bus-booking-manager' ),
                'type' => 'text',
                'default' => 'Departing'
            ),
             array(
                'name' => 'wbbm_coach_no_text',
                'label' => __( 'Coach No', 'bus-booking-manager' ),
                'desc' => __( 'Enter the translated text of: <strong>Coach No</strong>.', 'bus-booking-manager' ),
                'type' => 'text',
                'default' => 'Coach No'
            ),
             array(
                'name' => 'wbbm_starting_text',
                'label' => __( 'Starting Time', 'bus-booking-manager' ),
                'desc' => __( 'Enter the translated text of: <strong>Starting Time</strong>.', 'bus-booking-manager' ),
                'type' => 'text',
                'default' => 'Starting Time'
            ),
             array(
                'name' => 'wbbm_end_text',
                'label' => __( 'End Time', 'bus-booking-manager' ),
                'desc' => __( 'Enter the translated text of: <strong>End Time</strong>.', 'bus-booking-manager' ),
                'type' => 'text',
                'default' => 'End Time'
            ),
             array(
                'name' => 'wbbm_fare_text',
                'label' => __( 'Fare', 'bus-booking-manager' ),
                'desc' => __( 'Enter the translated text of: <strong>Fare</strong>.', 'bus-booking-manager' ),
                'type' => 'text',
                'default' => 'Fare'
            ),
             array(
                'name' => 'wbbm_ticket_text',
                'label' => __( 'Ticket', 'bus-booking-manager' ),
                'desc' => __( 'Enter the translated text of: <strong>Ticket</strong>.', 'bus-booking-manager' ),
                'type' => 'text',
                'default' => 'Ticket'
            ),
             array(
                'name' => 'wbbm_type_text',
                'label' => __( 'Type', 'bus-booking-manager' ),
                'desc' => __( 'Enter the translated text of: <strong>Type</strong>.', 'bus-booking-manager' ),
                'type' => 'text',
                'default' => 'Type'
            ),
             array(
                'name' => 'wbbm_arrival_text',
                'label' => __( 'Arrival', 'bus-booking-manager' ),
                'desc' => __( 'Enter the translated text of: <strong>Arrival</strong>.', 'bus-booking-manager' ),
                'type' => 'text',
                'default' => 'Arrival'
            ),
             array(
                'name' => 'wbbm_seats_available_text',
                'label' => __( 'Seats Available', 'bus-booking-manager' ),
                'desc' => __( 'Enter the translated text of: <strong>Seats Available</strong>.', 'bus-booking-manager' ),
                'type' => 'text',
                'default' => 'Seats Available'
            ),
             array(
                'name' => 'wbbm_item_in_cart_text',
                'label' => __( 'Item added to the cart', 'bus-booking-manager' ),
                'desc' => __( 'Enter the translated text of: <strong>Item added to the cart</strong>.', 'bus-booking-manager' ),
                'type' => 'text',
                'default' => 'Item has been added to the cart'
            ),
             array(
                'name' => 'wbbm_view_text',
                'label' => __( 'View', 'bus-booking-manager' ),
                'desc' => __( 'Enter the translated text of: <strong>View</strong>.', 'bus-booking-manager' ),
                'type' => 'text',
                'default' => 'View'
            ),
            array(
                'name' => 'wbbm_view_seats_text',
                'label' => __( 'View Seats', 'bus-booking-manager' ),
                'desc' => __( 'Enter the translated text of: <strong>View Seats</strong>.', 'bus-booking-manager' ),
                'type' => 'text',
                'default' => 'View Seats'
            ),

             array(
                'name' => 'wbbm_start_arrival_time_text',
                'label' => __( 'Start & Arrival Time', 'bus-booking-manager' ),
                'desc' => __( 'Enter the translated text of: <strong>Start & Arrival Time</strong>.', 'bus-booking-manager' ),
                'type' => 'text',
                'default' => 'Start & Arrival Time'
            ),

             array(
                'name' => 'wbbm_seat_no_text',
                'label' => __( 'Seat No.', 'bus-booking-manager' ),
                'desc' => __( 'Enter the translated text of: <strong>Seat No</strong>.', 'bus-booking-manager' ),
                'type' => 'text',
                'default' => 'Seat No'
            ),

             array(
                'name' => 'wbbm_remove_text',
                'label' => __( 'Remove', 'bus-booking-manager' ),
                'desc' => __( 'Enter the translated text of: <strong>Remove</strong>.', 'bus-booking-manager' ),
                'type' => 'text',
                'default' => 'Remove'
            ),
             array(
                'name' => 'wbbm_total_text',
                'label' => __( 'Total', 'bus-booking-manager' ),
                'desc' => __( 'Enter the translated text of: <strong>Total</strong>.', 'bus-booking-manager' ),
                'type' => 'text',
                'default' => 'Total'
            ),
             array(
                'name' => 'wbbm_sub_total_text',
                'label' => __( 'Sub Total', 'bus-booking-manager' ),
                'desc' => __( 'Enter the translated text of: <strong>Sub Total</strong>.', 'bus-booking-manager' ),
                'type' => 'text',
                'default' => 'Sub Total'
            ),
             array(
                'name' => 'wbbm_book_now_text',
                'label' => __( 'Book Now', 'bus-booking-manager' ),
                'desc' => __( 'Enter the translated text of: <strong>Book Now</strong>.', 'bus-booking-manager' ),
                'type' => 'text',
                'default' => 'Book Now'
            ),

             array(
                'name' => 'wbbm_bus_no_text',
                'label' => __( 'Bus No', 'bus-booking-manager' ),
                'desc' => __( 'Enter the translated text of: <strong>Bus No:</strong>.', 'bus-booking-manager' ),
                'type' => 'text',
                'default' => 'Bus No'
            ),
            array(
                'name' => 'wbbm_total_seat_text',
                'label' => __('Total Seat', 'bus-booking-manager' ),
                'desc' => __('Enter the translated text of: <strong>Total Seat</strong>.', 'bus-booking-manager' ),
                'type' => 'text',
                'default' => 'Total Seat'
            ),
            array(
                'name' => 'wbbm_boarding_points_text',
                'label' => __('Boarding Points', 'bus-booking-manager' ),
             'desc' => __('Enter the translated text of: <strong>Boarding Points</strong>.', 'bus-booking-manager' ),
                'type' => 'text',
                'default' => 'Boarding Points'
            ),  
             array(
                'name' => 'wbbm_dropping_points_text',
                'label' => __('Dropping Points', 'bus-booking-manager' ),
             'desc' => __('Enter the translated text of: <strong>Dropping Points</strong>.', 'bus-booking-manager' ),
                'type' => 'text',
                'default' => 'Dropping Points'
            ),  
            
             array(
                'name' => 'wbbm_select_journey_date_text',
                'label' => __('Select Journey Date', 'bus-booking-manager' ),
             'desc' => __('Enter the translated text of: <strong>Select Journey Date</strong>.', 'bus-booking-manager' ),
                'type' => 'text',
                'default' => 'Select Journey Date'
            ),
  
          array(
                'name' => 'wbbm_search_text',
                'label' => __('Search', 'bus-booking-manager' ),
             'desc' => __('Enter the translated text of: <strong>Search</strong>.', 'bus-booking-manager' ),
                'type' => 'text',
                'default' => 'Search'
            ),

         array(
                'name' => 'wbbm_seat_list_text',
                'label' => __('Seat List', 'bus-booking-manager' ),
             'desc' => __('Enter the translated text of: <strong>Seat List</strong>.', 'bus-booking-manager' ),
                'type' => 'text',
                'default' => 'Seat List'
            ),

        array(
                'name' => 'wbbm_total_passenger_text',
                'label' => __('Total Passenger', 'bus-booking-manager' ),
             'desc' => __('Enter the translated text of: <strong>Total Passenger</strong>.', 'bus-booking-manager' ),
                'type' => 'text',
                'default' => 'Total Passenger'
            ),
         array(
                'name' => 'wbbm_adult_text',
                'label' => __('Adult', 'bus-booking-manager' ),
             'desc' => __('Enter the translated text of: <strong>Adult</strong>.', 'bus-booking-manager' ),
                'type' => 'text',
                'default' => 'Adult'
            ),
          array(
                'name' => 'wbbm_child_text',
                'label' => __('Child', 'bus-booking-manager' ),
             'desc' => __('Enter the translated text of: <strong>Child</strong>.', 'bus-booking-manager' ),
                'type' => 'text',
                'default' => 'Child'
            ),
            array(
                'name' => 'wbbm_infant_text',
                'label' => __('Infant', 'bus-booking-manager' ),
                'desc' => __('Enter the translated text of: <strong>Infant</strong>.', 'bus-booking-manager' ),
                'type' => 'text',
                'default' => 'Infant'
            ),
            array(
                'name' => 'wbbm_pickuppoint_area_text',
                'label' => __('Select Pickup Area', 'bus-booking-manager' ),
                'desc' => __('Enter the translated text of: <strong>Select Pickup Area</strong>.', 'bus-booking-manager' ),
                'type' => 'text',
                'default' => 'Select Pickup Area'
            ),
            array(
                'name' => 'wbbm_schedule_text',
                'label' => __('Schedule', 'bus-booking-manager' ),
                'desc' => __('Enter the translated text of: <strong>Schedule</strong>.', 'bus-booking-manager' ),
                'type' => 'text',
                'default' => 'Schedule'
            ),
            array(
                'name' => 'wbbm_bus_image_text',
                'label' => __('Bus Image', 'bus-booking-manager' ),
                'desc' => __('Enter the translated text of: <strong>Bus Image</strong>.', 'bus-booking-manager' ),
                'type' => 'text',
                'default' => 'Bus Image'
            ),
            array(
                'name' => 'wbbm_seat_text',
                'label' => __('Seat', 'bus-booking-manager' ),
                'desc' => __('Enter the translated text of: <strong>Seat</strong>.', 'bus-booking-manager' ),
                'type' => 'text',
                'default' => 'Seat'
            ),
            array(
                'name' => 'wbbm_no_seat_available_text',
                'label' => __('No Seat Available', 'bus-booking-manager' ),
                'desc' => __('Enter the translated text of: <strong>No Seat Available</strong>.', 'bus-booking-manager' ),
                'type' => 'text',
                'default' => 'No Seat Available'
            ),
            array(
                'name' => 'wbbm_start_from_text',
                'label' => __('Start From', 'bus-booking-manager' ),
                'desc' => __('Enter the translated text of: <strong>Start From</strong>.', 'bus-booking-manager' ),
                'type' => 'text',
                'default' => 'Start From'
            ),
            array(
                'name' => 'wbbm_end_to_text',
                'label' => __('End To', 'bus-booking-manager' ),
                'desc' => __('Enter the translated text of: <strong>End To</strong>.', 'bus-booking-manager' ),
                'type' => 'text',
                'default' => 'End To'
            ),
            array(
                'name' => 'wbbm_name_text',
                'label' => __('Name', 'bus-booking-manager' ),
                'desc' => __('Enter the translated text of: <strong>Name</strong>.', 'bus-booking-manager' ),
                'type' => 'text',
                'default' => 'Name'
            ),
            array(
                'name' => 'wbbm_journeydate_text',
                'label' => __('Journey Date', 'bus-booking-manager' ),
                'desc' => __('Enter the translated text of: <strong>Journey Date</strong>.', 'bus-booking-manager' ),
                'type' => 'text',
                'default' => 'Journey Date'
            ),
            array(
                'name' => 'wbbm_time_text',
                'label' => __('Time', 'bus-booking-manager' ),
                'desc' => __('Enter the translated text of: <strong>Time</strong>.', 'bus-booking-manager' ),
                'type' => 'text',
                'default' => 'Time'
            ),
            array(
                'name' => 'wbbm_checkin_text',
                'label' => __('Check In', 'bus-booking-manager' ),
                'desc' => __('Enter the translated text of: <strong>Check In</strong>.', 'bus-booking-manager' ),
                'type' => 'text',
                'default' => 'Check In'
            ),
            array(
                'name' => 'wbbm_passangercheckedin_text',
                'label' => __('Passenger Checked In', 'bus-booking-manager' ),
                'desc' => __('Enter the translated text of: <strong>Passenger Checked In</strong>.', 'bus-booking-manager' ),
                'type' => 'text',
                'default' => 'Passenger Checked In'
            ),
            array(
                'name' => 'wbbm_login_to_view_ticket_text',
                'label' => __('You need to login your account to view the ticket', 'bus-booking-manager' ),
                'desc' => __('Enter the translated text of: <strong>You need to login your account to view the ticket</strong>.', 'bus-booking-manager' ),
                'type' => 'text',
                'default' => 'You need to login your account to view the ticket'
            ),
            array(
                'name' => 'wbbm_journeyinfo_text',
                'label' => __('Journey Information', 'bus-booking-manager' ),
                'desc' => __('Enter the translated text of: <strong>Journey Information</strong>.', 'bus-booking-manager' ),
                'type' => 'text',
                'default' => 'Journey Information'
            ),
            array(
                'name' => 'wbbm_issuedatetime_text',
                'label' => __('Issue Date & Time', 'bus-booking-manager' ),
                'desc' => __('Enter the translated text of: <strong>Issue Date & Time</strong>.', 'bus-booking-manager' ),
                'type' => 'text',
                'default' => 'Issue Date & Time'
            ),
            array(
                'name' => 'wbbm_jdate_dtime_text',
                'label' => __('Journey Date and Departure Time', 'bus-booking-manager' ),
                'desc' => __('Enter the translated text of: <strong>Journey Date and Departure Time</strong>.', 'bus-booking-manager' ),
                'type' => 'text',
                'default' => 'Journey Date and Departure Time'
            ),
            array(
                'name' => 'wbbm_coachname_text',
                'label' => __('Coach Name', 'bus-booking-manager' ),
                'desc' => __('Enter the translated text of: <strong>Coach Name</strong>.', 'bus-booking-manager' ),
                'type' => 'text',
                'default' => 'Coach Name'
            ),
            array(
                'name' => 'wbbm_boarding_text',
                'label' => __('Boarding', 'bus-booking-manager' ),
                'desc' => __('Enter the translated text of: <strong>Boarding</strong>.', 'bus-booking-manager' ),
                'type' => 'text',
                'default' => 'Boarding'
            ),
            array(
                'name' => 'wbbm_droping_text',
                'label' => __('Dropping', 'bus-booking-manager' ),
                'desc' => __('Enter the translated text of: <strong>Dropping</strong>.', 'bus-booking-manager' ),
                'type' => 'text',
                'default' => 'Dropping'
            ),
            array(
                'name' => 'wbbm_pickuppoint_text',
                'label' => __('Pickup Point', 'bus-booking-manager' ),
                'desc' => __('Enter the translated text of: <strong>Pickup Point</strong>.', 'bus-booking-manager' ),
                'type' => 'text',
                'default' => 'Pickup Point'
            ),
            array(
                'name' => 'wbbm_seattype_text',
                'label' => __('Seat Type', 'bus-booking-manager' ),
                'desc' => __('Enter the translated text of: <strong>Seat Type</strong>.', 'bus-booking-manager' ),
                'type' => 'text',
                'default' => 'Seat Type'
            ),
            array(
                'name' => 'wbbm_qrcode_text',
                'label' => __('QR Code', 'bus-booking-manager' ),
                'desc' => __('Enter the translated text of: <strong>QR Code</strong>.', 'bus-booking-manager' ),
                'type' => 'text',
                'default' => 'QR Code'
            ),
            array(
                'name' => 'wbbm_tpinfo_text',
                'label' => __('Ticket Printing Information', 'bus-booking-manager' ),
                'desc' => __('Enter the translated text of: <strong>Ticket Printing Information</strong>.', 'bus-booking-manager' ),
                'type' => 'text',
                'default' => 'Ticket Printing Information'
            ),
            array(
                'name' => 'wbbm_pin_text',
                'label' => __('PIN Number(s) (***)', 'bus-booking-manager' ),
                'desc' => __('Enter the translated text of: <strong>PIN Number(s) (***)</strong>.', 'bus-booking-manager' ),
                'type' => 'text',
                'default' => 'PIN Number(s) (***)'
            ),
            array(
                'name' => 'wbbm_psngrnfo_text',
                'label' => __('Passenger Information', 'bus-booking-manager' ),
                'desc' => __('Enter the translated text of: <strong>Passenger Information</strong>.', 'bus-booking-manager' ),
                'type' => 'text',
                'default' => 'Passenger Information'
            ),
            array(
                'name' => 'wbbm_address_text',
                'label' => __('Address', 'bus-booking-manager' ),
                'desc' => __('Enter the translated text of: <strong>Address</strong>.', 'bus-booking-manager' ),
                'type' => 'text',
                'default' => 'Address'
            ),
            array(
                'name' => 'wbbm_gender_text',
                'label' => __('Gender', 'bus-booking-manager' ),
                'desc' => __('Enter the translated text of: <strong>Gender</strong>.', 'bus-booking-manager' ),
                'type' => 'text',
                'default' => 'Gender'
            ),
            array(
                'name' => 'wbbm_email_text',
                'label' => __('Email', 'bus-booking-manager' ),
                'desc' => __('Enter the translated text of: <strong>Email</strong>.', 'bus-booking-manager' ),
                'type' => 'text',
                'default' => 'Email'
            ),
            array(
                'name' => 'wbbm_phone_text',
                'label' => __('Phone', 'bus-booking-manager' ),
                'desc' => __('Enter the translated text of: <strong>Phone</strong>.', 'bus-booking-manager' ),
                'type' => 'text',
                'default' => 'Phone'
            ),
            array(
                'name' => 'wbbm_nationality_text',
                'label' => __('Nationality', 'bus-booking-manager' ),
                'desc' => __('Enter the translated text of: <strong>Nationality</strong>.', 'bus-booking-manager' ),
                'type' => 'text',
                'default' => 'Nationality'
            ),
            array(
                'name' => 'wbbm_dofbirth_text',
                'label' => __('Date of Birth', 'bus-booking-manager' ),
                'desc' => __('Enter the translated text of: <strong>Date of Birth</strong>.', 'bus-booking-manager' ),
                'type' => 'text',
                'default' => 'Date of Birth'
            ),
            array(
                'name' => 'wbbm_fa_no_text',
                'label' => __('Flight Arrival No', 'bus-booking-manager' ),
                'desc' => __('Enter the translated text of: <strong>Flight Arrival No</strong>.', 'bus-booking-manager' ),
                'type' => 'text',
                'default' => 'Flight Arrival No'
            ),
            array(
                'name' => 'wbbm_fd_no_text',
                'label' => __('Flight Departure No', 'bus-booking-manager' ),
                'desc' => __('Enter the translated text of: <strong>Flight Departure No</strong>.', 'bus-booking-manager' ),
                'type' => 'text',
                'default' => 'Flight Departure No'
            ),
            array(
                'name' => 'wbbm_entire_bus_text',
                'label' => __('Entire Bus', 'bus-booking-manager' ),
                'desc' => __('Enter the translated text of: <strong>Entire Bus</strong>.', 'bus-booking-manager' ),
                'type' => 'text',
                'default' => 'Entire Bus'
            ),
            array(
                'name' => 'wbbm_extra_bag_qty_text',
                'label' => __('Extra Bag Qty', 'bus-booking-manager' ),
                'desc' => __('Enter the translated text of: <strong>Extra Bag Qty</strong>.', 'bus-booking-manager' ),
                'type' => 'text',
                'default' => 'Extra Bag Qty'
            ),
            array(
                'name' => 'wbbm_extra_bag_price_text',
                'label' => __('Extra Bag Price', 'bus-booking-manager' ),
                'desc' => __('Enter the translated text of: <strong>Extra Bag Price</strong>.', 'bus-booking-manager' ),
                'type' => 'text',
                'default' => 'Extra Bag Price'
            ),
            array(
                'name' => 'wbbm_bus_price_zero_allow_text',
                'label' => __('Bus Price Zero Allow', 'bus-booking-manager' ),
                'desc' => __('Enter the translated text of: <strong>Bus Price Zero Allow</strong>.', 'bus-booking-manager' ),
                'type' => 'text',
                'default' => 'Bus Price Zero Allow'
            ),
            array(
                'name' => 'wbbm_bus_sell_off_text',
                'label' => __('Bus Sell Off', 'bus-booking-manager' ),
                'desc' => __('Enter the translated text of: <strong>Bus Sell Off</strong>.', 'bus-booking-manager' ),
                'type' => 'text',
                'default' => 'Bus Sell Off'
            ),
            array(
                'name' => 'wbbm_bus_seat_available_show_text',
                'label' => __('Bus Seat Available Show', 'bus-booking-manager' ),
                'desc' => __('Enter the translated text of: <strong>Bus Seat Available Show</strong>.', 'bus-booking-manager' ),
                'type' => 'text',
                'default' => 'Bus Seat Available Show'
            ),
            array(
                'name' => 'wbbm_extra_services_text',
                'label' => __('Extra Services', 'bus-booking-manager' ),
                'desc' => __('Enter the translated text of: <strong>Extra Services</strong>.', 'bus-booking-manager' ),
                'type' => 'text',
                'default' => 'Extra Services'
            ),                                                                                         
        ),
        
         'wbbm_style_setting_sec' => array(
                array(
                    'name' => 'wbbm_base_color',
                    'label' => __( 'Choose Base Color', 'bus-booking-manager' ),
                    'desc' => __( 'Choose Base color that will match your website color', 'bus-booking-manager' ),
                    'type' => 'color',
                ),
                array(
                    'name' => 'wbbm_search_form_b_color',
                    'label' => __( 'Choose Search Form Background Color', 'bus-booking-manager' ),
                    'desc' => __( 'Choose Search Form Background Color', 'bus-booking-manager' ),
                    'type' => 'color',
                ),
                array(
                    'name' => 'wbbm_search_form_dropdown_b_color',
                    'label' => __( 'Choose Search Form Dropdown Background Color', 'bus-booking-manager' ),
                    'desc' => __( 'Choose Search Form Dropdown Background Color', 'bus-booking-manager' ),
                    'type' => 'color',
                ),
                array(
                    'name' => 'wbbm_search_form_dropdown_t_color',
                    'label' => __( 'Choose Search Form Dropdown Text Color', 'bus-booking-manager' ),
                    'desc' => __( 'Choose Search Form Dropdown Text Color', 'bus-booking-manager' ),
                    'type' => 'color',
                ),
                array(
                    'name' => 'wbbm_search_form_result_b_color',
                    'label' => __( 'Choose Search Form Result Background Color', 'bus-booking-manager' ),
                    'desc' => __( 'Choose Search Form Result Background Color', 'bus-booking-manager' ),
                    'type' => 'color',
                ),
                array(
                    'name' => 'wbbm_search_route_title_b_color',
                    'label' => __( 'Choose Search Route Title Background color', 'bus-booking-manager' ),
                    'desc' => __( 'Choose Search Route Title Background color', 'bus-booking-manager' ),
                    'type' => 'color',
                ),
                array(
                    'name' => 'wbbm_search_route_title_color',
                    'label' => __( 'Choose Search Route Title color', 'bus-booking-manager' ),
                    'desc' => __( 'Choose Search Route Title color', 'bus-booking-manager' ),
                    'type' => 'color',
                ),                
                array(
                    'name' => 'wbbm_search_list_header_text_color',
                    'label' => __( 'Choose Search List Header Text Color', 'bus-booking-manager' ),
                    'desc' => __( 'Choose Search List Header Text Color', 'bus-booking-manager' ),
                    'type' => 'color',
                ),
                array(
                    'name' => 'wbbm_search_list_header_b_color',
                    'label' => __( 'Choose Search List Header Background Color', 'bus-booking-manager' ),
                    'desc' => __( 'Choose Search List Header Background Color', 'bus-booking-manager' ),
                    'type' => 'color',
                ),
                array(
                    'name' => 'wbbm_search_button_bg_color',
                    'label' => __( 'Choose Button Background Color', 'bus-booking-manager' ),
                    'desc' => __( 'Choose Button Background Color', 'bus-booking-manager' ),
                    'type' => 'color',
                ),
                array(
                    'name' => 'wbbm_search_button_hover_bg_color',
                    'label' => __( 'Choose Button Hover Background Color', 'bus-booking-manager' ),
                    'desc' => __( 'Choose Button Hover Background Color', 'bus-booking-manager' ),
                    'type' => 'color',
                ),
                array(
                    'name' => 'wbbm_cart_table_bg_color',
                    'label' => __( 'Choose Cart Table Background Color', 'bus-booking-manager' ),
                    'desc' => __( 'Choose Cart Table Background Color', 'bus-booking-manager' ),
                    'type' => 'color',
                ), 
                array(
                    'name' => 'wbbm_cart_table_text_color',
                    'label' => __( 'Choose Cart Table Text Color', 'bus-booking-manager' ),
                    'desc' => __( 'Choose Cart Table Text Color', 'bus-booking-manager' ),
                    'type' => 'color',
                ),
                array(
                    'name' => 'wbbm_sub_total_bg_color',
                    'label' => __( 'Choose Sub Total Background Color', 'bus-booking-manager' ),
                    'desc' => __( 'Choose Sub Total Background Color', 'bus-booking-manager' ),
                    'type' => 'color',
                ),                                                                                                            
            ),
        );

        return apply_filters('wbbm_settings_sec_fields',$settings_fields);
    }

    function plugin_page() {
        settings_errors();
        echo '<div class="wbbm_settings_panel_header">';
        echo wbbm_get_plugin_data('Name');
        echo '<small>'.wbbm_get_plugin_data('Version').'</small>';
        echo '</div>';        
        echo '<div class="wbbm_settings_panel">';
        $this->settings_api->show_navigation();
        $this->settings_api->show_forms();
        echo '</div>';
    }

    public function wbbm_go_pro_page(){
        ?>
        <div class="wrap"></div>
        <div class="wbbm_go_pro_page_wrap">
            <div class="wbbm_go_pro_intro_sec">
                <div class="wbbm_go_pro_intro_col_1">
                    <h1><?php esc_html_e('Multipurpose Ticket Booking Manager PRO','bus-booking-manager'); ?></h1>
                    <h3><?php esc_html_e('Pro Version Plugin Features','bus-booking-manager'); ?></h3>
                    <ul>
                        <li><span class="dashicons dashicons-saved"></span><?php esc_html_e('Download PDF tickets for Passenger.','bus-booking-manager'); ?></li>
                        <li><span class="dashicons dashicons-saved"></span><?php esc_html_e('Custom Registration Form for Passenger.','bus-booking-manager'); ?></li>
                        <li><span class="dashicons dashicons-saved"></span><?php esc_html_e('Automatic Email Conformation Message and Pdf Tickets Mailing Features','bus-booking-manager'); ?></li>
                        <li><span class="dashicons dashicons-saved"></span><?php esc_html_e('Export Passenger List as CSV Format.','bus-booking-manager'); ?></li>
                        <li><span class="dashicons dashicons-saved"></span><?php esc_html_e('Backend Order Possible, Admin can book for a customer from Counter.','bus-booking-manager'); ?></li>
                    </ul>
                    <a href="<?php echo esc_url('https://mage-people.com/product/multipurpose-ticket-booking-manager-bus-train-ferry-boat-shuttle'); ?>" class="wbbm_go_pro_btn1"><?php esc_html_e('Download Now','bus-booking-manager'); ?></a>
                    <a href="<?php echo esc_url('https://vaincode.com/bus-ticket-manager'); ?>" class="wbbm_go_pro_btn2"><?php esc_html_e('View Demo','bus-booking-manager'); ?></a>
                    <a href="<?php echo esc_url('https://docs.mage-people.com/multipurpose-ticket-booking-manager/'); ?>" class="wbbm_go_pro_btn3"><?php esc_html_e('Documentation','bus-booking-manager'); ?></a>
                </div>
                <div class="wbbm_go_pro_intro_col_2">
                    <img src="<?php echo esc_url(PLUGIN_ROOT).'images/multipurpose-booking-manager.png';?>" alt="<?php esc_attr_e('Multipurpose Ticket Booking Manager PRO','bus-booking-manager'); ?>">
                </div>
            </div>
            <div class="wbbm_go_pro_review_sec">
                <h1><?php esc_html_e('What others are saying about the PRO version','bus-booking-manager'); ?></h1>
                <div class="wbbm_go_pro_review_row">
                    <div class="wbbm_go_pro_review">
                        <div class="wbbm_go_pro_review_stars">
                            <span class="dashicons dashicons-star-filled"></span>
                            <span class="dashicons dashicons-star-filled"></span>
                            <span class="dashicons dashicons-star-filled"></span>
                            <span class="dashicons dashicons-star-filled"></span>
                            <span class="dashicons dashicons-star-filled"></span>
                        </div>
                        <div class="wbbm_go_pro_review_text">
                            <p><?php esc_html_e('Excellent Plugin ever…! Its very nice plugin for Bus reservation business. Also the support team is greatly helpful and expert with client query handling. Impressive experience with free version, can\'t hold myself for getting pro plugin for additional features.!! Definitely, well deserved five star product by team.. Good work.','bus-booking-manager'); ?></p>
                        </div>
                        <div class="wbbm_go_pro_review_writer">
                            <div class="wbbm_go_pro_review_writer_name"><?php esc_html_e('sachinz1983','bus-booking-manager'); ?></div>
                            <div class="wbbm_go_pro_review_writer_designation"><?php esc_html_e('Member, WordPress.Org','bus-booking-manager'); ?></div>
                        </div>
                    </div>
                    <div class="wbbm_go_pro_review">
                        <div class="wbbm_go_pro_review_stars">
                            <span class="dashicons dashicons-star-filled"></span>
                            <span class="dashicons dashicons-star-filled"></span>
                            <span class="dashicons dashicons-star-filled"></span>
                            <span class="dashicons dashicons-star-filled"></span>
                            <span class="dashicons dashicons-star-filled"></span>
                        </div>
                        <div class="wbbm_go_pro_review_text">
                            <p><?php esc_html_e('Excellent plugin and great customer support. I\'ve used this plugin on a client\'s website and it works great straight out of the box . The Magepeople have really done their market research well and thought of almost everything while creating this plugin . I have also found them to be very patient and helpful through out our email exchanges, and there were a lot of emails as my client had very specific requirements . Overall I\'m extremely satisfied both the plugin and the team\'s response time and willingness to go out of their way to help out .','bus-booking-manager'); ?></p>
                        </div>
                        <div class="wbbm_go_pro_review_writer">
                            <div class="wbbm_go_pro_review_writer_name"><?php esc_html_e('aifali2482','bus-booking-manager'); ?></div>
                            <div class="wbbm_go_pro_review_writer_designation"><?php esc_html_e('Member, WordPress.Org','bus-booking-manager'); ?></div>
                        </div>
                    </div>
                    <div class="wbbm_go_pro_review">
                        <div class="wbbm_go_pro_review_stars">
                            <span class="dashicons dashicons-star-filled"></span>
                            <span class="dashicons dashicons-star-filled"></span>
                            <span class="dashicons dashicons-star-filled"></span>
                            <span class="dashicons dashicons-star-filled"></span>
                            <span class="dashicons dashicons-star-filled"></span>
                        </div>
                        <div class="wbbm_go_pro_review_text">
                            <p><?php esc_html_e('Really great!','bus-booking-manager'); ?></p>
                            <ol>
                                <li><?php esc_html_e('Easy installation','bus-booking-manager'); ?></li>
                                <li><?php esc_html_e('No crashes or interferences so far, TOP!','bus-booking-manager'); ?></li>
                                <li><?php esc_html_e('All functions you need to run Transporting services in regards to sales and Distribution.','bus-booking-manager'); ?></li>
                                <li><?php esc_html_e('Responsive','bus-booking-manager'); ?></li>
                            </ol>
                            <p><?php esc_html_e('Keep on going! This plug-in is great !','bus-booking-manager'); ?></p>
                        </div>
                        <div class="wbbm_go_pro_review_writer">
                            <div class="wbbm_go_pro_review_writer_name"><?php esc_html_e('jens','bus-booking-manager'); ?></div>
                            <div class="wbbm_go_pro_review_writer_designation"><?php esc_html_e('Member, WordPress.Org','bus-booking-manager'); ?></div>
                        </div>
                    </div>
                </div>
                <div class="wbbm_go_pro_review_row">
                    <a href="<?php echo esc_url('https://mage-people.com/product/multipurpose-ticket-booking-manager-bus-train-ferry-boat-shuttle'); ?>" class="wbbm_go_pro_btn1"><?php esc_html_e('Download PRO Version Now','bus-booking-manager'); ?></a>
                </div>
            </div>
        </div>
        <?php
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

    if ( isset( $options[$option] ) && $options[$option] != '' ) {
        return $options[$option];
    }    
    return $default;
}