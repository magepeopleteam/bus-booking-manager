<?php
if ( ! defined('ABSPATH')) exit;  // if direct access 

if( ! class_exists( 'AddMetaBox' ) ) {

    class AddMetaBox {

        public $data = array();

        public function __construct( $args ) {
            $this->data = &$args;

            add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ), 12 );
            add_action( 'save_post', array( $this, 'save_post' ), 12 );
        }

        public function add_meta_boxes() {
            add_meta_box(
                esc_attr($this->get_meta_box_id()),
                esc_html($this->get_meta_box_title()),
                array( $this, 'meta_box_callback' ),
                esc_attr($this->get_meta_box_screen()),
                esc_attr($this->get_meta_box_context()),
                esc_attr($this->get_meta_box_priority()),
                $this->get_callback_args()
            );
        }

        public function save_post($post_id) {
            // Security checks
            if ( ! isset($_POST['nonce_field']) || ! wp_verify_nonce($_POST['nonce_field'], 'nonce_action') ) {
                return; // Invalid nonce
            }

            if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
                return; // Prevents autosave
            }

            if (!current_user_can('edit_post', $post_id)) {
                return; // Permissions check
            }

            $get_option_name = $this->get_option_name();
            $post_id = $this->get_post_id(); // Get post ID

            if (!empty($get_option_name)) {
                $option_value = isset($_POST[$get_option_name]) ? $_POST[$get_option_name] : '';
                $option_value = is_array($option_value) ? array_map('sanitize_text_field', $option_value) : sanitize_text_field($option_value);
                update_post_meta($post_id, sanitize_key($get_option_name), serialize($option_value));
            } else {
                foreach ($this->get_panels() as $panelsIndex => $panel) {
                    foreach ($panel['sections'] as $sectionIndex => $section) {
                        foreach ($section['options'] as $option) {
                            $option_value = isset($_POST[$option['id']]) ? $_POST[$option['id']] : '';
                            $option_value = is_array($option_value) ? array_map('sanitize_text_field', $option_value) : sanitize_text_field($option_value);
                            update_post_meta($post_id, sanitize_key($option['id']), $option_value);
                        }
                    }
                }
            }
        }

        public function meta_box_callback() {
            $get_nav_position = $this->get_nav_position();
            ?>
            <div class='wrap ppof-settings ppof-metabox'>
                <div class='navigation <?php echo esc_attr($get_nav_position); ?>'>
                    <div class="nav-header">
                        <?php
                        do_action('nav_header_top');
                        ?>
                        <div class="themeName"><?php echo esc_html($this->get_item_name()); ?></div>
                        <div class="themeVersion"><?php echo esc_html(sprintf(__('Version: %s', 'wp-theme-settings'), $this->get_item_version())); ?></div>
                        <?php
                        do_action('nav_header_bottom');
                        ?>
                    </div>

                    <div class="nav-items">
                        <?php
                        do_action('nav_nav_items_top');
                        ?>
                        <?php
                        $current_page = 1;
                        foreach ($this->get_panels() as $page_id => $page) {
                            $page_settings = !empty($page['sections']) ? $page['sections'] : array();
                            $page_settings_count = count($page_settings);
                            ?>
                            <li class="nav-item-wrap <?php if (($page_settings_count > 1)) echo 'has-child'; ?> <?php if ($current_page == $page_id) echo 'active'; ?>">
                                <a dataid="<?php echo esc_attr($page_id); ?>" href='#<?php echo esc_attr($page_id); ?>' class='nav-item'><?php echo esc_html($page['page_nav']); ?>
                                    <?php if (($page_settings_count > 1)) echo '<i class="child-nav-icon fas fa-angle-down"></i>'; ?>
                                </a>
                                <?php if (($page_settings_count > 1)): ?>
                                    <ul class="child-navs">
                                        <?php
                                        foreach ($page_settings as $section_id => $nav_sections) {
                                            $nav_sections_title = !empty($nav_sections['nav_title']) ? esc_html($nav_sections['nav_title']) : esc_html($nav_sections['title']);
                                            ?>
                                            <li>
                                                <a sectionId="<?php echo esc_attr($section_id); ?>" dataid="<?php echo esc_attr($page_id); ?>" href='#<?php echo esc_attr($section_id); ?>' class='nav-item <?php if ($current_page == $page_id) echo 'active'; ?>'><?php echo $nav_sections_title; ?></a>
                                            </li>
                                            <?php
                                        }
                                        ?>
                                    </ul>
                                <?php endif; ?>
                            </li>
                            <?php
                            $current_page++;
                        }
                        ?>
                        <?php
                        do_action('nav_nav_items_bottom');
                        ?>
                    </div>

                    <div class="nav-footer">
                        <?php
                        do_action('nav_footer_top');
                        ?>
                        <?php
                        do_action('nav_footer_bottom');
                        ?>
                    </div>

                </div>

                <div class="form-wrapper <?php echo esc_attr($this->get_form_wrapper_position($get_nav_position)); ?>">

                    <div class="form-section">
                        <?php
                        $current_page = 1;
                        foreach ($this->get_panels() as $panelsIndex => $panel) {
                            ?>
                            <div class="tab-content <?php if ($current_page == 1) echo 'active'; ?> tab-content-<?php echo esc_attr($panelsIndex); ?>">
                                <?php
                                foreach ($panel['sections'] as $sectionIndex => $section) {
                                    ?>
                                    <div class="section">
                                        <h1 id="<?php echo esc_attr($sectionIndex); ?>" class="section-title"><?php echo esc_html($section['title']); ?></h1>
                                        <p class="description"><?php echo esc_html($section['description']); ?></p>

                                        <table class="form-table">
                                            <tbody>
                                            <?php
                                            foreach ($section['options'] as $option) {
                                                ?>
                                                <tr>
                                                    <th scope="row"><?php echo esc_html($option['title']); ?></th>
                                                    <td>
                                                        <?php
                                                        $option_value = get_post_meta($this->get_post_id(), $option['id'], true);
                                                        if (is_serialized($option_value)) {
                                                            $option_value = unserialize($option_value);
                                                        }
                                                        $option['value'] = $option_value;
                                                        $this->field_generator($option);
                                                        ?>
                                                    </td>
                                                </tr>
                                                <?php
                                            }
                                            ?>
                                            </tbody>
                                        </table>
                                    </div>
                                    <?php
                                }
                                ?>
                            </div>
                            <?php
                            $current_page++;
                        }
                        ?>
                    </div>
                </div>
            </div>
            <?php
        }

        public function field_generator($option) {
            $id = isset($option['id']) ? sanitize_key($option['id']) : "";
            $type = isset($option['type']) ? sanitize_key($option['type']) : "";
            $details = isset($option['details']) ? esc_html($option['details']) : "";

            $post_id = $this->get_post_id();

            if (empty($id)) return;

            $prent_option_name = $this->get_option_name();
            $FormFieldsGenerator = new FormFieldsGenerator();

            if (!empty($prent_option_name)) {
                $field_name = sanitize_key($prent_option_name . '[' . $id . ']');
                $option['field_name'] = $field_name;

                $prent_option_value = get_post_meta($post_id, $prent_option_name, true);
                $prent_option_value = is_serialized($prent_option_value) ? unserialize($prent_option_value) : array();
                $option['value'] = isset($prent_option_value[$id]) ? $prent_option_value[$id] : '';
            } else {
                $option['field_name'] = sanitize_key($id);
                $option_value = get_post_meta($post_id, $id, true);
                $option['value'] = is_serialized($option_value) ? unserialize($option_value) : $option_value;
            }

            // Generate the field based on type
            $this->generate_field($option, $FormFieldsGenerator);

            if (!empty($details)) echo "<p class='description'>" . esc_html($details) . "</p>";
        }

        private function generate_field($option, $FormFieldsGenerator) {
            $type = isset($option['type']) ? $option['type'] : '';

            switch ($type) {
                case 'text':
                    echo $FormFieldsGenerator->field_text($option);
                    break;
                case 'textarea':
                    echo $FormFieldsGenerator->field_textarea($option);
                    break;
                case 'checkbox':
                    echo $FormFieldsGenerator->field_checkbox($option);
                    break;
                case 'select':
                    echo $FormFieldsGenerator->field_select($option);
                    break;
                case 'radio':
                    echo $FormFieldsGenerator->field_radio($option);
                    break;
                // Add other cases as needed
            }
        }

        private function get_form_wrapper_position($get_nav_position) {
            switch ($get_nav_position) {
                case 'right':
                    return 'left';
                case 'left':
                    return 'right';
                case 'top':
                    return 'full-width-top';
                default:
                    return 'full-width';
            }
        }

        private function get_meta_box_id() {
            return isset($this->data['meta_box_id']) ? sanitize_key($this->data['meta_box_id']) : "";
        }

        private function get_meta_box_title() {
            return isset($this->data['meta_box_title']) ? esc_html($this->data['meta_box_title']) : "";
        }

        private function get_meta_box_screen() {
            return isset($this->data['screen']) ? $this->data['screen'] : array('post');
        }

        private function get_meta_box_context() {
            return isset($this->data['context']) ? sanitize_key($this->data['context']) : 'normal';
        }

        private function get_meta_box_priority() {
            return isset($this->data['priority']) ? sanitize_key($this->data['priority']) : "high";
        }

        private function get_callback_args() {
            return isset($this->data['callback_args']) ? $this->data['callback_args'] : array();
        }

        private function get_panels() {
            return isset($this->data['panels']) ? $this->data['panels'] : array();
        }

        private function get_item_name() {
            return isset($this->data['item_name']) ? esc_html($this->data['item_name']) : "PickPlugins";
        }

        private function get_item_version() {
            return isset($this->data['item_version']) ? esc_html($this->data['item_version']) : "1.0.0";
        }

        private function get_option_name() {
            return isset($this->data['option_name']) ? sanitize_key($this->data['option_name']) : false;
        }

        private function get_nav_position() {
            return isset($this->data['nav_position']) ? sanitize_key($this->data['nav_position']) : 'left';
        }

        private function get_post_id() {
            return get_the_ID();
        }
    }
}
