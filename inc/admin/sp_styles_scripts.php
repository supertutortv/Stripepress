<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class SP_Styles_Scripts {
	
	
	public function __construct() {
		
		add_action( 'admin_enqueue_scripts' , array( $this , 'sp_admin_scripts' ) );
		
	}
	
	public function sp_admin_scripts() {
		
		if (strpos($_SERVER['REQUEST_URI'],SP_PLUGIN_SLUG)) :
		
			//styles
			wp_enqueue_style( SP_PLUGIN_SLUG , plugins_url( 'assets/css/stripepress.css' , dirname( __DIR__ ) ) , array() , null, 'all' ); // handle, src, dependencies, version, media
			wp_enqueue_style( 'materialize' , plugins_url( 'assets/css/materialize/materialize.min.css' , dirname( __DIR__ ) ) , array() , null, 'all' ); // handle, src, dependencies, version, media
			wp_enqueue_style( 'material-icons' , 'https://fonts.googleapis.com/icon?family=Material+Icons' , 'materialize' ,time());

			//scripts
			wp_enqueue_script( 'materialize' , plugins_url( 'assets/js/materialize/materialize.min.js' , dirname( __DIR__ ) ) , array() , null , true ); // handle, src, dependencies, version, in_footer
			wp_enqueue_script( SP_PLUGIN_SLUG , plugins_url( 'assets/js/stripepress.js' , dirname( __DIR__ ) ) , array('jquery','materialize') , null , true ); // handle, src, dependencies, version, in_footer
		
			if (!wp_script_is('jquery-ui-core')) :
				wp_enqueue_script('jquery-ui-core');
			endif;
		
			if (!wp_script_is('jquery-effects-core')) :
				wp_enqueue_script('jquery-effects-core');
			endif;
		
		endif;
	}
	
}

return new SP_Styles_Scripts();