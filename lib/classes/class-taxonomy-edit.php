<?php
if ( ! defined('ABSPATH')) exit;  // if direct access 

if( ! class_exists( 'TaxonomyEdit' ) ) {

    class TaxonomyEdit {

        public $data = array();

        public function __construct( $args ){

            $this->data = &$args;

            add_action( $this->get_taxonomy().'_add_form_fields', array( $this, 'add_form_fields' ), 12 );
            add_action( $this->get_taxonomy().'_edit_form_fields', array( $this, 'edit_form_fields' ), 12 );

            add_action( 'edited_'.$this->get_taxonomy(), array( $this, 'save_update_taxonomy' ), 12 );
            add_action( 'create_'.$this->get_taxonomy(), array( $this, 'save_update_taxonomy' ), 12 );

        }

        public function save_update_taxonomy($term_id){
            foreach ($this->get_panels() as $optionIndex => $option) {
                $option_value = isset($_POST[$option['id']]) ? $_POST[$option['id']] : '';

                // Sanitize the input based on the type of the field
                if (is_array($option_value)) {
                    $option_value = array_map('sanitize_text_field', $option_value);
                    $option_value = serialize($option_value);
                } else {
                    $option_value = sanitize_text_field($option_value);
                }

                update_term_meta($term_id, $option['id'], $option_value);
            }
        }

        public function edit_form_fields($term) {
            $term_id = $term->term_id;

            foreach ($this->get_panels() as $optionIndex => $option) {
                ?>
                <tr class="form-field">
                    <th scope="row" valign="top"><label for="<?php echo esc_attr($option['id']); ?>"><?php echo esc_html($option['title']); ?></label></th>
                    <td>
                        <?php
                        $this->field_generator($option, $term_id);
                        ?>
                    </td>
                </tr>
                <?php
            }
        }

        public function add_form_fields($term) {
            $term_id = '';

            foreach ($this->get_panels() as $optionIndex => $option) {
                ?>
                <tr class="form-field">
                    <th scope="row" valign="top">
                        <label for="<?php echo esc_attr($option['id']); ?>"><?php echo esc_html($option['title']); ?></label>
                    </th>
                    <td>
                        <?php
                        $this->field_generator($option, $term_id);
                        ?>
                    </td>
                </tr>
                <?php
            }
        }

        public function field_generator( $option, $term_id ) {
            $id = isset( $option['id'] ) ? $option['id'] : "";
            $type = isset( $option['type'] ) ? $option['type'] : "";
            $details = isset( $option['details'] ) ? $option['details'] : "";

            if( empty( $id ) ) return;

            $FormFieldsGenerator = new FormFieldsGenerator();
            $option['field_name'] = $id;
            $option_value = get_term_meta($term_id, $id, true);
            $option['value'] = is_serialized($option_value) ? unserialize($option_value) : $option_value;

            $allowed = [
                'script' => [
                    'type'  => true,
                    'src'   => true,
                    'async' => true,
                    'defer' => true,
                ],
                'style' => [],
                'div'   => [
                    'class' => true,
                    'id'    => true,
                ],
            ];

            if ( isset( $option['type'] ) ) {
                switch ( $option['type'] ) {
                    case 'text':
                        echo wp_kses_post($FormFieldsGenerator->field_text( $option ));
                        break;
                    case 'text_multi':
                        echo wp_kses_post($FormFieldsGenerator->field_text_multi( $option ));
                        break;
                    case 'textarea':
                        echo wp_kses_post($FormFieldsGenerator->field_textarea( $option ));
                        break;
                    case 'checkbox':
                        echo wp_kses_post($FormFieldsGenerator->field_checkbox( $option ));
                        break;
                    case 'radio':
                        echo wp_kses_post($FormFieldsGenerator->field_radio( $option ));
                        break;
                    case 'select':
                        echo wp_kses_post($FormFieldsGenerator->field_select( $option ));
                        break;
                    case 'range':
                        echo wp_kses_post($FormFieldsGenerator->field_range( $option ));
                        break;
                    case 'range_input':
                        echo wp_kses_post($FormFieldsGenerator->field_range_input( $option ));
                        break;
                    case 'switch':
                        echo wp_kses_post($FormFieldsGenerator->field_switch( $option ));
                        break;
                    case 'switch_multi':
                        echo wp_kses_post($FormFieldsGenerator->field_switch_multi( $option ));
                        break;
                    case 'switch_img':
                        echo wp_kses_post($FormFieldsGenerator->field_switch_img( $option ));
                        break;
                    case 'time_format':
                        echo wp_kses_post($FormFieldsGenerator->field_time_format( $option ));
                        break;
                    case 'date_format':
                        echo wp_kses_post($FormFieldsGenerator->field_date_format( $option ));
                        break;
                    case 'datepicker':
                        echo wp_kses_post($FormFieldsGenerator->field_datepicker( $option ));
                        break;
                    case 'colorpicker':
                        echo wp_kses_post($FormFieldsGenerator->field_colorpicker( $option ));
                        break;
                    case 'colorpicker_multi':
                        echo wp_kses_post($FormFieldsGenerator->field_colorpicker_multi( $option ));
                        break;
                    case 'link_color':
                        echo wp_kses_post($FormFieldsGenerator->field_link_color( $option ));
                        break;
                    case 'icon':
                        echo wp_kses_post($FormFieldsGenerator->field_icon( $option ));
                        break;
                    case 'icon_multi':
                        echo wp_kses_post($FormFieldsGenerator->field_icon_multi( $option ));
                        break;
                    case 'dimensions':
                        echo wp_kses_post($FormFieldsGenerator->field_dimensions( $option ));
                        break;
                    case 'wp_editor':
                        echo wp_kses_post($FormFieldsGenerator->field_wp_editor( $option ));
                        break;
                    case 'select2':
                        echo wp_kses_post($FormFieldsGenerator->field_select2( $option ));
                        break;
                    case 'faq':
                        echo wp_kses_post($FormFieldsGenerator->field_faq( $option ));
                        break;
                    case 'grid':
                        echo wp_kses_post($FormFieldsGenerator->field_grid( $option ));
                        break;
                    case 'color_palette':
                        echo wp_kses_post($FormFieldsGenerator->field_color_palette( $option ));
                        break;
                    case 'color_palette_multi':
                        echo wp_kses_post($FormFieldsGenerator->field_color_palette_multi( $option ));
                        break;
                    case 'media':
                        echo wp_kses_post($FormFieldsGenerator->field_media( $option ));
                        break;
                    case 'media_multi':
                        echo wp_kses_post($FormFieldsGenerator->field_media_multi( $option ));
                        break;
                    case 'repeatable':
                        echo wp_kses($FormFieldsGenerator->field_repeatable( $option ), $allowed);
                        break;
                    case 'user':
                        echo wp_kses_post($FormFieldsGenerator->field_user( $option ));
                        break;
                    default:
                        do_action( "wp_theme_settings_field_{$option['type']}", $option );
                        break;
                }

                // Escape output for details description
                if ( ! empty( $details ) ) {
                    echo "<p class='description'>" . esc_html( $details ) . "</p>";
                }
            }
        }

        private function get_taxonomy(){
            if( isset( $this->data['taxonomy'] ) ) return sanitize_key($this->data['taxonomy']);
            else return "category";
        }

        private function get_panels(){
            if( isset( $this->data['options'] ) ) return $this->data['options'];
            else return array();
        }

        private function get_tax_id(){
            // $post_id = get_the_ID();
            // return $post_id;
        }
    }
}
