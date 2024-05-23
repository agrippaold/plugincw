<?php

$title = __('Current wallet balance', 'cws_games');

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

// foreach ($currencies as $key => $currency) {
// 	if (isset($currency['currency_code']) && $currency['currency_code'] != 'USD') {
// 		$currencies[$key]['type'] = 'virtual';
// 	}
// }

?>

<?php if ( !isset($shortcode_atts['tpl']) || (isset($shortcode_atts['tpl']) && $shortcode_atts['tpl'] != 'notifications') ): ?>

<?php if (isset($balance) && !empty($balance) && 
		  isset($default_currency) && $default_currency != ''): ?>

<div class="header-icon-block">
	<div class="content-holder wallet">
		<div class="currency-switcher new-layout three" data-template="three">
			<?php foreach ($currencies as $currency): ?>
				<?php if (isset($currency['type']) && $currency['type'] == 'virtual'): ?>
					<div class="currency <?= (strtoupper($currency['currency_code']) == strtoupper($default_currency)) ? 'active' : '' ?>" data-currency="<?= strtolower($currency['currency_code']) ?>">
						<?php
							$__balance 	= 0;
							$type 		= $currency['type'];

							foreach ($balance as $group => $_balance) {
								foreach ($_balance as $currency_balance) {
									if (strtolower($currency_balance->currency) == strtolower($currency['currency_code'])) {
										$__balance = $currency_balance->balance;
									}
								}
							}
						?>
						<div class="icons-wrapper">
							<?php foreach ($currencies as $_currency): ?>
								<?php if (isset($_currency['type']) && $_currency['type'] == 'virtual'): ?>
										<a class="icon woo-wallet-menu-contents trigger-currency-switcher" href="javascript:void(0)" data-currency="<?= $_currency['currency_code'] ?>" data-user_id="<?= get_current_user_id() ?>" data-target="<?= strtolower($_currency['currency_code']) ?>">
											<?php if (file_exists(CWS_GAMES_ASSETS . '/images/currency-'.strtoupper($_currency['currency_code']).'.png')): ?>
											<img src="<?= CWS_GAMES_ABSPATH_ASSETS . '/images/currency-'.strtoupper($_currency['currency_code']).'.png' ?>" alt="currency" />
											<?php else: ?>
												<span><?= $_currency['currency_symbol'] ?></span>
											<?php endif; ?>
										</a>
								<?php endif; ?>
							<?php endforeach; ?>
						</div>
						<div class="balance-wrapper">
							<span><?= $currency['currency_symbol'] ?></span>
							<?= number_format($__balance, 2, '.', ',') ?>
						</div>
					</div>
				<?php endif; ?>
			<?php endforeach; ?>
		</div>
	</div>
</div>

<?php endif; ?>

<?php else: ?>

<div class="header-icon-block user-profile user-notifications">
	<div class="icon-holder">
		<a href="<?= wc_get_account_endpoint_url('notifications') ?? '' ?>">
			<img src="<?= CWS_GAMES_ABSPATH_ASSETS . '/images/bell.png' ?>" />
			<?php if (isset($notifications_count) && intval($notifications_count) > 0): ?>
				<span class="counter"><?= $notifications_count ?></span>
			<?php endif; ?>
		</a>
	</div>
</div>

<?php endif; ?>