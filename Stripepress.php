<?php
/**
 * Plugin Name: StripePress
 * Plugin URI: https://stripepress.com/
 * Description: An e-commerce platform/solution for Wordpress based on Stripe's web API features.
 * Version: 1.0.0
 * Author: Circular Creative
 * Author URI: https://circularcreative.net
 * Requires at least: 4.7
 * Tested up to: 4.7
 *
 * Text Domain: stripepress
 * Domain Path: /i18n/languages/
 *
 * @package StripePress
 * @category Core
 * @author Circular Creative
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'Stripepress' ) ) :

class Stripepress {
	
	/**
	 * The single instance of the class.
	 *
	 * @var Stripepress
	 */
	protected static $_instance = null;
	
	/**
	 * The SVG icon for this plugin.
	 *
	 * @var Stripepress
	 */
	protected $sp_icon = null;
	
	/**
	 * The Stripe API secret key for this session.
	 *
	 * @var Stripepress
	 */
	public $secret_key = '';
	
	/**
	 * The Stripe API public key for this session.
	 *
	 * @var Stripepress
	 */
	public $public_key = '';
	
	/**
	 * Main Stripepress Instance.
	 *
	 * Ensures only one instance of Stripepress is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @static
	 * @return Stripepress - Main instance.
	 */
	final public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Cloning is forbidden.
	 * @since 1.0.0
	 */
	public function __clone() {
		trigger_error('Cloning forbidden.', E_USER_ERROR);
	}
	
	/**
	 * Stripepress Constructor.
	 */
	final public function __construct() {
		$this->sp_constants();
		$this->sp_includes();
		$this->sp_hooks();
		
		apply_filters( 'nonce_life', 300 );

		do_action( 'stripepress_loaded' );
	}
	
	private function sp_constants() {
		
		$this->sp_define( 'SP_PLUGIN_NAME', 'StripePress' );
		$this->sp_define( 'SP_PLUGIN_SLUG', 'stripepress' );
		$this->sp_define( 'SP_PLUGIN_PREFIX', 'sp_' );
		$this->sp_define( 'SP_VERSION', '1.0.0' );
		$this->sp_define( 'SP_ABSPATH', plugin_dir_path( __FILE__ ) );
		$this->sp_define( 'SP_INCLUDE_PATH', plugin_dir_path( __FILE__ ) . 'inc' );
		$this->sp_define( 'SP_ADMIN_PATH', plugin_dir_path( __FILE__ ) . 'inc/admin' );
		$this->sp_define( 'SP_LOG_PATH', plugin_dir_path( __FILE__ ) . 'stripepress_logs/' );
		$this->sp_define( 'SP_TEMPLATE_DIR', plugin_dir_path( __FILE__ ) . 'templates/' );
		$this->sp_define( 'SP_ASSET_DIR', plugin_dir_path( __FILE__ ) . 'assets/' );
		
		$sp_icon = '<svg class="icon icon-coin-dollar" viewBox="0 0 32 32" xmlns="http://www.w3.org/2000/svg"><path fill="black" d="M15 2c-8.284 0-15 6.716-15 15s6.716 15 15 15c8.284 0 15-6.716 15-15s-6.716-15-15-15zM15 29c-6.627 0-12-5.373-12-12s5.373-12 12-12c6.627 0 12 5.373 12 12s-5.373 12-12 12zM16 16v-4h4v-2h-4v-2h-2v2h-4v8h4v4h-4v2h4v2h2v-2h4l-0-8h-4zM14 16h-2v-4h2v4zM18 22h-2v-4h2v4z"></path></svg>';
		
		$this->sp_define( 'SP_SVG_ICON', $sp_icon );
		$this->sp_define( 'SP_SVG_ICON_B64', 'data:image/svg+xml;base64,' . base64_encode($sp_icon) );
		
	}
	
	private function sp_includes() {
		
		// include the Stripe PHP interface library
		require_once(SP_ABSPATH . '/libs/stripe-php/init.php');
		
		//functions
		require_once( SP_INCLUDE_PATH . '/sp_functions.php');
		
		
		if (is_admin()) : //admin
		
			require_once( SP_INCLUDE_PATH . '/admin/sp_admin.php');
		
		endif;
		
		//classes
		require_once( SP_INCLUDE_PATH . '/sp_auth.php');
		require_once( SP_INCLUDE_PATH . '/sp_sub.php');
		require_once( SP_INCLUDE_PATH . '/sp_events.php');
		require_once( SP_INCLUDE_PATH . '/sp_install.php');
		require_once( SP_ADMIN_PATH . '/sp_ajax.php');
		//require_once( SP_INCLUDE_PATH . '/sp_post_types.php');
		
		
	}
	
	private function sp_hooks() {
		
		register_activation_hook( __FILE__, array( 'SP_Events', 'stripe_events_setup' ) );
		//add_action( 'plugin_loaded' , array('SP_Events','init'));
		add_action( 'stripepress_loaded' , array( $this, 'set_api_keys') , 0);
	}
	
	private function sp_define( $name, $value ) {
		
		if ( ! defined( $name ) ) {
			define( $name, $value );
		}
		
	}
	
	final public function set_api_keys() {
		
		$db_api_keys = get_option('stripepress_api_keys');
		$prefix = (get_option('stripepress_testmode')) ? 'test_' : 'live_';
		
		if (empty($db_api_keys)) :
			throw new Exception('API keys not set in Stripepress Admin area. Please do this to continue.');
		endif;
		
		foreach ($db_api_keys as $key => $val) :
			
			switch ($key) :
				case 'pk_'.$prefix :
					$this->public_key = 'pk_'. $prefix . base64_decode($val);
					break;
				case 'sk_'.$prefix :
					$this->secret_key = 'sk_'. $prefix . base64_decode($val);
					break;
				default :
					continue;
			endswitch;	
		
		endforeach;
		
		\Stripe\Stripe::setApiKey($this->secret_key);
		
	}
	
} // end Stripepress class
endif;

function Spress() {
	return Stripepress::instance();
}
Spress();

//end of line, man.