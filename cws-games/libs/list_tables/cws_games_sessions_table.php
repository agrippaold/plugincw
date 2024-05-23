<?php
/**
 * List of API Calls
 *
 */

if(!class_exists('WP_List_Table')){
    require_once( ABSPATH . 'wp-admin/includes/screen.php' );
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class CWS_GamesSessions_Table extends WP_List_Table
{
    public $rows        = [];
    public $columns     = [];
    private $games      = [];
    private $servers    = [];
    private $currencies = [];

    /** Class constructor */
    public function __construct()
    {
        parent::__construct([
            'singular' => __('Session', 'cws_games'),  // singular name of the listed records
            'plural'   => __('Sessions', 'cws_games'), // plural name of the listed records
            'ajax'     => false                 // should this table support ajax?
        ]);

        echo '<style type="text/css">';
        echo '.wp-list-table .column-title { width: 350px; }';
        echo '.wp-list-table .column-status { width: 120px; }';
        echo '.wp-list-table .column-id { width: 50px; }';
        echo '</style>';
    }

    /**
     * Prepare rows Data
     *
     * @param [type] $params
     * @return void
     */
    public function prepare_rows($rows)
    {
        $this->rows = [];

        if ($rows) {
            foreach ($rows as $row) {

                $ggr = 0;

                $ggr += $row->totIn ?? 0;

                $ggr -= $row->totOut ?? 0;

                $this->rows[] = [
                    'id'            => $row->id ?? '',
                    // 'id_session'    => $this->column_title($row),
                    'id_session'    => '<a href="'.add_query_arg( array('page' => 'cws-games-session-details', 'id_session' => $row->id_session ?? ''), admin_url('admin.php') ).'">'.$row->id_session ?? ''.'</a>',
                    'gameId'        => $row->gameId ?? '',
                    'gameName'      => $row->gameName ?? '',
                    'providerName'  => $row->providerName ?? '',
                    'currency'      => $row->currency ?? '',
                    'totSpins'      => $row->totSpins ?? '',
                    'totIn'         => $row->totIn ?? '',
                    'totOut'        => $row->totOut ?? '',
                    'ggr'           => $ggr,
                    'percentage'    => '--',
                    'user'          => $row->userFullName ?? '',
                    'date_creation' => wp_date( get_option('date_format'), strtotime($row->date_creation ?? ''), get_option('timezone_string') )
                ];
     
            }
        }

        $this->columns = [
            // 'cb'                => '<input type="checkbox" />',
            'id'                => __('ID', 'cws_games'),
            'id_session'        => __('ID Session', 'cws_games'),
            'gameId'            => __('ID Game', 'cws_games'),
            'gameName'          => __('Name', 'cws_games'),
            'providerName'      => __('Casino', 'cws_games'),
            'currency'          => __('Currency', 'cws_games'),
            'totSpins'          => __('Tot Spin', 'cws_games'),
            'totIn'             => __('Tot In', 'cws_games'),
            'totOut'            => __('Tot Out', 'cws_games'),
            'ggr'               => __('GGR', 'cws_games'),
            'percentage'        => __('%', 'cws_games'),
            'user'              => __('User', 'cws_games'),
            'date_creation'     => __('Date', 'cws_games')
        ];

    }

    public function column_title($item)
    {
        $actions = array(
            'edit'   => sprintf('<a href="?page=cws-games-session-details&id_session=%s">Details</a>', $item->id_session ?? ''),
        );

        $title = $item->id_session ?? '';

        return sprintf('%1$s %2$s', $title, $this->row_actions($actions));
    }

    /**
     * Set Table Rows
     *
     * @return void
     */
    public function record_count()
    {
        return count($this->rows);
    }

    /** Text displayed when no customer data is available */
    public function no_items()
    {
        _e('No sessions found.', 'cws_games');
    }

    /**
     * Render a column when no column specific method exists.
     *
     * @param array $item
     * @param string $column_name
     *
     * @return mixed
     */
    public function column_default($item, $column_name)
    {

        return $item[$column_name];
    }

    /**
     * Render the bulk edit checkbox
     *
     * @param array $item
     *
     * @return string
     */
    public function column_cb($item)
    {   
        return sprintf(
            '<input type="checkbox" name="bulk-delete[]" value="%s" />', $item['id']
        );
    }

    /**
     *  Associative array of columns
     *
     * @return array
     */
    public function get_columns()
    {
        return $this->columns;
    }

    /**
     * Columns to make sortable.
     *
     * @return array
     */
    public function get_sortable_columns()
    {
        $sortable_columns = array(
        );

        return $sortable_columns;
    }

    /**
     * Returns an associative array containing the bulk action
     *
     * @return array
     */
    public function get_bulk_actions()
    {
        $actions = [
            // 'bulk-delete' => 'Delete'
        ];

        return $actions;
    }

    /**
     * Handles data query and filter, sorting, and pagination.
     */
    public function prepare_items()
    {
        $columns  = $this->get_columns();
        $hidden   = array();
        $sortable = array();

        $per_page     = 10;
        $current_page = $this->get_pagenum();
        $total_items  = count($this->rows);

        $found_data            = array_slice($this->rows, (($current_page - 1) * $per_page), $per_page);
        $this->_column_headers = array($columns, $hidden, $sortable);
        $this->items           = $found_data;

        //paging
        $this->set_pagination_args(array(
            'total_items' => $total_items, //WE have to calculate the total number of items
            'per_page'    => $per_page    //WE have to determine how many items to show on a page
        ));

    }

    public function extra_tablenav($which)
    {   
        switch ($which) {
            case 'top':
                ?>
                <div class="alignleft actions">
                    <label for="cws_games_sessions_date_from"><?= __('Date From', 'cws_games'); ?></label>
                    <input id="cws_games_sessions_date_from" type="date" name="formdata[date_from]" value="<?= $_POST['formdata']['date_from'] ?? '' ?>" />

                    <label for="cws_games_sessions_date_to"><?= __('Date To', 'cws_games'); ?></label>
                    <input id="cws_games_sessions_date_to" type="date" name="formdata[date_to]" value="<?= $_POST['formdata']['date_to'] ?? '' ?>" />

                    <label for="cws_games_sessions_type"><?= __('Type', 'cws_games'); ?></label>
                    <select id="cws_games_sessions_type" name="formdata[cws_games_sessions_type]" style="float: none;">
                        <option value="-1"><?= __('Select Type', 'cws_games'); ?></option>
                    </select>

                    <label for="cws_games_sessions_game"><?= __('Game', 'cws_games'); ?></label>
                    <select id="cws_games_sessions_game" name="formdata[gameId]" style="float: none;">
                        <option value="-1"><?= __('Select Game', 'cws_games'); ?></option>
                        <?php if (!empty($this->games)): ?>
                            <?php foreach ($this->games as $game): ?>
                                <option value="<?= $game->guid ?? '' ?>" <?= (isset($_POST['formdata']['gameId']) && $_POST['formdata']['gameId'] == $game->guid) ? 'selected="selected"' : '' ?>><?= $game->name_game ?? '' ?></option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>

                    <label for="cws_games_sessions_casino"><?= __('Casino', 'cws_games'); ?></label>
                    <select id="cws_games_sessions_casino" name="formdata[providerId]" style="float: none;">
                        <option value="-1"><?= __('Select Casino', 'cws_games'); ?></option>
                        <?php if (!empty($this->servers)): ?>
                            <?php foreach ($this->servers as $server): ?>
                                <option value="<?= $server['api_server_id'] ?? '' ?>" <?= (isset($_POST['formdata']['providerId']) && $_POST['formdata']['providerId'] == $server['api_server_id']) ? 'selected="selected"' : '' ?>><?= $server['api_server_alias'] ?? '' ?></option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>

                    <label for="cws_games_sessions_currency"><?= __('Currency', 'cws_games'); ?></label>
                    <select id="cws_games_sessions_currency" name="formdata[currency]" style="float: none;">
                        <option value="-1"><?= __('Select Currency', 'cws_games'); ?></option>
                        <?php if (!empty($this->currencies)): ?>
                            <?php foreach ($this->currencies as $currency): ?>
                                <option value="<?= $currency['currency_code'] ?? '' ?>" <?= (isset($_POST['formdata']['currency']) && $_POST['formdata']['currency'] == $currency['currency_code']) ? 'selected="selected"' : '' ?>><?= $currency['currency_symbol'] ?? '' ?></option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>

                    <input type="submit" name="filter_action" id="post-query-submit" class="button" value="<?= __('Filter', 'cws_games') ?>">

                    <!-- <input type="hidden" name="action" value="cws_games_sessions_export_csv" /> -->

                    <button type="button" id="export_csv" class="button"><?= __('Export CSV', 'cws_games') ?></button>
                </div>
                <?php

                break;
        }
    }

    /**
     * Set private variable $games
     * 
     * @param [array] $games
     */ 
    public function setGamesList($games)
    {
        $this->games = $games;
    }

    /**
     * Set private variable $servers
     * 
     * @param [array] $servers
     */ 
    public function setServers($servers)
    {
        $this->servers = $servers;
    }

    /**
     * Set private variable $currencies
     * 
     * @param [array] $currencies
     */ 
    public function setCurrencies($currencies) {
        $this->currencies = $currencies;
    }

}
