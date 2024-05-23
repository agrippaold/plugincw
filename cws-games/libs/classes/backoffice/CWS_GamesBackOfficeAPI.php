<?php

class CWS_GamesBackOfficeAPI
{	
	protected $vars = [];


	public function SetVars($vars)
	{
		$this->vars = $vars;
	}

	/**
	 * Check connection and validate providerId.
	 * If providerId is valid - return json with provider params from our db.
	 * 
	 * @param [object]
	 * {
	 * 		"token: "65866c7d5686d099f7f2714604dbc019",
	 * 		"action": "BackofficeConnectionTest",
	 * 		"providerId": "3c1640d30d7aa41c3168f256df792425"
	 * }
	 * @return [void]
	 */ 
	public function BackofficeConnectionTest()
	{	
		$result = [
			'status' 		=> 1,
			'status_txt' 	=> 'Successful',
			'request'	  	=> $this->vars,
		];

		// if (isset($this->vars['providerId']) && $this->vars['providerId'] != '') {

		// 	$providerId = $this->vars['providerId'];

		// 	$CWS_GamesServers = new CWS_GamesServers();

		// 	$server = $CWS_GamesServers->getServerByServerId($providerId);

		// 	if ($server) {
		// 		$result = $server;
		// 	} else {
		// 		$result['status'] 		= -101;
		// 		$result['status_txt'] 	= "Invalid providerId.";
		// 	}

		// } else {
		// 	$result['status'] 		= -102;
		// 	$result['status_txt'] 	= "Missing providerId.";
		// }

		return $result;
	}

	/**
	 * Get sessions by providerId.
	 * 
	 * @param [object] 
	 * {
	 * 		"token": "65866c7d5686d099f7f2714604dbc019",
	 * 		"action": "ProviderSessions",
	 * 		"providerId": "3c1640d30d7aa41c3168f256df792425",
	 * 		"date_from": "2023-10-01",
	 * 		"date_to": "2023-10-31",
	 * 		"gameId": "",
	 * 		"currency": ""
	 * }
	 * @return [void]
	 */ 
	public function ProviderSessions()
	{	
		$result = [
			'status' 		=> 201,
			'status_txt' 	=> 'OK'
		];

		if (isset($this->vars['providerId']) && $this->vars['providerId'] != '') {
			$CWS_GamesSessions = new CWS_GamesSessions();

			$params = array(
				'orderby'   => ' ORDER BY `id`'
			);

			if (isset($this->vars['date_from']) && $this->vars['date_from'] != '') {
	            $params['date_creation'] = [
	                'cond'      => '>=',
	                'value'     => "'".$this->vars['date_from']."'"
	            ];
	        }

	        if (isset($this->vars['date_to']) && $this->vars['date_to'] != '') {
	            if (array_key_exists('date_creation', $params)) {
	                $old_value = $params['date_creation']['cond'] . ' ' . $params['date_creation']['value'];

	                $params['date_creation']['cond']    = ' AND ';

	                $params['date_creation']['value']   = [$old_value, " <= '" . $this->vars['date_to'] . "'"];

	            } else {
	                $params['date_creation'] = [
	                    'cond'      => '<=',
	                    'value'     => "'".$this->vars['date_to']."'"
	                ];
	            }
	        }

	        if (isset($this->vars['gameId']) && $this->vars['gameId'] != '' && $this->vars['gameId'] != -1 ) {
	            $params['gameId'] = $this->vars['gameId'];
	        }

	        if (isset($this->vars['providerId']) && $this->vars['providerId'] != '' && $this->vars['providerId'] != -1) {
	            $params['providerId'] = $this->vars['providerId'];
	        }

	        if (isset($this->vars['currency']) && $this->vars['currency'] != '' && $this->vars['currency'] != -1) {
	            $params['currency'] = $this->vars['currency'];
	        }

	        $sessions = $CWS_GamesSessions->FindByParams($params);

	        if ($sessions && !empty($sessions)) {

	        	$_result = array();

	        	foreach ($sessions as $session) {
	        		$_result[] = array(
	        			'id_session' 		=> $session->id_session ?? '',
	        			'token' 			=> $session->token ?? '',
	        			'userId'			=> $session->userId ?? 0,
	        			'userFullName'		=> $session->userFullName ?? '',
	        			'userName'			=> $session->userName ?? '',
	        			'providerId'		=> $session->providerId ?? '',
	        			'providerName' 		=> $session->providerName ?? '',
	        			'gameId' 			=> $session->gameId ?? '',
	        			'gameName'			=> $session->gameName ?? '',
	        			'currency'			=> $session->currency ?? '',
	        			'totIn' 			=> $session->totIn ?? '',
	        			'totOut' 			=> $session->totOut ?? '',
	        			'totSpins' 			=> $session->totSpins ?? '',
	        			'date_creation' 	=> $session->date_creation ?? ''
	        		);
	        	}

	        	$result = $_result;

	        } else {

	        	$result['status'] 		= -201;
				$result['status_txt'] 	= "Empty Provider.";

	        }
		} else {

			$result['status'] 		= -202;
			$result['status_txt'] 	= "Missing providerId.";

		}

		return $result;
	}

