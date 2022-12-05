<?php
if (!defined('ABSPATH')) exit;  // if direct access

class WBBMMetaBox{
    public function __construct()
    {
        // WBBM Metabox
        add_action('add_meta_boxes', array($this, 'wbbm_add_meta_box_func')); 
        
        // Tab lists
        add_action('wbbm_meta_box_tab_label', array($this, 'wbbm_add_meta_box_tab_label'), 20);
        
        // Tab Contents
        add_action('wbbm_meta_box_tab_content', array($this, 'wbbm_add_meta_box_tab_content'), 10);
        
        //Save meta
        add_action('save_post', array($this, 'wbbm_single_settings_meta_save'));

        //Remove meta box from sidebar
        add_action('admin_init', array($this, 'wbbm_remove_sidebar_meta_box'));
    }

    public function wbbm_add_meta_box_func()
    {
        $cpt_label = wbbm_get_option('wbbm_cpt_label', 'wbbm_general_setting_sec', __('Bus', 'bus-booking-manager'));
        add_meta_box('wbbm-single-settings-meta', $cpt_label.' '. __('Settings','bus-booking-manager'), array($this, 'wbbm_meta_box_cb'), 'wbbm_bus', 'normal', 'high');
    }

    public function wbbm_meta_box_cb()
    {
        $post_id = get_the_id();
        ?>
<div class="mp_event_all_meta_in_tab mp_event_tab_area">
    <div class="mp_tab_menu">
        <ul>
            <?php do_action('wbbm_meta_box_tab_label', $post_id); ?>
        </ul>
    </div>
    <div class="mp_tab_details">
        <?php do_action('wbbm_meta_box_tab_content', $post_id); ?>
    </div>
</div>
<?php
    }

    // Tab lists
    public function wbbm_add_meta_box_tab_label($post_id)
    {
        $cpt_label = wbbm_get_option('wbbm_cpt_label', 'wbbm_general_setting_sec', __('Bus', 'bus-booking-manager'));
        ?>
        <li data-target-tabs="#wbtm_ticket_panel" class="active"><span
                class="dashicons dashicons-admin-settings"></span><?php echo $cpt_label.' '. __('Configuration','bus-booking-manager'); ?>
        </li>

        <li data-target-tabs="#wbtm_routing"><span
                class="dashicons dashicons-location-alt"></span><?php echo $cpt_label.' '.__('Routing', 'bus-booking-manager'); ?>
        </li>

        <li data-target-tabs="#wbtm_seat_price"><span
                class="dashicons dashicons-money-alt"></span><?php echo $cpt_label.' '.__('Seat Price', 'bus-booking-manager'); ?>
        </li>

        <li data-target-tabs="#wbtm_pickuppoint"><span
                class="dashicons dashicons-flag"></span><?php echo $cpt_label.' '.__('Pickup Point', 'bus-booking-manager'); ?>
        </li>

        <li data-target-tabs="#wbtm_bus_off_on_date"><span
                class="dashicons dashicons-calendar-alt"></span><?php echo $cpt_label.' '.__('Onday & Offday', 'bus-booking-manager'); ?>
        </li>

        <?php if (is_plugin_active('mage-partial-payment-pro/mage_partial_pro.php')) : ?>
        <li data-target-tabs="#wbtm_bus_partial_payment"><span
                class="dashicons dashicons-calendar-alt"></span><?php echo $cpt_label.' '.__('Partial payment', 'bus-booking-manager'); ?>
        </li>
        <?php endif; ?>

        <?php
        /*Hook:  wbbm_after_meta_box_tab_label */
        do_action('wbbm_after_meta_box_tab_label'); 
        ?>
        <?php
    }
    
    public function wbbm_add_meta_box_tab_content($post_id){
        
        $cpt_label = wbbm_get_option('wbbm_cpt_label', 'wbbm_general_setting_sec', __('Bus', 'bus-booking-manager'));
        wp_nonce_field('wbbm_single_bus_settings_nonce', 'wbbm_single_bus_settings_nonce');
        ?>
        <!-- start configuration tab content -->
        <div class="mp_tab_item" data-tab-item="#wbtm_ticket_panel" style="display:block;">
            <h3><?php echo $cpt_label.' '. __('Configuration:', 'bus-booking-manager'); ?></h3>
            <hr />
            <?php $this->wbbm_bus_configuration(); ?>
        </div>

        <!-- start routing tab content -->
        <div class="mp_tab_item" data-tab-item="#wbtm_routing">
            <h3><?php echo $cpt_label.' '. __('Routing:', 'bus-booking-manager'); ?></h3>
            <hr />
            <?php $this->wbbm_bus_routing(); ?>
        </div>

        <!-- start pricing tab content -->
        <div class="mp_tab_item" data-tab-item="#wbtm_seat_price">
            <h3><?php echo $cpt_label.' '. __('Seat Price:', 'bus-booking-manager'); ?></h3>
            <hr />
            <?php $this->wbbm_bus_pricing(); ?>
            <?php $this->wbbm_extra_price_option($post_id); ?>
        </div>
        <!-- start pickuppoint tab content -->
        <div class="mp_tab_item" data-tab-item="#wbtm_pickuppoint">
            <h3><?php echo $cpt_label.' '. __('Pickup Point:', 'bus-booking-manager'); ?></h3>
            <hr />
            <?php $this->wbbm_bus_pickuppoint(); ?>
        </div>
        <!-- start offday-onday tab content -->
        <div class="mp_tab_item" data-tab-item="#wbtm_bus_off_on_date">
            <h3><?php echo $cpt_label.' '. __('Onday and Offday:', 'bus-booking-manager'); ?></h3>
            <hr />
            <?php $this->wbbm_bus_ondayoffday(); ?>
        </div>

        <!-- Partial Payment Setting -->
        <div class="mp_tab_item tab-content" data-tab-item="#wbtm_bus_partial_payment">
            <h3><?php echo $cpt_label.' '. __('Partial Payment:', 'bus-booking-manager'); ?></h3>
            <hr />
            <?php $this->wbbm_partial_payment_setting(); ?>
        </div>

        <?php
        /*Hook:  wbbm_after_meta_box_tab_content */
        do_action('wbbm_after_meta_box_tab_content'); 
        ?>
        <?php
    }

    public function wbbm_partial_payment_setting(){
        global $post;
        $values = get_post_custom($post->ID);
        do_action('wcpp_partial_product_settings', $values);
    }

    public function wbbm_bus_configuration(){
        global $post;
        $values = get_post_custom($post->ID);
        $bus_ticket_type = get_post_meta($post->ID, 'wbbm_bus_ticket_type_info', true);
        $cpt_label = wbbm_get_option('wbbm_cpt_label', 'wbbm_general_setting_sec', __('Bus', 'bus-booking-manager'));
        $wbbm_bus_category = get_post_meta($post->ID, 'wbbm_bus_category', true);
        $bus_categories = wbbm_get_bus_categories();
        ?>
<div class="configuration_wrapper">
    <div class='sec'>
        <label for="wbbm_bus_category" class="ra-item-label">
            <?php  esc_html_e('Type', 'bus-booking-manager'); ?>
        </label>
            <span>
            <?php
            if(isset($bus_categories) && !empty($bus_categories)){
                echo '<select name="wbbm_bus_category" id="wbbm_bus_category">';
                echo '<option value="">'.__('Select Type','bus-booking-manager').'</option>'; 
                foreach ($bus_categories as $key => $value) {
                if($wbbm_bus_category == $key){
                    echo '<option value="'.esc_attr($key).'" selected>'.esc_html($value).'</option>'; 
                } else {
                    echo '<option value="'.esc_attr($key).'">'.esc_html($value).'</option>';  
                }  
                }
                echo '</select>';
            }
            ?>
            </span>

    </div>    
    <div class='sec'>
        <label for="wbbm_ev_98" class="ra-item-label">
            <?php _e('Coach No', 'bus-booking-manager'); ?>
        </label>
           <input id='wbbm_ev_98' type="text" name='wbbm_bus_no' value='<?php if (array_key_exists('wbbm_bus_no', $values)) {
                                        echo $values['wbbm_bus_no'][0];
                                    } ?>' />
    </div>

