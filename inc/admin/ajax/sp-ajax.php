<?php
/**
 * Stripepress Custom Ajax Process Execution
 */

define( 'DOING_AJAX', true );

require_once( dirname(dirname(dirname(dirname(dirname(dirname(__DIR__)))))).'/wp-load.php' );

send_origin_headers();

// Require an action parameter
if ( empty( $_REQUEST['action'] ) )
	wp_die( 'A valid action must be specified.', 403 );
	
@header( 'Content-Type: text/html; charset=' . get_option( 'blog_charset' ) );
@header( 'X-Robots-Tag: noindex' );

send_nosniff_header();
nocache_headers();

$action = trim($_REQUEST['action']);

$allowed_actions = array(
					'sttvsubmitter',
					'sttvajax_signup',
					'sttv_username',
					'sttv_email',
					'sttv_billing_pcode',
					'sttv_coupon',
					'update_api_keys',
					'sp_test_mode'
				);


if(in_array($action, $allowed_actions)) {
        if(is_user_logged_in()) :
			do_action( 'sp_ajax_' . $action );
        else :
            do_action( 'sp_ajax_nopriv_' . $action );
		endif;
} else {
	wp_die( 'A valid action must be specified.', 403 );
}

// Default status
die( 'dead' );