<div class="section-wrapper">
				
	<div class="cart-bottom">
			
		<div class="cart-checkout-box woo-wallet-content">

			<?php if (isset($balance) && !empty($balance)): ?>
	            <p class="woo-wallet-price">
	                <?= __( 'Current balance :', 'cws_games' ); ?>

	                <?php foreach ($balance as $group => $_balance): ?>
	                	<?php if ($group == 'virtual'): ?>
	                		<?php foreach ($_balance as $currency_balance): ?>
		                        <span>
		                            <i>
		                                <?php
		                                    $svg = file_get_contents(CWS_GAMES_ASSETS . '/svg/coin.svg');
		                                    echo $svg;
		                                ?>
		                            </i>
		                            <?= $currency_balance->balance . ' ' . $currency_balance->symbol ?>
		                        </span>
		                    <?php endforeach; ?>
	                    <?php endif; ?>
                    <?php endforeach; ?>
	            </p>
	        <?php endif; ?>

            <table id="wc-wallet-transaction-details" class="table"></table>
            <?php do_action( 'woo_wallet_after_transaction_details_content' ); ?>

        </div>

	</div>

</div>