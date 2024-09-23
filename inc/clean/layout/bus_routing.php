<div class="mp_tab_item" data-tab-item="#wbtm_routing">

    <h3 class="wbbm_mp_tab_item_heading">
        <?php echo esc_html($cpt_label) . ' ' . esc_html__('Routing Configuration', 'bus-booking-manager'); ?>
    </h3>
    <p><?php echo esc_html__('Here you can configure ' . $cpt_label . ' Routing. Specially Boarding and Dropping Stops.', 'bus-booking-manager'); ?></p>
    
    <section class="bgLight">
        <div>
            <label for=""><?php esc_html_e('New Stops settings', 'bus-booking-manager'); ?></label><br>
            <span><?php echo esc_html__('Here you can create new stops for ' . $cpt_label, 'bus-booking-manager'); ?></span>
        </div>
    </section>
    
    <section>
        <div>
            <label for=""><?php echo esc_html__('Create new ' . $cpt_label . ' Stops', 'bus-booking-manager'); ?></label><br>
            <span class="ra-stopage-desc"><?php esc_html_e('You can create new stops from here.', 'bus-booking-manager'); ?></span>
        </div>
        <div>
            <div class="mpStyle">
                <button type="button" class="_dButton_xs_bgBlue wbtm_route_add_new_bus_btn" data-target-popup="#wbtm_route_popup">
                    <i class="fas fa-plus"></i>
                    <?php echo esc_html__('Add New ' . $cpt_label . ' Stops', 'bus-booking-manager'); ?>
                </button>
            </div>
        </div>
    </section>

    <div class="mp_tab_item_inner_wrapper">
        <div class="col-md-6">
            <div class="mpStyle">
                <div class="mpPopup" data-popup="#wbtm_route_popup">
                    <div class="popupMainArea">
                        <div class="popupHeader">
                            <h4><?php echo esc_html__('Add New ' . $cpt_label . ' Stops', 'bus-booking-manager'); ?></h4>
                            <span class="fas fa-times popupClose"></span>
                        </div>
                        <div class="popupBody bus-stop-form">
                            <h6 class="textSuccess success_text" style="display: none;"><?php esc_html_e('Added Successfully', 'bus-booking-manager'); ?></h6>
                            <h6 class="textduplicate duplicate_text" style="display: none;color: red"><?php esc_html_e('This bus stop already exists', 'bus-booking-manager'); ?></h6>
                            <label>
                                <span class="w_200"><?php esc_html_e('Name:', 'bus-booking-manager'); ?></span>
                                <input type="text" class="formControl" id="bus_stop_name">
                            </label>
                            <p class="name_required"><?php esc_html_e('Name is required', 'bus-booking-manager'); ?></p>

                            <label class="mT">
                                <span class="w_200"><?php esc_html_e('Description:', 'bus-booking-manager'); ?></span>
                                <textarea id="bus_stop_description" rows="5" cols="50" class="formControl"></textarea>
                            </label>
                        </div>
                        <div class="popupFooter">
                            <div class="buttonGroup">
                                <button class="_themeButton submit-bus-stop" type="button"><?php esc_html_e('Save', 'bus-booking-manager'); ?></button>
                                <button class="_warningButton submit-bus-stop close_popup" type="button"><?php esc_html_e('Save & Close', 'bus-booking-manager'); ?></button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="bus-stops-wrapper">
            <section class="bgLight">
                <div>
                    <label><?php esc_html_e('Boarding Point Settings', 'bus-booking-manager'); ?></label><br>
                    <span><?php echo esc_html__('Configure ' . $cpt_label . ' Boarding Point', 'bus-booking-manager'); ?></span>
                </div>
            </section>
            
            <section>
                <div style="margin:auto;">
                    <table id="repeatable-fieldset-bp-one" width="100%">
                        <thead>
                            <tr>
                                <th><?php esc_html_e('Boarding Point', 'bus-booking-manager'); ?></th>
                                <th><?php esc_html_e('Time', 'bus-booking-manager'); ?></th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody class="boarding-point">
                            <?php if ($wbbm_bus_bp) :
                                $count = 0;
                                foreach ($wbbm_bus_bp as $field) { ?>
                                    <tr>
                                        <td align="center">
                                            <div class="wbbm_bus_route_select">
                                                <span class="wbbm_bus_route_icon wbbm_bus_route_icon1"> 
                                                    <img src="<?php echo esc_url(WBTM_PLUGIN_URL . 'images/bus_route_map.png'); ?>" /> 
                                                </span>
                                                <select name="wbbm_bus_bp_stops_name[]" class='seat_type bus_stop_add_option wbbm_bus_stops_route'>
                                                    <option value=""><?php esc_html_e('Please Select', 'bus-booking-manager'); ?></option>
                                                    <?php foreach ($terms as $term) { ?>
                                                        <option data-term_id="<?php echo esc_attr($term->term_id); ?>" value="<?php echo esc_attr($term->name); ?>" <?php selected($term->name, $field['wbbm_bus_bp_stops_name']); ?>> 
                                                            <?php echo esc_html($term->name); ?> 
                                                        </option>
                                                    <?php } ?>
                                                </select>
                                            </div>
                                        </td>
                                        <td align="center">
                                            <div class="wbbm_bus_route_time">
                                                <span class="wbbm_bus_route_icon wbbm_bus_route_icon2">
                                                    <img src="<?php echo esc_url(WBTM_PLUGIN_URL . 'images/bus_route_clock.png'); ?>"/>
                                                </span>
                                                <input type="text" data-clocklet name='wbbm_bus_bp_start_time[]' value="<?php echo isset($field['wbbm_bus_bp_start_time']) ? esc_attr($field['wbbm_bus_bp_start_time']) : ''; ?>" class="text" placeholder="15:00">
                                            </div>
                                        </td>
                                        <td align="center">
                                            <a class="button remove-bp-row" href="#"><i class="fas fa-minus-circle"></i><?php esc_html_e('Remove', 'bus-booking-manager'); ?></a>
                                        </td>
                                    </tr>
                            <?php $count++; } endif; ?>

                            <!-- empty hidden one for jQuery -->
                            <tr class="empty-row-bp screen-reader-text">
                                <td align="center">
                                    <div class="wbbm_bus_route_select">
                                        <span class="wbbm_bus_route_icon wbbm_bus_route_icon1">
                                            <img src="<?php echo esc_url(WBTM_PLUGIN_URL . 'images/bus_route_map.png'); ?>"/>
                                        </span>
                                        <?php echo wbbm_get_bus_stops_list('wbbm_bus_bp_stops_name[]', 'bus_stop_add_option wbbm_bus_stops_route'); ?>
                                    </div>
                                </td>
                                <td align="center">
                                    <div class="wbbm_bus_route_time">
                                        <span class="wbbm_bus_route_icon wbbm_bus_route_icon2">
                                            <img src="<?php echo esc_url(WBTM_PLUGIN_URL . 'images/bus_route_clock.png'); ?>"/>
                                        </span>
                                        <input type="text" data-clocklet name='wbbm_bus_bp_start_time[]' value="" class="text" placeholder="15:00">
                                    </div>
                                </td>
                                <td align="center">
                                    <a class="button remove-bp-row" href="#"><i class="fas fa-minus-circle"></i><?php esc_html_e('Remove', 'bus-booking-manager'); ?></a>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <p>
                        <a id="add-bp-row" class="button" href="#"><i class="fas fa-plus"></i><?php esc_html_e('Add More Boarding Point', 'bus-booking-manager'); ?></a>
                    </p>
                </div>
            </section>
        </div>
        
        <div class="bus-stops-wrapper">
            <section class="bgLight">
                <div>
                    <label><?php esc_html_e('Dropping Point Settings', 'bus-booking-manager'); ?></label><br>
                    <span><?php echo esc_html__('Configure ' . $cpt_label . ' dropping point.', 'bus-booking-manager'); ?></span>
                </div>
            </section>
            
            <section>
                <div style="margin: auto;">
                    <table id="repeatable-fieldset-faq-one" width="100%">
                        <thead>
                            <tr>
                                <th><?php esc_html_e('Dropping Point', 'bus-booking-manager'); ?></th>
                                <th><?php esc_html_e('Time', 'bus-booking-manager'); ?></th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody class="dropping-point">
                            <?php if ($wbtm_bus_next_stops) :
                                $count = 0;
                                foreach ($wbtm_bus_next_stops as $field) { ?>
                                    <tr>
                                        <td align="center">
                                            <div class="wbbm_bus_route_select">
                                                <span class="wbbm_bus_route_icon wbbm_bus_route_icon1">
                                                    <img src="<?php echo esc_url(WBTM_PLUGIN_URL . 'images/bus_route_map.png'); ?>"/>
                                                </span>
                                                <select name="wbbm_bus_next_stops_name[]" class='seat_type bus_stop_add_option wbbm_bus_stops_route'>
                                                    <option value=""><?php esc_html_e('Please Select', 'bus-booking-manager'); ?></option>
                                                    <?php foreach ($terms as $term) { ?>
                                                        <option data-term_id="<?php echo esc_attr($term->term_id); ?>" value="<?php echo esc_attr($term->name); ?>" <?php selected($term->name, $field['wbbm_bus_next_stops_name']); ?>> 
                                                            <?php echo esc_html($term->name); ?> 
                                                        </option>
                                                    <?php } ?>
                                                </select>
                                            </div>
                                        </td>
                                        <td align="center">
                                            <div class="wbbm_bus_route_time">
                                                <span class="wbbm_bus_route_icon wbbm_bus_route_icon2">
                                                    <img src="<?php echo esc_url(WBTM_PLUGIN_URL . 'images/bus_route_clock.png'); ?>"/>
                                                </span>
                                                <input type="text" data-clocklet name='wbbm_bus_next_end_time[]' value="<?php echo isset($field['wbbm_bus_next_end_time']) ? esc_attr($field['wbbm_bus_next_end_time']) : ''; ?>" class="text" placeholder="15:00">
                                            </div>
                                        </td>
                                        <td align="center">
                                            <a class="button remove-faq-row" href="#"><i class="fas fa-minus-circle"></i><?php esc_html_e('Remove', 'bus-booking-manager'); ?></a>
                                        </td>
                                    </tr>
                            <?php $count++; } endif; ?>

                            <!-- empty hidden one for jQuery -->
                            <tr class="empty-row-faq screen-reader-text">
                                <td align="center">
                                    <div class="wbbm_bus_route_select">
                                        <span class="wbbm_bus_route_icon wbbm_bus_route_icon1">
                                            <img src="<?php echo esc_url(WBTM_PLUGIN_URL . 'images/bus_route_map.png'); ?>"/>
                                        </span>
                                        <?php echo wbbm_get_bus_stops_list('wbbm_bus_next_stops_name[]', 'bus_stop_add_option wbbm_bus_stops_route'); ?>
                                    </div>
                                </td>
                                <td align="center">
                                    <div class="wbbm_bus_route_time">
                                        <span class="wbbm_bus_route_icon wbbm_bus_route_icon2">
                                            <img src="<?php echo esc_url(WBTM_PLUGIN_URL . 'images/bus_route_clock.png'); ?>"/>
                                        </span>
                                        <input type="text" data-clocklet name='wbbm_bus_next_end_time[]' value="" class="text" placeholder="15:00">
                                    </div>
                                </td>
                                <td align="center">
                                    <a class="button remove-faq-row" href="#"><i class="fas fa-minus-circle"></i><?php esc_html_e('Remove', 'bus-booking-manager'); ?></a>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <p>
                        <a id="add-faq-row" class="button" href="#"><i class="fas fa-plus"></i><?php esc_html_e('Add More Dropping Point', 'bus-booking-manager'); ?></a>
                    </p>
                </div>
            </section>
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

</div>
