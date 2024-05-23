<?php

class CWS_GamesAPI
{	
	protected $user_id 	= 0;
	protected $token 	= '';
	protected $vars 	= [];

	/**
     * Constructor
     */
    public function __construct()
    {
    	if (is_user_logged_in()) {
    		$this->user_id 	= get_current_user_id();
    		$this->token 	= md5($this->user_id);
    	}
    }

    /**
     * Set variables in Class
     *
     * @param [type] $data
     * @return void
     */
    public function SetVars($vars)
    {  
        $this->vars = $vars;
    }

    /**
     * Undocumented function
     *
     * @return array
     */
    public function API_GameLaunch()
    {   
        $result = false;
        
        __sd($this->vars, 'GameLaunch');

        if ($this->user_id == 0) {
            $result = ['status' => -10, 'status_txt' => 'Please log in to play!'];
        } else {
            if (!isset($this->vars['api_url']) || $this->vars['api_url'] == '') {
                $result = ['status' => -11, 'status_txt' => 'Missing API Server!'];
            } elseif (!isset($this->vars['game_id']) || $this->vars['game_id'] == '') {
                $result = ['status' => -12, 'status_txt' => 'Missing game ID!'];
            } else {

                $CWS_GamesConfig    = new CWS_GamesConfig();
                $user               = $CWS_GamesConfig->getUserByToken($this->token);

                if ($user) {
                    $currency = $CWS_GamesConfig->getDefaultCurrency($user->ID);
                }

                $token = base64_encode($this->token . "-" . $currency['currency_code'] . '-' . $this->vars['api_key']);
                $data = [
                    'idGame'        => $this->vars['game_id'],
                    'idCurrency'    => $currency['currency_code'] ?? '',
                    'idPlayer'      => $user->ID ?? '',
                    'token'         => $token,
                    'idLanguage'    => 'en'
                ];

                // __sd($data, 'GameLaunch DATA');
                // forMoney: 0 = for fun ; 1 = for money [not mandatory, default: 0]
                if( strtolower($this->vars['game_source']) == 'paid') $forMoney = 1;
                 else $forMoney = 0;
                $url = $this->vars['api_url'] . '?idGame=' . $this->vars['game_id'] . '&idCurrency=' . ($currency['currency_code'] ?? ''). '&forMoney='. $forMoney . '&idPlayer=' . ($user->ID ?? '') . '&token=' . $token . '&idLanguage=en';

                // __sd($url , 'GameLaunch URL');

                $result = ['status' => 10, 'status_txt' => 'Game successfully started!', 'game' => $url, 'token' => $this->token, 'idGame' => $this->vars['game_id'], 'idCurrency' => $currency['currency_code'] ?? '', 'idPlayer' => $user->ID ?? ''];
            }
            
        }

        __sd($result, 'GameLaunch RESPONSE');

        return $result;
    }

