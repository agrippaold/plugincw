<?php


// Hooks & Functions
require_once dirname(__FILE__) . '/backend/functions.php';
require_once dirname(__FILE__) . '/backend/hooks.php';

require_once dirname(__FILE__) . '/frontend/functions.php';
require_once dirname(__FILE__) . '/frontend/hooks.php';

// Shortcodes
require_once dirname(__FILE__) . '/frontend/shortcodes.php';

// Classes
require_once dirname(__FILE__) . '/libs/BrowserDetection.php';
require_once dirname(__FILE__) . '/libs/classes/CWS_GamesAPI.php';
require_once dirname(__FILE__) . '/libs/classes/CWS_GamesAPIValidation.php';
require_once dirname(__FILE__) . '/libs/classes/CWS_GamesGameslist.php';
require_once dirname(__FILE__) . '/libs/classes/CWS_GamesMostPlayed.php';
require_once dirname(__FILE__) . '/libs/classes/CWS_GamesMyGames.php';
require_once dirname(__FILE__) . '/libs/classes/CWS_GamesTransactions.php';
require_once dirname(__FILE__) . '/libs/classes/CWS_GamesLoginRewards.php';
require_once dirname(__FILE__) . '/libs/classes/CWS_GamesServers.php';
require_once dirname(__FILE__) . '/libs/classes/CWS_GamesSessions.php';
require_once dirname(__FILE__) . '/libs/classes/CWS_GamesCsvExport.php';
require_once dirname(__FILE__) . '/libs/classes/CWS_GamesUsers.php';

require_once dirname(__FILE__) . '/libs/list_tables/cws_games_gameslist_table.php';
require_once dirname(__FILE__) . '/libs/list_tables/cws_games_sessions_table.php';
require_once dirname(__FILE__) . '/libs/list_tables/cws_games_transactions_table.php';

require_once dirname(__FILE__) . '/ajax.php';

// Back Office
require_once dirname(__FILE__) . '/libs/classes/backoffice/CWS_GamesBackOfficeAPI.php';
require_once dirname(__FILE__) . '/libs/classes/backoffice/CWS_BackofficeAPI.php';

require_once dirname(__FILE__) . '/backoffice-api.php';

/**
 * Site Debug , log function
 *
 * @param [type] $url
 * @param [type] $data
 * @return void
 */
function __sd($data, $text = '')
{ 
    if (!file_exists(ABSPATH . "/tmp")) {
      mkdir(ABSPATH . "/tmp", 0777, true);
    }

    file_put_contents(ABSPATH . "/tmp/request.txt" , "\n ======== ".
                          date("Y-m-d H:i:s", time() ) . " ======== " . getUserIP() . " ======== " . $text .
                          "\n data: ". print_r( $data ,1 ), FILE_APPEND );
}

/**
 * recognize are data send as form-data or application/json 
 *
 * @return array
 */
function cws_takeAjaxData( $method = 'POST')
{ 
  $data = [];

  if (isset($_SERVER['CONTENT_TYPE'])) {
      $contentType = $_SERVER['CONTENT_TYPE'];
        // __sd($_SERVER, 'takeAjaxData  _SERVER');  
      if (strpos($contentType, 'application/json') !== false) {
          // Handle JSON data 
            $data = json_decode( file_get_contents("php://input") , true ); 
      } elseif (strpos($contentType, 'multipart/form-data') !== false) {
          // Handle form data 
            $data = $_POST; 
      } else {
          // Handle other content types as necessary
      }
  } 

  __sd($_POST, 'takeAjaxData POST');
  __sd($_GET, 'takeAjaxData GET');
  __sd($data, 'ContentType:'. $contentType);
  
  return $data ;
}

// Function to get the user IP address
function getUserIP() {
    $ipaddress = '';
    if (isset($_SERVER['HTTP_CLIENT_IP']))
        $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
    else if(isset($_SERVER['HTTP_X_FORWARDED_FOR']))
        $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
    else if(isset($_SERVER['HTTP_X_FORWARDED']))
        $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
    else if(isset($_SERVER['HTTP_X_CLUSTER_CLIENT_IP']))
        $ipaddress = $_SERVER['HTTP_X_CLUSTER_CLIENT_IP'];
    else if(isset($_SERVER['HTTP_FORWARDED_FOR']))
        $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
    else if(isset($_SERVER['HTTP_FORWARDED']))
        $ipaddress = $_SERVER['HTTP_FORWARDED'];
    else if(isset($_SERVER['REMOTE_ADDR']))
        $ipaddress = $_SERVER['REMOTE_ADDR'];
    else
        $ipaddress = 'UNKNOWN';
    return $ipaddress;
}

