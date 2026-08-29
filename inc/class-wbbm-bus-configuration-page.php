<?php

defined('ABSPATH') || exit;

/**
 * Unified management screen for bus taxonomies and extension-owned resources.
 */
final class WBBM_Bus_Configuration_Page
{
    const PAGE = 'wbbm-bus-configuration';

    /** @var array<string,string> */
    private $errors = array();

    /** @var array<string,mixed> */
    private $submitted = array();

    /** @var bool */
    private $force_modal = false;

    public function __construct()
    {
        add_action('admin_menu', array($this, 'register_page'), 30);
        add_action('admin_menu', array($this, 'remove_legacy_submenus'), 999);
        add_action('admin_enqueue_scripts', array($this, 'enqueue_assets'));
        add_action('admin_init', array($this, 'handle_request'), 5);
        add_action('admin_init', array($this, 'redirect_legacy_pages'), 50);
        add_filter('parent_file', array($this, 'parent_file'));
        add_filter('submenu_file', array($this, 'submenu_file'));
    }

    /** Register the one replacement submenu when at least one tab is available. */
    public function register_page()
    {
        if (empty($this->tabs(true))) {
            return;
        }

        add_submenu_page(
            'edit.php?post_type=wbbm_bus',
            __('Bus Configuration', 'bus-booking-manager'),
            __('Bus Configuration', 'bus-booking-manager'),
            apply_filters('wbbm_bus_configuration_menu_capability', 'read'),
            self::PAGE,
            array($this, 'render_page')
        );
    }

    /** Remove duplicate legacy menu entries without removing their routes. */
    public function remove_legacy_submenus()
    {
        $parent = 'edit.php?post_type=wbbm_bus';
        $slugs = array(
            'wbbm-bus-type-list',
            'wbbm-bus-stop-list',
            'wbbm-bus-pickpoint-list',
            'wbbm-bus-feature-list',
            'edit.php?post_type=wbbm_vehicle',
            'edit-tags.php?taxonomy=wbbm_bus_cat&amp;post_type=wbbm_bus',
            'edit-tags.php?taxonomy=wbbm_bus_stops&amp;post_type=wbbm_bus',
            'edit-tags.php?taxonomy=wbbm_bus_pickpoint&amp;post_type=wbbm_bus',
            'edit-tags.php?taxonomy=wbbm_bus_feature&amp;post_type=wbbm_bus',
        );
        foreach ($slugs as $slug) {
            remove_submenu_page($parent, $slug);
        }

        global $submenu;
        if (empty($submenu[$parent])) {
            return;
        }
        $configuration = null;
        foreach ($submenu[$parent] as $key => $item) {
            if (isset($item[2]) && self::PAGE === $item[2]) {
                $configuration = $item;
                unset($submenu[$parent][$key]);
                break;
            }
        }
        if ($configuration) {
            $items = array_values($submenu[$parent]);
            array_splice($items, 1, 0, array($configuration));
            $submenu[$parent] = $items;
        }
    }

    /**
     * Resource registry. Extensions may add descriptors through the filter.
     *
     * @param bool $permitted Only return tabs the current user may view.
     * @return array<string,array<string,mixed>>
     */
    private function tabs($permitted = false)
    {
        $tabs = array(
            'types' => array(
                'label' => __('Bus Types', 'bus-booking-manager'),
                'singular' => __('Bus Type', 'bus-booking-manager'),
                'taxonomy' => 'wbbm_bus_cat',
                'icon' => 'dashicons-tag',
                'filter_key' => 'wbbm_bus_cat',
            ),
            'stops' => array(
                'label' => __('Bus Stops', 'bus-booking-manager'),
                'singular' => __('Bus Stop', 'bus-booking-manager'),
                'taxonomy' => 'wbbm_bus_stops',
                'icon' => 'dashicons-location',
                'filter_key' => 'wbbm_bus_stops',
            ),
            'pickup-points' => array(
                'label' => __('Pickup Points', 'bus-booking-manager'),
                'singular' => __('Pickup Point', 'bus-booking-manager'),
                'taxonomy' => 'wbbm_bus_pickpoint',
                'icon' => 'dashicons-location-alt',
                'filter_key' => 'wbbm_bus_pickpoint',
            ),
            'features' => array(
                'label' => __('Bus Features', 'bus-booking-manager'),
                'singular' => __('Bus Feature', 'bus-booking-manager'),
                'taxonomy' => 'wbbm_bus_feature',
                'icon' => 'dashicons-star-filled',
                'filter_key' => 'wbbm_bus_feature',
                'feature_icon' => true,
            ),
        );

        $tabs = apply_filters('wbbm_bus_configuration_tabs', $tabs);
        if (!is_array($tabs)) {
            return array();
        }

        $valid = array();
        foreach ($tabs as $key => $tab) {
            $key = sanitize_key($key);
            if (!$key || !is_array($tab) || empty($tab['label']) || empty($tab['singular'])) {
                continue;
            }
            if (empty($tab['taxonomy']) && empty($tab['post_type'])) {
                continue;
            }
            if ($permitted && !$this->can($tab, 'view')) {
                continue;
            }
            $valid[$key] = $tab;
        }
        return $valid;
    }

    /** Check descriptor and object-level permissions. */
    private function can($tab, $operation, $item_id = 0)
    {
        if (!empty($tab['taxonomy'])) {
            $taxonomy = get_taxonomy($tab['taxonomy']);
            if (!$taxonomy) {
                return false;
            }
            $cap = 'manage_terms';
            if ('edit' === $operation) {
                $cap = 'edit_terms';
            } elseif ('delete' === $operation) {
                $cap = 'delete_terms';
            }
            if (!current_user_can($taxonomy->cap->{$cap})) {
                return false;
            }
            if ($item_id && 'edit' === $operation) {
                return current_user_can('edit_term', $item_id);
            }
            if ($item_id && 'delete' === $operation) {
                return current_user_can('delete_term', $item_id);
            }
            return true;
        }

        $post_type = get_post_type_object($tab['post_type']);
        if (!$post_type) {
            return false;
        }
        $cap = 'edit_posts';
        if ('create' === $operation) {
            $cap = isset($post_type->cap->create_posts) ? $post_type->cap->create_posts : $post_type->cap->edit_posts;
        } elseif ('delete' === $operation) {
            $cap = $post_type->cap->delete_posts;
        }
        if (!current_user_can($cap)) {
            return false;
        }
        if ($item_id && ('edit' === $operation || 'delete' === $operation)) {
            return current_user_can($operation . '_post', $item_id);
        }
        return true;
    }

