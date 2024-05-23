<?php


class CWS_GamesUsers
{
	/**
	 * Controlls the addPlayer communication with the BackOffice
	 * 
	 * @param [int] $user_id
	 * @param [array] $data - user fields
	 * @return [void]
	 */ 
	public function addUser($data)
	{	
		$error = null;

		$CWS_BackofficeAPI = new CWS_BackofficeAPI();

		$response = $CWS_BackofficeAPI->addPlayer($data);

		if (isset($response['code']) && $response['code'] == 200) {
			
			if (!isset($response['data']['state']) || 
				(isset($response['data']['state']) && $response['data']['state'] != 1) ) {

				$error = new WP_Error( 'cws_games_register_error', __('This account has been suspended. Please contact an administrator.') );

				$response['error'] = $error;

				return $response;
			}

			if(session_id() == '' || !isset($_SESSION) || session_status() === PHP_SESSION_NONE) {
			    // session isn't started
			    session_start();
			}

			if (isset($response['data']['playerId']) && $response['data']['playerId'] != '') {
				$_SESSION['new_customer_playerId'] = $response['data']['playerId'];
			}

		} else {
			if (isset($response['errors']) && !empty($response['errors'])) {
				$error = new WP_Error( array_key_first($response['errors']), $response['errors'][array_key_first($response['errors'])] );
			} else {
				$error = new WP_Error( 'cws_games_register_error', __('An unknown error occured. Please try again.', 'cws_games') );
			}
		}

		if (is_wp_error($error)) {
			$response['error'] = $error;
		}

		__sd($response, 'BackOffice addPlayer response');

		return $response;
	}

