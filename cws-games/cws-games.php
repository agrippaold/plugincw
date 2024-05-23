<?php
/*
 * Plugin Name: CWS Games
 * Plugin URI:
 * Description: CWS Games
 * Version: 2.0430 | by <a href="https://cobweb.biz" target="_blank">Cobweb Software</a>
 * Developer: Cobweb Software
 * Author URI: https://cobweb.biz
 */

// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);
 
require_once dirname(__FILE__) . '/config.php'; 
 

if (class_exists('CWS_GamesConfig')) {

    $CWS_GamesConfig = new CWS_GamesConfig();

    add_action('admin_menu', array($CWS_GamesConfig, 'admin'));

}


add_action( 'rest_api_init', function () {

  register_rest_route( 'cws_games', '/GetPlayerInfo', array(
    'methods'   => 'POST',
    'callback'  => 'restAPIGetPlayerInfo',
    'permission_callback' => '__return_true' // Use this line to make the endpoint public. For authenticated endpoints, define your permission callback.
  ));
  register_rest_route( 'cws_games', 'Withdraw', array(
    'methods'   => 'POST',
    'callback'  => 'restAPIWithdraw'
  ));
  register_rest_route( 'cws_games', 'Deposit', array(
    'methods'   => 'POST',
    'callback'  => 'restAPIDeposit'
  ));
  register_rest_route( 'cws_games', 'WithdrawDeposit', array(
    'methods'   => 'POST',
    'callback'  => 'restAPIWithdrawDeposit'
  ));
  register_rest_route( 'cws_games', 'WithdrawAndDeposit', array(
    'methods'   => 'POST',
    'callback'  => 'restAPIWithdrawDeposit'
  ));
  register_rest_route( 'cws_games', 'RollbackTransaction', array(
    'methods'   => 'POST',
    'callback'  => 'restAPIRollbackTransaction'
  ));
  register_rest_route( 'cws_games', 'GenerateUsers', array(
    'methods'   => 'POST',
    'callback'  => 'restAPIGenerateUsers'
  ));

});


// New code 
function cws_endpoint_rewrite_rule() {
    add_rewrite_rule('^GetPlayerInfo/?$', 'index.php?custom_endpoint=GetPlayerInfo', 'top');
    add_rewrite_rule('^Withdraw/?$', 'index.php?custom_endpoint=Withdraw', 'top');
    add_rewrite_rule('^Deposit/?$', 'index.php?custom_endpoint=Deposit', 'top');
    add_rewrite_rule('^WithdrawDeposit/?$', 'index.php?custom_endpoint=WithdrawDeposit', 'top');
    add_rewrite_rule('^WithdrawAndDeposit/?$', 'index.php?custom_endpoint=WithdrawAndDeposit', 'top');
    add_rewrite_rule('^RollbackTransaction/?$', 'index.php?custom_endpoint=RollbackTransaction', 'top');
    add_rewrite_rule('^GenerateUsers/?$', 'index.php?custom_endpoint=GenerateUsers', 'top');
}
add_action('init', 'cws_endpoint_rewrite_rule');


function custom_endpoint_query_vars($vars) {
    $vars[] = 'custom_endpoint';
    return $vars;
}
add_filter('query_vars', 'custom_endpoint_query_vars');


function capture_post_request() {
    
            
    if(isset($_POST['data'])) {
        $correctedJson = str_replace("'", '"', stripslashes($_POST['data']));
        $data = json_decode($correctedJson, true);
        $_POST['data'] = $data;
    }

    if(get_query_var('custom_endpoint') == 'GetPlayerInfo') {

        $request = new WP_REST_Request;
        restAPIGetPlayerInfo($request);
        die(); 
    }
    if(get_query_var('custom_endpoint') == 'Withdraw') { 
        
        $request = new WP_REST_Request;
        restAPIWithdraw($request);
        die(); 
    }
    if(get_query_var('custom_endpoint') == 'Deposit') { 
        
        $request = new WP_REST_Request;
        restAPIDeposit($request);
        die(); 
    }
    if(get_query_var('custom_endpoint') == 'WithdrawDeposit' || get_query_var('custom_endpoint') == 'WithdrawAndDeposit') { 
        
        $request = new WP_REST_Request;
        restAPIWithdrawDeposit($request);
        die(); 
    }
    if(get_query_var('custom_endpoint') == 'RollbackTransaction') { 
        
        $request = new WP_REST_Request;
        restAPIRollbackTransaction($request);
        die(); 
    }
    if(get_query_var('custom_endpoint') == 'GenerateUsers') { 
        
        $request = new WP_REST_Request;
        restAPIGenerateUsers($request);
        die(); 
    }
}
add_action('template_redirect', 'capture_post_request');

