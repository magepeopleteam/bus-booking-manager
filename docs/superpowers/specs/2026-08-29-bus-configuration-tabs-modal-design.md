# Unified Bus Configuration Tabs and Modal CRUD

## Goal

Replace the separate Bus Types, Bus Stops, Pickup Points, Bus Feature, and Pro Fleet Vehicles submenu entries with one **Bus Configuration** submenu. The page uses the plugin's current orange/white admin visual language, exposes each resource as a responsive tab, and performs normal add/edit work in an accessible modal without changing the stored data or public booking behavior.

## Compatibility boundaries

- Free remains the owner of the admin shell and the four taxonomy resources.
- Pro contributes Fleet Vehicles through the Free plugin's `wbbm_bus_configuration_tabs` filter.
- Existing taxonomy names, term IDs, slugs, descriptions, counts, and `feature_icon` term meta remain unchanged.
- Existing `wbbm_vehicle` post IDs, statuses, titles, and arbitrary legacy custom fields remain unchanged.
- Old custom list/edit URLs and native taxonomy URLs redirect to the matching tab and, for edit/add routes, open the matching modal.
- Fleet custom fields are managed inline inside the same modal. Existing public scalar custom-field rows retain their metadata IDs, support duplicates, and can be added, edited, or removed without touching protected or serialized Pro metadata.
- No frontend query, cart, checkout, capacity, route, ticket, or seat-plan behavior changes.

## Chosen architecture

The Free plugin adds a `WBBM_Bus_Configuration_Page` controller and a single scoped CSS/JavaScript bundle. Its tab registry contains four taxonomy descriptors and is passed through `wbbm_bus_configuration_tabs`. Each descriptor declares the backing object type, capability, labels, icon, and any supported extra field. The controller provides common list, search, pagination, save, delete, redirect, modal, notice, and responsive rendering behavior.

The Pro plugin filters the registry to add a post-backed Fleet Vehicles descriptor. Free provides generic, capability-checked post title/status/public-custom-field CRUD and fires `wbbm_bus_configuration_item_saved` after persistence so Pro or future add-ons can save owned metadata without Free depending on Pro. Pro redirects only the removed native fleet list route. Native `post.php` and `post-new.php` remain functional for old bookmarks and submissions, but normal add/edit/custom-field work remains in the modal.

The eight existing Free list/edit controllers are no longer loaded. The unified controller owns narrowly scoped redirects for their page slugs and for native taxonomy list/edit routes. This avoids duplicate submenu registration, competing redirect handlers, and unnecessary page assets while preserving old bookmarks.

The submenu uses a minimal shell capability filtered as `wbbm_bus_configuration_menu_capability` (default `read`) because WordPress accepts only one static menu capability. The render controller then builds the filtered registry, removes every descriptor for which the current user lacks its view/manage capability, selects the first permitted descriptor as the default, rejects a requested unauthorized tab, and returns a 403 response when no descriptor is permitted. This preserves access for a taxonomy-only or Fleet-only delegated manager without exposing operations that the user cannot perform.

This design is preferred over embedding legacy pages in tabs because embedded screens retain duplicate navigation and inconsistent layouts. It is preferred over a JavaScript SPA rewrite because the resources are small and WordPress server-side forms provide safer fallback behavior and lower regression risk.

## Page and interaction design

