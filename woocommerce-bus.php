<?php

/**
 * Plugin Name: Multipurpose Ticket Booking Manager (Bus/Train/Ferry/Boat/Shuttle)
 * Plugin URI: http://mage-people.com
 * Description: A Complete Ticket Booking System for WordPress & WooCommerce
 * Version: 4.0.9
 * Author: MagePeople Team
 * Author URI: http://www.mage-people.com/
 * Text Domain: bus-booking-manager
 * Domain Path: /languages/
 */

if (!defined('ABSPATH')) {
    die;
} // Cannot access pages directly.

function appsero_init_tracker_bus_booking_manager()
{
    if (!class_exists('Appsero\Client')) {
        require_once __DIR__ . '/lib/appsero/src/Client.php';
    }
    $client = new Appsero\Client('60610129-a874-4728-9b5c-feb8e44cc280', 'Multipurpose Ticket Booking Manager (Bus/Train/Ferry/Boat/Shuttle)', __FILE__);
    // Active insights
    $client->insights()->init();
}

appsero_init_tracker_bus_booking_manager();


// function to create passenger list table        
function wbbm_booking_list_table_create()
{
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();
    $table_name = $wpdb->prefix . 'wbbm_bus_booking_list';
    $sql = "CREATE TABLE $table_name (
    booking_id int(15) NOT NULL AUTO_INCREMENT,
    order_id int(9) NOT NULL,  
    bus_id int(9) NOT NULL, 
    user_id int(9) NOT NULL, 
    boarding_point varchar(55) NOT NULL, 
    next_stops text NOT NULL,     
    droping_point varchar(55) NOT NULL, 
    user_name varchar(55) NOT NULL, 
    user_email varchar(55) NOT NULL, 
    user_phone varchar(55) NOT NULL, 
    user_gender varchar(55) NOT NULL, 
    user_address text NOT NULL, 
    user_type varchar(55) NOT NULL, 
    bus_start varchar(55) NOT NULL, 
    user_start varchar(55) NOT NULL,
    total_adult int(9) NOT NULL,  
    per_adult_price int(9) NOT NULL, 
    total_child int(9) NOT NULL, 
    per_child_price int(9) NOT NULL, 
    total_price int(9) NOT NULL,     
    seat varchar(55) NOT NULL,
    journey_date date DEFAULT '0000-00-00' NOT NULL,    
    booking_date datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,    
    status int(1) NOT NULL,  
    PRIMARY KEY  (booking_id)
  ) $charset_collate;";
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);

    require_once plugin_dir_path(__FILE__) . 'inc/class-plugin-activator.php';
    WBTM_Plugin_Activator::activate();


}

// run the install scripts upon plugin activation
register_activation_hook(__FILE__, 'wbbm_booking_list_table_create');

define('WBTM_PLUGIN_URL', plugin_dir_url(__FILE__));
define('WBTM_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WBTM_PLUGIN_FILE', plugin_basename(__FILE__));

include_once(ABSPATH . 'wp-admin/includes/plugin.php');

