<?php
if (!defined('ABSPATH')) exit;  // if direct access

class NextDateClass extends CommonClass
{
    public function __construct()
    {
        // Constructor code here (if needed)
    }

    // Next 6 date suggestion
    public function mage_next_date_suggestion($return, $single_bus, $target)
    {
        $date = $return ? $this->mage_bus_isset('r_date') : $this->mage_bus_isset('j_date');
        $date = mage_wp_date($date, 'Y-m-d');
        if ($date) {
            // Verify next-date nonce if provided. If valid, allow using `tab_date`/`tab_date_r` from GET.
            $next_date_nonce = isset($_GET['bus_search_nonce']) ? sanitize_text_field(wp_unslash($_GET['bus_search_nonce'])) : '';
            $next_date_nonce_ok = $next_date_nonce && wp_verify_nonce($next_date_nonce, 'bus_search_nonce_action');
            if ( isset($_GET['bus_search_nonce']) && ! $next_date_nonce_ok ) {
                if ( function_exists('wc_add_notice') ) {
                    wc_add_notice(__('Security check failed. Invalid date request.', 'bus-booking-manager'), 'error');
                }
            }

            $tab_date = ($next_date_nonce_ok && isset($_GET['tab_date'])) ? sanitize_text_field(wp_unslash($_GET['tab_date'])) : mage_wp_date($this->mage_bus_isset('j_date'), 'Y-m-d');
            $tab_date_r = ($next_date_nonce_ok && isset($_GET['tab_date_r'])) ? sanitize_text_field(wp_unslash($_GET['tab_date_r'])) : mage_wp_date($this->mage_bus_isset('r_date'), 'Y-m-d');
            // sanitize commonly used GET parameters to avoid undefined index warnings
            $bus_start_route = $this->mage_bus_isset('bus_start_route') ?: '';
            $bus_end_route = $this->mage_bus_isset('bus_end_route') ?: '';
            $j_date_get = isset($_GET['j_date']) ? wp_strip_all_tags(wp_unslash($_GET['j_date'])) : '';
            $r_date_get = isset($_GET['r_date']) ? wp_strip_all_tags(wp_unslash($_GET['r_date'])) : '';
            $bus_r = isset($_GET['bus-r']) ? wp_strip_all_tags(wp_unslash($_GET['bus-r'])) : '';
            // safe GET values only if nonce is valid; otherwise fallback to internal values
            $j_date_safe = $next_date_nonce_ok ? $j_date_get : ($this->mage_bus_isset('j_date') ? wp_strip_all_tags(wp_unslash($this->mage_bus_isset('j_date'))) : '');
            $r_date_safe = $next_date_nonce_ok ? $r_date_get : ($this->mage_bus_isset('r_date') ? wp_strip_all_tags(wp_unslash($this->mage_bus_isset('r_date'))) : '');
            $next_date = $return ? $tab_date_r : $tab_date;
            $next_date_text = $next_date;
?>
            <div class="mage_default_xs">
                <ul class="mage_list_inline flexEqual mage_next_date">
                    <?php
                    // create a nonce for next-date links
                    $next_date_link_nonce = wp_create_nonce('bus_search_nonce_action');
                    $next_date_nonce_query = '&bus_search_nonce=' . rawurlencode($next_date_link_nonce);

                    for ($i = 0; $i < 6; $i++) {
                    ?>
                        <li class="<?php echo esc_attr($date == $next_date ? 'mage_active' : ''); ?>">
                            <a href="<?php echo esc_url($single_bus ? '' : get_site_url() . '/' . esc_attr($target)); ?>?bus_start_route=<?php echo esc_attr($bus_start_route); ?>&bus_end_route=<?php echo esc_attr($bus_end_route); ?>&j_date=<?php echo esc_attr($return ? $j_date_get : $next_date_text); ?>&r_date=<?php echo esc_attr($return ? $next_date : $r_date_get); ?>&bus-r=<?php echo esc_attr($bus_r); ?>&tab_date=<?php echo esc_attr($tab_date); ?>&tab_date_r=<?php echo esc_attr($tab_date_r); ?>" data-sroute='<?php echo esc_attr($bus_start_route); ?>' data-eroute='<?php echo esc_attr($bus_end_route); ?>' data-jdate='<?php echo esc_attr($return ? $j_date_get : $next_date); ?>' data-rdate='<?php echo esc_attr($return ? $next_date : $r_date_get); ?>' class='wbtm_next_day_search'>
                                <?php echo esc_html($this->wbbm_get_datetime($next_date, 'date-text')); ?>
                            </a>
                        </li>
                    <?php
                        $next_date = gmdate('Y-m-d', strtotime($next_date . ' +1 day'));
                        $next_date_text = $next_date;
                    }
                    ?>
                </ul>
            </div>
        <?php
        }
    }

