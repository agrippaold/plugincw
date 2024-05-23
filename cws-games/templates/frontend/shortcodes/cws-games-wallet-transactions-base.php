
<div class="woo-wallet-my-wallet-container">
	<div class="woo-wallet-content">
		<div class="woo-wallet-content-heading">
			<h3 class="woo-wallet-content-h3"><?php esc_html_e( 'Balance', 'cws-games' ); ?></h3>
			
			<?php if (isset($balance) && !empty($balance)): ?>
				<p class="woo-wallet-price">
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
		</div>
		<div style="clear: both"></div>
		<hr/>

		<?php if (isset($transactions) && !empty($transactions)): ?>
			<ul class="woo-wallet-transactions-items">
				<?php foreach ($transactions as $transaction): ?>
					<li>
                        <div>
                            <p><?php echo $transaction['details']; ?></p>
                            <small><?php echo wc_string_to_datetime( $transaction['created_at'] )->date_i18n( wc_date_format() ); ?></small>
                        </div>
                        <div class="woo-wallet-transaction-type-<?php echo $transaction['direction']; ?>">
                        	<?php
                                echo $transaction['direction'] == 'credit' ? '+' : '-';

                                echo wc_price( $transaction['amount'], array( 'currency' => strtoupper( $transaction['currency'] ) ) );
                            ?>
                        </div>
                    </li>
				<?php endforeach; ?>
			</ul>
		<?php else: ?>
			<?= __('No Transactions found', 'cws_games'); ?>
		<?php endif; ?>
	</div>
</div>