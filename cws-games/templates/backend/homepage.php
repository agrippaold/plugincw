<?php
	$url = admin_url('admin.php');
?>

<style>
	.homepage-wrapper {
		margin: 40px 0;
		text-align: center;
	}
	.homepage-wrapper h2 {
		margin-bottom: 40px;
	}
</style>

<ul class="nav-tab-wrapper">
	<a href="<?= $url . '?page=cws-games' ?>" class="nav-tab nav-tab-active">Home</a>
	<a href="<?= $url . '?page=cws-games-general-settings' ?>" class="nav-tab">General Settings</a>
	<a href="<?= $url . '?page=cws-games-settings' ?>" class="nav-tab">API Settings</a>
	<a href="<?= $url . '?page=cws-games-gameslist' ?>" class="nav-tab">Games</a>
</ul>

<div class="homepage-wrapper">
	<h2>CWS Games Plugin</h2>
	<img src="<?= CWS_GAMES_ABSPATH_ASSETS . '/images/homepage_banner.jpg' ?>" alt="Homepage Banner" style="width:650px;" />
</div>