<?php
if (!defined('ABSPATH')) {
    exit;  // Exit if accessed directly
}

class ActiveDataShowClass extends CommonClass {
    public function __construct() {
    }

    // Next 6 date suggestion
    public function active_date_picker($singleBus, $post_id) {
        if ($singleBus) {
            $wbtm_bus_on_dates = get_post_meta($post_id, 'wbtm_bus_on_date', true) ? maybe_unserialize(get_post_meta($post_id, 'wbtm_bus_on_date', true)) : [];
            $wbtm_offday_schedules = get_post_meta($post_id, 'wbtm_offday_schedule', true) ? get_post_meta($post_id, 'wbtm_offday_schedule', true) : [];
            $show_operational_on_day = sanitize_text_field(get_post_meta($post_id, 'show_operational_on_day', true));
            $show_off_day = sanitize_text_field(get_post_meta($post_id, 'show_off_day', true));

            if ($wbtm_bus_on_dates) {
                $wbtm_bus_on_dates = is_array($wbtm_bus_on_dates) ? $wbtm_bus_on_dates : explode(', ', sanitize_text_field($wbtm_bus_on_dates));
                $onday = array();

                foreach ($wbtm_bus_on_dates as $ondate) {
                    $onday[] = '"' . esc_attr(gmdate('d-m-Y', strtotime($ondate))) . '"';
                }

                $on_particular_date = implode(',', $onday);
                $enableDates = '[' . esc_attr($on_particular_date) . ']';
            } else {
                $enableDates = '0';
            }

            $alloffdays = array();
            foreach ($wbtm_offday_schedules as $wbtm_offday_schedule) {
                $from_date = sanitize_text_field($wbtm_offday_schedule['from_date']);
                $to_date = sanitize_text_field($wbtm_offday_schedule['to_date']);
                $alloffdays = array_unique(array_merge($alloffdays, displayDates($from_date, $to_date)));
            }

            $offday = array();
            foreach ($alloffdays as $alloffday) {
                $offday[] = '"' . esc_attr(gmdate('d-m-Y', strtotime($alloffday))) . '"';
            }

            $off_particular_date = implode(',', $offday);
            $off_particular_date = '[' . esc_attr($off_particular_date) . ']';

            $weekly_offday = get_post_meta($post_id, 'weekly_offday', true) ? get_post_meta($post_id, 'weekly_offday', true) : [];
            $weekly_offday = is_array($weekly_offday) ? array_map('sanitize_text_field', $weekly_offday) : [];
            $weekly_offday = '[' . esc_attr(implode(',', $weekly_offday)) . ']';

            echo "<input id='" . esc_attr('all_date_picker_info') . "' data-single_bus='" . esc_attr($singleBus ? 1 : 0) . "' data-enableDates='" . esc_attr($enableDates) . "' data-off_particular_date='" . esc_attr($off_particular_date) . "' data-weekly_offday='" . esc_attr($weekly_offday) . "' data-enable_onday='" . esc_attr($show_operational_on_day) . "' data-enable_offday='" . esc_attr($show_off_day) . "' data-date_format='" . esc_attr($this->convert_datepicker_dateformat()) . "' type='hidden' />";
        } else {
            $global_offdates = wbbm_get_option('global_particular_onday', 'wbbm_global_offday_sec', 0);
            $global_offdays = wbbm_get_option('bus_global_offdays', 'wbbm_global_offday_sec', 0);

            $global_offdates = !empty($global_offdates) ? explode(', ', sanitize_text_field($global_offdates)) : [];
            $global_offdays = is_array($global_offdays) ? array_map('sanitize_text_field', $global_offdays) : [];

            if (!empty($global_offdates)) {
                $pday = array();

                foreach ($global_offdates as $offdate) {
                    $pday[] = '"' . esc_attr(gmdate('d-m-Y', strtotime($offdate))) . '"';
                }

                $particular_date = implode(',', $pday);
                $disableDates = '[' . esc_attr($particular_date) . ']';
            } else {
                $disableDates = '[]';
            }

            if (!empty($global_offdays)) {
                $particular_offdays = implode(',', $global_offdays);
                $disableDays = '[' . esc_attr($particular_offdays) . ']';
            } else {
                $disableDays = '[]';
            }

            echo "<input id='" . esc_attr('all_date_picker_info') . "' data-single_bus='0' data-disableDates='" . esc_attr($disableDates) . "' data-disableDays='" . esc_attr($disableDays) . "' data-date_format='" . esc_attr($this->convert_datepicker_dateformat()) . "' type='hidden' />";
        }
    }
}
?>