	/**
	 * Controlls the loginPlayer communication with the BackOffice
	 * 
	 * @param [array] $data - login credentials [email, password]
	 * @return [void]
	 */ 
	public function loginUser($data)
	{	
		$error = null;

		$CWS_BackofficeAPI = new CWS_BackofficeAPI();

		$response = $CWS_BackofficeAPI->loginPlayer($data);

		if (isset($response['code']) && $response['code'] == 200) {

			$user_id = 0;

			// Check if user exists in the WordPress

			$creds = array(
				'user_login' 	=> sanitize_user( trim( $response['data']['email'] ?? '' ) ),
				'user_password' => $response['data']['password'] ? base64_decode($response['data']['password']) : '',
				'remember' 		=> false
			);

			// Perform the login
			$user = wp_signon( $creds, is_ssl() );

			if (is_wp_error($user)) {
				$message_code = $user->get_error_code();

				$error_codes = array(
					'invalid_username',
					'invalid_email'
				);

				if (in_array($message_code, $error_codes)) {

					// Create new user in WordPress
					$new_user_data = array(
						'user_login' 	=> $response['data']['username'] ?? '',
						'user_pass' 	=> $response['data']['password'] ? base64_decode($response['data']['password']) : '',
						'user_email' 	=> $response['data']['email'] ?? '',
						'role' 			=> 'customer'
					);

					$user_id = wp_insert_user( $new_user_data );

					// __sd($user_id, 'REGISTER NEW USER');

					if (is_wp_error($user_id)) {

						$message_code = $user_id->get_error_code();

						$error_codes = array(
							'existing_user_login',
							'empty_user_login'
						);

						if (in_array($message_code, $error_codes)) {

							$new_user_data['user_login'] = md5($new_user_data['user_email']);

							$user_id = wp_insert_user( $new_user_data );

							if (is_wp_error($user_id)) {

								$error = new WP_Error( 'cws_games_login_error', __('An error occured. Please contact us if you continue to have problems.', 'cws_games') );

								return $error;
								
							}

						} else {
							$error = new WP_Error( 'cws_games_login_error', __('An error occured. Please contact us if you continue to have problems.', 'cws_games') );

							return $error;
						}
						
					}
				} elseif ($message_code == 'incorrect_password') {

					$user = get_user_by( 'email', $response['data']['email'] );

					if ($user && !is_wp_error($user)) {
						$user_id = $user->ID ?? 0;

						if ($user_id) {
							// Update user password in WordPress
							$user_update_args = [];

							if (isset($response['data']['username']) && $response['data']['username'] != '') {
								$user_update_args['display_name'] = $response['data']['username'];
							}

							if (isset($response['data']['email']) && $response['data']['email'] != '') {
								$user_update_args['user_email'] = $response['data']['email'];
							}

							if (isset($response['data']['password']) && $response['data']['password'] != '') {
								$user_update_args['user_pass'] = base64_decode($response['data']['password']);
							}

							if (!empty($user_update_args)) {
								$user_update_args['ID'] = $user_id;

								wp_update_user( $user_update_args );
							}
						}
					}
				}

			} else {
				$user_id = $user->ID ?? 0;
			}


			if ($user_id > 0 && !is_wp_error($user_id)) {

				$user_update_args = [];

				if (isset($response['data']['username']) && $response['data']['username'] != '') {
					$user_update_args['display_name'] = $response['data']['username'];
				}

				if (isset($response['data']['email']) && $response['data']['email'] != '') {
					$user_update_args['user_email'] = $response['data']['email'];
				}

				if (isset($response['data']['password']) && $response['data']['password'] != '') {
					$user_update_args['user_pass'] = base64_decode($response['data']['password']);
				}

				if (!empty($user_update_args)) {
					$user_update_args['ID'] = $user_id;

					wp_update_user( $user_update_args );
				}

				if (isset($response['data']['first_name']) && $response['data']['first_name'] != '') {
					update_user_meta($user_id, 'first_name', $response['data']['first_name']);
				}

				if (isset($response['data']['second_name']) && $response['data']['second_name'] != '') {
					update_user_meta($user_id, 'last_name', $response['data']['second_name']);
				}

				if (isset($response['data']['playerId']) && $response['data']['playerId'] != '') {
					update_user_meta($user_id, 'playerId', $response['data']['playerId']);
				}

				if (isset($response['data']['state']) && $response['data']['state'] != '' && in_array(intval($response['data']['state']), array(0, 1))) {
					update_user_meta($user_id, 'state', intval($response['data']['state']));
				}

				update_user_meta($user_id, 'show_user_welcome_modal', 1);

			}

		} else {

			if (isset($response['errors']) && !empty($response['errors'])) {
				$error = new WP_Error( array_key_first($response['errors']), $response['errors'][array_key_first($response['errors'])] );
			} else {
				$error = new WP_Error( 'cws_games_login_error', __('An unknown error occured. Please try again.', 'cws_games') );
			}
		}

		__sd($response, 'BackOffice loginPlayer response');

		return $error;
	}

	/**
	 * Controlls the updatePlayer communication with the BackOffice
	 * 
	 * @param [int] $user_id
	 * @param [array] $data - user fields
	 * @return [void]
	 */ 
	public function updateUser($user_id, $data)
	{
		$CWS_BackofficeAPI = new CWS_BackofficeAPI();

		$save = $CWS_BackofficeAPI->updatePlayer($data);

		__sd($save, 'BackOffice updatePlayer response');
	}