    /** Resolve the requested tab, rejecting a known but unauthorized tab. */
    private function current_tab()
    {
        $requested = isset($_REQUEST['tab']) ? sanitize_key(wp_unslash($_REQUEST['tab'])) : '';
        $all = $this->tabs(false);
        $allowed = $this->tabs(true);
        if (!$allowed) {
            wp_die(esc_html__('You do not have permission to manage bus configuration.', 'bus-booking-manager'), '', array('response' => 403));
        }
        if ($requested && isset($all[$requested]) && !isset($allowed[$requested])) {
            wp_die(esc_html__('You do not have permission to access this configuration tab.', 'bus-booking-manager'), '', array('response' => 403));
        }
        return $requested && isset($allowed[$requested]) ? $requested : (string) key($allowed);
    }

    /** Process save/delete requests before output. */
    public function handle_request()
    {
        if ($this->handle_legacy_mutation()) {
            return;
        }
        if (empty($_REQUEST['page']) || self::PAGE !== sanitize_key(wp_unslash($_REQUEST['page']))) {
            return;
        }
        if ('POST' === strtoupper(isset($_SERVER['REQUEST_METHOD']) ? sanitize_text_field(wp_unslash($_SERVER['REQUEST_METHOD'])) : 'GET')) {
            $this->save_item();
            return;
        }
        if (isset($_GET['wbbm_action']) && 'delete' === sanitize_key(wp_unslash($_GET['wbbm_action']))) {
            $this->delete_item();
        }
    }

    /**
     * Finish submissions from a legacy page that was already open during update.
     * This is mutation handling, not a redirect shim, and retains the old nonces.
     *
     * @return bool Whether the request matched a legacy mutation.
     */
    private function handle_legacy_mutation()
    {
        $page = isset($_REQUEST['page']) ? sanitize_key(wp_unslash($_REQUEST['page'])) : '';
        $legacy = array(
            'wbbm-bus-type' => array('tab' => 'types', 'taxonomy' => 'wbbm_bus_cat', 'nonce_field' => 'wbbm_bus_type_nonce', 'nonce_action' => 'wbbm_bus_type_save'),
            'wbbm-bus-stop' => array('tab' => 'stops', 'taxonomy' => 'wbbm_bus_stops', 'nonce_field' => 'wbbm_bus_stop_save_nonce', 'nonce_action' => 'wbbm_bus_stop_nonce'),
            'wbbm-bus-pickpoint' => array('tab' => 'pickup-points', 'taxonomy' => 'wbbm_bus_pickpoint', 'nonce_field' => 'wbbm_bus_pickpoint_nonce', 'nonce_action' => 'wbbm_bus_pickpoint_save'),
            'wbbm-bus-feature' => array('tab' => 'features', 'taxonomy' => 'wbbm_bus_feature', 'nonce_field' => 'wbbm_bus_feature_nonce', 'nonce_action' => 'wbbm_bus_feature_save'),
        );
        $matched = null;
        foreach ($legacy as $prefix => $config) {
            if ($page === $prefix . '-list' || $page === $prefix . '-edit') {
                $matched = $config;
                break;
            }
        }
        if (!$matched) {
            return false;
        }

        $method = strtoupper(isset($_SERVER['REQUEST_METHOD']) ? sanitize_text_field(wp_unslash($_SERVER['REQUEST_METHOD'])) : 'GET');
        if ('GET' === $method && '-list' === substr($page, -5) && isset($_GET['action']) && 'delete' === sanitize_key(wp_unslash($_GET['action']))) {
            $term_id = isset($_GET['term_id']) ? absint(wp_unslash($_GET['term_id'])) : 0;
            $nonce_actions = array(
                'types' => 'delete-bus-type_',
                'stops' => 'delete-bus-stop_',
                'pickup-points' => 'delete-bus-pickpoint_',
                'features' => 'delete-bus-feature_',
            );
            check_admin_referer($nonce_actions[$matched['tab']] . $term_id);
            $tabs = $this->tabs(false);
            if (!$term_id || empty($tabs[$matched['tab']]) || !$this->can($tabs[$matched['tab']], 'delete', $term_id)) {
                wp_die(esc_html__('You do not have permission to delete this item.', 'bus-booking-manager'), '', array('response' => 403));
            }
            $term = get_term($term_id, $matched['taxonomy']);
            if (!$term || is_wp_error($term) || $matched['taxonomy'] !== $term->taxonomy) {
                wp_die(esc_html__('The requested item does not belong to this section.', 'bus-booking-manager'));
            }
            $deleted = wp_delete_term($term_id, $matched['taxonomy']);
            if (!$deleted || is_wp_error($deleted)) {
                wp_die(esc_html(is_wp_error($deleted) ? $deleted->get_error_message() : __('The item could not be deleted.', 'bus-booking-manager')));
            }
            wp_safe_redirect($this->page_url($matched['tab'], array('notice' => 'deleted')));
            exit;
        }

        if ('POST' !== $method || '-edit' !== substr($page, -5) || empty($_POST[$matched['nonce_field']])) {
            return false;
        }
        $nonce = sanitize_text_field(wp_unslash($_POST[$matched['nonce_field']]));
        if (!wp_verify_nonce($nonce, $matched['nonce_action'])) {
            wp_die(esc_html__('The link you followed has expired.', 'bus-booking-manager'), '', array('response' => 403));
        }
        $tabs = $this->tabs(false);
        $tab = isset($tabs[$matched['tab']]) ? $tabs[$matched['tab']] : null;
        $term_id = isset($_POST['term_id']) ? absint(wp_unslash($_POST['term_id'])) : 0;
        if (!$tab || !$this->can($tab, $term_id ? 'edit' : 'create', $term_id)) {
            wp_die(esc_html__('You do not have permission to save this item.', 'bus-booking-manager'), '', array('response' => 403));
        }
        $name = isset($_POST['term_name']) ? sanitize_text_field(wp_unslash($_POST['term_name'])) : '';
        if ('' === $name) {
            wp_die(esc_html__('Name is required.', 'bus-booking-manager'));
        }
        $args = array(
            'name' => $name,
            'slug' => isset($_POST['term_slug']) ? sanitize_title(wp_unslash($_POST['term_slug'])) : '',
            'description' => isset($_POST['term_description']) ? wp_kses_post(wp_unslash($_POST['term_description'])) : '',
        );
        $is_update = $term_id > 0;
        if ($term_id) {
            $term = get_term($term_id, $matched['taxonomy']);
            $result = (!$term || is_wp_error($term) || $matched['taxonomy'] !== $term->taxonomy)
                ? new WP_Error('invalid_item', __('The requested item does not belong to this section.', 'bus-booking-manager'))
                : wp_update_term($term_id, $matched['taxonomy'], $args);
        } else {
            $result = wp_insert_term($name, $matched['taxonomy'], $args);
            $term_id = !is_wp_error($result) && isset($result['term_id']) ? absint($result['term_id']) : 0;
        }
        if (is_wp_error($result)) {
            wp_die(esc_html($result->get_error_message()));
        }
        if ('features' === $matched['tab'] && isset($_POST['wbbm_feature_icon'])) {
            update_term_meta($term_id, 'feature_icon', $this->sanitize_icon(wp_unslash($_POST['wbbm_feature_icon'])));
        }
        wp_safe_redirect($this->page_url($matched['tab'], array('notice' => $is_update ? 'updated' : 'created')));
        exit;
    }

