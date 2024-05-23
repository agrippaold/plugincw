<?php


class CWS_GamesGameslist
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
        $this->TableName = $this->wpdb->prefix . 'cws_games_gameslist';
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

        $fields['id'] = (isset($this->vars['id']) ? $this->vars['id'] : 0);
        
        if (isset($this->vars['guid'])) {
            $fields['guid'] = $this->vars['guid'];
        }

        if (isset($this->vars['game_name'])) {
            $fields['game_name'] = $this->vars['game_name'];
        }

        if (isset($this->vars['urlButton'])) {
            $fields['urlButton'] = $this->vars['urlButton'];
        }

        if (isset($this->vars['game_server'])) {
            $fields['server_id'] = $this->vars['game_server'];
        }

        if (isset($this->vars['details'])) {
            $fields['details'] = $this->vars['details'];
        }

        if (isset($this->vars['game_server_name'])) {
            $fields['game_server_name'] = $this->vars['game_server_name'];
        }

        if (isset($this->vars['game_lanch_url'])) {
            $fields['game_lanch_url'] = $this->vars['game_lanch_url'];
        }

        if (isset($this->vars['server']) && $this->vars['server'] != '') {
            $fields['server'] = $this->vars['server'];
        }

        if (isset($this->vars['paid']) && $this->vars['paid'] != '') {
            $fields['source'] = $this->vars['paid'];
        }

        $fields['json'] = json_encode($this->vars, JSON_UNESCAPED_UNICODE);

        if (isset($this->vars['game_paid_type'])) {
            $fields['source'] = $this->vars['game_paid_type'];
        }

        $fields['updated_at'] = date("Y-m-d H:i:s", time());
 
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
        $result = [];

        $fields = $this->setValues();

        $result = $this->validate($fields);
        if ($result['status'] > 0) { 
            $result = $this->store($fields);
        } 

        if (isset($this->vars['id']) && $this->vars['id'] == 0 && $result['status'] > 0) {
            
            $result['status_txt'] = 'Row successfully stored';

        } else if (isset($this->vars['id']) && $this->vars['id'] > 0 && $result['status'] > 0) {
            
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
        $post_id = 0;

        $params = [
            'post_status'   => array('any', 'trash'),
            'meta_query'    => array(
                'relation'  => 'AND'
            )
        ];

        if (isset($fields['guid']) && $fields['guid'] != '') {
            $params['meta_query'][] = array(
                'key'       => 'game_guid',
                'value'     => $fields['guid'],
                'compare'   => '='
            );
        }

        if (isset($fields['server']) && $fields['server'] != '') {
            $params['meta_query'][] = array(
                'key'       => 'server_id',
                'value'     => $fields['server'],
                'compare'   => '='
            );
        }

        $game = $this->FindByParams($params, 'single');

        if ($game) {
            $post_id = $game->id;
        }

        if ($post_id > 0) {

            $post_fields = [
                'ID'            => $post_id,
                'post_title'    => isset($fields['game_name']) ? $fields['game_name'] : 'Game ' . rand(1000, 9000),
                'post_content'  => isset($fields['details']) ? $fields['details'] : '',
                'post_status'   => 'publish',
            ];

            $post_id = wp_update_post( $post_fields, false );

        } else {
            $post_fields = [
                'post_type'     => 'game',
                'post_title'    => isset($fields['game_name']) ? $fields['game_name'] : 'Game ' . rand(1000, 9000),
                'post_content'  => isset($fields['details']) ? $fields['details'] : '',
                'post_status'   => 'publish',
                'post_author'   => get_current_user_id()
            ];

            $post_id = wp_insert_post( $post_fields, false );
        }

        if ($post_id) {
            if (isset($fields['guid'])) {
                update_post_meta($post_id, 'game_guid', $fields['guid']);
            }

            if (isset($fields['urlButton']) && $fields['urlButton'] != 'undefined') {
                update_post_meta($post_id, 'url_button', $fields['urlButton']);
            }

            if (isset($fields['source'])) {
                update_post_meta($post_id, 'source', $fields['source']);
            }

            if (isset($fields['server'])) {
                update_post_meta($post_id, 'server_id', $fields['server']);
            }

            if (isset($fields['game_server_name'])) {
                update_post_meta($post_id, 'game_server_name', $fields['game_server_name']);
            }

            if (isset($fields['game_lanch_url'])) {
                update_post_meta($post_id, 'game_lanch_url', $fields['game_lanch_url']);
            }

            if (isset($fields['details'])) {
                update_post_meta($post_id, 'details', $fields['details']);
            }

            if (isset($fields['json'])) {
                update_post_meta($post_id, 'game_json', $fields['json']);
            }
            
            $result['status']       = $post_id;
            $result['status_txt']   = 'Row successfully saved';

        } else {

            $result['status']       = -11;
            $result['status_txt']   = 'Error';

        }

        // echo '<pre>'.print_r($fields, true).'</pre>';
        // echo '<pre>'.print_r($post_id, true).'</pre>';

        return $result;
    }

    /**
     * Validate data before save
     *
     * @param [type] $data
     * @return void
     */
    public function validate($fields)
    {
        $result = ['status' => 1, 'status_txt' => 'OK'];

        return $result;
    }

    /**
     *  Filter Function By array of Params
     *
     * @param [type] $params
     * @param string $single_resutl

     */
    public function FindByParams($params, $single_result = 'multiple', $debugSQL = false)
    { 

        $results = null;

        $default_args = [
            'post_type'         => 'game',
            'post_status'       => 'publish',
            'posts_per_page'    => -1
        ];

        $args = array_merge($default_args, $params); 

        $query = new WP_Query($args); 

        if ($query->have_posts()) {
            if ($single_result == 'single') {
                $post = $query->posts[key($query->posts)];

                $guid          = get_post_meta($post->ID, 'game_guid', true);
                $url_button    = get_post_meta($post->ID, 'url_button', true);
                $server_id     = get_post_meta($post->ID, 'server_id', true);
                $game_server_name   = get_post_meta($post->ID, 'game_server_name', true);
                $game_lanch_url     = get_post_meta($post->ID, 'game_lanch_url', true);
                $source        = get_post_meta($post->ID, 'source', true);
                $json          = get_post_meta($post->ID, 'game_json', true);

                $results = (object)[
                    'id'            => $post->ID,
                    'guid'          => $guid,
                    'name_game'     => $post->post_title,
                    'urlButton'     => $url_button,
                    'server_id'     => $server_id,
                    'game_server_name '  => $game_server_name,
                    'game_lanch_url'     => $game_lanch_url,
                    'details'       => $post->post_content,
                    'json'          => $json,
                    'source'        => $source
                ];
                // __sd($results , "Results- Import new game from Backoffice");
            } else {

                foreach ($query->posts as $post) {
                    $guid           = get_post_meta($post->ID, 'game_guid', true);
                    $url_button     = get_post_meta($post->ID, 'url_button', true);
                    $server_id      = get_post_meta($post->ID, 'server_id', true);
                    $game_server_name    = get_post_meta($post->ID, 'game_server_name', true);
                    $game_lanch_url      = get_post_meta($post->ID, 'game_lanch_url', true);
                    $source         = get_post_meta($post->ID, 'source', true);
                    $json           = get_post_meta($post->ID, 'game_json', true);

                    $results[] = (object)[
                        'id'            => $post->ID,
                        'guid'          => $guid,
                        'name_game'     => $post->post_title,
                        'urlButton'     => $url_button,
                        'server_id'     => $server_id,
                        'game_server_name '  => $game_server_name,
                        'game_lanch_url'     => $game_lanch_url,
                        'details'       => $post->post_content,
                        'json'          => $json,
                        'source'        => $source
                    ];
                }
            }
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

    /**
     * Delete Row
     *
     * @param [type] $data
     * @return void
     */
    public function delete($id)
    {
        $this->wpdb->delete($this->TableName, ['id' => $id]);

        return ['status' => 1, 'status_txt' => 'Row successfully deleted'];
    }

    /**
     * Empty table
     *
     * @param [type] $data
     * @return void
     */
    public function empty($source = '')
    {   

        $delete_args = [
            'post_type'         => 'game',
            'post_status'       => array('any', 'trash'),
            'posts_per_page'    => -1
        ];

        if ($source != '') {
            $delete_args['meta_query'] = [
                'relation' => 'OR',
                [
                    'key'       => 'source',
                    'value'     => $source,
                    'compare'   => '='
                ],
                [
                    'key'       => 'source',
                    'compare'   => 'NOT EXISTS'
                ]
            ];
        }

        $query = new WP_Query($delete_args);

        if ($query->have_posts()) {
            foreach ($query->posts as $post) {
                wp_delete_post($post->ID);
            }
        }

        return ['status' => 1, 'status_txt' => 'Games successfully deleted'];
    }

    public function trashOldGames($importedGames)
    {
        // $params = array(
        //     'post_status'   => 'any',
        //     'meta_query'    => array(
        //         'relation'  => 'AND',
        //         array(
        //             'key'       => 'game_guid',
        //             'value'     => $importedGames,
        //             'compare'   => 'NOT IN'
        //         )
        //     )
        // );

        // $games = $this->FindByParams($params, 'multiple');

        // if (!empty($games)) {
        //     foreach ($games as $game) {
        //         $post_fields = [
        //             'ID'            => $game->id,
        //             'post_status'   => 'draft'
        //         ];

        //         wp_update_post( $post_fields, false );
        //     }
            
        // }

        $params = array(
            'post_status'       => 'any',
            'posts_per_page'    => -1,
        );

        $games = $this->FindByParams($params, 'multiple');

        if (!empty($games)) {
            foreach ($games as $game) {
                $post_fields = [
                    'ID'            => $game->id,
                    'post_status'   => 'draft'
                ];

                wp_update_post( $post_fields, false );
            }
        }
    }

    public function getGameCategory($game_id)
    {
        $game_category = [];

        $terms = get_the_terms($game_id, 'game_category');

        if (!empty($terms)) {
            foreach ($terms as $term) {
                $game_category[] = $term->term_id;
            }
        }

        return $game_category;
    }
}