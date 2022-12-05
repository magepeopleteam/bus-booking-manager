<?php
function mage_search_page_horizontal(){
    $the_page = sanitize_post( $GLOBALS['wp_the_query']->get_queried_object() );
    $target = $the_page->post_name;
   mage_search_form_horizontal(false,$target);
    if (isset($_GET['bus_start_route']) && ($_GET['bus_end_route']) && ($_GET['j_date'])) {
        ?>
        <div class="mage_container">
            <div class="mage_row">
                <div style="width:100%">
                    <?php mage_search_list(); ?>
                </div>
            </div>
        </div>
        <?php
    }
}