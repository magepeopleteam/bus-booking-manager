<?php
if (!defined('ABSPATH')) {
    die;
} // Cannot access pages directly.



add_action('woocommerce_before_calculate_totals', 'wbbm_add_custom_price');
function wbbm_add_custom_price($cart_object)
{

    foreach ($cart_object->cart_contents as $key => $value) {
        $eid = $value['wbbm_id'];
        if (get_post_type($eid) == 'wbbm_bus') {
            $t_price = $value['wbbm_tp'];

            $p_info = $value['wbbm_passenger_info'];
            $ext_services = ($value['wbbm_extra_services'])?$value['wbbm_extra_services']:[];
            $ext_bag_price = 0;
            $ext_service_price = 0;
            
            foreach ($p_info as $key => $p_inf) {
                if(!empty($p_inf['extra_bag_quantity']) && $p_inf['extra_bag_quantity'] > 0){
                    $ext_bag_price += (float)$p_inf['wbtm_extra_bag_price'] * (int)$p_inf['extra_bag_quantity'];
                }   
            }

            foreach ($ext_services as $ext_service) {
                if(!empty($ext_service['wbbm_es_input_qty']) && $ext_service['wbbm_es_input_qty'] > 0){
                    $ext_service_price += (int)$ext_service['wbbm_es_price'] * (int)$ext_service['wbbm_es_input_qty'];
                }   
            }

            $total = (float)$t_price;

            $value['data']->set_price($total);
            $value['data']->set_regular_price($total);
            $value['data']->set_sale_price($total);
            $value['data']->set_sold_individually('yes');
            $value['data']->get_price();
        }
    }

}


