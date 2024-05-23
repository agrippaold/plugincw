<?php

$status = 'allowed';

if (isset($redeemable_coins) && !empty($redeemable_coins)) {
	foreach ($redeemable_coins as $redeemable) {
		if (isset($redeemable->pending) && $redeemable->pending > 0) {
			$status = 'pending';

			break;
		}
	}
}

?>

<div class="elementor-widget button-style-3 elementor-widget-button redeem-button">
	<div class="elementor-widget-container">
		<div class="elementor-button-wrapper">
			<a href="<?= wc_get_account_endpoint_url('redeemable-coins') ?>" class="elementor-button elementor-button-link elementor-size-sm">
				<span class="elementor-button-content-wrapper">
					<span class="elementor-button-text"><?= __("Redeem", 'cws-games') ?></span>
				</span>

				<?php

					if (file_exists(CWS_GAMES_ASSETS . '/images/' . $status . '.svg')) {
						$svg = file_get_contents(CWS_GAMES_ASSETS . '/images/' . $status . '.svg');

						echo '<div class="icon-top">' . $svg . '</div>';
					}
						

				?>
			</a>
		</div>
	</div>
</div>