<?php

if (!function_exists('cws_games_homepage')) {
    /**
     * Homepage 
     *
     * @return void
     */
    function cws_games_homepage()
    {
        load_template(
            CWS_GAMES_ABSPATH_TEMPLATE . '/backend/homepage.php'
        );
    }
}

if (!function_exists('cws_games_config')) {
    /**
     * Config 
     *
     * @return void
     */
    function cws_games_config()
    {
        load_template(
            CWS_GAMES_ABSPATH_TEMPLATE . '/backend/config.php'
        );
    }
}

if (!function_exists('cws_games_general_settings')) {
    /**
     * General Settings 
     *
     * @return void
     */
    function cws_games_general_settings()
    {
        load_template(
            CWS_GAMES_ABSPATH_TEMPLATE . '/backend/general-settings.php'
        );
    }
}

if (!function_exists('cws_games_gameslist')) {
    /**
     * Games List 
     *
     * @return void
     */
    function cws_games_gameslist()
    {
        $CWS_GamesGameslist = new CWS_GamesGameslist();

        $params = [
            'orderby'   => 'meta_value',
            'meta_key'  => 'server_id',
        ];

        $games = $CWS_GamesGameslist->FindByParams($params);

        set_query_var('games', $games);
        
        load_template(
            CWS_GAMES_ABSPATH_TEMPLATE . '/backend/gameslist.php'
        );
    }
}

if (!function_exists('cws_games_sessions')) {
    /**
     * Games Sessions 
     *
     * @return void
     */
    function cws_games_sessions()
    {   
        $CWS_GamesConfig    = new CWS_GamesConfig();
        $CWS_GamesGameslist = new CWS_GamesGameslist();
        $CWS_GamesSessions  = new CWS_GamesSessions();
        $CWS_GamesServers   = new CWS_GamesServers();

        $currencies         = $CWS_GamesConfig->getCurrencies();

        $gameIds        = $CWS_GamesSessions->getTableColumnValues('gameId');
        $providerIds    = $CWS_GamesSessions->getTableColumnValues('providerId');

        $gameslist_args = [
            'meta_query'    => [
                [
                    'key'       => 'game_guid',
                    'value'     => $gameIds,
                    'compare'   => 'IN'
                ]
            ]
        ];

        $gameslist  = $CWS_GamesGameslist->FindByParams($gameslist_args);
        $servers    = $CWS_GamesServers->getServersByServerIds($providerIds);

        $params = [
            'orderby'   => ' ORDER BY `id`',
        ];

        if (isset($_POST['formdata']) && !empty($_POST['formdata'])) {

            if (isset($_POST['formdata']['date_from']) && $_POST['formdata']['date_from'] != '') {
                $params['date_creation'] = [
                    'cond'      => '>=',
                    'value'     => "'".$_POST['formdata']['date_from']."'"
                ];
            }

            if (isset($_POST['formdata']['date_to']) && $_POST['formdata']['date_to'] != '') {
                if (array_key_exists('date_creation', $params)) {
                    $old_value = $params['date_creation']['cond'] . ' ' . $params['date_creation']['value'];

                    $params['date_creation']['cond']    = ' AND ';

                    $params['date_creation']['value']   = [$old_value, " <= '" . $_POST['formdata']['date_to'] . "'"];

                } else {
                    $params['date_creation'] = [
                        'cond'      => '<=',
                        'value'     => "'".$_POST['formdata']['date_to']."'"
                    ];
                }
                
            }

            if (isset($_POST['formdata']['gameId']) && $_POST['formdata']['gameId'] != '' && $_POST['formdata']['gameId'] != -1 ) {
                $params['gameId'] = $_POST['formdata']['gameId'];
            }

            if (isset($_POST['formdata']['providerId']) && $_POST['formdata']['providerId'] != '' && $_POST['formdata']['providerId'] != -1) {
                $params['providerId'] = $_POST['formdata']['providerId'];
            }

            if (isset($_POST['formdata']['currency']) && $_POST['formdata']['currency'] != '' && $_POST['formdata']['currency'] != -1) {
                $params['currency'] = $_POST['formdata']['currency'];
            }
        }

        $sessions = $CWS_GamesSessions->FindByParams($params);

        set_query_var('games', $gameslist);
        set_query_var('servers', $servers);
        set_query_var('currencies', $currencies);
        set_query_var('sessions', $sessions);
        
        load_template(
            CWS_GAMES_ABSPATH_TEMPLATE . '/backend/sessions.php'
        );
    }
}

