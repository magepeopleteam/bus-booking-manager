<?php
function mage_search_form_horizontal($single_bus,$target='')
{
    $search_form_b_color = wbbm_get_option('wbbm_search_form_b_color', 'wbbm_style_setting_sec');
    $wbbm_buy_ticket_text = wbbm_get_option('wbbm_buy_ticket_text', 'wbbm_label_setting_sec',__('Buy Ticket', 'bus-booking-manager'));
    ?>
    <div class="mage_container">
        <div class="search_form_horizontal" style="background-color:<?php echo ($search_form_b_color != '' ? $search_form_b_color : '#b30c3b12'); ?>">
            <?php if($wbbm_buy_ticket_text){ ?>    
                <h2><?php echo  $wbbm_buy_ticket_text; ?></h2>
            <?php } ?>
            <?php search_from_only($single_bus,$target); ?>
        </div>
    </div>
<?php } ?>