- A single white card uses the established orange brand block, page title, description, and orange primary action.
- Ordinary navigation links sit beneath the header, with `aria-current="page"` on the active link. This is a server-navigated page rather than a client-side tab widget, so it does not claim `tablist` keyboard semantics. Links remain horizontally scrollable on narrow screens.
- Each tab has its own search query and pagination state.
- Desktop uses the existing modern table hierarchy. Mobile converts each row into a labeled card so no horizontal table overflow is required.
- Add and Edit controls are real canonical `modal=add|edit&item_id=...` links. JavaScript intercepts them to open one `role="dialog"`, `aria-modal="true"` modal. The modal has a labelled title, close control, Escape handling, focus trapping, focus restoration, and a mobile full-height layout.
- The canonical modal URLs are fully server-rendered and the form posts normally with a nonce, so opening, editing, validation, and saving work when JavaScript is unavailable. JavaScript enhances the same links by using server-rendered row data for immediate opening; that data is presentation-only and is never trusted for mutation.
- Validation failure remains on the canonical page request and server-renders the modal with only sanitized submitted values and error codes. The modal reopens with an error summary, marks invalid fields, and moves focus to the first error. Successful saves use the POST/Redirect/GET pattern.
- Feature forms retain the Font Awesome class field and preview. Fleet forms expose title, status, and repeatable public custom fields in the modal; protected and serialized metadata is preserved without being exposed.
- Delete remains nonce-protected and uses a confirmation prompt. Fleet deletion uses the recoverable WordPress trash flow.

## Security and error handling

- Page visibility and tab visibility are filtered by operation-specific capabilities. Taxonomy descriptors use the registered taxonomy object's `manage_terms`, `edit_terms`, and `delete_terms` capabilities plus object-level `edit_term`/`delete_term` checks. Fleet uses the registered post type object's `create_posts`, `edit_posts`, `delete_posts`, and `publish_posts` capabilities plus object-level `edit_post`/`delete_post` checks.
- Every mutation verifies a resource-specific nonce and the capability for that operation.
- Before mutation, the server reloads the item and proves that its taxonomy or post type matches the selected descriptor. Missing, mismatched, trashed, and stale items are rejected. Submitted modal JSON, tab, item ID, and status are never considered proof of object ownership.
- Input is `wp_unslash()`ed before sanitization. Tab keys, item IDs, page numbers, search values, status, names, slugs, descriptions, and icon classes are sanitized at their boundaries.
- Fleet status transitions are whitelisted. Creating or moving an item to a publication status requires `publish_posts`; unauthorized explicit transitions are rejected. An existing nonstandard status is preserved unless the user explicitly requests a permitted transition and has the required capability.
- Writable Fleet statuses are exactly `draft`, `pending`, `publish`, and `private`. `publish` and `private` both require the post type's `publish_posts` capability. An unauthorized explicit transition is rejected with a validation error; it is never silently downgraded. Existing statuses outside the whitelist remain untouched when no authorized status change is submitted.
- Legacy/native redirects run only for safe `GET` or `HEAD` requests outside AJAX and REST. They never intercept POST, bulk/action, nonce-action, AJAX, or REST mutations, so a native form opened before deployment can still complete normally.
- Output is escaped by context; structured row data is JSON encoded then attribute escaped.
- `WP_Error` results are converted to escaped admin notices and never treated as success.
- Unknown/removed extension tabs fail closed and redirect to the default tab.

## Validation

- PHP syntax check all changed PHP files on the locally available PHP runtimes.
- JavaScript syntax check and CSS structural review.
- Render the real admin controller as an administrator and assert one submenu, navigation current state, modal ARIA markup, responsive labels, nonces, and Pro fleet contribution.
- Create, reload, update, and delete disposable records for each taxonomy; verify feature icon metadata survives.
- Create, reload, update, and trash a disposable fleet vehicle; verify unrelated legacy post meta survives title/status edits.
- Confirm old Free list/add/edit and native taxonomy URLs resolve to the matching configuration tab/modal; confirm the removed native fleet list resolves to the Fleet tab while direct `post.php`/`post-new.php` advanced routes and legacy bookmarks remain usable.
- Exercise keyboard-only modal open/close/focus trapping, screen-reader labels and current-page state, validation-error focus/error summary, and the full add/edit flow with JavaScript disabled.
- Add authorization regression coverage for taxonomy-only access, Fleet edit without publish access, users with no permitted descriptors, unauthorized `publish`/`private` transitions, forged cross-post IDs, mismatched-taxonomy term IDs, invalid/expired nonces, and direct requests for unauthorized tabs.
- Confirm existing frontend, cart, checkout, block compatibility declarations, and plugin activation remain unchanged.
