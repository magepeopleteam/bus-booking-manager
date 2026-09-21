<?php
/**
 * The booking drawer's framed checkout and order confirmation.
 *
 * The drawer loads the checkout, and then the order-received page, inside an
 * iframe. Both were arriving with the site header, footer and any fixed
 * mobile navigation the theme renders, because the only thing suppressing
 * them was a list of CSS selectors -- and that list can only ever name the
 * chrome of themes it was written against. A theme calling its header
 * something else, as most do, simply kept it.
 *
 * Rendering the framed request on this canvas instead means the theme's
 * header.php and footer.php are never reached, so there is no chrome to hide
 * and nothing to keep in step with any particular theme's markup. The page's
 * own content is rendered exactly as the loop produces it, so WooCommerce
 * still owns the checkout and the confirmation.
 *
 * @package Bus_Booking_Manager
 */

if (!defined('ABSPATH')) {
    die;
}
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php
// Kept so anything that legitimately injects here -- payment gateway
// scripts, consent banners -- still runs inside the frame.
wp_body_open();

while (have_posts()) {
    the_post();
    the_content();
}

wp_footer();
?>
</body>
</html>
