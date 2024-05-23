<?php


add_action('wp_ajax_spinWheelOfFortune', 'ajax_spinWheelOfFortune');
add_action('wp_ajax_nopriv_spinWheelOfFortune', 'ajax_spinWheelOfFortune');

function ajax_spinWheelOfFortune()
{	
	$result = [];

	$user_id = get_current_user_id();

	$playerId = get_user_meta($user_id, 'playerId', true);

	$data = [
		'playerId' => $playerId
	];

	$CWS_BackofficeAPI = new CWS_BackofficeAPI();

	$response = $CWS_BackofficeAPI->spinWheelOfFortune($data);

	if (isset($response['code']) && $response['code'] == 200) {
		$result = [
			'status' => $response['code'],
			'status_txt' => $response['data']['message']
		];
	} elseif (isset($response['errors']['message']) && $response['errors']['message'] != '') {
		$result = [
			'status' => -1,
			'status_txt' => $response['errors']['message']
		];
	} else {
		$result = [
			'status' => -1,
			'status_txt' => __('An error occured. Please Spin again.', 'cws-games')
		];
	}

	wp_send_json($result);
	die();
}

add_action('wp_ajax_clearLogFile', 'ajax_clearLogFile');
add_action('wp_ajax_nopriv_clearLogFile', 'ajax_clearLogFile');

function ajax_clearLogFile()
{		
	$result = ['status' => 1, 'status_txt' => 'API Logs successfully deleted', 'class' => 'green'];

	file_put_contents(ABSPATH . "/tmp/request.txt" , "" );

	echo json_encode($result);
	die();
}

add_action('wp_ajax_importGames', 'ajax_importGames');
add_action('wp_ajax_nopriv_importGames', 'ajax_importGames');

function ajax_importGames()
{		
	$result = [];

	$CWS_BackofficeAPI = new CWS_BackofficeAPI;

	$response = $CWS_BackofficeAPI->getGames();

	if (isset($response['code']) && $response['code'] == 200) {

		$CWS_GamesGameslist = new CWS_GamesGameslist();

		$CWS_GamesGameslist->trashOldGames($importedGames);

		$result['status'] 		= $response['code'];
		$result['status_txt'] 	= 'Response code: ' . $response['code'] . ' | ' . 'Success';

		if (isset($response['data']) && !empty($response['data'])) {
			
			$importedGames 		= [];

			foreach ($response['data'] as $row) {
				$CWS_GamesGameslist->SetVars($row);
				$save = $CWS_GamesGameslist->save();

				if ($save['status'] > 0) {
					$importedGames[] = $row['guid'];
				}
			}

		} else {
			$result['status_txt'] = 'No games found';
		}

	} else {
		if (isset($response['code'])) {
			$result['status'] = $response['code'];

			if (!empty($response['errors'])) {
				$error = reset($response['errors']);

				$result['status_txt'] 	= 'Response code: ' . $response['code'] . ' | ' .  ($error ?? '');
			} else {
				$result['status_txt'] 	= 'Response code: ' . $response['code'] . ' | ' .  ($response['data']['message'] ?? '');
			}
			
		} else {
			$result['status'] 		= 400;
			$result['status_txt'] 	= 'Response code: ' . $response['code'] . ' | ' .  'Bad request';
		}
	}

	echo json_encode($result);
	die();
}
 
add_action('wp_ajax_GameLaunch', 'ajax_GameLaunch');
add_action('wp_ajax_nopriv_GameLaunch', 'ajax_GameLaunch');

function ajax_GameLaunch()
{
	// __sd($_POST , "ajax_GameLaunch init request ...... ");

	$CWS_GamesConfig 		= new CWS_GamesConfig();
	$CWS_BackofficeAPI 		= new CWS_BackofficeAPI;	
	$CWS_GamesGameslist 	= new CWS_GamesGameslist();
	$CWS_GamesMostPlayed 	= new CWS_GamesMostPlayed();

	$params = [
        'post__in' 	=> array($_POST['game_id'])
    ];

    $user_id = get_current_user_id();

	$game = $CWS_GamesGameslist->FindByParams($params, 'single');
	$data = array();

	// __sd($game , "ajax_GameLaunch game data ...... ");

	if (!empty($game)) {

		// $server_data = $CWS_GamesConfig->CWS_GamesGetServerData($game->server_id);

		// $data['api_url'] = $server_data['api_url'];
		// $data = [
		// 	'api_url'		=> $server_data['api_url'],
		// 	'api_key'		=> $server_data['api_key'],
		// 	'api_secret'	=> $server_data['api_secret'] ?? '',
		// 	'game_id'		=> $game->guid,
		// 	'game_source'	=> $game->source,
		// ];

		// $CWS_GamesAPI = new CWS_GamesAPI();
		// $CWS_GamesAPI->SetVars($data);

		// $result = $CWS_GamesAPI->API_GameLaunch();

		// if ($result['status'] > 0) {

		// 	$game_category = $CWS_GamesGameslist->getGameCategory($game->id);

		// 	$most_played_data = [
		// 		'game_guid'		=> $game->guid,
		// 		'user_id' 		=> get_current_user_id(),
		// 		'game_category'	=> implode(',', $game_category)
		// 	];

		// 	$CWS_GamesMostPlayed->SetVars($most_played_data);
		// 	$most_played_save = $CWS_GamesMostPlayed->save();

		// }

		$data['server'] 	= $game->server_id ?? 0;
		$data['guid'] 		= $game->guid ?? '';
		$data['language'] 	= 'en';

	}

	if (isset($_POST['mobile']) && in_array($_POST['mobile'], array(0, 1))) {
		$data['mobile'] = $_POST['mobile'];
	}

	if ($user_id) {
		$default_currency = $CWS_GamesConfig->getDefaultCurrency($user_id)['currency_code'] ?? '';

		if ($default_currency) {
			$data['currency'] = $default_currency;
		}

		$playerId = get_user_meta($user_id, 'playerId', true);

		if ($playerId) {
			$data['player'] = $playerId;
		}
	}

	$response = $CWS_BackofficeAPI->gameLaunch($data);

	__sd($response, 'BackOffice gameLaunch response');

	if (isset($response['code']) && $response['code'] == 200) {
		$result = array(
			'status' => $response['code']
		);

		$result = array_merge($result, $response['data']);
	} else {

		$result['status'] = -1;

		if (isset($response['errors']) && !empty($response['errors'])) {
			$result['status_txt'] = $response['errors'][array_key_first($response['errors'])];
		} else {
			$result['status_txt'] = __('An unknown error occured. Please try again.', 'cws_games');
		}

		$modal_atts = [
			'title' 	=> __('Game Error', 'cws_games'),
			'content' 	=> __('An error occured and we were not able to start the game. If you continue having problems, please contact an administrator.')
		];

		$modal = prepare_modal($modal_atts);

		$result['modal'] = $modal;
	}

	echo json_encode($result);
	die(); 
}
 
add_action('wp_ajax_GetPlayerInfo', 'ajax_GetPlayerInfo');
add_action('wp_ajax_nopriv_GetPlayerInfo', 'ajax_GetPlayerInfo');

function ajax_GetPlayerInfo()
{ 
	$result = [];
 
	$_POST = cws_takeAjaxData();

	if (isset($_POST['data']) && !empty($_POST['data'])) { 

		$data = $_POST['data'];

		$CWS_GamesAPIValidation = new CWS_GamesAPIValidation();
		$CWS_GamesAPIValidation->SetVars($data);
		$tokenTest = $CWS_GamesAPIValidation->Validation_114_Extended();

		if(!$tokenTest['result']) wp_send_json($tokenTest);

		$token_data 		= $CWS_GamesAPIValidation->parseToken($data['token']);
		$data["token"] 		= $token_data['token'];
		$data["currencyId"] = $token_data['currencyId'];
		$data['privateKey']	= $token_data['privateKey'];

		$hashTest = $CWS_GamesAPIValidation->Validation_200_Extended($data['privateKey'] ?? '', $_POST['time'] ?? '', $_POST['data'] ?? [], $_POST['hash'] ?? '');

		if(!$hashTest['result']) wp_send_json($hashTest);
 
		__sd($data, 'GetPlayerInfo DATA');

		$CWS_GamesAPI = new CWS_GamesAPI();
		$CWS_GamesAPI->SetVars($data);
		
		$result = $CWS_GamesAPI->API_GetPlayerInfo();

	} else {

		$result['result'] 	= false;
		$result['err_desc'] = 'General Error';
		$result['err_code']	= 130;

	}

	__sd($result, 'GetPlayerInfo RESPONSE');

	wp_send_json($result);
	die();
}

