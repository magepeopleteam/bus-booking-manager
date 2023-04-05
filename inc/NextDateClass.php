<?php
if (!defined('ABSPATH')) exit;  // if direct access

class NextDateClass extends CommonClass
{
    public function __construct()
    {

    }

    //next 6  date suggestion
   public function mage_next_date_suggestion($return, $single_bus, $target)
    {
        $date = $return ? $this->mage_bus_isset('r_date') : $this->mage_bus_isset('j_date');
        $date = mage_wp_date($date, 'Y-m-d');
        if ($date) {
            $tab_date = isset($_GET['tab_date']) ? $_GET['tab_date'] : mage_wp_date($this->mage_bus_isset('j_date'), 'Y-m-d');
            $tab_date_r = isset($_GET['tab_date_r']) ? $_GET['tab_date_r'] : mage_wp_date($this->mage_bus_isset('r_date'), 'Y-m-d');
            $next_date = $return ? $tab_date_r : $tab_date;
            $next_date_text = $next_date;
            ?>
            <div class="mage_default_xs">
                <ul class="mage_list_inline flexEqual mage_next_date">
                    <?php
                    for ($i = 0; $i < 6; $i++) {
                        ?>
                        <li class="<?php echo $date == $next_date ? 'mage_active' : ''; ?>">
                            <a href="<?php echo $single_bus ? '' : get_site_url() . '/' . $target; ?>?bus_start_route=<?php echo strip_tags($_GET['bus_start_route']); ?>&bus_end_route=<?php echo strip_tags($_GET['bus_end_route']); ?>&j_date=<?php echo $return ? strip_tags($_GET['j_date']) : $next_date_text; ?>&r_date=<?php echo $return ? $next_date : (isset($_GET['r_date']) ? strip_tags($_GET['r_date']) : ''); ?>&bus-r=<?php echo (isset($_GET['bus-r']) ? strip_tags($_GET['bus-r']) : ''); ?>&tab_date=<?php echo $tab_date; ?>&tab_date_r=<?php echo $tab_date_r; ?>" data-sroute='<?php echo strip_tags($_GET['bus_start_route']); ?>' data-eroute='<?php echo strip_tags($_GET['bus_end_route']); ?>' data-jdate='<?php echo $return ? strip_tags($_GET['j_date']) : $next_date; ?>' data-rdate='<?php echo $return ? $next_date : (isset($_GET['r_date']) ? strip_tags($_GET['r_date']) : ''); ?>' class='wbtm_next_day_search'>
                                <?php echo $this->get_wbbm_datetime($next_date, 'date-text') ?>
                                <?php //echo mage_wp_date($next_date);
                                ?>
                            </a>
                        </li>
                        <?php
                        $next_date = date('Y-m-d', strtotime($next_date . ' +1 day'));

                        $next_date_text = $next_date;
                    }
                    ?>
                </ul>
            </div>
            <?php
        }
    }


