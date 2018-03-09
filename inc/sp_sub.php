<?php

class SP_Sub extends Stripepress {

	public static function create() {

		parse_str($_POST['inputs'], $output); // parse the serialized form values

		/**
		 * Create new subscription (Stripe)
		 * IF payments fails, delete customer and return to form
		 * (RETURN) sub_ID, cus_ID, inv_ID
		**/
		
		try {
			/**
			 * Get Plan metadata, where we've stored the appropriate WP role
			**/

			$planID = ($_POST['plan']) ?: '10789';

			$plan = \Stripe\Plan::retrieve($planID);

			$role = $plan->metadata->role;

			// get form values
			$firstname = sanitize_text_field($output['sttv_firstname']);
			$lastname = sanitize_text_field($output['sttv_lastname']);
			$fullname = $firstname . ' ' . $lastname;
			$email = sanitize_email($output['sttv_email']);
			$phone = $output['sttv_phone'];
			
			$no_trial = $output['sttv_no_trial'];
			$priority = (bool) $output['sttv_digital_book'];
			$coupon = sanitize_text_field($output['sttv_coupon'] ?: '');

			// addresses
			$b_line1 = sanitize_text_field($output['sttv_billing_address1']);
			$b_line2 = sanitize_text_field($output['sttv_billing_address2']);
			$b_city = sanitize_text_field($output['sttv_billing_city']);
			$b_state = sanitize_text_field($output['sttv_billing_state']);
			$b_ZIP = sanitize_text_field($output['sttv_billing_pcode']);
			$b_country = sanitize_text_field($output['sttv_billing_country']);

			// init customer object
			$customer = NULL;
			$customerID = (is_user_logged_in()) ? get_user_meta(get_current_user_id(),'stripe_cus_ID',true) : false;

			// payment token
			$token = $_POST['token']['id'];

			// tax calculation
			$ca_zips = json_decode(file_get_contents('https://gist.githubusercontent.com/enlightenedpie/99139b054dd9e4ad3f81689e2326d198/raw/69b654b47a01d2dc9e9ac34816c05ab5aa9ad355/ca_zips.json'));
			$tax = 0;
			if (in_array($b_ZIP,$ca_zips->losangeles)) :
				$tax = 9.5;
			else :
				foreach ($ca_zips as $array) :
					if (in_array($b_ZIP,$array)) :
						$tax = 7.5;
					endif;
				endforeach;
			endif;

		/**
		 * Create new customer (Stripe)
		 * IF user doesn't have stripe_cus_ID, they do not exist in Stripe; create the user.
		 * ELSE update user info and payment on Stripe
		**/
			if (!$customerID) {
				$customer = \Stripe\Customer::create(array(
				  "description" => $fullname,
				  "source" => $token,
				  "email" => $email,
				  "coupon"=> $coupon,
				  "shipping" => array(
					"name" => "shipping",
					"address" => array(
						"line1" => sanitize_text_field($output['sttv_shipping_address1']),
						"line2" => sanitize_text_field($output['sttv_shipping_address2']),
						"city" => sanitize_text_field($output['sttv_shipping_city']),
						"state" => sanitize_text_field($output['sttv_shipping_state']),
						"postal_code" => sanitize_text_field($output['sttv_shipping_pcode']),
						"country" => sanitize_text_field($output['sttv_shipping_country'])
					)
					)
				));

				$customerID = $customer->id;
			} else {
				$customer = \Stripe\Customer::retrieve($customerID);
				$customer->description = $fullname;
				$customer->source = $token;
				$customer->save();
			}
			
			/**
			 * Create invoice items for the shipping charge and book tax
			 *
			**/
			if ($priority) :
				\Stripe\InvoiceItem::create(array(
					"customer" => $customerID,
					"amount" => 1285,
					"currency" => "usd",
					"description" => "Priority Shipping",
					"discountable" => false
				));
			endif;
			
			if ($tax) :
				\Stripe\InvoiceItem::create(array(
					"customer" => $customerID,
					"amount" => round(2500*($tax/100)),
					"currency" => "usd",
					"description" => "Sales tax",
					"discountable" => false
				));
			endif;


			/**
			 * Subscription params setup
			 * @param
			**/
			$sub_array = array(
			  "customer" => $customerID,
			  "plan" => $planID
			);
			
			if ($no_trial) {
				$sub_array['trial_end'] = now;
			}
			

			/**
			 * Create Subscription and set to cancel at end of term
			**/
			$sub = \Stripe\Subscription::create($sub_array);

			$sub->cancel(array('at_period_end' => true));

			/**
			 * Fetch the invoice just created
			**/
			$inv = \Stripe\Invoice::all(array(
				"limit" => 1,
				"subscription" => $sub->id
			));
			$inv_id = $inv->data[0]->id;

		} catch(\Stripe\Error\Card $e) {
		  // card declined
		  $body = $e->getJsonBody();
		  $err  = $body['error'];

		  $error = array(
			'error'=>$err['type'],
			'code'=>$err['code'],
			'decline_code'=>$err['decline_code'],
			'param'=>$err['param'],
			'message'=>$err['message']
		  );
		  //$customer->delete();
		  echo wp_send_json_error($error);

		} catch (\Stripe\Error\RateLimit $e) {
		  // Too many requests made to the API too quickly
		  $body = $e->getJsonBody();
		  $err  = $body['error'];
		  $subject = 'Stripe API error: Rate Limit';

		  wp_mail(get_bloginfo('admin_email'),$subject,json_encode($err));

		  $error = array(
				'error'=>'rate_limit',
				'message'=>'There was a server issue and you have not been charged. Please try again.'
			);
			echo wp_send_json_error($error);

		} catch (\Stripe\Error\InvalidRequest $e) {
		  // Invalid parameters were supplied to Stripe's API
		  $body = $e->getJsonBody();
		  $err  = $body['error'];
		  $subject = 'Stripe API error: Invalid Parameters';

		  wp_mail(get_bloginfo('admin_email'),$subject,json_encode($err));
		  $error = array(
				'error'=>'invalid_request_error',
				'message'=>'An invalid request was made. You have not been charged. Please reload the page and try again.'
			);

			echo wp_send_json_error($err);

		} catch (\Stripe\Error\Authentication $e) {
		  // Authentication with Stripe's API failed
		  $body = $e->getJsonBody();
		  $err  = $body['error'];
		  $subject = 'Stripe API error: Authentication Failure';

		  wp_mail(get_bloginfo('admin_email'),$subject,json_encode($err));

		  $error = array(
				'error'=>'auth_fail',
				'message'=>'The server could not connect with the payment processor. You have not been charged. Please try again.'
			);
			echo wp_send_json_error($error);

		} catch (\Stripe\Error\ApiConnection $e) {
		  // Network communication with Stripe failed
		  $body = $e->getJsonBody();
		  $err  = $body['error'];
		  $subject = 'Stripe API error: Connection Failure';

		  wp_mail(get_bloginfo('admin_email'),$subject,json_encode($err));

		  $error = array(
				'error'=>'api_comm',
				'message'=>'The server could not connect with the payment processor. You have not been charged. Please try again.'
			);
			echo wp_send_json_error($error);

		} catch (\Stripe\Error\Base $e) {

			$body = $e->getJsonBody();
			$err  = $body['error'];
			$subject = 'Stripe API error: Generic Error';

		  wp_mail(get_bloginfo('admin_email'),$subject,json_encode($err));

			$error = array(
				'error'=>'generic',
				'message'=>'We apologize, something went wrong on our end. You have not been charged. Please try again later.'
			);
			echo wp_send_json_error($error);

		} catch (Exception $e) {
		  // Something else happened, completely unrelated to Stripe
		  $err = $e;
		  $subject = 'Generic Error';

		  wp_mail(get_bloginfo('admin_email'),$subject,json_encode($err));

		  $error = array(
				'error'=>'generic',
				'message'=>'We apologize, something went wrong on our end. You have not been charged. Please try again later.',
				'object'=>$e
			);
			echo wp_send_json_error($error);
		}
		// END TRY/CATCH

		/**
		 * Add a new user (or update current user), give them access to the appropriate content
		 * after all the Stripe stuff has been created and there are no errors
		**/
		$userdata = array(
			'ID'=> (get_current_user_id()) ?: '',
			'user_login'=>$email,
			'user_pass'=>$output['sttv_password'],
			'user_email'=>$email,
			'first_name'=>$firstname,
			'last_name'=>$lastname,
			'display_name'=>$firstname,
			'role'=>$role,
			'show_admin_bar_front'=>'false'
		);

		$user_id = wp_insert_user($userdata);

		// handle metadata, then echo success or failure
		if (!is_wp_error($user_id)) {

			$creds = array(
				'user_login'    => $email,
				'user_password' => $output['sttv_password'],
				'remember'      => true
			);
			wp_signon( $creds, is_ssl() );

			update_user_meta( $user_id, 'show_admin_bar_front', 'false' );
			update_user_meta( $user_id, 'show_admin_bar_admin', 'false' );


			$customer->metadata = array(
				'wp_id'=>$user_id
			);
			$customer->save();

			 $meta = array(
				'cus_ID'=>$customerID,
				'cus_created'=>$customer->created,
				'cus_default_source'=>$customer->default_source,
				'pmt_token'=>$token,
				'subscriptions'=>array()
			 );
			
			$meta['subscriptions'][] = array(
				'plan_ID'=>$sub->items->data[0]->plan->id,
				'sub_ID'=>$sub->id,
				'sub_expires'=>$sub->current_period_end,
				'inv_ID'=>$inv_id
			);
			 // success URL == customer ID with prefix stripped, token equal to hexidecimal version of UNIX timestamp known as "current_period_end"

			update_user_meta($user_id,'stripe_meta',$meta);

			// send success url back to the front end script
			echo wp_send_json_success($meta['success_url']);

		} else {
			echo wp_send_json_error($user_id);
		}

	} // end create() method
} // end class.