if (!function_exists('cws_games_session_details')) {
    /**
     * Session Details
     * 
     * @return void
     */
    function cws_games_session_details()
    {
        if (isset($_GET['id_session']) && $_GET['id_session'] != '') {

            $CWS_GamesSessions = new CWS_GamesSessions();

            $session = $CWS_GamesSessions->getDetails($_GET['id_session']);

            set_query_var('session', $session);
        
            load_template(
                CWS_GAMES_ABSPATH_TEMPLATE . '/backend/session-details.php'
            );
        }
    }
}

if (!function_exists('cws_games_providers')) {
    /**
     * Providers
     *
     * @return void
     */
    function cws_games_providers()
    {   

        $CWS_GamesConfig    = new CWS_GamesConfig();
        $CWS_GamesGameslist = new CWS_GamesGameslist();
        $CWS_GamesSessions  = new CWS_GamesSessions();
        $CWS_GamesServers   = new CWS_GamesServers();

        $currencies         = $CWS_GamesConfig->getCurrencies();

        $gameIds        = $CWS_GamesSessions->getTableColumnValues('gameId');
        $providerIds    = $CWS_GamesSessions->getTableColumnValues('providerId');

        if (!empty($providerIds)) {

            $params = [
                'orderby'       => ' ORDER BY `id`',
            ];

            $rows = $CWS_GamesSessions->FindByParams($params);

            $prepareServers = [];

            foreach ($rows as $row) {
                if (isset($row->providerId) && $row->providerId != '') {
                    $totIn = $totOut = $totSpins = 0;

                    $currency = $row->currency ?? '';

                    $currency = strtoupper($currency);

                    if ($currency != '') {
                        if (!array_key_exists($row->providerId, $prepareServers)) {
                            $prepareServers[$row->providerId] = array(
                                'providerId'        => $row->providerId,
                                'providerName'      => $row->providerName,
                            );
                        }

                        if (isset($prepareServers[$row->providerId]['currencies'][$currency])) {

                            $totIn      = intval($prepareServers[$row->providerId]['currencies'][$currency]['totIn']) ?? 0;
                            $totOut     = intval($prepareServers[$row->providerId]['currencies'][$currency]['totOut']) ?? 0;
                            $totSpins   = intval($prepareServers[$row->providerId]['currencies'][$currency]['totSpins']) ?? 0;

                        }

                        $totIn += intval($row->totIn) ?? 0;
                        $totOut += intval($row->totOut) ?? 0;
                        $totSpins += intval($row->totSpins) ?? 0;

                        $prepareServers[$row->providerId]['currencies'][$currency]['totIn'] = $totIn;
                        $prepareServers[$row->providerId]['currencies'][$currency]['totOut'] = $totOut;
                        $prepareServers[$row->providerId]['currencies'][$currency]['totSpins'] = $totSpins;

                    }
                }
            }

            if (!empty($prepareServers)) {

                foreach ($prepareServers as $key => $server) {
                    $serverTotalSpins = 0;

                    foreach ($server['currencies'] as $currency) {
                        $serverTotalSpins += intval($currency['totSpins']) ?? 0;
                    }

                    $prepareServers[$key]['serverTotalSpins'] = $serverTotalSpins;
                }

                set_query_var('prepareServers', $prepareServers);
            }
        }

        load_template(
            CWS_GAMES_ABSPATH_TEMPLATE . '/backend/providers.php'
        );
    }
}

if (!function_exists('cws_games_login_rewards')) {
    /**
     *  Login Rewards 
     *
     * @return void
     */
    function cws_games_login_rewards()
    {
        load_template(
            CWS_GAMES_ABSPATH_TEMPLATE . '/backend/login-rewards.php'
        );
    }
}


