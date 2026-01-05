# Copilot / AI agent instructions — Bus Booking Manager plugin

Purpose
- Help AI contributors be immediately productive modifying this WordPress/WooCommerce plugin.

Quick start (local dev)
- Install into a WordPress site: copy this folder to `wp-content/plugins/bus-booking-manager` and activate the plugin.
- Enable debugging: set `WP_DEBUG` and `WP_DEBUG_LOG` to true in `wp-config.php` and tail `wp-content/debug.log` for PHP errors.
- No build system: PHP/JS/CSS files are source-ready — edit files in place and reload the site.

Big picture architecture
- Single WordPress plugin entry: [woocommerce-bus.php](woocommerce-bus.php) — it registers activation hooks, loads files only when WooCommerce is active, and performs DB migrations.
- PHP classes live under `inc/` and `lib/classes/`. UI templates are in `templates/`. Frontend assets are in `assets/` and `js/`.
- Custom Post Type: `wbbm_bus` is declared in `inc/wbbm_cpt.php` and represents vehicles/routes; shortcodes and front-facing booking are handled by `inc/wbbm_shortcode.php` and `inc/BusBookingManagerClass.php`.
- Enqueuing & localization: `inc/wbbm_enque.php` — use this to add or modify frontend/admin scripts and localized data.
- Data persistence: a custom table `{$wpdb->prefix}wbbm_bus_booking_list` is created on activation (`wbbm_booking_list_table_create`) and later altered via migration flags in `woocommerce-bus.php` and `inc/WBTM_Quick_Setup.php`.

Project-specific conventions & patterns
- Prefixes and naming: functions/options use `wbbm_` or `WBTM_` class prefixes; DB option keys often start with `wbbm_` (e.g., `wbbm_update_db_once_06`). Follow these prefixes for new globals.
- Migration pattern: add ALTER/CREATE logic guarded by `get_option('wbbm_update_db_once_x')` then `update_option(...)`. This prevents re-applying schema changes.
- Security & escaping: code uses `sanitize_text_field`, `esc_sql`, prepared queries via `$wpdb->prepare`. Reuse these utilities for inputs and DB operations.
- Internationalization: textdomain is `bus-booking-manager` and languages live in `/languages/`. Use `__()`/`_e()` with the plugin textdomain.

Key integration points to check when changing behavior
- Shortcodes/hooks: `inc/wbbm_shortcode.php` and `woocommerce-bus.php` (look for `add_action`, `add_shortcode`).
- WooCommerce integration: cart modifications and product interactions live in `inc/class-remove-bus-info-to-cart.php` and several `inc/*.php` files — test the add-to-cart -> checkout flow after edits.
- Admin settings: `inc/wbbm_admin_settings.php` controls plugin options pages.

Common tasks — quick examples
- Add a new admin field: update `inc/wbbm_admin_settings.php` and persist with the `wbbm_` option key.
- Add frontend data to JS: enqueue in `inc/wbbm_enque.php` and use `wp_localize_script` or `wp_add_inline_script`.
- Add DB migration: append guarded ALTER logic to `woocommerce-bus.php` or `inc/WBTM_Quick_Setup.php` and mark with `update_option('wbbm_update_db_once_XX','completed')`.

Testing guidance
- Manual verification is the primary test approach: create a `wbbm_bus` CPT, perform a booking via the public flow (shortcode), ensure the booking row appears in `{$wpdb->prefix}wbbm_bus_booking_list` and order flow works in WooCommerce.
- Use `WP_DEBUG` and PHP error logs for runtime issues. There are no unit tests or CI configs in the repo.

Files to inspect for context
- [woocommerce-bus.php](woocommerce-bus.php) — plugin bootstrap, activation, migrations
- [inc/wbbm_cpt.php](inc/wbbm_cpt.php) — CPT and meta definitions
- [inc/wbbm_shortcode.php](inc/wbbm_shortcode.php) — shortcodes/public endpoints
- [inc/wbbm_enque.php](inc/wbbm_enque.php) — asset enqueues and script localizations
- [inc/BusBookingManagerClass.php](inc/BusBookingManagerClass.php) — booking logic

When in doubt
- Follow existing naming prefixes (`wbbm_`, `WBTM_`) and reuse the migration-option pattern.
- Run changes in a local WP instance and walk the booking + checkout flow — most regressions show up end-to-end.

If anything here is unclear or you want additional examples (e.g., how a specific shortcode builds its data), tell me which area and I'll expand with code excerpts and tests.
