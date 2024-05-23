<?php

add_filter('cws_games_homepage', 'cws_games_homepage', 10, 2);
add_filter('cws_games_general_settings', 'cws_games_general_settings', 10, 2);
add_filter('cws_games_config', 'cws_games_config', 10, 2);
add_filter('cws_games_gameslist', 'cws_games_gameslist', 10, 2);
add_filter('cws_games_sessions', 'cws_games_sessions', 10, 2);
add_filter('cws_games_session_details', 'cws_games_session_details', 10, 2);
add_filter('cws_games_providers', 'cws_games_providers', 10, 2);
add_filter('cws_games_login_rewards', 'cws_games_login_rewards', 10, 2);

add_filter('cws_games_gameslist_import', 'cron_importGames');



// Update the columns shown on the custom post type edit.php view - so we also have custom columns
add_filter('manage_game_posts_columns', 'cws_games_manage_game_posts_columns');

function cws_games_manage_game_posts_columns($columns)
{   
    $_columns = [];

    foreach ($columns as $key => $value) {
        $_columns[$key] = $value;

        if ($key == 'title') {
            $_columns['source'] 	= 'Source';
            $_columns['server_id'] 	= 'Server ID';
            $_columns['guid'] 		= 'Guid';
            $_columns['details'] 	= 'Details';
        }
    }

    return $_columns;
}
// this fills in the columns that were created with each individual post's value
add_action( 'manage_game_posts_custom_column' , 'cws_games_manage_game_posts_custom_column', 10, 2 );

function cws_games_manage_game_posts_custom_column($column, $post_id)
{
    switch($column) {
        case 'source':
            $source = get_post_meta($post_id, 'source', true);

            if ($source) {
                echo $source;
            }

            break;
        case 'server_id':
        	$server_id = get_post_meta($post_id, 'server_id', true);
            $server_data = (new CWS_GamesConfig)->CWS_GamesGetServerData($server_id); 
            
            if ($server_id) {
                echo $server_data['api_server_alias'] .'<BR>'. $server_id ?? '';
            }

            break;
        case 'guid':
        	$guid = get_post_meta($post_id, 'game_guid', true);

            if ($guid) {
                echo $guid;
            }

            break;
        case 'details':
        	$details = get_post_field('post_content', $post_id);

            if ($details) {
                echo $details;
            }

            break;
    }
}

add_filter( 'woocommerce_product_data_tabs', 'cws_games_add_custom_product_data_tab', 10, 1 );

/**
 * Add new custom tab to products (wp-admin)
 * 
 * @param [array] $tabs
 * @return [array] $tabs
 */
function cws_games_add_custom_product_data_tab( $tabs )
{   
    $tabs['cws_games'] = array(
        'label'    => __( 'Virtual Coins', 'cws_games' ),
        'target'   => 'cws_games_product_data_panel',
        'priority' => 40,
    );

    return $tabs;
}

add_action( 'woocommerce_product_data_panels', 'cws_games_add_custom_product_data_panel' );

/**
 * Add new custom panel with fields to a product data tab (wp-admin) 
 */
function cws_games_add_custom_product_data_panel()
{

    echo '<div id="cws_games_product_data_panel" class="panel woocommerce_options_panel hidden">';

    $CWS_GamesConfig    = new CWS_GamesConfig();
    $currencies         = $CWS_GamesConfig->getCurrencies();

    if (!empty($currencies)) {
        foreach ($currencies as $currency) {

            if ($currency['type'] != 'virtual') { continue; }

            echo '<div style="display: flex; flex-direction: row; align-items: center;">';

            $unique_id = 'cws_games_product_purchase_'.strtolower($currency['currency_code'] ?? '').'_amount';

            woocommerce_wp_text_input(
                array(
                    'id'          => $unique_id,
                    'value'       => get_post_meta( get_the_ID(), $unique_id, true ),
                    'label'       => __( $currency['currency_symbol'] . ' amount', 'cws_games' ),
                    'description' => '',
                    'style'       => 'width: 300px;'
                )
            );

            $unique_id_order = 'cws_games_product_purchase_'.strtolower($currency['currency_code'] ?? '').'_order';

            woocommerce_wp_text_input(
                array(
                    'id'          => $unique_id_order,
                    'value'       => get_post_meta( get_the_ID(), $unique_id_order, true ),
                    'label'       => __('Order', 'cws_games'),
                    'description' => '',
                    'style'       => 'width: 100px;'
                )
            );

            echo '</div>';
        }
    }

    echo '</div>';

}

add_action( 'woocommerce_process_product_meta', 'cws_games_save_custom_product_tab_data', 10, 1 );

/**
 * Save custom product fields
 * 
 * @param [int] $post_id
 */