add_action('wp_ajax_Withdraw', 'ajax_Withdraw');
add_action('wp_ajax_nopriv_Withdraw', 'ajax_Withdraw');

function ajax_Withdraw()
{
	$result = [];

	$_POST = cws_takeAjaxData();

	if (isset($_POST['data']) && !empty($_POST['data'])) {

		// $data = json_decode(stripslashes($_POST['data']), TRUE); 

		$data = $_POST['data'];

		// Vins patch 
		$CWS_GamesAPIValidation = new CWS_GamesAPIValidation();
		$CWS_GamesAPIValidation->SetVars($data);
		$tokenTest = $CWS_GamesAPIValidation->Validation_114_Extended();

		if(!$tokenTest['result']) wp_send_json($tokenTest); 

		$token_data 		= $CWS_GamesAPIValidation->parseToken($data['token']);
		$data["token"] 		= $token_data['token'];
		$data["currencyId"] = $token_data['currencyId'];

		__sd($data, 'Withdraw DATA');

		$referer = $_SERVER['HTTP_REFERER']; 
		// Parse URL to get host
		$domain = parse_url($referer, PHP_URL_HOST);

		__sd($domain, 'Withdraw DOMAIN');

		$CWS_GamesConfig 	= new CWS_GamesConfig();
		$CWS_GamesGameslist = new CWS_GamesGameslist();
		$CWS_GamesAPI 		= new CWS_GamesAPI();
		$CWS_GamesServers 	= new CWS_GamesServers();
		$CWS_GamesSessions 	= new CWS_GamesSessions();
		$CWS_GamesAPI->SetVars($data);
		 
		$result = $CWS_GamesAPI->API_Withdraw();

		if (!isset($result['err_code']) || ($result['err_code'] != 7 && $result['err_code'] != 21 && $result['err_code'] != 104 && $result['err_code'] != 106 && $result['err_code'] != 114)) {
			$CWS_GamesTransactions = new CWS_GamesTransactions();

			$cws_games_transaction_fields = [
				'token' 				=> isset($data['token']) ? $data['token'] : '',
				'transaction_type'		=> 'withdraw',
				'type'					=> 'debit',
				'gameId'				=> isset($data['gameId']) ? $data['gameId'] : '',
				'transactionId'			=> isset($result['transactionId']) ? $result['transactionId'] : '',
				'id_session' 			=> $CWS_GamesSessions->getSessionHash($data),
				'winAmount'				=> isset($data['winAmount']) ? $data['winAmount'] : '',
				'betAmount'				=> isset($data['betAmount']) ? $data['betAmount'] : '',
				'balance'				=> isset($result['balance']) ? $result['balance'] : '',
				'wallet_transaction_id'	=> isset($result['transaction_fields']['wallet_transaction_id']) ? $result['transaction_fields']['wallet_transaction_id'] : '',
				'user_id'				=> isset($result['transaction_fields']['user_id']) ? $result['transaction_fields']['user_id'] : '',
				'status'				=> isset($result['err_code']) ? $result['err_code'] : '1',
				'status_txt'			=> isset($result['err_desc']) ? $result['err_desc'] : 'OK',
				'request_json'			=> json_encode($data, JSON_UNESCAPED_UNICODE),
			];

			$user_id 	= isset($result['transaction_fields']['user_id']) ? $result['transaction_fields']['user_id'] : '';

			unset($result['transaction_fields']);

			$cws_games_transaction_fields['response_json'] = json_encode($result, JSON_UNESCAPED_UNICODE);

			$CWS_GamesTransactions->SetVars($cws_games_transaction_fields);

			$transaction_save = $CWS_GamesTransactions->save();

			__sd($cws_games_transaction_fields , "transaction SAVE DATA");
			__sd($transaction_save , "transaction SAVE RESULT");

			// NEW - Prepare Session Save/Update

			$gameslist_args = [
				'meta_query'	=> [
					[
						'key' 		=> 'game_guid',
						'value'		=> isset($data['gameId']) ? $data['gameId'] : '',
						'compare' 	=> '='
					]
				]
			];

			$game 		= $CWS_GamesGameslist->FindByParams($gameslist_args, 'single');
			$server_id 	= isset($game->server_id) ? $game->server_id : '';
			$server    	= $CWS_GamesServers->getServerByServerId($server_id);

			if ($user_id != '') {
				$userdata = get_userdata($user_id);
			}

			$currency 			= $CWS_GamesConfig->getDefaultCurrency();
			$default_currency 	= $currency['currency_code'] ?? '';

			if (isset($data['currencyId']) && $data['currencyId'] != '') {
				$default_currency = $data['currencyId'];
			}

			$userFullName 	= '';
			$first_name 	= get_user_meta($user_id, 'first_name', true);
			$last_name 		= get_user_meta($user_id, 'last_name', true);

			if ((isset($first_name) && $first_name != '') || (isset($last_name) && $last_name != '')) {
				$userFullNameArray = [];

				if (isset($first_name) && $first_name != '') {
					$userFullNameArray[] = $first_name;
				}
				if (isset($last_name) && $last_name != '') {
					$userFullNameArray[] = $last_name;
				}

				$userFullName = implode(' ', $userFullNameArray);
			} else {
				$userFullName = isset($userdata->data->display_name) ? $userdata->data->display_name : '';
			}

			$cws_games_sessions_fields = [
				'id_session' 			=> $CWS_GamesSessions->getSessionHash($data),
				'token' 				=> isset($data['token']) ? $data['token'] : '',
				'userId' 				=> $user_id,
				'userFullName' 			=> $userFullName,
				'userName' 				=> isset($userdata->data->display_name) ? $userdata->data->display_name : '',
				'providerId' 			=> isset($server['api_server_id']) ? $server['api_server_id'] : '',
				'providerName'			=> isset($server['api_server_alias']) ? $server['api_server_alias'] : '',
				'gameId' 				=> isset($data['gameId']) ? $data['gameId'] : '',
				'gameName' 				=> isset($game->name_game) ? $game->name_game : '',
				'currency' 				=> $default_currency,
				'currency2' 			=> '',
				'currencyExchangeRate' 	=> '',
				'totIn'					=> isset($data['betAmount']) ? $data['betAmount'] : '',
				// 'totOut' 				=> isset($data['winAmount']) ? $data['winAmount'] : '',
				'totSpins'				=> 1
			];

			$CWS_GamesSessions->SetVars($cws_games_sessions_fields);

			$session_save = $CWS_GamesSessions->save();

			__sd($session_save , "session SAVE RESULT");
		}

	} else {

		$result['result'] 	= false;
		$result['err_desc'] = 'Missing data.';
		$result['err_code']	= 130;

	}

	__sd($result, 'Withdraw RESPONSE');

	wp_send_json($result);
	die();
}

add_action('wp_ajax_Deposit', 'ajax_Deposit');
add_action('wp_ajax_nopriv_Deposit', 'ajax_Deposit');

