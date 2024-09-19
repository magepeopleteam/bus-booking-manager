<div class="mp_tab_item" data-tab-item="#wbtm_seat_price">
    <h3 class="wbbm_mp_tab_item_heading">
        <?php echo esc_html_e($cpt_label).' '.esc_html_e('Seat Price Settings', 'bus-booking-manager'); ?>
    </h3>
    <p><?php esc_html_e('Here you can configure seat price.', 'bus-booking-manager'); ?></p>
    <div class="wbbm_seat_price_inner_wrap">
    <?php
    $terms = get_terms($get_terms_default_attributes);
    $discount_price_switch = wbbm_get_option('discount_price_switch', 'wbbm_general_setting_sec', 'off');

    ?>
 <input type="hidden" id="price_bus_record" value="<?php echo esc_attr( ($wbbm_bus_prices == '') ? $wbbm_bus_prices : count($wbbm_bus_prices) ); ?>">
<input type="hidden" id="discount_price_switch" value="<?php echo esc_attr( $discount_price_switch ); ?>">
<input type="hidden" id="entire_bus_booking" value="<?php echo esc_attr( $entire_bus_booking ); ?>">

    <section class="bgLight">
        <div>
            <label for="">
                <?php esc_html_e('Price Settings', 'bus-booking-manager'); ?>
            </label>
            <br>
        <span>
            <?php esc_html_e('Configure seat price based on borading and dropping point.', 'bus-booking-manager'); ?>
        </span>
        </div>
    </section>
    <section>
        <div style="width:100%;overflow-x:auto;">
        <table id="repeatable-fieldset-price-one" width="auto">
            <tbody class="auto-generated">
            <tr>
                <th>
                    <?php esc_html_e('Boarding Point', 'bus-booking-manager'); ?>
                </th>
                <th>
                    <?php esc_html_e('Dropping Point', 'bus-booking-manager'); ?>
                </th>
                <th>
                    <?php esc_html_e(wbbm_get_option('wbbm_adult_text', 'wbbm_label_setting_sec') ? wbbm_get_option('wbbm_adult_text', 'wbbm_label_setting_sec') :esc_html_e('Adult', 'bus-booking-manager') . ' Fare', 'bus-booking-manager'); ?>
                </th>
                <th>
                    <?php esc_html_e(wbbm_get_option('wbbm_child_text', 'wbbm_label_setting_sec') ? wbbm_get_option('wbbm_child_text', 'wbbm_label_setting_sec') :esc_html_e('Child', 'bus-booking-manager') . ' Fare', 'bus-booking-manager'); ?>
                </th>
                <th>
                    <?php esc_html_e(wbbm_get_option('wbbm_infant_text', 'wbbm_label_setting_sec') ? wbbm_get_option('wbbm_infant_text', 'wbbm_label_setting_sec') :esc_html_e('Infant', 'bus-booking-manager') . ' Fare', 'bus-booking-manager'); ?>
                </th>
                <?php if($entire_bus_booking == 'on'): ?>
                    <th><?php esc_html_e(wbbm_get_option('wbbm_entire_bus_text', 'wbbm_label_setting_sec') ? wbbm_get_option('wbbm_entire_bus_text', 'wbbm_label_setting_sec') :esc_html_e('Entire Bus', 'bus-booking-manager') . ' Fare', 'bus-booking-manager'); ?></th>
                <?php endif; ?>
                <th></th>
            </tr>
            <?php
            if ($wbbm_bus_prices) :
                $coun = 0;
                foreach ($wbbm_bus_prices as $field) {
                    ?>
                    <tr>
                        <td>
                            <div class="wbbm_bus_route_select">
                            <span class="wbbm_bus_route_icon wbbm_bus_route_icon1"> <img src="<?php echo esc_url( WBTM_PLUGIN_URL . 'images/bus_route_map.png' ); ?>" /> </span>
                                <select name="wbbm_bus_bp_price_stop[]" class='seat_type'>
                                    <option value=""><?php esc_html_e('Please Select', 'bus-booking-manager'); ?></option>
                                    <?php foreach ($terms as $term) { ?>
                                        <option data-term_id="<?php echo esc_attr( $term->term_id ); ?>" value="<?php echo esc_attr( $term->name ); ?>" <?php echo selected( $term->name, $field['wbbm_bus_bp_price_stop'], false ); ?>>
                                            <?php echo esc_html( $term->name ); ?>
                                        </option>
                                    <?php } ?>
                                </select>
                            </div>
                        </td>

                        <td>
                            <div class="wbbm_bus_route_select">
                            <span class="wbbm_bus_route_icon wbbm_bus_route_icon1"> <img src="<?php echo esc_url( WBTM_PLUGIN_URL . 'images/bus_route_map.png' ); ?>" /> </span>
                                <select name="wbbm_bus_dp_price_stop[]" class='seat_type'>
                                    <option value=""><?php esc_html_e('Please Select', 'bus-booking-manager'); ?></option>
                                    <?php foreach ($terms as $term) { ?>
                                        <option data-term_id="<?php echo esc_attr( $term->term_id ); ?>" value="<?php echo esc_attr( $term->name ); ?>" <?php echo selected( $term->name, $field['wbbm_bus_dp_price_stop'], false ); ?>> <?php echo esc_html( $term->name ); ?> </option>
                                    <?php } ?>
                                </select>
                            </div>
                        </td>

                        <td class="wbbm-price-col">
                            <input type="number" step="0.01" name='wbbm_bus_price[]' value="<?php if (isset($field['wbbm_bus_price']) && $field['wbbm_bus_price'] != '') echo esc_attr($field['wbbm_bus_price']); ?>" class="text">
                            <?php if($discount_price_switch=='on'){ ?>
                                <input type="number" step="0.01" name='wbbm_bus_price_roundtrip[]' value="<?php if (isset($field['wbbm_bus_price_roundtrip']) && $field['wbbm_bus_price_roundtrip'] != '') echo esc_attr($field['wbbm_bus_price_roundtrip']); ?>" class="text roundtrip-input" placeholder="<?php echoesc_html_e(wbbm_get_option('wbbm_adult_text', 'wbbm_label_setting_sec') ? wbbm_get_option('wbbm_adult_text', 'wbbm_label_setting_sec') . ' ' .esc_html_e('return discount price', 'bus-booking-manager') :esc_html_e('Adult', 'bus-booking-manager') . ' ' .esc_html_e('return discount price', 'bus-booking-manager'), 'bus-booking-manager'); ?>">
                            <?php } ?>
                        </td>

                        <td class="wbbm-price-col">
                            <input type="number" step="0.01" name='wbbm_bus_price_child[]' value="<?php if (isset($field['wbbm_bus_price_child']) && $field['wbbm_bus_price_child'] != '') {
                                echo esc_attr($field['wbbm_bus_price_child']);
                            } else {
                                echo 0;
                            } ?>" class="text">

                            <?php if($discount_price_switch=='on'){ ?>

                                <input type="number" step="0.01" name='wbbm_bus_price_child_roundtrip[]' value="<?php if (isset($field['wbbm_bus_price_child_roundtrip']) && $field['wbbm_bus_price_child_roundtrip'] != '') {
                                echo esc_attr($field['wbbm_bus_price_child_roundtrip']);
                            } else {
                                echo 0;
                            } ?>" class="text roundtrip-input"
                                   placeholder="<?php echoesc_html_e(wbbm_get_option('wbbm_child_text', 'wbbm_label_setting_sec') ? wbbm_get_option('wbbm_child_text', 'wbbm_label_setting_sec') . ' ' .esc_html_e('return discount price', 'bus-booking-manager') :esc_html_e('Child', 'bus-booking-manager') . ' ' .esc_html_e('return discount price', 'bus-booking-manager'), 'bus-booking-manager'); ?>">
                            <?php } ?>

                        </td>

                        <td class="wbbm-price-col">
                            <input type="number" step="0.01" name='wbbm_bus_price_infant[]' value="<?php if (isset($field['wbbm_bus_price_infant']) && $field['wbbm_bus_price_infant'] != '') {
                                echo esc_attr($field['wbbm_bus_price_infant']);
                            } else {
                                echo 0;
                            } ?>" class="text">

                            <?php if($discount_price_switch=='on'){ ?>

                            <input type="number" step="0.01" name='wbbm_bus_price_infant_roundtrip[]' value="<?php if (isset($field['wbbm_bus_price_infant_roundtrip']) && $field['wbbm_bus_price_infant_roundtrip'] != '') {
                                echo esc_attr($field['wbbm_bus_price_infant_roundtrip']);
                            } else {
                                echo 0;
                            } ?>" class="text roundtrip-input"
                                   placeholder="<?php echoesc_html_e(wbbm_get_option('wbbm_infant_text', 'wbbm_label_setting_sec') ? wbbm_get_option('wbbm_infant_text', 'wbbm_label_setting_sec') . ' ' .esc_html_e('return discount price', 'bus-booking-manager') :esc_html_e('Infant', 'bus-booking-manager') . ' ' .esc_html_e('return discount price', 'bus-booking-manager'), 'bus-booking-manager'); ?>">
                            <?php } ?>
                        </td>

                        <?php if($entire_bus_booking == 'on'): ?>
                            <td class="wbbm-price-col">
                                <input type="number" step="0.01" name='wbbm_bus_price_entire[]' value="<?php if (isset($field['wbbm_bus_price_entire']) && $field['wbbm_bus_price_entire'] != '') {
                                    echo esc_attr($field['wbbm_bus_price_entire']);
                                } else {
                                    echo 0;
                                } ?>" class="text">
                                <input type="number" step="0.01" name='wbbm_bus_price_entire_roundtrip[]' value="<?php if (isset($field['wbbm_bus_price_entire_roundtrip']) && $field['wbbm_bus_price_entire_roundtrip'] != '') {
                                    echo esc_attr($field['wbbm_bus_price_entire_roundtrip']);
                                } else {
                                    echo 0;
                                } ?>" class="text roundtrip-input"
                                       placeholder="<?php echoesc_html_e(wbbm_get_option('wbbm_entire_bus_text', 'wbbm_label_setting_sec') ? wbbm_get_option('wbbm_entire_bus_text', 'wbbm_label_setting_sec') . ' ' .esc_html_e('return discount price', 'bus-booking-manager') :esc_html_e('Entire Bus', 'bus-booking-manager') . ' ' .esc_html_e('return discount price', 'bus-booking-manager'), 'bus-booking-manager'); ?>">
                            </td>
                        <?php endif; ?>

                        <td><a class="button remove-price-row" href="#"><i class="fas fa-minus-circle"></i>
                                <?php esc_html_e('Remove', 'bus-booking-manager'); ?>
                            </a></td>
                    </tr>
                    <!-- Roundtrip price -->

                    <!-- Roundtrip price -->
                    <?php
                    $coun++;
                }
            else :
                // show a blank one
            endif;
            ?>

            <!-- empty hidden one for jQuery -->
            <tr class="empty-row-price screen-reader-text">
                <td>
                    <div class="wbbm_bus_route_select">
                    <span class="wbbm_bus_route_icon wbbm_bus_route_icon1"> <img src="<?php echo esc_url( WBTM_PLUGIN_URL . 'images/bus_route_map.png' ); ?>" /> </span>
                    <?php echo esc_html( wbbm_get_bus_stops_list( 'wbbm_bus_bp_price_stop[]', 'ra_bus_bp_price_stop' ) ); ?>
                    </div>
                </td>
                <td>
                    <div class="wbbm_bus_route_select">
                    <span class="wbbm_bus_route_icon wbbm_bus_route_icon1"> <img src="<?php echo esc_url( WBTM_PLUGIN_URL . 'images/bus_route_map.png' ); ?>" /> </span>
                    <?php echo esc_html (wbbm_get_bus_stops_list('wbbm_bus_dp_price_stop[]','ra_bus_dp_price_stop') ); ?>
                    </div>
                </td>
                <td class="wbbm-price-col">
                    <input step="0.01" type="number" name='wbbm_bus_price[]' value="" class="text">
                    <?php if($discount_price_switch=='on'){ ?>
                        <input step="0.01" type="number" name='wbbm_bus_price_roundtrip[]' placeholder="<?php echo esc_html_e(wbbm_get_option('wbbm_adult_text', 'wbbm_label_setting_sec') ? wbbm_get_option('wbbm_adult_text', 'wbbm_label_setting_sec') . ' ' .esc_html_e('return discount price', 'bus-booking-manager') :esc_html_e('Adult', 'bus-booking-manager') . ' ' .esc_html_e('return discount price', 'bus-booking-manager'), 'bus-booking-manager'); ?>" value="" class="text roundtrip-input">
                    <?php } ?>
                </td>
                <td class="wbbm-price-col">
                    <input step="0.01" type="number" name='wbbm_bus_price_child[]' value="" class="text">
                    <?php if($discount_price_switch=='on'){ ?>
                        <input step="0.01" type="number" name='wbbm_bus_price_child_roundtrip[]' placeholder="<?php echo esc_html_e(wbbm_get_option('wbbm_child_text', 'wbbm_label_setting_sec') ? wbbm_get_option('wbbm_child_text', 'wbbm_label_setting_sec') . ' ' .esc_html_e('return discount price', 'bus-booking-manager') :esc_html_e('Child', 'bus-booking-manager') . ' ' .esc_html_e('return discount price', 'bus-booking-manager'), 'bus-booking-manager'); ?>" value="" class="text roundtrip-input">
                    <?php } ?>
                </td>
                <td class="wbbm-price-col">
                    <input step="0.01" type="number" name='wbbm_bus_price_infant[]' value="" class="text">
                    <?php if($discount_price_switch=='on'){ ?>
                        <input step="0.01" type="number" name='wbbm_bus_price_infant_roundtrip[]' placeholder="<?php echo esc_html_e(wbbm_get_option('wbbm_infant_text', 'wbbm_label_setting_sec') ? wbbm_get_option('wbbm_infant_text', 'wbbm_label_setting_sec') . ' ' .esc_html_e('return discount price', 'bus-booking-manager') :esc_html_e('Infant', 'bus-booking-manager') . ' ' .esc_html_e('return discount price', 'bus-booking-manager'), 'bus-booking-manager'); ?>" value="" class="text roundtrip-input">
                    <?php } ?>
                </td>

                <?php if($entire_bus_booking == 'on'): ?>
                    <td class="wbbm-price-col">
                        <input step="0.01" type="number" name='wbbm_bus_price_entire[]' value="" class="text">
                        <input step="0.01" type="number" name='wbbm_bus_price_entire_roundtrip[]' placeholder="<?php echo esc_html_e(wbbm_get_option('wbbm_entire_bus_text', 'wbbm_label_setting_sec') ? wbbm_get_option('wbbm_entire_bus_text', 'wbbm_label_setting_sec') . ' ' .esc_html_e('return discount price', 'bus-booking-manager') :esc_html_e('Entire Bus', 'bus-booking-manager') . ' ' .esc_html_e('return discount price', 'bus-booking-manager'), 'bus-booking-manager'); ?>" value="" class="text roundtrip-input">
                    </td>
                <?php endif; ?>

                <td>
                    <a class="button remove-price-row" href="#"><i class="fas fa-minus-circle"></i><?php esc_html_e('Remove', 'bus-booking-manager'); ?></a>
                </td>
            </tr>
            <!-- Roundtrip price -->

            <!-- Roundtrip price -->
            </tbody>
        </table>
        <p>
            <a id="add-price-row" class="button" href="#"><i class="fas fa-plus"></i><?php esc_html_e('Add More Price', 'bus-booking-manager'); ?></a>
        </p>
    </div>
    </section>

    <?php

    $mep_events_extra_prices = get_post_meta($post_id, 'mep_events_extra_prices', true);
    wp_nonce_field('mep_events_extra_price_nonce', 'mep_events_extra_price_nonce');
    ?>

    <section class="bgLight" style="margin-top: 20px;">
        <div>
            <label for=""><?php esc_html_e('Extra Service Settings', 'bus-booking-manager'); ?></label>
            <br>
            <span><?php esc_html_e('Configure extra service price.', 'bus-booking-manager'); ?></span>
        </div>
    </section>
    <section>
        <div>
            <label><?php esc_html_e('Enable extra service :', 'bus-booking-manager'); ?></label><br>
            <span><?php esc_html_e('You can offer extra services or sell products along with tickets by enabling this option. ', 'bus-booking-manager'); ?></span>   
        </div>
        <label class="switch">
            <input id="extra-service-control" name="show_extra_service" <?php echo ($show_extra_service == "yes" ? " checked" : ""); ?> value="yes" type="checkbox">
            <span class="slider round" data-collapse-target="#ttbm_display_related"></span>
        </label>
    </section>
    <section style="display: <?php echo ($show_extra_service == "yes" ? "block" : "none"); ?>" class="extra-service" id="wbbm_extra_service">
        <div class="mp_ticket_type_table" style="width: 100%;">
            <table id="repeatable-fieldset-one">
                <thead>
                <tr>
                    <th title="<?php esc_html_e('Extra Service Name', 'bus-booking-manager'); ?>">
                        <?php esc_html_e('Name', 'bus-booking-manager'); ?></th>
                    <th title="<?php esc_html_e('Extra Service Price', 'bus-booking-manager'); ?>">
                        <?php esc_html_e('Price', 'bus-booking-manager'); ?></th>
                    <th title="<?php esc_html_e('Available Qty', 'bus-booking-manager'); ?>">
                        <?php esc_html_e('Available', 'bus-booking-manager'); ?></th>
                    <th title="<?php esc_html_e('Qty Box Type', 'bus-booking-manager'); ?>"
                        style="min-width: 140px;">
                        <?php esc_html_e('Qty Box', 'bus-booking-manager'); ?></th>
                    <th></th>
                </tr>
                </thead>
                <tbody class="mp_event_type_sortable">
                <?php

                if ($mep_events_extra_prices) :

                    foreach ($mep_events_extra_prices as $field) {
                        $qty_type = esc_attr($field['option_qty_type']);
                        ?>
                        <tr>
                            <td><input type="text" class="mp_formControl" name="option_name[]" placeholder="Ex: Cap"
                                       value="<?php if ($field['option_name'] != '') {
                                           echo esc_attr($field['option_name']);
                                       } ?>"/></td>

                            <td><input type="number" step="0.001" class="mp_formControl" name="option_price[]"
                                       placeholder="Ex: 10"
                                       value="<?php if ($field['option_price'] != '') {
                                           echo esc_attr($field['option_price']);
                                       } else {
                                           echo '';
                                       } ?>"/></td>

                            <td><input type="number" class="mp_formControl" name="option_qty[]"
                                       placeholder="Ex: 100" value="<?php if ($field['option_qty'] != '') {
                                    echo esc_attr($field['option_qty']);
                                } else {
                                    echo '';
                                } ?>"/></td>

                            <td align="center">
                                <select name="option_qty_type[]" class='mp_formControl'>
                                    <option value="inputbox" <?php if ($qty_type == 'inputbox') {
                                        echo "Selected";
                                    } ?>><?php esc_html_e('Input Box', 'bus-booking-manager'); ?></option>
                                    <option value="dropdown" <?php if ($qty_type == 'dropdown') {
                                        echo "Selected";
                                    } ?>><?php esc_html_e('Dropdown List', 'bus-booking-manager'); ?></option>
                                </select>
                            </td>
                            <td>
                                <div class="mp_event_remove_move">
                                    <a class="button remove-row" type="button"><i class="fas fa-trash"></i></a>
                                </div>
                            </td>
                        </tr>
                        <?php
                    }
                else :
                    // show a blank one
                endif;
                ?>

                <!-- empty hidden one for jQuery -->
                <tr class="empty-row screen-reader-text">
                    <td><input type="text" class="mp_formControl" name="option_name[]" placeholder="Ex: Cap"/></td>
                    <td><input type="number" class="mp_formControl" step="0.001" name="option_price[]"
                               placeholder="Ex: 10"
                               value=""/></td>
                    <td><input type="number" class="mp_formControl" name="option_qty[]" placeholder="Ex: 100"
                               value=""/>
                    </td>

                    <td><select name="option_qty_type[]" class='mp_formControl'>
                            <option value=""><?php esc_html_e('Please Select Type', 'bus-booking-manager'); ?></option>
                            <option value="inputbox"><?php esc_html_e('Input Box', 'bus-booking-manager'); ?></option>
                            <option value="dropdown"><?php esc_html_e('Dropdown List', 'bus-booking-manager'); ?></option>
                        </select></td>
                    <td>
                        <a class="button remove-row"><i class="fas fa-trash"></i></a>
                    </td>
                </tr>
                </tbody>
            </table>
            <p>
                <a id="add-row" class="button"><i class="fas fa-plus-circle"></i> <?php esc_html_e('Add Extra Price', 'bus-booking-manager'); ?>
                </a>
            </p>
        </div>
    </section>
    </div>
</div>