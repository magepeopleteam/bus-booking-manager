<!-- start offday-onday tab content -->
<div class="mpStyle  mp_tab_item" data-tab-item="#wbmm_bus_features" style="display: none;">
    <div class="tabsItem ttbm_settings_feature active" data-tabs="#ttbm_settings_feature" style="display: block;">
        <div class="mtb ttbm_features_table">
            <table>
                <thead>
                <tr>
                    <td>

                        <h3><?php echo $cpt_label.' '. __('Features:', 'bus-booking-manager'); ?></h3>
                    </td>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td>
                        <div data-collapse="#ttbm_display_include_service" class="" style="display: block;">
                            <div class="groupCheckBox">
                                <label class="dNone">
                                    <input type="text" name="ttbm_service_included_in_price" value="">
                                </label>

                                <?php foreach ($feature_terms as $feature_term){ ?>

                                <label class="customCheckboxLabel">
                                    <input type="checkbox" data-checked="<?php echo $feature_term->term_id ?>">
                                    <span class="customCheckbox"><span class="mR_xs <?php echo get_term_meta($feature_term->term_id, '_pagetitle', true) ?>"></span><?php echo $feature_term->name ?></span>
                                </label>

                                <?php } ?>

                            </div>
                        </div>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
        <button type="button" class="_dButton_bgBlue" data-target-popup="add_new_feature_popup">
            <span class="fas fa-plus-square"></span>
            Create New Feature				</button>
        <p class="ttbm_description">
            <span class="fas fa-info-circle"></span>
            To include or exclude a feature from your tour, please select it from the list below. To create a new feature, go to the Tour page.				</p>
        <div class="mpPopup" data-popup="add_new_feature_popup">
            <div class="popupMainArea">
                <div class="popupHeader">
                    <h4>
                        Add New Feature								<p class="_textSuccess_ml_dNone ttbm_success_info"><span class="fas fa-check-circle mR_xs"></span>Feature is added successfully.</p>
                    </h4>
                    <span class="fas fa-times popupClose"></span>
                </div>
                <div class="popupBody ttbm_feature_form_area"></div>
                <div class="popupFooter">
                    <div class="buttonGroup">
                        <button class="_themeButton ttbm_new_feature_save" type="button">Save</button>
                        <button class="_warningButton ttbm_new_feature_save_close" type="button">Save &amp; Close</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>