function ajax_Deposit()
{
	$result = [];

	$_POST = cws_takeAjaxData();

	if (isset($_POST['data']) && !empty($_POST['data'])) {

		// $data = json_decode(stripslashes($_POST['data']), TRUE);

		$data = $_POST['data'];

		// Vins patch 
		$CWS_GamesAPIValidation = new CWS_GamesAPIValidation();
		$CWS_GamesAPIValidation->SetVars($data);
		$tokenTest = $CWS_GamesAPIValidation->Validation_114_Extended();

		if(!$tokenTest['result']) wp_send_json($tokenTest); 

		$token_data 		= $CWS_GamesAPIValidation->parseToken($data['token']);
		$data["token"] 		= $token_data['token'];
		$data["currencyId"] = $token_data['currencyId'];

		__sd($data, 'Deposit DATA');

		$CWS_GamesConfig 	= new CWS_GamesConfig();
		$CWS_GamesGameslist = new CWS_GamesGameslist();
		$CWS_GamesAPI 		= new CWS_GamesAPI();
		$CWS_GamesServers 	= new CWS_GamesServers();
		$CWS_GamesSessions 	= new CWS_GamesSessions();
		$CWS_GamesAPI->SetVars($data);

		// $game = $CWS_GamesGameslist->FindByParams(['guid' => $data['gameId']], 'single');

		// if (!empty($game) && isset($game->source) && $game->source == 'free') {
		// 	$result = $CWS_GamesAPI->API_Deposit_Free();

		// 	__sd($result, 'Deposit RESPONSE');

		// 	wp_send_json($result);
		// 	die();
		// }
		
		$result = $CWS_GamesAPI->API_Deposit();

		if (!isset($result['err_code']) || ($result['err_code'] != 7 && $result['err_code'] != 104 && $result['err_code'] != 106 && $result['err_code'] != 114)) {
			$CWS_GamesTransactions = new CWS_GamesTransactions();

			$cws_games_transaction_fields = [
				'token' 				=> isset($data['token']) ? $data['token'] : '',
				'transaction_type'		=> 'deposit',
				'type'					=> 'credit',
				'gameId'				=> isset($data['gameId']) ? $data['gameId'] : '',
				'transactionId'			=> isset($result['transactionId']) ? $result['transactionId'] : '',
				'id_session' 			=> $CWS_GamesSessions->getSessionHash($data),
				'winAmount'				=> isset($data['winAmount']) ? $data['winAmount'] : '',
				'betAmount'				=> isset($data['betAmount']) ? $data['betAmount'] : '',
				'balance'				=> isset($result['balance']) ? $result['balance'] : '',
				'wallet_transaction_id'	=> isset($result['transaction_fields']['wallet_transaction_id']) ? $result['transaction_fields']['wallet_transaction_id'] : '',
				'user_id'				=> isset($result['transaction_fields']['user_id']) ? $result['transaction_fields']['user_id'] : '',
				'status'				=> isset($result['err_code']) ? $result['err_code'] : '1',
				'status_txt'			=> isset($result['err_desc']) ? $result['err_desc'] : 'OK',
				'request_json'			=> json_encode($data, JSON_UNESCAPED_UNICODE),
			];

			$user_id 	= isset($result['transaction_fields']['user_id']) ? $result['transaction_fields']['user_id'] : '';

			unset($result['transaction_fields']);

			$cws_games_transaction_fields['response_json'] = json_encode($result, JSON_UNESCAPED_UNICODE);

			$CWS_GamesTransactions->SetVars($cws_games_transaction_fields);

			$transaction_save = $CWS_GamesTransactions->save();

			// NEW - Prepare Session Save/Update

			$gameslist_args = [
				'meta_query'	=> [
					[
						'key' 		=> 'game_guid',
						'value'		=> isset($data['gameId']) ? $data['gameId'] : '',
						'compare' 	=> '='
					]
				]
			];

			$game 		= $CWS_GamesGameslist->FindByParams($gameslist_args, 'single');
			$server_id 	= isset($game->server_id) ? $game->server_id : '';
			$server    	= $CWS_GamesServers->getServerByServerId($server_id);

			if ($user_id != '') {
				$userdata = get_userdata($user_id);
			}

			$currency 			= $CWS_GamesConfig->getDefaultCurrency();
			$default_currency 	= $currency['currency_code'] ?? '';

			if (isset($data['currencyId']) && $data['currencyId'] != '') {
				$default_currency = $data['currencyId'];
			}

			$userFullName 	= '';
			$first_name 	= get_user_meta($user_id, 'first_name', true);
			$last_name 		= get_user_meta($user_id, 'last_name', true);

			if ((isset($first_name) && $first_name != '') || (isset($last_name) && $last_name != '')) {
				$userFullNameArray = [];

				if (isset($first_name) && $first_name != '') {
					$userFullNameArray[] = $first_name;
				}
				if (isset($last_name) && $last_name != '') {
					$userFullNameArray[] = $last_name;
				}

				$userFullName = implode(' ', $userFullNameArray);
			} else {
				$userFullName = isset($userdata->data->display_name) ? $userdata->data->display_name : '';
			}

			$cws_games_sessions_fields = [
				'id_session' 			=> $CWS_GamesSessions->getSessionHash($data),
				'token' 				=> isset($data['token']) ? $data['token'] : '',
				'userId' 				=> $user_id,
				'userFullName' 			=> $userFullName,
				'userName' 				=> isset($userdata->data->display_name) ? $userdata->data->display_name : '',
				'providerId' 			=> isset($server['api_server_id']) ? $server['api_server_id'] : '',
				'providerName'			=> isset($server['api_server_alias']) ? $server['api_server_alias'] : '',
				'gameId' 				=> isset($data['gameId']) ? $data['gameId'] : '',
				'gameName' 				=> isset($game->name_game) ? $game->name_game : '',
				'currency' 				=> $default_currency,
				'currency2' 			=> '',
				'currencyExchangeRate' 	=> '',
				// 'totIn'					=> isset($data['betAmount']) ? $data['betAmount'] : '',
				'totOut' 				=> isset($data['winAmount']) ? $data['winAmount'] : '',
				'totSpins'				=> 1
			];

			$CWS_GamesSessions->SetVars($cws_games_sessions_fields);

			$session_save = $CWS_GamesSessions->save();

			__sd($session_save , "session SAVE RESULT");
		}

	} else {

		$result['result'] 	= false;
		$result['err_desc'] = 'Missing data.';
		$result['err_code']	= 130;

	}

	__sd($result, 'Deposit RESPONSE');

	wp_send_json($result);
	die();
}

add_action('wp_ajax_RollbackTransaction', 'ajax_RollbackTransaction');
add_action('wp_ajax_nopriv_RollbackTransaction', 'ajax_RollbackTransaction');

