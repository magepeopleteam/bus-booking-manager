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


        $boarding =   isset($_GET['bus_start_route']) ? strip_tags($_GET['bus_start_route']) : '';
        $dropping = isset($_GET['bus_end_route']) ? strip_tags($_GET['bus_end_route']) : '';
        $journy_date = isset($_GET['j_date']) ? strip_tags($_GET['j_date']) : '';
        $return_date = isset($_GET['r_date']) ? strip_tags($_GET['r_date']) : '';

        //echo $boarding;die;


        $defaults = array("style" => 'false', "theme" => 'minimal',);
        $params         = shortcode_atts($defaults, $atts);
        global $mage_bus_search_theme;
        $mage_bus_search_theme = $params['theme'];
        //ob_clean();
        ob_start();
        if($params['style']=='vertical'){
            $this->mage_search_page_vertical($boarding,$dropping,$journy_date,$return_date);
        }
        else{
            $this->mage_search_page_horizontal($boarding,$dropping,$journy_date,$return_date);
        }
        return ob_get_clean();
    }






}

