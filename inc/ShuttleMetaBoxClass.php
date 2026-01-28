<?php
if (!defined('ABSPATH')) exit;  // if direct access

/**
 * Shuttle Meta Box Class
 * 
 * Handles all meta boxes for shuttle configuration:
 * - Basic settings (capacity, vehicle type, shuttle type)
 * - Boarding points management
 * - Dropping points management
 * - Pricing configuration
 * - Schedule & availability
 * - Extra services
 */

class ShuttleMetaBoxClass
{
    public function __construct()
    {
        // Add meta boxes
        add_action('add_meta_boxes', array($this, 'wbbm_shuttle_add_meta_box'));
        
        // Save meta box data
        add_action('save_post_wbbm_shuttle', array($this, 'wbbm_shuttle_meta_save'), 10, 2);
        
        // Remove default taxonomy meta boxes from sidebar
        add_action('admin_init', array($this, 'wbbm_shuttle_remove_sidebar_meta_box'));
    }

    /**
     * Add shuttle meta boxes
     */
    public function wbbm_shuttle_add_meta_box()
    {
        $cpt_label = sanitize_text_field(wbbm_get_option('wbbm_shuttle_cpt_label', 'wbbm_general_setting_sec', __('Shuttle', 'bus-booking-manager')));
        
        add_meta_box(
            'wbbm-shuttle-settings-meta',
            $cpt_label . ' ' . __('Configuration', 'bus-booking-manager'),
            array($this, 'wbbm_shuttle_meta_box_cb'),
            'wbbm_shuttle',
            'normal',
            'high'
        );
    }

    /**
     * Meta box callback - renders the tabbed interface
     */
    public function wbbm_shuttle_meta_box_cb($post)
    {
        $post_id = $post->ID;
        $cpt_label = sanitize_text_field(wbbm_get_option('wbbm_shuttle_cpt_label', 'wbbm_general_setting_sec', __('Shuttle', 'bus-booking-manager')));
        
        wp_nonce_field('wbbm_shuttle_settings_nonce', 'wbbm_shuttle_settings_nonce');
        ?>
        <div class="wbtm_style mp_event_all_meta_in_tab mp_event_tab_area">
            <div class="mp_tab_menu">
                <ul>
                    <li data-target-tabs="#wbbm_shuttle_basic" class="active">
                        <i class="fas fa-cog"></i> <?php echo esc_html(__('Basic Settings', 'bus-booking-manager')); ?>
                    </li>
                    <li data-target-tabs="#wbbm_shuttle_routing">
                        <i class="fas fa-route"></i> <?php echo esc_html(__('Route Management', 'bus-booking-manager')); ?>
                    </li>
                    <li data-target-tabs="#wbbm_shuttle_pricing">
                        <i class="fas fa-dollar-sign"></i> <?php echo esc_html(__('Pricing', 'bus-booking-manager')); ?>
                    </li>
                    <li data-target-tabs="#wbbm_shuttle_schedule">
                        <i class="far fa-calendar-check"></i> <?php echo esc_html(__('Schedule', 'bus-booking-manager')); ?>
                    </li>
                    <li data-target-tabs="#wbbm_shuttle_extra">
                        <i class="fas fa-plus-circle"></i> <?php echo esc_html(__('Extra Services', 'bus-booking-manager')); ?>
                    </li>
                </ul>
            </div>
            <div class="mp_tab_details">
                <input type="hidden" name="wbbm_shuttle_post_id" value="<?php echo esc_attr($post_id); ?>"/>
                <?php
                $this->wbbm_shuttle_basic_settings($post_id);
                $this->wbbm_shuttle_routing($post_id);
                $this->wbbm_shuttle_pricing($post_id);
                $this->wbbm_shuttle_schedule($post_id);
                $this->wbbm_shuttle_extra_services($post_id);
                ?>
            </div>
        </div>
        <?php
    }

