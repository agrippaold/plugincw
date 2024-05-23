<?php

class CWS_GamesAPIValidation
{
	protected $vars = [];

	/**
     * Set variables in Class
     *
     * @param [type] $vars
     * @return void
     */
    public function SetVars($vars)
    {
        $this->vars = $vars;
    }

    /**
     * Decode and parse Token string
     *
     * @param [string] $token
     * @return void
     */
    public function parseToken($token)
    {
        $token = base64_decode($token);
        
        __($token, 'PARSE TOKEN BASE64_DECODE');

        $token_data         = explode("-", $token);
        $data["token"]      = $token_data[0] ?? '';
        $data["currencyId"] = $token_data[1] ?? '';
        $data['privateKey'] = $token_data[2] ?? '';

        __($data, 'PARSE TOKEN DATA');

        return $data;
    }

    /**
     * Validation 4 :: Wrong Bet Amount
     *
     * @return void
     */
    public function Validation_4()
    {
    	$result = [
    		'result' 	=> true,
    		'err_code'	=> '',
    		'err_desc'	=> ''
    	];

        return $result;
    }

    /**
     * Validation 7 :: Wrong Game ID
     *
     * @return void
     */
    public function Validation_7()
    {
        $result = [
    		'result' 	=> true,
    		'err_code'	=> '',
    		'err_desc'	=> ''
    	];

    	if (!isset($this->vars['gameId']) || $this->vars['gameId'] == '') {
    		$result['result']   = false;
            $result['err_desc'] = 'Wrong Game ID';
            $result['err_code'] = 7;
    	} else {
    		$CWS_GamesGameslist = new CWS_GamesGameslist();

            $params = [
                    'meta_query' => [
                        'key'       => 'game_quid',
                        'value'     => $this->vars['gameId'],
                        'compare'   => '='
                    ]
                ];

    		$game = $CWS_GamesGameslist->FindByParams($params, 'single');

    		if (!$game) {
    			$result['result']   = false;
	            $result['err_desc'] = 'Wrong Game ID';
	            $result['err_code'] = 7;
    		}
    	}
    	
        return $result;
    }

    /**
     * Validation 8 :: Authentication Failed
     *
     * @return void
     */
    public function Validation_8()
    {
        $result = [
    		'result' 	=> true,
    		'err_code'	=> '',
    		'err_desc'	=> ''
    	];
    	
        return $result;
    }

    /**
     * Validation 21 :: Not Enough Money
     *
     * @return void
     */
    public function Validation_21($user_id, $currencyId , $amount = 0 )
    {
        $result = [
    		'result' 	=> true,
    		'err_desc'	=> '',
    		'err_code'	=> ''
    	];

    	if ($amount == 0) {
    		$betAmount      = number_format($this->vars['betAmount'] / CWS_GAMES_API_AMOUNT_EXCHANGE_RATE, 2, '.', '');
    	} else {
    		$betAmount      = number_format($amount / CWS_GAMES_API_AMOUNT_EXCHANGE_RATE, 2, '.', '');
    	}
    	
        // $user_balance   = woo_wallet()->wallet->get_wallet_balance($user_id, '');
        $wallet_balance = woo_wallet()->wallet->get_wallet_balance_new($user_id, '') ;
        $user_balance = 0;

        foreach ($wallet_balance as $currency_balance) {
            if (strtoupper($currency_balance->currency_code) == strtoupper($currencyId)) {
                $user_balance = $currency_balance->balance;
                break;
            }
        }

        __sd("u: $user_balance < b: $betAmount" , "balance validation");

        if (floatval($user_balance) < floatval($betAmount)) {

            $result['result']   = false;
            $result['err_desc'] = 'Not Enough Money';
            $result['err_code'] = 21;

        }
    	
        return $result;
    }

