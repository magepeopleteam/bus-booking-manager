<?php
function mage_search_list(){
    ?>
    <div class="mage_route_title">
        <p>
            <strong><?php echo wbbm_get_option('wbbm_route_text', 'wbbm_label_setting_sec',__('Route', 'bus-booking-manager')); ?></strong>
            <?php echo mage_get_isset('bus_start_route'); ?>
            <span class="fa fa-long-arrow-right"></span>
            <?php echo mage_get_isset('bus_end_route'); ?>
            <strong><?php echo wbbm_get_option('wbbm_date_text', 'wbbm_label_setting_sec',__('Date', 'bus-booking-manager'));; ?></strong>
            <?php echo get_wbbm_datetime(mage_get_isset('j_date'),'date-text'); ?>
        </p>
    </div>
    <?php mage_search_bus_list(false); ?>
    <?php if (isset($_GET['r_date']) && $_GET['r_date']) { ?>
        <div class="mage_route_title return_title">
            <p>
                <strong><?php echo wbbm_get_option('wbbm_route_text', 'wbbm_label_setting_sec',__('Route', 'bus-booking-manager')); ?></strong>
                <?php echo mage_get_isset('bus_end_route'); ?>
                <span class="fa fa-long-arrow-right"></span>
                <?php echo mage_get_isset('bus_start_route'); ?>
                <strong><?php echo wbbm_get_option('wbbm_date_text', 'wbbm_label_setting_sec',__('Date', 'bus-booking-manager')); ?></strong>
                <?php echo get_wbbm_datetime(mage_get_isset('r_date'),'date-text'); ?>               
            </p>
        </div>
        <?php mage_search_bus_list(true); ?>
        <div class="mage_mini_cart mage_hidden">
            <p><?php echo wbbm_get_option('wbbm_total_text', 'wbbm_label_setting_sec',__('Total', 'bus-booking-manager')); ?></p>
            <p class="mage_total"><strong><span><?php echo wc_price(0); ?></span></strong></p>
        </div>
    <?php } 
}