	/**
	 * Controlls the getWallets communication with the BackOffice
	 * 
	 * @param [int] $user_id
	 * @return [void]
	 */ 
	public function getUserWallet($user_id)
	{	
		$data = [];

		if ($user_id) {
			$playerId = get_user_meta($user_id, 'playerId', true);

			if ($playerId) {
				$data['playerId'] = $playerId;
			}
		}

		$CWS_BackofficeAPI = new CWS_BackofficeAPI();

		$response = $CWS_BackofficeAPI->getWallets($data);

		if (isset($response['code']) && $response['code'] == 200) {
			if (isset($response['data']) && is_array($response['data']) && !empty($response['data'])) {
				update_user_meta($user_id, 'walletBallance', json_encode($response['data']));
			}

			if (isset($response['data']['redeemable']) && !empty($response['data']['redeemable'])) {
				// Generate, edit or delete WC Coupons

				$coupon_codes = [];

				foreach ($response['data']['redeemable'] as $coupon_data) {
					$coupon_code 	= $coupon_data['coupon'];
					$amount 		= $coupon_data['totalRealMoney'] ?? 0;
					$discount_type 	= 'fixed_cart'; // Type: percent, fixed_cart, fixed_product, percent_product

					$args = array(
						'posts_per_page' 	=> 1,
						'post_type' 		=> 'shop_coupon',
						'meta_query' 		=> array(
							'relation' 		=> 'AND',
							array(
								'key' 		=> 'og_coupon_code',
								'value' 	=> $coupon_code,
								'compare' 	=> '='
							),
							array(
								'key' 		=> 'user_id',
								'value' 	=> $user_id,
								'compare' 	=> '='
							)
						)
					);

					$query = new WP_Query($args);

					if ($query->have_posts()) {

						// UPDATE

						$coupon = reset($query->posts);

						update_post_meta( $coupon->ID, 'discount_type', $discount_type );
						update_post_meta( $coupon->ID, 'coupon_amount', $amount );
						// update_post_meta( $coupon->ID, 'minimum_amount', $amount );
						update_post_meta( $coupon->ID, 'redeemable_coupon', 1 );

					} else {

						// CREATE

						$coupon_fields = array(
							'post_title' 	=> $coupon_code,
							'post_content' 	=> '',
							'post_status' 	=> 'publish',
							'post_type' 	=> 'shop_coupon'
						);

						$new_coupon_id = wp_insert_post($coupon_fields);

						if ($new_coupon_id) {
							update_post_meta( $new_coupon_id, 'discount_type', $discount_type );
	    					update_post_meta( $new_coupon_id, 'coupon_amount', $amount );
	    					// update_post_meta( $new_coupon_id, 'minimum_amount', $amount );
	    					update_post_meta( $new_coupon_id, 'individual_use', 'no' );
	    					update_post_meta( $new_coupon_id, 'product_ids', '' );
						    update_post_meta( $new_coupon_id, 'exclude_product_ids', '' );
						    update_post_meta( $new_coupon_id, 'usage_limit', '' );
						    update_post_meta( $new_coupon_id, 'expiry_date', '' );
						    update_post_meta( $new_coupon_id, 'apply_before_tax', 'yes' );
						    update_post_meta( $new_coupon_id, 'free_shipping', 'no' );
						    update_post_meta( $new_coupon_id, 'user_id', $user_id );
						    update_post_meta( $new_coupon_id, 'og_coupon_code', $coupon_code );
						    update_post_meta( $new_coupon_id, 'redeemable_coupon', 1 );
						}
					}

					$coupon_codes[] = $coupon_code;
				}

				if (!empty($coupon_codes)) {
					$args = array(
						'posts_per_page' 	=> -1,
						'post_type' 		=> 'shop_coupon',
						'meta_query' 		=> array(
							'relation' 		=> 'AND',
							array(
								'key' 		=> 'og_coupon_code',
								'value' 	=> $coupon_codes,
								'compare' 	=> 'NOT IN'
							),
							array(
								'key' 		=> 'user_id',
								'value' 	=> $user_id,
								'compare' 	=> '='
							)
						)
					);

					$query = new WP_Query($args);

					if ($query->have_posts()) {

						// DELETE

						foreach ($query->posts as $coupon) {
							wp_delete_post($coupon->ID);
						}
					}
				}
			}
		}

		__sd($response, 'BackOffice getWallets response');
	}

