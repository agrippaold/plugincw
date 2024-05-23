<?php


class CWS_GamesMyGames
{
	protected $user_id = null;
	protected $vars    = [];

	/**
     * Constructor
     */
    public function __construct()
    {
        $this->user_id = get_current_user_id();
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

    public function addToMyGames()
    {
    	$result = [];

    	if (!$this->user_id) {
    		$result['status'] 		= -11;
    		$result['status_txt'] 	= 'Invalid user!';

    		return $result;
    	}

    	if (!isset($this->vars['game_guid']) || $this->vars['game_guid'] == '') {
    		$result['status'] 		= -12;
    		$result['status_txt'] 	= 'Invalid game!';

    		return $result;
    	}

    	$myGames = get_user_meta($this->user_id, 'my_games', true);

    	if ($myGames == '') {
    		$myGames = [];
    	} else {
    		$myGames = explode(',', $myGames);
    	}

    	if (!in_array($this->vars['game_guid'], $myGames)) {
    		$myGames[] = $this->vars['game_guid'];

    		$update_meta = update_user_meta($this->user_id, 'my_games', implode(',', $myGames));

    		if ($update_meta) {
    			$result['status'] 		= 11;
    			$result['status_txt'] 	= 'Game successfully added to "My Games"!';
    		} else {
    			$result['status'] 		= -13;
    			$result['status_txt'] 	= 'There was a problem with this action! Please try again!';
    		}
    	}

    	return $result;
    }

    public function removeFromMyGames()
    {
    	$result = [];

    	if (!$this->user_id) {
    		$result['status'] 		= -21;
    		$result['status_txt'] 	= 'Invalid user!';

    		return $result;
    	}

    	if (!isset($this->vars['game_guid']) || $this->vars['game_guid'] == '') {
    		$result['status'] 		= -22;
    		$result['status_txt'] 	= 'Invalid game!';

    		return $result;
    	}

    	$myGames = get_user_meta($this->user_id, 'my_games', true);

    	if ($myGames == '') {
    		$myGames = [];
    	} else {
    		$myGames = explode(',', $myGames);
    	}

    	foreach ($myGames as $key => $game_guid) {
    		if ($game_guid == $this->vars['game_guid']) {
    			unset($myGames[$key]);
    		}
    	}

    	$update_meta = update_user_meta($this->user_id, 'my_games', implode(',', $myGames));

		if ($update_meta) {
			$result['status'] 		= 21;
			$result['status_txt'] 	= 'Game successfully removed from "My Games"!';
		} else {
			$result['status'] 		= -23;
			$result['status_txt'] 	= 'There was a problem with this action! Please try again!';
		}

    	return $result;
    }
}