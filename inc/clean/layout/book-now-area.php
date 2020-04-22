<?php function mage_book_now_area(){
    $currency_pos = get_option( 'woocommerce_currency_pos' ); 
?>
    <div class="mage_flex mage_book_now_area">
        <div class="mage_thumb"></div>
        <div class="mage_flex_equal">
            <div class="mage_sub_price">
                <p class="mage_sub_total"><?php echo wbbm_get_option('wbbm_sub_total_text', 'wbbm_label_setting_sec',__('Sub Total :', 'bus-booking-manager')); ?><strong><?php if($currency_pos=="left"){ echo get_woocommerce_currency_symbol(); } ?><span>0<?php //echo wc_price(0); ?></span><?php if($currency_pos=="right"){ echo get_woocommerce_currency_symbol(); } ?></strong></p>
            </div>
            <div class="mage_book_now mage_center_space">
                <button type="button" class="mage_button mage_book_now"><?php  echo wbbm_get_option('wbbm_book_now_text', 'wbbm_label_setting_sec',__('Book Now', 'bus-booking-manager')); ?></button>
                <button type="submit" class="mage_hidden single_add_to_cart_button" name="add-to-cart" value="<?php echo esc_attr(get_the_id()); ?>"></button>
            </div>
        </div>
    </div>
    <?php
}