    /**
     * Validation 21 :: Not Enough Money
     * Extended Function
     *
     * @return void
     */
    public function Validation_21_Extended($user_id , $amount = 0 )
    {
        $result = [
            'result'    => true,
            'err_desc'  => '',
            'err_code'  => ''
        ];

        $gameId         = $this->vars['gameId'];
        $transactionId  = $this->vars['transactionId'];

        $CWS_GamesTransactions = new CWS_GamesTransactions();
        $transactions = $CWS_GamesTransactions->FindByParams([
            'gameId'        => $gameId,
            'transactionId' => $transactionId
        ]);

        $credit_transaction = $debit_transaction = 0;
        $betAmount = $winAmount = $transactionAmount = 0;

        if ($transactions && !empty($transactions)) {
            
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

            // recognize what kind of transaction should be stored 
            if( $transactionAmount > 0 ) {
                
                $transaction = $debit_transaction;
                $transactionAmount = abs($transactionAmount);

            } else {

                $transaction = $credit_transaction;
                $transactionAmount = abs($transactionAmount);

            }

        } else {
            $result['result']   = false;
            $result['err_desc'] = 'Transaction not found';
            $result['err_code'] = 107;

            return $result;
        }

        
        if ($transaction->type == 'credit' ) {
            $winAmount = number_format($winAmount / CWS_GAMES_API_AMOUNT_EXCHANGE_RATE, 2, '.', '');

            $wallet_balance     = woo_wallet()->wallet->get_wallet_balance_new($user_id, '');
            $user_balance       = 0;
            $CWS_GamesConfig    = new CWS_GamesConfig();
            $currencyId         = $this->vars['currencyId'] ?? ($CWS_GamesConfig->getDefaultCurrency($user_id)['currency_code'] ?? '');

            foreach ($wallet_balance as $currency_balance) {
                if (strtoupper($currency_balance->currency_code) == strtoupper($currencyId)) {
                    $user_balance = $currency_balance->balance;
                    break;
                }
            }

            __sd("u: $user_balance < b: $winAmount" , "balance validation EXTENDED");

            if (floatval($user_balance) < floatval($winAmount)) {

                $result['result']   = false;
                $result['err_desc'] = 'Not Enough Money';
                $result['err_code'] = 21;

            }
        }
        
        return $result;
    }

    /**
     * Validation 29 :: Player Is Blocked
     *
     * @return void
     */
    public function Validation_29()
    {
        $result = [
    		'result' 	=> true,
    		'err_desc'	=> '',
    		'err_code'	=> ''
    	];
    	
        return $result;
    }

    /**
     * Validation 34 :: Wrong Currency
     *
     * @return void
     */
    public function Validation_34()
    {
        $result = [
    		'result' 	=> true,
    		'err_desc'	=> '',
    		'err_code'	=> ''
    	];

    	if (isset($this->vars['currencyId']) && $this->vars['currencyId'] != '') {

            $CWS_GamesConfig    = new CWS_GamesConfig();
            $currencies         = $CWS_GamesConfig->getCurrencies();
            $currency           = $this->vars['currencyId'];
            $currency_found     = false;

            if ($currency == 'FiCo') { $currency = 'FICO'; }
            if ($currency == 'TiCo') { $currency = 'TICO'; }

            foreach ($currencies as $currency) {
                if (isset($currency['currency_code']) && $currency['currency_code'] = $currency) {
                    $currency_found = true;
                    break;
                }
            }

    		if (!$currency_found) {
    			$result['result']   = false;
	            $result['err_desc'] = 'Wrong Currency';
	            $result['err_code'] = 34;
    		}
    	}
    	
        return $result;
    }

    /**
     * Validation 63 :: Wrong Win Amount
     *
     * @return void
     */
    public function Validation_63()
    {
        $result = [
    		'result' 	=> true,
    		'err_desc'	=> '',
    		'err_code'	=> ''
    	];
    	
        return $result;
    }

    /**
     * Validation 84 :: Game is Blocked
     *
     * @return void
     */
    public function Validation_84()
    {
        $result = [
    		'result' 	=> true,
    		'err_desc'	=> '',
    		'err_code'	=> ''
    	];
    	
        return $result;
    }

    /**
     * Validation 104 :: Transaction is already existing
     *
     * @return void
     */
    public function Validation_104()
    {
        $result = [
    		'result' 	=> true,
    		'err_desc'	=> '',
    		'err_code'	=> ''
    	];

    	$CWS_GamesTransactions = new CWS_GamesTransactions();
    	$transaction = $CWS_GamesTransactions->FindByParams(['transactionId' => $this->vars['transactionId']], 'single');

    		if ($transaction) {
    			$result['result']   = false;
	            $result['err_desc'] = 'Transaction is already existing';
	            $result['err_code'] = 104;
    		}
    	
        return $result;
    }

