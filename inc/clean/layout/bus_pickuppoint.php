<div class="mp_tab_item" data-tab-item="#wbtm_pickuppoint">
    <h3 class="wbbm_mp_tab_item_heading"><?php echo  esc_html_e('Pickup Point Settings', 'bus-booking-manager'); ?></h3>
    <p><?php echo  esc_html_e('Here you can set pickup point', 'bus-booking-manager'); ?></p>

    <div class="wbbm_pickuppoint_inner_wrapper">
        <section class="bgLight">
            <div>
                <label for=""><?php esc_html_e('Pickup Point Settings', 'bus-booking-manager'); ?></label>
                <br>
                <span><?php esc_html_e('Here you can configure pickup point here.', 'bus-booking-manager'); ?></span>
            </div>
        </section>
        <section>
            <div>
                <label for=""><?php esc_html_e('On/Off Pickup Point', 'bus-booking-manager'); ?></label>
                <br>
                <span><?php esc_html_e('Turn On/Off pickup point. By default on.', 'bus-booking-manager'); ?></span>
            </div>
            <label class="switch">
                <input id="pickup-point-control" name="show_pickup_point" <?php echo esc_attr(($show_pickup_point == "yes" ? " checked" : "")); ?> value="yes" type="checkbox">
                <span class="slider round" data-collapse-target="#ttbm_display_related"></span>
            </label>
        </section>

        <div style="display: <?php echo esc_attr(($show_pickup_point == "yes" ? "block" : "none")); ?>" id="pickup-point">
            <section>
                <div>
                    <label><?php esc_html_e( "Create A New Point", 'bus-booking-manager' ); ?></label>
                    <br>
                    <span class="ra-stopage-desc"><?php esc_html_e( "You can add new pickup point from here that is not listed in pickup point.", 'bus-booking-manager' ); ?></span>
                </div>
                <div>
                    <div class="mpStyle">
                        <button type="button" class="_dButton_xs_bgBlue ra-picup-point-button" data-target-popup="#wbtm_pickup_popup">
                            <i class="fas fa-plus"></i>
                            <?php esc_html_e( "Create new pickup point", 'bus-booking-manager' ); ?>
                        </button>
                    </div>
                </div>
            </section>
            <div class="mpStyle">
                <div class="mpPopup" data-popup="#wbtm_pickup_popup">
                    <div class="popupMainArea">
                        <div class="popupHeader">
                            <h4>
                                <?php esc_html_e( 'Add new Pickup', 'bus-booking-manager' ); ?>
                            </h4>
                            <span class="fas fa-times popupClose"></span>
                        </div>
                        <div class="popupBody pickup-form">
                            <h6 class="textSuccess success_text" style="display: none;"><?php esc_html_e( 'Added Succesfully', 'bus-booking-manager' ); ?></h6>
                            <h6 class="textduplicate duplicate_text" style="display: none;color: red"><?php esc_html_e( 'This pickup point already exists', 'bus-booking-manager' ); ?></h6>
                            <label>
                                <span class="w_200"><?php esc_html_e( 'Name:', 'bus-booking-manager' ); ?></span>
                                <input type="text"  class="formControl" id="pickup_name">
                            </label>
                            <p class="name_required"><?php esc_html_e( 'Name is required', 'bus-booking-manager' ); ?></p>

                            <label class="mT">
                                <span class="w_200"><?php esc_html_e( 'Description:', 'bus-booking-manager' ); ?></span>
                                <textarea  id="pickup_description" rows="5" cols="50" class="formControl"></textarea>
                            </label>

                        </div>
                        <div class="popupFooter">
                            <div class="buttonGroup">
                                <button class="_themeButton submit-pickup" type="button"><?php esc_html_e( 'Save', 'bus-booking-manager' ); ?></button>
                                <button class="_warningButton submit-pickup close_popup" type="button"><?php esc_html_e( 'Save & Close', 'bus-booking-manager' ); ?></button>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <div class="wbbm_bus_pickpint_wrapper">
                
                <section>
                    <div>
                        <label><?php esc_html_e('Pickup Point', 'bus-booking-manager'); ?></label>
                        <br>
                        <span><?php esc_html_e('Select a location as a pickup point.', 'bus-booking-manager'); ?></span>
                    </div>
                    <div class="wbbm_field_group <?php echo esc_attr($boarding_points_class) ?>">
                        <?php
                        $selected_city_pickpoints = get_post_meta($post->ID, 'wbbm_pickpoint_selected_city', true);
                        ?>
                        <input type="hidden" name="select_city_pickpoints" id="select_city_pickpoints" value="<?php echo esc_attr($selected_city_pickpoints) ?>">
                        
                        <span class="wbbm_bus_route_icon wbbm_bus_route_icon1"><img src="<?php echo esc_url(WBTM_PLUGIN_URL .'images/bus_route_map.png');?>"/></span>
                        <select name="wbbm_pick_boarding" id="wbbm_pick_boarding" class="ra_pick_boarding">
                        </select>
                        <button class="wbbm_add_pickpoint_this_city" id="wbbm_add_pickpoint_this_city">
                            <i class="fas fa-plus"></i> <?php esc_html_e('Set pickup point', 'bus-booking-manager'); ?>
                        </button>
                    </div>
                </section>
                <section class="wbbm_right_col <?php echo($selected_city_pickpoints == '' ? 'all-center' : ''); ?>" >
                    <div id="wbbm_pickpoint_selected_city" style="margin:auto;">
                        <?php if ($selected_city_pickpoints != '') {
                            $selected_city_pickpoints = explode(',', $selected_city_pickpoints);
                            foreach ($selected_city_pickpoints as $single) {
                                $city_name = $single ? ucfirst(str_replace('_', ' ', $single)) : '';
                                $get_pickpoints_data = get_post_meta($post->ID, 'wbbm_selected_pickpoint_name_' . strtolower($single), true);

                                ?>
                                <div class="wbbm_selected_city_item">
                                    <span class="remove_city_for_pickpoint"><i class="fas fa-trash-alt"></i></span>
                                    <h4 class="wbbm_pickpoint_title"><?php echo esc_html_e($city_name); ?></h4>
                                    <input type="hidden" name="wbbm_pickpoint_selected_city[]" value="<?php echo esc_attr($single); ?>">
                                    <div class="pickpoint-adding-wrap-main">
                                        <div class="pickpoint-adding-wrap">
                                            <?php


                                            if ($get_pickpoints_data) {


                                                foreach ($get_pickpoints_data as $pickpoint) : ?>

                                                    <div class="pickpoint-adding">
                                                        <div class="pickpoint-adding-col">
                                                        <span class="wbbm_bus_route_icon wbbm_bus_route_icon1"> <img src="<?php echo esc_url(WBTM_PLUGIN_URL . 'images/bus_route_map.png'); ?>"/> </span>
                                                            <select class="pickup_add_option" name="wbbm_selected_pickpoint_name_<?php echo esc_attr($single); ?>[]">
                                                                <?php
                                                                if ($bus_pickpoints) {
                                                                    foreach ($bus_pickpoints as $bus_pickpoint) {
                                                                        echo '<option value="' . esc_html_e($bus_pickpoint->name) . '" ' . esc_attr(($bus_pickpoint->name == $pickpoint['pickpoint'] ? "selected=selected" : '')) . '>' . esc_attr($bus_pickpoint->name) . '</option>';
                                                                    }
                                                                }
                                                                ?>
                                                            </select>
                                                        </div>
                                                        <div class="pickpoint-adding-col">
                                                            <span class="wbbm_bus_route_icon wbbm_bus_route_icon2"><img src="<?php echo esc_url(WBTM_PLUGIN_URL .'images/bus_route_clock.png');?>"/></span>
                                                            <input type="text" data-clocklet name="wbbm_selected_pickpoint_time_<?php echo esc_attr($single); ?>[]" value="<?php echo esc_attr($pickpoint['time']); ?>" placeholder="15:00">
                                                        </div>
                                                        <button class="wbbm_remove_pickpoint"><i class="fas fa-minus-circle"></i> Remove
                                                        </button>
                                                    </div>

                                                <?php
                                                endforeach;
                                            } ?>
                                        </div>

                                        <button class="wbbm_add_more_pickpoint"><i class="fas fa-plus"></i>
                                            <?php esc_html_e('Add more', 'bus-booking-manager'); ?>
                                        </button>
                                    </div>
                                </div>
                                <?php
                            }

                        } else {
                            echo '<p class="blank-pickpoint" style="color: #FF9800;font-weight: 700;text-align:left">' . esc_html_e('No pickup point added yet!', 'bus-booking-manager') . '</p>';
                        }
                        ?>
                    </div>
                </section>
            </div>

            <div class="pickuppoints_first" style="display: none">
                <?php echo $pickpoints; ?>
            </div>




            <script>
                jQuery(document).ready(function($) {

                    // Select Boarding point and hit add


                    $(document).on('click','.wbbm_add_pickpoint_this_city',function (e){

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
                            $(".ra_pick_boarding[value="+get_boarding_point+"]").remove();
                        }

                        var get_boarding_point_name = $('#wbbm_pick_boarding option:selected').text();
                        $('#wbbm_pick_boarding option:selected').remove();

                        var pickuppoints_first = $('.pickuppoints_first').html();

                        var html =
                            '<div class="wbbm_selected_city_item"><span class="remove_city_for_pickpoint"><i class="fas fa-trash-alt"></i></span>' +
                            '<h4 class="wbbm_pickpoint_title">' + get_boarding_point_name + '</h4>' +
                            '<input type="hidden" name="wbbm_pickpoint_selected_city[]" value="' + get_boarding_point +
                            '">' +
                            '<div class="pickpoint-adding-wrap-main"><div class="pickpoint-adding-wrap"><div class="pickpoint-adding">' +
                            '<div class="pickpoint-adding-col">' +
                            '<span class="wbbm_bus_route_icon wbbm_bus_route_icon1"><img src="<?php echo esc_url(WBTM_PLUGIN_URL .'images/bus_route_map.png');?>"/></span>' +
                            '<select class="pickup_add_option" name="wbbm_selected_pickpoint_name_' + get_boarding_point + '[]">' + pickuppoints_first +
                            '</select>' +
                            '</div>'+
                            '<div class="pickpoint-adding-col">' +
                            '<span class="wbbm_bus_route_icon wbbm_bus_route_icon2"><img src="<?php echo esc_url(WBTM_PLUGIN_URL .'images/bus_route_clock.png');?>"/></span>' +
                            '<input type="text" data-clocklet name="wbbm_selected_pickpoint_time_' + get_boarding_point +
                            '[]" placeholder="15:00">' +
                            '</div>'+
                            '<button class="wbbm_remove_pickpoint"><i class="fas fa-minus-circle"></i> Remove</button>' +
                            '</div></div>' +
                            '<button class="wbbm_add_more_pickpoint"><i class="fas fa-plus"></i> <?php esc_html_e("Add more", "bus-booking-manager"); ?></button>' +
                            '</div></div>';



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
        </div>
    </div>



</div>