    /** Save one taxonomy term or extension-owned post. */
    private function save_item()
    {
        if (empty($_POST['wbbm_config_action']) || 'save' !== sanitize_key(wp_unslash($_POST['wbbm_config_action']))) {
            return;
        }
        $tab_key = $this->current_tab();
        $tabs = $this->tabs(false);
        $tab = $tabs[$tab_key];
        check_admin_referer('wbbm_config_save_' . $tab_key, 'wbbm_config_nonce');

        $item_id = isset($_POST['item_id']) ? absint(wp_unslash($_POST['item_id'])) : 0;
        $operation = $item_id ? 'edit' : 'create';
        if (!$this->can($tab, $operation, $item_id)) {
            wp_die(esc_html__('You do not have permission to save this item.', 'bus-booking-manager'), '', array('response' => 403));
        }

        $this->submitted = array(
            'item_id' => $item_id,
            'name' => isset($_POST['item_name']) ? sanitize_text_field(wp_unslash($_POST['item_name'])) : '',
            'slug' => isset($_POST['item_slug']) ? sanitize_title(wp_unslash($_POST['item_slug'])) : '',
            'description' => isset($_POST['item_description']) ? wp_kses_post(wp_unslash($_POST['item_description'])) : '',
            'feature_icon' => isset($_POST['feature_icon']) ? $this->sanitize_icon(wp_unslash($_POST['feature_icon'])) : '',
            'status' => isset($_POST['item_status']) ? sanitize_key(wp_unslash($_POST['item_status'])) : '',
            'token' => isset($_POST['item_token']) ? sanitize_text_field(wp_unslash($_POST['item_token'])) : '',
            'custom_fields' => $this->posted_custom_fields(),
            'deleted_meta_ids' => isset($_POST['deleted_meta_ids']) && is_array($_POST['deleted_meta_ids']) ? array_map('absint', wp_unslash($_POST['deleted_meta_ids'])) : array(),
        );

        if ('' === $this->submitted['name']) {
            $this->errors['name'] = __('Name is required.', 'bus-booking-manager');
            $this->force_modal = true;
            return;
        }

        if (!empty($tab['taxonomy'])) {
            $result = $this->save_term($tab, $item_id);
        } else {
            $result = $this->save_post_item($tab, $item_id);
        }
        if (is_wp_error($result)) {
            $this->errors['form'] = $result->get_error_message();
            $this->force_modal = true;
            return;
        }

        do_action('wbbm_bus_configuration_item_saved', $tab_key, absint($result), $this->submitted, $item_id > 0);
        wp_safe_redirect($this->page_url($tab_key, array('notice' => $item_id ? 'updated' : 'created')));
        exit;
    }

    /** Persist a taxonomy resource after binding the object to its descriptor. */
    private function save_term($tab, $item_id)
    {
        if ($item_id) {
            $term = get_term($item_id, $tab['taxonomy']);
            if (!$term || is_wp_error($term) || $tab['taxonomy'] !== $term->taxonomy) {
                return new WP_Error('invalid_item', __('The requested item no longer exists in this section.', 'bus-booking-manager'));
            }
            if (!$this->token_matches($this->term_token($term, $tab), $this->submitted['token'])) {
                return new WP_Error('stale_item', __('This item changed after the form was opened. Reload it and try again.', 'bus-booking-manager'));
            }
            $result = wp_update_term($item_id, $tab['taxonomy'], array(
                'name' => $this->submitted['name'],
                'slug' => $this->submitted['slug'],
                'description' => $this->submitted['description'],
            ));
            $saved_id = $item_id;
        } else {
            $result = wp_insert_term($this->submitted['name'], $tab['taxonomy'], array(
                'slug' => $this->submitted['slug'],
                'description' => $this->submitted['description'],
            ));
            $saved_id = !is_wp_error($result) && isset($result['term_id']) ? absint($result['term_id']) : 0;
        }
        if (is_wp_error($result)) {
            return $result;
        }
        if (!empty($tab['feature_icon'])) {
            update_term_meta($saved_id, 'feature_icon', $this->submitted['feature_icon']);
        }
        return $saved_id;
    }

    /** Persist a post-backed extension resource after strict post-type binding. */
    private function save_post_item($tab, $item_id)
    {
        $post_type = get_post_type_object($tab['post_type']);
        $existing = null;
        if ($item_id) {
            $existing = get_post($item_id);
            if (!$existing || $tab['post_type'] !== $existing->post_type || 'trash' === $existing->post_status) {
                return new WP_Error('invalid_item', __('The requested item no longer exists in this section.', 'bus-booking-manager'));
            }
            if (!$this->token_matches($this->post_token($existing), $this->submitted['token'])) {
                return new WP_Error('stale_item', __('This item changed after the form was opened. Reload it and try again.', 'bus-booking-manager'));
            }
        }

        $allowed_statuses = array('draft', 'pending', 'publish', 'private');
        $status = $this->submitted['status'];
        if (!in_array($status, $allowed_statuses, true)) {
            $status = $existing ? $existing->post_status : 'draft';
        }
        if (in_array($status, array('publish', 'private'), true) && !current_user_can($post_type->cap->publish_posts)) {
            return new WP_Error('status_forbidden', __('You do not have permission to publish or privately publish this item.', 'bus-booking-manager'));
        }
        if ($item_id && !empty($tab['custom_fields'])) {
            $meta_validation = $this->validate_custom_fields($item_id);
            if (is_wp_error($meta_validation)) {
                return $meta_validation;
            }
        }

        $args = array(
            'post_type' => $tab['post_type'],
            'post_title' => $this->submitted['name'],
            'post_status' => $status,
        );
        if ($item_id) {
            $args['ID'] = $item_id;
        }
        $saved_id = wp_insert_post(wp_slash($args), true);
        if (is_wp_error($saved_id)) {
            return $saved_id;
        }
        if (!empty($tab['custom_fields'])) {
            $meta_result = $this->save_custom_fields($saved_id);
            if (is_wp_error($meta_result)) {
                return $meta_result;
            }
        }
        return $saved_id;
    }