function ajax_RollbackTransaction()
{
	$result = [];

	$_POST = cws_takeAjaxData();

	if (isset($_POST['data']) && !empty($_POST['data'])) {

		// $data = json_decode(stripslashes($_POST['data']), TRUE);

		$data = $_POST['data'];

		// Vins patch 
		$CWS_GamesAPIValidation = new CWS_GamesAPIValidation();
		$CWS_GamesAPIValidation->SetVars($data);
		$tokenTest = $CWS_GamesAPIValidation->Validation_114_Extended();

		if(!$tokenTest['result']) wp_send_json($tokenTest);

		$token_data 		= $CWS_GamesAPIValidation->parseToken($data['token']);
		$data["token"] 		= $token_data['token'];
		$data["currencyId"] = $token_data['currencyId'];
        
        $CWS_GamesConfig 	= new CWS_GamesConfig();
    	$user = $CWS_GamesConfig->getUserByToken($data["token"]);

		$balanceTest = $CWS_GamesAPIValidation->Validation_21_Extended($user->ID);

		if(!$balanceTest['result']) wp_send_json($balanceTest);

		__sd($data, 'RollbackTransaction DATA');

		$CWS_GamesConfig 	= new CWS_GamesConfig();
		$CWS_GamesGameslist = new CWS_GamesGameslist();
		$CWS_GamesAPI 		= new CWS_GamesAPI();
		$CWS_GamesServers 	= new CWS_GamesServers();
		$CWS_GamesSessions 	= new CWS_GamesSessions();
		$CWS_GamesAPI->SetVars($data);

		// $game = $CWS_GamesGameslist->FindByParams(['guid' => $data['gameId']], 'single');

		// if (!empty($game) && isset($game->source) && $game->source == 'free') {
		// 	$result = $CWS_GamesAPI->API_RollbackTransaction_Free();

		// 	__sd($result, 'RollbackTransaction RESPONSE');

		// 	wp_send_json($result);
		// 	die();
		// }
		
		$result = $CWS_GamesAPI->API_RollbackTransaction();

		__sd($result, 'RollbackTransaction st');

		if (!isset($result['err_code']) || ($result['err_code'] != 104 && $result['err_code'] != 106 && $result['err_code'] != 114 && $result['err_code'] != 105 && $result['err_code'] != 107)) {
			$CWS_GamesTransactions = new CWS_GamesTransactions();

			$cws_games_transaction_fields = [
				'token' 				=> isset($data['token']) ? $data['token'] : '',
				'transaction_type'		=> 'rollback',
				'type'					=> isset($result['transaction_fields']['type']) ? $result['transaction_fields']['type'] : '',
				'gameId'				=> isset($data['gameId']) ? $data['gameId'] : '',
				'transactionId'			=> isset($result['transactionId']) ? $result['transactionId'] : '',
				'id_session' 			=> $CWS_GamesSessions->getSessionHash($data),
				'winAmount'				=> isset($result['transaction_fields']['winAmount']) ? $result['transaction_fields']['winAmount'] : '',
				'betAmount'				=> isset($result['transaction_fields']['betAmount']) ? $result['transaction_fields']['betAmount'] : '',
				'balance'				=> isset($result['balance']) ? $result['balance'] : '',
				'currency'				=> isset($result['currencyId']) ? $result['currencyId'] : '',
				'wallet_transaction_id'	=> isset($result['transaction_fields']['wallet_transaction_id']) ? $result['transaction_fields']['wallet_transaction_id'] : '',
				'user_id'				=> isset($result['transaction_fields']['user_id']) ? $result['transaction_fields']['user_id'] : '',
				'status'				=> isset($result['err_code']) ? $result['err_code'] : '1',
				'status_txt'			=> isset($result['err_desc']) ? $result['err_desc'] : 'OK',
				'request_json'			=> json_encode($data, JSON_UNESCAPED_UNICODE),
			];

			$transaction_fields = $result['transaction_fields'] ?? [];
			$user_id 			= isset($transaction_fields['user_id']) ? $transaction_fields['user_id'] : '';

			unset($result['transaction_fields']);

			$cws_games_transaction_fields['response_json'] = json_encode($result, JSON_UNESCAPED_UNICODE);

			$CWS_GamesTransactions->SetVars($cws_games_transaction_fields);

			$transaction_save = $CWS_GamesTransactions->save();

			// NEW - Prepare Session Save/Update

			$gameslist_args = [
				'meta_query'	=> [
					[
						'key' 		=> 'game_guid',
						'value'		=> isset($data['gameId']) ? $data['gameId'] : '',
						'compare' 	=> '='
					]
				]
			];

			$game 		= $CWS_GamesGameslist->FindByParams($gameslist_args, 'single');
			$server_id 	= isset($game->server_id) ? $game->server_id : '';
			$server    	= $CWS_GamesServers->getServerByServerId($server_id);

			if ($user_id != '') {
				$userdata = get_userdata($user_id);
			}

			$currency 			= $CWS_GamesConfig->getDefaultCurrency();
			$default_currency   = $data['currencyId'];
			
			$userFullName 	= '';
			$first_name 	= get_user_meta($user_id, 'first_name', true);
			$last_name 		= get_user_meta($user_id, 'last_name', true);

			if ((isset($first_name) && $first_name != '') || (isset($last_name) && $last_name != '')) {
				$userFullNameArray = [];

				if (isset($first_name) && $first_name != '') {
					$userFullNameArray[] = $first_name;
				}
				if (isset($last_name) && $last_name != '') {
					$userFullNameArray[] = $last_name;
				}

				$userFullName = implode(' ', $userFullNameArray);
			} else {
				$userFullName = isset($userdata->data->display_name) ? $userdata->data->display_name : '';
			}

			$cws_games_sessions_fields = [
				'id_session' 			=> $CWS_GamesSessions->getSessionHash($data),
				'token' 				=> isset($data['token']) ? $data['token'] : '',
				'userId' 				=> $user_id,
				'userFullName' 			=> $userFullName,
				'userName' 				=> isset($userdata->data->display_name) ? $userdata->data->display_name : '',
				'providerId' 			=> isset($server['api_server_id']) ? $server['api_server_id'] : '',
				'providerName'			=> isset($server['api_server_alias']) ? $server['api_server_alias'] : '',
				'gameId' 				=> isset($data['gameId']) ? $data['gameId'] : '',
				'gameName' 				=> isset($game->name_game) ? $game->name_game : '',
				'currency' 				=> $default_currency,
				'currency2' 			=> '',
				'currencyExchangeRate' 	=> '',
				'totIn'					=> isset($transaction_fields['betAmount']) ? $transaction_fields['betAmount'] : '',
				'totOut' 				=> isset($transaction_fields['winAmount']) ? $transaction_fields['winAmount'] : '',
			];

			$CWS_GamesSessions->SetVars($cws_games_sessions_fields);

			$session_save = $CWS_GamesSessions->save();
 
		}

	} else {

		$result['result'] 	= false;
		$result['err_desc'] = 'Missing data.';
		$result['err_code']	= 130;

	}

	__sd($result, 'RollbackTransaction RESPONSE');

	wp_send_json($result);
	die();
}

add_action('wp_ajax_WithdrawDeposit', 'ajax_WithdrawDeposit');
add_action('wp_ajax_nopriv_WithdrawDeposit', 'ajax_WithdrawDeposit');