function wbbm_add_custom_fields_text_to_order_items($item, $cart_item_key, $values, $order)
{
    $eid = $values['wbbm_id'];
    if (get_post_type($eid) == 'wbbm_bus') {
        $passenger_info             = $values['wbbm_passenger_info'];
        $custom_reg_user             = $values['custom_reg_user'];
        $passenger_info_additional  = $values['wbbm_passenger_info_additional'];
        $wbbm_extra_services        = $values['wbbm_extra_services'];
        $wbbm_start_stops           = $values['wbbm_start_stops'];
        $wbbm_end_stops             = $values['wbbm_end_stops'];
        $wbbm_journey_date          = $values['wbbm_journey_date'];
        $wbbm_journey_time          = $values['wbbm_journey_time'];
        $wbbm_bus_start_time        = $values['wbbm_bus_time'];
        $wbbm_bus_id                = $values['wbbm_bus_id'];
        $total_adult                = $values['wbbm_total_adult_qt'];
        $total_adult_fare           = $values['wbbm_per_adult_price'];
        $total_child                = $values['wbbm_total_child_qt'];
        $total_child_fare           = $values['wbbm_per_child_price'];
        $total_infant               = $values['wbbm_total_infant_qt'];
        $total_infant_fare          = $values['wbbm_per_infant_price'];
        $total_entire               = $values['wbbm_total_entire_qt'];
        $total_entire_fare          = $values['wbbm_per_entire_price'];        
        $total_fare                 = $values['wbbm_tp'];
        if($values['pickpoint'] != 'n_a' && $values['pickpoint'] != 'N_a'):
        $pickpoint                  = ucfirst($values['pickpoint']);
        else:
        $pickpoint                  = '';   
        endif;
        $extra_per_bag_price        = get_post_meta($eid, 'wbbm_extra_bag_price', true);
        $extra_per_bag_price        = $extra_per_bag_price ? $extra_per_bag_price : 0;

        $jtime                      = $wbbm_journey_time;

        $adult_label            = wbbm_get_option('wbbm_adult_text', 'wbbm_label_setting_sec') ? wbbm_get_option('wbbm_adult_text', 'wbbm_label_setting_sec') : __('Adult','bus-booking-manager');
        $child_label            = wbbm_get_option('wbbm_child_text', 'wbbm_label_setting_sec') ? wbbm_get_option('wbbm_child_text', 'wbbm_label_setting_sec') : __('Child','bus-booking-manager');
        $infant_label           = wbbm_get_option('wbbm_infant_text', 'wbbm_label_setting_sec') ? wbbm_get_option('wbbm_infant_text', 'wbbm_label_setting_sec') : __('Infant','bus-booking-manager');
        $entire_label           = wbbm_get_option('wbbm_entire_bus_text', 'wbbm_label_setting_sec') ? wbbm_get_option('wbbm_entire_bus_text', 'wbbm_label_setting_sec') : __('Entire Bus','bus-booking-manager');
        $passenger_info_label   = wbbm_get_option('wbbm_psngrnfo_text', 'wbbm_label_setting_sec') ? wbbm_get_option('wbbm_psngrnfo_text', 'wbbm_label_setting_sec') : __('Passenger Information','bus-booking-manager');
        $extra_bag_qty_label    = wbbm_get_option('wbbm_extra_bag_text', 'wbbm_label_setting_sec') ? wbbm_get_option('wbbm_extra_bag_text', 'wbbm_label_setting_sec') : __('Extra Bag Qty', 'bus-booking-manager');
        $extra_bag_price_label  = wbbm_get_option('wbbm_extra_bag_price_text', 'wbbm_label_setting_sec') ? wbbm_get_option('wbbm_extra_bag_price_text', 'wbbm_label_setting_sec') : __('Extra Bag Price', 'bus-booking-manager');
        $name_label             = wbbm_get_option('wbbm_name_text', 'wbbm_label_setting_sec') ? wbbm_get_option('wbbm_name_text', 'wbbm_label_setting_sec') : __('Name', 'bus-booking-manager');
        $email_label            = wbbm_get_option('wbbm_email_text', 'wbbm_label_setting_sec') ? wbbm_get_option('wbbm_email_text', 'wbbm_label_setting_sec') : __('Email', 'bus-booking-manager');
        $phone_label            = wbbm_get_option('wbbm_phone_text', 'wbbm_label_setting_sec') ? wbbm_get_option('wbbm_phone_text', 'wbbm_label_setting_sec') : __('Phone', 'bus-booking-manager');
        $address_label          = wbbm_get_option('wbbm_address_text', 'wbbm_label_setting_sec') ? wbbm_get_option('wbbm_address_text', 'wbbm_label_setting_sec')  : __('Address', 'bus-booking-manager');
        $gender_label           = wbbm_get_option('wbbm_gender_text', 'wbbm_label_setting_sec') ? wbbm_get_option('wbbm_gender_text', 'wbbm_label_setting_sec') : __('Gender', 'bus-booking-manager');
        $dofbirth_label         = wbbm_get_option('wbbm_dofbirth_text', 'wbbm_label_setting_sec') ? wbbm_get_option('wbbm_dofbirth_text', 'wbbm_label_setting_sec') : __('Date of Birth', 'bus-booking-manager');
        $nationality_label      = wbbm_get_option('wbbm_nationality_text', 'wbbm_label_setting_sec') ? wbbm_get_option('wbbm_nationality_text', 'wbbm_label_setting_sec') : __('Nationality', 'bus-booking-manager');
        $fa_no_label            = wbbm_get_option('wbbm_fa_no_text', 'wbbm_label_setting_sec') ? wbbm_get_option('wbbm_fa_no_text', 'wbbm_label_setting_sec') : __('Flight Arrival No', 'bus-booking-manager');
        $fd_no_label            = wbbm_get_option('wbbm_fd_no_text', 'wbbm_label_setting_sec') ? wbbm_get_option('wbbm_fd_no_text', 'wbbm_label_setting_sec') : __('Flight Departure No', 'bus-booking-manager');
        $extra_services_label   = wbbm_get_option('wbbm_extra_services_text', 'wbbm_label_setting_sec') ? wbbm_get_option('wbbm_extra_services_text', 'wbbm_label_setting_sec') : __('Extra Services', 'bus-booking-manager');
        
        $boarding_point_label = __('Boarding Point', 'bus-booking-manager');
        $droping_point_label = __('Dropping Point', 'bus-booking-manager');
        $journey_date_label = __('Journey Date', 'bus-booking-manager');
        $journey_time_label = __('Journey Time', 'bus-booking-manager');

        $item->add_meta_data($boarding_point_label, $wbbm_start_stops);
        $item->add_meta_data($droping_point_label, $wbbm_end_stops);
        $item->add_meta_data($journey_date_label, get_wbbm_datetime($wbbm_journey_date, 'date'));
        $item->add_meta_data($journey_time_label, get_wbbm_datetime($jtime, 'time'));

        $item->add_meta_data('_boarding_point', $wbbm_start_stops);
        $item->add_meta_data('_droping_point', $wbbm_end_stops);
        $item->add_meta_data('_journey_date', $wbbm_journey_date);
        $item->add_meta_data('_journey_time', $jtime);
        $p_content = '';
        if($custom_reg_user=='no'){

            $p_content .= '<table style="border: 2px dashed #d3d3d3;margin:0;width: 100%;margin-bottom:10px;">';
            if ($total_adult > 0 ):
                $p_content .='<tr>';
                $p_content .='<td style="border:1px solid #f5f5f5;">';
                $p_content .= $adult_label.': '. ' (' . wc_price($total_adult_fare) . ' x '.$total_adult.') = ' . wc_price($total_adult_fare * $total_adult);
                $p_content .='</td>';
                $p_content .='</tr>';
            endif;

            if ($total_child > 0 ):
                $p_content .='<tr>';
                $p_content .='<td style="border:1px solid #f5f5f5;">';
                $p_content .= $child_label.': '. ' (' . wc_price($total_child_fare) . ' x '.$total_child.') = ' . wc_price($total_child_fare * $total_child);
                $p_content .='</td>';
                $p_content .='</tr>';
            endif;

            if ($total_infant > 0 ):
                $p_content .='<tr>';
                $p_content .='<td style="border:1px solid #f5f5f5;">';
                $p_content .= $infant_label.': '. ' (' . wc_price($total_infant_fare) . ' x '.$total_infant.') = ' . wc_price($total_infant_fare * $total_infant);
                $p_content .='</td>';
                $p_content .='</tr>';
            endif;
            $p_content .= '</table>';

        }else{
            if (is_array($passenger_info) && sizeof($passenger_info) > 0) {

                $i = 0;
                foreach ($passenger_info as $_passenger) {

                    $p_content .= '<table style="border: 2px dashed #d3d3d3;margin:0;width: 100%;margin-bottom:10px;">';


                    if ($total_adult > 0 && ($_passenger['wbbm_user_type'] == 'adult')):
                        $p_content .='<tr>';
                        $p_content .='<td style="border:1px solid #f5f5f5;">';
                        $p_content .= $adult_label.': '. ' (' . wc_price($total_adult_fare) . ' x '.$total_adult.') = ' . wc_price($total_adult_fare * $total_adult);
                        $p_content .='</td>';
                        $p_content .='</tr>';
                    endif;

                    if ($total_child > 0 && ($_passenger['wbbm_user_type'] == 'child')):
                        $p_content .='<tr>';
                        $p_content .='<td style="border:1px solid #f5f5f5;">';
                        $p_content .= $child_label.': '. ' (' . wc_price($total_child_fare) . ' x '.$total_child.') = ' . wc_price($total_child_fare * $total_child);
                        $p_content .='</td>';
                        $p_content .='</tr>';
                    endif;

                    if ($total_infant > 0 && ($_passenger['wbbm_user_type'] == 'infant')):
                        $p_content .='<tr>';
                        $p_content .='<td style="border:1px solid #f5f5f5;">';
                        $p_content .= $infant_label.': '. ' (' . wc_price($total_infant_fare) . ' x '.$total_infant.') = ' . wc_price($total_infant_fare * $total_infant);
                        $p_content .='</td>';
                        $p_content .='</tr>';
                    endif;



                    if ($total_entire = 1 && $total_entire_fare > 0 && ($_passenger['wbbm_user_type'] == 'entire')):
                        $p_content .='<tr>';
                        $p_content .='<td style="border:1px solid #f5f5f5;">';
                        $p_content .= $entire_label.': '.$total_entire;
                        $p_content .='</td>';
                        $p_content .='</tr>';
                    endif;

                    if(!empty($_passenger['extra_bag_quantity']) && $_passenger['extra_bag_quantity'] > 0):
                        $p_content .='<tr>';
                        $p_content .='<td style="border:1px solid #f5f5f5;">';
                        $p_content .= $extra_bag_qty_label.': '.$_passenger['extra_bag_quantity'];
                        $p_content .='</td>';
                        $p_content .='</tr>';
                    endif;

                    if(!empty($_passenger['wbtm_extra_bag_price']) && $_passenger['extra_bag_quantity'] > 0):
                        $p_content .='<tr>';
                        $p_content .='<td style="border:1px solid #f5f5f5;">';
                        $p_content .= $extra_bag_price_label.': '. ' (' . wc_price($extra_per_bag_price) . ' x '.$_passenger['extra_bag_quantity'].') = ' . wc_price((int)$_passenger['extra_bag_quantity'] * (int)$_passenger['wbtm_extra_bag_price']);
                        $p_content .='</td>';
                        $p_content .='</tr>';
                    endif;

                    if(!empty($_passenger['wbbm_user_name'])):
                        $p_content .='<tr>';
                        $p_content .='<td style="border:1px solid #f5f5f5;">';
                        $p_content .= $name_label.': '.$_passenger['wbbm_user_name'];
                        $p_content .='</td>';
                        $p_content .='</tr>';
                    endif;

                    if(!empty($_passenger['wbbm_user_email'])):
                        $p_content .='<tr>';
                        $p_content .='<td style="border:1px solid #f5f5f5;">';
                        $p_content .= $email_label.': '.$_passenger['wbbm_user_email'];
                        $p_content .='</td>';
                        $p_content .='</tr>';
                    endif;

                    if(!empty($_passenger['wbbm_user_phone'])):
                        $p_content .='<tr>';
                        $p_content .='<td style="border:1px solid #f5f5f5;">';
                        $p_content .= $phone_label.': '.$_passenger['wbbm_user_phone'];
                        $p_content .='</td>';
                        $p_content .='</tr>';
                    endif;

                    if(!empty($_passenger['wbbm_user_address'])):
                        $p_content .='<tr>';
                        $p_content .='<td style="border:1px solid #f5f5f5;">';
                        $p_content .= $address_label.': '.$_passenger['wbbm_user_address'];
                        $p_content .='</td>';
                        $p_content .='</tr>';
                    endif;

                    if(!empty($_passenger['wbbm_user_gender'])):
                        $p_content .='<tr>';
                        $p_content .='<td style="border:1px solid #f5f5f5;">';
                        $p_content .= $gender_label.': '.$_passenger['wbbm_user_gender'];
                        $p_content .='</td>';
                        $p_content .='</tr>';
                    endif;

                    if(!empty($_passenger['wbbm_user_dob'])):
                        $p_content .='<tr>';
                        $p_content .='<td style="border:1px solid #f5f5f5;">';
                        $p_content .= $dofbirth_label.': '.$_passenger['wbbm_user_dob'];
                        $p_content .='</td>';
                        $p_content .='</tr>';
                    endif;

                    if(!empty($_passenger['wbbm_user_nationality'])):
                        $p_content .='<tr>';
                        $p_content .='<td style="border:1px solid #f5f5f5;">';
                        $p_content .= $nationality_label.': '.$_passenger['wbbm_user_nationality'];
                        $p_content .='</td>';
                        $p_content .='</tr>';
                    endif;

                    if(!empty($_passenger['wbbm_user_flight_arrival_no'])):
                        $p_content .='<tr>';
                        $p_content .='<td style="border:1px solid #f5f5f5;">';
                        $p_content .= $fa_no_label.': '.$_passenger['wbbm_user_flight_arrival_no'];
                        $p_content .='</td>';
                        $p_content .='</tr>';
                    endif;

                    if(!empty($_passenger['wbbm_user_flight_departure_no'])):
                        $p_content .='<tr>';
                        $p_content .='<td style="border:1px solid #f5f5f5;">';
                        $p_content .= $fd_no_label.': '.$_passenger['wbbm_user_flight_departure_no'];
                        $p_content .='</td>';
                        $p_content .='</tr>';
                    endif;

                    if (is_array($passenger_info_additional) && sizeof($passenger_info_additional) > 0):
                        foreach ($passenger_info_additional[$i] as $builder):
                            $p_content .='<tr>';
                            $p_content .='<td style="border:1px solid #f5f5f5;">';
                            $p_content .= $builder['name'].': '.$builder['value'];
                            $p_content .='</td>';
                            $p_content .='</tr>';
                        endforeach;
                    endif;

                    $p_content .= '</table>';
                    $i++;
                }

                if(!empty($wbbm_extra_services)):
                    $p_content .= '<table style="border: 2px dashed #d3d3d3;margin:0;width: 100%;margin-bottom:10px;">';
                    $p_content .='<tr><th>'.$extra_services_label.'</th></tr>';

                    foreach ($wbbm_extra_services as $wbbm_extra_service) {
                        if($wbbm_extra_service['wbbm_es_input_qty'] > 0):
                            $p_content .='<tr>';
                            $p_content .='<td style="border:1px solid #f5f5f5;">';
                            $p_content .= $wbbm_extra_service['wbbm_es_name'].': '. ' (' . wc_price($wbbm_extra_service['wbbm_es_price']) . ' x '.$wbbm_extra_service['wbbm_es_input_qty'].') = ' . wc_price((int)$wbbm_extra_service['wbbm_es_price'] * (int)$wbbm_extra_service['wbbm_es_input_qty']);
                            $p_content .='</td>';
                            $p_content .='</tr>';
                        endif;
                    }

                    $p_content .= '</table>';
                endif;


            }

        }

        $item->add_meta_data($passenger_info_label, $p_content);



        $item->add_meta_data('Pickpoint', $pickpoint);

        $item->add_meta_data('_Adult', $total_adult);
        $item->add_meta_data('_Child', $total_child);
        $item->add_meta_data('_Infant', $total_infant);
        $item->add_meta_data('_Entire', $total_entire);      
        $item->add_meta_data('_adult_per_price', $total_adult_fare);
        $item->add_meta_data('_child_per_price', $total_child_fare);
        $item->add_meta_data('_infant_per_price', $total_infant_fare);
        $item->add_meta_data('_entire_per_price', $total_entire_fare);
        $item->add_meta_data('_total_price', $total_fare);
        $item->add_meta_data('_bus_id', $wbbm_bus_id);
        $item->add_meta_data('_btime', $jtime);
        $item->add_meta_data('_wbbm_passenger_info', $passenger_info );
        $item->add_meta_data('_wbbm_passenger_info_additional', $passenger_info_additional );
        $item->add_meta_data('_wbbm_extra_services', $wbbm_extra_services );
    }
    $item->add_meta_data('_wbbm_bus_id', $eid);
}