    /** Return sanitized public custom-field rows from the modal submission. */
    private function posted_custom_fields()
    {
        $ids = isset($_POST['meta_id']) && is_array($_POST['meta_id']) ? wp_unslash($_POST['meta_id']) : array();
        $keys = isset($_POST['meta_key']) && is_array($_POST['meta_key']) ? wp_unslash($_POST['meta_key']) : array();
        $values = isset($_POST['meta_value']) && is_array($_POST['meta_value']) ? wp_unslash($_POST['meta_value']) : array();
        $rows = array();
        $length = max(count($ids), count($keys), count($values));
        for ($index = 0; $index < $length; $index++) {
            $key = isset($keys[$index]) ? sanitize_key($keys[$index]) : '';
            if ('' === $key || is_protected_meta($key, 'post')) {
                continue;
            }
            $rows[] = array(
                'meta_id' => isset($ids[$index]) ? absint($ids[$index]) : 0,
                'key' => $key,
                'value' => isset($values[$index]) ? sanitize_textarea_field($values[$index]) : '',
            );
        }
        return $rows;
    }

    /** Update only public scalar custom fields that were explicitly submitted. */
    private function save_custom_fields($post_id)
    {
        foreach ($this->submitted['deleted_meta_ids'] as $meta_id) {
            $meta = get_metadata_by_mid('post', $meta_id);
            if ($meta && absint($meta->post_id) === absint($post_id) && !is_protected_meta($meta->meta_key, 'post')) {
                delete_metadata_by_mid('post', $meta_id);
            }
        }
        foreach ($this->submitted['custom_fields'] as $row) {
            if ($row['meta_id'] && in_array($row['meta_id'], $this->submitted['deleted_meta_ids'], true)) {
                continue;
            }
            if ($row['meta_id']) {
                $meta = get_metadata_by_mid('post', $row['meta_id']);
                if (!$meta || absint($meta->post_id) !== absint($post_id) || is_protected_meta($meta->meta_key, 'post')) {
                    return new WP_Error('invalid_meta', __('One custom field no longer belongs to this vehicle. Reload and try again.', 'bus-booking-manager'));
                }
                update_metadata_by_mid('post', $row['meta_id'], $row['value'], $row['key']);
            } else {
                add_post_meta($post_id, $row['key'], $row['value']);
            }
        }
        return true;
    }

    /** Validate every submitted metadata ID before changing the post itself. */
    private function validate_custom_fields($post_id)
    {
        $meta_ids = $this->submitted['deleted_meta_ids'];
        foreach ($this->submitted['custom_fields'] as $row) {
            if ($row['meta_id']) {
                $meta_ids[] = $row['meta_id'];
            }
        }
        foreach (array_unique($meta_ids) as $meta_id) {
            $meta = get_metadata_by_mid('post', $meta_id);
            if (!$meta || absint($meta->post_id) !== absint($post_id) || is_protected_meta($meta->meta_key, 'post')) {
                return new WP_Error('invalid_meta', __('One custom field no longer belongs to this vehicle. Reload and try again.', 'bus-booking-manager'));
            }
        }
        return true;
    }

    /** Delete/trash one item after nonce, capability, and object binding checks. */
    private function delete_item()
    {
        $tab_key = $this->current_tab();
        $tabs = $this->tabs(false);
        $tab = $tabs[$tab_key];
        $item_id = isset($_GET['item_id']) ? absint(wp_unslash($_GET['item_id'])) : 0;
        check_admin_referer('wbbm_config_delete_' . $tab_key . '_' . $item_id);
        if (!$item_id || !$this->can($tab, 'delete', $item_id)) {
            wp_die(esc_html__('You do not have permission to delete this item.', 'bus-booking-manager'), '', array('response' => 403));
        }

        if (!empty($tab['taxonomy'])) {
            $item = get_term($item_id, $tab['taxonomy']);
            $result = (!$item || is_wp_error($item) || $tab['taxonomy'] !== $item->taxonomy)
                ? new WP_Error('invalid_item', __('The requested item does not belong to this section.', 'bus-booking-manager'))
                : wp_delete_term($item_id, $tab['taxonomy']);
        } else {
            $item = get_post($item_id);
            $result = (!$item || $tab['post_type'] !== $item->post_type || 'trash' === $item->post_status)
                ? new WP_Error('invalid_item', __('The requested item does not belong to this section.', 'bus-booking-manager'))
                : wp_trash_post($item_id);
        }
        if (is_wp_error($result) || !$result) {
            wp_die(esc_html(is_wp_error($result) ? $result->get_error_message() : __('The item could not be deleted.', 'bus-booking-manager')));
        }
        do_action('wbbm_bus_configuration_item_deleted', $tab_key, $item_id);
        wp_safe_redirect($this->page_url($tab_key, array('notice' => 'deleted')));
        exit;
    }

