<?php

add_shortcode( 'cws_games_gameslist', 'shortcode_cws_games_gameslist' );

function shortcode_cws_games_gameslist($atts = [])
{
	ob_start();

	do_action('function_cws_games_gameslist', $atts);

	$content = ob_get_clean();

	return $content;

}

add_shortcode( 'cws_games_gameslist_filters', 'shortcode_cws_games_gameslist_filters' );

function shortcode_cws_games_gameslist_filters($atts = [])
{
	ob_start();

	do_action('function_cws_games_gameslist_filters', $atts);

	$content = ob_get_clean();

	return $content;
}

add_shortcode( 'cws_games_progress_bar', 'shortcode_cws_games_progress_bar' );

function shortcode_cws_games_progress_bar($atts = [])
{
	ob_start();

	do_action('function_cws_games_progress_bar', $atts);

	$content = ob_get_clean();

	return $content;
}

add_shortcode( 'cws_games_wheel_progress_bar', 'shortcode_cws_games_wheel_progress_bar' );

function shortcode_cws_games_wheel_progress_bar($atts = [])
{
	ob_start();

	do_action('function_cws_games_wheel_progress_bar', $atts);

	$content = ob_get_clean();

	return $content;
}


add_shortcode( 'cws_games_product_list', 'shortcode_cws_games_product_list' );

function shortcode_cws_games_product_list($atts = [])
{	
	ob_start();

	do_action('function_cws_games_product_list', $atts);

	$content = ob_get_clean();

	return $content;
}

add_shortcode('theme_mini_wallet', 'shortcode_cws_games_mini_wallet');

function shortcode_cws_games_mini_wallet($atts = [])
{
	ob_start();

	do_action('function_cws_games_mini_wallet', $atts);

	$content = ob_get_clean();

	return $content;
}

add_shortcode('cws_games_wallet_transactions', 'shortcode_cws_games_wallet_transactions');

function shortcode_cws_games_wallet_transactions($atts = [])
{
	ob_start();

	do_action('function_cws_games_wallet_transactions', $atts);

	$content = ob_get_clean();

	return $content;
}

add_shortcode('cws_games_notifications', 'shortcode_cws_games_notifications');

function shortcode_cws_games_notifications($atts = [])
{
	ob_start();

	do_action('function_cws_games_notifications', $atts);

	$content = ob_get_clean();

	return $content;
}

add_shortcode('cws_games_redeemable_coins', 'shortcode_cws_games_redeemable_coins');

function shortcode_cws_games_redeemable_coins($atts = [])
{
	ob_start();

	do_action('function_cws_games_redeemable_coins', $atts);

	$content = ob_get_clean();

	return $content;
}

add_shortcode('cws_games_jackpot', 'shortcode_cws_games_jackpot');

function shortcode_cws_games_jackpot($atts = [])
{
	ob_start();

	do_action('function_cws_games_jackpot', $atts);

	$content = ob_get_clean();

	return $content;
}

add_shortcode( 'cws_games_redeem_button', 'shortcode_cws_games_redeem_button' );

function shortcode_cws_games_redeem_button($atts = [])
{
	ob_start();

	do_action('function_cws_games_redeem_button', $atts);

	$content = ob_get_clean();

	return $content;
}

add_shortcode( 'cws_games_sound_toggle', 'shortcode_cws_games_sound_toggle' );

function shortcode_cws_games_sound_toggle($atts = [])
{
	ob_start();

	do_action('function_cws_games_sound_toggle', $atts);

	$content = ob_get_clean();

	return $content;
}