    /**
     * Basic Settings Tab
     */
    public function wbbm_shuttle_basic_settings($post_id)
    {
        $shuttle_type = wp_get_post_terms($post_id, 'wbbm_shuttle_type', array('fields' => 'ids'));
        $shuttle_cat = wp_get_post_terms($post_id, 'wbbm_shuttle_cat', array('fields' => 'ids'));
        $capacity = get_post_meta($post_id, 'wbbm_shuttle_capacity', true);
        $vehicle_number = get_post_meta($post_id, 'wbbm_shuttle_vehicle_number', true);
        
        // Get all shuttle types
        $shuttle_types = get_terms(array(
            'taxonomy' => 'wbbm_shuttle_type',
            'hide_empty' => false
        ));
        
        // Get all vehicle categories
        $vehicle_cats = get_terms(array(
            'taxonomy' => 'wbbm_shuttle_cat',
            'hide_empty' => false
        ));
        
        ?>
        <div class="mp_tab_item tab-content active" data-tab-item="#wbbm_shuttle_basic">
            <h3 class="wbbm_mp_tab_item_heading">
                <img src="<?php echo esc_url(WBTM_PLUGIN_URL . 'images/bus_arrow_left.png'); ?>"/>
                <?php echo esc_html(__('Basic Shuttle Settings', 'bus-booking-manager')); ?>
            </h3>
            
            <div class="mp_settings_panel">
                <div class="mp_settings_panel_body">
                    
                    <!-- Shuttle Type -->
                    <div class="mpStyle">
                        <label for="wbbm_shuttle_type">
                            <?php echo esc_html(__('Shuttle Type', 'bus-booking-manager')); ?>
                            <span class="required">*</span>
                        </label>
                        <select name="wbbm_shuttle_type" id="wbbm_shuttle_type" required class="formControl">
                            <option value=""><?php echo esc_html(__('Select Type', 'bus-booking-manager')); ?></option>
                            <?php foreach ($shuttle_types as $type) : ?>
                                <option value="<?php echo esc_attr($type->term_id); ?>" <?php selected(in_array($type->term_id, $shuttle_type)); ?>>
                                    <?php echo esc_html($type->name); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <p class="description"><?php echo esc_html(__('Select shuttle type (Airport, Hotel, Corporate)', 'bus-booking-manager')); ?></p>
                    </div>
                    
                    <!-- Vehicle Category -->
                    <div class="mpStyle">
                        <label for="wbbm_shuttle_category">
                            <?php echo esc_html(__('Vehicle Category', 'bus-booking-manager')); ?>
                        </label>
                        <select name="wbbm_shuttle_category" id="wbbm_shuttle_category" class="formControl">
                            <option value=""><?php echo esc_html(__('Select Category', 'bus-booking-manager')); ?></option>
                            <?php foreach ($vehicle_cats as $cat) : ?>
                                <option value="<?php echo esc_attr($cat->term_id); ?>" <?php selected(in_array($cat->term_id, $shuttle_cat)); ?>>
                                    <?php echo esc_html($cat->name); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <p class="description"><?php echo esc_html(__('Select vehicle type (Van, Minibus, Bus, etc.)', 'bus-booking-manager')); ?></p>
                    </div>
                    
                    <!-- Vehicle Number -->
                    <div class="mpStyle">
                        <label for="wbbm_shuttle_vehicle_number">
                            <?php echo esc_html(__('Vehicle Number / ID', 'bus-booking-manager')); ?>
                        </label>
                        <input type="text" 
                               name="wbbm_shuttle_vehicle_number" 
                               id="wbbm_shuttle_vehicle_number" 
                               class="formControl" 
                               value="<?php echo esc_attr($vehicle_number); ?>" 
                               placeholder="<?php echo esc_attr(__('e.g., SH-001', 'bus-booking-manager')); ?>"/>
                        <p class="description"><?php echo esc_html(__('Vehicle identification number', 'bus-booking-manager')); ?></p>
                    </div>
                    
                    <!-- Maximum Capacity -->
                    <div class="mpStyle">
                        <label for="wbbm_shuttle_capacity">
                            <?php echo esc_html(__('Maximum Capacity (Seats)', 'bus-booking-manager')); ?>
                            <span class="required">*</span>
                        </label>
                        <input type="number" 
                               name="wbbm_shuttle_capacity" 
                               id="wbbm_shuttle_capacity" 
                               class="formControl" 
                               value="<?php echo esc_attr($capacity); ?>" 
                               min="1" 
                               required 
                               placeholder="<?php echo esc_attr(__('e.g., 12', 'bus-booking-manager')); ?>"/>
                        <p class="description"><?php echo esc_html(__('Total number of seats available', 'bus-booking-manager')); ?></p>
                    </div>
                    
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Route Management Tab
     */
    public function wbbm_shuttle_routing($post_id)
    {
        $routes = maybe_unserialize(get_post_meta($post_id, 'wbbm_shuttle_routes', true)) ?: array();
        
        // Get all shuttle stops for dropdown
        $all_stops = get_terms(array(
            'taxonomy' => 'wbbm_shuttle_stops',
            'hide_empty' => false
        ));
        
        ?>
        <div class="mp_tab_item" data-tab-item="#wbbm_shuttle_routing">
            <h3 class="wbbm_mp_tab_item_heading">
                <img src="<?php echo esc_url(WBTM_PLUGIN_URL . 'images/bus_arrow_left.png'); ?>"/>
                <?php echo esc_html(__('Route Management', 'bus-booking-manager')); ?>
            </h3>
            
            <div class="mp_settings_panel">
                <div class="mp_settings_panel_body">
                    
                    <p class="description" style="margin-bottom: 20px;">
                        <?php echo esc_html(__('Configure routes for this shuttle service. Each route can have multiple intermediate stops.', 'bus-booking-manager')); ?>
                    </p>
                    
                    <div id="wbbm_routes_container">
                        <?php
                        if (!empty($routes)) {
                            foreach ($routes as $index => $route) {
                                $this->render_route_row($index, $route, $all_stops);
                            }
                        }
                        ?>
                    </div>
                    
                    <button type="button" class="button button-primary wbbm_add_route">
                         <i class="fas fa-plus"></i> <?php echo esc_html(__('Add New Route', 'bus-booking-manager')); ?>
                    </button>
                    
                    <!-- Hidden Templates -->
                    <script type="text/html" id="wbbm_route_template">
                        <?php $this->render_route_row('{{route_index}}', array(), $all_stops); ?>
                    </script>
                    
                    <script type="text/html" id="wbbm_stop_template">
                        <?php $this->render_route_stop_row('{{route_index}}', '{{stop_index}}', array(), $all_stops); ?>
                    </script>
                    
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Render a single route row
     */
    private function render_route_row($index, $data, $all_stops)
    {
        $name = isset($data['name']) ? $data['name'] : '';
        $type = isset($data['type']) ? $data['type'] : 'one-way';
        $stops = isset($data['stops']) ? $data['stops'] : array();
        
        // Ensure ID
        $route_id = isset($data['id']) ? $data['id'] : ($index === '{{route_index}}' ? '{{route_index}}' : uniqid('route_'));
        
        ?>
        <div class="wbbm_route_row <?php echo !is_string($index) ? 'active' : ''; ?>" data-index="<?php echo esc_attr($index); ?>">
            <input type="hidden" name="wbbm_shuttle_routes[<?php echo esc_attr($index); ?>][id]" value="<?php echo esc_attr($route_id); ?>">
            <div class="wbbm_route_header">
                <div>
                    <span class="dashicons dashicons-location-alt"></span>
                    <strong class="wbbm_route_title_text"><?php echo $name ? esc_html($name) : 'New Route'; ?></strong>
                </div>
                <div class="wbbm_route_actions">
                    <button type="button" class="button wbbm_remove_route text-danger"><?php echo esc_html(__('Remove Route', 'bus-booking-manager')); ?></button>
                    <span class="dashicons dashicons-arrow-down-alt2"></span>
                </div>
            </div>
            
            <div class="wbbm_route_body">
                <div class="wbbm_route_fields_grid">
                    <div class="wbbm_shuttle_field">
                        <label><?php echo esc_html(__('Route Name', 'bus-booking-manager')); ?></label>
                        <input type="text" 
                               name="wbbm_shuttle_routes[<?php echo esc_attr($index); ?>][name]" 
                               class="formControl wbbm_route_name_input" 
                               value="<?php echo esc_attr($name); ?>" 
                               placeholder="<?php echo esc_attr(__('e.g. Airport to Downtown', 'bus-booking-manager')); ?>"/>
                    </div>
                    <div class="wbbm_shuttle_field">
                        <label><?php echo esc_html(__('Route Type', 'bus-booking-manager')); ?></label>
                        <select name="wbbm_shuttle_routes[<?php echo esc_attr($index); ?>][type]" class="formControl">
                            <option value="one-way" <?php selected($type, 'one-way'); ?>><?php echo esc_html(__('One Way', 'bus-booking-manager')); ?></option>
                            <option value="round-trip" <?php selected($type, 'round-trip'); ?>><?php echo esc_html(__('Round Trip', 'bus-booking-manager')); ?></option>
                        </select>
                    </div>
                </div>
                
                <h4 style="margin-top: 0;"><?php echo esc_html(__('Stops', 'bus-booking-manager')); ?></h4>
                <div class="wbbm_route_stops_wrapper">
                    <div class="wbbm_route_stops_header" style="display: flex; gap: 10px; margin-bottom: 5px; font-weight: bold; padding-left: 30px;">
                        <div style="flex: 2;"><?php echo esc_html(__('Location', 'bus-booking-manager')); ?></div>
                        <div style="flex: 1;"><?php echo esc_html(__('Time Offset (min)', 'bus-booking-manager')); ?></div>
                        <div style="flex: 1;"><?php echo esc_html(__('Distance (km)', 'bus-booking-manager')); ?></div>
                        <div style="width: 40px;"></div>
                    </div>
                    
                    <div class="wbbm_route_stops_container">
                        <?php
                        if (!empty($stops)) {
                            foreach ($stops as $stop_idx => $stop) {
                                $this->render_route_stop_row($index, $stop_idx, $stop, $all_stops);
                            }
                        }
                        ?>
                    </div>
                    
                    <button type="button" class="button wbbm_add_route_stop" style="margin-top: 10px;">
                        <i class="fas fa-plus"></i> <?php echo esc_html(__('Add Stop', 'bus-booking-manager')); ?>
                    </button>
                    <p class="description"><?php echo esc_html(__('Define stops in order (Origin -> Intermediates -> Destination)', 'bus-booking-manager')); ?></p>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Render a single stop row
     */
    private function render_route_stop_row($route_index, $stop_index, $data, $all_stops)
    {
        $location = isset($data['location']) ? $data['location'] : '';
        $time_offset = isset($data['time_offset']) ? $data['time_offset'] : '0';
        $distance = isset($data['distance']) ? $data['distance'] : '0';
        ?>
        <div class="wbbm_route_stop_row">
            <span class="wbbm_stop_drag_handle dashicons dashicons-move"></span>
            
            <div style="flex: 2;">
                <select name="wbbm_shuttle_routes[<?php echo esc_attr($route_index); ?>][stops][<?php echo esc_attr($stop_index); ?>][location]" class="formControl" required>
                    <option value=""><?php echo esc_html(__('Select Stop', 'bus-booking-manager')); ?></option>
                    <?php foreach ($all_stops as $stop) : ?>
                        <option value="<?php echo esc_attr($stop->name); ?>" <?php selected($location, $stop->name); ?>>
                            <?php echo esc_html($stop->name); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div style="flex: 1;">
                <input type="number" 
                       name="wbbm_shuttle_routes[<?php echo esc_attr($route_index); ?>][stops][<?php echo esc_attr($stop_index); ?>][time_offset]" 
                       class="formControl" 
                       value="<?php echo esc_attr($time_offset); ?>"
                       min="0"
                       placeholder="Min"/>
            </div>
            
            <div style="flex: 1;">
                 <input type="number" 
                       name="wbbm_shuttle_routes[<?php echo esc_attr($route_index); ?>][stops][<?php echo esc_attr($stop_index); ?>][distance]" 
                       class="formControl" 
                       value="<?php echo esc_attr($distance); ?>"
                       min="0" step="0.1"
                       placeholder="Km"/>
            </div>
            
            <div style="width: 40px; text-align: right;">
                <button type="button" class="button wbbm_remove_route_stop text-danger">
                    <span class="dashicons dashicons-trash"></span>
                </button>
            </div>
        </div>
        <?php
    }

    /**
     * Pricing Tab - Stop-to-Stop Matrix
     */
    public function wbbm_shuttle_pricing($post_id)
    {
        $routes = maybe_unserialize(get_post_meta($post_id, 'wbbm_shuttle_routes', true)) ?: array();
        $pricing = maybe_unserialize(get_post_meta($post_id, 'wbbm_shuttle_pricing', true)) ?: array();
        
        ?>
        <div class="mp_tab_item" data-tab-item="#wbbm_shuttle_pricing">
            <h3 class="wbbm_mp_tab_item_heading">
                <img src="<?php echo esc_url(WBTM_PLUGIN_URL . 'images/bus_arrow_left.png'); ?>"/>
                <?php echo esc_html(__('Shuttle Pricing (Stop-to-Stop)', 'bus-booking-manager')); ?>
            </h3>
            
            <div class="mp_settings_panel">
                <div class="mp_settings_panel_body">
                    <?php if (empty($routes)): ?>
                        <div class="notice notice-warning inline" style="margin: 0;"><p><?php _e('Please define and save Routes first to configure pricing.', 'bus-booking-manager'); ?></p></div>
                    <?php else: ?>
                        <p class="description"><?php echo esc_html(__('Set prices for each stop-to-stop combination. Leaving a field empty implies service is not available for that segment.', 'bus-booking-manager')); ?></p>
                        
                        <?php foreach($routes as $route): 
                                $route_id = isset($route['id']) ? $route['id'] : '';
                                if(!$route_id) continue;
                                
                                $route_stops = isset($route['stops']) ? $route['stops'] : array();
                                if(count($route_stops) < 2) continue;
                        ?>
                            <div class="wbbm_route_pricing_block" style="margin-bottom: 30px; border: 1px solid #ddd; padding: 15px; background: #fff;">
                                <h4><?php printf(esc_html__('Route: %s', 'bus-booking-manager'), esc_html($route['name'])); ?></h4>
                                <div style="overflow-x: auto;">
                                    <table class="widefat striped">
                                       <thead>
                                           <tr>
                                               <th><?php _e('From \ To', 'bus-booking-manager'); ?></th>
                                               <?php for($i=1; $i<count($route_stops); $i++) { echo '<th>'.esc_html($route_stops[$i]['location']).'</th>'; } ?>
                                           </tr>
                                       </thead>
                                       <tbody>
                                           <?php 
                                           for($i=0; $i<count($route_stops)-1; $i++) {
                                               $origin = $route_stops[$i]['location'];
                                               echo '<tr>';
                                               echo '<th>'.esc_html($origin).'</th>';
                                               
                                               for($j=1; $j<count($route_stops); $j++) {
                                                   if ($j > $i) {
                                                       $dest = $route_stops[$j]['location'];
                                                       $val = '';
                                                        if(isset($pricing['routes'][$route_id][$origin][$dest])) {
                                                            $val = $pricing['routes'][$route_id][$origin][$dest];
                                                        }
                                                        
                                                        $field_name = 'wbbm_shuttle_pricing[routes]['.$route_id.']['.esc_attr($origin).']['.esc_attr($dest).']';
                                                        
                                                        echo '<td>';
                                                        echo '<input type="number" step="0.01" style="width: 80px;" name="'.$field_name.'" value="'.esc_attr($val).'" placeholder="0.00">';
                                                        echo '</td>';
                                                   } else {
                                                       echo '<td>-</td>';
                                                   }
                                               }
                                               echo '</tr>';
                                           }
                                           ?>
                                       </tbody>
                                    </table>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Schedule Tab
     */
    public function wbbm_shuttle_schedule($post_id)
    {
        $routes = maybe_unserialize(get_post_meta($post_id, 'wbbm_shuttle_routes', true)) ?: array();
        $schedule = maybe_unserialize(get_post_meta($post_id, 'wbbm_shuttle_schedule', true)) ?: array();
        
        ?>
        <div class="mp_tab_item" data-tab-item="#wbbm_shuttle_schedule">
            <h3 class="wbbm_mp_tab_item_heading">
                <img src="<?php echo esc_url(WBTM_PLUGIN_URL . 'images/bus_arrow_left.png'); ?>"/>
                <?php echo esc_html(__('Route Schedules', 'bus-booking-manager')); ?>
            </h3>
            
            <div class="mp_settings_panel">
                <div class="mp_settings_panel_body">
                    <?php if (empty($routes)): ?>
                        <div class="notice notice-warning inline" style="margin: 0;"><p><?php _e('Please define and save Routes first to configure schedules.', 'bus-booking-manager'); ?></p></div>
                    <?php else: ?>
                        <p class="description"><?php echo esc_html(__('Define departure times for each route. You can set different times for different days of the week.', 'bus-booking-manager')); ?></p>
                        
                        <?php foreach($routes as $route): 
                                $route_id = isset($route['id']) ? $route['id'] : '';
                                if(!$route_id) continue;
                                $route_schedule = isset($schedule[$route_id]) ? $schedule[$route_id] : array();
                        ?>
                            <div class="wbbm_route_schedule_block" data-route-id="<?php echo esc_attr($route_id); ?>" style="margin-bottom: 20px; border: 1px solid #ddd; padding: 15px; background: #fff;">
                                <h4><?php printf(esc_html__('Schedule for: %s', 'bus-booking-manager'), esc_html($route['name'])); ?></h4>
                                
                                <div class="wbbm_schedule_rows">
                                    <?php 
                                    if(!empty($route_schedule)) {
                                        foreach($route_schedule as $idx => $time_data) {
                                            $this->render_schedule_row($route_id, $idx, $time_data);
                                        }
                                    }
                                    ?>
                                </div>
                                
                                <button type="button" class="button wbbm_add_schedule_time">
                                    <i class="fas fa-plus"></i> <?php echo esc_html(__('Add Departure Time', 'bus-booking-manager')); ?>
                                </button>
                            </div>
                        <?php endforeach; ?>
                        
                        <!-- Hidden Template -->
                        <script type="text/html" id="wbbm_schedule_row_template">
                            <?php $this->render_schedule_row('{{route_id}}', '{{time_index}}', array()); ?>
                        </script>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php
    }

    private function render_schedule_row($route_id, $index, $data)
    {
        $time = isset($data['time']) ? $data['time'] : '';
        $days = isset($data['days']) ? $data['days'] : array();
        
        $days_of_week = array(
            'mon' => __('Mon', 'bus-booking-manager'),
            'tue' => __('Tue', 'bus-booking-manager'),
            'wed' => __('Wed', 'bus-booking-manager'),
            'thu' => __('Thu', 'bus-booking-manager'),
            'fri' => __('Fri', 'bus-booking-manager'),
            'sat' => __('Sat', 'bus-booking-manager'),
            'sun' => __('Sun', 'bus-booking-manager'),
        );
        ?>
        <div class="wbbm_schedule_row" style="display: flex; gap: 15px; border-bottom: 1px dashed #eee; padding-bottom: 10px; margin-bottom: 10px; align-items: center;">
            <div style="width: 150px;">
                <label style="display: block; font-size: 11px; margin-bottom: 2px;"><?php _e('Departure Time', 'bus-booking-manager'); ?></label>
                <input type="text" 
                       name="wbbm_shuttle_schedule[<?php echo esc_attr($route_id); ?>][<?php echo esc_attr($index); ?>][time]" 
                       class="formControl wbbm_time_picker" 
                       value="<?php echo esc_attr($time); ?>" 
                       placeholder="e.g. 08:00 AM" required>
            </div>
            <div style="flex: 1;">
                <label style="display: block; font-size: 11px; margin-bottom: 2px;"><?php _e('Operating Days', 'bus-booking-manager'); ?></label>
                <div class="wbbm_schedule_days" style="display: flex; gap: 10px; flex-wrap: wrap;">
                    <?php foreach ($days_of_week as $key => $label): ?>
                        <label style="font-size: 12px;">
                            <input type="checkbox" 
                                   name="wbbm_shuttle_schedule[<?php echo esc_attr($route_id); ?>][<?php echo esc_attr($index); ?>][days][]" 
                                   value="<?php echo esc_attr($key); ?>" 
                                   <?php checked(in_array($key, $days)); ?>>
                            <?php echo esc_html($label); ?>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>
            <div>
                 <button type="button" class="button wbbm_remove_schedule_row text-danger">
                    <span class="dashicons dashicons-trash" style="margin-top: 5px;"></span>
                </button>
            </div>
        </div>
        <?php
    }

    /**
     * Extra Services Tab - Placeholder for now
     */
    public function wbbm_shuttle_extra_services($post_id)
    {
        ?>
        <div class="mp_tab_item" data-tab-item="#wbbm_shuttle_extra">
            <h3 class="wbbm_mp_tab_item_heading">
                <img src="<?php echo esc_url(WBTM_PLUGIN_URL . 'images/bus_arrow_left.png'); ?>"/>
                <?php echo esc_html(__('Extra Services', 'bus-booking-manager')); ?>
            </h3>
            
            <div class="mp_settings_panel">
                <div class="mp_settings_panel_body">
                    <p><?php echo esc_html(__('Extra services configuration will be added in the next phase.', 'bus-booking-manager')); ?></p>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Save shuttle meta data
     */
    public function wbbm_shuttle_meta_save($post_id, $post)
    {
        // Security checks
        if (!isset($_POST['wbbm_shuttle_settings_nonce']) || 
            !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['wbbm_shuttle_settings_nonce'])), 'wbbm_shuttle_settings_nonce')) {
            return;
        }

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        // Save shuttle type
        if (isset($_POST['wbbm_shuttle_type'])) {
            $shuttle_type = absint($_POST['wbbm_shuttle_type']);
            wp_set_post_terms($post_id, array($shuttle_type), 'wbbm_shuttle_type', false);
        }

        // Save vehicle category
        if (isset($_POST['wbbm_shuttle_category'])) {
            $shuttle_cat = absint($_POST['wbbm_shuttle_category']);
            wp_set_post_terms($post_id, array($shuttle_cat), 'wbbm_shuttle_cat', false);
        }

        // Save vehicle number
        if (isset($_POST['wbbm_shuttle_vehicle_number'])) {
            update_post_meta($post_id, 'wbbm_shuttle_vehicle_number', sanitize_text_field(wp_unslash($_POST['wbbm_shuttle_vehicle_number'])));
        }

        // Save capacity
        if (isset($_POST['wbbm_shuttle_capacity'])) {
            update_post_meta($post_id, 'wbbm_shuttle_capacity', absint($_POST['wbbm_shuttle_capacity']));
        }

        // Save Routes
        $routes = array();
        if (isset($_POST['wbbm_shuttle_routes']) && is_array($_POST['wbbm_shuttle_routes'])) {
            foreach ($_POST['wbbm_shuttle_routes'] as $route) {
                if (!empty($route['name'])) {
                    $clean_route = array(
                        'name' => sanitize_text_field($route['name']),
                        'type' => isset($route['type']) ? sanitize_text_field($route['type']) : 'one-way',
                        'id'   => isset($route['id']) ? sanitize_text_field($route['id']) : uniqid('route_'),
                        'stops' => array()
                    );

                    if (isset($route['stops']) && is_array($route['stops'])) {
                        foreach ($route['stops'] as $stop) {
                             $clean_route['stops'][] = array(
                                'location' => sanitize_text_field($stop['location']),
                                'time_offset' => sanitize_text_field($stop['time_offset']),
                                'distance' => sanitize_text_field($stop['distance'])
                             );
                        }
                    }
                    $routes[] = $clean_route;
                }
            }
        }
        update_post_meta($post_id, 'wbbm_shuttle_routes', $routes);
        
        // Save Pricing
        $pricing = array();
        if (isset($_POST['wbbm_shuttle_pricing']) && is_array($_POST['wbbm_shuttle_pricing'])) {
             // Structure: ['routes' => [ route_id => [ origin => [ dest => price ] ] ]]
             if (isset($_POST['wbbm_shuttle_pricing']['routes'])) {
                 foreach($_POST['wbbm_shuttle_pricing']['routes'] as $r_id => $stops) {
                     foreach($stops as $origin => $dests) {
                         foreach($dests as $dest => $price) {
                             if($price !== '') {
                                $pricing['routes'][$r_id][sanitize_text_field($origin)][sanitize_text_field($dest)] = sanitize_text_field($price);
                             }
                         }
                     }
                 }
             }
        }
        update_post_meta($post_id, 'wbbm_shuttle_pricing', $pricing);

        // Save Schedule
        $schedule = array();
        if (isset($_POST['wbbm_shuttle_schedule']) && is_array($_POST['wbbm_shuttle_schedule'])) {
            foreach ($_POST['wbbm_shuttle_schedule'] as $route_id => $times) {
                if (is_array($times)) {
                    foreach ($times as $time_idx => $data) {
                        if (!empty($data['time'])) {
                            $schedule[sanitize_text_field($route_id)][] = array(
                                'time' => sanitize_text_field($data['time']),
                                'days' => isset($data['days']) ? array_map('sanitize_text_field', $data['days']) : array()
                            );
                        }
                    }
                }
            }
        }
        update_post_meta($post_id, 'wbbm_shuttle_schedule', $schedule);

        // Mark as virtual product for WooCommerce
        update_post_meta($post_id, '_virtual', 'yes');
        update_post_meta($post_id, '_sold_individually', 'yes');
    }

    /**
     * Remove default taxonomy meta boxes from sidebar
     */
    public function wbbm_shuttle_remove_sidebar_meta_box()
    {
        remove_meta_box('wbbm_shuttle_typediv', 'wbbm_shuttle', 'side');
        remove_meta_box('wbbm_shuttle_catdiv', 'wbbm_shuttle', 'side');
        remove_meta_box('wbbm_shuttle_stopsdiv', 'wbbm_shuttle', 'side');
    }
}

// Initialize the class
new ShuttleMetaBoxClass();