define("CWS_GAMES_ASSETS_VERSION", rand(1000, 9999));
define("CWS_GAMES_ABSPATH_ASSETS", plugin_dir_url( __FILE__ ) . '/assets');
define("CWS_GAMES_ASSETS", plugin_dir_path( __FILE__ ) . '/assets');
define("CWS_GAMES_ABSPATH_TEMPLATE", dirname(__FILE__) . '/templates');

define("CWS_GAMES_API_AMOUNT_EXCHANGE_RATE", 1);

/**
 * CWS Games CURL Function
 * @param $url - rest api url
 * @param $data - array of POST parameters
 * @param $headers - array of header parameters
 * @return void
 */
function cwsGamesCURL($url, $type = 'POST', $data = [], $headers = [])
{
    $curl = curl_init(); 
    
    if ($type == 'POST') {
      curl_setopt($curl, CURLOPT_POST, true);
    } else {
      curl_setopt($curl, CURLOPT_POST, false);
    }
    
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_VERBOSE, true);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

    if (!empty($headers)) {
        curl_setopt($curl, CURLOPT_HEADER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    } else {
        curl_setopt($curl, CURLOPT_HEADER, false);
    }

    if (!empty($data)) {
      curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query( $data ));
    }

    $response = curl_exec($curl); 
    curl_close($curl); 

    return $response;
}


class CWS_GamesConfig
{

  public function admin()
  {
    add_menu_page('CWS Games', 'CWS Games', 'manage_options', 'cws-games', array($this, 'CWS_GamesHomepage'), '', 20);

    add_submenu_page(null, 'CWS Games General', 'CWS Games General', 'manage_options', 'cws-games-general-settings', array($this, 'CWS_GamesGeneralSettings'));

    add_submenu_page(null, 'CWS Games Config', 'CWS Games Config', 'manage_options', 'cws-games-settings', array($this, 'CWS_GamesSettings'));

    add_submenu_page(null, 'CWS Games List', 'CWS Games List', 'manage_options', 'cws-games-gameslist', array($this, 'CWS_GamesGameslist'));

    add_submenu_page(null, 'CWS Games Sessions', 'CWS Games Sessions', 'manage_options', 'cws-games-sessions', array($this, 'CWS_GamesSessions'));

    add_submenu_page(null, 'CWS Games Session Details', 'CWS Games Session Details', 'manage_options', 'cws-games-session-details', array($this, 'CWS_GamesSessionDetails'));

    add_submenu_page(null, 'CWS Games Providers', 'CWS Games Providers', 'manage_options', 'cws-games-providers', array($this, 'CWS_GamesProviders'));

    if (post_type_exists('game')) {
        add_submenu_page('cws-games', 'Games List', 'Games List', 'manage_options', 'edit.php?post_type=game', '');
    }

    add_submenu_page(null, 'CWS Games Login Rewards', 'CWS Games Login Rewards', 'manage_options', 'cws-games-login-rewards', array($this, 'CWS_GamesLoginRewards'));
  }

  public function CWS_GamesHomepage()
  {
    do_action('cws_games_homepage');
  }

  public function CWS_GamesGeneralSettings()
  {
    do_action('cws_games_general_settings');
  }

  public function CWS_GamesSettings()
  {
    do_action('cws_games_config');
  }

  public function CWS_GamesGameslist()
  {
    do_action('cws_games_gameslist');
  }

  public function CWS_GamesSessions()
  {
    do_action('cws_games_sessions');
  }

  public function CWS_GamesSessionDetails()
  {
    do_action('cws_games_session_details');
  }

  public function CWS_GamesProviders()
  {
    do_action('cws_games_providers');
  }

  public function CWS_GamesLoginRewards()
  {
    do_action('cws_games_login_rewards');
  }

  
  /**
   * Return List of servers
   *
   * @param [string] $server_type
   * @param [string] $api_server_id
   * 
   * @return void
   */
  public static function CWS_GamesGetAllServerData($server_type , $api_server_id = '')
  {
    $cws_games_plugin = get_option('cws_games_plugin');
 
    $server_data = [];
    if (isset($cws_games_plugin['api_'.$server_type]) && is_array($cws_games_plugin['api_'.$server_type])) {

        foreach ($cws_games_plugin['api_'.$server_type] as $server_info) {

            if(!empty($api_server_id) ) {

              if($api_server_id == $server_info['api_server_id']) {
                  $server_data =  $server_info;
                  break;
              }

            } else { // list all 
                $server_data[] =  $server_info;
            }
        }
    }

    return $server_data;
  }

