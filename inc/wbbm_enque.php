<?php 
if ( ! defined( 'ABSPATH' ) ) { die; } // Cannot access pages directly.

// Enqueue Scripts for admin dashboard
add_action('admin_enqueue_scripts', 'wbbm_bus_admin_scripts');
function wbbm_bus_admin_scripts() {
    wp_enqueue_script('jquery-ui-datepicker');
    wp_enqueue_script('jquery-ui-core');   
    wp_enqueue_style('wbbm-clocklet-style',plugin_dir_url( __DIR__ ).'css/clocklet.css',array());
    wp_enqueue_style('mep-admin-style',plugin_dir_url( __DIR__ ).'css/admin_style.css',array(),time());
    wp_enqueue_style('mep-jquery-ui-style',plugin_dir_url( __DIR__ ).'css/jquery-ui.css',array());
    wp_enqueue_style('font-awesome-css-cdn', "https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.2.0/css/all.min.css", null, 1);
    wp_enqueue_script('wbbm-select2-lib',plugin_dir_url( __DIR__ ).'js/select2.full.min.js',array('jquery','jquery-ui-core'),1,true); 
    wp_register_script('multidatepicker-wbbm', 'https://cdn.rawgit.com/dubrox/Multiple-Dates-Picker-for-jQuery-UI/master/jquery-ui.multidatespicker.js', array('jquery'), 1, true);
    wp_enqueue_script('multidatepicker-wbbm');
    wp_enqueue_script('wbbm-clocklet-lib',plugin_dir_url( __DIR__ ).'js/clocklet.js',array('jquery','jquery-ui-core'),1,true);
    wp_enqueue_script('gmap-scripts',plugin_dir_url( __DIR__ ).'js/mkb-admin.js',array('jquery','jquery-ui-core'),1,true);
    wp_enqueue_script('wbbm-single-datatabs',plugin_dir_url( __DIR__ ).'js/wbbm-single-datatabs.js',array('jquery'),time(),true);
    wp_enqueue_script('mp_script',plugin_dir_url( __DIR__ ).'js/mp_script.js',array('jquery'),time(),true);
    wp_enqueue_script('wbbm_custom_admin_script',plugin_dir_url( __DIR__ ).'js/wbbm_custom_admin_script.js',array('jquery'),time(),true);
    wp_enqueue_style('mep-ra-admin-style',plugin_dir_url( __DIR__ ).'css/wbbm-custom-style.css',array());
    wp_enqueue_style('mp-style',plugin_dir_url( __DIR__ ).'css/mp_style.css',array());
}



function wbbm_add_admin_scripts( $hook ) {
    global $post;
    if ( $hook == 'post-new.php' || $hook == 'post.php' ) {
        if ( 'wbbm_bus' === $post->post_type ) { 
             wp_enqueue_style('mep-jquery-ui-style',plugin_dir_url( __DIR__ ).'css/jquery-ui.css',array());
        
        }
    }
}

function displayDates($date1, $date2, $format = 'd-m-Y' ) {
    $dates = array();
    $current = strtotime($date1);
    $date2 = strtotime($date2);
    $stepVal = '+1 day';
    while( $current <= $date2 ) {
        $dates[] = date($format, $current);
        $current = strtotime($stepVal, $current);
    }
    return  $dates;
}



add_action( 'admin_enqueue_scripts', 'wbbm_add_admin_scripts', 10, 1 );




