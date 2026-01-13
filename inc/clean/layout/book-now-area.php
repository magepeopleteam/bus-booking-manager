<?php

if (!defined('ABSPATH')) {
    die;
}

function mage_book_now_area($available_seat = null)
{
    $currency_pos = sanitize_text_field(get_option('woocommerce_currency_pos'));
    $is_sell_off = sanitize_text_field(get_post_meta(get_the_ID(), 'wbbm_sell_off', true));

    // phpcs:ignore WordPress.Security.NonceVerification.Recommended
    $search_date = (isset($_GET['j_date']) ? sanitize_text_field(wp_unslash($_GET['j_date'])) : '');
    $current_date = gmdate('Y-m-d');

    $boarding_time = sanitize_text_field(get_wbbm_datetime(boarding_dropping_time(false, false), 'time'));

    // If Current time is greater than bus time
    // Bus should not be shown in search result
    if ($current_date === $search_date) {
        $search_timestamp = strtotime($search_date . ' ' . $boarding_time);
        if (current_time('timestamp') >= $search_timestamp) {
            return;
        }
    }
?>
    <div class="mage_flex mage_book_now_area">
        <div class="mage_thumb mage-notification-area">
            <p class="mage-notification mage-seat-available">
                <?php
                /* translators: show available seats number */
                printf(esc_html__('Only %s Seat Available', 'bus-booking-manager'), esc_html(intval($available_seat)));
                ?>
            </p>
        </div>
        <div class="mage_flex_equal">
            <div class="mage_sub_price">
                <div class="mage_sub_total">
                    <div>
                        <?php echo esc_html(wbbm_get_option('wbbm_sub_total_text', 'wbbm_label_setting_sec', __('Sub Total', 'bus-booking-manager')));
                        echo esc_html(':'); ?>
                    </div>
                    <span class="mage_subtotal_figure">0</span>
                </div>
            </div>
            <?php if ($is_sell_off != 'on') :

                do_action('wbbm_before_add_cart_btn', get_the_ID());
            ?>

                <div class="mage_book_now mage_center_space">
                    <button type="button" class="mage_button mage_book_now <?php if ($available_seat == 0) {
                                                                                echo 'cursor-disabled';
                                                                            } ?>">
                        <?php echo esc_html(wbbm_get_option('wbbm_book_now_text', 'wbbm_label_setting_sec', __('Book Now', 'bus-booking-manager'))); ?>
                    </button>

                    <?php
                    // Nonce field for mage_book_now_area action — used for server-side verification
                    //wp_nonce_field('mage_book_now_area', 'mage_book_now_area_nonce');
                    ?>

                    <button type="submit" class="mage_hidden single_add_to_cart_button" name="add-to-cart" value="<?php echo esc_attr(sanitize_text_field(get_post_meta(get_the_ID(), 'link_wc_product', true))); ?>">
                    </button>
                </div>
            <?php endif; ?>
        </div>
    </div>
<?php
}