  /**
   * Ureturn all server data 
   *
   * @param [type] $server_id
   * @return array
   */
  public function CWS_GamesGetServerData($server_id){
      
      $cws_games_plugin = get_option('cws_games_plugin');

      if ( isset($cws_games_plugin['api_free']) ) {
        foreach ($cws_games_plugin['api_free'] as $server_info) {

          if($server_id == $server_info['api_server_id']) {
            return $server_info;
            break;
          }
        }
      }

      if ( isset($cws_games_plugin['api_paid']) ) {
        foreach ($cws_games_plugin['api_paid'] as $server_info) {

          if($server_id == $server_info['api_server_id']) {
            return $server_info;
            break;
          }
        }
      }

      // server not found 
      return [];
  }

  /**
   * Return Games Import Url
   *
   * @return void
   */
  public static function CWS_GamesGetGamesImportUrl($source)
  {
    //TODO: use CWS_GamesGetAllServerData() method
    // $options = get_option('cws_games_plugin');
    // return $options['games_import_'.$source] ?? '';
  }

  /**
   * Return Login Rewards - Enabled
   *
   * @return void
   */
  public static function CWS_GamesGetLoginRewardsEnabled()
  {
    $options = get_option('cws_games_plugin_login_rewards');
    return $options['enable_login_rewards'] ?? 0;
  }

  /**
   * Return Login Rewards - Amount
   *
   * @return void
   */
  public static function CWS_GamesGetLoginRewardsAmount()
  {
    $options = get_option('cws_games_plugin_login_rewards');
    return $options['login_reward_amount'] ?? 0;
  }

  /**
   * Return Login Rewards - Interval
   *
   * @return void
   */
  public static function CWS_GamesGetLoginRewardsInterval()
  {
    $options = get_option('cws_games_plugin_login_rewards');
    return $options['login_reward_interval'] ?? 0;
  }




  /**
   * Return newly registered user credit amount
   *
   * @return void
   */
  public static function CWS_GamesGetNewUserCreditAmount()
  {
    $options = get_option('cws_games_plugin');
    return $options['games_referral_new_user_amount'] ?? '';
  }

  /**
   * Return newly registered user referrer credit amount
   *
   * @return void
   */
  public static function CWS_GamesGetNewUserReferrerCreditAmount()
  {
    $options = get_option('cws_games_plugin');
    return $options['games_referral_referrer_user_amount'] ?? '';
  }

  /**
   * Return newly registered user credit message
   *
   * @return void
   */
  public static function CWS_GamesGetNewUserCreditMessage()
  {
    $options = get_option('cws_games_plugin');
    return $options['games_referral_new_user_message'] ?? '';
  }

  /**
   * Return newly registered user credit message
   *
   * @return void
   */
  public static function CWS_GamesGetNewUserReferrerCreditMessage()
  {
    $options = get_option('cws_games_plugin');
    return $options['games_referral_referrer_user_message'] ?? '';
  }

 
  /**
   * Return Server Types
   *
   */
  public static function getServerTypes(){
    return [
      'free' => 'Free Server',
      'paid' => 'Paid Server'
    ];
  }

  public static function getServerStatus(){
    return [
      'active' => 'Active',
      'inactive' => 'Inactive'
    ];

  }

  public static function generateRandomString($length = 10){ 
      $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
      $charactersLength = strlen($characters);
      $randomString = '';
      for ($i = 0; $i < $length; $i++) {
          $randomString .= $characters[rand(0, $charactersLength - 1)];
      }
      return $randomString; 
  }


  /**
   * Return User from `wp_users` by token
   *
   * @return void
   */
  public function getUserByToken($token)
  {
      global $wpdb;

      $sql = 'SELECT * FROM ' . $wpdb->prefix . 'users WHERE md5(ID) = "' . $token . '"';

      $row = $wpdb->get_row($sql);

      return $row;
  }