    <div class='sec'>
        <label for="wbbm_ev_99" class="ra-item-label">
            <?php _e('Total Seat', 'bus-booking-manager'); ?>
        </label>
        <input id='wbbm_ev_99' type="text" name='wbbm_total_seat' value='<?php if (array_key_exists('wbbm_total_seat', $values)) {
                                        echo $values['wbbm_total_seat'][0];
                                    } ?>' />
        </span>
    </div>

    <div class='sec'>
        <label for="wbbm_price_zero_allow" class="ra-item-label">
            <?php echo wbbm_get_option('wbbm_bus_price_zero_allow_text', 'wbbm_label_setting_sec') ? wbbm_get_option('wbbm_bus_price_zero_allow_text', 'wbbm_label_setting_sec') : __('Bus Price Zero Allow','bus-booking-manager'); ?>
        </label>
            <label class="switch">
                <input type="checkbox" id="wbbm_price_zero_allow" name='wbbm_price_zero_allow' <?php if (array_key_exists('wbbm_price_zero_allow', $values)) {
                                if ($values['wbbm_price_zero_allow'][0] == 'on') {
                                    echo 'checked';
                                }
                            } else {
                                echo '';
                            } ?> />
                <span class="slider round"></span>

        </label>
    </div>
    <div class='sec'>
        <label for="wbbm_sell_off" class="ra-item-label">
            <?php echo wbbm_get_option('wbbm_bus_sell_off_text', 'wbbm_label_setting_sec') ? wbbm_get_option('wbbm_bus_sell_off_text', 'wbbm_label_setting_sec') : __('Bus Sell Off','bus-booking-manager'); ?>
        </label>
            <label class="switch">
                <input type="checkbox" id="wbbm_sell_off" name='wbbm_sell_off' <?php if (array_key_exists('wbbm_sell_off', $values)) {
                                if ($values['wbbm_sell_off'][0] == 'on') {
                                    echo 'checked';
                                }
                            } else {
                                echo '';
                            } ?> />
                <span class="slider round"></span>
            </label>
    </div>
    <div class='sec'>
        <label for="wbbm_seat_available" class="ra-item-label">
            <?php echo wbbm_get_option('wbbm_bus_seat_available_show_text', 'wbbm_label_setting_sec') ? wbbm_get_option('wbbm_bus_seat_available_show_text', 'wbbm_label_setting_sec') : __('Bus Seat Available Show','bus-booking-manager'); ?>
        </label>
            <label class="switch">
                <input type="checkbox" id="wbbm_seat_available" name='wbbm_seat_available' <?php if (array_key_exists('wbbm_seat_available', $values)) {
                                    if ($values['wbbm_seat_available'][0] == 'on') {
                                        echo 'checked';
                                    }
                                } else {
                                    echo 'checked';
                                } ?> />
                <span class="slider round"></span>
            </label>
        </label>
    </div>
</div>
<script type="text/javascript">
jQuery(document).ready(function($) {
    $('#add-row-t').on('click', function() {
        var row = $('.empty-row-t.screen-reader-text').clone(true);
        row.removeClass('empty-row-t screen-reader-text');
        row.insertBefore('#repeatable-fieldset-one-t tbody>tr:last');
        return false;
    });

    $('.remove-row-t').on('click', function() {
        $(this).parents('tr').remove();
        return false;
    });
});
</script>