    public function API_GetPlayerInfo()
    {   
        $result = [];

        $CWS_GamesConfig        = new CWS_GamesConfig();
        $CWS_GamesAPIValidation = new CWS_GamesAPIValidation();

        $CWS_GamesAPIValidation->setVars($this->vars);

        $result = $CWS_GamesAPIValidation->Validation_106();

        if ($result['result'] == true) {

            $result = $CWS_GamesAPIValidation->Validation_114();

            if ($result['result'] == true) {

                $user = $CWS_GamesConfig->getUserByToken($this->vars['token']);

                // Vins patch 
                if(isset($this->vars['currencyId'])){
                    $currency['currency_code'] = $this->vars['currencyId'];
                } else {
                    $currency = $CWS_GamesConfig->getDefaultCurrency($user->ID);
                }

                $result['result']       = true;
                $result['currencyId']   = $currency['currency_code'] ?? '';
                $result['nickName']     = $user->display_name;
                $result['userID']       = $user->ID;

                // $wallet_balance = woo_wallet()->wallet->get_wallet_balance_new($user->ID, '') ;

                // if ($wallet_balance && !empty($wallet_balance)) {
                //     $user_balance = 0;

                //     foreach ($wallet_balance as $currency_balance) {
                //         if (strtoupper($currency_balance->currency_code) == strtoupper($currency['currency_code'])) {
                //             $user_balance = $currency_balance->balance;
                //         }
                //     }

                //     $result['totalBalance'] = $user_balance ? intval(round($user_balance * CWS_GAMES_API_AMOUNT_EXCHANGE_RATE)) : 0;
                // }

                $walletBalance  = get_user_meta($user->ID, 'walletBallance', true);

                if ($walletBalance) {
                    $balance = json_decode($walletBalance);

                    if ($balance && !empty($balance)) {
                        $user_balance = 0;

                        foreach ($balance as $group => $_balance) {
                            if ($group == 'virtual') {
                                foreach ($_balance as $currency_balance) {
                                    if (strtoupper($currency_balance->currency) == strtoupper($currency['currency_code'])) {
                                        $user_balance = $currency_balance->balance;
                                    }
                                }
                            }
                        }

                        $result['totalBalance'] = $user_balance ? intval(round($user_balance * CWS_GAMES_API_AMOUNT_EXCHANGE_RATE)) : 0;
                    }
                }

            } else {

                return $result;

            }

        } else {

            return $result;

        }

    	return $result;
    }

     public function API_Withdraw()
    {

        $result = [];

        $Woo_Wallet_Wallet      = new Woo_Wallet_Wallet();
        $CWS_GamesConfig        = new CWS_GamesConfig();
        $CWS_GamesAPIValidation = new CWS_GamesAPIValidation();
        $CWS_GamesGameslist     = new CWS_GamesGameslist();

        $CWS_GamesAPIValidation->setVars($this->vars);
 
        if (!($err = $CWS_GamesAPIValidation->Validation_114())['result']) {
            // Token not Found
            return $err;
        }

        if (!($err = $CWS_GamesAPIValidation->Validation_106())['result']) {
            // Token Expired
            return $err;
        }

        if (!($err = $CWS_GamesAPIValidation->Validation_7())['result']) {
            // Wrong Game ID
            return $err;
        }

        if (!($err = $CWS_GamesAPIValidation->Validation_104())['result']) {
            // Transaction is already existing
            return $err;
        }

        if (!($err = $CWS_GamesAPIValidation->Validation_34())['result']) {
            // Wrong Currency
            return $err;
        }

        $user = $CWS_GamesConfig->getUserByToken($this->vars['token']);

        if (!($err = $CWS_GamesAPIValidation->Validation_21($user->ID, $this->vars['currencyId']))['result']) {
            // Not Enough Money
            return $err;
        }   

        $params = [
            'meta_query' => [
                'key'       => 'game_quid',
                'value'     => $this->vars['gameId'],
                'compare'   => '='
            ]
        ];

        $game = $CWS_GamesGameslist->FindByParams($params, 'single');

        $betAmount          = number_format($this->vars['betAmount'] / CWS_GAMES_API_AMOUNT_EXCHANGE_RATE , 2);
        $betAmountWallet    = $this->vars['betAmount'] / CWS_GAMES_API_AMOUNT_EXCHANGE_RATE;
        // $betAmount          = number_format($this->vars['betAmount'] , 2);
        // $betAmountWallet    = $this->vars['betAmount'];

        $transaction_args = [];

        $currency_code = $this->vars['currencyId'] ?? '';

        if ($currency_code == '') {
            $currency       = $CWS_GamesConfig->getDefaultCurrency($user->ID);
            $currency_code  = $currency['currency_code'] ?? '';
        } else {
            $currency       = $CWS_GamesConfig->getCurrencyByCode($currency_code);
        }

        if ($currency_code != '') {
            $transaction_args['currency'] = $currency_code;
        }

        // $transactionDesc    = 'New bet for game : ' . $this->vars['gameId'] . ', transactionId : ' . $this->vars['transactionId'];
        $transactionDesc    = 'New bet for game : ' . $game->name_game . ', Bet Amount : ' . $betAmount . ' ' . $currency['currency_symbol'] ?? 'FiCo';

        __sd( $this->vars['betAmount'] ,"betAmount = ". $betAmount );
        __sd( $transactionDesc ,"transactionDesc" );
   
        $transaction_id = $Woo_Wallet_Wallet->debit($user->ID, $betAmountWallet, $transactionDesc, $transaction_args);

        if ($transaction_id) {
            update_wallet_transaction_meta($transaction_id, '_partial_payment', true, $user->ID);

            $result['result']           = true;
            $result['transactionId']    = $this->vars['transactionId'];

            $wallet_balance = woo_wallet()->wallet->get_wallet_balance_new($user->ID, '') ;

            if ($wallet_balance && !empty($wallet_balance)) {
                $user_balance = 0;

                foreach ($wallet_balance as $currency_balance) {
                    if (strtoupper($currency_balance->currency_code) == strtoupper($currency_code)) {
                        $user_balance = $currency_balance->balance;
                    }
                }

                $result['balance'] = $user_balance ? intval(round($user_balance * CWS_GAMES_API_AMOUNT_EXCHANGE_RATE)) : 0;
            }

            $result['transaction_fields'] = [
                'wallet_transaction_id' => $transaction_id,
                'user_id'               => $user->ID
            ];

        } else {
            $result['result']   = false;
            $result['err_desc'] = 'General Error. There has been a problem with this transaction. Try again.';
            $result['err_code'] = 130;
        }

        return $result;
    }

