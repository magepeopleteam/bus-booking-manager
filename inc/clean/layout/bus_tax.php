<div class="mp_tab_item" data-tab-item="#wbmm_bus_tax">
    <h3 class="wbbm_mp_tab_item_heading"><?php echo esc_html_e('Tax Settings', 'bus-booking-manager'); ?></h3>
    <p>
        <?php esc_html_e('Here you can configure Tax. To enable automated tax calculation, first ensure that "enable taxes and tax calculations" is checked on WooCommerce > Settings > General.', 'bus-booking-manager'); ?>
        <a href="<?php echo esc_url('https://woocommerce.com/document/woocommerce-shipping-and-tax/woocommerce-tax/'); ?>">
            <?php esc_html_e('View Documentation', 'bus-booking-manager'); ?>
        </a>
    </p>

    <section class="bgLight">
        <div>
             <label for="_tax_status" class="ra-item-label"><?php echo esc_html_e('Tax Settings.', 'bus-booking-manager'); ?></label>
             <br>
             <span><?php echo esc_html_e('Here you can set tax class and status.', 'bus-booking-manager'); ?></span>
        </div>
    </section>
    <div class="configuration_wrapper">
        <?php if(get_option( 'woocommerce_calc_taxes' ) == 'yes'){ ?>
        <section>
            <div>
                <label for="_tax_status" class="ra-item-label"><?php echo esc_html_e('Tax Status.', 'bus-booking-manager'); ?></label>
                <br>
                <span for="_tax_status" class="ra-item-label"><?php echo esc_html_e('Select a tax status.', 'bus-booking-manager'); ?></span>
            </div>
            <span>
            <?php
                global $post;
                $post_id = $post->ID;
                $tax_status = get_post_meta($post_id, '_tax_status', true);
                $tax_class = get_post_meta($post_id, '_tax_class', true);
                $MP_Global_Function = new MP_Global_Function();
                $tax_lists = $MP_Global_Function->all_tax_list();
            ?>
            <select name="_tax_status" id="_tax_status">
                <option value="taxable" <?php if($tax_status == 'taxable'){ echo 'selected'; } ?>>Taxable</option>
                <option value="shipping" <?php if($tax_status == 'shipping'){ echo 'selected'; } ?>>Shipping only</option>
                <option value="none" <?php if($tax_status == 'none'){ echo 'selected'; } ?>>None</option>
            </select>
        </section>
        <section>
            <div>
                <label for="_tax_status" class="ra-item-label"><?php echo esc_html_e('Tax Class.', 'bus-booking-manager'); ?></label>
                <br>
                <span for="_tax_status" class="ra-item-label"><?php echo esc_html_e('Select a tax class.', 'bus-booking-manager'); ?></span>
            </div>
            <span>
            <select name="_tax_class" id="_tax_class">
                <option value="standard" <?php echo ($tax_class == 'standard') ? esc_attr('selected') : ''; ?>><?php esc_html_e('Standard', 'bus-booking-manager'); ?></option>
                <?php foreach ($tax_lists as $key => $value) { ?>
                    <option value="<?php echo esc_attr( $key ); ?>" <?php selected( $tax_class, $key ); ?>><?php echo esc_html( $value ); ?></option>
                <?php } ?>
            </select>
            </span>
        </section>
        <?php } else { ?>
            <section>
                <div class="wbbm_alert_info"><?php echo esc_html_e( 'To enable automated tax calculation, first ensure that “enable taxes and tax calculations” is checked on WooCommerce > Settings > General. <a href="https://woocommerce.com/document/woocommerce-shipping-and-tax/woocommerce-tax/">View Documentation</a>', 'bus-booking-manager' ); ?></div>
            </section>
        <?php } ?>
    </div>
</div>