<?php

                if ($bus_ticket_type) :

                    foreach ($bus_ticket_type as $field) {
                        $qty_t_type = esc_attr($field['ticket_type_qty_t_type']);
                        ?>
<tr>
    <td><input type="text" class="widefat" name="ticket_type_name[]"
            value="<?php if ($field['ticket_type_name'] != '') echo esc_attr($field['ticket_type_name']); ?>" />
    </td>

    <td><input type="number" class="widefat" name="ticket_type_price[]"
            value="<?php if ($field['ticket_type_price'] != '') echo esc_attr($field['ticket_type_price']); else echo ''; ?>" />
    </td>

    <td><input type="number" class="widefat" name="ticket_type_qty[]"
            value="<?php if ($field['ticket_type_qty'] != '') echo esc_attr($field['ticket_type_qty']); else echo ''; ?>" />
    </td>

    <td><select name="ticket_type_qty_t_type[]" id="mep_ev_9800kj8" class=''>
            <option value="inputbox" <?php if ($qty_t_type == 'inputbox') {
                                        echo "Selected";
                                    } ?>><?php _e('Input Box', 'bus-booking-manager'); ?></option>
            <option value="dropdown" <?php if ($qty_t_type == 'dropdown') {
                                        echo "Selected";
                                    } ?>><?php _e('Dropdown List', 'bus-booking-manager'); ?></option>
        </select></td>

    <td><a class="button remove-row-t" href="#" <?php _e('Remove', 'bus-booking-manager'); ?></a></td>
</tr>
<?php
                    }
                else :
                    // show a blank one
                endif;
    }

    public function wbbm_bus_routing(){
        global $post;
        $wbbm_event_faq = get_post_meta($post->ID, 'wbbm_bus_next_stops', true);
        $wbbm_bus_bp = get_post_meta($post->ID, 'wbbm_bus_bp_stops', true);
        $values = get_post_custom($post->ID);

        $get_terms_default_attributes = array(
            'taxonomy' => 'wbbm_bus_stops',
            'hide_empty' => false
        );
        $terms = get_terms($get_terms_default_attributes);
        if ($terms) {
            ?>
<div class="bus-stops-wrapper">
    <div class="bus-stops-left-col">
        <h3 class="bus-tops-sec-title"><?php _e('Boarding Point', 'bus-booking-manager'); ?></h3>
        <table id="repeatable-fieldset-bp-one" width="100%">
            <thead>
                <tr>
                    <th><?php _e('Boarding Point', 'bus-booking-manager'); ?></th>
                    <th><?php _e('Time', 'bus-booking-manager'); ?></th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php
                                if ($wbbm_bus_bp) :
                                    $count = 0;
                                    foreach ($wbbm_bus_bp as $field) {
                                        ?>
                <tr>
                    <td align="center">
                        <?php echo wbbm_get_next_bus_stops_list('wbbm_bus_bp_stops_name[]', 'wbbm_bus_bp_stops_name', 'wbbm_bus_bp_stops', $count); ?>
                    </td>
                    <td align="center"><input type="text" data-clocklet name='wbbm_bus_bp_start_time[]'
                            value="<?php if (isset($field['wbbm_bus_bp_start_time']) && $field['wbbm_bus_bp_start_time'] != '') echo esc_attr($field['wbbm_bus_bp_start_time']); ?>"
                            class="text" placeholder="15:00"></td>
                    <td align="center"><a class="button remove-faq-row" href="#"><i class="fas fa-minus-circle"></i>
                            <?php _e('Remove', 'bus-booking-manager'); ?>
                        </a></td>
                </tr>
                <?php
                                        $count++;
                                    }
                                else :
                                    // show a blank one
                                endif;
                                ?>

                <!-- empty hidden one for jQuery -->
                <tr class="empty-row-bp screen-reader-text">
                    <td align="center"><?php echo wbbm_get_bus_stops_list('wbbm_bus_bp_stops_name[]'); ?></td>
                    <td align="center"><input type="text" data-clocklet name='wbbm_bus_bp_start_time[]' value=""
                            class="text" placeholder="15:00"></td>
                    <td align="center"><a class="button remove-bp-row" href="#"><i class="fas fa-minus-circle"></i>
                            <?php _e('Remove', 'bus-booking-manager'); ?>
                        </a></td>
                </tr>
            </tbody>
        </table>
        <p><a id="add-bp-row" class="button" href="#"><i class="fas fa-plus"></i>
                <?php _e('Add More Boarding Point', 'bus-booking-manager'); ?>
            </a></p>
    </div>

    <div class="bus-stops-right-col">
        <h3 class="bus-tops-sec-title"><?php _e('Dropping Point', 'bus-booking-manager'); ?></h3>
        <table id="repeatable-fieldset-faq-one" width="100%">
            <thead>
                <tr>
                    <th><?php _e('Dropping Point', 'bus-booking-manager'); ?></th>
                    <th><?php _e('Time', 'bus-booking-manager'); ?></th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php
                                if ($wbbm_event_faq) :
                                    $coun = 0;
                                    foreach ($wbbm_event_faq as $field) {
                                        ?>
                <tr>
                    <td align="center">
                        <?php echo wbbm_get_next_bus_stops_list('wbbm_bus_next_stops_name[]', 'wbbm_bus_next_stops_name', 'wbbm_bus_next_stops', $coun); ?>
                    </td>
                    <td align="center"><input type="text" data-clocklet name='wbbm_bus_next_end_time[]'
                            value="<?php if (isset($field['wbbm_bus_next_end_time']) && $field['wbbm_bus_next_end_time'] != '') echo esc_attr($field['wbbm_bus_next_end_time']); ?>"
                            class="text" placeholder="15:00"></td>
                    <td align="center"><a class="button remove-faq-row" href="#"><i class="fas fa-minus-circle"></i>
                            <?php _e('Remove', 'bus-booking-manager'); ?>
                        </a></td>
                </tr>
                <?php
                                        $coun++;
                                    }
                                else :
                                    // show a blank one
                                endif;
                                ?>

                <!-- empty hidden one for jQuery -->
                <tr class="empty-row-faq screen-reader-text">
                    <td align="center"><?php echo wbbm_get_bus_stops_list('wbbm_bus_next_stops_name[]'); ?></td>
                    <td align="center"><input type="text" data-clocklet name='wbbm_bus_next_end_time[]' value=""
                            class="text" placeholder="15:00"></td>
                    <td align="center"><a class="button remove-faq-row" href="#"><i class="fas fa-minus-circle"></i>
                            <?php _e('Remove', 'bus-booking-manager'); ?>
                        </a></td>
                </tr>
            </tbody>
        </table>
        <p><a id="add-faq-row" class="button" href="#"><i class="fas fa-plus"></i>
                <?php _e('Add More Droping Point', 'bus-booking-manager'); ?>
            </a></p>
    </div>
</div>
<script type="text/javascript">
jQuery(document).ready(function($) {
    $('#add-faq-row').on('click', function() {
        var row = $('.empty-row-faq.screen-reader-text').clone(true);
        row.removeClass('empty-row-faq screen-reader-text');
        row.insertBefore('#repeatable-fieldset-faq-one tbody>tr:last');
        return false;
    });

    $('.remove-faq-row').on('click', function() {
        $(this).parents('tr').remove();
        return false;
    });

    $('#add-bp-row').on('click', function() {
        var row = $('.empty-row-bp.screen-reader-text').clone(true);
        row.removeClass('empty-row-bp screen-reader-text');
        row.insertBefore('#repeatable-fieldset-bp-one tbody>tr:last');
        return false;
    });

    $('.remove-bp-row').on('click', function() {
        $(this).parents('tr').remove();
        return false;
    });
});
</script>
<?php
                } else {
                    echo "<div class='wbbm_bus_stops_notice'>".__('Please Enter some bus stops first.', 'bus-booking-manager')." <a class='wbbm_color_white' href='" . get_admin_url() . "edit-tags.php?taxonomy=wbbm_bus_stops&post_type=wbbm_bus'>".__('Click here for bus stops', 'bus-booking-manager')."</a></div>";
                }
    }

    public function wbbm_bus_pricing(){
        global $post;
        $entire_bus_booking = wbbm_get_option('wbbm_entire_bus_booking_switch', 'wbbm_general_setting_sec');
        $wbbm_bus_prices = get_post_meta($post->ID, 'wbbm_bus_prices', true);
        $values = get_post_custom($post->ID);
        $get_terms_default_attributes = array(
            'taxonomy' => 'wbbm_bus_stops',
            'hide_empty' => false
        );
        $terms = get_terms($get_terms_default_attributes);
        if ($terms) {
            ?>

<div style="width:100%;overflow-x:auto;">
    <table id="repeatable-fieldset-price-one" width="auto">
        <tr>
            <th><?php _e('Boarding Point', 'bus-booking-manager'); ?></th>
            <th><?php _e('Dropping Point', 'bus-booking-manager'); ?></th>
            <th><?php _e(wbbm_get_option('wbbm_adult_text', 'wbbm_label_setting_sec') ? wbbm_get_option('wbbm_adult_text', 'wbbm_label_setting_sec') : __('Adult', 'bus-booking-manager') . ' Fare', 'bus-booking-manager'); ?>
            </th>
            <th><?php _e(wbbm_get_option('wbbm_child_text', 'wbbm_label_setting_sec') ? wbbm_get_option('wbbm_child_text', 'wbbm_label_setting_sec') : __('Child', 'bus-booking-manager') . ' Fare', 'bus-booking-manager'); ?>
            </th>
            <th><?php _e(wbbm_get_option('wbbm_infant_text', 'wbbm_label_setting_sec') ? wbbm_get_option('wbbm_infant_text', 'wbbm_label_setting_sec') : __('Infant', 'bus-booking-manager') . ' Fare', 'bus-booking-manager'); ?>
            </th>
            <?php if($entire_bus_booking == 'on'): ?>
            <th><?php _e(wbbm_get_option('wbbm_entire_bus_text', 'wbbm_label_setting_sec') ? wbbm_get_option('wbbm_entire_bus_text', 'wbbm_label_setting_sec') : __('Entire Bus', 'bus-booking-manager') . ' Fare', 'bus-booking-manager'); ?></th>
            <?php endif; ?>
            <th></th>
        </tr>
        <tbody>
            <?php
            if ($wbbm_bus_prices) :
                $coun = 0;
                foreach ($wbbm_bus_prices as $field) {
            ?>
            <tr>
                <td><?php echo wbbm_get_next_bus_stops_list('wbbm_bus_bp_price_stop[]', 'wbbm_bus_bp_price_stop', 'wbbm_bus_prices', $coun); ?>
                </td>

                <td><?php echo wbbm_get_next_bus_stops_list('wbbm_bus_dp_price_stop[]', 'wbbm_bus_dp_price_stop', 'wbbm_bus_prices', $coun); ?>
                </td>

                <td class="wbbm-price-col">
                    <input type="number" step="0.01" name='wbbm_bus_price[]'
                        value="<?php if (isset($field['wbbm_bus_price']) && $field['wbbm_bus_price'] != '') echo esc_attr($field['wbbm_bus_price']); ?>"
                        class="text">
                    <input type="number" step="0.01" name='wbbm_bus_price_roundtrip[]'
                        value="<?php if (isset($field['wbbm_bus_price_roundtrip']) && $field['wbbm_bus_price_roundtrip'] != '') echo esc_attr($field['wbbm_bus_price_roundtrip']); ?>"
                        class="text roundtrip-input"
                        placeholder="<?php echo __(wbbm_get_option('wbbm_adult_text', 'wbbm_label_setting_sec') ? wbbm_get_option('wbbm_adult_text', 'wbbm_label_setting_sec') . ' ' . __('return discount price', 'bus-booking-manager') : __('Adult', 'bus-booking-manager') . ' ' . __('return discount price', 'bus-booking-manager'), 'bus-booking-manager'); ?>">
                </td>

                <td class="wbbm-price-col">
                    <input type="number" step="0.01" name='wbbm_bus_price_child[]' value="<?php if (isset($field['wbbm_bus_price_child']) && $field['wbbm_bus_price_child'] != '') {
                                                    echo esc_attr($field['wbbm_bus_price_child']);
                                                } else {
                                                    echo 0;
                                                } ?>" class="text">
                    <input type="number" step="0.01" name='wbbm_bus_price_child_roundtrip[]' value="<?php if (isset($field['wbbm_bus_price_child_roundtrip']) && $field['wbbm_bus_price_child_roundtrip'] != '') {
                                                    echo esc_attr($field['wbbm_bus_price_child_roundtrip']);
                                                } else {
                                                    echo 0;
                                                } ?>" class="text roundtrip-input"
                        placeholder="<?php echo __(wbbm_get_option('wbbm_child_text', 'wbbm_label_setting_sec') ? wbbm_get_option('wbbm_child_text', 'wbbm_label_setting_sec') . ' ' . __('return discount price', 'bus-booking-manager') : __('Child', 'bus-booking-manager') . ' ' . __('return discount price', 'bus-booking-manager'), 'bus-booking-manager'); ?>">
                </td>

                <td class="wbbm-price-col">
                    <input type="number" step="0.01" name='wbbm_bus_price_infant[]' value="<?php if (isset($field['wbbm_bus_price_infant']) && $field['wbbm_bus_price_infant'] != '') {
                                                    echo esc_attr($field['wbbm_bus_price_infant']);
                                                } else {
                                                    echo 0;
                                                } ?>" class="text">
                    <input type="number" step="0.01" name='wbbm_bus_price_infant_roundtrip[]' value="<?php if (isset($field['wbbm_bus_price_infant_roundtrip']) && $field['wbbm_bus_price_infant_roundtrip'] != '') {
                                                    echo esc_attr($field['wbbm_bus_price_infant_roundtrip']);
                                                } else {
                                                    echo 0;
                                                } ?>" class="text roundtrip-input"
                        placeholder="<?php echo __(wbbm_get_option('wbbm_infant_text', 'wbbm_label_setting_sec') ? wbbm_get_option('wbbm_infant_text', 'wbbm_label_setting_sec') . ' ' . __('return discount price', 'bus-booking-manager') : __('Infant', 'bus-booking-manager') . ' ' . __('return discount price', 'bus-booking-manager'), 'bus-booking-manager'); ?>">
                </td>
                
                <?php if($entire_bus_booking == 'on'): ?>
                <td class="wbbm-price-col">
                    <input type="number" step="0.01" name='wbbm_bus_price_entire[]' value="<?php if (isset($field['wbbm_bus_price_entire']) && $field['wbbm_bus_price_entire'] != '') {
                                                    echo esc_attr($field['wbbm_bus_price_entire']);
                                                } else {
                                                    echo 0;
                                                } ?>" class="text">
                    <input type="number" step="0.01" name='wbbm_bus_price_entire_roundtrip[]' value="<?php if (isset($field['wbbm_bus_price_entire_roundtrip']) && $field['wbbm_bus_price_entire_roundtrip'] != '') {
                                                    echo esc_attr($field['wbbm_bus_price_entire_roundtrip']);
                                                } else {
                                                    echo 0;
                                                } ?>" class="text roundtrip-input"
                        placeholder="<?php echo __(wbbm_get_option('wbbm_entire_bus_text', 'wbbm_label_setting_sec') ? wbbm_get_option('wbbm_entire_bus_text', 'wbbm_label_setting_sec') . ' ' . __('return discount price', 'bus-booking-manager') : __('Entire Bus', 'bus-booking-manager') . ' ' . __('return discount price', 'bus-booking-manager'), 'bus-booking-manager'); ?>">
                </td>
                <?php endif; ?>

                <td><a class="button remove-price-row" href="#"><i class="fas fa-minus-circle"></i>
                        <?php _e('Remove', 'bus-booking-manager'); ?>
                    </a></td>
            </tr>
            <!-- Roundtrip price -->

            <!-- Roundtrip price -->
            <?php
                                    $coun++;
                                }
                            else :
                                // show a blank one
                            endif;
                            ?>

            <!-- empty hidden one for jQuery -->
            <tr class="empty-row-price screen-reader-text">
                <td><?php echo wbbm_get_bus_stops_list('wbbm_bus_bp_price_stop[]'); ?></td>
                <td><?php echo wbbm_get_bus_stops_list('wbbm_bus_dp_price_stop[]'); ?></td>
                <td class="wbbm-price-col">
                    <input step="0.01" type="number" name='wbbm_bus_price[]' value="" class="text">
                    <input step="0.01" type="number" name='wbbm_bus_price_roundtrip[]'
                        placeholder="<?php echo __(wbbm_get_option('wbbm_adult_text', 'wbbm_label_setting_sec') ? wbbm_get_option('wbbm_adult_text', 'wbbm_label_setting_sec') . ' ' . __('return discount price', 'bus-booking-manager') : __('Adult', 'bus-booking-manager') . ' ' . __('return discount price', 'bus-booking-manager'), 'bus-booking-manager'); ?>"
                        value="" class="text roundtrip-input">
                </td>
                <td class="wbbm-price-col">
                    <input step="0.01" type="number" name='wbbm_bus_price_child[]' value="" class="text">
                    <input step="0.01" type="number" name='wbbm_bus_price_child_roundtrip[]'
                        placeholder="<?php echo __(wbbm_get_option('wbbm_child_text', 'wbbm_label_setting_sec') ? wbbm_get_option('wbbm_child_text', 'wbbm_label_setting_sec') . ' ' . __('return discount price', 'bus-booking-manager') : __('Child', 'bus-booking-manager') . ' ' . __('return discount price', 'bus-booking-manager'), 'bus-booking-manager'); ?>"
                        value="" class="text roundtrip-input">
                </td>
                <td class="wbbm-price-col">
                    <input step="0.01" type="number" name='wbbm_bus_price_infant[]' value="" class="text">
                    <input step="0.01" type="number" name='wbbm_bus_price_infant_roundtrip[]'
                        placeholder="<?php echo __(wbbm_get_option('wbbm_infant_text', 'wbbm_label_setting_sec') ? wbbm_get_option('wbbm_infant_text', 'wbbm_label_setting_sec') . ' ' . __('return discount price', 'bus-booking-manager') : __('Infant', 'bus-booking-manager') . ' ' . __('return discount price', 'bus-booking-manager'), 'bus-booking-manager'); ?>"
                        value="" class="text roundtrip-input">
                </td>

                <?php if($entire_bus_booking == 'on'): ?>
                    <td class="wbbm-price-col">
                    <input step="0.01" type="number" name='wbbm_bus_price_entire[]' value="" class="text">
                    <input step="0.01" type="number" name='wbbm_bus_price_entire_roundtrip[]'
                        placeholder="<?php echo __(wbbm_get_option('wbbm_entire_bus_text', 'wbbm_label_setting_sec') ? wbbm_get_option('wbbm_entire_bus_text', 'wbbm_label_setting_sec') . ' ' . __('return discount price', 'bus-booking-manager') : __('Entire Bus', 'bus-booking-manager') . ' ' . __('return discount price', 'bus-booking-manager'), 'bus-booking-manager'); ?>"
                        value="" class="text roundtrip-input">
                </td>
                <?php endif; ?> 
                   
                <td><a class="button remove-price-row" href="#"><i class="fas fa-minus-circle"></i>
                        <?php _e('Remove', 'bus-booking-manager'); ?></a>
                </td>
            </tr>
            <!-- Roundtrip price -->

            <!-- Roundtrip price -->
        </tbody>
    </table>
    <p><a id="add-price-row" class="button" href="#"><i class="fas fa-plus"></i>
            <?php _e('Add More Price', 'bus-booking-manager'); ?></a></p>