function ajax_WithdrawDeposit()
{
	$result = [];

	$_POST = cws_takeAjaxData();

	if (isset($_POST['data']) && !empty($_POST['data'])) {

		// $data = json_decode(stripslashes($_POST['data']), TRUE);

		$data = $_POST['data'];

		// Vins patch 
		$CWS_GamesAPIValidation = new CWS_GamesAPIValidation();
		$CWS_GamesAPIValidation->SetVars($data);
		$tokenTest = $CWS_GamesAPIValidation->Validation_114_Extended();

		if(!$tokenTest['result']) wp_send_json($tokenTest); 

		$token_data 		= $CWS_GamesAPIValidation->parseToken($data['token']);
		$data["token"] 		= $token_data['token'];
		$data["currencyId"] = $token_data['currencyId'];

		__sd($data, 'WithdrawDeposit DATA');

		$CWS_GamesConfig 	= new CWS_GamesConfig();
		$CWS_GamesGameslist = new CWS_GamesGameslist();
		$CWS_GamesAPI 		= new CWS_GamesAPI();
		$CWS_GamesServers 	= new CWS_GamesServers();
		$CWS_GamesSessions 	= new CWS_GamesSessions();
		$CWS_GamesAPI->SetVars($data);

		$currency 			= $CWS_GamesConfig->getDefaultCurrency();
		$default_currency 	= $currency['currency_code'] ?? '';

		if (isset($data['currencyId']) && $data['currencyId'] != '') {
			$default_currency = $data['currencyId'];
		}

		// $game = $CWS_GamesGameslist->FindByParams(['guid' => $data['gameId']], 'single');

		// if (!empty($game) && isset($game->source) && $game->source == 'free') {
		// 	$result = $CWS_GamesAPI->API_WithdrawDeposit_Free();

		// 	__sd($result, 'WithdrawDeposit RESPONSE');

		// 	wp_send_json($result);
		// 	die();
		// }
		
		$result = $CWS_GamesAPI->API_Withdraw();

		if (!isset($result['err_code']) || ($result['err_code'] != 7 && $result['err_code'] != 21 && $result['err_code'] != 104 && $result['err_code'] != 106 && $result['err_code'] != 114)) {
			$CWS_GamesTransactions = new CWS_GamesTransactions();

			$cws_games_transaction_fields = [
				'token' 				=> isset($data['token']) ? $data['token'] : '',
				'transaction_type'		=> 'withdrawdeposit',
				'type'					=> 'debit',
				'gameId'				=> isset($data['gameId']) ? $data['gameId'] : '',
				'transactionId'			=> isset($result['transactionId']) ? $result['transactionId'] : '',
				'id_session' 			=> $CWS_GamesSessions->getSessionHash($data),
				'winAmount'				=> '',
				'betAmount'				=> isset($data['betAmount']) ? $data['betAmount'] : '',
				'balance'				=> isset($result['balance']) ? $result['balance'] : '',
				'wallet_transaction_id'	=> isset($result['transaction_fields']['wallet_transaction_id']) ? $result['transaction_fields']['wallet_transaction_id'] : '',
				'user_id'				=> isset($result['transaction_fields']['user_id']) ? $result['transaction_fields']['user_id'] : '',
				'status'				=> isset($result['err_code']) ? $result['err_code'] : '1',
				'status_txt'			=> isset($result['err_desc']) ? $result['err_desc'] : 'OK',
				'request_json'			=> json_encode($data, JSON_UNESCAPED_UNICODE),
			];

			$transaction_fields = $result['transaction_fields'] ?? [];
			$user_id 			= isset($transaction_fields['user_id']) ? $transaction_fields['user_id'] : '';

			unset($result['transaction_fields']);

			$cws_games_transaction_fields['response_json'] = json_encode($result, JSON_UNESCAPED_UNICODE);

			$CWS_GamesTransactions->SetVars($cws_games_transaction_fields);

			$transaction_save = $CWS_GamesTransactions->save();

			// NEW - Prepare Session Save/Update

			$gameslist_args = [
				'meta_query'	=> [
					[
						'key' 		=> 'game_guid',
						'value'		=> isset($data['gameId']) ? $data['gameId'] : '',
						'compare' 	=> '='
					]
				]
			];

			$game 		= $CWS_GamesGameslist->FindByParams($gameslist_args, 'single');
			$server_id 	= isset($game->server_id) ? $game->server_id : '';
			$server    	= $CWS_GamesServers->getServerByServerId($server_id);

			if ($user_id != '') {
				$userdata = get_userdata($user_id);
			}

			$userFullName 	= '';
			$first_name 	= get_user_meta($user_id, 'first_name', true);
			$last_name 		= get_user_meta($user_id, 'last_name', true);

			if ((isset($first_name) && $first_name != '') || (isset($last_name) && $last_name != '')) {
				$userFullNameArray = [];

				if (isset($first_name) && $first_name != '') {
					$userFullNameArray[] = $first_name;
				}
				if (isset($last_name) && $last_name != '') {
					$userFullNameArray[] = $last_name;
				}

				$userFullName = implode(' ', $userFullNameArray);
			} else {
				$userFullName = isset($userdata->data->display_name) ? $userdata->data->display_name : '';
			}

			$cws_games_sessions_fields = [
				'id_session' 			=> $CWS_GamesSessions->getSessionHash($data),
				'token' 				=> isset($data['token']) ? $data['token'] : '',
				'userId' 				=> $user_id,
				'userFullName' 			=> $userFullName,
				'userName' 				=> isset($userdata->data->display_name) ? $userdata->data->display_name : '',
				'providerId' 			=> isset($server['api_server_id']) ? $server['api_server_id'] : '',
				'providerName'			=> isset($server['api_server_alias']) ? $server['api_server_alias'] : '',
				'gameId' 				=> isset($data['gameId']) ? $data['gameId'] : '',
				'gameName' 				=> isset($game->name_game) ? $game->name_game : '',
				'currency' 				=> $default_currency,
				'currency2' 			=> '',
				'currencyExchangeRate' 	=> '',
				'totIn'					=> isset($data['betAmount']) ? $data['betAmount'] : '',
				// 'totOut' 				=> isset($data['winAmount']) ? $data['winAmount'] : '',
				'totSpins'				=> 1
			];

			$CWS_GamesSessions->SetVars($cws_games_sessions_fields);

			$session_save = $CWS_GamesSessions->save();

			__sd($session_save , "session SAVE RESULT");
		}

		if (!$result['result']) {

			__sd($result, 'WithdrawDeposit RESPONSE');
			
			wp_send_json($result);
			die();
		}

		$result = $CWS_GamesAPI->API_Deposit([104]);

		if (!isset($result['err_code']) || ($result['err_code'] != 7 && $result['err_code'] != 104 && $result['err_code'] != 106 && $result['err_code'] != 114)) {
			$CWS_GamesTransactions = new CWS_GamesTransactions();

			$cws_games_transaction_fields = [
				'token' 				=> isset($data['token']) ? $data['token'] : '',
				'transaction_type'		=> 'withdrawdeposit',
				'type'					=> 'credit',
				'gameId'				=> isset($data['gameId']) ? $data['gameId'] : '',
				'transactionId'			=> isset($result['transactionId']) ? $result['transactionId'] : '',
				'id_session' 			=> $CWS_GamesSessions->getSessionHash($data),
				'winAmount'				=> isset($data['winAmount']) ? $data['winAmount'] : '',
				'betAmount'				=> '',
				'balance'				=> isset($result['balance']) ? $result['balance'] : '',
				'wallet_transaction_id'	=> isset($result['transaction_fields']['wallet_transaction_id']) ? $result['transaction_fields']['wallet_transaction_id'] : '',
				'user_id'				=> isset($result['transaction_fields']['user_id']) ? $result['transaction_fields']['user_id'] : '',
				'status'				=> isset($result['err_code']) ? $result['err_code'] : '1',
				'status_txt'			=> isset($result['err_desc']) ? $result['err_desc'] : 'OK',
				'request_json'			=> json_encode($data, JSON_UNESCAPED_UNICODE),
			];

			$transaction_fields = $result['transaction_fields'] ?? [];
			$user_id 			= isset($transaction_fields['user_id']) ? $transaction_fields['user_id'] : '';
			
			unset($result['transaction_fields']);

			$cws_games_transaction_fields['response_json'] = json_encode($result, JSON_UNESCAPED_UNICODE);

			$CWS_GamesTransactions->SetVars($cws_games_transaction_fields);

			$transaction_save = $CWS_GamesTransactions->save();

			// NEW - Prepare Session Save/Update

			$gameslist_args = [
				'meta_query'	=> [
					[
						'key' 		=> 'game_guid',
						'value'		=> isset($data['gameId']) ? $data['gameId'] : '',
						'compare' 	=> '='
					]
				]
			];

			$game 		= $CWS_GamesGameslist->FindByParams($gameslist_args, 'single');
			$server_id 	= isset($game->server_id) ? $game->server_id : '';
			$server    	= $CWS_GamesServers->getServerByServerId($server_id);

			if ($user_id != '') {
				$userdata = get_userdata($user_id);
			}

			$userFullName 	= '';
			$first_name 	= get_user_meta($user_id, 'first_name', true);
			$last_name 		= get_user_meta($user_id, 'last_name', true);

			if ((isset($first_name) && $first_name != '') || (isset($last_name) && $last_name != '')) {
				$userFullNameArray = [];

				if (isset($first_name) && $first_name != '') {
					$userFullNameArray[] = $first_name;
				}
				if (isset($last_name) && $last_name != '') {
					$userFullNameArray[] = $last_name;
				}

				$userFullName = implode(' ', $userFullNameArray);
			} else {
				$userFullName = isset($userdata->data->display_name) ? $userdata->data->display_name : '';
			}

			$cws_games_sessions_fields = [
				'id_session' 			=> $CWS_GamesSessions->getSessionHash($data),
				'token' 				=> isset($data['token']) ? $data['token'] : '',
				'userId' 				=> $user_id,
				'userFullName' 			=> $userFullName,
				'userName' 				=> isset($userdata->data->display_name) ? $userdata->data->display_name : '',
				'providerId' 			=> isset($server['api_server_id']) ? $server['api_server_id'] : '',
				'providerName'			=> isset($server['api_server_alias']) ? $server['api_server_alias'] : '',
				'gameId' 				=> isset($data['gameId']) ? $data['gameId'] : '',
				'gameName' 				=> isset($game->name_game) ? $game->name_game : '',
				'currency' 				=> $default_currency,
				'currency2' 			=> '',
				'currencyExchangeRate' 	=> '',
				// 'totIn'					=> isset($data['betAmount']) ? $data['betAmount'] : '',
				'totOut' 				=> isset($data['winAmount']) ? $data['winAmount'] : '',
			];

			$CWS_GamesSessions->SetVars($cws_games_sessions_fields);

			$session_save = $CWS_GamesSessions->save();

			__sd($session_save , "session SAVE RESULT");
		}

	} else {

		$result['result'] 	= false;
		$result['err_desc'] = 'Missing data.';
		$result['err_code']	= 130;

	}

	__sd($result, 'WithdrawDeposit RESPONSE');

	wp_send_json($result);
	die();
}

add_action('wp_ajax_GenerateUsers', 'ajax_GenerateUsers');
add_action('wp_ajax_nopriv_GenerateUsers', 'ajax_GenerateUsers');

/**
 * Automatically generate users
 * 
 */ 
