<?php
	$url = admin_url('admin.php');
?>

<style>
	table thead th#name_game {
		width: 10%;
	}
	table thead th#details  {
		width: 40%;
	}
	.row-actions {
		left: initial;
	}
	.cws-games-wrapper {
		position: relative;
		min-height: 600px;
	}
	.cws-games-wrapper .overlay-wrapper {
		display: none;
		position: absolute;
		top: 0;
		left: 0;
		width: 100%;
		height: 100%;
		background-color: rgba(255, 255, 255, 0.6);
	}
	.cws-games-wrapper .overlay-wrapper img {
		position: absolute;
		top: 50%;
		left: 50%;
		transform: translate(-50%, -50%);
	}
	.green {
		color: green;
	}
	.red {
		color: red;
	}
</style>

<ul class="nav-tab-wrapper">
	<a href="<?= $url . '?page=cws-games'?>" class="nav-tab">Home</a>
	<a href="<?= $url . '?page=cws-games-general-settings' ?>" class="nav-tab">General Settings</a>
	<a href="<?= $url . '?page=cws-games-settings' ?>" class="nav-tab">API Settings</a>
	<a href="<?= $url . '?page=cws-games-gameslist' ?>" class="nav-tab nav-tab-active">Games</a>
</ul>

<div class="cws-games-wrapper">

	<h2>CWS Games - Games</h2>
	<hr>
	<?php 
$options = get_option('cws_games_plugin');
$servers = array_merge($options['api_free'] ?? [], $options['api_paid'] ?? []);
  
foreach ($servers as $server) { 
 if ($server['api_server_status'] === 'active')
    $option_all_servers .= '<option value="' . $server['api_server_type'] . '" data-serverid="'. $server['api_server_id']  .'">' . $server['api_server_alias'] . ' (' . $server['api_server_type']  . ')' . '</option>';
  
}
?>

	<div class="gameslist-importer">
		<button type="button" id="gameslist-import" class="button btn btn-primary">Import games</button>
		
		<div class="wp-message" style="margin-left: 0;"></div>
	</div>

	<div class="overlay-wrapper">
		<img src="<?= CWS_GAMES_ABSPATH_ASSETS . '/images/double-ring-loader.gif' ?>" alt="Loader" />
	</div>


	<?php

	   $CWS_GamesGameslist_Table = new CWS_GamesGameslist_Table;
	   echo '<div class="wrap">';
	   $CWS_GamesGameslist_Table->prepare_rows($games);
	   $CWS_GamesGameslist_Table->prepare_items();
	   $CWS_GamesGameslist_Table->display();
	   echo '</div>';

	?>

	<!-- <script>
const source = document.getElementById('importSource');
const freeServers = document.getElementById('freeServers');
const paidServers = document.getElementById('paidServers');

source.addEventListener('change', function(){
	if(source.value == 'free'){
		freeServers.style = 'display:inline-block';
		paidServers.style = 'display:none';
	}if(source.value == 'paid'){
		paidServers.style = 'display:inline-block';
		freeServers.style = 'display:none';
	}if(source.value == 'default'){
		freeServers.style = 'display:none';
		paidServers.style = 'display:none';
	}
})

	</script> -->
</div>