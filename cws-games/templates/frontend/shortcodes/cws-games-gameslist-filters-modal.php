<?php

global $wp;

?>

<div id="searchModal" class="iziModal" data-izimodal-title="">
	<div class="modal-content">
		<div class="cws-gameslist-filters compact" id="CWSGamesFilter">
			<form action="<?= $shortcode_atts["action"] ?? "/starsplay/search/" ?>" method="get">
				<div class="absolute-icon">
					<?php
						$svg = file_get_contents(CWS_GAMES_ASSETS . '/images/filter.svg');
						echo $svg;
					?>
				</div>
				<div class="control-group game-name">
					<input type="text" name="formdata[game_name]" value="" placeholder="<?= __('Looking for something?', 'cws_games') ?>" />
					<img class="loader" src="<?= CWS_GAMES_ABSPATH_ASSETS . '/images/double-ring-loader.gif' ?>" alt="currency" />
				</div>
				<div class="control-group actions">
					<button type="submit">
						<?php
							$svg = file_get_contents(CWS_GAMES_ASSETS . '/images/SEARCH_BUTTON.svg');
							echo $svg;
						?>
					</button>
				</div>
			</form>
		</div>
	</div>
</div>