    public function API_Deposit($excludedValidations = [])
    {   
        $result = [];

        $Woo_Wallet_Wallet      = new Woo_Wallet_Wallet();
        $CWS_GamesConfig        = new CWS_GamesConfig();
        $CWS_GamesAPIValidation = new CWS_GamesAPIValidation();
        $CWS_GamesGameslist     = new CWS_GamesGameslist();

        $CWS_GamesAPIValidation->setVars($this->vars);

        if (!($err = $CWS_GamesAPIValidation->Validation_114())['result']) {
            // Token not Found
            return $err;
        }

        if (!($err = $CWS_GamesAPIValidation->Validation_106())['result']) {
            // Token Expired
            return $err;
        }

        if (!($err = $CWS_GamesAPIValidation->Validation_7())['result']) {
            // Wrong Game ID
            return $err;
        }

        if (!in_array(104, $excludedValidations) && !($err = $CWS_GamesAPIValidation->Validation_104())['result']) {
            // Transaction is already existing
            return $err;
        }

        if (!($err = $CWS_GamesAPIValidation->Validation_34())['result']) {
            // Wrong Currency
            return $err;
        }

        $params = [
            'meta_query' => [
                'key'       => 'game_quid',
                'value'     => $this->vars['gameId'],
                'compare'   => '='
            ]
        ];

        $game = $CWS_GamesGameslist->FindByParams($params, 'single');

        $user = $CWS_GamesConfig->getUserByToken($this->vars['token']);

        if ($this->vars['winAmount'] > 0) {
            $winAmount          = number_format($this->vars['winAmount'] / CWS_GAMES_API_AMOUNT_EXCHANGE_RATE, 2);
            $winAmountWallet    = $this->vars['winAmount'] / CWS_GAMES_API_AMOUNT_EXCHANGE_RATE;
            // $winAmount          = number_format($this->vars['winAmount'], 2);
            // $winAmountWallet    = $this->vars['winAmount'];

            $transaction_args = [];

            $currency_code = $this->vars['currencyId'] ?? '';

            if ($currency_code == '') {
                $currency       = $CWS_GamesConfig->getDefaultCurrency($user->ID);
                $currency_code  = $currency['currency_code'] ?? '';
            } else {
                $currency       = $CWS_GamesConfig->getCurrencyByCode($currency_code);
            }

            if ($currency_code != '') {
                $transaction_args['currency'] = $currency_code;
            }

            // $transactionDesc    = 'New winnings for game :' . $this->vars['gameId'] . ', transactionId : ' . $this->vars['transactionId'];
            $transactionDesc    = 'New winnings for game : ' . $game->name_game . ', Win amount : ' . $winAmount . ' ' . $currency['currency_symbol'] ?? 'FiCo';

            // if (isset($this->vars['betInfo']) && $this->vars['betInfo'] != '') {
            //     $transactionDesc .= ', Bet info : ' . $this->vars['betInfo'];
            // }


            $recharge_amount = apply_filters('woo_wallet_credit_purchase_amount', $winAmountWallet, $user->ID);
            $transaction_id = $Woo_Wallet_Wallet->credit($user->ID, $recharge_amount, $transactionDesc, $transaction_args);

            __sd($this->vars['winAmount'] ,"winAmount = ". $recharge_amount );
            __sd( $transactionDesc ,"transactionDesc" );

            if ($transaction_id) {
                update_wallet_transaction_meta($transaction_id, '_type', 'credit_purchase', $user->ID);

                $result['result']           = true;
                $result['transactionId']    = $this->vars['transactionId'];

                $wallet_balance = woo_wallet()->wallet->get_wallet_balance_new($user->ID, '') ;

                if ($wallet_balance && !empty($wallet_balance)) {
                    $user_balance = 0;

                    foreach ($wallet_balance as $currency_balance) {
                        if (strtoupper($currency_balance->currency_code) == strtoupper($currency_code)) {
                            $user_balance = $currency_balance->balance;
                        }
                    }

                    $result['balance'] = $user_balance ? intval(round($user_balance * CWS_GAMES_API_AMOUNT_EXCHANGE_RATE)) : 0;
                }

                $result['transaction_fields'] = [
                    'wallet_transaction_id' => $transaction_id,
                    'user_id'               => $user->ID
                ];

            } else {
                $result['result']   = false;
                $result['err_desc'] = 'General Error. There has been a problem with this transaction. Try again.';
                $result['err_code'] = 130;
            }
        } else {
            $result['result']           = true;
            $result['transactionId']    = $this->vars['transactionId'];

            $currency_code = $this->vars['currencyId'] ?? '';

            if ($currency_code == '') {
                $currency       = $CWS_GamesConfig->getDefaultCurrency($user->ID);
                $currency_code  = $currency['currency_code'] ?? '';
            } else {
                $currency       = $CWS_GamesConfig->getCurrencyByCode($currency_code);
            }

            $wallet_balance = woo_wallet()->wallet->get_wallet_balance_new($user->ID, '') ;

            if ($wallet_balance && !empty($wallet_balance)) {
                $user_balance = 0;

                foreach ($wallet_balance as $currency_balance) {
                    if (strtoupper($currency_balance->currency_code) == strtoupper($currency_code)) {
                        $user_balance = $currency_balance->balance;
                    }
                }

                $result['balance'] = $user_balance ? intval(round($user_balance * CWS_GAMES_API_AMOUNT_EXCHANGE_RATE)) : 0;
            }

            $result['transaction_fields'] = [
                'wallet_transaction_id' => 0,
                'user_id'               => $user->ID
            ];
        }
        

        return $result;
    }

