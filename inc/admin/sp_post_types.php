<?php 

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class SP_Post_Types {
	
	public function __construct() {
		
		//add_action( 'init' , array( $this , 'sp_register_post_types' ) );
	}
	
	public function sp_register_post_types() {
		
		register_post_type( 'subscriptions' , array(
				'labels' => array(
								'name' => __( 'Subscriptions' ),
								'singular_name' => __( 'Subscription' )
							),
				'public' => true,
				'public_queryable' => false,
				'hierarchical' => false,
				'has_archive' => false,
				'exclude_from_search' => true,
				'show_in_nav_menus' => false,
				'show_ui' => true,
				'show_in_menu' => SP_PLUGIN_SLUG,
				'show_in_admin_bar' => false,
				'show_in_rest' => true,
				'capabilities' => array('manage_options'),
				'supports' => array('title','comments','custom-fields')
			) // end args
						  			  
		); // end register post type
		
	}
}
new SP_Post_types();