function cws_games_register_settings()
{   
    if(isset($_POST['cws_games_plugin']['api_'])) {
        cws_games_plugin_convert_new_api_server();
    } 
    
    cws_games_plugin_move_existing_server();

    // General Settings
    register_setting('cws_games_plugin_general', 'cws_games_plugin_general', 'cws_games_plugin_options_validate');

    add_settings_section('cws_games_plugin_general_settings', 'General', 'cws_games_plugin_settings', 'cws_games_plugin_general');

    add_settings_field('cws_games_plugin_games_sign_in_url', 'Sign In Page', 'cws_games_plugin_games_sign_in_url', 'cws_games_plugin_general', 'cws_games_plugin_general_settings');

    add_settings_field('cws_games_plugin_games_home_logged_in', 'Home Page (Logged-In)', 'cws_games_plugin_games_home_logged_in', 'cws_games_plugin_general', 'cws_games_plugin_general_settings');

    add_settings_field('cws_games_plugin_games_home_logged_out', 'Home Page (Logged-Out)', 'cws_games_plugin_games_home_logged_out', 'cws_games_plugin_general', 'cws_games_plugin_general_settings');

    // Config
    register_setting('cws_games_plugin', 'cws_games_plugin', 'cws_games_plugin_options_validate');

    // Backoffice
    add_settings_section('cws_games_plugin_settings_backoffice', 'Backoffice', 'cws_games_plugin_settings', 'cws_games_plugin');
    add_settings_field('cws_games_plugin_games_backoffice_api_url', 'API URL', 'cws_games_plugin_games_backoffice_api_url', 'cws_games_plugin', 'cws_games_plugin_settings_backoffice');
    add_settings_field('cws_games_plugin_games_backoffice_backoffice_api_url', 'Backoffice API URL', 'cws_games_plugin_games_backoffice_backoffice_api_url', 'cws_games_plugin', 'cws_games_plugin_settings_backoffice');
    add_settings_field('cws_games_plugin_games_backoffice_user', 'Backoffice User', 'cws_games_plugin_games_backoffice_user', 'cws_games_plugin', 'cws_games_plugin_settings_backoffice');
    add_settings_field('cws_games_plugin_games_backoffice_pass', 'Backoffice Pass', 'cws_games_plugin_games_backoffice_pass', 'cws_games_plugin', 'cws_games_plugin_settings_backoffice');
    add_settings_field('cws_games_plugin_games_backoffice_access_token', 'Backoffice Access Token', 'cws_games_plugin_games_backoffice_access_token', 'cws_games_plugin', 'cws_games_plugin_settings_backoffice');
    add_settings_field('cws_games_plugin_games_backoffice_token', 'Client Access Token', 'cws_games_plugin_games_backoffice_token', 'cws_games_plugin', 'cws_games_plugin_settings_backoffice');

    // Config - Currencies 
    add_settings_section('cws_games_plugin_settings_currencies', 'Currencies', 'cws_games_plugin_settings', 'cws_games_plugin');
    add_settings_field('cws_games_plugin_games_currencies', 'Currencies', 'cws_games_plugin_games_currencies', 'cws_games_plugin', 'cws_games_plugin_settings_currencies');
    add_settings_field('cws_games_plugin_games_jackpot', 'Jackpot', 'cws_games_plugin_games_jackpot', 'cws_games_plugin', 'cws_games_plugin_settings_currencies');
   
    // Config - Referral Settings 
    add_settings_section('cws_games_plugin_settings_referral', 'Referral Settings', 'cws_games_plugin_settings', 'cws_games_plugin');
    
    add_settings_field('cws_games_plugin_games_referral_new_user_amount', 'Amount for newly registered user', 'cws_games_plugin_games_referral_new_user_amount', 'cws_games_plugin', 'cws_games_plugin_settings_referral');
    add_settings_field('cws_games_plugin_games_referral_referrer_user_amount', 'Amount for referrer user', 'cws_games_plugin_games_referral_referrer_user_amount', 'cws_games_plugin', 'cws_games_plugin_settings_referral');
    add_settings_field('cws_games_plugin_games_referral_new_user_message', 'Message to newly registered user', 'cws_games_plugin_games_referral_new_user_message', 'cws_games_plugin', 'cws_games_plugin_settings_referral');
    add_settings_field('cws_games_plugin_games_referral_referrer_user_message', 'Message to referrer user', 'cws_games_plugin_games_referral_referrer_user_message', 'cws_games_plugin', 'cws_games_plugin_settings_referral');
    
    
    // Login Rewards 
    register_setting('cws_games_plugin_login_rewards', 'cws_games_plugin_login_rewards', 'cws_games_plugin_options_validate');
    
    add_settings_section('cws_games_plugin_settings_login_rewards', 'Login Rewards', 'cws_games_plugin_settings', 'cws_games_plugin_login_rewards'); 
    add_settings_field('cws_games_plugin_enable_login_rewards', 'Enable Login Rewards', 'cws_games_plugin_enable_login_rewards', 'cws_games_plugin_login_rewards', 'cws_games_plugin_settings_login_rewards');
    add_settings_field('cws_games_plugin_login_reward_amount', 'Reward Amount', 'cws_games_plugin_login_reward_amount', 'cws_games_plugin_login_rewards', 'cws_games_plugin_settings_login_rewards');
    add_settings_field('cws_games_plugin_login_reward_interval', 'Reward Interval (in Hours)', 'cws_games_plugin_login_reward_interval', 'cws_games_plugin_login_rewards', 'cws_games_plugin_settings_login_rewards');
 
}

