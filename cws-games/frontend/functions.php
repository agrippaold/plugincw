<?php

if (!function_exists('function_cws_games_gameslist')) {
    /**
     * Games list Shortocde
     * @param tpl 		= none by default | two | three
     * @param per_row 	= items per row (min 2 / max 8)
     * @param gametype 	= paid by default | free | paid
     * @param type 		= most_played | my_games
     * @param limit 	= WP_Query posts_per_page | -1 by default
     * @param details_* = Meta Query search filter
     * @param category 	= category slug
     *
     * @return void
     */
    function function_cws_games_gameslist($atts = [])
    {

    	if (isset($_GET['type']) && $_GET['type'] != '') {
    		$atts['type'] = $_GET['type'];
    	}

    	$source = 'paid';

    	$template = 'gameslist';

    	if (isset($atts['tpl']) && $atts['tpl'] != '') {
    		$template = 'gameslist-tpl-' . $atts['tpl'];
    	}

    	if (isset($atts['gametype']) && $atts['gametype'] != '') {
    		$source = $atts['gametype'];
    	}

    	if (isset($atts['per_row']) && $atts['per_row'] != '') {
    		
    		if ($atts['per_row'] < 2) {
    			$atts['per_row'] = 2;
    		}

    		if ($atts['per_row'] > 8) {
    			$atts['per_row'] = 8;
    		}
    		
    	}

    	$CWS_GamesConfig 		= new CWS_GamesConfig();
    	$CWS_GamesGameslist 	= new CWS_GamesGameslist();
    	$CWS_GamesMostPlayed 	= new CWS_GamesMostPlayed();

    	$sign_in_url = home_url('/login/');

    	if ($CWS_GamesConfig->getSignInPageUrl() != '') {
    		$sign_in_url = $CWS_GamesConfig->getSignInPageUrl();
    	}

    	set_query_var('sign_in_url', $sign_in_url);

    	$params = $load_more_params = [];

    	$params['meta_query']['relation'] = 'AND';

    	if (isset($atts['type']) && $atts['type'] != '') {

    		$load_more_params['type'] = $atts['type'];

    		if ($atts['type'] == 'most_played') {

    		}

    		if ($atts['type'] == 'last_played') {

    		}

    		if ($atts['type'] == 'my_games') {
    			$userGames = get_user_meta(get_current_user_id(), 'my_games', true);

		    	if ($userGames == '') {
		    		$userGames = [];
		    	} else {
		    		$userGames = explode(',', $userGames);
		    	}

    			if (empty($userGames)) {
    				$params['meta_query'][] = array(
						'key'		=> 'game_guid',
						'value'		=> ['secret-guid'],
						'compare'	=> 'IN'
					);
    			} else {
    				$params['meta_query'][] = array(
						'key'		=> 'game_guid',
						'value'		=> $userGames,
						'compare'	=> 'IN'
					);
    			}
    			
    		}
    	}

    	if (is_array($atts)) {
    		foreach ($atts as $key => $value) {
	    		if (strpos($key, 'details_') !== false) {
	    			$key_array = explode('_', $key);

	    			$load_more_params[$key] = $value;

	    			$params['meta_query'][] = [
	    				'key'		=> 'details',
	    				'value'		=> ucfirst($key_array[1]) . ': ' . $value,
	    				'compare' 	=> 'LIKE'
	    			];
	    		}
	    	}
    	}

    	if (isset($atts['category']) && $atts['category'] != '') {
    		$load_more_params['category'] = $atts['category'];

    		$params['tax_query'] = [
    			[
    				'taxonomy' 	=> 'game_category',
    				'field'		=> 'slug',
    				'terms'		=> $atts['category']
    			]
    		];
    	}
    	

    	if (isset($atts['limit']) && $atts['limit'] != '') {
    		$load_more_params['limit'] = $atts['limit'];

    		$params['posts_per_page'] = $atts['limit'];
    	}

    	if (isset($_GET['formdata']['game_name']) && $_GET['formdata']['game_name'] != '') {
    		$params['s'] = $_GET['formdata']['game_name'];
    	}

    	$params['meta_query'][] = [
    		'key' 		=> 'source',
    		'value' 	=> $source,
    		'compare' 	=> '='
    	];

    	if (isset($atts['tpl']) && $atts['tpl'] == 'loadmore') {
    		$params['posts_per_page'] = 11;
    	}

    	if (isset($atts['loadmore_count']) && $atts['loadmore_count'] != '') {
    		$params['posts_per_page'] = $atts['loadmore_count'];
    	}

    	if (isset($atts['offset']) && $atts['offset'] != '') {
    		$load_more_params['offset'] = $atts['offset'];

    		$params['offset'] = $atts['offset'];
    	}

		$games = $CWS_GamesGameslist->FindByParams($params);

		// This is only for the 'loadmore' shortcode
		if (isset($atts['tpl']) && $atts['tpl'] == 'loadmore') {
			unset($params['offset']);
			$params['posts_per_page'] = -1;

			$total_games = $CWS_GamesGameslist->FindByParams($params);

			if (!empty($total_games)) {
				$total_games_count = count($total_games);

				$show_load_more_button = true;

				$offset = isset($atts['offset']) && $atts['offset'] != '' ? intval($atts['offset']) : 0;

				$shown_games_count = count($games) ?? 0;

				if (intval($total_games_count) <= (intval($offset) + intval($shown_games_count))) {
					$show_load_more_button = false;
				}

				set_query_var('show_load_more_button', $show_load_more_button);
			}
		}

		if (!empty($games)) {

			if (isset($userGames)) {
				set_query_var('userGames', $userGames);
			}
	    	
			set_query_var('gametype', $source);
			set_query_var('games', $games);
			set_query_var('shortcode_atts', $atts);

			if (isset($atts['tpl']) && $atts['tpl'] == 'loadmore') {
				set_query_var('load_more_params', $load_more_params);
			}

			ob_start();

	        load_template(
	            CWS_GAMES_ABSPATH_TEMPLATE . '/frontend/shortcodes/' . $template . '.php', false
	        );

	        $content = ob_get_contents();
			ob_end_clean();

			echo $content;
		}
		
    }
}

function function_cws_games_gameslist_filters($atts)
{
	$template = 'cws-games-gameslist-filters';

	if (isset($atts['tpl']) && $atts['tpl'] != '' && file_exists(CWS_GAMES_ABSPATH_TEMPLATE . '/frontend/shortcodes/cws-games-gameslist-filters-'.$atts['tpl'].'.php')) {
		$template .= '-'.$atts['tpl'];
	}

	$template .= '.php';

	$override_path = '/cws-games/templates/' . $template;

	$_template = CWS_GAMES_ABSPATH_TEMPLATE . '/frontend/shortcodes/' . $template;

	if (file_exists(get_stylesheet_directory() . $override_path)) {
		$_template = get_stylesheet_directory() . $override_path;
	} elseif (file_exists(get_template_directory() . $override_path)) {
		$_template = get_template_directory() . $override_path;
	}

	set_query_var('shortcode_atts', $atts);
	set_query_var('get', $_GET);

	ob_start();

    load_template(
        $_template, false
    );

    $content = ob_get_contents();
	ob_end_clean();

	echo $content;
}