</div>

<?php
                } else {
                    echo "<div class='wbbm_bus_stops_notice'>".__('Please Enter some bus stops first.', 'bus-booking-manager')."<a class='wbbm_color_white' href='" . get_admin_url() . "edit-tags.php?taxonomy=wbbm_bus_stops&post_type=wbbm_bus'>".__('Click here for bus stops', 'bus-booking-manager')."</a></div>";
                }
                ?>
<script type="text/javascript">
jQuery(document).ready(function($) {


    $('#add-price-row').on('click', function() {
        var row = $('.empty-row-price.screen-reader-text').clone(true);
        row.removeClass('empty-row-price screen-reader-text');
        row.insertBefore('#repeatable-fieldset-price-one tbody>tr:last');
        return false;
    });

    $('.remove-price-row').on('click', function() {
        $(this).parents('tr').remove();
        return false;
    });

});
</script>
<?php     
    }

    public function wbbm_bus_pickuppoint(){
        global $post;

        $bus_stops = get_terms(array(
            'taxonomy' => 'wbbm_bus_stops',
            'hide_empty' => false
        ));

        $bus_pickpoints = get_terms(array(
            'taxonomy' => 'wbbm_bus_pickpoint',
            'hide_empty' => false
        ));

        if ($bus_pickpoints) {
            $pickpoints = '';
            foreach ($bus_pickpoints as $points) {
                $pickpoints .= '<option value="' . $points->slug . '">' . str_replace("'", '', $points->name) . '</option>';
            }
        }

        ?>

<div class="wbbm_bus_pickpint_wrapper">
    <div class="wbbm_left_col">
        <div class="wbbm_field_group">
            <select name="wbbm_pick_boarding" id="wbbm_pick_boarding">
                <option value=""><?php _e('Select Boarding Point', 'bus-booking-manager'); ?></option>
                <?php foreach ($bus_stops as $stop) :
                                    $stop_slug = $stop->name;
                                    $stop_slug = strtolower($stop_slug);
                                    $stop_slug = preg_replace('/[^A-Za-z0-9-]/', '_', $stop_slug);
                                    ?>
                <option value="<?php echo $stop_slug ?>"><?php echo $stop->name ?></option>
                <?php endforeach; ?>
            </select>
            <button class="wbbm_add_pickpoint_this_city"
                id="wbbm_add_pickpoint_this_city"><?php _e('Add Pickup point', 'bus-booking-manager'); ?> <i
                    class="fas fa-arrow-right"></i></button>
        </div>
    </div>
    <?php $selected_city_pickpoints = get_post_meta($post->ID, 'wbbm_pickpoint_selected_city', true); ?>
    <div class="wbbm_right_col <?php echo($selected_city_pickpoints == '' ? 'all-center' : ''); ?>">
        <div id="wbbm_pickpoint_selected_city">

            <?php

                            if ($selected_city_pickpoints != '') {

                                $selected_city_pickpoints = explode(',', $selected_city_pickpoints);
                                foreach ($selected_city_pickpoints as $single) {
                                    $get_pickpoints_data = get_post_meta($post->ID, 'wbbm_selected_pickpoint_name_' . $single, true); ?>
            <div class="wbbm_selected_city_item">
                <span class="remove_city_for_pickpoint"><i class="fas fa-minus-circle"></i></span>
                <h4 class="wbbm_pickpoint_title"><?php echo ucfirst($single); ?></h4>
                <input type="hidden" name="wbbm_pickpoint_selected_city[]" value="<?php echo $single; ?>">
                <div class="pickpoint-adding-wrap">
                    <?php

                                            if ($get_pickpoints_data) {
                                                $get_pickpoints_data = unserialize($get_pickpoints_data);

                                                foreach ($get_pickpoints_data as $pickpoint) : ?>


                    <div class="pickpoint-adding">
                        <select name="wbbm_selected_pickpoint_name_<?php echo $single; ?>[]">
                            <?php
                                                            if ($bus_pickpoints) {
                                                                foreach ($bus_pickpoints as $bus_pickpoint) {
                                                                    echo '<option value="' . $bus_pickpoint->slug . '" ' . ($bus_pickpoint->slug == $pickpoint['pickpoint'] ? "selected=selected" : '') . '>' . $bus_pickpoint->name . '</option>';
                                                                }
                                                            }
                                                            ?>
                        </select>
                        <input type="text" name="wbbm_selected_pickpoint_time_<?php echo $single; ?>[]"
                            value="<?php echo $pickpoint['time']; ?>" placeholder="15:00">
                        <button class="wbbm_remove_pickpoint"><i class="fas fa-minus-circle"></i>
                        </button>
                    </div>

                    <?php
                                                endforeach;
                                            } ?>
                </div>
                <button class="wbbm_add_more_pickpoint"><i class="fas fa-plus"></i>
                    <?php _e('Add more', 'bus-booking-manager'); ?>
                </button>
            </div>
            <?php
                                }

                            } else {
                                echo '<p class="blank-pickpoint" style="color: #FF9800;font-weight: 700;">' . __('No pickup point added yet!', 'bus-booking-manager') . '</p>';
                            }
                            ?>

        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {

    // Select Boarding point and hit add
    $('.wbbm_add_pickpoint_this_city').click(function(e) {
        e.preventDefault();

        $('.blank-pickpoint').remove();
        $('.wbbm_right_col').removeClass('all-center');
        var get_boarding_point = $('#wbbm_pick_boarding option:selected').val();

        // Validation
        if (get_boarding_point == '') {
            $('#wbbm_pick_boarding').css({
                'border': '1px solid red',
                'color': 'red'
            }); // Not ok!!!
            return;
        } else {
            $('#wbbm_pick_boarding').css({
                'border': '1px solid #7e8993',
                'color': '#8ac34a'
            }); // Ok

        }

        var get_boarding_point_name = $('#wbbm_pick_boarding option:selected').text();
        $('#wbbm_pick_boarding option:selected').remove();
        var html =
            '<div class="wbbm_selected_city_item"><span class="remove_city_for_pickpoint"><i class="fas fa-minus-circle"></i></i></span>' +
            '<h4 class="wbbm_pickpoint_title">' + get_boarding_point_name + '</h4>' +
            '<input type="hidden" name="wbbm_pickpoint_selected_city[]" value="' + get_boarding_point +
            '">' +
            '<div class="pickpoint-adding-wrap"><div class="pickpoint-adding">' +
            '<select name="wbbm_selected_pickpoint_name_' + get_boarding_point + '[]">' +
            '<?php echo $pickpoints; ?>' +
            '</select>' +
            '<input type="text" name="wbbm_selected_pickpoint_time_' + get_boarding_point +
            '[]" placeholder="15:00">' +
            '<button class="wbbm_remove_pickpoint"><i class="fas fa-minus-circle"></i></button>' +
            '</div></div>' +
            '<button class="wbbm_add_more_pickpoint"><i class="fas fa-plus"></i> <?php _e("Add more", "bus-booking-manager"); ?></button>' +
            '</div>';


        if ($('#wbbm_pickpoint_selected_city').children().length > 0) {
            $('#wbbm_pickpoint_selected_city').append(html);
        } else {
            $('#wbbm_pickpoint_selected_city').html(html);
        }

        $('#wbbm_pick_boarding option:first').attr('selected', 'selected');

    });

    // Remove City for Pickpoint
    $(document).on('click', '.remove_city_for_pickpoint', function(e) {
        e.preventDefault();

        var city_name = $(this).siblings('.wbbm_pickpoint_title').text();
        var city_name_val = $(this).siblings('input').val();
        $('#wbbm_pick_boarding').append('<option value="' + city_name_val + '">' + city_name +
            '</option>');
        $(this).parents('.wbbm_selected_city_item').remove();
    });

    // Adding more pickup point
    $(document).on('click', '.wbbm_add_more_pickpoint', function(e) {
        e.preventDefault();

        $adding_more = $(this).siblings('.pickpoint-adding-wrap').find('.pickpoint-adding:first').clone(
            true);
        $(this).siblings('.pickpoint-adding-wrap').append($adding_more);
    });

    // Remove More Pickpoint
    $(document).on('click', '.wbbm_remove_pickpoint', function(e) {
        e.preventDefault();

        // Remove wrapper
        if ($(this).parents('.pickpoint-adding-wrap').children().length == 1) {
            $(this).parents('.wbbm_selected_city_item').find('.remove_city_for_pickpoint').trigger(
                'click');
        }

        // Remove Item
        $(this).parent().remove();


    });

});
</script>
<?php
            }

            public function wbbm_bus_ondayoffday(){
                global $post;
                $values = get_post_custom($post->ID);
                $cpt_label = wbbm_get_option('wbbm_cpt_label', 'wbbm_general_setting_sec', __('Bus', 'bus-booking-manager'));
                ?>
<div class='sec onday-sec'>
    <label for="bus_on_date">
        <?php echo $cpt_label.' '. __('Onday Dates:', 'bus-booking-manager'); ?>
        <span><input type="text" id='bus_on_date' name='wbtm_bus_on_date' value='<?php if (array_key_exists('wbtm_bus_on_date', $values)) {
                                        echo $values['wbtm_bus_on_date'][0];
                                    } ?>' /> </span></label>
