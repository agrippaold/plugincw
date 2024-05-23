<?php

wp_enqueue_style( 'slick-styles' );
wp_enqueue_script( 'slick-script' );

?>

<?php if (isset($shortcode_atts['title']) && $shortcode_atts['title'] != ''): ?>
	<div class="section-header">
		<h2><?= $shortcode_atts['title'] ?></h2>
	</div>
<?php endif; ?>

<?php if (isset($shortcode_atts['all_list']) && $shortcode_atts['all_list'] != ''): ?>
	<div style="display: flex; justify-content: flex-end; margin-right: 20px; margin-bottom: 10px;">
	<a href="<?= $shortcode_atts['all_list'] ?>" class="cws-games-see-all"><?= __('All', 'cws_games') ?><svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
<path d="M10.5 17.5C9.95 17.5 9.5 17.05 9.5 16.5C9.5 16.24 9.6 15.98 9.8 15.79C10.76 14.84 13.65 12 13.65 12C13.65 12 10.76 9.16 9.8 8.21C9.6 8.01 9.5 7.76 9.5 7.5C9.5 6.95 9.95 6.5 10.5 6.5C10.75 6.5 11.01 6.6 11.2 6.79C12.53 8.09 16.5 12 16.5 12C16.5 12 12.53 15.91 11.2 17.21C11.01 17.4 10.75 17.5 10.5 17.5Z" fill="#7B756B"></path>
</svg></a></div>
<?php endif; ?>

<div class="row g-4 cws-games-row <?= $shortcode_atts['class'] ?? '' ?> cws-games-slick">

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