add_action('admin_init', 'cws_games_register_settings'); 

// add_action('admin_init', 'cws_games_plugin_validate_server_fields');

function cws_games_plugin_settings()
{

}

/**
 * Moving the new API server to the correct  list  of servers 
 *
 * @return void
 */
function cws_games_plugin_convert_new_api_server() {

    $key = array_key_first($_POST['cws_games_plugin']['api_']); 
    $new_api_server = $_POST['cws_games_plugin']['api_'][$key];

    if(!empty($new_api_server['api_url']) && !empty($new_api_server['api_server_type'] && !empty($new_api_server['api_server_alias']) && !empty($new_api_server['api_return_url']))) {

        $_POST['cws_games_plugin']['api_'.$new_api_server['api_server_type']][$key] = $new_api_server;
        $_POST['cws_games_plugin']['api_'.$new_api_server['api_server_type']][$key]['new_server'] = 1; 

    } else{ 
        add_settings_error('cws_games_plugin', 'empty-fields', "Fields (*) are required!", 'error');
    }


    
    unset($_POST['cws_games_plugin']['api_']);
}

/**
 * Moving already existing server to the correct list of servers 
 *
 * @return void
 */
function cws_games_plugin_move_existing_server() {

    foreach (CWS_GamesConfig::getServerTypes() as $api_server_key => $api_server_label) {
        if (isset($_POST['cws_games_plugin']['api_'.$api_server_key])) {
            $servers_data = $_POST['cws_games_plugin']['api_'.$api_server_key];

            if ($servers_data) {
                foreach($servers_data as $server_id => $server_data){
                    if($server_data['api_server_type'] !== $api_server_key && $server_data['api_server_type'] !== '') {
                        $_POST['cws_games_plugin']['api_'.$server_data['api_server_type']][$server_id] = $server_data;
                        unset($_POST['cws_games_plugin']['api_'.$api_server_key][$server_id]);
                    }
                    if($server_data['api_server_type'] == '' && empty($server_data['api_url']) && empty($server_data['api_server_alias']) && empty($server_data['api_return_url']) && empty($server_data['api_server_id']) && $server_data['api_server_status'] == ''){
                        unset($_POST['cws_games_plugin']['api_'.$api_server_key][$server_id]);
                    }
                }
            }
        }
    }
}

/**
 * Validate Server settings before insert/ update 
 *
 * @return void
 */
function cws_games_plugin_validate_api_server_fields($servers_data, $api_server_type)
{ 
    $validation_status = true;

    foreach ($servers_data as $server_id => $server_data) {

        if (($server_data['api_server_type'] !== '') && !empty($server_data['api_url']) && !empty($server_data['api_server_alias'])) {
            if (isset($server_data['new_server']) && $server_data['new_server'] == 1) {
                // unset($servers_data[$server_id]);
            } else {
                
                //Check if the servers has published games
                // $custom_post_type = 'game';
                // $field_name = 'server_id';
                // $posts = pods($custom_post_type)->find(['limit' => -1]);
                

                // foreach ($posts as $post) {
                //     $field_values = $post->get_field($field_name);

                //     if (is_array($field_values) && !empty($field_values)) {

                //         if (in_array($server_data['api_server_id'], $field_values)) {

                //             unset($servers_data[$server_id]);
                //         }
                //     } else {
                //         echo "No values found for the server_id field.<br>";
                //     }
                // }
            }
        }else {
            $validation_status = false;
            if (empty($server_data['api_server_type'])) {
                add_settings_error('cws_games_plugin', 'empty-fields', "API Server Type is required!", 'error');
            }
            
            if (empty($server_data['api_url'])) {
                add_settings_error('cws_games_plugin', 'empty-fields', "API Server (URL) is required!", 'error');
            }

            if (empty($server_data['api_return_url'])) {
                add_settings_error('cws_games_plugin', 'empty-fields', "Games Import URL is required!", 'error');
            }

            if (empty($server_data['api_server_alias'])) {
                add_settings_error('cws_games_plugin', 'empty-fields', "API Server Alias is required!", 'error');
            }

        }
    }
 
    // If you want to update the original $_POST array, you can reassign the modified $servers_data back to it.
    $_POST['cws_games_plugin']['api_' . $api_server_type] = $servers_data;

    return $validation_status;
}



