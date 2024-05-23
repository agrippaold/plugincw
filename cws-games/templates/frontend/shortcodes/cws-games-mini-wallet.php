<?php

$title = __('Current wallet balance', 'cws_games');

// echo '<pre>'.print_r($currencies, true).'</pre>';
// echo '<pre>'.print_r($balance, true).'</pre>';
// echo '<pre>'.print_r($default_currency, true).'</pre>';

if (isset($currencies) && !empty($currencies)) {
	if (isset($default_currency) && $default_currency != '') {
		foreach ($currencies as $currency) {
			if (strtoupper($currency['currency_code']) == strtoupper($default_currency)) {
				if (isset($currency['type']) && in_array($currency['type'], array('real', 'redeemable'))) {
					$default_currency = '';
					break;
				}
			}
		}

		if ($default_currency == '') {
			foreach ($currencies as $currency) {
				if (isset($currency['type']) && !in_array($currency['type'], array('real', 'redeemable'))) {
					$default_currency = $currency['currency_code'];
				}
			}
		}
	}
}

?>

<?php if (isset($balance) && !empty($balance) && 
		  isset($default_currency) && $default_currency != ''): ?>

<div class="header-icon-block">
	<div class="icon-holder">
		<span dir="rtl" class="woo-wallet-icon-wallet"></span>
	</div>
	<div class="content-holder wallet">
		<i>
			<?php
				$svg = file_get_contents(CWS_GAMES_ASSETS . '/svg/coin.svg');
				echo $svg;
			?>
		</i>
		
		<?php foreach ($balance as $group => $_balance): ?>
			<?php if ($group == 'virtual'): ?>
				<?php foreach ($_balance as $currency_balance): ?>
					<?php if (strtoupper($currency_balance->currency) == strtoupper($default_currency)): ?>
						<a class="woo-wallet-menu-contents open-currency-switcher" href="javascript:void(0)" title="<?= $title ?>"><?= $currency_balance->balance . ' ' . $currency_balance->symbol ?></a>
					<?php endif; ?>
				<?php endforeach; ?>
			<?php endif; ?>
		<?php endforeach; ?>

		<div class="currency-switcher">
			<?php foreach ($balance as $group => $_balance): ?>
				<?php if ($group == 'virtual'): ?>
					<?php foreach ($_balance as $currency_balance): ?>
						<?php
							$type = '';

							if (isset($currencies) && !empty($currencies)) {
								foreach ($currencies as $currency) {
									if (strtoupper($currency_balance->currency) == strtoupper($currency['currency_code'])) {
										$type = $currency['type'];
									}
								}
							}
						?>
						<a class="woo-wallet-menu-contents trigger-currency-switcher <?= strtoupper($currency_balance->currency) == strtoupper($default_currency) ? 'selected-currency' : '' ?> <?= in_array($type, array('real', 'redeemable')) ? 'disabled' : '' ?>" data-currency="<?= $currency_balance->currency ?>" data-user_id="<?= get_current_user_id() ?>" href="javascript:void(0)">
							<i>
								<?php
									$svg = file_get_contents(CWS_GAMES_ASSETS . '/svg/coin.svg');
									echo $svg;
								?>
							</i>
							<?= $currency_balance->balance . ' ' . $currency_balance->symbol ?>
							<?php if ($type != '' && in_array($type, array('real', 'redeemable'))): ?>
								&nbsp;&nbsp;(<?= $type ?>)
							<?php endif; ?>
						</a>
					<?php endforeach; ?>
				<?php elseif ($group == 'redeemable'): ?>
					<?php foreach ($_balance as $currency_balance): ?>
						<?php
							$type = '';

							if (isset($currencies) && !empty($currencies)) {
								foreach ($currencies as $currency) {
									if (strtoupper($currency_balance->currency) == strtoupper($currency['currency_code'])) {
										$type = $currency['type'];
									}
								}
							}
						?>
						<a class="woo-wallet-menu-contents" data-currency="<?= $currency_balance->currency ?>" data-user_id="<?= get_current_user_id() ?>" href="javascript:void(0)">
							<i>
								<?php
									$svg = file_get_contents(CWS_GAMES_ASSETS . '/svg/coin.svg');
									echo $svg;
								?>
							</i>
							<?= $currency_balance->redeemableTotal . ' ' . $currency_balance->symbol ?>
							&nbsp;&nbsp;(<?= __('Redeemable') ?>)
						</a>
					<?php endforeach; ?>
				<?php endif; ?>
			<?php endforeach; ?>
		</div>
	</div>
</div>

<?php endif; ?>

<div class="header-icon-block user-profile user-notifications">
	<div class="icon-holder">
		<a href="<?= wc_get_account_endpoint_url('notifications') ?? '' ?>">
			<i class="fas fa-envelope"></i>
			<?php if (isset($notifications_count) && intval($notifications_count) > 0): ?>
				<span class="counter"><?= $notifications_count ?></span>
			<?php endif; ?>
		</a>
	</div>
</div>