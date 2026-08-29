<?php

defined('ABSPATH') || exit;

/**
 * Shared tabbed-hub shell for Bus Booking Manager admin screens.
 *
 * A hub owns one submenu entry and renders several existing modules as
 * capability-aware tabs. It never duplicates business logic: each tab
 * delegates to the module callback that already owns that screen.
 *
 * Subclasses supply page(), parent_slug(), title(), description(), icon()
 * and register_tabs(). Everything else — capability filtering, tab
 * selection, the branded shell, legacy submenu removal and legacy URL
 * redirects — is handled here.
 */
abstract class WBBM_Admin_Hub
{
    /** Registered tabs for the current request, lazily built. */
    private $resolved_tabs = null;

    /** True while a tab callback is running, for modules that adapt inside a hub. */
    private static $rendering = false;

    /**
     * Whether a module is currently being rendered inside a hub tab.
     *
     * Modules use this to drop their own page masthead, which the hub header
     * already provides, while keeping the standalone page unchanged.
     */
    public static function is_rendering()
    {
        return self::$rendering;
    }

    /** Slug of this hub's admin page. */
    abstract public function page();

    /** Human title shown in the header and the submenu. */
    abstract public function title();

    /**
     * Tab registry.
     *
     * @return array<string,array{label:string,description?:string,icon?:string,capability?:string,callback:callable,legacy?:string|array}>
     */
    abstract protected function register_tabs();

    /** Parent menu slug. Bus screens live under the Bus CPT. */
    protected function parent_slug()
    {
        return 'edit.php?post_type=wbbm_bus';
    }

    /** One-line description under the title. */
    protected function description()
    {
        return '';
    }

    /** Dashicon for the header mark. */
    protected function icon()
    {
        return 'dashicons-admin-generic';
    }

    /** Where this hub sits among its siblings. */
    protected function menu_position()
    {
        return null;
    }

    /** Filter hook name used to extend the tab registry. */
    protected function tabs_filter()
    {
        return 'wbbm_admin_hub_tabs_' . str_replace('-', '_', $this->page());
    }

    /** Wire the hub into wp-admin. */
    public function boot()
    {
        add_action('admin_menu', array($this, 'register_page'), 20);
        add_action('admin_menu', array($this, 'remove_legacy_submenus'), 999);
        add_action('admin_enqueue_scripts', array($this, 'enqueue_assets'));
        add_action('admin_init', array($this, 'redirect_legacy_pages'), 50);
        add_filter('parent_file', array($this, 'parent_file'));
        add_filter('submenu_file', array($this, 'submenu_file'), 10, 2);
        add_action('wp_ajax_' . $this->ajax_action(), array($this, 'ajax_render_tab'));
    }

    /** AJAX action name for this hub's tab fetches. */
    public function ajax_action()
    {
        return 'wbbm_hub_tab_' . str_replace('-', '_', $this->page());
    }

    /**
     * Render one tab's panel for an in-page swap.
     *
     * Returns exactly the markup the full page would put inside
     * .wbbm-hub-panel, so the client can replace the panel without a reload.
     * Capability and nonce are checked the same way as a full page load.
     */
    public function ajax_render_tab()
    {
        check_ajax_referer($this->ajax_action(), 'nonce');

        $tabs = $this->tabs(true);
        if (empty($tabs)) {
            wp_send_json_error(array('message' => __('You do not have permission to view this page.', 'bus-booking-manager')), 403);
        }

        $requested = isset($_POST['tab']) ? sanitize_key(wp_unslash($_POST['tab'])) : '';
        if (!$requested || !isset($tabs[$requested])) {
            wp_send_json_error(array('message' => __('You do not have permission to open this section.', 'bus-booking-manager')), 403);
        }

        $tab = $tabs[$requested];
        if (!$this->can($tab)) {
            wp_send_json_error(array('message' => __('You do not have permission to open this section.', 'bus-booking-manager')), 403);
        }

        // Modules read filters and paging from $_GET, so mirror the query the
        // tab was requested with before rendering.
        $query = isset($_POST['query']) ? wp_unslash($_POST['query']) : '';
        if (is_string($query) && '' !== $query) {
            $parsed = array();
            wp_parse_str(ltrim($query, '?'), $parsed);
            if (is_array($parsed)) {
                $_GET = array_merge($_GET, $parsed);
                $_REQUEST = array_merge($_REQUEST, $parsed);
            }
        }
        $_GET['page'] = $this->page();
        $_GET['tab'] = $requested;

        self::$rendering = true;
        ob_start();
        try {
            call_user_func($tab['callback'], $requested, $tab, $this);
        } catch (\Throwable $e) {
            ob_end_clean();
            self::$rendering = false;
            wp_send_json_error(array('message' => __('This section could not be loaded.', 'bus-booking-manager')), 500);
        }
        $html = ob_get_clean();
        self::$rendering = false;

        wp_send_json_success(array(
            'tab'         => $requested,
            'label'       => $tab['label'],
            'description' => isset($tab['description']) ? $tab['description'] : '',
            'html'        => $html,
        ));
    }

