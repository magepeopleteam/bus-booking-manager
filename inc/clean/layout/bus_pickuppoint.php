<div class="mp_tab_item" data-tab-item="#wbtm_pickuppoint">
    <h3><?php echo $cpt_label.' '. __('Pickup Point:', 'bus-booking-manager'); ?></h3>
    <h5 class="dFlex mpStyle">
        <span class="pb-10"><b class="ra-enable-button"><?php _e('Enable pickup point :', 'bus-ticket-booking-with-seat-reservation'); ?></b>
            <label class="roundSwitchLabel">
            <input id="pickup-point-control" name="show_pickup_point" <?php echo ($show_pickup_point == "yes" ? " checked" : ""); ?> value="yes" type="checkbox">
            <span class="roundSwitch" data-collapse-target="#ttbm_display_related"></span>
        </label>
        </span>

        <p><?php _e('Do you have multiple pickup point for single boarding point then enable this to add pickup point ', 'bus-ticket-booking-with-seat-reservation'); ?></p>
    </h5>


    <hr />

    <div style="display: <?php echo ($show_pickup_point == "yes" ? "block" : "none"); ?>" id="pickup-point">

        <div class="mpStyle">
            <div class="mpPopup" data-popup="#wbtm_pickup_popup">
                <div class="popupMainArea">
                    <div class="popupHeader">
                        <h4>
                            <?php esc_html_e( 'Add new Pickup', 'bus-ticket-booking-with-seat-reservation' ); ?>
                        </h4>
                        <span class="fas fa-times popupClose"></span>
                    </div>
                    <div class="popupBody pickup-form">
                        <h6 class="textSuccess success_text" style="display: none;"><?php esc_html_e( 'Added Succesfully', 'bus-ticket-booking-with-seat-reservation' ); ?></h6>
                        <label>
                            <span class="w_200"><?php esc_html_e( 'Name:', 'bus-ticket-booking-with-seat-reservation' ); ?></span>
                            <input type="text"  class="formControl" id="pickup_name">
                        </label>
                        <p class="name_required"><?php esc_html_e( 'Name is required', 'bus-ticket-booking-with-seat-reservation' ); ?></p>

                        <label class="mT">
                            <span class="w_200"><?php esc_html_e( 'Description:', 'bus-ticket-booking-with-seat-reservation' ); ?></span>
                            <textarea  id="pickup_description" rows="5" cols="50" class="formControl"></textarea>
                        </label>

                    </div>
                    <div class="popupFooter">
                        <div class="buttonGroup">
                            <button class="_themeButton submit-pickup" type="button"><?php esc_html_e( 'Save', 'bus-ticket-booking-with-seat-reservation' ); ?></button>
                            <button class="_warningButton submit-pickup close_popup" type="button"><?php esc_html_e( 'Save & Close', 'bus-ticket-booking-with-seat-reservation' ); ?></button>
                        </div>
                    </div>
                </div>

            </div>
            <div class="ra-text-center">
                <button type="button" class="_dButton_xs_bgBlue ra-picup-point-button" data-target-popup="#wbtm_pickup_popup">
                    <span class="fas fa-plus-square"></span>
                    Add new pickup point
                </button>
                <p class="ra-stopage-desc"><?php esc_html_e( "", 'bus-ticket-booking-with-seat-reservation' ); ?></p>
            </div>

        </div>

        <div class="wbbm_bus_pickpint_wrapper">
            <div class="wbbm_left_col">
                <div class="wbbm_field_group <?php echo $boarding_points_class ?>">
                    <select name="wbbm_pick_boarding" id="wbbm_pick_boarding" class="ra_pick_boarding" style="border: 1px solid rgb(126, 137, 147); color: rgb(138, 195, 74);">
                    </select>
                    <button class="wbbm_add_pickpoint_this_city" id="wbbm_add_pickpoint_this_city">
                        <?php _e('Add Pickup point', 'bus-booking-manager'); ?> <i class="fas fa-arrow-right"></i>
                    </button>
                </div>
            </div>
            <?php $selected_city_pickpoints = get_post_meta($post->ID, 'wbbm_pickpoint_selected_city', true); ?>

            <div class="wbbm_right_col <?php echo($selected_city_pickpoints == '' ? 'all-center' : ''); ?>">
                <div id="wbbm_pickpoint_selected_city">
                    <?php if ($selected_city_pickpoints != '') {
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
                                                <select class="pickup_add_option" name="wbbm_selected_pickpoint_name_<?php echo $single; ?>[]">
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
                        '<select class="pickup_add_option" name="wbbm_selected_pickpoint_name_' + get_boarding_point + '[]">' +
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

    </div>



</div>