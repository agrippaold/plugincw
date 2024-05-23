<?php
/**
 *  API auth Calls 
 */

 class CWS_BackofficeAPI
 {
 
    //----------------- API action function ------------------
    public function getToken($input_data) {
        // get Server param 
        $pluginOption = get_option('cws_games_plugin');
 
        $action = 'client/guest/user.login';
        $url    = $pluginOption['backoffice']['backoffice_api_url'] . $action;
        $data   = [
            'email'     => trim($pluginOption['backoffice']['user']),
            'password'  => trim($pluginOption['backoffice']['pass'])
        ];
 
        $json = cwsGamesCURL($url, $type = 'POST', $data);

        if ($json) {
            __sd($json, "Import new login - Login data json" );
            $response_data = json_decode($json);
            $token = $response_data->data->token ?? '';
        } else {
            __sd($json, "Import new login failed" );
        }

        if ($token && !empty($input_data['user']) && !empty($input_data['password'])) {

            $result['accessToken']  = $token;
            $result['status']       = 'Success';
                
        } else {
            $result['accessToken']  = '';
            $result['status']       = 'Invalid BackOffice login credentials. Please check your User and/or Password.';
        }
 
        return $result;
    }

    public function getSettings(){
        // get Server param 
        $pluginOption = get_option('cws_games_plugin');
 
        $action = 'client/site.settings';
        $data   = []; 
        $result = $this->makeApiCall($action ,$data);
        
        __sd($result, "makeApiCall in getSettings");

        $errors = [];

        if (count($result['errors']) == 0 ) {
            $settings = [];

            foreach($result['data'] as $key => $setting) {
                if ($key == 'currencies') {
                    foreach ($setting as $_key => $currency) {
                        $settings['currencies'][$_key] = [
                            'currency_code'     => $currency['code'],
                            'currency_symbol'   => $currency['symbol'],
                            'type'              => $currency['type'] ?? '',
                            'default_currency'  => $currency['currency_default'],
                        ];
                    }
                } elseif ($key == 'jackpot') {
                    $settings['jackpot'] = $setting;
                }
            }
        }

        $result = [
            'settings'  => $settings,
            'original'  => $result,
            'errors'    => $errors
        ];
 
        return $result;
    }
  
    public function getGames(){
 
        $action = 'client/site.getGames';
 
        $result = $this->makeApiCall($action ,[]);
 
        return $result;
    }

    /**
     * Get player details from BackOffice
     * 
     * @param [array] $data
     * @return [void]
     */ 
    public function getPlayer($data)
    {
        $action = 'client/site.getPlayer';
 
        $result = $this->makeApiCall($action, $data);
 
        return $result;
    }

    /**
     * Send BackOffice request to add new player
     * 
     * @param [array] $data - user fields
     * @return [void]
     */ 
    public function addPlayer($data)
    {
        $action = 'client/site.addPlayer';

        $result = $this->makeApiCall($action, $data);
 
        return $result;
    }

    /**
     * Send BackOffice request to add login player
     * 
     * @param [array] $data - user fields
     * @return [void]
     */ 
    public function loginPlayer($data)
    {
        $action = 'client/site.loginPlayer';

        $result = $this->makeApiCall($action, $data);
 
        return $result;
    }

    /**
     * Send BackOffice request to add new player
     * 
     * @param [array] $data - user fields
     * @return [void]
     */ 
    public function updatePlayer($data)
    {
        $action = 'client/site.updatePlayer';

        $result = $this->makeApiCall($action, $data);
 
        return $result;
    }

    /**
     * Send BackOffice request to get user wallet details (balance)
     * 
     * @param [array] $data
     * @return [void]
     */ 
    public function getWallets($data)
    {
        $action = 'client/site.getWallets';

        $result = $this->makeApiCall($action, $data);
 
        return $result;
    }

    /**
     * Send BackOffice request to get user wallet transactions
     * 
     * @param [array] $data
     * @return [void]
     */ 
    public function getWalletTransactions($data)
    {
        $action = 'client/site.getWalletTransactions';

        $result = $this->makeApiCall($action, $data);
 
        return $result;
    }
    
    /**
     * Send BackOffice request to add new wallet transaction
     * 
     * @param [array] $data
     * @return [void]
     */ 
    public function addWalletTransaction($data)
    {
        $action = 'client/site.addWalletTransaction';

        $result = $this->makeApiCall($action, $data);
 
        return $result;
    }

    /**
     * Send BackOffice request to get player notification details
     * 
     * @param [array] $data
     * @return [void]
     */ 
    public function getNotification($data)
    {
        $action = 'client/site.getNotification';

        $result = $this->makeApiCall($action, $data);
 
        return $result;
    }

    /**
     * Send BackOffice request to get player notifications
     * 
     * @param [array] $data
     * @return [void]
     */ 
    public function getNotifications($data)
    {
        $action = 'client/site.getNotifications';

        $result = $this->makeApiCall($action, $data);
 
        return $result;
    }
 
     public function gameLaunch($data) {

        $action = 'client/site.gameLaunch';

        $result = $this->makeApiCall($action, $data);

        return $result;
     }

     /**
     * Send BackOffice request to validate new order
     * 
     * @param [array] $data - order fields
     * @return [void]
     */ 
    public function validatePreOrder($data)
    {
        $action = 'client/shop.validatePreOrder';

        $result = $this->makeApiCall($action, $data);
 
        return $result;
    }

    /**
     * Send BackOffice request to add / update order
     * 
     * @param [array] $data - order fields
     * @return [void]
     */ 
    public function addOrUpdateOrder($data)
    {
        $action = 'client/shop.addOrUpdateOrder';

        $result = $this->makeApiCall($action, $data);
 
        return $result;
    }

    /**
     * Get wheel of fortune values
     * 
     * @param [array] $data
     * @return [void]
     */ 
    public function getWheelOfFortune($data)
    {
        $action = 'client/site.getWheelOfFortune';
 
        $result = $this->makeApiCall($action, $data);
 
        return $result;
    }

    /**
     * Spin the wheel of fortune
     * 
     * @param [array] $data
     * @return [void]
     */ 
    public function spinWheelOfFortune($data)
    {
        $action = 'client/site.spinWheelOfFortune';
 
        $result = $this->makeApiCall($action, $data);
 
        return $result;
    }
     
     // ----------------- Real Call  ---------------------------

     public function makeApiCall($action, $data) {

        $response = [
            'data' => [],
            'error' => []
        ];

        $identifications = [
            'ip'            => '',
            'device'        => '',
            'deviceNum'     => '',
            'os'            => '',
            'browser'       => ''
        ];

        $BrowserDetection   = new foroco\BrowserDetection();
        $userAgent          = $_SERVER['HTTP_USER_AGENT'];
        $browserData        = $BrowserDetection->getAll($userAgent);
        
        $identifications['ip'] = getUserIP();

        if (isset($browserData['device_type']) && $browserData['device_type'] != '') {
            if ($browserData['device_type'] == 'desktop') {
                $identifications['device'] = 'PC';
            } else {
                $identifications['device'] = $browserData['device_type'];
            }
        }

        $os_array = [];

        if (isset($browserData['os_name']) && $browserData['os_name'] != '') {
            $os_array[] = $browserData['os_name'];
        }

        if (isset($browserData['os_version']) && $browserData['os_version'] != '') {
            $os_array[] = $browserData['os_version'];
        }

        if (isset($browserData['device_type']) && $browserData['device_type'] != '') {
            $os_array[] = $browserData['device_type'];
        }

        if (isset($browserData['64bits_mode']) && $browserData['64bits_mode'] != '') {
            $os_array[] = $browserData['64bits_mode'];
        }

        $identifications['os'] = implode('/', $os_array);

        $identifications['browser'] = $userAgent;

        $data['identification'] = $identifications;

        // __sd($data, 'TEST 1');
        // __sd($_SERVER, 'TEST 2');

        // get Server param 
        $pluginOption = get_option('cws_games_plugin');
        $url = $pluginOption['backoffice']['backoffice_api_url'] . $action;
        $data["token"] = $data["token"] ?? $pluginOption['backoffice']['token'];

        // get Token
        $bearerToken = $pluginOption['backoffice']['access_token'];

        if ($bearerToken) {
            $headers = [
                        'Authorization: Bearer '. $bearerToken,
                        'Accept: application/json', 
                        'Content-Type: application/json',
                        'Cache-control: no-cache',
                        'Accept-encoding: keep-alive'
                       ];

            // make a API Call
            $data = $this->sendPOST($url, $data, $headers); 
            // __sd($data , "json response 2" );

            if ($data) {

                  $response = $data;
            } else {

                $response['error'] = 'Error fetch data';
            }
        } else {
            $response['error'] = 'Bearer Token does not  exist';
        }

        return $response ?? [];
    }

    /**
     * CWS Games CURL Function
     * @param $url - rest api url
     * @param $data - array of POST parameters
     * @param $headers - array of header parameters
     * @return void
     */
    public function sendPOST($url, $data, $headers = []) { 
 
        $curl = curl_init();

        __sd([$url , $data , $headers], 'sendPOST');

        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_VERBOSE, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

        if (!empty($headers)) {
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        } else {
            curl_setopt($curl, CURLOPT_HEADER, false);
        }

        if (!empty($data)) {
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
        }
   
        $response = curl_exec($curl);
        $httpResponseCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        curl_close($curl);
        
        $data = json_decode($response, true);
        $data['code'] = $httpResponseCode;
        return $data;
    }

}