add_action('woocommerce_checkout_create_order_line_item', 'wbbm_add_custom_fields_text_to_order_items', 10, 4);

function add_the_date_validation( $passed ) {

    $eid = $_POST['bus_id'];
    if (get_post_type($eid) == 'wbbm_bus') {
        $return = false;
        $boarding_var = $return ? 'bus_end_route' : 'bus_start_route';
        $dropping_var = $return ? 'bus_start_route' : 'bus_end_route';
        $date_var = $return ? 'r_date' : 'j_date';
        $available_seat = wbbm_intermidiate_available_seat(@$_GET[$boarding_var], @$_GET[$dropping_var], wbbm_convert_date_to_php(mage_get_isset($date_var)),$eid);
        $adult_qty = isset($_POST['adult_quantity']) ? (int) $_POST['adult_quantity'] : 0;
        $child_qty = isset($_POST['child_quantity']) ? (int) $_POST['child_quantity'] : 0;
        $infant_qty = isset($_POST['infant_quantity']) ? (int) $_POST['infant_quantity'] : 0;

        $total_booking_seat = $adult_qty + $child_qty  + $infant_qty ;


        if($available_seat<$total_booking_seat){
            wc_add_notice( __( 'You have booked more than available seats', 'bus-booking-manager' ), 'error' );
            $passed = false;
        }
    }
    return $passed;
}
add_filter( 'woocommerce_add_to_cart_validation', 'add_the_date_validation', 10, 5 );