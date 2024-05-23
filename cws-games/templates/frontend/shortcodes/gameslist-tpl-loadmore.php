<?php if (isset($shortcode_atts['title']) && $shortcode_atts['title'] != ''): ?>
	<div class="section-header">
		<h2><?= $shortcode_atts['title'] ?></h2>
	</div>
<?php endif; ?>

<?php
	
$secondary 	= 8;
$leading 	= 3;

__sd($load_more_params, 'LOAD MORE PARAMS');

?>

<?php if (isset($shortcode_atts['loadmore_count']) && $shortcode_atts['loadmore_count'] != '' && intval($shortcode_atts['loadmore_count']) > 11) : ?>

	<?php if (!empty($games)): ?>
		<?php $game_chunks = array_chunk($games, 11) ?>
		<?php foreach ($game_chunks as $chunk_key => $chunk): ?>
			<?php $chunk_8 = array_slice($chunk, 0, 8) ?>
			<?php $chunk_3 = array_slice($chunk, 8, 3) ?>
			<div class="row g-4 cws-games-row template-4 loadmore">
				<?php foreach ($chunk_8 as $key => $game): ?>
					<div class="col-lg-6 col-12 matchlistitem template-4">
						<div class="game__item item-layer">
							<div class="game__inner text-center p-0">
								<div class="game__thumb mb-0">
									<img src="<?= str_replace('https//', 'https://', $game->urlButton) ?>" alt="<?= $game->name_game ?>" class="rounded-3 w-100" />
								</div>

								<div class="game__overlay">
									<div class="game__overlay-left">
										<h4><?= $game->name_game ?></h4>
									</div>

									<div class="game__overlay-right">
										<?php if (isset($gametype) && $gametype == 'free'): ?>
											<a href="javascript:void(0)" class="default-button game-launch" data-guid="<?= $game->id ?>"><span>play now <i class="icofont-circled-right"></i></span> </a>
										<?php else: ?>
											<?php if (is_user_logged_in()): ?>
												<a href="javascript:void(0)" class="default-button game-launch" data-guid="<?= $game->id ?>"><span>play now <i class="icofont-circled-right"></i></span> </a>
											<?php else: ?>
												<a href="<?= $sign_in_url ?>" class="default-button"><span>Login to play <i class="icofont-circled-right"></i></span> </a>
											<?php endif; ?>
										<?php endif; ?>
									</div>

									<?php
										$svg = file_get_contents(CWS_GAMES_ASSETS . '/images/star.svg');
										$svg2 = file_get_contents(CWS_GAMES_ASSETS . '/images/gradient.svg');
									?>

									<?php if (is_user_logged_in()): ?>
										<?php if (isset($userGames) && is_array($userGames) && in_array($game->guid, $userGames)): ?>
											<a href="javascript:void(0)" class="removeFromMyGames" data-guid="<?= $game->guid ?>"><?= $svg ?><?= $svg2 ?></a>
										<?php else: ?>
											<a href="javascript:void(0)" class="addToMyGames" data-guid="<?= $game->guid ?>"><?= $svg ?><?= $svg2 ?></a>
										<?php endif; ?>
									<?php endif; ?>
								</div>
							</div>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
			<div class="row g-4 cws-games-row template-3 loadmore">
				<?php foreach ($chunk_3 as $key => $game): ?>
					<div class="col-lg-6 col-12 matchlistitem template-3">
						<div class="game__item item-layer">
							<div class="game__inner text-center p-0">
								<div class="game__thumb mb-0">
									<img src="<?= str_replace('https//', 'https://', $game->urlButton) ?>" alt="<?= $game->name_game ?>" class="rounded-3 w-100" />
								</div>

								<div class="game__overlay">
									<div class="game__overlay-left">
										<h4><?= $game->name_game ?></h4>
									</div>

									<div class="game__overlay-right">
										<?php if (isset($gametype) && $gametype == 'free'): ?>
											<a href="javascript:void(0)" class="default-button game-launch" data-guid="<?= $game->id ?>"><span>play now <i class="icofont-circled-right"></i></span> </a>
										<?php else: ?>
											<?php if (is_user_logged_in()): ?>
												<a href="javascript:void(0)" class="default-button game-launch" data-guid="<?= $game->id ?>"><span>play now <i class="icofont-circled-right"></i></span> </a>
											<?php else: ?>
												<a href="<?= $sign_in_url ?>" class="default-button"><span>Login to play <i class="icofont-circled-right"></i></span> </a>
											<?php endif; ?>
										<?php endif; ?>
									</div>

									<?php
										$svg = file_get_contents(CWS_GAMES_ASSETS . '/images/star.svg');
										$svg2 = file_get_contents(CWS_GAMES_ASSETS . '/images/gradient.svg');
									?>

									<?php if (is_user_logged_in()): ?>
										<?php if (isset($userGames) && is_array($userGames) && in_array($game->guid, $userGames)): ?>
											<a href="javascript:void(0)" class="removeFromMyGames" data-guid="<?= $game->guid ?>"><?= $svg ?><?= $svg2 ?></a>
										<?php else: ?>
											<a href="javascript:void(0)" class="addToMyGames" data-guid="<?= $game->guid ?>"><?= $svg ?><?= $svg2 ?></a>
										<?php endif; ?>
									<?php endif; ?>
								</div>
							</div>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
		<?php endforeach; ?>
	<?php endif; ?>

