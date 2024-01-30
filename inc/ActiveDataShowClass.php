<?php
if (!defined('ABSPATH')) exit;  // if direct access

class ActiveDataShowClass extends CommonClass
{
    public function __construct()
    {

    }

    //next 6  date suggestion
   public function active_date_picker($singleBus, $post_id)
    {

        if($singleBus){
            $wbtm_bus_on_dates = get_post_meta($post_id, 'wbtm_bus_on_date', true) ? maybe_unserialize(get_post_meta($post_id, 'wbtm_bus_on_date', true)) : [];
            $wbtm_offday_schedules = get_post_meta($post_id, 'wbtm_offday_schedule', true) ? get_post_meta($post_id, 'wbtm_offday_schedule', true) : [];
            $show_operational_on_day = get_post_meta($post_id, 'show_operational_on_day', true) ? get_post_meta($post_id, 'show_operational_on_day', true) : '';
            $show_off_day = get_post_meta($post_id, 'show_off_day', true) ? get_post_meta($post_id, 'show_off_day', true) : '';

            if($wbtm_bus_on_dates){
                $wbtm_bus_on_dates_arr = explode(',', $wbtm_bus_on_dates);
                $onday = array();
                foreach ($wbtm_bus_on_dates_arr as $ondate) {
                    $onday[] = '"' . date('d-m-Y', strtotime($ondate)) . '"';
                }
                $on_particular_date = implode(',', $onday);
                $enableDates = '[' . $on_particular_date . ']';
            }else{
                $enableDates = '0';
            }



            $alloffdays = array();
            foreach ($wbtm_offday_schedules as $wbtm_offday_schedule) {
                $alloffdays =  array_unique(array_merge($alloffdays, displayDates($wbtm_offday_schedule['from_date'], $wbtm_offday_schedule['to_date'])));;
            }
            $offday = array();
            foreach ($alloffdays as $alloffday) {
                $offday[] = '"' . date('d-m-Y', strtotime($alloffday)) . '"';
            }
            $off_particular_date = implode(',', $offday);
            $off_particular_date = '[' . $off_particular_date . ']';
            $weekly_offday = get_post_meta($post_id, 'weekly_offday', true) ? get_post_meta($post_id, 'weekly_offday', true) : [];
            $weekly_offday = implode(',', $weekly_offday);
            $weekly_offday = '[' . $weekly_offday . ']';
            

            echo "<input id='".'all_date_picker_info'."' data-single_bus='".($singleBus ? 1 : 0)."'  data-enableDates='".$enableDates."' data-off_particular_date='".$off_particular_date."' data-weekly_offday='".$weekly_offday."' data-enable_onday='".$show_operational_on_day."' data-enable_offday='".$show_off_day."' data-date_format='".$this->convert_datepicker_dateformat()."' type='".'hidden'."' />";

        }else{

            $global_offdates = wbbm_get_option('global_particular_onday', 'wbbm_global_offday_sec', 0);
            $global_offdays = wbbm_get_option('bus_global_offdays', 'wbbm_global_offday_sec', 0);

            $global_offdates = isset($global_offdates) ? $global_offdates : [];
            $global_offdays = isset($global_offdays) ? $global_offdays : [];

            if ($global_offdates) {
                $global_offdates_arr = explode(', ', $global_offdates);
                $pday = array();
                foreach ($global_offdates_arr as $offdate) {
                    $pday[] = '"' . date('d-m-Y', strtotime($offdate)) . '"';
                }
                $particular_date = implode(',', $pday);

                $disableDates = '[' . $particular_date . ']';
            } else {
                $disableDates = '['.']';
            }

            if ($global_offdays) {
                $particular_offdays = implode(',', $global_offdays);

                $disableDays = '[' . $particular_offdays . ']';
            } else {
                $disableDays = '['.']';
            }

            echo "<input id='".'all_date_picker_info'."' data-single_bus='".'0'."'  data-disableDates='".$disableDates."' data-disableDays='".$disableDays."'  data-date_format='".$this->convert_datepicker_dateformat()."' type='".'hidden'."' />";

        }

    }

}