    //nearest max 6  date suggestion for single bus
    function mage_next_date_suggestion_single($return, $single_bus, $target)
    {
        $j_date = $this->mage_bus_isset('j_date');
        $j_date = mage_wp_date($j_date,'Y-m-d');
        $show_operational_on_day = get_post_meta(get_the_ID(), 'show_operational_on_day', true) ?: 'no';
        $wbtm_bus_on_dates = get_post_meta(get_the_id(), 'wbtm_bus_on_date', true) ? maybe_unserialize(get_post_meta(get_the_id(), 'wbtm_bus_on_date', true)) : [];

        $show_off_day = get_post_meta(get_the_ID(), 'show_off_day', true) ?: 'no';
        $wbtm_offday_schedules = get_post_meta(get_the_id(), 'wbtm_offday_schedule', true)?get_post_meta(get_the_id(), 'wbtm_offday_schedule', true):[];
        $weekly_offday = get_post_meta(get_the_id(), 'weekly_offday', true) ? get_post_meta(get_the_id(), 'weekly_offday', true):[];

        // echo '<pre>'; echo print_r($wbtm_offday_schedules); echo '<pre>';


        if ($wbtm_bus_on_dates && $show_operational_on_day === 'yes') {
            ?>
            <div class="mage_default_xs">
                <ul class="mage_list_inline flexEqual mage_next_date">
                    <?php
                    $wbtm_bus_on_dates_arr = explode(',', $wbtm_bus_on_dates);
                    foreach ($wbtm_bus_on_dates_arr as $i => $ondate) {
                        $ondate = mage_wp_date($ondate, 'Y-m-d');
                        if ($j_date <= $ondate) {
                            $ondate = $ondate ?: $j_date;
                            ?>
                            <?php if(!in_array($j_date, $wbtm_bus_on_dates_arr) && $i === 0) : ?>
                            <li class="mage_active">
                                <a href="#">
                                    <?php
                                    echo $this->get_wbbm_datetime($j_date, 'date-text')
                                    ?>
                                </a>
                            </li>
                            <?php endif; ?>
                            <li class="<?php echo $j_date == $ondate ? 'mage_active' : ''; ?>">
                                <a href="<?php echo $single_bus ? '' : get_site_url() . '/' . $target; ?>?bus_start_route=<?php echo strip_tags($_GET['bus_start_route']); ?>&bus_end_route=<?php echo strip_tags($_GET['bus_end_route']); ?>&j_date=<?php echo $return ? strip_tags($_GET['j_date']) : $ondate; ?>&r_date=<?php echo $return ? $ondate : (isset($_GET['r_date']) ? strip_tags($_GET['r_date']) : ''); ?>&bus-r=<?php echo (isset($_GET['bus-r']) ? strip_tags($_GET['bus-r']) : ''); ?>" data-sroute='<?php echo strip_tags($_GET['bus_start_route']); ?>' data-eroute='<?php echo strip_tags($_GET['bus_end_route']); ?>' data-jdate='<?php echo $return ? strip_tags($_GET['j_date']) : ''; ?>' data-rdate='<?php echo $return ? '' : (isset($_GET['r_date']) ? strip_tags($_GET['r_date']) : ''); ?>' class='wbtm_next_day_search'>
                                    <?php
                                    echo $this->get_wbbm_datetime($ondate, 'date-text')
                                    ?>
                                </a>
                            </li>
                            <?php
                        }
                    }
                    ?>

                </ul>
            </div>

            <?php
        }elseif (($wbtm_offday_schedules || $weekly_offday) && $show_off_day === 'yes'){


            $alloffdays = array();
            foreach ($wbtm_offday_schedules as $wbtm_offday_schedule) {
                $alloffdays =  array_unique(array_merge($alloffdays, displayDates($wbtm_offday_schedule['from_date'], $wbtm_offday_schedule['to_date'])));;
            }

            $offday = array();
            foreach ($alloffdays as $alloffday) {
                $offday[] =  date('Y-m-d', strtotime($alloffday));
            }
            $next_date = $j_date;

            $next_date_text = $next_date;

            $weekly_offday = get_post_meta(get_the_id(), 'weekly_offday', true) ? get_post_meta(get_the_id(), 'weekly_offday', true) : [];

            ?>
            <div class="mage_default_xs">
                <ul class="mage_list_inline flexEqual mage_next_date">
                    <?php
                    $i = 0;
                    for ($m = 1; $m < 6; $i++) {
                        if (!in_array($next_date, $offday) and !in_array(date('w', strtotime($next_date)), $weekly_offday) and $m < 6) {
                            $m++;
                            ?>
                            <li class="<?php echo $j_date == $next_date ? 'mage_active' : ''; ?>">
                                <a href="<?php echo $single_bus ? '' : get_site_url() . '/' . $target; ?>?bus_start_route=<?php echo strip_tags($_GET['bus_start_route']); ?>&bus_end_route=<?php echo strip_tags($_GET['bus_end_route']); ?>&j_date=<?php echo $return ? strip_tags($_GET['j_date']) : $next_date_text; ?>&r_date=<?php echo $return ? $next_date : (isset($_GET['r_date']) ? strip_tags($_GET['r_date']) : ''); ?>&bus-r=<?php echo (isset($_GET['bus-r']) ? strip_tags($_GET['bus-r']) : ''); ?>" data-sroute='<?php echo strip_tags($_GET['bus_start_route']); ?>' data-eroute='<?php echo strip_tags($_GET['bus_end_route']); ?>' data-jdate='<?php echo $return ? strip_tags($_GET['j_date']) : $next_date; ?>' data-rdate='<?php echo $return ? $next_date : (isset($_GET['r_date']) ? strip_tags($_GET['r_date']) : ''); ?>' class='wbtm_next_day_search'>
                                    <?php echo $this->get_wbbm_datetime($next_date, 'date-text') ?>
                                </a>
                            </li>
                            <?php
                        }
                        $next_date = date('Y-m-d', strtotime($next_date . ' +1 day'));

                        $next_date_text = $next_date;
                    }
                    ?>
                </ul>
            </div>

            <?php
        }else{
            $this->mage_next_date_suggestion(false, true, $target);

        }
    }

    public function mage_bus_isset($parameter)
    {
        return isset($_GET[$parameter]) ? strip_tags($_GET[$parameter]) : false;
    }



}

