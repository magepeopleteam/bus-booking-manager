<?php
if (!defined('ABSPATH')) exit;

/**
 * Shuttle List Page Class
 * 
 * Handles the modern custom list page for shuttles.
 */
class ShuttleListPageClass
{
    public function __construct()
    {
        add_action('admin_menu', array($this, 'register_shuttle_list_page'), 1);
        add_action('admin_menu', array($this, 'reorder_shuttle_submenu'), 999);
        add_action('admin_enqueue_scripts', array($this, 'enqueue_assets'));
        add_action('admin_init', array($this, 'handle_shuttle_actions'));
    }

    /**
     * Register the custom list page and replace default
     */
    public function register_shuttle_list_page()
    {
        // Add custom list page
        add_submenu_page(
            'edit.php?post_type=wbbm_shuttle',
            __('Shuttles', 'bus-booking-manager'),
            __('Shuttles', 'bus-booking-manager'),
            'manage_options',
            'wbbm-shuttle-list',
            array($this, 'render_shuttle_list_page')
        );

        // Adjust order: Add New, Shuttle List (custom), etc.
        // We might want to remove the default "All Shuttle Services"
        remove_submenu_page('edit.php?post_type=wbbm_shuttle', 'edit.php?post_type=wbbm_shuttle');
    }

    /**
     * Reorder shuttle submenu to keep "Shuttles" list item as first item
     */
    public function reorder_shuttle_submenu()
    {
        global $submenu;
        $parent_slug = 'edit.php?post_type=wbbm_shuttle';

        if (!isset($submenu[$parent_slug])) {
            return;
        }

        $shuttle_menu = $submenu[$parent_slug];
        $list_page_slug = 'wbbm-shuttle-list';

        $list_item_key = false;
        foreach ($shuttle_menu as $key => $item) {
            if (isset($item[2]) && $item[2] === $list_page_slug) {
                $list_item_key = $key;
                break;
            }
        }

        if ($list_item_key !== false) {
            $list_item = $shuttle_menu[$list_item_key];
            unset($shuttle_menu[$list_item_key]);
            array_unshift($shuttle_menu, $list_item);
            // Re-index array
            $submenu[$parent_slug] = array_values($shuttle_menu);
        }
    }

    /**
     * Enqueue CSS and JS
     */
    public function enqueue_assets($hook)
    {
        if (strpos($hook, 'wbbm-shuttle-list') === false) {
            return;
        }

        wp_enqueue_style('shuttle-list-css', WBTM_PLUGIN_URL . 'assets/admin/shuttle-list.css', array(), time());
        wp_enqueue_script('shuttle-list-js', WBTM_PLUGIN_URL . 'assets/admin/shuttle-list.js', array('jquery'), time(), true);
    }

