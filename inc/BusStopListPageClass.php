<?php
if (!defined('ABSPATH')) exit;

/**
 * Bus Stop List Page Class
 * 
 * Handles the modern custom list page for bus stops (wbbm_bus_stops taxonomy).
 */
class BusStopListPageClass
{
    public function __construct()
    {
        add_action('admin_menu', array($this, 'register_bus_stop_list_page'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_assets'), 20);
        add_action('admin_init', array($this, 'handle_bus_stop_actions'));
        add_action('admin_init', array($this, 'handle_redirects'));
        add_action('admin_menu', array($this, 'reorder_bus_submenu'), 1001);
    }

    /**
     * Register the custom list page and replace default taxonomy entry
     */
    public function register_bus_stop_list_page()
    {
        add_submenu_page(
            'edit.php?post_type=wbbm_bus',
            __('Bus Stops', 'bus-booking-manager'),
            __('Bus Stops', 'bus-booking-manager'),
            'manage_options',
            'wbbm-bus-stop-list',
            array($this, 'render_bus_stop_list_page')
        );

        // Remove default taxonomy submenu
        remove_submenu_page('edit.php?post_type=wbbm_bus', 'edit-tags.php?taxonomy=wbbm_bus_stops&amp;post_type=wbbm_bus');
        remove_submenu_page('edit.php?post_type=wbbm_bus', 'edit-tags.php?taxonomy=wbbm_bus_stops&post_type=wbbm_bus');
    }

    /**
     * Reorder bus submenu to keep "Bus Stops" as 4th item (index 3)
     */
    public function reorder_bus_submenu()
    {
        global $submenu;
        $parent_slug = 'edit.php?post_type=wbbm_bus';

        if (!isset($submenu[$parent_slug])) {
            return;
        }

        $bus_menu = $submenu[$parent_slug];
        $stop_page_slug = 'wbbm-bus-stop-list';

        $stop_item = false;
        foreach ($bus_menu as $key => $item) {
            if (isset($item[2]) && $item[2] === $stop_page_slug) {
                $stop_item = $item;
                unset($bus_menu[$key]);
                break;
            }
        }

        if ($stop_item) {
            $bus_menu = array_values($bus_menu);
            // Insert at index 3 (4th position)
            array_splice($bus_menu, 3, 0, array($stop_item));
            $submenu[$parent_slug] = $bus_menu;
        }
    }

    /**
     * Enqueue CSS and JS
     */
    public function enqueue_assets($hook)
    {
        if (!isset($_GET['page']) || $_GET['page'] !== 'wbbm-bus-stop-list') {
            return;
        }

        wp_enqueue_style('shuttle-list-css', WBTM_PLUGIN_URL . 'assets/admin/shuttle-list.css', array(), time());
        wp_enqueue_script('shuttle-list-js', WBTM_PLUGIN_URL . 'assets/admin/shuttle-list.js', array('jquery'), time(), true);
    }

    /**
     * Handle custom actions like delete
     */
    public function handle_bus_stop_actions()
    {
        if (isset($_GET['page']) && $_GET['page'] === 'wbbm-bus-stop-list' && isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['term_id'])) {
            $term_id = intval($_GET['term_id']);

            if (!current_user_can('manage_options')) {
                return;
            }

            check_admin_referer('delete-bus-stop_' . $term_id);

            wp_delete_term($term_id, 'wbbm_bus_stops');

            wp_safe_redirect(admin_url('edit.php?post_type=wbbm_bus&page=wbbm-bus-stop-list&deleted=1'));
            exit;
        }
    }

    /**
     * Handle redirects from default taxonomy pages
     */
    public function handle_redirects()
    {
        global $pagenow;
        if ($pagenow === 'edit-tags.php' && isset($_GET['taxonomy']) && $_GET['taxonomy'] === 'wbbm_bus_stops' && !isset($_GET['action'])) {
            wp_safe_redirect(admin_url('edit.php?post_type=wbbm_bus&page=wbbm-bus-stop-list'));
            exit;
        }
    }

    /**
     * Render the custom list page
     */
    public function render_bus_stop_list_page()
    {
        $s = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';
        $paged = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
        $number = 20;
        $offset = ($paged - 1) * $number;

        $args = array(
            'taxonomy'   => 'wbbm_bus_stops',
            'hide_empty' => false,
            'number'     => $number,
            'offset'     => $offset,
            'search'     => $s,
        );

        $terms = get_terms($args);
        $total_terms = wp_count_terms(array('taxonomy' => 'wbbm_bus_stops', 'hide_empty' => false, 'search' => $s));
        $total_pages = ceil($total_terms / $number);

        $start_num = $offset + 1;
        $end_num = min($offset + $number, $total_terms);

?>
        <div class="wrap shuttle-list-wrap">
            <div class="shuttle-list-container-fullwidth">
                <!-- Header Section -->
                <div class="shuttle-list-header">
                    <div class="header-left">
                        <div class="brand-logo">
                            <span class="dashicons dashicons-location"></span>
                        </div>
                        <div class="header-title-area">
                            <h2><?php _e('Bus Stops', 'bus-booking-manager'); ?></h2>
                        </div>
                    </div>
                    <div class="header-right">
                        <a href="<?php echo admin_url('admin.php?post_type=wbbm_bus&page=wbbm-bus-stop-edit'); ?>" class="btn btn-primary">
                            <span class="dashicons dashicons-plus"></span> <?php _e('Add New Bus Stop', 'bus-booking-manager'); ?>
                        </a>
                    </div>
                </div>

                <!-- Filters Card -->
                <div class="shuttle-filters-card">
                    <form method="get" action="<?php echo admin_url('edit.php'); ?>" id="shuttle-list-filter-form">
                        <input type="hidden" name="post_type" value="wbbm_bus">
                        <input type="hidden" name="page" value="wbbm-bus-stop-list">

                        <div class="filters-row">
                            <div class="filter-left" style="flex-grow:initial">
                                <div class="filter-group search-group">
                                    <span class="dashicons dashicons-search"></span>
                                    <input type="text" name="s" value="<?php echo esc_attr($s); ?>" placeholder="<?php _e('Search bus stops...', 'bus-booking-manager'); ?>" class="form-control">
                                </div>
                                <button type="submit" class="btn btn-primary btn-sm">
                                    <?php _e('Search', 'bus-booking-manager'); ?>
                                </button>
                                <?php if ($s) : ?>
                                    <a href="<?php echo admin_url('edit.php?post_type=wbbm_bus&page=wbbm-bus-stop-list'); ?>" class="btn btn-outline btn-sm" style="text-decoration:none;">
                                        <?php _e('Clear', 'bus-booking-manager'); ?>
                                    </a>
                                <?php endif; ?>
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
                                <th class="col-shuttle" style="width: 300px;"><?php _e('Name', 'bus-booking-manager'); ?></th>
                                <th class="col-category"><?php _e('Description', 'bus-booking-manager'); ?></th>
                                <th class="col-stops"><?php _e('Slug', 'bus-booking-manager'); ?></th>
                                <th class="col-capacity"><?php _e('Count', 'bus-booking-manager'); ?></th>
                                <th class="col-action"><?php _e('Action', 'bus-booking-manager'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($terms) && !is_wp_error($terms)) : ?>
                                <?php foreach ($terms as $term) :
                                    $edit_url = admin_url('admin.php?post_type=wbbm_bus&page=wbbm-bus-stop-edit&term_id=' . $term->term_id);
                                    $delete_url = wp_nonce_url(add_query_arg(array('action' => 'delete', 'term_id' => $term->term_id)), 'delete-bus-stop_' . $term->term_id);
                                ?>
                                    <tr>
                                        <td><input type="checkbox" name="term_ids[]" value="<?php echo esc_attr($term->term_id); ?>"></td>
                                        <td class="shuttle-info-cell">
                                            <div class="shuttle-details" style="padding-left: 0;">
                                                <div class="shuttle-title"><a href="<?php echo esc_url($edit_url); ?>"><?php echo esc_html($term->name); ?></a></div>
                                                <div class="shuttle-meta" style="font-size:12px; font-weight:normal;">
                                                    <?php echo __('ID:', 'bus-booking-manager') . ' ' . esc_html($term->term_id); ?>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div style="font-size: 13px; color: var(--sh-text-main);">
                                                <?php echo $term->description ? esc_html($term->description) : '—'; ?>
                                            </div>
                                        </td>
                                        <td>
                                            <div style="font-family: monospace; font-size: 13px; color: var(--sh-text-light);">
                                                <?php echo esc_html($term->slug); ?>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="capacity-info">
                                                <span class="count"><?php echo esc_html($term->count); ?></span>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="action-buttons">
                                                <a href="<?php echo esc_url($edit_url); ?>" class="action-btn" title="Edit"><span class="dashicons dashicons-edit"></span></a>
                                                <a href="<?php echo esc_url($delete_url); ?>" class="action-btn delete-btn wbbm-delete-bus-stop" title="Delete" onclick="return confirm('<?php esc_attr_e('Are you sure you want to delete this bus stop?', 'bus-booking-manager'); ?>');" data-wbbm-persist-toast="Item deleted successfully!"><span class="dashicons dashicons-trash"></span></a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else : ?>
                                <tr>
                                    <td colspan="6" class="no-results"><?php _e('No bus stops found.', 'bus-booking-manager'); ?></td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>

                    <!-- Pagination -->
                    <?php if ($total_pages > 1) : ?>
                        <div class="shuttle-pagination-area">
                            <div class="pagination-info">
                                <?php printf(__('Showing %d - %d of %d items', 'bus-booking-manager'), max(1, $start_num), $end_num, $total_terms); ?>
                            </div>
                            <div class="pagination-links">
                                <?php
                                echo paginate_links(array(
                                    'base'      => add_query_arg('paged', '%#%'),
                                    'format'    => '',
                                    'prev_text' => '<span class="dashicons dashicons-arrow-left-alt2"></span>',
                                    'next_text' => '<span class="dashicons dashicons-arrow-right-alt2"></span>',
                                    'total'     => $total_pages,
                                    'current'   => $paged,
                                ));
                                ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
<?php
    }
}

new BusStopListPageClass();