</div>

<script type="text/javascript">
jQuery(document).ready(function($) {
    jQuery("#bus_on_date").multiDatesPicker({
        numberOfMonths: [1,3],
        dateFormat: "yy-mm-dd",
        minDate: 0,
    });

});
</script>
<hr>
<!-- Start Offday wrapper-->
<?php
                    $values = get_post_custom($post->ID);
                    $wbtm_offday_schedule = get_post_meta($post->ID, 'wbtm_offday_schedule', true);
                    ?>

<div class="wbbm-offday-wrapper">
    <div class="wbbm-offday-inner">
        <label><?php echo $cpt_label.' '. __('Offday Date and Time:', 'bus-booking-manager'); ?></label>
        <table id="repeatable-fieldset-offday" width="100%">
            <tr>
                <th><?php _e('From Date', 'bus-booking-manager'); ?></th>
                <th><?php _e('From Time', 'bus-booking-manager'); ?></th>
                <th><?php _e('To Date', 'bus-booking-manager'); ?></th>
                <th><?php _e('To Time', 'bus-booking-manager'); ?></th>
                <th></th>
            </tr>
            <tbody>
                <?php
                            if ($wbtm_offday_schedule) :
                                $count = 0;
                                foreach ($wbtm_offday_schedule as $field) {
                                    ?>
                <tr class="">
                    <td align="left"><input type="text" id="<?php echo 'db_offday_from_'.$count; ?>"
                            class="repeatable-offday-from-field" name='wbtm_od_offdate_from[]' placeholder="2020-12-31"
                            value="<?php echo $field['from_date'] ?>" /></td>
                    <td align="left"><input type="text" class="repeatable-offtime-from-field"
                            name='wbtm_od_offtime_from[]' placeholder="09:00 am"
                            value="<?php echo $field['from_time'] ?>" /></td>
                    <td align="left"><input type="text" id="<?php echo 'db_offday_to_'.$count; ?>"
                            class="repeatable-offday-to-field" name='wbtm_od_offdate_to[]' placeholder="2020-12-31"
                            value="<?php echo $field['to_date'] ?>" /></td>
                    <td align="left"><input type="text" class="repeatable-offtime-to-field" name='wbtm_od_offtime_to[]'
                            placeholder="09:59 pm" value="<?php echo $field['to_time'] ?>" /></td>
                    <td align="left">
                        <a class="button remove-bp-row" href="#">
                            <i class="fas fa-minus-circle"></i>
                            <?php _e('Remove', 'bus-booking-manager'); ?>
                        </a>
                    </td>
                </tr>

                <script>
                setTimeout(function() {
                    $("#db_offday_from_<?php echo $count ?>").datepicker({
                        dateFormat: "yy-mm-dd",
                        minDate: 0
                    });
                    $("#db_offday_to_<?php echo $count ?>").datepicker({
                        dateFormat: "yy-mm-dd",
                        minDate: 0
                    });
                }, 400);
                </script>
                <?php
                
                                    $count++;
                                }
                            else :
                                // show a blank one
                            endif;
                            ?>

                <!-- empty hidden one for jQuery -->
                <tr class="empty-row-offday screen-reader-text">
                    <td align="left"><input type="text" class="repeatable-offday-from-field"
                            name='wbtm_od_offdate_from[]' placeholder="2020-12-31" /></td>
                    <td align="left"><input type="text" class="repeatable-offtime-from-field"
                            name='wbtm_od_offtime_from[]' placeholder="09:00 am" /></td>
                    <td align="left"><input type="text" class="repeatable-offday-to-field" name='wbtm_od_offdate_to[]'
                            placeholder="2020-12-31" /></td>
                    <td align="left"><input type="text" class="repeatable-offtime-to-field" name='wbtm_od_offtime_to[]'
                            placeholder="09:59 pm" /></td>
                    <td align="left">
                        <a class="button remove-bp-row" href="#">
                            <i class="fas fa-minus-circle"></i>
                            <?php _e('Remove', 'bus-booking-manager'); ?>
                        </a>
                    </td>
                </tr>
            </tbody>
        </table>
        <a id="add-offday-row" class="button" href="#"><i class="fas fa-plus"></i>
            <?php _e('Add More offdate', 'bus-booking-manager'); ?>
        </a>
    </div>
