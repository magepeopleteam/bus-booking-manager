<?php
	if ( ! defined( 'ABSPATH' ) ) {
		die;
    } // Cannot access pages directly.
    
    if (!function_exists('get_mep_datetime')) {
        function get_mep_datetime($date, $type) {
            $date_format = get_option('date_format');
            $time_format = get_option('time_format');
            $wpdatesettings = $date_format . '  ' . $time_format;
            $timezone = wp_timezone_string();
            $timestamp = strtotime($date . ' ' . $timezone);
    
            if ($type == 'date') {
                return esc_html(wp_date($date_format, $timestamp));
            }
            if ($type == 'date-time') {
                return esc_html(wp_date($wpdatesettings, $timestamp));
            }
            if ($type == 'date-text') {
    
                return esc_html(wp_date($date_format, $timestamp));
            }
    
            if ($type == 'date-time-text') {
                return esc_html(wp_date($wpdatesettings, $timestamp, wp_timezone()));
            }
            if ($type == 'time') {
                return esc_html(wp_date($time_format, $timestamp, wp_timezone()));
            }
    
            if ($type == 'Hour') {
                return esc_html(wp_date('H', $timestamp, wp_timezone()));
            }
            if ($type == 'hour') {
                return esc_html(wp_date('h', $timestamp, wp_timezone()));
            }
            if ($type == 'minute') {
                return esc_html(wp_date('i', $timestamp, wp_timezone()));
            }
    
            if ($type == 'second') {
                return esc_html(wp_date('s', $timestamp, wp_timezone()));
            }
    
            if ($type == 'day') {
                return esc_html(wp_date('d', $timestamp));
            }
            if ($type == 'Dday') {
                return esc_html(wp_date('D', $timestamp));
            }
            if ($type == 'month') {
                return esc_html(wp_date('m', $timestamp));
            }
            if ($type == 'month-name') {
                return esc_html(wp_date('M', $timestamp));
            }
            if ($type == 'year') {
                return esc_html(wp_date('y', $timestamp));
            }
            if ($type == 'year-full') {
                return esc_html(wp_date('Y', $timestamp));
            }
            if ($type == 'timezone') {
                return esc_html(wp_date('T', $timestamp));
            }
            return '';
        }
    }
    if (!function_exists('mep_license_error_code')) {
        function mep_license_error_code($license_data, $item_name = 'this Plugin') {
        
            switch ($license_data->error) {
                case 'expired':
                    $message = sprintf(
                        __('Your license key expired on %s.'),
                        date_i18n(get_option('date_format'), strtotime($license_data->expires, current_time('timestamp')))
                    );
                    break;
        
                case 'revoked':
                    $message = __('Your license key has been disabled.');
                    break;
        
                case 'missing':
                    $message = __('Invalid license.');
                    break;
        
                case 'invalid':
                case 'site_inactive':
                    $message = __('Your license is not active for this URL.');
                    break;
        
                case 'item_name_mismatch':
        
                    $message = sprintf(__('This appears to be an invalid license key for %s.'), $item_name);
                    break;
        
                case 'no_activations_left':
                    $message = __('Your license key has reached its activation limit.');
                    break;
                default:
        
                    $message = __('An error occurred, please try again.');
                    break;
            }
            return $message;
        }
        }
        
        if (!function_exists('mep_license_expire_date')) {
            function mep_license_expire_date($date) {
    if (empty($date) || $date == 'lifetime') {
        echo esc_html($date);
    } else {
        if (strtotime(current_time('Y-m-d H:i')) < strtotime(date('Y-m-d H:i', strtotime($date)))) {
            echo esc_html(get_mep_datetime($date, 'date-time-text')); // Escape the output
        } else {
            esc_html_e('Expired', 'mage-eventpress');
        }
    }
}

        }
        
    add_filter( 'wbbm_settings_sec_reg', 'wbbm_register_license_tab_name', 90 );

    function wbbm_register_license_tab_name( $default_sec ){
        $sections = array(
            array(
                'id'    => 'wbbm_basic_license_settings',
                'title' => __( 'License', 'tour-booking-manager' )
            )
        );
        return array_merge( $default_sec, $sections );
    }


    add_action('wsa_form_bottom_wbbm_basic_license_settings', 'wbbm_licensing_page', 5);
    function wbbm_licensing_page($form) {
        ?>
        <div class='ttbm-licensing-page'>
            <h3>Multipurpose Ticket Booking Manager Licensing</h3>
            <p>Thanks you for using our Multipurpose Ticket Booking Manager Licensing plugin. This plugin is free and no license is required. We have some Additional addon to enhace feature of this plugin functionality. If you have any addon you need to enter a valid license for that plugin below. </p>
    
            <div class="mep_licensae_info"></div>
            <table class='wp-list-table widefat striped posts mep-licensing-table'>
                <thead>
                <tr>
                    <th>Plugin Name</th>
                    <th width=10%>Order No</th>
                    <th width=15%>Expire on</th>
                    <th width=30%>License Key</th>
                    <th width=10%>Status</th>
                    <th width=10%>Action</th>
                </tr>
                </thead>
                <tbody>
                <?php do_action('wbbm_license_page_addon_list'); ?>
                </tbody>
            </table>
        </div>
        <?php
    }    