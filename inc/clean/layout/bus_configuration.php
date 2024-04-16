<div class="mp_tab_item" data-tab-item="#wbtm_ticket_panel" style="display:block;">
    <h3 class="wbbm_mp_tab_item_heading">
        <?php echo $cpt_label.' '. __('Configuration', 'bus-booking-manager'); ?>
    </h3>
    <p><?php echo $cpt_label.' '. __('Configuration', 'bus-booking-manager'); ?></p>
    <div class="configuration_wrapper">
        <section class="bgLight">
            <div class="">
                <label for="wbbm_bus_category" class="ra-item-label">
                    <?php  esc_html_e('General Settings', 'bus-booking-manager'); ?>
                </label>
                <br>
                <span>
                    <?php  esc_html_e('Here you can set '.$cpt_label.' type, number, coach etc.', 'bus-booking-manager'); ?>
                </span>
            </div>
        </section>
        <section>
            
            <div>
                <label for="wbbm_bus_category" class="ra-item-label">
                    <?php  esc_html_e('Type', 'bus-booking-manager'); ?>
                </label>
                <br>
                <span>
                    <?php  esc_html_e('Select '.$cpt_label.' type, AC or None AC.', 'bus-booking-manager'); ?>
                </span>
            </div>
            <span>
                <select name="wbbm_bus_category" id="wbbm_bus_category">'
                    <option value=""><?php esc_html_e('Select Type','bus-booking-manager') ?></option>
                    <?php

                    foreach ($bus_categories as $key => $value) {
                        if ($wbbm_bus_category == $key) {
                            echo '<option value="' . esc_attr($key) . '" selected>' . esc_html($value) . '</option>';
                        } else {
                            echo '<option value="' . esc_attr($key) . '">' . esc_html($value) . '</option>';
                        }
                    }
                    ?>
                </select>
            </span>
        </section>

        <section>
            <div>
                <label for="wbbm_ev_98" class="ra-item-label">
                    <?php _e('Coach No', 'bus-booking-manager'); ?>
                </label>
                <br>
                <span>
                    <?php  esc_html_e('Select unique number for '.$cpt_label, 'bus-booking-manager'); ?>
                </span>
            </div>
            <input id='wbbm_ev_98' type="text" name='wbbm_bus_no' value='<?php if (array_key_exists('wbbm_bus_no', $values)) {echo $values['wbbm_bus_no'][0];} ?>' />
        </section>

        <section>
            <div>
                <label for="wbbm_ev_99" class="ra-item-label">
                    <?php _e('Total Seat', 'bus-booking-manager'); ?>
                </label>
                <br>
                <span>
                    <?php  esc_html_e('Select total seat number for '.$cpt_label, 'bus-booking-manager'); ?>
                </span>
            </div>
            <input id='wbbm_ev_99' type="text" name='wbbm_total_seat' value='<?php if (array_key_exists('wbbm_total_seat', $values)) {
                echo $values['wbbm_total_seat'][0];
            } ?>' />
            </span>
        </section>

        <section>
            <div>
                <label for="wbbm_price_zero_allow" class="ra-item-label">
                    <?php echo wbbm_get_option('wbbm_bus_price_zero_allow_text', 'wbbm_label_setting_sec') ? wbbm_get_option('wbbm_bus_price_zero_allow_text', 'wbbm_label_setting_sec') : __('Price Zero Allow','bus-booking-manager'); ?>
                </label>
                <br>
                <span>
                    <?php  esc_html_e('To allow seat price zero.', 'bus-booking-manager'); ?>
                </span>
            </div>
            <label class="switch">
                <input type="checkbox" id="wbbm_price_zero_allow" name='wbbm_price_zero_allow' <?php if (array_key_exists('wbbm_price_zero_allow', $values)) {
                    if ($values['wbbm_price_zero_allow'][0] == 'on') {
                        echo 'checked';
                    }
                } else {
                    echo '';
                } ?> />
                <span class="slider round"></span>

            </label>
        </section>

        <section>
            <div>
                <label for="wbbm_sell_off" class="ra-item-label">
                    <?php echo wbbm_get_option('wbbm_bus_sell_off_text', 'wbbm_label_setting_sec') ? wbbm_get_option('wbbm_bus_sell_off_text', 'wbbm_label_setting_sec') : __('Sell Off','bus-booking-manager'); ?>
                </label>
                <br>
                <span>
                    <?php  esc_html_e('To allow seat price zero.', 'bus-booking-manager'); ?>
                </span>
            </div>
            <label class="switch">
                <input type="checkbox" id="wbbm_sell_off" name='wbbm_sell_off' <?php if (array_key_exists('wbbm_sell_off', $values)) {
                    if ($values['wbbm_sell_off'][0] == 'on') {
                        echo 'checked';
                    }
                } else {
                    echo '';
                } ?> />
                <span class="slider round"></span>
            </label>
        </section>

        <section>
            <div>
                <label for="wbbm_seat_available" class="ra-item-label">
                    <?php echo wbbm_get_option('wbbm_bus_seat_available_show_text', 'wbbm_label_setting_sec') ? wbbm_get_option('wbbm_bus_seat_available_show_text', 'wbbm_label_setting_sec') : __('Show Seat Available','bus-booking-manager'); ?>
                </label>
                <br>
                <span>
                    <?php  esc_html_e('To show seat available.', 'bus-booking-manager'); ?>
                </span>
            </div>
            <label class="switch">
                <input type="checkbox" id="wbbm_seat_available" name='wbbm_seat_available' <?php if (array_key_exists('wbbm_seat_available', $values)) {
                    if ($values['wbbm_seat_available'][0] == 'on') {
                        echo 'checked';
                    }
                } else {
                    echo 'checked';
                } ?> />
                <span class="slider round"></span>
            </label>
            </label>
        </section>

    </div>

    <script type="text/javascript">
        jQuery(document).ready(function($) {
            $('#add-row-t').on('click', function() {
                var row = $('.empty-row-t.screen-reader-text').clone(true);
                row.removeClass('empty-row-t screen-reader-text');
                row.insertBefore('#repeatable-fieldset-one-t tbody>tr:last');
                return false;
            });

            $('.remove-row-t').on('click', function() {
                $(this).parents('tr').remove();
                return false;
            });
        });
    </script>

    <?php if ($bus_ticket_type) :
        foreach ($bus_ticket_type as $field) {
            $qty_t_type = esc_attr($field['ticket_type_qty_t_type']);
            ?>
            <tr>
                <td>
                    <input type="text" class="widefat" name="ticket_type_name[]" value="<?php if ($field['ticket_type_name'] != '') echo esc_attr($field['ticket_type_name']); ?>" />
                </td>

                <td>
                    <input type="number" class="widefat" name="ticket_type_price[]" value="<?php if ($field['ticket_type_price'] != '') echo esc_attr($field['ticket_type_price']); else echo ''; ?>" />
                </td>

                <td>
                    <input type="number" class="widefat" name="ticket_type_qty[]" value="<?php if ($field['ticket_type_qty'] != '') echo esc_attr($field['ticket_type_qty']); else echo ''; ?>" />
                </td>

                <td><select name="ticket_type_qty_t_type[]" id="mep_ev_9800kj8" class=''>
                        <option value="inputbox" <?php if ($qty_t_type == 'inputbox') {
                            echo "Selected";
                        } ?>><?php _e('Input Box', 'bus-booking-manager'); ?></option>
                        <option value="dropdown" <?php if ($qty_t_type == 'dropdown') {
                            echo "Selected";
                        } ?>><?php _e('Dropdown List', 'bus-booking-manager'); ?></option>
                    </select></td>

                <td>
                    <a class="button remove-row-t" href="#" <?php _e('Remove', 'bus-booking-manager'); ?></a></td>
            </tr>

        <?php } else :
    // show a blank one
    endif;
        ?>
</div>