if (is_plugin_active('woocommerce/woocommerce.php')) {

    define('PLUGIN_ROOT', plugin_dir_url(__FILE__));
    require_once(dirname(__FILE__) . "/inc/class-mage-settings.php");
    require_once(dirname(__FILE__) . "/inc/wbbm_admin_settings.php");
    require_once(dirname(__FILE__) . "/inc/wbbm_cpt.php");
    require_once(dirname(__FILE__) . "/lib/classes/class-form-fields-generator.php");
    require_once(dirname(__FILE__) . "/lib/classes/class-form-fields-wrapper.php");
    require_once(dirname(__FILE__) . "/lib/classes/class-meta-box.php");
    require_once(dirname(__FILE__) . "/lib/classes/class-taxonomy-edit.php");
    require_once(dirname(__FILE__) . "/inc/wbbm_tax.php");
    //require_once(dirname(__FILE__) . "/inc/wbbm_bus_ticket_meta.php");
    require_once(dirname(__FILE__) . "/inc/wbbm_extra_price.php");
    require_once(dirname(__FILE__) . "/inc/class-remove-bus-info-to-cart.php");
    require_once(dirname(__FILE__) . "/inc/wbbm_shortcode.php");
    require_once(dirname(__FILE__) . "/inc/wbbm_enque.php");
    require_once(dirname(__FILE__) . "/inc/wbbm_upgrade.php");
    require_once(dirname(__FILE__) . "/inc/wbbm_license.php");
    //added by sumon
    require_once(dirname(__FILE__) . "/inc/clean/mage_short_code.php");
    require_once(dirname(__FILE__) . "/inc/clean/mage_function.php");
    //--------------
    require_once(dirname(__FILE__) . "/inc/class-meta-box.php");
    require_once(dirname(__FILE__) . "/inc/BusBookingManagerClass.php");
    
   // Language Load
    add_action('init', 'wbbm_language_load');
    function wbbm_language_load()
    {
        $plugin_dir = basename(dirname(__FILE__)) . "/languages/";
        load_plugin_textdomain('bus-booking-manager', false, $plugin_dir);
    }

    flush_rewrite_rules();
    require_once WBTM_PLUGIN_DIR . '/inc/WBTM_Quick_Setup.php';
    add_action('activated_plugin', 'activation_redirect', 90, 1);
    /**
     * Run code only once
     */
    function wbbm_update_databas_once()
    {
        global $wpdb;
        if (get_option('wbbm_update_db_once_06') != 'completed') {
            $table = $wpdb->prefix . "wbbm_bus_booking_list";
            $column_name_user_type = 'user_type';
            $column_user_type = $wpdb->get_results($wpdb->prepare(
                "SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = %s AND TABLE_NAME = %s AND COLUMN_NAME = %s ",
                DB_NAME,
                $table,
                $column_name_user_type
            ));
            if (empty($column_user_type)) {
                $wpdb->query(sprintf("ALTER TABLE %s  
                ADD COLUMN user_type varchar(55) NOT NULL AFTER user_address,  
                ADD COLUMN total_adult int(9) NOT NULL AFTER user_start,  
                ADD COLUMN per_adult_price int(9) NOT NULL AFTER total_adult, 
                ADD COLUMN total_child int(9) NOT NULL AFTER per_adult_price, 
                ADD COLUMN per_child_price int(9) NOT NULL AFTER total_child, 
                ADD COLUMN total_price int(9) NOT NULL AFTER per_child_price", $table));
            }
            update_option('wbbm_update_db_once_06', 'completed');
        }
        if (get_option('wbbm_update_db_once_07') != 'completed') {
            $table = $wpdb->prefix . "wbbm_bus_booking_list";
            $column_name_next_stops = 'next_stops';
            $column_next_stops = $wpdb->get_results($wpdb->prepare(
                "SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = %s AND TABLE_NAME = %s AND COLUMN_NAME = %s ",
                DB_NAME,
                $table,
                $column_name_next_stops
            ));
            if (empty($column_next_stops)) {
                $wpdb->query(sprintf("ALTER TABLE %s ADD next_stops text NOT NULL AFTER boarding_point", $table));
            }
            update_option('wbbm_update_db_once_07', 'completed');
        }


        //  Add Infant column
        $column_name = 'total_infant';
        $column_name_two = 'per_infant_price';
        $table = $wpdb->prefix . "wbbm_bus_booking_list";
        $column = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = %s AND TABLE_NAME = %s AND COLUMN_NAME = %s ",
            DB_NAME,
            $table,
            $column_name
        ));

        $column_two = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = %s AND TABLE_NAME = %s AND COLUMN_NAME = %s ",
            DB_NAME,
            $table,
            $column_name_two
        ));


        if (empty($column) && empty($column_two)) {
            $wpdb->query("ALTER TABLE " . $table . " ADD total_infant INT(9) NULL AFTER per_child_price, ADD per_infant_price INT(9) NULL AFTER per_child_price");
        }
        //  Add Infant column END

        //  Add entire column
        $column_name = 'total_entire';
        $column_name_two = 'per_entire_price';
        $table = $wpdb->prefix . "wbbm_bus_booking_list";
        $column = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = %s AND TABLE_NAME = %s AND COLUMN_NAME = %s ",
            DB_NAME,
            $table,
            $column_name
        ));

        $column_two = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = %s AND TABLE_NAME = %s AND COLUMN_NAME = %s ",
            DB_NAME,
            $table,
            $column_name_two
        ));


        if (empty($column) && empty($column_two)) {
            $wpdb->query("ALTER TABLE " . $table . " ADD total_entire INT(9) NULL AFTER total_infant, ADD per_entire_price INT(9) NULL AFTER total_infant");
        }
        //  Add entire column END

        // Add Dob, Nationality, Flight arrival no, Fligh departure no
        $c_dob = 'dob';
        $c_nationality = 'nationality';
        $c_flight_arrial_no = 'flight_arrial_no';
        $c_flight_departure_no = 'flight_departure_no';

        $table = $wpdb->prefix . "wbbm_bus_booking_list";

        $cc_dob = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = %s AND TABLE_NAME = %s AND COLUMN_NAME = %s ",
            DB_NAME,
            $table,
            $c_dob
        ));

        $cc_nationality = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = %s AND TABLE_NAME = %s AND COLUMN_NAME = %s ",
            DB_NAME,
            $table,
            $c_nationality
        ));

        $cc_flight_arrial_no = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = %s AND TABLE_NAME = %s AND COLUMN_NAME = %s ",
            DB_NAME,
            $table,
            $c_flight_arrial_no
        ));

        $cc_flight_departure_no = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = %s AND TABLE_NAME = %s AND COLUMN_NAME = %s ",
            DB_NAME,
            $table,
            $c_flight_departure_no
        ));
        $pickpoint_column = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = %s AND TABLE_NAME = %s AND COLUMN_NAME = %s ",
            DB_NAME,
            $table,
            'pickpoint'
        ));
        if (empty($pickpoint_column)) {
            $wpdb->query(sprintf("ALTER TABLE %s ADD pickpoint VARCHAR (255) NOT NULL AFTER booking_date", $table));
        }
        if (empty($cc_dob) && empty($cc_nationality) && empty($cc_flight_arrial_no) && empty($cc_flight_departure_no)) {
            $wpdb->query("ALTER TABLE " . $table . " ADD user_dob varchar(55) NULL AFTER pickpoint, ADD nationality varchar(255) NULL AFTER pickpoint, ADD flight_arrial_no varchar(255) NULL AFTER pickpoint, ADD flight_departure_no varchar(255) NULL AFTER pickpoint");
        }
        // Add Dob, Nationality, Flight arrival no, Fligh departure no END


        // Alter Columns datatype [ per_adult_price, total_adult, per_child_price, total_child, per_infant_price, total_infant, total_price ] on version 
        // 1
        $t_column1 = $wpdb->get_results($wpdb->prepare(
            "SELECT DATA_TYPE FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = %s AND TABLE_NAME = %s AND COLUMN_NAME = %s ",
            DB_NAME,
            $table,
            'per_adult_price'
        ));

        if (!empty($t_column1) && $t_column1[0]->DATA_TYPE == 'int') {
            $wpdb->query("ALTER TABLE " . $table . " MODIFY COLUMN per_adult_price FLOAT(9,2)");
        }

        // 2
        $t_column2 = $wpdb->get_results($wpdb->prepare(
            "SELECT DATA_TYPE FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = %s AND TABLE_NAME = %s AND COLUMN_NAME = %s ",
            DB_NAME,
            $table,
            'total_adult'
        ));

        if (!empty($t_column2) && $t_column2[0]->DATA_TYPE == 'int') {
            $wpdb->query("ALTER TABLE " . $table . " MODIFY COLUMN total_adult FLOAT(9,2)");
        }

        // 2
        $t_column3 = $wpdb->get_results($wpdb->prepare(
            "SELECT DATA_TYPE FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = %s AND TABLE_NAME = %s AND COLUMN_NAME = %s ",
            DB_NAME,
            $table,
            'per_child_price'
        ));

        if (!empty($t_column3) && $t_column3[0]->DATA_TYPE == 'int') {
            $wpdb->query("ALTER TABLE " . $table . " MODIFY COLUMN per_child_price FLOAT(9,2)");
        }

        // 3
        $t_column4 = $wpdb->get_results($wpdb->prepare(
            "SELECT DATA_TYPE FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = %s AND TABLE_NAME = %s AND COLUMN_NAME = %s ",
            DB_NAME,
            $table,
            'total_child'
        ));

        if (!empty($t_column4) && $t_column4[0]->DATA_TYPE == 'int') {
            $wpdb->query("ALTER TABLE " . $table . " MODIFY COLUMN total_child FLOAT(9,2)");
        }

        // 4
        $t_column4 = $wpdb->get_results($wpdb->prepare(
            "SELECT DATA_TYPE FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = %s AND TABLE_NAME = %s AND COLUMN_NAME = %s ",
            DB_NAME,
            $table,
            'total_infant'
        ));

        if (!empty($t_column4) && $t_column4[0]->DATA_TYPE == 'int') {
            $wpdb->query("ALTER TABLE " . $table . " MODIFY COLUMN total_infant FLOAT(9,2)");
        }

        // 5
        $t_column5 = $wpdb->get_results($wpdb->prepare(
            "SELECT DATA_TYPE FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = %s AND TABLE_NAME = %s AND COLUMN_NAME = %s ",
            DB_NAME,
            $table,
            'per_infant_price'
        ));

        if (!empty($t_column5) && $t_column5[0]->DATA_TYPE == 'int') {
            $wpdb->query("ALTER TABLE " . $table . " MODIFY COLUMN per_infant_price FLOAT(9,2)");
        }

        // 7
        /*
        $t_column7 = $wpdb->get_results($wpdb->prepare(
            "SELECT DATA_TYPE FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = %s AND TABLE_NAME = %s AND COLUMN_NAME = %s ",
            DB_NAME, $table, 'total_entire'
        ));

        if (!empty($t_column7) && $t_column7[0]->DATA_TYPE == 'int') {
            $wpdb->query("ALTER TABLE " . $table . " MODIFY COLUMN total_entire FLOAT(9,2)");
        }
        */

        // 8
        $t_column8 = $wpdb->get_results($wpdb->prepare(
            "SELECT DATA_TYPE FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = %s AND TABLE_NAME = %s AND COLUMN_NAME = %s ",
            DB_NAME,
            $table,
            'per_entire_price'
        ));

        if (!empty($t_column8) && $t_column8[0]->DATA_TYPE == 'int') {
            $wpdb->query("ALTER TABLE " . $table . " MODIFY COLUMN per_entire_price FLOAT(9,2)");
        }

        // 6
        $t_column6 = $wpdb->get_results($wpdb->prepare(
            "SELECT DATA_TYPE FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = %s AND TABLE_NAME = %s AND COLUMN_NAME = %s ",
            DB_NAME,
            $table,
            'total_price'
        ));

        if (!empty($t_column6) && $t_column6[0]->DATA_TYPE == 'int') {
            $wpdb->query("ALTER TABLE " . $table . " MODIFY COLUMN total_price FLOAT(9,2)");
        }

        // Boarding, Dropping and Pick point data type length
        // 1
        $length_column1 = $wpdb->get_results($wpdb->prepare(
            "SELECT CHARACTER_MAXIMUM_LENGTH FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = %s AND TABLE_NAME = %s AND COLUMN_NAME = %s ",
            DB_NAME,
            $table,
            'boarding_point'
        ));

        if (!empty($length_column1) && $length_column1[0]->CHARACTER_MAXIMUM_LENGTH < 253) {
            $wpdb->query("ALTER TABLE " . $table . " MODIFY COLUMN boarding_point VARCHAR(255)");
        }

        // 2
        $length_column2 = $wpdb->get_results($wpdb->prepare(
            "SELECT CHARACTER_MAXIMUM_LENGTH FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = %s AND TABLE_NAME = %s AND COLUMN_NAME = %s ",
            DB_NAME,
            $table,
            'droping_point'
        ));

        if (!empty($length_column2) && $length_column2[0]->CHARACTER_MAXIMUM_LENGTH < 253) {
            $wpdb->query("ALTER TABLE " . $table . " MODIFY COLUMN droping_point VARCHAR(255)");
        }

        // 3
        $length_column3 = $wpdb->get_results($wpdb->prepare(
            "SELECT CHARACTER_MAXIMUM_LENGTH FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = %s AND TABLE_NAME = %s AND COLUMN_NAME = %s ",
            DB_NAME,
            $table,
            'pickpoint'
        ));

        if (!empty($length_column3) && $length_column3[0]->CHARACTER_MAXIMUM_LENGTH < 253) {
            $wpdb->query("ALTER TABLE " . $table . " MODIFY COLUMN pickpoint VARCHAR(255)");
        }
        // Boarding, Dropping and Pick point data type length END

        // Add 'ticket_status' column
        $column_name = 'ticket_status';
        $column = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = %s AND TABLE_NAME = %s AND COLUMN_NAME = %s ",
            DB_NAME,
            $table,
            $column_name
        ));

        if (empty($column)) {
            $wpdb->query(sprintf("ALTER TABLE %s ADD ticket_status INT NOT NULL DEFAULT 0  AFTER next_stops", $table));
        }
        // Add 'ticket_status' column End


        // wbbm_price_zero_allow
        if (get_option('mbbm_wbbm_price_zero_allow_update_01') != 'completed') {

            $args = array(
                'post_type' => 'wbbm_bus',
                'posts_per_page' => -1
            );

            $qr = new WP_Query($args);
            foreach ($qr->posts as $result) {
                $post_id = $result->ID;
                $ea_checkin = get_post_meta($post_id, 'wbbm_price_zero_allow', true) ? get_post_meta($post_id, 'wbbm_price_zero_allow', true) : 'off';
                if ($ea_checkin == 'off') {
                    update_post_meta($post_id, 'wbbm_price_zero_allow', 'off');
                }
            }
            update_option('mbbm_wbbm_price_zero_allow_update_01', 'completed');
        }
        // wbbm_price_zero_allow END

    }

    add_action('admin_init', 'wbbm_update_databas_once');


    // Function to get page slug
    function wbbm_get_page_by_slug($slug)
    {
        if ($pages = get_pages())
            foreach ($pages as $page)
                if ($slug === $page->post_name) return $page;
        return false;
    }

    // Cretae pages on plugin activation
    function wbbm_page_create()
    {
        if (!wbbm_get_page_by_slug('bus-search')) {
            $bus_search_page = array(
                'post_type' => 'page',
                'post_name' => 'bus-search',
                'post_title' => 'Bus Search',
                'post_content' => '[bus-search]',
                'post_status' => 'publish',
            );
            wp_insert_post($bus_search_page);
        }
        if (!wbbm_get_page_by_slug('view-ticket')) {
            $view_ticket_page = array(
                'post_type' => 'page',
                'post_name' => 'view-ticket',
                'post_title' => 'View Ticket',
                'post_content' => '[view-ticket]',
                'post_status' => 'publish',
            );
            wp_insert_post($view_ticket_page);
        }
    }

    register_activation_hook(__FILE__, 'wbbm_page_create');

    // Class for Linking with Woocommerce with Bus Pricing 
    add_action('plugins_loaded', 'wbbm_load_wc_class');
    function wbbm_load_wc_class()
    {
        if (class_exists('WC_Product_Data_Store_CPT')) {
            class WBBM_Product_Data_Store_CPT extends WC_Product_Data_Store_CPT
            {
                public function read(&$product)
                {

                    $product->set_defaults();

                    if (!$product->get_id() || !($post_object = get_post($product->get_id())) || !in_array($post_object->post_type, array('wbbm_bus', 'product'))) { // change birds with your post type
                        throw new Exception(__('Invalid product.', 'woocommerce'));
                    }

                    $id = $product->get_id();

                    $product->set_props(array(
                        'name' => $post_object->post_title,
                        'slug' => $post_object->post_name,
                        'date_created' => 0 < $post_object->post_date_gmt ? wc_string_to_timestamp($post_object->post_date_gmt) : null,
                        'date_modified' => 0 < $post_object->post_modified_gmt ? wc_string_to_timestamp($post_object->post_modified_gmt) : null,
                        'status' => $post_object->post_status,
                        'description' => $post_object->post_content,
                        'short_description' => $post_object->post_excerpt,
                        'parent_id' => $post_object->post_parent,
                        'menu_order' => $post_object->menu_order,
                        'reviews_allowed' => 'open' === $post_object->comment_status,
                    ));
                    $this->read_attributes($product);
                    $this->read_downloads($product);
                    $this->read_visibility($product);
                    $this->read_product_data($product);
                    $this->read_extra_data($product);
                    $product->set_object_read(true);
                }

                /**
                 * Get the product type based on product ID.
                 *
                 * @param int $product_id
                 * @return bool|string
                 * @since 3.0.0
                 */
                public function get_product_type($product_id)
                {
                    $post_type = get_post_type($product_id);
                    if ('product_variation' === $post_type) {
                        return 'variation';
                    } elseif (in_array($post_type, array('wbbm_bus', 'product'))) { // change birds with your post type
                        $terms = get_the_terms($product_id, 'product_type');
                        return !empty($terms) ? sanitize_title(current($terms)->name) : 'simple';
                    } else {
                        return false;
                    }
                }
            }


            add_filter('woocommerce_data_stores', 'wbbm_woocommerce_data_stores');
            function wbbm_woocommerce_data_stores($stores)
            {
                $stores['product'] = 'WBBM_Product_Data_Store_CPT';
                return $stores;
            }
        } else {

            add_action('admin_notices', 'wc_not_loaded');
        }
    }


    add_action('woocommerce_before_checkout_form', 'wbbm_displays_cart_products_feature_image');
    function wbbm_displays_cart_products_feature_image()
    {
        foreach (WC()->cart->get_cart() as $cart_item) {
            $item = $cart_item['data'];
        }
    }


    add_action('restrict_manage_posts', 'wbbm_filter_post_type_by_taxonomy');
    function wbbm_filter_post_type_by_taxonomy()
    {
        global $typenow;
        $post_type = 'wbbm_bus'; // change to your post type
        $taxonomy = 'wbbm_bus_cat'; // change to your taxonomy
        if ($typenow == $post_type) {
            $selected = isset($_GET[$taxonomy]) ? $_GET[$taxonomy] : '';
            $info_taxonomy = get_taxonomy($taxonomy);
            wp_dropdown_categories(array(
                'show_option_all' => __("Show All {$info_taxonomy->label}"),
                'taxonomy' => $taxonomy,
                'name' => $taxonomy,
                'orderby' => 'name',
                'selected' => $selected,
                'show_count' => true,
                'hide_empty' => true,
            ));
        };
    }


    add_filter('parse_query', 'wbbm_convert_id_to_term_in_query');
    function wbbm_convert_id_to_term_in_query($query)
    {
        global $pagenow;
        $post_type = 'wbbm_bus'; // change to your post type
        $taxonomy = 'wbbm_bus_cat'; // change to your taxonomy
        $q_vars = &$query->query_vars;

        if ($pagenow == 'edit.php' && isset($q_vars['post_type']) && $q_vars['post_type'] == $post_type && isset($q_vars[$taxonomy]) && is_numeric($q_vars[$taxonomy]) && $q_vars[$taxonomy] != 0) {
            $term = get_term_by('id', $q_vars[$taxonomy], $taxonomy);
            $q_vars[$taxonomy] = $term->slug;
        }
    }





    add_filter('template_include', 'wbbm_taxonomy_set_template');
    function wbbm_taxonomy_set_template($template)
    {

        if (is_tax('wbbm_bus_cat')) {
            $template = plugin_dir_path(__FILE__) . 'templates/taxonomy-category.php';
        }

        return $template;
    }


    function wbbm_get_bus_ticket_order_metadata($id, $part)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'woocommerce_order_itemmeta';
        $result = $wpdb->get_results("SELECT * FROM $table_name WHERE order_item_id=$id");

        foreach ($result as $page) {
            if (strpos($page->meta_key, '_') !== 0) {
                echo wbbm_get_string_part($page->meta_key, $part) . '<br/>';
            }
        }
    }


    function wbbm_get_seat_type($name)
    {
        global $post;
        $values = get_post_custom($post->ID);
        $seat_name = $name;
        if (array_key_exists($seat_name, $values)) {
            $type_name = $values[$seat_name][0];
        } else {
            $type_name = '';
        }

        $get_terms_default_attributes = array(
            'taxonomy' => 'wbbm_seat_type', //empty string(''), false, 0 don't work, and return 
            'hide_empty' => false, //can be 1, '1' too
        );
        $terms = get_terms($get_terms_default_attributes);
        if (!empty($terms) && !is_wp_error($terms)) {
            ob_start();
?>
            <select name="<?php echo $name; ?>" class='seat_type select2'>
                <?php
                foreach ($terms as $term) {
                ?>
                    <option value="<?php echo $term->name; ?>" <?php if ($type_name == $term->name) {
                                                                    echo "Selected";
                                                                } ?>><?php echo $term->name; ?></option>
                <?php
                }
                ?>
            </select>
        <?php

        }
        $content = ob_get_clean();
        return $content;
    }


    function wbbm_get_bus_route_list($name, $value = '')
    {
        global $post;
        $values = array();

        if ($post) {
            $values = get_post_custom($post->ID);
        }

        if ($values) {
            $values = $values;
        }


        if (array_key_exists($name, $values)) {
            $seat_name = $name;
            $type_name = $values[$seat_name][0];
        } else {
            $type_name = '';
        }
        $terms = get_terms(array(
            // 'taxonomy' => 'wbbm_bus_route',
            'taxonomy' => 'wbbm_bus_stops',
            'hide_empty' => false,
        ));

        if (!empty($terms) && !is_wp_error($terms)) : ob_start(); ?>

            <select required name="<?php echo $name; ?>" class='seat_type select2'>

                <option value=""><?php _e('Please Select', 'bus-booking-manager'); ?></option>

                <?php foreach ($terms as $term) :
                    $wbbm_bs_show = get_term_meta($term->term_id, 'wbbm_bs_show', true);
                    if ($wbbm_bs_show) {
                        $show = $wbbm_bs_show;
                    } else {
                        $show = 'yes';
                    }
                    if ($show == 'yes') {
                        $selected = $type_name == $term->name ? 'selected' : '';

                        if (!empty($value)) $selected = $term->name == $value ? 'selected' : '';
                        printf('<option %s value="%s">%s</option>', $selected, $term->name, $term->name);
                    }
                endforeach; ?>

            </select>

        <?php endif;

        return ob_get_clean();
    }


    function wbbm_get_bus_stops_list($name, $class = null)
    {
        global $post;
        $values = get_post_custom($post->ID);
        $seat_name = $name;
        if (array_key_exists($seat_name, $values)) {
            $type_name = $values[$seat_name][0];
        } else {
            $type_name = '';
        }

        $get_terms_default_attributes = array(
            'taxonomy' => 'wbbm_bus_stops', //empty string(''), false, 0 don't work, and return 
            'hide_empty' => false, //can be 1, '1' too
        );
        $terms = get_terms($get_terms_default_attributes);

        ?>
            <select name="<?php echo $name; ?>" class='seat_type select2 <?php echo $class; ?>'>
                <option value=""><?php _e('Please Select', 'bus-booking-manager'); ?></option>
                <?php
        if (!empty($terms) && !is_wp_error($terms)) {
            ob_start();
            foreach ($terms as $term) {
                ?>
                    <option data-term_id="<?php echo $term->name; ?>" value="<?php echo $term->name; ?>" <?php echo ($type_name == $term->name)?'Selected':''  ?>><?php echo $term->name; ?></option>
                <?php
                }
        }
                ?>
            </select>
        <?php


        $content = ob_get_clean();
        return $content;
    }


    function wbbm_get_next_bus_stops_list($name, $data, $list, $coun)
    {
        global $post;

        $nxt_arr = get_post_meta($post->ID, $list, true);
        $seat_name = $name;
        $type_name = isset($nxt_arr[$coun]) ? $nxt_arr[$coun][$data] : null;

        $get_terms_default_attributes = array(
            'taxonomy' => 'wbbm_bus_stops', //empty string(''), false, 0 don't work, and return 
            'hide_empty' => false, //can be 1, '1' too
        );
        $terms = get_terms($get_terms_default_attributes);
        if (!empty($terms) && !is_wp_error($terms)) {
            ob_start();
        ?>
            <select name="<?php echo $name; ?>" class='seat_type select2'>
                <option value=""><?php _e('Please Select', 'bus-booking-manager'); ?></option>
                <?php
                foreach ($terms as $term) {
                ?>
                    <option value="<?php echo $term->name; ?>" <?php if ($type_name == $term->name) {
                                                                    echo "Selected";
                                                                } ?>><?php echo $term->name; ?></option>
                <?php
                }
                ?>
            </select>
        <?php

        }
        $content = ob_get_clean();
        return $content;
    }


    function wbbm_get_bus_price($start, $end, $array)
    {
        foreach ($array as $key => $val) {
            if ($val['wbbm_bus_bp_price_stop'] === $start && $val['wbbm_bus_dp_price_stop'] === $end) {
                return $val['wbbm_bus_price'];
                // return $key;
            }
        }
        return null;
    }

    function wbbm_get_bus_price_by_type($start, $end, $type = 'adult', $array = null)
    {
        $type = $type;
        foreach ($array as $key => $val) {
            if ($val['wbbm_bus_bp_price_stop'] === $start && $val['wbbm_bus_dp_price_stop'] === $end) {
                if ($type == 'child') {
                    return (isset($val['wbbm_bus_price_child']) ? $val['wbbm_bus_price_child'] : null);
                } elseif ($type == 'infant') {
                    return (isset($val['wbbm_bus_price_infant']) ? $val['wbbm_bus_price_infant'] : null);
                } else {
                    return $val['wbbm_bus_price'];
                }
            }
        }
        return null;
    }


    function wbbm_get_bus_price_child($start, $end, $array)
    {
        foreach ($array as $key => $val) {
            if ($val['wbbm_bus_bp_price_stop'] === $start && $val['wbbm_bus_dp_price_stop'] === $end) {

                return isset($val['wbbm_bus_price_child']);
                // return $key;
            }
        }
        return null;
    }

    function wbbm_get_bus_price_infant($start, $end, $array)
    {
        foreach ($array as $key => $val) {
            if ($val['wbbm_bus_bp_price_stop'] === $start && $val['wbbm_bus_dp_price_stop'] === $end) {

                return isset($val['wbbm_bus_price_infant']);
                // return $key;
            }
        }
        return null;
    }

    function wbbm_get_bus_price_entire($start, $end, $array)
    {
        foreach ($array as $key => $val) {
            if ($val['wbbm_bus_bp_price_stop'] === $start && $val['wbbm_bus_dp_price_stop'] === $end) {

                return isset($val['wbbm_bus_price_entire']);
                // return $key;
            }
        }
        return null;
    }

    function wbbm_get_bus_start_time($start, $array)
    {
        foreach ($array as $key => $val) {
            if ($val['wbbm_bus_bp_stops_name'] === $start) {
                return $val['wbbm_bus_bp_start_time'];
                // return $key;
            }
        }
        return null;
    }


    function wbbm_get_bus_end_time($end, $array)
    {
        foreach ($array as $key => $val) {
            if ($val['wbbm_bus_next_stops_name'] === $end) {
                return $val['wbbm_bus_next_end_time'];
                // return $key;
            }
        }
        return null;
    }

    //add_action('wbbm_search_fields','wbbm_bus_search_fileds');
    function wbbm_bus_search_fileds($start, $end, $date, $r_date)
    {
        ob_start();
        ?>
        <div class="search-fields">

            <div class="fields-li">
                <label>
                    <i class="fa fa-map-marker" aria-hidden="true"></i> <?php _e('From', 'bus-booking-manager'); ?>
                    <?php echo wbbm_get_bus_route_list('bus_start_route', $start); ?></label>
            </div>

            <div class="fields-li">
                <label>
                    <i class="fa fa-map-marker" aria-hidden="true"></i> <?php _e('To:', 'bus-booking-manager'); ?>
                    <?php echo wbbm_get_bus_route_list('bus_end_route', $end); ?>
                </label>
            </div>


            <div class="fields-li">
                <label for='j_date'>
                    <i class="fa fa-calendar" aria-hidden="true"></i> <?php _e('Date of Journey:', 'bus-booking-manager'); ?>
                    <input type="text" id="j_date" name="j_date" value="<?php echo $date; ?>">
                </label>
            </div>


            <div class="fields-li return-date-sec">
                <label for='r_date'>
                    <i class="fa fa-calendar" aria-hidden="true"></i> <?php _e('Return Date:', 'bus-booking-manager'); ?>
                    <input type="text" id="r_date" name="r_date" value="">
                </label>
            </div>
            <?php
            if (isset($_GET['bus-r'])) {
                $busr = strip_tags($_GET['bus-r']);
            } else {
                $busr = 'oneway';
            }
            ?>
            <div class="fields-li">
                <div class="search-radio-sec">
                    <label for="oneway"><input type="radio" <?php if ($busr == 'oneway') {
                                                                echo 'checked';
                                                            } ?> id='oneway' name="bus-r" value='oneway'> <?php _e('One Way', 'bus-booking-manager'); ?>
                    </label>
                    <label for="return_date"><input type="radio" <?php if ($busr == 'return') {
                                                                        echo 'checked';
                                                                    } ?> id='return_date' name="bus-r" value='return'>
                        <?php _e('Return', 'bus-booking-manager'); ?>
                    </label>
                </div>
                <button type="submit"><i class='fa fa-search'></i> <?php _e('Search', 'bus-booking-manager'); ?>
                </button>
            </div>
        </div>
        <script>
            <?php if (isset($_GET['bus-r']) && $_GET['bus-r'] == 'oneway') { ?>
                jQuery('.return-date-sec').hide();
            <?php } elseif (isset($_GET['bus-r']) && $_GET['bus-r'] == 'return') { ?>
                jQuery('.return-date-sec').show();
            <?php } else { ?>
                jQuery('.return-date-sec').hide();
            <?php } ?>
            jQuery('#oneway').on('click', function() {
                jQuery('.return-date-sec').hide();
            });
            jQuery('#return_date').on('click', function() {
                jQuery('.return-date-sec').show();
            });
        </script>
    <?php
        $content = ob_get_clean();
        echo $content;
    }


    function wbbm_get_seat_status($seat, $date, $bus_id, $start)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . "wbbm_bus_booking_list";
        $total_mobile_users = $wpdb->get_results("SELECT status FROM $table_name WHERE seat='$seat' AND journey_date='$date' AND bus_id = $bus_id AND ( boarding_point ='$start' OR next_stops LIKE '%$start%' ) ORDER BY booking_id DESC Limit 1 ");
        return $total_mobile_users;
    }


    function wbbm_get_available_seat($bus_id, $date)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . "wbbm_bus_booking_list";
        $total_mobile_users = $wpdb->get_var("SELECT COUNT(booking_id) FROM $table_name WHERE bus_id=$bus_id AND journey_date='$date' AND (status=2 OR status=1)");
        return $total_mobile_users;
    }

    function wbbm_get_available_seat_new($bus_id, $start, $end, $date)
    {
        if ($date) {
            $date = $date;
        } else {
            $date = current_time('Y-m-d');
        }
        global $wpdb;
        $total_seats = get_post_meta($bus_id, 'wbbm_total_seat', true);
        $sold_seats = 0;
        $table_name = $wpdb->prefix . "wbbm_bus_booking_list";
        $bus_start_stops_arr = maybe_unserialize(get_post_meta($bus_id, 'wbbm_bus_bp_stops', true)); // $bus_id bus start points
        $bus_end_stops_arr = maybe_unserialize(get_post_meta($bus_id, 'wbbm_bus_next_stops', true)); // $bus_id bus end points

        $seat_booked_status = wbbm_seat_booked_on_status();

        if ($bus_start_stops_arr && $bus_end_stops_arr) {
            $bus_stops = array_column($bus_start_stops_arr, 'wbbm_bus_bp_stops_name'); // remove time
            $bus_ends = array_column($bus_end_stops_arr, 'wbbm_bus_next_stops_name'); // remove time
            $bus_stops_merge = array_merge($bus_stops, $bus_ends); // Bus start and stop merge
            $bus_stops_unique = array_values(array_unique($bus_stops_merge)); // Make stops unique

            $sp = array_search($start, $bus_stops_unique); // Get search start position in all bus stops
            $ep = array_search($end, $bus_stops_unique); // Get search end position in all bus stops

            $f = mage_array_slice($bus_stops_unique, 0, $sp + 1);
            $l = mage_array_slice($bus_stops_unique, $ep, (count($bus_stops_unique) - 1));

            $where = mage_intermidiate_available_seat_condition($start, $end, $bus_stops_unique);
            $entire_query = "SELECT seat FROM $table_name WHERE bus_id=$bus_id AND journey_date='$date' AND $where AND status IN ($seat_booked_status)";
            if ($wpdb->get_var($entire_query) == -1) { // is entire booking
                $sold_seats = $total_seats;
            } else { // is single booking
                $query = "SELECT COUNT(booking_id) FROM $table_name WHERE bus_id=$bus_id AND journey_date='$date' AND $where AND ticket_status != 99 AND status IN ($seat_booked_status)";
                $sold_seats = $wpdb->get_var($query);
            }
        }

        return $sold_seats;
    }

    // Mage array slice
    function mage_array_slice($arr, $s, $e = null)
    {
        return $arr ? array_slice($arr, $s, $e) : array();
    }

    // Get bus stops position in all bus stops
    function mage_intermidiate_available_seat_condition($start, $end, $all_stops)
    {
        $where = '';
        $sp = array_search($start, $all_stops);
        $ep = array_search($end, $all_stops);

        if ($sp == 0) {
            $where = sprintf("(boarding_point IN (\"%s\") AND droping_point IN (\"%s\"))", implode('","', mage_array_slice($all_stops, 0, $ep)), implode('","', mage_array_slice($all_stops, $sp)));
        } elseif ($ep == (count($all_stops) - 1)) {
            $where = sprintf("(boarding_point IN (\"%s\") AND droping_point IN (\"%s\"))", implode('","', mage_array_slice($all_stops, 0, $ep)), implode('","', mage_array_slice($all_stops, $sp + 1)));
        } else {
            $where = sprintf("(boarding_point IN (\"%s\") AND droping_point IN (\"%s\"))", implode('","', mage_array_slice($all_stops, 0, $ep)), implode('","', mage_array_slice($all_stops, $ep)));
        }

        return $where;
    }

    function wbbm_get_order_meta($item_id, $key)
    {
        global $wpdb;
        $value = null;
        $table_name = $wpdb->prefix . "woocommerce_order_itemmeta";
        $sql = 'SELECT meta_value FROM ' . $table_name . ' WHERE order_item_id =' . $item_id . ' AND meta_key="' . $key . '"';
        $results = $wpdb->get_results($sql);
        if ($results) {
            foreach ($results as $result) {
                $value = $result->meta_value;
            }
        }
        return $value;
    }


    function wbbm_get_order_seat_check($bus_id, $order_id, $user_type, $bus_start, $date)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . "wbbm_bus_booking_list";
        $total_mobile_users = $wpdb->get_var("SELECT COUNT(booking_id) FROM $table_name WHERE bus_id=$bus_id AND order_id = $order_id AND bus_start = '$bus_start' AND user_type = '$user_type' AND journey_date='$date' AND (status = 1 OR status = 2 OR status = 3)");
        return $total_mobile_users;
    }

    // add_action('init','wwbbm_ch');
    function wwbbm_ch()
    {
        global $wpdb, $woocommerce;
        $order = wc_get_order(117);
        echo '<pre>';
        // print_r($order);
        echo $order->status;
        echo '</pre>';
        if ($order->has_status('pending')) {
            echo 'Yes';
        }
        die();
    }

    // add_action( 'woocommerce_checkout_order_processed', 'wbbm_order_status_before_payment', 10, 3 );
    function wbbm_order_status_before_payment($order_id, $posted_data, $order)
    {
        $order->update_status('processing');
    }


    function wbbm_get_all_stops_after_this($bus_id, $val, $end)
    {
        $start_stops = get_post_meta($bus_id, 'wbbm_bus_bp_stops', true);
        $all_stops = array();
        foreach ($start_stops as $_start_stops) {
            $all_stops[] = $_start_stops['wbbm_bus_bp_stops_name'];
        }
        $pos = array_search($val, $all_stops);
        $pos2 = array_search($end, $all_stops);
        unset($all_stops[$pos]);
        unset($all_stops[$pos2]);
        return $all_stops;
    }


    function wbbm_add_passenger($order_id, $bus_id, $user_id, $start, $next_stops, $end, $user_name, $user_email, $user_phone, $user_gender, $user_dob, $nationality, $flight_arrival_no, $flight_departure_no, $user_address, $user_type, $b_time, $j_time, $adult, $adult_per_price, $child, $child_per_price, $infant, $infant_per_price, $entire, $entire_per_price, $total_price, $item_quantity, $j_date, $add_datetime, $pickpoint, $status)
    {
        global $wpdb;

        $table_name = $wpdb->prefix . 'wbbm_bus_booking_list';
        // $add_datetime = current_time("Y-m-d h:i:s");
        $wpdb->insert(
            $table_name,
            array(
                'order_id' => $order_id,
                'bus_id' => $bus_id,
                'user_id' => $user_id,
                'boarding_point' => $start,
                'next_stops' => $next_stops,
                'droping_point' => $end,
                'user_name' => $user_name,
                'user_email' => $user_email,
                'user_phone' => $user_phone,
                'user_gender' => $user_gender,
                'user_dob' => $user_dob,
                'nationality' => $nationality,
                'flight_arrial_no' => $flight_arrival_no,
                'flight_departure_no' => $flight_departure_no,
                'user_address' => $user_address,
                'user_type' => $user_type,
                'bus_start' => $b_time,
                'user_start' => $j_time,
                'total_adult' => $adult,
                'per_adult_price' => $adult_per_price,
                'total_child' => $child,
                'per_child_price' => $child_per_price,
                'total_infant' => $infant,
                'per_infant_price' => $infant_per_price,
                'total_entire' => $entire,
                'per_entire_price' => $entire_per_price,
                'total_price' => $total_price,
                'seat' => $item_quantity,
                'journey_date' => $j_date,
                'booking_date' => $add_datetime,
                'pickpoint' => $pickpoint,
                'status' => $status
            ),
            array(
                '%d',
                '%d',
                '%d',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%f',
                '%f',
                '%f',
                '%f',
                '%f',
                '%f',
                '%f',
                '%f',
                '%f',
                '%s',
                '%s',
                '%s',
                '%s',
                '%d'
            )
        );
    }


    add_action('woocommerce_checkout_order_processed', 'wbbm_add_passenger_to_db', 1, 1);
    function wbbm_add_passenger_to_db($order_id)
    {
        global $wpdb;
        // Getting an instance of the order object
        $order = wc_get_order($order_id);
        $order_meta = get_post_meta($order_id);

        # Iterating through each order items (WC_Order_Item_Product objects in WC 3+)
        foreach ($order->get_items() as $item_id => $item_values) {
            $product_id = $item_values->get_product_id();
            $item_data = $item_values->get_data();
            $product_id = $item_data['product_id'];
            $item_quantity = $item_values->get_quantity();
            $product = get_page_by_title($item_data['name'], OBJECT, 'wbbm_bus');
            $event_name = $item_data['name'];
            $event_id = $product->ID;
            $item_id = $item_id;
            // $item_data = $item_values->get_data();

            $user_id = $order_meta['_customer_user'][0];
            $order_status = $order->status;
            $eid = wbbm_get_order_meta($item_id, '_wbbm_bus_id');
            if (get_post_type($eid) == 'wbbm_bus') {
                $user_info_arr = wbbm_get_order_meta($item_id, '_wbbm_passenger_info');
                $start = wbbm_get_order_meta($item_id, '_boarding_point');
                $end = wbbm_get_order_meta($item_id, '_droping_point');
                $j_date = wbbm_get_order_meta($item_id, '_journey_date');
                // $j_date = wbbm_convert_date_to_php($j_date);
                $j_time = wbbm_get_order_meta($item_id, '_journey_time');
                $bus_id = wbbm_get_order_meta($item_id, '_bus_id');
                $b_time = wbbm_get_order_meta($item_id, '_btime');

                $adult = wbbm_get_order_meta($item_id, '_Adult');
                $child = wbbm_get_order_meta($item_id, '_Child');
                $infant = wbbm_get_order_meta($item_id, '_Infant');
                $entire = wbbm_get_order_meta($item_id, '_Entire');
                $adult_per_price = wbbm_get_order_meta($item_id, '_adult_per_price');
                $child_per_price = wbbm_get_order_meta($item_id, '_child_per_price');
                $infant_per_price = wbbm_get_order_meta($item_id, '_infant_per_price');
                $entire_per_price = wbbm_get_order_meta($item_id, '_entire_per_price');
                $total_price = wbbm_get_order_meta($item_id, '_total_price');
                $next_stops = maybe_serialize(wbbm_get_all_stops_after_this($bus_id, $start, $end));
                $pickpoint = wbbm_get_order_meta($item_id, 'Pickpoint');

                $usr_inf = maybe_unserialize($user_info_arr);
                $counter = 0;
                $_seats = 'None';

                $item_quantity = $adult;
                if ($child) {
                    $item_quantity = $item_quantity + $child;
                } else {
                    $child = 0;
                    $child_per_price = 0;
                }

                if ($infant) {
                    $item_quantity = $item_quantity + $infant;
                } else {
                    $infant = 0;
                    $infant_per_price = 0;
                }

                if ($entire) {
                    $item_quantity = $item_quantity + $entire;
                } else {
                    $entire = 0;
                    $entire_per_price = 0;
                }

                $user_name = "";
                $user_email = "";
                $user_phone = "";
                $user_address = "";
                $user_gender = "";
                $user_dob = "";
                $nationality = "";
                $flight_arrival_no = "";
                $flight_departure_no = "";
                $user_type = "";

                // For Entire Bus booking
                if ($entire) {
                    $item_quantity = 1;
                }

                for ($x = 1; $x <= $item_quantity; $x++) {

                    // if(!empty($_seats)){

                    if (isset($usr_inf[$counter]['wbbm_user_name'])) {
                        $user_name = $usr_inf[$counter]['wbbm_user_name'];
                    } else {
                        $user_name = "";
                    }
                    if (isset($usr_inf[$counter]['wbbm_user_email'])) {
                        $user_email = $usr_inf[$counter]['wbbm_user_email'];
                    } else {
                        $user_email = "";
                    }
                    if (isset($usr_inf[$counter]['wbbm_user_phone'])) {
                        $user_phone = $usr_inf[$counter]['wbbm_user_phone'];
                    } else {
                        $user_phone = "";
                    }
                    if (isset($usr_inf[$counter]['wbbm_user_address'])) {
                        $user_address = $usr_inf[$counter]['wbbm_user_address'];
                    } else {
                        $user_address = "";
                    }
                    if (isset($usr_inf[$counter]['wbbm_user_gender'])) {
                        $user_gender = $usr_inf[$counter]['wbbm_user_gender'];
                    } else {
                        $user_gender = "";
                    }
                    if (isset($usr_inf[$counter]['wbbm_user_dob'])) {
                        $user_dob = $usr_inf[$counter]['wbbm_user_dob'];
                    } else {
                        $user_dob = "";
                    }
                    if (isset($usr_inf[$counter]['wbbm_user_nationality'])) {
                        $nationality = $usr_inf[$counter]['wbbm_user_nationality'];
                    } else {
                        $nationality = "";
                    }
                    if (isset($usr_inf[$counter]['wbbm_user_flight_arrival_no'])) {
                        $flight_arrival_no = $usr_inf[$counter]['wbbm_user_flight_arrival_no'];
                    } else {
                        $flight_arrival_no = "";
                    }
                    if (isset($usr_inf[$counter]['wbbm_user_flight_departure_no'])) {
                        $flight_departure_no = $usr_inf[$counter]['wbbm_user_flight_departure_no'];
                    } else {
                        $flight_departure_no = "";
                    }

                    if (isset($usr_inf[$counter]['wbbm_user_type'])) {
                        $user_type = $usr_inf[$counter]['wbbm_user_type'];
                    } else {
                        $user_type = "Adult";
                    }
                    if ($entire) {
                        $item_quantity = -1;
                    }
                    $_seats = $item_quantity;
                    $check_before_add = wbbm_get_order_seat_check($bus_id, $order_id, $user_type, $b_time, $j_date);
                    if ($check_before_add == 0) {

                        wbbm_add_passenger($order_id, $bus_id, $user_id, $start, $next_stops, $end, $user_name, $user_email, $user_phone, $user_gender, $user_dob, $nationality, $flight_arrival_no, $flight_departure_no, $user_address, $user_type, $b_time, $j_time, $adult, $adult_per_price, $child, $child_per_price, $infant, $infant_per_price, $entire, $entire_per_price, $total_price, $item_quantity, $j_date, current_time("Y-m-d h:i:s"), $pickpoint, 0);
                    }
                    // }
                    $counter++;
                }
            }
        }

        return 0;
    }


    add_action('woocommerce_order_status_changed', 'wbbm_bus_ticket_seat_management', 10, 4);
    function wbbm_bus_ticket_seat_management($order_id, $from_status, $to_status, $order)
    {
        global $wpdb;
        // Getting an instance of the order object
        $order = wc_get_order($order_id);
        $order_meta = get_post_meta($order_id);

        # Iterating through each order items (WC_Order_Item_Product objects in WC 3+)
        foreach ($order->get_items() as $item_id => $item_values) {
            $product_id = $item_values->get_product_id();
            $item_data = $item_values->get_data();
            $product_id = $item_data['product_id'];
            $item_quantity = $item_values->get_quantity();
            $product = get_page_by_title($item_data['name'], OBJECT, 'wbbm_bus');
            $event_name = $item_data['name'];
            $event_id = $product->ID;
            $item_id = $item_id;
            // $item_data = $item_values->get_data();

            $user_id = $order_meta['_customer_user'][0];
            $order_status = $order->status;
            $eid = wbbm_get_order_meta($item_id, '_wbbm_bus_id');

            if (get_post_type($eid) == 'wbbm_bus') {


                $user_info_arr = wbbm_get_order_meta($item_id, '_wbbm_passenger_info');
                $start = wbbm_get_order_meta($item_id, 'Boarding Point');
                $end = wbbm_get_order_meta($item_id, 'Dropping Point');
                $j_date = wbbm_get_order_meta($item_id, 'Journey Date');
                $j_time = wbbm_get_order_meta($item_id, 'Journey Time');
                $bus_id = wbbm_get_order_meta($item_id, '_bus_id');
                $b_time = wbbm_get_order_meta($item_id, '_btime');

                $adult = wbbm_get_order_meta($item_id, 'Adult');
                $child = wbbm_get_order_meta($item_id, 'Child');
                $infant = wbbm_get_order_meta($item_id, 'Infant');
                $entire = wbbm_get_order_meta($item_id, 'Entire');
                $adult_per_price = wbbm_get_order_meta($item_id, '_adult_per_price');
                $child_per_price = wbbm_get_order_meta($item_id, '_child_per_price');
                $infant_per_price = wbbm_get_order_meta($item_id, '_infant_per_price');
                $entire_per_price = wbbm_get_order_meta($item_id, '_entire_per_price');
                $total_price = wbbm_get_order_meta($item_id, '_total_price');
                $next_stops = maybe_serialize(wbbm_get_all_stops_after_this($bus_id, $start, $end));


                $usr_inf = unserialize($user_info_arr);
                $counter = 0;
                $_seats = 'None';

                $item_quantity = ($adult + $child + $infant + $entire);
                // $_seats         =   $item_quantity;
                // foreach ($seats as $_seats) {
                for ($x = 1; $x <= $item_quantity; $x++) {

                    // if(!empty($_seats)){

                    if (isset($usr_inf[$counter]['wbbm_user_name'])) {
                        $user_name = $usr_inf[$counter]['wbbm_user_name'];
                    } else {
                        $user_name = "";
                    }
                    if (isset($usr_inf[$counter]['wbbm_user_email'])) {
                        $user_email = $usr_inf[$counter]['wbbm_user_email'];
                    } else {
                        $user_email = "";
                    }
                    if (isset($usr_inf[$counter]['wbbm_user_phone'])) {
                        $user_phone = $usr_inf[$counter]['wbbm_user_phone'];
                    } else {
                        $user_phone = "";
                    }
                    if (isset($usr_inf[$counter]['wbbm_user_address'])) {
                        $user_address = $usr_inf[$counter]['wbbm_user_address'];
                    } else {
                        $user_address = "";
                    }
                    if (isset($usr_inf[$counter]['wbbm_user_gender'])) {
                        $user_gender = $usr_inf[$counter]['wbbm_user_gender'];
                    } else {
                        $user_gender = "";
                    }
                    if (isset($usr_inf[$counter]['wbbm_user_dob'])) {
                        $user_dob = $usr_inf[$counter]['wbbm_user_dob'];
                    } else {
                        $user_dob = "";
                    }
                    if (isset($usr_inf[$counter]['wbbm_user_type'])) {
                        $user_type = $usr_inf[$counter]['wbbm_user_type'];
                    } elseif (isset($entire)) {
                        $user_type = "Entire";
                    } else {
                        $user_type = "Adult";
                    }
                    $_seats = $item_quantity;
                    $check_before_add = wbbm_get_order_seat_check($bus_id, $order_id, $user_type, $b_time, $j_date);
                    // }
                    $counter++;
                }


                if ($order->has_status('processing')) {
                    $status = 1;
                    $table_name = $wpdb->prefix . 'wbbm_bus_booking_list';
                    $wpdb->query(
                        $wpdb->prepare("UPDATE $table_name SET status = %d WHERE order_id = %d AND bus_id = %d", $status, $order_id, $event_id)
                    );
                }


                if ($order->has_status('pending')) {
                    $status = 3;
                    $table_name = $wpdb->prefix . 'wbbm_bus_booking_list';
                    $wpdb->query(
                        $wpdb->prepare("UPDATE $table_name SET status = %d WHERE order_id = %d AND bus_id = %d", $status, $order_id, $event_id)
                    );
                }

                if ($order->has_status('on-hold')) {
                    $status = 6;
                    $table_name = $wpdb->prefix . 'wbbm_bus_booking_list';
                    $wpdb->query(
                        $wpdb->prepare("UPDATE $table_name SET status = %d WHERE order_id = %d AND bus_id = %d", $status, $order_id, $event_id)
                    );
                }

                if ($order->has_status('failed')) {
                    $status = 7;
                    $table_name = $wpdb->prefix . 'wbbm_bus_booking_list';
                    $wpdb->query(
                        $wpdb->prepare("UPDATE $table_name SET status = %d WHERE order_id = %d AND bus_id = %d", $status, $order_id, $event_id)
                    );
                }

                if ($order->has_status('cancelled')) {
                    $status = 5;
                    $table_name = $wpdb->prefix . 'wbbm_bus_booking_list';
                    $wpdb->query(
                        $wpdb->prepare("UPDATE $table_name SET status = %d WHERE order_id = %d AND bus_id = %d", $status, $order_id, $event_id)
                    );
                }

                if ($order->has_status('completed')) {
                    $status = 2;
                    $table_name = $wpdb->prefix . 'wbbm_bus_booking_list';
                    $wpdb->query(
                        $wpdb->prepare("UPDATE $table_name SET status = %d WHERE order_id = %d AND bus_id = %d", $status, $order_id, $event_id)
                    );
                }

                if ($order->has_status('refunded')) {
                    $status = 4;
                    $table_name = $wpdb->prefix . 'wbbm_bus_booking_list';
                    $wpdb->query(
                        $wpdb->prepare("UPDATE $table_name SET status = %d WHERE order_id = %d AND bus_id = %d", $status, $order_id, $event_id)
                    );
                }
            }
        }
    }


    function wbbm_array_strip($string, $allowed_tags = NULL)
    {
        if (is_array($string)) {
            foreach ($string as $k => $v) {
                $string[$k] = wbbm_array_strip($v, $allowed_tags);
            }
            return $string;
        }
        return strip_tags($string, $allowed_tags);
    }


    function wbbm_find_product_in_cart($id)
    {

        $product_id = $id;
        $in_cart = false;

        foreach (WC()->cart->get_cart() as $cart_item) {
            $product_in_cart = $cart_item['product_id'];
            if ($product_in_cart === $product_id) $in_cart = true;
        }

        if ($in_cart) {
            return 'into-cart';
        } else {
            return 'not-in-cart';
        }
    }


    add_action('show_seat_form', 'wbbm_seat_form');
    function wbbm_seat_form($start, $end, $price_arr, $return = false)
    {
        $date = $return ? mage_get_isset('r_date') : mage_get_isset('j_date');
        // $available_seat = mage_available_seat(wbbm_convert_date_to_php($date));
        $id = get_the_id();
        $boarding = isset($_GET['bus_start_route']) ? strip_tags($_GET['bus_start_route']) : '';
        $dropping = isset($_GET['bus_end_route']) ? strip_tags($_GET['bus_end_route']) : '';
        $seat_price_adult = mage_seat_price($id, $boarding, $dropping, 'adult');
        $seat_price_child = mage_seat_price($id, $boarding, $dropping, 'child');
        $seat_price_infant = mage_seat_price($id, $boarding, $dropping, 'infant');
        $seat_price_entire = mage_seat_price($id, $boarding, $dropping, 'entire');
        $available_seat = wbbm_intermidiate_available_seat($boarding, $dropping, wbbm_convert_date_to_php($date));
        $total_seat = get_post_meta($id, 'wbbm_total_seat', true);
        $entire_bus_booking = wbbm_get_option('wbbm_entire_bus_booking_switch', 'wbbm_general_setting_sec');
        ob_start();
    ?>
        <div class="seat-no-form">
            <?php
            $adult_fare = wbbm_get_bus_price($start, $end, $price_arr);
            if ($adult_fare > 0) {
            ?>
                <label for='quantity_<?php echo get_the_id(); ?>'>
                    Adult (<?php //echo get_woocommerce_currency_symbol();
                            ?><?php echo wc_price($seat_price_adult); ?> )
                    <input type="number" id="quantity_<?php echo get_the_id(); ?>" class="input-text qty text bqty" step="1" min="0" max="<?php echo $available_seat; ?>" name="adult_quantity" value="0" title="Qty" size="4" pattern="[0-9]*" inputmode="numeric" required aria-labelledby="" placeholder='0' />
                </label>
            <?php
            }
            $child_fare = wbbm_get_bus_price_child($start, $end, $price_arr);
            if ($child_fare > 0) {
            ?>
                <label for='child_quantity_<?php echo get_the_id(); ?>'>
                    Child (<?php //echo get_woocommerce_currency_symbol();
                            ?><?php echo wc_price($seat_price_child); ?>)
                    <input type="number" id="child_quantity_<?php echo get_the_id(); ?>" class="input-text qty text bqty" step="1" min="0" max="<?php echo $available_seat; ?>" name="child_quantity" value="0" title="Qty" size="4" pattern="[0-9]*" inputmode="numeric" required aria-labelledby="" placeholder='0' />
                </label>
            <?php }
            $infant_fare = wbbm_get_bus_price_infant($start, $end, $price_arr);
            if ($infant_fare > 0) : ?>
                <label for='infant_quantity_<?php echo get_the_id(); ?>'>
                    Infant
                    (<?php //echo get_woocommerce_currency_symbol(); 
                        ?><?php echo wc_price($seat_price_infant); ?>)
                    <input type="number" id="infant_quantity_<?php echo get_the_id(); ?>" class="input-text qty text bqty" step="1" min="0" max="<?php echo $available_seat; ?>" name="infant_quantity" value="0" title="Qty" size="4" pattern="[0-9]*" inputmode="numeric" required aria-labelledby="" placeholder='0' />
                </label>
            <?php endif; ?>
            <?php
            $entire_fare = wbbm_get_bus_price_entire($start, $end, $price_arr);
            if (($entire_bus_booking == 'on') && ($available_seat == $total_seat) && $entire_fare > 0) : ?>
                <label for='entire_quantity_<?php echo get_the_id(); ?>'>
                    <?php echo wbbm_get_option('wbbm_entire_bus_text', 'wbbm_label_setting_sec') ? wbbm_get_option('wbbm_entire_bus_text', 'wbbm_label_setting_sec') : __('Entire Bus', 'bus-booking-manager'); ?>
                    (<?php //echo get_woocommerce_currency_symbol(); 
                        ?><?php echo wc_price($seat_price_entire); ?>)
                    <input type="number" id="entire_quantity_<?php echo get_the_id(); ?>" class="input-text qty text bqty" step="1" min="0" max="1" name="entire_quantity" value="0" title="Qty" size="1" pattern="[0-9]*" inputmode="numeric" required aria-labelledby="" placeholder='0' maxlength="1" oninput="maxLengthCheck(this)" />
                    <p><?php esc_html_e('Please enter 1 for entire bus booking.', 'bus-booking-manager'); ?></p>
                </label>
                <script>
                    function maxLengthCheck(object) {
                        if (object.value.length > object.maxLength)
                            object.value = object.value.slice(0, object.maxLength)
                    }
                </script>
            <?php endif; ?>
        </div>
<?php
        $seat_form = ob_get_clean();
        echo $seat_form;
    }

    function wbbm_check_od_in_range($start_date, $end_date, $j_date)
    {
        // Convert to timestamp
        $start_ts = strtotime($start_date);
        $end_ts = strtotime($end_date);
        $user_ts = strtotime($j_date);

        // Check that user date is between start & end
        if (($user_ts >= $start_ts) && ($user_ts <= $end_ts)) {
            return 'yes';
        } else {
            return 'no';
        }
    }


    add_filter('woocommerce_cart_item_price', 'wbbm_avada_mini_cart_price_fixed', 100, 3);
    function wbbm_avada_mini_cart_price_fixed($price, $cart_item, $r)
    {
        $price = wc_price($cart_item['line_total']);
        return $price;
    }


    /**
     * The magical Datetime Function, Just call this function where you want display date or time, Pass the date or time and the format this will be return the date or time in the current wordpress saved datetime format and according the timezone.
     */
    function get_wbbm_datetime($date, $type)
    {
        $date_format = get_option('date_format');
        $time_format = get_option('time_format');
        $wpdatesettings = $date_format . '  ' . $time_format;
        $timezone = wp_timezone_string();
        $timestamp = strtotime($date . ' ' . $timezone);

        if ($type == 'date') {
            return wp_date($date_format, $timestamp);
        }
        if ($type == 'date-time') {
            return wp_date($wpdatesettings, $timestamp);
        }
        if ($type == 'date-text') {

            return wp_date($date_format, $timestamp);
        }

        if ($type == 'date-time-text') {
            return wp_date($wpdatesettings, $timestamp);
        }
        if ($type == 'time') {
            return wp_date($time_format, $timestamp);
        }

        if ($type == 'time-raw') {
            return wp_date('h:i A', $timestamp);
        }

        if ($type == 'day') {
            return wp_date('d', $timestamp);
        }
        if ($type == 'month') {
            return wp_date('M', $timestamp);
        }
    }


    function wbbm_get_page_list()
    {
        $args = array(
            'post_type' => 'page',
            'posts_per_page' => -1
        );

        $loop = new WP_Query($args);
        $page = [];
        foreach ($loop->posts as $_page) {
            # code...
            $page[$_page->post_name] = $_page->post_title;
        }
        return $page;
    }


    function wbbm_convert_datepicker_dateformat()
    {
        $date_format = get_option('date_format');
        // return $date_format;
        // $php_d     = array('F', 'j', 'Y', 'm','d','D','M','y');
        // $js_d   = array('d', 'M', 'yy','mm','dd','tt','mm','yy');
        $dformat = str_replace('d', 'dd', $date_format);
        $dformat = str_replace('m', 'mm', $dformat);
        $dformat = str_replace('Y', 'yy', $dformat);

        if ($date_format == 'Y-m-d' || $date_format == 'm/d/Y' || $date_format == 'd/m/Y' || $date_format == 'Y/d/m' || $date_format == 'Y-d-m') {
            return str_replace('/', '-', $dformat);
        } elseif ($date_format == 'Y.m.d' || $date_format == 'm.d.Y' || $date_format == 'd.m.Y' || $date_format == 'Y.d.m' || $date_format == 'Y.d.m') {
            return str_replace('.', '-', $dformat);
        } else {
            return 'yy-mm-dd';
        }
    }

    function wbbm_convert_date_to_php($date, $to = 'Y-m-d')
    {
        // die;
        $setting_format = get_option('date_format');

        if (!$date) {
            return null;
        }
        if (preg_match('/\s/', $setting_format)) {

            return date($to, strtotime($date));
        } else {
            $setting_format__dashed = str_replace('/', '-', $setting_format);
            $dash_date = str_replace('/', '-', $date);

            $date_f = DateTime::createFromFormat($setting_format__dashed, $dash_date);
            if ($date_f) {
                $res = $date_f->format($to);
                return $res;
            } else {
                return null;
            }
        }
    }

    // function wbbm_convert_date_to_php($date){

    // $date_format        = get_option( 'date_format' );
    // if($date_format == 'Y-m-d' || $date_format == 'm/d/Y' || $date_format == 'm/d/Y'){
    // if($date_format == 'd/m/Y'){
    //   $date = str_replace('/', '-', $date);
    // }
    // }
    // return date('Y-m-d', strtotime($date));
    // }


    // Function for create hidden product for bus
    function wbbm_create_hidden_event_product($post_id, $title)
    {
        $new_post = array(
            'post_title' => $title,
            'post_content' => '',
            'post_name' => uniqid(),
            'post_category' => array(),
            'tags_input' => array(),
            'post_status' => 'publish',
            'post_type' => 'product'
        );


        $pid = wp_insert_post($new_post);

        update_post_meta($post_id, 'link_wc_product', $pid);
        update_post_meta($pid, 'link_wbbm_bus', $post_id);
        update_post_meta($pid, '_price', 0.01);

        update_post_meta($pid, '_sold_individually', 'yes');
        update_post_meta($pid, '_virtual', 'yes');
        $terms = array('exclude-from-catalog', 'exclude-from-search');
        wp_set_object_terms($pid, $terms, 'product_visibility');
        update_post_meta($post_id, 'check_if_run_once', true);
    }


    function wbbm_on_post_publish($post_id, $post, $update)
    {
        if ($post->post_type == 'wbtm_bus' && $post->post_status == 'publish' && empty(get_post_meta($post_id, 'check_if_run_once'))) {

            // ADD THE FORM INPUT TO $new_post ARRAY
            $new_post = array(
                'post_title' => $post->post_title,
                'post_content' => '',
                'post_name' => uniqid(),
                'post_category' => array(),  // Usable for custom taxonomies too
                'tags_input' => array(),
                'post_status' => 'publish', // Choose: publish, preview, future, draft, etc.
                'post_type' => 'product'  //'post',page' or use a custom post type if you want to
            );
            //SAVE THE POST
            $pid = wp_insert_post($new_post);
            $product_type = mep_get_option('mep_event_product_type', 'general_setting_sec', 'yes');
            update_post_meta($post_id, 'link_wc_product', $pid);
            update_post_meta($pid, 'link_wbbm_bus', $post_id);
            update_post_meta($pid, '_price', 0.01);
            update_post_meta($pid, '_sold_individually', 'yes');
            update_post_meta($pid, '_virtual', $product_type);
            $terms = array('exclude-from-catalog', 'exclude-from-search');
            wp_set_object_terms($pid, $terms, 'product_visibility');
            update_post_meta($post_id, 'check_if_run_once', true);
        }
    }

    add_action('wp_insert_post', 'wbbm_on_post_publish', 10, 3);

    function wbbm_count_hidden_wc_product($event_id)
    {
        $args = array(
            'post_type' => 'product',
            'posts_per_page' => -1,
            'meta_query' => array(
                array(
                    'key' => 'link_wbbm_bus',
                    'value' => $event_id,
                    'compare' => '='
                )
            )
        );
        $loop = new WP_Query($args);
        // print_r($loop->posts);
        return $loop->post_count;
    }


    add_action('save_post', 'wbbm_wc_link_product_on_save', 99, 1);
    function wbbm_wc_link_product_on_save($post_id)
    {

        if (get_post_type($post_id) == 'wbbm_bus') {

            //   if ( ! isset( $_POST['mep_event_reg_btn_nonce'] ) ||
            //   ! wp_verify_nonce( $_POST['mep_event_reg_btn_nonce'], 'mep_event_reg_btn_nonce' ) )
            //     return;

            if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
                return;

            if (!current_user_can('edit_post', $post_id))
                return;
            $event_name = get_the_title($post_id);

            if (wbbm_count_hidden_wc_product($post_id) == 0 || empty(get_post_meta($post_id, 'link_wc_product', true))) {
                wbbm_create_hidden_event_product($post_id, $event_name);
            }

            $product_id = get_post_meta($post_id, 'link_wc_product', true) ? get_post_meta($post_id, 'link_wc_product', true) : $post_id;
            set_post_thumbnail($product_id, get_post_thumbnail_id($post_id));
            wp_publish_post($product_id);

            // $product_type               = mep_get_option('mep_event_product_type', 'general_setting_sec','yes');

            $_tax_status = isset($_POST['_tax_status']) ? strip_tags($_POST['_tax_status']) : 'none';
            $_tax_class = isset($_POST['_tax_class']) ? strip_tags($_POST['_tax_class']) : '';

            update_post_meta($product_id, '_tax_status', $_tax_status);
            update_post_meta($product_id, '_tax_class', $_tax_class);
            update_post_meta($product_id, '_stock_status', 'instock');
            update_post_meta($product_id, '_manage_stock', 'no');
            update_post_meta($product_id, '_virtual', 'yes');
            update_post_meta($product_id, '_sold_individually', 'yes');


            // Update post
            $my_post = array(
                'ID' => $product_id,
                'post_title' => $event_name, // new title
                'post_name' => uniqid() // do your thing here
            );

            // unhook this function so it doesn't loop infinitely
            remove_action('save_post', 'wbbm_wc_link_product_on_save');
            // update the post, which calls save_post again
            wp_update_post($my_post);
            // re-hook this function
            add_action('save_post', 'wbbm_wc_link_product_on_save');
            // Update the post into the database


        }
    }


    add_action('parse_query', 'wbbm_product_tags_sorting_query');
    function wbbm_product_tags_sorting_query($query)
    {
        global $pagenow;

        $taxonomy = 'product_visibility';

        $q_vars = &$query->query_vars;

        if ($pagenow == 'edit.php' && isset($q_vars['post_type']) && $q_vars['post_type'] == 'product') {


            $tax_query = array([
                'taxonomy' => 'product_visibility',
                'field' => 'slug',
                'terms' => 'exclude-from-catalog',
                'operator' => 'NOT IN',
            ]);
            $query->set('tax_query', $tax_query);
        }
    }
} else {


    require_once WBTM_PLUGIN_DIR . '/inc/WBTM_Quick_Setup.php';
    add_action('activated_plugin', 'activation_redirect_setup', 90, 1);



}