function function_cws_games_progress_bar($atts)
{
	if (is_user_logged_in()) {
		$CWS_BackofficeAPI = new CWS_BackofficeAPI();

		$user_id = get_current_user_id();

		$playerId = get_user_meta($user_id, 'playerId', true);

		$data = [
			'playerId' => $playerId
		];

		$response = $CWS_BackofficeAPI->getPlayer( $data );

		$levels = [];

		__sd($response, 'GET PLAYER RESPONSE');

		if (isset($response['code']) && $response['code'] == 200) {
			if (isset($response['data']['levels']) && !empty($response['data']['levels'])) {

				foreach ($response['data']['levels'] as $key => $level) {
					if (isset($level['progress']) && $level['progress'] == 1) {
						if (file_exists(get_stylesheet_directory() . '/cws-games/images/level-' . $key . '.png')) {
							$level['image'] = get_stylesheet_directory_uri() . '/cws-games/images/level-' . $key . '.png';
						} elseif (file_exists(get_template_directory() . '/cws-games/images/level-' . $key . '.png')) {
							$level['image'] = get_template_directory_uri() . '/cws-games/images/level-' . $key . '.png';
						} elseif (file_exists(CWS_GAMES_ASSETS . '/images/level-' . $key . '.png')) {
							$level['image'] = CWS_GAMES_ABSPATH_ASSETS . '/images/level-' . $key . '.png';
						}

						$levels[$key] = $level;
					}
				}
			}
		}

		if (isset($atts['level']) && $atts['level'] != '') {
			if (array_key_exists($atts['level'], $levels)) {
				set_query_var( 'level', $levels[$atts['level']] );
				set_query_var( 'key', $atts['level'] );
			} elseif ($atts['level'] == 'current') {
				foreach ($levels as $key => $level) {
					if (isset($level['score']) && floatval($level['score']) > 0 && floatval($level['score']) < 100) {
						set_query_var( 'level', $level );
						set_query_var( 'key', $key );
						break;
					}
				}
			}

		} else {
			set_query_var( 'levels', $levels );
		}

		if (!empty($levels)) {

			if (isset($atts['level']) && $atts['level'] != '') {

				$override_path = '/cws-games/templates/cws-games-progress-bar.php';

				$template = CWS_GAMES_ABSPATH_TEMPLATE . '/frontend/shortcodes/cws-games-progress-bar.php';

				if (file_exists(get_stylesheet_directory() . $override_path)) {
					$template = get_stylesheet_directory() . $override_path;
				} elseif (file_exists(get_template_directory() . $override_path)) {
					$template = get_template_directory() . $override_path;
				}

				ob_start();

				load_template(
			        $template, false
			    );

			    $content = ob_get_contents();
				ob_end_clean();

				echo $content;

			} else {

				$override_path = '/cws-games/templates/cws-games-progress-bars.php';

				$template = CWS_GAMES_ABSPATH_TEMPLATE . '/frontend/shortcodes/cws-games-progress-bars.php';

				if (file_exists(get_stylesheet_directory() . $override_path)) {
					$template = get_stylesheet_directory() . $override_path;
				} elseif (file_exists(get_template_directory() . $override_path)) {
					$template = get_template_directory() . $override_path;
				}
				
				ob_start();

				load_template(
			        $template, false
			    );

			    $content = ob_get_contents();
				ob_end_clean();

				echo $content;

			}
		}
	}
}

function function_cws_games_wheel_progress_bar($atts)
{
	if (is_user_logged_in()) {
		$CWS_BackofficeAPI = new CWS_BackofficeAPI();

		$user_id = get_current_user_id();

		$playerId = get_user_meta($user_id, 'playerId', true);

		$data = [
			'playerId' => $playerId
		];

		$response = $CWS_BackofficeAPI->getWheelOfFortune( $data );
		$getPlayer = $CWS_BackofficeAPI->getPlayer( $data );

		__sd($response, "getWheelOfFortune RESPONSE");

		$wheel_segments = [];
		$show_wheel 	= false;
		$progress 		= 0;

		if (isset($getPlayer['data']['wellOfLuck']) && $getPlayer['data']['wellOfLuck'] != '') {
			$progress = floatval($getPlayer['data']['wellOfLuck']);
		}

		if (isset($atts['image']) && $atts['image'] != '') {
			$wheel_image = $atts['image'];
		} else {
			$wheel_image = CWS_GAMES_ABSPATH_ASSETS . '/images/wheel-default.png';
		}

		if (isset($response['code']) && $response['code'] == 200) {
			if (isset($response['data']['options']) && !empty($response['data']['options'])) {
				$wheel_segments = $response['data']['options'];
			}

			if ($progress >= 100) {
				$show_wheel = true;
			}

			set_query_var( 'wheel_image', $wheel_image );
			set_query_var( 'wheel_segments', $wheel_segments );
			set_query_var( 'show_wheel', $show_wheel );
			set_query_var( 'progress', $progress );

			$override_path = '/cws-games/templates/cws-games-wheel-progress-bar.php';

			$template = CWS_GAMES_ABSPATH_TEMPLATE . '/frontend/shortcodes/cws-games-wheel-progress-bar.php';

			if (file_exists(get_stylesheet_directory() . $override_path)) {
				$template = get_stylesheet_directory() . $override_path;
			} elseif (file_exists(get_template_directory() . $override_path)) {
				$template = get_template_directory() . $override_path;
			}
			
			ob_start();

			load_template(
		        $template, false
		    );

		    $content = ob_get_contents();
			ob_end_clean();

			echo $content;
		}

	}
}

function function_cws_games_product_list($atts)
{	
	$data 		= array();
	$products 	= array();

	$args = array(
		'post_type' 	=> 'product',
		'post_status' 	=> 'publish'
	);

	if (isset($atts['category']) && $atts['category'] != '') {
		$args['tax_query'] = array(
			array(
				'taxonomy' 	=> 'product_cat',
				'field' 	=> 'slug',
				'terms' 	=> $atts['category']
			)
		);

		$term = get_term_by( 'slug', $atts['category'], 'product_cat' );

		if (isset($term->name) && $term->name != '') {
			$data['title'] = $term->name;
		}
	}

	$CWS_GamesConfig 	= new CWS_GamesConfig();
	$currencies  		= $CWS_GamesConfig->getCurrencies();

	$query = new WP_Query($args);

	if ($query->have_posts()) {

		foreach ($query->posts as $post) {
			$product = wc_get_product($post->ID);

			if ($product) {
				$prepare_product = array(
					'product_id' 	=> $product->get_id(),
					'product_name' 	=> $product->get_name(),
					'product_type' 	=> $product->get_type(),
					'price' 		=> $product->get_price() ?? '',
				);

				$currenciesForScreen = [];

				if (!empty($currencies)) {
					foreach ($currencies as $currency) {
						$unique_id = 'cws_games_product_purchase_'.strtolower($currency['currency_code'] ?? '').'_amount';

						$value = get_post_meta($product->get_id(), $unique_id, true);

						if ($value != '') {
							$unique_id_order = 'cws_games_product_purchase_'.strtolower($currency['currency_code'] ?? '').'_order';

							$order = get_post_meta($product->get_id(), $unique_id_order, true) ?? 1;

							$currenciesForScreen[strtolower($currency['currency_code'])] = [
								'amount' 		=> $value,
								'currency_code' => $currency['currency_code'],
								'order' 		=> $order
							];

							// $prepare_product['currencies'][strtolower($currency['currency_code'])] = $value;
						}
					}
				}

				if (!empty($currenciesForScreen)) {
					usort($currenciesForScreen, function($a, $b) {
						return floatval($a['order']) - floatval($b['order']);
					});

					foreach ($currenciesForScreen as $currency) {
						$prepare_product['currencies'][strtolower($currency['currency_code'])] = $currency['amount'];
					}
				}

				$products[] = (object) $prepare_product;
			}
		}

		set_query_var('data', (object) $data);
		set_query_var('products', $products);

		$template = 'modal-products-list.php';

		if (isset($atts['tmpl']) && $atts['tmpl'] != '' && file_exists(CWS_GAMES_ABSPATH_TEMPLATE . '/frontend/shortcodes/modal-products-list-'.$atts['tmpl'].'.php')) {
			$template = 'modal-products-list-'.$atts['tmpl'].'.php';
		}

		ob_start();

	    load_template(
	        CWS_GAMES_ABSPATH_TEMPLATE . '/frontend/shortcodes/'.$template, false
	    );

	    $content = ob_get_contents();
		ob_end_clean();

		// return $content;
		echo $content;
	}
}