//end new code 
 
function restAPIGetPlayerInfo(WP_REST_Request $request)
{ 
    $params = $request->get_params();
 
    $_POST = array_merge($_POST, $params);  
    
    ajax_GetPlayerInfo();
}
function restAPIWithdraw(WP_REST_Request $request)
{
    $params = $request->get_params();

    $_POST = array_merge($_POST, $params);

    ajax_Withdraw();
}
function restAPIDeposit(WP_REST_Request $request)
{
    $params = $request->get_params();

    $_POST = array_merge($_POST, $params);

    ajax_Deposit();
}
function restAPIWithdrawDeposit(WP_REST_Request $request)
{
    $params = $request->get_params();

    $_POST = array_merge($_POST, $params);

    ajax_WithdrawDeposit();
}
function restAPIRollbackTransaction(WP_REST_Request $request)
{
    $params = $request->get_params();

    $_POST = array_merge($_POST, $params);

    ajax_RollbackTransaction();
}
function restAPIGenerateUsers(WP_REST_Request $request)
{
    $params = $request->get_params();

    $_POST = array_merge($_POST, $params);

    ajax_GenerateUsers();
}

function cws_games_styles_and_scripts()
{   
    wp_register_style( 'slick-styles', CWS_GAMES_ABSPATH_ASSETS . '/Slick/slick.css' );
    wp_register_script( 'slick-script', CWS_GAMES_ABSPATH_ASSETS . '/Slick/slick.min.js', [], '', true );

    wp_register_style( 'wheel-styles', CWS_GAMES_ABSPATH_ASSETS . '/css/cws-games-wheel-styles.css' );
    wp_register_script( 'wheel-scripts', CWS_GAMES_ABSPATH_ASSETS . '/js/cws-games-wheel-scripts.js', [], '', true );
    wp_localize_script('wheel-scripts', 'cws_games_wheel_ajax_object',
            array( 'ajax_url' => admin_url( 'admin-ajax.php' ) )
    );
    
    wp_enqueue_style( 'iziModal', 'https://cdnjs.cloudflare.com/ajax/libs/izimodal/1.5.1/css/iziModal.min.css', array(), CWS_GAMES_ASSETS_VERSION, 'all' );
    wp_enqueue_script('iziModal', 'https://cdnjs.cloudflare.com/ajax/libs/izimodal/1.5.1/js/iziModal.js', array('jquery'), CWS_GAMES_ASSETS_VERSION, true);

    wp_enqueue_style('style-icofonts', CWS_GAMES_ABSPATH_ASSETS . '/css/icofont.min.css');

	wp_enqueue_style('style-cws_games', CWS_GAMES_ABSPATH_ASSETS . '/css/cws_games.css');

    wp_enqueue_style( 'style-theme-cws_games', get_stylesheet_directory_uri() . '/cws-games/css/cws-games-style.css', array('astra-theme-css'), CWS_GAMES_ASSETS_VERSION, 'all' );
	
    wp_enqueue_script('jquery-ui', 'https://code.jquery.com/ui/1.10.2/jquery-ui.js', array('jquery'), CWS_GAMES_ASSETS_VERSION);

    wp_enqueue_script('script-cws_games', CWS_GAMES_ABSPATH_ASSETS . '/js/cws_games.js', array('jquery', 'jquery-ui'), '', true);
    wp_localize_script('script-cws_games', 'cws_games_ajax_object',
            array( 'ajax_url' => admin_url( 'admin-ajax.php' ) )
    );
    
}

