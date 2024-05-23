<?php

global $wp;

?>

<div class="cws-gameslist-filters">
	<form action="<?= home_url($wp->request) ?>" method="get">
		<div class="control-group">
			<input type="text" name="formdata[game_name]" value="<?= $get['formdata']['game_name'] ?? '' ?>" placeholder="<?= __('Game Name', 'cws_games') ?>" />
		</div>
		<div class="control-group">
			<input type="text" name="formdata[provider_name]" value="<?= $get['formdata']['provider_name'] ?? '' ?>" placeholder="<?= __('Provider Name', 'cws_games') ?>" />
		</div>
		<div class="control-group">
			<input type="text" name="formdata[game_theme]" value="<?= $get['formdata']['game_theme'] ?? '' ?>" placeholder="<?= __('Game Theme', 'cws_games') ?>" />
		</div>
		<div class="control-group actions">
			<button type="submit"><i class="fa fa-search"></i></button>
		</div>

		<input type="hidden" name="type" value="<?= $_GET['type'] ?? '' ?>" />
	</form>
</div>