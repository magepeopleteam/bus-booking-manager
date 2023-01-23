<?php
function mage_search_list(){
    global $mage_bus_search_theme;
    $cpt_label = wbbm_get_option( 'wbbm_cpt_label', 'wbbm_general_setting_sec', 'Bus');
    $route_title_bg_color = wbbm_get_option('wbbm_search_route_title_b_color', 'wbbm_style_setting_sec');
    $route_title_bg_color = $route_title_bg_color ? $route_title_bg_color : '#727272';
    $route_title_color = wbbm_get_option('wbbm_search_route_title_color', 'wbbm_style_setting_sec');
    $route_title_color = $route_title_color ? $route_title_color : '#fff';
    $search_list_header_b_color = wbbm_get_option('wbbm_search_list_header_b_color', 'wbbm_style_setting_sec');
    $general_setting = get_option('wbbm_general_setting_sec') ? maybe_unserialize(get_option('wbbm_general_setting_sec')) : array(); 
    ?>
    <div class="mage_route_title" style="background-color:<?php echo $route_title_bg_color; ?>;color:<?php echo $route_title_color; ?>">
        <div>
            <strong><?php echo wbbm_get_option('wbbm_route_text', 'wbbm_label_setting_sec',__('Route', 'bus-booking-manager')); echo ':'; ?></strong>
            <?php echo mage_get_isset('bus_start_route'); ?>
            <span class="fa fa-long-arrow-right"></span>
            <?php echo mage_get_isset('bus_end_route'); ?>
            <strong><?php echo ' | '; echo wbbm_get_option('wbbm_date_text', 'wbbm_label_setting_sec',__('Date', 'bus-booking-manager')); echo ':'; ?></strong>
            <?php echo mage_wp_date(mage_get_isset('j_date')); ?>
        </div>
    </div>
    <div class="mage-search-res-wrapper">
        <?php do_action( 'woocommerce_before_single_product' ); ?>
        <?php if ( $mage_bus_search_theme == 'minimal' ) { ?>
        <div class="mage-search-res-header" style="background-color:<?php echo ($search_list_header_b_color != '' ? $search_list_header_b_color : '#EA2330'); ?>">
            <div class="mage-search-res-header--img">
                <span><?php echo wbbm_get_option('wbbm_bus_image_text', 'wbbm_label_setting_sec', __('Bus Image', 'bus-booking-manager')); ?></span>
            </div>
            <div class="mage-search-res-header--left">
                <span><?php echo wbbm_get_option('wbbm_bus_name_text', 'wbbm_label_setting_sec', __('Bus Name', 'bus-booking-manager')); ?></span>
                <span><?php echo wbbm_get_option('wbbm_schedule_text', 'wbbm_label_setting_sec', __('Schedule', 'bus-booking-manager')); ?></span>
            </div>
            <div class="mage-search-res-header--right">
                <?php if(isset($general_setting['wbbm_type_column_switch']) && $general_setting['wbbm_type_column_switch'] == 'on'){ ?>
                    <span><?php echo wbbm_get_option('wbbm_type_text', 'wbbm_label_setting_sec', __('Type', 'bus-booking-manager')); ?></span>
                <?php  } ?> 
                <span><?php echo wbbm_get_option('wbbm_fare_text', 'wbbm_label_setting_sec', __('Fare', 'bus-booking-manager')); ?></span>

                <?php if(isset($general_setting['wbbm_seat_column_switch']) && $general_setting['wbbm_seat_column_switch'] == 'on'){ ?>
                <span><?php echo wbbm_get_option('wbbm_seats_available_text', 'wbbm_label_setting_sec', __('Seat Available', 'bus-booking-manager')); ?></span>
                <?php  } ?>
                
                <span><?php echo wbbm_get_option('wbbm_view_text', 'wbbm_label_setting_sec', __('View', 'bus-booking-manager')); ?></span>
            </div>
        </div>
        <?php } ?>
        <?php mage_search_bus_list(false); ?>
        <!-- <div class="mage-search-res-wrapper--footer"></div> -->
    </div>
    <?php //mage_search_bus_list(false); ?>
    <?php if (isset($_GET['r_date']) && $_GET['r_date'] != '') { ?>
        <div class="mage_route_title return_title" style="background-color:<?php echo $route_title_bg_color; ?>">
            <div>
                <strong><?php echo wbbm_get_option('wbbm_route_text', 'wbbm_label_setting_sec',__('Route', 'bus-booking-manager')); echo ':'; ?></strong>
                <?php echo mage_get_isset('bus_end_route'); ?>
                <span class="fa fa-long-arrow-right"></span>
                <?php echo mage_get_isset('bus_start_route'); ?>
                <strong><?php echo ' | '; echo wbbm_get_option('wbbm_date_text', 'wbbm_label_setting_sec',__('Date', 'bus-booking-manager')); echo ':'; ?></strong>
                <?php echo mage_wp_date(mage_get_isset('r_date')); ?>
            </div>
        </div>
        <div class="mage-search-res-wrapper">
        <?php if ( $mage_bus_search_theme == 'minimal' ) { ?>
            <div class="mage-search-res-header">
                <div class="mage-search-res-header--img">
                    <span><?php echo wbbm_get_option('wbbm_bus_image_text', 'wbbm_label_setting_sec', __('Bus Image', 'bus-booking-manager')); ?></span>
                </div>
                <div class="mage-search-res-header--left">
                    <span><?php echo wbbm_get_option('wbbm_bus_name_text', 'wbbm_label_setting_sec', __('Bus Name', 'bus-booking-manager')); ?></span>
                    <span><?php echo wbbm_get_option('wbbm_schedule_text', 'wbbm_label_setting_sec', __('Schedule', 'bus-booking-manager')); ?></span>
                </div>
                <div class="mage-search-res-header--right">
                    <?php if(isset($general_setting['wbbm_type_column_switch']) && $general_setting['wbbm_type_column_switch'] == 'on'){ ?>
                        <span><?php echo wbbm_get_option('wbbm_type_text', 'wbbm_label_setting_sec', __('Type', 'bus-booking-manager')); ?></span>
                    <?php  } ?>

                    <span><?php echo wbbm_get_option('wbbm_fare_text', 'wbbm_label_setting_sec', __('Fare', 'bus-booking-manager')); ?></span>

                    <?php if(isset($general_setting['wbbm_seat_column_switch']) && $general_setting['wbbm_seat_column_switch'] == 'on'){ ?>
                        <span><?php echo wbbm_get_option('wbbm_seats_available_text', 'wbbm_label_setting_sec', __('Seat Available', 'bus-booking-manager')); ?></span>
                    <?php  } ?>

                    <span><?php echo wbbm_get_option('wbbm_view_text', 'wbbm_label_setting_sec', __('View', 'bus-booking-manager')); ?></span>
                </div>
            </div>
            <?php } ?>
            <?php mage_search_bus_list(true); ?>
        </div>
        <div class="mage_mini_cart mage_hidden">
            <p><?php echo wbbm_get_option('wbbm_total_text', 'wbbm_label_setting_sec',__('Total', 'bus-booking-manager')); ?></p>
            <p class="mage_total"><strong><span><?php echo wc_price(0); ?></span></strong></p>
        </div>
    <?php } 
}