<div class="mp_tab_item" data-tab-item="#wbmm_bus_tax">
    <h3 class="wbbm_mp_tab_item_heading"><img src="<?php echo WBTM_PLUGIN_URL .'images/bus_arrow_left.png';?>"/><?php echo __('Tax', 'bus-booking-manager'); ?></h3>
    <div class="configuration_wrapper">
        <?php if(get_option( 'woocommerce_calc_taxes' ) == 'yes'){ ?>
        <div class="sec">
            <label for="_tax_status" class="ra-item-label">Tax Status</label>
            <span>
            <?php
                global $post;
                $post_id = $post->ID;
                $tax_status = get_post_meta($post_id, '_tax_status', true);
                $tax_class = get_post_meta($post_id, '_tax_class', true);
            ?>
            <select name="_tax_status" id="_tax_status">
                <option value="taxable" <?php if($tax_status == 'taxable'){ echo 'selected'; } ?>>Taxable</option>
                <option value="shipping" <?php if($tax_status == 'shipping'){ echo 'selected'; } ?>>Shipping only</option>
                <option value="none" <?php if($tax_status == 'none'){ echo 'selected'; } ?>>None</option>
            </select>
            </span>
        </div>
        <div class="sec">
            <label for="_tax_class" class="ra-item-label">Tax Class</label>
            <span>
            <select name="_tax_class" id="_tax_class">
                <option value="reduced-rate" <?php if($tax_class == 'reduced-rate'){ echo 'selected'; } ?>>Reduced rate</option>
                <option value="zero-rate" <?php if($tax_class == 'zero-rate'){ echo 'selected'; } ?>>Zero rate</option>
            </select>
            </span>
        </div>
        <?php } else { ?>
            <div class="sec">
                <div class="wbbm_alert_info"><?php echo __( 'To enable automated tax calculation, first ensure that “enable taxes and tax calculations” is checked on WooCommerce > Settings > General. <a href="https://woocommerce.com/document/woocommerce-shipping-and-tax/woocommerce-tax/">View Documentation</a>', 'bus-booking-manager' ); ?></div>
            </div>
        <?php } ?>
    </div>
</div>