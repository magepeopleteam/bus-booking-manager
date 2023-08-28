<?php
/**
 *  Required Plugins Notification
 *  Dev: Ariful
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

if (!class_exists('WBBM_Required_Plugins')) {

class WBBM_Required_Plugins
{
	public function __construct() {
		add_action('admin_notices',array($this,'wbbm_admin_notices'));
        add_action( 'admin_menu', array( $this, 'wbbm_plugins_admin_menu' ) );
		add_action( 'admin_init', array( $this, 'wbbm_plugin_activate' ) );
	}

	public function wbbm_plugin_page_location(){

		$location = 'plugins.php';

		return $location;	
	}

	public function wbbm_plugins_admin_menu() {
			add_submenu_page(
				$this->wbbm_plugin_page_location(),
				__( 'Install WBBM Plugins', 'bus-booking-manager' ),
				__( 'Install WBBM Plugins', 'bus-booking-manager' ),
				'manage_options',
				'wbbm-plugins',
				array($this,'wbbm_plugin_page')
			);
    }

	public function wbbm_chk_plugin_folder_exist($slug){
		$plugin_dir = ABSPATH . 'wp-content/plugins/'.$slug;
		if(is_dir($plugin_dir)){
			return true;
		}
		else{
			return false;
		}		
	}

	public function wbbm_plugin_activate(){
		if(isset($_GET['wbbm_plugin_activate']) && !is_plugin_active( $_GET['wbbm_plugin_activate'] )){
			$slug = $_GET['wbbm_plugin_activate'];
			$activate = activate_plugin( $slug );
			$url = admin_url( $this->wbbm_plugin_page_location().'?page=wbbm-plugins' );
			echo '<script>
			var url = "'.$url.'";
			window.location.replace(url);
			</script>';
		}
		else{
			return false;
		}
	}

	public function wbbm_mpdf_plugin_install(){

		if(!current_user_can('administrator')) {
			exit;
		}

		if(isset($_GET['wbbm_plugin_install']) && $this->wbbm_chk_plugin_folder_exist($_GET['wbbm_plugin_install']) == false){
			$slug = $_GET['wbbm_plugin_install'];
			if($slug != 'magepeople-pdf-support-master'){
				$action = 'install-plugin';
				$url = wp_nonce_url(
					add_query_arg(
						array(
							'action' => $action,
							'plugin' => $slug
						),
						admin_url( 'update.php' )
					),
					$action.'_'.$slug
				);
				if(isset($url)){
					echo '<script>
						str = "'.$url.'";
						var url = str.replace(/&amp;/g, "&");
						window.location.replace(url);
						</script>';
				}


			}
			elseif($slug == 'magepeople-pdf-support-master'){

				include_once( ABSPATH . 'wp-admin/includes/plugin-install.php' );
				include_once( ABSPATH . 'wp-admin/includes/file.php' );
				include_once( ABSPATH . 'wp-admin/includes/misc.php' );
				include_once( ABSPATH . 'wp-admin/includes/class-wp-upgrader.php' );
				$upgrader = new Plugin_Upgrader( new Plugin_Installer_Skin() );
				$upgrader->install('https://github.com/magepeopleteam/magepeople-pdf-support/archive/master.zip');
			}
			else{
				return false;
			}
		}
		else{
			return false;
		}
	}	

	public function wbbm_wp_plugin_activation_url($slug){
		if($this->wbbm_plugin_page_location() == 'plugins.php'){
			$url = admin_url($this->wbbm_plugin_page_location()).'?page=wbbm-plugins&wbbm_plugin_activate='.$slug;
		}
		else{
			$url = admin_url($this->wbbm_plugin_page_location()).'&page=wbbm-plugins&wbbm_plugin_activate='.$slug;
		}

		return $url;
	}

	public function wbbm_plugin_page(){
		$pdfsetting = is_array(get_option( 'wbbm_pdf_setting_sec' )) ? maybe_unserialize(get_option( 'wbbm_pdf_setting_sec' )) : array();
		$pdflibrary = 'mpdf';
		$button_wc = '';
		$button_wbbm = '';
		$button_mpdf = '';

		/* WooCommerce */
		if($this->wbbm_chk_plugin_folder_exist('woocommerce') == false) {;
			$button_wc = '<a href="'.esc_url($this->wbbm_wp_plugin_installation_url('woocommerce')).'" class="wbbm_plugin_btn">'.esc_html__('Install','bus-booking-manager').'</a>';
		}
		elseif($this->wbbm_chk_plugin_folder_exist('woocommerce') == true && !is_plugin_active( 'woocommerce/woocommerce.php')){
			$button_wc = '<a href="'.esc_url($this->wbbm_wp_plugin_activation_url('woocommerce/woocommerce.php')).'" class="wbbm_plugin_btn">'.esc_html__('Activate','bus-booking-manager').'</a>';
		}
		else{
			$button_wc = '<span class="wbbm_plugin_status">'.esc_html__('Activated','bus-booking-manager').'</span>';
		}

		/* Multipurpose Ticket Booking Manager */
		if($this->wbbm_chk_plugin_folder_exist('bus-booking-manager') == false) {;
			$button_wbbm = '<a href="'.esc_url($this->wbbm_wp_plugin_installation_url('bus-booking-manager')).'" class="wbbm_plugin_btn">'.esc_html__('Install','bus-booking-manager').'</a>';
		}
		elseif($this->wbbm_chk_plugin_folder_exist('bus-booking-manager') == true && !is_plugin_active( 'bus-booking-manager/woocommerce-bus.php')){
			$button_wbbm = '<a href="'.esc_url($this->wbbm_wp_plugin_activation_url('bus-booking-manager/woocommerce-bus.php')).'" class="wbbm_plugin_btn">'.esc_html__('Activate','bus-booking-manager').'</a>';
		}
		else{
			$button_wbbm = '<span class="wbbm_plugin_status">'.esc_html__('Activated','bus-booking-manager').'</span>';
		}
		
		/* MagePeople PDF Support */
		if(is_plugin_active('bus-booking-manager-pro/wbtm-pro.php') && $pdflibrary == 'mpdf'){
			if($this->wbbm_chk_plugin_folder_exist('magepeople-pdf-support-master') == false) {;
				$button_mpdf = '<a href="'.esc_url($this->wbbm_wp_plugin_installation_url('magepeople-pdf-support-master')).'" class="wbbm_plugin_btn">'.esc_html__('Install','bus-booking-manager').'</a>';
			}
			elseif($this->wbbm_chk_plugin_folder_exist('magepeople-pdf-support-master') == true && !is_plugin_active( 'magepeople-pdf-support-master/mage-pdf.php')){
				$button_mpdf = '<a href="'.esc_url($this->wbbm_wp_plugin_activation_url('magepeople-pdf-support-master/mage-pdf.php')).'" class="wbbm_plugin_btn">'.esc_html__('Activate','bus-booking-manager').'</a>';
			}
			else{
				$button_mpdf = '<span class="wbbm_plugin_status">'.esc_html__('Activated','bus-booking-manager').'</span>';
			}
		}		
		?>
		<div class="wrap wbbm_plugin_page_wrap">
			<table>
				<thead>
					<tr>
						<th colspan="2"><?php esc_html_e('Multipurpose Ticket Booking Manager  Required Plugins','bus-booking-manager'); ?></th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td><?php esc_html_e('WooCommerce','bus-booking-manager'); ?></td>
						<td><?php echo $button_wc; ?></td>
					</tr>
					<tr>
						<td><?php esc_html_e('Multipurpose Ticket Booking Manager','bus-booking-manager'); ?></td>
						<td><?php echo $button_wbbm; ?></td>
					</tr>
					<?php if (is_plugin_active('bus-booking-manager-pro/wbtm-pro.php') && $pdflibrary == 'mpdf') {  ?>
					<tr>
						<td><?php esc_html_e('MagePeople PDF Support','bus-booking-manager'); ?></td>
						<td><?php echo $button_mpdf; ?></td>
					</tr>
					<?php } ?>										
				</tbody>
			</table>
		</div>
		<style>
		.wbbm_plugin_page_wrap{
			margin-left: 15px;
			margin-right: 15px;			
		}
		.wbbm_plugin_page_wrap table{
			width: 100%;
			border-collapse: collapse;
			border: 1px solid #d3d3d3;
		}
		.wbbm_plugin_page_wrap table tr{
			border-bottom: 1px solid #d3d3d3;
			background-color: #fff;
		}
		.wbbm_plugin_page_wrap table tr th{
			background: #162748;
			color: #fff;
		}
		.wbbm_plugin_page_wrap table tr th,
		.wbbm_plugin_page_wrap table tr td{
			padding: 15px;
			text-align: left;
		}
		.wbbm_plugin_page_wrap .wbbm_plugin_status{
			color: #1c931c;
		}
		.wbbm_plugin_page_wrap .wbbm_plugin_btn{
			background-color: #22D02D;
			color: #fff;
			text-decoration: none;
			padding: 8px;
			transition: 0.2s;
			border-radius: 5px;
		}
		.wbbm_plugin_page_wrap .wbbm_plugin_btn:hover{
			background-color: #0FA218;
			color: #fff;
			transition: 0.2s;
		}
		</style>
		<?php

		$this->wbbm_mpdf_plugin_install();
	}

	public function wbbm_wp_plugin_installation_url($slug){

		if($slug){

			$url = admin_url($this->wbbm_plugin_page_location()).'?page=wbbm-plugins&wbbm_plugin_install='.$slug;			
		}
		else{

			$url = '';
		}

		return $url;		
	}

	public function wbbm_required_plugin_list(){

		$pdfsetting = is_array(get_option( 'wbbm_pdf_setting_sec' )) ? maybe_unserialize(get_option( 'wbbm_pdf_setting_sec' )) : array();
		$pdflibrary = 'mpdf';
		
		$list = array();

		if( $this->wbbm_chk_plugin_folder_exist('woocommerce') == false ) {
			$list[] = __('WooCommerce','bus-booking-manager');
		}
		if( $this->wbbm_chk_plugin_folder_exist('bus-booking-manager')  == false) {
			$list[] = __('Multipurpose Ticket Booking Manager','bus-booking-manager');			
		}
		if (is_plugin_active('bus-booking-manager-pro/wbtm-pro.php') && $pdflibrary == 'mpdf') {				
			if( $this->wbbm_chk_plugin_folder_exist('magepeople-pdf-support-master')  == false) {
				$list[] = __('MagePeople PDF Support','bus-booking-manager');			
			}
		}
		return $list;		
	}

	public function wbbm_inactive_plugin_list(){

		$pdfsetting = is_array(get_option( 'wbbm_pdf_setting_sec' )) ? maybe_unserialize(get_option( 'wbbm_pdf_setting_sec' )) : array();
		$pdflibrary = 'mpdf';
		
		$list = array();

		if($this->wbbm_chk_plugin_folder_exist('woocommerce') == true && !is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
			$list[] = __('WooCommerce','bus-booking-manager');
		}
		if($this->wbbm_chk_plugin_folder_exist('bus-booking-manager') == true && !is_plugin_active( 'bus-booking-manager/woocommerce-bus.php' ) ) {
			$list[] = __('Multipurpose Ticket Booking Manager','bus-booking-manager');			
		}
		if (is_plugin_active('bus-booking-manager-pro/wbtm-pro.php') && $pdflibrary == 'mpdf') {				
			if($this->wbbm_chk_plugin_folder_exist('magepeople-pdf-support-master') == true && !is_plugin_active( 'magepeople-pdf-support-master/mage-pdf.php' ) ) {
				$list[] = __('MagePeople PDF Support','bus-booking-manager');			
			}
		}
		return $list;		
	}	

	public function wbbm_admin_notices(){

		$pdfsetting = is_array(get_option( 'wbbm_pdf_setting_sec' )) ? maybe_unserialize(get_option( 'wbbm_pdf_setting_sec' )) : array();
		$pdflibrary = isset($pdfsetting['wbtm_pdf_lib']) ? $pdfsetting['wbtm_pdf_lib'] : 'mpdf';

		$url = admin_url($this->wbbm_plugin_page_location()).'?page=wbbm-plugins';	
		
		$required_plugins = $this->wbbm_required_plugin_list();
		$inactive_plugins = $this->wbbm_inactive_plugin_list();
		$total_r_plugins = count($required_plugins);
		$total_i_plugins = count($inactive_plugins);

		if($total_r_plugins > 0 || $total_i_plugins > 0){
		?>
		<div class="notice notice-success is-dismissible">
			<?php
			echo '<p>';
			echo '<strong>';

			if($total_r_plugins > 0){
				$i = 1;
				if($total_r_plugins == 1){
					echo __('Multipurpose Ticket Booking Manager  required the following plugin: ','bus-booking-manager');
				}
				else{
					echo __('Multipurpose Ticket Booking Manager  required the following plugins: ','bus-booking-manager');
				}

				echo '<i>';
				
				foreach ($required_plugins as $plugin) {
					if($i < $total_r_plugins){
						echo $plugin.', ';
					}
					else{
						echo $plugin.'.';
					}
	
					$i++;
				}
				echo '</i>';
				echo '<br/>';
			}

			if($total_i_plugins > 0){
				$i = 1;
				if($total_i_plugins == 1){
					echo __('The following required plugin is currently inactive: ','bus-booking-manager');
				}
				else{
					echo __('The following required plugins are currently inactive: ','bus-booking-manager');
				}				

				echo '<i>';

				foreach ($inactive_plugins as $plugin) {
					if($i < $total_i_plugins){
						echo $plugin.', ';
					}
					else{
						echo $plugin.'.';
					}

					$i++;
				}
				echo '</i>';
				echo '<br/>';
			}

			if($total_r_plugins > 0){
				echo '<a href="'.esc_url($url).'">';
				echo __('Begin installing plugins','bus-booking-manager');
				echo '</a>';
			}

			if($total_r_plugins > 0 && $total_i_plugins > 0){
				echo ' | ';
			}
			
			if($total_i_plugins > 0){
				echo '<a href="'.esc_url($url).'">';
				echo __('Activate installed plugin','bus-booking-manager');
				echo '</a>';
			}

			echo '</strong>';
			echo '</p>';
			?>
		</div>
		<?php
		}	
	}
}
}
new WBBM_Required_Plugins();