    /* --------------------------------------------------------------- tabs */

    /**
     * Validated tab registry.
     *
     * @param bool $permitted Only return tabs the current user may open.
     * @return array<string,array<string,mixed>>
     */
    public function tabs($permitted = false)
    {
        if (null === $this->resolved_tabs) {
            $tabs = apply_filters($this->tabs_filter(), $this->register_tabs(), $this);
            $valid = array();

            if (is_array($tabs)) {
                foreach ($tabs as $key => $tab) {
                    $key = sanitize_key($key);
                    if (!$key || !is_array($tab) || empty($tab['label']) || empty($tab['callback'])) {
                        continue;
                    }
                    if (!is_callable($tab['callback'])) {
                        continue;
                    }
                    $valid[$key] = $tab;
                }
            }

            $this->resolved_tabs = $valid;
        }

        if (!$permitted) {
            return $this->resolved_tabs;
        }

        $allowed = array();
        foreach ($this->resolved_tabs as $key => $tab) {
            if ($this->can($tab)) {
                $allowed[$key] = $tab;
            }
        }

        return $allowed;
    }

    /** Capability check for one tab. */
    protected function can($tab)
    {
        $cap = isset($tab['capability']) ? $tab['capability'] : 'manage_options';

        return current_user_can($cap);
    }

    /** Capability required to see the hub at all: the loosest permitted tab. */
    public function menu_capability()
    {
        foreach ($this->tabs() as $tab) {
            if ($this->can($tab)) {
                return isset($tab['capability']) ? $tab['capability'] : 'manage_options';
            }
        }

        return 'manage_options';
    }

    /**
     * Tab key for this request.
     *
     * Falls back to the first permitted tab. A known-but-unauthorized tab is
     * rejected rather than silently swapped, so a bookmarked URL cannot make
     * it look as though the tab does not exist.
     *
     * @return string|false
     */
    public function current_tab()
    {
        $permitted = $this->tabs(true);
        if (empty($permitted)) {
            return false;
        }

        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only tab selector.
        $requested = isset($_GET['tab']) ? sanitize_key(wp_unslash($_GET['tab'])) : '';

        if ($requested && isset($permitted[$requested])) {
            return $requested;
        }

        if ($requested && isset($this->resolved_tabs[$requested])) {
            wp_die(
                esc_html__('You do not have permission to open this section.', 'bus-booking-manager'),
                esc_html__('Forbidden', 'bus-booking-manager'),
                array('response' => 403)
            );
        }

        return key($permitted);
    }

    /* ---------------------------------------------------------------- URLs */

    /**
     * URL for one tab of this hub.
     *
     * @param string $tab
     * @param array  $args Extra query args.
     * @return string
     */
    public function page_url($tab = '', $args = array())
    {
        $base = array('page' => $this->page());

        $parent = $this->parent_slug();
        if (0 === strpos($parent, 'edit.php')) {
            $query = wp_parse_url($parent, PHP_URL_QUERY);
            if ($query) {
                wp_parse_str($query, $parsed);
                $base = array_merge($parsed, $base);
            }
            $file = 'edit.php';
        } else {
            $file = 'admin.php';
        }

        if ($tab) {
            $base['tab'] = $tab;
        }

        return add_query_arg(array_merge($base, $args), admin_url($file));
    }

