<?php

if (!defined('ABSPATH')) {
    die;
}

?>

<div class="mp_tab_item" data-tab-item="#wbmm_bus_features" style="display: none;">

<h3 class="wbbm_mp_tab_item_heading"> 
    <?php echo esc_html($cpt_label) . ' ' . esc_html__('Features Settings', 'bus-booking-manager'); ?> 
</h3>
<p><?php
/* translators: %s post type general name */
printf(esc_html__('Here you can add features for %s', 'bus-booking-manager'), esc_html($cpt_label)); ?></p>

<div class="mp_tab_item_inner_wrapper">
    <div class="col-md-6">
        <section class="bgLight">
            <div>
                <label><?php echo esc_html($cpt_label) . ' ' . esc_html__('Features', 'bus-booking-manager'); ?></label>
                <br>
                <span>
                    <?php echo esc_html__('Add Features', 'bus-booking-manager'); ?>
                </span>
            </div>
        </section>
        <div class="mpStyle">
            <?php
            $wbbm_features_name = array();
            if (!empty($wbbm_features)) {
                foreach ($wbbm_features as $wbbm_feature) {
                    $term = get_term($wbbm_feature);
                    if ($term && !is_wp_error($term)) {
                        $wbbm_features_name[] = sanitize_text_field($term->name);
                    }
                }
            }
            ?>

            <section>
                <div data-collapse="#ttbm_display_include_service" class="" style="display: block;">
                    <div class="groupCheckBox">
                        <label class="dNone">
                            <input type="hidden" name="ttbm_service_included_in_price" value="<?php echo esc_attr(implode(',', $wbbm_features_name)); ?>">
                        </label>

                        <div class="features">
                            <?php foreach ($feature_terms as $wbbm_feature_term) { ?>
                                <p>
                                    <label class="customCheckboxLabel">
                                        <input <?php checked(in_array($wbbm_feature_term->term_id, $wbbm_features)); ?> type="checkbox" name="wbbm_features[<?php echo esc_attr($wbbm_feature_term->term_id); ?>]" data-checked="<?php echo esc_attr($wbbm_feature_term->name); ?>" value="<?php echo esc_attr($wbbm_feature_term->term_id); ?>">
                                        <span class="customCheckbox">
                                            <span class="mR_xs <?php echo esc_attr(get_term_meta($wbbm_feature_term->term_id, 'feature_icon', true)); ?>"></span>
                                            <?php echo esc_html($wbbm_feature_term->name); ?>
                                        </span>
                                    </label>
                                </p>
                            <?php } ?>
                        </div>
                    </div>
                    <br>
                    <button type="button" class="_dButton_xs_bgBlue wbtm_route_add_new_bus_btn" data-target-popup="#wbtm_feature_popup">
                        <i class="fas fa-plus"></i>
                        <?php echo esc_html__('Add New Feature', 'bus-booking-manager'); ?>
                    </button>
                </div>
            </section>

            <div class="mpPopup" data-popup="#wbtm_feature_popup">
                <div class="popupMainArea">
                    <div class="popupHeader">
                        <h4><?php echo esc_html__('Add New Feature', 'bus-booking-manager'); ?></h4>
                        <span class="fas fa-times popupClose"></span>
                    </div>
                    <div class="popupBody bus-feature">
                        <h6 class="textSuccess success_text" style="display: none;"><?php echo esc_html__('Added Successfully', 'bus-booking-manager'); ?></h6>
                        <label>
                            <span class="w_200"><?php echo esc_html__('Name:', 'bus-booking-manager'); ?></span>
                            <input type="text" class="formControl" id="bus_feature">
                        </label>
                        <p class="name_required"><?php echo esc_html__('Name is required', 'bus-booking-manager'); ?></p>

                        <label class="mT">
                            <span class="w_200"><?php echo esc_html__('Description:', 'bus-booking-manager'); ?></span>
                            <textarea id="feature_description" rows="5" cols="50" class="formControl"></textarea>
                        </label>

                        <label for="wbbm_feature_icon"><?php echo esc_html__('Feature Icon', 'bus-booking-manager'); ?></label>

                        <div id="field-wrapper-wbbm_feature_icon" class="wbtm_feature field-wrapper field-icon-wrapper field-icon-wrapper-wbbm_feature_icon">
                            <div class="mp_input_add_icon">
                                <button type="button" class="mp_input_add_icon_button dButton_xs ">
                                    <input type="hidden" id="feature_icon" name="wbbm_feature_icon" placeholder="" value="<?php echo esc_attr('fas fa-forward'); ?>">
                                    <span class="fas fa-forward" data-empty-text="<?php echo esc_attr__('Add Icon', 'bus-booking-manager'); ?>"></span>
                                    <span class="fas fa-times remove_input_icon active " title="<?php echo esc_attr__('Remove Icon', 'bus-booking-manager'); ?>"></span>
                                </button>
                            </div>
                        </div>

                        <p class="description"><?php echo esc_html__('Please select a suitable icon for this feature', 'bus-booking-manager'); ?></p>

                        <?php wbbm_all_font_awesome(); ?>

                    </div>
                    <div class="popupFooter">
                        <div class="buttonGroup">
                            <button class="_themeButton submit-feature" type="button"><?php echo esc_html__('Save', 'bus-booking-manager'); ?></button>
                            <button class="_warningButton submit-feature close_popup" type="button"><?php echo esc_html__('Save & Close', 'bus-booking-manager'); ?></button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</div>
