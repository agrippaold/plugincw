<?php

add_filter( 'template_include', function ($template) {

	global $post;

	global $wp;

	if (is_user_logged_in()) {
		if (strpos(home_url($wp->request), 'woo-wallet') !== false || strpos(home_url($wp->request), 'woo-wallet-transactions') !== false || strpos(home_url($wp->request), 'redeemable-coins')) {

			$user_id = get_current_user_id();

			$CWS_GamesUsers = new CWS_GamesUsers();

			$CWS_GamesUsers->getUserWallet($user_id);
		}
	}

	$options 			= get_option('cws_games_plugin_general');
	$home_logged_in 	= $options['home_logged_in'] ?? '';
	$home_logged_out 	= $options['home_logged_out'] ?? '';
	$elementor_view 	= $elementor_preview_active = \Elementor\Plugin::$instance->preview->is_preview_mode();

	if ($home_logged_in != $home_logged_out && !is_admin() && !$elementor_view) {
		if (is_user_logged_in()) {
			if ( $home_logged_out != '' && intval($home_logged_out) > 0 && $home_logged_out == ($post->ID ?? 0) ) {
				if ($home_logged_in != '' && intval($home_logged_in) > 0) {
					wp_redirect(get_permalink($home_logged_in), 301);
					exit;
				}
			}
		} else {
			if ( $home_logged_in != '' && intval($home_logged_in) > 0 && $home_logged_in == ($post->ID ?? 0) )  {
				if ($home_logged_out != '' && intval($home_logged_out) > 0) {
					wp_redirect(get_permalink($home_logged_out), 301);
					exit;
				}
			}
		}
	}

	return $template;
} );

add_filter('function_cws_games_gameslist', 'function_cws_games_gameslist', 10, 2);
add_filter('function_cws_games_gameslist_filters', 'function_cws_games_gameslist_filters', 10, 2);
add_action('function_cws_games_progress_bar', 'function_cws_games_progress_bar', 10, 2);
add_action('function_cws_games_wheel_progress_bar', 'function_cws_games_wheel_progress_bar', 10, 2);
add_filter('function_cws_games_product_list', 'function_cws_games_product_list', 10, 2);
add_filter('function_cws_games_mini_wallet', 'function_cws_games_mini_wallet', 10, 2);
add_filter('function_cws_games_wallet_transactions', 'function_cws_games_wallet_transactions', 10, 2);
add_filter('function_cws_games_notifications', 'function_cws_games_notifications', 10, 2);
add_filter('function_cws_games_redeemable_coins', 'function_cws_games_redeemable_coins', 10, 2);
add_filter('function_cws_games_jackpot', 'function_cws_games_jackpot', 10, 2);
add_filter('function_cws_games_redeem_button', 'function_cws_games_redeem_button', 10, 2);
add_filter('function_cws_games_sound_toggle', 'function_cws_games_sound_toggle', 10, 2);

add_action( 'wp_referral_code_after_refer_submitted', 'cws_games_wp_referral_code_after_refer_submitted', 10, 4 );

function cws_games_wp_referral_code_after_refer_submitted($new_user_id, $referrer_user_id, $ref_code, $new_user_ref_code)
{	
	$Woo_Wallet_Wallet 	= new Woo_Wallet_Wallet();
	$CWS_GamesConfig 	= new CWS_GamesConfig();

	$new_user_amount 			= $CWS_GamesConfig->CWS_GamesGetNewUserCreditAmount();
	$new_user_referrer_amount 	= $CWS_GamesConfig->CWS_GamesGetNewUserReferrerCreditAmount();
	$new_user_message 			= $CWS_GamesConfig->CWS_GamesGetNewUserCreditMessage();
	$new_user_referrer_message 	= $CWS_GamesConfig->CWS_GamesGetNewUserReferrerCreditMessage();


	// New User
	$amount 			= $new_user_amount;
	$transactionDesc 	= $new_user_message;
	$recharge_amount 	= apply_filters('woo_wallet_credit_purchase_amount', $amount, $new_user_id);
	$transaction_id 	= $Woo_Wallet_Wallet->credit($new_user_id, $recharge_amount, $transactionDesc);

	if ($transaction_id) {
		update_wallet_transaction_meta($transaction_id, '_type', 'credit_purchase', $new_user_id);
	}


	// Referrer User
	$amount 			= $new_user_referrer_amount;
	$transactionDesc 	= $new_user_referrer_message;
	$recharge_amount 	= apply_filters('woo_wallet_credit_purchase_amount', $amount, $referrer_user_id);
	$transaction_id 	= $Woo_Wallet_Wallet->credit($referrer_user_id, $recharge_amount, $transactionDesc);

	if ($transaction_id) {
		update_wallet_transaction_meta($transaction_id, '_type', 'credit_purchase', $referrer_user_id);
	}
}

