
<?php if (isset($products) && !empty($products)): ?>
	
	<?php if (isset($data) && isset($data->title) && $data->title != ''): ?>
		<h4 class="shop-wrapper-heading"><?= $data->title ?></h4>
	<?php endif; ?>

	<div class="shop-wrapper">
		<?php foreach ($products as $product): ?>
			<div class="wallet-shop">
				<div class="wallet-shop-inner">
					<div class="front">
						<img src="https://gametwist-com-cdn-static.gt-cdn.net/Content/images/build/assets/shop/coins-xxs.3d9f209.png" alt="" />
						<div class="content-wrap">
							<?php if (isset($product->currencies) && !empty($product->currencies)): ?>
								<?php foreach ($product->currencies as $currency_code => $amount): ?>
									<div class="o-grid o-box__item c-coinpack__amount c-coinpack__amount--bonus o-box__item c-coinpack__amount--highlight">
		                                <span><?= wc_price($amount, array('currency' => $currency_code)) ?></span>
		                            </div>
								<?php endforeach; ?>
							<?php endif; ?>

							<?php if (isset($product->product_type) && $product->product_type == 'variable'): ?>
								<a href="<?= get_permalink($product->product_id) ?>" class="c-coinpack__cta c-btn c-btn--primary c-btn--block"><?= wc_price( $product->price ) ?? __('Buy', 'cws_games') ?></a>
							<?php else: ?>
								<a href="<?= add_query_arg( array( 'add-to-cart' => $product->product_id, 'quantity' => 1 ), wc_get_cart_url() ??	home_url('/') ) ?>" class="c-coinpack__cta c-btn c-btn--primary c-btn--block"><?= wc_price( $product->price ) ?? __('Buy', 'cws_games') ?></a>
							<?php endif; ?>
						</div>
					</div>
				</div>
			</div>
		<?php endforeach; ?>
	</div>

<?php endif; ?>