<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class SP_Admin_Settings {
	
	public function __construct() {
		add_action( 'admin_init', array( $this ,'sp_register_settings') );
		add_action( 'admin_menu', array( $this ,'sp_settings_pages') );
	}
	
	public function sp_register_settings() {
		
		$sp_settings = array(
			'sp_api_keys' => array()
		
		);
		
		foreach ($sp_settings as $key => $value) :
			if (get_option($key)) :
				continue;
			else :
				update_option($key,esc_sql($value));
			endif;
		endforeach;
	}
	
	public function sp_settings_pages() {
		
		//require_once( __DIR__ . '/settings/sp_settings_page.php');
		
		//separator to set StripePress on its own in the admin menu
		$this->sp_add_separator(55);
		
		//top level page
		add_menu_page( SP_PLUGIN_NAME, SP_PLUGIN_NAME, 'manage_options', SP_PLUGIN_SLUG , array( $this , 'main_settings_page') , SP_SVG_ICON_B64, 56 );
		
		$sub_pages = array(
			'subscriptions'=>'Subscriptions',
			'orders'=>'Orders'
		);
		
		foreach ($sub_pages as $slug => $name) :
		
			$new_slug = SP_PLUGIN_SLUG . '-' . $slug;
		
			add_submenu_page( SP_PLUGIN_SLUG , $name , $name , 'manage_options' , $new_slug , array( $this , "{$slug}_page") ); // parent slug, page title, menu title, role/capability, menu slug, callback function
		
		endforeach;
	}
	
	private function sp_add_separator($position) {
		global $menu;
		
		$menu[ $position ] = array(
			0	=>	'',
			1	=>	'read',
			2	=>	'separator' . $position,
			3	=>	'',
			4	=>	'wp-menu-separator',
			5	=>	'sp-menu-separator',
			6	=>	SP_PLUGIN_SLUG
		);
		return $menu;
		
	}
	
	public function main_settings_page() {
		
		if (function_exists('sp_get_template')) :
		
			echo sp_get_template('settings_page','admin');
		
		endif;
		
	}
	
	public function subscriptions_page() {
		
		if (function_exists('sp_get_template')) :
		
			echo sp_get_template('subscriptions_page','admin');
		
		endif;
		
	}
	
}
new SP_Admin_Settings();