    /**
     * Handle custom actions like delete
     */
    public function handle_shuttle_actions()
    {
        if (isset($_GET['page']) && $_GET['page'] === 'wbbm-shuttle-list' && isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['post_id'])) {
            $post_id = intval($_GET['post_id']);
            $post = get_post($post_id);

            if (!$post || $post->post_type !== 'wbbm_shuttle') {
                return;
            }

            if (!current_user_can('delete_post', $post_id)) {
                wp_die(__('You do not have permission to delete this shuttle.', 'bus-booking-manager'));
            }

            check_admin_referer('delete-shuttle_' . $post_id);

            wp_trash_post($post_id);

            wp_redirect(admin_url('edit.php?post_type=wbbm_shuttle&page=wbbm-shuttle-list&deleted=1'));
            exit;
        }
    }

    /**
     * Render the custom list page
     */
    public function render_shuttle_list_page()
    {
        // Filters
        $status = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : '';
        $s = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';

        $paged = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
        $posts_per_page = 10;

        $args = array(
            'post_type'      => 'wbbm_shuttle',
            'post_status'    => $status ? $status : array('publish', 'draft', 'pending', 'private', 'future'),
            's'              => $s,
            'posts_per_page' => $posts_per_page,
            'paged'          => $paged,
        );

        $query = new WP_Query($args);
        $total_posts = $query->found_posts;
        $total_pages = $query->max_num_pages;

        if (isset($_GET['deleted']) && $_GET['deleted'] === '1') {
            echo '<div class="notice notice-success is-dismissible" style="margin: 20px 20px 0 0;"><p>' . __('Shuttle moved to trash.', 'bus-booking-manager') . '</p></div>';
        }

        $start_num = ($paged - 1) * $posts_per_page + 1;
        $end_num = min($paged * $posts_per_page, $total_posts);

?>
        <div class="wrap shuttle-list-wrap">
            <div class="shuttle-list-container">
                <!-- Header Section -->
                <div class="shuttle-list-header">
                    <div class="header-left">
                        <div class="brand-logo">
                            <img src="<?php echo WBTM_PLUGIN_URL . 'assets/admin/img/logo-icon.png'; ?>" alt="" onerror="this.style.display='none'">
                            <span class="dashicons dashicons-car"></span>
                        </div>
                        <div class="header-title-area">
                            <h2><?php _e('Shuttle List', 'bus-booking-manager'); ?></h2>
                        </div>
                    </div>
                    <div class="header-right">
                        <a href="<?php echo admin_url('admin.php?page=wbbm-shuttle-edit'); ?>" class="btn btn-primary">
                            <span class="dashicons dashicons-plus"></span> <?php _e('New Shuttle', 'bus-booking-manager'); ?>
                        </a>
                        <!-- <button type="button" class="btn btn-icon-outline">
                            <span class="dashicons dashicons-ellipsis"></span>
                        </button> -->
                    </div>
                </div>

                <!-- Filters Card -->
                <div class="shuttle-filters-card">
                    <form method="get" action="" id="shuttle-list-filter-form">
                        <input type="hidden" name="post_type" value="wbbm_shuttle">
                        <input type="hidden" name="page" value="wbbm-shuttle-list">

                        <div class="filters-row">
                            <div class="filter-left">
                                <div class="filter-group search-group">
                                    <span class="dashicons dashicons-search"></span>
                                    <input type="text" name="s" value="<?php echo esc_attr($s); ?>" placeholder="<?php _e('Search by shuttle name...', 'bus-booking-manager'); ?>" class="form-control">
                                </div>
                                <div class="filter-group status-filter-group">
                                    <select name="status" id="status-filter" class="form-control">
                                        <option value=""><?php _e('All Status', 'bus-booking-manager'); ?></option>
                                        <option value="publish" <?php selected($status, 'publish'); ?>><?php _e('Published', 'bus-booking-manager'); ?></option>
                                        <option value="draft" <?php selected($status, 'draft'); ?>><?php _e('Draft', 'bus-booking-manager'); ?></option>
                                        <option value="pending" <?php selected($status, 'pending'); ?>><?php _e('Pending', 'bus-booking-manager'); ?></option>
                                    </select>
                                </div>
                                <button type="submit" class="btn btn-primary btn-sm">
                                    <?php _e('Filter', 'bus-booking-manager'); ?>
                                </button>
                                <?php if ($s || $status) : ?>
                                    <a href="<?php echo admin_url('edit.php?post_type=wbbm_shuttle&page=wbbm-shuttle-list'); ?>" class="btn btn-outline btn-sm">
                                        <?php _e('Clear', 'bus-booking-manager'); ?>
                                    </a>
                                <?php endif; ?>
                            </div>

                            <!-- <div class="filter-right">
                                <div class="filter-actions">
                                    <button type="button" class="btn btn-outline btn-sm">
                                        <span class="dashicons dashicons-upload"></span> <?php _e('Export', 'bus-booking-manager'); ?>
                                    </button>
                                    <button type="button" class="btn btn-outline btn-sm">
                                        <span class="dashicons dashicons-download"></span> <?php _e('Import', 'bus-booking-manager'); ?>
                                    </button>
                                </div>
                            </div> -->
                        </div>
                    </form>
                </div>

                <!-- Table Content -->
                <div class="shuttle-list-table-card">
                    <table class="shuttle-modern-table">
                        <thead>
                            <tr>
                                <th class="col-check"><input type="checkbox" id="select-all"></th>
                                <th class="col-shuttle"><?php _e('Shuttle', 'bus-booking-manager'); ?></th>
                                <th class="col-category"><?php _e('Category', 'bus-booking-manager'); ?></th>
                                <th class="col-stops"><?php _e('Stops', 'bus-booking-manager'); ?></th>
                                <th class="col-capacity"><?php _e('Capacity', 'bus-booking-manager'); ?></th>
                                <th class="col-status"><?php _e('Status', 'bus-booking-manager'); ?></th>
                                <th class="col-action"><?php _e('Action', 'bus-booking-manager'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($query->have_posts()) : ?>
                                <?php while ($query->have_posts()) : $query->the_post();
                                    $post_id = get_the_ID();
                                    $thumb_id = get_post_thumbnail_id($post_id);
                                    $thumb_url = $thumb_id ? wp_get_attachment_image_url($thumb_id, 'thumbnail') : '';
                                    $categories = wp_get_post_terms($post_id, 'wbbm_shuttle_cat', array('fields' => 'names'));
                                    $author_id = get_the_author_meta('ID');
                                    $author_name = get_the_author();
                                    $capacity = get_post_meta($post_id, 'wbbm_shuttle_capacity', true);
                                    $routes = maybe_unserialize(get_post_meta($post_id, 'wbbm_shuttle_routes', true)) ?: array();
                                    $route_name = !empty($routes) ? $routes[0]['name'] : '--';

                                    $current_status = get_post_status();
                                    $status_label = ucfirst($current_status);
                                    $status_class = 'status-' . $current_status;

                                    $edit_url = add_query_arg(array(
                                        'page'      => 'wbbm-shuttle-edit',
                                        'post_id'   => $post_id
                                    ), admin_url('admin.php'));

                                    $view_url = get_permalink($post_id);
                                    $delete_url = wp_nonce_url(add_query_arg(array('action' => 'delete', 'post_id' => $post_id)), 'delete-shuttle_' . $post_id);
                                ?>
                                    <tr>
                                        <td><input type="checkbox" name="shuttle_ids[]" value="<?php echo esc_attr($post_id); ?>"></td>
                                        <td class="shuttle-info-cell" style="width: 250px;">
                                            <div class="shuttle-thumb">
                                                <?php if ($thumb_url) : ?>
                                                    <img src="<?php echo esc_url($thumb_url); ?>" alt="">
                                                <?php else : ?>
                                                    <span class="dashicons dashicons-image-flip"></span>
                                                <?php endif; ?>
                                            </div>
                                            <div class="shuttle-details">
                                                <div class="shuttle-title"><a href="<?php echo esc_url($edit_url); ?>"><?php the_title(); ?></a></div>
                                                <div class="shuttle-meta"><?php echo esc_html($route_name); ?></div>
                                                <!-- <div class="shuttle-sub-meta"><?php //the_date('F d, Y, g:i a'); 
                                                                                    ?></div> -->
                                            </div>
                                        </td>
                                        <td><?php echo !empty($categories) ? esc_html(implode(', ', $categories)) : '-'; ?></td>
                                        <td>
                                            <div class="shuttle-stops-display">
                                                <?php
                                                $route_stops = !empty($routes) && isset($routes[0]['stops']) ? $routes[0]['stops'] : array();
                                                $stop_names = array();
                                                foreach ($route_stops as $s) {
                                                    $loc = isset($s['location']) ? $s['location'] : '';
                                                    if (empty($loc)) continue;

                                                    if (is_numeric($loc)) {
                                                        $term = get_term($loc, 'wbbm_shuttle_stops');
                                                        if ($term && !is_wp_error($term)) {
                                                            $stop_names[] = $term->name;
                                                        }
                                                    } else {
                                                        // It's already a name string
                                                        $stop_names[] = $loc;
                                                    }
                                                }

                                                if (!empty($stop_names)) :
                                                    $count = count($stop_names);
                                                ?>
                                                    <div class="stops-list-wrapper">
                                                        <div class="stops-visible">
                                                            <?php echo esc_html(implode(', ', array_slice($stop_names, 0, 2))); ?>
                                                            <?php if ($count > 2) : ?>
                                                                <button type="button" class="stops-toggle-btn" title="View all stops">...</button>
                                                            <?php endif; ?>
                                                        </div>
                                                        <?php if ($count > 2) : ?>
                                                            <div class="stops-hidden-content">
                                                                <div class="stops-full-list">
                                                                    <?php echo esc_html(implode(', ', $stop_names)); ?>
                                                                </div>
                                                                <button type="button" class="stops-collapse-btn"><?php _e('Collapse', 'bus-booking-manager'); ?></button>
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                <?php else : echo '--';
                                                endif; ?>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="capacity-info">
                                                <span class="count"><?php echo esc_html($capacity); ?></span>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="status-badge <?php echo esc_attr($status_class); ?>"><?php echo esc_html($status_label); ?></span>
                                        </td>
                                        <td>
                                            <div class="action-buttons">
                                                <a href="<?php echo esc_url($edit_url); ?>" class="action-btn" title="Edit"><span class="dashicons dashicons-edit"></span></a>
                                                <a href="<?php echo esc_url($delete_url); ?>" class="action-btn delete-btn" title="Delete" onclick="return confirm('<?php esc_attr_e('Are you sure you want to delete this shuttle?', 'bus-booking-manager'); ?>');"><span class="dashicons dashicons-trash"></span></a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile;
                                wp_reset_postdata(); ?>
                            <?php else : ?>
                                <tr>
                                    <td colspan="7" class="no-results"><?php _e('No shuttles found.', 'bus-booking-manager'); ?></td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>

                    <!-- Pagination -->
                    <div class="shuttle-pagination-area">
                        <div class="pagination-info">
                            <?php printf(__('Showing %d - %d of %d shuttles', 'bus-booking-manager'), $start_num, $end_num, $total_posts); ?>
                        </div>
                        <div class="pagination-controls">
                            <?php if ($paged > 1) : ?>
                                <a href="<?php echo add_query_arg('paged', $paged - 1); ?>" class="page-link prev"><span class="dashicons dashicons-arrow-left-alt2"></span></a>
                            <?php endif; ?>

                            <?php
                            for ($i = 1; $i <= $total_pages; $i++) {
                                if ($i == $paged) {
                                    echo '<span class="page-link active">' . $i . '</span>';
                                } else if ($i == 1 || $i == $total_pages || ($i >= $paged - 1 && $i <= $paged + 1)) {
                                    echo '<a href="' . add_query_arg('paged', $i) . '" class="page-link">' . $i . '</a>';
                                } else if ($i == 2 || $i == $total_pages - 1) {
                                    echo '<span class="pager-sep">...</span>';
                                }
                            }
                            ?>

                            <?php if ($paged < $total_pages) : ?>
                                <a href="<?php echo add_query_arg('paged', $paged + 1); ?>" class="page-link next"><span class="dashicons dashicons-arrow-right-alt2"></span></a>
                            <?php endif; ?>

                            <div class="per-page-selector">
                                <select class="form-control">
                                    <option value="10">10 / page</option>
                                    <option value="20">20 / page</option>
                                    <option value="50">50 / page</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
<?php
    }
}

new ShuttleListPageClass();