    /* ---------------------------------------------------------------- menu */

    public function register_page()
    {
        if (empty($this->tabs(true))) {
            return;
        }

        add_submenu_page(
            $this->parent_slug(),
            $this->title(),
            $this->title(),
            $this->menu_capability(),
            $this->page(),
            array($this, 'render_page'),
            $this->menu_position()
        );
    }

    /**
     * Drop the individual submenu rows the hub replaces.
     *
     * The page registrations themselves stay alive so old URLs, form actions
     * and nonce targets keep resolving; only the duplicate menu row goes.
     */
    public function remove_legacy_submenus()
    {
        $parent = $this->parent_slug();

        foreach ($this->tabs() as $tab) {
            if (empty($tab['legacy'])) {
                continue;
            }
            foreach ((array) $tab['legacy'] as $slug) {
                remove_submenu_page($parent, $slug);
            }
        }
    }

    /**
     * Send safe navigation for a replaced page to its hub tab.
     *
     * Only bare GET/HEAD page views are redirected. Anything carrying an
     * action, a nonce, bulk parameters, or arriving via AJAX/REST/POST is
     * left untouched so existing handlers keep working.
     */
    public function redirect_legacy_pages()
    {
        if (wp_doing_ajax() || (defined('REST_REQUEST') && REST_REQUEST) || wp_doing_cron()) {
            return;
        }

        $method = isset($_SERVER['REQUEST_METHOD']) ? strtoupper(sanitize_text_field(wp_unslash($_SERVER['REQUEST_METHOD']))) : 'GET';
        if ('GET' !== $method && 'HEAD' !== $method) {
            return;
        }

        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Navigation only; mutations are excluded below.
        $requested = isset($_GET['page']) ? sanitize_key(wp_unslash($_GET['page'])) : '';
        if (!$requested || $requested === $this->page()) {
            return;
        }

        foreach (array('_wpnonce', '_wp_http_referer', 'action', 'action2', 'doaction', 'delete_all') as $mutation) {
            if (isset($_GET[$mutation])) {
                return;
            }
        }

        foreach ($this->tabs() as $key => $tab) {
            if (empty($tab['legacy'])) {
                continue;
            }
            if (!in_array($requested, (array) $tab['legacy'], true)) {
                continue;
            }
            if (!$this->can($tab)) {
                return;
            }

            // Preserve any remaining query args the module may rely on.
            // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Navigation only.
            $carry = wp_unslash($_GET);
            unset($carry['page'], $carry['post_type'], $carry['tab']);
            $carry = array_map('sanitize_text_field', array_filter($carry, 'is_scalar'));

            wp_safe_redirect($this->page_url($key, $carry));
            exit;
        }
    }

    /** Keep the Bus menu open while a hub page is showing. */
    public function parent_file($parent)
    {
        return $this->is_hub_screen() ? $this->parent_slug() : $parent;
    }

    public function submenu_file($submenu_file, $parent_file = '')
    {
        return $this->is_hub_screen() ? $this->page() : $submenu_file;
    }

    protected function is_hub_screen()
    {
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Screen check only.
        return !empty($_GET['page']) && $this->page() === sanitize_key(wp_unslash($_GET['page']));
    }

    /* -------------------------------------------------------------- assets */

