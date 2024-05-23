<?php


class CWS_GamesSessions
{
	protected $TableName = '';
    protected $wpdb      = null;
    protected $vars      = [];

	/**
     * Constructor
     */
    public function __construct()
    {
        global $wpdb;
        $this->wpdb = $wpdb; 
        $this->TableName = $this->wpdb->prefix . 'cws_games_sessions';
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
     * Prepare default values
     *
     * @return void
     */
    public function setValues()
    { 
        $fields = [];

        $hash 	= ($this->vars['id_session'] ? $this->vars['id_session'] : $this->getSessionHash());
        $gameId = $this->vars['gameId'] ?? '';

        $session = $this->FindByParams(['id_session' => $hash, 'gameId' => $gameId], 'single');

        $totIn = $totOut = $totSpins = 0;

        if (isset($session->totIn) && $session->totIn != '') {
        	$totIn += intval($session->totIn);
        }

        if (isset($this->vars['totIn']) && $this->vars['totIn'] != '') {
        	$totIn += intval($this->vars['totIn']);
        }

        if (isset($session->totOut) && $session->totOut != '') {
        	$totOut += intval($session->totOut);
        }

        if (isset($this->vars['totOut']) && $this->vars['totOut'] != '') {
        	$totOut += intval($this->vars['totOut']);
        }

        if (isset($session->totSpins) && $session->totSpins != '') {
        	$totSpins += intval($session->totSpins);
        }

        if (isset($this->vars['totSpins']) && $this->vars['totSpins'] != '') {
        	$totSpins += intval($this->vars['totSpins']);
        }

        $fields = [
			'id'					=> $session->id ?? ($this->vars['id'] ?? 0),
			'id_session' 			=> $session->id_session ?? ($this->vars['id_session'] ?? $hash),
			'token' 				=> $session->token ?? ($this->vars['token'] ?? ''),
			'userId' 				=> $session->user_id ?? ($this->vars['userId'] ?? ''),
			'userFullName' 			=> $session->userFullName ?? ($this->vars['userFullName'] ?? ''),
			'userName' 				=> $session->userName ?? ($this->vars['userName'] ?? ''),
			'providerId' 			=> $session->providerId ?? ($this->vars['providerId'] ?? ''),
			'providerName'			=> $session->providerName ?? ($this->vars['providerName'] ?? ''),
			'gameId' 				=> $session->gameId ?? ($this->vars['gameId'] ?? ''),
			'gameName' 				=> $session->gameName ?? ($this->vars['gameName'] ?? ''),
			'gameType' 				=> $session->gameType ?? ($this->vars['gameType'] ?? ''),
			'currency' 				=> $this->vars['currency'] ?? ($session->currency ?? ''),
			'currency2' 			=> $this->vars['currency2'] ?? ($session->currency2 ?? ''),
			'currencyExchangeRate' 	=> $this->vars['currencyExchangeRate'] ?? ($session->currencyExchangeRate ?? ''),
			'totIn' 				=> $totIn,
			'totOut' 				=> $totOut,
			'totSpins'				=> $totSpins,
			'date_update' 			=> date("Y-m-d H:i:s", time()),
			'ip' 					=> $session->ip ?? getUserIP()
		];
 
        return $fields;
    }

    /**
     * Save Method
     *
     * @param [type] $data
     * @return void
     */
    public function save()
    { 
        $fields = $this->setValues();

        __sd($fields , "session SAVE DATA");

        $result = $this->validate($fields);
        if ($result['status'] > 0) { 
            $result = $this->store($fields);
        } 

        if ($this->vars['id'] == 0 && $result['status'] > 0) {
            
            $result['status_txt'] = 'Row successfully stored';

        } else if ($this->vars['id'] > 0 && $result['status'] > 0) {
            
            $result['status_txt'] = 'Row successfully updated';
        
        }

        return $result;
    }

    /**
     * Main Store Function
     *
     * @param [type] $fields
     * @return void
     */
    public function store($fields)
    {  
        if (isset($fields['id']) && $fields['id'] > 0) {

            // Update
            $id 	= $fields['id'];
            $result = $this->wpdb->update(
                $this->TableName,
                $fields,
                [
                    'id' => $id
                ]
            ); 
            // $this->wpdb->show_errors();
            // $this->wpdb->print_error();

	        $result = ['status' =>  $id, 'id' =>  $id, 'status_txt' => 'Update OK'];

        } else {

            // Insert
            $result = $this->wpdb->insert($this->TableName, $fields);

            // $this->wpdb->show_errors();
            // $this->wpdb->print_error();

            if ($result) {
                $result = ['status' => $this->wpdb->insert_id, 'id' => $this->wpdb->insert_id, 'status_txt' => 'Insert OK'];
            } else {
                $result = ['status' => -1, 'status_txt' => ' !!!! Insert Error'];
            }

        }

        return $result;
    }

    /**
     * Validate data before save
     *
     * @param [type] $data
     * @return void
     */
    public function Validate($fields)
    {
        $result = ['status' => 1, 'status_txt' => 'OK'];

        return $result;
    }

    /**
     *  Filter Function By array of Params
     *
     * @param [type] $params
     * @param string $single_resutl
     * @return void
     */
    public function FindByParams($params, $single_result = 'multiple', $debugSQL = false)
    { 
        $where   = [];
        $results = null;

        foreach ($params as $column => $value) {

            if (strtolower($column) == 'orderby' || strtolower($column) == 'groupby') {
                continue;
            }

            if (is_array($value)) {

                if (is_array($value['value'])) {
                    $_where = [];

                    foreach ($value['value'] as $_value) {
                        $_where[] = $column . " " . $_value;
                    }

                    $where[] = '(' . implode($value['cond'], $_where) . ')';
                } else {
                    $where[] = $column . " " . $value['cond'] . " " . $value['value'];
                }

            } else {
                $where[] = $column . " = '" . $value . "'";
            }

        }

        $where_condition = implode(" AND ", $where);
        if (count($where) > 0) {
            $sql = "SELECT * FROM " . $this->TableName . " WHERE " . $where_condition;
        } else {
            $sql = "SELECT * FROM " . $this->TableName;
        }

        if (isset($params['orderby'])) {
            $sql .= " " . $params['orderby'];
        }

        if (isset($params['groupby'])) {
            $sql .= " " . $params['groupby'];
        }

        if ($debugSQL) {
            echo $sql . "<BR> \n";
        }

        // echo '<pre>'.print_r($sql, true).'</pre>';

        if ($single_result == 'single') {
            $results = $this->wpdb->get_row($sql);
        } else if ($single_result == 'details') {

            $results  = [];
            $_results = $this->wpdb->get_results($sql);
            foreach ($_results as $_result) {
                $results[$_result->id] = $this->getDetails($_result->id);
            }

        } else if ($single_result == 'single-details') {
            $results  = [];
            $_results = $this->wpdb->get_results($sql);
            foreach ($_results as $_result) {
                $results = $this->getDetails($_result->id);
            }
        } else {
            $results = $this->wpdb->get_results($sql);
        }

        return $results;
    }

    /**
     * Get full data
     *
     * @param [type] $data
     * @return void
     */
    public function getDetails($id_session)
    {  
        $item = $this->FindByParams(['id_session' => $id_session], 'single', false);

        if ($item) {
            $CWS_GamesTransactions = new CWS_GamesTransactions();

            $transactions = $CWS_GamesTransactions->FindByParams(['id_session' => $item->id_session]);

            if ($transactions && !empty($transactions)) {
                $item->transactions = $transactions;
            }
        }
 
        return $item;
    }

    /**
     * Returns all distinct values of a table column
     *
     * @param [string] $column
     * @return void
     */
    public function getTableColumnValues($column)
    {   
        $result = [];

        $sql = "SELECT DISTINCT {$column} FROM {$this->TableName}";

        $rows = $this->wpdb->get_results($sql);

        if (!empty($rows)) {
            foreach ($rows as $row) {
                $result[] = $row->$column;
            }
        }

        return $result;
    }


    /**
     * Get current user's session hash (id_session)
     * 
     * @param [array] $data - API Request params
     * @return [string] $hash
     */ 
    public function getSessionHash($data = array())
    {
        $hash = '';

        $hash .= $data['gameId'] ?? '';

        if (isset($data['roundId']) && $data['roundId'] != '') {
            $rountIdPartsArray = explode('|', $data['roundId']);

            if (isset($rountIdPartsArray[1])) {
                $hash .= '-' . $rountIdPartsArray[1];
            }
        }

        if (isset($data['currencyId']) && $data['currencyId'] != '') {
            $hash .= '-' . strtoupper($data['currencyId']);
        }

		return $hash;
    }

    /**
     * Generate unique session hash (id_session)
     * 
     * @return [string]
     */
    public function generateSessionHash()
    {
    	return md5(uniqid(rand(), true));
    }

}