<?php

/**
 * 2AM Settings API wrapper class
 *
 * @version 1.0
 *
 */

if (!defined('ABSPATH')) {
    die;
}

if (!class_exists('MAGE_Setting_API')):
    class MAGE_Setting_API
    {

        /**
         * settings sections array
         *
         * @var array
         */
        private $settings_sections = array();

        /**a
         * Settings fields array
         *
         * @var array
         */
        private $settings_fields = array();

        /**
         * Singleton instance
         *
         * @var object
         */
        private static $_instance;

        public function __construct()
        {
            add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts'));
        }

        /**
         * Enqueue scripts and styles
         */
        function admin_enqueue_scripts()
        {
            wp_enqueue_style('wp-color-picker');
            wp_enqueue_style('thickbox');

            wp_enqueue_script('wp-color-picker');
            wp_enqueue_script('jquery');
            wp_enqueue_script('media-upload');
            wp_enqueue_script('thickbox');
        }

        /**
         * Set settings sections
         *
         * @param array   $sections setting sections array
         */
        function set_sections($sections)
        {
            $this->settings_sections = $sections;

            return $this;
        }

        /**
         * Add a single section
         *
         * @param array   $section
         */
        function add_section($section)
        {
            $this->settings_sections[] = $section;

            return $this;
        }

        /**
         * Set settings fields
         *
         * @param array   $fields settings fields array
         */
        function set_fields($fields)
        {
            $this->settings_fields = $fields;

            return $this;
        }

        function add_field($section, $field)
        {
            $defaults = array(
                'name' => '',
                'label' => '',
                'desc' => '',
                'type' => 'text'
            );

            $arg = wp_parse_args($field, $defaults);
            $this->settings_fields[$section][] = $arg;

            return $this;
        }

        /**
         * Initialize and registers the settings sections and fileds to WordPress
         *
         * Usually this should be called at `admin_init` hook.
         *
         * This function gets the initiated settings sections and fields. Then
         * registers them to WordPress and ready for use.
         */
        function admin_init()
        {
            //register settings sections
            foreach ($this->settings_sections as $section) {
                if (false == get_option($section['id'])) {
                    add_option($section['id']);
                }

                if (isset($section['desc']) && !empty($section['desc'])) {
                    $section['desc'] = '<div class="inside">' . $section['desc'] . '</div>';
                    // $callback = create_function('', 'echo "'.str_replace('"', '\"', $section['desc']).'";');
                    $callback = '__return_false';
                } else {
                    $callback = '__return_false';
                }

                add_settings_section($section['id'], $section['title'], $callback, $section['id']);
            }

            //register settings fields
            foreach ($this->settings_fields as $section => $field) {
                foreach ($field as $option) {

                    $type = isset($option['type']) ? $option['type'] : 'text';
                    $label = isset($option['label']) ? $option['label'] : '';
                    $desc = isset($option['desc']) ? $option['desc'] : '';
                    $args = array(
                        'id' => $option['name'],
                        'class' => isset($option['class']) ? $option['class'] : '',
                        'args' => isset($option['args']) ? $option['args'] : '',
                        'desc' => isset($option['desc']) ? $option['desc'] : '',
                        'name' => $option['label'],
                        'section' => $section,
                        'size' => isset($option['size']) ? $option['size'] : null,
                        'options' => isset($option['options']) ? $option['options'] : '',
                        'std' => isset($option['default']) ? $option['default'] : '',
                        'sanitize_callback' => isset($option['sanitize_callback']) ? $option['sanitize_callback'] : '',
                        'placeholder' => isset($option['placeholder']) ? $option['placeholder'] : '',
                    );
                    $label .= sprintf('<span class="description"> %s</span>', $desc);
                    add_settings_field($section . '[' . $option['name'] . ']', $label, array($this, 'callback_' . $type), $section, $section, $args);
                }
            }

            // creates our settings in the options table
            foreach ($this->settings_sections as $section) {
                register_setting($section['id'], $section['id'], array($this, 'sanitize_options'));
            }
        }

        /**
         * Displays a text field for a settings field
         *
         * @param array   $args settings field args
         */
        function callback_text($args): void
        {
            $value = esc_attr(text: $this->get_option($args['id'], $args['section'], $args['std']));
            $custom_class = esc_attr(text: $args['class']);
            $size = isset($args['size']) && ! is_null(value: $args['size']) ? esc_attr($args['size']) : 'regular';
            $placeholder = isset($args['placeholder']) && ! is_null(value: $args['placeholder']) ? esc_attr(text: $args['placeholder']) : '';
?>
            <input type="text" class="<?php echo esc_attr(text: $size); ?>-text <?php echo esc_attr(text: $custom_class); ?>" id="<?php echo esc_attr(text: $args['section'] . '[' . $args['id'] . ']'); ?>" name="<?php echo esc_attr(text: $args['section'] . '[' . $args['id'] . ']'); ?>" value="<?php echo esc_attr(text: $value); ?>" placeholder="<?php echo esc_attr(text: $placeholder); ?>" />
        <?php
        }

        public function callback_checkbox_multi($option)
        {

            $id                = isset($option['id']) ? $option['id'] : "";
            $field_name     = isset($option['field_name']) ? $option['field_name'] : $id;
            $default         = isset($option['default']) ? $option['default'] : array();
            $args            = isset($option['args']) ? $option['args'] : array();
            $args            = is_array($args) ? $args : $this->args_from_string($args);
            $value            = isset($option['value']) ? $option['value'] : array();
            $value          = !empty($value) ?  $value : $default;
            $value =  $this->get_option($option['id'], $option['section'], $option['std']);
            $field_id       = $id;
            $field_name     = !empty($field_name) ? '[' . $field_name . ']' : '[' . $id . ']';
        ?>
            <div class="hhh">
                <?php
                foreach ($args as $key => $argName):
                    $checked = is_array($value) && in_array($key, $value) ? "checked" : "";
                ?>
                    <label for="<?php echo esc_attr($field_id . '-' . $key); ?>"> <input class="<?php echo esc_attr($field_id); ?>" name="<?php echo esc_attr($option['section'] . $field_name . '[]'); ?>" type="checkbox" id="<?php echo esc_attr($field_id . '-' . $key); ?>" value="<?php echo esc_attr($key); ?>" <?php echo esc_attr($checked); ?>> <?php echo esc_html($argName); ?> </label><br>
                <?php
                endforeach;
                ?>
                <div class="error-mgs"></div>
            </div>
        <?php
        }


        public function args_from_string($string)
        {

            if (strpos($string, 'PAGES_IDS_ARRAY')    !== false) return $this->get_pages_array();
            if (strpos($string, 'POSTS_IDS_ARRAY')    !== false) return $this->get_posts_array();
            if (strpos($string, 'POST_TYPES_ARRAY')   !== false) return $this->get_post_types_array();
            if (strpos($string, 'TAX_')               !== false) return $this->get_taxonomies_array($string);
            if (strpos($string, 'USER_ROLES')         !== false) return $this->get_user_roles_array();
            if (strpos($string, 'USER_IDS_ARRAY')     !== false) return $this->get_user_ids_array();
            if (strpos($string, 'MENUS')              !== false) return $this->get_menus_array();
            if (strpos($string, 'SIDEBARS_ARRAY')     !== false) return $this->get_sidebars_array();
            if (strpos($string, 'THUMB_SIEZS_ARRAY')  !== false) return $this->get_thumb_sizes_array();
            if (strpos($string, 'FONTAWESOME_ARRAY')  !== false) return $this->get_font_aws_array();

            return array();
        }

        /**
         * Displays a checkbox for a settings field
         *
         * @param array   $args settings field args
         */
        function callback_checkbox($args)
        {
            // Retrieve and escape the option value
            $value = esc_attr($this->get_option($args['id'], $args['section'], $args['std']));
            $section = isset($args['section']) ? esc_attr($args['section']) : '';
            $id      = isset($args['id']) ? esc_attr($args['id']) : '';
            $desc    = isset($args['desc']) ? esc_html($args['desc']) : '';
            $custom_class = isset($args['class']) ? esc_attr($args['class']) : '';
            $checked = checked($value, 'on', false);
            $html = sprintf('<input type="hidden" name="%1$s[%2$s]" value="off" />', $section, $id);
            $html .= sprintf('<input type="checkbox" class="checkbox %3$s" id="wpuf-%1$s[%2$s]" name="%1$s[%2$s]" value="on"%4$s />', $section, $id, $custom_class, $checked);
            $html .= sprintf('<label for="wpuf-%1$s[%2$s]"> %3$s</label><br>', $section, $id, $desc);
            $allowed_html = array('input' => array('type'  => array(), 'name'  => array(), 'id'    => array(), 'value' => array(), 'class' => array(), 'checked' => array(),), 'label' => array('for' => array(),), 'br' => array(),);
            echo wp_kses($html, $allowed_html);
        }


        /**
         * Displays a multicheckbox a settings field
         *
         * @param array   $args settings field args
         */
        function callback_multicheck($args)
        {
            $value = $this->get_option($args['id'], $args['section'], $args['std']);
            $allowed_html = array('input' => array('type'    => array(), 'class'   => array(), 'id'      => array(), 'name'    => array(), 'value'   => array(), 'checked' => array(),), 'label' => array('for' => array(),), 'br' => array(),);
            $html = '';
            foreach ($args['options'] as $key => $label) {
                $checked = isset($value[$key]) ? $value[$key] : '0';
                $input_id = sprintf('wpuf-%s[%s][%s]', $args['section'], $args['id'], $key);
                $input_name = sprintf('%s[%s][%s]', $args['section'], $args['id'], $key);
                $html .= sprintf(
                    '<input type="checkbox" class="checkbox" id="%1$s" name="%2$s" value="%3$s" %4$s />',
                    esc_attr($input_id),      // Escaped ID
                    esc_attr($input_name),    // Escaped name
                    esc_attr($key),           // Escaped value
                    checked($checked, $key, false) // 'checked' attribute handled by WordPress
                );
                $html .= sprintf(
                    '<label for="%1$s"> %2$s</label><br>',
                    esc_attr($input_id),      // Escaped 'for' attribute
                    esc_html($label)          // Escaped label text
                );
            }
            if (! empty($args['desc'])) {
                $html .= sprintf(
                    '<span class="description">%s</span>',
                    esc_html($args['desc'])
                );
            }
            echo wp_kses($html, $allowed_html);
        }

        /**
         * Displays a multicheckbox a settings field
         *
         * @param array   $args settings field args
         */
        function callback_radio($args)
        {
            // Retrieve the option value without escaping yet
            $value = $this->get_option($args['id'], $args['section'], $args['std']);
            $allowed_html = array('input' => array('type'    => array(), 'class'   => array(), 'id'      => array(), 'name'    => array(), 'value'   => array(), 'checked' => array(),), 'label' => array('for' => array(),), 'br' => array(),);
            $html = '';
            foreach ($args['options'] as $key => $label) {
                // Escape dynamic variables
                $section = isset($args['section']) ? esc_attr($args['section']) : '';
                $id      = isset($args['id']) ? esc_attr($args['id']) : '';
                $key_escaped   = esc_attr($key);
                $label_escaped = esc_html($label);
                $input_id = sprintf('wpuf-%s[%s][%s]', $section, $id, $key_escaped);
                $input_name = sprintf('%s[%s]', $section, $id);
                $html .= sprintf(
                    '<input type="radio" class="radio" id="%1$s" name="%2$s" value="%3$s" %4$s />',
                    $input_id,                        // Escaped ID
                    $input_name,                      // Escaped Name
                    $key_escaped,                     // Escaped Value
                    checked($value, $key, false)    // 'checked' attribute handled by WordPress
                );
                $html .= sprintf(
                    '<label for="%1$s"> %2$s</label><br>',
                    $input_id,                        // Escaped 'for' attribute
                    $label_escaped                    // Escaped Label Text
                );
            }
            if (! empty($args['desc'])) {
                $html .= sprintf(
                    '<span class="description">%s</span>',
                    esc_html($args['desc'])
                );
            }
            echo wp_kses($html, $allowed_html);
        }


        /**
         * Displays a selectbox for a settings field
         *
         * @param array   $args settings field args
         */
        function callback_select($args)
        {

            // Sanitize the value being retrieved
            $value = sanitize_text_field($this->get_option($args['id'], $args['section'], $args['std']));

            // Escape the size attribute
            $size = isset($args['size']) && !is_null($args['size']) ? esc_attr($args['size']) : 'regular';

            // Start the select input with escaped attributes
            printf(
                '<select class="%s" name="%s[%s]" id="%s[%s]">',
                esc_attr($size),
                esc_attr($args['section']),
                esc_attr($args['id']),
                esc_attr($args['section']),
                esc_attr($args['id'])
            );

            // Loop through options, escape both key and label, and ensure 'selected' is safe
            foreach ($args['options'] as $key => $label) {
                printf(
                    '<option value="%s" %s>%s</option>',
                    esc_attr($key),
                    selected($value, $key, false),
                    esc_html($label)
                );
            }

            echo '</select>'; // Close the select tag

            // Optionally, output the description with escaping
            // if ( isset( $args['desc'] ) && !empty( $args['desc'] ) ) {
            //     printf( '<span class="description">%s</span>', esc_html( $args['desc'] ) );
            // }
        }


        /**
         * Displays a textarea for a settings field
         *
         * @param array   $args settings field args
         */
        function callback_textarea($args)
        {

            // Sanitize and escape the value
            $value = esc_textarea($this->get_option($args['id'], $args['section'], $args['std']));

            // Escape the size attribute
            $size = isset($args['size']) && !is_null($args['size']) ? esc_attr($args['size']) : 'regular';

            // Output the textarea element with properly escaped attributes and content
            printf(
                '<textarea rows="5" cols="55" class="%s-text" id="%s[%s]" name="%s[%s]">%s</textarea>',
                esc_attr($size),
                esc_attr($args['section']),
                esc_attr($args['id']),
                esc_attr($args['section']),
                esc_attr($args['id']),
                esc_textarea($value) // Ensures that textarea content is safe
            );

            // Optionally, output the description with escaping
            // if ( isset( $args['desc'] ) && !empty( $args['desc'] ) ) {
            //     printf( '<br><span class="description">%s</span>', esc_html( $args['desc'] ) );
            // }
        }


        /**
         * Displays a textarea for a settings field
         *
         * @param array   $args settings field args
         */
        function callback_html($args)
        {
            // Sanitize and escape the description as plain text
            echo esc_html($args['desc']);
        }


        /**
         * Displays a rich text textarea for a settings field
         *
         * @param array   $args settings field args
         */
        function callback_wysiwyg($args)
        {

            // Sanitize and escape the value
            $value = sanitize_text_field($this->get_option($args['id'], $args['section'], $args['std']));

            // Sanitize and escape the size
            $size = isset($args['size']) && !is_null($args['size']) ? esc_attr($args['size']) : '500px';

            // Output the editor with sanitized width
            echo '<div style="width: ' . esc_attr($size) . ';">';

            // Output the WYSIWYG editor
            wp_editor($value, esc_attr($args['section'] . '-' . $args['id']), array('teeny' => true, 'textarea_name' => esc_attr($args['section'] . '[' . $args['id'] . ']'), 'textarea_rows' => 10));

            echo '</div>';

            // Optionally, escape and output the description
            // if ( isset( $args['desc'] ) && !empty( $args['desc'] ) ) {
            //     printf( '<br><span class="description">%s</span>', esc_html( $args['desc'] ) );
            // }
        }


        /**
         * Displays a file upload field for a settings field
         *
         * @param array   $args settings field args
         */
        function callback_file($args)
        {

            // Sanitize and escape the value
            $value = esc_attr($this->get_option($args['id'], $args['section'], $args['std']));

            // Sanitize and escape the size
            $size = isset($args['size']) && !is_null($args['size']) ? esc_attr($args['size']) : 'regular';

            // Sanitize and escape the ID attributes
            $id = esc_attr($args['section'])  . '[' . esc_attr($args['id']) . ']';
            $js_id = esc_js($args['section']  . '\\\\[' . $args['id'] . '\\\\]');

            // Output the input field
            printf(
                '<input type="text" class="%1$s-text" id="%2$s[%3$s]" name="%2$s[%3$s]" value="%4$s"/>',
                esc_attr($size),
                esc_attr($args['section']),
                esc_attr($args['id']),
                esc_attr($value)
            );

            // Output the "Browse" button
            printf(
                '<input type="button" class="button wpsf-browse" id="%1$s_button" value="%2$s" />',
                esc_attr($id),
                esc_html__('Browse', 'bus-booking-manager') // Safely handle the button label
            );

            // Output the script for media uploader
            printf(
                '<script type="text/javascript">
    jQuery(document).ready(function($){
        $("#%1$s_button").click(function() {
            tb_show("", "media-upload.php?post_id=0&amp;type=image&amp;TB_iframe=true");
            window.original_send_to_editor = window.send_to_editor;
            window.send_to_editor = function(html) {
                var url = $(html).attr(\'href\');
                if (!url) {
                    url = $(html).attr(\'src\');
                }
                $("#%1$s").val(url);
                tb_remove();
                window.send_to_editor = window.original_send_to_editor;
            };
            return false;
        });
    });
    </script>',
                esc_js($js_id)  // Escaping the JS ID for use in the script
            );

            // Optionally, output the description if it exists
            // if ( isset( $args['desc'] ) && !empty( $args['desc'] ) ) {
            //     printf( '<span class="description">%s</span>', esc_html( $args['desc'] ) );
            // }
        }

        function callback_media($args)
        {

            $id         = isset($args['id']) ? esc_attr($args['id']) : "";  // Escape the ID
            $value      = esc_attr($this->get_option($args['id'], $args['section'], $args['std']));  // Escape the value
            $media_url  = esc_url(wp_get_attachment_url($value));  // Escape the media URL
            $media_type = esc_attr(get_post_mime_type($value));  // Escape the media type
            $media_title = esc_html(get_the_title($value));  // Escape the media title
            wp_enqueue_media();

            echo "<div class='media_preview'>";
            if ("audio/mpeg" === $media_type) {
                esc_html_e('Audio/Video format not supported.', 'bus-booking-manager');
            } else {
                printf('<img id="media_preview_%s" src="%s" alt="%s" />', esc_attr($id), esc_url($media_url), esc_attr($media_title));
            }
            echo "</div>";
            printf('<input type="hidden" id="media_input_%1$s" name="%2$s[%1$s]" value="%3$s" />', esc_attr($id), esc_attr($args['section']), esc_attr($value));
            printf('<div class="wbbm_green_btn" id="media_upload_%s" style="margin-right:5px">%s</div>', esc_attr($id), esc_html__('Upload', 'bus-booking-manager'));
            printf('<div class="wbbm_red_btn" id="media_remove_%s">%s</div>', esc_attr($id), esc_html__('Remove', 'bus-booking-manager'));
            printf(
                '<script>jQuery(document).ready(function($){
        $("#media_upload_%1$s").click(function() {
            var send_attachment_bkp = wp.media.editor.send.attachment;
            wp.media.editor.send.attachment = function(props, attachment) {
                $("#media_preview_%1$s").attr("src", attachment.url);
                $("#media_input_%1$s").val(attachment.id);
                wp.media.editor.send.attachment = send_attachment_bkp;
            };
            wp.media.editor.open($(this));
            return false;
        });
        $("#media_remove_%1$s").click(function() {
            $("#media_preview_%1$s").attr("src","");
            $("#media_input_%1$s").val("");
        });
    });</script>',
                esc_js($id)
            );
        }

        /**
         * Displays a password field for a settings field
         *
         * @param array   $args settings field args
         */
        function callback_password($args)
        {
            $value = esc_attr($this->get_option($args['id'], $args['section'], $args['std']));
            $size = isset($args['size']) && !is_null($args['size']) ? esc_attr($args['size']) : 'regular';
            printf(
                '<input type="password" class="%1$s-text" id="%2$s[%3$s]" name="%2$s[%3$s]" value="%4$s"/>',
                esc_attr($size),
                esc_attr($args['section']),
                esc_attr($args['id']),
                esc_attr($value)
            );

            // Optionally, escape and output the description if present
            // if ( isset( $args['desc'] ) && !empty( $args['desc'] ) ) {
            //     printf( '<span class="description">%s</span>', esc_html( $args['desc'] ) );
            // }
        }


        /**
         * Displays a color picker field for a settings field
         *
         * @param array   $args settings field args
         */
        function callback_color($args)
        {
            $value = esc_attr($this->get_option($args['id'], $args['section'], $args['std']));
            $size = isset($args['size']) && !is_null($args['size']) ? esc_attr($args['size']) : 'regular';
            $default_color = isset($args['std']) ? esc_attr($args['std']) : '';
            printf(
                '<input type="text" class="%1$s-text wp-color-picker-field" id="%2$s[%3$s]" name="%2$s[%3$s]" value="%4$s" data-default-color="%5$s" />',
                esc_attr($size),
                esc_attr($args['section']),
                esc_attr($args['id']),
                esc_attr($value),
                esc_attr($default_color)
            );
            // if ( isset( $args['desc'] ) && !empty( $args['desc'] ) ) {
            //     printf( '<span class="description" style="display:block;">%s</span>', esc_html( $args['desc'] ) );
            // }
        }


        /**
         * Sanitize callback for Settings API
         */
        function sanitize_options($options)
        {
            if (is_array($options)):
                foreach ($options as $option_slug => $option_value) {
                    $sanitize_callback = $this->get_sanitize_callback($option_slug);

                    // If callback is set, call it
                    if ($sanitize_callback) {
                        $options[$option_slug] = call_user_func($sanitize_callback, $option_value);
                        continue;
                    } else {
                        $options[$option_slug] = sanitize_text_field($option_value);
                    }
                }
            endif;
            return $options;
        }

        /**
         * Get sanitization callback for given option slug
         *
         * @param string $slug option slug
         *
         * @return mixed string or bool false
         */
        function get_sanitize_callback($slug = '')
        {
            if (empty($slug)) {
                return false;
            }

            // Iterate over registered fields and see if we can find proper callback
            foreach ($this->settings_fields as $section => $options) {
                foreach ($options as $option) {
                    if ($option['name'] != $slug) {
                        continue;
                    }

                    // Return the callback name
                    return isset($option['sanitize_callback']) && is_callable($option['sanitize_callback']) ? $option['sanitize_callback'] : false;
                }
            }

            return false;
        }

        /**
         * Get the value of a settings field
         *
         * @param string  $option  settings field name
         * @param string  $section the section name this field belongs to
         * @param string  $default default text if it's not found
         * @return string
         */
        function get_option($option, $section, $default = '')
        {

            $options = get_option($section);

            if (isset($options[$option])) {
                return $options[$option];
            }

            return $default;
        }

        /**
         * Show navigations as tab
         *
         * Shows all the settings section labels as tab
         */
        function show_navigation()
        {

            echo '<h2 class="nav-tab-wrapper">';
            foreach ($this->settings_sections as $tab) {
                printf(
                    '<a href="#%1$s" class="nav-tab" id="%1$s-tab">%2$s</a>',
                    esc_attr($tab['id']),
                    esc_html($tab['title'])
                );
            }
            echo '</h2>';
        }


        /**
         * Show the section settings forms
         *
         * This function displays every sections in a different form
         */
        function show_forms()
        {
        ?>
            <div class="metabox-holder">
                <div class="postbox">
                    <?php foreach ($this->settings_sections as $form) { ?>
                        <div id="<?php echo esc_attr($form['id']); ?>" class="group">
                            <form method="post" action="options.php">

                                <?php do_action('wsa_form_top_' . esc_attr($form['id']), $form); ?>
                                <?php settings_fields(esc_attr($form['id'])); ?>
                                <?php do_settings_sections(esc_attr($form['id'])); ?>
                                <?php do_action('wsa_form_bottom_' . esc_attr($form['id']), $form); ?>

                                <div style="padding-left: 10px">
                                    <?php submit_button(); ?>
                                </div>
                            </form>
                        </div>
                    <?php } ?>
                </div>
            </div>
        <?php
            $this->script();
        }


        /**
         * Tabbable JavaScript codes & Initiate Color Picker
         *
         * This code uses localstorage for displaying active tabs
         */
        function script()
        {
        ?>
            <script>
                jQuery(document).ready(function($) {
                    //Initiate Color Picker
                    $('.wp-color-picker-field').wpColorPicker();
                    // Switches option sections
                    $('.group').hide();
                    var activetab = '';
                    if (typeof(localStorage) != 'undefined') {
                        activetab = localStorage.getItem("activetab");
                    }
                    if (activetab != '' && $(activetab).length) {
                        $(activetab).fadeIn();
                    } else {
                        $('.group:first').fadeIn();
                    }
                    $('.group .collapsed').each(function() {
                        $(this).find('input:checked').parent().parent().parent().nextAll().each(
                            function() {
                                if ($(this).hasClass('last')) {
                                    $(this).removeClass('hidden');
                                    return false;
                                }
                                $(this).filter('.hidden').removeClass('hidden');
                            });
                    });

                    if (activetab != '' && $(activetab + '-tab').length) {
                        $(activetab + '-tab').addClass('nav-tab-active');
                    } else {
                        $('.nav-tab-wrapper a:first').addClass('nav-tab-active');
                    }
                    $('.nav-tab-wrapper a').click(function(evt) {
                        $('.nav-tab-wrapper a').removeClass('nav-tab-active');
                        $(this).addClass('nav-tab-active').blur();
                        var clicked_group = $(this).attr('href');
                        if (typeof(localStorage) != 'undefined') {
                            localStorage.setItem("activetab", $(this).attr('href'));
                        }
                        $('.group').hide();
                        $(clicked_group).fadeIn();
                        evt.preventDefault();
                    });
                });
            </script>

            <style type="text/css">
                /** WordPress 3.8 Fix **/
                .form-table th {
                    padding: 20px 10px;
                }

                #wpbody-content .metabox-holder {
                    padding-top: 5px;
                }

                .form-wrap p,
                p.description,
                p.help,
                span.description {
                    font-size: 13px;
                    display: block;
                }

                .wp-picker-container input[type=text].wp-color-picker {
                    text-align: left !important;
                }
            </style>
<?php
        }
    }
endif;
