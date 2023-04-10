<div class="mp_tab_item" data-tab-item="#wbmm_bus_gallery" style="display: none;">

    <h3 class="wbbm_mp_tab_item_heading"><img src="<?php echo WBTM_PLUGIN_URL .'images/bus_arrow_left.png';?>"/><?php echo $cpt_label.' '. __('Gallery:', 'bus-booking-manager'); ?></h3>

    <div class="mp_tab_item_inner_wrapper">

        <ul class="misha-gallery">
            <?php

            add_filter( 'simple_register_metaboxes', 'rudr_multiple_img_upload_metabox' );
            function rudr_multiple_img_upload_metabox( $metaboxes ) {

                $metaboxes[] = array(
                    'id'	=> 'my_metabox',
                    'name'	=> 'Meta Box',
                    'post_type' => array( 'page' ),
                    'fields' => array(
                        array(
                            'id' => 'wbbm_image_gallery',
                            'label' => 'Images',
                            'type' => 'gallery'
                        ),
                    )
                );
                return $metaboxes;
            }

            $image_ids = ( $image_ids = get_post_meta( $post->ID, 'wbbm_image_gallery', true ) ) ? $image_ids : array();

            foreach( $image_ids as $i => &$id ) {
                $url = wp_get_attachment_image_url( $id, 'array( 200, 200 )' );
                if( $url ) {
                    ?>
                    <li data-id="<?php echo $id ?>">
                        <img src="<?php echo $url; ?>" alt="<?php esc_attr_e( $url ); ?>"/>
                        <a href="#" class="misha-gallery-remove">&times;</a>
                    </li>
                    <?php
                } else {
                    unset( $image_ids[ $i ] );
                }
            }
            ?>
        </ul>
        <input type="hidden" name="wbbm_image_gallery" value="<?php echo join( ',', $image_ids ) ?>" />
        <a href="#" class="button misha-upload-button">Add Images</a>


    </div>


</div>


