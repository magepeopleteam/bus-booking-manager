<!-- start offday-onday tab content -->
<div class="mp_tab_item" data-tab-item="#wbtm_bus_off_on_date">
    <h3 class="wbbm_mp_tab_item_heading"><?php echo $cpt_label.' '. __('On & Off Day Settings', 'bus-booking-manager'); ?></h3>
    <p><?php esc_html_e('Here you can configure '.$cpt_label.' On & Off Day.', 'bus-booking-manager'); ?></p>
    
    <div class="wbtm_bus_off_on_date_inner_wrapper">
        <section class="bgLight">
            <div>
                <label><?php _e('Onday Settings', 'bus-booking-manager'); ?></label>
                <br>
                <span><?php _e('Congiure onday settings here.', 'bus-booking-manager'); ?></span>
            </div>
        </section>
        <section>
            <div>
                <label><?php _e('Enable Operation days', 'bus-booking-manager'); ?></label>
                <br>
                <span><?php _e('If you want to operate '.$cpt_label.' on a certain date, please enable it and configure operational day. ', 'bus-booking-manager'); ?></span>
            </div>
            <label class="switch">
                <input id="operational-on-day-control" name="show_operational_on_day" <?php echo ($show_operational_on_day == "yes" ? " checked" : ""); ?> value="yes" type="checkbox">
                <span class="slider round" data-collapse-target="#ttbm_display_related"></span>
            </label>
        </section>


        <div style="display: <?php echo ($show_operational_on_day == "yes" ? "block" : "none"); ?>" class='onday-sec operational-on-day'>
            <section>
                <div>
                    <label for="bus_on_date">
                        <?php echo $cpt_label.' '. __('Select Dates:', 'bus-booking-manager'); ?>
                    </label>
                    <span>
                        <?php esc_html_e('Select dates to operate '.$cpt_label, 'bus-booking-manager'); ?>
                    </span>
                </div>
                <div>
                    <input type="text" class="ra_bus_on_date"  id='bus_on_date' name='wbtm_bus_on_date' value='<?php if (array_key_exists('wbtm_bus_on_date', $values)) { echo $values['wbtm_bus_on_date'][0]; } ?>' />
                </div>
            </section>
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
        

        <!-- Start Offday wrapper-->
        <?php
        $values = get_post_custom($post->ID);
        $wbtm_offday_schedule = get_post_meta($post->ID, 'wbtm_offday_schedule', true);
        ?>
        <section class="bgLight" style="margin-top: 20px;">
            <div>
                <label><?php _e('Offday Settings', 'bus-booking-manager'); ?></label>
                <br>
                <span><?php _e('If you need to keep '.$cpt_label.' off for a certain date please enable it and configure offday. ', 'bus-booking-manager'); ?></span>
            </div>
        </section>
        <section>
            <div>
                <label><?php _e('Enable offday dates', 'bus-booking-manager'); ?></label>
                <br>
                <span><?php _e('If you need to keep bus off for a certain date please enable it and configure offday. ', 'bus-booking-manager'); ?></span>
            </div>
            <label class="switch">
                <input id="off-day-control" name="show_off_day" <?php echo ($show_off_day == "yes" ? " checked" : ""); ?> value="yes" type="checkbox">
                <span class="slider round" data-collapse-target="#ttbm_display_related"></span>
            </label>
        </section>

        <div style="display: <?php echo ($show_off_day == 'yes' ? 'block' : 'none'); ?>" class="wbbm-offday-wrapper  off-day">
            <section class="wbbm-offday-inner">
                <div style="width: 100%;">
                    <table id="repeatable-fieldset-offday" width="100%">
                        <tr>
                            <th><?php _e('From Date', 'bus-booking-manager'); ?></th>
                            <th><?php _e('From Time', 'bus-booking-manager'); ?></th>
                            <th></th>
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
                                <td align="left">
                                    <div class="wbbm_bus_icon_group">
                                    <span class="wbbm_bus_route_icon wbbm_bus_route_icon1"><img src="<?php echo WBTM_PLUGIN_URL .'images/bus_calendar.png';?>"/></span>
                                    <input type="text" id="<?php echo 'db_offday_from_'.$count; ?>" class="repeatable-offday-from-field datepicker_has" name='wbtm_od_offdate_from[]' placeholder="2020-12-31" value="<?php echo $field['from_date'] ?>" />
                                    </div>
                                </td>
                                <td align="left">
                                    <div class="wbbm_bus_icon_group">
                                    <span class="wbbm_bus_route_icon wbbm_bus_route_icon1"><img src="<?php echo WBTM_PLUGIN_URL .'images/bus_route_clock.png';?>"/></span>
                                    <input type="text" class="repeatable-offtime-from-field" name='wbtm_od_offtime_from[]' placeholder="09:00 am" value="<?php echo $field['from_time'] ?>" />
                                    </div>
                                </td>
                                <td align="left">
                                    <span class="wbtm_bus_off_on_date_arrow"><img src="<?php echo WBTM_PLUGIN_URL .'images/bus_route_arrow.png';?>"/></span>
                                </td>
                                <td align="left">
                                    <div class="wbbm_bus_icon_group">
                                    <span class="wbbm_bus_route_icon wbbm_bus_route_icon1"><img src="<?php echo WBTM_PLUGIN_URL .'images/bus_calendar.png';?>"/></span>
                                    <input type="text" id="<?php echo 'db_offday_to_'.$count; ?>" class="repeatable-offday-to-field datepicker_has" name='wbtm_od_offdate_to[]' placeholder="2020-12-31"
                                        value="<?php echo $field['to_date'] ?>" />
                                    </div>
                                </td>
                                <td align="left">
                                    <div class="wbbm_bus_icon_group">
                                    <span class="wbbm_bus_route_icon wbbm_bus_route_icon1"><img src="<?php echo WBTM_PLUGIN_URL .'images/bus_route_clock.png';?>"/></span>
                                    <input type="text" class="repeatable-offtime-to-field" name='wbtm_od_offtime_to[]'
                                        placeholder="09:59 pm" value="<?php echo $field['to_time'] ?>" />
                                    </div>
                                </td>
                                <td align="left">
                                    <a class="button remove-bp-row" href="#">
                                        <i class="fas fa-minus-circle"></i>
                                        <?php _e('Remove', 'bus-booking-manager'); ?>
                                    </a>
                                </td>
                            </tr>


                            <?php
                                $count++;
                            }
                            endif;
                                        ?>

                            <script>
                                jQuery(document).ready(function($) {
                                    $( ".datepicker_has" ).datepicker({
                                        dateFormat: "yy-mm-dd",
                                        minDate: 0
                                    });
                                });
                            </script>

                            <!-- empty hidden one for jQuery -->
                            <tr class="empty-row-offday screen-reader-text">
                                <td align="left">
                                    <div class="wbbm_bus_icon_group">
                                        <span class="wbbm_bus_route_icon wbbm_bus_route_icon1"><img src="<?php echo WBTM_PLUGIN_URL .'images/bus_calendar.png';?>"/></span>
                                        <input type="text" class="repeatable-offday-from-field"
                                        name='wbtm_od_offdate_from[]' placeholder="2020-12-31" />
                                    </div>
                                    </td>
                                <td align="left">
                                    <div class="wbbm_bus_icon_group">
                                    <span class="wbbm_bus_route_icon wbbm_bus_route_icon1"><img src="<?php echo WBTM_PLUGIN_URL .'images/bus_route_clock.png';?>"/></span>
                                    <input type="text" class="repeatable-offtime-from-field"
                                        name='wbtm_od_offtime_from[]' placeholder="09:00 am" />
                                    </div>
                                </td>
                                <td align="left">
                                    <span class="wbtm_bus_off_on_date_arrow"><img src="<?php echo WBTM_PLUGIN_URL .'images/bus_route_arrow.png';?>"/></span>
                                </td>
                                <td align="left">
                                    <div class="wbbm_bus_icon_group">
                                        <span class="wbbm_bus_route_icon wbbm_bus_route_icon1"><img src="<?php echo WBTM_PLUGIN_URL .'images/bus_calendar.png';?>"/></span>
                                        <input type="text" class="repeatable-offday-to-field" name='wbtm_od_offdate_to[]'
                                        placeholder="2020-12-31" />
                                    </div>
                                </td>
                                <td align="left">
                                    <div class="wbbm_bus_icon_group">
                                        <span class="wbbm_bus_route_icon wbbm_bus_route_icon1"><img src="<?php echo WBTM_PLUGIN_URL .'images/bus_route_clock.png';?>"/></span>
                                        <input type="text" class="repeatable-offtime-to-field" name='wbtm_od_offtime_to[]'
                                        placeholder="09:59 pm" />
                                    </div>
                                </td>
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
            </section>

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
                        $("#db_offday_from_0").datepicker(datePickerOpt);

                    });

                    $('.remove-bp-row').on('click', function() {
                        $(this).parents('tr').remove();
                        return false;
                    });
                });
            </script>

            <section>
                <div>
                    <label><?php echo $cpt_label.' '.__('Off days', 'bus-booking-manager'); ?></label>
                    <br>
                    <span><?php esc_html_e('Check '.$cpt_label.' off days.', 'bus-booking-manager'); ?></span>
                </div>
                <div class='offday-sec'>
                    <label for='sun'>
                        <input type="checkbox" style="text-align: left;width: auto;" name="weekly_offday[]" value='7' id='sun' <?php echo ((in_array(7, $weekly_offday))?'Checked':'') ?>>
                        <?php _e('Sunday', 'bus-booking-manager'); ?>
                    </label>
                    <label for='mon'>
                        <input type="checkbox" style="text-align: left;width: auto;" name="weekly_offday[]" value='1' id='mon' <?php echo ((in_array(1, $weekly_offday))?'Checked':'') ?>>
                        <?php _e('Monday', 'bus-booking-manager'); ?>
                    </label>
                    <label for='tue'>
                        <input type="checkbox" style="text-align: left;width: auto;" name="weekly_offday[]" value='2' id='tue' <?php echo ((in_array(2, $weekly_offday))?'Checked':'') ?>>
                        <?php _e('Tuesday', 'bus-booking-manager'); ?>
                    </label>
                    <label for='wed'>
                        <input type="checkbox" style="text-align: left;width: auto;" name="weekly_offday[]" value='3' id='wed' <?php echo ((in_array(3, $weekly_offday))?'Checked':'') ?>>
                        <?php _e('Wednesday', 'bus-booking-manager'); ?>
                    </label>
                    <label for='thu'>
                        <input type="checkbox" style="text-align: left;width: auto;" name="weekly_offday[]" value='4' id='thu' <?php echo ((in_array(4, $weekly_offday))?'Checked':'') ?>>
                        <?php _e('Thursday', 'bus-booking-manager'); ?>
                    </label>
                    <label for='fri'>
                        <input type="checkbox" style="text-align: left;width: auto;" name="weekly_offday[]" value='5' id='fri' <?php echo ((in_array(5, $weekly_offday))?'Checked':'') ?>>
                        <?php _e('Friday', 'bus-booking-manager'); ?>
                    </label>
                    <label for='sat'>
                        <input type="checkbox" style="text-align: left;width: auto;" name="weekly_offday[]" value='6' id='sat' <?php echo ((in_array(6, $weekly_offday))?'Checked':'') ?>>
                        <?php _e('Saturday', 'bus-booking-manager'); ?>
                    </label>
                </div>
            </section>

            


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

                });
            </script>

        </div>
    </div>
</div>