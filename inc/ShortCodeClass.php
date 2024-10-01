<?php
if (!defined('ABSPATH')) exit;  // if direct access

class ShortCodeClass extends SearchClass
{
    public function __construct()
    {

    }

    function mage_bus_search_form($atts)
    {
        // Define default attributes
        $defaults = array("style" => false, "result-page" => '');
        // Extract parameters with sanitization
        $params = shortcode_atts($defaults, $atts);
        
        // Sanitize the result page target
        $global_target = wbbm_get_option('wbbm_search_result_page', 'wbbm_general_setting_sec', 'bus-search');
        $target = !empty($params['result-page']) ? sanitize_title($params['result-page']) : sanitize_title($global_target);

        ob_start();
        if ($params['style'] == 'vertical') {
            $this->mage_search_form_vertical($target);
        } else {
            $this->mage_search_form_horizontal(false, $target);
        }
        return ob_get_clean();
    }

    function mage_bus_search($atts)
    {
        // Define default attributes
        $defaults = array("style" => 'false', "theme" => 'minimal');
        // Extract parameters with sanitization
        $params = shortcode_atts($defaults, $atts);
        global $mage_bus_search_theme;

        // Sanitize the theme parameter
        $mage_bus_search_theme = sanitize_text_field($params['theme']);

        ob_start();
        if ($params['style'] == 'vertical') {
            $this->mage_search_page_vertical();
        } else {
            $this->mage_search_page_horizontal();
        }
        return ob_get_clean();
    }
}
