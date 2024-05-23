<div class="cws-games-sound">
	<?php if ($sound): ?>
		<span><?= __('Sound <span>ON</span>', 'cws-games') ?></span>
		<a href="javascript:void(0)" data-sound="0" data-user="<?= $user_id ?? 0 ?>" class="sound-toggle">
			<?php
				$svg = file_get_contents(CWS_GAMES_ASSETS . '/svg/switch-on.svg');
				echo $svg;
			?>
		</a>
	<?php else: ?>
		<span><?= __('Sound <span>OFF</span>', 'cws-games') ?></span>
		<a href="javascript:void(0)" data-sound="1" data-user="<?= $user_id ?? 0 ?>" class="sound-toggle">
			<?php
				$svg = file_get_contents(CWS_GAMES_ASSETS . '/svg/switch-off.svg');
				echo $svg;
			?>
		</a>
	<?php endif; ?>
</div>