</div>

<script type="text/javascript">
jQuery(document).ready(function($) {
    var datePickerOpt = {
        dateFormat: "yy-mm-dd",
        minDate: 0
    }
    $('#add-offday-row').on('click', function(e) {
        e.preventDefault();
        var now = Date.now();
        var row = $('.empty-row-offday.screen-reader-text').clone(true);
        row.removeClass('empty-row-offday screen-reader-text');
        row.insertBefore('#repeatable-fieldset-offday tbody>tr:last');
        row.find(".repeatable-offday-from-field").attr('id', 'offday_from' + now);
        row.find(".repeatable-offday-to-field").attr('id', 'offday_to' + now);

        $("#offday_from" + now).datepicker(datePickerOpt);
        $("#offday_to" + now).datepicker(datePickerOpt);

    });

    $('.remove-bp-row').on('click', function() {
        $(this).parents('tr').remove();
        return false;
    });
});
</script>
<hr>
<label><?php echo $cpt_label.' '.__('Offdays:', 'bus-booking-manager'); ?></label>
<div class='sec offday-sec'>
    <label for='sun'>
        <input type="checkbox" id='sun' style="text-align: left;width: auto;" name="od_sun" value='yes' <?php if (array_key_exists('od_Sun', $values)) {
                                if ($values['od_Sun'][0] == 'yes') {
                                    echo 'Checked';
                                }
                            } ?> /> <?php _e('Sunday', 'bus-booking-manager'); ?>
    </label>
    <label for='mon'>
        <input type="checkbox" style="text-align: left;width: auto;" name="od_mon" value='yes' id='mon' <?php if (array_key_exists('od_Mon', $values)) {
                                if ($values['od_Mon'][0] == 'yes') {
                                    echo 'Checked';
                                }
                            } ?>> <?php _e('Monday', 'bus-booking-manager'); ?>
    </label>
    <label for='tue'>
        <input type="checkbox" style="text-align: left;width: auto;" name="od_tue" value='yes' id='tue' <?php if (array_key_exists('od_Tue', $values)) {
                                if ($values['od_Tue'][0] == 'yes') {
                                    echo 'Checked';
                                }
                            } ?>> <?php _e('Tuesday', 'bus-booking-manager'); ?>
    </label>
    <label for='wed'>
        <input type="checkbox" style="text-align: left;width: auto;" name="od_wed" value='yes' id='wed' <?php if (array_key_exists('od_Wed', $values)) {
                                if ($values['od_Wed'][0] == 'yes') {
                                    echo 'Checked';
                                }
                            } ?>> <?php _e('Wednesday', 'bus-booking-manager'); ?>
    </label>
    <label for='thu'>
        <input type="checkbox" style="text-align: left;width: auto;" name="od_thu" value='yes' id='thu' <?php if (array_key_exists('od_Thu', $values)) {
                                if ($values['od_Thu'][0] == 'yes') {
                                    echo 'Checked';
                                }
                            } ?>> <?php _e('Thursday', 'bus-booking-manager'); ?>
    </label>
    <label for='fri'>
        <input type="checkbox" style="text-align: left;width: auto;" name="od_fri" value='yes' id='fri' <?php if (array_key_exists('od_Fri', $values)) {
                                if ($values['od_Fri'][0] == 'yes') {
                                    echo 'Checked';
                                }
                            } ?>> <?php _e('Friday', 'bus-booking-manager'); ?>
    </label>
    <label for='sat'>
        <input type="checkbox" style="text-align: left;width: auto;" name="od_sat" value='yes' id='sat' <?php if (array_key_exists('od_Sat', $values)) {
                                if ($values['od_Sat'][0] == 'yes') {
                                    echo 'Checked';
                                }
                            } ?>> <?php _e('Saturday', 'bus-booking-manager'); ?>
    </label>
</div>

