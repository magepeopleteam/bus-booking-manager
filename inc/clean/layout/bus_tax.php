<div class="mp_tab_item" data-tab-item="#wbmm_bus_tax">
    <h3 class="wbbm_mp_tab_item_heading"><?php esc_html_e('Tax Settings', 'bus-booking-manager'); ?></h3>
    <p>
        <?php esc_html_e('Here you can configure Tax. To enable automated tax calculation, first ensure that "enable taxes and tax calculations" is checked on WooCommerce > Settings > General.', 'bus-booking-manager'); ?>
        <a href="<?php echo esc_url('https://woocommerce.com/document/woocommerce-shipping-and-tax/woocommerce-tax/'); ?>">
            <?php esc_html_e('View Documentation', 'bus-booking-manager'); ?>
        </a>
    </p>

    <section class="bgLight">
        <div>
            <label for="_tax_status" class="ra-item-label"><?php esc_html_e('Tax Settings.', 'bus-booking-manager'); ?></label>
            <br>
            <span><?php esc_html_e('Here you can set tax class and status.', 'bus-booking-manager'); ?></span>
        </div>
    </section>
    <div class="configuration_wrapper">
        <?php if (get_option('woocommerce_calc_taxes') == 'yes') { ?>
            <section>
                <div>
                    <label for="_tax_status" class="ra-item-label"><?php esc_html_e('Tax Status.', 'bus-booking-manager'); ?></label>
                    <br>
                    <span for="_tax_status" class="ra-item-label"><?php esc_html_e('Select a tax status.', 'bus-booking-manager'); ?></span>
                </div>
                <span>
                <?php
                global $post;
                $wbbm_post_id = isset($post->ID) ? intval($post->ID) : 0;
                $wbbm_tax_status = get_post_meta($wbbm_post_id, '_tax_status', true);
                $wbbm_tax_class = get_post_meta($wbbm_post_id, '_tax_class', true);
                $wbbm_MP_Global_Function = new MP_Global_Function();
                $wbbm_tax_lists = $wbbm_MP_Global_Function->all_tax_list();
                ?>
                <select name="_tax_status" id="_tax_status">
                    <option value="taxable" <?php selected($wbbm_tax_status, 'taxable'); ?>><?php esc_html_e('Taxable', 'bus-booking-manager'); ?></option>
                    <option value="shipping" <?php selected($wbbm_tax_status, 'shipping'); ?>><?php esc_html_e('Shipping only', 'bus-booking-manager'); ?></option>
                    <option value="none" <?php selected($wbbm_tax_status, 'none'); ?>><?php esc_html_e('None', 'bus-booking-manager'); ?></option>
                </select>
            </section>
            <section>
                <div>
                    <label for="_tax_class" class="ra-item-label"><?php esc_html_e('Tax Class.', 'bus-booking-manager'); ?></label>
                    <br>
                    <span for="_tax_class" class="ra-item-label"><?php esc_html_e('Select a tax class.', 'bus-booking-manager'); ?></span>
                </div>
                <span>
                <select name="_tax_class" id="_tax_class">
                    <option value="standard" <?php selected($wbbm_tax_class, 'standard'); ?>><?php esc_html_e('Standard', 'bus-booking-manager'); ?></option>
                    <?php foreach ($wbbm_tax_lists as $wbbm_key => $wbbm_value) { ?>
                        <option value="<?php echo esc_attr($wbbm_key); ?>" <?php selected($wbbm_tax_class, $wbbm_key); ?>><?php echo esc_html($wbbm_value); ?></option>
                    <?php } ?>
                </select>
                </span>
            </section>
        <?php } else { ?>
            <section>
                <div class="wbbm_alert_info">
                    <?php 
                        printf(
                            wp_kses_post(
                                /* translators: %1$s: opening link tag, %2$s: closing link tag */
                                __( 
                                    'To enable automated tax calculation, first ensure that "enable taxes and tax calculations" is checked on WooCommerce > Settings > General. %1$sView Documentation%2$s',
                                    'bus-booking-manager'
                                )
                            ),
                            '<a href="' . esc_url( 'https://woocommerce.com/document/woocommerce-shipping-and-tax/woocommerce-tax/' ) . '" target="_blank" rel="noopener noreferrer">',
                            '</a>'
                        );
                    ?>
                </div>
            </section>
        <?php } ?>
    </div>
</div>
