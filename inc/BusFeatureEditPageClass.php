<?php
if (!defined('ABSPATH')) exit;

/**
 * Bus Feature Edit Page Class
 * 
 * Handles the custom modern edit/add page for bus features (taxonomy wbbm_bus_feature),
 * including the specialized icon selection field.
 */
class BusFeatureEditPageClass
{
    public function __construct()
    {
        add_action('admin_menu', array($this, 'register_bus_feature_edit_page'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_assets'), 20);
        add_action('admin_init', array($this, 'handle_bus_feature_save'));
        add_action('admin_init', array($this, 'handle_redirects'));
        add_filter('parent_file', array($this, 'set_parent_menu'), 999);
        add_filter('submenu_file', array($this, 'set_submenu_active'), 999);
        add_action('admin_menu', function () {
            remove_submenu_page(
                'admin.php?post_type=wbbm_bus',
                'wbbm-bus-feature-edit'
            );
        }, 999);
    }

    /**
     * Register the hidden custom edit page
     */
    public function register_bus_feature_edit_page()
    {
        add_submenu_page(
            'admin.php?post_type=wbbm_bus',
            __('Edit Bus Feature', 'bus-booking-manager'),
            __('Edit Bus Feature', 'bus-booking-manager'),
            'manage_options',
            'wbbm-bus-feature-edit',
            array($this, 'render_bus_feature_edit_page')
        );
    }

    /**
     * Handle redirects from default taxonomy edit page
     */
    public function handle_redirects()
    {
        global $pagenow;
        if ($pagenow === 'term.php' && isset($_GET['taxonomy']) && $_GET['taxonomy'] === 'wbbm_bus_feature') {
            $tag_id = isset($_GET['tag_ID']) ? intval($_GET['tag_ID']) : 0;
            wp_safe_redirect(admin_url('edit.php?post_type=wbbm_bus&page=wbbm-bus-feature-edit&term_id=' . $tag_id));
            exit;
        }
    }

    /**
     * Set the parent menu to active
     */
    public function set_parent_menu($parent_file)
    {
        if (isset($_GET['page']) && $_GET['page'] === 'wbbm-bus-feature-edit') {
            return 'edit.php?post_type=wbbm_bus';
        }
        return $parent_file;
    }

    /**
     * Set the submenu to active
     */
    public function set_submenu_active($submenu_file)
    {
        if (isset($_GET['page']) && $_GET['page'] === 'wbbm-bus-feature-edit') {
            $submenu_file = 'wbbm-bus-feature-list';
        }
        return $submenu_file;
    }

    /**
     * Enqueue CSS and JS
     */
    public function enqueue_assets($hook)
    {
        if (strpos($hook, 'wbbm-bus-feature-edit') === false && (!isset($_GET['page']) || $_GET['page'] !== 'wbbm-bus-feature-edit')) {
            return;
        }

        wp_enqueue_style('bus-edit-css', WBTM_PLUGIN_URL . 'assets/admin/bus-edit.css', array(), time());
        
        // Ensure Font Awesome and any other icon assets are loaded
        if (function_exists('wbbm_all_font_awesome')) {
            wbbm_all_font_awesome();
        }
    }

    /**
     * Handle saving/updating the term
     */
    public function handle_bus_feature_save()
    {
        if (isset($_POST['wbbm_bus_feature_nonce']) && wp_verify_nonce($_POST['wbbm_bus_feature_nonce'], 'wbbm_bus_feature_save')) {
            if (!current_user_can('manage_options')) {
                return;
            }

            $term_id = isset($_POST['term_id']) ? intval($_POST['term_id']) : 0;
            $name = sanitize_text_field($_POST['term_name']);
            $slug = sanitize_title($_POST['term_slug']);
            $description = wp_kses_post($_POST['term_description']);
            $icon = sanitize_text_field($_POST['wbbm_feature_icon']);

            $args = array(
                'name'        => $name,
                'slug'        => $slug,
                'description' => $description,
            );

            if ($term_id) {
                wp_update_term($term_id, 'wbbm_bus_feature', $args);
                update_term_meta($term_id, 'feature_icon', $icon);
                $message = 'updated';
            } else {
                $term = wp_insert_term($name, 'wbbm_bus_feature', $args);
                if (!is_wp_error($term)) {
                    $term_id = $term['term_id'];
                    update_term_meta($term_id, 'feature_icon', $icon);
                    $message = 'created';
                } else {
                    wp_die($term->get_error_message());
                }
            }

            $redirect_url = add_query_arg(
                array(
                    'post_type' => 'wbbm_bus',
                    'page'      => 'wbbm-bus-feature-edit',
                    'term_id'   => $term_id,
                    'message'   => $message
                ),
                admin_url('edit.php')
            );
            wp_safe_redirect($redirect_url);
            exit;
        }
    }

    /**
     * Render the custom edit/add page
     */
    public function render_bus_feature_edit_page()
    {
        $term_id = isset($_GET['term_id']) ? intval($_GET['term_id']) : 0;
        $term = $term_id ? get_term($term_id, 'wbbm_bus_feature') : null;

        $name = $term ? $term->name : '';
        $slug = $term ? $term->slug : '';
        $description = $term ? $term->description : '';
        $icon = $term_id ? get_term_meta($term_id, 'feature_icon', true) : 'fas fa-forward';

?>
        <div class="wrap bus-edit-wrap">
            <div class="bus-edit-header">
                <div style="display: flex; align-items: center; gap: 15px;">
                    <a href="<?php echo admin_url('edit.php?post_type=wbbm_bus&page=wbbm-bus-feature-list'); ?>" class="back-btn">
                        <span class="dashicons dashicons-arrow-left-alt"></span>
                        <?php _e('Back to List', 'bus-booking-manager'); ?>
                    </a>
                    <h2><?php echo $term_id ? __('Edit Bus Feature', 'bus-booking-manager') . ': ' . esc_html($name) : __('Add New Bus Feature', 'bus-booking-manager'); ?></h2>
                </div>
                <?php if ($term_id) : ?>
                    <div class="header-actions">
                        <a href="<?php echo admin_url('edit.php?post_type=wbbm_bus&page=wbbm-bus-feature-edit'); ?>" class="btn btn-primary">
                            <span class="dashicons dashicons-plus-alt2"></span>
                            <?php _e('Add New Bus Feature', 'bus-booking-manager'); ?>
                        </a>
                    </div>
                <?php endif; ?>
            </div>

            <div class="bus-container">
                <form id="bus-feature-edit-form" method="post" action="">
                    <?php wp_nonce_field('wbbm_bus_feature_save', 'wbbm_bus_feature_nonce'); ?>
                    <input type="hidden" name="term_id" value="<?php echo esc_attr($term_id); ?>">

                    <div class="bus-edit-content">
                        <div class="bus-edit-left">
                            <div class="bus-card">
                                <div class="form-group">
                                    <label for="term_name"><?php _e('Name', 'bus-booking-manager'); ?> <span class="required">*</span></label>
                                    <input type="text" name="term_name" id="term_name" class="form-control" value="<?php echo esc_attr($name); ?>" required>
                                    <p class="description"><?php _e('The name of the feature.', 'bus-booking-manager'); ?></p>
                                </div>

                                <div class="form-group">
                                    <label for="term_slug"><?php _e('Slug', 'bus-booking-manager'); ?></label>
                                    <input type="text" name="term_slug" id="term_slug" class="form-control" value="<?php echo esc_attr($slug); ?>">
                                    <p class="description"><?php _e('The "slug" is the URL-friendly version of the name.', 'bus-booking-manager'); ?></p>
                                </div>

                                <div class="form-group">
                                    <label for="term_description"><?php _e('Description', 'bus-booking-manager'); ?></label>
                                    <textarea name="term_description" id="term_description" class="form-control" rows="5"><?php echo esc_textarea($description); ?></textarea>
                                    <p class="description"><?php _e('A short description of the feature.', 'bus-booking-manager'); ?></p>
                                </div>
                                
                                <div class="form-group">
                                    <label for="wbbm_feature_icon"><?php _e('Feature Icon', 'bus-booking-manager'); ?></label>
                                    <div id="field-wrapper-wbbm_feature_icon" class="wbtm_feature field-wrapper field-icon-wrapper field-icon-wrapper-wbbm_feature_icon">
                                        <div class="mp_input_add_icon">
                                            <button type="button" class="mp_input_add_icon_button dButton_xs ">
                                                <input type="hidden" name="wbbm_feature_icon" placeholder="" value="<?php echo esc_attr($icon); ?>">
                                                <span class="<?php echo esc_attr($icon); ?>" data-empty-text="<?php _e('Add Icon', 'bus-booking-manager'); ?>"></span>
                                                <!-- <span class="fas fa-times remove_input_icon active " title="<?php _e('Remove Icon', 'bus-booking-manager'); ?>"></span> -->
                                            </button>
                                        </div>
                                    </div>
                                    <p class="description"><?php _e('Please select a suitable icon for this feature.', 'bus-booking-manager'); ?></p>
                                </div>
                            </div>
                        </div>

                        <div class="bus-edit-right">
                            <div class="bus-card">
                                <h3><?php _e('Publish', 'bus-booking-manager'); ?></h3>
                                <div class="form-group" style="margin-top: 20px;">
                                    <button type="submit" id="bus-feature-submit" class="btn btn-primary btn-block" style="width: 100%; justify-content: center;">
                                        <?php echo $term_id ? __('Update Bus Feature', 'bus-booking-manager') : __('Add New Bus Feature', 'bus-booking-manager'); ?>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
<?php
    }
}

new BusFeatureEditPageClass();