function cws_games_plugin_options_validate($input)
{ 
    $errors = false;

    if (isset($input['login_reward_amount'])) {
        $input['login_reward_amount'] = trim($input['login_reward_amount']);
    }

    if (isset($input['login_reward_interval'])) {
        $input['login_reward_interval'] = trim($input['login_reward_interval']);
    } 
    
    // Validate API Paid servers 
    if (isset($input['api_paid'])) {
       $validate_paid_server = cws_games_plugin_validate_api_server_fields($input['api_paid'] ,'paid'); 
       if( !$validate_paid_server){ 
        $errors = true; 
       }
    } 

    // Validate API Free servers 
    if (isset($input['api_free'])) {
        $validate_free_server = cws_games_plugin_validate_api_server_fields($input['api_free'] , 'free');
       if( !$validate_free_server){ 
        $errors = true; 
       }
    }  
 

    if ($errors) { 
        return get_option('cws_games_plugin');
    }
 
    
    return $input;
}


function cws_games_plugin_display_server_fields($server_data)
{
    $cws_games_plugin = get_option('cws_games_plugin'); 
   
    if (!empty($cws_games_plugin['api_paid'])) {
        echo '<h2>Available Paid Servers</h2>';
        foreach ($cws_games_plugin['api_paid'] as $server_key => $server_data) {
            echo '<div class="server-container">';
            echo '<table class="form-table cws-games-table-paid">';
            echo '<tbody>';
            cws_games_plugin_api_server($server_data, $server_key);
            echo '</tbody>';
            echo '</table>';
            echo '<td colspan="2"><button type="button" class="btn-clear btn-clear-paid">Clear Fields</button></td>';
            echo '</div>';
        }
    }
    
    if (!empty($cws_games_plugin['api_free'])) {
        echo '<hr>';
        echo '<h2>Available Free Servers</h2>';
        foreach ($cws_games_plugin['api_free'] as $server_key => $server_data) {
            echo '<div class="server-container">';
            echo '<table class="form-table cws-games-table-free">';
            echo '<tbody>';
            cws_games_plugin_api_server($server_data, $server_key);
            echo '</tbody>';
            echo '</table>';
            echo '<td colspan="2"><button type="button" class="btn-clear btn-clear-free">Clear Fields</button></td>';
            echo '</div>';
        }
    }
    
 
    echo '<hr style="margin: 20px 0;">';
    echo '<table class="form-table" >';
    echo '<tbody>';
    echo '<h2>Add New API Server</h2>'; 
    cws_games_plugin_api_server([], ''); 
    echo '</tbody>'; 
    echo '</table>';
    echo '<hr style="margin: 20px 0;">'; 

}
 

/**
 * Undocumented function
 *
 * @param [type] $server_data
 * @param string $api_server_id
 * @return void
 */
