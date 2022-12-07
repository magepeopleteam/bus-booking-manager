<!-- start offday-onday tab content -->
<div class="mp_tab_item" data-tab-item="#wbtm_bus_off_on_date">
    <h3><?php echo $cpt_label.' '. __('Onday and Offday:', 'bus-booking-manager'); ?></h3>
    <hr />

    <div class='sec onday-sec'>
    <label for="bus_on_date">
        <?php echo $cpt_label.' '. __('Onday Dates:', 'bus-booking-manager'); ?>
        <span>
            <input type="text" class="ra_bus_on_date"  id='bus_on_date' name='wbtm_bus_on_date' value='<?php if (array_key_exists('wbtm_bus_on_date', $values)) {
                                        echo $values['wbtm_bus_on_date'][0];
                                    } ?>' />
        </span>
    </label>
</div>

<script type="text/javascript">
jQuery(document).ready(function($) {
    jQuery("#bus_on_date").multiDatesPicker({
        numberOfMonths: [1,3],
        dateFormat: "yy-mm-dd",
        minDate: 0,
    });

});
</script>
<hr>
<!-- Start Offday wrapper-->
<?php
                    $values = get_post_custom($post->ID);
                    $wbtm_offday_schedule = get_post_meta($post->ID, 'wbtm_offday_schedule', true);
                    ?>

<div class="wbbm-offday-wrapper">
    <div class="wbbm-offday-inner">
        <label><?php echo $cpt_label.' '. __('Offday Date and Time:', 'bus-booking-manager'); ?></label>
        <table id="repeatable-fieldset-offday" width="100%">
            <tr>
                <th><?php _e('From Date', 'bus-booking-manager'); ?></th>
                <th><?php _e('From Time', 'bus-booking-manager'); ?></th>
                <th><?php _e('To Date', 'bus-booking-manager'); ?></th>
                <th><?php _e('To Time', 'bus-booking-manager'); ?></th>
                <th></th>
            </tr>
            <tbody>
                <?php
                            if ($wbtm_offday_schedule) :
                                $count = 0;
                                foreach ($wbtm_offday_schedule as $field) {
                                    ?>
                <tr class="">
                    <td align="left"><input type="text" id="<?php echo 'db_offday_from_'.$count; ?>"
                            class="repeatable-offday-from-field" name='wbtm_od_offdate_from[]' placeholder="2020-12-31"
                            value="<?php echo $field['from_date'] ?>" /></td>
                    <td align="left"><input type="text" class="repeatable-offtime-from-field"
                            name='wbtm_od_offtime_from[]' placeholder="09:00 am"
                            value="<?php echo $field['from_time'] ?>" /></td>
                    <td align="left"><input type="text" id="<?php echo 'db_offday_to_'.$count; ?>"
                            class="repeatable-offday-to-field" name='wbtm_od_offdate_to[]' placeholder="2020-12-31"
                            value="<?php echo $field['to_date'] ?>" /></td>
                    <td align="left"><input type="text" class="repeatable-offtime-to-field" name='wbtm_od_offtime_to[]'
                            placeholder="09:59 pm" value="<?php echo $field['to_time'] ?>" /></td>
                    <td align="left">
                        <a class="button remove-bp-row" href="#">
                            <i class="fas fa-minus-circle"></i>
                            <?php _e('Remove', 'bus-booking-manager'); ?>
                        </a>
                    </td>
                </tr>

                <script>
                setTimeout(function() {
                    $("#db_offday_from_<?php echo $count ?>").datepicker({
                        dateFormat: "yy-mm-dd",
                        minDate: 0
                    });
                    $("#db_offday_to_<?php echo $count ?>").datepicker({
                        dateFormat: "yy-mm-dd",
                        minDate: 0
                    });
                }, 400);
                </script>
                <?php

                                    $count++;
                                }
                            else :
                                // show a blank one
                            endif;
                            ?>

                <!-- empty hidden one for jQuery -->
                <tr class="empty-row-offday screen-reader-text">
                    <td align="left"><input type="text" class="repeatable-offday-from-field"
                            name='wbtm_od_offdate_from[]' placeholder="2020-12-31" /></td>
                    <td align="left"><input type="text" class="repeatable-offtime-from-field"
                            name='wbtm_od_offtime_from[]' placeholder="09:00 am" /></td>
                    <td align="left"><input type="text" class="repeatable-offday-to-field" name='wbtm_od_offdate_to[]'
                            placeholder="2020-12-31" /></td>
                    <td align="left"><input type="text" class="repeatable-offtime-to-field" name='wbtm_od_offtime_to[]'
                            placeholder="09:59 pm" /></td>
                    <td align="left">
                        <a class="button remove-bp-row" href="#">
                            <i class="fas fa-minus-circle"></i>
                            <?php _e('Remove', 'bus-booking-manager'); ?>
                        </a>
                    </td>
                </tr>
            </tbody>
        </table>
        <a id="add-offday-row" class="button" href="#"><i class="fas fa-plus"></i>
            <?php _e('Add More offdate', 'bus-booking-manager'); ?>
        </a>
    </div>