<?php else: ?>

	<?php if (!empty($games)): ?>
		<div class="row g-4 cws-games-row template-4 loadmore">
			<?php foreach ($games as $key => $game): ?>
				<div class="col-lg-6 col-12 matchlistitem template-4">
					<div class="game__item item-layer">
						<div class="game__inner text-center p-0">
							<div class="game__thumb mb-0">
								<img src="<?= str_replace('https//', 'https://', $game->urlButton) ?>" alt="<?= $game->name_game ?>" class="rounded-3 w-100" />
							</div>

							<div class="game__overlay">
								<div class="game__overlay-left">
									<h4><?= $game->name_game ?></h4>
								</div>

								<div class="game__overlay-right">
									<?php if (isset($gametype) && $gametype == 'free'): ?>
										<a href="javascript:void(0)" class="default-button game-launch" data-guid="<?= $game->id ?>"><span>play now <i class="icofont-circled-right"></i></span> </a>
									<?php else: ?>
										<?php if (is_user_logged_in()): ?>
											<a href="javascript:void(0)" class="default-button game-launch" data-guid="<?= $game->id ?>"><span>play now <i class="icofont-circled-right"></i></span> </a>
										<?php else: ?>
											<a href="<?= $sign_in_url ?>" class="default-button"><span>Login to play <i class="icofont-circled-right"></i></span> </a>
										<?php endif; ?>
									<?php endif; ?>
								</div>

								<?php
									$svg = file_get_contents(CWS_GAMES_ASSETS . '/images/star.svg');
									$svg2 = file_get_contents(CWS_GAMES_ASSETS . '/images/gradient.svg');
								?>

								<?php if (is_user_logged_in()): ?>
									<?php if (isset($userGames) && is_array($userGames) && in_array($game->guid, $userGames)): ?>
										<a href="javascript:void(0)" class="removeFromMyGames" data-guid="<?= $game->guid ?>"><?= $svg ?><?= $svg2 ?></a>
									<?php else: ?>
										<a href="javascript:void(0)" class="addToMyGames" data-guid="<?= $game->guid ?>"><?= $svg ?><?= $svg2 ?></a>
									<?php endif; ?>
								<?php endif; ?>
							</div>
						</div>
					</div>
				</div>
				<?php
					$secondary--;
					unset($games[$key]);

					if ($secondary <= 0) {
						break;
					}
				?>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>

	<?php if (!empty($games)): ?>
		<div class="row g-4 cws-games-row template-3 loadmore">
			<?php foreach ($games as $key => $game): ?>
				<div class="col-lg-6 col-12 matchlistitem template-3">
					<div class="game__item item-layer">
						<div class="game__inner text-center p-0">
							<div class="game__thumb mb-0">
								<img src="<?= str_replace('https//', 'https://', $game->urlButton) ?>" alt="<?= $game->name_game ?>" class="rounded-3 w-100" />
							</div>

							<div class="game__overlay">
								<div class="game__overlay-left">
									<h4><?= $game->name_game ?></h4>
								</div>

								<div class="game__overlay-right">
									<?php if (isset($gametype) && $gametype == 'free'): ?>
										<a href="javascript:void(0)" class="default-button game-launch" data-guid="<?= $game->id ?>"><span>play now <i class="icofont-circled-right"></i></span> </a>
									<?php else: ?>
										<?php if (is_user_logged_in()): ?>
											<a href="javascript:void(0)" class="default-button game-launch" data-guid="<?= $game->id ?>"><span>play now <i class="icofont-circled-right"></i></span> </a>
										<?php else: ?>
											<a href="<?= $sign_in_url ?>" class="default-button"><span>Login to play <i class="icofont-circled-right"></i></span> </a>
										<?php endif; ?>
									<?php endif; ?>
								</div>

								<?php
									$svg = file_get_contents(CWS_GAMES_ASSETS . '/images/star.svg');
									$svg2 = file_get_contents(CWS_GAMES_ASSETS . '/images/gradient.svg');
								?>

								<?php if (is_user_logged_in()): ?>
									<?php if (isset($userGames) && is_array($userGames) && in_array($game->guid, $userGames)): ?>
										<a href="javascript:void(0)" class="removeFromMyGames" data-guid="<?= $game->guid ?>"><?= $svg ?><?= $svg2 ?></a>
									<?php else: ?>
										<a href="javascript:void(0)" class="addToMyGames" data-guid="<?= $game->guid ?>"><?= $svg ?><?= $svg2 ?></a>
									<?php endif; ?>
								<?php endif; ?>
							</div>
						</div>
					</div>
				</div>
				<?php
					$leading--;
					unset($games[$key]);

					if ($leading <= 0) {
						break;
					}
				?>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>
