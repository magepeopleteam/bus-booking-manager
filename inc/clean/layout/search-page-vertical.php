<?php
function mage_search_page_vertical()
{
    if (isset($_GET['bus_start_route']) && ($_GET['bus_end_route']) && ($_GET['j_date'])) {
        ?>
        <div class="mage_container">
            <div class="mage_row">
                <div class="mage_search_box_sidebar">
                    <div class="mage_sidebar_search_form">
                        <h2><?php _e('BUY TICKET', 'bus-booking-manager'); ?></h2>
                        <?php do_action('mage_search_from_only'); ?>
                    </div>
                </div>
                <div class="mage_search_area">
                    <?php mage_search_list(); ?>
                </div>
            </div>
        </div>
        <?php
    } else {
        mage_search_form_vertical();
    }
}