add_filter( 'xoo_aff_easy-login-woocommerce_field_args', 'cws_games_registration_fields', 10, 1 );
add_filter( 'xoo_el_register_fields', 'cws_games_registration_fields', 10, 1 );
add_filter( 'xoo_el_process_registration_errors', 'cws_games_custom_user_registration_validation', 10, 4 );
add_action( 'xoo_el_created_customer', 'cws_games_created_customer', 10, 2 );
add_action( 'wp_update_user', 'cws_games_update_customer', 10, 3 );
add_action( 'woocommerce_save_account_details','cws_games_save_account_details', 100 );
add_filter( 'xoo_el_process_login_errors', 'cws_games_user_process_login', 10, 2);
add_action( 'xoo_el_login_success', 'cws_games_user_login_success', 10, 1 );
add_filter( 'send_password_change_email', 'cws_games_send_password_change_email', 10, 3 );

add_action( 'init', function() {
	add_rewrite_endpoint( get_option( 'woocommerce_woo_wallet_endpoint', 'woo-wallet' ), EP_PAGES );
	add_rewrite_endpoint( get_option( 'woocommerce_woo_wallet_transactions_endpoint', 'woo-wallet-transactions' ), EP_PAGES );
	add_rewrite_endpoint( get_option( 'woocommerce_notifications_endpoint', 'notifications' ), EP_PAGES );
	add_rewrite_endpoint( get_option( 'woocommerce_redeemable_coins_endpoint', 'redeemable-coins' ), EP_PAGES );
} );

add_action( 'woocommerce_account_woo-wallet_endpoint', 'cws_games_woo_wallet_enpoint' );
add_action( 'woocommerce_account_woo-wallet-transactions_endpoint', 'cws_games_woo_wallet_transactions_endpoint' );
add_action( 'woocommerce_account_notifications_endpoint', 'cws_games_notifications_endpoint' );
add_action( 'woocommerce_account_redeemable-coins_endpoint', 'cws_games_redeemable_coins_endpoint' );

add_action( 'woocommerce_after_checkout_validation', 'cws_games_woocommerce_after_checkout_validation', 10, 2 );
add_action( 'woocommerce_new_order', 'cws_games_woocommerce_new_order', 10, 2 );
add_action( 'woocommerce_before_order_object_save', 'cws_games_woocommerce_before_order_object_save', 10, 2 );
add_action( 'save_post', 'cws_games_woocommerce_after_order_object_save', 10, 3 );
add_action( 'woocommerce_update_order', 'cws_games_woocommerce_update_order', 10, 2 );

add_action( 'woocommerce_email_after_order_table', 'cws_games_show_coupons_used_in_emails', 10, 4 );

/**
 * Add custom menu links to My Account navigation menu
 * 
 */ 
function cws_games_add_myaccount_links( $menu_links ) {
 
	$_menu_links = [
		// 'woo-wallet' => __('Wallet Transactions', 'cws_games'),
		'woo-wallet-transactions' => __('Wallet Transactions', 'cws_games'),
		'notifications' => __('Notifications', 'cws_games'),
		'redeemable-coins' => __('Redeemable Coins', 'cws_games')
	];

	$menu_links = array_merge($_menu_links, $menu_links);

	return $menu_links;
}

add_filter( 'woocommerce_account_menu_items', 'cws_games_add_myaccount_links' );


/**
 * Register Custom CWS GAMES Sidebars
 * 
 */
function cws_games_widget_sidebars()
{
	register_sidebar( array(
		'name'          => 'Redeemable Coins Top Sidebar',
		'id'            => 'redeemable_coins_top_sidebar',
		'before_widget' => '<div>',
		'after_widget'  => '</div>',
		'before_title'  => '<h2 class="widget-title">',
		'after_title'   => '</h2>',
	) );

	register_sidebar( array(
		'name'          => 'Redeemable Coins Bottom Sidebar',
		'id'            => 'redeemable_coins_bottom_sidebar',
		'before_widget' => '<div>',
		'after_widget'  => '</div>',
		'before_title'  => '<h2 class="widget-title">',
		'after_title'   => '</h2>',
	) );
}

add_action( 'widgets_init', 'cws_games_widget_sidebars' );