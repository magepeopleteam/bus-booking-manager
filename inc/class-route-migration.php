<?php
/**
 * Bus Route Data Migration Class
 * Migrates old separate boarding/dropping point data to unified route structure
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('WBBM_Route_Migration')) {
    class WBBM_Route_Migration
    {
        private $log = [];
        private $dry_run = false;

        public function __construct()
        {
            // Admin notice
            add_action('admin_notices', [$this, 'migration_admin_notice']);
            
            // AJAX handlers
            add_action('wp_ajax_wbbm_run_route_migration', [$this, 'ajax_run_migration']);
            add_action('wp_ajax_wbbm_dismiss_migration_notice', [$this, 'ajax_dismiss_notice']);
        }

        /**
         * Display admin notice for migration
         */
        public function migration_admin_notice()
        {
            // Only show to administrators
            if (!current_user_can('manage_options')) {
                return;
            }

            // Check if notice was dismissed
            if (get_option('wbbm_route_migration_notice_dismissed', false)) {
                return;
            }

            // Check if there are buses that need migration
            $needs_migration = $this->count_buses_needing_migration();
            
            if ($needs_migration === 0) {
                return;
            }

            ?>
            <div class="notice notice-warning is-dismissible wbbm-migration-notice" id="wbbm-route-migration-notice">
                <h3><?php esc_html_e('Bus Booking Manager - Route Data Migration', 'bus-booking-manager'); ?></h3>
                <p>
                    <?php 
                    printf(
                        esc_html__('We found %d bus(es) with old route data structure. Please migrate to the new unified route system for better compatibility.', 'bus-booking-manager'),
                        $needs_migration
                    ); 
                    ?>
                </p>
                <p>
                    <strong><?php esc_html_e('Note:', 'bus-booking-manager'); ?></strong>
                    <?php esc_html_e('Your old data will be preserved. This is a safe operation.', 'bus-booking-manager'); ?>
                </p>
                <p>
                    <!-- <button type="button" class="button button-primary" id="wbbm-migration-dry-run">
                        <span class="dashicons dashicons-search" style="vertical-align: middle;"></span>
                        <?php // esc_html_e('Test Migration (Dry Run)', 'bus-booking-manager'); ?>
                    </button> -->
                    <button type="button" class="button button-primary button-hero" id="wbbm-migration-run" style="background: #2271b1;">
                        <span class="dashicons dashicons-update" style="vertical-align: middle;"></span>
                        <?php esc_html_e('Run Migration Now', 'bus-booking-manager'); ?>
                    </button>
                    <button type="button" class="button" id="wbbm-migration-dismiss">
                        <?php esc_html_e('Dismiss Notice', 'bus-booking-manager'); ?>
                    </button>
                </p>
                <div id="wbbm-migration-progress" style="display:none; margin-top: 15px;">
                    <div style="background: #fff; padding: 15px; border: 1px solid #ccc; border-radius: 4px;">
                        <h4 id="wbbm-migration-status"><?php esc_html_e('Processing...', 'bus-booking-manager'); ?></h4>
                        <div id="wbbm-migration-log" style="max-height: 300px; overflow-y: auto; font-family: monospace; font-size: 12px; background: #f5f5f5; padding: 10px; border-radius: 3px;"></div>
                    </div>
                </div>
            </div>

            <script type="text/javascript">
            jQuery(document).ready(function($) {
                // Dry run
                $('#wbbm-migration-dry-run').on('click', function() {
                    if (!confirm('<?php esc_html_e('Run migration test without saving changes?', 'bus-booking-manager'); ?>')) {
                        return;
                    }
                    runMigration(true);
                });

                // Actual migration
                $('#wbbm-migration-run').on('click', function() {
                    if (!confirm('<?php esc_html_e('Are you sure you want to migrate bus route data? This will create new route info for all buses.', 'bus-booking-manager'); ?>')) {
                        return;
                    }
                    runMigration(false);
                });

                // Dismiss notice
                $('#wbbm-migration-dismiss').on('click', function() {
                    $.ajax({
                        url: ajaxurl,
                        type: 'POST',
                        data: {
                            action: 'wbbm_dismiss_migration_notice',
                            nonce: '<?php echo esc_js(wp_create_nonce('wbbm_migration_nonce')); ?>'
                        },
                        success: function(response) {
                            $('#wbbm-route-migration-notice').fadeOut();
                        }
                    });
                });

                function runMigration(dryRun) {
                    var $progress = $('#wbbm-migration-progress');
                    var $log = $('#wbbm-migration-log');
                    var $status = $('#wbbm-migration-status');
                    var $buttons = $('#wbbm-migration-dry-run, #wbbm-migration-run, #wbbm-migration-dismiss');

                    $buttons.prop('disabled', true);
                    $progress.show();
                    $log.html('');
                    $status.text(dryRun ? '<?php esc_html_e('Running test migration...', 'bus-booking-manager'); ?>' : '<?php esc_html_e('Running migration...', 'bus-booking-manager'); ?>');

                    $.ajax({
                        url: ajaxurl,
                        type: 'POST',
                        data: {
                            action: 'wbbm_run_route_migration',
                            dry_run: dryRun ? 1 : 0,
                            nonce: '<?php echo esc_js(wp_create_nonce('wbbm_migration_nonce')); ?>'
                        },
                        success: function(response) {
                            if (response.success) {
                                $status.html('<span style="color: green;">✓ ' + response.data.message + '</span>');
                                
                                // Display log
                                if (response.data.log && response.data.log.length > 0) {
                                    $.each(response.data.log, function(i, entry) {
                                        var color = entry.type === 'error' ? 'red' : (entry.type === 'warning' ? 'orange' : '#333');
                                        $log.append('<div style="color: ' + color + '; margin-bottom: 5px;">[' + entry.type.toUpperCase() + '] ' + entry.message + '</div>');
                                    });
                                }

                                if (!dryRun) {
                                    setTimeout(function() {
                                        location.reload();
                                    }, 3000);
                                }
                            } else {
                                $status.html('<span style="color: red;">✗ ' + response.data.message + '</span>');
                            }
                        },
                        error: function() {
                            $status.html('<span style="color: red;">✗ <?php esc_html_e('Migration failed. Please try again.', 'bus-booking-manager'); ?></span>');
                        },
                        complete: function() {
                            $buttons.prop('disabled', false);
                        }
                    });
                }
            });
            </script>

            <style>
                .wbbm-migration-notice h3 {
                    margin: 0.5em 0;
                }
                .wbbm-migration-notice .button {
                    margin-right: 10px;
                }
            </style>
            <?php
        }

        /**
         * AJAX handler for running migration
         */
        public function ajax_run_migration()
        {
            check_ajax_referer('wbbm_migration_nonce', 'nonce');

            if (!current_user_can('manage_options')) {
                wp_send_json_error(['message' => __('Insufficient permissions.', 'bus-booking-manager')]);
            }

            $this->dry_run = isset($_POST['dry_run']) && $_POST['dry_run'] == '1';
            $this->log = [];

            $result = $this->migrate_all_buses();

            wp_send_json_success([
                'message' => $result['message'],
                'log' => $this->log,
                'stats' => $result['stats']
            ]);
        }

        /**
         * AJAX handler for dismissing notice
         */
        public function ajax_dismiss_notice()
        {
            check_ajax_referer('wbbm_migration_nonce', 'nonce');

            if (!current_user_can('manage_options')) {
                wp_send_json_error(['message' => __('Insufficient permissions.', 'bus-booking-manager')]);
            }

            update_option('wbbm_route_migration_notice_dismissed', true);
            wp_send_json_success();
        }

        /**
         * Count buses that need migration
         */
        private function count_buses_needing_migration()
        {
            $args = [
                'post_type' => 'wbbm_bus',
                'posts_per_page' => -1,
                'post_status' => 'any',
                'fields' => 'ids'
            ];

            $buses = get_posts($args);
            $count = 0;

            foreach ($buses as $bus_id) {
                $route_info = get_post_meta($bus_id, 'wbbm_route_info', true);
                $boarding = get_post_meta($bus_id, 'wbbm_bus_bp_stops', true);
                $dropping = get_post_meta($bus_id, 'wbbm_bus_next_stops', true);

                // Needs migration if has old data but no new data
                if ((is_array($boarding) && !empty($boarding)) || (is_array($dropping) && !empty($dropping))) {
                    if (empty($route_info) || !is_array($route_info)) {
                        $count++;
                    }
                }
            }

            return $count;
        }

        /**
         * Migrate all buses
         */
        public function migrate_all_buses()
        {
            $args = [
                'post_type' => 'wbbm_bus',
                'posts_per_page' => -1,
                'post_status' => 'any'
            ];

            $buses = get_posts($args);
            $stats = [
                'total' => count($buses),
                'migrated' => 0,
                'skipped' => 0,
                'errors' => 0
            ];

            $this->add_log('info', sprintf('Starting migration for %d buses...', $stats['total']));

            if ($this->dry_run) {
                $this->add_log('warning', 'DRY RUN MODE - No changes will be saved');
            }

            foreach ($buses as $bus) {
                $result = $this->migrate_single_bus($bus->ID);
                
                if ($result === 'migrated') {
                    $stats['migrated']++;
                } elseif ($result === 'skipped') {
                    $stats['skipped']++;
                } else {
                    $stats['errors']++;
                }
            }

            $this->add_log('info', sprintf(
                'Migration complete: %d migrated, %d skipped, %d errors',
                $stats['migrated'],
                $stats['skipped'],
                $stats['errors']
            ));

            $message = $this->dry_run 
                ? sprintf(__('Test complete: Would migrate %d buses', 'bus-booking-manager'), $stats['migrated'])
                : sprintf(__('Successfully migrated %d buses', 'bus-booking-manager'), $stats['migrated']);

            return [
                'message' => $message,
                'stats' => $stats
            ];
        }

        /**
         * Migrate a single bus
         */
        private function migrate_single_bus($post_id)
        {
            $bus_title = get_the_title($post_id);
            
            // Check if already migrated
            $existing_route_info = get_post_meta($post_id, 'wbbm_route_info', true);
            if (!empty($existing_route_info) && is_array($existing_route_info)) {
                $this->add_log('info', sprintf('Bus #%d "%s": Already migrated, skipping', $post_id, $bus_title));
                return 'skipped';
            }

            // Get old data
            $boarding_points = get_post_meta($post_id, 'wbbm_bus_bp_stops', true);
            $dropping_points = get_post_meta($post_id, 'wbbm_bus_next_stops', true);

            // Ensure arrays
            if (!is_array($boarding_points)) {
                $boarding_points = [];
            }
            if (!is_array($dropping_points)) {
                $dropping_points = [];
            }

            // Check if there's data to migrate
            if (empty($boarding_points) && empty($dropping_points)) {
                $this->add_log('info', sprintf('Bus #%d "%s": No route data found, skipping', $post_id, $bus_title));
                return 'skipped';
            }

            // Merge the data
            $merged_route = $this->merge_route_data($boarding_points, $dropping_points);

            if (empty($merged_route)) {
                $this->add_log('error', sprintf('Bus #%d "%s": Failed to merge route data', $post_id, $bus_title));
                return 'error';
            }

            // Save the new structure
            if (!$this->dry_run) {
                $updated = update_post_meta($post_id, 'wbbm_route_info', $merged_route);
                if ($updated) {
                    $this->add_log('success', sprintf(
                        'Bus #%d "%s": Migrated %d stops (%d boarding, %d dropping)',
                        $post_id,
                        $bus_title,
                        count($merged_route),
                        count($boarding_points),
                        count($dropping_points)
                    ));
                    return 'migrated';
                } else {
                    $this->add_log('error', sprintf('Bus #%d "%s": Failed to save migrated data', $post_id, $bus_title));
                    return 'error';
                }
            } else {
                $this->add_log('success', sprintf(
                    'Bus #%d "%s": Would migrate %d stops (%d boarding, %d dropping)',
                    $post_id,
                    $bus_title,
                    count($merged_route),
                    count($boarding_points),
                    count($dropping_points)
                ));
                return 'migrated';
            }
        }

        /**
         * Merge boarding and dropping point data into unified route structure
         */
        private function merge_route_data($boarding_points, $dropping_points)
        {
            $merged = [];
            $stops_map = [];

            // Process boarding points
            foreach ($boarding_points as $index => $bp) {
                if (empty($bp['wbbm_bus_bp_stops_name'])) {
                    continue;
                }

                $place = sanitize_text_field($bp['wbbm_bus_bp_stops_name']);
                $time = isset($bp['wbbm_bus_bp_start_time']) ? sanitize_text_field($bp['wbbm_bus_bp_start_time']) : '';

                $stops_map[$place] = [
                    'place' => $place,
                    'time' => $time,
                    'type' => 'bp',
                    'next_day' => 0,
                    'order' => count($merged)
                ];

                $merged[] = &$stops_map[$place];
            }

            // Process dropping points
            foreach ($dropping_points as $index => $dp) {
                if (empty($dp['wbbm_bus_next_stops_name'])) {
                    continue;
                }

                $place = sanitize_text_field($dp['wbbm_bus_next_stops_name']);
                $time = isset($dp['wbbm_bus_next_end_time']) ? sanitize_text_field($dp['wbbm_bus_next_end_time']) : '';

                if (isset($stops_map[$place])) {
                    // Stop exists in boarding, update to 'both'
                    $stops_map[$place]['type'] = 'both';
                    // Keep boarding time as primary, but could add logic here if needed
                } else {
                    // New dropping-only stop
                    $stops_map[$place] = [
                        'place' => $place,
                        'time' => $time,
                        'type' => 'dp',
                        'next_day' => 0,
                        'order' => count($merged)
                    ];
                    $merged[] = &$stops_map[$place];
                }
            }

            // Clean up references and remove order field
            $result = [];
            foreach ($merged as $stop) {
                unset($stop['order']);
                $result[] = $stop;
            }

            return $result;
        }

        /**
         * Add log entry
         */
        private function add_log($type, $message)
        {
            $this->log[] = [
                'type' => $type,
                'message' => $message,
                'time' => current_time('mysql')
            ];

            // Also log to WordPress debug log if enabled
            if (defined('WP_DEBUG') && WP_DEBUG && defined('WP_DEBUG_LOG') && WP_DEBUG_LOG) {
                error_log(sprintf('[WBBM Migration] [%s] %s', strtoupper($type), $message));
            }
        }
    }

    // Initialize the migration class
    new WBBM_Route_Migration();
}