    public function enqueue_assets()
    {
        if (!$this->is_hub_screen()) {
            return;
        }

        self::register_shell_style();
        wp_enqueue_style('wbbm-admin-shell');
        wp_enqueue_script('wbbm-admin-shell');

        $tabs = array();
        foreach ($this->tabs(true) as $key => $tab) {
            $tabs[$key] = isset($tab['description']) ? $tab['description'] : '';
        }

        // Config for the in-page tab swap; see assets/admin/wbbm-admin-shell.js.
        wp_localize_script('wbbm-admin-shell', 'wbbmHub', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'action'  => $this->ajax_action(),
            'nonce'   => wp_create_nonce($this->ajax_action()),
            'page'    => $this->page(),
            'tabs'    => $tabs,
            'i18n'    => array(
                'loading' => __('Loading…', 'bus-booking-manager'),
                'failed'  => __('This section could not be loaded. Reload the page to try again.', 'bus-booking-manager'),
            ),
        ));
    }

    /**
     * Screens this plugin owns, for the plugin-wide theme layer.
     *
     * Covers hubs, the bus/shuttle post types and their edit screens.
     */
    public static function is_plugin_screen()
    {
        $screen = function_exists('get_current_screen') ? get_current_screen() : null;
        if ($screen) {
            $types = array('wbbm_bus', 'wbbm_shuttle', 'wbbm_vehicle', 'wbbm_booking');
            if (in_array($screen->post_type, $types, true)) {
                return true;
            }
            if (false !== strpos((string) $screen->id, 'wbbm') || false !== strpos((string) $screen->id, 'wbtm')) {
                return true;
            }
        }

        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Screen check only.
        $page = isset($_GET['page']) ? sanitize_key(wp_unslash($_GET['page'])) : '';

        return $page && (0 === strpos($page, 'wbbm') || 0 === strpos($page, 'wbtm') || 'admin_purchase_ticket' === $page || 'passenger_list' === $page || 'create_ticket' === $page);
    }

    /**
     * Boot the plugin-wide theme layer.
     *
     * One stylesheet and one body class across every plugin admin screen, so
     * hubs and the standalone list/edit screens share a palette instead of
     * each carrying its own colours.
     */
    public static function boot_theme()
    {
        add_action('admin_enqueue_scripts', array(__CLASS__, 'enqueue_theme'), 20);
        add_filter('admin_body_class', array(__CLASS__, 'body_class'));
    }

    public static function enqueue_theme()
    {
        if (!self::is_plugin_screen()) {
            return;
        }
        self::register_shell_style();
        wp_enqueue_style('wbbm-admin-shell');
        wp_enqueue_script('wbbm-admin-shell');
    }

    public static function body_class($classes)
    {
        if (self::is_plugin_screen()) {
            $classes .= ' wbbm-admin';
        }

        return $classes;
    }

    /**
     * Register the shared shell assets once, from the free plugin.
     *
     * Pro hubs call this then enqueue the same handles, so both plugins
     * render one design system from a single source file.
     */
    public static function register_shell_style()
    {
        if (wp_style_is('wbbm-admin-shell', 'registered')) {
            return;
        }

        $css_rel = 'assets/admin/wbbm-admin-shell.css';
        $js_rel = 'assets/admin/wbbm-admin-shell.js';
        $css = WBTM_PLUGIN_DIR . $css_rel;
        $js = WBTM_PLUGIN_DIR . $js_rel;

        wp_register_style(
            'wbbm-admin-shell',
            WBTM_PLUGIN_URL . $css_rel,
            array('dashicons'),
            file_exists($css) ? filemtime($css) : '1.0.0'
        );
        wp_register_script(
            'wbbm-admin-shell',
            WBTM_PLUGIN_URL . $js_rel,
            array(),
            file_exists($js) ? filemtime($js) : '1.0.0',
            true
        );
    }

    /* -------------------------------------------------------------- render */

    /** Extra buttons for the header. Subclasses may override. */
    protected function header_actions($tab_key, $tab)
    {
        return '';
    }

    public function render_page()
    {
        $tabs = $this->tabs(true);
        if (empty($tabs)) {
            wp_die(
                esc_html__('You do not have permission to view this page.', 'bus-booking-manager'),
                esc_html__('Forbidden', 'bus-booking-manager'),
                array('response' => 403)
            );
        }

        $tab_key = $this->current_tab();
        $tab = $tabs[$tab_key];
        $actions = $this->header_actions($tab_key, $tab);
        ?>
        <div class="wrap wbbm-hub">
            <section class="wbbm-hub-shell">
                <header class="wbbm-hub-header">
                    <div class="wbbm-hub-heading">
                        <span class="wbbm-hub-mark" aria-hidden="true"><span class="dashicons <?php echo esc_attr($this->icon()); ?>"></span></span>
                        <div>
                            <h1><?php echo esc_html($this->title()); ?></h1>
                            <?php if ($this->description()) : ?>
                                <p><?php echo esc_html($this->description()); ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php if ($actions) : ?>
                        <div class="wbbm-hub-header-actions"><?php echo wp_kses_post($actions); ?></div>
                    <?php endif; ?>
                </header>

                <?php if (count($tabs) > 1) : ?>
                    <nav class="wbbm-hub-tabs" data-wbbm-tabs aria-label="<?php echo esc_attr(sprintf(/* translators: %s: hub title. */ __('%s sections', 'bus-booking-manager'), $this->title())); ?>">
                        <?php foreach ($tabs as $key => $item) : ?>
                            <a href="<?php echo esc_url($this->page_url($key)); ?>" data-wbbm-tab="<?php echo esc_attr($key); ?>"<?php echo $key === $tab_key ? ' aria-current="page"' : ''; ?>>
                                <span class="dashicons <?php echo esc_attr(isset($item['icon']) ? $item['icon'] : 'dashicons-marker'); ?>" aria-hidden="true"></span><?php echo esc_html($item['label']); ?>
                            </a>
                        <?php endforeach; ?>
                    </nav>
                <?php endif; ?>

                <?php // Anchor for WP admin notices; keeps them out of the header. ?>
                <hr class="wp-header-end">

                <p class="wbbm-hub-subtitle" data-wbbm-subtitle<?php echo empty($tab['description']) ? ' hidden' : ''; ?>><?php echo esc_html(isset($tab['description']) ? $tab['description'] : ''); ?></p>

                <div class="wbbm-hub-panel wbbm-hub-panel--<?php echo esc_attr($tab_key); ?>" data-wbbm-panel>
                    <?php $this->render_tab($tab_key, $tab); ?>
                </div>
            </section>
        </div>
        <?php
    }

    /**
     * Run one tab's callback.
     *
     * The capability is re-checked here, not only at tab-selection time, so a
     * filtered-in tab cannot bypass its own gate.
     */
    protected function render_tab($tab_key, $tab)
    {
        if (!$this->can($tab)) {
            wp_die(
                esc_html__('You do not have permission to open this section.', 'bus-booking-manager'),
                esc_html__('Forbidden', 'bus-booking-manager'),
                array('response' => 403)
            );
        }

        self::$rendering = true;
        try {
            call_user_func($tab['callback'], $tab_key, $tab, $this);
        } finally {
            self::$rendering = false;
        }
    }

    /* -------------------------------------------------------------- helpers */

    /**
     * Is the current request a given module's screen?
     *
     * True for the module's own legacy page, and for the hub tab that now
     * hosts it (including the hub's default tab, when no ?tab= is present).
     * Modules use this to enqueue their assets in both places.
     *
     * @param string|array $legacy_pages Legacy page slug(s).
     * @param string       $hub_page     Hub page slug.
     * @param string       $tab          Tab key within the hub.
     * @param bool         $is_first_tab Whether that tab is the hub default.
     */
    public static function is_module_screen($legacy_pages, $hub_page = '', $tab = '', $is_first_tab = false)
    {
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Screen check only.
        $page = isset($_GET['page']) ? sanitize_key(wp_unslash($_GET['page'])) : '';
        if (!$page) {
            return false;
        }

        if (in_array($page, (array) $legacy_pages, true)) {
            return true;
        }

        if (!$hub_page || $page !== $hub_page) {
            return false;
        }

        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Screen check only.
        $current = isset($_GET['tab']) ? sanitize_key(wp_unslash($_GET['tab'])) : '';

        return $current === $tab || ('' === $current && $is_first_tab);
    }

    /** Render a standard empty state. */
    public static function empty_state($title, $body = '', $icon = 'dashicons-info-outline')
    {
        ?>
        <div class="wbbm-hub-empty">
            <span class="dashicons <?php echo esc_attr($icon); ?>" aria-hidden="true"></span>
            <strong><?php echo esc_html($title); ?></strong>
            <?php if ($body) : ?><span><?php echo esc_html($body); ?></span><?php endif; ?>
        </div>
        <?php
    }
}
