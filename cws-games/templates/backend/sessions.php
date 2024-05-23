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
	<a href="<?= $url . '?page=cws-games-gameslist' ?>" class="nav-tab">Games</a>
	<a href="<?= $url . '?page=cws-games-sessions' ?>" class="nav-tab nav-tab-active">Sessions</a>
	<a href="<?= $url . '?page=cws-games-providers' ?>" class="nav-tab">Providers</a>
	<a href="<?= $url . '?page=cws-games-login-rewards' ?>" class="nav-tab">Login Rewards</a>
</ul>

<div class="cws-games-wrapper">

	<h2>CWS Games - Sessions</h2>
	<hr>

	<a href="javascript:void(0)" id="deleteCsvFiles" class="button button-primary">Delete CSV files</a>

	<div class="overlay-wrapper">
		<img src="<?= CWS_GAMES_ABSPATH_ASSETS . '/images/double-ring-loader.gif' ?>" alt="Loader" />
	</div>

	<form method="post">
		
		<?php

		   $CWS_GamesSessions_Table = new CWS_GamesSessions_Table;
		   echo '<div class="wrap">';
		   $CWS_GamesSessions_Table->setGamesList($games);
		   $CWS_GamesSessions_Table->setServers($servers);
		   $CWS_GamesSessions_Table->setCurrencies($currencies);
		   $CWS_GamesSessions_Table->prepare_rows($sessions);
		   $CWS_GamesSessions_Table->prepare_items();
		   $CWS_GamesSessions_Table->display();
		   echo '</div>';

		?>

	</form>

</div>