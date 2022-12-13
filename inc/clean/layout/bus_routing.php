<div class="mp_tab_item" data-tab-item="#wbtm_routing">

    <h3><?php echo $cpt_label.' '. __('Routing:', 'bus-booking-manager'); ?></h3>


    <div class="col-md-6">
        <div class="mpStyle">
            <div class="mpPopup" data-popup="#wbtm_route_popup">
                <div class="popupMainArea">
                    <div class="popupHeader">
                        <h4>
                            <?php esc_html_e( 'Add New Bus Stop', 'bus-ticket-booking-with-seat-reservation' ); ?>
                        </h4>
                        <span class="fas fa-times popupClose"></span>
                    </div>
                    <div class="popupBody bus-stop-form">
                        <h6 class="textSuccess success_text" style="display: none;">Added Succesfully</h6>
                        <label>
                            <span class="w_200"><?php esc_html_e( 'Name:', 'bus-ticket-booking-with-seat-reservation' ); ?></span>
                            <input type="text"  class="formControl" id="bus_stop_name">
                        </label>
                        <p class="name_required"><?php esc_html_e( 'Name is required', 'bus-ticket-booking-with-seat-reservation' ); ?></p>

                        <label class="mT">
                            <span class="w_200"><?php esc_html_e( 'Description:', 'bus-ticket-booking-with-seat-reservation' ); ?></span>
                            <textarea  id="bus_stop_description" rows="5" cols="50" class="formControl"></textarea>
                        </label>

                    </div>
                    <div class="popupFooter">
                        <div class="buttonGroup">
                            <button class="_themeButton submit-bus-stop" type="button"><?php esc_html_e( 'Save', 'bus-ticket-booking-with-seat-reservation' ); ?></button>
                            <button class="_warningButton submit-bus-stop close_popup" type="button"><?php esc_html_e( 'Save & Close', 'bus-ticket-booking-with-seat-reservation' ); ?></button>
                        </div>
                    </div>
                </div>

            </div>
            <button type="button" class="_dButton_xs_bgBlue" data-target-popup="#wbtm_route_popup">
                <span class="fas fa-plus-square"></span>
                Add New Bus Stop
            </button>
            <p class="ra-stopage-desc"><?php esc_html_e( "You can't start routing until you add bus stoppages. If you haven't added any yet, you can do so from here.", 'bus-ticket-booking-with-seat-reservation' ); ?></p>
        </div>

    </div>

    <hr />
    <?php   if ($terms) { ?>
        <div class="bus-stops-wrapper">
            <div class="bus-stops-left-col">
                <h3 class="bus-tops-sec-title"><?php _e('Boarding Point', 'bus-booking-manager'); ?></h3>
                <table id="repeatable-fieldset-bp-one" width="100%"><thead>
                    <tr>
                        <th><?php _e('Boarding Point', 'bus-booking-manager'); ?></th>
                        <th><?php _e('Time', 'bus-booking-manager'); ?></th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody class="boarding-point">
                    <?php if ($wbbm_bus_bp) :
                        $count = 0;
                    foreach ($wbbm_bus_bp as $field) { ?>
                        <tr>
                            <td align="center">
                                <select name="wbbm_bus_bp_stops_name[]" class='seat_type bus_stop_add_option'>
                                    <option value=""><?php _e('Please Select', 'bus-ticket-booking-with-seat-reservation'); ?></option>
                                    <?php foreach ($terms as $term) {?>
                                        <option data-term_id="<?php echo $term->term_id; ?>" value="<?php echo $term->name; ?>" <?php echo ($term->name == $field['wbbm_bus_bp_stops_name'])?'Selected':'' ?>><?php echo $term->name; ?></option>
                                    <?php } ?>
                                </select>
                            </td>
                            <td align="center">
                                <input type="text" data-clocklet name='wbbm_bus_bp_start_time[]' value="<?php if (isset($field['wbbm_bus_bp_start_time']) && $field['wbbm_bus_bp_start_time'] != '') echo esc_attr($field['wbbm_bus_bp_start_time']); ?>" class="text" placeholder="15:00">
                            </td>
                            <td align="center">
                                <a class="button remove-faq-row" href="#"><i class="fas fa-minus-circle"></i>
                                    <?php _e('Remove', 'bus-booking-manager'); ?>
                                </a>
                            </td>
                        </tr>

                        <?php $count++; } endif; ?>


                        <!-- empty hidden one for jQuery -->

                        <tr class="empty-row-bp screen-reader-text">
                            <td align="center"><?php echo wbbm_get_bus_stops_list('wbbm_bus_bp_stops_name[]'); ?></td>
                            <td align="center">
                                <input type="text" data-clocklet name='wbbm_bus_bp_start_time[]' value="" class="text" placeholder="15:00">
                            </td>
                            <td align="center">
                                <a class="button remove-bp-row" href="#"><i class="fas fa-minus-circle"></i><?php _e('Remove', 'bus-booking-manager'); ?></a>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <p>
                    <a id="add-bp-row" class="button" href="#"><i class="fas fa-plus"></i>
                        <?php _e('Add More Boarding Point', 'bus-booking-manager'); ?>
                    </a>
                </p>
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
                    <tbody class="dropping-point">
                    <?php
                    if ($wbtm_bus_next_stops) :
                    $count = 0;
                    foreach ($wbtm_bus_next_stops as $field) {
                    ?>
                        <tr>
                            <td align="center">

                                <select name="wbbm_bus_next_stops_name[]" class='seat_type bus_stop_add_option'>
                                    <option value=""><?php _e('Please Select', 'bus-ticket-booking-with-seat-reservation'); ?></option>
                                    <?php foreach ($terms as $term) {?>
                                        <option data-term_id="<?php echo $term->term_id; ?>" value="<?php echo $term->name; ?>" <?php echo ($term->name == $field['wbbm_bus_next_stops_name'])?'Selected':'' ?>><?php echo $term->name; ?></option>
                                    <?php } ?>
                                </select>

                                <?php //echo wbbm_get_next_bus_stops_list('wbbm_bus_next_stops_name[]', 'wbbm_bus_next_stops_name', 'wbbm_bus_next_stops', $coun); ?>
                            </td>
                            <td align="center"><input type="text" data-clocklet name='wbbm_bus_next_end_time[]'
                                    value="<?php if (isset($field['wbbm_bus_next_end_time']) && $field['wbbm_bus_next_end_time'] != '') echo esc_attr($field['wbbm_bus_next_end_time']); ?>"
                                    class="text" placeholder="15:00"></td>
                            <td align="center"><a class="button remove-faq-row" href="#"><i class="fas fa-minus-circle"></i>
                                    <?php _e('Remove', 'bus-booking-manager'); ?>
                                </a></td>
                        </tr>
                        <?php }
                                        endif;
                                        ?>

                        <!-- empty hidden one for jQuery -->
                        <tr class="empty-row-faq screen-reader-text">
                            <td align="center">
                                <?php echo wbbm_get_bus_stops_list('wbbm_bus_next_stops_name[]'); ?>
                            </td>
                            <td align="center">
                                <input type="text" data-clocklet name='wbbm_bus_next_end_time[]' value="" class="text" placeholder="15:00">
                            </td>
                            <td align="center">
                                <a class="button remove-faq-row" href="#"><i class="fas fa-minus-circle"></i>
                                    <?php _e('Remove', 'bus-booking-manager'); ?>
                                </a>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <p>
                    <a id="add-faq-row" class="button" href="#"><i class="fas fa-plus"></i>
                        <?php _e('Add More Droping Point', 'bus-booking-manager'); ?>
                    </a>
                </p>
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

        <?php } else {
        echo "<div class='wbbm_bus_stops_notice'>".__('Please Enter some bus stops first.', 'bus-booking-manager')." <a class='wbbm_color_white' href='" . get_admin_url() . "edit-tags.php?taxonomy=wbbm_bus_stops&post_type=wbbm_bus'>".__('Click here for bus stops', 'bus-booking-manager')."</a></div>";
    } ?>
</div>