add_action('wp_enqueue_scripts', 'cws_games_styles_and_scripts');

// Act on plugin activation
register_activation_hook( __FILE__, "activate_cws_games_plugin" );

function activate_cws_games_plugin()
{
    init_db_cws_games_plugin();
}

// Initialize DB Tables
function init_db_cws_games_plugin() {

    global $table_prefix, $wpdb;

    $cws_games_gameslist = $table_prefix . 'cws_games_gameslist';

    // Create Gameslist Table if not exist
    if( $wpdb->get_var( "show tables like '$cws_games_gameslist'" ) != $cws_games_gameslist ) {

        // Query - Create Table
        $sql = "CREATE TABLE `$cws_games_gameslist` (";
        $sql .= " `id` int(10) NOT NULL auto_increment, ";
        $sql .= " `guid` varchar(250) NULL DEFAULT NULL,";
        $sql .= " `name_game` varchar(250) NULL DEFAULT NULL, ";
        $sql .= " `urlButton` varchar(250) NULL DEFAULT NULL, ";
        $sql .= " `details` text NULL DEFAULT NULL, ";
        $sql .= " `json` text NULL DEFAULT NULL, ";
        $sql .= " `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, ";
        $sql .= " `updated_at` DATETIME NOT NULL, ";
        $sql .= " PRIMARY KEY (`id`) ";
        $sql .= ") ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;";

        // Include Upgrade Script
        require_once( ABSPATH . '/wp-admin/includes/upgrade.php' );
    
        // Create Table
        dbDelta( $sql );

    }

    $cws_games_transactions = $table_prefix . 'cws_games_transactions';

    // Create Transactions Table if not exist
    if( $wpdb->get_var( "show tables like '$cws_games_transactions'" ) != $cws_games_transactions ) {

        // Query - Create Table
        $sql = "CREATE TABLE `$cws_games_transactions` (";
        $sql .= " `id` int(10) NOT NULL auto_increment, ";
        $sql .= " `token` varchar(250) NULL DEFAULT NULL,";
        $sql .= " `transaction_type` varchar(150) NULL DEFAULT NULL, ";
        $sql .= " `type` varchar(50) NULL DEFAULT NULL, ";
        $sql .= " `gameId` varchar(250) NULL DEFAULT NULL, ";
        $sql .= " `transactionId` varchar(250) NULL DEFAULT NULL, ";
        $sql .= " `id_session` varchar(250) NULL DEFAULT NULL, ";
        $sql .= " `winAmount` varchar(50) NULL DEFAULT NULL, ";
        $sql .= " `betAmount` varchar(50) NULL DEFAULT NULL, ";
        $sql .= " `balance` varchar(50) NULL DEFAULT NULL, ";
        $sql .= " `currency` varchar(50) NULL DEFAULT NULL, "; 
        $sql .= " `wallet_transaction_id` int(10) NULL DEFAULT NULL, ";
        $sql .= " `user_id` int(10) NULL DEFAULT NULL, ";
        $sql .= " `status` int(10) NULL DEFAULT NULL, ";
        $sql .= " `status_txt` varchar(250) NULL DEFAULT NULL, ";
        $sql .= " `request_json` text NULL DEFAULT NULL, ";
        $sql .= " `response_json` text NULL DEFAULT NULL, ";
        $sql .= " `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, ";
        $sql .= " `updated_at` DATETIME NOT NULL, ";
        $sql .= " `ip` varchar(50) NULL DEFAULT NULL, ";
        $sql .= " PRIMARY KEY (`id`) ";
        $sql .= ") ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;";

        // Include Upgrade Script
        require_once( ABSPATH . '/wp-admin/includes/upgrade.php' );
    
        // Create Table
        dbDelta( $sql );

    }

    // ALTER TABLE `wp_cws_games_transactions` ADD `id_session` VARCHAR(250) NULL DEFAULT NULL AFTER `transactionId`;
    // maybe_add_column( $cws_games_transactions, 'id_session', 'ALTER TABLE `'.$cws_games_transactions.'` ADD `id_session` VARCHAR(250) NULL DEFAULT NULL AFTER `transactionId`' );

    $cws_games_sessions = $table_prefix . 'cws_games_sessions';

    // Create Sessions Table if not exists
    if ( $wpdb->get_var( "Show tables like '$cws_games_sessions'" ) != $cws_games_sessions ) {

        // Query - Create Table
        $sql = "CREATE TABLE `$cws_games_sessions` (";
        $sql .= " `id` int(10) NOT NULL auto_increment, ";
        $sql .= " `id_session` varchar(250) NULL DEFAULT NULL,";
        $sql .= " `token` varchar(250) NULL DEFAULT NULL, ";
        $sql .= " `userId` int(10) NULL DEFAULT NULL, ";
        $sql .= " `userFullName` varchar(250) NULL DEFAULT NULL, ";
        $sql .= " `userName` varchar(150) NULL DEFAULT NULL, ";
        $sql .= " `providerId` varchar(250) NULL DEFAULT NULL, ";
        $sql .= " `providerName` varchar(150) NULL DEFAULT NULL, ";
        $sql .= " `gameId` varchar(250) NULL DEFAULT NULL, ";
        $sql .= " `gameName` varchar(250) NULL DEFAULT NULL, ";
        $sql .= " `gameType` varchar(50) NULL DEFAULT NULL, ";
        $sql .= " `currency` varchar(50) NULL DEFAULT NULL, ";
        $sql .= " `currency2` varchar(50) NULL DEFAULT NULL, ";
        $sql .= " `currencyExchangeRate` varchar(25) NULL DEFAULT NULL, ";
        $sql .= " `totIn` varchar(50) NULL DEFAULT NULL, ";
        $sql .= " `totOut` varchar(50) NULL DEFAULT NULL, ";
        $sql .= " `totSpins` varchar(50) NULL DEFAULT NULL, ";
        $sql .= " `date_creation` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, ";
        $sql .= " `date_update` DATETIME NOT NULL, ";
        $sql .= " `ip` varchar(50) NULL DEFAULT NULL, ";
        $sql .= " PRIMARY KEY (`id`) ";
        $sql .= ") ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;";

        // Include Upgrade Script
        require_once( ABSPATH . '/wp-admin/includes/upgrade.php' );
    
        // Create Table
        dbDelta( $sql );

    }

    $cws_games_most_played = $table_prefix . 'cws_games_most_played';

    // Create Game Most Played Table if not exist
    if( $wpdb->get_var( "show tables like '$cws_games_most_played'" ) != $cws_games_most_played ) {

        // Query - Create Table
        $sql = "CREATE TABLE `$cws_games_most_played` (";
        $sql .= " `id` int(10) NOT NULL auto_increment, ";
        $sql .= " `game_guid` varchar(250) NULL DEFAULT NULL,";
        $sql .= " `user_id` int(10) NULL DEFAULT NULL, ";
        $sql .= " `game_category` varchar(250) NULL DEFAULT NULL, ";
        $sql .= " `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, ";
        $sql .= " `updated_at` DATETIME NOT NULL, ";
        $sql .= " PRIMARY KEY (`id`) ";
        $sql .= ") ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;";

        // Include Upgrade Script
        require_once( ABSPATH . '/wp-admin/includes/upgrade.php' );
    
        // Create Table
        dbDelta( $sql );

    }
}

