<?php
if ( ! defined('ABSPATH')) exit;  // if direct access 


/*Input fields
*  Text
*  Select
*  Checkbox
*  Checkbox Multi
*  Radio
*  Textarea
*  Number
*  Hidden
*  Range
*  Color
*  Email
*  URL
*  Tel
*  Search
*  Month
*  Week
*  Date
*  Time
*  Submit
 *
 *
*  Text multi
*  Select multi
*  Select2
*  Range with input
*  Color picker
*  Datepicker
*  Media
*  Media Gallery
*  Switcher
*  Switch
*  Switch multi
*  Switch image
*  Dimensions (width, height, custom)
*  WP Editor
*  Code Editor
*  Link Color
*  Repeatable
*  Icon
*  Icon multi
*  Date format
*  Time format
*  FAQ
*  Grid
*  Custom_html
*  Color palette
*  Color palette multi
*  User select
*  Color picker multi
*  Google reCaptcha
*  Nonce
*  Border
*  Margin
*  Padding
*  Google Map
 *
*/






if( ! class_exists( 'FormFieldsGenerator' ) ) {

    class FormFieldsGenerator {




        public function field_post_objects( $option ){

            $id 			= isset( $option['id'] ) ? $option['id'] : "";
            if(empty($id)) return;

            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();
            $sortable 	    = isset( $option['sortable'] ) ? $option['sortable'] : true;
            $default 	    = isset( $option['default'] ) ? $option['default'] : array();
            $args 	        = isset( $option['args'] ) ? $option['args'] : array();

            $values 	    = !empty( $option['value'] ) ? $option['value'] : array();
            $values         = !empty($values) ? $values : $default;

            $field_id       = $id;
            $field_name     = !empty( $field_name ) ? $field_name : $id;

            if(!empty($values)):

                foreach ($values as $value):
                    $values_sort[$value] = $value;
                endforeach;
                $args = array_replace($values_sort, $args);
            endif;



            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;





            ob_start();
            ?>
            <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_html($depends); ?>]" <?php } ?>
                    id="field-wrapper-<?php esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field';
                    ?> field-wrapper field-post-objects-wrapper
            field-post-objects-wrapper-<?php echo esc_html($field_id); ?>">
                <div class="field-list" id="<?php echo esc_html($field_id); ?>">
                    <?php
                    if(!empty($args)):
                        foreach ($args as $argsKey=>$arg):
                            ?>
                            <div class="item">
                                <?php if($sortable):?>
                                    <span class="button sort"><i class="fas fa-arrows-alt"></i></span>
                                <?php endif; ?>
                                <label>
                                    <input type="checkbox" <?php if(in_array($argsKey,$values)) echo 'checked';?>  value="<?php
                                    echo esc_attr($argsKey); ?>" name="<?php echo esc_attr($field_name); ?>[]">
                                    <span><?php echo esc_attr($arg); ?></span>
                                </label>
                            </div>
                        <?php
                        endforeach;
                    endif;
                    ?>
                </div>
                <script>
                    jQuery(document).ready(function($) {
                        jQuery( ".field-post-objects-wrapper-<?php esc_attr($id); ?> .field-list" ).sortable({ handle: '.sort' });
                    })
                </script>
                <script>
                    <?php if(!empty($depends)) {?>
                    jQuery('#field-wrapper-<?php esc_attr($id); ?>').formFieldDependency({});
                    <?php } ?>
                </script>
            </div>
            <?php
            return ob_get_clean();
        }






        public function field_switcher( $option ){

            $id				= isset( $option['id'] ) ? $option['id'] : "";
            if(empty($id)) return;
            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();
            $default 		= isset( $option['default'] ) ? $option['default'] : '';
            $args 	        = isset( $option['args'] ) ? $option['args'] : "";
            $value 	        = isset( $option['value'] ) ? $option['value'] : "";
            $value          = !empty($value) ? $value : $default;

            $checked = !empty($value) ? 'checked':'';
            $field_id       = $id;
            $field_name     = !empty( $field_name ) ? $field_name : $id;



            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;


            ob_start();
            ?>
            <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_html($depends); ?>]" <?php } ?>
                    id="field-wrapper-<?php echo esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-switcher-wrapper
            field-switcher-wrapper-<?php echo esc_attr($id); ?>">
                <label class="switcher <?php echo esc_attr($checked); ?>">
                    <input type="checkbox" id="<?php echo esc_attr($id); ?>" value="<?php echo esc_attr($value); ?>"
                           name="<?php echo esc_attr($field_name); ?>" <?php echo esc_attr($checked); ?>>
                    <span class="layer"></span>
                    <span class="slider"></span>
                    <?php
                    if(!empty($args))
                    foreach ($args as $index=>$arg):
                        ?>
                        <span  unselectable="on" class="switcher-text <?php echo esc_attr($index); ?>"><?php echo esc_html($arg);
                        ?></span>
                    <?php
                    endforeach;
                    ?>
                </label>
            </div>

            <script>
                jQuery(document).ready(function($) {
                    jQuery(document).on('click', '.field-switcher-wrapper-<?php esc_attr($id); ?> .switcher .layer', function() {
                        if(jQuery(this).parent().hasClass('checked')){
                            jQuery(this).parent().removeClass('checked');
                        }else{
                            jQuery(this).parent().addClass('checked');
                        }
                    })
                })
            </script>

            <script>
                <?php if(!empty($depends)) {?>
                jQuery('#field-wrapper-<?php esc_attr($id); ?>').formFieldDependency({});
                <?php } ?>
            </script>
            <?php
            return ob_get_clean();
        }




        public function field_google_map( $option ){

            $id 			= isset( $option['id'] ) ? $option['id'] : "";
            if(empty($id)) return;
            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();
            $placeholder 	= isset( $option['placeholder'] ) ? $option['placeholder'] : "";
            $default 	    = isset( $option['default'] ) ? $option['default'] : array();
            $args 	        = isset( $option['args'] ) ? $option['args'] : "";
            $preview 	        = isset( $option['preview'] ) ? $option['preview'] : false;
            $value 	        = isset( $option['value'] ) ? $option['value'] : "";
            $values         = !empty($value) ? $value : $default;

            $field_id       = $id;
            $field_name     = !empty( $field_name ) ? $field_name : $id;


            $lat  = isset($values['lat']) ? $values['lat'] : '';
            $lng   = isset($values['lng']) ? $values['lng'] :'';
            $zoom  = isset($values['zoom']) ? $values['zoom'] : '';
            $title  = isset($values['title']) ? $values['title'] : '';
            $apikey  = isset($values['apikey']) ? $values['apikey'] : '';


            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;



            ob_start();
            if(!empty($args)):
                ?>

                <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?>
                        id="field-wrapper-<?php esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-google-map-wrapper
                field-google-map-wrapper-<?php esc_attr($id); ?>">
                    <div class="item-list">
                        <?php
                        foreach ($args as $index=>$name):
                            ?>
                            <div class="item">
                                <span class="field-title"><?php echo esc_html($name); ?></span>
                                <span class="input-wrapper"><input type='text' name='<?php echo esc_attr($field_name);?>[<?php
                                    echo esc_html($index); ?>]' value='<?php
                                    echo esc_html($values[$index]); ?>' /></span>
                            </div>
                        <?php
                        endforeach;
                        ?>
                    </div>
                </div>
                <script>
                    <?php if(!empty($depends)) {?>
                    jQuery('#field-wrapper-<?php esc_attr($id); ?>').formFieldDependency({});
                    <?php } ?>
                </script>
                <?php
                if($preview):
                    ?>
                    <div id="map-<?php echo esc_attr($field_id); ?>"></div>
                    <script>
                        function initMap() {
                            var myLatLng = {lat: <?php echo esc_html($lat); ?>, lng: <?php echo esc_html($lng); ?>};
                            var map = new google.maps.Map(document.getElementById('map-<?php echo esc_html($field_id); ?>'), {
                                zoom: <?php echo esc_html($zoom); ?>,
                                center: myLatLng
                            });
                            var marker = new google.maps.Marker({
                                position: myLatLng,
                                map: map,
                                title: '<?php echo esc_html($title); ?>'
                            });
                        }
                    </script>
                    <script async defer
                            src="https://maps.googleapis.com/maps/api/js?key=<?php echo esc_html($apikey); ?>&callback=initMap">
                    </script>
                    <?php
                endif;
            endif;
            return ob_get_clean();
        }



        public function field_border( $option ){

            $id 			= isset( $option['id'] ) ? $option['id'] : "";
            if(empty($id)) return;
            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();
            $placeholder 	= isset( $option['placeholder'] ) ? $option['placeholder'] : "";
            $default 	    = isset( $option['default'] ) ? $option['default'] : array();

            $value 	        = isset( $option['value'] ) ? $option['value'] : "";
            $values         = !empty($value) ? $value : $default;

            $field_id       = $id;
            $field_name     = !empty( $field_name ) ? $field_name : $id;

            $width  = $values['width'];
            $unit   = $values['unit'];
            $style  = $values['style'];
            $color  = $values['color'];



            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;


            ob_start();
            ?>
            <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?>
                    id="field-wrapper-<?php esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-border-wrapper
            field-border-wrapper-<?php esc_attr($id); ?>">
                <div class="item-list">
                        <div class="item">
                            <span class="field-title">Width</span>
                            <span class="input-wrapper"><input type='number' name='<?php echo esc_attr($field_name);?>[width]' value='<?php
                                echo esc_attr($width); ?>' /></span>
                            <select name="<?php echo esc_attr($field_name);?>[unit]">
                                <option <?php if($unit == 'px') echo 'selected'; ?> value="px">px</option>
                                <option <?php if($unit == '%') echo 'selected'; ?> value="%">%</option>
                                <option <?php if($unit == 'em') echo 'selected'; ?> value="em">em</option>
                                <option <?php if($unit == 'cm') echo 'selected'; ?> value="cm">cm</option>
                                <option <?php if($unit == 'mm') echo 'selected'; ?> value="mm">mm</option>
                                <option <?php if($unit == 'in') echo 'selected'; ?> value="in">in</option>
                                <option <?php if($unit == 'pt') echo 'selected'; ?> value="pt">pt</option>
                                <option <?php if($unit == 'pc') echo 'selected'; ?> value="pc">pc</option>
                                <option <?php if($unit == 'ex') echo 'selected'; ?> value="ex">ex</option>
                            </select>
                        </div>
                        <div class="item">
                            <span class="field-title">Style</span>
                            <select name="<?php echo esc_attr($field_name);?>[style]">
                                <option <?php if($style == 'dotted') echo 'selected'; ?> value="dotted">dotted</option>
                                <option <?php if($style == 'dashed') echo 'selected'; ?> value="dashed">dashed</option>
                                <option <?php if($style == 'solid') echo 'selected'; ?> value="solid">solid</option>
                                <option <?php if($style == 'double') echo 'selected'; ?> value="double">double</option>
                                <option <?php if($style == 'groove') echo 'selected'; ?> value="groove">groove</option>
                                <option <?php if($style == 'ridge') echo 'selected'; ?> value="ridge">ridge</option>
                                <option <?php if($style == 'inset') echo 'selected'; ?> value="inset">inset</option>
                                <option <?php if($style == 'outset') echo 'selected'; ?> value="outset">outset</option>
                                <option <?php if($style == 'none') echo 'selected'; ?> value="none">none</option>
                            </select>
                        </div>
                    <div class="item">
                        <span class="field-title">Color</span>
                        <span class="input-wrapper"><input class="colorpicker" type='text' name='<?php echo esc_attr($field_name);
                        ?>[color]' value='<?php echo esc_attr($color); ?>' /></span>
                    </div>
                </div>
            </div>
            <script>
                <?php if(!empty($depends)) {?>
                jQuery('#field-wrapper-<?php esc_attr($id); ?>').formFieldDependency({});
                <?php } ?>
            </script>
            <script>
                jQuery(document).ready(function($) {
                    $('.field-border-wrapper-<?php esc_attr($id); ?> .colorpicker').wpColorPicker();
                });
            </script>
            <?php
            return ob_get_clean();
        }



        public function field_dimensions( $option ){

            $id 			= isset( $option['id'] ) ? $option['id'] : "";
            if(empty($id)) return;
            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();
            $placeholder 	= isset( $option['placeholder'] ) ? $option['placeholder'] : "";
            $default 	    = isset( $option['default'] ) ? $option['default'] : array();
            $args 	        = isset( $option['args'] ) ? $option['args'] : "";

            $value 	        = isset( $option['value'] ) ? $option['value'] : array();
            $values         = !empty($value) ? $value : $default;

            $field_id       = $id;
            $field_name     = !empty( $field_name ) ? $field_name : $id;


            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;



            ob_start();
            if(!empty($args)):
                ?>
                <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?> id="field-wrapper-<?php esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-margin-wrapper
                field-margin-wrapper-<?php esc_attr($id); ?>">
                    <div class="item-list">
                        <?php
                        foreach ($args as $index=>$arg):
                            $name = $arg['name'];
                            $unit = $values[$index]['unit'];
                            ?>
                            <div class="item">
                                <span class="field-title"><?php echo esc_html($name); ?></span>
                                <span class="input-wrapper"><input type='number' name='<?php echo esc_attr($field_name);?>[<?php
                                    echo esc_attr($index); ?>][val]' value='<?php
                                    echo esc_attr($values[$index]['val']); ?>' /></span>
                                <select name="<?php echo esc_attr($field_name);?>[<?php echo esc_attr($index); ?>][unit]">
                                    <option <?php if($unit == 'px') echo 'selected'; ?> value="px">px</option>
                                    <option <?php if($unit == '%') echo 'selected'; ?> value="%">%</option>
                                    <option <?php if($unit == 'em') echo 'selected'; ?> value="em">em</option>
                                    <option <?php if($unit == 'cm') echo 'selected'; ?> value="cm">cm</option>
                                    <option <?php if($unit == 'mm') echo 'selected'; ?> value="mm">mm</option>
                                    <option <?php if($unit == 'in') echo 'selected'; ?> value="in">in</option>
                                    <option <?php if($unit == 'pt') echo 'selected'; ?> value="pt">pt</option>
                                    <option <?php if($unit == 'pc') echo 'selected'; ?> value="pc">pc</option>
                                    <option <?php if($unit == 'ex') echo 'selected'; ?> value="ex">ex</option>
                                </select>
                            </div>
                        <?php
                        endforeach;
                        ?>
                    </div>
                </div>
                <script>
                    <?php if(!empty($depends)) {?>
                    jQuery('#field-wrapper-<?php esc_attr($id); ?>').formFieldDependency({});
                    <?php } ?>
                </script>
            <?php
            endif;
            return ob_get_clean();
        }



        public function field_padding( $option ){

            $id 			= isset( $option['id'] ) ? $option['id'] : "";
            if(empty($id)) return;
            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();
            $placeholder 	= isset( $option['placeholder'] ) ? $option['placeholder'] : "";
            $default 	    = isset( $option['default'] ) ? $option['default'] : array();
            $args 	        = isset( $option['args'] ) ? $option['args'] : "";

            $value 	        = isset( $option['value'] ) ? $option['value'] : array();
            $values         = !empty($value) ? $value : $default;

            $field_id       = $id;
            $field_name     = !empty( $field_name ) ? $field_name : $id;


            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;



            ob_start();
            if(!empty($args)):
                ?>
                <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?> id="field-wrapper-<?php esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-padding-wrapper
                field-padding-wrapper-<?php esc_attr($id); ?>">
                    <label><input type="checkbox" class="change-together">Apply for all</label>
                    <div class="item-list">
                        <?php
                        foreach ($args as $index=>$arg):
                            $name = $arg['name'];
                            $unit = $values[$index]['unit'];
                            ?>
                            <div class="item">
                                <span class="field-title"><?php echo esc_attr($name); ?></span>
                                <span class="input-wrapper"><input type='number' name='<?php echo esc_attr($field_name);?>[<?php
                                    echo esc_attr($index); ?>][val]' value='<?php
                                    echo esc_attr($values[$index]['val']); ?>' /></span>
                                <select name="<?php echo esc_attr($field_name);?>[<?php echo esc_attr($index); ?>][unit]">
                                    <option <?php if($unit == 'px') echo 'selected'; ?> value="px">px</option>
                                    <option <?php if($unit == '%') echo 'selected'; ?> value="%">%</option>
                                    <option <?php if($unit == 'em') echo 'selected'; ?> value="em">em</option>
                                    <option <?php if($unit == 'cm') echo 'selected'; ?> value="cm">cm</option>
                                    <option <?php if($unit == 'mm') echo 'selected'; ?> value="mm">mm</option>
                                    <option <?php if($unit == 'in') echo 'selected'; ?> value="in">in</option>
                                    <option <?php if($unit == 'pt') echo 'selected'; ?> value="pt">pt</option>
                                    <option <?php if($unit == 'pc') echo 'selected'; ?> value="pc">pc</option>
                                    <option <?php if($unit == 'ex') echo 'selected'; ?> value="ex">ex</option>
                                </select>
                            </div>
                        <?php
                        endforeach;
                        ?>
                    </div>
                </div>
                <script>
                    <?php if(!empty($depends)) {?>
                    jQuery('#field-wrapper-<?php esc_attr($id); ?>').formFieldDependency({});
                    <?php } ?>
                </script>
                <script>
                    jQuery(document).ready(function($) {
                        jQuery(document).on('keyup change', '.field-padding-wrapper-<?php esc_attr($id); ?>  input[type="number"]',
                            function() {
                                is_checked = jQuery('.field-padding-wrapper-<?php esc_attr($id); ?> .change-together').attr('checked');
                                if(is_checked == 'checked'){
                                    val = jQuery(this).val();
                                    i = 0;
                                    $('.field-padding-wrapper-<?php esc_attr($id); ?> input[type="number"]').each(function( index ) {
                                        if(i > 0){
                                            jQuery(this).val(val);
                                        }
                                        i++;
                                    });
                                }
                            })
                        jQuery(document).on('click', '.field-padding-wrapper-<?php esc_attr($id); ?> .change-together', function() {
                            is_checked = this.checked;
                            if(is_checked){
                                i = 0;
                                $('.field-padding-wrapper-<?php esc_attr($id); ?> input[type="number"]').each(function( index ) {
                                    if(i > 0){
                                        jQuery(this).attr('readonly','readonly');
                                    }
                                    i++;
                                });
                                i = 0;
                                $('.field-padding-wrapper-<?php esc_attr($id); ?> select').each(function( index ) {
                                    if(i > 0){
                                        //jQuery(this).attr('disabled','disabled');
                                    }
                                    i++;
                                });
                            }else{
                                jQuery('.field-padding-wrapper-<?php esc_attr($id); ?> input[type="number"]').removeAttr('readonly');
                                //jQuery('.field-margin-wrapper-<?php esc_attr($id); ?> select').removeAttr('disabled');
                            }
                        })
                    })
                </script>
            <?php
            endif;
            return ob_get_clean();
        }



        public function field_margin( $option ){

            $id 			= isset( $option['id'] ) ? $option['id'] : "";
            if(empty($id)) return;
            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();
            $placeholder 	= isset( $option['placeholder'] ) ? $option['placeholder'] : "";
            $default 	    = isset( $option['default'] ) ? $option['default'] : array();
            $args 	        = isset( $option['args'] ) ? $option['args'] : "";

            $value 	        = isset( $option['value'] ) ? $option['value'] : array();
            $values         = !empty($value) ? $value : $default;

            $field_id       = $id;
            $field_name     = !empty( $field_name ) ? $field_name : $id;



            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;


            ob_start();
            if(!empty($args)):
                ?>
                <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?> id="field-wrapper-<?php esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-margin-wrapper
                field-margin-wrapper-<?php esc_attr($id); ?>">
                    <label><input type="checkbox" class="change-together">Apply for all</label>
                    <div class="item-list">
                        <?php
                        foreach ($args as $index=>$arg):
                            $name = $arg['name'];
                            $unit = $values[$index]['unit'];
                            ?>
                            <div class="item">
                                <span class="field-title"><?php echo esc_attr($name); ?></span>
                                <span class="input-wrapper"><input class="<?php echo esc_attr($index); ?>" type='number'
                                                                   name='<?php echo esc_attr($field_name); ?>[<?php
                                    echo esc_attr($index); ?>][val]' value='<?php
                                    echo esc_attr($values[$index]['val']); ?>' /></span>
                                <select name="<?php echo esc_attr($field_name);?>[<?php echo esc_attr($index); ?>][unit]">
                                    <option <?php if($unit == 'px') echo 'selected'; ?> value="px">px</option>
                                    <option <?php if($unit == '%') echo 'selected'; ?> value="%">%</option>
                                    <option <?php if($unit == 'em') echo 'selected'; ?> value="em">em</option>
                                    <option <?php if($unit == 'cm') echo 'selected'; ?> value="cm">cm</option>
                                    <option <?php if($unit == 'mm') echo 'selected'; ?> value="mm">mm</option>
                                    <option <?php if($unit == 'in') echo 'selected'; ?> value="in">in</option>
                                    <option <?php if($unit == 'pt') echo 'selected'; ?> value="pt">pt</option>
                                    <option <?php if($unit == 'pc') echo 'selected'; ?> value="pc">pc</option>
                                    <option <?php if($unit == 'ex') echo 'selected'; ?> value="ex">ex</option>
                                </select>
                            </div>
                        <?php
                        endforeach;
                        ?>
                    </div>
                </div>
                <script>
                    jQuery(document).ready(function($) {
                        jQuery(document).on('keyup change', '.field-margin-wrapper-<?php esc_attr($id); ?>  input[type="number"]',
                            function() {
                                is_checked = jQuery('.field-margin-wrapper-<?php esc_attr($id); ?> .change-together').attr('checked');
                                if(is_checked == 'checked'){
                                    val = jQuery(this).val();
                                    i = 0;
                                    $('.field-margin-wrapper-<?php esc_attr($id); ?> input[type="number"]').each(function( index ) {
                                        if(i > 0){
                                            jQuery(this).val(val);
                                        }
                                        i++;
                                    });
                                }
                            })
                        jQuery(document).on('click', '.field-margin-wrapper-<?php esc_attr($id); ?> .change-together', function() {
                            is_checked = this.checked;
                            if(is_checked){
                                i = 0;
                                $('.field-margin-wrapper-<?php esc_attr($id); ?> input[type="number"]').each(function( index ) {
                                    if(i > 0){
                                        jQuery(this).attr('readonly','readonly');
                                    }
                                    i++;
                                });
                                i = 0;
                                $('.field-margin-wrapper-<?php esc_attr($id); ?> select').each(function( index ) {
                                    if(i > 0){
                                        //jQuery(this).attr('disabled','disabled');
                                    }
                                    i++;
                                });
                            }else{
                                jQuery('.field-margin-wrapper-<?php esc_attr($id); ?> input[type="number"]').removeAttr('readonly');
                                //jQuery('.field-margin-wrapper-<?php esc_attr($id); ?> select').removeAttr('disabled');
                            }
                        })
                    })
                </script>
                <script>
                    <?php if(!empty($depends)) {?>
                    jQuery('#field-wrapper-<?php esc_attr($id); ?>').formFieldDependency({});
                    <?php } ?>
                </script>
            <?php
            endif;
            return ob_get_clean();
        }



        public function field_google_recaptcha( $option ){

            $id 			= isset( $option['id'] ) ? $option['id'] : "";
            if(empty($id)) return;

            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();

            $secret_key 	= isset( $option['secret_key'] ) ? $option['secret_key'] : "";
            $site_key 	    = isset( $option['site_key'] ) ? $option['site_key'] : "";
            $version 	    = isset( $option['version'] ) ? $option['version'] : "";
            $action_name 	= isset( $option['action_name'] ) ? $option['action_name'] : "action_name";

            $field_id       = $id;
            $field_name     = !empty( $field_name ) ? $field_name : $id;



            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;


            ob_start();
            ?>
            <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?> id="field-wrapper-<?php esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-google-recaptcha-wrapper
            field-google-recaptcha-wrapper-<?php esc_attr($id);
            ?>">
                <?php if($version == 'v2'):?>
                    <div class="g-recaptcha" data-sitekey="<?php echo esc_attr($site_key); ?>"></div>
                    <script src='https://www.google.com/recaptcha/api.js'></script>
            <?php elseif($version == 'v3'):?>
                    <script src='https://www.google.com/recaptcha/api.js?render=<?php echo esc_attr($site_key); ?>'></script>
                    <script>
                        grecaptcha.ready(function() {
                            grecaptcha.execute('<?php echo esc_attr($site_key); ?>', {action: '<?php echo esc_attr($action_name); ?>'})
                                .then(function(token) {
// Verify the token on the server.
                                });
                        });
                    </script>

                <?php endif;?>
            </div>
            <script>
                <?php if(!empty($depends)) {?>
                jQuery('#field-wrapper-<?php esc_attr($id); ?>').formFieldDependency({});
                <?php } ?>
            </script>

            <?php

            return ob_get_clean();
        }


        public function field_img_select( $option ){

            $id				= isset( $option['id'] ) ? $option['id'] : "";
            if(empty($id)) return;

            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();
            $width			= isset( $option['width'] ) ? $option['width'] : "";
            $height			= isset( $option['height'] ) ? $option['height'] : "";
            $default 		= isset( $option['default'] ) ? $option['default'] : '';
            $args			= isset( $option['args'] ) ? $option['args'] : array();
            $args			= is_array( $args ) ? $args : $this->args_from_string( $args );

            $value 	        = isset( $option['value'] ) ? $option['value'] : '';
            $value          = !empty($value) ? $value : $default;

            $field_id       = $id;
            $field_name     = !empty( $field_name ) ? $field_name : $id;



            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;


            ob_start();
            ?>
            <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?> id="field-wrapper-<?php esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-img-select-wrapper
            field-img-select-wrapper-<?php esc_attr($id); ?>">
                <div class="img-list">
                    <?php
                    foreach( $args as $key => $arg ):
                        $checked = ( $arg == $value ) ? "checked" : "";
                        ?><label class="<?php echo esc_attr($checked); ?>" for='<?php esc_attr($id); ?>-<?php echo esc_attr($key); ?>'><input type='radio' id='<?php esc_attr($id); ?>-<?php echo esc_attr($key); ?>' value='<?php echo esc_attr($key); ?>' <?php echo esc_attr($checked); ?>><span class="sw-button"><img src="<?php echo esc_attr($arg); ?>"> </span></label><?php

                    endforeach;
                    ?>
                </div>
                <div class="img-val">
                    <input type="text" name="<?php echo esc_attr($field_name); ?>" value="<?php echo esc_attr($value); ?>">
                </div>
            </div>
            <script>jQuery(document).ready(function($) {
                    jQuery(document).on('click', '.field-img-select-wrapper-<?php esc_attr($id); ?> .sw-button img', function() {
                        var src = jQuery(this).attr('src');
                        jQuery('.field-img-select-wrapper-<?php esc_attr($id); ?> .img-val input').val(src);
                        jQuery('.field-img-select-wrapper-<?php esc_attr($id); ?> label').removeClass('checked');
                        if(jQuery(this).parent().parent().hasClass('checked')){
                            jQuery(this).parent().parent().removeClass('checked');
                        }else{
                            jQuery(this).parent().parent().addClass('checked');
                        }
                    })
                })
            </script>
            <script>
                <?php if(!empty($depends)) {?>
                jQuery('#field-wrapper-<?php esc_attr($id); ?>').formFieldDependency({});
                <?php } ?>
            </script>
            <?php
            return ob_get_clean();

        }





        public function field_submit( $option ){

            $id 			= isset( $option['id'] ) ? $option['id'] : "";
            if(empty($id)) return;

            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();
            $placeholder 	= isset( $option['placeholder'] ) ? $option['placeholder'] : "";
            $default 	    = isset( $option['default'] ) ? $option['default'] : "";

            $value 	        = isset( $option['value'] ) ? $option['value'] : "";
            $value          = !empty($value) ? $value : $default;

            $field_id       = $id;
            $field_name     = !empty( $field_name ) ? $field_name : $id;



            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;


            ob_start();
            ?>
            <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?> id="field-wrapper-<?php esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-submit-wrapper
            field-submit-wrapper-<?php esc_attr($id); ?>">
                <input type='submit' name='<?php echo esc_attr($field_name); ?>' id='<?php echo esc_attr($field_id); ?>' placeholder='<?php echo esc_attr($placeholder); ?>' value='<?php echo esc_attr($value); ?>' />
            </div>
            <script>
                <?php if(!empty($depends)) {?>
                jQuery('#field-wrapper-<?php esc_attr($id); ?>').formFieldDependency({});
                <?php } ?>
            </script>
            <?php
            return ob_get_clean();
        }


        public function field_nonce( $option ){

            $id 			= isset( $option['id'] ) ? $option['id'] : "";
            if(empty($id)) return;

            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();
            $placeholder 	= isset( $option['placeholder'] ) ? $option['placeholder'] : "";
            $action_name 	    = isset( $option['action_name'] ) ? $option['action_name'] : "";

            $field_id       = $id;
            $field_name     = !empty( $field_name ) ? $field_name : $id;


            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;



            ob_start();
            ?>
            <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?> id="field-wrapper-<?php esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-nonce-wrapper
            field-nonce-wrapper-<?php esc_attr($id); ?>">
                <?php wp_nonce_field( $action_name, $field_name ); ?>
            </div>
            <script>
                <?php if(!empty($depends)) {?>
                jQuery('#field-wrapper-<?php esc_attr($id); ?>').formFieldDependency({});
                <?php } ?>
            </script>
            <?php

            return ob_get_clean();
        }



        public function field_color( $option ){

            $id 			= isset( $option['id'] ) ? $option['id'] : "";
            if(empty($id)) return;

            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();
            $placeholder 	= isset( $option['placeholder'] ) ? $option['placeholder'] : "";
            $default 	    = isset( $option['default'] ) ? $option['default'] : "";

            $value 	        = isset( $option['value'] ) ? $option['value'] : "";
            $value          = !empty($value) ? $value : $default;

            $field_id       = $id;
            $field_name     = !empty( $field_name ) ? $field_name : $id;


            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;



            ob_start();
            ?>
            <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?> id="field-wrapper-<?php esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-color-wrapper
            field-color-wrapper-<?php esc_attr($id); ?>">
                <input type='color' name='<?php echo esc_attr($field_name); ?>' id='<?php echo esc_attr($field_id); ?>' placeholder='<?php echo esc_attr($placeholder); ?>' value='<?php echo esc_attr($value); ?>' />
            </div>
            <script>
                <?php if(!empty($depends)) {?>
                jQuery('#field-wrapper-<?php esc_attr($id); ?>').formFieldDependency({});
                <?php } ?>
            </script>
            <?php

            return ob_get_clean();
        }




        public function field_email( $option ){

            $id 			= isset( $option['id'] ) ? $option['id'] : "";
            if(empty($id)) return;

            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();
            $placeholder 	= isset( $option['placeholder'] ) ? $option['placeholder'] : "";
            $default 	    = isset( $option['default'] ) ? $option['default'] : "";

            $value 	        = isset( $option['value'] ) ? $option['value'] : "";
            $value          = !empty($value) ? $value : $default;

            $field_id       = $id;
            $field_name     = !empty( $field_name ) ? $field_name : $id;


            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;



            ob_start();
            ?>
            <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?> id="field-wrapper-<?php esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-email-wrapper
            field-email-wrapper-<?php esc_attr($id); ?>">
                <input type='email' name='<?php echo esc_attr($field_name); ?>' id='<?php echo esc_attr($field_id); ?>' placeholder='<?php echo esc_attr($placeholder); ?>' value='<?php echo esc_attr($value); ?>' />
            </div>
            <script>
                <?php if(!empty($depends)) {?>
                jQuery('#field-wrapper-<?php esc_attr($id); ?>').formFieldDependency({});
                <?php } ?>
            </script>
            <?php

            return ob_get_clean();
        }


        public function field_password( $option ){

            $id 			= isset( $option['id'] ) ? $option['id'] : "";
            if(empty($id)) return;

            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();
            $placeholder 	= isset( $option['placeholder'] ) ? $option['placeholder'] : "";
            $default 	    = isset( $option['default'] ) ? $option['default'] : "";
            $password_meter = isset( $option['password_meter'] ) ? $option['password_meter'] : true;
            $value 	        = isset( $option['value'] ) ? $option['value'] : "";
            $value          = !empty($value) ? $value : $default;

            $field_id       = $id;
            $field_name     = !empty( $field_name ) ? $field_name : $id;


            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;


            ob_start();
            ?>
            <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?> id="field-wrapper-<?php esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-password-wrapper
            field-password-wrapper-<?php esc_attr($id); ?>">
                <input type='password' name='<?php echo esc_attr($field_name); ?>' id='<?php echo esc_attr($field_id); ?>' placeholder='<?php echo esc_attr($placeholder); ?>' value='<?php echo esc_attr($value); ?>' />
                <?php if($password_meter): ?>
                <div class="scorePassword"></div>
                <div class="scoreText"></div>
                <?php endif; ?>
            </div>
            <script>
                <?php if(!empty($depends)) {?>
                jQuery('#field-wrapper-<?php esc_attr($id); ?>').formFieldDependency({});
                <?php } ?>
            </script>
            <script>
                jQuery(document).ready(function($) {
                    jQuery(document).on('keyup', '.field-password-wrapper-<?php esc_attr($id); ?> input',function(){
                        pass = $(this).val();
                        var score = 0;
                        if (!pass)
                            return score;
                        // award every unique letter until 5 repetitions
                        var letters = new Object();
                        for (var i=0; i<pass.length; i++) {
                            letters[pass[i]] = (letters[pass[i]] || 0) + 1;
                            score += 5.0 / letters[pass[i]];
                        }
                        // bonus points for mixing it up
                        var variations = {
                            digits: /\d/.test(pass),
                            lower: /[a-z]/.test(pass),
                            upper: /[A-Z]/.test(pass),
                            nonWords: /\W/.test(pass),
                        }
                        variationCount = 0;
                        for (var check in variations) {
                            variationCount += (variations[check] == true) ? 1 : 0;
                        }
                        score += (variationCount - 1) * 10;
                        if(score > 80){
                            score_style = '#4CAF50;';
                            score_text = 'Strong';
                        }else if(score > 60){
                            score_style = '#cddc39;';
                            score_text = 'Good';
                        }else if(score > 30){
                            score_style = '#FF9800;';
                            score_text = 'Normal';
                        }else{
                            score_style = '#F44336;';
                            score_text = 'Week';
                        }
                        html = '<span style="width:'+parseInt(score)+'%;background-color: '+score_style+'"></span>';
                        $(".field-password-wrapper-<?php esc_attr($id); ?> .scorePassword").html(html)
                        $(".field-password-wrapper-<?php esc_attr($id); ?> .scoreText").html(score_text)
                    })
                })
            </script>
            <?php

            return ob_get_clean();
        }

        public function field_search( $option ){

            $id 			= isset( $option['id'] ) ? $option['id'] : "";
            if(empty($id)) return;

            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();
            $placeholder 	= isset( $option['placeholder'] ) ? $option['placeholder'] : "";
            $default 	    = isset( $option['default'] ) ? $option['default'] : "";

            $value 	        = isset( $option['value'] ) ? $option['value'] : "";
            $value          = !empty($value) ? $value : $default;

            $field_id       = $id;
            $field_name     = !empty( $field_name ) ? $field_name : $id;


            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;



            ob_start();
            ?>
            <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?> id="field-wrapper-<?php esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-search-wrapper
            field-search-wrapper-<?php esc_attr($id); ?>">
                <input type='search' name='<?php echo esc_attr($field_name); ?>' id='<?php echo esc_attr($field_id); ?>' placeholder='<?php echo esc_attr($placeholder); ?>' value='<?php echo esc_attr($value); ?>' />
            </div>
            <script>
                <?php if(!empty($depends)) {?>
                jQuery('#field-wrapper-<?php esc_attr($id); ?>').formFieldDependency({});
                <?php } ?>
            </script>
            <?php

            return ob_get_clean();
        }

        public function field_month( $option ){

            $id 			= isset( $option['id'] ) ? $option['id'] : "";
            if(empty($id)) return;

            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();
            $placeholder 	= isset( $option['placeholder'] ) ? $option['placeholder'] : "";
            $default 	    = isset( $option['default'] ) ? $option['default'] : "";

            $value 	        = isset( $option['value'] ) ? $option['value'] : "";
            $value          = !empty($value) ? $value : $default;

            $field_id       = $id;
            $field_name     = !empty( $field_name ) ? $field_name : $id;


            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;



            ob_start();
            ?>
            <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?> id="field-wrapper-<?php esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-month-wrapper
            field-month-wrapper-<?php esc_attr($id); ?>">
                <input type='time' name='<?php echo esc_attr($field_name); ?>' id='<?php echo esc_attr($field_id); ?>' placeholder='<?php echo esc_attr($placeholder); ?>' value='<?php echo esc_attr($value); ?>' />
            </div>
            <script>
                <?php if(!empty($depends)) {?>
                jQuery('#field-wrapper-<?php esc_attr($id); ?>').formFieldDependency({});
                <?php } ?>
            </script>
            <?php

            return ob_get_clean();
        }

        public function field_date( $option ){

            $id 			= isset( $option['id'] ) ? $option['id'] : "";
            if(empty($id)) return;

            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();
            $placeholder 	= isset( $option['placeholder'] ) ? $option['placeholder'] : "";
            $default 	    = isset( $option['default'] ) ? $option['default'] : "";

            $value 	        = isset( $option['value'] ) ? $option['value'] : "";
            $value          = !empty($value) ? $value : $default;

            $field_id       = $id;
            $field_name     = !empty( $field_name ) ? $field_name : $id;


            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;



            ob_start();
            ?>
            <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?> id="field-wrapper-<?php esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-date-wrapper
            field-date-wrapper-<?php esc_attr($id); ?>">
                <input type='date' name='<?php echo esc_attr($field_name); ?>' id='<?php echo esc_attr($field_id); ?>' placeholder='<?php echo esc_attr($placeholder); ?>' value='<?php echo esc_attr($value); ?>' />
            </div>
            <script>
                <?php if(!empty($depends)) {?>
                jQuery('#field-wrapper-<?php esc_attr($id); ?>').formFieldDependency({});
                <?php } ?>
            </script>
            <?php
            return ob_get_clean();
        }

        public function field_url( $option ){

            $id 			= isset( $option['id'] ) ? $option['id'] : "";
            if(empty($id)) return;

            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();
            $placeholder 	= isset( $option['placeholder'] ) ? $option['placeholder'] : "";
            $default 	    = isset( $option['default'] ) ? $option['default'] : "";

            $value 	        = isset( $option['value'] ) ? $option['value'] : "";
            $value          = !empty($value) ? $value : $default;

            $field_id       = $id;
            $field_name     = !empty( $field_name ) ? $field_name : $id;


            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;



            ob_start();
            ?>
            <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?> class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-url-wrapper field-url-wrapper-<?php esc_attr($id); ?>">
                <input type='url' name='<?php echo esc_attr($field_name); ?>' id='<?php echo esc_attr($field_id); ?>' placeholder='<?php echo esc_attr($placeholder); ?>' value='<?php echo esc_attr($value); ?>' />
            </div>
            <script>
                <?php if(!empty($depends)) {?>
                jQuery('#field-wrapper-<?php esc_attr($id); ?>').formFieldDependency({});
                <?php } ?>
            </script>
            <?php
            return ob_get_clean();
        }



        public function field_time( $option ){

            $id 			= isset( $option['id'] ) ? $option['id'] : "";
            if(empty($id)) return;

            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();
            $placeholder 	= isset( $option['placeholder'] ) ? $option['placeholder'] : "";
            $default 	    = isset( $option['default'] ) ? $option['default'] : "";

            $value 	        = isset( $option['value'] ) ? $option['value'] : "";
            $value          = !empty($value) ? $value : $default;

            $field_id       = $id;
            $field_name     = !empty( $field_name ) ? $field_name : $id;


            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;



            ob_start();
            ?>
            <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?> id="field-wrapper-<?php esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-time-wrapper
            field-time-wrapper-<?php esc_attr($id); ?>">
                <input type='time' name='<?php echo esc_attr($field_name); ?>' id='<?php echo esc_attr($field_id); ?>' placeholder='<?php echo esc_attr($placeholder); ?>' value='<?php echo esc_attr($value); ?>' />
            </div>
            <script>
                <?php if(!empty($depends)) {?>
                jQuery('#field-wrapper-<?php esc_attr($id); ?>').formFieldDependency({});
                <?php } ?>
            </script>
            <?php
            return ob_get_clean();
        }


        public function field_tel( $option ){

            $id 			= isset( $option['id'] ) ? $option['id'] : "";
            if(empty($id)) return;

            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();
            $placeholder 	= isset( $option['placeholder'] ) ? $option['placeholder'] : "";
            $default 	    = isset( $option['default'] ) ? $option['default'] : "";

            $value 	        = isset( $option['value'] ) ? $option['value'] : "";
            $value          = !empty($value) ? $value : $default;

            $field_id       = $id;
            $field_name     = !empty( $field_name ) ? $field_name : $id;


            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;



            ob_start();
            ?>
            <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?> id="field-wrapper-<?php esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-tel-wrapper field-tel-wrapper-<?php
            esc_attr($id); ?>">
                <input type='tel' name='<?php echo esc_attr($field_name); ?>' id='<?php echo esc_attr($field_id); ?>' placeholder='<?php echo esc_attr($placeholder); ?>' value='<?php echo esc_attr($value); ?>' />
            </div>
            <script>
                <?php if(!empty($depends)) {?>
                jQuery('#field-wrapper-<?php esc_attr($id); ?>').formFieldDependency({});
                <?php } ?>
            </script>
            <?php
            return ob_get_clean();
        }

        public function field_text( $option ){

            $id 			= isset( $option['id'] ) ? $option['id'] : "";
            if(empty($id))  return;

            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();
            $placeholder 	= isset( $option['placeholder'] ) ? $option['placeholder'] : "";
            $visible 	    = isset( $option['visible'] ) ? $option['visible'] : "";
            $default 	    = isset( $option['default'] ) ? $option['default'] : "";

            $value 	        = isset( $option['value'] ) ? $option['value'] : "";
            $value          = !empty($value) ? $value : $default;

            $field_id       = $id;
            $field_name     = !empty( $field_name ) ? $field_name : $id;


            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;



            ob_start();


            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif; ?>


            <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?>
                    id="field-wrapper-<?php esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-text-wrapper
         field-text-wrapper-<?php esc_attr($id); ?>">
                <input type='text' name='<?php echo esc_attr($field_name); ?>' id='<?php echo esc_attr($field_id); ?>'
                       placeholder='<?php
                echo esc_attr($placeholder); ?>' value='<?php echo esc_attr($value); ?>' />
            </div>
            <script>
                jQuery('#field-wrapper-<?php esc_attr($id); ?>').formFieldDependency({});
            </script>
            <?php

            return ob_get_clean();
        }


        public function field_hidden( $option ){




            $id 			= isset( $option['id'] ) ? $option['id'] : "";
            if(empty($id)) return;

            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();
            $placeholder 	= isset( $option['placeholder'] ) ? $option['placeholder'] : "";

            $default 	    = isset( $option['default'] ) ? $option['default'] : "";

            $value 	        = isset( $option['value'] ) ? $option['value'] : "";
            $value          = !empty($value) ? $value : $default;

            $field_id       = $id;
            $field_name     = !empty( $field_name ) ? $field_name : $id;


            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;


            ob_start();
            ?>


            <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?> id="field-wrapper-<?php esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-hidden-wrapper
            field-hidden-wrapper-<?php esc_attr($id); ?>">
                <input type='hidden' name='<?php echo esc_attr($field_name); ?>' id='<?php echo esc_attr($field_id); ?>' placeholder='<?php
                echo esc_attr($placeholder); ?>' value='<?php echo esc_attr($value); ?>' />
            </div>
            <script>
                <?php if(!empty($depends)) {?>
                jQuery('#field-wrapper-<?php esc_attr($id); ?>').formFieldDependency({});
                <?php } ?>
            </script>
            <?php

            return ob_get_clean();
        }




        public function field_text_multi( $option ){

            $id 			= isset( $option['id'] ) ? $option['id'] : "";
            if(empty($id)) return;

            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();
            $placeholder 	= isset( $option['placeholder'] ) ? $option['placeholder'] : "";
            $remove_text 	= isset( $option['remove_text'] ) ? $option['remove_text'] : '<i class="fas fa-times"></i>';
            $sortable 	    = isset( $option['sortable'] ) ? $option['sortable'] : true;
            $default 	    = isset( $option['default'] ) ? $option['default'] : array();

            $values 	    = isset( $option['value'] ) ? $option['value'] : array();
            $values         = !empty($values) ? $values : $default;

            $field_id       = $id;
            $field_name     = !empty( $field_name ) ? $field_name : $id;



            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;



            ob_start();
            ?>
            <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?> id="field-wrapper-<?php esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-text-multi-wrapper
            field-text-multi-wrapper-<?php echo esc_attr($field_id); ?>">
                <span class="button add-item">Add</span>
                <div class="field-list" id="<?php echo esc_attr($field_id); ?>">
                    <?php
                    if(!empty($values)):
                        foreach ($values as $value):
                            ?>
                            <div class="item">
                                <input type='text' name='<?php echo esc_attr($field_name); ?>[]'  placeholder='<?php
                                echo esc_attr($placeholder); ?>' value="<?php echo esc_attr($value); ?>" /><span class="button remove" onclick="jQuery(this)
                                .parent().remove()"><?php echo esc_attr($remove_text); ?></span>
                                <?php if($sortable):?>
                                <span class="button sort"><i class="fas fa-arrows-alt"></i></span>
                                <?php endif; ?>
                            </div>
                        <?php
                        endforeach;
                    else:
                        ?>
                        <div class="item">
                            <input type='text' name='<?php echo esc_attr($field_name); ?>[]'  placeholder='<?php echo
                            esc_attr($placeholder); ?>'
                                   value='' /><span class="button remove" onclick="jQuery(this).parent().remove()
"><?php echo esc_attr($remove_text); ?></span>
                            <?php if($sortable):?>
                                <span class="button sort"><i class="fas fa-arrows-alt"></i></span>
                            <?php endif; ?>
                        </div>
                    <?php
                    endif;
                    ?>
                </div>
                <script>jQuery(document).ready(function($) {
                    jQuery(document).on('click', '.field-text-multi-wrapper-<?php esc_attr($id); ?> .add-item',function(){
                        html_<?php esc_attr($id); ?> = '<div class="item">';
                        html_<?php esc_attr($id); ?> += '<input type="text" name="<?php echo esc_attr($field_name); ?>[]" placeholder="<?php
                            echo esc_attr($placeholder); ?>" />';
                        html_<?php esc_attr($id); ?> += '<span class="button remove" onclick="jQuery(this).parent().remove()' +
                            '"><?php echo esc_attr($remove_text); ?></span>';
                        <?php if($sortable):?>
                        html_<?php esc_attr($id); ?> += ' <span class="button sort" ><i class="fas fa-arrows-alt"></i></span>';
                        <?php endif; ?>
                        html_<?php esc_attr($id); ?> += '</div>';
                        jQuery('.field-text-multi-wrapper-<?php esc_attr($id); ?> .field-list').append(html_<?php esc_attr($id); ?>);
                    })
                    jQuery( ".field-text-multi-wrapper-<?php esc_attr($id); ?> .field-list" ).sortable({ handle: '.sort' });
                })
                </script>
                <script>
                    <?php if(!empty($depends)) {?>
                    jQuery('#field-wrapper-<?php esc_attr($id); ?>').formFieldDependency({});
                    <?php } ?>
                </script>
            </div>
            <?php
            return ob_get_clean();

        }



        public function field_textarea( $option ){

            $id             = isset( $option['id'] ) ? $option['id'] : "";
            if(empty($id)) return;
            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();
            $visible 	    = isset( $option['visible'] ) ? $option['visible'] : "";
            $placeholder    = isset( $option['placeholder'] ) ? $option['placeholder'] : "";
            $default 	    = isset( $option['default'] ) ? $option['default'] : array();

            $value 	        = isset( $option['value'] ) ? $option['value'] : "";
            $value          = !empty($value) ? $value : $default;

            $field_id       = $id;
            $field_name     = !empty( $field_name ) ? $field_name : $id;



            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;



            ob_start();
            ?>
            <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?>
                    id="field-wrapper-<?php esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-textarea-wrapper field-textarea-wrapper-<?php echo esc_attr($field_id); ?>">
                <textarea name='<?php echo esc_attr($field_name); ?>' id='<?php echo esc_attr($field_id); ?>'
                          cols='40' rows='5'
                          placeholder='<?php echo esc_attr($placeholder); ?>'><?php echo esc_attr($value); ?></textarea>
            </div>

            <script>
                <?php if(!empty($depends)) {?>
                jQuery('#field-wrapper-<?php esc_attr($id); ?>').formFieldDependency({});
                <?php } ?>
            </script>

            <?php
            return ob_get_clean();
        }


        public function field_code( $option ){

            $id             = isset( $option['id'] ) ? $option['id'] : "";
            if(empty($id)) return;
            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();

            $placeholder    = isset( $option['placeholder'] ) ? $option['placeholder'] : "";
            $default 	    = isset( $option['default'] ) ? $option['default'] : array();
            $value 	        = isset( $option['value'] ) ? $option['value'] : "";
            $value          = !empty($value) ? $value : $default;
            $args	        = isset( $option['args'] ) ? $option['args'] : array(
                'lineNumbers'	=> true,
                'mode'	=> "javascript",
            );


            $field_id       = $id;
            $field_name     = !empty( $field_name ) ? $field_name : $id;


            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;


            ob_start();
            ?>
            <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?> id="field-wrapper-<?php esc_attr($id); ?>"  class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper  field-code-wrapper
            field-code-wrapper-<?php echo esc_attr($field_id); ?>">
                <textarea name='<?php echo esc_attr($field_name); ?>' id='<?php echo esc_attr($field_id); ?>' cols='40' rows='5' placeholder='<?php echo esc_attr($placeholder); ?>'><?php echo esc_attr($value); ?></textarea>
            </div>
            <script>
                var editor = CodeMirror.fromTextArea(document.getElementById("<?php echo esc_attr($field_id); ?>"), {
                    <?php
                    foreach ($args as $argkey=>$arg):
                        echo esc_html($argkey.':'.$arg.',');
                    endforeach;
                    ?>
                });
            </script>
            <script>
                <?php if(!empty($depends)) {?>
                jQuery('#field-wrapper-<?php esc_attr($id); ?>').formFieldDependency({});
                <?php } ?>
            </script>
            <?php
            return ob_get_clean();
        }

        public function field_checkbox( $option ){

            $id				= isset( $option['id'] ) ? $option['id'] : "";
            if(empty($id)) return;
            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : "";

            $default 		= isset( $option['default'] ) ? $option['default'] : array();
            $args			= isset( $option['args'] ) ? $option['args'] : array();
            $args			= is_array( $args ) ? $args : $this->args_from_string( $args );

            $value			= isset( $option['value'] ) ? $option['value'] : array();
            $value          = !empty($value) ?  $value : $default;

            $field_id       = $id;
            $field_name     = !empty( $field_name ) ? $field_name : $id;


            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;


            ob_start();
            ?>
            <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?>
                    id="field-wrapper-<?php esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-checkbox-wrapper
            field-checkbox-wrapper-<?php esc_attr($id); ?>">
                <?php
                foreach( $args as $key => $argName ):
                    $checked = (  $key == $value ) ? "checked" : "";
                    ?>
                    <label for='<?php echo esc_attr($field_id); ?>'><input class="<?php echo esc_attr($field_id); ?>" name='<?php echo esc_attr($field_name); ?>' type='checkbox' id='<?php echo esc_attr($field_id); ?>' value='<?php echo esc_attr($key); ?>' <?php echo esc_attr($checked); ?>><?php echo esc_attr($argName); ?></label><br>
                <?php
                endforeach;
                ?>
            </div>

            <script>
                <?php if(!empty($depends)) {?>
                jQuery('#field-wrapper-<?php esc_attr($id); ?>').formFieldDependency({});
                <?php } ?>
            </script>
            <?php
            return ob_get_clean();
        }

        public function field_checkbox_multi( $option ){

            $id				= isset( $option['id'] ) ? $option['id'] : "";
            if(empty($id)) return;
            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : "";

            $default 		= isset( $option['default'] ) ? $option['default'] : array();
            $args			= isset( $option['args'] ) ? $option['args'] : array();
            $args			= is_array( $args ) ? $args : $this->args_from_string( $args );

            $value			= isset( $option['value'] ) ? $option['value'] : array();
            $value          = !empty($value) ?  $value : $default;

            $field_id       = $id;
            $field_name     = !empty( $field_name ) ? $field_name.'[]' : $id.'[]';



            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;


            ob_start();
            ?>
            <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?> id="field-wrapper-<?php esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-checkbox-wrapper
            field-checkbox-wrapper-<?php esc_attr($id); ?>">
                <?php
                foreach( $args as $key => $argName ):
                    $checked = is_array( $value ) && in_array( $key, $value ) ? "checked" : "";
                    ?>
                    <label for='<?php echo esc_attr($field_id.'-'.$key); ?>'><input class="<?php echo esc_attr($field_id); ?>" name='<?php
                        echo esc_attr($field_name); ?>' type='checkbox' id='<?php echo esc_attr($field_id.'-'.$key); ?>' value='<?php
                        echo esc_attr($key); ?>' <?php echo esc_attr($checked); ?>><?php echo esc_attr($argName); ?></label><br>
                    <?php
                endforeach;
                ?>
            </div>
            <script>
                <?php if(!empty($depends)) {?>
                jQuery('#field-wrapper-<?php esc_attr($id); ?>').formFieldDependency({});
                <?php } ?>
            </script>
            <?php
            return ob_get_clean();
        }



        public function field_radio( $option ){

            $id				= isset( $option['id'] ) ? $option['id'] : "";
            if(empty($id)) return;
            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();
            $default 		= isset( $option['default'] ) ? $option['default'] : array();
            $args			= isset( $option['args'] ) ? $option['args'] : array();
            $args			= is_array( $args ) ? $args : $this->args_from_string( $args );

            $value			= isset( $option['value'] ) ? $option['value'] : '';
            $value          = !empty($value) ?  $value : $default;

            $field_id       = $id;
            $field_name     = !empty( $field_name ) ? $field_name : $id;



            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;


            ob_start();
            ?>
            <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?> id="field-wrapper-<?php esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-radio-wrapper
            field-radio-wrapper-<?php esc_attr($id); ?>">
                <?php
                foreach( $args as $key => $argName ):
                    $checked = ( $key == $value ) ? "checked" : "";
                    ?>
                    <label for='<?php echo esc_attr($field_id.'-'.$key); ?>'><input name='<?php echo esc_attr($field_name); ?>' type='radio' id='<?php echo esc_attr($field_id.'-'.$key); ?>' value='<?php echo esc_attr($key); ?>' <?php echo esc_attr($checked); ?>><?php echo esc_attr($argName); ?></label><br>
                <?php
                endforeach;
                ?>
            </div>
            <script>
                <?php if(!empty($depends)) {?>
                jQuery('#field-wrapper-<?php esc_attr($id); ?>').formFieldDependency({});
                <?php } ?>
            </script>
            <?php
            return ob_get_clean();
        }


        public function field_select( $option ){

            $id 	    = isset( $option['id'] ) ? $option['id'] : "";
            if(empty($id)) return;
            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();

            $args 	        = isset( $option['args'] ) ? $option['args'] : "";
            $args	    = is_array( $args ) ? $args : $this->args_from_string( $args );
            $default    = isset( $option['default'] ) ? $option['default'] : "";
            $multiple 	= isset( $option['multiple'] ) ? $option['multiple'] : false;

            $value		= isset( $option['value'] ) ? $option['value'] : '';
            $value      = !empty($value) ?  $value : $default;

            $field_id       = $id;
            $field_name     = !empty( $field_name ) ? $field_name : $id;

            ob_start();
            ?>
            <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?> id="field-wrapper-<?php esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-select-wrapper
            field-select-wrapper-<?php esc_attr($id); ?>">
                <?php
                if($multiple):
                    ?>
                    <select name='<?php echo esc_attr($field_name); ?>[]' id='<?php echo esc_attr($field_id); ?>' multiple>
                    <?php
                else:
                    ?>
                        <select name='<?php echo esc_attr($field_name); ?>' id='<?php echo esc_attr($field_id); ?>'>
                    <?php
                endif;

                foreach( $args as $key => $argName ):
                    if( $multiple ) $selected = is_array( $value ) && in_array( $key, $value ) ? "selected" : "";
                    else $selected = ($value == $key) ? "selected" : "";
                    ?>
                    <option <?php echo esc_attr($selected); ?> value='<?php echo esc_attr($key); ?>'><?php echo esc_attr($argName); ?></option>
                    <?php
                endforeach;
                ?>
                </select>
                <?php
                ?>
            </div>
            <script>
                <?php if(!empty($depends)) {?>
                jQuery('#field-wrapper-<?php esc_attr($id); ?>').formFieldDependency({});
                <?php } ?>
            </script>
            <?php
            return ob_get_clean();
        }


        public function field_range( $option ){

            $id 			= isset( $option['id'] ) ? $option['id'] : "";
            if(empty($id)) return;
            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();

            $default 	    = isset( $option['default'] ) ? $option['default'] : "";
            $args 	        = isset( $option['args'] ) ? $option['args'] : "";

            $value 	        = isset( $option['value'] ) ? $option['value'] : "";
            $value          = !empty($value) ? $value : $default;

            $min            = isset( $args['min'] ) ? $args['min'] : 0;
            $max            = isset( $args['max'] ) ? $args['max'] : 100;
            $step           = isset( $args['step'] ) ? $args['step'] : 1;

            $field_id       = $id;
            $field_name     = !empty( $field_name ) ? $field_name : $id;



            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;



            ob_start();
            ?>
            <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?> id="field-wrapper-<?php esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-range-wrapper
            field-range-wrapper-<?php esc_attr($id); ?>">
                <input type='range' min='<?php echo esc_attr($min); ?>' max='<?php echo esc_attr($max); ?>' step='<?php echo esc_attr($args['step']); ?>' name='<?php echo esc_attr($field_name); ?>' id='<?php echo esc_attr($field_id); ?>' value='<?php echo esc_attr($value); ?>' />
            </div>
            <script>
                <?php if(!empty($depends)) {?>
                jQuery('#field-wrapper-<?php esc_attr($id); ?>').formFieldDependency({});
                <?php } ?>
            </script>
            <?php
            return ob_get_clean();
        }

        public function field_range_input( $option ){

            $id 			= isset( $option['id'] ) ? $option['id'] : "";
            if(empty($id)) return;
            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();
            $default 	= isset( $option['default'] ) ? $option['default'] : "";
            $args 	= isset( $option['args'] ) ? $option['args'] : "";

            $value 	        = isset( $option['value'] ) ? $option['value'] : "";
            $value = !empty($value) ? $value : $default;

            $min            = isset( $args['min'] ) ? $args['min'] : 0;
            $max            = isset( $args['max'] ) ? $args['max'] : 100;
            $step           = isset( $args['step'] ) ? $args['step'] : 1;

            $field_id       = $id;
            $field_name     = !empty( $field_name ) ? $field_name : $id;


            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;



            ob_start();
            ?>
            <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?> id="field-wrapper-<?php esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-range-input-wrapper
            field-range-input-wrapper-<?php esc_attr($id); ?>">
                <input type="number" class="range-val" name='<?php echo esc_attr($field_name); ?>' value="<?php echo esc_attr($value); ?>">
                <input type='range' class='range-hndle' id="<?php echo esc_attr($field_id); ?>" min='<?php echo $args['min']; ?>' max='<?php echo
                $args['max']; ?>' step='<?php echo $args['step']; ?>' value='<?php echo esc_attr($value); ?>' />
                <script>jQuery(document).ready(function($) {
                        jQuery(document).on('change', '.field-range-input-wrapper-<?php esc_attr($id); ?> .range-hndle', function() {
                            val = $(this).val();
                            $('.field-range-input-wrapper-<?php esc_attr($id); ?> .range-val').val(val);
                        })
                        jQuery(document).on('keyup', '.field-range-input-wrapper-<?php esc_attr($id); ?> .range-val', function() {
                            val = $(this).val();
                            console.log(val);
                            $('.field-range-input-wrapper-<?php esc_attr($id); ?> .range-hndle').val(val);
                        })
                    })
                </script>
            </div>
            <script>
                <?php if(!empty($depends)) {?>
                jQuery('#field-wrapper-<?php esc_attr($id); ?>').formFieldDependency({});
                <?php } ?>
            </script>
            <?php
            return ob_get_clean();
        }


        public function field_switch( $option ){

            $id				= isset( $option['id'] ) ? $option['id'] : "";
            if(empty($id)) return;
            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();
            $default 		= isset( $option['default'] ) ? $option['default'] : '';
            $args			= isset( $option['args'] ) ? $option['args'] : array();
            $args			= is_array( $args ) ? $args : $this->args_from_string( $args );

            $value 	        = isset( $option['value'] ) ? $option['value'] : "";
            $value          = !empty($value) ? $value : $default;

            $field_id       = $id;
            $field_name     = !empty( $field_name ) ? $field_name : $id;


            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;



            ob_start();
            ?>
            <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?> id="field-wrapper-<?php esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-switch-wrapper
            field-switch-wrapper-<?php esc_attr($id); ?>">
                <?php
                foreach( $args as $key => $argName ):
                    $checked = ( $key == $value ) ? "checked" : "";
                    ?><label class="<?php echo esc_attr($checked); ?>" for='<?php esc_attr($id); ?>-<?php echo esc_attr($key); ?>'><input name='<?php echo esc_attr($field_name); ?>' type='radio' id='<?php esc_attr($id); ?>-<?php echo esc_attr($key); ?>' value='<?php echo esc_attr($key); ?>' <?php echo esc_attr($checked); ?>><span class="sw-button"><?php echo esc_attr($argName); ?></span></label><?php
                endforeach;
                ?>

            </div>
            <script>jQuery(document).ready(function($) {
                    jQuery(document).on('click', '.field-switch-wrapper-<?php esc_attr($id); ?> .sw-button', function() {
                        jQuery('.field-switch-wrapper-<?php esc_attr($id); ?> label').removeClass('checked');
                        if(jQuery(this).parent().hasClass('checked')){
                            jQuery(this).parent().removeClass('checked');
                        }else{
                            jQuery(this).parent().addClass('checked');
                        }
                    })
                })
            </script>
            <script>
                <?php if(!empty($depends)) {?>
                jQuery('#field-wrapper-<?php esc_attr($id); ?>').formFieldDependency({});
                <?php } ?>
            </script>
            <?php
            return ob_get_clean();
        }



        public function field_switch_multi( $option ){

            $id				= isset( $option['id'] ) ? $option['id'] : "";
            if(empty($id)) return;
            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();
            $default 		= isset( $option['default'] ) ? $option['default'] : '';
            $args			= isset( $option['args'] ) ? $option['args'] : array();
            $args			= is_array( $args ) ? $args : $this->args_from_string( $args );

            $value			= isset( $option['value'] ) ? $option['value'] : array();
            $value      = !empty($value) ?  $value : $default;

            $field_id       = $id;
            $field_name     = !empty( $field_name ) ? $field_name : $id;


            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;



            ob_start();
            ?>
            <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?> id="field-wrapper-<?php esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-switch-multi-wrapper
            field-switch-multi-wrapper-<?php echo
            $id; ?>">
                <?php
                foreach( $args as $key => $argName ):
                    $checked = is_array( $value ) && in_array( $key, $value ) ? "checked" : "";
                    ?><label class="<?php echo esc_attr($checked); ?>" for='<?php echo esc_attr($field_id); ?>-<?php echo esc_attr($key); ?>'><input name='<?php echo esc_attr($field_name); ?>[]' type='checkbox' id='<?php echo esc_attr($field_id); ?>-<?php echo esc_attr($key); ?>' value='<?php echo esc_attr($key); ?>' <?php echo esc_attr($checked); ?>><span class="sw-button"><?php echo esc_attr($argName); ?></span></label><?php
                endforeach;
                ?>
            </div>
            <script>
                jQuery(document).ready(function($) {
                    jQuery(document).on('click', '.field-switch-multi-wrapper-<?php esc_attr($id); ?> .sw-button', function() {
                        if(jQuery(this).parent().hasClass('checked')){
                            jQuery(this).parent().removeClass('checked');
                        }else{
                            jQuery(this).parent().addClass('checked');
                        }
                    })
                })
            </script>
            <script>
                <?php if(!empty($depends)) {?>
                jQuery('#field-wrapper-<?php esc_attr($id); ?>').formFieldDependency({});
                <?php } ?>
            </script>
            <?php
            return ob_get_clean();
        }



        public function field_switch_img( $option ){

            $id				= isset( $option['id'] ) ? $option['id'] : "";
            if(empty($id)) return;
            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();
            $width			= isset( $option['width'] ) ? $option['width'] : "";
            $height			= isset( $option['height'] ) ? $option['height'] : "";
            $default 		= isset( $option['default'] ) ? $option['default'] : '';
            $args			= isset( $option['args'] ) ? $option['args'] : array();
            $args			= is_array( $args ) ? $args : $this->args_from_string( $args );

            $value 	        = isset( $option['value'] ) ? $option['value'] : "";
            $value          = !empty($value) ? $value : $default;

            $field_id       = $id;
            $field_name     = !empty( $field_name ) ? $field_name : $id;



            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;



            ob_start();
            ?>
            <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?> id="field-wrapper-<?php esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-switch-img-wrapper
            field-switch-img-wrapper-<?php esc_attr($id); ?>">
                <?php
                foreach( $args as $key => $arg ):
                    $src = isset( $arg['src'] ) ? $arg['src'] : "";

                    $checked = ( $key == $value ) ? "checked" : "";
                    ?><label class="<?php echo esc_attr($checked); ?>" for='<?php esc_attr($id); ?>-<?php echo esc_attr($key); ?>'><input name='<?php echo esc_attr($field_name); ?>' type='radio' id='<?php esc_attr($id); ?>-<?php echo esc_attr($key); ?>' value='<?php echo esc_attr($key); ?>' <?php echo esc_attr($checked); ?>><span class="sw-button"><img src="<?php echo $src; ?>"> </span></label><?php

                endforeach;
                ?>
            </div>
            <script>
                jQuery(document).ready(function($) {
                    jQuery(document).on('click', '.field-switch-img-wrapper-<?php esc_attr($id); ?> .sw-button img', function() {
                        jQuery('.field-switch-img-wrapper-<?php esc_attr($id); ?> label').removeClass('checked');
                        if(jQuery(this).parent().parent().hasClass('checked')){
                            jQuery(this).parent().parent().removeClass('checked');
                        }else{
                            jQuery(this).parent().parent().addClass('checked');
                        }
                    })
                })

            </script>
            <script>
                <?php if(!empty($depends)) {?>
                jQuery('#field-wrapper-<?php esc_attr($id); ?>').formFieldDependency({});
                <?php } ?>
            </script>
            <?php
            return ob_get_clean();
        }



        public function field_time_format( $option ){

            $id 			= isset( $option['id'] ) ? $option['id'] : "";
            if(empty($id)) return;
            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();
            $default 	= isset( $option['default'] ) ? $option['default'] : "";
            $args 	= isset( $option['args'] ) ? $option['args'] : "";

            $value 	= isset( $option['value'] ) ? $option['value'] : "";
            $value 	 		= !empty($value) ? $value : $default;

            $field_id       = $id;
            $field_name     = !empty( $field_name ) ? $field_name : $id;


            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;




            ob_start();
            ?>
            <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?> id="field-wrapper-<?php esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-time-format-wrapper
            field-time-format-wrapper-<?php esc_attr($id); ?>">
                <div class="format-list">
                    <?php
                    if(!empty($args)):
                        foreach ($args as $item):
                            $checked = ($item == $value) ? 'checked':false;
                            ?>
                            <div class="format" datavalue="<?php echo $item; ?>">
                                <label><input type="radio" <?php echo esc_attr($checked); ?> name="preset_<?php esc_attr($id); ?>" value="<?php echo $item; ?>">
                                    <span class="name"><?php echo date($item); ?></span></label>
                                <span class="format"><code><?php echo $item; ?></code></span>
                            </div>
                        <?php
                        endforeach;
                        ?>
                        <div class="format-value">
                            <span class="format"><input value="<?php echo esc_attr($value); ?>" name="<?php echo esc_attr($field_name); ?>"></span>
                            <div class="">Preview: <?php echo date($value); ?></div>
                        </div>
                    <?php
                    endif;
                    ?>
                </div>
            </div>
            <script>
                jQuery(document).ready(function($) {
                    jQuery(document).on('click', '.field-time-format-wrapper-<?php esc_attr($id); ?> .format-list .format',
                        function () {
                        value = $(this).attr('datavalue');
                        $('.field-time-format-wrapper-<?php esc_attr($id); ?> .format-value input').val(value);
                    })
                });
            </script>
            <script>
                <?php if(!empty($depends)) {?>
                jQuery('#field-wrapper-<?php esc_attr($id); ?>').formFieldDependency({});
                <?php } ?>
            </script>
            <?php
            return ob_get_clean();
        }






        public function field_date_format( $option ){

            $id 			= isset( $option['id'] ) ? $option['id'] : "";
            if(empty($id)) return;
            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();
            $default 	= isset( $option['default'] ) ? $option['default'] : "";
            $args 	= isset( $option['args'] ) ? $option['args'] : "";

            $value 	= isset( $option['value'] ) ? $option['value'] : "";
            $value 	 		= !empty($value) ? $value : $default;

            $field_id       = $id;
            $field_name     = !empty( $field_name ) ? $field_name : $id;


            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;


            ob_start();
            ?>
            <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?> id="field-wrapper-<?php esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-date-format-wrapper
            field-date-format-wrapper-<?php esc_attr($id); ?>">
                <div class="format-list">
                    <?php
                    if(!empty($args)):
                        foreach ($args as $item):
                            $checked = ($item == $value) ? 'checked':false;
                            ?>
                            <div class="format" datavalue="<?php echo $item; ?>">
                                <label><input type="radio" <?php echo esc_attr($checked); ?> name="preset_<?php esc_attr($id); ?>" value="<?php echo $item; ?>"><span class="name"><?php echo date($item); ?></span></label>
                                <span class="format"><code><?php echo $item; ?></code></span>
                            </div>
                            <?php
                        endforeach;
                        ?>
                        <div class="format-value">
                            <span class="format"><input value="<?php echo esc_attr($value); ?>" name="<?php echo esc_attr($field_name); ?>"></span>
                            <div class="">Preview: <?php echo date($value); ?></div>
                        </div>
                    <?php
                    endif;
                    ?>
                </div>
            </div>
            <script>
                jQuery(document).ready(function($) {
                    jQuery(document).on('click', '.field-date-format-wrapper-<?php esc_attr($id); ?> .format-list .format', function () {
                        value = $(this).attr('datavalue');
                        $('.field-date-format-wrapper-<?php esc_attr($id); ?> .format-value input').val(value);
                    })
                });
            </script>
            <script>
                <?php if(!empty($depends)) {?>
                jQuery('#field-wrapper-<?php esc_attr($id); ?>').formFieldDependency({});
                <?php } ?>
            </script>
            <?php
            return ob_get_clean();
        }


        public function field_datepicker( $option ){

            $id 			= isset( $option['id'] ) ? $option['id'] : "";
            if(empty($id)) return;
            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();
            $placeholder 	= isset( $option['placeholder'] ) ? $option['placeholder'] : "";
            $default 	    = isset( $option['default'] ) ? $option['default'] : "";
            $date_format	= isset( $option['date_format'] ) ? $option['date_format'] : "dd-mm-yy";

            $value 	        = isset( $option['value'] ) ? $option['value'] : "";
            $value          = !empty($value) ?$value : $default;

            $field_id       = $id;
            $field_name     = !empty( $field_name ) ? $field_name : $id;


            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;


            ob_start();
            ?>
            <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?> id="field-wrapper-<?php esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-datepicker-wrapper
            field-datepicker-wrapper-<?php esc_attr($id); ?>">
                <input type='text' name='<?php echo esc_attr($field_name); ?>' id='<?php echo esc_attr($field_id); ?>' placeholder='<?php echo esc_attr($placeholder); ?>' value='<?php echo esc_attr($value); ?>' />
            </div>
            <script>
                jQuery(document).ready(function($) {
                    $('#<?php echo esc_attr($field_id); ?>').datepicker({dateFormat : '<?php echo $date_format; ?>'})});
            </script>
            <script>
                <?php if(!empty($depends)) {?>
                jQuery('#field-wrapper-<?php esc_attr($id); ?>').formFieldDependency({});
                <?php } ?>
            </script>
            <?php
            return ob_get_clean();
        }






        public function field_colorpicker( $option ){

            $id 			= isset( $option['id'] ) ? $option['id'] : "";
            if(empty($id)) return;
            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();
            $placeholder 	= isset( $option['placeholder'] ) ? $option['placeholder'] : "";
            $default 	    = isset( $option['default'] ) ? $option['default'] : "";

            $value 	        = isset( $option['value'] ) ? $option['value'] : "";
            $value          = !empty($value) ? $value : $default;

            $field_id       = $id;
            $field_name     = !empty( $field_name ) ? $field_name : $id;


            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;


            ob_start();
            ?>
            <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?> id="field-wrapper-<?php esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-colorpicker-wrapper
            field-colorpicker-wrapper-<?php esc_attr($id); ?>">
                <input type='text'  name='<?php echo esc_attr($field_name); ?>' id='<?php echo esc_attr($field_id); ?>' placeholder='<?php echo esc_attr($placeholder); ?>' value='<?php echo esc_attr($value); ?>' />
            </div>
            <script>jQuery(document).ready(function($) { $('#<?php echo esc_attr($field_id); ?>').wpColorPicker();});</script>
            <script>
                <?php if(!empty($depends)) {?>
                jQuery('#field-wrapper-<?php esc_attr($id); ?>').formFieldDependency({});
                <?php } ?>
            </script>
            <?php
            return ob_get_clean();
        }


        public function field_colorpicker_multi( $option ){

            $id 			= isset( $option['id'] ) ? $option['id'] : "";
            if(empty($id)) return;
            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();
            $placeholder 	= isset( $option['placeholder'] ) ? $option['placeholder'] : "";
            $value 	        = isset( $option['value'] ) ? $option['value'] : "";
            $default 	= isset( $option['default'] ) ? $option['default'] : array();

            $values = !empty($value) ? $value : $default;

            $field_id       = $id;
            $field_name     = !empty( $field_name ) ? $field_name : $id;


            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;



            ob_start();
            if(!empty($values)):
                ?>
                <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?> id="field-wrapper-<?php esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-colorpicker-multi-wrapper
                field-colorpicker-multi-wrapper-<?php esc_attr($id);
                ?>">
                    <div class="button add">Add</div>
                    <div class="item-list">
                        <?php
                        foreach ($values as $value):
                            ?>
                            <div class="item">
                                <span class="button remove">X</span>
                                <input type='text' name='<?php echo esc_attr($field_name); ?>[]' value='<?php echo esc_attr($value); ?>' />
                            </div>
                        <?php
                        endforeach;
                        ?>
                    </div>
                </div>
                <?php
            endif;
            ?>
            <script>
                jQuery(document).ready(function($) {
                    jQuery(document).on('click', '.field-colorpicker-multi-wrapper-<?php esc_attr($id); ?> .item-list .remove', function(){
                        jQuery(this).parent().remove();
                    })
                    jQuery(document).on('click', '.field-colorpicker-multi-wrapper-<?php esc_attr($id); ?> .add', function() {
                        html='<div class="item">';
                        html+='<span class="button remove">X</span> <input type="text"  name="<?php echo esc_attr($field_name); ?>[]" value="" />';
                        html+='</div>';
                        $('.field-colorpicker-multi-wrapper-<?php esc_attr($id); ?> .item-list').append(html);
                        $('.field-colorpicker-multi-wrapper-<?php esc_attr($id); ?> input').wpColorPicker();
                    })
                    $('.field-colorpicker-multi-wrapper-<?php esc_attr($id); ?> input').wpColorPicker();
                });
            </script>
            <script>
                <?php if(!empty($depends)) {?>
                jQuery('#field-wrapper-<?php esc_attr($id); ?>').formFieldDependency({});
                <?php } ?>
            </script>
            <?php

            return ob_get_clean();
        }




        public function field_link_color( $option ){

            $id 			= isset( $option['id'] ) ? $option['id'] : "";
            if(empty($id)) return;
            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();
            $placeholder 	= isset( $option['placeholder'] ) ? $option['placeholder'] : "";
            $value 	        = isset( $option['value'] ) ? $option['value'] : "";
            $args 	        = isset( $option['args'] ) ? $option['args'] : array('link'	=> '#1B2A41','hover' => '#3F3244','active' => '#60495A','visited' => '#7D8CA3' );

            $field_id       = $id;
            $field_name     = !empty( $field_name ) ? $field_name : $id;


            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;



            ob_start();
            ?>
            <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?> id="field-wrapper-<?php esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-link-color-wrapper
            field-link-color-wrapper-<?php esc_attr($id); ?>">
            <?php
            if(!empty($values) && is_array($values)):
                foreach ($args as $argindex=>$value):
                    ?>
                    <div>
                        <div class="item"><span class="title">a:<?php echo $argindex; ?> Color</span><div class="colorpicker"><input type='text' class='<?php esc_attr($id); ?>' name='<?php echo esc_attr($field_name); ?>[<?php echo $argindex; ?>]'   value='<?php echo $values[$argindex]; ?>' /></div></div>
                    </div>
                    <?php
                endforeach;
            else:
                foreach ($args as $argindex=>$value):
                    ?>
                    <div>
                        <div class="item"><span class="title">a:<?php echo $argindex; ?> Color</span><div class="colorpicker"><input type='text' class='<?php esc_attr($id); ?>' name='<?php echo esc_attr($field_name); ?>[<?php echo $argindex; ?>]'   value='<?php echo esc_attr($value); ?>' /></div></div>
                    </div>
                <?php
                endforeach;
            endif;
            ?>
            </div>
            <script>jQuery(document).ready(function($) { $('.<?php esc_attr($id); ?>').wpColorPicker();});</script>
            <script>
                <?php if(!empty($depends)) {?>
                jQuery('#field-wrapper-<?php esc_attr($id); ?>').formFieldDependency({});
                <?php } ?>
            </script>
            <?php
            return ob_get_clean();
        }






        public function field_user( $option ){

            $id 			= isset( $option['id'] ) ? $option['id'] : "";
            if(empty($id)) return;
            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();

            $args 			= isset( $option['args'] ) ? $option['args'] : array();
            $default 	    = isset( $option['default'] ) ? $option['default'] : array();
            $icons		    = is_array( $args ) ? $args :  $this->args_from_string( $args );

            $value 	        = isset( $option['value'] ) ? $option['value'] : "";
            $values         = !empty($value) ? $value : $default;

            $field_id       = $id;
            $field_name     = !empty( $field_name ) ? $field_name : $id;



            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;


            ob_start();
            ?>
            <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?> id="field-wrapper-<?php esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-user-multi-wrapper
            field-user-multi-wrapper-<?php esc_attr($id); ?>">
                <div class="users-wrapper" >
                    <?php if(!empty($values)):
                        foreach ($values as $user_id):
                            $get_avatar_url = get_avatar_url($user_id,array('size'=>'60'));

                            ?><div class="item" title="click to remove"><img src="<?php echo $get_avatar_url; ?>" /><input type="hidden" name="<?php echo esc_attr($field_name); ?>[]" value="<?php echo $user_id; ?>"></div><?php
                        endforeach;
                    endif; ?>
                </div>
                <div class="user-list">
                    <div class="button select-user" >Choose User</div>
                    <div class="search-user" ><input class="" type="text" placeholder="Start typing..."></div>
                    <ul>
                        <?php
                        if(!empty($icons)):
                            foreach ($icons as $user_id=>$iconTitle):
                                $user_data = get_user_by('ID',$user_id);
                                $get_avatar_url = get_avatar_url($user_id,array('size'=>'60'));
                                ?>
                                <li title="<?php echo $user_data->display_name; ?>(#<?php echo $user_id; ?>)"
                                    userSrc="<?php echo
                                $get_avatar_url; ?>"
                                    iconData="<?php echo $user_id; ?>"><img src="<?php echo $get_avatar_url; ?>" />
                                </li>
                            <?php
                            endforeach;
                        endif;
                        ?>
                    </ul>
                </div>

            </div>


            <script>
                jQuery(document).ready(function($){
                    jQuery(document).on('click', '.field-user-multi-wrapper-<?php esc_attr($id); ?> .users-wrapper .item', function(){
                        jQuery(this).remove();
                    })
                    jQuery(document).on('click', '.field-user-multi-wrapper-<?php esc_attr($id); ?> .select-user', function(){
                        if(jQuery(this).parent().hasClass('active')){
                            jQuery(this).parent().removeClass('active');
                        }else{
                            jQuery(this).parent().addClass('active');
                        }
                    })
                    jQuery(document).on('keyup', '.field-user-multi-wrapper-<?php esc_attr($id); ?> .search-user input', function(){
                        text = jQuery(this).val();
                        $('.field-user-multi-wrapper-<?php esc_attr($id); ?> .user-list li').each(function( index ) {
                            console.log( index + ": " + $( this ).attr('title') );
                            title = $( this ).attr('title');
                            n = title.indexOf(text);
                            if(n<0){
                                $( this ).hide();
                            }else{
                                $( this ).show();
                            }
                        });
                    })
                    jQuery(document).on('click', '.field-user-multi-wrapper-<?php esc_attr($id); ?> .user-list li', function(){
                        iconData = jQuery(this).attr('iconData');
                        userSrc = jQuery(this).attr('userSrc');
                        html = '';
                        html = '<div class="item" title="click to remove"><img src="'+userSrc+'" /><input type="hidden" ' +
                        'name="<?php echo esc_attr($field_name); ?>[]" value="'+iconData+'"></div>';
                        jQuery('.field-user-multi-wrapper-<?php esc_attr($id); ?> .users-wrapper').append(html);
                    })
                })
            </script>
            <script>
                <?php if(!empty($depends)) {?>
                jQuery('#field-wrapper-<?php esc_attr($id); ?>').formFieldDependency({});
                <?php } ?>
            </script>
            <?php
            return ob_get_clean();
        }



        public function field_icon( $option ){

            $id 			= isset( $option['id'] ) ? $option['id'] : "";
            if(empty($id)) return;
            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();
            $args 			= isset( $option['args'] ) ? $option['args'] : array();
            $default 	    = isset( $option['default'] ) ? $option['default'] : "";
            $value 	        = isset( $option['value'] ) ? $option['value'] : "";
            $value          = !empty($value) ? $value : $default;

            $icons		    = is_array( $args ) ? $args : $this->args_from_string( $args );

            $field_id       = $id;
            $field_name     = !empty( $field_name ) ? $field_name : $id;


            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;


            ob_start();
            ?>
            <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?> id="field-wrapper-<?php esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-icon-wrapper
            field-icon-wrapper-<?php esc_attr($id); ?>">
                <div class="icon-wrapper" >
                    <span><i class="<?php echo esc_attr($value); ?>"></i></span>
                    <input type="hidden" name="<?php echo esc_attr($field_name); ?>" value="<?php echo esc_attr($value); ?>">
                </div>
                <div class="icon-list">
                    <div class="button select-icon" >Choose Icon</div>
                    <div class="search-icon" ><input class="" type="text" placeholder="start typing..."></div>
                    <ul>
                        <?php
                        if(!empty($icons)):
                            foreach ($icons as $iconindex=>$iconTitle):
                                ?>
                                <li title="<?php echo $iconTitle; ?>" iconData="<?php echo $iconindex; ?>"><i class="<?php echo $iconindex; ?>"></i></li>
                                <?php
                            endforeach;
                        endif;
                        ?>
                    </ul>
                </div>
            </div>
            <script>jQuery(document).ready(function($){
                jQuery(document).on('click', '.field-icon-wrapper-<?php esc_attr($id); ?> .select-icon', function(){
                    if(jQuery(this).parent().hasClass('active')){
                        jQuery(this).parent().removeClass('active');
                    }else{
                        jQuery(this).parent().addClass('active');
                    }
                })
                jQuery(document).on('keyup', '.field-icon-wrapper-<?php esc_attr($id); ?> .search-icon input', function(){
                    text = jQuery(this).val();
                    $('.field-icon-wrapper-<?php esc_attr($id); ?> .icon-list li').each(function( index ) {
                        console.log( index + ": " + $( this ).attr('title') );
                        title = $( this ).attr('title');
                        n = title.indexOf(text);
                        if(n<0){
                            $( this ).hide();
                        }else{
                            $( this ).show();
                        }
                    });
                })
                jQuery(document).on('click', '.field-icon-wrapper-<?php esc_attr($id); ?> .icon-list li', function(){
                    iconData = jQuery(this).attr('iconData');
                    html = '<i class="'+iconData+'"></i>';
                    jQuery('.field-icon-wrapper-<?php esc_attr($id); ?> .icon-wrapper span').html(html);
                    jQuery('.field-icon-wrapper-<?php esc_attr($id); ?> .icon-wrapper input').val(iconData);
                })
            })
            </script>
            <script>
                <?php if(!empty($depends)) {?>
                jQuery('#field-wrapper-<?php esc_attr($id); ?>').formFieldDependency({});
                <?php } ?>
            </script>
            <?php
            return ob_get_clean();
        }



        public function field_icon_multi( $option ){

            $id 			= isset( $option['id'] ) ? $option['id'] : "";
            if(empty($id)) return;
            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();
            $args 			= isset( $option['args'] ) ? $option['args'] : array();
            $default 	    = isset( $option['default'] ) ? $option['default'] : array();
            $icons		    = is_array( $args ) ? $args :  $this->args_from_string( $args );

            $value 	        = isset( $option['value'] ) ? $option['value'] : "";
            $values         = !empty($value) ? $value : $default;

            $field_id       = $id;
            $field_name     = !empty( $field_name ) ? $field_name : $id;


            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;



            ob_start();
            ?>
            <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?> id="field-wrapper-<?php esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-icon-multi-wrapper
            field-icon-multi-wrapper-<?php esc_attr($id); ?>">
                <div class="icons-wrapper" >
                    <?php if(!empty($values)):
                        foreach ($values as $value):
                            ?><div class="item" title="click to remove"><span><i class="<?php echo esc_attr($value); ?>"></i></span><input type="hidden" name="<?php echo esc_attr($field_name); ?>[]" value="<?php echo esc_attr($value); ?>"></div><?php
                        endforeach;
                    endif; ?>
                </div>
                <div class="icon-list">
                    <div class="button select-icon" >Choose Icon</div>
                    <div class="search-icon" ><input class="" type="text" placeholder="start typing..."></div>
                    <ul>
                        <?php
                        if(!empty($icons)):
                            foreach ($icons as $iconindex=>$iconTitle):
                                ?><li title="<?php echo $iconTitle; ?>" iconData="<?php echo $iconindex; ?>"><i class="<?php echo $iconindex; ?>"></i></li><?php
                            endforeach;
                        endif;
                        ?>
                    </ul>
                </div>
            </div>


            <script>
                jQuery(document).ready(function($){
                    jQuery(document).on('click', '.field-icon-multi-wrapper-<?php esc_attr($id); ?> .icons-wrapper .item', function(){
                        jQuery(this).remove();
                    })
                    jQuery(document).on('click', '.field-icon-multi-wrapper-<?php esc_attr($id); ?> .select-icon', function(){
                        if(jQuery(this).parent().hasClass('active')){
                            jQuery(this).parent().removeClass('active');
                        }else{
                            jQuery(this).parent().addClass('active');
                        }
                    })
                    jQuery(document).on('keyup', '.field-icon-multi-wrapper-<?php esc_attr($id); ?> .search-icon input', function(){
                        text = jQuery(this).val();
                        $('.field-icon-multi-wrapper-<?php esc_attr($id); ?> .icon-list li').each(function( index ) {
                            console.log( index + ": " + $( this ).attr('title') );
                            title = $( this ).attr('title');
                            n = title.indexOf(text);
                            if(n<0){
                                $( this ).hide();
                            }else{
                                $( this ).show();
                            }
                        });
                    })
                    jQuery(document).on('click', '.field-icon-multi-wrapper-<?php esc_attr($id); ?> .icon-list li', function(){
                        iconData = jQuery(this).attr('iconData');
                        html = '<div class="item" title="click to remove"><span><i class="'+iconData+'"></i></span><input type="hidden" name="<?php echo esc_attr($field_name); ?>[]" value="'+iconData+'"></div>';
                        jQuery('.field-icon-multi-wrapper-<?php esc_attr($id); ?> .icons-wrapper').append(html);
                    })
                })
            </script>
            <script>
                <?php if(!empty($depends)) {?>
                jQuery('#field-wrapper-<?php esc_attr($id); ?>').formFieldDependency({});
                <?php } ?>
            </script>
            <?php
            return ob_get_clean();
        }









        public function field_number( $option ){

            $id 			= isset( $option['id'] ) ? $option['id'] : "";
            if(empty($id)) return;
            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();
            $default 			= isset( $option['default'] ) ? $option['default'] : "";
            $placeholder 	= isset( $option['placeholder'] ) ? $option['placeholder'] : "";

            $value 			= isset( $option['value '] ) ? $option['value '] : "";
            $value = !empty($value) ? $value : $default;

            $field_id       = $id;
            $field_name     = !empty( $field_name ) ? $field_name : $id;


            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;



            ob_start();
            ?>
             <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?> id="field-wrapper-<?php esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-number-wrapper
             field-number-wrapper-<?php esc_attr($id); ?>">
                <input type='number' class='' name='<?php echo esc_attr($field_name); ?>' id='<?php echo esc_attr($field_id); ?>' placeholder='<?php echo esc_attr($placeholder); ?>' value='<?php echo esc_attr($value); ?>' />
            </div>
            <script>
                <?php if(!empty($depends)) {?>
                jQuery('#field-wrapper-<?php esc_attr($id); ?>').formFieldDependency({});
                <?php } ?>
            </script>
            <?php
            return ob_get_clean();
        }



        public function field_wp_editor( $option ){

            $id = isset( $option['id'] ) ? $option['id'] : "";
            if(empty($id)) return;
            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();
            $placeholder = isset( $option['placeholder'] ) ? $option['placeholder'] : "";
            $default = isset( $option['default'] ) ? $option['default'] : "";
            $editor_settings= isset( $option['editor_settings'] ) ? $option['editor_settings'] : array('textarea_name'=>$id);

            $value 			= isset( $option['value '] ) ? $option['value '] : "";
            $value = !empty($value) ? $value : $default;

            $field_id       = $id;
            $field_name     = !empty( $field_name ) ? $field_name : $id;


            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;



            ob_start();
            ?>
            <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?> id="field-wrapper-<?php esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-wp_editor-wrapper
            field-wp_editor-wrapper-<?php esc_attr($id); ?>">
                <?php
                wp_editor( $value, $id, $settings = $editor_settings);
                ?>
            </div>
            <script>
                <?php if(!empty($depends)) {?>
                jQuery('#field-wrapper-<?php esc_attr($id); ?>').formFieldDependency({});
                <?php } ?>
            </script>
            <?php
            return ob_get_clean();
        }




        public function field_select2( $option ){

            $id 	    = isset( $option['id'] ) ? $option['id'] : "";
            if(empty($id)) return;
            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();

            $args 	        = isset( $option['args'] ) ? $option['args'] : "";
            $args	    = is_array( $args ) ? $args : $this->args_from_string( $args );
            $default    = isset( $option['default'] ) ? $option['default'] : "";
            $multiple 	= isset( $option['multiple'] ) ? $option['multiple'] : false;

            $value		= isset( $option['value'] ) ? $option['value'] : '';
            $value      = !empty($value) ?  $value : $default;

            $field_id       = $id;
            $field_name     = !empty( $field_name ) ? $field_name : $id;

            if($multiple):
                $value = !empty($value) ? $value : array();
            endif;


            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;



            ob_start();
            ?>
            <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?> id="field-wrapper-<?php esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-select2-wrapper
            field-select2-wrapper-<?php esc_attr($id); ?>">
                <?php
                if($multiple):
                ?>
                <select name='<?php echo esc_attr($field_name); ?>[]' id='<?php echo esc_attr($field_id); ?>' multiple>
                    <?php
                    else:
                    ?>
                    <select name='<?php echo esc_attr($field_name); ?>' id='<?php echo esc_attr($field_id); ?>'>
                        <?php
                        endif;
                        foreach( $args as $key => $name ):

                            if( $multiple ) $selected = in_array( $key, $value ) ? "selected" : "";
                            else $selected = $value == $key ? "selected" : "";
                            ?>
                            <option <?php echo $selected; ?> value='<?php echo esc_attr($key); ?>'><?php echo $name; ?></option>
                        <?php
                        endforeach;
                        ?>
            </div>
            </select>
            <script>
                jQuery(document).ready(function($) { $('#<?php echo esc_attr($field_id); ?>').select2({
                    width: '320px',
                    allowClear: true
                });
                });
            </script>
            <script>
                <?php if(!empty($depends)) {?>
                jQuery('#field-wrapper-<?php esc_attr($id); ?>').formFieldDependency({});
                <?php } ?>
            </script>
            <?php
            return ob_get_clean();

        }





        public function field_faq( $option ){

            $id				= isset( $option['id'] ) ? $option['id'] : "";
            $args			= isset( $option['args'] ) ? $option['args'] : array();

            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();


            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;


            ob_start();
            ?>
            <script>
                jQuery(document).ready(function($) {
                    jQuery(document).on('click', '.faq-list-<?php esc_attr($id); ?> .faq-header', function() {
                        if(jQuery(this).parent().hasClass('active')){
                            jQuery(this).parent().removeClass('active');
                        }else{
                            jQuery(this).parent().addClass('active');
                        }
                    })
                })
            </script>
            <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?> id="field-wrapper-<?php esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-faq-wrapper
            field-faq-wrapper-<?php esc_attr($id); ?>">
                <div class='faq-list faq-list-<?php esc_attr($id); ?>'>
                    <?php
                    foreach( $args as $key => $value ):
                        $title = $value['title'];
                        $link = $value['link'];
                        $content = $value['content'];
                        ?>
                        <div class="faq-item">
                            <div class="faq-header"><?php echo $title; ?></div>
                            <div class="faq-content"><?php echo $content; ?></div>
                        </div>
                    <?php
                    endforeach;
                    ?>
                </div>
            </div>
            <script>
                <?php if(!empty($depends)) {?>
                jQuery('#field-wrapper-<?php esc_attr($id); ?>').formFieldDependency({});
                <?php } ?>
            </script>
            <?php
            return ob_get_clean();
        }




        public function field_grid( $option ){

            $id 			= isset( $option['id'] ) ? $option['id'] : "";
            $args 			= isset( $option['args'] ) ? $option['args'] : "";
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();
            $widths 		= isset( $option['width'] ) ? $option['width'] : array('768px'=>'100%','992px'=>'50%', '1200px'=>'30%', );
            $heights 		= isset( $option['height'] ) ? $option['height'] : array('768px'=>'auto','992px'=>'250px', '1200px'=>'250px', );


            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;




            ob_start();
            ?>
            <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?> id="field-wrapper-<?php esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-grid-wrapper
            field-grid-wrapper-<?php esc_attr($id); ?>">
                <?php
                foreach($args as $key=>$grid_item){
                    $title = isset($grid_item['title']) ? $grid_item['title'] : '';
                    $link = isset($grid_item['link']) ? $grid_item['link'] : '';
                    $thumb = isset($grid_item['thumb']) ? $grid_item['thumb'] : '';
                    ?>
                    <div class="item">
                        <div class="thumb"><a href="<?php echo $link; ?>"><img src="<?php echo $thumb; ?>"></img></a></div>
                        <div class="name"><a href="<?php echo $link; ?>"><?php echo $title; ?></a></div>
                    </div>
                    <?php
                }
                ?>
            </div>
            <style type="text/css">
                <?php
                if(!empty($widths)):
                    foreach ($widths as $screen_size=>$width):
                    $height = !empty($heights[$screen_size]) ? $heights[$screen_size] : 'auto';
                    ?>
                    @media screen and (min-width: <?php echo $screen_size; ?>) {
                        .field-grid-wrapper-<?php esc_attr($id); ?> .item{
                            width: <?php echo $width; ?>;
                        }
                        .field-grid-wrapper-<?php esc_attr($id); ?> .item .thumb{
                            height: <?php echo $height; ?>;
                        }
                    }
                    <?php
                    endforeach;
                endif;
                ?>
            </style>
            <script>
                <?php if(!empty($depends)) {?>
                jQuery('#field-wrapper-<?php esc_attr($id); ?>').formFieldDependency({});
                <?php } ?>
            </script>
            <?php
            return ob_get_clean();
        }






        public function field_color_palette( $option ){

            $id				= isset( $option['id'] ) ? $option['id'] : "";
            if(empty($id)) return;
            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();

            $args			= isset( $option['args'] ) ? $option['args'] : array();
            $width				= isset( $args['width'] ) ? $args['width'] : "";
            $height				= isset( $args['height'] ) ? $args['height'] : "";
            $colors			= isset( $option['colors'] ) ? $option['colors'] : array();
            //$option_value	= get_option( $id );
            $default			= isset( $option['default'] ) ? $option['default'] : '';
            $value			= isset( $option['value'] ) ? $option['value'] : '';
            $value          = !empty($value) ?  $value : $default;

            $field_id       = $id;
            $field_name     = !empty( $field_name ) ? $field_name : $id;



            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;



            ob_start();
            ?>
            <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?> id="field-wrapper-<?php esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-color-palette-wrapper
            field-color-palette-wrapper-<?php esc_attr($id); ?>">
                <?php
                foreach( $colors as $key => $color ):

                    $checked = ( $key == $value ) ? "checked" : "";
                    ?><label  class="<?php echo esc_attr($checked); ?>" for='<?php esc_attr($id); ?>-<?php echo esc_attr($key); ?>'><input
                            name='<?php echo esc_attr($field_name); ?>' type='radio' id='<?php esc_attr($id); ?>-<?php echo esc_attr($key); ?>'
                            value='<?php echo esc_attr($key); ?>' <?php echo esc_attr($checked); ?>><span title="<?php echo $color; ?>" style="background-color: <?php
                    echo $color; ?>" class="sw-button"></span></label><?php
                endforeach;
                ?>
            </div>
            <style type="text/css">
                .field-color-palette-wrapper-<?php esc_attr($id); ?> .sw-button{
                    transition: ease all 1s;
                <?php if(!empty($width)):  ?>
                    width: <?php echo $width; ?>;
                <?php endif; ?>
                <?php if(!empty($height)):  ?>
                    height: <?php echo $height; ?>;
                <?php endif; ?>
                }
                .field-color-palette-wrapper-<?php esc_attr($id); ?> label:hover .sw-button{
                }
            </style>
            <script>
                jQuery(document).ready(function($) {
                    jQuery(document).on('click', '.field-color-palette-wrapper-<?php esc_attr($id); ?> .sw-button', function() {
                        jQuery('.field-color-palette-wrapper-<?php esc_attr($id); ?> label').removeClass('checked');
                        if(jQuery(this).parent().hasClass('checked')){
                            jQuery(this).parent().removeClass('checked');
                        }else{
                            jQuery(this).parent().addClass('checked');
                        }
                    })
                })
            </script>
            <script>
                <?php if(!empty($depends)) {?>
                jQuery('#field-wrapper-<?php esc_attr($id); ?>').formFieldDependency({});
                <?php } ?>
            </script>
            <?php
            return ob_get_clean();

        }




        public function field_color_palette_multi( $option ){

            $id				= isset( $option['id'] ) ? $option['id'] : "";

            if(empty($id)) return;
            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();

            $args			= isset( $option['args'] ) ? $option['args'] : array();
            $width				= isset( $args['width'] ) ? $args['width'] : "";
            $height				= isset( $args['height'] ) ? $args['height'] : "";
            $colors			= isset( $option['colors'] ) ? $option['colors'] : array();
            $default			= isset( $option['default'] ) ? $option['default'] : '';
            $value			= isset( $option['value'] ) ? $option['value'] : '';
            $value          = !empty($value) ?  $value : $default;

            $field_id       = $id;
            $field_name     = !empty( $field_name ) ? $field_name : $id;



            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;



            ob_start();
            ?>
            <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?> id="field-wrapper-<?php esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-color-palette-multi-wrapper
            field-color-palette-multi-wrapper-<?php esc_attr($id); ?>">
                <?php
                foreach( $colors as $key => $color ):
                    $checked = is_array( $value ) && in_array( $key, $value ) ? "checked" : "";
                    ?><label  class="<?php echo esc_attr($checked); ?>" for='<?php esc_attr($id); ?>-<?php echo esc_attr($key); ?>'><input
                            name='<?php echo esc_attr($field_name); ?>[]' type='checkbox' id='<?php esc_attr($id); ?>-<?php echo esc_attr($key); ?>'
                            value='<?php echo esc_attr($key); ?>' <?php echo esc_attr($checked); ?>><span title="<?php echo $color; ?>" style="background-color: <?php
                    echo $color; ?>" class="sw-button"></span></label><?php
                endforeach;
                ?>
            </div>
            <style type="text/css">
                .field-color-palette-multi-wrapper-<?php esc_attr($id); ?> .sw-button{
                    transition: ease all 1s;
                <?php if(!empty($width)):  ?>
                    width: <?php echo $width; ?>;
                <?php endif; ?>
                <?php if(!empty($height)):  ?>
                    height: <?php echo $height; ?>;
                <?php endif; ?>
                }
                .field-color-palette-multi-wrapper-<?php esc_attr($id); ?> label:hover .sw-button{
                }
            </style>
            <script>
                jQuery(document).ready(function($) {
                    jQuery(document).on('click', '.field-color-palette-multi-wrapper-<?php esc_attr($id); ?> .sw-button',
                        function() {
                            if(jQuery(this).parent().hasClass('checked')){
                                jQuery(this).parent().removeClass('checked');
                            }else{
                                jQuery(this).parent().addClass('checked');
                            }
                        })
                })
            </script>
            <script>
                <?php if(!empty($depends)) {?>
                jQuery('#field-wrapper-<?php esc_attr($id); ?>').formFieldDependency({});
                <?php } ?>
            </script>
            <?php
            return ob_get_clean();
        }




        public function field_media( $option ){

            $id			= isset( $option['id'] ) ? $option['id'] : "";
            if(empty($id)) return;
            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();
            $placeholder	= isset( $option['placeholder'] ) ? $option['placeholder'] : "";

            $default			= isset( $option['default'] ) ? $option['default'] : '';
            $value			= isset( $option['value'] ) ? $option['value'] : '';
            $value          = !empty($value) ?  $value : $default;

            $media_url	= wp_get_attachment_url( $value );
            $media_type	= get_post_mime_type( $value );
            $media_title= get_the_title( $value );
            $media_url = !empty($media_url) ? $media_url : $placeholder;

            $field_id       = $id;
            $field_name     = !empty( $field_name ) ? $field_name : $id;


            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;



            ob_start();
            wp_enqueue_media();

            ?>
            <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?> id="field-wrapper-<?php esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-media-wrapper
            field-media-wrapper-<?php esc_attr($id); ?>">
                <div class='media_preview' style='width: 150px;margin-bottom: 10px;background: #eee;padding: 5px;    text-align: center;'>
                    <?php

                    if( "audio/mpeg" == $media_type ){
                        ?>
                        <div id='media_preview_$id' class='dashicons dashicons-format-audio' style='font-size: 70px;display: inline;'></div>
                        <div><?php echo $media_title; ?></div>
                        <?php
                    }
                    else {
                        ?>
                        <img id='media_preview_<?php esc_attr($id); ?>' src='<?php echo $media_url; ?>' style='width:100%'/>
                        <?php
                    }
                    ?>
                </div>
                <input type='hidden' name='<?php echo esc_attr($field_name); ?>' id='media_input_<?php esc_attr($id); ?>' value='<?php echo esc_attr($value); ?>' />
                <div class='button' id='media_upload_<?php esc_attr($id); ?>'>Upload</div><div class='button clear' id='media_clear_<?php esc_attr($id); ?>'>Clear</div>
            </div>

            <script>jQuery(document).ready(function($){
                    $('#media_upload_<?php esc_attr($id); ?>').click(function() {
                        var send_attachment_bkp = wp.media.editor.send.attachment;
                        wp.media.editor.send.attachment = function(props, attachment) {
                            $('#media_preview_<?php esc_attr($id); ?>').attr('src', attachment.url);
                            $('#media_input_<?php esc_attr($id); ?>').val(attachment.id);
                            wp.media.editor.send.attachment = send_attachment_bkp;
                        }
                        wp.media.editor.open($(this));
                        return false;
                    });
                    $('#media_clear_<?php esc_attr($id); ?>').click(function() {
                        $('#media_input_<?php esc_attr($id); ?>').val('');
                        $('#media_preview_<?php esc_attr($id); ?>').attr('src','');
                    })

                });
            </script>
            <script>
                <?php if(!empty($depends)) {?>
                jQuery('#field-wrapper-<?php esc_attr($id); ?>').formFieldDependency({});
                <?php } ?>
            </script>
            <?php
            return ob_get_clean();
        }




        public function field_media_multi( $option ){

            $id			= isset( $option['id'] ) ? $option['id'] : "";
            if(empty($id)) return;
            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();

            $default			= isset( $option['default'] ) ? $option['default'] : '';
            $values			= isset( $option['value'] ) ? $option['value'] : '';

            $field_id       = $id;
            $field_name     = !empty( $field_name ) ? $field_name : $id;


            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;



            ob_start();
            wp_enqueue_media();

            ?>
            <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?> id="field-wrapper-<?php esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-media-multi-wrapper
            field-media-multi-wrapper-<?php esc_attr($id); ?>">
                <div class='button' id='media_upload_<?php esc_attr($id); ?>'>Upload</div><div class='button clear'
                                                                                          id='media_clear_<?php echo
                                                                                          $id;
                                                                                          ?>'>Clear</div>
                <div class="media-list media-list-<?php esc_attr($id); ?>">
                    <?php
                    if(!empty($values) && is_array($values)):
                        foreach ($values as $value ):
                            $media_url	= wp_get_attachment_url( $value );
                            $media_type	= get_post_mime_type( $value );
                            $media_title= get_the_title( $value );
                            ?>
                            <div class="item">
                                <span class="remove" onclick="jQuery(this).parent().remove()">X</span>
                                <img id='media_preview_<?php esc_attr($id); ?>' src='<?php echo $media_url; ?>' style='width:100%'/>
                                <div class="item-title"><?php echo $media_title; ?></div>
                                <input type='hidden' name='<?php echo esc_attr($field_name); ?>[]' value='<?php echo esc_attr($value); ?>' />
                            </div>
                        <?php
                        endforeach;
                    endif;
                    ?>
                </div>
            </div>
            <script>jQuery(document).ready(function($){
                    $('#media_upload_<?php esc_attr($id); ?>').click(function() {
                        //var send_attachment_bkp = wp.media.editor.send.attachment;
                        wp.media.editor.send.attachment = function(props, attachment) {
                            attachment_id = attachment.id;
                            attachment_url = attachment.url;
                            html = '<div class="item">';
                            html += '<span class="remove" onclick="jQuery(this).parent().remove()">X</span>';
                            html += '<img src="'+attachment_url+'" style="width:100%"/>';
                            html += '<input type="hidden" name="<?php echo esc_attr($field_name); ?>[]" value="'+attachment_id+'" />';
                            html += '</div>';
                            $('.media-list-<?php esc_attr($id); ?>').append(html);
                            //wp.media.editor.send.attachment = send_attachment_bkp;
                        }
                        wp.media.editor.open($(this));
                        return false;
                    });
                    $('#media_clear_<?php esc_attr($id); ?>').click(function() {
                        $('.media-list-<?php esc_attr($id); ?> .item').remove();
                    })
                    jQuery( ".field-media-multi-wrapper-<?php esc_attr($id); ?> .media-list" ).sortable();
                });
            </script>
            <script>
                <?php if(!empty($depends)) {?>
                jQuery('#field-wrapper-<?php esc_attr($id); ?>').formFieldDependency({});
                <?php } ?>
            </script>
            <?php
            return ob_get_clean();
        }




        public function field_custom_html( $option ){

            $id 			= isset( $option['id'] ) ? $option['id'] : "";
            $args 			= isset( $option['args'] ) ? $option['args'] : "";
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();
            $html 			= isset( $option['html'] ) ? $option['html'] : "";


            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;




            ob_start();
            ?>
            <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?>
                    id="field-wrapper-<?php esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-custom-html-wrapper
            field-custom-html-wrapper-<?php esc_attr($id); ?>">
                <?php
                echo $html;
                ?>
            </div>
            <script>
                <?php if(!empty($depends)) {?>
                jQuery('#field-wrapper-<?php esc_attr($id); ?>').formFieldDependency({});
                <?php } ?>
            </script>
            <?php

            return ob_get_clean();


        }

        public function field_repeatable( $option ){

            $id 			= isset( $option['id'] ) ? $option['id'] : "";
            if(empty($id)) return;
            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();
            $sortable 	    = isset( $option['sortable'] ) ? $option['sortable'] : true;
            $collapsible 	= isset( $option['collapsible'] ) ? $option['collapsible'] : true;
            $placeholder 	= isset( $option['placeholder'] ) ? $option['placeholder'] : "";
            $values			= isset( $option['value'] ) ? $option['value'] : '';
            $fields 		= isset( $option['fields'] ) ? $option['fields'] : array();
            $title_field 	= isset( $option['title_field'] ) ? $option['title_field'] : '';
            $field_id       = $id;
            $field_name     = !empty( $field_name ) ? $field_name : $id;


            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;




            ob_start();
            ?>
            <script>
                jQuery(document).ready(function($) {
                    jQuery(document).on('click', '.field-repeatable-wrapper-<?php esc_attr($id); ?> .collapsible .header', function() {
                        if(jQuery(this).parent().hasClass('active')){
                            jQuery(this).parent().removeClass('active');
                        }else{
                            jQuery(this).parent().addClass('active');
                        }
                    })
                    jQuery(document).on('click', '.field-repeatable-wrapper-<?php esc_attr($id); ?> .add-item', function() {
                        now = jQuery.now();
                        fields_arr = <?php echo json_encode($fields); ?>;
                        html = '<div class="item-wrap collapsible"><div class="header"><span class="button remove" ' +
                            'onclick="jQuery(this).parent().remove()">X</span>';

                        <?php if($sortable):?>
                        html += ' <span class="button sort" ><i class="fas fa-arrows-alt"></i></span>';
                        <?php endif; ?>
                        html += ' <span>#'+now+'</span></div>';
                        fields_arr.forEach(function(element) {
                            type = element.type;
                            item_id = element.item_id;
                            default_val = element.default;
                            html+='<div class="item">';
                            <?php if($collapsible):?>
                            html+='<div class="content">';
                            <?php endif; ?>
                            html+='<div class="item-title">'+element.name+'</div>';
                            if(type == 'text'){
                                html+='<input type="text" name="<?php echo esc_attr($field_name); ?>['+now+']['+element.item_id+']"/>';
                            }else if(type == 'number'){
                                html+='<input type="number" name="<?php echo esc_attr($field_name); ?>['+now+']['+element.item_id+']"/>';
                            }else if(type == 'tel'){
                                html+='<input type="tel" name="<?php echo esc_attr($field_name); ?>['+now+']['+element.item_id+']"/>';
                            }else if(type == 'time'){
                                html+='<input type="time" name="<?php echo esc_attr($field_name); ?>['+now+']['+element.item_id+']"/>';
                            }else if(type == 'url'){
                                html+='<input type="url" name="<?php echo esc_attr($field_name); ?>['+now+']['+element.item_id+']"/>';
                            }else if(type == 'date'){
                                html+='<input type="date" name="<?php echo esc_attr($field_name); ?>['+now+']['+element.item_id+']"/>';
                            }else if(type == 'month'){
                                html+='<input type="month" name="<?php echo esc_attr($field_name); ?>['+now+']['+element.item_id+']"/>';
                            }else if(type == 'search'){
                                html+='<input type="search" name="<?php echo esc_attr($field_name); ?>['+now+']['+element.item_id+']"/>';
                            }else if(type == 'color'){
                                html+='<input type="color" name="<?php echo esc_attr($field_name); ?>['+now+']['+element.item_id+']"/>';
                            }else if(type == 'email'){
                                html+='<input type="email" name="<?php echo esc_attr($field_name); ?>['+now+']['+element.item_id+']"/>';
                            }else if(type == 'textarea'){
                                html+='<textarea name="<?php echo esc_attr($field_name); ?>['+now+']['+element.item_id+']"></textarea>';
                            }else if(type == 'select'){
                                args = element.args;
                                html+='<select name="<?php echo esc_attr($field_name); ?>['+now+']['+element.item_id+']">';
                                for(argKey in args){
                                    html+='<option value="'+argKey+'">'+args[argKey]+'</option>';
                                }
                                html+='</select>';
                            }else if(type == 'radio'){
                                args = element.args;
                                for(argKey in args){
                                    html+='<label>';
                                    html+='<input value="'+argKey+'" type="radio" name="<?php echo esc_attr($field_name); ?>['+now+']['+element.item_id+']"/>';
                                    html+= args[argKey];
                                    html+='</label ><br/>';
                                }
                            }else if(type == 'checkbox'){
                                args = element.args;
                                for(argKey in args){
                                    html+='<label>';
                                    html+='<input value="'+argKey+'" type="checkbox" name="<?php echo esc_attr($field_name); ?>['+now+']['+element.item_id+']"/>';
                                    html+= args[argKey];
                                    html+='</label ><br/>';
                                }
                            }
                            <?php if($collapsible):?>
                            html+='</div>';
                            <?php endif; ?>
                            html+='</div>';
                        });
                        html+='</div>';
                        jQuery('.<?php echo 'field-repeatable-wrapper-'.$id; ?> .field-list').append(html);
                    })
                    jQuery( ".field-repeatable-wrapper-<?php esc_attr($id); ?> .field-list" ).sortable({ handle: '.sort' });
                });
            </script>
            <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?> id="field-wrapper-<?php esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-repeatable-wrapper
            field-repeatable-wrapper-<?php esc_attr($id); ?>">
                <div class="button add-item">Add</div>
                <div class="field-list" id="<?php esc_attr($id); ?>">
                    <?php
                    if(!empty($values)):
                        $count = 1;
                        foreach ($values as $index=>$val):
                            $title_field_val = isset($val[$title_field]) ? $val[$title_field] : '#'.$count;
                            ?>
                            <div class="item-wrap <?php if($collapsible) echo 'collapsible'; ?>">
                                <?php if($collapsible):?>
                                <div class="header">
                                    <?php endif; ?>
                                    <span class="button remove" onclick="jQuery(this).parent().parent().remove()">X</span>
                                    <?php if($sortable):?>
                                        <span class="button sort"><i class="fas fa-arrows-alt"></i></span>
                                    <?php endif; ?>
                                    <span class="title-text"><?php echo $title_field_val; ?></span>
                                    <?php if($collapsible):?>
                                </div>
                            <?php endif; ?>
                                <?php foreach ($fields as $field_index => $field):
                                    $type = $field['type'];
                                    $item_id = $field['item_id'];
                                    $name = $field['name'];
                                    $title_field_class = ($title_field == $field_index) ? 'title-field':'';
                                    ?>
                                    <div class="item <?php echo $title_field_class; ?>">
                                        <?php if($collapsible):?>
                                        <div class="content">
                                            <?php endif; ?>
                                            <div><?php echo $name; ?></div>
                                            <?php if($type == 'text'):
                                                $default = isset($field['default']) ? $field['default'] : '';
                                                $value = !empty($val[$item_id]) ? $val[$item_id] : $default;
                                                ?>
                                                <input type="text" class="regular-text" name="<?php echo esc_attr($field_name); ?>[<?php echo $index; ?>][<?php echo $item_id; ?>]" placeholder="" value="<?php echo esc_html($value); ?>">
                                            <?php elseif($type == 'number'):
                                                $default = isset($field['default']) ? $field['default'] : '';
                                                $value = !empty($val[$item_id]) ? $val[$item_id] : $default;
                                                ?>
                                                <input type="number" class="regular-text" name="<?php echo esc_attr($field_name); ?>[<?php echo $index; ?>][<?php echo $item_id; ?>]" placeholder="" value="<?php echo esc_html($value); ?>">
                                            <?php elseif($type == 'url'):
                                                $default = isset($field['default']) ? $field['default'] : '';
                                                $value = !empty($val[$item_id]) ? $val[$item_id] : $default;
                                                ?>
                                                <input type="url" class="regular-text" name="<?php echo esc_attr($field_name); ?>[<?php echo $index; ?>][<?php echo $item_id; ?>]" placeholder="" value="<?php echo esc_html($value); ?>">
                                            <?php elseif($type == 'tel'):
                                                $default = isset($field['default']) ? $field['default'] : '';
                                                $value = !empty($val[$item_id]) ? $val[$item_id] : $default;
                                                ?>
                                                <input type="tel" class="regular-text" name="<?php echo esc_attr($field_name); ?>[<?php echo $index; ?>][<?php echo $item_id; ?>]" placeholder="" value="<?php echo esc_html($value); ?>">
                                            <?php elseif($type == 'time'):
                                                $default = isset($field['default']) ? $field['default'] : '';
                                                $value = !empty($val[$item_id]) ? $val[$item_id] : $default;
                                                ?>
                                                <input type="time" class="regular-text" name="<?php echo esc_attr($field_name); ?>[<?php echo $index; ?>][<?php echo $item_id; ?>]" placeholder="" value="<?php echo esc_html($value); ?>">
                                            <?php elseif($type == 'search'):
                                                $default = isset($field['default']) ? $field['default'] : '';
                                                $value = !empty($val[$item_id]) ? $val[$item_id] : $default;
                                                ?>
                                                <input type="search" class="regular-text" name="<?php echo esc_attr($field_name); ?>[<?php echo $index; ?>][<?php echo $item_id; ?>]" placeholder="" value="<?php echo esc_html($value); ?>">
                                            <?php elseif($type == 'month'):
                                                $default = isset($field['default']) ? $field['default'] : '';
                                                $value = !empty($val[$item_id]) ? $val[$item_id] : $default;
                                                ?>
                                                <input type="month" class="regular-text" name="<?php echo esc_attr($field_name); ?>[<?php echo $index; ?>][<?php echo $item_id; ?>]" placeholder="" value="<?php echo esc_html($value); ?>">
                                            <?php elseif($type == 'color'):
                                                $default = isset($field['default']) ? $field['default'] : '';
                                                $value = !empty($val[$item_id]) ? $val[$item_id] : $default;
                                                ?>
                                                <input type="color" class="regular-text" name="<?php echo esc_attr($field_name); ?>[<?php echo $index; ?>][<?php echo $item_id; ?>]" placeholder="" value="<?php echo esc_html($value); ?>">
                                            <?php elseif($type == 'date'):
                                                $default = isset($field['default']) ? $field['default'] : '';
                                                $value = !empty($val[$item_id]) ? $val[$item_id] : $default;
                                                ?>
                                                <input type="date" class="regular-text" name="<?php echo esc_attr($field_name); ?>[<?php echo $index; ?>][<?php echo $item_id; ?>]" placeholder="" value="<?php echo esc_html($value); ?>">
                                            <?php elseif($type == 'email'):
                                                $default = isset($field['default']) ? $field['default'] : '';
                                                $value = !empty($val[$item_id]) ? $val[$item_id] : $default;
                                                ?>
                                                <input type="email" class="regular-text" name="<?php echo esc_attr($field_name); ?>[<?php echo $index; ?>][<?php echo $item_id; ?>]" placeholder="" value="<?php echo esc_html($value); ?>">
                                            <?php elseif($type == 'textarea'):
                                                $default = isset($field['default']) ? $field['default'] : '';
                                                $value = !empty($val[$item_id]) ? $val[$item_id] : $default;
                                                ?>
                                                <textarea name="<?php echo esc_attr($field_name); ?>[<?php echo $index; ?>][<?php echo $item_id; ?>]"><?php echo esc_html($value); ?></textarea>
                                            <?php elseif($type == 'select'):
                                                $args = isset($field['args']) ? $field['args'] : array();
                                                $default = isset($field['default']) ? $field['default'] : '';
                                                $value = !empty($val[$item_id]) ? $val[$item_id] : $default;
                                                ?>
                                                <select class="" name="<?php echo esc_attr($field_name); ?>[<?php echo $index; ?>][<?php echo $item_id; ?>]">
                                                    <?php foreach ($args as $argIndex => $argName):
                                                        $selected = ($argIndex == $value) ? 'selected' : '';
                                                        ?>
                                                        <option <?php echo $selected; ?>  value="<?php echo $argIndex; ?>"><?php echo esc_attr($argName); ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            <?php elseif($type == 'radio'):
                                                $args = isset($field['args']) ? $field['args'] : array();
                                                $default = isset($field['default']) ? $field['default'] : '';
                                                $value = !empty($val[$item_id]) ? $val[$item_id] : $default;
                                                ?>
                                                <?php foreach ($args as $argIndex => $argName):
                                                $checked = ($argIndex == $value) ? 'checked' : '';
                                                ?>
                                                <label class="" >
                                                    <input  type="radio" name="<?php echo esc_attr($field_name); ?>[<?php echo $index; ?>][<?php echo $item_id; ?>]" <?php echo esc_attr($checked); ?>  value="<?php echo $argIndex; ?>"><?php echo esc_attr($argName); ?></input>
                                                </label>
                                            <?php endforeach; ?>
                                            <?php elseif($type == 'checkbox'):
                                                $args = isset($field['args']) ? $field['args'] : array();
                                                $default = isset($field['default']) ? $field['default'] : '';
                                                $value = !empty($val[$item_id]) ? $val[$item_id] : $default;
                                                ?>
                                                <?php foreach ($args as $argIndex => $argName):
                                                $checked = in_array($argIndex, $value ) ? 'checked' : '';
                                                ?>
                                                <label class="" >
                                                    <input  type="checkbox" name="<?php echo esc_attr($field_name); ?>[<?php echo $index; ?>][<?php echo $item_id; ?>][]" <?php echo esc_attr($checked); ?>  value="<?php echo $argIndex; ?>"><?php echo esc_attr($argName); ?></input>
                                                </label>
                                            <?php endforeach; ?>
                                            <?php
                                            else:
                                                do_action('repeatable_custom_input_field_'.$type, $field);
                                                ?>
                                            <?php endif;?>
                                            <?php if($collapsible):?>
                                        </div>
                                    <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <?php
                            //endforeach;
                            $count++;
                        endforeach;
                    else:
                        ?>
                    <?php
                    endif;
                    ?>
                </div>
            </div>
            <script>
                <?php if(!empty($depends)) {?>
                jQuery('#field-wrapper-<?php esc_attr($id); ?>').formFieldDependency({});
                <?php } ?>
            </script>
            <?php
            return ob_get_clean();
        }




        public function args_from_string( $string ){

            if( strpos( $string, 'PAGES_IDS_ARRAY' )    !== false ) return $this->get_pages_array();
            if( strpos( $string, 'POSTS_IDS_ARRAY' )    !== false ) return $this->get_posts_array();
            if( strpos( $string, 'POST_TYPES_ARRAY' )   !== false ) return $this->get_post_types_array();
            if( strpos( $string, 'TAX_' )               !== false ) return $this->get_taxonomies_array( $string );
            if( strpos( $string, 'USER_ROLES' )         !== false ) return $this->get_user_roles_array();
            if( strpos( $string, 'USER_IDS_ARRAY' )     !== false ) return $this->get_user_ids_array();
            if( strpos( $string, 'MENUS' )              !== false ) return $this->get_menus_array();
            if( strpos( $string, 'SIDEBARS_ARRAY' )     !== false ) return $this->get_sidebars_array();
            if( strpos( $string, 'THUMB_SIEZS_ARRAY' )  !== false ) return $this->get_thumb_sizes_array();
            if( strpos( $string, 'FONTAWESOME_ARRAY' )  !== false ) return $this->get_font_aws_array();

            return array();
        }




        public function get_taxonomies_array( $string ){

            $taxonomies = array();

            preg_match_all( "/\%([^\]]*)\%/", $string, $matches );

            if( isset( $matches[1][0] ) ) $taxonomy = $matches[1][0];
            else throw new Pick_error('Invalid taxonomy declaration !');

            if( ! taxonomy_exists( $taxonomy ) ) throw new Pick_error("Taxonomy <strong>$taxonomy</strong> doesn't exists !");

            $terms = get_terms( $taxonomy, array(
                'hide_empty' => false,
            ) );

            foreach( $terms as $term ) $taxonomies[ $term->term_id ] = $term->name;

            return $taxonomies;
        }



        public function get_user_ids_array(){

            $user_ids = array();
            $users = get_users();

            foreach( $users as $user ) $user_ids[ $user->ID ] = $user->display_name. '(#'.$user->ID.')';

            return apply_filters( 'USER_IDS_ARRAY', $user_ids );
        }


        public function get_pages_array(){

            $pages_array = array();
            foreach( get_pages() as $page ) $pages_array[ $page->ID ] = $page->post_title;

            return apply_filters( 'PAGES_IDS_ARRAY', $pages_array );
        }

        public function get_menus_array(){

            $menus = get_registered_nav_menus();



            return apply_filters( 'MENUS_ARRAY', $menus );
        }

        public function get_sidebars_array(){

            global $wp_registered_sidebars;
            $sidebars = $wp_registered_sidebars;

            foreach ($sidebars as $index => $sidebar){

                $sidebars_name[$index] = $sidebar['name'];
            }


            return apply_filters( 'SIDEBARS_ARRAY', $sidebars_name );
        }

        public function get_user_roles_array(){
            require_once ABSPATH . 'wp-admin/includes/user.php';

            $roles = get_editable_roles();

            foreach ($roles as $index => $data){

                $role_name[$index] = $data['name'];
            }

            return apply_filters( 'USER_ROLES', $role_name );
        }



        public function get_post_types_array(){

            $post_types = get_post_types('', 'names' );
            $pages_array = array();
            foreach( $post_types as $index => $name ) $pages_array[ $index ] = $name;

            return apply_filters( 'POST_TYPES_ARRAY', $pages_array );
        }


        public function get_posts_array(){

            $posts_array = array();
            foreach( get_posts(array('posts_per_page'=>-1)) as $page ) $posts_array[ $page->ID ] = $page->post_title;

            return apply_filters( 'POSTS_IDS_ARRAY', $posts_array );
        }


        public function get_thumb_sizes_array(){

            $get_intermediate_image_sizes =  get_intermediate_image_sizes();
            $get_intermediate_image_sizes = array_merge($get_intermediate_image_sizes,array('full'));
            $thumb_sizes_array = array();

            foreach( $get_intermediate_image_sizes as $key => $name ):
                $size_key = str_replace('_', ' ',$name);
                $size_key = str_replace('-', ' ',$size_key);
                $size_name = ucfirst($size_key);
                $thumb_sizes_array[$name] = $size_name;
            endforeach;

            return apply_filters( 'THUMB_SIEZS_ARRAY', $get_intermediate_image_sizes );
        }




        public function get_font_aws_array(){

            $fonts_arr = array (
                'fab fa-500px' => __( '500px', 'bus-booking-manager' ),
                'fab fa-accessible-icon' => __( 'accessible-icon', 'bus-booking-manager' ),
                'fab fa-accusoft' => __( 'accusoft', 'bus-booking-manager' ),
                'fas fa-address-book' => __( 'address-book', 'bus-booking-manager' ),
                'far fa-address-book' => __( 'address-book', 'bus-booking-manager' ),
                'fas fa-address-card' => __( 'address-card', 'bus-booking-manager' ),
                'far fa-address-card' => __( 'address-card', 'bus-booking-manager' ),
                'fas fa-adjust' => __( 'adjust', 'bus-booking-manager' ),
                'fab fa-adn' => __( 'adn', 'bus-booking-manager' ),
                'fab fa-adversal' => __( 'adversal', 'bus-booking-manager' ),
                'fab fa-affiliatetheme' => __( 'affiliatetheme', 'bus-booking-manager' ),
                'fab fa-algolia' => __( 'algolia', 'bus-booking-manager' ),
                'fas fa-align-center' => __( 'align-center', 'bus-booking-manager' ),
                'fas fa-align-justify' => __( 'align-justify', 'bus-booking-manager' ),
                'fas fa-align-left' => __( 'align-left', 'bus-booking-manager' ),
                'fas fa-align-right' => __( 'align-right', 'bus-booking-manager' ),
                'fas fa-allergies' => __( 'allergies', 'bus-booking-manager' ),
                'fab fa-amazon' => __( 'amazon', 'bus-booking-manager' ),
                'fab fa-amazon-pay' => __( 'amazon-pay', 'bus-booking-manager' ),
                'fas fa-ambulance' => __( 'ambulance', 'bus-booking-manager' ),
                'fas fa-american-sign-language-interpreting' => __( 'american-sign-language-interpreting', 'bus-booking-manager' ),
                'fab fa-amilia' => __( 'amilia', 'bus-booking-manager' ),
                'fas fa-anchor' => __( 'anchor', 'bus-booking-manager' ),
                'fab fa-android' => __( 'android', 'bus-booking-manager' ),
                'fab fa-angellist' => __( 'angellist', 'bus-booking-manager' ),
                'fas fa-angle-double-down' => __( 'angle-double-down', 'bus-booking-manager' ),
                'fas fa-angle-double-left' => __( 'angle-double-left', 'bus-booking-manager' ),
                'fas fa-angle-double-right' => __( 'angle-double-right', 'bus-booking-manager' ),
                'fas fa-angle-double-up' => __( 'angle-double-up', 'bus-booking-manager' ),
                'fas fa-angle-down' => __( 'angle-down', 'bus-booking-manager' ),
                'fas fa-angle-left' => __( 'angle-left', 'bus-booking-manager' ),
                'fas fa-angle-right' => __( 'angle-right', 'bus-booking-manager' ),
                'fas fa-angle-up' => __( 'angle-up', 'bus-booking-manager' ),
                'fab fa-angrycreative' => __( 'angrycreative', 'bus-booking-manager' ),
                'fab fa-angular' => __( 'angular', 'bus-booking-manager' ),
                'fab fa-app-store' => __( 'app-store', 'bus-booking-manager' ),
                'fab fa-app-store-ios' => __( 'app-store-ios', 'bus-booking-manager' ),
                'fab fa-apper' => __( 'apper', 'bus-booking-manager' ),
                'fab fa-apple' => __( 'apple', 'bus-booking-manager' ),
                'fab fa-apple-pay' => __( 'apple-pay', 'bus-booking-manager' ),
                'fas fa-archive' => __( 'archive', 'bus-booking-manager' ),
                'fas fa-arrow-alt-circle-down' => __( 'arrow-alt-circle-down', 'bus-booking-manager' ),
                'far fa-arrow-alt-circle-down' => __( 'arrow-alt-circle-down', 'bus-booking-manager' ),
                'fas fa-arrow-alt-circle-left' => __( 'arrow-alt-circle-left', 'bus-booking-manager' ),
                'far fa-arrow-alt-circle-left' => __( 'arrow-alt-circle-left', 'bus-booking-manager' ),
                'fas fa-arrow-alt-circle-right' => __( 'arrow-alt-circle-right', 'bus-booking-manager' ),
                'far fa-arrow-alt-circle-right' => __( 'arrow-alt-circle-right', 'bus-booking-manager' ),
                'fas fa-arrow-alt-circle-up' => __( 'arrow-alt-circle-up', 'bus-booking-manager' ),
                'far fa-arrow-alt-circle-up' => __( 'arrow-alt-circle-up', 'bus-booking-manager' ),
                'fas fa-arrow-circle-down' => __( 'arrow-circle-down', 'bus-booking-manager' ),
                'fas fa-arrow-circle-left' => __( 'arrow-circle-left', 'bus-booking-manager' ),
                'fas fa-arrow-circle-right' => __( 'arrow-circle-right', 'bus-booking-manager' ),
                'fas fa-arrow-circle-up' => __( 'arrow-circle-up', 'bus-booking-manager' ),
                'fas fa-arrow-down' => __( 'arrow-down', 'bus-booking-manager' ),
                'fas fa-arrow-left' => __( 'arrow-left', 'bus-booking-manager' ),
                'fas fa-arrow-right' => __( 'arrow-right', 'bus-booking-manager' ),
                'fas fa-arrow-up' => __( 'arrow-up', 'bus-booking-manager' ),
                'fas fa-arrows-alt' => __( 'arrows-alt', 'bus-booking-manager' ),
                'fas fa-arrows-alt-h' => __( 'arrows-alt-h', 'bus-booking-manager' ),
                'fas fa-arrows-alt-v' => __( 'arrows-alt-v', 'bus-booking-manager' ),
                'fas fa-assistive-listening-systems' => __( 'assistive-listening-systems', 'bus-booking-manager' ),
                'fas fa-asterisk' => __( 'asterisk', 'bus-booking-manager' ),
                'fab fa-asymmetrik' => __( 'asymmetrik', 'bus-booking-manager' ),
                'fas fa-at' => __( 'at', 'bus-booking-manager' ),
                'fab fa-audible' => __( 'audible', 'bus-booking-manager' ),
                'fas fa-audio-description' => __( 'audio-description', 'bus-booking-manager' ),
                'fab fa-autoprefixer' => __( 'autoprefixer', 'bus-booking-manager' ),
                'fab fa-avianex' => __( 'avianex', 'bus-booking-manager' ),
                'fab fa-aviato' => __( 'aviato', 'bus-booking-manager' ),
                'fab fa-aws' => __( 'aws', 'bus-booking-manager' ),
                'fas fa-backward' => __( 'backward', 'bus-booking-manager' ),
                'fas fa-balance-scale' => __( 'balance-scale', 'bus-booking-manager' ),
                'fas fa-ban' => __( 'ban', 'bus-booking-manager' ),
                'fas fa-band-aid' => __( 'band-aid', 'bus-booking-manager' ),
                'fab fa-bandcamp' => __( 'bandcamp', 'bus-booking-manager' ),
                'fas fa-barcode' => __( 'barcode', 'bus-booking-manager' ),
                'fas fa-bars' => __( 'bars', 'bus-booking-manager' ),
                'fas fa-baseball-ball' => __( 'baseball-ball', 'bus-booking-manager' ),
                'fas fa-basketball-ball' => __( 'basketball-ball', 'bus-booking-manager' ),
                'fas fa-bath' => __( 'bath', 'bus-booking-manager' ),
                'fas fa-battery-empty' => __( 'battery-empty', 'bus-booking-manager' ),
                'fas fa-battery-full' => __( 'battery-full', 'bus-booking-manager' ),
                'fas fa-battery-half' => __( 'battery-half', 'bus-booking-manager' ),
                'fas fa-battery-quarter' => __( 'battery-quarter', 'bus-booking-manager' ),
                'fas fa-battery-three-quarters' => __( 'battery-three-quarters', 'bus-booking-manager' ),
                'fas fa-bed' => __( 'bed', 'bus-booking-manager' ),
                'fas fa-beer' => __( 'beer', 'bus-booking-manager' ),
                'fab fa-behance' => __( 'behance', 'bus-booking-manager' ),
                'fab fa-behance-square' => __( 'behance-square', 'bus-booking-manager' ),
                'fas fa-bell' => __( 'bell', 'bus-booking-manager' ),
                'far fa-bell' => __( 'bell', 'bus-booking-manager' ),
                'fas fa-bell-slash' => __( 'bell-slash', 'bus-booking-manager' ),
                'far fa-bell-slash' => __( 'bell-slash', 'bus-booking-manager' ),
                'fas fa-bicycle' => __( 'bicycle', 'bus-booking-manager' ),
                'fab fa-bimobject' => __( 'bimobject', 'bus-booking-manager' ),
                'fas fa-binoculars' => __( 'binoculars', 'bus-booking-manager' ),
                'fas fa-birthday-cake' => __( 'birthday-cake', 'bus-booking-manager' ),
                'fab fa-bitbucket' => __( 'bitbucket', 'bus-booking-manager' ),
                'fab fa-bitcoin' => __( 'bitcoin', 'bus-booking-manager' ),
                'fab fa-bity' => __( 'bity', 'bus-booking-manager' ),
                'fab fa-black-tie' => __( 'black-tie', 'bus-booking-manager' ),
                'fab fa-blackberry' => __( 'blackberry', 'bus-booking-manager' ),
                'fas fa-blind' => __( 'blind', 'bus-booking-manager' ),
                'fab fa-blogger' => __( 'blogger', 'bus-booking-manager' ),
                'fab fa-blogger-b' => __( 'blogger-b', 'bus-booking-manager' ),
                'fab fa-bluetooth' => __( 'bluetooth', 'bus-booking-manager' ),
                'fab fa-bluetooth-b' => __( 'bluetooth-b', 'bus-booking-manager' ),
                'fas fa-bold' => __( 'bold', 'bus-booking-manager' ),
                'fas fa-bolt' => __( 'bolt', 'bus-booking-manager' ),
                'fas fa-bomb' => __( 'bomb', 'bus-booking-manager' ),
                'fas fa-book' => __( 'book', 'bus-booking-manager' ),
                'fas fa-bookmark' => __( 'bookmark', 'bus-booking-manager' ),
                'far fa-bookmark' => __( 'bookmark', 'bus-booking-manager' ),
                'fas fa-bowling-ball' => __( 'bowling-ball', 'bus-booking-manager' ),
                'fas fa-box' => __( 'box', 'bus-booking-manager' ),
                'fas fa-box-open' => __( 'box-open', 'bus-booking-manager' ),
                'fas fa-boxes' => __( 'boxes', 'bus-booking-manager' ),
                'fas fa-braille' => __( 'braille', 'bus-booking-manager' ),
                'fas fa-briefcase' => __( 'briefcase', 'bus-booking-manager' ),
                'fas fa-briefcase-medical' => __( 'briefcase-medical', 'bus-booking-manager' ),
                'fab fa-btc' => __( 'btc', 'bus-booking-manager' ),
                'fas fa-bug' => __( 'bug', 'bus-booking-manager' ),
                'fas fa-building' => __( 'building', 'bus-booking-manager' ),
                'far fa-building' => __( 'building', 'bus-booking-manager' ),
                'fas fa-bullhorn' => __( 'bullhorn', 'bus-booking-manager' ),
                'fas fa-bullseye' => __( 'bullseye', 'bus-booking-manager' ),
                'fas fa-burn' => __( 'burn', 'bus-booking-manager' ),
                'fab fa-buromobelexperte' => __( 'buromobelexperte', 'bus-booking-manager' ),
                'fas fa-bus' => __( 'bus', 'bus-booking-manager' ),
                'fab fa-buysellads' => __( 'buysellads', 'bus-booking-manager' ),
                'fas fa-calculator' => __( 'calculator', 'bus-booking-manager' ),
                'fas fa-calendar' => __( 'calendar', 'bus-booking-manager' ),
                'far fa-calendar' => __( 'calendar', 'bus-booking-manager' ),
                'fas fa-calendar-alt' => __( 'calendar-alt', 'bus-booking-manager' ),
                'far fa-calendar-alt' => __( 'calendar-alt', 'bus-booking-manager' ),
                'fas fa-calendar-check' => __( 'calendar-check', 'bus-booking-manager' ),
                'far fa-calendar-check' => __( 'calendar-check', 'bus-booking-manager' ),
                'fas fa-calendar-minus' => __( 'calendar-minus', 'bus-booking-manager' ),
                'far fa-calendar-minus' => __( 'calendar-minus', 'bus-booking-manager' ),
                'fas fa-calendar-plus' => __( 'calendar-plus', 'bus-booking-manager' ),
                'far fa-calendar-plus' => __( 'calendar-plus', 'bus-booking-manager' ),
                'fas fa-calendar-times' => __( 'calendar-times', 'bus-booking-manager' ),
                'far fa-calendar-times' => __( 'calendar-times', 'bus-booking-manager' ),
                'fas fa-camera' => __( 'camera', 'bus-booking-manager' ),
                'fas fa-camera-retro' => __( 'camera-retro', 'bus-booking-manager' ),
                'fas fa-capsules' => __( 'capsules', 'bus-booking-manager' ),
                'fas fa-car' => __( 'car', 'bus-booking-manager' ),
                'fas fa-caret-down' => __( 'caret-down', 'bus-booking-manager' ),
                'fas fa-caret-left' => __( 'caret-left', 'bus-booking-manager' ),
                'fas fa-caret-right' => __( 'caret-right', 'bus-booking-manager' ),
                'fas fa-caret-square-down' => __( 'caret-square-down', 'bus-booking-manager' ),
                'far fa-caret-square-down' => __( 'caret-square-down', 'bus-booking-manager' ),
                'fas fa-caret-square-left' => __( 'caret-square-left', 'bus-booking-manager' ),
                'far fa-caret-square-left' => __( 'caret-square-left', 'bus-booking-manager' ),
                'fas fa-caret-square-right' => __( 'caret-square-right', 'bus-booking-manager' ),
                'far fa-caret-square-right' => __( 'caret-square-right', 'bus-booking-manager' ),
                'fas fa-caret-square-up' => __( 'caret-square-up', 'bus-booking-manager' ),
                'far fa-caret-square-up' => __( 'caret-square-up', 'bus-booking-manager' ),
                'fas fa-caret-up' => __( 'caret-up', 'bus-booking-manager' ),
                'fas fa-cart-arrow-down' => __( 'cart-arrow-down', 'bus-booking-manager' ),
                'fas fa-cart-plus' => __( 'cart-plus', 'bus-booking-manager' ),
                'fab fa-cc-amazon-pay' => __( 'cc-amazon-pay', 'bus-booking-manager' ),
                'fab fa-cc-amex' => __( 'cc-amex', 'bus-booking-manager' ),
                'fab fa-cc-apple-pay' => __( 'cc-apple-pay', 'bus-booking-manager' ),
                'fab fa-cc-diners-club' => __( 'cc-diners-club', 'bus-booking-manager' ),
                'fab fa-cc-discover' => __( 'cc-discover', 'bus-booking-manager' ),
                'fab fa-cc-jcb' => __( 'cc-jcb', 'bus-booking-manager' ),
                'fab fa-cc-mastercard' => __( 'cc-mastercard', 'bus-booking-manager' ),
                'fab fa-cc-paypal' => __( 'cc-paypal', 'bus-booking-manager' ),
                'fab fa-cc-stripe' => __( 'cc-stripe', 'bus-booking-manager' ),
                'fab fa-cc-visa' => __( 'cc-visa', 'bus-booking-manager' ),
                'fab fa-centercode' => __( 'centercode', 'bus-booking-manager' ),
                'fas fa-certificate' => __( 'certificate', 'bus-booking-manager' ),
                'fas fa-chart-area' => __( 'chart-area', 'bus-booking-manager' ),
                'fas fa-chart-bar' => __( 'chart-bar', 'bus-booking-manager' ),
                'far fa-chart-bar' => __( 'chart-bar', 'bus-booking-manager' ),
                'fas fa-chart-line' => __( 'chart-line', 'bus-booking-manager' ),
                'fas fa-chart-pie' => __( 'chart-pie', 'bus-booking-manager' ),
                'fas fa-check' => __( 'check', 'bus-booking-manager' ),
                'fas fa-check-circle' => __( 'check-circle', 'bus-booking-manager' ),
                'far fa-check-circle' => __( 'check-circle', 'bus-booking-manager' ),
                'fas fa-check-square' => __( 'check-square', 'bus-booking-manager' ),
                'far fa-check-square' => __( 'check-square', 'bus-booking-manager' ),
                'fas fa-chess' => __( 'chess', 'bus-booking-manager' ),
                'fas fa-chess-bishop' => __( 'chess-bishop', 'bus-booking-manager' ),
                'fas fa-chess-board' => __( 'chess-board', 'bus-booking-manager' ),
                'fas fa-chess-king' => __( 'chess-king', 'bus-booking-manager' ),
                'fas fa-chess-knight' => __( 'chess-knight', 'bus-booking-manager' ),
                'fas fa-chess-pawn' => __( 'chess-pawn', 'bus-booking-manager' ),
                'fas fa-chess-queen' => __( 'chess-queen', 'bus-booking-manager' ),
                'fas fa-chess-rook' => __( 'chess-rook', 'bus-booking-manager' ),
                'fas fa-chevron-circle-down' => __( 'chevron-circle-down', 'bus-booking-manager' ),
                'fas fa-chevron-circle-left' => __( 'chevron-circle-left', 'bus-booking-manager' ),
                'fas fa-chevron-circle-right' => __( 'chevron-circle-right', 'bus-booking-manager' ),
                'fas fa-chevron-circle-up' => __( 'chevron-circle-up', 'bus-booking-manager' ),
                'fas fa-chevron-down' => __( 'chevron-down', 'bus-booking-manager' ),
                'fas fa-chevron-left' => __( 'chevron-left', 'bus-booking-manager' ),
                'fas fa-chevron-right' => __( 'chevron-right', 'bus-booking-manager' ),
                'fas fa-chevron-up' => __( 'chevron-up', 'bus-booking-manager' ),
                'fas fa-child' => __( 'child', 'bus-booking-manager' ),
                'fab fa-chrome' => __( 'chrome', 'bus-booking-manager' ),
                'fas fa-circle' => __( 'circle', 'bus-booking-manager' ),
                'far fa-circle' => __( 'circle', 'bus-booking-manager' ),
                'fas fa-circle-notch' => __( 'circle-notch', 'bus-booking-manager' ),
                'fas fa-clipboard' => __( 'clipboard', 'bus-booking-manager' ),
                'far fa-clipboard' => __( 'clipboard', 'bus-booking-manager' ),
                'fas fa-clipboard-check' => __( 'clipboard-check', 'bus-booking-manager' ),
                'fas fa-clipboard-list' => __( 'clipboard-list', 'bus-booking-manager' ),
                'fas fa-clock' => __( 'clock', 'bus-booking-manager' ),
                'far fa-clock' => __( 'clock', 'bus-booking-manager' ),
                'fas fa-clone' => __( 'clone', 'bus-booking-manager' ),
                'far fa-clone' => __( 'clone', 'bus-booking-manager' ),
                'fas fa-closed-captioning' => __( 'closed-captioning', 'bus-booking-manager' ),
                'far fa-closed-captioning' => __( 'closed-captioning', 'bus-booking-manager' ),
                'fas fa-cloud' => __( 'cloud', 'bus-booking-manager' ),
                'fas fa-cloud-download-alt' => __( 'cloud-download-alt', 'bus-booking-manager' ),
                'fas fa-cloud-upload-alt' => __( 'cloud-upload-alt', 'bus-booking-manager' ),
                'fab fa-cloudscale' => __( 'cloudscale', 'bus-booking-manager' ),
                'fab fa-cloudsmith' => __( 'cloudsmith', 'bus-booking-manager' ),
                'fab fa-cloudversify' => __( 'cloudversify', 'bus-booking-manager' ),
                'fas fa-code' => __( 'code', 'bus-booking-manager' ),
                'fas fa-code-branch' => __( 'code-branch', 'bus-booking-manager' ),
                'fab fa-codepen' => __( 'codepen', 'bus-booking-manager' ),
                'fab fa-codiepie' => __( 'codiepie', 'bus-booking-manager' ),
                'fas fa-coffee' => __( 'coffee', 'bus-booking-manager' ),
                'fas fa-cog' => __( 'cog', 'bus-booking-manager' ),
                'fas fa-cogs' => __( 'cogs', 'bus-booking-manager' ),
                'fas fa-columns' => __( 'columns', 'bus-booking-manager' ),
                'fas fa-comment' => __( 'comment', 'bus-booking-manager' ),
                'far fa-comment' => __( 'comment', 'bus-booking-manager' ),
                'fas fa-comment-alt' => __( 'comment-alt', 'bus-booking-manager' ),
                'far fa-comment-alt' => __( 'comment-alt', 'bus-booking-manager' ),
                'fas fa-comment-dots' => __( 'comment-dots', 'bus-booking-manager' ),
                'fas fa-comment-slash' => __( 'comment-slash', 'bus-booking-manager' ),
                'fas fa-comments' => __( 'comments', 'bus-booking-manager' ),
                'far fa-comments' => __( 'comments', 'bus-booking-manager' ),
                'fas fa-compass' => __( 'compass', 'bus-booking-manager' ),
                'far fa-compass' => __( 'compass', 'bus-booking-manager' ),
                'fas fa-compress' => __( 'compress', 'bus-booking-manager' ),
                'fab fa-connectdevelop' => __( 'connectdevelop', 'bus-booking-manager' ),
                'fab fa-contao' => __( 'contao', 'bus-booking-manager' ),
                'fas fa-copy' => __( 'copy', 'bus-booking-manager' ),
                'far fa-copy' => __( 'copy', 'bus-booking-manager' ),
                'fas fa-copyright' => __( 'copyright', 'bus-booking-manager' ),
                'far fa-copyright' => __( 'copyright', 'bus-booking-manager' ),
                'fas fa-couch' => __( 'couch', 'bus-booking-manager' ),
                'fab fa-cpanel' => __( 'cpanel', 'bus-booking-manager' ),
                'fab fa-creative-commons' => __( 'creative-commons', 'bus-booking-manager' ),
                'fas fa-credit-card' => __( 'credit-card', 'bus-booking-manager' ),
                'far fa-credit-card' => __( 'credit-card', 'bus-booking-manager' ),
                'fas fa-crop' => __( 'crop', 'bus-booking-manager' ),
                'fas fa-crosshairs' => __( 'crosshairs', 'bus-booking-manager' ),
                'fab fa-css3' => __( 'css3', 'bus-booking-manager' ),
                'fab fa-css3-alt' => __( 'css3-alt', 'bus-booking-manager' ),
                'fas fa-cube' => __( 'cube', 'bus-booking-manager' ),
                'fas fa-cubes' => __( 'cubes', 'bus-booking-manager' ),
                'fas fa-cut' => __( 'cut', 'bus-booking-manager' ),
                'fab fa-cuttlefish' => __( 'cuttlefish', 'bus-booking-manager' ),
                'fab fa-d-and-d' => __( 'd-and-d', 'bus-booking-manager' ),
                'fab fa-dashcube' => __( 'dashcube', 'bus-booking-manager' ),
                'fas fa-database' => __( 'database', 'bus-booking-manager' ),
                'fas fa-deaf' => __( 'deaf', 'bus-booking-manager' ),
                'fab fa-delicious' => __( 'delicious', 'bus-booking-manager' ),
                'fab fa-deploydog' => __( 'deploydog', 'bus-booking-manager' ),
                'fab fa-deskpro' => __( 'deskpro', 'bus-booking-manager' ),
                'fas fa-desktop' => __( 'desktop', 'bus-booking-manager' ),
                'fab fa-deviantart' => __( 'deviantart', 'bus-booking-manager' ),
                'fas fa-diagnoses' => __( 'diagnoses', 'bus-booking-manager' ),
                'fab fa-digg' => __( 'digg', 'bus-booking-manager' ),
                'fab fa-digital-ocean' => __( 'digital-ocean', 'bus-booking-manager' ),
                'fab fa-discord' => __( 'discord', 'bus-booking-manager' ),
                'fab fa-discourse' => __( 'discourse', 'bus-booking-manager' ),
                'fas fa-dna' => __( 'dna', 'bus-booking-manager' ),
                'fab fa-dochub' => __( 'dochub', 'bus-booking-manager' ),
                'fab fa-docker' => __( 'docker', 'bus-booking-manager' ),
                'fas fa-dollar-sign' => __( 'dollar-sign', 'bus-booking-manager' ),
                'fas fa-dolly' => __( 'dolly', 'bus-booking-manager' ),
                'fas fa-dolly-flatbed' => __( 'dolly-flatbed', 'bus-booking-manager' ),
                'fas fa-donate' => __( 'donate', 'bus-booking-manager' ),
                'fas fa-dot-circle' => __( 'dot-circle', 'bus-booking-manager' ),
                'far fa-dot-circle' => __( 'dot-circle', 'bus-booking-manager' ),
                'fas fa-dove' => __( 'dove', 'bus-booking-manager' ),
                'fas fa-download' => __( 'download', 'bus-booking-manager' ),
                'fab fa-draft2digital' => __( 'draft2digital', 'bus-booking-manager' ),
                'fab fa-dribbble' => __( 'dribbble', 'bus-booking-manager' ),
                'fab fa-dribbble-square' => __( 'dribbble-square', 'bus-booking-manager' ),
                'fab fa-dropbox' => __( 'dropbox', 'bus-booking-manager' ),
                'fab fa-drupal' => __( 'drupal', 'bus-booking-manager' ),
                'fab fa-dyalog' => __( 'dyalog', 'bus-booking-manager' ),
                'fab fa-earlybirds' => __( 'earlybirds', 'bus-booking-manager' ),
                'fab fa-edge' => __( 'edge', 'bus-booking-manager' ),
                'fas fa-edit' => __( 'edit', 'bus-booking-manager' ),
                'far fa-edit' => __( 'edit', 'bus-booking-manager' ),
                'fas fa-eject' => __( 'eject', 'bus-booking-manager' ),
                'fab fa-elementor' => __( 'elementor', 'bus-booking-manager' ),
                'fas fa-ellipsis-h' => __( 'ellipsis-h', 'bus-booking-manager' ),
                'fas fa-ellipsis-v' => __( 'ellipsis-v', 'bus-booking-manager' ),
                'fab fa-ember' => __( 'ember', 'bus-booking-manager' ),
                'fab fa-empire' => __( 'empire', 'bus-booking-manager' ),
                'fas fa-envelope' => __( 'envelope', 'bus-booking-manager' ),
                'far fa-envelope' => __( 'envelope', 'bus-booking-manager' ),
                'fas fa-envelope-open' => __( 'envelope-open', 'bus-booking-manager' ),
                'far fa-envelope-open' => __( 'envelope-open', 'bus-booking-manager' ),
                'fas fa-envelope-square' => __( 'envelope-square', 'bus-booking-manager' ),
                'fab fa-envira' => __( 'envira', 'bus-booking-manager' ),
                'fas fa-eraser' => __( 'eraser', 'bus-booking-manager' ),
                'fab fa-erlang' => __( 'erlang', 'bus-booking-manager' ),
                'fab fa-ethereum' => __( 'ethereum', 'bus-booking-manager' ),
                'fab fa-etsy' => __( 'etsy', 'bus-booking-manager' ),
                'fas fa-euro-sign' => __( 'euro-sign', 'bus-booking-manager' ),
                'fas fa-exchange-alt' => __( 'exchange-alt', 'bus-booking-manager' ),
                'fas fa-exclamation' => __( 'exclamation', 'bus-booking-manager' ),
                'fas fa-exclamation-circle' => __( 'exclamation-circle', 'bus-booking-manager' ),
                'fas fa-exclamation-triangle' => __( 'exclamation-triangle', 'bus-booking-manager' ),
                'fas fa-expand' => __( 'expand', 'bus-booking-manager' ),
                'fas fa-expand-arrows-alt' => __( 'expand-arrows-alt', 'bus-booking-manager' ),
                'fab fa-expeditedssl' => __( 'expeditedssl', 'bus-booking-manager' ),
                'fas fa-external-link-alt' => __( 'external-link-alt', 'bus-booking-manager' ),
                'fas fa-external-link-square-alt' => __( 'external-link-square-alt', 'bus-booking-manager' ),
                'fas fa-eye' => __( 'eye', 'bus-booking-manager' ),
                'fas fa-eye-dropper' => __( 'eye-dropper', 'bus-booking-manager' ),
                'fas fa-eye-slash' => __( 'eye-slash', 'bus-booking-manager' ),
                'far fa-eye-slash' => __( 'eye-slash', 'bus-booking-manager' ),
                'fab fa-facebook' => __( 'facebook', 'bus-booking-manager' ),
                'fab fa-facebook-f' => __( 'facebook-f', 'bus-booking-manager' ),
                'fab fa-facebook-messenger' => __( 'facebook-messenger', 'bus-booking-manager' ),
                'fab fa-facebook-square' => __( 'facebook-square', 'bus-booking-manager' ),
                'fas fa-fast-backward' => __( 'fast-backward', 'bus-booking-manager' ),
                'fas fa-fast-forward' => __( 'fast-forward', 'bus-booking-manager' ),
                'fas fa-fax' => __( 'fax', 'bus-booking-manager' ),
                'fas fa-female' => __( 'female', 'bus-booking-manager' ),
                'fas fa-fighter-jet' => __( 'fighter-jet', 'bus-booking-manager' ),
                'fas fa-file' => __( 'file', 'bus-booking-manager' ),
                'far fa-file' => __( 'file', 'bus-booking-manager' ),
                'fas fa-file-alt' => __( 'file-alt', 'bus-booking-manager' ),
                'far fa-file-alt' => __( 'file-alt', 'bus-booking-manager' ),
                'fas fa-file-archive' => __( 'file-archive', 'bus-booking-manager' ),
                'far fa-file-archive' => __( 'file-archive', 'bus-booking-manager' ),
                'fas fa-file-audio' => __( 'file-audio', 'bus-booking-manager' ),
                'far fa-file-audio' => __( 'file-audio', 'bus-booking-manager' ),
                'fas fa-file-code' => __( 'file-code', 'bus-booking-manager' ),
                'far fa-file-code' => __( 'file-code', 'bus-booking-manager' ),
                'fas fa-file-excel' => __( 'file-excel', 'bus-booking-manager' ),
                'far fa-file-excel' => __( 'file-excel', 'bus-booking-manager' ),
                'fas fa-file-image' => __( 'file-image', 'bus-booking-manager' ),
                'far fa-file-image' => __( 'file-image', 'bus-booking-manager' ),
                'fas fa-file-medical' => __( 'file-medical', 'bus-booking-manager' ),
                'fas fa-file-medical-alt' => __( 'file-medical-alt', 'bus-booking-manager' ),
                'fas fa-file-pdf' => __( 'file-pdf', 'bus-booking-manager' ),
                'far fa-file-pdf' => __( 'file-pdf', 'bus-booking-manager' ),
                'fas fa-file-powerpoint' => __( 'file-powerpoint', 'bus-booking-manager' ),
                'far fa-file-powerpoint' => __( 'file-powerpoint', 'bus-booking-manager' ),
                'fas fa-file-video' => __( 'file-video', 'bus-booking-manager' ),
                'far fa-file-video' => __( 'file-video', 'bus-booking-manager' ),
                'fas fa-file-word' => __( 'file-word', 'bus-booking-manager' ),
                'far fa-file-word' => __( 'file-word', 'bus-booking-manager' ),
                'fas fa-film' => __( 'film', 'bus-booking-manager' ),
                'fas fa-filter' => __( 'filter', 'bus-booking-manager' ),
                'fas fa-fire' => __( 'fire', 'bus-booking-manager' ),
                'fas fa-fire-extinguisher' => __( 'fire-extinguisher', 'bus-booking-manager' ),
                'fab fa-firefox' => __( 'firefox', 'bus-booking-manager' ),
                'fas fa-first-aid' => __( 'first-aid', 'bus-booking-manager' ),
                'fab fa-first-order' => __( 'first-order', 'bus-booking-manager' ),
                'fab fa-firstdraft' => __( 'firstdraft', 'bus-booking-manager' ),
                'fas fa-flag' => __( 'flag', 'bus-booking-manager' ),
                'far fa-flag' => __( 'flag', 'bus-booking-manager' ),
                'fas fa-flag-checkered' => __( 'flag-checkered', 'bus-booking-manager' ),
                'fas fa-flask' => __( 'flask', 'bus-booking-manager' ),
                'fab fa-flickr' => __( 'flickr', 'bus-booking-manager' ),
                'fab fa-flipboard' => __( 'flipboard', 'bus-booking-manager' ),
                'fab fa-fly' => __( 'fly', 'bus-booking-manager' ),
                'fas fa-folder' => __( 'folder', 'bus-booking-manager' ),
                'far fa-folder' => __( 'folder', 'bus-booking-manager' ),
                'fas fa-folder-open' => __( 'folder-open', 'bus-booking-manager' ),
                'far fa-folder-open' => __( 'folder-open', 'bus-booking-manager' ),
                'fas fa-font' => __( 'font', 'bus-booking-manager' ),
                'fab fa-font-awesome' => __( 'font-awesome', 'bus-booking-manager' ),
                'fab fa-font-awesome-alt' => __( 'font-awesome-alt', 'bus-booking-manager' ),
                'fab fa-font-awesome-flag' => __( 'font-awesome-flag', 'bus-booking-manager' ),
                'fab fa-fonticons' => __( 'fonticons', 'bus-booking-manager' ),
                'fab fa-fonticons-fi' => __( 'fonticons-fi', 'bus-booking-manager' ),
                'fas fa-football-ball' => __( 'football-ball', 'bus-booking-manager' ),
                'fab fa-fort-awesome' => __( 'fort-awesome', 'bus-booking-manager' ),
                'fab fa-fort-awesome-alt' => __( 'fort-awesome-alt', 'bus-booking-manager' ),
                'fab fa-forumbee' => __( 'forumbee', 'bus-booking-manager' ),
                'fas fa-forward' => __( 'forward', 'bus-booking-manager' ),
                'fab fa-foursquare' => __( 'foursquare', 'bus-booking-manager' ),
                'fab fa-free-code-camp' => __( 'free-code-camp', 'bus-booking-manager' ),
                'fab fa-freebsd' => __( 'freebsd', 'bus-booking-manager' ),
                'fas fa-frown' => __( 'frown', 'bus-booking-manager' ),
                'far fa-frown' => __( 'frown', 'bus-booking-manager' ),
                'fas fa-futbol' => __( 'futbol', 'bus-booking-manager' ),
                'far fa-futbol' => __( 'futbol', 'bus-booking-manager' ),
                'fas fa-gamepad' => __( 'gamepad', 'bus-booking-manager' ),
                'fas fa-gavel' => __( 'gavel', 'bus-booking-manager' ),
                'fas fa-gem' => __( 'gem', 'bus-booking-manager' ),
                'far fa-gem' => __( 'gem', 'bus-booking-manager' ),
                'fas fa-genderless' => __( 'genderless', 'bus-booking-manager' ),
                'fab fa-get-pocket' => __( 'get-pocket', 'bus-booking-manager' ),
                'fab fa-gg' => __( 'gg', 'bus-booking-manager' ),
                'fab fa-gg-circle' => __( 'gg-circle', 'bus-booking-manager' ),
                'fas fa-gift' => __( 'gift', 'bus-booking-manager' ),
                'fab fa-git' => __( 'git', 'bus-booking-manager' ),
                'fab fa-git-square' => __( 'git-square', 'bus-booking-manager' ),
                'fab fa-github' => __( 'github', 'bus-booking-manager' ),
                'fab fa-github-alt' => __( 'github-alt', 'bus-booking-manager' ),
                'fab fa-github-square' => __( 'github-square', 'bus-booking-manager' ),
                'fab fa-gitkraken' => __( 'gitkraken', 'bus-booking-manager' ),
                'fab fa-gitlab' => __( 'gitlab', 'bus-booking-manager' ),
                'fab fa-gitter' => __( 'gitter', 'bus-booking-manager' ),
                'fas fa-glass-martini' => __( 'glass-martini', 'bus-booking-manager' ),
                'fab fa-glide' => __( 'glide', 'bus-booking-manager' ),
                'fab fa-glide-g' => __( 'glide-g', 'bus-booking-manager' ),
                'fas fa-globe' => __( 'globe', 'bus-booking-manager' ),
                'fab fa-gofore' => __( 'gofore', 'bus-booking-manager' ),
                'fas fa-golf-ball' => __( 'golf-ball', 'bus-booking-manager' ),
                'fab fa-goodreads' => __( 'goodreads', 'bus-booking-manager' ),
                'fab fa-goodreads-g' => __( 'goodreads-g', 'bus-booking-manager' ),
                'fab fa-google' => __( 'google', 'bus-booking-manager' ),
                'fab fa-google-drive' => __( 'google-drive', 'bus-booking-manager' ),
                'fab fa-google-play' => __( 'google-play', 'bus-booking-manager' ),
                'fab fa-google-plus' => __( 'google-plus', 'bus-booking-manager' ),
                'fab fa-google-plus-g' => __( 'google-plus-g', 'bus-booking-manager' ),
                'fab fa-google-plus-square' => __( 'google-plus-square', 'bus-booking-manager' ),
                'fab fa-google-wallet' => __( 'google-wallet', 'bus-booking-manager' ),
                'fas fa-graduation-cap' => __( 'graduation-cap', 'bus-booking-manager' ),
                'fab fa-gratipay' => __( 'gratipay', 'bus-booking-manager' ),
                'fab fa-grav' => __( 'grav', 'bus-booking-manager' ),
                'fab fa-gripfire' => __( 'gripfire', 'bus-booking-manager' ),
                'fab fa-grunt' => __( 'grunt', 'bus-booking-manager' ),
                'fab fa-gulp' => __( 'gulp', 'bus-booking-manager' ),
                'fas fa-h-square' => __( 'h-square', 'bus-booking-manager' ),
                'fab fa-hacker-news' => __( 'hacker-news', 'bus-booking-manager' ),
                'fab fa-hacker-news-square' => __( 'hacker-news-square', 'bus-booking-manager' ),
                'fas fa-hand-holding' => __( 'hand-holding', 'bus-booking-manager' ),
                'fas fa-hand-holding-heart' => __( 'hand-holding-heart', 'bus-booking-manager' ),
                'fas fa-hand-holding-usd' => __( 'hand-holding-usd', 'bus-booking-manager' ),
                'fas fa-hand-lizard' => __( 'hand-lizard', 'bus-booking-manager' ),
                'far fa-hand-lizard' => __( 'hand-lizard', 'bus-booking-manager' ),
                'fas fa-hand-paper' => __( 'hand-paper', 'bus-booking-manager' ),
                'far fa-hand-paper' => __( 'hand-paper', 'bus-booking-manager' ),
                'fas fa-hand-peace' => __( 'hand-peace', 'bus-booking-manager' ),
                'far fa-hand-peace' => __( 'hand-peace', 'bus-booking-manager' ),
                'fas fa-hand-point-down' => __( 'hand-point-down', 'bus-booking-manager' ),
                'far fa-hand-point-down' => __( 'hand-point-down', 'bus-booking-manager' ),
                'fas fa-hand-point-left' => __( 'hand-point-left', 'bus-booking-manager' ),
                'far fa-hand-point-left' => __( 'hand-point-left', 'bus-booking-manager' ),
                'fas fa-hand-point-right' => __( 'hand-point-right', 'bus-booking-manager' ),
                'far fa-hand-point-right' => __( 'hand-point-right', 'bus-booking-manager' ),
                'fas fa-hand-point-up' => __( 'hand-point-up', 'bus-booking-manager' ),
                'far fa-hand-point-up' => __( 'hand-point-up', 'bus-booking-manager' ),
                'fas fa-hand-pointer' => __( 'hand-pointer', 'bus-booking-manager' ),
                'far fa-hand-pointer' => __( 'hand-pointer', 'bus-booking-manager' ),
                'fas fa-hand-rock' => __( 'hand-rock', 'bus-booking-manager' ),
                'far fa-hand-rock' => __( 'hand-rock', 'bus-booking-manager' ),
                'fas fa-hand-scissors' => __( 'hand-scissors', 'bus-booking-manager' ),
                'far fa-hand-scissors' => __( 'hand-scissors', 'bus-booking-manager' ),
                'fas fa-hand-spock' => __( 'hand-spock', 'bus-booking-manager' ),
                'far fa-hand-spock' => __( 'hand-spock', 'bus-booking-manager' ),
                'fas fa-hands' => __( 'hands', 'bus-booking-manager' ),
                'fas fa-hands-helping' => __( 'hands-helping', 'bus-booking-manager' ),
                'fas fa-handshake' => __( 'handshake', 'bus-booking-manager' ),
                'far fa-handshake' => __( 'handshake', 'bus-booking-manager' ),
                'fas fa-hashtag' => __( 'hashtag', 'bus-booking-manager' ),
                'fas fa-hdd' => __( 'hdd', 'bus-booking-manager' ),
                'far fa-hdd' => __( 'hdd', 'bus-booking-manager' ),
                'fas fa-heading' => __( 'heading', 'bus-booking-manager' ),
                'fas fa-headphones' => __( 'headphones', 'bus-booking-manager' ),
                'fas fa-heart' => __( 'heart', 'bus-booking-manager' ),
                'far fa-heart' => __( 'heart', 'bus-booking-manager' ),
                'fas fa-heartbeat' => __( 'heartbeat', 'bus-booking-manager' ),
                'fab fa-hips' => __( 'hips', 'bus-booking-manager' ),
                'fab fa-hire-a-helper' => __( 'hire-a-helper', 'bus-booking-manager' ),
                'fas fa-history' => __( 'history', 'bus-booking-manager' ),
                'fas fa-hockey-puck' => __( 'hockey-puck', 'bus-booking-manager' ),
                'fas fa-home' => __( 'home', 'bus-booking-manager' ),
                'fab fa-hooli' => __( 'hooli', 'bus-booking-manager' ),
                'fas fa-hospital' => __( 'hospital', 'bus-booking-manager' ),
                'far fa-hospital' => __( 'hospital', 'bus-booking-manager' ),
                'fas fa-hospital-alt' => __( 'hospital-alt', 'bus-booking-manager' ),
                'fas fa-hospital-symbol' => __( 'hospital-symbol', 'bus-booking-manager' ),
                'fab fa-hotjar' => __( 'hotjar', 'bus-booking-manager' ),
                'fas fa-hourglass' => __( 'hourglass', 'bus-booking-manager' ),
                'far fa-hourglass' => __( 'hourglass', 'bus-booking-manager' ),
                'fas fa-hourglass-end' => __( 'hourglass-end', 'bus-booking-manager' ),
                'fas fa-hourglass-half' => __( 'hourglass-half', 'bus-booking-manager' ),
                'fas fa-hourglass-start' => __( 'hourglass-start', 'bus-booking-manager' ),
                'fab fa-houzz' => __( 'houzz', 'bus-booking-manager' ),
                'fab fa-html5' => __( 'html5', 'bus-booking-manager' ),
                'fab fa-hubspot' => __( 'hubspot', 'bus-booking-manager' ),
                'fas fa-i-cursor' => __( 'i-cursor', 'bus-booking-manager' ),
                'fas fa-id-badge' => __( 'id-badge', 'bus-booking-manager' ),
                'far fa-id-badge' => __( 'id-badge', 'bus-booking-manager' ),
                'fas fa-id-card' => __( 'id-card', 'bus-booking-manager' ),
                'far fa-id-card' => __( 'id-card', 'bus-booking-manager' ),
                'fas fa-id-card-alt' => __( 'id-card-alt', 'bus-booking-manager' ),
                'fas fa-image' => __( 'image', 'bus-booking-manager' ),
                'far fa-image' => __( 'image', 'bus-booking-manager' ),
                'fas fa-images' => __( 'images', 'bus-booking-manager' ),
                'far fa-images' => __( 'images', 'bus-booking-manager' ),
                'fab fa-imdb' => __( 'imdb', 'bus-booking-manager' ),
                'fas fa-inbox' => __( 'inbox', 'bus-booking-manager' ),
                'fas fa-indent' => __( 'indent', 'bus-booking-manager' ),
                'fas fa-industry' => __( 'industry', 'bus-booking-manager' ),
                'fas fa-info' => __( 'info', 'bus-booking-manager' ),
                'fas fa-info-circle' => __( 'info-circle', 'bus-booking-manager' ),
                'fab fa-instagram' => __( 'instagram', 'bus-booking-manager' ),
                'fab fa-internet-explorer' => __( 'internet-explorer', 'bus-booking-manager' ),
                'fab fa-ioxhost' => __( 'ioxhost', 'bus-booking-manager' ),
                'fas fa-italic' => __( 'italic', 'bus-booking-manager' ),
                'fab fa-itunes' => __( 'itunes', 'bus-booking-manager' ),
                'fab fa-itunes-note' => __( 'itunes-note', 'bus-booking-manager' ),
                'fab fa-java' => __( 'java', 'bus-booking-manager' ),
                'fab fa-jenkins' => __( 'jenkins', 'bus-booking-manager' ),
                'fab fa-joget' => __( 'joget', 'bus-booking-manager' ),
                'fab fa-joomla' => __( 'joomla', 'bus-booking-manager' ),
                'fab fa-js' => __( 'js', 'bus-booking-manager' ),
                'fab fa-js-square' => __( 'js-square', 'bus-booking-manager' ),
                'fab fa-jsfiddle' => __( 'jsfiddle', 'bus-booking-manager' ),
                'fas fa-key' => __( 'key', 'bus-booking-manager' ),
                'fas fa-keyboard' => __( 'keyboard', 'bus-booking-manager' ),
                'far fa-keyboard' => __( 'keyboard', 'bus-booking-manager' ),
                'fab fa-keycdn' => __( 'keycdn', 'bus-booking-manager' ),
                'fab fa-kickstarter' => __( 'kickstarter', 'bus-booking-manager' ),
                'fab fa-kickstarter-k' => __( 'kickstarter-k', 'bus-booking-manager' ),
                'fab fa-korvue' => __( 'korvue', 'bus-booking-manager' ),
                'fas fa-language' => __( 'language', 'bus-booking-manager' ),
                'fas fa-laptop' => __( 'laptop', 'bus-booking-manager' ),
                'fab fa-laravel' => __( 'laravel', 'bus-booking-manager' ),
                'fab fa-lastfm' => __( 'lastfm', 'bus-booking-manager' ),
                'fab fa-lastfm-square' => __( 'lastfm-square', 'bus-booking-manager' ),
                'fas fa-leaf' => __( 'leaf', 'bus-booking-manager' ),
                'fab fa-leanpub' => __( 'leanpub', 'bus-booking-manager' ),
                'fas fa-lemon' => __( 'lemon', 'bus-booking-manager' ),
                'far fa-lemon' => __( 'lemon', 'bus-booking-manager' ),
                'fab fa-less' => __( 'less', 'bus-booking-manager' ),
                'fas fa-level-down-alt' => __( 'level-down-alt', 'bus-booking-manager' ),
                'fas fa-level-up-alt' => __( 'level-up-alt', 'bus-booking-manager' ),
                'fas fa-life-ring' => __( 'life-ring', 'bus-booking-manager' ),
                'far fa-life-ring' => __( 'life-ring', 'bus-booking-manager' ),
                'fas fa-lightbulb' => __( 'lightbulb', 'bus-booking-manager' ),
                'far fa-lightbulb' => __( 'lightbulb', 'bus-booking-manager' ),
                'fab fa-line' => __( 'line', 'bus-booking-manager' ),
                'fas fa-link' => __( 'link', 'bus-booking-manager' ),
                'fab fa-linkedin' => __( 'linkedin', 'bus-booking-manager' ),
                'fab fa-linkedin-in' => __( 'linkedin-in', 'bus-booking-manager' ),
                'fab fa-linode' => __( 'linode', 'bus-booking-manager' ),
                'fab fa-linux' => __( 'linux', 'bus-booking-manager' ),
                'fas fa-lira-sign' => __( 'lira-sign', 'bus-booking-manager' ),
                'fas fa-list' => __( 'list', 'bus-booking-manager' ),
                'fas fa-list-alt' => __( 'list-alt', 'bus-booking-manager' ),
                'far fa-list-alt' => __( 'list-alt', 'bus-booking-manager' ),
                'fas fa-list-ol' => __( 'list-ol', 'bus-booking-manager' ),
                'fas fa-list-ul' => __( 'list-ul', 'bus-booking-manager' ),
                'fas fa-location-arrow' => __( 'location-arrow', 'bus-booking-manager' ),
                'fas fa-lock' => __( 'lock', 'bus-booking-manager' ),
                'fas fa-lock-open' => __( 'lock-open', 'bus-booking-manager' ),
                'fas fa-long-arrow-alt-down' => __( 'long-arrow-alt-down', 'bus-booking-manager' ),
                'fas fa-long-arrow-alt-left' => __( 'long-arrow-alt-left', 'bus-booking-manager' ),
                'fas fa-long-arrow-alt-right' => __( 'long-arrow-alt-right', 'bus-booking-manager' ),
                'fas fa-long-arrow-alt-up' => __( 'long-arrow-alt-up', 'bus-booking-manager' ),
                'fas fa-low-vision' => __( 'low-vision', 'bus-booking-manager' ),
                'fab fa-lyft' => __( 'lyft', 'bus-booking-manager' ),
                'fab fa-magento' => __( 'magento', 'bus-booking-manager' ),
                'fas fa-magic' => __( 'magic', 'bus-booking-manager' ),
                'fas fa-magnet' => __( 'magnet', 'bus-booking-manager' ),
                'fas fa-male' => __( 'male', 'bus-booking-manager' ),
                'fas fa-map' => __( 'map', 'bus-booking-manager' ),
                'far fa-map' => __( 'map', 'bus-booking-manager' ),
                'fas fa-map-marker' => __( 'map-marker', 'bus-booking-manager' ),
                'fas fa-map-marker-alt' => __( 'map-marker-alt', 'bus-booking-manager' ),
                'fas fa-map-pin' => __( 'map-pin', 'bus-booking-manager' ),
                'fas fa-map-signs' => __( 'map-signs', 'bus-booking-manager' ),
                'fas fa-mars' => __( 'mars', 'bus-booking-manager' ),
                'fas fa-mars-double' => __( 'mars-double', 'bus-booking-manager' ),
                'fas fa-mars-stroke' => __( 'mars-stroke', 'bus-booking-manager' ),
                'fas fa-mars-stroke-h' => __( 'mars-stroke-h', 'bus-booking-manager' ),
                'fas fa-mars-stroke-v' => __( 'mars-stroke-v', 'bus-booking-manager' ),
                'fab fa-maxcdn' => __( 'maxcdn', 'bus-booking-manager' ),
                'fab fa-medapps' => __( 'medapps', 'bus-booking-manager' ),
                'fab fa-medium' => __( 'medium', 'bus-booking-manager' ),
                'fab fa-medium-m' => __( 'medium-m', 'bus-booking-manager' ),
                'fas fa-medkit' => __( 'medkit', 'bus-booking-manager' ),
                'fab fa-medrt' => __( 'medrt', 'bus-booking-manager' ),
                'fab fa-meetup' => __( 'meetup', 'bus-booking-manager' ),
                'fas fa-meh' => __( 'meh', 'bus-booking-manager' ),
                'far fa-meh' => __( 'meh', 'bus-booking-manager' ),
                'fas fa-mercury' => __( 'mercury', 'bus-booking-manager' ),
                'fas fa-microchip' => __( 'microchip', 'bus-booking-manager' ),
                'fas fa-microphone' => __( 'microphone', 'bus-booking-manager' ),
                'fas fa-microphone-slash' => __( 'microphone-slash', 'bus-booking-manager' ),
                'fab fa-microsoft' => __( 'microsoft', 'bus-booking-manager' ),
                'fas fa-minus' => __( 'minus', 'bus-booking-manager' ),
                'fas fa-minus-circle' => __( 'minus-circle', 'bus-booking-manager' ),
                'fas fa-minus-square' => __( 'minus-square', 'bus-booking-manager' ),
                'far fa-minus-square' => __( 'minus-square', 'bus-booking-manager' ),
                'fab fa-mix' => __( 'mix', 'bus-booking-manager' ),
                'fab fa-mixcloud' => __( 'mixcloud', 'bus-booking-manager' ),
                'fab fa-mizuni' => __( 'mizuni', 'bus-booking-manager' ),
                'fas fa-mobile' => __( 'mobile', 'bus-booking-manager' ),
                'fas fa-mobile-alt' => __( 'mobile-alt', 'bus-booking-manager' ),
                'fab fa-modx' => __( 'modx', 'bus-booking-manager' ),
                'fab fa-monero' => __( 'monero', 'bus-booking-manager' ),
                'fas fa-money-bill-alt' => __( 'money-bill-alt', 'bus-booking-manager' ),
                'far fa-money-bill-alt' => __( 'money-bill-alt', 'bus-booking-manager' ),
                'fas fa-moon' => __( 'moon', 'bus-booking-manager' ),
                'far fa-moon' => __( 'moon', 'bus-booking-manager' ),
                'fas fa-motorcycle' => __( 'motorcycle', 'bus-booking-manager' ),
                'fas fa-mouse-pointer' => __( 'mouse-pointer', 'bus-booking-manager' ),
                'fas fa-music' => __( 'music', 'bus-booking-manager' ),
                'fab fa-napster' => __( 'napster', 'bus-booking-manager' ),
                'fas fa-neuter' => __( 'neuter', 'bus-booking-manager' ),
                'fas fa-newspaper' => __( 'newspaper', 'bus-booking-manager' ),
                'far fa-newspaper' => __( 'newspaper', 'bus-booking-manager' ),
                'fab fa-nintendo-switch' => __( 'nintendo-switch', 'bus-booking-manager' ),
                'fab fa-node' => __( 'node', 'bus-booking-manager' ),
                'fab fa-node-js' => __( 'node-js', 'bus-booking-manager' ),
                'fas fa-notes-medical' => __( 'notes-medical', 'bus-booking-manager' ),
                'fab fa-npm' => __( 'npm', 'bus-booking-manager' ),
                'fab fa-ns8' => __( 'ns8', 'bus-booking-manager' ),
                'fab fa-nutritionix' => __( 'nutritionix', 'bus-booking-manager' ),
                'fas fa-object-group' => __( 'object-group', 'bus-booking-manager' ),
                'far fa-object-group' => __( 'object-group', 'bus-booking-manager' ),
                'fas fa-object-ungroup' => __( 'object-ungroup', 'bus-booking-manager' ),
                'far fa-object-ungroup' => __( 'object-ungroup', 'bus-booking-manager' ),
                'fab fa-odnoklassniki' => __( 'odnoklassniki', 'bus-booking-manager' ),
                'fab fa-odnoklassniki-square' => __( 'odnoklassniki-square', 'bus-booking-manager' ),
                'fab fa-opencart' => __( 'opencart', 'bus-booking-manager' ),
                'fab fa-openid' => __( 'openid', 'bus-booking-manager' ),
                'fab fa-opera' => __( 'opera', 'bus-booking-manager' ),
                'fab fa-optin-monster' => __( 'optin-monster', 'bus-booking-manager' ),
                'fab fa-osi' => __( 'osi', 'bus-booking-manager' ),
                'fas fa-outdent' => __( 'outdent', 'bus-booking-manager' ),
                'fab fa-page4' => __( 'page4', 'bus-booking-manager' ),
                'fab fa-pagelines' => __( 'pagelines', 'bus-booking-manager' ),
                'fas fa-paint-brush' => __( 'paint-brush', 'bus-booking-manager' ),
                'fab fa-palfed' => __( 'palfed', 'bus-booking-manager' ),
                'fas fa-pallet' => __( 'pallet', 'bus-booking-manager' ),
                'fas fa-paper-plane' => __( 'paper-plane', 'bus-booking-manager' ),
                'far fa-paper-plane' => __( 'paper-plane', 'bus-booking-manager' ),
                'fas fa-paperclip' => __( 'paperclip', 'bus-booking-manager' ),
                'fas fa-parachute-box' => __( 'parachute-box', 'bus-booking-manager' ),
                'fas fa-paragraph' => __( 'paragraph', 'bus-booking-manager' ),
                'fas fa-paste' => __( 'paste', 'bus-booking-manager' ),
                'fab fa-patreon' => __( 'patreon', 'bus-booking-manager' ),
                'fas fa-pause' => __( 'pause', 'bus-booking-manager' ),
                'fas fa-pause-circle' => __( 'pause-circle', 'bus-booking-manager' ),
                'far fa-pause-circle' => __( 'pause-circle', 'bus-booking-manager' ),
                'fas fa-paw' => __( 'paw', 'bus-booking-manager' ),
                'fab fa-paypal' => __( 'paypal', 'bus-booking-manager' ),
                'fas fa-pen-square' => __( 'pen-square', 'bus-booking-manager' ),
                'fas fa-pencil-alt' => __( 'pencil-alt', 'bus-booking-manager' ),
                'fas fa-people-carry' => __( 'people-carry', 'bus-booking-manager' ),
                'fas fa-percent' => __( 'percent', 'bus-booking-manager' ),
                'fab fa-periscope' => __( 'periscope', 'bus-booking-manager' ),
                'fab fa-phabricator' => __( 'phabricator', 'bus-booking-manager' ),
                'fab fa-phoenix-framework' => __( 'phoenix-framework', 'bus-booking-manager' ),
                'fas fa-phone' => __( 'phone', 'bus-booking-manager' ),
                'fas fa-phone-slash' => __( 'phone-slash', 'bus-booking-manager' ),
                'fas fa-phone-square' => __( 'phone-square', 'bus-booking-manager' ),
                'fas fa-phone-volume' => __( 'phone-volume', 'bus-booking-manager' ),
                'fab fa-php' => __( 'php', 'bus-booking-manager' ),
                'fab fa-pied-piper' => __( 'pied-piper', 'bus-booking-manager' ),
                'fab fa-pied-piper-alt' => __( 'pied-piper-alt', 'bus-booking-manager' ),
                'fab fa-pied-piper-hat' => __( 'pied-piper-hat', 'bus-booking-manager' ),
                'fab fa-pied-piper-pp' => __( 'pied-piper-pp', 'bus-booking-manager' ),
                'fas fa-piggy-bank' => __( 'piggy-bank', 'bus-booking-manager' ),
                'fas fa-pills' => __( 'pills', 'bus-booking-manager' ),
                'fab fa-pinterest' => __( 'pinterest', 'bus-booking-manager' ),
                'fab fa-pinterest-p' => __( 'pinterest-p', 'bus-booking-manager' ),
                'fab fa-pinterest-square' => __( 'pinterest-square', 'bus-booking-manager' ),
                'fas fa-plane' => __( 'plane', 'bus-booking-manager' ),
                'fas fa-play' => __( 'play', 'bus-booking-manager' ),
                'fas fa-play-circle' => __( 'play-circle', 'bus-booking-manager' ),
                'far fa-play-circle' => __( 'play-circle', 'bus-booking-manager' ),
                'fab fa-playstation' => __( 'playstation', 'bus-booking-manager' ),
                'fas fa-plug' => __( 'plug', 'bus-booking-manager' ),
                'fas fa-plus' => __( 'plus', 'bus-booking-manager' ),
                'fas fa-plus-circle' => __( 'plus-circle', 'bus-booking-manager' ),
                'fas fa-plus-square' => __( 'plus-square', 'bus-booking-manager' ),
                'far fa-plus-square' => __( 'plus-square', 'bus-booking-manager' ),
                'fas fa-podcast' => __( 'podcast', 'bus-booking-manager' ),
                'fas fa-poo' => __( 'poo', 'bus-booking-manager' ),
                'fas fa-pound-sign' => __( 'pound-sign', 'bus-booking-manager' ),
                'fas fa-power-off' => __( 'power-off', 'bus-booking-manager' ),
                'fas fa-prescription-bottle' => __( 'prescription-bottle', 'bus-booking-manager' ),
                'fas fa-prescription-bottle-alt' => __( 'prescription-bottle-alt', 'bus-booking-manager' ),
                'fas fa-print' => __( 'print', 'bus-booking-manager' ),
                'fas fa-procedures' => __( 'procedures', 'bus-booking-manager' ),
                'fab fa-product-hunt' => __( 'product-hunt', 'bus-booking-manager' ),
                'fab fa-pushed' => __( 'pushed', 'bus-booking-manager' ),
                'fas fa-puzzle-piece' => __( 'puzzle-piece', 'bus-booking-manager' ),
                'fab fa-python' => __( 'python', 'bus-booking-manager' ),
                'fab fa-qq' => __( 'qq', 'bus-booking-manager' ),
                'fas fa-qrcode' => __( 'qrcode', 'bus-booking-manager' ),
                'fas fa-question' => __( 'question', 'bus-booking-manager' ),
                'fas fa-question-circle' => __( 'question-circle', 'bus-booking-manager' ),
                'far fa-question-circle' => __( 'question-circle', 'bus-booking-manager' ),
                'fas fa-quidditch' => __( 'quidditch', 'bus-booking-manager' ),
                'fab fa-quinscape' => __( 'quinscape', 'bus-booking-manager' ),
                'fab fa-quora' => __( 'quora', 'bus-booking-manager' ),
                'fas fa-quote-left' => __( 'quote-left', 'bus-booking-manager' ),
                'fas fa-quote-right' => __( 'quote-right', 'bus-booking-manager' ),
                'fas fa-random' => __( 'random', 'bus-booking-manager' ),
                'fab fa-ravelry' => __( 'ravelry', 'bus-booking-manager' ),
                'fab fa-react' => __( 'react', 'bus-booking-manager' ),
                'fab fa-readme' => __( 'readme', 'bus-booking-manager' ),
                'fab fa-rebel' => __( 'rebel', 'bus-booking-manager' ),
                'fas fa-recycle' => __( 'recycle', 'bus-booking-manager' ),
                'fab fa-red-river' => __( 'red-river', 'bus-booking-manager' ),
                'fab fa-reddit' => __( 'reddit', 'bus-booking-manager' ),
                'fab fa-reddit-alien' => __( 'reddit-alien', 'bus-booking-manager' ),
                'fab fa-reddit-square' => __( 'reddit-square', 'bus-booking-manager' ),
                'fas fa-redo' => __( 'redo', 'bus-booking-manager' ),
                'fas fa-redo-alt' => __( 'redo-alt', 'bus-booking-manager' ),
                'fas fa-registered' => __( 'registered', 'bus-booking-manager' ),
                'far fa-registered' => __( 'registered', 'bus-booking-manager' ),
                'fab fa-rendact' => __( 'rendact', 'bus-booking-manager' ),
                'fab fa-renren' => __( 'renren', 'bus-booking-manager' ),
                'fas fa-reply' => __( 'reply', 'bus-booking-manager' ),
                'fas fa-reply-all' => __( 'reply-all', 'bus-booking-manager' ),
                'fab fa-replyd' => __( 'replyd', 'bus-booking-manager' ),
                'fab fa-resolving' => __( 'resolving', 'bus-booking-manager' ),
                'fas fa-retweet' => __( 'retweet', 'bus-booking-manager' ),
                'fas fa-ribbon' => __( 'ribbon', 'bus-booking-manager' ),
                'fas fa-road' => __( 'road', 'bus-booking-manager' ),
                'fas fa-rocket' => __( 'rocket', 'bus-booking-manager' ),
                'fab fa-rocketchat' => __( 'rocketchat', 'bus-booking-manager' ),
                'fab fa-rockrms' => __( 'rockrms', 'bus-booking-manager' ),
                'fas fa-rss' => __( 'rss', 'bus-booking-manager' ),
                'fas fa-rss-square' => __( 'rss-square', 'bus-booking-manager' ),
                'fas fa-ruble-sign' => __( 'ruble-sign', 'bus-booking-manager' ),
                'fas fa-rupee-sign' => __( 'rupee-sign', 'bus-booking-manager' ),
                'fab fa-safari' => __( 'safari', 'bus-booking-manager' ),
                'fab fa-sass' => __( 'sass', 'bus-booking-manager' ),
                'fas fa-save' => __( 'save', 'bus-booking-manager' ),
                'far fa-save' => __( 'save', 'bus-booking-manager' ),
                'fab fa-schlix' => __( 'schlix', 'bus-booking-manager' ),
                'fab fa-scribd' => __( 'scribd', 'bus-booking-manager' ),
                'fas fa-search' => __( 'search', 'bus-booking-manager' ),
                'fas fa-search-minus' => __( 'search-minus', 'bus-booking-manager' ),
                'fas fa-search-plus' => __( 'search-plus', 'bus-booking-manager' ),
                'fab fa-searchengin' => __( 'searchengin', 'bus-booking-manager' ),
                'fas fa-seedling' => __( 'seedling', 'bus-booking-manager' ),
                'fab fa-sellcast' => __( 'sellcast', 'bus-booking-manager' ),
                'fab fa-sellsy' => __( 'sellsy', 'bus-booking-manager' ),
                'fas fa-server' => __( 'server', 'bus-booking-manager' ),
                'fab fa-servicestack' => __( 'servicestack', 'bus-booking-manager' ),
                'fas fa-share' => __( 'share', 'bus-booking-manager' ),
                'fas fa-share-alt' => __( 'share-alt', 'bus-booking-manager' ),
                'fas fa-share-alt-square' => __( 'share-alt-square', 'bus-booking-manager' ),
                'fas fa-share-square' => __( 'share-square', 'bus-booking-manager' ),
                'far fa-share-square' => __( 'share-square', 'bus-booking-manager' ),
                'fas fa-shekel-sign' => __( 'shekel-sign', 'bus-booking-manager' ),
                'fas fa-shield-alt' => __( 'shield-alt', 'bus-booking-manager' ),
                'fas fa-ship' => __( 'ship', 'bus-booking-manager' ),
                'fas fa-shipping-fast' => __( 'shipping-fast', 'bus-booking-manager' ),
                'fab fa-shirtsinbulk' => __( 'shirtsinbulk', 'bus-booking-manager' ),
                'fas fa-shopping-bag' => __( 'shopping-bag', 'bus-booking-manager' ),
                'fas fa-shopping-basket' => __( 'shopping-basket', 'bus-booking-manager' ),
                'fas fa-shopping-cart' => __( 'shopping-cart', 'bus-booking-manager' ),
                'fas fa-shower' => __( 'shower', 'bus-booking-manager' ),
                'fas fa-sign' => __( 'sign', 'bus-booking-manager' ),
                'fas fa-sign-in-alt' => __( 'sign-in-alt', 'bus-booking-manager' ),
                'fas fa-sign-language' => __( 'sign-language', 'bus-booking-manager' ),
                'fas fa-sign-out-alt' => __( 'sign-out-alt', 'bus-booking-manager' ),
                'fas fa-signal' => __( 'signal', 'bus-booking-manager' ),
                'fab fa-simplybuilt' => __( 'simplybuilt', 'bus-booking-manager' ),
                'fab fa-sistrix' => __( 'sistrix', 'bus-booking-manager' ),
                'fas fa-sitemap' => __( 'sitemap', 'bus-booking-manager' ),
                'fab fa-skyatlas' => __( 'skyatlas', 'bus-booking-manager' ),
                'fab fa-skype' => __( 'skype', 'bus-booking-manager' ),
                'fab fa-slack' => __( 'slack', 'bus-booking-manager' ),
                'fab fa-slack-hash' => __( 'slack-hash', 'bus-booking-manager' ),
                'fas fa-sliders-h' => __( 'sliders-h', 'bus-booking-manager' ),
                'fab fa-slideshare' => __( 'slideshare', 'bus-booking-manager' ),
                'fas fa-smile' => __( 'smile', 'bus-booking-manager' ),
                'far fa-smile' => __( 'smile', 'bus-booking-manager' ),
                'fas fa-smoking' => __( 'smoking', 'bus-booking-manager' ),
                'fab fa-snapchat' => __( 'snapchat', 'bus-booking-manager' ),
                'fab fa-snapchat-ghost' => __( 'snapchat-ghost', 'bus-booking-manager' ),
                'fab fa-snapchat-square' => __( 'snapchat-square', 'bus-booking-manager' ),
                'fas fa-snowflake' => __( 'snowflake', 'bus-booking-manager' ),
                'far fa-snowflake' => __( 'snowflake', 'bus-booking-manager' ),
                'fas fa-sort' => __( 'sort', 'bus-booking-manager' ),
                'fas fa-sort-alpha-down' => __( 'sort-alpha-down', 'bus-booking-manager' ),
                'fas fa-sort-alpha-up' => __( 'sort-alpha-up', 'bus-booking-manager' ),
                'fas fa-sort-amount-down' => __( 'sort-amount-down', 'bus-booking-manager' ),
                'fas fa-sort-amount-up' => __( 'sort-amount-up', 'bus-booking-manager' ),
                'fas fa-sort-down' => __( 'sort-down', 'bus-booking-manager' ),
                'fas fa-sort-numeric-down' => __( 'sort-numeric-down', 'bus-booking-manager' ),
                'fas fa-sort-numeric-up' => __( 'sort-numeric-up', 'bus-booking-manager' ),
                'fas fa-sort-up' => __( 'sort-up', 'bus-booking-manager' ),
                'fab fa-soundcloud' => __( 'soundcloud', 'bus-booking-manager' ),
                'fas fa-space-shuttle' => __( 'space-shuttle', 'bus-booking-manager' ),
                'fab fa-speakap' => __( 'speakap', 'bus-booking-manager' ),
                'fas fa-spinner' => __( 'spinner', 'bus-booking-manager' ),
                'fab fa-spotify' => __( 'spotify', 'bus-booking-manager' ),
                'fas fa-square' => __( 'square', 'bus-booking-manager' ),
                'far fa-square' => __( 'square', 'bus-booking-manager' ),
                'fas fa-square-full' => __( 'square-full', 'bus-booking-manager' ),
                'fab fa-stack-exchange' => __( 'stack-exchange', 'bus-booking-manager' ),
                'fab fa-stack-overflow' => __( 'stack-overflow', 'bus-booking-manager' ),
                'fas fa-star' => __( 'star', 'bus-booking-manager' ),
                'far fa-star' => __( 'star', 'bus-booking-manager' ),
                'fas fa-star-half' => __( 'star-half', 'bus-booking-manager' ),
                'far fa-star-half' => __( 'star-half', 'bus-booking-manager' ),
                'fab fa-staylinked' => __( 'staylinked', 'bus-booking-manager' ),
                'fab fa-steam' => __( 'steam', 'bus-booking-manager' ),
                'fab fa-steam-square' => __( 'steam-square', 'bus-booking-manager' ),
                'fab fa-steam-symbol' => __( 'steam-symbol', 'bus-booking-manager' ),
                'fas fa-step-backward' => __( 'step-backward', 'bus-booking-manager' ),
                'fas fa-step-forward' => __( 'step-forward', 'bus-booking-manager' ),
                'fas fa-stethoscope' => __( 'stethoscope', 'bus-booking-manager' ),
                'fab fa-sticker-mule' => __( 'sticker-mule', 'bus-booking-manager' ),
                'fas fa-sticky-note' => __( 'sticky-note', 'bus-booking-manager' ),
                'far fa-sticky-note' => __( 'sticky-note', 'bus-booking-manager' ),
                'fas fa-stop' => __( 'stop', 'bus-booking-manager' ),
                'fas fa-stop-circle' => __( 'stop-circle', 'bus-booking-manager' ),
                'far fa-stop-circle' => __( 'stop-circle', 'bus-booking-manager' ),
                'fas fa-stopwatch' => __( 'stopwatch', 'bus-booking-manager' ),
                'fab fa-strava' => __( 'strava', 'bus-booking-manager' ),
                'fas fa-street-view' => __( 'street-view', 'bus-booking-manager' ),
                'fas fa-strikethrough' => __( 'strikethrough', 'bus-booking-manager' ),
                'fab fa-stripe' => __( 'stripe', 'bus-booking-manager' ),
                'fab fa-stripe-s' => __( 'stripe-s', 'bus-booking-manager' ),
                'fab fa-studiovinari' => __( 'studiovinari', 'bus-booking-manager' ),
                'fab fa-stumbleupon' => __( 'stumbleupon', 'bus-booking-manager' ),
                'fab fa-stumbleupon-circle' => __( 'stumbleupon-circle', 'bus-booking-manager' ),
                'fas fa-subscript' => __( 'subscript', 'bus-booking-manager' ),
                'fas fa-subway' => __( 'subway', 'bus-booking-manager' ),
                'fas fa-suitcase' => __( 'suitcase', 'bus-booking-manager' ),
                'fas fa-sun' => __( 'sun', 'bus-booking-manager' ),
                'far fa-sun' => __( 'sun', 'bus-booking-manager' ),
                'fab fa-superpowers' => __( 'superpowers', 'bus-booking-manager' ),
                'fas fa-superscript' => __( 'superscript', 'bus-booking-manager' ),
                'fab fa-supple' => __( 'supple', 'bus-booking-manager' ),
                'fas fa-sync' => __( 'sync', 'bus-booking-manager' ),
                'fas fa-sync-alt' => __( 'sync-alt', 'bus-booking-manager' ),
                'fas fa-syringe' => __( 'syringe', 'bus-booking-manager' ),
                'fas fa-table' => __( 'table', 'bus-booking-manager' ),
                'fas fa-table-tennis' => __( 'table-tennis', 'bus-booking-manager' ),
                'fas fa-tablet' => __( 'tablet', 'bus-booking-manager' ),
                'fas fa-tablet-alt' => __( 'tablet-alt', 'bus-booking-manager' ),
                'fas fa-tablets' => __( 'tablets', 'bus-booking-manager' ),
                'fas fa-tachometer-alt' => __( 'tachometer-alt', 'bus-booking-manager' ),
                'fas fa-tag' => __( 'tag', 'bus-booking-manager' ),
                'fas fa-tags' => __( 'tags', 'bus-booking-manager' ),
                'fas fa-tape' => __( 'tape', 'bus-booking-manager' ),
                'fas fa-tasks' => __( 'tasks', 'bus-booking-manager' ),
                'fas fa-taxi' => __( 'taxi', 'bus-booking-manager' ),
                'fab fa-telegram' => __( 'telegram', 'bus-booking-manager' ),
                'fab fa-telegram-plane' => __( 'telegram-plane', 'bus-booking-manager' ),
                'fab fa-tencent-weibo' => __( 'tencent-weibo', 'bus-booking-manager' ),
                'fas fa-terminal' => __( 'terminal', 'bus-booking-manager' ),
                'fas fa-text-height' => __( 'text-height', 'bus-booking-manager' ),
                'fas fa-text-width' => __( 'text-width', 'bus-booking-manager' ),
                'fas fa-th' => __( 'th', 'bus-booking-manager' ),
                'fas fa-th-large' => __( 'th-large', 'bus-booking-manager' ),
                'fas fa-th-list' => __( 'th-list', 'bus-booking-manager' ),
                'fab fa-themeisle' => __( 'themeisle', 'bus-booking-manager' ),
                'fas fa-thermometer' => __( 'thermometer', 'bus-booking-manager' ),
                'fas fa-thermometer-empty' => __( 'thermometer-empty', 'bus-booking-manager' ),
                'fas fa-thermometer-full' => __( 'thermometer-full', 'bus-booking-manager' ),
                'fas fa-thermometer-half' => __( 'thermometer-half', 'bus-booking-manager' ),
                'fas fa-thermometer-quarter' => __( 'thermometer-quarter', 'bus-booking-manager' ),
                'fas fa-thermometer-three-quarters' => __( 'thermometer-three-quarters', 'bus-booking-manager' ),
                'fas fa-thumbs-down' => __( 'thumbs-down', 'bus-booking-manager' ),
                'far fa-thumbs-down' => __( 'thumbs-down', 'bus-booking-manager' ),
                'fas fa-thumbs-up' => __( 'thumbs-up', 'bus-booking-manager' ),
                'far fa-thumbs-up' => __( 'thumbs-up', 'bus-booking-manager' ),
                'fas fa-thumbtack' => __( 'thumbtack', 'bus-booking-manager' ),
                'fas fa-ticket-alt' => __( 'ticket-alt', 'bus-booking-manager' ),
                'fas fa-times' => __( 'times', 'bus-booking-manager' ),
                'fas fa-times-circle' => __( 'times-circle', 'bus-booking-manager' ),
                'far fa-times-circle' => __( 'times-circle', 'bus-booking-manager' ),
                'fas fa-tint' => __( 'tint', 'bus-booking-manager' ),
                'fas fa-toggle-off' => __( 'toggle-off', 'bus-booking-manager' ),
                'fas fa-toggle-on' => __( 'toggle-on', 'bus-booking-manager' ),
                'fas fa-trademark' => __( 'trademark', 'bus-booking-manager' ),
                'fas fa-train' => __( 'train', 'bus-booking-manager' ),
                'fas fa-transgender' => __( 'transgender', 'bus-booking-manager' ),
                'fas fa-transgender-alt' => __( 'transgender-alt', 'bus-booking-manager' ),
                'fas fa-trash' => __( 'trash', 'bus-booking-manager' ),
                'fas fa-trash-alt' => __( 'trash-alt', 'bus-booking-manager' ),
                'far fa-trash-alt' => __( 'trash-alt', 'bus-booking-manager' ),
                'fas fa-tree' => __( 'tree', 'bus-booking-manager' ),
                'fab fa-trello' => __( 'trello', 'bus-booking-manager' ),
                'fab fa-tripadvisor' => __( 'tripadvisor', 'bus-booking-manager' ),
                'fas fa-trophy' => __( 'trophy', 'bus-booking-manager' ),
                'fas fa-truck' => __( 'truck', 'bus-booking-manager' ),
                'fas fa-truck-loading' => __( 'truck-loading', 'bus-booking-manager' ),
                'fas fa-truck-moving' => __( 'truck-moving', 'bus-booking-manager' ),
                'fas fa-tty' => __( 'tty', 'bus-booking-manager' ),
                'fab fa-tumblr' => __( 'tumblr', 'bus-booking-manager' ),
                'fab fa-tumblr-square' => __( 'tumblr-square', 'bus-booking-manager' ),
                'fas fa-tv' => __( 'tv', 'bus-booking-manager' ),
                'fab fa-twitch' => __( 'twitch', 'bus-booking-manager' ),
                'fab fa-twitter' => __( 'twitter', 'bus-booking-manager' ),
                'fab fa-twitter-square' => __( 'twitter-square', 'bus-booking-manager' ),
                'fab fa-typo3' => __( 'typo3', 'bus-booking-manager' ),
                'fab fa-uber' => __( 'uber', 'bus-booking-manager' ),
                'fab fa-uikit' => __( 'uikit', 'bus-booking-manager' ),
                'fas fa-umbrella' => __( 'umbrella', 'bus-booking-manager' ),
                'fas fa-underline' => __( 'underline', 'bus-booking-manager' ),
                'fas fa-undo' => __( 'undo', 'bus-booking-manager' ),
                'fas fa-undo-alt' => __( 'undo-alt', 'bus-booking-manager' ),
                'fab fa-uniregistry' => __( 'uniregistry', 'bus-booking-manager' ),
                'fas fa-universal-access' => __( 'universal-access', 'bus-booking-manager' ),
                'fas fa-university' => __( 'university', 'bus-booking-manager' ),
                'fas fa-unlink' => __( 'unlink', 'bus-booking-manager' ),
                'fas fa-unlock' => __( 'unlock', 'bus-booking-manager' ),
                'fas fa-unlock-alt' => __( 'unlock-alt', 'bus-booking-manager' ),
                'fab fa-untappd' => __( 'untappd', 'bus-booking-manager' ),
                'fas fa-upload' => __( 'upload', 'bus-booking-manager' ),
                'fab fa-usb' => __( 'usb', 'bus-booking-manager' ),
                'fas fa-user' => __( 'user', 'bus-booking-manager' ),
                'far fa-user' => __( 'user', 'bus-booking-manager' ),
                'fas fa-user-circle' => __( 'user-circle', 'bus-booking-manager' ),
                'far fa-user-circle' => __( 'user-circle', 'bus-booking-manager' ),
                'fas fa-user-md' => __( 'user-md', 'bus-booking-manager' ),
                'fas fa-user-plus' => __( 'user-plus', 'bus-booking-manager' ),
                'fas fa-user-secret' => __( 'user-secret', 'bus-booking-manager' ),
                'fas fa-user-times' => __( 'user-times', 'bus-booking-manager' ),
                'fas fa-users' => __( 'users', 'bus-booking-manager' ),
                'fab fa-ussunnah' => __( 'ussunnah', 'bus-booking-manager' ),
                'fas fa-utensil-spoon' => __( 'utensil-spoon', 'bus-booking-manager' ),
                'fas fa-utensils' => __( 'utensils', 'bus-booking-manager' ),
                'fab fa-vaadin' => __( 'vaadin', 'bus-booking-manager' ),
                'fas fa-venus' => __( 'venus', 'bus-booking-manager' ),
                'fas fa-venus-double' => __( 'venus-double', 'bus-booking-manager' ),
                'fas fa-venus-mars' => __( 'venus-mars', 'bus-booking-manager' ),
                'fab fa-viacoin' => __( 'viacoin', 'bus-booking-manager' ),
                'fab fa-viadeo' => __( 'viadeo', 'bus-booking-manager' ),
                'fab fa-viadeo-square' => __( 'viadeo-square', 'bus-booking-manager' ),
                'fas fa-vial' => __( 'vial', 'bus-booking-manager' ),
                'fas fa-vials' => __( 'vials', 'bus-booking-manager' ),
                'fab fa-viber' => __( 'viber', 'bus-booking-manager' ),
                'fas fa-video' => __( 'video', 'bus-booking-manager' ),
                'fas fa-video-slash' => __( 'video-slash', 'bus-booking-manager' ),
                'fab fa-vimeo' => __( 'vimeo', 'bus-booking-manager' ),
                'fab fa-vimeo-square' => __( 'vimeo-square', 'bus-booking-manager' ),
                'fab fa-vimeo-v' => __( 'vimeo-v', 'bus-booking-manager' ),
                'fab fa-vine' => __( 'vine', 'bus-booking-manager' ),
                'fab fa-vk' => __( 'vk', 'bus-booking-manager' ),
                'fab fa-vnv' => __( 'vnv', 'bus-booking-manager' ),
                'fas fa-volleyball-ball' => __( 'volleyball-ball', 'bus-booking-manager' ),
                'fas fa-volume-down' => __( 'volume-down', 'bus-booking-manager' ),
                'fas fa-volume-off' => __( 'volume-off', 'bus-booking-manager' ),
                'fas fa-volume-up' => __( 'volume-up', 'bus-booking-manager' ),
                'fab fa-vuejs' => __( 'vuejs', 'bus-booking-manager' ),
                'fas fa-warehouse' => __( 'warehouse', 'bus-booking-manager' ),
                'fab fa-weibo' => __( 'weibo', 'bus-booking-manager' ),
                'fas fa-weight' => __( 'weight', 'bus-booking-manager' ),
                'fab fa-weixin' => __( 'weixin', 'bus-booking-manager' ),
                'fab fa-whatsapp' => __( 'whatsapp', 'bus-booking-manager' ),
                'fab fa-whatsapp-square' => __( 'whatsapp-square', 'bus-booking-manager' ),
                'fas fa-wheelchair' => __( 'wheelchair', 'bus-booking-manager' ),
                'fab fa-whmcs' => __( 'whmcs', 'bus-booking-manager' ),
                'fas fa-wifi' => __( 'wifi', 'bus-booking-manager' ),
                'fab fa-wikipedia-w' => __( 'wikipedia-w', 'bus-booking-manager' ),
                'fas fa-window-close' => __( 'window-close', 'bus-booking-manager' ),
                'far fa-window-close' => __( 'window-close', 'bus-booking-manager' ),
                'fas fa-window-maximize' => __( 'window-maximize', 'bus-booking-manager' ),
                'far fa-window-maximize' => __( 'window-maximize', 'bus-booking-manager' ),
                'fas fa-window-minimize' => __( 'window-minimize', 'bus-booking-manager' ),
                'far fa-window-minimize' => __( 'window-minimize', 'bus-booking-manager' ),
                'fas fa-window-restore' => __( 'window-restore', 'bus-booking-manager' ),
                'far fa-window-restore' => __( 'window-restore', 'bus-booking-manager' ),
                'fab fa-windows' => __( 'windows', 'bus-booking-manager' ),
                'fas fa-wine-glass' => __( 'wine-glass', 'bus-booking-manager' ),
                'fas fa-won-sign' => __( 'won-sign', 'bus-booking-manager' ),
                'fab fa-wordpress' => __( 'wordpress', 'bus-booking-manager' ),
                'fab fa-wordpress-simple' => __( 'wordpress-simple', 'bus-booking-manager' ),
                'fab fa-wpbeginner' => __( 'wpbeginner', 'bus-booking-manager' ),
                'fab fa-wpexplorer' => __( 'wpexplorer', 'bus-booking-manager' ),
                'fab fa-wpforms' => __( 'wpforms', 'bus-booking-manager' ),
                'fas fa-wrench' => __( 'wrench', 'bus-booking-manager' ),
                'fas fa-x-ray' => __( 'x-ray', 'bus-booking-manager' ),
                'fab fa-xbox' => __( 'xbox', 'bus-booking-manager' ),
                'fab fa-xing' => __( 'xing', 'bus-booking-manager' ),
                'fab fa-xing-square' => __( 'xing-square', 'bus-booking-manager' ),
                'fab fa-y-combinator' => __( 'y-combinator', 'bus-booking-manager' ),
                'fab fa-yahoo' => __( 'yahoo', 'bus-booking-manager' ),
                'fab fa-yandex' => __( 'yandex', 'bus-booking-manager' ),
                'fab fa-yandex-international' => __( 'yandex-international', 'bus-booking-manager' ),
                'fab fa-yelp' => __( 'yelp', 'bus-booking-manager' ),
                'fas fa-yen-sign' => __( 'yen-sign', 'bus-booking-manager' ),
                'fab fa-yoast' => __( 'yoast', 'bus-booking-manager' ),
                'fab fa-youtube' => __( 'youtube', 'bus-booking-manager' ),
                'fab fa-youtube-square' => __( 'youtube-square', 'bus-booking-manager' ),
            );


            return apply_filters( 'FONTAWESOME_ARRAY', $fonts_arr );
        }



    }
}