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
        add_action('admin_menu', array($this, 'register_shuttle_list_page'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_assets'));
    }

    /**
     * Register the custom list page and replace default
     */
    public function register_shuttle_list_page()
    {
        // Add custom list page
        add_submenu_page(
            'edit.php?post_type=wbbm_shuttle',
            __('Shuttle List', 'bus-booking-manager'),
            __('Shuttle List', 'bus-booking-manager'),
            'manage_options',
            'wbbm-shuttle-list',
            array($this, 'render_shuttle_list_page')
        );

        // Adjust order: Add New, Shuttle List (custom), etc.
        // We might want to remove the default "All Shuttle Services"
        remove_submenu_page('edit.php?post_type=wbbm_shuttle', 'edit.php?post_type=wbbm_shuttle');
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
                            <h2><?php _e('Shuttle List', 'bus-booking-manager'); ?> <span class="version-badge">v1.0.0</span></h2>
                        </div>
                    </div>
                    <div class="header-right">
                        <a href="<?php echo admin_url('edit.php?post_type=wbbm_shuttle&page=wbbm-shuttle-edit'); ?>" class="btn btn-primary">
                            <span class="dashicons dashicons-plus"></span> <?php _e('New Shuttle', 'bus-booking-manager'); ?>
                        </a>
                        <button type="button" class="btn btn-icon-outline">
                            <span class="dashicons dashicons-ellipsis"></span>
                        </button>
                    </div>
                </div>

                <!-- Filters Card -->
                <div class="shuttle-filters-card">
                    <form method="get" action="" id="shuttle-list-filter-form">
                        <input type="hidden" name="post_type" value="wbbm_shuttle">
                        <input type="hidden" name="page" value="wbbm-shuttle-list">

                        <div class="filters-row">
                            <div class="filter-group">
                                <select name="status" id="status-filter" class="form-control">
                                    <option value=""><?php _e('Status', 'bus-booking-manager'); ?></option>
                                    <option value="publish" <?php selected($status, 'publish'); ?>><?php _e('Published', 'bus-booking-manager'); ?></option>
                                    <option value="draft" <?php selected($status, 'draft'); ?>><?php _e('Draft', 'bus-booking-manager'); ?></option>
                                    <option value="pending" <?php selected($status, 'pending'); ?>><?php _e('Pending', 'bus-booking-manager'); ?></option>
                                </select>
                            </div>
                            <!-- Date Filters (Placeholder as per attachment) -->
                            <div class="filter-group date-range">
                                <input type="text" placeholder="<?php _e('Start Date', 'bus-booking-manager'); ?>" class="form-control" disabled>
                                <span class="date-sep">&rarr;</span>
                                <input type="text" placeholder="<?php _e('End Date', 'bus-booking-manager'); ?>" class="form-control" disabled>
                                <span class="dashicons dashicons-calendar-alt"></span>
                            </div>
                            <div class="filter-group search-group">
                                <span class="dashicons dashicons-search"></span>
                                <input type="text" name="s" value="<?php echo esc_attr($s); ?>" placeholder="<?php _e('Search shuttle by name', 'bus-booking-manager'); ?>" class="form-control">
                            </div>

                            <div class="filter-actions">
                                <button type="button" class="btn btn-outline btn-sm">
                                    <span class="dashicons dashicons-upload"></span> <?php _e('Export', 'bus-booking-manager'); ?>
                                </button>
                                <button type="button" class="btn btn-outline btn-sm">
                                    <span class="dashicons dashicons-download"></span> <?php _e('Import', 'bus-booking-manager'); ?>
                                </button>
                            </div>
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
                                <th class="col-capacity"><?php _e('Capacity', 'bus-booking-manager'); ?></th>
                                <th class="col-author"><?php _e('Author', 'bus-booking-manager'); ?></th>
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
                                        'post_type' => 'wbbm_shuttle',
                                        'page'      => 'wbbm-shuttle-edit',
                                        'post_id'   => $post_id
                                    ), admin_url('edit.php'));

                                    $view_url = get_permalink($post_id);
                                ?>
                                    <tr>
                                        <td><input type="checkbox" name="shuttle_ids[]" value="<?php echo esc_attr($post_id); ?>"></td>
                                        <td class="shuttle-info-cell">
                                            <div class="shuttle-thumb">
                                                <?php if ($thumb_url) : ?>
                                                    <img src="<?php echo esc_url($thumb_url); ?>" alt="">
                                                <?php else : ?>
                                                    <span class="dashicons dashicons-image-flip"></span>
                                                <?php endif; ?>
                                            </div>
                                            <div class="shuttle-details">
                                                <div class="shuttle-title"><?php the_title(); ?></div>
                                                <div class="shuttle-meta"><?php echo esc_html($route_name); ?></div>
                                                <div class="shuttle-sub-meta"><?php the_date('F d, Y, g:i a'); ?></div>
                                            </div>
                                        </td>
                                        <td><?php echo !empty($categories) ? esc_html(implode(', ', $categories)) : '-'; ?></td>
                                        <td>
                                            <div class="capacity-info">
                                                <span class="count">0 / <?php echo esc_html($capacity); ?></span>
                                                <div class="progress-bar">
                                                    <div class="progress" style="width: 0%"></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="author-cell">
                                                <?php echo esc_html($author_name); ?>
                                                <span class="dashicons dashicons-edit"></span>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="status-badge <?php echo esc_attr($status_class); ?>"><?php echo esc_html($status_label); ?></span>
                                        </td>
                                        <td>
                                            <div class="action-buttons">
                                                <a href="<?php echo esc_url($view_url); ?>" class="action-btn" target="_blank" title="View"><span class="dashicons dashicons-external"></span></a>
                                                <a href="<?php echo esc_url($edit_url); ?>" class="action-btn" title="Edit"><span class="dashicons dashicons-edit"></span></a>
                                                <button type="button" class="action-btn more-btn"><span class="dashicons dashicons-ellipsis"></span></button>
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
                            <?php printf(__('Showing %d - %d of %d events', 'bus-booking-manager'), $start_num, $end_num, $total_posts); ?>
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
