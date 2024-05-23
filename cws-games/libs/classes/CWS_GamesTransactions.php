<?php


class CWS_GamesTransactions
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
        $this->TableName = $this->wpdb->prefix . 'cws_games_transactions';
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

        $fields['id'] = ($this->vars['id'] ? $this->vars['id'] : 0);
        
        if (isset($this->vars['token'])) {
            $fields['token'] = $this->vars['token'];
        }

        if (isset($this->vars['transaction_type'])) {
            $fields['transaction_type'] = $this->vars['transaction_type'];
        }

        if (isset($this->vars['type'])) {
            $fields['type'] = $this->vars['type'];
        }

        if (isset($this->vars['gameId'])) {
            $fields['gameId'] = $this->vars['gameId'];
        }

        if (isset($this->vars['transactionId'])) {
            $fields['transactionId'] = $this->vars['transactionId'];
        }

        if (isset($this->vars['id_session'])) {
            $fields['id_session'] = $this->vars['id_session'];
        }

        if (isset($this->vars['winAmount'])) {
            $fields['winAmount'] = $this->vars['winAmount'];
        }

        if (isset($this->vars['betAmount'])) {
            $fields['betAmount'] = $this->vars['betAmount'];
        }

        if (isset($this->vars['balance'])) {
            $fields['balance'] = $this->vars['balance'];
        }

        if (isset($this->vars['wallet_transaction_id'])) {
            $fields['wallet_transaction_id'] = $this->vars['wallet_transaction_id'];
        }

        if (isset($this->vars['user_id'])) {
            $fields['user_id'] = $this->vars['user_id'];
        }

        if (isset($this->vars['status'])) {
            $fields['status'] = $this->vars['status'];
        }

        if (isset($this->vars['status_txt'])) {
            $fields['status_txt'] = $this->vars['status_txt'];
        }

        if (isset($this->vars['request_json'])) {
            $fields['request_json'] = $this->vars['request_json'];
        }

        if (isset($this->vars['response_json'])) {
            $fields['response_json'] = $this->vars['response_json'];
        }

        $fields['updated_at'] = date("Y-m-d H:i:s", time());

        $fields['ip'] = getUserIP();
 
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

            if (strtolower($column) == 'orderby') {
                continue;
            }

            if (is_array($value)) {

                $where[] = $column . " " . $value['cond'] . " " . $value['value'];

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

        if ($debugSQL) {
            echo $sql . "<BR> \n";
        }

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
    public function getDetails($id)
    {  
        $item = $this->FindByParams(['id' => $id], 'single', true);
 
        return $item;
    }

}