function ajax_GenerateUsers()
{	
	$result = ['status' => 100, 'status_txt' => 'OK'];

	$_POST = cws_takeAjaxData();

	if (isset($_POST['data']) && !empty($_POST['data'])) {

		require_once(ABSPATH.'wp-admin/includes/user.php');

		global $wpdb;

		$data 				= $_POST['data'];
		// Vins patch 
		$CWS_GamesAPIValidation = new CWS_GamesAPIValidation();
		$CWS_GamesAPIValidation->SetVars($data);
		$tokenTest = $CWS_GamesAPIValidation->Validation_114_Extended();

		if(!$tokenTest['result']) wp_send_json($tokenTest); 

		$token_data 		= $CWS_GamesAPIValidation->parseToken($data['token']);
		$data["token"] 		= $token_data['token'];
		$data["currencyId"] = $token_data['currencyId'];

		$mode 				= $data['mode'] ?? 'create';
		$Woo_Wallet_Wallet  = new Woo_Wallet_Wallet();
		$CWS_GamesConfig 	= new CWS_GamesConfig();
		$currencies 		= $CWS_GamesConfig->getCurrencies();

		if ($mode == 'create') {

			// Create users
			$count 					= $data['count'] ?? 100;
			$balanceAmount 			= $data['balance'] ?? 500000; // 500,000.000
			$generated_users_count 	= 0;

			for ($i = 1; $i <= $count; $i++) {
				$hash  		= md5(uniqid(rand(), true));
				$username 	= 'fake_user_' . $hash;
				$password  	= $hash;

				$user_id = wp_create_user( $username, $password );

				if ($user_id && !is_wp_error($user_id)) {
					update_user_meta( $user_id, 'is_fake_user', '1' );

					$recharge_amount 	= apply_filters('woo_wallet_credit_purchase_amount', $balanceAmount, $user_id);
					$transactionDesc 	= 'Fake user wallet balance transaction';

					if (isset($currencies) && !empty($currencies)) {
						foreach ($currencies as $currency) {
							$transaction_id = $Woo_Wallet_Wallet->credit($user_id, $recharge_amount, $transactionDesc, array('currency' => $currency['currency_code'] ?? ''));
						}
					} else {
						$transaction_id = $Woo_Wallet_Wallet->credit($user_id, $recharge_amount, $transactionDesc);
					}
					
					$generated_users_count++;

					$result['credentials'][] = 'Username: ' . $username . ' | Password: ' . $password;
				}
			}

			if ($generated_users_count > 0) {
				$result['status_txt'] 	= 'New users created: ' . $generated_users_count;
			} else {
				$result['status'] 		= -101;
				$result['status_txt'] 	= 'An error occured while trying to generate new users. New users created: 0';
			}

		} elseif ($mode == 'refreshBalance') {

			// Refresh user wallet balances
			$balanceAmount = $data['balance'] ?? 500000; // 500,000.000

			$users = get_users(array('meta_key' => 'is_fake_user', 'meta_value' => '1'));

			if ($users && !empty($users) && !is_wp_error($users)) {
				foreach ($users as $user) {
					$user_id = $user->ID;

					$recharge_amount 	= apply_filters('woo_wallet_credit_purchase_amount', $balanceAmount, $user_id);
					$transactionDesc 	= 'Fake user wallet balance transaction';

					if (isset($currencies) && !empty($currencies)) {
						foreach ($currencies as $currency) {
							$transaction_id = $Woo_Wallet_Wallet->credit($user_id, $recharge_amount, $transactionDesc, array('currency' => $currency['currency_code'] ?? ''));
						}
					} else {
						$transaction_id = $Woo_Wallet_Wallet->credit($user_id, $recharge_amount, $transactionDesc);
					}
				}
			} else {
				$result['status'] 		= -102;
				$result['status_txt'] 	= 'An error occured while trying to recharge wallet ballance.';
			}

		} elseif ($mode == 'delete') {
			$users = get_users(array('meta_key' => 'is_fake_user', 'meta_value' => '1'));

			if ($users && !empty($users) && !is_wp_error($users)) {
				$deleted_users_count = 0;

				foreach ($users as $user) {
					$user_id = $user->ID;

					$delete = wp_delete_user( $user_id );

					if ($delete) {

						// Clear all custom tables
						$sql1 = "DELETE FROM {$wpdb->prefix}cws_games_transactions WHERE user_id = {$user_id}";
						$sql2 = "DELETE FROM {$wpdb->prefix}cws_games_sessions WHERE userId = {$user_id}";
						$sql3 = "DELETE FROM {$wpdb->prefix}woo_wallet_transactions WHERE user_id = {$user_id}";

						$wpdb->query($sql1);
						$wpdb->query($sql2);
						$wpdb->query($sql3);

						$deleted_users_count++;
					}
				}

				if ($deleted_users_count > 0) {
					$result['status_txt'] 	= 'Users deleted: ' . $deleted_users_count;
				} else {
					$result['status'] 		= -104;
					$result['status_txt'] 	= 'An error occured while trying to delete all fake users.';
				}

			} else {
				$result['status'] 		= -103;
				$result['status_txt'] 	= 'An error occured while trying to delete all fake users.';
			}
		}

	} else {
		$result['status'] 		= -100;
		$result['status_txt'] 	= 'Invalid or missing data.';
	}

	wp_send_json($result);
	die();
}

add_action('wp_ajax_AddToMyGames', 'ajax_AddToMyGames');
add_action('wp_ajax_nopriv_AddToMyGames', 'ajax_AddToMyGames');

function ajax_AddToMyGames()
{	
	$result = [];

	$CWS_GamesMyGames = new CWS_GamesMyGames();

	$CWS_GamesMyGames->SetVars($_POST);

	$result = $CWS_GamesMyGames->addToMyGames();

	wp_send_json($result);
	die();
}

add_action('wp_ajax_RemoveFromMyGames', 'ajax_RemoveFromMyGames');
add_action('wp_ajax_nopriv_RemoveFromMyGames', 'ajax_RemoveFromMyGames');

function ajax_RemoveFromMyGames()
{	
	$result = [];

	$CWS_GamesMyGames = new CWS_GamesMyGames();

	$CWS_GamesMyGames->SetVars($_POST);

	$result = $CWS_GamesMyGames->removeFromMyGames();

	wp_send_json($result);
	die();
}

add_action('wp_ajax_cws_games_sessions_export_csv_by_provider', 'ajax_cws_games_sessions_export_csv_by_provider');
add_action('wp_ajax_nopriv_cws_games_sessions_export_csv_by_provider', 'ajax_cws_games_sessions_export_csv_by_provider');

function ajax_cws_games_sessions_export_csv_by_provider()
{
	$result = ['status' => 1, 'status_txt' => 'OK'];

	$CWS_GamesCsvExport = new CWS_GamesCsvExport();
	$CWS_GamesSessions  = new CWS_GamesSessions();
    $CWS_GamesServers   = new CWS_GamesServers();

    $params = [
	    'orderby'   	=> ' ORDER BY `id`',
	    'providerId' 	=> $_POST['providerId'] ?? ''
	];

	$sessions = $CWS_GamesSessions->FindByParams($params);

	$result = $CWS_GamesCsvExport->prepareCsv($sessions);

	wp_send_json($result);
	die();
}

add_action('wp_ajax_cws_games_sessions_export_csv', 'ajax_cws_games_sessions_export_csv');
add_action('wp_ajax_nopriv_cws_games_sessions_export_csv', 'ajax_cws_games_sessions_export_csv');

function ajax_cws_games_sessions_export_csv()
{
	$result = ['status' => 1, 'status_txt' => 'OK'];

	$CWS_GamesCsvExport = new CWS_GamesCsvExport();
	$CWS_GamesSessions  = new CWS_GamesSessions();
    $CWS_GamesServers   = new CWS_GamesServers();

	parse_str( $_POST['formdata'], $POST );

	$params = [
        'orderby'   => ' ORDER BY `id`',
    ];

    if (isset($POST['formdata']) && !empty($POST['formdata'])) {

        if (isset($POST['formdata']['date_from']) && $POST['formdata']['date_from'] != '') {
            $params['date_creation'] = [
                'cond'      => '>=',
                'value'     => "'".$POST['formdata']['date_from']."'"
            ];
        }

        if (isset($POST['formdata']['date_to']) && $POST['formdata']['date_to'] != '') {
            if (array_key_exists('date_creation', $params)) {
                $old_value = $params['date_creation']['cond'] . ' ' . $params['date_creation']['value'];

                $params['date_creation']['cond']    = ' AND ';

                $params['date_creation']['value']   = [$old_value, " <= '" . $POST['formdata']['date_to'] . "'"];

            } else {
                $params['date_creation'] = [
                    'cond'      => '<=',
                    'value'     => "'".$POST['formdata']['date_to']."'"
                ];
            }
            
        }

        if (isset($POST['formdata']['gameId']) && $POST['formdata']['gameId'] != '' && $POST['formdata']['gameId'] != -1 ) {
            $params['gameId'] = $POST['formdata']['gameId'];
        }

        if (isset($POST['formdata']['providerId']) && $POST['formdata']['providerId'] != '' && $POST['formdata']['providerId'] != -1) {
            $params['providerId'] = $POST['formdata']['providerId'];
        }

        if (isset($POST['formdata']['currency']) && $POST['formdata']['currency'] != '' && $POST['formdata']['currency'] != -1) {
            $params['currency'] = $POST['formdata']['currency'];
        }
    }

    // echo '<pre>'.print_r($params, true).'</pre>';

    $sessions = $CWS_GamesSessions->FindByParams($params);

	$result = $CWS_GamesCsvExport->prepareCsv($sessions);

	wp_send_json($result);
	die();
}

