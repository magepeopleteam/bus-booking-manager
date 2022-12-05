<?php
function mage_search_form_vertical($target=''){
    ?>
    <div class="mage_container">
        <div class="mage_search_box_small">
        <h2><?php echo wbbm_get_option('wbbm_buy_ticket_text', 'wbbm_label_setting_sec') ? wbbm_get_option('wbbm_buy_ticket_text', 'wbbm_label_setting_sec') : _e('BUY TICKET', 'bus-booking-manager'); ?></h2>
            <?php do_action('mage_search_from_only',false,$target); ?>
        </div>
    </div>
<?php } ?>