    /** Redirect old GET/HEAD routes only; never consume a mutation request. */
    public function redirect_legacy_pages()
    {
        if (!$this->is_safe_navigation()) {
            return;
        }
        global $pagenow;
        $page = isset($_GET['page']) ? sanitize_key(wp_unslash($_GET['page'])) : '';
        $legacy = array(
            'wbbm-bus-type-list' => 'types', 'wbbm-bus-type-edit' => 'types',
            'wbbm-bus-stop-list' => 'stops', 'wbbm-bus-stop-edit' => 'stops',
            'wbbm-bus-pickpoint-list' => 'pickup-points', 'wbbm-bus-pickpoint-edit' => 'pickup-points',
            'wbbm-bus-feature-list' => 'features', 'wbbm-bus-feature-edit' => 'features',
        );
        if ($page && isset($legacy[$page])) {
            $args = array();
            if (false !== strpos($page, '-edit')) {
                $args['modal'] = !empty($_GET['term_id']) ? 'edit' : 'add';
                if (!empty($_GET['term_id'])) {
                    $args['item_id'] = absint(wp_unslash($_GET['term_id']));
                }
            }
            wp_safe_redirect($this->page_url($legacy[$page], $args));
            exit;
        }

        $taxonomy_map = array(
            'wbbm_bus_cat' => 'types',
            'wbbm_bus_stops' => 'stops',
            'wbbm_bus_pickpoint' => 'pickup-points',
            'wbbm_bus_feature' => 'features',
        );
        $taxonomy = isset($_GET['taxonomy']) ? sanitize_key(wp_unslash($_GET['taxonomy'])) : '';
        if ($taxonomy && isset($taxonomy_map[$taxonomy]) && in_array($pagenow, array('edit-tags.php', 'term.php'), true)) {
            $args = array();
            if ('term.php' === $pagenow) {
                $args['modal'] = 'edit';
                $args['item_id'] = isset($_GET['tag_ID']) ? absint(wp_unslash($_GET['tag_ID'])) : 0;
            }
            wp_safe_redirect($this->page_url($taxonomy_map[$taxonomy], $args));
            exit;
        }
    }

    private function is_safe_navigation()
    {
        $method = strtoupper(isset($_SERVER['REQUEST_METHOD']) ? sanitize_text_field(wp_unslash($_SERVER['REQUEST_METHOD'])) : 'GET');
        $doing_ajax = function_exists('wp_doing_ajax') ? wp_doing_ajax() : (defined('DOING_AJAX') && DOING_AJAX);
        if (!in_array($method, array('GET', 'HEAD'), true) || $doing_ajax || (defined('REST_REQUEST') && REST_REQUEST)) {
            return false;
        }
        return empty($_GET['action']) && empty($_GET['action2']) && empty($_GET['_wpnonce']);
    }

    public function enqueue_assets()
    {
        if (empty($_GET['page']) || self::PAGE !== sanitize_key(wp_unslash($_GET['page']))) {
            return;
        }
        $css = WBTM_PLUGIN_DIR . 'assets/admin/bus-configuration.css';
        $js = WBTM_PLUGIN_DIR . 'assets/admin/bus-configuration.js';
        wp_enqueue_style('wbbm-bus-configuration', WBTM_PLUGIN_URL . 'assets/admin/bus-configuration.css', array('dashicons'), file_exists($css) ? filemtime($css) : '1.0.0');
        wp_enqueue_script('wbbm-bus-configuration', WBTM_PLUGIN_URL . 'assets/admin/bus-configuration.js', array(), file_exists($js) ? filemtime($js) : '1.0.0', true);
    }

    public function parent_file($parent)
    {
        return (!empty($_GET['page']) && self::PAGE === sanitize_key(wp_unslash($_GET['page']))) ? 'edit.php?post_type=wbbm_bus' : $parent;
    }

    public function submenu_file($submenu)
    {
        return (!empty($_GET['page']) && self::PAGE === sanitize_key(wp_unslash($_GET['page']))) ? self::PAGE : $submenu;
    }

    /** Render the unified page. */
    public function render_page()
    {
        $tab_key = $this->current_tab();
        $tabs = $this->tabs(true);
        $tab = $tabs[$tab_key];
        $search = isset($_GET['s']) ? sanitize_text_field(wp_unslash($_GET['s'])) : '';
        $paged = isset($_GET['paged']) ? max(1, absint(wp_unslash($_GET['paged']))) : 1;
        $data = $this->items($tab, $search, $paged);
        $modal = $this->modal_state($tab_key, $tab);
        ?>
        <div class="wrap wbbm-config-wrap">
            <section class="wbbm-config-shell">
                <header class="wbbm-config-header">
                    <div class="wbbm-config-heading">
                        <span class="wbbm-config-mark"><span class="dashicons dashicons-admin-generic"></span></span>
                        <div><h1><?php esc_html_e('Bus Configuration', 'bus-booking-manager'); ?></h1><p><?php esc_html_e('Manage reusable bus data and fleet records from one place.', 'bus-booking-manager'); ?></p></div>
                    </div>
                    <?php if ($this->can($tab, 'create')) : ?>
                        <a class="wbbm-config-button wbbm-config-primary js-wbbm-modal-link" href="<?php echo esc_url($this->page_url($tab_key, array('modal' => 'add'))); ?>" data-mode="add"><span class="dashicons dashicons-plus-alt2"></span><?php echo esc_html(sprintf(__('Add %s', 'bus-booking-manager'), $tab['singular'])); ?></a>
                    <?php endif; ?>
                </header>

                <nav class="wbbm-config-tabs" aria-label="<?php esc_attr_e('Bus configuration sections', 'bus-booking-manager'); ?>">
                    <?php foreach ($tabs as $key => $item) : ?>
                        <a href="<?php echo esc_url($this->page_url($key)); ?>" <?php echo $key === $tab_key ? 'aria-current="page"' : ''; ?>><span class="dashicons <?php echo esc_attr(isset($item['icon']) ? $item['icon'] : 'dashicons-admin-generic'); ?>"></span><?php echo esc_html($item['label']); ?></a>
                    <?php endforeach; ?>
                </nav>

                <?php $this->render_notice(); ?>
                <div class="wbbm-config-toolbar">
                    <form method="get" action="<?php echo esc_url(admin_url('edit.php')); ?>">
                        <input type="hidden" name="post_type" value="wbbm_bus"><input type="hidden" name="page" value="<?php echo esc_attr(self::PAGE); ?>"><input type="hidden" name="tab" value="<?php echo esc_attr($tab_key); ?>">
                        <label class="screen-reader-text" for="wbbm-config-search"><?php echo esc_html(sprintf(__('Search %s', 'bus-booking-manager'), $tab['label'])); ?></label>
                        <span class="dashicons dashicons-search"></span><input id="wbbm-config-search" type="search" name="s" value="<?php echo esc_attr($search); ?>" placeholder="<?php echo esc_attr(sprintf(__('Search %s...', 'bus-booking-manager'), strtolower($tab['label']))); ?>">
                        <button class="wbbm-config-button wbbm-config-primary" type="submit"><?php esc_html_e('Search', 'bus-booking-manager'); ?></button>
                        <?php if ($search) : ?><a class="wbbm-config-button" href="<?php echo esc_url($this->page_url($tab_key)); ?>"><?php esc_html_e('Clear', 'bus-booking-manager'); ?></a><?php endif; ?>
                    </form>
                </div>

                <?php $this->render_table($tab_key, $tab, $data); ?>
            </section>
            <?php $this->render_modal($tab_key, $tab, $modal); ?>
        </div>
        <?php
        if (!empty($tab['feature_icon']) && function_exists('wbbm_all_font_awesome')) {
            wbbm_all_font_awesome();
        }
    }