function cws_games_scripts_admin()
{   
    wp_enqueue_script('script-cws_games-admin', CWS_GAMES_ABSPATH_ASSETS . '/js/cws_games_admin.js', '', CWS_GAMES_ASSETS_VERSION, true);
    wp_localize_script('script-cws_games-admin', 'cws_games_ajax_object',
            array( 'ajax_url' => admin_url( 'admin-ajax.php' ) )
    );
}


add_action('admin_enqueue_scripts', 'cws_games_scripts_admin');


/* Add Custom currency to WooCommerce */
add_filter( 'woocommerce_currencies', 'add_cws_games_currency' );
function add_cws_games_currency( $cw_currency ) {
    $CWS_GamesConfig    = new CWS_GamesConfig();
    $currencies         = $CWS_GamesConfig->getCurrencies();

    if (!empty($currencies)) {
        foreach ($currencies as $currency) {
            $cw_currency[strtoupper($currency['currency_code'])] = __($currency['currency_symbol'] . ' Coins', 'woocommerce');
        }
    }

    // $cw_currency['FICO'] = __( 'Fils Coin', 'woocommerce' );
    // $cw_currency['TICO'] = __( 'Ti Coin', 'woocommerce' );

    return $cw_currency;
}
/* Associate custom WooCommerce currency to custom symbol */
add_filter('woocommerce_currency_symbol', 'add_cws_games_currency_symbol', 10, 2);
function add_cws_games_currency_symbol( $custom_currency_symbol, $custom_currency ) {
    $CWS_GamesConfig    = new CWS_GamesConfig();
    $currencies         = $CWS_GamesConfig->getCurrencies();

    if (!empty($currencies)) {
        foreach ($currencies as $currency) {
            if (strtoupper($currency['currency_code']) == strtoupper($custom_currency)) {
                $custom_currency_symbol = $currency['currency_symbol'];

                break;
            }
        }
    }

    // switch( $custom_currency ) {
    //     case 'FICO': $custom_currency_symbol = 'FiCo'; break;
    //     case 'TICO': $custom_currency_symbol = 'TiCo'; break;
    // }

    return $custom_currency_symbol;
}


