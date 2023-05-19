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

    function convert_datepicker_dateformat()
    {
        $date_format = get_option('date_format');
        // return $date_format;
        // $php_d     = array('F', 'j', 'Y', 'm','d','D','M','y');
        // $js_d   = array('d', 'M', 'yy','mm','dd','tt','mm','yy');
        $dformat = str_replace('d', 'dd', $date_format);
        $dformat = str_replace('m', 'mm', $dformat);
        $dformat = str_replace('Y', 'yy', $dformat);

        if ($date_format == 'Y-m-d' || $date_format == 'm/d/Y' || $date_format == 'd/m/Y' || $date_format == 'Y/d/m' || $date_format == 'Y-d-m') {
            return str_replace('/', '-', $dformat);
        } elseif ($date_format == 'Y.m.d' || $date_format == 'm.d.Y' || $date_format == 'd.m.Y' || $date_format == 'Y.d.m' || $date_format == 'Y.d.m') {
            return str_replace('.', '-', $dformat);
        } else {
            return 'yy-mm-dd';
        }
    }

    // Function to get page slug
    function wbbm_get_page_by_slug($slug)
    {
        if ($pages = get_pages())
            foreach ($pages as $page)
                if ($slug === $page->post_name) return $page;
        return false;
    }



}