	/**
	 * Controlls the getWalletTransactions communication with the BackOffice
	 * 
	 * @param [int] $user_id
	 * @return [void]
	 */
	public function getUserWalletTransactions($user_id)
	{
		$data = [];

		if ($user_id) {
			$playerId = get_user_meta($user_id, 'playerId', true);

			if ($playerId) {
				$data['playerId'] = $playerId;
			}
		}

		$CWS_BackofficeAPI = new CWS_BackofficeAPI();

		$response = $CWS_BackofficeAPI->getWalletTransactions($data);

		__sd($response, 'BackOffice getWalletTransactions response');

		return $response['data'] ?? [];
	}

	/**
	 * Controlls the getWalletTransactions communication with the BackOffice
	 * 
	 * @param [int] $user_id
	 * @param [array] $data - request parameters
	 * @return [void]
	 */
	public function addWalletTransaction($user_id, $data)
	{
		if ($user_id) {
			$playerId = get_user_meta($user_id, 'playerId', true);

			if ($playerId) {
				$data['playerId'] = $playerId;
			}
		}

		$CWS_BackofficeAPI = new CWS_BackofficeAPI();

		$response = $CWS_BackofficeAPI->addWalletTransaction($data);

		__sd($response, 'BackOffice addWalletTransaction response');

		return $response;
	}

	/**
	 * Get user notifications list
	 * 
	 * @param [int] $user_id
	 * @return [void]
	 */
	public function getUserNotifications($user_id, $data = [])
	{
		if ($user_id) {

			$playerId = get_user_meta($user_id, 'playerId', true);

			if ($playerId) {
				$data['playerId'] = $playerId;
			}
		}

		$CWS_BackofficeAPI = new CWS_BackofficeAPI();

		$response = $CWS_BackofficeAPI->getNotifications($data);

		__sd($response, 'BackOffice getNotifications response');

		return $response['data'] ?? [];
	}

	/**
	 * Controlls the getWalletTransactions communication with the BackOffice
	 * 
	 * @param [int] $user_id
	 * @param [array] $data - request parameters
	 * @return [void]
	 */
	public function getUserNotification($user_id, $data)
	{
		if ($user_id) {
			$playerId = get_user_meta($user_id, 'playerId', true);

			if ($playerId) {
				$data['playerId'] = $playerId;
			}
		}

		$CWS_BackofficeAPI = new CWS_BackofficeAPI();

		$response = $CWS_BackofficeAPI->getNotification($data);

		__sd($response, 'BackOffice getNotification response');

		return $response;
	}

	public function getNotificationForScreen($user_id, $data)
	{
		$result = [
			'title' 	=> '',
			'body' 		=> ''
		];

		$response = $this->getUserNotification($user_id, $data);

		if (isset($response['code']) && $response['code'] == 200) {
			$result['title'] = $response['data']['title'] ?? '';
			$result['body'] = $response['data']['body'] ?? '';

			$notification_id = $response['data']['id'];

			// mark it as READ in site db
			$cws_games_notifications = get_user_meta($user_id, 'cws_games_notifications', true);

			if ($cws_games_notifications) {
				$cws_games_notifications_array = explode(',', $cws_games_notifications);

				if (is_array($cws_games_notifications_array) && !empty($cws_games_notifications_array)) {
					foreach ($cws_games_notifications_array as $key => $value) {
						if (intval($value) == intval($notification_id)) {
							unset($cws_games_notifications_array[$key]);
						}
					}

					update_user_meta($user_id, 'cws_games_notifications', implode(',', $cws_games_notifications_array));
				}
			}

		} else {
			if (isset($response['errors']) && !empty($response['errors'])) {
				$result['title'] 	= __('Notification Error', 'cws_games');
				$result['body'] 	= $response['errors'][array_key_first($response['errors'])];
			} else {
				$result['title'] 	= __('Notification Error', 'cws_games');
				$result['body'] 	= __('An unknown error occured. Please try again.', 'cws_games');
			}
		}

		return $result;
	}
}