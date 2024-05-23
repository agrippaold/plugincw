<?php //echo '<pre>'.print_r($notifications, true).'</pre>'; ?>

<?php

global $wp;

?>

<div class="woo-wallet-my-wallet-container">
	<div class="woo-wallet-content">
		<div class="woo-wallet-content-heading">
			<h3 class="woo-wallet-content-h3"><?php esc_html_e( 'Notifications', 'cws-games' ); ?></h3>
		</div>
		<div style="clear: both"></div>
		<hr/>

		<?php if (isset($notifications['notifications']) && !empty($notifications['notifications'])): ?>
			<ul class="woo-wallet-transactions-items">
				<?php foreach ($notifications['notifications'] as $notification): ?>
					<li data-notification_details="<?= $notification['id'] ?? '' ?>">
                        <div class="notification-row">
                        	<div class="date">
                        		<?php if (isset($notification['created_at']) && $notification['created_at'] != ''): ?>
                        			<small><?php echo wc_string_to_datetime( $notification['created_at'] )->date_i18n( wc_date_format() ); ?></small>
                        		<?php else: ?>
                        			<small>--</small>
                        		<?php endif; ?>
                        	</div>
                        	<div class="title"><?php echo $notification['title'] ?? ''; ?></div>
                            <div class="status <?php echo $notification['state'] ?? '' ?>"><i class="<?= $notification['state'] == 'read' ? 'fas fa-envelope-open' : 'fas fa-envelope' ?>"></i></div>
                        </div>
                    </li>
				<?php endforeach; ?>
			</ul>
		<?php else: ?>
			<?= __('No Notifications found', 'cws_games'); ?>
		<?php endif; ?>

		<?php
			$paged 	= false;
			$pages 	= 0;
			$page 	= 1;

			if (isset($notifications['total']) && isset($notifications['pagination'])) {
				if (intval($notifications['total']) > intval($notifications['pagination'])) {
					$paged = true;

					$pages = ceil(intval($notifications['total']) / intval($notifications['pagination']));

					$page = $notifications['page'] ?? 1;
				}
			}

			if ($paged && $pages > 0):

			?>
			<div class="notifications-pagination">
				<ul>
				<?php for($i = 1; $i <= $pages; $i++): ?>
					<li class="<?= $i == $page ? 'active' : '' ?>">
						<a href="javascript:void(0)" data-page="<?= $i ?>" data-base="<?= $base_url ?? home_url($wp->request) ?>" data-href="<?= add_query_arg( ['paged' => $i], $base_url ?? home_url($wp->request) ) ?>"><?= $i ?></a>
					</li>
				<?php endfor; ?>
				</ul>
			</div>
			<?php

			endif;
		?>
	</div>

	<div class="overlay-wrapper">
		<img src="<?= CWS_GAMES_ABSPATH_ASSETS . '/images/double-ring-loader.gif' ?>" alt="Loader" />
	</div>
</div>