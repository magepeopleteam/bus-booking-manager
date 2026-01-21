<?php
/*
 * Bus Pricing and Routing Configuration
 * Exact match to target plugin design
 */
if (!defined('ABSPATH')) {
    die;
}

if (!class_exists('WBBM_Pricing_Routing')) {
    class WBBM_Pricing_Routing
    {
        public function __construct()
        {
            add_action('wbtm_add_settings_tab_content', [$this, 'tab_content']);
            add_action('wp_ajax_wbtm_reload_pricing', [$this, 'wbtm_reload_pricing']);
        }

        public function tab_content($post_id)
        {
            $full_route_infos = MP_Global_Function::get_post_info($post_id, 'wbbm_route_info', []);
            $bus_stop_lists = MP_Global_Function::get_all_term_data('wbbm_bus_stops');
?>
            <div class="tabsItem wbtm_settings_pricing_routing" data-tabs="#wbtm_settings_pricing_routing">
                <h3 class="pB_xs"><?php esc_html_e('Price And Routing Settings', 'bus-booking-manager'); ?></h3>
                <p style="margin-bottom:0;font-size:15px"><?php esc_html_e('Here you can configure Price And Routing for a bus.', 'bus-booking-manager'); ?></p>
                <div class="">
                    <div class="_dLayout_padding_bgLight">
                        <div class="_dFlex_fdColumn">
                            <label>
                                <?php esc_html_e('Boarding and Dropping Settings', 'bus-booking-manager'); ?>
                            </label>
                            <br>
                            <span><?php esc_html_e('Configure bus stops with boarding and dropping points.', 'bus-booking-manager'); ?></span>
                        </div>
                    </div>
                    <div class="_dLayout_padding">
                        <div class="wbtm_settings_area wbbm_repeater_area">
                            <div class="mp_stop_items wbtm_sortable_area wbtm_item_insert">
                                <?php if (sizeof($full_route_infos) > 0) {
                                    foreach ($full_route_infos as $key => $full_route_info) {
                                        $this->add_stops_item($bus_stop_lists, $full_route_info, $key);
                                    }
                                } ?>
                                <div class="_mB_xs wbtm_item_insert_before"></div>
                            </div>
                            <div class="justifyCenter">
                                <?php $this->add_new_button(esc_html__('Add New Stops', 'bus-booking-manager'), 'wbtm_add_item', '_themeButton_xs_fullHeight'); ?>
                            </div>
                            <!-- create new bus route -->
                            <div class="wbtm_hidden_content">
                                <div class="wbtm_hidden_item">
                                    <?php $this->add_stops_item($bus_stop_lists, [], 0); ?>
                                </div>
                            </div>
                        </div>
                        
                    </div>
                </div>
                <div class="_mT"></div>
                <div class="_dLayout_padding_bgLight ">
                    <div class="_dFlex_fdColumn">
                        <label>
                            <?php esc_html_e('Pricing Settings', 'bus-booking-manager'); ?>
                        </label>
                        <br>
                        <span><?php esc_html_e('Configure prices for different route segments.', 'bus-booking-manager'); ?></span>
                    </div>
                </div>
                <div class="_dLayout_padding">
                    <div class="wbtm_price_setting_area">
                        <?php $this->route_pricing($post_id, $full_route_infos); ?>
                    </div>
                </div>
            </div>
        <?php
        }

        public function add_stops_item($bus_stop_lists, $full_route_info = [], $key = 0)
        {
            $place = array_key_exists('place', $full_route_info) ? $full_route_info['place'] : '';
            $time = array_key_exists('time', $full_route_info) ? $full_route_info['time'] : '';
            $type = array_key_exists('type', $full_route_info) ? $full_route_info['type'] : '';
            $next_day = array_key_exists('next_day', $full_route_info) ? $full_route_info['next_day'] : false;

            $location = '';
            foreach ($bus_stop_lists as $bus_stop) {
                if ($bus_stop == $place) {
                    $location = $place;
                }
            }
        ?>
            <div class="wbtm_remove_area col_12_mB  wbtm_stop_item ">
                <div class="wbbm_stop_item_inner">
                    <div class="_bgLight_dFlex_justifyBetween_alignCenter wbtm_stop_item_header" data-collapse-target="">
                        <?php
                        $location = '';
                        foreach ($bus_stop_lists as $bus_stop) {
                            if ($bus_stop == $place) {
                                $location = $place;
                            }
                        }
                        ?>
                        <div class="col_4 mp_zero">
                            <?php if (empty($location)): ?>
                                <label><?php esc_html_e('Add Stop', 'bus-booking-manager'); ?></label>
                            <?php else: ?>
                                <label><?php echo esc_html($location); ?></label>
                                <span>
                                    <?php echo esc_html(($type == 'bp') ? ' (Bording) ' : ''); ?>
                                    <?php echo esc_html(($type == 'dp') ? ' (Dropping) ' : ''); ?>
                                    <?php echo esc_html(($type == 'both') ? ' (Bording+Dropping) ' : ''); ?>
                                </span>
                            <?php endif; ?>
                        </div>
                        <label class="col_4 _mp_zero _dFlex_alignCenter" style="gap:4px">
                            <?php if ($time): ?>
                                <i class="far fa-clock"></i> <input class="_zeroBorder_mp_zero" type="text" value="<?php echo esc_attr(date('h:i A', strtotime($time))); ?>" readonly>
                            <?php else: ?>
                                <i class="far fa-clock"></i>&nbsp;--:-- --
                            <?php endif; ?>
                        </label>
                        <?php $this->edit_move_remove_button(); ?>
                    </div>
                    <div class="wbtm_stop_item_content" data-collapse="">
                        <div class="_dFlex_justifyCenter_alignCenter ">
                            <div class="col_4 _dFlex_justifyCenter_alignCenter">
                                <label class="_mp_zero _mR"><?php esc_html_e('Stop : ', 'bus-booking-manager'); ?></label>
                                <select name="wbtm_route_place[]" class='formControl max_200 _mL_xs'>
                                    <option selected disabled><?php esc_html_e('Select bus stop', 'bus-booking-manager'); ?></option>
                                    <?php foreach ($bus_stop_lists as $bus_stop) { ?>
                                        <option value="<?php echo esc_attr($bus_stop); ?>" <?php echo esc_attr($bus_stop == $place ? 'selected' : ''); ?>><?php echo esc_html($bus_stop); ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                            <div class="col_4 _dFlex_justifyCenter_alignCenter">
                                <label class="mp_zero"><?php esc_html_e('Time : ', 'bus-booking-manager'); ?></label>
                                <input type="text" data-clocklet="format: hh:mm A" name="wbtm_route_time[]" class='formControl max_200 _mL_xs' value="<?php echo esc_attr($time); ?>" placeholder="hh:mm AM" />
                            </div>
                            <div class="col_4 _dFlex_justifyCenter_alignCenter">
                                <label class="mp_zero"><?php esc_html_e('Type : ', 'bus-booking-manager'); ?></label>
                                <select name="wbtm_route_type[]" class='formControl max_200 _mL_xs wbtm_route_type_select'>
                                    <option selected disabled><?php esc_html_e('Select place type', 'bus-booking-manager'); ?></option>
                                    <option value="bp" <?php echo esc_attr($type == 'bp' ? 'selected' : ''); ?>><?php esc_html_e('Boarding ', 'bus-booking-manager'); ?></option>
                                    <option value="dp" <?php echo esc_attr($type == 'dp' ? 'selected' : ''); ?>><?php esc_html_e('Dropping ', 'bus-booking-manager'); ?></option>
                                    <option value="both" <?php echo esc_attr($type == 'both' ? 'selected' : ''); ?>><?php esc_html_e('Boarding & Dropping', 'bus-booking-manager'); ?></option>
                                </select>
                            </div>
                        </div>
                        <div class="">
                            <div class=" next-day-dropping-checkbox" style="display: <?php echo ($type == 'dp' || $type == 'both') ? 'block' : 'none'; ?>;">
                                <label class="mp_zero"><?php esc_html_e('Next Day Dropping: ', 'bus-booking-manager'); ?></label>
                                <input type="hidden" name="wbtm_route_next_day[<?php echo esc_attr($key); ?>]" value="0" />
                                <input type="checkbox" name="wbtm_route_next_day[<?php echo esc_attr($key); ?>]" value="1" <?php echo esc_attr($next_day ? 'checked' : ''); ?> />
                            </div>
                        </div>
                        <script>
                            jQuery(document).ready(function($) {
                                // Handle showing/hiding checkbox when selecting "Dropping" or "Boarding & Dropping"
                                $('select[name="wbtm_route_type[]"]').on('change', function() {
                                    var type = $(this).val();
                                    var nextDayCheckbox = $(this).closest('.wbtm_stop_item').find('.next-day-dropping-checkbox');
                                    if (type == 'dp' || type == 'both') {
                                        nextDayCheckbox.show();
                                    } else {
                                        nextDayCheckbox.hide();
                                    }
                                });
                                // Trigger the change event on page load to ensure the checkbox visibility is correct
                                $('select[name="wbtm_route_type[]"]').each(function() {
                                    $(this).trigger('change');
                                });
                            });
                        </script>
                    </div>
                </div>
            </div>
            <?php
        }

        public function route_pricing($post_id, $full_route_infos)
        {
            $all_price_info = [];
            if (sizeof($full_route_infos) > 0) {
                $price_infos = MP_Global_Function::get_post_info($post_id, 'wbbm_bus_prices', []);
                foreach ($full_route_infos as $key => $full_route_info) {
                    if ($full_route_info['type'] == 'bp' || $full_route_info['type'] == 'both') {
                        $bp = $full_route_info['place'];
                        $next_infos = array_slice($full_route_infos, $key + 1);
                        if (sizeof($next_infos) > 0) {
                            foreach ($next_infos as $next_info) {
                                if ($next_info['type'] == 'dp' || $next_info['type'] == 'both') {
                                    $dp = $next_info['place'];
                                    $adult_price = '';
                                    $child_price = '';
                                    $infant_price = '';
                                    if (sizeof($price_infos) > 0) {
                                        foreach ($price_infos as $price_info) {
                                            if (strtolower($price_info['wbbm_bus_bp_price_stop']) == strtolower($bp) && strtolower($price_info['wbbm_bus_dp_price_stop']) == strtolower($dp)) {
                                                $adult_price = array_key_exists('wbbm_bus_price', $price_info) && $price_info['wbbm_bus_price'] !== '' ? (float)$price_info['wbbm_bus_price'] : '';
                                                $child_price = array_key_exists('wbbm_bus_price_child', $price_info) && $price_info['wbbm_bus_price_child'] !== '' ? (float)$price_info['wbbm_bus_price_child'] : '';
                                                $infant_price = array_key_exists('wbbm_bus_price_infant', $price_info) && $price_info['wbbm_bus_price_infant'] !== '' ? (float)$price_info['wbbm_bus_price_infant'] : '';
                                                $student_price = array_key_exists('wbbm_bus_price_student', $price_info) && $price_info['wbbm_bus_price_student'] !== '' ? (float)$price_info['wbbm_bus_price_student'] : '';
                                            }
                                        }
                                    }
                                    $all_price_info[] = [
                                        'bp' => $bp,
                                        'dp' => $dp,
                                        'adult_price' => $adult_price,
                                        'child_price' => $child_price,
                                        'infant_price' => $infant_price,
                                        'student_price' => $student_price,
                                    ];
                                }
                            }
                        }
                    }
                }
            }

            if (sizeof($all_price_info) > 0) {
            ?>
                <table>
                    <thead>
                        <tr>
                            <th colspan="2">
                                <div class="_dFlex_justifyBetween ">
                                    <div class="col_5 _textLeft_pL_xs">
                                        <span><?php esc_html_e('Boarding', 'bus-booking-manager'); ?></span>
                                    </div>
                                    <div class="col_5 _textRight_pR_xs">
                                        <span><?php esc_html_e('Dropping', 'bus-booking-manager'); ?></span>
                                    </div>
                                </div>
                            </th>
                            <th><?php echo esc_html(wbbm_get_option('wbbm_adult_text', 'wbbm_label_setting_sec', __('Adult Price', 'bus-booking-manager'))); ?>
                                <sup class="required">*</sup>
                            </th>
                            <th><?php echo esc_html(wbbm_get_option('wbbm_child_text', 'wbbm_label_setting_sec', __('Child Price', 'bus-booking-manager'))); ?></th>
                            <th><?php echo esc_html(wbbm_get_option('wbbm_student_text', 'wbbm_label_setting_sec', __('Student Price', 'bus-booking-manager'))); ?></th>
                            <th><?php echo esc_html(wbbm_get_option('wbbm_infant_text', 'wbbm_label_setting_sec', __('Infant Price', 'bus-booking-manager'))); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($all_price_info as $price_info) { ?>
                            <tr>
                                <td colspan="2">
                                    <div class="d-flex">
                                        <div class="col_5">
                                            <input type="hidden" name="wbtm_price_bp[]" value="<?php echo esc_attr($price_info['bp']); ?>" />
                                            <span><?php echo esc_html($price_info['bp']); ?></span>
                                        </div>
                                        <div class="long-arrow col_2">
                                        </div>
                                        <div class="col_5 _textRight_pR_xs">
                                            <input type="hidden" name="wbtm_price_dp[]" value="<?php echo esc_attr($price_info['dp']); ?>" />
                                            <span><?php echo esc_html($price_info['dp']); ?></span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <label>
                                        <input type="number" pattern="[0-9]*" step="0.01" class="formControl wbtm_price_validation" name="wbtm_adult_price[]" placeholder="Ex: 10" value="<?php echo esc_attr($price_info['adult_price']); ?>" />
                                    </label>
                                </td>
                                <td>
                                    <label>
                                        <input type="number" pattern="[0-9]*" step="0.01" class="formControl wbtm_price_validation" name="wbtm_child_price[]" placeholder="Ex: 10" value="<?php echo esc_attr($price_info['child_price']); ?>" />
                                    </label>
                                </td>
                                <td>
                                    <label>
                                        <input type="number" pattern="[0-9]*" step="0.01" class="formControl wbtm_price_validation" name="wbtm_student_price[]" placeholder="Ex: 10" value="<?php echo esc_attr($price_info['student_price']); ?>" />
                                    </label>
                                </td>
                                <td>
                                    <label>
                                        <input type="number" pattern="[0-9]*" step="0.01" class="formControl wbtm_price_validation" name="wbtm_infant_price[]" placeholder="Ex: 10" value="<?php echo esc_attr($price_info['infant_price']); ?>" />
                                    </label>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            <?php } else { ?>
                <div class="_dLayout_bgWarning_mZero">
                    <h3 style="margin:0"><?php esc_html_e('Please Create Bus route .', 'bus-booking-manager'); ?></h3>
                </div>
            <?php
            }
        }

        public function wbtm_reload_pricing()
        {
            if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'wbbm_admin_ajax_nonce')) {
                wp_send_json_error('Invalid nonce!');
            }
            $post_id = isset($_POST['post_id']) ? sanitize_text_field(wp_unslash($_POST['post_id'])) : '';
            $places = isset($_POST['places']) ? array_map('sanitize_text_field', wp_unslash($_POST['places'])) : [];
            $types = isset($_POST['types']) ? array_map('sanitize_text_field', wp_unslash($_POST['types'])) : [];
            $route_infos = [];
            if (sizeof($places) > 0) {
                foreach ($places as $key => $place) {
                    $route_infos[$key]['place'] = $place;
                    $route_infos[$key]['type'] = $types[$key];
                }
            }
            $this->route_pricing($post_id, $route_infos);
            die();
        }

        // Helper methods matching target plugin
        public function add_new_button($button_text, $class = 'wbtm_add_item', $button_class = '_themeButton_xs_mT_xs', $icon_class = 'fas fa-plus-square')
        {
            ?>
            <button class="<?php echo esc_attr($button_class . ' ' . $class); ?>" type="button">
                <span class="<?php echo esc_attr($icon_class); ?>"></span>
                <span class="mL_xs"><?php echo esc_html($button_text); ?></span>
            </button>
        <?php
        }

        public function edit_move_remove_button()
        {
        ?>
            <div class="allCenter">
                <div class="buttonGroup max_200">
                    <div class="_whiteButton_xs wbtm_edit_item_btn" type="">
                        <span class="far fa-edit mp_zero"></span>
                    </div>
                    <button class="_whiteButton_xs wbtm_item_remove" type="button">
                        <span class="fas fa-trash-alt mp_zero"></span>
                    </button>
                    <div class="_mpBtn_themeButton_xs wbtm_sortable_button" type="">
                        <span class="fas fa-expand-arrows-alt mp_zero"></span>
                    </div>
                </div>
            </div>
<?php
        }
    }
    new WBBM_Pricing_Routing();
}