function cws_games_save_custom_product_tab_data( $post_id )
{   

    $CWS_GamesConfig    = new CWS_GamesConfig();
    $currencies         = $CWS_GamesConfig->getCurrencies();

    if (!empty($currencies)) {
        foreach ($currencies as $currency) {
            $unique_id = 'cws_games_product_purchase_'.strtolower($currency['currency_code'] ?? '').'_amount';

            $value = $_POST[$unique_id] ?? '';

            update_post_meta( $post_id, $unique_id, esc_html( $value ) );

            $unique_id_order = 'cws_games_product_purchase_'.strtolower($currency['currency_code'] ?? '').'_order';

            $_value = $_POST[$unique_id_order] ?? 0;

            update_post_meta( $post_id, $unique_id_order, esc_html( $_value ) );
        }
    }
}

add_filter( 'woocommerce_get_item_data', 'cws_games_product_custom_fields_display', 10, 2 );

/**
 * Display product meta data in Cart and Checkout
 * 
 * @param [array] $item_data
 * @param [array] $cart_item
 * @return [array] $item_data
 */ 
function cws_games_product_custom_fields_display( $item_data, $cart_item )
{   
    $product_id = $cart_item['product_id'] ?? 0;

    $CWS_GamesConfig    = new CWS_GamesConfig();
    $currencies         = $CWS_GamesConfig->getCurrencies();

    if (!empty($currencies)) {
        foreach ($currencies as $currency) {
            $unique_id = 'cws_games_product_purchase_'.strtolower($currency['currency_code'] ?? '').'_amount';

            $value = get_post_meta( $product_id, $unique_id, true );

            if ($value != '') {
                $item_data[] = array(
                    'name'      => __( $currency['currency_symbol'] ?? '' . ' amount', 'cws_games'),
                    'value'     => wc_price( $value, array('currency' => strtoupper($currency['currency_code'] ?? '')) )
                );
            }
        }
    }

    return $item_data;
}

add_action( 'woocommerce_checkout_create_order_line_item', 'cws_games_order_and_email_product_custom_fields_display', 10, 4 );

/**
 * Display product meta data in Order details and Emails
 * 
 * @param [WC_Product object] $item
 * @param [int] $cart_item_key
 * @param [array] $values
 * @param [WC_Order object] $order 
 */ 
function cws_games_order_and_email_product_custom_fields_display( $item, $cart_item_key, $values, $order )
{
    $product_id = $values['product_id'] ?? 0;

    $CWS_GamesConfig    = new CWS_GamesConfig();
    $currencies         = $CWS_GamesConfig->getCurrencies();

    if (!empty($currencies)) {
        foreach ($currencies as $currency) {
            $unique_id = 'cws_games_product_purchase_'.strtolower($currency['currency_code'] ?? '').'_amount';

            $value = get_post_meta( $product_id, $unique_id, true );

            if ($value != '') {
                $item->update_meta_data( __($currency['currency_symbol'] . ' amount', 'cws_games'), wc_price( $value, array( 'currency' => strtoupper( $currency['currency_code'] ?? '' ) ) ) );
            }
        }
    }
}

// add_action( 'woocommerce_order_status_completed', 'cws_games_add_user_wallet_credit', 10, 1 );

/**
 * Fires upon order `completed` status change
 * 
 * @param [int] $order_id
 */ 
function cws_games_add_user_wallet_credit( $order_id )
{       
    // $_balance_calculated = get_post_meta( $order_id, '_balance_calculated', true );

    $_balance_calculated = 0;

    if ($_balance_calculated != 1) {
        $order = wc_get_order( $order_id );

        $CWS_GamesConfig    = new CWS_GamesConfig();
        $currencies         = $CWS_GamesConfig->getCurrencies();

        $currencies_amount = [];

        foreach ($order->get_items() as $item_id => $item) {

            $product_id = $item->get_product_id();

            if (!empty($currencies)) {
                foreach ($currencies as $currency) {
                    $unique_id = 'cws_games_product_purchase_'.strtolower($currency['currency_code'] ?? '').'_amount';

                    $value = get_post_meta( $product_id, $unique_id, true );

                    if ($value != '') {
                        $amount = 0;

                        if (array_key_exists($currency['currency_code'], $currencies_amount)) {
                            $amount = intval($currencies_amount[$currency['currency_code']]);
                        }

                        $amount += intval($value) ?? 0;

                        $currencies_amount[$currency['currency_code']] = $amount;
                    }
                }
            }
        }

        if (!empty($currencies_amount)) {
            $user_id = $order->get_user_id();

            $CWS_GamesUsers = new CWS_GamesUsers();

            foreach ($currencies_amount as $currency_code => $amount) {
                $data = [
                    'currency'      => $currency_code,
                    'direction'     => 'credit',
                    'amount'        => $amount,
                    'details'       => 'Credit upon completion of Order #' . $order_id
                ];

                $CWS_GamesUsers->addWalletTransaction($user_id, $data);
            }

            $CWS_GamesUsers->getUserWallet($user_id);
        }

        // update_post_meta( $order_id, '_balance_calculated', 1 );
    }
}