function function_login_reward_modal()
{
	ob_start();

    load_template(
        CWS_GAMES_ABSPATH_TEMPLATE . '/frontend/login-reward-modal.php', false
    );

    $content = ob_get_contents();
	ob_end_clean();

	echo $content;
}

function function_register_reward_modal()
{
	ob_start();

    load_template(
        CWS_GAMES_ABSPATH_TEMPLATE . '/frontend/register-reward-modal.php', false
    );

    $content = ob_get_contents();
	ob_end_clean();

	echo $content;
}

function prepare_modal($atts)
{	
	set_query_var('atts', $atts);

	ob_start();

    load_template(
        CWS_GAMES_ABSPATH_TEMPLATE . '/frontend/site-modal.php', false
    );

    $content = ob_get_contents();
	ob_end_clean();

	return $content;
}

function function_cws_games_mini_wallet($atts)
{	
	if (is_user_logged_in()) {
		$user_id = get_current_user_id();

		if ($user_id) {
			$CWS_GamesUsers = new CWS_GamesUsers();

			$CWS_GamesUsers->getUserWallet($user_id);
			

			$CWS_GamesConfig = new CWS_GamesConfig();

			$balance 		= [];
			$walletBalance 	= get_user_meta($user_id, 'walletBallance', true);
			$currencies 	= $CWS_GamesConfig->getCurrencies();

			if ($walletBalance) {
				$balance = json_decode($walletBalance);
			}

			$default_currency = null;

			if ($default_currency === null) {
			    $default_currency 	= $CWS_GamesConfig->getDefaultCurrency($user_id)['currency_code'];
			}

			if (!empty($balance)) {

				if ($default_currency === null || $default_currency == '') {
					foreach ($balance as $currency_balance) {
						if (isset($currency_balance->currency_default) && $currency_balance->currency_default == 1) {
							$default_currency = $currency_balance->currency;
						}
					}
				}
			}

			set_query_var('shortcode_atts', $atts);
			set_query_var('balance', $balance);
			set_query_var('default_currency', $default_currency);
			set_query_var('currencies', $currencies);

			$notifications_count = 0;

			$cws_games_notifications = get_user_meta($user_id, 'cws_games_notifications', true);

			if ($cws_games_notifications) {
				$cws_games_notifications_array = explode(',', $cws_games_notifications);

				if (is_array($cws_games_notifications_array) && !empty($cws_games_notifications_array)) {
					$notifications_count = count($cws_games_notifications_array);
				}
			}

			$template = 'cws-games-mini-wallet.php';

			if (isset($atts['tmpl']) && $atts['tmpl'] != '') {
				$template = 'cws-games-mini-wallet-'.$atts['tmpl'].'.php';
			}

			$override_path = '/cws-games/templates/'.$template;

			$template = CWS_GAMES_ABSPATH_TEMPLATE . '/frontend/shortcodes/'.$template;

			if (file_exists(get_stylesheet_directory() . $override_path)) {
				$template = get_stylesheet_directory() . $override_path;
			} elseif (file_exists(get_template_directory() . $override_path)) {
				$template = get_template_directory() . $override_path;
			}

			set_query_var('notifications_count', $notifications_count);

			ob_start();

		    load_template(
		        $template, false
		    );

		    $content = ob_get_contents();
			ob_end_clean();

			// return $content;
			echo $content;
		}
	}
}

function function_cws_games_wallet_transactions($atts)
{
	if (is_user_logged_in()) {
		$user_id = get_current_user_id();

		$CWS_GamesUsers = new CWS_GamesUsers();

		$template = 'cws-games-wallet-transactions';

		if (isset($atts['tpl']) && $atts['tpl'] != '') {
			$template .= '-' . $atts['tpl'];

			$transactions = $CWS_GamesUsers->getUserWalletTransactions($user_id);

			if ($transactions) {
				set_query_var('transactions', $transactions);
			}
		}

		$balance 		= [];
		$walletBalance 	= get_user_meta($user_id, 'walletBallance', true);

		if ($walletBalance) {
			$balance = json_decode($walletBalance);
		}

		set_query_var('balance', $balance);

		ob_start();

	    load_template(
	        CWS_GAMES_ABSPATH_TEMPLATE . '/frontend/shortcodes/' . $template . '.php', false
	    );

	    $content = ob_get_contents();
		// ob_end_clean();

		return $content;
	}
	
}

function function_cws_games_notifications($atts)
{
	if (is_user_logged_in()) {
		$user_id = get_current_user_id();

		$data = [];

		global $wp_query;

		if (isset($wp_query->query['notifications']) && $wp_query->query['notifications'] != '') {
			preg_match('/^page\/(\d+)$/', $wp_query->query['notifications'], $matches);

			if ($matches) {
				$data['page'] = $matches[1];
			}
		}

		$CWS_GamesUsers = new CWS_GamesUsers();

		$notifications = $CWS_GamesUsers->getUserNotifications($user_id, $data);

		__sd($notifications, "USER NOTIFICATIONS");

		if ($notifications) {
			set_query_var('notifications', $notifications);
		}

		ob_start();

	    load_template(
	        CWS_GAMES_ABSPATH_TEMPLATE . '/frontend/shortcodes/cws-games-notifications.php', false
	    );

	    $content = ob_get_contents();
		// ob_end_clean();

		return $content;
	}
}

function function_cws_games_redeemable_coins($atts)
{
	if (is_user_logged_in()) {
		$user_id = get_current_user_id();

		$balance 				= [];
		$redeemable_coins 		= [];
		$walletBalance 			= get_user_meta($user_id, 'walletBallance', true);

		if ($walletBalance) {
			$_balance = json_decode($walletBalance);

			if (isset($_balance->virtual) && !empty($_balance->virtual)) {
				$balance = $_balance->virtual;
			}

			if (isset($_balance->redeemable) && !empty($_balance->redeemable)) {
				$redeemable_coins = $_balance->redeemable;
			}
		}

		set_query_var('balance', $balance);
		set_query_var('redeemable_coins', $redeemable_coins);

		ob_start();

	    load_template(
	        CWS_GAMES_ABSPATH_TEMPLATE . '/frontend/shortcodes/cws-games-redeemable-coins.php', false
	    );

	    $content = ob_get_contents();
		// ob_end_clean();

		return $content;
	}
}

function function_cws_games_jackpot($atts)
{
	$options = get_option('cws_games_plugin');

	$jackpot = isset($options['jackpot']) && $options['jackpot'] != '' ? $options['jackpot'] : 0;

	set_query_var('jackpot', $jackpot);

	ob_start();

    load_template(
        CWS_GAMES_ABSPATH_TEMPLATE . '/frontend/shortcodes/cws-games-jackpot.php', false
    );

    $content = ob_get_contents();
	ob_end_clean();

	echo $content;
}

