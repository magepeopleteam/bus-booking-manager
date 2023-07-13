<?php function mage_book_now_area($available_seat = null){
    $currency_pos = get_option( 'woocommerce_currency_pos' );
    $is_sell_off = get_post_meta(get_the_ID(), 'wbbm_sell_off', true);

    $search_date = (isset($_GET['j_date']) ? $_GET['j_date'] : '');
    $current_date = date('Y-m-d');

    $boarding_time = get_wbbm_datetime(boarding_dropping_time(false, false), 'time');
    // If Current time is greater than bus time
    // Bus should not be shown in search result
    if($current_date === $search_date) {
        $search_timestamp = strtotime($search_date.' '.$boarding_time);
        if(current_time('timestamp') >= $search_timestamp ) {
            return;
        }
    }
?>
    <div class="mage_flex mage_book_now_area">
        <div class="mage_thumb mage-notification-area">
        <p class="mage-notification mage-seat-available"><?php _e('Only '.$available_seat.' Seat Available', 'bus-booking-manager'); ?></p>
        </div>
        <div class="mage_flex_equal">
            <div class="mage_sub_price">
                <div class="mage_sub_total"><div><?php echo wbbm_get_option('wbbm_sub_total_text', 'wbbm_label_setting_sec',__('Sub Total', 'bus-booking-manager')); echo ':'; ?></div> <span class="mage_subtotal_figure">0</span> </div>
            </div>
            <?php if( $is_sell_off != 'on' ) : 
                
                do_action('wbbm_before_add_cart_btn', get_the_ID());
                ?>

                <div class="mage_book_now mage_center_space">
                    <button type="button" class="mage_button mage_book_now <?php if($available_seat == 0){ echo 'cursor-disabled'; } ?>"><?php  echo wbbm_get_option('wbbm_book_now_text', 'wbbm_label_setting_sec',__('Book Now', 'bus-booking-manager')); ?></button>
                    <button type="submit" class="mage_hidden single_add_to_cart_button" name="add-to-cart" value="<?php echo get_post_meta(get_the_id(), 'link_wc_product', true); // link_wc_product echo esc_attr(get_the_id()); ?>"></button>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <?php
}