<?php
	$url = admin_url('admin.php');
?>

<style>
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
	<a href="<?= $url . '?page=cws-games-login-rewards' ?>" class="nav-tab nav-tab-active">Login Rewards</a>
</ul>

<div class="cws-games-wrapper">

	<h2>CWS Games - Login Rewards</h2>

	<hr>

	<form action="options.php" method="post">

	    <?php 
	    settings_fields( 'cws_games_plugin_login_rewards' );
	    do_settings_sections( 'cws_games_plugin_login_rewards' );
	    ?>
	    
	    <input name="submit" class="button button-primary" type="submit" value="<?php esc_attr_e( 'Save Settings' ); ?>" />
	</form>


	<div class="overlay-wrapper">
		<img src="<?= CWS_GAMES_ABSPATH_ASSETS . '/images/double-ring-loader.gif' ?>" alt="Loader" />
	</div>

</div>