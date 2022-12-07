
<div class="mp_tab_item" data-tab-item="#wbtm_pickuppoint">
    <h3><?php echo $cpt_label.' '. __('Pickup Point:', 'bus-booking-manager'); ?></h3>
    <hr />



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

</div>