  /**
   * Set Currencies
   *
   * @return [array]
   */
  public function setCurrencies($data)
  {
    __sd($data, "setCurrencies from ajax response" );
    $options = get_option('cws_games_plugin');
    $success = true;

    if ($data && !empty($data)) {
      $options['currencies'] = $data;
      update_option('cws_games_plugin', $options);
      __sd($options['currencies'], "setCurrencies in options database" );
      return $success;
    }

    return !$success;
  }

  /**
   * Set Jackpot
   *
   * @return [array]
   */
  public function setJackpot($data)
  {
    $options = get_option('cws_games_plugin');
    $success = true;

    if ($data && !empty($data)) {
      $options['jackpot'] = $data;
      update_option('cws_games_plugin', $options);
      return $success;
    }

    return !$success;
  }

  /**
   * Return Currencies
   *
   * @return [array]
   */
  public function getCurrencies($indexed = false)
  {
    $currencies = [];

    $options = get_option('cws_games_plugin');

    if (isset($options['currencies']) && !empty($options['currencies'])) {
      foreach ($options['currencies'] as $key => $currency) {
        if (isset($currency['currency_code']) && $currency['currency_code'] != '' && isset($currency['currency_symbol']) && $currency['currency_symbol'] != '') {
          if ($indexed) {
            $currencies[strtoupper($currency['currency_code'])] = $currency;
          } else {
            $currencies[$key] = $currency;
          }
        }
      }
    }

    return $currencies;
  }

  /**
   * Return Currency by currency_code
   *
   * @param [string] $currency_code
   * @return [array] - [currency_code => *, currency_symbol => *]
   */
  public function getCurrencyByCode($currency_code)
  {
    $response = [
      'currency_code'     => '',
      'currency_symbol'   => ''
    ];

    $currencies = $this->getCurrencies();

    if (!empty($currencies)) {
      foreach ($currencies as $currency) {
        
        if (strtoupper($currency['currency_code']) == strtoupper($currency_code)) {
          $response['currency_code'] = $currency['currency_code'];
          $response['currency_symbol'] = $currency['currency_symbol'];
          break;
        }
      }
    }

    return $response;
  }

  /**
   * Return default Currency
   *
   * @param [int] $user_id
   * @param [bool] $check_user - Allows to skip checking the user default currency. TRUE by default. If FALSE - will return the default currency from the plugin settings.
   * @return [array] - [currency_code => *, currency_symbol => *]
   */
  public function getDefaultCurrency($user_id = null, $check_user = true)
  {

    $default_currency = [
      'currency_code'     => '',
      'currency_symbol'   => ''
    ];

    $currencies = $this->getCurrencies();

    if ( $check_user ) { // Allows to skip checking the user default currency. Will return the default currency from the plugin settings
      if ($user_id == null) {
        $user_id = get_current_user_id();
      }

      $user_default_currency = get_user_meta( $user_id, 'user_default_currency', true );

      if ($user_default_currency && $user_default_currency != '') {

        foreach ($currencies as $currency) {
          if (strtoupper($currency['currency_code']) == strtoupper($user_default_currency)) {
            $default_currency['currency_code'] = $currency['currency_code'];
            $default_currency['currency_symbol'] = $currency['currency_symbol'];

            break;
          }
        }

      }

      if (!isset($default_currency['currency_code']) || $default_currency['currency_code'] == '') {
        $options = get_option('cws_games_plugin');

        if (isset($options['currencies']) && !empty($options['currencies'])) {
          foreach ($options['currencies'] as $currency) {
            if (isset($currency['default_currency']) && $currency['default_currency'] == 1) {
              $default_currency['currency_code'] = $currency['currency_code'] ?? '';
              $default_currency['currency_symbol'] = $currency['currency_symbol'] ?? '';
            }
          }
        }
      }

    } else {
      $options = get_option('cws_games_plugin');

      if (isset($options['currencies']) && !empty($options['currencies'])) {
        foreach ($options['currencies'] as $currency) {
          if (isset($currency['default_currency']) && $currency['default_currency'] == 1) {
            $default_currency['currency_code'] = $currency['currency_code'] ?? '';
            $default_currency['currency_symbol'] = $currency['currency_symbol'] ?? '';
          }
        }
      }
    }

    return $default_currency;

  }

  public function getSignInPageUrl()
  {
    $options = get_option('cws_games_plugin_general');

    return (isset($options['sign_in_url']) && $options['sign_in_url'] > 0) ? get_permalink($options['sign_in_url']) : '';
  }

}