	/**
	 * Get sessions summary by providerId.
	 * 
	 * @param [object] 
	 * {
	 * 		"token": "65866c7d5686d099f7f2714604dbc019",
	 * 		"action": "ProviderSummary",
	 * 		"providerId": "3c1640d30d7aa41c3168f256df792425",
	 * }
	 * @return [void]
	 */ 
	public function ProviderSummary()
	{
		$result = [
			'status' 		=> 301,
			'status_txt' 	=> 'OK'
		];

		if (isset($this->vars['providerId']) && $this->vars['providerId'] != '') {

			$CWS_GamesSessions = new CWS_GamesSessions();

			$params = [
				'providerId'	=> $this->vars['providerId'],
                'orderby'       => ' ORDER BY `id`',
            ];

            $rows = $CWS_GamesSessions->FindByParams($params);

            if ($rows && !empty($rows)) {

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
	                    $serverTotalSpins 	= 0;
	                    $totalPerServer 	= 0;

	                    foreach ($server['currencies'] as $currency_code => $currency) {
	                        $serverTotalSpins += intval($currency['totSpins']) ?? 0;

	                        $ggr = 0;
	                        $ggr += intval($currency['totIn']) ?? 0;
	                        $ggr -= intval($currency['totOut']) ?? 0;

	                        $totalPerServer += ($ggr);

	                        $prepareServers[$key]['currencies'][$currency_code]['totIn'] = number_format((intval($currency['totIn']) ?? 0), 2, ',', ' ');
	                        $prepareServers[$key]['currencies'][$currency_code]['totOut'] = number_format((intval($currency['totOut']) ?? 0), 2, ',', ' ');
	                        $prepareServers[$key]['currencies'][$currency_code]['ggr'] = number_format(($ggr ?? 0), 2, ',', ' ');
	                    }

	                    $prepareServers[$key]['serverTotalSpins'] = $serverTotalSpins;
	                    $prepareServers[$key]['totalPerServer'] = number_format(($totalPerServer ?? 0), 2, ',', ' ');
	                }
	            }

	            if (isset($prepareServers[$this->vars['providerId']])) {

	            	$result = $prepareServers[$this->vars['providerId']];

	            } else {

	            	$result['status'] 		= -301;
					$result['status_txt'] 	= "Empty Provider.";

	            }

            } else {

            	$result['status'] 		= -301;
				$result['status_txt'] 	= "Empty Provider.";

            }

		} else {

			$result['status'] 		= -302;
			$result['status_txt'] 	= "Missing providerId.";

		}

		return $result;
	}

	/**
	 * Get games by providerId.
	 * 
	 * @param [object] 
	 * {
	 * 		"token": "65866c7d5686d099f7f2714604dbc019",
	 * 		"action": "ProviderGames",
	 * 		"providerId": "3c1640d30d7aa41c3168f256df792425",
	 * }
	 * @return [void]
	 */ 
	public function ProviderGames()
	{
		$result = [
			'status' 		=> 401,
			'status_txt' 	=> 'OK'
		];

		if (isset($this->vars['providerId']) && $this->vars['providerId'] != '') {

			$CWS_GamesGameslist = new CWS_GamesGameslist();

	        $params = [
	            'orderby'   	=> 'meta_value',
	            'meta_key'  	=> 'source',
	            'meta_query' 	=> array(
	            	'relation' 	=> 'AND',
	            	array(
	                    'key'       => 'server_id',
	                    'value'     => $this->vars['providerId'],
	                    'compare'   => '='
	                ),
	            )
	        ];

	        $games = $CWS_GamesGameslist->FindByParams($params);

	        if ($games && !empty($games)) {

	        	$result = $games;

	        } else {

	        	$result['status'] 		= -401;
				$result['status_txt'] 	= "Empty Provider.";

	        }

		} else {

			$result['status'] 		= -402;
			$result['status_txt'] 	= "Missing providerId.";

		}

		return $result;
	}

	/**
	 * Get session details.
	 * 
	 * @param [object] 
	 * {
	 * 		"token": "65866c7d5686d099f7f2714604dbc019",
	 * 		"action": "SessionDetails",
	 * 		"sessionId": "3aa2a282-a868-4783-80ea-59868e42d1e7-123,165509-TICO",
	 * }
	 * @return [void]
	 */ 
	public function SessionDetails()
	{
		$result = [
			'status' 		=> 501,
			'status_txt' 	=> 'OK'
		];

		if (isset($this->vars['sessionId']) && $this->vars['sessionId'] != '') {

			$CWS_GamesSessions = new CWS_GamesSessions();

            $session = $CWS_GamesSessions->getDetails($this->vars['sessionId']);

            if ($session) {

            	$result = $session;

            } else {

            	$result['status'] 		= -501;
				$result['status_txt'] 	= "Invalid sessionId.";

            }

		} else {

			$result['status'] 		= -502;
			$result['status_txt'] 	= "Missing sessionId.";

		}

		return $result;
	}

	/**
	 * Update player (user) details.
	 * 
	 * @param [object] 
	 * {
	 * 		"token": "65866c7d5686d099f7f2714604dbc019",
	 * 		"action": "UpdatePlayer",
	 * 		"playerId": "a87ff679a2f3e71d9181a67b7542122c",
	 * 		"data": {
	 *			"username": "",
	 * 			"email": "",
	 * 			"password": "",
	 * 			"first_name": "",
	 * 			"second_name": "",
	 * 			"state": 0|1
	 * 		}
	 * }
	 * @return [void]
	 */ 
	public function UpdatePlayer()
	{
		$result = [
			'status' 		=> 601,
			'status_txt' 	=> 'Sucessfully updated.'
		];

		if (isset($this->vars['playerId']) && $this->vars['playerId'] != '') {

			$user = null;

			$query = get_users(array(
				'meta_key' 		=> 'playerId',
				'meta_value' 	=> $this->vars['playerId']
			));

			if (!is_wp_error($query) && !empty($query)) {
				$user = reset($query);
			}

			if ($user && isset($user->ID)) {

				$user_update_args = [];

				if (isset($this->vars['data']['username']) && $this->vars['data']['username'] != '') {
					$user_update_args['display_name'] = $this->vars['data']['username'];
				}

				if (isset($this->vars['data']['email']) && $this->vars['data']['email'] != '') {
					$user_update_args['user_email'] = $this->vars['data']['email'];
				}

				if (isset($this->vars['data']['password']) && $this->vars['data']['password'] != '') {
					// $user_update_args['user_pass'] = $this->vars['data']['password'];
					$user_update_args['user_pass'] = base64_decode($this->vars['data']['password']);
				}

				if (!empty($user_update_args)) {
					$user_update_args['ID'] = $user->ID;

					wp_update_user( $user_update_args );
				}

				if (isset($this->vars['data']['first_name']) && $this->vars['data']['first_name'] != '') {
					update_user_meta($user->ID, 'first_name', $this->vars['data']['first_name']);
				}

				if (isset($this->vars['data']['second_name']) && $this->vars['data']['second_name'] != '') {
					update_user_meta($user->ID, 'last_name', $this->vars['data']['second_name']);
				}

				if (isset($this->vars['state']) && $this->vars['state'] != '' && in_array($this->vars['state'], array(0, 1))) {
					update_user_meta($user->ID, 'state', $this->vars['state']);
				}


			} else {

				$result['status'] 		= -601;
				$result['status_txt'] 	= "Invalid playerId.";

			}

		} else {

			$result['status'] 		= -602;
			$result['status_txt'] 	= "Missing playerId.";

		}

		return $result;
	}

	/**
	 * Update player (user) notifications.
	 * 
	 * @param [object] 
	 * {
	 * 		"token": "65866c7d5686d099f7f2714604dbc019",
	 * 		"action": "PutNotifications",
	 * 		"playerId": "a87ff679a2f3e71d9181a67b7542122c",
	 * 		"data": {
	 *			TBD
	 * 		}
	 * }
	 * @return [void]
	 */ 
	public function PutNotifications()
	{
		$result = [
			'status' 		=> 701,
			'status_txt' 	=> 'Sucessfully updated.'
		];

		__sd($this->vars, 'BackOffice API - PutNotifications');

		if (isset($this->vars['playerId']) && $this->vars['playerId'] != '') {

			$user = null;

			$query = get_users(array(
				'meta_key' 		=> 'playerId',
				'meta_value' 	=> $this->vars['playerId']
			));

			if (!is_wp_error($query) && !empty($query)) {
				$user = reset($query);
			}

			if ($user && isset($user->ID)) {

				if (isset($this->vars['data'])) {
					$ids = [];

					foreach ($this->vars['data'] as $vars) {
						if (isset($vars['id']) && $vars['id'] != '') {
							$ids[] = $vars['id'];
						}
					}
					update_user_meta($user->ID, 'cws_games_notifications', implode(',', $ids));
				}

				if (isset($this->vars['data']) && !empty($this->vars['data'])) {
					$notif = reset($this->vars['data']);

					if (isset($notif['id'])) {
						update_user_meta($user->ID, 'last_notification', $notif['id']);
					}
				}

			} else {

				$result['status'] 		= -701;
				$result['status_txt'] 	= "Invalid playerId.";

			}

		} else {

			$result['status'] 		= -702;
			$result['status_txt'] 	= "Missing playerId.";

		}

		$result['request'] = $this->vars;

		return $result;
	}

	/**
	 * Update site currencies
	 * 
	 * @param [object] 
	 * {
	 * 		"token": "65866c7d5686d099f7f2714604dbc019",
	 * 		"action": "PutCurrencies",
	 * 		"data": [
				{
					"currency_code": "FICO",
					"currency_symbol": "FiCo",
					"default_currency": 1
				},
				{
					"currency_code": "TICO",
					"currency_symbol": "TiCo",
					"default_currency": 0
				}
		    ]
	 * }
	 * @return [void]
	 */ 
	public function PutCurrencies()
	{
		$result = [
			'status' 		=> 801,
			'status_txt' 	=> 'Sucessfully updated.'
		];

		if (isset($this->vars['data']) && !empty($this->vars['data'])) {

			if (is_array($this->vars['data'])) {

				$keys = ['currency_code', 'currency_symbol', 'default_currency'];

				foreach ($this->vars['data'] as $currency_array) {
					foreach ($currency_array as $key => $value) {
						if (!in_array($key, $keys)) {
							$result['status'] 		= -804;
							$result['status_txt'] 	= 'Unknown key `'.$key.'` in the request data.';

							return $result;
						}

						if ( ($key == 'currency_code' || $key == 'currency_symbol') && ($value == '' || !is_string($value)) ) {
							$result['status'] 		= -805;
							$result['status_txt'] 	= 'Invalid or empty value provided for key `'.$key.'` in the request data. String required.';

							return $result;
						}

						if ( $key == 'default_currency' && $value != 0 && ( $value == "" || !in_array(intval($value), array(0, 1)) ) ) {
							$result['status'] 		= -806;
							$result['status_txt'] 	= 'Invalid or empty value provided for key `'.$key.'` in the request data. 0 | 1 required';

							return $result;
						}
					}
				}

				// Save settings
				$options = get_option('cws_games_plugin');

				$options['currencies'] = $this->vars['data'];

				update_option('cws_games_plugin', $options);

			} else {

				$result['status'] 		= -803;
				$result['status_txt'] 	= 'Invalid request data format. Array required.';

			}

		} else {

			$result['status'] 		= -801;
			$result['status_txt'] 	= 'Empty currencies';

		}

		__sd($this->vars, 'BackOffice API - PutCurrencies');

		$result['request'] = $this->vars;

		return $result;
	}
}