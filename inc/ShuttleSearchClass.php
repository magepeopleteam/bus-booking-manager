<?php
if (!defined('ABSPATH')) exit;

class ShuttleSearchClass {
    
    public function __construct() {
        add_shortcode('wbbm_shuttle_search', array($this, 'render_shuttle_search_shortcode'));

        // AJAX Actions for Dynamic Fields
        add_action('wp_ajax_wbbm_get_shuttle_pickup_points', array($this, 'ajax_get_pickup_points'));
        add_action('wp_ajax_nopriv_wbbm_get_shuttle_pickup_points', array($this, 'ajax_get_pickup_points'));
        
        add_action('wp_ajax_wbbm_get_shuttle_times', array($this, 'ajax_get_times'));
        add_action('wp_ajax_nopriv_wbbm_get_shuttle_times', array($this, 'ajax_get_times'));

        add_action('wp_ajax_wbbm_get_shuttle_dropoff', array($this, 'ajax_get_dropoff_stops'));
        add_action('wp_ajax_nopriv_wbbm_get_shuttle_dropoff', array($this, 'ajax_get_dropoff_stops'));

        add_action('wp_ajax_wbbm_get_shuttle_dropoff_points', array($this, 'ajax_get_dropoff_points'));
        add_action('wp_ajax_nopriv_wbbm_get_shuttle_dropoff_points', array($this, 'ajax_get_dropoff_points'));
    }

    /**
     * Shortcode Handler
     */
    public function render_shuttle_search_shortcode($atts) {
        $atts = shortcode_atts(array(
            'target_page' => '', 
        ), $atts);

        ob_start();
        
        if (isset($_GET['shuttle_search']) && $_GET['shuttle_search'] == '1') {
            //
        } else {
            $this->render_search_form($atts);
        }

        return ob_get_clean();
    }

