<div class="mp_tab_item" data-tab-item="#wbmm_bus_features" style="display: none;">

    <h3 class="wbbm_mp_tab_item_heading"><img src="<?php echo WBTM_PLUGIN_URL .'images/bus_arrow_left.png';?>"/><?php echo $cpt_label.' '. __('Features:', 'bus-booking-manager'); ?></h3>

    <div class="mp_tab_item_inner_wrapper">
        <div class="col-md-6">
            <div class="mpStyle">
                <?php
                    $wbbm_features_name = array();
                    if($wbbm_features) {
                        foreach($wbbm_features as $feature) {
                            $wbbm_features_name[] = get_term($feature)->name;
                        }
                    }
                ?>


                <div data-collapse="#ttbm_display_include_service" class="" style="display: block;">
                    <div class="groupCheckBox">
                        <label class="dNone">
                            <input type="hidden" name="ttbm_service_included_in_price" value="<?php echo ($wbbm_features_name ? implode(',', $wbbm_features_name) : '') ?>">
                        </label>

                        <div class="features">
                            <?php  foreach ($feature_terms as $feature_term){ ?>
                                <p>
                                    <label class="customCheckboxLabel">
                                        <input <?php echo (in_array($feature_term->term_id,$wbbm_features))?'checked':'' ?> type="checkbox" name="wbbm_features[<?php echo $feature_term->term_id ?>]" data-checked="<?php echo $feature_term->name ?>" value="<?php echo $feature_term->term_id ?>">
                                        <span class="customCheckbox"><span class="mR_xs <?php echo get_term_meta($feature_term->term_id, 'feature_icon', true) ?>"></span><?php echo $feature_term->name ?></span>
                                    </label>
                                </p>
                            <?php } ?>
                        </div>
                    </div>
                </div>



                <div class="mpPopup" data-popup="#wbtm_feature_popup">
                    <div class="popupMainArea">
                        <div class="popupHeader">
                            <h4>
                                Add New Feature
                            </h4>
                            <span class="fas fa-times popupClose"></span>
                        </div>
                        <div class="popupBody bus-feature">
                            <h6 class="textSuccess success_text" style="display: none;">Added Succesfully</h6>
                            <label>
                                <span class="w_200">Name:</span>
                                <input type="text" class="formControl" id="bus_feature">
                            </label>
                            <p class="name_required">Name is required</p>

                            <label class="mT">
                                <span class="w_200">Description:</span>
                                <textarea id="feature_description" rows="5" cols="50" class="formControl"></textarea>
                            </label>

                            <label for="wbbm_feature_icon">Feature Icon</label>

                            <div id="field-wrapper-wbbm_feature_icon" class="wbtm_feature field-wrapper field-icon-wrapper field-icon-wrapper-wbbm_feature_icon">
                                <div class="mp_input_add_icon">
                                    <button type="button" class="mp_input_add_icon_button dButton_xs ">
                                        <input type="hidden" id="feature_icon" name="wbbm_feature_icon" placeholder="" value="fas fa-forward">
                                        <span class="fas fa-forward" data-empty-text="Add Icon"></span>
                                        <span class="fas fa-times remove_input_icon active " title="Remove Icon"></span>
                                    </button>
                                </div>
                            </div>

                            <p class="description">Please select a suitable icon for this feature</p>

                            <?php

                            all_font_awesome();

                            ?>


                        </div>
                        <div class="popupFooter">
                            <div class="buttonGroup">
                                <button class="_themeButton submit-feature" type="button">Save</button>
                                <button class="_warningButton submit-feature close_popup" type="button">Save &amp; Close</button>
                            </div>
                        </div>
                    </div>

                </div>
                <br>
                <button type="button" class="_dButton_xs_bgBlue wbtm_route_add_new_bus_btn" data-target-popup="#wbtm_feature_popup">
                    <i class="fas fa-plus"></i>
                    Add New Feature
                </button>
            </div>

        </div>


    </div>


</div>


