<?php
if (!defined('ABSPATH')) exit;  // if direct access

class ShortCodeClass extends SearchClass
{
    public function __construct()
    {

    }


    function mage_bus_search_form($atts){
        $defaults = array("style" => false, "result-page" => '');
        $params         = shortcode_atts($defaults, $atts);
        $global_target = wbbm_get_option('wbbm_search_result_page', 'wbbm_general_setting_sec','bus-search');
        $target = $params['result-page'] ? $params['result-page'] : $global_target;
        ob_start();
        if($params['style']=='vertical'){
            $this->mage_search_form_vertical($target);
        }
        else{
            $this->mage_search_form_horizontal(false,$target);
        }
        return ob_get_clean();
    }


    function mage_bus_search($atts){
        $defaults = array("style" => 'false', "theme" => 'minimal',);
        $params         = shortcode_atts($defaults, $atts);
        global $mage_bus_search_theme;
        $mage_bus_search_theme = $params['theme'];
        //ob_clean();
        ob_start();
        if($params['style']=='vertical'){
            $this->mage_search_page_vertical();
        }
        else{
            $this->mage_search_page_horizontal();
        }
        return ob_get_clean();
    }






}

