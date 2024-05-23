<?php if (isset($shortcode_atts['title']) && $shortcode_atts['title'] != ''): ?>
	<div class="section-header">
		<h2><?= $shortcode_atts['title'] ?></h2>
	</div>
<?php endif; ?>


<div class="row g-4 cws-games-row <?= $shortcode_atts['class'] ?? '' ?> <?= isset($shortcode_atts['per_row']) ? 'template-' . $shortcode_atts['per_row'] : '' ?>">

<?php foreach ($games as $game): ?>
	<div class="col-lg-6 col-12 matchlistitem tpl-three <?= isset($shortcode_atts['per_row']) ? 'template-' . $shortcode_atts['per_row'] : '' ?>">
		<div class="game__item item-layer">
			<div class="game__inner text-center p-0">
				<!-- <div class="sticky">500 FiCo</div> -->

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

<div id="game-box-wrapper">
	<span><i class="icofont-ui-close fa fa-close"></i></span>
	<div id="game-box"></div>
</div>