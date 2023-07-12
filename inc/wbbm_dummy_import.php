<?php
if (!defined('ABSPATH')) {
    die;
} // Cannot access pages directly.

require_once ABSPATH . 'wp-admin/includes/media.php';
require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/image.php';

if (!class_exists('wbbm_dummy_import')) {
    class wbbm_dummy_import
    {
        public function __construct()
        {
            //update_option('wbbm_bus_data_update_01', 'completed');
            
            add_action('deactivate_plugin', array($this, 'update_option'), 98);
            add_action('activated_plugin', array($this, 'update_option'), 98);
            add_action('admin_init', array($this, 'dummy_import'), 98);

        }

        
        function update_option() 
        {
            update_option('wbbm_dummy_already_inserted', 'no');           
        }

        public function test()
        {


        }

        public static function check_plugin($plugin_dir_name, $plugin_file): int
        {
            include_once ABSPATH . 'wp-admin/includes/plugin.php';
            $plugin_dir = ABSPATH . 'wp-content/plugins/' . $plugin_dir_name;
            if (is_plugin_active($plugin_dir_name . '/' . $plugin_file)) {
                return 1;
            } elseif (is_dir($plugin_dir)) {
                return 2;
            } else {
                return 0;
            }
        }
	
		function craete_pages()
		{
				if (empty(wbbm_get_page_by_slug('events-list-style'))) {			
				$post_details = array(
					'post_title'    => 'Events – List Style',
					'post_content'  => '[event-list show="10" style="list" pagination="yes"]',
					'post_status'   => 'publish',
					'post_author'   => 1,
					'post_type' 	  => 'page'
				);		   
				wp_insert_post( $post_details );
				}

				if (empty(wbbm_get_page_by_slug('events-grid-style'))) {
				$post_details = array(
					'post_title'    => 'Events – Grid Style',
					'post_content'  => "[event-list show='10' style='grid']",
					'post_status'   => 'publish',
					'post_author'   => 1,
					'post_type' 	  => 'page'
				);
				wp_insert_post( $post_details );
				}

				if (empty(wbbm_get_page_by_slug('events-list-style-with-search-box'))) {

				$post_details = array(
					'post_title'    => 'Events – List Style with Search Box',
					'post_content'  => "[event-list column=4 search-filter='yes']",
					'post_status'   => 'publish',
					'post_author'   => 1,
					'post_type' 	  => 'page'
				);
				wp_insert_post( $post_details );	
				}	   

		}

        public function dummy_import()
        {
            
            $dummy_post_inserted = get_option('wbbm_dummy_already_inserted','no');
            $count_existing_event = wp_count_posts('wbbm_bus')->publish;
            
            $plugin_active = self::check_plugin('bus-booking-manager', 'woocommerce-bus.php');
			
            if ($count_existing_event == 0 && $plugin_active == 1 && $dummy_post_inserted != 'yes') {

                $dummy_taxonomies = $this->dummy_taxonomy();

                if(array_key_exists('taxonomy', $dummy_taxonomies))
                {
                    foreach ($dummy_taxonomies['taxonomy'] as $taxonomy => $dummy_taxonomy) 
                    {
                        
                        if (taxonomy_exists($taxonomy)) {
                            
                            $check_terms = get_terms(array('taxonomy' => $taxonomy, 'hide_empty' => false));

                            if (is_string($check_terms) || sizeof($check_terms) == 0) {
                                foreach ($dummy_taxonomy as $taxonomy_data) {
                                    unset($term);
                                    $term = wp_insert_term($taxonomy_data['name'], $taxonomy);

                                    if (array_key_exists('tax_data', $taxonomy_data)) {
                                        foreach ($taxonomy_data['tax_data'] as $meta_key => $data) {
                                            update_term_meta($term['term_id'], $meta_key, $data);
                                        }
                                    }
                                }
                            }

                        }

                    }

                    //echo "<pre>";print_r($dummy_taxonomies);exit;

                }

                $dummy_cpt = $this->dummy_cpt();

                if(array_key_exists('custom_post', $dummy_cpt))
                {
                    foreach ($dummy_cpt['custom_post'] as $custom_post => $dummy_post) 
                    {
                        unset($args);
                        $args = array(
                            'post_type' => $custom_post,
                            'posts_per_page' => -1,
                        );

                        unset($post);
                        $post = new WP_Query($args);

                        if ($post->post_count == 0) {

                            foreach ($dummy_post as $dummy_data) {
                                $title = $dummy_data['name'];
                                $content = $dummy_data['content'];
                                $post_id = wp_insert_post([
                                    'post_title' => $title,
                                    'post_content' => $content,
                                    'post_status' => 'publish',
                                    'post_type' => $custom_post,
                                ]);

                                if (array_key_exists('taxonomy_terms', $dummy_data) && count($dummy_data['taxonomy_terms'])) 
                                {
                                    foreach ($dummy_data['taxonomy_terms'] as $taxonomy_term) 
                                    {
                                        wp_set_object_terms( $post_id, $taxonomy_term['terms'], $taxonomy_term['taxonomy_name'], true );
                                    }
                                }

                                if (array_key_exists('post_data', $dummy_data)) {
                                    foreach ($dummy_data['post_data'] as $meta_key => $data) {
                                        if ($meta_key == 'feature_image') {

                                            $url = $data;
                                            $desc = "The Demo Dummy Image of the bus booking";
                                            $image = media_sideload_image($url, $post_id, $desc, 'id');
                                            set_post_thumbnail($post_id, $image);

                                        } else {

                                            update_post_meta($post_id, $meta_key, $data);

                                        }

                                    }
                                }

                            }
                        }
                    }
                }
				//$this->craete_pages();
                update_option('wbbm_dummy_already_inserted', 'yes');
            }
        }

        public function dummy_taxonomy(): array
        {
            return [
                'taxonomy' => [
                    'wbbm_bus_cat' => [
                        0 => ['name' => 'AC'],
                        1 => ['name' => 'Non AC'],
                    ],
                    'wbbm_bus_stops' => [
                        0 => [
                            'name' => 'Berlin',
                            'tax_data' => array(
                                'wbbm_bus_routes_name_list' => array(
                                    0 => array(
                                        'wbbm_bus_routes_name' => 'Frankfurt'
                                    ),
                                    1 => array(
                                        'wbbm_bus_routes_name' => 'Hamburg'
                                    ),
                                    2 => array(
                                        'wbbm_bus_routes_name' => 'Paris'
                                    ),
                                )
                            ),
                        ],
                        1 => [
                            'name' => 'Frankfurt',
                            'tax_data' => array(
                                'wbbm_bus_routes_name_list' => array(
                                    0 => array(
                                        'wbbm_bus_routes_name' => 'Berlin'
                                    ),
                                    1 => array(
                                        'wbbm_bus_routes_name' => 'Hamburg'
                                    ),
                                    2 => array(
                                        'wbbm_bus_routes_name' => 'Paris'
                                    ),
                                )
                            ),
                        ],
                        2 => [
                            'name' => 'Hamburg',
                            'tax_data' => array(
                                'wbbm_bus_routes_name_list' => array(
                                    0 => array(
                                        'wbbm_bus_routes_name' => 'Berlin'
                                    ),
                                    1 => array(
                                        'wbbm_bus_routes_name' => 'Frankfurt'
                                    ),
                                    2 => array(
                                        'wbbm_bus_routes_name' => 'Paris'
                                    ),
                                )
                            ),
                        ],
                        3 => [
                            'name' => 'Paris',
                            'tax_data' => array(
                                'wbbm_bus_routes_name_list' => array(
                                    0 => array(
                                        'wbbm_bus_routes_name' => 'Berlin'
                                    ),
                                    1 => array(
                                        'wbbm_bus_routes_name' => 'Frankfurt'
                                    ),
                                    2 => array(
                                        'wbbm_bus_routes_name' => 'Hamburg'
                                    ),
                                )
                            ),
                        ],                        
                    ],
                    'wbbm_bus_pickpoint' => [
                        0 => ['name' => 'Berlin'],
                        1 => ['name' => 'Frankfurt'],
                        2 => ['name' => 'Hamburg'],
                        3 => ['name' => 'Paris'],
                    ],
                    'wbbm_bus_feature' => [
                        0 => ['name' => 'Mobile Charger'],
                        1 => ['name' => 'WI-FI'],
                        2 => ['name' => 'Welcome Drink'],
                    ],

                ],
            ];
        }

        public function dummy_cpt(): array
        {
            return [
                'custom_post' => [
                    'wbbm_bus' => [
                        0 => [
                            'name' => 'Flix Bus Service',
                            'content' => '

                            Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.
                            
                            Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae ab illo inventore veritatis et quasi architecto beatae vitae dicta sunt explicabo. Nemo enim ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit, sed quia consequuntur magni dolores eos qui ratione voluptatem sequi nesciunt. Neque porro quisquam est, qui dolorem ipsum quia dolor sit amet, consectetur, adipisci velit, sed quia non numquam eius modi tempora incidunt ut labore et dolore magnam aliquam quaerat voluptatem. Ut enim ad minima veniam, quis nostrum exercitationem ullam corporis suscipit laboriosam, nisi ut aliquid ex ea commodi consequatur? Quis autem vel eum iure reprehenderit qui in ea voluptate velit esse quam nihil molestiae consequatur, vel illum qui dolorem eum fugiat quo voluptas nulla pariatur.
                            ',
                            'taxonomy_terms' => [
                                0 => array(
                                    'taxonomy_name' => 'wbbm_bus_cat',
                                    'terms' => array(
                                        0 => 'Non AC',
                                    )
                                ),
                                1 => array(
                                    'taxonomy_name' => 'wbbm_bus_stops',
                                    'terms' => array(
                                        0 => 'Berlin',
                                        1 => 'Frankfurt',
                                        2 => 'Hamburg',
                                        3 => 'Paris',
                                    )
                                ),
                                2 => array(
                                    'taxonomy_name' => 'wbbm_bus_pickpoint',
                                    'terms' => array(
                                        0=>'Berlin',
                                        1=>'Frankfurt',
                                    )
                                ),
                                3 => array(
                                    'taxonomy_name' => 'wbbm_bus_feature',
                                    'terms' => array(
                                        0=>'WI-FI',
                                        1=>'Mobile Charger',
                                    )
                                ),                                
                            ],
                            'post_data' => [
                                //configuration
                                'feature_image' => 'https://img.freepik.com/free-photo/runner-training-marathon_23-2149284923.jpg',
                                
                                'wbbm_bus_category' => get_term_by('name', 'Non AC', 'wbbm_bus_cat') ? get_term_by('name', 'Non AC', 'wbbm_bus_cat')->term_id : '',
                                'wbbm_bus_no' => 'Flixbus-01',
                                'wbbm_total_seat' => '27',
                                'wbbm_price_zero_allow' => 'off',
                                'wbbm_sell_off' => 'off',
                                'wbbm_seat_available' => 'on',

                                //Routing
                                'wbbm_bus_bp_stops' => array(
                                    0 => array(
                                        'wbbm_bus_bp_stops_name' => 'Paris',
                                        'wbbm_bus_bp_start_time' => '12:00',
                                    ),
                                    1 => array(
                                        'wbbm_bus_bp_stops_name' => 'Frankfurt',
                                        'wbbm_bus_bp_start_time' => '12:20',
                                    ),
                                    2 => array(
                                        'wbbm_bus_bp_stops_name' => 'Hamburg',
                                        'wbbm_bus_bp_start_time' => '12:30',
                                    ),
                                ),
                                'wbbm_bus_next_stops' => array(
                                    0 => array(
                                        'wbbm_bus_next_stops_name' => 'Frankfurt',
                                        'wbbm_bus_next_end_time' => '16:10',
                                    ),
                                    1 => array(
                                        'wbbm_bus_next_stops_name' => 'Hamburg',
                                        'wbbm_bus_next_end_time' => '19:10',
                                    ),
                                    2 => array(
                                        'wbbm_bus_next_stops_name' => 'Berlin',
                                        'wbbm_bus_next_end_time' => '22:30',
                                    ),
                                ),
                                // Seat Price
                                'wbbm_bus_prices' => array(
                                    0 => array(
                                        'wbbm_bus_bp_price_stop' => 'Paris',
                                        'wbbm_bus_dp_price_stop' => 'Frankfurt',
                                        'wbbm_bus_price' => '10',
                                        'wbbm_bus_price_roundtrip' => '',
                                        'wbbm_bus_price_child' => '0',
                                        'wbbm_bus_price_infant' => '0',
                                        'wbbm_bus_price_infant_roundtrip' => '',
                                        'wbbm_bus_price_entire' => '',
                                        'wbbm_bus_price_entire_roundtrip' => '',
                                    ),
                                    1 => array(
                                        'wbbm_bus_bp_price_stop' => 'Paris',
                                        'wbbm_bus_dp_price_stop' => 'Hamburg',
                                        'wbbm_bus_price' => '15',
                                        'wbbm_bus_price_roundtrip' => '',
                                        'wbbm_bus_price_child' => '0',
                                        'wbbm_bus_price_infant' => '0',
                                        'wbbm_bus_price_infant_roundtrip' => '',
                                        'wbbm_bus_price_entire' => '',
                                        'wbbm_bus_price_entire_roundtrip' => '',
                                    ),
                                    2 => array(
                                        'wbbm_bus_bp_price_stop' => 'Paris',
                                        'wbbm_bus_dp_price_stop' => 'Berlin',
                                        'wbbm_bus_price' => '25',
                                        'wbbm_bus_price_roundtrip' => '',
                                        'wbbm_bus_price_child' => '0',
                                        'wbbm_bus_price_infant' => '0',
                                        'wbbm_bus_price_infant_roundtrip' => '',
                                        'wbbm_bus_price_entire' => '',
                                        'wbbm_bus_price_entire_roundtrip' => '',
                                    ),
                                    3 => array(
                                        'wbbm_bus_bp_price_stop' => 'Frankfurt',
                                        'wbbm_bus_dp_price_stop' => 'Hamburg',
                                        'wbbm_bus_price' => '5',
                                        'wbbm_bus_price_roundtrip' => '',
                                        'wbbm_bus_price_child' => '0',
                                        'wbbm_bus_price_infant' => '0',
                                        'wbbm_bus_price_infant_roundtrip' => '',
                                        'wbbm_bus_price_entire' => '',
                                        'wbbm_bus_price_entire_roundtrip' => '',
                                    ),
                                    4 => array(
                                        'wbbm_bus_bp_price_stop' => 'Frankfurt',
                                        'wbbm_bus_dp_price_stop' => 'Berlin',
                                        'wbbm_bus_price' => '15',
                                        'wbbm_bus_price_roundtrip' => '',
                                        'wbbm_bus_price_child' => '0',
                                        'wbbm_bus_price_infant' => '0',
                                        'wbbm_bus_price_infant_roundtrip' => '',
                                        'wbbm_bus_price_entire' => '',
                                        'wbbm_bus_price_entire_roundtrip' => '',
                                    ),
                                    5 => array(
                                        'wbbm_bus_bp_price_stop' => 'Hamburg',
                                        'wbbm_bus_dp_price_stop' => 'Berlin',
                                        'wbbm_bus_price' => '10',
                                        'wbbm_bus_price_roundtrip' => '',
                                        'wbbm_bus_price_child' => '0',
                                        'wbbm_bus_price_infant' => '0',
                                        'wbbm_bus_price_infant_roundtrip' => '',
                                        'wbbm_bus_price_entire' => '',
                                        'wbbm_bus_price_entire_roundtrip' => '',
                                    ),
                                ),
                                // Pickup Points
                                'show_pickup_point' => 'no',
                                // Onday & Offday
                                'wbtm_offday_schedule' => array(),
                                'weekly_offday' => '',
                                'wbtm_od_start' => '',
                                'wbtm_od_end' => '',
                                'wbtm_bus_on_date' => '',
                                'show_boarding_points' => '',
                                'show_extra_service' => 'no',                                
                                'show_operational_on_day' => 'no',
                                'show_off_day' => 'no',
                                //Bus Feature
                                'wbbm_features' => array(
                                    0 => get_term_by('name', 'Mobile Charger', 'wbbm_bus_feature') ? get_term_by('name', 'Mobile Charger', 'wbbm_bus_feature')->term_id : '',
                                    1 => get_term_by('name', 'WI-FI', 'wbbm_bus_feature') ? get_term_by('name', 'WI-FI', 'wbbm_bus_feature')->term_id : '',                                    
                                ),
                            ],
                        ],
                        1 => [
                            'name' => 'Mega Bus Express',
                            'content' => '

                            Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.
                            
                            Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae ab illo inventore veritatis et quasi architecto beatae vitae dicta sunt explicabo. Nemo enim ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit, sed quia consequuntur magni dolores eos qui ratione voluptatem sequi nesciunt. Neque porro quisquam est, qui dolorem ipsum quia dolor sit amet, consectetur, adipisci velit, sed quia non numquam eius modi tempora incidunt ut labore et dolore magnam aliquam quaerat voluptatem. Ut enim ad minima veniam, quis nostrum exercitationem ullam corporis suscipit laboriosam, nisi ut aliquid ex ea commodi consequatur? Quis autem vel eum iure reprehenderit qui in ea voluptate velit esse quam nihil molestiae consequatur, vel illum qui dolorem eum fugiat quo voluptas nulla pariatur.
                            ',
                            'taxonomy_terms' => [
                                0 => array(
                                    'taxonomy_name' => 'wbbm_bus_cat',
                                    'terms' => array(
                                        0 => 'AC',
                                    )
                                ),
                                1 => array(
                                    'taxonomy_name' => 'wbbm_bus_stops',
                                    'terms' => array(
                                        0 => 'Berlin',
                                        1 => 'Frankfurt',
                                        2 => 'Hamburg',
                                        3 => 'Paris',
                                    )
                                ),
                                2 => array(
                                    'taxonomy_name' => 'wbbm_bus_pickpoint',
                                    'terms' => array(
                                        0=>'Berlin',
                                        1=>'Frankfurt',
                                    )
                                ),
                                3 => array(
                                    'taxonomy_name' => 'wbbm_bus_feature',
                                    'terms' => array(
                                        0=>'WI-FI',
                                        1=>'Mobile Charger',
                                    )
                                ),                                
                            ],
                            'post_data' => [
                                //configuration
                                'feature_image' => 'https://img.freepik.com/free-photo/runner-training-marathon_23-2149284923.jpg',
                                
                                'wbbm_bus_category' => get_term_by('name', 'AC', 'wbbm_bus_cat') ? get_term_by('name', 'Non AC', 'wbbm_bus_cat')->term_id : '',
                                'wbbm_bus_no' => 'Megabus-01',
                                'wbbm_total_seat' => '27',
                                'wbbm_price_zero_allow' => 'off',
                                'wbbm_sell_off' => 'off',
                                'wbbm_seat_available' => 'on',

                                //Routing
                                'wbbm_bus_bp_stops' => array(
                                    0 => array(
                                        'wbbm_bus_bp_stops_name' => 'Paris',
                                        'wbbm_bus_bp_start_time' => '11:00',
                                    ),
                                    1 => array(
                                        'wbbm_bus_bp_stops_name' => 'Frankfurt',
                                        'wbbm_bus_bp_start_time' => '12:20',
                                    ),
                                    2 => array(
                                        'wbbm_bus_bp_stops_name' => 'Hamburg',
                                        'wbbm_bus_bp_start_time' => '12:30',
                                    ),
                                ),
                                'wbbm_bus_next_stops' => array(
                                    0 => array(
                                        'wbbm_bus_next_stops_name' => 'Berlin',
                                        'wbbm_bus_next_end_time' => '21:30',
                                    ),
                                ),
                                // Seat Price
                                'wbbm_bus_prices' => array(
                                    0 => array(
                                        'wbbm_bus_bp_price_stop' => 'Paris',
                                        'wbbm_bus_dp_price_stop' => 'Frankfurt',
                                        'wbbm_bus_price' => '10',
                                        'wbbm_bus_price_roundtrip' => '',
                                        'wbbm_bus_price_child' => '0',
                                        'wbbm_bus_price_infant' => '0',
                                        'wbbm_bus_price_infant_roundtrip' => '',
                                        'wbbm_bus_price_entire' => '',
                                        'wbbm_bus_price_entire_roundtrip' => '',
                                    ),
                                    1 => array(
                                        'wbbm_bus_bp_price_stop' => 'Paris',
                                        'wbbm_bus_dp_price_stop' => 'Hamburg',
                                        'wbbm_bus_price' => '15',
                                        'wbbm_bus_price_roundtrip' => '',
                                        'wbbm_bus_price_child' => '0',
                                        'wbbm_bus_price_infant' => '0',
                                        'wbbm_bus_price_infant_roundtrip' => '',
                                        'wbbm_bus_price_entire' => '',
                                        'wbbm_bus_price_entire_roundtrip' => '',
                                    ),
                                    2 => array(
                                        'wbbm_bus_bp_price_stop' => 'Paris',
                                        'wbbm_bus_dp_price_stop' => 'Berlin',
                                        'wbbm_bus_price' => '25',
                                        'wbbm_bus_price_roundtrip' => '',
                                        'wbbm_bus_price_child' => '0',
                                        'wbbm_bus_price_infant' => '0',
                                        'wbbm_bus_price_infant_roundtrip' => '',
                                        'wbbm_bus_price_entire' => '',
                                        'wbbm_bus_price_entire_roundtrip' => '',
                                    ),
                                    3 => array(
                                        'wbbm_bus_bp_price_stop' => 'Frankfurt',
                                        'wbbm_bus_dp_price_stop' => 'Hamburg',
                                        'wbbm_bus_price' => '5',
                                        'wbbm_bus_price_roundtrip' => '',
                                        'wbbm_bus_price_child' => '0',
                                        'wbbm_bus_price_infant' => '0',
                                        'wbbm_bus_price_infant_roundtrip' => '',
                                        'wbbm_bus_price_entire' => '',
                                        'wbbm_bus_price_entire_roundtrip' => '',
                                    ),
                                    4 => array(
                                        'wbbm_bus_bp_price_stop' => 'Frankfurt',
                                        'wbbm_bus_dp_price_stop' => 'Berlin',
                                        'wbbm_bus_price' => '15',
                                        'wbbm_bus_price_roundtrip' => '',
                                        'wbbm_bus_price_child' => '0',
                                        'wbbm_bus_price_infant' => '0',
                                        'wbbm_bus_price_infant_roundtrip' => '',
                                        'wbbm_bus_price_entire' => '',
                                        'wbbm_bus_price_entire_roundtrip' => '',
                                    ),
                                    5 => array(
                                        'wbbm_bus_bp_price_stop' => 'Hamburg',
                                        'wbbm_bus_dp_price_stop' => 'Berlin',
                                        'wbbm_bus_price' => '10',
                                        'wbbm_bus_price_roundtrip' => '',
                                        'wbbm_bus_price_child' => '0',
                                        'wbbm_bus_price_infant' => '0',
                                        'wbbm_bus_price_infant_roundtrip' => '',
                                        'wbbm_bus_price_entire' => '',
                                        'wbbm_bus_price_entire_roundtrip' => '',
                                    ),
                                ),
                                // Pickup Points
                                'show_pickup_point' => 'no',
                                // Onday & Offday
                                'wbtm_offday_schedule' => array(),
                                'weekly_offday' => '',
                                'wbtm_od_start' => '',
                                'wbtm_od_end' => '',
                                'wbtm_bus_on_date' => '',
                                'show_boarding_points' => '',
                                'show_extra_service' => 'no',                                
                                'show_operational_on_day' => 'no',
                                'show_off_day' => 'no',
                                //Bus Feature
                                'wbbm_features' => array(
                                    0 => get_term_by('name', 'Mobile Charger', 'wbbm_bus_feature') ? get_term_by('name', 'Mobile Charger', 'wbbm_bus_feature')->term_id : '',
                                    1 => get_term_by('name', 'WI-FI', 'wbbm_bus_feature') ? get_term_by('name', 'WI-FI', 'wbbm_bus_feature')->term_id : '',                                    
                                ),
                            ],
                        ],
                        2 => [
                            'name' => 'BYD Express',
                            'content' => '

                            Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.
                            
                            Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae ab illo inventore veritatis et quasi architecto beatae vitae dicta sunt explicabo. Nemo enim ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit, sed quia consequuntur magni dolores eos qui ratione voluptatem sequi nesciunt. Neque porro quisquam est, qui dolorem ipsum quia dolor sit amet, consectetur, adipisci velit, sed quia non numquam eius modi tempora incidunt ut labore et dolore magnam aliquam quaerat voluptatem. Ut enim ad minima veniam, quis nostrum exercitationem ullam corporis suscipit laboriosam, nisi ut aliquid ex ea commodi consequatur? Quis autem vel eum iure reprehenderit qui in ea voluptate velit esse quam nihil molestiae consequatur, vel illum qui dolorem eum fugiat quo voluptas nulla pariatur.
                            ',
                            'taxonomy_terms' => [
                                0 => array(
                                    'taxonomy_name' => 'wbbm_bus_cat',
                                    'terms' => array(
                                        0 => 'Non AC',
                                    )
                                ),
                                1 => array(
                                    'taxonomy_name' => 'wbbm_bus_stops',
                                    'terms' => array(
                                        0 => 'Berlin',
                                        1 => 'Frankfurt',
                                        2 => 'Hamburg',
                                        3 => 'Paris',
                                    )
                                ),
                                2 => array(
                                    'taxonomy_name' => 'wbbm_bus_pickpoint',
                                    'terms' => array(
                                        0=>'Berlin',
                                        1=>'Frankfurt',
                                    )
                                ),
                                3 => array(
                                    'taxonomy_name' => 'wbbm_bus_feature',
                                    'terms' => array(
                                        0=>'WI-FI',
                                        1=>'Mobile Charger',
                                    )
                                ),                                
                            ],
                            'post_data' => [
                                //configuration
                                'feature_image' => 'https://img.freepik.com/free-photo/runner-training-marathon_23-2149284923.jpg',
                                
                                'wbbm_bus_category' => get_term_by('name', 'Non AC', 'wbbm_bus_cat') ? get_term_by('name', 'Non AC', 'wbbm_bus_cat')->term_id : '',
                                'wbbm_bus_no' => 'BYDbus-01',
                                'wbbm_total_seat' => '27',
                                'wbbm_price_zero_allow' => 'off',
                                'wbbm_sell_off' => 'off',
                                'wbbm_seat_available' => 'on',

                                //Routing
                                'wbbm_bus_bp_stops' => array(
                                    0 => array(
                                        'wbbm_bus_bp_stops_name' => 'Berlin',
                                        'wbbm_bus_bp_start_time' => '10:00',
                                    ),
                                    1 => array(
                                        'wbbm_bus_bp_stops_name' => 'Hamburg',
                                        'wbbm_bus_bp_start_time' => '10:15',
                                    ),
                                    2 => array(
                                        'wbbm_bus_bp_stops_name' => 'Frankfurt',
                                        'wbbm_bus_bp_start_time' => '10:30',
                                    ),
                                ),
                                'wbbm_bus_next_stops' => array(
                                    0 => array(
                                        'wbbm_bus_next_stops_name' => 'Hamburg',
                                        'wbbm_bus_next_end_time' => '10:10',
                                    ),
                                    1 => array(
                                        'wbbm_bus_next_stops_name' => 'Frankfurt',
                                        'wbbm_bus_next_end_time' => '11:10',
                                    ),
                                    2 => array(
                                        'wbbm_bus_next_stops_name' => 'Paris',
                                        'wbbm_bus_next_end_time' => '21:10',
                                    ),
                                ),
                                // Seat Price
                                'wbbm_bus_prices' => array(
                                    0 => array(
                                        'wbbm_bus_bp_price_stop' => 'Berlin',
                                        'wbbm_bus_dp_price_stop' => 'Hamburg',
                                        'wbbm_bus_price' => '10',
                                        'wbbm_bus_price_roundtrip' => '',
                                        'wbbm_bus_price_child' => '0',
                                        'wbbm_bus_price_infant' => '0',
                                        'wbbm_bus_price_infant_roundtrip' => '',
                                        'wbbm_bus_price_entire' => '',
                                        'wbbm_bus_price_entire_roundtrip' => '',
                                    ),
                                    1 => array(
                                        'wbbm_bus_bp_price_stop' => 'Berlin',
                                        'wbbm_bus_dp_price_stop' => 'Frankfurt',
                                        'wbbm_bus_price' => '15',
                                        'wbbm_bus_price_roundtrip' => '',
                                        'wbbm_bus_price_child' => '0',
                                        'wbbm_bus_price_infant' => '0',
                                        'wbbm_bus_price_infant_roundtrip' => '',
                                        'wbbm_bus_price_entire' => '',
                                        'wbbm_bus_price_entire_roundtrip' => '',
                                    ),
                                    2 => array(
                                        'wbbm_bus_bp_price_stop' => 'Berlin',
                                        'wbbm_bus_dp_price_stop' => 'Paris',
                                        'wbbm_bus_price' => '25',
                                        'wbbm_bus_price_roundtrip' => '',
                                        'wbbm_bus_price_child' => '0',
                                        'wbbm_bus_price_infant' => '0',
                                        'wbbm_bus_price_infant_roundtrip' => '',
                                        'wbbm_bus_price_entire' => '',
                                        'wbbm_bus_price_entire_roundtrip' => '',
                                    ),
                                    3 => array(
                                        'wbbm_bus_bp_price_stop' => 'Hamburg',
                                        'wbbm_bus_dp_price_stop' => 'Frankfurt',
                                        'wbbm_bus_price' => '5',
                                        'wbbm_bus_price_roundtrip' => '',
                                        'wbbm_bus_price_child' => '0',
                                        'wbbm_bus_price_infant' => '0',
                                        'wbbm_bus_price_infant_roundtrip' => '',
                                        'wbbm_bus_price_entire' => '',
                                        'wbbm_bus_price_entire_roundtrip' => '',
                                    ),
                                    4 => array(
                                        'wbbm_bus_bp_price_stop' => 'Hamburg',
                                        'wbbm_bus_dp_price_stop' => 'Paris',
                                        'wbbm_bus_price' => '15',
                                        'wbbm_bus_price_roundtrip' => '',
                                        'wbbm_bus_price_child' => '0',
                                        'wbbm_bus_price_infant' => '0',
                                        'wbbm_bus_price_infant_roundtrip' => '',
                                        'wbbm_bus_price_entire' => '',
                                        'wbbm_bus_price_entire_roundtrip' => '',
                                    ),
                                    5 => array(
                                        'wbbm_bus_bp_price_stop' => 'Frankfurt',
                                        'wbbm_bus_dp_price_stop' => 'Paris',
                                        'wbbm_bus_price' => '10',
                                        'wbbm_bus_price_roundtrip' => '',
                                        'wbbm_bus_price_child' => '0',
                                        'wbbm_bus_price_infant' => '0',
                                        'wbbm_bus_price_infant_roundtrip' => '',
                                        'wbbm_bus_price_entire' => '',
                                        'wbbm_bus_price_entire_roundtrip' => '',
                                    ),
                                    6 => array(
                                        'wbbm_bus_bp_price_stop' => 'Frankfurt',
                                        'wbbm_bus_dp_price_stop' => 'Hambarg',
                                        'wbbm_bus_price' => '15',
                                        'wbbm_bus_price_roundtrip' => '',
                                        'wbbm_bus_price_child' => '0',
                                        'wbbm_bus_price_infant' => '0',
                                        'wbbm_bus_price_infant_roundtrip' => '',
                                        'wbbm_bus_price_entire' => '',
                                        'wbbm_bus_price_entire_roundtrip' => '',
                                    ),
                                ),
                                // Pickup Points
                                'show_pickup_point' => 'no',
                                // Onday & Offday
                                'wbtm_offday_schedule' => array(),
                                'weekly_offday' => '',
                                'wbtm_od_start' => '',
                                'wbtm_od_end' => '',
                                'wbtm_bus_on_date' => '',
                                'show_boarding_points' => '',
                                'show_extra_service' => 'no',                                
                                'show_operational_on_day' => 'no',
                                'show_off_day' => 'no',
                                //Bus Feature
                                'wbbm_features' => array(
                                    0 => get_term_by('name', 'Mobile Charger', 'wbbm_bus_feature') ? get_term_by('name', 'Mobile Charger', 'wbbm_bus_feature')->term_id : '',
                                    1 => get_term_by('name', 'WI-FI', 'wbbm_bus_feature') ? get_term_by('name', 'WI-FI', 'wbbm_bus_feature')->term_id : '',                                    
                                ),
                            ],
                        ],
                        3 => [
                            'name' => 'RED Coach',
                            'content' => '

                            Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.
                            
                            Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae ab illo inventore veritatis et quasi architecto beatae vitae dicta sunt explicabo. Nemo enim ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit, sed quia consequuntur magni dolores eos qui ratione voluptatem sequi nesciunt. Neque porro quisquam est, qui dolorem ipsum quia dolor sit amet, consectetur, adipisci velit, sed quia non numquam eius modi tempora incidunt ut labore et dolore magnam aliquam quaerat voluptatem. Ut enim ad minima veniam, quis nostrum exercitationem ullam corporis suscipit laboriosam, nisi ut aliquid ex ea commodi consequatur? Quis autem vel eum iure reprehenderit qui in ea voluptate velit esse quam nihil molestiae consequatur, vel illum qui dolorem eum fugiat quo voluptas nulla pariatur.
                            ',
                            'taxonomy_terms' => [
                                0 => array(
                                    'taxonomy_name' => 'wbbm_bus_cat',
                                    'terms' => array(
                                        0 => 'AC',
                                    )
                                ),
                                1 => array(
                                    'taxonomy_name' => 'wbbm_bus_stops',
                                    'terms' => array(
                                        0 => 'Berlin',
                                        1 => 'Frankfurt',
                                        2 => 'Hamburg',
                                        3 => 'Paris',
                                    )
                                ),
                                2 => array(
                                    'taxonomy_name' => 'wbbm_bus_pickpoint',
                                    'terms' => array(
                                        0=>'Berlin',
                                        1=>'Frankfurt',
                                    )
                                ),
                                3 => array(
                                    'taxonomy_name' => 'wbbm_bus_feature',
                                    'terms' => array(
                                        0=>'WI-FI',
                                        1=>'Mobile Charger',
                                    )
                                ),                                
                            ],
                            'post_data' => [
                                //configuration
                                'feature_image' => 'https://img.freepik.com/free-photo/runner-training-marathon_23-2149284923.jpg',
                                
                                'wbbm_bus_category' => get_term_by('name', 'AC', 'wbbm_bus_cat') ? get_term_by('name', 'Non AC', 'wbbm_bus_cat')->term_id : '',
                                'wbbm_bus_no' => 'RED-01',
                                'wbbm_total_seat' => '27',
                                'wbbm_price_zero_allow' => 'off',
                                'wbbm_sell_off' => 'off',
                                'wbbm_seat_available' => 'on',

                                //Routing
                                'wbbm_bus_bp_stops' => array(
                                    0 => array(
                                        'wbbm_bus_bp_stops_name' => 'Berlin',
                                        'wbbm_bus_bp_start_time' => '10:00',
                                    ),
                                    1 => array(
                                        'wbbm_bus_bp_stops_name' => 'Hamburg',
                                        'wbbm_bus_bp_start_time' => '10:15',
                                    ),
                                    2 => array(
                                        'wbbm_bus_bp_stops_name' => 'Frankfurt',
                                        'wbbm_bus_bp_start_time' => '10:30',
                                    ),
                                ),
                                'wbbm_bus_next_stops' => array(
                                    0 => array(
                                        'wbbm_bus_next_stops_name' => 'Hamburg',
                                        'wbbm_bus_next_end_time' => '12:10',
                                    ),
                                    1 => array(
                                        'wbbm_bus_next_stops_name' => 'Frankfurt',
                                        'wbbm_bus_next_end_time' => '01:10',
                                    ),
                                    2 => array(
                                        'wbbm_bus_next_stops_name' => 'Paris',
                                        'wbbm_bus_next_end_time' => '22:10',
                                    ),
                                ),
                                // Seat Price
                                'wbbm_bus_prices' => array(
                                    0 => array(
                                        'wbbm_bus_bp_price_stop' => 'Berlin',
                                        'wbbm_bus_dp_price_stop' => 'Hamburg',
                                        'wbbm_bus_price' => '10',
                                        'wbbm_bus_price_roundtrip' => '',
                                        'wbbm_bus_price_child' => '0',
                                        'wbbm_bus_price_infant' => '0',
                                        'wbbm_bus_price_infant_roundtrip' => '',
                                        'wbbm_bus_price_entire' => '',
                                        'wbbm_bus_price_entire_roundtrip' => '',
                                    ),
                                    1 => array(
                                        'wbbm_bus_bp_price_stop' => 'Berlin',
                                        'wbbm_bus_dp_price_stop' => 'Frankfurt',
                                        'wbbm_bus_price' => '15',
                                        'wbbm_bus_price_roundtrip' => '',
                                        'wbbm_bus_price_child' => '0',
                                        'wbbm_bus_price_infant' => '0',
                                        'wbbm_bus_price_infant_roundtrip' => '',
                                        'wbbm_bus_price_entire' => '',
                                        'wbbm_bus_price_entire_roundtrip' => '',
                                    ),
                                    2 => array(
                                        'wbbm_bus_bp_price_stop' => 'Berlin',
                                        'wbbm_bus_dp_price_stop' => 'Paris',
                                        'wbbm_bus_price' => '25',
                                        'wbbm_bus_price_roundtrip' => '',
                                        'wbbm_bus_price_child' => '0',
                                        'wbbm_bus_price_infant' => '0',
                                        'wbbm_bus_price_infant_roundtrip' => '',
                                        'wbbm_bus_price_entire' => '',
                                        'wbbm_bus_price_entire_roundtrip' => '',
                                    ),
                                    3 => array(
                                        'wbbm_bus_bp_price_stop' => 'Hamburg',
                                        'wbbm_bus_dp_price_stop' => 'Frankfurt',
                                        'wbbm_bus_price' => '5',
                                        'wbbm_bus_price_roundtrip' => '',
                                        'wbbm_bus_price_child' => '0',
                                        'wbbm_bus_price_infant' => '0',
                                        'wbbm_bus_price_infant_roundtrip' => '',
                                        'wbbm_bus_price_entire' => '',
                                        'wbbm_bus_price_entire_roundtrip' => '',
                                    ),
                                    4 => array(
                                        'wbbm_bus_bp_price_stop' => 'Hamburg',
                                        'wbbm_bus_dp_price_stop' => 'Paris',
                                        'wbbm_bus_price' => '15',
                                        'wbbm_bus_price_roundtrip' => '',
                                        'wbbm_bus_price_child' => '0',
                                        'wbbm_bus_price_infant' => '0',
                                        'wbbm_bus_price_infant_roundtrip' => '',
                                        'wbbm_bus_price_entire' => '',
                                        'wbbm_bus_price_entire_roundtrip' => '',
                                    ),
                                    5 => array(
                                        'wbbm_bus_bp_price_stop' => 'Frankfurt',
                                        'wbbm_bus_dp_price_stop' => 'Paris',
                                        'wbbm_bus_price' => '10',
                                        'wbbm_bus_price_roundtrip' => '',
                                        'wbbm_bus_price_child' => '0',
                                        'wbbm_bus_price_infant' => '0',
                                        'wbbm_bus_price_infant_roundtrip' => '',
                                        'wbbm_bus_price_entire' => '',
                                        'wbbm_bus_price_entire_roundtrip' => '',
                                    ),
                                    6 => array(
                                        'wbbm_bus_bp_price_stop' => 'Frankfurt',
                                        'wbbm_bus_dp_price_stop' => 'Hambarg',
                                        'wbbm_bus_price' => '15',
                                        'wbbm_bus_price_roundtrip' => '',
                                        'wbbm_bus_price_child' => '0',
                                        'wbbm_bus_price_infant' => '0',
                                        'wbbm_bus_price_infant_roundtrip' => '',
                                        'wbbm_bus_price_entire' => '',
                                        'wbbm_bus_price_entire_roundtrip' => '',
                                    ),
                                ),
                                // Pickup Points
                                'show_pickup_point' => 'no',
                                // Onday & Offday
                                'wbtm_offday_schedule' => array(),
                                'weekly_offday' => '',
                                'wbtm_od_start' => '',
                                'wbtm_od_end' => '',
                                'wbtm_bus_on_date' => '',
                                'show_boarding_points' => '',
                                'show_extra_service' => 'no',                                
                                'show_operational_on_day' => 'no',
                                'show_off_day' => 'no',
                                //Bus Feature
                                'wbbm_features' => array(
                                    0 => get_term_by('name', 'Mobile Charger', 'wbbm_bus_feature') ? get_term_by('name', 'Mobile Charger', 'wbbm_bus_feature')->term_id : '',
                                    1 => get_term_by('name', 'WI-FI', 'wbbm_bus_feature') ? get_term_by('name', 'WI-FI', 'wbbm_bus_feature')->term_id : '',                                    
                                ),
                            ],
                        ],
                        4 => [
                            'name' => 'Bonanza BUS',
                            'content' => '

                            Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.
                            
                            Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae ab illo inventore veritatis et quasi architecto beatae vitae dicta sunt explicabo. Nemo enim ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit, sed quia consequuntur magni dolores eos qui ratione voluptatem sequi nesciunt. Neque porro quisquam est, qui dolorem ipsum quia dolor sit amet, consectetur, adipisci velit, sed quia non numquam eius modi tempora incidunt ut labore et dolore magnam aliquam quaerat voluptatem. Ut enim ad minima veniam, quis nostrum exercitationem ullam corporis suscipit laboriosam, nisi ut aliquid ex ea commodi consequatur? Quis autem vel eum iure reprehenderit qui in ea voluptate velit esse quam nihil molestiae consequatur, vel illum qui dolorem eum fugiat quo voluptas nulla pariatur.
                            ',
                            'taxonomy_terms' => [
                                0 => array(
                                    'taxonomy_name' => 'wbbm_bus_cat',
                                    'terms' => array(
                                        0 => 'Non AC',
                                    )
                                ),
                                1 => array(
                                    'taxonomy_name' => 'wbbm_bus_stops',
                                    'terms' => array(
                                        0 => 'Berlin',
                                        1 => 'Frankfurt',
                                        2 => 'Hamburg',
                                        3 => 'Paris',
                                    )
                                ),
                                2 => array(
                                    'taxonomy_name' => 'wbbm_bus_pickpoint',
                                    'terms' => array(
                                        0=>'Berlin',
                                        1=>'Frankfurt',
                                    )
                                ),
                                3 => array(
                                    'taxonomy_name' => 'wbbm_bus_feature',
                                    'terms' => array(
                                        0=>'WI-FI',
                                        1=>'Mobile Charger',
                                    )
                                ),                                
                            ],
                            'post_data' => [
                                //configuration
                                'feature_image' => 'https://img.freepik.com/free-photo/runner-training-marathon_23-2149284923.jpg',
                                
                                'wbbm_bus_category' => get_term_by('name', 'Non AC', 'wbbm_bus_cat') ? get_term_by('name', 'Non AC', 'wbbm_bus_cat')->term_id : '',
                                'wbbm_bus_no' => 'Bonanza-01',
                                'wbbm_total_seat' => '27',
                                'wbbm_price_zero_allow' => 'off',
                                'wbbm_sell_off' => 'off',
                                'wbbm_seat_available' => 'on',

                                //Routing
                                'wbbm_bus_bp_stops' => array(
                                    0 => array(
                                        'wbbm_bus_bp_stops_name' => 'Berlin',
                                        'wbbm_bus_bp_start_time' => '07:00',
                                    ),
                                    1 => array(
                                        'wbbm_bus_bp_stops_name' => 'Hamburg',
                                        'wbbm_bus_bp_start_time' => '08:00',
                                    ),
                                    2 => array(
                                        'wbbm_bus_bp_stops_name' => 'Frankfurt',
                                        'wbbm_bus_bp_start_time' => '08:30',
                                    ),
                                ),
                                'wbbm_bus_next_stops' => array(
                                    0 => array(
                                        'wbbm_bus_next_stops_name' => 'Hamburg',
                                        'wbbm_bus_next_end_time' => '10:10',
                                    ),
                                    1 => array(
                                        'wbbm_bus_next_stops_name' => 'Frankfurt',
                                        'wbbm_bus_next_end_time' => '17:10',
                                    ),
                                    2 => array(
                                        'wbbm_bus_next_stops_name' => 'Paris',
                                        'wbbm_bus_next_end_time' => '22:00',
                                    ),
                                ),
                                // Seat Price
                                'wbbm_bus_prices' => array(
                                    0 => array(
                                        'wbbm_bus_bp_price_stop' => 'Berlin',
                                        'wbbm_bus_dp_price_stop' => 'Hamburg',
                                        'wbbm_bus_price' => '10',
                                        'wbbm_bus_price_roundtrip' => '',
                                        'wbbm_bus_price_child' => '0',
                                        'wbbm_bus_price_infant' => '0',
                                        'wbbm_bus_price_infant_roundtrip' => '',
                                        'wbbm_bus_price_entire' => '',
                                        'wbbm_bus_price_entire_roundtrip' => '',
                                    ),
                                    1 => array(
                                        'wbbm_bus_bp_price_stop' => 'Berlin',
                                        'wbbm_bus_dp_price_stop' => 'Frankfurt',
                                        'wbbm_bus_price' => '15',
                                        'wbbm_bus_price_roundtrip' => '',
                                        'wbbm_bus_price_child' => '0',
                                        'wbbm_bus_price_infant' => '0',
                                        'wbbm_bus_price_infant_roundtrip' => '',
                                        'wbbm_bus_price_entire' => '',
                                        'wbbm_bus_price_entire_roundtrip' => '',
                                    ),
                                    2 => array(
                                        'wbbm_bus_bp_price_stop' => 'Berlin',
                                        'wbbm_bus_dp_price_stop' => 'Paris',
                                        'wbbm_bus_price' => '25',
                                        'wbbm_bus_price_roundtrip' => '',
                                        'wbbm_bus_price_child' => '0',
                                        'wbbm_bus_price_infant' => '0',
                                        'wbbm_bus_price_infant_roundtrip' => '',
                                        'wbbm_bus_price_entire' => '',
                                        'wbbm_bus_price_entire_roundtrip' => '',
                                    ),
                                    3 => array(
                                        'wbbm_bus_bp_price_stop' => 'Hamburg',
                                        'wbbm_bus_dp_price_stop' => 'Frankfurt',
                                        'wbbm_bus_price' => '5',
                                        'wbbm_bus_price_roundtrip' => '',
                                        'wbbm_bus_price_child' => '0',
                                        'wbbm_bus_price_infant' => '0',
                                        'wbbm_bus_price_infant_roundtrip' => '',
                                        'wbbm_bus_price_entire' => '',
                                        'wbbm_bus_price_entire_roundtrip' => '',
                                    ),
                                    4 => array(
                                        'wbbm_bus_bp_price_stop' => 'Hamburg',
                                        'wbbm_bus_dp_price_stop' => 'Paris',
                                        'wbbm_bus_price' => '15',
                                        'wbbm_bus_price_roundtrip' => '',
                                        'wbbm_bus_price_child' => '0',
                                        'wbbm_bus_price_infant' => '0',
                                        'wbbm_bus_price_infant_roundtrip' => '',
                                        'wbbm_bus_price_entire' => '',
                                        'wbbm_bus_price_entire_roundtrip' => '',
                                    ),
                                    5 => array(
                                        'wbbm_bus_bp_price_stop' => 'Frankfurt',
                                        'wbbm_bus_dp_price_stop' => 'Paris',
                                        'wbbm_bus_price' => '10',
                                        'wbbm_bus_price_roundtrip' => '',
                                        'wbbm_bus_price_child' => '0',
                                        'wbbm_bus_price_infant' => '0',
                                        'wbbm_bus_price_infant_roundtrip' => '',
                                        'wbbm_bus_price_entire' => '',
                                        'wbbm_bus_price_entire_roundtrip' => '',
                                    ),
                                    6 => array(
                                        'wbbm_bus_bp_price_stop' => 'Frankfurt',
                                        'wbbm_bus_dp_price_stop' => 'Hambarg',
                                        'wbbm_bus_price' => '15',
                                        'wbbm_bus_price_roundtrip' => '',
                                        'wbbm_bus_price_child' => '0',
                                        'wbbm_bus_price_infant' => '0',
                                        'wbbm_bus_price_infant_roundtrip' => '',
                                        'wbbm_bus_price_entire' => '',
                                        'wbbm_bus_price_entire_roundtrip' => '',
                                    ),
                                ),
                                // Pickup Points
                                'show_pickup_point' => 'no',
                                // Onday & Offday
                                'wbtm_offday_schedule' => array(),
                                'weekly_offday' => '',
                                'wbtm_od_start' => '',
                                'wbtm_od_end' => '',
                                'wbtm_bus_on_date' => '',
                                'show_boarding_points' => '',
                                'show_extra_service' => 'no',                                
                                'show_operational_on_day' => 'no',
                                'show_off_day' => 'no',
                                //Bus Feature
                                'wbbm_features' => array(
                                    0 => get_term_by('name', 'Mobile Charger', 'wbbm_bus_feature') ? get_term_by('name', 'Mobile Charger', 'wbbm_bus_feature')->term_id : '',
                                    1 => get_term_by('name', 'WI-FI', 'wbbm_bus_feature') ? get_term_by('name', 'WI-FI', 'wbbm_bus_feature')->term_id : '',                                    
                                ),
                            ],
                        ],
                        
                    ],
                ],
            ];

        }
    }

    new wbbm_dummy_import();
}