function cws_games_plugin_api_server($server_data, $api_server_id = '')
{ 
    if(empty($api_server_id)) {

        $api_server_id = md5(CWS_GamesConfig::generateRandomString());
        $api_server_type = '';

    } else {// edit mode

        $api_server_type = $server_data['api_server_type'];
    }

    // if (isset($_POST['cws_games_plugin']) && isset($_POST['cws_games_plugin']['api_' . $server_type][$api_server_id]['api_server_type'])) {
    //     $api_server_type = $_POST['cws_games_plugin']['api_' . $server_type][$api_server_id]['api_server_type'];
    // }
 
    ob_start();
    ?>
        <tr><td colspan="2">&nbsp;&nbsp;&nbsp;</td></tr>
        <tr><td colspan="2">&nbsp;&nbsp;&nbsp;</td></tr>
        
        <tr>
            <td scope="row">API Server Type: </td>
            <td>
                <select id="cws_games_plugin_api_server_<?= $api_server_id?>" name="cws_games_plugin[api_<?= $api_server_type?>][<?= $api_server_id ?>][api_server_type]" type='text' value="<?= $api_server_type ?>" style="width:400px;">
                    <option value="">Select API Server Type...</option>
                    <?php
                        foreach (CWS_GamesConfig::getServerTypes() as $api_server_key => $api_server_label) {
                            echo "<option value='".  $api_server_key. "' ". selected($api_server_type, $api_server_key). ">". $api_server_label . "</option>";
                        }
                  ?>
                </select>

            </td>
         </tr> 
        <tr>
            <td scope="row">*API Server URL: </td>
            <td>
                <input id="cws_games_plugin_api_server_<?= $api_server_id?>" name="cws_games_plugin[api_<?= $api_server_type?>][<?= $api_server_id ?>][api_url]" type='text' value="<?= $server_data['api_url'] ?? '' ?>" style="width:400px;" />
            </td>
         </tr> 
         <tr>
         <td scope="row">*Games import URL: </td>
            <td>
                <input id="cws_games_plugin_api_server_<?= $api_server_id?>" name="cws_games_plugin[api_<?= $api_server_type?>][<?= $api_server_id ?>][api_return_url]" type='text' value="<?= $server_data['api_return_url'] ?? '' ?>" style="width:400px;" />
            </td>
         </tr> 
         <tr>
            <td scope="row">API Key: </td>
            <td>
                <input id="cws_games_plugin_api_server_<?= $api_server_id?>" name="cws_games_plugin[api_<?= $api_server_type?>][<?= $api_server_id ?>][api_key]" type='text' value="<?= $server_data['api_key'] ?? '' ?>" style="width:400px;" />
            </td>
         </tr> 
         <tr>
            <td scope="row">*API Server Alias: </td>
            <td>
                <input id="cws_games_plugin_api_server_<?= $api_server_id?>" name="cws_games_plugin[api_<?= $api_server_type?>][<?= $api_server_id ?>][api_server_alias]" type='text' value="<?= $server_data['api_server_alias'] ?? ''?>" style="width:400px;" />
            </td>
         </tr> 
         <tr>
            <td scope="row">API Server Status: </td>
            <td>
                <select id="cws_games_plugin_api_server_<?= $api_server_id?>" name="cws_games_plugin[api_<?= $api_server_type?>][<?= $api_server_id ?>][api_server_status]" type='text' value="<?= $server_data['api_server_status'] ?? '' ?>" style="width:400px;" >
                    <option value="">Select API Server Status...</option>
                    <?php
                        foreach (CWS_GamesConfig::getServerStatus() as $api_server_key => $api_server_label) {
                            echo "<option value='".  $api_server_key. "' ". selected($server_data['api_server_status'], $api_server_key). ">". $api_server_label . "</option>";
                        }
                  ?>
                </select>
            </td>
         </tr> 
         <tr>
            <td scope="row">Provider ID: </td>
            <td>
                <input id="cws_games_plugin_api_server_<?= $api_server_id?>" readonly name="cws_games_plugin[api_<?= $api_server_type?>][<?= $api_server_id ?>][api_server_id]" type='text' value="<?= $server_data['api_server_id'] ?? $api_server_id ?>" style="width:400px;" />
            </td>
         </tr> 

    <?php
	$content = ob_end_flush();
    echo $content;
}

function cws_games_plugin_games_currencies()
{
    $options = get_option('cws_games_plugin'); 
    $currencies = $options['currencies'] ?? [];
    $max_currencies = count($currencies);
    __sd($options, "cws_games_plugin_games_currencies" );
    

    if (isset($options['currencies']['default_currency']) && $options['currencies']['default_currency'] != '') {
        $default_currency = intval($options['currencies']['default_currency']);
    }

    ob_start();

    ?>  

        <div style="display: flex; flex-direction: row; gap: 15px; margin-bottom: 15px;">
            <div style="display:flex; flex-direction:column; width: 200px;">
                <label>Currency code (ex. FICO)</label>
            </div>
            <div style="display:flex; flex-direction:column; width: 200px;">
                <label>Currency symbol (ex. FiCo)</label>
            </div>
            <div style="display:flex; flex-direction:column; width: 150px;">
                <label>Default currency</label>
            </div>
            <div style="display:flex; flex-direction:column;">
                <label>Currency Type</label>
            </div>
        </div>

        <?php for($i = 0; $i < $max_currencies; $i++): ?>

            <?php

                $currency_code      = $options['currencies'][$i]['currency_code'] ?? '';
                $currency_symbol    = $options['currencies'][$i]['currency_symbol'] ?? '';
                $currency_type      = $options['currencies'][$i]['type'] ?? '';
                $default_currency   = $options['currencies'][$i]['default_currency'] ?? '';
            ?>

            <?php if ($currency_code != ''  && $currency_symbol != '') { ?>

            <div style="display: flex; flex-direction: row; gap: 15px; margin-bottom: 5px;">
                <div style="display:flex; flex-direction:column; width: 200px;">
                    <input type="text"
                           class="currency_code_<?= $i ?>"
                           name="cws_games_plugin[currencies][<?= $i ?>][currency_code]"
                           value="<?= $currency_code ?? '' ?>"
                           readonly
                    />
                </div>
                <div style="display:flex; flex-direction:column; width: 200px;">
                    <input type="text"
                           class="currency_symbol_<?= $i ?>"
                           name="cws_games_plugin[currencies][<?= $i ?>][currency_symbol]"
                           value="<?= $currency_symbol ?? '' ?>"
                           readonly
                    />
                </div>
                <div style="display:flex; flex-direction:column; justify-content: center; width: 150px;">
                    <input type="radio"
                           class="default_currency"
                           name="cws_games_plugin[currencies][<?= $i ?>][default_currency]"
                           value="1"
                           <?= $default_currency ? 'checked="checked"' : '' ?>
                           readonly
                    />
                </div>
                <div style="display: flex; flex-direction: row; gap: 15px;">
                    <?= $currency_type ?>
                </div>
            </div>

            <?php } ?>

        <?php endfor; ?>

    <?php

    $content = ob_get_contents();
    ob_end_clean();

    echo $content;

}