    /**
     * Validation 105 :: The transaction is already canceled
     *
     * @return void
     */
    public function Validation_105()
    {
        $result = [
    		'result' 	=> true,
    		'err_desc'	=> '',
    		'err_code'	=> ''
    	];

    	$CWS_GamesTransactions = new CWS_GamesTransactions();
    	$transaction = $CWS_GamesTransactions->FindByParams([
            'transactionId' => $this->vars['transactionId'], 
            'gameId' => $this->vars['gameId'],
            'type' => 'debit',
            'transaction_type' => 'rollback'
        ], 'single');

    		if ($transaction) {
    			$result['result']   = false;
	            $result['err_desc'] = 'The transaction is already canceled';
	            $result['err_code'] = 105;
    		}

        return $result;
    }

    /**
     * Validation 106 :: Token Expired
     *
     * @return void
     */
    public function Validation_106()
    {
        $result = [
    		'result' 	=> true,
    		'err_desc'	=> '',
    		'err_code'	=> ''
    	];
 
    	if (!isset($this->vars['token']) && $this->vars['token'] == '') {
    		$result['result']   = false;
            $result['err_desc'] = 'Token Expired';
            $result['err_code'] = 106;
    	}
    	
        return $result;
    }

    /**
     * Validation 107 :: Transaction not found
     *
     * @return void
     */
    public function Validation_107()
    {
        $result = [
    		'result' 	=> true,
    		'err_desc'	=> '',
    		'err_code'	=> ''
    	];

    	$CWS_GamesTransactions = new CWS_GamesTransactions();
    	$transaction = $CWS_GamesTransactions->FindByParams(['transactionId' => $this->vars['transactionId']], 'single');

    		if (!$transaction) {
    			$result['result']   = false;
	            $result['err_desc'] = 'Transaction not found';
	            $result['err_code'] = 107;
    		}
    	
        return $result;
    }

    /**
     * Validation 114 :: Token not Found
     *
     * @return void
     */
    public function Validation_114()
    {
        $result = [
    		'result' 	=> true,
    		'err_desc'	=> '',
    		'err_code'	=> ''
    	];

    	$CWS_GamesConfig = new CWS_GamesConfig();

        $token_data = explode("-", $this->vars['token']); 
        
    	$user = $CWS_GamesConfig->getUserByToken($this->vars['token']);
 
    	if (!$user) {
    		$result['result']   = false;
            $result['err_desc'] = 'Token not Found';
            $result['err_code'] = 114; 
    	}

        return $result;
    }

    /**
     * Validate Token with Currency  
     *
     * @return array
     */
    public function Validation_114_Extended()
    {
        $result = [
    		'result' 	=> true,
    		'err_desc'	=> '',
    		'err_code'	=> ''
    	];

    	$CWS_GamesConfig = new CWS_GamesConfig();

        $token_data         = $this->parseToken($this->vars['token']);
        $data["token"]      = $token_data['token'];
        $data["currencyId"] = $token_data['currencyId'];
        
    	$user = $CWS_GamesConfig->getUserByToken($data["token"]);
		$currency = $CWS_GamesConfig->getCurrencyByCode($data["currencyId"]);

        if (!$user || $currency["currency_code"] == '') {
            $result['result']   = false;
            $result['err_desc'] = 'Token not Found.';
            $result['err_code'] = 114;
        }

        return $result;
    }

    /**
     * Validation 130 :: General Error
     *
     * @return void
     */
    public function Validation_130()
    {
        $result = [
    		'result' 	=> true,
    		'err_desc'	=> '',
    		'err_code'	=> ''
    	];
    	
        return $result;
    }

    /**
     * Validation 200 :: Incorrect Parameters Passed
     *
     * @return void
     */
    public function Validation_200()
    {
        $result = [
    		'result' 	=> true,
    		'err_desc'	=> '',
    		'err_code'	=> ''
    	];
    	
        return $result;
    }

    /**
     * Validation 200 :: Incorrect Parameters Passed
     *
     * @return void
     */
    public function Validation_200_Extended($privateKey, $time, $data, $hash)
    {
        $result = [
            'result'    => true,
            'err_desc'  => '',
            'err_code'  => ''
        ];

        $md5 = md5($privateKey . $time . json_encode($data));

        if ($md5 != $hash) {
            $result['result']   = false;
            $result['err_desc'] = 'Invalid Hash.';
            $result['err_code'] = 200;
        }
        
        return $result;
    }

}