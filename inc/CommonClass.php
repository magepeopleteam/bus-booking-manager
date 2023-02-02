<?php
if (!defined('ABSPATH')) exit;  // if direct access

class CommonClass
{
    public function __construct()
    {

    }


    public function wbbm_convert_date_to_php($date)
    {

        $date_format = get_option('date_format');
        if ($date_format == 'Y-m-d' || $date_format == 'm/d/Y' || $date_format == 'm/d/Y') {
            if ($date_format == 'd/m/Y') {
                $date = str_replace('/', '-', $date);
            }
        }
        return date('Y-m-d', strtotime($date));
    }

    function get_wbbm_datetime($date, $type)
    {
        $date_format = get_option('date_format');
        $time_format = get_option('time_format');
        $wpdatesettings = $date_format . '  ' . $time_format;
        $timezone = wp_timezone_string();
        $timestamp = strtotime($date . ' ' . $timezone);

        if ($type == 'date') {
            return wp_date($date_format, $timestamp);
        }
        if ($type == 'date-time') {
            return wp_date($wpdatesettings, $timestamp);
        }
        if ($type == 'date-text') {

            return wp_date($date_format, $timestamp);
        }

        if ($type == 'date-time-text') {
            return wp_date($wpdatesettings, $timestamp, wp_timezone());
        }
        if ($type == 'time') {
            return wp_date($time_format, $timestamp, wp_timezone());
        }

        if ($type == 'day') {
            return wp_date('d', $timestamp);
        }
        if ($type == 'month') {
            return wp_date('M', $timestamp);
        }
    }



}

