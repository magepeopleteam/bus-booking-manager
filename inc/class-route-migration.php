<?php

/**
 * Bus Route Data Migration Class
 * Migrates old separate boarding/dropping point data to unified route structure.
 *
 * Runs automatically in the background the next time an admin screen loads --
 * no button to click, no action required. A single dismissible notice
 * confirms it happened, once, after the fact.
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
            // Runs on every admin screen load (cheap: a "nothing to do"
            // result is cached for a few minutes so it isn't re-checking
            // on every single request) so any bus added later with
            // old-format data -- a restore, an import, the demo-data
            // seeder -- gets picked up too, not just a one-time pass.
            add_action('admin_init', [$this, 'maybe_auto_migrate'], 20);

            // Also run immediately at activation, so a fresh
            // activate/reactivate finishes migrating right away instead
            // of waiting for the next admin page load.
            add_action('activated_plugin', [$this, 'maybe_auto_migrate_on_activation'], 5, 1);

            add_action('admin_notices', [$this, 'migration_done_notice']);
        }

        public function maybe_auto_migrate_on_activation($plugin)
        {
            if (defined('WBTM_PLUGIN_FILE') && $plugin !== WBTM_PLUGIN_FILE) {
                return;
            }
            $this->maybe_auto_migrate(true);
        }

        /**
         * Run the migration automatically -- no button, no admin action.
         * Guarded by a short "nothing to do" cache (so a fresh install
         * with zero buses doesn't re-run this query on every request) and
         * a short lock (so two overlapping requests can't both migrate at
         * once). Pass $force to skip the "nothing to do" cache -- used at
         * plugin activation, so it finishes instantly rather than waiting
         * out the cache window.
         */
        public function maybe_auto_migrate($force = false)
        {
            if (!current_user_can('manage_options')) {
                return;
            }

            if (get_transient('wbbm_route_migration_lock')) {
                return;
            }

            if (!$force && get_transient('wbbm_route_migration_nothing_to_do')) {
                return;
            }

            if ($this->count_buses_needing_migration() === 0) {
                set_transient('wbbm_route_migration_nothing_to_do', 1, 5 * MINUTE_IN_SECONDS);
                return;
            }

            set_transient('wbbm_route_migration_lock', 1, MINUTE_IN_SECONDS);

            $this->dry_run = false;
            $this->log = [];
            $result = $this->migrate_all_buses();

            update_option('wbbm_route_migration_summary', $result['stats']);
            delete_transient('wbbm_route_migration_lock');
            delete_transient('wbbm_route_migration_nothing_to_do');

            // Shown once on the next admin_notices pass, then cleared.
            if ($result['stats']['migrated'] > 0) {
                set_transient('wbbm_route_migration_done_notice', $result['stats'], DAY_IN_SECONDS);
            }
        }

        /**
         * One-time, informational-only notice -- nothing to click to make
         * the migration happen, it already ran. Dismissing it (WordPress's
         * standard is-dismissible close button) is just cosmetic.
         */
        public function migration_done_notice()
        {
            if (!current_user_can('manage_options')) {
                return;
            }

            $stats = get_transient('wbbm_route_migration_done_notice');
            if (!$stats) {
                return;
            }

            // Show it exactly once.
            delete_transient('wbbm_route_migration_done_notice');
            ?>
            <div class="notice notice-success is-dismissible">
                <p>
                    <?php
                    printf(
                        /* translators: %d: number of buses migrated */
                        esc_html__('Bus Booking Manager automatically updated route data for %d bus(es) to the new format. Your old data was preserved -- no action needed.', 'bus-booking-manager'),
                        (int) $stats['migrated']
                    );
                    ?>
                </p>
            </div>
            <?php
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
