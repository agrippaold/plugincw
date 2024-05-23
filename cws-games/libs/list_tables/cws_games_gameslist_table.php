<?php
/**
 * List of API Calls
 *
 */

if(!class_exists('WP_List_Table')){
    require_once( ABSPATH . 'wp-admin/includes/screen.php' );
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class CWS_GamesGameslist_Table extends WP_List_Table
{
    public $rows    = [];
    public $columns = [];

    /** Class constructor */
    public function __construct()
    {
        parent::__construct([
            'singular' => __('Game', 'cws_games'),  // singular name of the listed records
            'plural'   => __('Games', 'cws_games'), // plural name of the listed records
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

                // $json = json_encode(json_decode($row->json), JSON_PRETTY_PRINT);
                // echo "<pre>" . print_r($row) . "</pre>";

                $this->rows[] = [
                    'name_game'             => $row->name_game,
                    'source'                => $row->source,
                    'guid'                  => $row->guid,
                    'urlButton'             => $row->urlButton,
                    "server_id"             => $row->server_id,
                    "game_server_name"      => $row->game_server_name,
                    "game_lanch_url"        => $row->game_lanch_url,
                    'details'               => $row->details
                ]; 
            }
        }

        $this->columns = [
            // 'cb'                     => '<input type="checkbox" />',
            'name_game'                 => __('Game Name', 'cws_games'),
            'source'                    => __('Source', 'cws_games'),
            'guid'                      => __('Guid', 'cws_games'),
            'urlButton'                 => __('Url Button', 'cws_games'),
            'server_id'                 => __('Server Id', 'cws_games'),
            'game_server_name'               => __('Game Server Name', 'cws_games'),
            'game_lanch_url'                 => __('Game Lanch Url', 'cws_games'),
            'details'                   => __('Details', 'cws_games')
        ];

    }

    public function column_title($item)
    {
        $actions = array(
            
        );

        $title = $item['name_game'];

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
        _e('No games found.', 'cws_games');
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

}
