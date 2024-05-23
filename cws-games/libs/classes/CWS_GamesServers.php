<?php


class CWS_GamesServers
{

	/**
	 * Get Server info by server_id (api_server_id)
	 * 
	 * @param [string] $server_id
	 * @return [void] $server
	 */ 
	public function getServerByServerId($server_id)
	{	
		$server = null;

		$options 		= get_option('cws_games_plugin');
		$serverTypes 	= CWS_GamesConfig::getServerTypes();

		if (!empty($serverTypes)) {
			foreach ($serverTypes as $server_type => $server_type_label) {
				$servers_data = $options['api_'.$server_type] ?? [];

				if ($servers_data && !empty($servers_data)) {
					foreach ($servers_data as $server_key => $server_data) {
						if (isset($server_data['api_server_id']) && $server_data['api_server_id'] == $server_id ) {
							$server = $server_data;
							break;
						}
					}
				}
			}
		}

		return $server;
	}

	/**
	 * Get Server info by server_id (api_server_id)
	 * 
	 * @param [array] $server_ids
	 * @return [array] $servers
	 */ 
	public function getServersByServerIds($server_ids = [])
	{
		$servers = [];

		$options 		= get_option('cws_games_plugin');
		$serverTypes 	= CWS_GamesConfig::getServerTypes();

		if (!empty($serverTypes)) {
			foreach ($serverTypes as $server_type => $server_type_label) {
				$servers_data = $options['api_'.$server_type] ?? [];

				if ($servers_data && !empty($servers_data)) {
					foreach ($servers_data as $server_key => $server_data) {
						if (isset($server_data['api_server_id']) && in_array($server_data['api_server_id'], $server_ids) ) {
							$servers[$server_data['api_server_id']] = $server_data;
						}
					}
				}
			}
		}

		return $servers;
	}
}