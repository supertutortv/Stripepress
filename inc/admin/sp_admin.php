<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class SP_Admin {
	
	
	public function __construct() {
		
		$this->admin_includes();
		$this->admin_hooks();
		
		do_action('stripepress_admin_loaded');
		
	}
	
	private function admin_includes() {
		
		require_once( SP_ADMIN_PATH . '/sp_admin_settings.php');
		require_once( SP_ADMIN_PATH . '/sp_styles_scripts.php');
		require_once( SP_ADMIN_PATH . '/sp_post_types.php');
		
	}
	
	private function admin_hooks() {
		
		return;
		
	}
	
}

return new SP_Admin();