    // Nearest max 6 date suggestion for single bus
    public function mage_next_date_suggestion_single($return, $single_bus, $target)
    {
        $j_date = $this->mage_bus_isset('j_date');
        $j_date = mage_wp_date($j_date, 'Y-m-d');
        // sanitize commonly used GET parameters to avoid undefined index warnings
        $bus_start_route = $this->mage_bus_isset('bus_start_route') ?: '';
        $bus_end_route = $this->mage_bus_isset('bus_end_route') ?: '';
        $j_date_get = isset($_GET['j_date']) ? wp_strip_all_tags(wp_unslash($_GET['j_date'])) : '';
        $r_date_get = isset($_GET['r_date']) ? wp_strip_all_tags(wp_unslash($_GET['r_date'])) : '';
        $bus_r = isset($_GET['bus-r']) ? wp_strip_all_tags(wp_unslash($_GET['bus-r'])) : '';

        // verify incoming next-date nonce (if present) before trusting GET tab/date parameters
        $next_date_nonce = isset($_GET['bus_search_nonce']) ? sanitize_text_field(wp_unslash($_GET['bus_search_nonce'])) : '';
        $next_date_nonce_ok = $next_date_nonce && wp_verify_nonce($next_date_nonce, 'bus_search_nonce_action');
        if ( isset($_GET['bus_search_nonce']) && ! $next_date_nonce_ok ) {
            if ( function_exists('wc_add_notice') ) {
                wc_add_notice(__('Security check failed. Invalid date request.', 'bus-booking-manager'), 'error');
            }
        }

        // safe GET values only if nonce is valid; otherwise fallback to internal values
        $j_date_safe = $next_date_nonce_ok ? $j_date_get : ($this->mage_bus_isset('j_date') ? wp_strip_all_tags(wp_unslash($this->mage_bus_isset('j_date'))) : '');
        $r_date_safe = $next_date_nonce_ok ? $r_date_get : ($this->mage_bus_isset('r_date') ? wp_strip_all_tags(wp_unslash($this->mage_bus_isset('r_date'))) : '');

        // create a nonce for next-date links
        $next_date_link_nonce = wp_create_nonce('bus_search_nonce_action');
        $next_date_nonce_query = '&bus_search_nonce=' . rawurlencode($next_date_link_nonce);
        $show_operational_on_day = sanitize_text_field(get_post_meta(get_the_ID(), 'show_operational_on_day', true)) ?: 'no';
        $wbtm_bus_on_dates = get_post_meta(get_the_id(), 'wbtm_bus_on_date', true) ? maybe_unserialize(get_post_meta(get_the_id(), 'wbtm_bus_on_date', true)) : [];

        $show_off_day = sanitize_text_field(get_post_meta(get_the_ID(), 'show_off_day', true)) ?: 'no';
        $wbtm_offday_schedules = get_post_meta(get_the_id(), 'wbtm_offday_schedule', true) ? get_post_meta(get_the_id(), 'wbtm_offday_schedule', true) : [];
        $weekly_offday = get_post_meta(get_the_id(), 'weekly_offday', true) ? get_post_meta(get_the_id(), 'weekly_offday', true) : [];

        if ($wbtm_bus_on_dates && $show_operational_on_day === 'yes') {
        ?>
            <div class="mage_default_xs">
                <ul class="mage_list_inline flexEqual mage_next_date">
                    <?php
                    $wbtm_bus_on_dates_arr = is_array($wbtm_bus_on_dates) ? $wbtm_bus_on_dates : explode(',', $wbtm_bus_on_dates);
                    foreach ($wbtm_bus_on_dates_arr as $i => $ondate) {
                        $ondate = mage_wp_date($ondate, 'Y-m-d');
                        if ($j_date <= $ondate) {
                            $ondate = $ondate ?: $j_date;
                    ?>
                            <?php if (!in_array($j_date, $wbtm_bus_on_dates_arr) && $i === 0) : ?>
                                <li class="mage_active">
                                    <a href="#">
                                        <?php echo esc_html($this->wbbm_get_datetime($j_date, 'date-text')); ?>
                                    </a>
                                </li>
                            <?php endif; ?>
                            <li class="<?php echo esc_attr($j_date == $ondate ? 'mage_active' : ''); ?>">
                                <a href="<?php echo esc_url($single_bus ? '' : get_site_url() . '/' . esc_attr($target)); ?>?bus_start_route=<?php echo esc_attr($bus_start_route); ?>&bus_end_route=<?php echo esc_attr($bus_end_route); ?>&j_date=<?php echo esc_attr($return ? $j_date_get : $ondate); ?>&r_date=<?php echo esc_attr($return ? $ondate : $r_date_get); ?>&bus-r=<?php echo esc_attr($bus_r); ?>" data-sroute='<?php echo esc_attr($bus_start_route); ?>' data-eroute='<?php echo esc_attr($bus_end_route); ?>' data-jdate='<?php echo esc_attr($return ? $j_date_get : ''); ?>' data-rdate='<?php echo esc_attr($return ? '' : $r_date_get); ?>' class='wbtm_next_day_search'>
                                    <?php echo esc_html($this->wbbm_get_datetime($ondate, 'date-text')); ?>
                                </a>
                            </li>
                    <?php
                        }
                    }
                    ?>
                </ul>
            </div>
        <?php
        } elseif (($wbtm_offday_schedules || $weekly_offday) && $show_off_day === 'yes') {
            $alloffdays = [];
            foreach ($wbtm_offday_schedules as $wbtm_offday_schedule) {
                $alloffdays = array_unique(array_merge($alloffdays, displayDates($wbtm_offday_schedule['from_date'], $wbtm_offday_schedule['to_date'])));
            }

            $offday = [];
            foreach ($alloffdays as $alloffday) {
                $offday[] = gmdate('Y-m-d', strtotime($alloffday));
            }
            $next_date = $j_date;

            $next_date_text = $next_date;

        ?>
            <div class="mage_default_xs">
                <ul class="mage_list_inline flexEqual mage_next_date">
                    <?php
                    $i = 0;
                    for ($m = 1; $m < 6; $i++) {
                        if (!in_array($next_date, $offday) && !in_array(gmdate('w', strtotime($next_date)), $weekly_offday) && $m < 6) {
                            $m++;
                    ?>
                            <li class="<?php echo esc_attr($j_date == $next_date ? 'mage_active' : ''); ?>">
                                <a href="<?php echo esc_url($single_bus ? '' : get_site_url() . '/' . esc_url($target)); ?>?bus_start_route=<?php echo esc_attr($bus_start_route); ?>&bus_end_route=<?php echo esc_attr($bus_end_route); ?>&j_date=<?php echo esc_attr($return ? $j_date_get : $next_date_text); ?>&r_date=<?php echo esc_attr($return ? $next_date : $r_date_get); ?>&bus-r=<?php echo esc_attr($bus_r); ?>" data-sroute='<?php echo esc_attr($bus_start_route); ?>' data-eroute='<?php echo esc_attr($bus_end_route); ?>' data-jdate='<?php echo esc_attr($return ? $j_date_get : $next_date); ?>' data-rdate='<?php echo esc_attr($return ? $next_date : $r_date_get); ?>' class='wbtm_next_day_search'>
                                    <?php echo esc_html($this->wbbm_get_datetime($next_date, 'date-text')); ?>
                                </a>
                            </li>
                    <?php
                        }
                        $next_date = gmdate('Y-m-d', strtotime($next_date . ' +1 day'));
                        $next_date_text = $next_date;
                    }
                    ?>
                </ul>
            </div>
<?php
        } else {
            $this->mage_next_date_suggestion(false, true, $target);
        }
    }

    public function mage_bus_isset($parameter)
    {
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        return isset($_GET[$parameter]) ? wp_strip_all_tags(wp_unslash($_GET[$parameter])) : false;
    }
}