    /** Fetch one page of terms or posts. */
    private function items($tab, $search, $paged)
    {
        $per_page = 20;
        if (!empty($tab['taxonomy'])) {
            $args = array('taxonomy' => $tab['taxonomy'], 'hide_empty' => false, 'number' => $per_page, 'offset' => ($paged - 1) * $per_page, 'search' => $search);
            $items = get_terms($args);
            $total = wp_count_terms(array('taxonomy' => $tab['taxonomy'], 'hide_empty' => false, 'search' => $search));
        } else {
            $query = new WP_Query(array('post_type' => $tab['post_type'], 'post_status' => array('draft', 'pending', 'publish', 'private', 'future'), 'posts_per_page' => $per_page, 'paged' => $paged, 's' => $search, 'orderby' => 'title', 'order' => 'ASC'));
            $items = $query->posts;
            $total = $query->found_posts;
        }
        return array('items' => is_wp_error($items) ? array() : $items, 'total' => is_wp_error($total) ? 0 : absint($total), 'pages' => max(1, (int) ceil(absint($total) / $per_page)), 'paged' => $paged);
    }

    private function render_table($tab_key, $tab, $data)
    {
        ?>
        <div class="wbbm-config-table-card"><table class="wbbm-config-table"><thead><tr>
            <th><?php esc_html_e('Name', 'bus-booking-manager'); ?></th>
            <?php if (!empty($tab['taxonomy'])) : ?><th><?php esc_html_e('Description', 'bus-booking-manager'); ?></th><th><?php esc_html_e('Slug', 'bus-booking-manager'); ?></th><th><?php esc_html_e('Count', 'bus-booking-manager'); ?></th><?php else : ?><th><?php esc_html_e('Status', 'bus-booking-manager'); ?></th><th><?php esc_html_e('Last updated', 'bus-booking-manager'); ?></th><?php endif; ?>
            <th class="wbbm-config-actions-heading"><?php esc_html_e('Action', 'bus-booking-manager'); ?></th>
        </tr></thead><tbody>
        <?php if (!$data['items']) : ?><tr><td class="wbbm-config-empty" colspan="5"><span class="dashicons dashicons-database-view"></span><?php echo esc_html(sprintf(__('No %s found.', 'bus-booking-manager'), strtolower($tab['label']))); ?></td></tr>
        <?php else : foreach ($data['items'] as $item) :
            $row = $this->row($tab, $item);
            $edit_url = $this->page_url($tab_key, array('modal' => 'edit', 'item_id' => $row['id']));
            $delete_url = wp_nonce_url($this->page_url($tab_key, array('wbbm_action' => 'delete', 'item_id' => $row['id'])), 'wbbm_config_delete_' . $tab_key . '_' . $row['id']);
            ?>
            <tr>
                <td data-label="<?php esc_attr_e('Name', 'bus-booking-manager'); ?>"><strong><a class="js-wbbm-modal-link" data-mode="edit" data-item="<?php echo esc_attr(wp_json_encode($row)); ?>" href="<?php echo esc_url($edit_url); ?>"><?php echo esc_html($row['name']); ?></a></strong><small><?php echo esc_html(sprintf(__('ID: %d', 'bus-booking-manager'), $row['id'])); ?></small><?php if (!empty($row['icon'])) : ?><span class="wbbm-config-feature-icon <?php echo esc_attr($row['icon']); ?>" aria-hidden="true"></span><?php endif; ?></td>
                <?php if (!empty($tab['taxonomy'])) : ?><td data-label="<?php esc_attr_e('Description', 'bus-booking-manager'); ?>"><?php echo $row['description'] ? esc_html($row['description']) : '&mdash;'; ?></td><td data-label="<?php esc_attr_e('Slug', 'bus-booking-manager'); ?>"><code><?php echo esc_html($row['slug']); ?></code></td><td data-label="<?php esc_attr_e('Count', 'bus-booking-manager'); ?>"><?php echo esc_html($row['count']); ?></td>
                <?php else : ?><td data-label="<?php esc_attr_e('Status', 'bus-booking-manager'); ?>"><span class="wbbm-config-status status-<?php echo esc_attr($row['status']); ?>"><?php echo esc_html($row['status_label']); ?></span></td><td data-label="<?php esc_attr_e('Last updated', 'bus-booking-manager'); ?>"><?php echo esc_html($row['updated']); ?></td><?php endif; ?>
                <td data-label="<?php esc_attr_e('Action', 'bus-booking-manager'); ?>"><div class="wbbm-config-actions">
                    <?php if ($this->can($tab, 'edit', $row['id'])) : ?><a class="wbbm-config-icon-button js-wbbm-modal-link" data-mode="edit" data-item="<?php echo esc_attr(wp_json_encode($row)); ?>" href="<?php echo esc_url($edit_url); ?>" aria-label="<?php echo esc_attr(sprintf(__('Edit %s', 'bus-booking-manager'), $row['name'])); ?>"><span class="dashicons dashicons-edit"></span></a><?php endif; ?>
                    <?php if ($this->can($tab, 'delete', $row['id'])) : ?><a class="wbbm-config-icon-button wbbm-config-delete" href="<?php echo esc_url($delete_url); ?>" data-confirm="<?php echo esc_attr(sprintf(__('Delete %s?', 'bus-booking-manager'), $row['name'])); ?>" aria-label="<?php echo esc_attr(sprintf(__('Delete %s', 'bus-booking-manager'), $row['name'])); ?>"><span class="dashicons dashicons-trash"></span></a><?php endif; ?>
                </div></td>
            </tr>
        <?php endforeach; endif; ?></tbody></table>
        <?php if ($data['pages'] > 1) : ?><div class="wbbm-config-pagination"><span><?php echo esc_html(sprintf(_n('%d item', '%d items', $data['total'], 'bus-booking-manager'), $data['total'])); ?></span><?php echo wp_kses_post(paginate_links(array('base' => add_query_arg('paged', '%#%'), 'total' => $data['pages'], 'current' => $data['paged'], 'type' => 'list'))); ?></div><?php endif; ?>
        </div>
        <?php
    }