/**
 * Register and enqueue frontend styles and scripts - Wallet pages
 */
function cws_games_wallet_scripts() {
    $wp_scripts = wp_scripts();
    $suffix     = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
    wp_register_style( 'woo-wallet-payment-jquery-ui', CWS_GAMES_ABSPATH_ASSETS . '/wallet/jquery/css/jquery-ui.css', false, CWS_GAMES_ASSETS_VERSION, false );
    wp_register_style( 'jquery-datatables-style', CWS_GAMES_ABSPATH_ASSETS . '/wallet/jquery/css/jquery.dataTables.min.css', false, CWS_GAMES_ASSETS_VERSION, false );
    wp_register_style( 'jquery-datatables-responsive-style', CWS_GAMES_ABSPATH_ASSETS . '/wallet/jquery/css/responsive.bootstrap.min.css', false, CWS_GAMES_ASSETS_VERSION, false );
    wp_register_style( 'woo-wallet-style', CWS_GAMES_ABSPATH_ASSETS . '/wallet/css/frontend.css', array(), CWS_GAMES_ASSETS_VERSION );
    // Add RTL support.
    wp_style_add_data( 'woo-wallet-style', 'rtl', 'replace' );
    wp_register_script( 'jquery-datatables-script', CWS_GAMES_ABSPATH_ASSETS . '/wallet/jquery/js/jquery.dataTables.min.js', array( 'jquery' ), CWS_GAMES_ASSETS_VERSION, true );
    wp_register_script( 'jquery-datatables-responsive-script', CWS_GAMES_ABSPATH_ASSETS . '/wallet/jquery/js/dataTables.responsive.min.js', array( 'jquery' ), CWS_GAMES_ASSETS_VERSION, true );
    wp_register_script( 'wc-endpoint-wallet', CWS_GAMES_ABSPATH_ASSETS . '/wallet/js/frontend/wc-endpoint-wallet' . $suffix . '.js', array( 'jquery', 'jquery-datatables-script' ), CWS_GAMES_ASSETS_VERSION, true );
    $data_table_columns    = apply_filters(
        'woo_wallet_transactons_datatable_columns',
        array(
            array(
                'data'      => 'credit',
                'title'     => __( 'Credit', 'woo-wallet' ),
                'orderable' => false,
            ),
            array(
                'data'      => 'debit',
                'title'     => __( 'Debit', 'woo-wallet' ),
                'orderable' => false,
            ),
            array(
                'data'      => 'details',
                'title'     => __( 'Details', 'woo-wallet' ),
                'orderable' => false,
            ),
            array(
                'data'      => 'date',
                'title'     => __( 'Date', 'woo-wallet' ),
                'orderable' => false,
            ),
        )
    );

    $wallet_localize_param = array(
        'ajax_url'                => admin_url( 'admin-ajax.php' ),
        'transaction_table_nonce' => wp_create_nonce( 'woo-wallet-transactions' ),
        'search_user_nonce'       => wp_create_nonce( 'search-user' ),
        'search_by_user_email'    => apply_filters( 'woo_wallet_user_search_exact_match', true ),
        'i18n'                    => array(
            'emptyTable'           => __( 'No transactions available', 'woo-wallet' ),
            /* translators: menu length */
            'lengthMenu'           => sprintf( __( 'Show %s entries', 'woo-wallet' ), '_MENU_' ),
            /* translators: 1.start 2.end 3.total */
            'info'                 => sprintf( __( 'Showing %1$1s to %2$2s of %3$3s entries', 'woo-wallet' ), '_START_', '_END_', '_TOTAL_' ),
            /* translators: max length */
            'infoFiltered'         => sprintf( __( '(filtered from %1s total entries)', 'woo-wallet' ), '_MAX_' ),
            'infoEmpty'            => __( 'Showing 0 to 0 of 0 entries', 'woo-wallet' ),
            'paginate'             => array(
                'first'    => __( 'First', 'woo-wallet' ),
                'last'     => __( 'Last', 'woo-wallet' ),
                'next'     => __( 'Next', 'woo-wallet' ),
                'previous' => __( 'Previous', 'woo-wallet' ),
            ),
            'non_valid_email_text' => __( 'Please enter a valid email address', 'woo-wallet' ),
            'no_resualt'           => __( 'No results found', 'woo-wallet' ),
            'zeroRecords'          => __( 'No matching records found', 'woo-wallet' ),
            'inputTooShort'        => __( 'Please enter 3 or more characters', 'woo-wallet' ),
            'searching'            => __( 'Searching…', 'woo-wallet' ),
            'processing'           => __( 'Processing...', 'woo-wallet' ),
            'search'               => __( 'Search by date:', 'woo-wallet' ),
            'placeholder'          => __( 'yyyy-mm-dd', 'woo-wallet' ),
        ),
        'columns'                 => $data_table_columns,
    );

    wp_localize_script( 'wc-endpoint-wallet', 'wallet_param', $wallet_localize_param );
    wp_enqueue_style( 'woo-wallet-style' );

    if ( is_account_page() ) {
        wp_enqueue_style( 'woo-wallet-payment-jquery-ui' );
        wp_enqueue_style( 'dashicons' );
        wp_enqueue_style( 'select2' );
        wp_enqueue_style( 'jquery-datatables-style' );
        wp_enqueue_style( 'jquery-datatables-responsive-style' );
        wp_enqueue_script( 'jquery-ui-datepicker' );
        wp_enqueue_script( 'selectWoo' );
        wp_enqueue_script( 'jquery-datatables-script' );
        wp_enqueue_script( 'jquery-datatables-responsive-script' );
        wp_enqueue_script( 'wc-endpoint-wallet' );
    }

    $add_to_cart_variation = "jQuery(function ($) { $(document).on('show_variation', function (event, variation, purchasable) { if(variation.cashback_amount) { $('.on-woo-wallet-cashback').show(); $('.on-woo-wallet-cashback').html(variation.cashback_html); } else { $('.on-woo-wallet-cashback').hide(); } }) });";
    wp_add_inline_script( 'wc-add-to-cart-variation', $add_to_cart_variation );
}

add_action( 'wp_enqueue_scripts', 'cws_games_wallet_scripts', 20 );