function cws_games_plugin_games_jackpot()
{
    $options = get_option('cws_games_plugin');

    ob_start();

    ?>

        <input type="text" name="cws_games_plugin[jackpot]" value="<?= isset($options['jackpot']) && $options['jackpot'] ? $options['jackpot'] : '' ?>" style="width: 415px;" readonly />

    <?php

    $content = ob_get_contents();
    ob_end_clean();

    echo $content;
}

function cws_games_plugin_games_referral_new_user_amount()
{
    $options = get_option('cws_games_plugin');

    echo "<input id='cws_games_plugin_games_referral_new_user_amount' name='cws_games_plugin[games_referral_new_user_amount]' type='text' value='" . (is_array($options) && isset($options['games_referral_new_user_amount']) ? esc_attr($options['games_referral_new_user_amount']) : 100) . "' style='width:400px;' />";
}

function cws_games_plugin_games_referral_referrer_user_amount()
{
    $options = get_option('cws_games_plugin');

    echo "<input id='cws_games_plugin_games_referral_referrer_user_amount' name='cws_games_plugin[games_referral_referrer_user_amount]' type='text' value='" . (is_array($options) && isset($options['games_referral_referrer_user_amount']) ? esc_attr($options['games_referral_referrer_user_amount']) : 50) . "' style='width:400px;' />";
}

function cws_games_plugin_games_referral_new_user_message()
{

    $options = get_option('cws_games_plugin');

    echo "<input id='cws_games_plugin_games_referral_new_user_message' name='cws_games_plugin[games_referral_new_user_message]' type='text' value='" . (is_array($options) && isset($options['games_referral_new_user_message']) ? esc_attr($options['games_referral_new_user_message']) : 'Credit for registering through referral link.') . "' style='width:800px;' />";
}

function cws_games_plugin_games_referral_referrer_user_message()
{

    $options = get_option('cws_games_plugin');

    echo "<input id='cws_games_plugin_games_referral_referrer_user_message' name='cws_games_plugin[games_referral_referrer_user_message]' type='text' value='" . (is_array($options) && isset($options['games_referral_referrer_user_message']) ? esc_attr($options['games_referral_referrer_user_message']) : 'Credit for new user registering through your referral link.') . "' style='width:800px;' />";
}

function cws_games_plugin_enable_login_rewards()
{
    $options = get_option('cws_games_plugin_login_rewards');

    echo "<input id='cws_games_plugin_enable_login_rewards' name='cws_games_plugin_login_rewards[enable_login_rewards]' type='checkbox' value='1' " . (is_array($options) && isset($options['enable_login_rewards']) && $options['enable_login_rewards'] == 1 ? 'checked="checked"' : '') . " />";
}

function cws_games_plugin_login_reward_amount()
{
    $options = get_option('cws_games_plugin_login_rewards');

    echo "<input id='cws_games_plugin_login_reward_amount' name='cws_games_plugin_login_rewards[login_reward_amount]' type='text' value='" . (is_array($options) && isset($options['login_reward_amount']) ? esc_attr($options['login_reward_amount']) : '') . "' placeholder='Ex: 10000' style='width:200px;' /> FiCo";
}

function cws_games_plugin_login_reward_interval()
{
    $options = get_option('cws_games_plugin_login_rewards');

    echo "<input id='cws_games_plugin_login_reward_interval' name='cws_games_plugin_login_rewards[login_reward_interval]' type='text' value='" . (is_array($options) && isset($options['login_reward_interval']) ? esc_attr($options['login_reward_interval']) : '') . "' placeholder='Ex: 24' style='width:200px;' /> Hours";
}

function cws_games_plugin_games_backoffice_token()
{
    $token = md5( home_url('/') );

    echo "<input type='text' name='cws_games_plugin[backoffice][token]' value='".$token."' style='width: 400px;' readonly />";
}

