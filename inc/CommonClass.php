<?php
if (!defined('ABSPATH')) exit; // if direct access

class CommonClass
{
    public function __construct()
    {
    }

    public function wbbm_convert_date_to_php($date)
    {
        $date_format = get_option('date_format');

        // Ensure the date is properly sanitized
        $date = sanitize_text_field($date);

        if ($date_format == 'Y-m-d' || $date_format == 'm/d/Y' || $date_format == 'd/m/Y') {
            if ($date_format == 'd/m/Y') {
                $date = str_replace('/', '-', $date);
            }
        }
        return gmdate('Y-m-d', strtotime($date));
    }

    public function wbbm_get_datetime($date, $type)
    {
        // Ensure the date is properly sanitized
        $date = sanitize_text_field($date);

        $date_format = get_option('date_format');
        $time_format = get_option('time_format');
        $wpdatesettings = $date_format . ' ' . $time_format;
        $timestamp = strtotime($date);

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

    public function convert_datepicker_dateformat()
    {
        $date_format = get_option('date_format');
        $dformat = str_replace('d', 'dd', $date_format);
        $dformat = str_replace('m', 'mm', $dformat);
        $dformat = str_replace('Y', 'yy', $dformat);

        if (in_array($date_format, ['Y-m-d', 'm/d/Y', 'd/m/Y', 'Y/d/m', 'Y-d-m'])) {
            return str_replace('/', '-', $dformat);
        } elseif (in_array($date_format, ['Y.m.d', 'm.d.Y', 'd.m.Y', 'Y.d.m'])) {
            return str_replace('.', '-', $dformat);
        } else {
            return 'yy-mm-dd';
        }
    }

    // Function to get page slug
    public function wbbm_get_page_by_slug($slug)
    {
        $slug = sanitize_text_field($slug); // Sanitize slug input
        if ($pages = get_pages()) {
            foreach ($pages as $page) {
                if ($slug === $page->post_name) return $page;
            }
        }
        return false;
    }
}
