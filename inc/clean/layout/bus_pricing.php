<div class="mp_tab_item" data-tab-item="#wbtm_seat_price">
    <h3><?php echo $cpt_label.' '. __('Seat Price:', 'bus-booking-manager'); ?></h3>
    <hr />

    <?php
    $terms = get_terms($get_terms_default_attributes);
    if ($terms) {
    ?>

    <div style="width:100%;overflow-x:auto;">
        <table id="repeatable-fieldset-price-one" width="auto">
            <tr>
                <th><?php _e('Boarding Point', 'bus-booking-manager'); ?></th>
                <th><?php _e('Dropping Point', 'bus-booking-manager'); ?></th>
                <th><?php _e(wbbm_get_option('wbbm_adult_text', 'wbbm_label_setting_sec') ? wbbm_get_option('wbbm_adult_text', 'wbbm_label_setting_sec') : __('Adult', 'bus-booking-manager') . ' Fare', 'bus-booking-manager'); ?>
                </th>
                <th><?php _e(wbbm_get_option('wbbm_child_text', 'wbbm_label_setting_sec') ? wbbm_get_option('wbbm_child_text', 'wbbm_label_setting_sec') : __('Child', 'bus-booking-manager') . ' Fare', 'bus-booking-manager'); ?>
                </th>
                <th><?php _e(wbbm_get_option('wbbm_infant_text', 'wbbm_label_setting_sec') ? wbbm_get_option('wbbm_infant_text', 'wbbm_label_setting_sec') : __('Infant', 'bus-booking-manager') . ' Fare', 'bus-booking-manager'); ?>
                </th>
                <?php if($entire_bus_booking == 'on'): ?>
                    <th><?php _e(wbbm_get_option('wbbm_entire_bus_text', 'wbbm_label_setting_sec') ? wbbm_get_option('wbbm_entire_bus_text', 'wbbm_label_setting_sec') : __('Entire Bus', 'bus-booking-manager') . ' Fare', 'bus-booking-manager'); ?></th>
                <?php endif; ?>
                <th></th>
            </tr>
            <tbody>
            <?php
            if ($wbbm_bus_prices) :
                $coun = 0;
                foreach ($wbbm_bus_prices as $field) {
                    ?>
                    <tr>
                        <td>
                            <select name="wbbm_bus_bp_price_stop[]" class='seat_type'>
                                <option value=""><?php _e('Please Select', 'bus-ticket-booking-with-seat-reservation'); ?></option>
                                <?php foreach ($terms as $term) { ?>
                                    <option data-term_id="<?php echo $term->term_id; ?>" value="<?php echo $term->name; ?>" <?php echo ($term->name == $field['wbbm_bus_bp_price_stop'])?'Selected':'' ?>><?php echo $term->name; ?></option>
                                <?php } ?>
                            </select>
                        </td>

                        <td>
                            <select name="wbbm_bus_dp_price_stop[]" class='seat_type'>
                                <option value=""><?php _e('Please Select', 'bus-ticket-booking-with-seat-reservation'); ?></option>
                                <?php foreach ($terms as $term) { ?>
                                    <option data-term_id="<?php echo $term->term_id; ?>" value="<?php echo $term->name; ?>" <?php echo ($term->name == $field['wbbm_bus_dp_price_stop'])?'Selected':'' ?>><?php echo $term->name; ?></option>
                                <?php } ?>
                            </select>
                        </td>

                        <td class="wbbm-price-col">
                            <input type="number" step="0.01" name='wbbm_bus_price[]'
                                   value="<?php if (isset($field['wbbm_bus_price']) && $field['wbbm_bus_price'] != '') echo esc_attr($field['wbbm_bus_price']); ?>"
                                   class="text">
                            <input type="number" step="0.01" name='wbbm_bus_price_roundtrip[]'
                                   value="<?php if (isset($field['wbbm_bus_price_roundtrip']) && $field['wbbm_bus_price_roundtrip'] != '') echo esc_attr($field['wbbm_bus_price_roundtrip']); ?>"
                                   class="text roundtrip-input"
                                   placeholder="<?php echo __(wbbm_get_option('wbbm_adult_text', 'wbbm_label_setting_sec') ? wbbm_get_option('wbbm_adult_text', 'wbbm_label_setting_sec') . ' ' . __('return discount price', 'bus-booking-manager') : __('Adult', 'bus-booking-manager') . ' ' . __('return discount price', 'bus-booking-manager'), 'bus-booking-manager'); ?>">
                        </td>

                        <td class="wbbm-price-col">
                            <input type="number" step="0.01" name='wbbm_bus_price_child[]' value="<?php if (isset($field['wbbm_bus_price_child']) && $field['wbbm_bus_price_child'] != '') {
                                echo esc_attr($field['wbbm_bus_price_child']);
                            } else {
                                echo 0;
                            } ?>" class="text">
                            <input type="number" step="0.01" name='wbbm_bus_price_child_roundtrip[]' value="<?php if (isset($field['wbbm_bus_price_child_roundtrip']) && $field['wbbm_bus_price_child_roundtrip'] != '') {
                                echo esc_attr($field['wbbm_bus_price_child_roundtrip']);
                            } else {
                                echo 0;
                            } ?>" class="text roundtrip-input"
                                   placeholder="<?php echo __(wbbm_get_option('wbbm_child_text', 'wbbm_label_setting_sec') ? wbbm_get_option('wbbm_child_text', 'wbbm_label_setting_sec') . ' ' . __('return discount price', 'bus-booking-manager') : __('Child', 'bus-booking-manager') . ' ' . __('return discount price', 'bus-booking-manager'), 'bus-booking-manager'); ?>">
                        </td>

                        <td class="wbbm-price-col">
                            <input type="number" step="0.01" name='wbbm_bus_price_infant[]' value="<?php if (isset($field['wbbm_bus_price_infant']) && $field['wbbm_bus_price_infant'] != '') {
                                echo esc_attr($field['wbbm_bus_price_infant']);
                            } else {
                                echo 0;
                            } ?>" class="text">
                            <input type="number" step="0.01" name='wbbm_bus_price_infant_roundtrip[]' value="<?php if (isset($field['wbbm_bus_price_infant_roundtrip']) && $field['wbbm_bus_price_infant_roundtrip'] != '') {
                                echo esc_attr($field['wbbm_bus_price_infant_roundtrip']);
                            } else {
                                echo 0;
                            } ?>" class="text roundtrip-input"
                                   placeholder="<?php echo __(wbbm_get_option('wbbm_infant_text', 'wbbm_label_setting_sec') ? wbbm_get_option('wbbm_infant_text', 'wbbm_label_setting_sec') . ' ' . __('return discount price', 'bus-booking-manager') : __('Infant', 'bus-booking-manager') . ' ' . __('return discount price', 'bus-booking-manager'), 'bus-booking-manager'); ?>">
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
                                       placeholder="<?php echo __(wbbm_get_option('wbbm_entire_bus_text', 'wbbm_label_setting_sec') ? wbbm_get_option('wbbm_entire_bus_text', 'wbbm_label_setting_sec') . ' ' . __('return discount price', 'bus-booking-manager') : __('Entire Bus', 'bus-booking-manager') . ' ' . __('return discount price', 'bus-booking-manager'), 'bus-booking-manager'); ?>">
                            </td>
                        <?php endif; ?>

                        <td><a class="button remove-price-row" href="#"><i class="fas fa-minus-circle"></i>
                                <?php _e('Remove', 'bus-booking-manager'); ?>
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
                <td><?php echo wbbm_get_bus_stops_list('wbbm_bus_bp_price_stop[]','ra_bus_bp_price_stop'); ?></td>
                <td><?php echo wbbm_get_bus_stops_list('wbbm_bus_dp_price_stop[]','ra_bus_dp_price_stop'); ?></td>
                <td class="wbbm-price-col">
                    <input step="0.01" type="number" name='wbbm_bus_price[]' value="" class="text">
                    <input step="0.01" type="number" name='wbbm_bus_price_roundtrip[]'
                           placeholder="<?php echo __(wbbm_get_option('wbbm_adult_text', 'wbbm_label_setting_sec') ? wbbm_get_option('wbbm_adult_text', 'wbbm_label_setting_sec') . ' ' . __('return discount price', 'bus-booking-manager') : __('Adult', 'bus-booking-manager') . ' ' . __('return discount price', 'bus-booking-manager'), 'bus-booking-manager'); ?>"
                           value="" class="text roundtrip-input">
                </td>
                <td class="wbbm-price-col">
                    <input step="0.01" type="number" name='wbbm_bus_price_child[]' value="" class="text">
                    <input step="0.01" type="number" name='wbbm_bus_price_child_roundtrip[]'
                           placeholder="<?php echo __(wbbm_get_option('wbbm_child_text', 'wbbm_label_setting_sec') ? wbbm_get_option('wbbm_child_text', 'wbbm_label_setting_sec') . ' ' . __('return discount price', 'bus-booking-manager') : __('Child', 'bus-booking-manager') . ' ' . __('return discount price', 'bus-booking-manager'), 'bus-booking-manager'); ?>"
                           value="" class="text roundtrip-input">
                </td>
                <td class="wbbm-price-col">
                    <input step="0.01" type="number" name='wbbm_bus_price_infant[]' value="" class="text">
                    <input step="0.01" type="number" name='wbbm_bus_price_infant_roundtrip[]'
                           placeholder="<?php echo __(wbbm_get_option('wbbm_infant_text', 'wbbm_label_setting_sec') ? wbbm_get_option('wbbm_infant_text', 'wbbm_label_setting_sec') . ' ' . __('return discount price', 'bus-booking-manager') : __('Infant', 'bus-booking-manager') . ' ' . __('return discount price', 'bus-booking-manager'), 'bus-booking-manager'); ?>"
                           value="" class="text roundtrip-input">
                </td>

                <?php if($entire_bus_booking == 'on'): ?>
                    <td class="wbbm-price-col">
                        <input step="0.01" type="number" name='wbbm_bus_price_entire[]' value="" class="text">
                        <input step="0.01" type="number" name='wbbm_bus_price_entire_roundtrip[]'
                               placeholder="<?php echo __(wbbm_get_option('wbbm_entire_bus_text', 'wbbm_label_setting_sec') ? wbbm_get_option('wbbm_entire_bus_text', 'wbbm_label_setting_sec') . ' ' . __('return discount price', 'bus-booking-manager') : __('Entire Bus', 'bus-booking-manager') . ' ' . __('return discount price', 'bus-booking-manager'), 'bus-booking-manager'); ?>"
                               value="" class="text roundtrip-input">
                    </td>
                <?php endif; ?>

                <td><a class="button remove-price-row" href="#"><i class="fas fa-minus-circle"></i>
                        <?php _e('Remove', 'bus-booking-manager'); ?></a>
                </td>
            </tr>
            <!-- Roundtrip price -->

            <!-- Roundtrip price -->
            </tbody>
        </table>
        <p><a id="add-price-row" class="button" href="#"><i class="fas fa-plus"></i>
                <?php _e('Add More Price', 'bus-booking-manager'); ?></a></p>
    </div>

    <?php
                } else {
                    echo "<div class='wbbm_bus_stops_notice'>".__('Please Enter some bus stops first.', 'bus-booking-manager')."<a class='wbbm_color_white' href='" . get_admin_url() . "edit-tags.php?taxonomy=wbbm_bus_stops&post_type=wbbm_bus'>".__('Click here for bus stops', 'bus-booking-manager')."</a></div>";
                }
                ?>
    <script type="text/javascript">
        jQuery(document).ready(function($) {


            $('#add-price-row').on('click', function() {
                var row = $('.empty-row-price.screen-reader-text').clone(true);
                row.removeClass('empty-row-price screen-reader-text');
                row.insertBefore('#repeatable-fieldset-price-one tbody>tr:last');
                return false;
            });

            $('.remove-price-row').on('click', function() {
                $(this).parents('tr').remove();
                return false;
            });

        });
    </script>
    <?php


    $mep_events_extra_prices = get_post_meta($post_id, 'mep_events_extra_prices', true);
    wp_nonce_field('mep_events_extra_price_nonce', 'mep_events_extra_price_nonce');
    ?>
    <div id="wbbm_extra_service">
        <h3><?php _e('Extra service Area :', 'bus-booking-manager'); ?></h3>

        <p class="event_meta_help_txt">
            <?php _e('Extra Service as Product that you can sell and it is not included on ticket', 'bus-booking-manager'); ?>
        </p>
        <hr>
        <div class="mp_ticket_type_table">
            <table id="repeatable-fieldset-one">
                <thead>
                <tr>
                    <th title="<?php _e('Extra Service Name', 'bus-booking-manager'); ?>">
                        <?php _e('Name', 'bus-booking-manager'); ?></th>
                    <th title="<?php _e('Extra Service Price', 'bus-booking-manager'); ?>">
                        <?php _e('Price', 'bus-booking-manager'); ?></th>
                    <th title="<?php _e('Available Qty', 'bus-booking-manager'); ?>">
                        <?php _e('Available', 'bus-booking-manager'); ?></th>
                    <th title="<?php _e('Qty Box Type', 'bus-booking-manager'); ?>"
                        style="min-width: 140px;">
                        <?php _e('Qty Box', 'bus-booking-manager'); ?></th>
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
                                    } ?>><?php _e('Input Box', 'bus-booking-manager'); ?></option>
                                    <option value="dropdown" <?php if ($qty_type == 'dropdown') {
                                        echo "Selected";
                                    } ?>><?php _e('Dropdown List', 'bus-booking-manager'); ?></option>
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
                            <option value=""><?php _e('Please Select Type', 'bus-booking-manager'); ?></option>
                            <option value="inputbox"><?php _e('Input Box', 'bus-booking-manager'); ?></option>
                            <option value="dropdown"><?php _e('Dropdown List', 'bus-booking-manager'); ?></option>
                        </select></td>
                    <td>
                        <a class="button remove-row"><i class="fas fa-trash"></i></a>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
        <p>
            <a id="add-row" class="button"><i class="fas fa-plus-circle"></i> <?php _e('Add Extra Price', 'bus-booking-manager'); ?>
            </a>
        </p>
    </div>




</div>