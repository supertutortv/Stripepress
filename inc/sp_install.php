<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class SP_Install {
	
	
	public static function install() {
		
		global $wpdb;

		if ( ! is_blog_installed() ) {
			return;
		}
		
		do_action('stripepress_pre_install');
		
		self::sp_create_options();
		//self::sp_roles();
		
		do_action('stripepress_installed');
		
	}
	
	private static function sp_create_options() {
		
		$options = array(
			'testmode',
			'api_keys'
		
		);
		
		foreach ($options as $option) :
		
			if (get_option(SP_PLUGIN_PREFIX.$option)) :
				continue;
			else :
				add_option(SP_PLUGIN_PREFIX.$option);
			endif;
		
		endforeach;
		
	}
	
	private static function sp_roles() {
		
	}

}