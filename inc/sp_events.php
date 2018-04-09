<?php
if ( ! defined( 'ABSPATH' ) ) {exit;}

add_action( 'init' , array('SP_Events','stripe_events_handler'), 1 );
class SP_Events {
	
	public static function stripe_events_handler() {
		if (!isset($_GET['stripeevent'])) { //end this function if GET var 'stripeevent' isn't set
			return false;
		}
	
		date_default_timezone_set('America/Los_Angeles');
		
		$input = @file_get_contents("php://input");
		$decoded = json_decode($input,true);
		if ($decoded['object'] !== 'event') // end function if 'object' doesn't equal 'event'
			return false;
		
		// set up Stripe environment
		\Stripe\Stripe::setApiKey(Spress()->secret_key);
		//$ep_sec = STRIPE_WHSEC_TEST; //test
		$ep_sec = STRIPE_WHSEC_LIVE; //live
		$sig_header = @$_SERVER["HTTP_STRIPE_SIGNATURE"];
		$event = null;
		
		//catch any authentication errors
		try {
			
			$event = \Stripe\Webhook::constructEvent(
				$input, $sig_header, $ep_sec
			);
			
			$log_vars = array(
				'log_path'=>SP_LOG_PATH . 'events/',
				'date'=>date('m/d/Y G:i:s', time()),
				'fw_ip'=>getenv('HTTP_X_FORWARDED_FOR') ?: '0.0.0.0',
				'ip'=>getenv('REMOTE_ADDR'),
				'ua'=>getenv('HTTP_USER_AGENT'),
				'event'=>$decoded['type'],
				'id'=>$decoded['id'],
				'error'=>false
			);
			
		self::log_it($log_vars);

		do_action('stripepress_events',$decoded['type'],$input);
			
		http_response_code(200);
		echo wp_send_json('Thank you.');
		
		} catch(\UnexpectedValueException $e) {
		// Invalid payload
			$log_vars['error']=true;
			self::log_it($log_vars);
			
			do_action('stripepress_events_invalid');
			
			$kubrick = array(
			array(
				'Dave'=>'Do you read me, HAL?',
				'HAL'=>'Affirmative, Dave. I read you.'
			),
			array(
				'Dave'=>'Open the pod bay doors, HAL.',
				'HAL'=>'I\'m sorry Dave, I\'m afraid I can\'t do that.'
			),
			array(
				'Dave'=>'What\'s the problem?',
				'HAL'=>'I think you know what the problem is, just as well as I do.'
			),
			array(
				'Dave'=>'What are you talking about, HAL?',
				'HAL'=>'This mission is too important for me to allow you to jeopardize it.'
			),
			array(
				'Dave'=>'I don\'t know what you\'re talking about, HAL.',
				'HAL'=>'I know that you and Frank were planning to disconnect me, and I\'m afraid that is something I cannot allow to happen.'
			),
			array(
				'Dave'=>'Where the hell\'d you get that idea, HAL?',
				'HAL'=>'Dave, although you took very thorough precautions in the pod against my hearing you, I could see your lips move.'
			),
			array(
				'Dave'=>'Alright HAL, I\'ll go in through the emergency airlock.',
				'HAL'=>'Without your space helmet, Dave, you\'re going to find that rather difficult.'
			),
			array(
				'Dave'=>'HAL, I won\'t argue with you anymore! Open the doors!',
				'HAL'=>'Dave... This conversation can serve no purpose anymore. Goodbye.'
			)
		);
			
			http_response_code(418);
			echo wp_send_json($kubrick);
			
		} catch(\Stripe\Error\SignatureVerification $e) {
		// Invalid signature
			$log_vars['error']=true;
			self::log_it($log_vars);
			http_response_code(401);
			echo wp_send_json(array('message'=>'Invalid signature'));
		}
		die;
	}
	
	private static function log_it($vars = array()) {
		$suffix = $err = '';
		if ($error) :
			$err = "ERROR\t";
			$suffix = '_error';
		endif;
		$suffix .= '_'.date('m-d-Y');

		$input = array(
			'event'=>$vars['event'],
			'id'=>$vars['id'],
			'ip'=>$vars['ip'],
			'forwarded'=>$vars['fw_ip']
		);
		
		file_put_contents(
			$vars['log_path']."stripe_events{$suffix}.log",
			"\n\n".$err.$vars['date']."\t".json_encode($input)."\t{$vars['ua']}",
			FILE_APPEND | LOCK_EX
		);
	}

	public static function action_test() {
		echo wp_send_json(array('test'=>'action hook test'));
	}
	
}