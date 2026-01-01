<?php
if (!defined('ABSPATH')) {
    die;
}
?>

<!-- start offday-onday tab content -->
<div class="mp_tab_item" data-tab-item="#wbtm_bus_off_on_date">
    <h3 class="wbbm_mp_tab_item_heading">
        <?php echo esc_html($cpt_label) . ' ' . esc_html__('On & Off Day Settings', 'bus-booking-manager'); ?>
    </h3>
    <p><?php esc_html_e('Here you can configure ' . esc_html($cpt_label) . ' On & Off Day.', 'bus-booking-manager'); ?></p>

    <div class="wbtm_bus_off_on_date_inner_wrapper">
        <section class="bgLight">
            <div>
                <label><?php esc_html_e('On Day Settings', 'bus-booking-manager'); ?></label>
                <br>
                <span><?php esc_html_e('Configure on day settings here.', 'bus-booking-manager'); ?></span>
            </div>
        </section>
        <section>
            <div>
                <label><?php esc_html_e('Enable Operation days', 'bus-booking-manager'); ?></label>
                <br>
                <span><?php esc_html_e('If you want to operate ' . esc_html($cpt_label) . ' on a certain date, please enable it and configure operational day.', 'bus-booking-manager'); ?></span>
            </div>
            <label class="switch">
                <input id="operational-on-day-control" name="show_operational_on_day" <?php checked($show_operational_on_day, "yes"); ?> value="yes" type="checkbox">
                <span class="slider round" data-collapse-target="#ttbm_display_related"></span>
            </label>
        </section>

        <?php
        // Retrieve and sanitize post meta
        $wbtm_bus_on_date = array_map('sanitize_text_field', (array)get_post_meta($post->ID, 'wbtm_bus_on_date', true));
        ?>

        <div style="display: <?php echo esc_attr($show_operational_on_day == "yes" ? "block" : "none"); ?>" class='onday-sec operational-on-day'>
            <section>
                <div>
                    <label for="bus_on_date"><?php echo esc_html__('Operational Dates:', 'bus-booking-manager'); ?></label>
                    <span><?php esc_html_e('You can add specific dates for operations', 'bus-booking-manager'); ?></span>
                </div>
                <div>
                    <?php foreach ($wbtm_bus_on_date as $date) : 
                        if (!empty($date)) : ?>
                        <div class="operate-day-row">
                            <input type="text" class="ra_bus_on_date datepicker_has" id="bus_on_date" name="wbtm_bus_on_date[]" value="<?php echo esc_attr($date); ?>" placeholder="2024-04-30" />
                            <a class="button remove-bp-row" href="#"><i class="fas fa-minus-circle"></i><?php esc_html_e('Remove', 'bus-booking-manager'); ?></a>
                        </div>
                    <?php endif; endforeach; ?>

                    <div id="operate-date-area">
                        <div class="empty-row-operate-day screen-reader-text">
                            <input type="text" class="ra_bus_on_date" id="bus_on_date" name="wbtm_bus_on_date[]" placeholder="2024-04-30" />
                            <a class="button remove-bp-row" href="#"><i class="fas fa-minus-circle"></i><?php esc_html_e('Remove', 'bus-booking-manager'); ?></a>
                        </div>
                    </div>
                    <div class="mpStyle">
                        <a id="clone-operate-date" class="button" href="#"><i class="fas fa-plus"></i><?php esc_html_e('Add operate date', 'bus-booking-manager'); ?></a>
                    </div>
                </div>
            </section>
        </div>

        <script type="text/javascript">
            jQuery(document).ready(function($) {
                var datePickerOpt = {
                    dateFormat: "yy-mm-dd",
                    minDate: 0
                };
                $('#clone-operate-date').on('click', function(e) {
                    e.preventDefault();
                    var now = Date.now();
                    var row = $('.empty-row-operate-day.screen-reader-text').clone(true);
                    row.removeClass('empty-row-operate-day screen-reader-text');
                    row.addClass('operate-day-row');
                    row.insertBefore('#operate-date-area:last');
                    row.find(".ra_bus_on_date").attr('id', 'bus_on_date' + now);
                    $("#bus_on_date" + now).datepicker(datePickerOpt);
                });
                $('.remove-bp-row').on('click', function() {
                    $(this).parents('div.operate-day-row').remove();
                    return false;
                });
            });
        </script>

        <!-- ============================================End operation day wrapper================================= -->

        <!-- ------------------Start Offday wrapper-------------------------------->
        <?php
        $wbtm_offday_schedule = get_post_meta($post->ID, 'wbtm_offday_schedule', true);
        ?>

        <section class="bgLight" style="margin-top: 20px;">
            <div>
                <label><?php esc_html_e('Off Day Settings', 'bus-booking-manager'); ?></label>
                <br>
                <span><?php esc_html_e('If you need to keep ' . esc_html($cpt_label) . ' off for a certain date please enable it and configure off day.', 'bus-booking-manager'); ?></span>
            </div>
        </section>
        <section>
            <div>
                <label><?php esc_html_e('Enable off day dates', 'bus-booking-manager'); ?></label>
                <br>
                <span><?php esc_html_e('If you need to keep the bus off for a certain date please enable it and configure off day.', 'bus-booking-manager'); ?></span>
            </div>
            <label class="switch">
                <input id="off-day-control" name="show_off_day" <?php checked($show_off_day, "yes"); ?> value="yes" type="checkbox">
                <span class="slider round" data-collapse-target="#ttbm_display_related"></span>
            </label>
        </section>

        <div style="display: <?php echo esc_attr($show_off_day == 'yes' ? 'block' : 'none'); ?>" class="wbbm-offday-wrapper off-day">
            <section class="wbbm-offday-inner">
                <div style="width: 100%;">
                    <table id="repeatable-fieldset-offday" width="100%">
                        <tr>
                            <th><?php esc_html_e('From Date', 'bus-booking-manager'); ?></th>
                            <th><?php esc_html_e('From Time', 'bus-booking-manager'); ?></th>
                            <th></th>
                            <th><?php esc_html_e('To Date', 'bus-booking-manager'); ?></th>
                            <th><?php esc_html_e('To Time', 'bus-booking-manager'); ?></th>
                            <th></th>
                        </tr>
                        <tbody>
                            <?php if ($wbtm_offday_schedule) :
                                $count = 0;
                                foreach ($wbtm_offday_schedule as $field) : ?>
                                    <tr>
                                        <td align="left">
                                            <div class="wbbm_bus_icon_group">
                                                <span class="wbbm_bus_route_icon wbbm_bus_route_icon1">
                                                    <img src="<?php echo esc_url(WBTM_PLUGIN_URL . 'images/bus_calendar.png'); ?>" />
                                                </span>
                                                <input type="text" id="<?php echo esc_attr('db_offday_from_' . $count); ?>" class="repeatable-offday-from-field datepicker_has" name="wbtm_od_offdate_from[]" placeholder="2020-12-31" value="<?php echo esc_attr(sanitize_text_field($field['from_date'])); ?>" />
                                            </div>
                                        </td>
                                        <td align="left">
                                            <div class="wbbm_bus_icon_group">
                                                <span class="wbbm_bus_route_icon wbbm_bus_route_icon1">
                                                    <img src="<?php echo esc_url(WBTM_PLUGIN_URL . 'images/bus_route_clock.png'); ?>" />
                                                </span>
                                                <input type="text" class="repeatable-offtime-from-field" name="wbtm_od_offtime_from[]" placeholder="09:00 am" value="<?php echo esc_attr(sanitize_text_field($field['from_time'])); ?>" />
                                            </div>
                                        </td>
                                        <td align="left">
                                            <span class="wbtm_bus_off_on_date_arrow"><img src="<?php echo esc_url(WBTM_PLUGIN_URL . 'images/bus_route_arrow.png'); ?>" /></span>
                                        </td>
                                        <td align="left">
                                            <div class="wbbm_bus_icon_group">
                                                <span class="wbbm_bus_route_icon wbbm_bus_route_icon1">
                                                    <img src="<?php echo esc_url(WBTM_PLUGIN_URL . 'images/bus_calendar.png'); ?>" />
                                                </span>
                                                <input type="text" id="<?php echo esc_attr('db_offday_to_' . $count); ?>" class="repeatable-offday-to-field datepicker_has" name="wbtm_od_offdate_to[]" placeholder="2020-12-31" value="<?php echo esc_attr(sanitize_text_field($field['to_date'])); ?>" />
                                            </div>
                                        </td>
                                        <td align="left">
                                            <div class="wbbm_bus_icon_group">
                                                <span class="wbbm_bus_route_icon wbbm_bus_route_icon1">
                                                    <img src="<?php echo esc_url(WBTM_PLUGIN_URL . 'images/bus_route_clock.png'); ?>" />
                                                </span>
                                                <input type="text" class="repeatable-offtime-to-field" name="wbtm_od_offtime_to[]" placeholder="09:59 pm" value="<?php echo esc_attr(sanitize_text_field($field['to_time'])); ?>" />
                                            </div>
                                        </td>
                                        <td align="left">
                                            <a class="button remove-bp-row" href="#"><i class="fas fa-minus-circle"></i><?php esc_html_e('Remove', 'bus-booking-manager'); ?></a>
                                        </td>
                                    </tr>
                                <?php $count++;
                                endforeach;
                            endif; ?>
                            <script>
                                jQuery(document).ready(function($) {
                                    $(".datepicker_has").datepicker({
                                        dateFormat: "yy-mm-dd",
                                        minDate: 0
                                    });
                                });
                            </script>

                            <!-- Empty hidden row for jQuery -->
                            <tr class="empty-row-offday screen-reader-text">
                                <td align="left">
                                    <div class="wbbm_bus_icon_group">
                                        <span class="wbbm_bus_route_icon wbbm_bus_route_icon1"><img src="<?php echo esc_url(WBTM_PLUGIN_URL . 'images/bus_calendar.png'); ?>" /></span>
                                        <input type="text" class="repeatable-offday-from-field" name="wbtm_od_offdate_from[]" placeholder="2020-12-31" />
                                    </div>
                                </td>
                                <td align="left">
                                    <div class="wbbm_bus_icon_group">
                                        <span class="wbbm_bus_route_icon wbbm_bus_route_icon1"><img src="<?php echo esc_url(WBTM_PLUGIN_URL . 'images/bus_route_clock.png'); ?>" /></span>
                                        <input type="text" class="repeatable-offtime-from-field" name="wbtm_od_offtime_from[]" placeholder="09:00 am" />
                                    </div>
                                </td>
                                <td align="left">
                                    <span class="wbtm_bus_off_on_date_arrow"><img src="<?php echo esc_url(WBTM_PLUGIN_URL . 'images/bus_route_arrow.png'); ?>" /></span>
                                </td>
                                <td align="left">
                                    <div class="wbbm_bus_icon_group">
                                        <span class="wbbm_bus_route_icon wbbm_bus_route_icon1"><img src="<?php echo esc_url(WBTM_PLUGIN_URL . 'images/bus_calendar.png'); ?>" /></span>
                                        <input type="text" class="repeatable-offday-to-field" name="wbtm_od_offdate_to[]" placeholder="2020-12-31" />
                                    </div>
                                </td>
                                <td align="left">
                                    <div class="wbbm_bus_icon_group">
                                        <span class="wbbm_bus_route_icon wbbm_bus_route_icon1"><img src="<?php echo esc_url(WBTM_PLUGIN_URL . 'images/bus_route_clock.png'); ?>" /></span>
                                        <input type="text" class="repeatable-offtime-to-field" name="wbtm_od_offtime_to[]" placeholder="09:59 pm" />
                                    </div>
                                </td>
                                <td align="left">
                                    <a class="button remove-bp-row" href="#"><i class="fas fa-minus-circle"></i><?php esc_html_e('Remove', 'bus-booking-manager'); ?></a>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <a id="add-offday-row" class="button" href="#"><i class="fas fa-plus"></i><?php esc_html_e('Add More off date', 'bus-booking-manager'); ?></a>
                </div>
            </section>

            <script type="text/javascript">
                jQuery(document).ready(function($) {
                    var datePickerOpt = {
                        dateFormat: "yy-mm-dd",
                        minDate: 0
                    };
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
                    <label><?php echo esc_html($cpt_label) . ' ' . esc_html__('Off days', 'bus-booking-manager'); ?></label>
                    <br>
                    <span><?php esc_html_e('Check ' . esc_html($cpt_label) . ' off days.', 'bus-booking-manager'); ?></span>
                </div>
                <div class='offday-sec'>
                    <?php
                    $days = array(
                        'sun' => esc_html__('Sunday', 'bus-booking-manager'),
                        'mon' => esc_html__('Monday', 'bus-booking-manager'),
                        'tue' => esc_html__('Tuesday', 'bus-booking-manager'),
                        'wed' => esc_html__('Wednesday', 'bus-booking-manager'),
                        'thu' => esc_html__('Thursday', 'bus-booking-manager'),
                        'fri' => esc_html__('Friday', 'bus-booking-manager'),
                        'sat' => esc_html__('Saturday', 'bus-booking-manager')
                    );
                    $day_values = array(7, 1, 2, 3, 4, 5, 6);
                    $i = 0;
                    foreach ($days as $day_id => $day_name) : ?>
                        <label for="<?php echo esc_attr($day_id); ?>">
                            <input type="checkbox" style="text-align: left;width: auto;" name="weekly_offday[]" value="<?php echo esc_attr($day_values[$i]); ?>" id="<?php echo esc_attr($day_id); ?>" <?php checked(in_array($day_values[$i], $weekly_offday)); ?>>
                            <?php echo esc_html($day_name); ?>
                        </label>
                    <?php
                        $i++;
                    endforeach;
                    ?>
                </div>
            </section>

            <script type="text/javascript">
                jQuery(document).ready(function($) {
                    $("#od_start, #od_end").datepicker({
                        dateFormat: "yy-mm-dd",
                        minDate: 0
                    });
                });
            </script>

        </div>
    </div>
</div>