function cws_games_plugin_games_backoffice_api_url()
{
    $api_url = home_url('/') . 'backofficeAPI';

    echo "<input type='text' name='cws_games_plugin[backoffice][api_url]' value='".$api_url."' style='width: 400px;' readonly />";
}


function cws_games_plugin_games_backoffice_backoffice_api_url()
{
    $options = get_option('cws_games_plugin');
    $backoffice_api_url = $options['backoffice']['backoffice_api_url'];

    echo "<input type='text' name='cws_games_plugin[backoffice][backoffice_api_url]' value='".$backoffice_api_url."' style='width: 400px;' />";
}

function cws_games_plugin_games_backoffice_user()
{
    $options = get_option('cws_games_plugin');

    ?> 
    <input type="text"
        id="backoffice_user"
        name="cws_games_plugin[backoffice][user]"
        value="<?= $options['backoffice']['user'] ?? '' ?>"
        style="width: 400px;"
    />
    <?php
}

function cws_games_plugin_games_backoffice_pass()
{
    $options = get_option('cws_games_plugin');

    ?> 
        <input type="text"
            id="backoffice_pass"
            name="cws_games_plugin[backoffice][pass]"
            value="<?= $options['backoffice']['pass'] ?? '' ?>"
            style="width: 400px;"
        />
    <?php
}

function cws_games_plugin_games_backoffice_access_token()
{
    $options = get_option('cws_games_plugin');

    echo "<input type='text' id='apiToken' name='cws_games_plugin[backoffice][access_token]' value='". ($options['backoffice']['access_token'] ?? '') . "' style='width: 400px;' readonly />";

    ?>
        <a href="javascript:void(0)" id="apiTokenButton" class="button button-primary" style="margin-left: 20px">Get Access Token</a>  
        <span id="statusMessage" style="margin-left: 20px"></span> 
            
    <?php
}

function cws_games_plugin_games_sign_in_url()
{
    $options = get_option('cws_games_plugin_general');

    $query = new WP_Query(array(
        'post_type' => 'page',
        'posts_per_page' => -1,
        'post_status' => 'publish'
    ));

    ob_start();
    ?>
        <select name="cws_games_plugin_general[sign_in_url]">
            <option value="-1"><?= __('Select page', 'cws_games') ?></option>
            <?php if ($query->have_posts()): ?>
                <?php foreach ($query->posts as $post): ?>
                    <option value="<?= $post->ID ?>" <?= isset($options['sign_in_url']) && $options['sign_in_url'] == $post->ID ? 'selected="selected"' : '' ?>><?= $post->post_title ?></option>
                <?php endforeach; ?>
            <?php endif; ?>
        </select>
    <?php
    $field = ob_get_clean();

    echo $field;
}

function cws_games_plugin_games_home_logged_in()
{
    $options = get_option('cws_games_plugin_general');

    $query = new WP_Query(array(
        'post_type' => 'page',
        'posts_per_page' => -1,
        'post_status' => 'publish'
    ));

    ob_start();
    ?>
        <select name="cws_games_plugin_general[home_logged_in]">
            <option value="-1"><?= __('Select page', 'cws_games') ?></option>
            <?php if ($query->have_posts()): ?>
                <?php foreach ($query->posts as $post): ?>
                    <option value="<?= $post->ID ?>" <?= isset($options['home_logged_in']) && $options['home_logged_in'] == $post->ID ? 'selected="selected"' : '' ?>><?= $post->post_title ?></option>
                <?php endforeach; ?>
            <?php endif; ?>
        </select>
    <?php
    $field = ob_get_clean();

    echo $field;
}

function cws_games_plugin_games_home_logged_out()
{
    $options = get_option('cws_games_plugin_general');

    $query = new WP_Query(array(
        'post_type' => 'page',
        'posts_per_page' => -1,
        'post_status' => 'publish'
    ));

    ob_start();
    ?>
        <select name="cws_games_plugin_general[home_logged_out]">
            <option value="-1"><?= __('Select page', 'cws_games') ?></option>
            <?php if ($query->have_posts()): ?>
                <?php foreach ($query->posts as $post): ?>
                    <option value="<?= $post->ID ?>" <?= isset($options['home_logged_out']) && $options['home_logged_out'] == $post->ID ? 'selected="selected"' : '' ?>><?= $post->post_title ?></option>
                <?php endforeach; ?>
            <?php endif; ?>
        </select>
    <?php
    $field = ob_get_clean();

    echo $field;
}