    private function row($tab, $item)
    {
        if (!empty($tab['taxonomy'])) {
            return array('id' => absint($item->term_id), 'name' => $item->name, 'slug' => $item->slug, 'description' => $item->description, 'count' => absint($item->count), 'icon' => !empty($tab['feature_icon']) ? $this->sanitize_icon(get_term_meta($item->term_id, 'feature_icon', true)) : '', 'token' => $this->term_token($item, $tab));
        }
        $custom_fields = !empty($tab['custom_fields']) ? $this->public_custom_fields($item->ID) : array();
        return array('id' => absint($item->ID), 'name' => $item->post_title, 'status' => $item->post_status, 'status_label' => get_post_status_object($item->post_status) ? get_post_status_object($item->post_status)->label : $item->post_status, 'updated' => get_date_from_gmt($item->post_modified_gmt, get_option('date_format') . ' ' . get_option('time_format')), 'custom_fields' => $custom_fields, 'token' => $this->post_token($item, $custom_fields));
    }

    /** Return native-editor-compatible public scalar custom fields. */
    private function public_custom_fields($post_id)
    {
        global $wpdb;
        $rows = $wpdb->get_results($wpdb->prepare(
            "SELECT meta_id, meta_key, meta_value FROM {$wpdb->postmeta} WHERE post_id = %d ORDER BY meta_id ASC",
            absint($post_id)
        ));
        $fields = array();
        foreach ($rows as $row) {
            if (is_protected_meta($row->meta_key, 'post') || is_serialized($row->meta_value)) {
                continue;
            }
            $fields[] = array('meta_id' => absint($row->meta_id), 'key' => $row->meta_key, 'value' => (string) $row->meta_value);
        }
        return $fields;
    }

    private function modal_state($tab_key, $tab)
    {
        $mode = $this->force_modal ? (!empty($this->submitted['item_id']) ? 'edit' : 'add') : (isset($_GET['modal']) ? sanitize_key(wp_unslash($_GET['modal'])) : '');
        $item_id = $this->force_modal ? absint($this->submitted['item_id']) : (isset($_GET['item_id']) ? absint(wp_unslash($_GET['item_id'])) : 0);
        $values = $this->submitted;
        if (!$this->force_modal && 'edit' === $mode && $item_id) {
            if (!empty($tab['taxonomy'])) {
                $item = get_term($item_id, $tab['taxonomy']);
            } else {
                $item = get_post($item_id);
                if ($item && $item->post_type !== $tab['post_type']) {
                    $item = null;
                }
            }
            if (!$item || is_wp_error($item) || !$this->can($tab, 'edit', $item_id)) {
                $mode = '';
            } else {
                $values = $this->row($tab, $item);
            }
        }
        return array('open' => in_array($mode, array('add', 'edit'), true), 'mode' => $mode ?: 'add', 'values' => $values);
    }

    private function render_modal($tab_key, $tab, $modal)
    {
        $v = wp_parse_args($modal['values'], array('id' => 0, 'item_id' => 0, 'name' => '', 'slug' => '', 'description' => '', 'icon' => '', 'feature_icon' => '', 'status' => 'draft', 'token' => '', 'custom_fields' => array()));
        $id = absint($v['id'] ? $v['id'] : $v['item_id']);
        $icon = $v['feature_icon'] ? $v['feature_icon'] : $v['icon'];
        $title = ('edit' === $modal['mode']) ? sprintf(__('Edit %s', 'bus-booking-manager'), $tab['singular']) : sprintf(__('Add %s', 'bus-booking-manager'), $tab['singular']);
        ?>
        <div class="wbbm-config-modal<?php echo $modal['open'] ? ' is-open' : ''; ?>" id="wbbm-config-modal" <?php echo $modal['open'] ? '' : 'hidden'; ?>>
            <a class="wbbm-config-backdrop js-wbbm-modal-close" href="<?php echo esc_url($this->page_url($tab_key)); ?>" tabindex="-1" aria-hidden="true"></a>
            <div class="wbbm-config-dialog" role="dialog" aria-modal="true" aria-labelledby="wbbm-config-modal-title">
                <header><div><span class="dashicons <?php echo esc_attr(isset($tab['icon']) ? $tab['icon'] : 'dashicons-admin-generic'); ?>"></span><h2 id="wbbm-config-modal-title"><?php echo esc_html($title); ?></h2></div><a class="wbbm-config-modal-close js-wbbm-modal-close" href="<?php echo esc_url($this->page_url($tab_key)); ?>" aria-label="<?php esc_attr_e('Close dialog', 'bus-booking-manager'); ?>"><span class="dashicons dashicons-no-alt"></span></a></header>
                <form method="post" action="<?php echo esc_url($this->page_url($tab_key)); ?>" novalidate>
                    <?php wp_nonce_field('wbbm_config_save_' . $tab_key, 'wbbm_config_nonce'); ?>
                    <input type="hidden" name="wbbm_config_action" value="save"><input type="hidden" name="tab" value="<?php echo esc_attr($tab_key); ?>"><input type="hidden" name="item_id" id="wbbm-config-item-id" value="<?php echo esc_attr($id); ?>"><input type="hidden" name="item_token" id="wbbm-config-item-token" value="<?php echo esc_attr($v['token']); ?>">
                    <?php if ($this->errors) : ?><div class="wbbm-config-error" role="alert" tabindex="-1"><strong><?php esc_html_e('Please fix the following:', 'bus-booking-manager'); ?></strong><ul><?php foreach ($this->errors as $error) : ?><li><?php echo esc_html($error); ?></li><?php endforeach; ?></ul></div><?php endif; ?>
                    <div class="wbbm-config-fields">
                        <label for="wbbm-config-name"><?php esc_html_e('Name', 'bus-booking-manager'); ?> <span aria-hidden="true">*</span></label><input type="text" id="wbbm-config-name" name="item_name" value="<?php echo esc_attr($v['name']); ?>" required <?php echo isset($this->errors['name']) ? 'aria-invalid="true" autofocus' : ''; ?>>
                        <?php if (!empty($tab['taxonomy'])) : ?>
                            <label for="wbbm-config-slug"><?php esc_html_e('Slug', 'bus-booking-manager'); ?></label><input type="text" id="wbbm-config-slug" name="item_slug" value="<?php echo esc_attr($v['slug']); ?>">
                            <label for="wbbm-config-description"><?php esc_html_e('Description', 'bus-booking-manager'); ?></label><textarea id="wbbm-config-description" name="item_description" rows="5"><?php echo esc_textarea($v['description']); ?></textarea>
                            <?php if (!empty($tab['feature_icon'])) : ?><label><?php esc_html_e('Feature Icon', 'bus-booking-manager'); ?></label><div class="mp_input_add_icon wbbm-config-icon-picker"><button type="button" class="mp_input_add_icon_button"><input type="hidden" id="wbbm-config-feature-icon" name="feature_icon" value="<?php echo esc_attr($icon); ?>"><span class="<?php echo esc_attr($icon ?: 'fas fa-forward'); ?>" data-empty-text="<?php esc_attr_e('Select icon', 'bus-booking-manager'); ?>"></span><span><?php esc_html_e('Choose icon', 'bus-booking-manager'); ?></span></button></div><?php endif; ?>
                        <?php else : ?>
                            <label for="wbbm-config-status"><?php esc_html_e('Status', 'bus-booking-manager'); ?></label><select id="wbbm-config-status" name="item_status"><?php foreach (array('draft' => __('Draft', 'bus-booking-manager'), 'pending' => __('Pending review', 'bus-booking-manager'), 'publish' => __('Published', 'bus-booking-manager'), 'private' => __('Private', 'bus-booking-manager')) as $status => $label) : ?><option value="<?php echo esc_attr($status); ?>" <?php selected($v['status'], $status); ?>><?php echo esc_html($label); ?></option><?php endforeach; ?></select>
                            <?php if (!empty($tab['custom_fields'])) : ?>
                                <div class="wbbm-config-custom-fields">
                                    <div class="wbbm-config-section-heading"><div><h3><?php esc_html_e('Custom Fields', 'bus-booking-manager'); ?></h3><p><?php esc_html_e('Add vehicle details without leaving this modal.', 'bus-booking-manager'); ?></p></div><button type="button" class="wbbm-config-button" id="wbbm-config-add-field"><span class="dashicons dashicons-plus-alt2"></span><?php esc_html_e('Add field', 'bus-booking-manager'); ?></button></div>
                                    <div id="wbbm-config-meta-rows">
                                        <?php foreach ((array) $v['custom_fields'] as $field) : $this->render_custom_field_row($field); endforeach; ?>
                                    </div>
                                    <p class="wbbm-config-no-fields" <?php echo !empty($v['custom_fields']) ? 'hidden' : ''; ?>><?php esc_html_e('No custom fields added yet.', 'bus-booking-manager'); ?></p>
                                </div>
                                <template id="wbbm-config-meta-template"><?php $this->render_custom_field_row(array('meta_id' => 0, 'key' => '', 'value' => '')); ?></template>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                    <footer><a class="wbbm-config-button js-wbbm-modal-close" href="<?php echo esc_url($this->page_url($tab_key)); ?>"><?php esc_html_e('Cancel', 'bus-booking-manager'); ?></a><button class="wbbm-config-button wbbm-config-primary" type="submit"><span class="dashicons dashicons-saved"></span><?php esc_html_e('Save changes', 'bus-booking-manager'); ?></button></footer>
                </form>
            </div>
        </div>
        <?php
    }

