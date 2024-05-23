<?php

class CWS_GamesLoginRewards
{	
	protected $user_id 	= 0;

	/**
     * Constructor
     */
    public function __construct()
    {
    	if (is_user_logged_in()) {
    		$this->user_id 	= get_current_user_id();
    	}
    }

    /**
	 * Return Login Rewards - Enabled
	 *
	 * @return void
	 */
	public static function GetLoginRewardsEnabled()
	{
		$options = get_option('cws_games_plugin_login_rewards');
		return $options['enable_login_rewards'] ?? 0;
	}

	/**
	 * Return Login Rewards - Amount
	 *
	 * @return void
	 */
	public static function GetLoginRewardsAmount()
	{
		$options = get_option('cws_games_plugin_login_rewards');
		return $options['login_reward_amount'] ?? 0;
	}

	/**
	 * Return Login Rewards - Interval
	  *
	 * @return void
	 */
	public static function GetLoginRewardsInterval()
	{
		$options = get_option('cws_games_plugin_login_rewards');
		return $options['login_reward_interval'] ?? 0;
	}

	/**
	 * Check if User is legitimate for login reward
	 *
	 * @return void
	 */
	public function GetIsUserLegitimateForReward()
	{	
		// return true; //debug;

		if ( $this->user_id == 0 ) {
			return false;
		}

		$interval = 24; // hours

		if (!$interval) {
			return false;
		}

		$user_last_reward_date = get_user_meta($this->user_id, 'last_login_reward', true);

		if ( !$user_last_reward_date ) {
			return true;
		}

		$current_date = date('Y-m-d H:i', strtotime('now'));

		$date_diff_in_hours = round( ( strtotime($current_date) - strtotime($user_last_reward_date) ) / (60 * 60) );

		return $date_diff_in_hours >= $interval ?? false;
	}

	/**
	 * Check if login reward modal needs to be shown
	 *
	 * @return void
	 */
	public function GetLoginRewardModalStatus()
	{	
		// return false; //debug;

		if ($this->user_id == 0) {
			return true;
		}

		$modal_status = get_user_meta($this->user_id, 'login_reward_modal', true);

		return $modal_status ?? 0;
	}

	/**
	 * Add new login reward to current User
	 *
	 * @return void
	 */
	public function AddLoginRewardToUser()
	{	
		if ($this->user_id == 0) {
			return ['status' => false];
		}

		$show_user_welcome_modal = get_user_meta($this->user_id, 'show_user_welcome_modal', true);

		if ($show_user_welcome_modal != 1) {
			return ['status' => false]; 
		}

		$amount = 0;

		$CWS_BackofficeAPI = new CWS_BackofficeAPI();

		$settings = $CWS_BackofficeAPI->getSettings();

		if (isset($settings['original']) && !empty($settings['original'])) {
			if (isset($settings['original']['code']) && $settings['original']['code'] == 200) {
				if (isset($settings['original']['data']) && !empty($settings['original']['data'])) {
					foreach ($settings['original']['data'] as $key => $settings) {
						if ($key == 'bonuses') {
							if (isset($settings['welcome']) && intval($settings['welcome']) > 0) {
								$amount = intval($settings['welcome']);
							}
						}
					}
				}
			}
		}

		if ($amount == 0) {
			return ['status' => false];
		}

		$result = [];

		$CWS_GamesConfig 	= new CWS_GamesConfig();
		$CWS_GamesUsers  	= new CWS_GamesUsers();

		$default_currency 	= $CWS_GamesConfig->getDefaultCurrency();
		$current_date 		= date('Y-m-d H:i', strtotime('now'));

		$transaction_data = [
			'currency' 	=> $default_currency['currency_code'],
			'direction' => 'credit',
			'amount' 	=> $amount,
			'details' 	=> 'Welcome Bonus on ' . $current_date . ', amount : ' . $amount
		];

		$response = $CWS_GamesUsers->addWalletTransaction($this->user_id, $transaction_data);

		if (isset($response['code']) && $response['code'] == 200) {

			update_user_meta($this->user_id, 'last_login_reward', $current_date);
			update_user_meta($this->user_id, 'show_user_welcome_modal', 0);

			$result['status'] 		= true;
			$result['data'] 		= $response['data'];
		} else {
			$result['status'] 		= false;

			if (isset($response['errors']) && !empty($response['errors'])) {
				$result['err_desc'] = $response['errors'][array_key_first($response['errors'])];
			} else {
				$result['err_desc'] = __('General Error. There has been a problem with this transaction. Try again.', 'cws_games');
			}
		}

        return $result;
	}

	/**
	 * Add new login reward to current User
	 *
	 * @return void
	 */
	public function AddRegisterRewardToUser()
	{	
		if ($this->user_id == 0) {
			return ['status' => false];
		}

		$show_user_register_modal = get_user_meta($this->user_id, 'show_user_register_modal', true);

		if ($show_user_register_modal != 1) {
			return ['status' => false]; 
		}

		$amount = 0;

		$CWS_BackofficeAPI = new CWS_BackofficeAPI();

		$settings = $CWS_BackofficeAPI->getSettings();

		if (isset($settings['original']) && !empty($settings['original'])) {
			if (isset($settings['original']['code']) && $settings['original']['code'] == 200) {
				if (isset($settings['original']['data']) && !empty($settings['original']['data'])) {
					foreach ($settings['original']['data'] as $key => $settings) {
						if ($key == 'bonuses') {
							if (isset($settings['registration']) && intval($settings['registration']) > 0) {
								$amount = intval($settings['registration']);
							}
						}
					}
				}
			}
		}

		if ($amount == 0) {
			return ['status' => false];
		}

		$result = [];

		$CWS_GamesConfig 	= new CWS_GamesConfig();
		$CWS_GamesUsers  	= new CWS_GamesUsers();

		$default_currency 	= $CWS_GamesConfig->getDefaultCurrency();
		$current_date 		= date('Y-m-d H:i', strtotime('now'));

		$transaction_data = [
			'currency' 	=> $default_currency['currency_code'],
			'direction' => 'credit',
			'amount' 	=> $amount,
			'details' 	=> 'Register Bonus, amount : ' . $amount
		];

		$response = $CWS_GamesUsers->addWalletTransaction($this->user_id, $transaction_data);

		if (isset($response['code']) && $response['code'] == 200) {

			update_user_meta($this->user_id, 'show_user_register_modal', 0);

			$result['status'] 		= true;
			$result['data'] 		= $response['data'];
		} else {
			$result['status'] 		= false;

			if (isset($response['errors']) && !empty($response['errors'])) {
				$result['err_desc'] = $response['errors'][array_key_first($response['errors'])];
			} else {
				$result['err_desc'] = __('General Error. There has been a problem with this transaction. Try again.', 'cws_games');
			}
		}

        return $result;
	}

}