</div>

<script type="text/javascript">
jQuery(document).ready(function($) {
    var datePickerOpt = {
        dateFormat: "yy-mm-dd",
        minDate: 0
    }
    $('#add-offday-row').on('click', function(e) {
        e.preventDefault();
        var now = Date.now();
        var row = $('.empty-row-offday.screen-reader-text').clone(true);
        row.removeClass('empty-row-offday screen-reader-text');
        row.insertBefore('#repeatable-fieldset-offday tbody>tr:last');
        row.find(".repeatable-offday-from-field").attr('id', 'offday_from' + now);
        row.find(".repeatable-offday-to-field").attr('id', 'offday_to' + now);

        $("#offday_from" + now).datepicker(datePickerOpt);
        $("#offday_to" + now).datepicker(datePickerOpt);

    });

    $('.remove-bp-row').on('click', function() {
        $(this).parents('tr').remove();
        return false;
    });
});
</script>
<hr>
<label><?php echo $cpt_label.' '.__('Offdays:', 'bus-booking-manager'); ?></label>
<div class='sec offday-sec'>
    <label for='sun'>
        <input type="checkbox" id='sun' style="text-align: left;width: auto;" name="od_sun" value='yes' <?php if (array_key_exists('od_Sun', $values)) {
                                if ($values['od_Sun'][0] == 'yes') {
                                    echo 'Checked';
                                }
                            } ?> /> <?php _e('Sunday', 'bus-booking-manager'); ?>
    </label>
    <label for='mon'>
        <input type="checkbox" style="text-align: left;width: auto;" name="od_mon" value='yes' id='mon' <?php if (array_key_exists('od_Mon', $values)) {
                                if ($values['od_Mon'][0] == 'yes') {
                                    echo 'Checked';
                                }
                            } ?>> <?php _e('Monday', 'bus-booking-manager'); ?>
    </label>
    <label for='tue'>
        <input type="checkbox" style="text-align: left;width: auto;" name="od_tue" value='yes' id='tue' <?php if (array_key_exists('od_Tue', $values)) {
                                if ($values['od_Tue'][0] == 'yes') {
                                    echo 'Checked';
                                }
                            } ?>> <?php _e('Tuesday', 'bus-booking-manager'); ?>
    </label>
    <label for='wed'>
        <input type="checkbox" style="text-align: left;width: auto;" name="od_wed" value='yes' id='wed' <?php if (array_key_exists('od_Wed', $values)) {
                                if ($values['od_Wed'][0] == 'yes') {
                                    echo 'Checked';
                                }
                            } ?>> <?php _e('Wednesday', 'bus-booking-manager'); ?>
    </label>
    <label for='thu'>
        <input type="checkbox" style="text-align: left;width: auto;" name="od_thu" value='yes' id='thu' <?php if (array_key_exists('od_Thu', $values)) {
                                if ($values['od_Thu'][0] == 'yes') {
                                    echo 'Checked';
                                }
                            } ?>> <?php _e('Thursday', 'bus-booking-manager'); ?>
    </label>
    <label for='fri'>
        <input type="checkbox" style="text-align: left;width: auto;" name="od_fri" value='yes' id='fri' <?php if (array_key_exists('od_Fri', $values)) {
                                if ($values['od_Fri'][0] == 'yes') {
                                    echo 'Checked';
                                }
                            } ?>> <?php _e('Friday', 'bus-booking-manager'); ?>
    </label>
    <label for='sat'>
        <input type="checkbox" style="text-align: left;width: auto;" name="od_sat" value='yes' id='sat' <?php if (array_key_exists('od_Sat', $values)) {
                                if ($values['od_Sat'][0] == 'yes') {
                                    echo 'Checked';
                                }
                            } ?>> <?php _e('Saturday', 'bus-booking-manager'); ?>
    </label>
</div>

<script type="text/javascript">
jQuery(document).ready(function($) {

    jQuery("#od_start").datepicker({
        dateFormat: "yy-mm-dd",
        minDate: 0
    });
    jQuery("#od_end").datepicker({
        dateFormat: "yy-mm-dd",
        // minDate:0
    });


    // jQuery( "#ja_date" ).datepicker({
    //   dateFormat: "yy-mm-dd"
    // });
    // jQuery(".the_select select").select2();
});
</script>





</div>