    /** Render one accessible Fleet custom-field row. */
    private function render_custom_field_row($field)
    {
        $field = wp_parse_args($field, array('meta_id' => 0, 'key' => '', 'value' => ''));
        $meta_id = absint($field['meta_id']);
        ?>
        <div class="wbbm-config-meta-row">
            <input type="hidden" name="meta_id[]" value="<?php echo esc_attr($meta_id); ?>">
            <div><label><?php esc_html_e('Field name', 'bus-booking-manager'); ?><input type="text" name="meta_key[]" value="<?php echo esc_attr($field['key']); ?>" autocomplete="off"></label></div>
            <div><label><?php esc_html_e('Value', 'bus-booking-manager'); ?><textarea name="meta_value[]" rows="2"><?php echo esc_textarea($field['value']); ?></textarea></label></div>
            <?php if ($meta_id) : ?><label class="wbbm-config-remove-field"><input type="checkbox" name="deleted_meta_ids[]" value="<?php echo esc_attr($meta_id); ?>"><span class="dashicons dashicons-trash"></span><?php esc_html_e('Remove', 'bus-booking-manager'); ?></label><?php else : ?><button type="button" class="wbbm-config-remove-field js-wbbm-remove-new-field"><span class="dashicons dashicons-trash"></span><?php esc_html_e('Remove', 'bus-booking-manager'); ?></button><?php endif; ?>
        </div>
        <?php
    }

    private function render_notice()
    {
        $notice = isset($_GET['notice']) ? sanitize_key(wp_unslash($_GET['notice'])) : '';
        $messages = array('created' => __('Item created successfully.', 'bus-booking-manager'), 'updated' => __('Item updated successfully.', 'bus-booking-manager'), 'deleted' => __('Item deleted successfully.', 'bus-booking-manager'));
        if (isset($messages[$notice])) {
            echo '<div class="wbbm-config-notice" role="status"><span class="dashicons dashicons-yes-alt"></span>' . esc_html($messages[$notice]) . '</div>';
        }
    }

    private function page_url($tab, $args = array())
    {
        return add_query_arg(array_merge(array('post_type' => 'wbbm_bus', 'page' => self::PAGE, 'tab' => sanitize_key($tab)), $args), admin_url('edit.php'));
    }

    private function sanitize_icon($icon)
    {
        $icon = sanitize_text_field((string) $icon);
        return preg_match('/^[a-z0-9 _-]*$/i', $icon) ? $icon : '';
    }

    private function term_token($term, $tab)
    {
        $icon = !empty($tab['feature_icon']) ? get_term_meta($term->term_id, 'feature_icon', true) : '';
        return hash('sha256', $term->term_id . '|' . $term->name . '|' . $term->slug . '|' . $term->description . '|' . $icon);
    }

    private function post_token($post, $custom_fields = null)
    {
        if (null === $custom_fields) {
            $custom_fields = $this->public_custom_fields($post->ID);
        }
        return hash('sha256', $post->ID . '|' . $post->post_type . '|' . $post->post_title . '|' . $post->post_status . '|' . $post->post_modified_gmt . '|' . wp_json_encode($custom_fields));
    }

    private function token_matches($expected, $provided)
    {
        return $provided && hash_equals($expected, $provided);
    }
}

new WBBM_Bus_Configuration_Page();
