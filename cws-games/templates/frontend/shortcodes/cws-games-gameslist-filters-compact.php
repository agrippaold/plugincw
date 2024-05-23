<?php

global $wp;

?>

<div class="cws-gameslist-filters compact">
	<form action="<?= $shortcode_atts["action"] ?? home_url($wp->request) ?>" method="get">
		<div class="search-switcher">
			<a href="javascript:void(0)"><?= __('Game name', 'cws-games') ?></a>
			<div class="options">
				<a href="javascript:void(0)" data-option="game-name" class="selected"><?= __('Game name', 'cws-games') ?></a>
				<a href="javascript:void(0)" data-option="provider-name" class=""><?= __('Provider name', 'cws-games') ?></a>
			</div>
		</div>
		<div class="control-group game-name shown">
			<input type="text" name="formdata[game_name]" value="<?= $get['formdata']['game_name'] ?? '' ?>" placeholder="<?= __('Looking for something?', 'cws_games') ?>" />
		</div>
		<div class="control-group provider-name">
			<input type="text" name="formdata[provider_name]" value="<?= $get['formdata']['provider_name'] ?? '' ?>" placeholder="<?= __('Looking for something?', 'cws_games') ?>" />
		</div>
		<div class="control-group actions">
			<button type="submit"><img src="<?= CWS_GAMES_ABSPATH_ASSETS . '/images/search-btn.png' ?>" /></button>
		</div>
	</form>
</div>