function activation_redirect($plugin)
{
    if ($plugin == plugin_basename(__FILE__)) {
        exit(wp_redirect(admin_url('edit.php?post_type=wbbm_bus&page=wbbm_init_quick_setup')));
    }
}


function activation_redirect_setup($plugin)
{
    if ($plugin == plugin_basename(__FILE__)) {
        exit(wp_redirect(admin_url('admin.php?post_type=wbtm_bus&page=wbbm_init_quick_setup')));
    }
}

// Customize Woocommerce order itemmeta
add_action('woocommerce_order_item_get_formatted_meta_data', 'wbbm_after_order_itemmeta', 20, 2);
if (!function_exists('wbbm_after_order_itemmeta')) {
    function wbbm_after_order_itemmeta($meta_data, $item)
    {
        $new_meta = array();
        foreach ($meta_data as $id => $meta_array) {
            // We are removing the meta with the key 'something' from the whole array.
            if ('_Adult' === $meta_array->key) {
                continue;
            }
            if ('_Child' === $meta_array->key) {
                continue;
            }
            if ('_Infant' === $meta_array->key) {
                continue;
            }
            if ('_Entire' === $meta_array->key) {
                continue;
            }
            if ('_total_price' === $meta_array->key) {
                continue;
            }
            $new_meta[$id] = $meta_array;
        }
        return $new_meta;
    }
}
// Get Plugin Data
if (!function_exists('wbbm_get_plugin_data')) {
    function wbbm_get_plugin_data($data)
    {
        $get_wbbm_plugin_data = get_plugin_data(__FILE__);
        $wbbm_data = $get_wbbm_plugin_data[$data];
        return $wbbm_data;
    }
}

