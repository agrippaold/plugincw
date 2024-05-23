<?php

/**
 * Site Debug , log function
 *
 * @param [type] $url
 * @param [type] $data
 * @return void
 */
function __sd_backoffice($data, $text = '')
{ 
    if (!file_exists(ABSPATH . "/tmp")) {
      mkdir(ABSPATH . "/tmp", 0777, true);
    }

    file_put_contents(ABSPATH . "/tmp/backoffice.txt" , "\n ======== ".
                          date("Y-m-d H:i:s", time() ) . " ======== " . getUserIP() . " ======== " . $text .
                          "\n data: ". print_r( $data ,1 ), FILE_APPEND );
}

add_action( 'rest_api_init', function () {

  register_rest_route( 'cws_games', 'backofficeAPI', array(
    'methods'   => 'POST',
    'callback'  => 'restAPIbackofficeAPI'
  ));

});


function cws_backoffice_endpoint_rewrite_rule()
{
    add_rewrite_rule('^backofficeAPI/?$', 'index.php?custom_endpoint=backofficeAPI', 'top');
}

add_action('init', 'cws_backoffice_endpoint_rewrite_rule');


function cws_backoffice_capture_post_request()
{	
    if (get_query_var('custom_endpoint') == 'backofficeAPI') {        
        $request = new WP_REST_Request;
        restAPIbackofficeAPI($request);
        die(); 
    }
}

add_action('template_redirect', 'cws_backoffice_capture_post_request');

function restAPIbackofficeAPI(WP_REST_Request $request)
{

	$data = [];

	if (isset($_SERVER['CONTENT_TYPE'])) {
	    $contentType = $_SERVER['CONTENT_TYPE'];
	        // __sd($_SERVER, 'takeAjaxData  _SERVER');  
	    if (strpos($contentType, 'application/json') !== false) {
	        // Handle JSON data 
	            $data = json_decode( file_get_contents("php://input") , true ); 
	    } elseif (strpos($contentType, 'multipart/form-data') !== false) {
	        // Handle form data 
	        $data = $_POST; 
	    } else {
	        // Handle other content types as necessary
	    }
	}

	__sd_backoffice($data, 'DATA API REQUEST');

	if ( !isset($data['token']) || $data['token'] == '' || $data['token'] != md5( home_url('/') ) ) {

		$result = array(
			'status' 		=> -403,
			'status_txt' 	=> 'Invalid token.'
		);

		__sd_backoffice($result, 'RESULT API RESPONSE');

		status_header(403);

		wp_send_json($result);
		die();

	} else {

		$CWS_GamesBackOfficeAPI = new CWS_GamesBackOfficeAPI();

		if ( !isset($data['action']) || $data['action'] == '' || !method_exists($CWS_GamesBackOfficeAPI, $data['action']) ) {

			$result = array(
				'status' 		=> -405,
				'status_txt' 	=> 'Action not allowed.'
			);

			__sd_backoffice($result, 'RESULT API RESPONSE');

			status_header(405);

			wp_send_json($result);
			die();

		} else {

			$method = $data['action'];

			$CWS_GamesBackOfficeAPI->SetVars($data);

			$result = $CWS_GamesBackOfficeAPI->$method();

			if (is_array($result) && isset($result['status']) && $result['status'] < 0) {

				status_header(400);

			}

			__sd_backoffice($result, 'RESULT API RESPONSE');

			wp_send_json($result);
			die();
		}
	}
}
