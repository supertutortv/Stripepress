<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class SP_Ajax {
	
	
	public function __construct() {
		
		// no priv
		
		// yes priv
		add_action( 'sp_ajax_update_api_keys' , array( $this , 'update_api_keys' ) );
		add_action( 'sp_ajax_sp_test_mode' , array( $this , 'sp_test_mode' ) );
		
		// localize
		add_action( 'admin_enqueue_scripts' , array( $this , 'localize_ajax' ) );
		
	}
	
	public function localize_ajax() {
		
		wp_localize_script( SP_PLUGIN_SLUG , 'spAjax' ,  array('ajaxURL' => plugins_url( 'ajax/sp-ajax.php' , __FILE__ ) ));
		
	}
	// plugins_url( 'ajax/sp-ajax.php' , __FILE__ )
	// admin_url( 'admin-ajax.php' )
	
	public function update_api_keys() {
		
		$api_keys = get_option('stripepress_api_keys');
		
		$key = trim($_POST['key']);
		
		$value = base64_encode(str_replace($key,'',trim($_POST['value'])));
		
		$api_keys[esc_sql($key)] = esc_sql($value);
		
		echo wp_send_json( array('success'=> update_option( 'stripepress_api_keys' , $api_keys ) , 'message' => $api_keys));
		
	}
	
	public function sp_test_mode() {
		
		update_option( 'stripepress_testmode' , filter_var($_POST['value'],FILTER_VALIDATE_BOOLEAN) );
	
		echo wp_send_json( get_option( 'stripepress_testmode' ) );
	}
	
}
new SP_Ajax();