<script type="text/javascript">
jQuery(document).ready(function($) {

    jQuery("#od_start").datepicker({
        dateFormat: "yy-mm-dd",
        minDate: 0
    });
    jQuery("#od_end").datepicker({
        dateFormat: "yy-mm-dd",
        // minDate:0
    });


    // jQuery( "#ja_date" ).datepicker({
    //   dateFormat: "yy-mm-dd"
    // });
    // jQuery(".the_select select").select2();
});
</script>
<?php
    }
    public function wbbm_extra_price_option($post_id)
    {
        $mep_events_extra_prices = get_post_meta($post_id, 'mep_events_extra_prices', true);
        wp_nonce_field('mep_events_extra_price_nonce', 'mep_events_extra_price_nonce');
        ?>
        <div id="wbbm_extra_service">
            <h3><?php _e('Extra service Area :', 'bus-booking-manager'); ?></h3>
            
            <p class="event_meta_help_txt">
                <?php _e('Extra Service as Product that you can sell and it is not included on ticket', 'bus-booking-manager'); ?>
            </p>
            <hr>
            <div class="mp_ticket_type_table">
                <table id="repeatable-fieldset-one">
                    <thead>
                    <tr>
                        <th title="<?php _e('Extra Service Name', 'bus-booking-manager'); ?>">
                            <?php _e('Name', 'bus-booking-manager'); ?></th>
                        <th title="<?php _e('Extra Service Price', 'bus-booking-manager'); ?>">
                            <?php _e('Price', 'bus-booking-manager'); ?></th>
                        <th title="<?php _e('Available Qty', 'bus-booking-manager'); ?>">
                            <?php _e('Available', 'bus-booking-manager'); ?></th>
                        <th title="<?php _e('Qty Box Type', 'bus-booking-manager'); ?>"
                            style="min-width: 140px;">
                            <?php _e('Qty Box', 'bus-booking-manager'); ?></th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody class="mp_event_type_sortable">
                    <?php

                    if ($mep_events_extra_prices) :

                        foreach ($mep_events_extra_prices as $field) {
                            $qty_type = esc_attr($field['option_qty_type']);
                            ?>
                            <tr>
                                <td><input type="text" class="mp_formControl" name="option_name[]" placeholder="Ex: Cap"
                                           value="<?php if ($field['option_name'] != '') {
                                               echo esc_attr($field['option_name']);
                                           } ?>"/></td>

                                <td><input type="number" step="0.001" class="mp_formControl" name="option_price[]"
                                           placeholder="Ex: 10"
                                           value="<?php if ($field['option_price'] != '') {
                                               echo esc_attr($field['option_price']);
                                           } else {
                                               echo '';
                                           } ?>"/></td>

                                <td><input type="number" class="mp_formControl" name="option_qty[]"
                                           placeholder="Ex: 100" value="<?php if ($field['option_qty'] != '') {
                                        echo esc_attr($field['option_qty']);
                                    } else {
                                        echo '';
                                    } ?>"/></td>

                                <td align="center">
                                    <select name="option_qty_type[]" class='mp_formControl'>
                                        <option value="inputbox" <?php if ($qty_type == 'inputbox') {
                                            echo "Selected";
                                        } ?>><?php _e('Input Box', 'bus-booking-manager'); ?></option>
                                        <option value="dropdown" <?php if ($qty_type == 'dropdown') {
                                            echo "Selected";
                                        } ?>><?php _e('Dropdown List', 'bus-booking-manager'); ?></option>
                                    </select>
                                </td>
                                <td>
                                    <div class="mp_event_remove_move">
                                        <a class="button remove-row" type="button"><i class="fas fa-trash"></i></a>
                                    </div>
                                </td>
                            </tr>
                            <?php
                        }
                    else :
                        // show a blank one
                    endif;
                    ?>

                    <!-- empty hidden one for jQuery -->
                    <tr class="empty-row screen-reader-text">
                        <td><input type="text" class="mp_formControl" name="option_name[]" placeholder="Ex: Cap"/></td>
                        <td><input type="number" class="mp_formControl" step="0.001" name="option_price[]"
                                   placeholder="Ex: 10"
                                   value=""/></td>
                        <td><input type="number" class="mp_formControl" name="option_qty[]" placeholder="Ex: 100"
                                   value=""/>
                        </td>

                        <td><select name="option_qty_type[]" class='mp_formControl'>
                                <option value=""><?php _e('Please Select Type', 'bus-booking-manager'); ?></option>
                                <option value="inputbox"><?php _e('Input Box', 'bus-booking-manager'); ?></option>
                                <option value="dropdown"><?php _e('Dropdown List', 'bus-booking-manager'); ?></option>
                            </select></td>
                        <td>
                            <a class="button remove-row"><i class="fas fa-trash"></i></a>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
            <p>
                <a id="add-row" class="button"><i class="fas fa-plus-circle"></i> <?php _e('Add Extra Price', 'bus-booking-manager'); ?>
                </a>
            </p>
        </div>
        <?php
    }

    public function wbbm_single_settings_meta_save($post_id){
        global $post;

        if (!isset($_POST['wbbm_single_bus_settings_nonce']) ||
        !wp_verify_nonce($_POST['wbbm_single_bus_settings_nonce'], 'wbbm_single_bus_settings_nonce')){
            return;
        }
        
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE){
            return;
        }
               
        if (!current_user_can('edit_post', $post_id)){
            return;
        }
                           
        /* Bus Price Zero Allow */
        if (isset($_POST['wbbm_price_zero_allow'])) {
            $wbbm_price_zero_allow = strip_tags($_POST['wbbm_price_zero_allow']);
        } else {
            $wbbm_price_zero_allow = 'off';
        }
        $update_seat = update_post_meta($post_id, 'wbbm_price_zero_allow', $wbbm_price_zero_allow);

        /* Bus Sell Off */
        if (isset($_POST['wbbm_sell_off'])) {
            $wbbm_sell_off = strip_tags($_POST['wbbm_sell_off']);
        } else {
            $wbbm_sell_off = 'off';
        }
        update_post_meta($post_id, 'wbbm_sell_off', $wbbm_sell_off);
        
        /* Bus Seat Available Show */
        if (isset($_POST['wbbm_seat_available'])) {
            $wbbm_seat_available = strip_tags($_POST['wbbm_seat_available']);
        } else {
            $wbbm_seat_available = 'off';
        }
        update_post_meta($post_id, 'wbbm_seat_available', $wbbm_seat_available);
        
        /* Bus Category, Coach no and Seat */
        $wbbm_bus_category = strip_tags($_POST['wbbm_bus_category']);
        $wbbm_bus_no = strip_tags($_POST['wbbm_bus_no']);
        $wbbm_total_seat = strip_tags($_POST['wbbm_total_seat']);
        $update_seat_stock_status = update_post_meta($post_id, '_manage_stock', 'no');
        $update_price = update_post_meta($post_id, '_price', 0);
        $update_seat5 = update_post_meta($post_id, 'wbbm_bus_no', $wbbm_bus_no);
        $update_seat6 = update_post_meta($post_id, 'wbbm_total_seat', $wbbm_total_seat);
        $update_virtual = update_post_meta($post_id, '_virtual', 'yes');
        $update_bus_category = update_post_meta($post_id, 'wbbm_bus_category', $wbbm_bus_category);

        /* Bus Boarding Point */
        $old = get_post_meta($post_id, 'wbbm_bus_bp_stops', true);
        $new = array();
        $bp_stops = $_POST['wbbm_bus_bp_stops_name'];
        $start_t = $_POST['wbbm_bus_bp_start_time'];   
        $order_id = 0;
        if(!empty($bp_stops)){
            $count = count($bp_stops);
        }else{
            $count = 0;
        }
        
    
        for ($i = 0; $i < $count; $i++) {
    
            if ($bp_stops[$i] != '') :
                $new[$i]['wbbm_bus_bp_stops_name'] = stripslashes(strip_tags($bp_stops[$i]));
            endif;
    
            if ($start_t[$i] != '') :
                $new[$i]['wbbm_bus_bp_start_time'] = stripslashes(strip_tags($start_t[$i]));
            endif;
        }
    
        $bstart_time = $start_t[0];
        update_post_meta($post_id, 'wbbm_bus_start_time', $bstart_time);
        if (!empty($new) && $new != $old) {
            update_post_meta($post_id, 'wbbm_bus_bp_stops', $new);
        } elseif (empty($new) && $old) {
            delete_post_meta($post_id, 'wbbm_bus_bp_stops', $old);
            update_post_meta($post_id, 'wbbm_bus_start_time', '');
        }
        
        /* Bus Dropping Point */
        $old = get_post_meta($post_id, 'wbbm_bus_next_stops', true);
        $new = array();
    
        $stops = $_POST['wbbm_bus_next_stops_name'];
        $end_t = $_POST['wbbm_bus_next_end_time'];
    
    
        $order_id = 0;
        if(!empty($stops)){
            $count = count($stops);
        }
        else{
            $count = 0; 
        }
        
    
        for ($i = 0; $i < $count; $i++) {
    
            if ($stops[$i] != '') :
                $new[$i]['wbbm_bus_next_stops_name'] = stripslashes(strip_tags($stops[$i]));
            endif;
    
            if ($end_t[$i] != '') :
                $new[$i]['wbbm_bus_next_end_time'] = stripslashes(strip_tags($end_t[$i]));
            endif;
    
            $opt_name = $post_id . str_replace(' ', '', $names[$i]);
    
            // update_post_meta( $post_id, "wbbm_xtra_$opt_name",0 );
    
        }
    
        if (!empty($new) && $new != $old){
            update_post_meta($post_id, 'wbbm_bus_next_stops', $new);
        } elseif (empty($new) && $old){
            delete_post_meta($post_id, 'wbbm_bus_next_stops', $old);
        }

        /* Bus Pricing */    
        $old = get_post_meta($post_id, 'wbbm_bus_prices', true);
        $new = array();
        $bp_pice_stops = $_POST['wbbm_bus_bp_price_stop'];
        $dp_pice_stops = $_POST['wbbm_bus_dp_price_stop'];
        $the_price = $_POST['wbbm_bus_price'];
        $the_price_roundtrip = $_POST['wbbm_bus_price_roundtrip'];
        $the_price_child = $_POST['wbbm_bus_price_child'];
        $the_price_child_roundtrip = $_POST['wbbm_bus_price_child_roundtrip'];
        $the_price_infant = $_POST['wbbm_bus_price_infant'];
        $the_price_infant_roundtrip = $_POST['wbbm_bus_price_infant_roundtrip'];
        $the_price_entire = $_POST['wbbm_bus_price_entire'];
        $the_price_entire_roundtrip = $_POST['wbbm_bus_price_entire_roundtrip'];          
        $order_id = 0;
        if(!empty($bp_pice_stops)){
            $count = count($bp_pice_stops);
        }
        else{
            $count = 0;
        }
        
    
        for ($i = 0; $i < $count; $i++) {
    
            if ($bp_pice_stops[$i] != '') :
                $new[$i]['wbbm_bus_bp_price_stop'] = stripslashes(strip_tags($bp_pice_stops[$i]));
            endif;
    
            if ($dp_pice_stops[$i] != '') :
                $new[$i]['wbbm_bus_dp_price_stop'] = stripslashes(strip_tags($dp_pice_stops[$i]));
            endif;
    
            if ($the_price[$i] != '') :
                $new[$i]['wbbm_bus_price'] = stripslashes(strip_tags($the_price[$i]));
                $new[$i]['wbbm_bus_price_roundtrip'] = stripslashes(strip_tags($the_price_roundtrip[$i]));
            endif;
    
            if ($the_price_child[$i] != '') :
                $new[$i]['wbbm_bus_price_child'] = stripslashes(strip_tags($the_price_child[$i]));
                $new[$i]['wbbm_bus_price_child_roundtrip'] = stripslashes(strip_tags($the_price_child_roundtrip[$i]));
            endif;
    
            if ($the_price_infant[$i] != '') :
                $new[$i]['wbbm_bus_price_infant'] = stripslashes(strip_tags($the_price_infant[$i]));
                $new[$i]['wbbm_bus_price_infant_roundtrip'] = stripslashes(strip_tags($the_price_infant_roundtrip[$i]));
            endif;

            if ($the_price_entire[$i] != '') :
                $new[$i]['wbbm_bus_price_entire'] = stripslashes(strip_tags($the_price_entire[$i]));
                $new[$i]['wbbm_bus_price_entire_roundtrip'] = stripslashes(strip_tags($the_price_entire_roundtrip[$i]));
            endif;
        }
    
        if (!empty($new) && $new != $old){
            update_post_meta($post_id, 'wbbm_bus_prices', $new);
        }elseif (empty($new) && $old){
            delete_post_meta($post_id, 'wbbm_bus_prices', $old);
        }

        // Extra services
        $extra_service_old = get_post_meta($post_id, 'mep_events_extra_prices', true);
        $extra_service_new = array();
        $names = isset($_POST['option_name']) ? $_POST['option_name'] : array();
        $urls = $_POST['option_price'];
        $qty = $_POST['option_qty'];
        $qty_type = $_POST['option_qty_type'];
        $order_id = 0;
        $count = count($names);

        for ($i = 0; $i < $count; $i++) {
            if ($names[$i] != '') :
                $extra_service_new[$i]['option_name'] = stripslashes(strip_tags($names[$i]));
            else :
                continue;
            endif;

            if ($urls[$i] != '') :
                $extra_service_new[$i]['option_price'] = stripslashes(strip_tags($urls[$i]));
            else : 
                $extra_service_new[$i]['option_price'] = 0;
            endif;

            if ($qty[$i] != '') :
                $extra_service_new[$i]['option_qty'] = stripslashes(strip_tags($qty[$i]));
            else : 
                $extra_service_new[$i]['option_qty'] = 0;
            endif;

            if ($qty_type[$i] != '') :
                $extra_service_new[$i]['option_qty_type'] = stripslashes(strip_tags($qty_type[$i]));
            else :
                $extra_service_new[$i]['option_qty_type'] = 'inputbox';
            endif;
        }

        update_post_meta($post_id, 'mep_events_extra_prices', $extra_service_new ? $extra_service_new : null);
        // Extra services END

        /* Bus Pickuppoint */
        $selected_city_key = 'wbbm_pickpoint_selected_city';
        $selected_pickpoint_name = 'wbbm_selected_pickpoint_name_';
        $selected_pickpoint_time = 'wbbm_selected_pickpoint_time_';
    
        if (isset($_POST['wbbm_pickpoint_selected_city'])) {
            $selected_city = $_POST['wbbm_pickpoint_selected_city'];
   
            if (!empty($selected_city)) {
    
                $selected_city_str = implode(',', $selected_city);
    
                // If need delete
                $prev_selected_city = get_post_meta($post_id, $selected_city_key, true);
                if ($prev_selected_city) {
                    $prev_selected_city = explode(',', $prev_selected_city);
    
                    $diff = array_diff($prev_selected_city, $selected_city);
                    if (!empty($diff)) {
    
                        $diff = array_values($diff);
                        foreach ($diff as $s) {
                            delete_post_meta($post_id, 'wbbm_selected_pickpoint_name_' . $s);
                        }
                    }
                }
                // If need delete END
    
                update_post_meta($post_id, $selected_city_key, $selected_city_str);
    
                foreach ($selected_city as $city) {
                    $m_array = array();
                    $i = 0;
                    foreach ($_POST[$selected_pickpoint_name . $city] as $pickpoint) {
    
                        $m_array[$i] = array(
                            'pickpoint' => $_POST[$selected_pickpoint_name . $city][$i],
                            'time' => $_POST[$selected_pickpoint_time . $city][$i],
                        );
    
                        $i++;
                    }
    
                    update_post_meta($post_id, $selected_pickpoint_name . $city, serialize($m_array));
                }
    
            }
        } else {
            // If need delete
            $prev_selected_city = get_post_meta($post_id, $selected_city_key, true);
            if ($prev_selected_city) {
                $prev_selected_city = explode(',', $prev_selected_city);
    
                delete_post_meta($post_id, $selected_city_key);
    
                foreach ($prev_selected_city as $s) {
                    delete_post_meta($post_id, 'wbbm_selected_pickpoint_name_' . $s);
                }
            }
            // If need delete END
        }
        
        /* Bus Onday & Offday */

        // Offday schedule
        $offday_schedule_array = array();
        $offday_date_from = $_POST['wbtm_od_offdate_from'];
        $offday_date_to = $_POST['wbtm_od_offdate_to'];
        $offday_time_from = $_POST['wbtm_od_offtime_from'];
        $offday_time_to = $_POST['wbtm_od_offtime_to'];

        if(is_array($offday_date_from) && !empty($offday_date_from)) {
            $i = 0;
            for ($i = 0; $i < count($offday_date_from); $i++) {
                if( $offday_date_from[$i] != '' ) {
                    $offday_schedule_array[$i]['from_date'] = $offday_date_from[$i];
                    $offday_schedule_array[$i]['from_time'] = $offday_time_from[$i];
                    $offday_schedule_array[$i]['to_date']   = $offday_date_to[$i];
                    $offday_schedule_array[$i]['to_time']   = $offday_time_to[$i];
                }
            }
        }
        update_post_meta($post_id, 'wbtm_offday_schedule', $offday_schedule_array);
        // Offday schedule END

        $wbtm_od_start = strip_tags($_POST['wbtm_od_start']);
        $wbtm_od_end = strip_tags($_POST['wbtm_od_end']);
        $wbtm_bus_on_date = $_POST['wbtm_bus_on_date'];
        $od_sun = strip_tags($_POST['od_sun']);
        $od_mon = strip_tags($_POST['od_mon']);
        $od_tue = strip_tags($_POST['od_tue']);
        $od_wed = strip_tags($_POST['od_wed']);
        $od_thu = strip_tags($_POST['od_thu']);
        $od_fri = strip_tags($_POST['od_fri']);
        $od_sat = strip_tags($_POST['od_sat']);
        $show_boarding_points = strip_tags($_POST['show_boarding_points']);
        $update_virtual = update_post_meta($post_id, '_virtual', 'yes');
        $update_wbtm_od_start = update_post_meta($post_id, 'wbtm_od_start', $wbtm_od_start);
        $update_wbtm_od_end = update_post_meta($post_id, 'wbtm_od_end', $wbtm_od_end);
        $wbtm_bus_on_date = update_post_meta($post_id, 'wbtm_bus_on_date', $wbtm_bus_on_date);
        $update_wbtm_od_sun = update_post_meta($post_id, 'od_Sun', $od_sun);
        $update_wbtm_od_mon = update_post_meta($post_id, 'od_Mon', $od_mon);
        $update_wbtm_od_tue = update_post_meta($post_id, 'od_Tue', $od_tue);
        $update_wbtm_od_wed = update_post_meta($post_id, 'od_Wed', $od_wed);
        $update_wbtm_od_thu = update_post_meta($post_id, 'od_Thu', $od_thu);
        $update_wbtm_od_fri = update_post_meta($post_id, 'od_Fri', $od_fri);
        $update_wbtm_od_sat = update_post_meta($post_id, 'od_Sat', $od_sat);
        $update_wbtm_show_boarding_points = update_post_meta($post_id, 'show_boarding_points', $show_boarding_points);

        // Partial Payment
        do_action('wcpp_partial_settings_saved', $post_id);
        // Partial Payment END
    }

    public function wbbm_remove_sidebar_meta_box()
    {
        remove_meta_box('wbbm_bus_catdiv', 'wbbm_bus', 'side');
        remove_meta_box('wbbm_bus_pickpointdiv', 'wbbm_bus', 'side');
        remove_meta_box('wbbm_bus_stopsdiv', 'wbbm_bus', 'side');
    }
} // Class End

new WBBMMetaBox();
