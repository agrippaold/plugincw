
<?php if (isset($products) && !empty($products)): ?>
	
	<?php if (isset($data) && isset($data->title) && $data->title != ''): ?>
		<h4 class="shop-wrapper-heading"><?= $data->title ?></h4>
	<?php endif; ?>

	<div class="shop-wrapper">
		<?php foreach ($products as $product): ?>
			<div class="wallet-shop">
				<div class="wallet-shop-inner">
					<div class="front">
						<div class="content-wrap">
							<?php if (isset($product->currencies) && !empty($product->currencies)): ?>
								<?php foreach ($product->currencies as $currency_code => $amount): ?>
									<div class="o-grid o-box__item c-coinpack__amount c-coinpack__amount--bonus o-box__item c-coinpack__amount--highlight">
										<span class="heading"><?= __('Free bonus offer', 'cws-games') ?></span>
		                                <span><?= wc_price($amount, array('currency' => $currency_code)) ?></span>
		                            </div>
								<?php endforeach; ?>
							<?php endif; ?>
						</div>
					</div>
					<?php if (isset($product->product_type) && $product->product_type == 'variable'): ?>
						<a href="<?= get_permalink($product->product_id) ?>" class="c-coinpack__cta c-btn c-btn--primary c-btn--block"><?= wc_price( $product->price ) ?? __('Buy', 'cws_games') ?></a>
					<?php else: ?>
						<a href="<?= add_query_arg( array( 'add-to-cart' => $product->product_id, 'quantity' => 1 ), wc_get_cart_url() ??	home_url('/') ) ?>" class="c-coinpack__cta c-btn c-btn--primary c-btn--block"><?= wc_price( $product->price ) ?? __('Buy', 'cws_games') ?></a>
					<?php endif; ?>
				</div>
			</div>
		<?php endforeach; ?>
	</div>

<?php endif; ?>