add_action('wp_ajax_cws_games_sessions_delete_csv_files', 'ajax_cws_games_sessions_delete_csv_files');
add_action('wp_ajax_nopriv_cws_games_sessions_delete_csv_files', 'ajax_cws_games_sessions_delete_csv_files');

function ajax_cws_games_sessions_delete_csv_files()
{
	$CWS_GamesCsvExport = new CWS_GamesCsvExport();

	$result = $CWS_GamesCsvExport->deleteCsvFiles();

	wp_send_json($result);
	die();
}

add_action('wp_ajax_cws_games_setUserDefaultCurrency', 'ajax_cws_games_setUserDefaultCurrency');
add_action('wp_ajax_nopriv_cws_games_setUserDefaultCurrency', 'ajax_cws_games_setUserDefaultCurrency');

function ajax_cws_games_setUserDefaultCurrency()
{
	$result = ['status' => 1, 'status_txt' => 'OK'];

	if (isset($_POST['currency']) && $_POST['currency'] != '' &&
		isset($_POST['user_id']) && $_POST['user_id'] != '') {
		update_user_meta( $_POST['user_id'], 'user_default_currency', $_POST['currency'] );

		ob_start();

		echo do_shortcode('[theme_mini_wallet tmpl="'.($_POST['tmpl'] ?? '').'"]');

		$html = ob_get_contents();
		ob_end_clean();

		$result['html'] = str_replace('"', "'", $html);

	} else {
		$result['status'] 		= -1;
		$result['status_txt'] 	= 'Missing or invalid currency and/or user_id';
	}

	wp_send_json($result);
	die();
}



add_action('wp_ajax_getAccessToken', 'ajax_getAccessToken');
add_action('wp_ajax_nopriv_getAccessToken', 'ajax_getAccessToken');

function ajax_getAccessToken()
{
	$CWS_BackofficeAPI = new CWS_BackofficeAPI;

	$data = [
		'user' 		=> $_POST['user'],
		'password' 	=> $_POST['password'],
	];

	__sd($_POST, "ajax_getToken ");

	$result = $CWS_BackofficeAPI-> getToken($data);

	wp_send_json($result ?? []);

	die();

}

add_action('wp_ajax_getSettings', 'ajax_getSettings');
add_action('wp_ajax_nopriv_getSettings', 'ajax_getSettings');

function ajax_getSettings()
{
	$result = [];
	__sd($_POST, "ajax_getSettings ");

	$CWS_BackofficeAPI = new CWS_BackofficeAPI;	
	$CWS_GamesConfig = new CWS_GamesConfig();
	$response = $CWS_BackofficeAPI->getSettings();

	$settings = $response['settings'];

	if (!empty($settings)) {
		if (isset($settings['currencies']) && !empty($settings['currencies'])) {
			$result['success'] = [
				'txt_status' => 'Success backoffice response. Currencies are obtained',
				'class' => 'green'
			];
			$result['errors'] = [];

			$update_status = $CWS_GamesConfig->setCurrencies($settings['currencies']);

			if($update_status) {
				$result['success'] = [
					'txt_status' => 'Currencies are updated',
					'class' => 'green'
				];
			} else {
				$result['success'] = [];
				$result['errors'] = [
					'txt_status' => 'Updating error!',
					'class' => 'red'
				];
			}
		}

		if (isset($settings['jackpot']) && $settings['jackpot'] != '') {
			$update_status = $CWS_GamesConfig->setJackpot($settings['jackpot']);
		}
	} else {
		$result['errors'] = [
			'txt_status' => 'Error backoffice response. Currencies is empty!'
		];
	}

	

	__sd($result, "ajax_getSettings 2");
	wp_send_json($result);
	die();

}


add_action('wp_ajax_draw_wallet_transaction_details_table', 'ajax_draw_wallet_transaction_details_table');
add_action('wp_ajax_nopriv_draw_wallet_transaction_details_table', 'ajax_draw_wallet_transaction_details_table');

function ajax_draw_wallet_transaction_details_table()
{
	$start  = isset( $_POST['start'] ) ? sanitize_text_field( wp_unslash( $_POST['start'] ) ) : 0;
	$length = isset( $_POST['length'] ) ? sanitize_text_field( wp_unslash( $_POST['length'] ) ) : 10;
	$search = isset( $_POST['search'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['search'] ) ) : '';
	$searched_date = '';

	if (isset($search['value']) && !empty($search['value'])) {
		$searched_date = $search['value'];
	}

	$response = array(
		'draw'            => isset( $_POST['draw'] ) ? sanitize_text_field( wp_unslash( $_POST['draw'] ) ) : 1,
		'recordsTotal'    => 0,
		'recordsFiltered' => 0,
		'data'            => array(),
	);

	$user_id = get_current_user_id();

	$CWS_GamesUsers = new CWS_GamesUsers();

	$CWS_GamesUsers->getUserWallet($user_id);

	$transactions = $CWS_GamesUsers->getUserWalletTransactions($user_id);

	$recordsTotal = count($transactions);
	$recordsFiltered = 0;

	if ($transactions && !empty($transactions)) {

		$index = 0;

		foreach ($transactions as $transaction) {

			if ($length <= 0) { break; }

			if ($start > $index ) { continue; }

			if ($searched_date != '') {

				if (strtotime($searched_date) == strtotime(date('Y-m-d', strtotime($transaction['created_at'])))) {

					$response['data'][] = array(
						'credit' 	=> $transaction['direction'] == 'credit' ? $transaction['amount'] . ' ' . $transaction['currency'] : '-',
						'debit' 	=> $transaction['direction'] == 'debit' ? $transaction['amount'] . ' ' . $transaction['currency'] : '-',
						'details' 	=> $transaction['details'] ?? '',
						'date' 		=> wc_string_to_datetime( $transaction['created_at'] )->date_i18n( wc_date_format() )
					);

					$index++;

					$recordsFiltered++;
				} else {
					continue;
				}
				
			} else {
				$response['data'][] = array(
					'credit' 	=> $transaction['direction'] == 'credit' ? $transaction['amount'] . ' ' . $transaction['currency'] : '-',
					'debit' 	=> $transaction['direction'] == 'debit' ? $transaction['amount'] . ' ' . $transaction['currency'] : '-',
					'details' 	=> $transaction['details'] ?? '',
					'date' 		=> wc_string_to_datetime( $transaction['created_at'] )->date_i18n( wc_date_format() )
				);

				$index++;
			}

		}
	}

	if ($searched_date == '') {
		$recordsFiltered = $recordsTotal;
	}

	$response['recordsTotal'] = $recordsTotal;
	$response['recordsFiltered'] = $recordsFiltered;

	wp_send_json( $response );
	die();
}


add_action('wp_ajax_updateWallet', 'ajax_updateWallet');
add_action('wp_ajax_nopriv_updateWallet', 'ajax_updateWallet');

function ajax_updateWallet()
{	
	$response = [];

	if (is_user_logged_in()) {
		$user_id = get_current_user_id();

		$CWS_GamesUsers = new CWS_GamesUsers();

		$CWS_GamesUsers->getUserWallet($user_id);

		ob_start();

		echo do_shortcode('[theme_mini_wallet tmpl="'.($_POST['tmpl'] ?? '').'"]');

		$html = ob_get_contents();
		ob_end_clean();

		$response['html'] = str_replace('"', "'", $html);

	} else {
		$response['status'] = -1;
		$response['status_txt'] = 'User not logged in.';
	}

	wp_send_json( $response );
	die();
}

