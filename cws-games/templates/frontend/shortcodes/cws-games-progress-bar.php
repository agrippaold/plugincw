
<div class="cws-games-flex">
	<?php if (isset($level['image']) && $level['image'] != ''): ?>
		<img src="<?= $level['image'] ?>" alt="<?= $level['label'] ?? '' ?>" />
	<?php endif; ?>

	<div class="cws-games-progress-bar">
		<div class="progress-bar" title="Progress <?= $level['score'] ?? 0 ?> out of 100%">
			<div class="progress-bar-inner" <?= isset($level['score']) ? 'style="width: ' . $level['score'] . '%;"' : '' ?>></div>
		</div>
	</div>
</div>