<?php
if (!defined('ABSPATH')) exit;  // if direct access

class WbbmPublicClass
{
    public function __construct()
    {

    }


    function wbbm_bus_enqueue_scripts() {

        wp_enqueue_style('wbbm-jquery-ui-style',plugin_dir_url( __DIR__ ).'css/jquery-ui.css',array());
        wp_enqueue_style('font-awesome-css-cdn', "https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.2.0/css/all.min.css", null, 1);
        wp_enqueue_style ('wbbm-select2-style-cdn',"https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/css/select2.min.css",null,1);
        wp_enqueue_style('lightslider',plugin_dir_url( __DIR__ ).'css/lightslider.css',array());
        wp_enqueue_style('wbbm-bus-style',plugin_dir_url( __DIR__ ).'css/style.css',array());
        wp_enqueue_style('wbbm-ra-bus-style',plugin_dir_url( __DIR__ ).'css/wbbm-custom-style.css',array());
        wp_enqueue_style('mage_css',plugin_dir_url( __DIR__ ).'css/mage_css.css',array(), time());

        wp_enqueue_style('wbbm custom pro', plugin_dir_url( __DIR__ ).'assets/css/wbbm_custom_pro.css', array(), time());

        wp_enqueue_script('jquery');
        wp_enqueue_script('jquery-ui-datepicker');
        wp_enqueue_script('jquery-ui-core');
        wp_enqueue_script('jquery-ui-accordion');
        wp_enqueue_script('wbbm-select2-lib',plugin_dir_url( __DIR__ ).'js/select2.full.min.js',array('jquery','jquery-ui-core'),1,false);
        wp_enqueue_script('lightslider js',plugin_dir_url( __DIR__ ).'js/lightslider.js',array(),1,false);
        wp_enqueue_script('ra_script_public',plugin_dir_url( __DIR__ ).'js/wbbm_custom_public_script.js',array(),time(),false);
        wp_enqueue_script('mage_style',plugin_dir_url( __DIR__ ).'js/mage_style.js',array('jquery'),time(),true);
        wp_enqueue_script('mp_script',plugin_dir_url( __DIR__ ).'js/mp_script.js',array('jquery'),time(),true);
        wp_enqueue_style('mp_style',plugin_dir_url( __DIR__ ).'css/mp_style.css',array());



    }








}

