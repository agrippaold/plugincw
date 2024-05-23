<?php

wp_enqueue_style( 'wheel-styles' );
wp_enqueue_script( 'wheel-scripts' );

?>

<?php if (isset($wheel_image) && $wheel_image != ''): ?>

	<div class="cws-progress-bar-image">
		<a href="javascript:void(0)"
			style="<?= !$show_wheel ? 'cursor: default;' : '' ?>"
			<?= $show_wheel === true ? 'id="show_wheel"' : '' ?>
		>
			<img src="<?= $wheel_image ?>" 
				 alt="<?= __('Wheel of Luck', 'cws-games') ?>"
				 style="<?= !$show_wheel ? 'filter: grayscale(1);' : '' ?>"
			/>
		</a>
	</div>

	<div class="cws-games-flex">
		<div class="cws-games-progress-bar">
			<div class="progress-bar" title="Progress <?= $progress ?? 0 ?> out of 100%">
				<div class="progress-bar-inner" <?= isset($progress) ? 'style="width: ' . $progress . '%;"' : '' ?>></div>
			</div>
		</div>
	</div>

	<?php if ($show_wheel): ?>
		<div id="wheelModal" class="iziModal" data-izimodal-title="<?= __('Wheel of luck', 'cws-games') ?>">
		    <div class="modal-content">
		    	<div class="mainbox" id="mainbox">
		    		<div class="box" id="box">
		    			<div class="board">
				    		<div class="spinner-table"> 
								<div class="dial">
									<?php foreach ($wheel_segments as $segment): ?>
										<div class="slice" data-segment="<?= $segment ?>"><div class="label"><?= $segment ?></div></div>
									<?php endforeach; ?>
								</div>
							</div>
				    	</div>
		    		</div>
		    		<button class="spin"><?= __('Spin', 'cws-games') ?></button>
		    	</div>
		    </div>
		</div>
	<?php endif; ?>

<?php endif; ?>