add_action('wp_ajax_getNotification', 'ajax_getNotification');
add_action('wp_ajax_nopriv_getNotification', 'ajax_getNotification');

function ajax_getNotification()
{
	$response = [
		'status' 		=> 1,
		'status_txt' 	=> 'OK'
	];

	if (is_user_logged_in()) {

		if (isset($_POST['notificationId']) && $_POST['notificationId'] == '') {
			$response['status'] 	= -2;
			$response['status_txt'] = 'Missing notification ID.';

			wp_send_json($response);
			die();
		}

		$user_id 	= get_current_user_id();
		$data 		= [
			'id' 	=> $_POST['notificationId']
		];

		$CWS_GamesUsers = new CWS_GamesUsers();

		$notification = $CWS_GamesUsers->getNotificationForScreen($user_id, $data);

		if (isset($notification['title']) && isset($notification['body'])) {
			$modal_atts = [
				'title' 	=> $notification['title'],
				'content' 	=> $notification['body']
			];

			$modal = prepare_modal($modal_atts);

			$response['modal'] = $modal;
		}

	} else {
		$response['status'] 	= -1;
		$response['status_txt'] = 'User not logged in.';
	}

	wp_send_json($response);
	die();
}

add_action('wp_ajax_getNotifications', 'ajax_getNotifications');
add_action('wp_ajax_nopriv_getNotifications', 'ajax_getNotifications');

function ajax_getNotifications()
{
	$response = ['status' => 1, 'status_txt' => 'OK'];

	$user_id = get_current_user_id();

	$data = [];

	if (isset($_POST['page']) && $_POST['page'] != '') {
		$data['page'] = $_POST['page'];
	}

	$CWS_GamesUsers = new CWS_GamesUsers();

	$notifications = $CWS_GamesUsers->getUserNotifications($user_id, $data);

	if ($notifications) {
		set_query_var('notifications', $notifications);
	}

	if (isset($_POST['base_url']) && $_POST['base_url'] != '') {
		set_query_var('base_url', $_POST['base_url']);
	}

	ob_start();

    load_template(
        CWS_GAMES_ABSPATH_TEMPLATE . '/frontend/shortcodes/cws-games-notifications.php', false
    );

    $html = ob_get_contents();
	ob_end_clean();

    echo $html;
	die();
}

add_action('wp_ajax_checkNotification', 'ajax_checkNotification');
add_action('wp_ajax_nopriv_checkNotification', 'ajax_checkNotification');

function ajax_checkNotification()
{
	$response = [
		'status' 		=> 1,
		'status_txt' 	=> 'OK'
	];

	if (is_user_logged_in()) {

		$user_id = get_current_user_id();

		$last_notification = get_user_meta($user_id, 'last_notification', true);

		if ($last_notification != '' && $last_notification != 0) {
			$data 		= [
				'id' 	=> $last_notification
			];

			$CWS_GamesUsers = new CWS_GamesUsers();

			$notification = $CWS_GamesUsers->getNotificationForScreen($user_id, $data);

			if (isset($notification['title']) && isset($notification['body'])) {
				$modal_atts = [
					'title' 	=> $notification['title'],
					'content' 	=> $notification['body']
				];

				$modal = prepare_modal($modal_atts);

				$response['modal'] = $modal;
			}

			update_user_meta($user_id, 'last_notification', 0);
		}

	} else {
		$response['status'] 	= -1;
		$response['status_txt'] = 'User not logged in.';
	}

	wp_send_json($response);
	die();
}

add_action('wp_ajax_cwsGamesGetAutompleteResults', 'ajax_cwsGamesGetAutompleteResults');
add_action('wp_ajax_nopriv_cwsGamesGetAutompleteResults', 'ajax_cwsGamesGetAutompleteResults');

function ajax_cwsGamesGetAutompleteResults()
{
	$response = [];

	$search = $_POST['query'] ?? '';

	if ($search != '') {
		$CWS_GamesGameslist = new CWS_GamesGameslist();

		$params = [];

    	$params['meta_query']['relation'] = 'AND';

    	$params['s'] = $search;

    	$games = $CWS_GamesGameslist->FindByParams($params);

    	if ($games && !empty($games)) {
    		foreach ($games as $game) {
    			$prepareGame = array(
    				'label' 		=> $game->name_game ?? '',
    				'value' 		=> $game->name_game ?? '',
    				'name' 			=> $game->name_game ?? '',
    				'icon' 			=> $game->urlButton ?? '',
    				'provider_name' => $game->game_server_name ?? 'FilsGame',
					'id' => $game->id,
    			);

    			$response[] = $prepareGame;
    		}
    	}
	}

	wp_send_json($response);
	die();
}

add_action('wp_ajax_checkJackpot', 'ajax_checkJackpot');
add_action('wp_ajax_nopriv_checkJackpot', 'ajax_checkJackpot');

function ajax_checkJackpot()
{
	$result = [
		'status' 		=> 1,
		'status_txt' 	=> 'OK'
	];

	$CWS_BackofficeAPI = new CWS_BackofficeAPI;	
	$response = $CWS_BackofficeAPI->getSettings();

	$settings = $response['settings'];

	if (!empty($settings)) {
		if (isset($settings['jackpot']) && $settings['jackpot'] != '') {
			// $result['jackpot'] = number_format($settings['jackpot'], 2, '.', ',');
			$result['jackpot'] = $settings['jackpot'];
		}
	}

	wp_send_json($result);
	die();
}

add_action('wp_ajax_redeemBalance', 'ajax_redeemBalance');
add_action('wp_ajax_nopriv_redeemBalance', 'ajax_redeemBalance');

function ajax_redeemBalance()
{
	$response = [];

	parse_str( $_POST['formdata'], $POST );

	$CWS_GamesUsers = new CWS_GamesUsers();

	$user_id = $POST['user_id'] ?? 0;

	$data = [
		'currency' 	=> $POST['currency'] ?? '',
		'direction' => 'debit',
		'type' 		=> 'redeemable',
		'amount' 	=> $POST['amount'] ?? 0,
		'details' 	=> __('Withdraw to my bank account', 'cws_games')
	];

	$_response = $CWS_GamesUsers->addWalletTransaction($user_id, $data);

	if (isset($_response['code']) && $_response['code'] == 200) {
		$response['status'] = $_response['code'];
		$response['status_txt'] = __('Amount successfully redeemed. Pending approval.', 'cws_games');
	} else {
		$response['status'] = -1;

		if (isset($_response['errors']) && !empty($_response['errors'])) {
			$response['status_txt'] = $_response['errors'][array_key_first($_response['errors'])];
		} elseif (isseT($_response['data']['message']) && $_response['data']['message'] != '') {
			$response['status_txt'] = $_response['data']['message'];
		}
	}

	wp_send_json($response);
	die();
}

add_action('wp_ajax_cws_games_LoadMoreGames', 'ajax_cws_games_LoadMoreGames');
add_action('wp_ajax_nopriv_cws_games_LoadMoreGames', 'ajax_cws_games_LoadMoreGames');

function ajax_cws_games_LoadMoreGames()
{
	parse_str( $_POST['formdata'], $POST );

	$atts = [];

	if (!empty($POST)) {
		foreach ($POST as $key => $value) {
			$atts[] = $key . '="' . $value . '"';
		}
	}

	if (isset($_POST['loadmore_count']) && $_POST['loadmore_count'] != '') {
		$atts[] = 'loadmore_count="'.$_POST['loadmore_count'].'"';
	}

	ob_start();

	echo do_shortcode('[cws_games_gameslist ' . implode(' ', $atts) . ']');

	$response = ob_get_clean();

	echo $response;
	die();
}


add_action('wp_ajax_cws_games_soundToggle', 'ajax_cws_games_soundToggle');
add_action('wp_ajax_nopriv_cws_games_soundToggle', 'ajax_cws_games_soundToggle');

function ajax_cws_games_soundToggle()
{
	$user_id = $_POST['user_id'] ?? 0;

	$sound = $_POST['sound'] ?? 0;

	update_user_meta($user_id, 'sound', $sound);

	set_query_var('user_id', $user_id);
	set_query_var('sound', $sound);

	ob_start();

    load_template(
        CWS_GAMES_ABSPATH_TEMPLATE . '/frontend/shortcodes/cws-games-sound-toggle.php', false
    );

    $response = ob_get_contents();
	ob_end_clean();

	echo $response;
	die();
}