// Datepicker code for admin dashboard load in footer section
add_action('admin_footer','wbbm_admin_footer_script',10,99);
add_action('wp_footer','wbbm_admin_footer_script',10,99);
function wbbm_admin_footer_script(){

    global $post;
    ob_start();
    ?>

    <script type="text/javascript">

        jQuery(document).ready(function($){


            <?php
            if (is_single()) {

            $wbtm_bus_on_dates = get_post_meta($post->ID, 'wbtm_bus_on_date', true) ? maybe_unserialize(get_post_meta($post->ID, 'wbtm_bus_on_date', true)) : [];
            $wbtm_offday_schedules = get_post_meta($post->ID, 'wbtm_offday_schedule', true)?get_post_meta($post->ID, 'wbtm_offday_schedule', true):[];


            if ($wbtm_bus_on_dates) {
                $wbtm_bus_on_dates_arr = explode(',',$wbtm_bus_on_dates);
                $onday = array();
                foreach ($wbtm_bus_on_dates_arr as $ondate) {
                    $onday[] = '"' . date('d-m-Y', strtotime($ondate)) . '"';
                }
                $on_particular_date = implode(',', $onday);
                echo 'var enableDates = [' . $on_particular_date . '];';
            ?>


            function enableAllTheseDays(date) {
                var sdate = jQuery.datepicker.formatDate('dd-mm-yy', date)
                if (enableDates.length > 0) {
                    if (jQuery.inArray(sdate, enableDates) != -1) {
                        return [true];
                    }
                }
                return [false];
            }

            jQuery('#j_date').datepicker({
                dateFormat: '<?php echo wbbm_convert_datepicker_dateformat(); ?>',
                minDate: 0,
                beforeShowDay: enableAllTheseDays
            });

            <?php } elseif($wbtm_offday_schedules) {



            $alloffdays = array();
            foreach ($wbtm_offday_schedules as $wbtm_offday_schedule){
                $alloffdays =  array_unique( array_merge( $alloffdays ,displayDates($wbtm_offday_schedule['from_date'], $wbtm_offday_schedule['to_date'])) ); ;
            }

            $offday = array();
            foreach ($alloffdays as $alloffday) {
                $offday[] = '"' . date('d-m-Y', strtotime($alloffday)) . '"';
            }
            $off_particular_date = implode(',', $offday);

            echo 'var off_particular_date = [' . $off_particular_date . '];';

            $weekly_offday = get_post_meta(get_the_id(), 'weekly_offday', true) ? get_post_meta(get_the_id(), 'weekly_offday', true) : [];;

            $weekly_offday = implode(', ', $weekly_offday);

            echo 'var weekly_offday = [' . $weekly_offday . '];';



            ?>


            function off_particular(date) {
                var sdate = jQuery.datepicker.formatDate('dd-mm-yy', date)
                if (off_particular_date.length > 0) {
                    if (jQuery.inArray(sdate, off_particular_date) != -1) {
                        return [false];
                    }
                }


                if (weekly_offday.length > 0) {
                    if (weekly_offday.includes(date.getDay())) {
                        return [false];
                    }
                }
                return [true];
            }

            jQuery("#j_date").datepicker({
                dateFormat: "<?php echo wbbm_convert_datepicker_dateformat(); ?>",
                minDate: 0,
                beforeShowDay: off_particular
            });


            <?php }  else{


            $weekly_offday = get_post_meta(get_the_id(), 'weekly_offday', true)?get_post_meta(get_the_id(), 'weekly_offday', true):[];

            $weekly_offday = implode(', ', $weekly_offday);

            echo 'var weekly_offday = [' . $weekly_offday . '];';
            ?>

            function weekly_offday_d(date) {
                if (weekly_offday.length > 0) {
                    if (weekly_offday.includes(date.getDay())) {
                        return [false];
                    }
                }
                return [true];

            }

            jQuery("#j_date").datepicker({
                dateFormat: "<?php echo wbbm_convert_datepicker_dateformat(); ?>",
                minDate: 0,
                beforeShowDay: weekly_offday_d
            });

            <?php

            /////
            } } else {

            ?>



            jQuery( "#j_date" ).datepicker({
                dateFormat: "<?php echo wbbm_convert_datepicker_dateformat(); ?>",
                minDate:0
            });



            <?php } ?>

            jQuery( "#r_date" ).datepicker({
                dateFormat: "<?php echo wbbm_convert_datepicker_dateformat(); ?>",
                minDate:0
            });
            jQuery( "#ja_date" ).datepicker({
                dateFormat: "yy-mm-dd"
            });
        });

    </script>

    <?php }





// Select2 code for admin dashboard load in footer section
add_action('wp_footer','wbbm_admin_footer_select_2_script',10,99);
function wbbm_admin_footer_select_2_script(){
  ?>
<script type="text/javascript">
jQuery(document).ready(function($){
      jQuery(".select2, #boarding_point, #drp_point").select2();
    });
</script>
  <?php
}







// Enqueue Scripts for frontend
add_action('wp_enqueue_scripts', 'wbbm_bus_enqueue_scripts');
function wbbm_bus_enqueue_scripts() {
   wp_enqueue_script('jquery');
   wp_enqueue_script('jquery-ui-datepicker');
   wp_enqueue_script('jquery-ui-core');   
   wp_enqueue_script('jquery-ui-accordion');
   wp_enqueue_style('wbbm-jquery-ui-style',plugin_dir_url( __DIR__ ).'css/jquery-ui.css',array());
    wp_enqueue_script('wbbm-select2-lib',plugin_dir_url( __DIR__ ).'js/select2.full.min.js',array('jquery','jquery-ui-core'),1,false);

    wp_enqueue_script('ra_script_public',plugin_dir_url( __DIR__ ).'js/wbbm_custom_public_script.js',array(),time(),false);

    wp_enqueue_style('wbbm-bus-style',plugin_dir_url( __DIR__ ).'css/style.css',array());

    wp_enqueue_style('wbbm-ra-bus-style',plugin_dir_url( __DIR__ ).'css/wbbm-custom-style.css',array());


   wp_enqueue_style ('font-awesome-css-cdn',"https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.4.0/css/font-awesome.min.css",null,1);

   wp_enqueue_style ('wbbm-select2-style-cdn',"https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/css/select2.min.css",null,1);

    wp_enqueue_script('mage_style',plugin_dir_url( __DIR__ ).'js/mage_style.js',array('jquery'),time(),true);

    wp_enqueue_style('mage_css',plugin_dir_url( __DIR__ ).'css/mage_css.css',array(), time());


}

// Ajax Issue
add_action('wp_head','wbbm_ajax_url',5);
add_action('admin_head','wbbm_ajax_url',5);
function wbbm_ajax_url() {
    ?>
    <script type="text/javascript">
        var wbtm_ajaxurl = "<?php echo admin_url('admin-ajax.php'); ?>";
    </script>
    <?php
}