function function_cws_games_redeem_button($atts)
{
	$user_id = get_current_user_id();

	$balance 				= [];
	$redeemable_coins 		= [];
	$walletBalance 			= get_user_meta($user_id, 'walletBallance', true);

	if ($walletBalance) {
		$_balance = json_decode($walletBalance);

		if (isset($_balance->virtual) && !empty($_balance->virtual)) {
			$balance = $_balance->virtual;
		}

		if (isset($_balance->redeemable) && !empty($_balance->redeemable)) {
			$redeemable_coins = $_balance->redeemable;
		}
	}

	set_query_var('redeemable_coins', $redeemable_coins);

	ob_start();

    load_template(
        CWS_GAMES_ABSPATH_TEMPLATE . '/frontend/shortcodes/cws-games-redeem-button.php', false
    );

    $content = ob_get_contents();
	ob_end_clean();

	echo $content;
}

function function_cws_games_sound_toggle($atts)
{
	$user_id = get_current_user_id();

	$sound = get_user_meta($user_id, 'sound', true) ?? 1;

	set_query_var('user_id', $user_id);
	set_query_var('sound', $sound);

	ob_start();

    load_template(
        CWS_GAMES_ABSPATH_TEMPLATE . '/frontend/shortcodes/cws-games-sound-toggle.php', false
    );

    $content = ob_get_contents();
	ob_end_clean();

	echo $content;
}

/* END OF SHORTCODE FUNCTIONS */


/**
 * `xoo_aff_easy-login-woocommerce_field_args` filter hook callback function
 * `xoo_el_register_fields` filter hook callback function
 */ 
function cws_games_registration_fields($args = array())
{
	// __sd($args, 'REGISTRATION FIELDS');

	foreach ($args as $field_key => $field) {
		$unique_id = $field['settings']['unique_id'] ?? '';

		// if (strpos($unique_id, 'cws_games') !== false) {
		// 	unset($args[$field_key]);
		// }
	}

	return $args;
}

/**
 * `xoo_el_process_registration_errors` filter hook callback function
 */ 
function cws_games_custom_user_registration_validation($error, $username, $password, $email) {

	if ( $username == '' ) {
		$username = md5($_POST['xoo_el_reg_email'] ?? ($email ?? ''));
	}

	/* First, we will go through the WordPress validation */
		$reg_admin_fields = xoo_el()->aff->fields->get_fields_data();

		$doNotValidateOtherFields = array_keys( array_diff_key( $reg_admin_fields, xoo_el_fields()->get_fields('register') ) );

		$fieldValues = xoo_el()->aff->fields->validate_submitted_field_values( $_POST, $doNotValidateOtherFields );

		if( is_wp_error( $fieldValues ) ) {

			return $error;
		}

		if ( !empty($email) ) {
			if ( email_exists( $email ) || !is_email( $email ) ) {
				return $error;
			}
		}

		if ( !empty($username) ) {
			if ( !validate_username( $username ) || username_exists( $username ) ) {
				return $error;
			}
		}
	/* END WordPress validation and continue to BackOffice validation*/

	$user_email = $_POST['xoo_el_reg_email'] ?? '';
	$user_pass 	= $_POST['xoo_el_reg_pass'] ?? '';
	$first_name = $_POST['xoo_el_reg_fname'] ?? '';
	$last_name  = $_POST['xoo_el_reg_lname'] ?? '';

	$data = [];

	if ($username) { $data['username'] = $username; }

	if ($user_email) { $data['email'] = $user_email; }

	// if ($user_pass) { $data['password'] = $user_pass; }
	if ($user_pass) { $data['password'] = base64_encode($user_pass); }

	if ($first_name) { $data['first_name'] = $first_name; }

	if ($last_name) { $data['second_name'] = $last_name; }

	foreach ($_POST as $unique_id => $value) {
		if (strpos($unique_id, 'cws_games_') !== false) {

			if ($value != '') {

				if (strpos($unique_id, 'country') !== false) {
					if (class_exists('WooCommerce')) {
						$country_code = $value;

						if ( isset(WC()->countries->countries[$country_code]) ) {
							$country_name = WC()->countries->countries[$country_code];

							$data['country'] 		= $country_name;
							$data['country_code'] 	= $country_code;
						}

					} else {
						$key = str_replace('cws_games_', '', $unique_id);
						$data[$key] = $value;
					}

				} else {
					$key = str_replace('cws_games_', '', $unique_id);
					$data[$key] = $value;
				}
			}
		}
	}

	$CWS_GamesUsers = new CWS_GamesUsers();

	$response = $CWS_GamesUsers->addUser($data);

	if (isset($response['error']) && is_wp_error($response['error'])) {
		$error = $response['error'];
	}

	return $error;
}

/**
 * `xoo_el_created_customer` action hook callback function
 */ 
function cws_games_created_customer($customer_id, $customer_data)
{	
	/**
	 * This is executed after xoo_el_process_registration_errors, which triggers BackOffice addPlayer API request. If the validaation whould have been successfull, check the session and update the new customer's usermeta playerId value
	 */
	if(session_id() == '' || !isset($_SESSION) || session_status() === PHP_SESSION_NONE) {
	    // session isn't started
	    session_start();
	}

	if (isset($_SESSION['new_customer_playerId']) && $_SESSION['new_customer_playerId'] != '') {
		update_user_meta($customer_id, 'playerId', $_SESSION['new_customer_playerId']);

		unset($_SESSION['new_customer_playerId']);

		update_user_meta($customer_id, 'show_user_register_modal', 1);
	}
}

/**
 * `woocommerce_save_account_details` action hook callback function
 */ 
function cws_games_update_customer($user_id, $user_data, $userdata_raw)
{
	$user_login = $user_data['display_name'] ?? '';
	$user_email = $user_data['user_email'] ?? '';
	$user_pass 	= $userdata_raw['user_pass'] ?? '';
	$first_name = $user_data['first_name'] ?? '';
	$last_name  = $user_data['last_name'] ?? '';
	$playerId   = get_user_meta($user_id, 'playerId', true);

	$data = [];

	if ($user_login) { $data['username'] = $user_login; }

	if ($user_email) { $data['email'] = $user_email; }

	// if ($user_pass) { $data['password'] = $user_pass; }
	if ($user_pass) { $data['password'] = base64_encode($user_pass); }

	if ($first_name) { $data['first_name'] = $first_name; }

	if ($last_name) { $data['second_name'] = $last_name; }

	if ($playerId) { $data['playerId'] = $playerId; }

	$CWS_GamesUsers = new CWS_GamesUsers();

	$CWS_GamesUsers->updateUser($user_id, $data);
	
}

/**
 * `wp_update_user` action hook callback function
 */ 
function cws_games_save_account_details($user_id)
{
	$playerId = get_user_meta($user_id, 'playerId', true);

	$data = [];

	if ($playerId) { $data['playerId'] = $playerId; }

	$fields = xoo_el_fields()->get_fields('myaccount');

	if ($fields) {
		// __sd($fields, 'REGISTRATION FIELDS');

		foreach ($fields as $field_key => $field) {
			$unique_id = $field['settings']['unique_id'] ?? '';

			if (strpos($unique_id, 'cws_games_') !== false) {
				$user_meta_value = get_user_meta($user_id, $unique_id, true);

				if ($user_meta_value != '') {

					if (strpos($unique_id, 'country') !== false) {
						if (class_exists('WooCommerce')) {
							$country_code = $user_meta_value;

							if ( isset(WC()->countries->countries[$country_code]) ) {
								$country_name = WC()->countries->countries[$country_code];

								$data['country'] 		= $country_name;
								$data['country_code'] 	= $country_code;
							}

						} else {
							$key = str_replace('cws_games_', '', $unique_id);
							$data[$key] = $user_meta_value;
						}

					} else {
						$key = str_replace('cws_games_', '', $unique_id);
						$data[$key] = $user_meta_value;
					}
				}
			}
		}
	}

	$CWS_GamesUsers = new CWS_GamesUsers();

	$CWS_GamesUsers->updateUser($user_id, $data);
}