// Added Settings link to plugin action links
add_filter('plugin_action_links', 'wbbm_plugin_action_link', 10, 2);

function wbbm_plugin_action_link($links_array, $plugin_file_name)
{

    if (strpos($plugin_file_name, basename(__FILE__))) {

        array_unshift($links_array, '<a href="' . esc_url(admin_url()) . 'edit.php?post_type=wbbm_bus&page=wbbm_quick_setup">' . __('Settings', 'bus-booking-manager') . '</a>');
    }

    return $links_array;
}

// Added links to plugin row meta
add_filter('plugin_row_meta', 'wbbm_plugin_row_meta', 10, 2);

function wbbm_plugin_row_meta($links_array, $plugin_file_name)
{

    if (strpos($plugin_file_name, basename(__FILE__))) {

        if (!is_plugin_active('bus-booking-manager-pro/wbtm-pro.php')) {
            $wbbm_links = array(
                'docs' => '<a href="' . esc_url("https://docs.mage-people.com/multipurpose-ticket-booking-manager/") . '" target="_blank">' . __('Docs', 'bus-booking-manager') . '</a>',
                'support' => '<a href="' . esc_url("https://mage-people.com/my-account") . '" target="_blank">' . __('Support', 'bus-booking-manager') . '</a>',
                'get_pro' => '<a href="' . esc_url("https://mage-people.com/product/multipurpose-ticket-booking-manager-bus-train-ferry-boat-shuttle/") . '" target="_blank" class="wbbm_plugin_pro_meta_link">' . __('Upgrade to PRO Version', 'bus-booking-manager') . '</a>'
            );
        } else {
            $wbbm_links = array(
                'docs' => '<a href="' . esc_url("https://docs.mage-people.com/multipurpose-ticket-booking-manager/") . '" target="_blank">' . __('Docs', 'bus-booking-manager') . '</a>',
                'support' => '<a href="' . esc_url("https://mage-people.com/my-account") . '" target="_blank">' . __('Support', 'bus-booking-manager') . '</a>',
            );
        }

        $links_array = array_merge($links_array, $wbbm_links);
    }

    return $links_array;
}


function check_woocommerce()
{
    include_once(ABSPATH . 'wp-admin/includes/plugin.php');
    $plugin_dir = ABSPATH . 'wp-content/plugins/woocommerce';
    if (is_plugin_active('woocommerce/woocommerce.php')) {
        return 'yes';
    } elseif (is_dir($plugin_dir)) {
        return 'no';
    } else {
        return 0;
    }
}

/*************************
 * Check the required plugins
 ***************************/
require_once(dirname(__FILE__) . "/inc/wbbm-required-plugins.php");