    public function API_RollbackTransaction()
    {
        $result = [];

        $Woo_Wallet_Wallet      = new Woo_Wallet_Wallet();
        $CWS_GamesConfig        = new CWS_GamesConfig();
        $CWS_GamesAPIValidation = new CWS_GamesAPIValidation();
        $CWS_GamesGameslist     = new CWS_GamesGameslist();

        $CWS_GamesAPIValidation->setVars($this->vars);

        if (!($err = $CWS_GamesAPIValidation->Validation_114())['result']) {
            // Token not Found
            return $err;
        }

        if (!($err = $CWS_GamesAPIValidation->Validation_106())['result']) {
            // Token Expired
            return $err;
        }

        if (!($err = $CWS_GamesAPIValidation->Validation_34())['result']) {
            // Wrong Currency
            return $err;
        }

        if (!($err = $CWS_GamesAPIValidation->Validation_105())['result']) {
            // The transaction is already canceled
            return $err;
        }

        if (!($err = $CWS_GamesAPIValidation->Validation_107())['result']) {
            // Transaction not found
            return $err;
        }

        $params = [
            'meta_query' => [
                'key'       => 'game_quid',
                'value'     => $this->vars['gameId'],
                'compare'   => '='
            ]
        ];

        $game = $CWS_GamesGameslist->FindByParams($params, 'single');
        $user = $CWS_GamesConfig->getUserByToken($this->vars['token']);
        $CWS_GamesTransactions = new CWS_GamesTransactions();
        $transactions = $CWS_GamesTransactions->FindByParams([
            'gameId' => $this->vars['gameId'],
            'transactionId' => $this->vars['transactionId']
        ]);

            $credit_transaction = $debit_transaction = 0;
            $betAmount = $winAmount = $transactionAmount = 0;

            foreach($transactions as $transactionX) {

                if ($transactionX->type == 'deposit' || $transactionX->type == 'debit') {

                    $betAmount = $transactionX->betAmount;
                    $debit_transaction = $transactionX;
                    $transactionAmount += $betAmount;
                } else {

                    $winAmount = $transactionX->winAmount;
                    $credit_transaction = $transactionX;
                    $transactionAmount -= $winAmount;
                }
            }

            // recognize what king of transaction should be stored 
            if( $transactionAmount > 0 ) {
                
                $transaction = $debit_transaction;
                $transactionAmount = abs($transactionAmount);
            } else {

                $transaction = $credit_transaction;
                $transactionAmount = abs($transactionAmount);
            }

            __sd($transaction, " -------- transaction --------");
            __sd($transactionAmount, "transactionAmount");
 
            if ($transaction->type == 'deposit' || $transaction->type == 'debit') {

                // both debit and creadit - betAmount
                $betAmount = $transactionAmount;
                $amount          = number_format($betAmount / CWS_GAMES_API_AMOUNT_EXCHANGE_RATE, 2);
                $amountWallet    = $betAmount / CWS_GAMES_API_AMOUNT_EXCHANGE_RATE;

                $transaction_args = [];
                $currency_code = $this->vars['currencyId'] ?? '';
                $transaction_args['currency'] = $currency_code;

                $transactionDesc = 'Rollback credit transaction for the game : ' . $game->name_game . ', Amount : ' . $amount . ' ' . $this->vars['currencyId'];
                $transaction_id  = $Woo_Wallet_Wallet->credit($user->ID, $amountWallet, $transactionDesc, $transaction_args);

                if ($transaction_id) {
                    update_wallet_transaction_meta($transaction_id, '_type', 'credit_purchase', $user->ID);

                    $result['result']           = true;
                    $result['transactionId']    = $this->vars['transactionId'];

                    $wallet_balance = woo_wallet()->wallet->get_wallet_balance_new($user->ID, '') ;

                    if ($wallet_balance && !empty($wallet_balance)) {
                        $user_balance = 0;

                        foreach ($wallet_balance as $currency_balance) {
                            if (strtoupper($currency_balance->currency_code) == strtoupper($currency_code)) {
                                $user_balance = $currency_balance->balance;
                            }
                        }

                        $result['balance'] = $user_balance ? intval(round($user_balance * CWS_GAMES_API_AMOUNT_EXCHANGE_RATE)) : 0;
                    }

                    $result['transaction_fields'] = [
                        'type'                  => 'credit',
                        'winAmount'             => $betAmount,
                        'wallet_transaction_id' => $transaction_id,
                        'user_id'               => $user->ID,
                        'currencyId'            => $this->vars['currencyId']
                    ];

                } else {
                    $result['result']   = false;
                    $result['err_desc'] = 'General Error. There has been a problem with credit transaction. Try again.';
                    $result['err_code'] = 130;
                }

            } else {
 
                $winAmount    = $transactionAmount;
                $amount       = number_format($winAmount / CWS_GAMES_API_AMOUNT_EXCHANGE_RATE, 2);
                $amountWallet = $winAmount / CWS_GAMES_API_AMOUNT_EXCHANGE_RATE;
 
                if (!($err = $CWS_GamesAPIValidation->Validation_21($user->ID, $this->vars['currencyId'], $winAmount))['result']) {
                    // Not Enough Money
                    return $err;
                }

                $transaction_args = [];
                $currency_code = $this->vars['currencyId'] ?? '';  
                $transaction_args['currency'] = $currency_code; 

                $transactionDesc = 'Rollback debit transaction for the game : '. $game->name_game .', Amount : '. $amount .' '. $this->vars['currencyId'];
              
                $a1 = $Woo_Wallet_Wallet->get_wallet_balance( $user->ID, '' );
   
                $transaction_id = $Woo_Wallet_Wallet->debit($user->ID, $amountWallet, $transactionDesc, $transaction_args);
 
                if ($transaction_id) {
                    update_wallet_transaction_meta($transaction_id, '_partial_payment', true, $user->ID);

                    $result['result']           = true;
                    $result['transactionId']    = $this->vars['transactionId'];

                    $wallet_balance = woo_wallet()->wallet->get_wallet_balance_new($user->ID, '');

                    if ($wallet_balance && !empty($wallet_balance)) {
                        $user_balance = 0;

                        foreach ($wallet_balance as $currency_balance) {
                            if (strtoupper($currency_balance->currency_code) == strtoupper($currency_code)) {
                                $user_balance = $currency_balance->balance;
                            }
                        }

                        $result['balance'] = $user_balance ? intval(round($user_balance * CWS_GAMES_API_AMOUNT_EXCHANGE_RATE)) : 0;
                    }

                    $result['transaction_fields'] = [
                        'type'                  => 'debit',
                        'winAmount'             => $winAmount,
                        'wallet_transaction_id' => $transaction_id,
                        'user_id'               => $user->ID,
                        'currencyId'            => $this->vars['currencyId']
                    ];

                } else {
                    $result['result']   = false;
                    $result['err_desc'] = 'General Error. There has been a problem with debit transaction. Try again.';
                    $result['err_code'] = 130;
                }

            }

        return $result;
    }

    public function API_WithdrawDeposit()
    {
        $result = [];

        $result = $this->API_Withdraw();

        if (!$result['result']) {
            return $result;
        }

        $result = $this->API_Deposit([104]);

        return $result;
    }

    /**
     * API Response Functions for FREE games :
     */

    public function API_Withdraw_Free()
    {
        $result = [
            'result'        => true,
            'transactionId' => $this->vars['transactionId'],
            'balance'       => 1000000
        ];

        return $result;
    }

    public function API_Deposit_Free()
    {
        $result = [
            'result'        => true,
            'transactionId' => $this->vars['transactionId'],
            'balance'       => 1000000
        ];

        return $result;
    }

    public function API_WithdrawDeposit_Free()
    {
        $result = [
            'result'        => true,
            'transactionId' => $this->vars['transactionId'],
            'balance'       => 1000000
        ];

        return $result;
    }

    public function API_RollbackTransaction_Free()
    {
        $result = [
            'result'        => true,
            'transactionId' => $this->vars['transactionId'],
            'balance'       => 1000000
        ];

        return $result;
    }
    
}