<?php endif; ?>

<?php if (isset($show_load_more_button) && $show_load_more_button): ?>
	<div class="elementor-widget button-style-1 elementor-align-center elementor-widget-button load-more-button">
		<div class="elementor-widget-container">
			<div class="elementor-button-wrapper">
				<a href="javascript:void(0)" class="elementor-button elementor-button-link elementor-size-sm align-center">
					<span class="elementor-button-content-wrapper">
						<span class="elementor-button-text"><?= __("Load more", 'cws-games') ?></span>
					</span>
				</a>
				
				<form style="display: none;">
					<?php if (isset($load_more_params) && !empty($load_more_params)): ?>
						<?php foreach ($load_more_params as $key => $value): ?>
							<input type="hidden" name="<?= $key ?>" value="<?= $value ?>" />
						<?php endforeach; ?>
					<?php endif; ?>

					<input type="hidden" name="tpl" value="loadmore" />
					<input type="hidden" name="offset" value="<?= isset($load_more_params['offset']) ? intval($load_more_params['offset']) + ($shortcode_atts['loadmore_count'] ?? 11) : ($shortcode_atts['loadmore_count'] ?? 11) ?>" />
				</form>
				
				<div class="loader-overlay">
					<img src="<?= CWS_GAMES_ABSPATH_ASSETS . '/images/double-ring-loader.gif' ?>" alt="Loader" />
				</div>

			</div>
		</div>
	</div>
<?php endif; ?>

<div id="game-box-wrapper">
	<span><i class="icofont-ui-close fa fa-close"></i></span>
	<div id="game-box"></div>
</div>