    /**
     * Render Search Form (Route First)
     */
    private function render_search_form($atts) {
        $action_url = $atts['target_page'] ? $atts['target_page'] : '';
        
        // 1. Get All Routes
        // We need to list "Shuttle Name - Route Name"
        $shuttles = get_posts(array('post_type' => 'wbbm_shuttle', 'posts_per_page' => -1, 'post_status' => 'publish'));
        $routes_dropdown = array();
        
        foreach ($shuttles as $shuttle) {
            $routes = maybe_unserialize(get_post_meta($shuttle->ID, 'wbbm_shuttle_routes', true));
            if ($routes && is_array($routes)) {
                foreach ($routes as $route) {
                    // Key: post_id|route_id
                    $key = $shuttle->ID . '|' . $route['id'];
                    $label = $shuttle->post_title . ' - ' . $route['name'];
                    $routes_dropdown[$key] = $label;
                }
            }
        }
        
        ?>
        <div class="wbbm_shuttle_search_card">
            <h3 class="wbbm_search_title"><?php _e('Book a Shuttle', 'bus-booking-manager'); ?></h3>
            <form action="<?php echo esc_url($action_url); ?>" method="GET" class="wbbm_shuttle_search_form" id="wbbm_shuttle_search_form">
                <input type="hidden" name="shuttle_search" value="1">
                
                <div class="wbbm_form_grid">
                    <!-- 1. Route Selection -->
                    <div class="wbbm_field_group">
                        <label><?php _e('Select Route', 'bus-booking-manager'); ?></label>
                        <select name="shuttle_route_id" id="shuttle_route_id" required class="form-control">
                            <option value=""><?php _e('Choose a Route', 'bus-booking-manager'); ?></option>
                            <?php foreach ($routes_dropdown as $val => $label) : ?>
                                <option value="<?php echo esc_attr($val); ?>"><?php echo esc_html($label); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- 2. Pickup Point (Populated via AJAX) -->
                    <div class="wbbm_field_group">
                        <label><?php _e('Pickup Point', 'bus-booking-manager'); ?></label>
                        <select name="shuttle_pickup_point" id="shuttle_pickup_point" required class="form-control" disabled>
                            <option value=""><?php _e('Select Route First', 'bus-booking-manager'); ?></option>
                        </select>
                    </div>

                    <!-- 3. Date -->
                    <div class="wbbm_field_group">
                        <label><?php _e('Date', 'bus-booking-manager'); ?></label>
                        <input type="date" name="shuttle_date" id="shuttle_date" required class="form-control" value="<?php echo date('Y-m-d'); ?>">
                    </div>

                    <!-- 4. Time (Populated via AJAX based on Route + Date + Pickup Offset) -->
                    <div class="wbbm_field_group">
                        <label><?php _e('Time', 'bus-booking-manager'); ?></label>
                        <select name="shuttle_time" id="shuttle_time" required class="form-control" disabled>
                            <option value=""><?php _e('Select Date & Pickup', 'bus-booking-manager'); ?></option>
                        </select>
                    </div>

                    <!-- 5. Passengers -->
                    <div class="wbbm_field_group">
                        <label><?php _e('Passengers', 'bus-booking-manager'); ?></label>
                        <input type="number" name="shuttle_qty" min="1" value="1" required class="form-control">
                    </div>

                    <!-- 6. Drop-off Stop (Populated via AJAX based on Route + Pickup Index) -->
                    <div class="wbbm_field_group">
                        <label><?php _e('Drop-off Stop', 'bus-booking-manager'); ?></label>
                        <select name="shuttle_dropoff_stop" id="shuttle_dropoff_stop" required class="form-control" disabled>
                            <option value=""><?php _e('Select Pickup First', 'bus-booking-manager'); ?></option>
                        </select>
                    </div>
                     
                     <!-- 7. Drop-off Point (Populated via AJAX based on Dropoff Stop) -->
                     <div class="wbbm_field_group">
                        <label><?php _e('Drop-off Point', 'bus-booking-manager'); ?></label>
                         <select name="shuttle_dropoff_point" id="shuttle_dropoff_point" class="form-control" disabled>
                            <option value=""><?php _e('Select Drop-off Stop First', 'bus-booking-manager'); ?></option>
                        </select>
                    </div>

                    <!-- Submit -->
                    <div class="wbbm_field_group wbbm_submit_group">
                         <button type="submit" class="button wbbm_search_btn"><?php _e('Search', 'bus-booking-manager'); ?></button>
                    </div>
                </div>
            </form>
        </div>
        
        <script type="text/javascript">
            jQuery(document).ready(function($) {
                var ajaxUrl = '<?php echo admin_url('admin-ajax.php'); ?>';
                
                // 1. Route Changed -> Get Pickup Points
                $('#shuttle_route_id').on('change', function() {
                    var routeVal = $(this).val();
                    resetFields(['shuttle_pickup_point', 'shuttle_time', 'shuttle_dropoff_stop', 'shuttle_dropoff_point']);
                    
                    if (!routeVal) return;
                    
                    $.ajax({
                        url: ajaxUrl,
                        type: 'POST',
                        data: {
                            action: 'wbbm_get_shuttle_pickup_points',
                            route_val: routeVal
                        },
                        success: function(res) {
                            if (res.success) {
                                populateSelect('#shuttle_pickup_point', res.data, 'Select Pickup Point');
                                $('#shuttle_pickup_point').prop('disabled', false);
                            }
                        }
                    });
                });

                // 2. Pickup Point OR Date Changed -> Get Times
                $('#shuttle_pickup_point, #shuttle_date').on('change', function() {
                    var routeVal = $('#shuttle_route_id').val();
                    var pickupVal = $('#shuttle_pickup_point').val(); // This should be index|location|offset
                    var dateVal = $('#shuttle_date').val();
                    
                    $('#shuttle_time').prop('disabled', true).html('<option>Loading...</option>');
                    
                    if (!routeVal || !pickupVal || !dateVal) {
                         $('#shuttle_time').html('<option>Select Date & Pickup</option>');
                         return;
                    }

                    $.ajax({
                        url: ajaxUrl,
                        type: 'POST',
                        data: {
                            action: 'wbbm_get_shuttle_times',
                            route_val: routeVal,
                            pickup_val: pickupVal,
                            date: dateVal
                        },
                        success: function(res) {
                            if(res.success) {
                                populateSelect('#shuttle_time', res.data, 'Select Time');
                                $('#shuttle_time').prop('disabled', false);
                            } else {
                                $('#shuttle_time').html('<option>' + res.data + '</option>');
                            }
                        }
                    });
                    
                    // Also Update Available Drop-off Stops (must be AFTER pickup index)
                    if (this.id === 'shuttle_pickup_point') {
                         updateDropoffStops(routeVal, pickupVal);
                    }
                });
                
                // 3. Drop-off Stop Changed -> Get Drop-off Points
                $('#shuttle_dropoff_stop').on('change', function() {
                    var routeVal = $('#shuttle_route_id').val();
                    var stopVal = $(this).val();
                    
                    $('#shuttle_dropoff_point').prop('disabled', true).html('<option>Loading...</option>');
                    
                    if (!routeVal || !stopVal) return;
                    
                    $.ajax({
                        url: ajaxUrl,
                        type: 'POST',
                        data: {
                            action: 'wbbm_get_shuttle_dropoff_points',
                            route_val: routeVal,
                            stop_location: stopVal
                        },
                        success: function(res) {
                            if (res.success) {
                                populateSelect('#shuttle_dropoff_point', res.data, 'Select Drop-off Point');
                                $('#shuttle_dropoff_point').prop('disabled', false);
                            }
                        }
                    });
                });
                
                function updateDropoffStops(routeVal, pickupVal) {
                    resetFields(['shuttle_dropoff_stop', 'shuttle_dropoff_point']);
                    
                     $.ajax({
                        url: ajaxUrl,
                        type: 'POST',
                        data: {
                            action: 'wbbm_get_shuttle_dropoff',
                            route_val: routeVal,
                            pickup_val: pickupVal
                        },
                        success: function(res) {
                            if (res.success) {
                                populateSelect('#shuttle_dropoff_stop', res.data, 'Select Drop-off Stop');
                                $('#shuttle_dropoff_stop').prop('disabled', false);
                            }
                        }
                    });
                }
                
                function resetFields(ids) {
                    ids.forEach(function(id) {
                        $('#' + id).prop('disabled', true).empty().append('<option>Wait...</option>');
                    });
                }
                
                function populateSelect(selector, options, placeholder) {
                    var $el = $(selector);
                    $el.empty().append('<option value="">' + placeholder + '</option>');
                    $.each(options, function(key, val) {
                        $el.append($('<option>', { value: key, text: val }));
                    });
                }
            });
        </script>
        
        <style>
            .wbbm_shuttle_search_card {
                background: #fff;
                border-radius: 8px;
                box-shadow: 0 4px 15px rgba(0,0,0,0.1);
                padding: 30px;
                max-width: 800px;
                margin: 0 auto;
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            }
            .wbbm_search_title {
                margin-top: 0;
                margin-bottom: 20px;
                text-align: center;
                color: #333;
                font-size: 24px;
            }
            .wbbm_form_grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
                gap: 20px;
                align-items: end;
            }
            .wbbm_field_group label {
                display: block;
                margin-bottom: 6px;
                font-weight: 600;
                color: #555;
                font-size: 14px;
            }
            .wbbm_field_group .form-control {
                width: 100%;
                padding: 10px 12px;
                border: 1px solid #e1e1e1;
                border-radius: 6px;
                font-size: 14px;
                transition: border-color 0.3s;
                box-sizing: border-box;
                height: 42px;
            }
            .wbbm_field_group .form-control:focus {
                border-color: #0073aa;
                outline: none;
            }
            .wbbm_field_group .form-control:disabled {
                background: #f5f5f5;
                cursor: not-allowed;
            }
            .wbbm_search_btn {
                background: #0073aa;
                color: #fff;
                border: none;
                padding: 12px 25px;
                border-radius: 6px;
                font-size: 16px;
                cursor: pointer;
                width: 100%;
                font-weight: 600;
                height: 42px;
                transition: background 0.3s;
            }
            .wbbm_search_btn:hover {
                background: #005177;
            }
            .wbbm_submit_group {
                 align-self: end;
            }
        </style>
        <?php
    }
    
    // AJAX: Get Pickup Points
    public function ajax_get_pickup_points() {
        $route_val = isset($_POST['route_val']) ? sanitize_text_field($_POST['route_val']) : '';
        if (!$route_val) wp_send_json_error();
        
        list($post_id, $route_id) = explode('|', $route_val);
        $routes = maybe_unserialize(get_post_meta($post_id, 'wbbm_shuttle_routes', true));
        
        $options = array();
        
        if ($routes) {
            foreach ($routes as $route) {
                if ($route['id'] == $route_id && isset($route['stops'])) {
                   foreach ($route['stops'] as $idx => $stop) {
                       // We send: index|location|time_offset as Value
                       // But do we need individual pickup points?
                       // User request: "select pickup point, ..., drop-off stops AND points"
                       
                       $loc_val = $idx . '|' . $stop['location'] . '|' . ($stop['time_offset'] ? $stop['time_offset'] : 0);
                       
                       // If stop has specific pickup points, list them
                       if (!empty($stop['pickup_points'])) {
                           $points = explode("\n", $stop['pickup_points']);
                           foreach ($points as $p) {
                               $p = trim($p);
                               if($p) {
                                   // Append point name to value
                                   $options[$loc_val . '|' . $p] = $stop['location'] . ' - ' . $p;
                               }
                           }
                       } else {
                           // Generic Stop
                           $options[$loc_val . '|main'] = $stop['location'];
                       }
                   }
                }
            }
        }
        
        wp_send_json_success($options);
    }
    
    // AJAX: Get Times
    public function ajax_get_times() {
        $route_val = isset($_POST['route_val']) ? sanitize_text_field($_POST['route_val']) : '';
        $pickup_val = isset($_POST['pickup_val']) ? sanitize_text_field($_POST['pickup_val']) : '';
        $date = isset($_POST['date']) ? sanitize_text_field($_POST['date']) : '';
        
        if (!$route_val || !$pickup_val || !$date) wp_send_json_error('Missing Params');
        
        list($post_id, $route_id) = explode('|', $route_val);
        // pickup_val format: index|location|offset|point
        $parts = explode('|', $pickup_val);
        $offset = intval($parts[2]);
        
        $schedule = maybe_unserialize(get_post_meta($post_id, 'wbbm_shuttle_schedule', true));
        $day_of_week = strtolower(date('D', strtotime($date)));
        
        $times = array();
        
        if (isset($schedule[$route_id]['forward'])) {
            foreach ($schedule[$route_id]['forward'] as $dep) {
                if (isset($dep['days']) && in_array($day_of_week, $dep['days'])) {
                    // Calculate Time at Pickup Point
                    $base_time = $dep['time'];
                    $pickup_time = date('h:i A', strtotime("+$offset minutes", strtotime($base_time)));
                    $times[$pickup_time] = $pickup_time;
                }
            }
        }
        
        if (empty($times)) {
            wp_send_json_error(__('No shuttles on this date', 'bus-booking-manager'));
        } else {
            wp_send_json_success($times);
        }
    }

    // AJAX: Get Drop-off Stops
    public function ajax_get_dropoff_stops() {
        $route_val = isset($_POST['route_val']) ? sanitize_text_field($_POST['route_val']) : '';
        $pickup_val = isset($_POST['pickup_val']) ? sanitize_text_field($_POST['pickup_val']) : '';
        
        if (!$route_val || !$pickup_val) wp_send_json_error();

        list($post_id, $route_id) = explode('|', $route_val);
        $parts = explode('|', $pickup_val);
        $pickup_idx = intval($parts[0]);
        
        $routes = maybe_unserialize(get_post_meta($post_id, 'wbbm_shuttle_routes', true));
        $options = array();
        
        if ($routes) {
             foreach ($routes as $route) {
                if ($route['id'] == $route_id && isset($route['stops'])) {
                   foreach ($route['stops'] as $idx => $stop) {
                       // Only show stops AFTER the pickup stop
                       if ($idx > $pickup_idx) {
                            $loc = $stop['location'];
                            $options[$loc] = $loc;
                       }
                   }
                }
             }
        }
        wp_send_json_success($options);
    }
    
    // AJAX: Get Drop-off Points for a specific Stop
    public function ajax_get_dropoff_points() {
        $route_val = isset($_POST['route_val']) ? sanitize_text_field($_POST['route_val']) : '';
        $stop_location = isset($_POST['stop_location']) ? sanitize_text_field($_POST['stop_location']) : '';
        
        if (!$route_val || !$stop_location) wp_send_json_error();
        
        list($post_id, $route_id) = explode('|', $route_val);
        $routes = maybe_unserialize(get_post_meta($post_id, 'wbbm_shuttle_routes', true));
        $options = array();
        
        if ($routes) {
            foreach ($routes as $route) {
                if ($route['id'] == $route_id && isset($route['stops'])) {
                    foreach ($route['stops'] as $stop) {
                        if ($stop['location'] == $stop_location) {
                            // Found the stop, check for points
                            if (!empty($stop['dropoff_points'])) {
                                $points = explode("\n", $stop['dropoff_points']);
                                foreach ($points as $p) {
                                    $p = trim($p);
                                    if ($p) $options[$p] = $p;
                                }
                            } else {
                                $options['main'] = 'Main Stop';
                            }
                            break;
                        }
                    }
                }
            }
        }
        
        wp_send_json_success($options);
    }

    
}

new ShuttleSearchClass();
