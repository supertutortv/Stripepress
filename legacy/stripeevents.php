<?php
if ( ! defined( 'ABSPATH' ) ) {exit;}

add_action('init','stripe_events_handler');
	
function stripe_events_handler() {
		
		$input = @file_get_contents("php://input");
		$decoded = json_decode($input,true);
		if (!isset($_GET['stripeevent']) && $decoded['object'] !== 'event') //end this function if GET var 'stripeevent' isn't set AND 'object' doesn't equal 'event'
			return;
			
			require_once(dirname(__DIR__).'/themes/sttv_2017/lib/Stripe/init.php');
			\Stripe\Stripe::setApiKey("sk_live_7MEB0v6Ylh4GkEeQ9hNAngwc");
			
			date_default_timezone_set('America/Los_Angeles');
		
			$allowed_calls = array(
				'customer.subscription.deleted',
				'customer.subscription.created',
				'customer.subscription.trial_will_end'
			);
			$params = array(
				'log_path'=>__DIR__.'/stripe_logs/',
				'event'=>$decoded['type'] ?: '',
				'id'=>$decoded['id'],
				'date'=>date('m/d/Y G:i:s', time()),
				'ip'=>getenv('REMOTE_ADDR'),
				'forwarded'=>getenv('HTTP_X_FORWARDED_FOR') ?: '0.0.0.0',
				'user_agent'=>getenv('HTTP_USER_AGENT')
			);
			
			
				if (in_array($params['event'],$allowed_calls)) :
						
					switch ($params['event']) :
					
						case 'customer.subscription.created' :
						case 'customer.subscription.deleted' :
						case 'customer.subscription.trial_will_end' :
							//print $decoded['data']['object']['customer'];
							mail_it($decoded);
							break;
						
						default :
							print 'Event not allowed';
							http_response_code(403);
							die;
					
					endswitch;
					
					http_response_code(200);
					
					log_it($params);
					
				else :
					$params['error'] = true;
					log_it($params);
					
					print 'Forbidden';
					http_response_code(403);
				
					//wp_redirect(home_url());
					
				endif;
				
			die;
	}
		function mail_it($input) {
			$body = '<pre>'.json_encode($input,JSON_PRETTY_PRINT).'</pre>';
				$headers = 'Content-type: text/html; charset=iso-8859-1'.'\r\n'.
				'From: dave@supertutortv.com'.'\r\n'.
				'Reply-To: dave@supertutortv.com'.'\r\n';
				mail('enlightenedpie@gmail.com','Stripe Events test',$body,$headers);
			
		}
		function log_it($params = array()) {
			$defaults = array(
				'log_path'=>'',
				'event'=>'',
				'id'=>'',
				'date'=>'',
				'ip'=>'',
				'forwarded'=>'',
				'user_agent'=>'',
				'error'=>false
			);
			$p = array_merge($defaults,$params);
			$suffix = $err = '';
			if (!$p['id'] || $p['error']) :
				$suffix = '_error';
			endif;
			if ($p['error']) :
				$err = "ERROR\t";
			endif;
			$suffix .= '_'.date('m-d-Y');
			
			$input = array(
				'event'=>$p['event'],
				'id'=>$p['id'],
				'ip'=>$p['ip'],
				'forwarded'=>$p['forwarded']
			);
			$payload = json_encode($input);
			
			file_put_contents(
				$p['log_path']."/stripe_events{$suffix}.log",
				"\n\n".$err.$p['date']."\t".$payload."\t".$p['user_agent'],
				FILE_APPEND | LOCK_EX
			);
		}