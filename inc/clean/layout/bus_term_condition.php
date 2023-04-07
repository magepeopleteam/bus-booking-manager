<div class="mp_tab_item" data-tab-item="#wbmm_bus_term_condition" style="display: none;">

    <h3 class="wbbm_mp_tab_item_heading"><img src="<?php echo WBTM_PLUGIN_URL .'images/bus_arrow_left.png';?>"/><?php echo $cpt_label.' '. __('Terms & Conditions:', 'bus-booking-manager'); ?></h3>

    <div class="mp_tab_item_inner_wrapper">

        <label>
            <span class="min_200">Terms & Conditions</span>
            <?php
            $settings = [
                'wpautop'       => false,
                'media_buttons' => false,
                'textarea_name' =>  'term_condition',
                'tabindex'      => '323',
                'editor_height' => 200,
                'editor_css'    => '',
                'editor_class'  => '',
                'teeny'         => false,
                'dfw'           => false,
                'tinymce'       => true,
                'quicktags'     => true
            ];
            wp_editor( $wbbm_term_condition, 'term_condition_'.uniqid(), $settings );
            ?>
        </label>



    </div>


</div>


