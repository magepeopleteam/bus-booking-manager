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
            $this->render_search_results();
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
        
        // Get URL parameters for pre-filling form
        $selected_route = isset($_GET['shuttle_route_id']) ? sanitize_text_field($_GET['shuttle_route_id']) : '';
        $selected_pickup = isset($_GET['shuttle_pickup_point']) ? sanitize_text_field($_GET['shuttle_pickup_point']) : '';
        $selected_dropoff = isset($_GET['dropoff']) ? sanitize_text_field($_GET['dropoff']) : '';
        $selected_date = isset($_GET['date']) ? sanitize_text_field($_GET['date']) : date('Y-m-d');
        $selected_time = isset($_GET['time']) ? sanitize_text_field($_GET['time']) : '';
        $selected_passengers = isset($_GET['passengers']) ? absint($_GET['passengers']) : 1;
        
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
                                <option value="<?php echo esc_attr($val); ?>" <?php selected($selected_route, $val); ?>><?php echo esc_html($label); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- 2. Pickup Point (Populated via AJAX) -->
                    <div class="wbbm_field_group">
                        <label><?php _e('Pickup Point', 'bus-booking-manager'); ?></label>
                        <select name="shuttle_pickup_point" id="shuttle_pickup_point" required class="form-control" <?php echo $selected_pickup ? '' : 'disabled'; ?>>
                            <option value=""><?php _e('Select Route First', 'bus-booking-manager'); ?></option>
                            <?php if ($selected_pickup) : ?>
                                <option value="<?php echo esc_attr($selected_pickup); ?>" selected><?php echo esc_html($selected_pickup); ?></option>
                            <?php endif; ?>
                        </select>
                    </div>

                    <!-- 3. Date -->
                    <div class="wbbm_field_group">
                        <label><?php _e('Date', 'bus-booking-manager'); ?></label>
                        <input type="date" name="shuttle_date" id="shuttle_date" required class="form-control" value="<?php echo esc_attr($selected_date); ?>">
                    </div>

                    <!-- 4. Time (Populated via AJAX based on Route + Date + Pickup Offset) -->
                    <div class="wbbm_field_group">
                        <label><?php _e('Time', 'bus-booking-manager'); ?></label>
                        <select name="shuttle_time" id="shuttle_time" required class="form-control" <?php echo $selected_time ? '' : 'disabled'; ?>>
                            <option value=""><?php _e('Select Date & Pickup', 'bus-booking-manager'); ?></option>
                            <?php if ($selected_time) : ?>
                                <option value="<?php echo esc_attr($selected_time); ?>" selected><?php echo esc_html($selected_time); ?></option>
                            <?php endif; ?>
                        </select>
                    </div>

                    <!-- 5. Passengers -->
                    <div class="wbbm_field_group">
                        <label><?php _e('Passengers', 'bus-booking-manager'); ?></label>
                        <input type="number" name="shuttle_qty" min="1" value="<?php echo esc_attr($selected_passengers); ?>" required class="form-control">
                    </div>

                    <!-- 6. Drop-off Stop (Populated via AJAX based on Route + Pickup Index) -->
                    <div class="wbbm_field_group">
                        <label><?php _e('Drop-off Stop', 'bus-booking-manager'); ?></label>
                        <select name="shuttle_dropoff_stop" id="shuttle_dropoff_stop" required class="form-control" <?php echo $selected_dropoff ? '' : 'disabled'; ?>>
                            <option value=""><?php _e('Select Pickup First', 'bus-booking-manager'); ?></option>
                            <?php if ($selected_dropoff) : ?>
                                <option value="<?php echo esc_attr($selected_dropoff); ?>" selected><?php echo esc_html($selected_dropoff); ?></option>
                            <?php endif; ?>
                        </select>
                    </div>
                     
                     <!-- 7. Drop-off Point (Populated via AJAX based on Dropoff Stop) -->
                     <div class="wbbm_field_group">
                        <label><?php _e('Drop-off Point', 'bus-booking-manager'); ?></label>
                         <select name="shuttle_dropoff_point" id="shuttle_dropoff_point" class="form-control" <?php echo $selected_dropoff ? '' : 'disabled'; ?>>
                            <option value=""><?php _e('Select Drop-off Stop First', 'bus-booking-manager'); ?></option>
                            <?php 
                            // Get dropoff_point from URL if available
                            $selected_dropoff_point = isset($_GET['shuttle_dropoff_point']) ? sanitize_text_field($_GET['shuttle_dropoff_point']) : '';
                            if ($selected_dropoff_point) : 
                            ?>
                                <option value="<?php echo esc_attr($selected_dropoff_point); ?>" selected><?php echo esc_html($selected_dropoff_point); ?></option>
                            <?php endif; ?>
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
                
                // Pre-selected values from PHP
                var preSelected = {
                    route: "<?php echo esc_js($selected_route); ?>",
                    pickup: "<?php echo esc_js($selected_pickup); ?>",
                    dropoff: "<?php echo esc_js($selected_dropoff); ?>",
                    dropoffPoint: "<?php echo esc_js($selected_dropoff_point); ?>",
                    time: "<?php echo esc_js($selected_time); ?>"
                };

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
                                // Populate and try to select pre-value
                                populateSelect('#shuttle_pickup_point', res.data, 'Select Pickup Point', preSelected.pickup);
                                $('#shuttle_pickup_point').prop('disabled', false);
                                
                                // Reset preSelected after use to avoid stickiness on manual changes
                                if (preSelected.pickup) {
                                    // Only trigger if we actually set a value
                                    if ($('#shuttle_pickup_point').val() === preSelected.pickup) {
                                         $('#shuttle_pickup_point').trigger('change');
                                    }
                                    preSelected.pickup = ''; 
                                }
                            }
                        }
                    });
                });

                // 2. Pickup Point OR Date Changed -> Get Times
                $('#shuttle_pickup_point, #shuttle_date').on('change', function() {
                    var routeVal = $('#shuttle_route_id').val();
                    var pickupVal = $('#shuttle_pickup_point').val(); 
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
                                populateSelect('#shuttle_time', res.data, 'Select Time', preSelected.time);
                                $('#shuttle_time').prop('disabled', false);
                                
                                if (preSelected.time) preSelected.time = '';
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
                                populateSelect('#shuttle_dropoff_point', res.data, 'Select Drop-off Point', preSelected.dropoffPoint);
                                $('#shuttle_dropoff_point').prop('disabled', false);
                                
                                if (preSelected.dropoffPoint) preSelected.dropoffPoint = '';
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
                                populateSelect('#shuttle_dropoff_stop', res.data, 'Select Drop-off Stop', preSelected.dropoff);
                                $('#shuttle_dropoff_stop').prop('disabled', false);
                                
                                if (preSelected.dropoff) {
                                    if ($('#shuttle_dropoff_stop').val() === preSelected.dropoff) {
                                        $('#shuttle_dropoff_stop').trigger('change');
                                    }
                                    preSelected.dropoff = '';
                                }
                            }
                        }
                    });
                }
                
                function resetFields(ids) {
                    ids.forEach(function(id) {
                        $('#' + id).prop('disabled', true).empty().append('<option>Wait...</option>');
                    });
                }
                
                
                function populateSelect(selector, options, placeholder, selectedVal = '') {
                    var $el = $(selector);
                    $el.empty().append('<option value="">' + placeholder + '</option>');
                    $.each(options, function(key, val) {
                        var isSelected = (selectedVal && selectedVal == key) ? 'selected' : '';
                        $el.append($('<option value="' + key + '" ' + isSelected + '>' + val + '</option>'));
                    });
                }
                
                // Initialize form with pre-filled values if present
                if (preSelected.route) {
                    // Trigger initial route change to start the cascade
                    setTimeout(function() {
                        $('#shuttle_route_id').trigger('change');
                    }, 100);
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

    /**
     * Render Search Results (Trip Summary)
     */
    private function render_search_results() {
        $route_id_val = isset($_GET['shuttle_route_id']) ? sanitize_text_field($_GET['shuttle_route_id']) : '';
        $pickup_val = isset($_GET['shuttle_pickup_point']) ? sanitize_text_field($_GET['shuttle_pickup_point']) : '';
        $date = isset($_GET['shuttle_date']) ? sanitize_text_field($_GET['shuttle_date']) : '';
        $time = isset($_GET['shuttle_time']) ? sanitize_text_field($_GET['shuttle_time']) : '';
        $passengers = isset($_GET['shuttle_qty']) ? absint($_GET['shuttle_qty']) : 1;
        $dropoff_stop = isset($_GET['shuttle_dropoff_stop']) ? sanitize_text_field($_GET['shuttle_dropoff_stop']) : '';
        $dropoff_point = isset($_GET['shuttle_dropoff_point']) ? sanitize_text_field($_GET['shuttle_dropoff_point']) : '';

        if (!$route_id_val || !$pickup_val || !$date || !$time) {
            echo '<p class="wbbm_error">' . __('Incomplete search criteria.', 'bus-booking-manager') . '</p>';
            $this->render_search_form(array());
            return;
        }

        list($post_id, $route_id) = explode('|', $route_id_val);
        $pickup_parts = explode('|', $pickup_val);
        // index|location|offset|point
        $pickup_loc = isset($pickup_parts[1]) ? $pickup_parts[1] : '';
        $pickup_point_name = isset($pickup_parts[3]) ? $pickup_parts[3] : '';

        $post = get_post($post_id);
        if (!$post) return;

        $routes = maybe_unserialize(get_post_meta($post_id, 'wbbm_shuttle_routes', true));
        $pricing = maybe_unserialize(get_post_meta($post_id, 'wbbm_shuttle_pricing', true));
        
        $selected_route = null;
        if ($routes) {
            foreach ($routes as $r) {
                if ($r['id'] == $route_id) {
                    $selected_route = $r;
                    break;
                }
            }
        }

        if (!$selected_route) return;

        // Calculate Price & Subtotal
        $price = 0;
        if (isset($pricing['routes'][$route_id][$pickup_loc][$dropoff_stop])) {
            $p = $pricing['routes'][$route_id][$pickup_loc][$dropoff_stop];
            $price = is_array($p) ? $p['oneway'] : $p;
        }
        $subtotal = $price * $passengers;

        // Calculate Arrival Time
        $arrival_time = '';
        if ($selected_route && isset($selected_route['stops'])) {
            $dropoff_offset = 0;
            foreach ($selected_route['stops'] as $stop) {
                if ($stop['location'] == $dropoff_stop) {
                    $dropoff_offset = intval($stop['time_offset']);
                    break;
                }
            }
            // We need to find the base time from the selected time and pickup offset
            $pickup_offset = intval($pickup_parts[2]);
            $base_time_str = date('h:i A', strtotime("-$pickup_offset minutes", strtotime($time)));
            $arrival_time = date('h:i A', strtotime("+$dropoff_offset minutes", strtotime($base_time_str)));
        }

        ?>
        <div class="wbbm_shuttle_booking_summary">
            <h3><?php _e('Review Your Trip', 'bus-booking-manager'); ?></h3>
            
            <div class="wbbm_trip_card">
                <div class="wbbm_trip_main">
                    <div class="wbbm_trip_header">
                        <h4><?php echo esc_html($post->post_title); ?> <small>(<?php echo esc_html($selected_route['name']); ?>)</small></h4>
                    </div>
                    
                    <div class="wbbm_trip_details">
                        <div class="wbbm_detail_item">
                            <span class="dashicons dashicons-calendar"></span>
                            <strong><?php echo date_i18n(get_option('date_format'), strtotime($date)); ?></strong>
                        </div>
                        <div class="wbbm_detail_item">
                            <?php
                            // Calculate available seats for this shuttle
                            $available_seats = wbbm_shuttle_available_seats($post_id, $date, $route_id, $pickup_loc, $dropoff_stop);
                            $seat_class = $available_seats <= 3 ? 'wbbm_low_seats' : 'wbbm_available_seats';
                            ?>
                            <span class="dashicons dashicons-tickets-alt"></span>
                            <strong class="<?php echo esc_attr($seat_class); ?>">
                                <?php 
                                if ($available_seats > 0) {
                                    printf(_n('%d Seat Available', '%d Seats Available', $available_seats, 'bus-booking-manager'), $available_seats);
                                } else {
                                    _e('Sold Out', 'bus-booking-manager');
                                }
                                ?>
                            </strong>
                        </div>
                        <div class="wbbm_detail_item">
                            <span class="dashicons dashicons-groups"></span>
                            <strong><?php printf(_n('%d Passenger', '%d Passengers', $passengers, 'bus-booking-manager'), $passengers); ?></strong>
                        </div>
                    </div>

                    <div class="wbbm_route_visual">
                        <div class="wbbm_stop_info">
                            <span class="wbbm_dot start"></span>
                            <div class="wbbm_stop_content">
                                <div class="wbbm_stop_time"><?php echo esc_html($time); ?></div>
                                <strong><?php echo esc_html($pickup_loc); ?></strong>
                                <?php if ($pickup_point_name && $pickup_point_name !== 'main') : ?>
                                    <div class="wbbm_sub_point"><?php echo esc_html($pickup_point_name); ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="wbbm_line"></div>
                        <div class="wbbm_stop_info">
                            <span class="wbbm_dot end"></span>
                            <div class="wbbm_stop_content">
                                <div class="wbbm_stop_time"><?php echo esc_html($arrival_time); ?></div>
                                <strong><?php echo esc_html($dropoff_stop); ?></strong>
                                <?php if ($dropoff_point && $dropoff_point !== 'main') : ?>
                                    <div class="wbbm_sub_point"><?php echo esc_html($dropoff_point); ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="wbbm_trip_action">
                    <?php
                    $product_id = get_post_meta($post_id, 'link_wc_product', true);
                    if ($product_id) :
                    ?>
                    <form action="<?php echo esc_url(wc_get_cart_url()); ?>" method="POST" class="wbbm_shuttle_add_to_cart_form">
                        <input type="hidden" name="add-to-cart" value="<?php echo esc_attr($product_id); ?>">
                        <input type="hidden" name="shuttle_id" value="<?php echo esc_attr($post_id); ?>">
                        <input type="hidden" name="route_id" value="<?php echo esc_attr($route_id); ?>">
                        <input type="hidden" name="pickup" value="<?php echo esc_attr($pickup_loc); ?>">
                        <input type="hidden" name="dropoff" value="<?php echo esc_attr($dropoff_stop); ?>">
                        <input type="hidden" name="date" value="<?php echo esc_attr($date); ?>">
                        <input type="hidden" name="time" value="<?php echo esc_attr($time); ?>">
                        <input type="hidden" name="passengers" value="<?php echo esc_attr($passengers); ?>">
                        <input type="hidden" name="pickup_point" value="<?php echo esc_attr($pickup_point_name); ?>">
                        <input type="hidden" name="dropoff_point" value="<?php echo esc_attr($dropoff_point); ?>">
                        <?php wp_nonce_field('wbbm_shuttle_add_to_cart', 'wbbm_shuttle_nonce'); ?>

                        <div class="wbbm_pricing_summary">
                            <?php
                            // Calculate price server-side
                            $pricing = maybe_unserialize(get_post_meta($post_id, 'wbbm_shuttle_pricing', true));
                            $price_per_seat = 0;
                            
                            if ($pricing && isset($pricing['routes'][$route_id][$pickup_loc][$dropoff_stop])) {
                                $p = $pricing['routes'][$route_id][$pickup_loc][$dropoff_stop];
                                $price_per_seat = is_array($p) ? (float)$p['oneway'] : (float)$p;
                            }
                            
                            $subtotal = $price_per_seat * $passengers;
                            ?>
                            <div class="wbbm_price_row">
                                <span><?php _e('Price per seat:', 'bus-booking-manager'); ?></span>
                                <strong><?php echo function_exists('wc_price') ? wc_price($price_per_seat) : '$' . number_format($price_per_seat, 2); ?></strong>
                            </div>
                            <div class="wbbm_price_row wbbm_subtotal">
                                <span><?php _e('Subtotal:', 'bus-booking-manager'); ?></span>
                                <strong><?php echo function_exists('wc_price') ? wc_price($subtotal) : '$' . number_format($subtotal, 2); ?></strong>
                            </div>
                        </div>
                        
                        <button type="submit" class="wbbm_book_btn">
                            <?php _e('Proceed to Booking', 'bus-booking-manager'); ?>
                        </button>
                    </form>
                    <?php else : ?>
                        <p class="wbbm_error"><?php _e('Booking currently unavailable.', 'bus-booking-manager'); ?></p>
                    <?php endif; ?>
                    <?php
                    // Preserve search parameters when going back
                    $back_url = remove_query_arg('shuttle_search');
                    $back_url = add_query_arg(array(
                        'route' => $route_id,
                        'pickup' => $pickup_loc,
                        'dropoff' => $dropoff_stop,
                        'date' => $date,
                        'time' => $time,
                        'passengers' => $passengers
                    ), $back_url);
                    ?>
                    <a href="<?php echo esc_url($back_url); ?>" class="wbbm_back_link"><?php _e('← Change Selection', 'bus-booking-manager'); ?></a>
                </div>
            </div>
        </div>

        <style>
            .wbbm_shuttle_booking_summary { max-width: 800px; margin: 30px auto; font-family: 'Segoe UI', sans-serif; }
            .wbbm_shuttle_booking_summary h3 { margin-bottom: 20px; text-align: center; color: #333; }
            .wbbm_trip_card { 
                background: #fff; border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.08); 
                display: flex; overflow: hidden; border: 1px solid #eee;
            }
            .wbbm_trip_main { flex: 2; padding: 25px; }
            .wbbm_trip_action { flex: 1; padding: 25px; background: #fcfcfc; border-left: 1px solid #eee; display: flex; flex-direction: column; justify-content: center; }
            .wbbm_trip_header h4 { margin: 0 0 15px 0; font-size: 20px; color: #0073aa; }
            .wbbm_trip_details { display: flex; gap: 20px; margin-bottom: 25px; color: #666; font-size: 14px; }
            .wbbm_detail_item { display: flex; align-items: center; gap: 5px; }
            .wbbm_detail_item .dashicons { font-size: 18px; height: 18px; width: 18px; color: #999; }
            
            .wbbm_route_visual { display: flex; align-items: flex-start; gap: 15px; margin-top: 10px; }
            .wbbm_stop_info { position: relative; display: flex; align-items: flex-start; gap: 10px; flex: 1; }
            .wbbm_dot { height: 12px; width: 12px; border-radius: 50%; border: 2px solid #0073aa; background: #fff; margin-top: 25px; z-index: 2; }
            .wbbm_dot.start { background: #0073aa; }
            .wbbm_line { height: 2px; background: #e0e0e0; flex: 0 0 40px; margin-top: 31px; position: relative; }
            .wbbm_stop_time { font-size: 13px; color: #0073aa; font-weight: 700; margin-bottom: 2px; }
            .wbbm_stop_content strong { display: block; font-size: 16px; color: #333; }
            .wbbm_sub_point { font-size: 12px; color: #888; margin-top: 2px; }

            .wbbm_pricing_box { margin-bottom: 20px; }
            .wbbm_price_row { display: flex; justify-content: space-between; font-size: 14px; color: #666; margin-bottom: 5px; }
            .wbbm_price_row.subtotal { margin-top: 10px; padding-top: 10px; border-top: 1px dashed #ddd; color: #333; font-size: 16px; font-weight: 700; }
            .wbbm_price_row.subtotal strong { color: #28a745; font-size: 22px; }
            
            .wbbm_book_btn { 
                background: #28a745; color: #fff; text-decoration: none; padding: 12px 20px; 
                border-radius: 6px; font-weight: 600; display: block; transition: background 0.3s;
                margin-bottom: 15px; text-align: center;
                width: 100%;
            }
            .wbbm_book_btn:hover { background: #218838; color: #fff; }
            .wbbm_back_link { font-size: 13px; color: #0073aa; text-decoration: none; text-align: center; display: block; }
            
            @media (max-width: 600px) {
                .wbbm_trip_card { flex-direction: column; }
                .wbbm_trip_action { border-left: none; border-top: 1px solid #eee; }
            }
        </style>
        <?php
    }
}

new ShuttleSearchClass();