/**
 * `xoo_el_process_login_errors` filter hook callback function
 */ 
function cws_games_user_process_login($error, $creds)
{	
	$data = [];

	$data['email'] 		= $creds['user_login'] ?? '';
	// $data['password'] 	= $creds['user_password'] ?? '';
	$data['password'] 	= base64_encode($creds['user_password'] ?? '');

	$creds = array(
		'user_login' 	=> sanitize_user( trim( $data['email'] ) ),
		'user_password' => base64_decode($data['password']),
		'remember' 		=> false
	);

	$is_admin = false;

	// Perform the login
	$user = wp_signon( $creds, is_ssl() );

	if (!is_wp_error($user)) {
		if (isset($user->roles) && !empty($user->roles)) {
			$user_id = $user->ID;

			if (in_array('administrator', $user->roles)) {
				$is_admin = true;
			}
		}

		if (isset($user->data->user_email) && $user->data->user_email != '') {
			$data['email'] = $user->data->user_email;
		}
	}

	$CWS_GamesUsers = new CWS_GamesUsers();

	$login = $CWS_GamesUsers->loginUser($data);

	if (is_wp_error($login) && !$is_admin) {
		$error = $login;
	}

	return $error;
}

/**
 * `xoo_el_login_success` action hook callback function
 */ 
function cws_games_user_login_success($user)
{
	$user_id = $user->ID ?? 0;

	$CWS_GamesUsers = new CWS_GamesUsers();

	$CWS_GamesUsers->getUserWallet($user_id);
}

/**
 * `send_password_change_email` filter hook callback function
 */
function cws_games_send_password_change_email($send, $user, $userdata) {

	$send = false;

	return $send;
}

function cws_games_woo_wallet_enpoint()
{
	echo do_shortcode("[cws_games_wallet_transactions tpl='base']");
}

function cws_games_woo_wallet_transactions_endpoint()
{
	echo do_shortcode("[cws_games_wallet_transactions]");
}

function cws_games_notifications_endpoint()
{
	echo do_shortcode("[cws_games_notifications]");
}

function cws_games_redeemable_coins_endpoint()
{
	echo do_shortcode("[cws_games_redeemable_coins]");
}

/**
 * `woocommerce_after_checkout_validation` action hook callback function
 */
function cws_games_woocommerce_after_checkout_validation($data, $errors)
{
	// echo '<pre>'.print_r($data, true).'</pre>';
	// echo '<pre>'.print_r($errors, true).'</pre>';
	// exit;

	$CWS_GamesConfig    = new CWS_GamesConfig();
    $currencies         = $CWS_GamesConfig->getCurrencies();

	$_data = [];

	$user_id = get_current_user_id();

	$_data['order']['user_id'] = $user_id;

	if ($user_id) {
		$_data['playerId'] = get_user_meta( $user_id, 'playerId', true );
	}

	$_data['order']['billing_first_name'] 	= $data['billing_first_name'] ?? '';
	$_data['order']['billing_last_name'] 	= $data['billing_last_name'] ?? '';
	$_data['order']['billing_address_1'] 	= $data['billing_address_1'] ?? '';
	$_data['order']['billing_address_2'] 	= $data['billing_address_2'] ?? '';
	$_data['order']['billing_city'] 		= $data['billing_city'] ?? '';
	$_data['order']['billing_state'] 		= $data['billing_state'] ?? '';
	$_data['order']['billing_postcode'] 	= $data['billing_postcode'] ?? '';
	$_data['order']['billing_country'] 		= $data['billing_country'] ?? '';
	$_data['order']['billing_email'] 		= $data['billing_email'] ?? '';
	$_data['order']['billing_phone'] 		= $data['billing_phone'] ?? '';

	$_data['order']['shipping_first_name'] 	= $data['shipping_first_name'] ?? '';
	$_data['order']['shipping_last_name'] 	= $data['shipping_last_name'] ?? '';
	$_data['order']['shipping_address_1'] 	= $data['shipping_address_1'] ?? '';
	$_data['order']['shipping_address_2'] 	= $data['shipping_address_2'] ?? '';
	$_data['order']['shipping_city'] 		= $data['shipping_city'] ?? '';
	$_data['order']['shipping_state'] 		= $data['shipping_state'] ?? '';
	$_data['order']['shipping_postcode'] 	= $data['shipping_postcode'] ?? '';
	$_data['order']['shipping_country'] 	= $data['shipping_country'] ?? '';

	$shipping_methods = WC()->shipping->get_shipping_methods();

	$selected_shipping_method = reset($data['shipping_method']);
	$_selected_shipping_method = explode(':', $selected_shipping_method);
	$selected_shipping_method = $_selected_shipping_method[0];

	if ($selected_shipping_method && isset($shipping_methods[$selected_shipping_method])) {
		$_data['order']['shipping_method'] = $shipping_methods[$selected_shipping_method]->method_title;
	}

	$chosen_payment_method = WC()->session->get('chosen_payment_method') ?? '';

	if ($chosen_payment_method) {
		$payment_methods = WC()->payment_gateways->get_available_payment_gateways();

		if ($payment_methods && isset($payment_methods[$chosen_payment_method])) {
			$_data['order']['payment_method'] = $payment_methods[$chosen_payment_method]->title;
		}
	}

	if (WC()->cart) {
		$_data['order']['order_total'] 			= WC()->cart->total ?? '';
		$_data['order']['order_subtotal'] 		= WC()->cart->subtotal;
		$_data['order']['order_total_discount'] = WC()->cart->get_discount_total();
		$_data['order']['currency'] 			= get_woocommerce_currency();

		foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
			$product = $cart_item['data'];

			$prepareItem = [];

			$product_id = $product->get_id();

			$prepareItem['product_id'] 		= $product_id;
			$prepareItem['product_name'] 	= $product->get_name();
			$prepareItem['quantity'] 		= $cart_item['quantity'];

			$extra_data = [];

			if (!empty($currencies)) {
				foreach ($currencies as $currency) {
					$unique_id = 'cws_games_product_purchase_'.strtolower($currency['currency_code'] ?? '').'_amount';

					$value = get_post_meta($product_id, $unique_id, true);

					if ($value != '') {
						$amount = intval($value) ?? 0;

						$extra_data['virtual_coins'][$currency['currency_code']] = $amount;
					}
				}
			}

			$attributes = $product->get_attributes();

			if ($attributes && !empty($attributes)) {
				foreach ($attributes as $attribute_name => $attribute_value) {
					if ( !is_object($attribute_value) ) {
						$extra_data['attributes'][$attribute_name] = $attribute_value;
 					}
				}
			}

			$prepareItem['extra_data'] = $extra_data;

			$_data['order']['items'][] = $prepareItem;
		}

		$_data['order']['coupon'] = '';
		$_data['order']['redeemableCoupon'] = '';
		$_data['order']['couponAmount'] = '';

		$coupons = WC()->cart->get_coupons();

		if (!empty($coupons)) {
			foreach ($coupons as $coupon) {
				$coupon_id = $coupon->get_id() ?? 0;

				if ($coupon_id) {
					$redeemable_coupon = get_post_meta($coupon_id, 'redeemable_coupon', true);

					if ($redeemable_coupon) {
						$coupon_code = get_post_meta($coupon_id, 'og_coupon_code', true);

						$_data['order']['redeemableCoupon'] = $coupon_code;
						$_data['order']['couponAmount'] = WC()->cart->get_discount_total();
					} else {
						$coupon_data = $coupon->get_data();

						if (isset($coupon_data['code']) && $coupon_data['code'] != '') {
							$_data['order']['coupon'] = $coupon_data['code'];
							$_data['order']['couponAmount'] = WC()->cart->get_discount_total();
						}
					}
				}
			}
		}
	}

	$CWS_BackofficeAPI = new CWS_BackofficeAPI();

	__sd($_data , "validatePreOrder data" );

	$response = $CWS_BackofficeAPI->validatePreOrder($_data);

	__sd($response , "validatePreOrder response" );

	if (isset($response['code']) && $response['code'] != 200) {
		$error_messages = [];

		if (!empty($response['errors'])) {
			if (isset($response['errors']['message']) && $response['errors']['message'] != '') {
				$error_messages['order-validation'] = $response['errors']['message'];
			}

			foreach ($response['errors'] as $key => $error_message) {
				if (!in_array($key, array('code', 'message'))) {
					$error_messages[$key] = $error_message;
				}
			}
		} elseif (isset($response['data']['message']) && $response['data']['message'] != '') {
			$error_messages['order-validation'] = $response['data']['message'];
		}

		if (!empty($error_messages)) {
			foreach ($error_messages as $error => $message) {
				$errors->add($key, $message);
			}
		}
	}

	// $errors->add('debug', 'debug');

}

