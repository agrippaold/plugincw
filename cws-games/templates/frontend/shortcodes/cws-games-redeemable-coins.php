<?php //echo '<pre>'.print_r($redeemable_coins, true).'</pre>'; ?>

<?php if (is_active_sidebar('redeemable_coins_top_sidebar')): ?>

	<div class="redeemable-coins-top">
		<?php dynamic_sidebar('redeemable_coins_top_sidebar') ?>
	</div>

<?php endif; ?>

<div class="overlay-parent">
	<div class="woo-wallet-my-wallet-container">
		<div class="woo-wallet-content">
			<div class="woo-wallet-content-heading">
				<h3 class="woo-wallet-content-h3"><?php esc_html_e( 'Redeemable Coins', 'cws-games' ); ?></h3>
			</div>
			<div style="clear: both"></div>
			<hr/>

			<?php if (isset($redeemable_coins) && !empty($redeemable_coins)): ?>
				<ul class="woo-wallet-redeemable-items header">
					<li>
						<div class="redeemable-row">
							<div class="currency"><?= __('Currency', 'cws_games') ?></div>
							<div class="walletTotal"><?= __('Balance', 'cws_games') ?></div>
							<div class="redeemableTotal"><?= __('Redeemable Total', 'cws-games') ?></div>
							<div class="coupon"><?= __('Coupon', 'cws_games') ?></div>
							<div class="status"><?= __('Pending', 'cws_games') ?></div>
							<div class="totalRealMoney"><?= __('Amount', 'cws_games') ?></div>
						</div>
					</li>
				</ul>

				<ul class="woo-wallet-redeemable-items">
					<?php foreach ($redeemable_coins as $redeemable): ?>
						<li>
							<div class="redeemable-row">
								<div class="currency"><?= $redeemable->currency ?></div>
								<div class="walletTotal">
									<?php
										if (isset($balance) && !empty($balance)) {
											foreach ($balance as $currency_balance) {
												if (strtoupper($currency_balance->currency) == strtoupper($redeemable->currency)) {
													echo $currency_balance->balance ?? '';
												}
											}
										}
									?>
								</div>
								<div class="redeemableTotal"><?= $redeemable->redeemableTotal ?></div>
								<div class="coupon"><?= $redeemable->coupon ?></div>
								<div class="status"><?= $redeemable->pending ?? '' ?>
									<?php if ($redeemable->pending > 0): ?>
										<a href="javascript:void(0)" class="c-btn--block cancel" data-currency="<?= $redeemable->currency ?>" data-amount="0" data-user_id="<?= get_current_user_id() ?>"><?= __('Cancel', 'cws_games') ?></a>
									<?php endif; ?>
								</div>
								<div class="totalRealMoney"><?= $redeemable->totalRealMoney_sign . $redeemable->totalRealMoney ?></div>
							</div>
						</li>
					<?php endforeach; ?>
				</ul>
			<?php else: ?>
				<?= __('No Redeemable coins found', 'cws_games'); ?>
			<?php endif; ?>
		</div>
	</div>

	<?php

	$redeem_balance = true;
	$redeemable_currencies = [];

	if (isset($redeemable_coins) && !empty($redeemable_coins)) {
		foreach ($redeemable_coins as $redeemable) {
			if ($redeemable->pending != 1) {
				$redeemable_currencies[] = $redeemable;
			}
		}
	} else {
		$redeem_balance = false;
	}

	if (empty($redeemable_currencies)) {
		$redeem_balance = false;
	}

	if ($redeem_balance):

	?>
		<div class="redeemable-balance">
			<form action="" id="redeem_balance">
				<select name="currency">
					<option value="-1"><?= __('Select currency', 'cws_games') ?></option>
					<?php foreach ($redeemable_currencies as $redeemable): ?>
						<option value="<?= $redeemable->currency ?>"><?= $redeemable->currency ?></option>
					<?php endforeach; ?>
				</select>
				<input type="text" name="amount" placeholder="<?= __('Amount', 'cws_games') ?>" value="" required />
				<button type="submit" class="c-btn--block"><?= __('Withdraw to my bank account', 'cws_games') ?></button>
				<input type="hidden" name="user_id" value="<?= get_current_user_id() ?>" />
			</form>
			<div class="response-message"></div>
		</div>

	<?php

	endif;

	?>

	<div class="overlay-wrapper">
		<img src="<?= CWS_GAMES_ABSPATH_ASSETS . '/images/double-ring-loader.gif' ?>" alt="Loader" />
	</div>
</div>

<?php if (is_active_sidebar('redeemable_coins_bottom_sidebar')): ?>

	<div class="redeemable-coins-bottom">
		<?php dynamic_sidebar('redeemable_coins_bottom_sidebar') ?>
	</div>

<?php endif; ?>