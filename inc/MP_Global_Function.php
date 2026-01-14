<?php
/*
* @Author 		engr.sumonazma@gmail.com
* Copyright: 	mage-people.com
*/
if (! defined('ABSPATH')) {
    die;
} // Cannot access pages directly.
if (! class_exists('MP_Global_Function')) {
    class MP_Global_Function
    {
        public function __construct()
        {
            add_action('mp_load_date_picker_js', [$this, 'date_picker_js'], 10, 2);
        }

        public static function query_post_type($post_type, $show = -1, $page = 1): WP_Query
        {
            $args = array(
                'post_type'      => sanitize_key($post_type), // Sanitize post type
                'posts_per_page' => intval($show), // Ensure it's an integer
                'paged'          => intval($page), // Ensure it's an integer
                'post_status'    => 'publish',
            );
            return new WP_Query($args);
        }

        public static function get_all_post_id($post_type, $show = -1, $page = 1, $status = 'publish'): array
        {
            $all_data = get_posts(array(
                'fields'         => 'ids',
                'post_type'      => sanitize_key($post_type), // Sanitize post type
                'posts_per_page' => intval($show), // Ensure it's an integer
                'paged'          => intval($page), // Ensure it's an integer
                'post_status'    => sanitize_key($status), // Sanitize status
            ));
            return array_unique($all_data);
        }

        public static function get_post_info($post_id, $key, $default = '')
        {
            $data = get_post_meta(intval($post_id), sanitize_key($key), true) ?: $default; // Sanitize post ID and key
            return self::data_sanitize($data);
        }

        public static function get_taxonomy($name)
        {
            return get_terms(array('taxonomy' => sanitize_key($name), 'hide_empty' => false)); // Sanitize taxonomy name
        }

        public static function get_term_meta($meta_id, $meta_key, $default = '')
        {
            $data = get_term_meta(intval($meta_id), sanitize_key($meta_key), true) ?: $default; // Sanitize meta ID and key
            return self::data_sanitize($data);
        }

        public static function get_all_term_data($term_name, $value = 'name')
        {
            $all_data = [];
            $taxonomies = self::get_taxonomy($term_name);
            if ($taxonomies && is_array($taxonomies) && sizeof($taxonomies) > 0) {
                foreach ($taxonomies as $taxonomy) {
                    $all_data[] = sanitize_text_field($taxonomy->$value); // Sanitize term data
                }
            }
            return $all_data;
        }

        public static function get_submit_info($key, $default = '')
        {
            // phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash
            if (!isset(($_POST[sanitize_key($key)]))) {
                return;
            }
            // phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
            return self::data_sanitize(wp_unslash($_POST[sanitize_key($key)]) ?? $default); // Sanitize key
        }

        public static function get_submit_info_get_method($key, $default = '')
        {
            // phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash
            if (!isset(($_GET[sanitize_key($key)]))) {
                return;
            }

            // phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
            return self::data_sanitize(wp_unslash($_GET[sanitize_key($key)]) ?? $default); // Sanitize key
        }

        public static function data_sanitize($data)
        {
            $data = maybe_unserialize($data);
            if (is_string($data)) {
                if (is_array($data)) {
                    $data = self::data_sanitize($data);
                } else {
                    $data = sanitize_text_field(wp_strip_all_tags($data));
                }
            } elseif (is_array($data)) {
                foreach ($data as &$value) {
                    if (is_array($value)) {
                        $value = self::data_sanitize($value);
                    } else {
                        $value = sanitize_text_field(wp_strip_all_tags($value));
                    }
                }
            }
            return $data;
        }

        // Date related functions
        public static function date_picker_format_without_year($key = 'date_format'): string
        {
            $format = MP_Global_Function::get_settings('mp_global_settings', $key, 'D d M , yy');
            $date_format = 'm-d';

            // Use a mapping for the date formats
            $date_format_mapping = [
                'yy/mm/dd' => 'm/d',
                'yy-dd-mm' => 'd-m',
                'yy/dd/mm' => 'd/m',
                'dd-mm-yy' => 'd-m',
                'dd/mm/yy' => 'd/m',
                'mm-dd-yy' => 'm-d',
                'mm/dd/yy' => 'm/d',
                'd M , yy' => 'j M',
                'D d M , yy' => 'D j M',
                'M d , yy' => 'M j',
                'D M d , yy' => 'D M j',
            ];

            return $date_format_mapping[$format] ?? 'm-d';
        }

        public static function date_picker_format($key = 'date_format'): string
        {
            $format = MP_Global_Function::get_settings('mp_global_settings', $key, 'D d M , yy');
            $date_format = 'Y-m-d';

            // Use a mapping for the date formats
            $date_format_mapping = [
                'yy/mm/dd' => 'Y/m/d',
                'yy-dd-mm' => 'Y-d-m',
                'yy/dd/mm' => 'Y/d/m',
                'dd-mm-yy' => 'd-m-Y',
                'dd/mm/yy' => 'd/m/Y',
                'mm-dd-yy' => 'm-d-Y',
                'mm/dd/yy' => 'm/d/Y',
                'd M , yy' => 'j M , Y',
                'D d M , yy' => 'D j M , Y',
                'M d , yy' => 'M j, Y',
                'D M d , yy' => 'D M j, Y',
            ];

            return $date_format_mapping[$format] ?? 'Y-m-d';
        }

        public function date_picker_js($selector, $dates)
        {
            $start_date = $dates[0];
            $start_year = gmdate('Y', strtotime($start_date));
            $start_month = (gmdate('n', strtotime($start_date)) - 1);
            $start_day = gmdate('j', strtotime($start_date));
            $end_date = end($dates);
            $end_year = gmdate('Y', strtotime($end_date));
            $end_month = (gmdate('n', strtotime($end_date)) - 1);
            $end_day = gmdate('j', strtotime($end_date));
            $all_date = [];

            // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
            foreach ($dates as $date) {
                $all_date[] = '"' . gmdate('j-n-Y', strtotime($date)) . '"';
            }
?>
            <script>
                jQuery(document).ready(function() {
                    jQuery("<?php echo esc_attr($selector); ?>").datepicker({
                        dateFormat: mp_date_format,
                        minDate: new Date(<?php echo esc_attr($start_year); ?>, <?php echo esc_attr($start_month); ?>, <?php echo esc_attr($start_day); ?>),
                        maxDate: new Date(<?php echo esc_attr($end_year); ?>, <?php echo esc_attr($end_month); ?>, <?php echo esc_attr($end_day); ?>),
                        autoSize: true,
                        changeMonth: true,
                        changeYear: true,
                        beforeShowDay: WorkingDates,
                        onSelect: function(dateString, data) {
                            let date = data.selectedYear + '-' + ('0' + (parseInt(data.selectedMonth) + 1)).slice(-2) + '-' + ('0' + parseInt(data.selectedDay)).slice(-2);
                            jQuery(this).closest('label').find('input[type="hidden"]').val(date).trigger('change');
                        }
                    });

                    function WorkingDates(date) {
                        let availableDates = [<?php echo esc_js(implode(',', $all_date)); ?>];
                        let dmy = date.getDate() + "-" + (date.getMonth() + 1) + "-" + date.getFullYear();
                        if (jQuery.inArray(dmy, availableDates) !== -1) {
                            return [true, "", "Available"];
                        } else {
                            return [false, "", "unAvailable"];
                        }
                    }
                });
            </script>
<?php
        }

        public static function date_format($date, $format = 'date')
        {
            $date_format = get_option('date_format');
            $time_format = get_option('time_format');
            $wp_settings = $date_format . ' ' . $time_format;

            $timestamp = strtotime($date);
            if ($format == 'date') {
                return date_i18n($date_format, $timestamp);
            } elseif ($format == 'time') {
                return date_i18n($time_format, $timestamp);
            } elseif ($format == 'full') {
                return date_i18n($wp_settings, $timestamp);
            } elseif ($format == 'day') {
                return date_i18n('d', $timestamp);
            } elseif ($format == 'month') {
                return date_i18n('M', $timestamp);
            } elseif ($format == 'year') {
                return date_i18n('Y', $timestamp);
            } else {
                return date_i18n($format, $timestamp);
            }
        }

        public static function date_separate_period($start_date, $end_date, $repeat = 1): DatePeriod
        {
            $repeat = max($repeat, 1);
            $_interval = "P" . $repeat . "D";
            $end_date = gmdate('Y-m-d', strtotime($end_date . ' +1 day'));
            return new DatePeriod(new DateTime($start_date), new DateInterval($_interval), new DateTime($end_date));
        }

        public static function check_time_exit_date($date)
        {
            if ($date) {
                $parse_date = date_parse($date);
                return ($parse_date['hour'] > 0 || $parse_date['minute'] > 0 || $parse_date['second'] > 0);
            }
            return false;
        }

        public static function check_licensee_date($date)
        {
            if ($date) {
                if ($date == 'lifetime') {
                    return esc_html__('Lifetime', 'bus-booking-manager');
                } elseif (strtotime(current_time('Y-m-d H:i')) < strtotime(gmdate('Y-m-d H:i', strtotime($date)))) {
                    return MP_Global_Function::date_format($date, 'full');
                } else {
                    return esc_html__('Expired', 'bus-booking-manager');
                }
            }
            return $date;
        }

        public static function sort_date($a, $b)
        {
            return strtotime($a) - strtotime($b);
        }

        public static function sort_date_array($a, $b)
        {
            $dateA = strtotime($a['time']);
            $dateB = strtotime($b['time']);
            if ($dateA == $dateB) {
                return 0;
            }
            return ($dateA > $dateB) ? 1 : -1;
        }


        public static function date_difference($startdate, $enddate)
        {
            $starttimestamp = strtotime($startdate);
            $endtimestamp   = strtotime($enddate);
            $difference     = abs($endtimestamp - $starttimestamp) / 3600;

            $datetime1 = new DateTime($startdate);
            $datetime2 = new DateTime($enddate);
            $interval  = $datetime1->diff($datetime2);
            return $interval->format('%h') . "H " . $interval->format('%i') . "M";
        }

        public static function get_settings($section, $key, $default = '')
        {
            $options = get_option(sanitize_key($section)); // Sanitize section
            if (isset($options[$key])) {
                if (is_array($options[$key])) {
                    return !empty($options[$key]) ? $options[$key] : $default;
                } else {
                    return !empty($options[$key]) ? wp_kses_post($options[$key]) : $default; // Escape for output
                }
            }
            return is_array($default) ? $default : wp_kses_post($default); // Escape default
        }

        public static function get_style_settings($key, $default = '')
        {
            return self::get_settings('mp_style_settings', $key, $default);
        }

        public static function get_slider_settings($key, $default = '')
        {
            return self::get_settings('mp_slider_settings', $key, $default);
        }

        public static function get_licence_settings($key, $default = '')
        {
            return self::get_settings('mp_basic_license_settings', $key, $default);
        }

        public static function price_convert_raw($price)
        {
            $price = wp_strip_all_tags($price);
            $price = str_replace(get_woocommerce_currency_symbol(), '', $price);
            $price = str_replace(wc_get_price_thousand_separator(), 't_s', $price);
            $price = str_replace(wc_get_price_decimal_separator(), 'd_s', $price);
            $price = str_replace('t_s', '', $price);
            $price = str_replace('d_s', '.', $price);
            $price = str_replace('&nbsp;', '', $price);
            return max(floatval($price), 0); // Ensure it's a float
        }

        public static function wc_price($post_id, $price, $args = array()): string
        {
            $num_of_decimal = intval(get_option('woocommerce_price_num_decimals', 2)); // Ensure it's an integer
            $args = wp_parse_args($args, array(
                'qty'   => '',
                'price' => '',
            ));

            $_product = self::get_post_info($post_id, 'link_wc_product', $post_id);
            $product = wc_get_product($_product);
            $qty = '' !== $args['qty'] ? max(0.0, (float)$args['qty']) : 1;
            $tax_with_price = get_option('woocommerce_tax_display_shop');

            if ('' === $price) {
                return '';
            } elseif (empty($qty)) {
                return '0.00';
            }

            $line_price = (float)$price * (int)$qty;
            $return_price = $line_price;

            if ($product && $product->is_taxable()) {
                if (!wc_prices_include_tax()) {
                    $tax_rates = WC_Tax::get_rates($product->get_tax_class());
                    $taxes = WC_Tax::calc_tax($line_price, $tax_rates);
                    $taxes_total = 'yes' === get_option('woocommerce_tax_round_at_subtotal') ? array_sum($taxes) : array_sum(array_map('wc_round_tax_total', $taxes));
                    $return_price = $tax_with_price == 'excl' ? round($line_price, $num_of_decimal) : round($line_price + $taxes_total, $num_of_decimal);
                } else {
                    $base_tax_rates = WC_Tax::get_base_tax_rates($product->get_tax_class());
                    if (!empty(WC()->customer) && WC()->customer->get_is_vat_exempt()) {
                        // phpcs:ignore WordPress.NamingConventions.ValidHookName
                        $remove_taxes = apply_filters('woocommerce_adjust_non_base_location_prices', true) ? WC_Tax::calc_tax($line_price, $base_tax_rates, true) : WC_Tax::calc_tax($line_price, $tax_rates, true);
                        $remove_taxes_total = 'yes' === get_option('woocommerce_tax_round_at_subtotal') ? array_sum($remove_taxes) : array_sum(array_map('wc_round_tax_total', $remove_taxes));
                        $return_price = round($line_price - $remove_taxes_total, $num_of_decimal);
                    } else {
                        $base_taxes = WC_Tax::calc_tax($line_price, $base_tax_rates, true);
                        $modded_taxes = WC_Tax::calc_tax($line_price - array_sum($base_taxes), $tax_rates);
                        $base_taxes_total = 'yes' === get_option('woocommerce_tax_round_at_subtotal') ? array_sum($base_taxes) : array_sum(array_map('wc_round_tax_total', $base_taxes));
                        $modded_taxes_total = 'yes' === get_option('woocommerce_tax_round_at_subtotal') ? array_sum($modded_taxes) : array_sum(array_map('wc_round_tax_total', $modded_taxes));
                        $return_price = $tax_with_price == 'excl' ? round($line_price - $base_taxes_total, $num_of_decimal) : round($line_price - $base_taxes_total + $modded_taxes_total, $num_of_decimal);
                    }
                }
            }

            $return_price = apply_filters('woocommerce_get_price_including_tax', $return_price, $qty, $product);
            $display_suffix = get_option('woocommerce_price_display_suffix') ? get_option('woocommerce_price_display_suffix') : '';
            return wc_price($return_price) . ' ' . esc_html($display_suffix); // Escape suffix
        }

        public static function get_wc_raw_price($post_id, $price, $args = array())
        {
            $price = self::wc_price($post_id, $price, $args);
            return self::price_convert_raw($price);
        }

        public static function get_image_url($post_id = '', $image_id = '', $size = 'full')
        {
            if ($post_id) {
                $image_id = get_post_thumbnail_id($post_id);
                $image_id = $image_id ?: self::get_post_info($post_id, 'mp_thumbnail');
            }
            return wp_get_attachment_image_url($image_id, sanitize_key($size)); // Sanitize size
        }

        public static function get_page_by_slug($slug)
        {
            $slug = sanitize_key($slug); // Sanitize slug
            if ($pages = get_pages()) {
                foreach ($pages as $page) {
                    if ($slug === $page->post_name) {
                        return $page;
                    }
                }
            }
            return false;
        }

        public static function get_id_by_slug($page_slug)
        {
            $page_slug = sanitize_key($page_slug); // Sanitize slug
            $page = get_page_by_path($page_slug);
            return $page ? $page->ID : null;
        }

        public static function check_plugin($plugin_dir_name, $plugin_file): int
        {
            include_once ABSPATH . 'wp-admin/includes/plugin.php';
            $plugin_dir = ABSPATH . 'wp-content/plugins/' . sanitize_key($plugin_dir_name); // Sanitize
            if (is_plugin_active($plugin_dir_name . '/' . $plugin_file)) {
                return 1;
            } elseif (is_dir($plugin_dir)) {
                return 2;
            } else {
                return 0;
            }
        }

        public static function wbbm_check_woocommerce(): int
        {
            include_once(ABSPATH . 'wp-admin/includes/plugin.php');
            $plugin_dir = ABSPATH . 'wp-content/plugins/woocommerce';
            if (is_plugin_active('woocommerce/woocommerce.php')) {
                return 1;
            } elseif (is_dir($plugin_dir)) {
                return 2;
            } else {
                return 0;
            }
        }

        public static function get_order_item_meta($item_id, $key): string
        {
            $item_id = intval($item_id);
            $key = sanitize_text_field($key);

            // Prefer WooCommerce helper if available (uses metadata API internally)
            if (function_exists('wc_get_order_item_meta')) {
                $value = wc_get_order_item_meta($item_id, $key, true);
            } else {
                // Use WP metadata API which includes caching
                $value = get_metadata('order_item', $item_id, $key, true);
            }

            if (is_array($value) || is_object($value)) {
                return maybe_serialize($value);
            }

            return (string) ($value ?? '');
        }

        public static function check_product_in_cart($post_id)
        {
            $status = MP_Global_Function::wbbm_check_woocommerce();
            if ($status == 1) {
                $product_id = MP_Global_Function::get_post_info($post_id, 'link_wc_product');
                foreach (WC()->cart->get_cart() as $cart_item) {
                    if ($cart_item['product_id'] == $product_id) {
                        return true;
                    }
                }
            }
            return false;
        }

        public static function wc_product_sku($product_id)
        {
            return $product_id ? new WC_Product(intval($product_id)) : null; // Ensure product ID is an integer
        }

        public static function all_tax_list(): array
        {
            global $wpdb;
            // Prefer using WooCommerce helper to avoid direct DB queries
            if (function_exists('wc_get_tax_classes')) {
                $classes = wc_get_tax_classes();
                $tax_list = [];
                foreach ($classes as $class) {
                    $slug = sanitize_title($class);
                    $tax_list[$slug] = sanitize_text_field($class);
                }
                return $tax_list;
            }

            
            // Table name (safe, controlled)
            $table_name = $wpdb->prefix . 'wc_tax_rate_classes';

            // phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
            // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- table name cannot be used as a placeholder in prepare()
            $results = $wpdb->get_results("SELECT slug, name FROM " . esc_sql($table_name), ARRAY_A);
            // phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching

            $tax_list = [];
            if ($results) {
                foreach ($results as $tax) {
                    $tax_list[sanitize_text_field($tax['slug'])] = sanitize_text_field($tax['name']);
                }
            }
            return $tax_list;
        }

        public static function week_day(): array
        {
            return [
                'monday'    => esc_html__('Monday', 'bus-booking-manager'),
                'tuesday'   => esc_html__('Tuesday', 'bus-booking-manager'),
                'wednesday' => esc_html__('Wednesday', 'bus-booking-manager'),
                'thursday'  => esc_html__('Thursday', 'bus-booking-manager'),
                'friday'    => esc_html__('Friday', 'bus-booking-manager'),
                'saturday'  => esc_html__('Saturday', 'bus-booking-manager'),
                'sunday'    => esc_html__('Sunday', 'bus-booking-manager'),
            ];
        }

        public static function get_plugin_data($data)
        {
            $plugin_data = get_plugin_data(__FILE__);
            return sanitize_text_field($plugin_data[$data] ?? ''); // Sanitize plugin data
        }

        public static function array_to_string($array)
        {
            return implode(',', array_filter(array_map('sanitize_text_field', $array))); // Sanitize each element
        }

        public static function esc_html($string): string
        {
            $allow_attr = array(
                'input'    => [
                    'type'               => [],
                    'class'              => [],
                    'id'                 => [],
                    'name'               => [],
                    'value'              => [],
                    'size'               => [],
                    'placeholder'        => [],
                    'min'                => [],
                    'max'                => [],
                    'checked'            => [],
                    'required'           => [],
                    'disabled'           => [],
                    'readonly'           => [],
                    'step'               => [],
                    'data-default-color' => [],
                    'data-price'         => [],
                ],
                'p'        => ['class' => []],
                'img'      => ['class' => [], 'id' => [], 'src' => [], 'alt' => []],
                'fieldset' => ['class' => []],
                'label'    => ['for' => [], 'class' => []],
                'select'   => ['class' => [], 'name' => [], 'id' => [], 'data-price' => []],
                'option'   => ['class' => [], 'value' => [], 'id' => [], 'selected' => []],
                'textarea' => ['class' => [], 'rows' => [], 'id' => [], 'cols' => [], 'name' => []],
                'h1'       => ['class' => [], 'id' => []],
                'h2'       => ['class' => [], 'id' => []],
                'h3'       => ['class' => [], 'id' => []],
                'h4'       => ['class' => [], 'id' => []],
                'h5'       => ['class' => [], 'id' => []],
                'h6'       => ['class' => [], 'id' => []],
                'a'        => ['class' => [], 'id' => [], 'href' => []],
                'div'      => ['class' => [], 'id' => [], 'data-ticket-type-name' => []],
                'span'     => ['class' => [], 'id' => [], 'data' => [], 'data-input-change' => []],
                'i'        => ['class' => [], 'id' => [], 'data' => []],
                'table'    => ['class' => [], 'id' => [], 'data' => []],
                'tr'       => ['class' => [], 'id' => [], 'data' => []],
                'td'       => ['class' => [], 'id' => [], 'data' => []],
                'thead'    => ['class' => [], 'id' => [], 'data' => []],
                'tbody'    => ['class' => [], 'id' => [], 'data' => []],
                'th'       => ['class' => [], 'id' => [], 'data' => []],
                'svg'      => ['class' => [], 'id' => [], 'width' => [], 'height' => [], 'viewBox' => [], 'xmlns' => []],
                'g'        => ['fill' => []],
                'path'     => ['d' => []],
                'br'       => [],
                'em'       => [],
                'strong'   => [],
            );
            return wp_kses($string, $allow_attr);
        }

        public static function license_error_text($response, $license_data, $plugin_name)
        {
            if (is_wp_error($response) || 200 !== wp_remote_retrieve_response_code($response)) {
                $message = (is_wp_error($response) && !empty($response->get_error_message())) ? $response->get_error_message() : esc_html__('An error occurred, please try again.', 'bus-booking-manager');
            } else {
                if (false === $license_data->success) {
                    switch ($license_data->error) {
                        case 'expired':
                            $message = sprintf(
                                /* translators: %s date */
                                esc_html__('Your license key expired on %s', 'bus-booking-manager'),
                                esc_html(
                                    date_i18n(
                                        get_option('date_format'),
                                        strtotime($license_data->expires)
                                    )
                                )
                            );

                            break;
                        case 'revoked':
                            $message = esc_html__('Your license key has been disabled.', 'bus-booking-manager');
                            break;
                        case 'missing':
                            $message = esc_html__('Missing license.', 'bus-booking-manager');
                            break;
                        case 'invalid':
                            $message = esc_html__('Invalid license.', 'bus-booking-manager');
                            break;
                        case 'site_inactive':
                            $message = esc_html__('Your license is not active for this URL.', 'bus-booking-manager');
                            break;
                        case 'item_name_mismatch':
                            $message = esc_html__('This appears to be an invalid license key for.', 'bus-booking-manager') . ' ' . esc_html($plugin_name);
                            break;
                        case 'no_activations_left':
                            $message = esc_html__('Your license key has reached its activation limit.', 'bus-booking-manager');
                            break;
                        default:
                            $message = esc_html__('An error occurred, please try again.', 'bus-booking-manager');
                            break;
                    }
                } else {
                    $payment_id = sanitize_text_field($license_data->payment_id); // Sanitize payment ID
                    $expire     = sanitize_text_field($license_data->expires); // Sanitize expiration date
                    $message    = esc_html__('Success, License Key is valid for the plugin', 'bus-booking-manager') . ' ' . esc_html($plugin_name) . ' ' . esc_html__('Your Order id is', 'bus-booking-manager') . ' ' . esc_html($payment_id) . ' ' . esc_html($plugin_name) . ' ' . esc_html__('Validity of this license is', 'bus-booking-manager') . ' ' . MP_Global_Function::check_licensee_date($expire);
                }
            }
            return $message;
        }

        public static function wbbm_recursive_sanitize($data)
        {
            if (is_array($data)) {
                return array_map([__CLASS__, 'wbbm_recursive_sanitize'], $data);
            }

            if (is_numeric($data)) {
                return intval($data);
            }

            if (filter_var($data, FILTER_VALIDATE_URL)) {
                return esc_url_raw($data);
            }

            return sanitize_text_field($data);
        }


        //***********************************//
        public static function get_country_list()
        {
            return array(
                'AF' => 'Afghanistan',
                'AX' => 'Aland Islands',
                'AL' => 'Albania',
                'DZ' => 'Algeria',
                'AS' => 'American Samoa',
                'AD' => 'Andorra',
                'AO' => 'Angola',
                'AI' => 'Anguilla',
                'AQ' => 'Antarctica',
                'AG' => 'Antigua And Barbuda',
                'AR' => 'Argentina',
                'AM' => 'Armenia',
                'AW' => 'Aruba',
                'AU' => 'Australia',
                'AT' => 'Austria',
                'AZ' => 'Azerbaijan',
                'BS' => 'Bahamas',
                'BH' => 'Bahrain',
                'BD' => 'Bangladesh',
                'BB' => 'Barbados',
                'BY' => 'Belarus',
                'BE' => 'Belgium',
                'BZ' => 'Belize',
                'BJ' => 'Benin',
                'BM' => 'Bermuda',
                'BT' => 'Bhutan',
                'BO' => 'Bolivia',
                'BA' => 'Bosnia And Herzegovina',
                'BW' => 'Botswana',
                'BV' => 'Bouvet Island',
                'BR' => 'Brazil',
                'IO' => 'British Indian Ocean Territory',
                'BN' => 'Brunei Darussalam',
                'BG' => 'Bulgaria',
                'BF' => 'Burkina Faso',
                'BI' => 'Burundi',
                'KH' => 'Cambodia',
                'CM' => 'Cameroon',
                'CA' => 'Canada',
                'CV' => 'Cape Verde',
                'KY' => 'Cayman Islands',
                'CF' => 'Central African Republic',
                'TD' => 'Chad',
                'CL' => 'Chile',
                'CN' => 'China',
                'CX' => 'Christmas Island',
                'CC' => 'Cocos (Keeling) Islands',
                'CO' => 'Colombia',
                'KM' => 'Comoros',
                'CG' => 'Congo',
                'CD' => 'Congo, Democratic Republic',
                'CK' => 'Cook Islands',
                'CR' => 'Costa Rica',
                'CI' => 'Cote D\'Ivoire',
                'HR' => 'Croatia',
                'CU' => 'Cuba',
                'CY' => 'Cyprus',
                'CZ' => 'Czech Republic',
                'DK' => 'Denmark',
                'DJ' => 'Djibouti',
                'DM' => 'Dominica',
                'DO' => 'Dominican Republic',
                'EC' => 'Ecuador',
                'EG' => 'Egypt',
                'SV' => 'El Salvador',
                'GQ' => 'Equatorial Guinea',
                'ER' => 'Eritrea',
                'EE' => 'Estonia',
                'ET' => 'Ethiopia',
                'FK' => 'Falkland Islands (Malvinas)',
                'FO' => 'Faroe Islands',
                'FJ' => 'Fiji',
                'FI' => 'Finland',
                'FR' => 'France',
                'GF' => 'French Guiana',
                'PF' => 'French Polynesia',
                'TF' => 'French Southern Territories',
                'GA' => 'Gabon',
                'GM' => 'Gambia',
                'GE' => 'Georgia',
                'DE' => 'Germany',
                'GH' => 'Ghana',
                'GI' => 'Gibraltar',
                'GR' => 'Greece',
                'GL' => 'Greenland',
                'GD' => 'Grenada',
                'GP' => 'Guadeloupe',
                'GU' => 'Guam',
                'GT' => 'Guatemala',
                'GG' => 'Guernsey',
                'GN' => 'Guinea',
                'GW' => 'Guinea-Bissau',
                'GY' => 'Guyana',
                'HT' => 'Haiti',
                'HM' => 'Heard Island & Mcdonald Islands',
                'VA' => 'Holy See (Vatican City State)',
                'HN' => 'Honduras',
                'HK' => 'Hong Kong',
                'HU' => 'Hungary',
                'IS' => 'Iceland',
                'IN' => 'India',
                'ID' => 'Indonesia',
                'IR' => 'Iran, Islamic Republic Of',
                'IQ' => 'Iraq',
                'IE' => 'Ireland',
                'IM' => 'Isle Of Man',
                'IL' => 'Israel',
                'IT' => 'Italy',
                'JM' => 'Jamaica',
                'JP' => 'Japan',
                'JE' => 'Jersey',
                'JO' => 'Jordan',
                'KZ' => 'Kazakhstan',
                'KE' => 'Kenya',
                'KI' => 'Kiribati',
                'KR' => 'Korea',
                'KW' => 'Kuwait',
                'KG' => 'Kyrgyzstan',
                'LA' => 'Lao People\'s Democratic Republic',
                'LV' => 'Latvia',
                'LB' => 'Lebanon',
                'LS' => 'Lesotho',
                'LR' => 'Liberia',
                'LY' => 'Libyan Arab Jamahiriya',
                'LI' => 'Liechtenstein',
                'LT' => 'Lithuania',
                'LU' => 'Luxembourg',
                'MO' => 'Macao',
                'MK' => 'Macedonia',
                'MG' => 'Madagascar',
                'MW' => 'Malawi',
                'MY' => 'Malaysia',
                'MV' => 'Maldives',
                'ML' => 'Mali',
                'MT' => 'Malta',
                'MH' => 'Marshall Islands',
                'MQ' => 'Martinique',
                'MR' => 'Mauritania',
                'MU' => 'Mauritius',
                'YT' => 'Mayotte',
                'MX' => 'Mexico',
                'FM' => 'Micronesia, Federated States Of',
                'MD' => 'Moldova',
                'MC' => 'Monaco',
                'MN' => 'Mongolia',
                'ME' => 'Montenegro',
                'MS' => 'Montserrat',
                'MA' => 'Morocco',
                'MZ' => 'Mozambique',
                'MM' => 'Myanmar',
                'NA' => 'Namibia',
                'NR' => 'Nauru',
                'NP' => 'Nepal',
                'NL' => 'Netherlands',
                'AN' => 'Netherlands Antilles',
                'NC' => 'New Caledonia',
                'NZ' => 'New Zealand',
                'NI' => 'Nicaragua',
                'NE' => 'Niger',
                'NG' => 'Nigeria',
                'NU' => 'Niue',
                'NF' => 'Norfolk Island',
                'MP' => 'Northern Mariana Islands',
                'NO' => 'Norway',
                'OM' => 'Oman',
                'PK' => 'Pakistan',
                'PW' => 'Palau',
                'PS' => 'Palestinian Territory, Occupied',
                'PA' => 'Panama',
                'PG' => 'Papua New Guinea',
                'PY' => 'Paraguay',
                'PE' => 'Peru',
                'PH' => 'Philippines',
                'PN' => 'Pitcairn',
                'PL' => 'Poland',
                'PT' => 'Portugal',
                'PR' => 'Puerto Rico',
                'QA' => 'Qatar',
                'RE' => 'Reunion',
                'RO' => 'Romania',
                'RU' => 'Russian Federation',
                'RW' => 'Rwanda',
                'BL' => 'Saint Barthelemy',
                'SH' => 'Saint Helena',
                'KN' => 'Saint Kitts And Nevis',
                'LC' => 'Saint Lucia',
                'MF' => 'Saint Martin',
                'PM' => 'Saint Pierre And Miquelon',
                'VC' => 'Saint Vincent And Grenadines',
                'WS' => 'Samoa',
                'SM' => 'San Marino',
                'ST' => 'Sao Tome And Principe',
                'SA' => 'Saudi Arabia',
                'SN' => 'Senegal',
                'RS' => 'Serbia',
                'SC' => 'Seychelles',
                'SL' => 'Sierra Leone',
                'SG' => 'Singapore',
                'SK' => 'Slovakia',
                'SI' => 'Slovenia',
                'SB' => 'Solomon Islands',
                'SO' => 'Somalia',
                'ZA' => 'South Africa',
                'GS' => 'South Georgia And Sandwich Isl.',
                'ES' => 'Spain',
                'LK' => 'Sri Lanka',
                'SD' => 'Sudan',
                'SR' => 'Suriname',
                'SJ' => 'Svalbard And Jan Mayen',
                'SZ' => 'Swaziland',
                'SE' => 'Sweden',
                'CH' => 'Switzerland',
                'SY' => 'Syrian Arab Republic',
                'TW' => 'Taiwan',
                'TJ' => 'Tajikistan',
                'TZ' => 'Tanzania',
                'TH' => 'Thailand',
                'TL' => 'Timor-Leste',
                'TG' => 'Togo',
                'TK' => 'Tokelau',
                'TO' => 'Tonga',
                'TT' => 'Trinidad And Tobago',
                'TN' => 'Tunisia',
                'TR' => 'Turkey',
                'TM' => 'Turkmenistan',
                'TC' => 'Turks And Caicos Islands',
                'TV' => 'Tuvalu',
                'UG' => 'Uganda',
                'UA' => 'Ukraine',
                'AE' => 'United Arab Emirates',
                'GB' => 'United Kingdom',
                'US' => 'United States',
                'UM' => 'United States Outlying Islands',
                'UY' => 'Uruguay',
                'UZ' => 'Uzbekistan',
                'VU' => 'Vanuatu',
                'VE' => 'Venezuela',
                'VN' => 'Viet Nam',
                'VG' => 'Virgin Islands, British',
                'VI' => 'Virgin Islands, U.S.',
                'WF' => 'Wallis And Futuna',
                'EH' => 'Western Sahara',
                'YE' => 'Yemen',
                'ZM' => 'Zambia',
                'ZW' => 'Zimbabwe',
            );
        }
    }
    new MP_Global_Function();
}