/**
 * `woocommerce_new_order` action hook callback function
 */
function cws_games_woocommerce_new_order($order_id, $order)
{
	$CWS_GamesConfig    = new CWS_GamesConfig();
    $currencies         = $CWS_GamesConfig->getCurrencies();

	$data = [];

	if ($order) {
		$data['orderId'] 						= $order->get_id();
		$data['order']['order_id'] 				= $order->get_id();
		$data['order']['order_total'] 			= $order->get_total();
		$data['order']['order_subtotal'] 		= $order->get_subtotal();
		$data['order']['order_total_discount'] 	= $order->get_total_discount();
		$data['order']['order_total_tax'] 		= $order->get_total_tax();
		$data['order']['currency'] 				= $order->get_currency();

		$user_id = $order->get_user_id();

		$data['order']['user_id'] 				= $user_id;

		$data['playerId'] 						= get_user_meta( $user_id, 'playerId', true );

		$data['order']['billing_first_name'] 	= $order->get_billing_first_name();
		$data['order']['billing_last_name'] 	= $order->get_billing_last_name();
		$data['order']['billing_address_1'] 	= $order->get_billing_address_1();
		$data['order']['billing_address_2'] 	= $order->get_billing_address_2();
		$data['order']['billing_city'] 			= $order->get_billing_city();
		$data['order']['billing_state'] 		= $order->get_billing_state();
		$data['order']['billing_postcode'] 		= $order->get_billing_postcode();
		$data['order']['billing_country'] 		= $order->get_billing_country();
		$data['order']['billing_email'] 		= $order->get_billing_email();
		$data['order']['billing_phone'] 		= $order->get_billing_phone();

		$data['order']['shipping_first_name'] 	= $order->get_shipping_first_name();
		$data['order']['shipping_last_name'] 	= $order->get_shipping_last_name();
		$data['order']['shipping_address_1'] 	= $order->get_shipping_address_1();
		$data['order']['shipping_address_2'] 	= $order->get_shipping_address_2();
		$data['order']['shipping_city'] 		= $order->get_shipping_city();
		$data['order']['shipping_state'] 		= $order->get_shipping_state();
		$data['order']['shipping_postcode'] 	= $order->get_shipping_postcode();
		$data['order']['shipping_country'] 		= $order->get_shipping_country();
		$data['order']['shipping_method'] 		= $order->get_shipping_to_display();
		$data['order']['payment_method'] 		= $order->get_payment_method_title();

		$data['order']['order_status'] 			= $order->get_status();

		foreach ($order->get_items() as $item_id => $item) {
			$prepareItem = [];

			$product_id = $item->get_product_id();

			$prepareItem['product_id'] 		= $product_id;
			$prepareItem['product_name'] 	= $item->get_name();
			$prepareItem['quantity'] 		= $item->get_quantity();
			$prepareItem['subtotal'] 		= $item->get_subtotal();
			$prepareItem['total'] 			= $item->get_total();

			$extra_data = [];

			if (!empty($currencies)) {
				foreach ($currencies as $currency) {
					$unique_id = 'cws_games_product_purchase_'.strtolower($currency['currency_code'] ?? '').'_amount';

					$value = get_post_meta($product_id, $unique_id, true);

					if ($value != '') {
						$amount = intval($value) ?? 0;

						$extra_data['virtual_coins'][$currency['currency_code']] = $amount;
					}
				}
			}

			$attributes = $item->get_meta_data();

			if ($attributes && !empty($attributes)) {
				foreach ($attributes as $metaData) {
					$attribute = $metaData->get_data();

					if (!empty($attribute)) {
						if (strpos($attribute['key'], 'amount') === false) {
							$extra_data['attributes'][$attribute['key'] ?? ''] = $attribute['value'] ?? '';
						}
					}
				}
			}

			$prepareItem['extra_data'] = $extra_data;

			$data['order']['items'][] = $prepareItem;
		}

		$data['order']['coupon'] = '';
		$data['order']['redeemableCoupon'] = '';
		$data['order']['couponAmount'] = '';

		$coupons = $order->get_coupon_codes();

		if (!empty($coupons)) {
			foreach ($coupons as $_coupon_code) {

				$args = array(
					'posts_per_page' 	=> 1,
					'post_type' 		=> 'shop_coupon',
					'meta_query' 		=> array(
						'relation' 		=> 'AND',
						array(
							'key' 		=> 'og_coupon_code',
							'value' 	=> $_coupon_code,
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
					$coupon = reset($query->posts);

					$coupon_id = $coupon->ID ?? 0;

					if ($coupon_id) {
						$redeemable_coupon = get_post_meta($coupon_id, 'redeemable_coupon', true);

						if ($redeemable_coupon) {
							$coupon_code = get_post_meta($coupon_id, 'og_coupon_code', true);

							$data['order']['redeemableCoupon'] = $coupon_code;
							$data['order']['couponAmount'] = $order->get_total_discount();
						} else {
							$coupon_data = $coupon->get_data();

							if (isset($coupon_data['code']) && $coupon_data['code'] != '') {
								$data['order']['coupon'] = $_coupon_code;
								$data['order']['couponAmount'] = $order->get_total_discount();
							}
						}
					}
				}

				
			}
		}

		$CWS_BackofficeAPI = new CWS_BackofficeAPI();

		__sd($data , "addOrUpdateOrder data" );

		$response = $CWS_BackofficeAPI->addOrUpdateOrder($data);

		__sd($response , "addOrUpdateOrder response" );

	}
}

/**
 * `woocommerce_before_order_object_save` action hook callback function
 */
function cws_games_woocommerce_before_order_object_save($order, $data_store)
{
	// echo '<pre>'.print_r($order, true).'</pre>';
	// echo '<pre>'.print_r($data_store, true).'</pre>';
	// exit;

    $CWS_GamesConfig    = new CWS_GamesConfig();
    $currencies         = $CWS_GamesConfig->getCurrencies();

	$data = [];

	if ($order) {
		$data['orderId'] 						= $order->get_id();
		$data['order']['order_id'] 				= $order->get_id();
		$data['order']['order_total'] 			= $order->get_total();
		$data['order']['order_subtotal'] 		= $order->get_subtotal();
		$data['order']['order_total_discount'] 	= $order->get_total_discount();
		$data['order']['order_total_tax'] 		= $order->get_total_tax();
		$data['order']['currency'] 				= $order->get_currency();

		$user_id = $order->get_user_id();

		$data['order']['user_id'] 				= $user_id;

		$data['playerId'] 						= get_user_meta( $user_id, 'playerId', true );

		$data['order']['billing_first_name'] 	= $order->get_billing_first_name();
		$data['order']['billing_last_name'] 	= $order->get_billing_last_name();
		$data['order']['billing_address_1'] 	= $order->get_billing_address_1();
		$data['order']['billing_address_2'] 	= $order->get_billing_address_2();
		$data['order']['billing_city'] 			= $order->get_billing_city();
		$data['order']['billing_state'] 		= $order->get_billing_state();
		$data['order']['billing_postcode'] 		= $order->get_billing_postcode();
		$data['order']['billing_country'] 		= $order->get_billing_country();
		$data['order']['billing_email'] 		= $order->get_billing_email();
		$data['order']['billing_phone'] 		= $order->get_billing_phone();

		$data['order']['shipping_first_name'] 	= $order->get_shipping_first_name();
		$data['order']['shipping_last_name'] 	= $order->get_shipping_last_name();
		$data['order']['shipping_address_1'] 	= $order->get_shipping_address_1();
		$data['order']['shipping_address_2'] 	= $order->get_shipping_address_2();
		$data['order']['shipping_city'] 		= $order->get_shipping_city();
		$data['order']['shipping_state'] 		= $order->get_shipping_state();
		$data['order']['shipping_postcode'] 	= $order->get_shipping_postcode();
		$data['order']['shipping_country'] 		= $order->get_shipping_country();
		$data['order']['shipping_method'] 		= $order->get_shipping_to_display();
		$data['order']['payment_method'] 		= $order->get_payment_method_title();

		$data['order']['order_status'] 			= $order->get_status();

		foreach ($order->get_items() as $item_id => $item) {
			$prepareItem = [];

			$product_id = $item->get_product_id();

			$prepareItem['product_id'] 		= $product_id;
			$prepareItem['product_name'] 	= $item->get_name();
			$prepareItem['quantity'] 		= $item->get_quantity();
			$prepareItem['subtotal'] 		= $item->get_subtotal();
			$prepareItem['total'] 			= $item->get_total();

			$extra_data = [];

			if (!empty($currencies)) {
				foreach ($currencies as $currency) {
					$unique_id = 'cws_games_product_purchase_'.strtolower($currency['currency_code'] ?? '').'_amount';

					$value = get_post_meta($product_id, $unique_id, true);

					if ($value != '') {
						$amount = intval($value) ?? 0;

						$extra_data['virtual_coins'][$currency['currency_code']] = $amount;
					}
				}
			}

			$attributes = $item->get_meta_data();

			if ($attributes && !empty($attributes)) {
				foreach ($attributes as $metaData) {
					$attribute = $metaData->get_data();

					if (!empty($attribute)) {
						if (strpos($attribute['key'], 'amount') === false) {
							$extra_data['attributes'][$attribute['key'] ?? ''] = $attribute['value'] ?? '';
						}
					}
				}
			}

			$prepareItem['extra_data'] = $extra_data;

			$data['order']['items'][] = $prepareItem;
		}

		$data['order']['coupon'] = '';
		$data['order']['redeemableCoupon'] = '';
		$data['order']['couponAmount'] = '';

		$coupons = $order->get_coupon_codes();

		if (!empty($coupons)) {
			foreach ($coupons as $_coupon_code) {

				$args = array(
					'posts_per_page' 	=> 1,
					'post_type' 		=> 'shop_coupon',
					'meta_query' 		=> array(
						'relation' 		=> 'AND',
						array(
							'key' 		=> 'og_coupon_code',
							'value' 	=> $_coupon_code,
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
					$coupon = reset($query->posts);

					$coupon_id = $coupon->ID ?? 0;

					if ($coupon_id) {
						$redeemable_coupon = get_post_meta($coupon_id, 'redeemable_coupon', true);

						if ($redeemable_coupon) {
							$coupon_code = get_post_meta($coupon_id, 'og_coupon_code', true);

							$data['order']['redeemableCoupon'] = $coupon_code;
							$data['order']['couponAmount'] = $order->get_total_discount();
						} else {
							$coupon_data = $coupon->get_data();

							if (isset($coupon_data['code']) && $coupon_data['code'] != '') {
								$data['order']['coupon'] = $_coupon_code;
								$data['order']['couponAmount'] = $order->get_total_discount();
							}
						}
					}
				}

				
			}
		}

		$CWS_BackofficeAPI = new CWS_BackofficeAPI();

		__sd($data , "validatePreOrder data 2" );

		$response = $CWS_BackofficeAPI->validatePreOrder($data);

		__sd($response , "validatePreOrder response 2" );

		if (isset($response['code']) && $response['code'] != 200) {
			$error_messages = [];

			if (!empty($response['errors'])) {
				if (isset($response['errors']['message']) && $response['errors']['message'] != '') {
					$error_messages['order-validation'] = $response['errors']['message'];
				}

				foreach ($response['errors'] as $key => $error_message) {
					if (!in_array($key, array('code', 'message'))) {
						$error_messages[$key] = $error_message;
					}
				}
			} elseif (isset($response['data']['message']) && $response['data']['message'] != '') {
				$error_messages['order-validation'] = $response['data']['message'];
			} elseif (isseT($response['message']) && $response['message'] != '') {
				$error_messages['order-validation'] = $response['message'];
			}

			if (!empty($error_messages)) {
				foreach ($error_messages as $error => $message) {
					$order->add_order_note( PHP_EOL . implode( PHP_EOL, $error_messages ) );

					throw new Exception( implode( PHP_EOL, $error_messages ) );
            		return false;
				}
			}
		}

	}

	return $order;
}

function cws_games_woocommerce_after_order_object_save($post_id, $post, $update)
{

	if ( !$update ) {
		return;
	}

	if ($post->post_type == 'shop_order') {
		$order = wc_get_order($post->ID);

		$CWS_GamesConfig    = new CWS_GamesConfig();
	    $currencies         = $CWS_GamesConfig->getCurrencies();

		$data = [];

		if ($order) {
			$data['orderId'] 						= $order->get_id();
			$data['order']['order_id'] 				= $order->get_id();
			$data['order']['order_total'] 			= $order->get_total();
			$data['order']['order_subtotal'] 		= $order->get_subtotal();
			$data['order']['order_total_discount'] 	= $order->get_total_discount();
			$data['order']['order_total_tax'] 		= $order->get_total_tax();
			$data['order']['currency'] 				= $order->get_currency();

			$user_id = $order->get_user_id();

			$data['order']['user_id'] 				= $user_id;

			$data['playerId'] 						= get_user_meta( $user_id, 'playerId', true );

			$data['order']['billing_first_name'] 	= $order->get_billing_first_name();
			$data['order']['billing_last_name'] 	= $order->get_billing_last_name();
			$data['order']['billing_address_1'] 	= $order->get_billing_address_1();
			$data['order']['billing_address_2'] 	= $order->get_billing_address_2();
			$data['order']['billing_city'] 			= $order->get_billing_city();
			$data['order']['billing_state'] 		= $order->get_billing_state();
			$data['order']['billing_postcode'] 		= $order->get_billing_postcode();
			$data['order']['billing_country'] 		= $order->get_billing_country();
			$data['order']['billing_email'] 		= $order->get_billing_email();
			$data['order']['billing_phone'] 		= $order->get_billing_phone();

			$data['order']['shipping_first_name'] 	= $order->get_shipping_first_name();
			$data['order']['shipping_last_name'] 	= $order->get_shipping_last_name();
			$data['order']['shipping_address_1'] 	= $order->get_shipping_address_1();
			$data['order']['shipping_address_2'] 	= $order->get_shipping_address_2();
			$data['order']['shipping_city'] 		= $order->get_shipping_city();
			$data['order']['shipping_state'] 		= $order->get_shipping_state();
			$data['order']['shipping_postcode'] 	= $order->get_shipping_postcode();
			$data['order']['shipping_country'] 		= $order->get_shipping_country();
			$data['order']['shipping_method'] 		= $order->get_shipping_to_display();
			$data['order']['payment_method'] 		= $order->get_payment_method_title();

			$data['order']['order_status'] 			= $order->get_status();

			foreach ($order->get_items() as $item_id => $item) {
				$prepareItem = [];

				$product_id = $item->get_product_id();

				$prepareItem['product_id'] 		= $product_id;
				$prepareItem['product_name'] 	= $item->get_name();
				$prepareItem['quantity'] 		= $item->get_quantity();
				$prepareItem['subtotal'] 		= $item->get_subtotal();
				$prepareItem['total'] 			= $item->get_total();

				$extra_data = [];

				if (!empty($currencies)) {
					foreach ($currencies as $currency) {
						$unique_id = 'cws_games_product_purchase_'.strtolower($currency['currency_code'] ?? '').'_amount';

						$value = get_post_meta($product_id, $unique_id, true);

						if ($value != '') {
							$amount = intval($value) ?? 0;

							$extra_data['virtual_coins'][$currency['currency_code']] = $amount;
						}
					}
				}

				$attributes = $item->get_meta_data();

				if ($attributes && !empty($attributes)) {
					foreach ($attributes as $metaData) {
						$attribute = $metaData->get_data();

						if (!empty($attribute)) {
							if (strpos($attribute['key'], 'amount') === false) {
								$extra_data['attributes'][$attribute['key'] ?? ''] = $attribute['value'] ?? '';
							}
						}
					}
				}

				$prepareItem['extra_data'] = $extra_data;

				$data['order']['items'][] = $prepareItem;
			}

			$data['order']['coupon'] = '';
			$data['order']['redeemableCoupon'] = '';
			$data['order']['couponAmount'] = '';

			$coupons = $order->get_coupon_codes();

			if (!empty($coupons)) {
				foreach ($coupons as $_coupon_code) {

					$args = array(
						'posts_per_page' 	=> 1,
						'post_type' 		=> 'shop_coupon',
						'meta_query' 		=> array(
							'relation' 		=> 'AND',
							array(
								'key' 		=> 'og_coupon_code',
								'value' 	=> $_coupon_code,
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
						$coupon = reset($query->posts);

						$coupon_id = $coupon->ID ?? 0;

						if ($coupon_id) {
							$redeemable_coupon = get_post_meta($coupon_id, 'redeemable_coupon', true);

							if ($redeemable_coupon) {
								$coupon_code = get_post_meta($coupon_id, 'og_coupon_code', true);

								$data['order']['redeemableCoupon'] = $coupon_code;
								$data['order']['couponAmount'] = $order->get_total_discount();
							} else {
								$coupon_data = $coupon->get_data();

								if (isset($coupon_data['code']) && $coupon_data['code'] != '') {
									$data['order']['coupon'] = $_coupon_code;
									$data['order']['couponAmount'] = $order->get_total_discount();
								}
							}
						}
					}

					
				}
			}

			$CWS_BackofficeAPI = new CWS_BackofficeAPI();

			__sd($data , "addOrUpdateOrder data 2" );

			$response = $CWS_BackofficeAPI->addOrUpdateOrder($data);

			__sd($response , "addOrUpdateOrder response 2" );

			// if (isset($response['code']) && $response['code'] != 200) {
			// 	$error_messages = [];

			// 	if (!empty($response['errors'])) {
			// 		if (isset($response['errors']['message']) && $response['errors']['message'] != '') {
			// 			$error_messages['order-validation'] = $response['errors']['message'];
			// 		}

			// 		foreach ($response['errors'] as $key => $error_message) {
			// 			if (!in_array($key, array('code', 'message'))) {
			// 				$error_messages[$key] = $error_message;
			// 			}
			// 		}
			// 	} elseif (isset($response['data']['message']) && $response['data']['message'] != '') {
			// 		$error_messages['order-validation'] = $response['data']['message'];
			// 	}  elseif (isseT($response['message']) && $response['message'] != '') {
			// 		$error_messages['order-validation'] = $response['message'];
			// 	}

			// 	if (!empty($error_messages)) {
			// 		foreach ($error_messages as $error => $message) {
			// 			$order->add_order_note( PHP_EOL . implode( PHP_EOL, $error_messages ) );

			// 			throw new Exception( implode( PHP_EOL, $error_messages ) );
	        //     		return false;
			// 		}
			// 	}
			// }

		}
	}
}

/**
 * `woocommerce_update_order` action hook callback function
 */
function cws_games_woocommerce_update_order($order_id, $order)
{
	
}

/**
 * `woocommerce_email_after_order_table` action hook callback function
 * 
 * WooCommerce Show Coupon Code Used In Emails
 */
function cws_games_show_coupons_used_in_emails( $order, $sent_to_admin, $plain_text, $email ) {
    if (count( $order->get_coupons() ) > 0 ) {
        $html = '<div class="used-coupons">
        <h2>Used coupons<h2>
        <table class="td" cellspacing="0" cellpadding="6" border="1"><tr>
        <th>Coupon Code</th>
        <th>Coupon Amount</th>
        </tr>';

        foreach( $order->get_coupons() as $item ){
            $coupon_code   	= $item->get_code();
            $coupon 		= new WC_Coupon($coupon_code);
			$discount_type 	= $coupon->get_discount_type();
			$coupon_amount 	= $coupon->get_amount();

			if ($discount_type == 'percent') {
				$output = $coupon_amount . "%";
			} else {
				$output = wc_price($coupon_amount);
			}

            $html .= '<tr>
                <td>' . strtoupper($coupon_code) . '</td>
                <td>' . $output . '</td>
            </tr>';
        }
        $html .= '</table><br></div>';

        $css = '<style>
            .used-coupons table {
				width: 100%;
				font-family: \'Helvetica Neue\', Helvetica, Roboto, Arial, sans-serif;
				border: 1px solid #e4e4e4;
				margin-bottom:8px;
			}
            .used-coupons table th, table.tracking-info td {
				text-align: left;
				border-top-width: 4px;
				border: 1px solid #e4e4e4;
				padding: 12px;
			}
            .used-coupons table td {
				text-align: left;
				border-top-width: 4px;
				border: 1px solid #e4e4e4;
				padding: 12px;
			}
        </style>';

        echo $css . $html;
    }
}
