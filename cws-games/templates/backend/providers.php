<?php
	$url = admin_url('admin.php');
?>

<style>
	table thead th#name_game {
		width: 10%;
	}
	table thead th#details  {
		width: 40%;
	}
	.row-actions {
		left: initial;
	}
	.cws-games-wrapper {
		position: relative;
		min-height: 600px;
	}
	.cws-games-wrapper .overlay-wrapper {
		display: none;
		position: absolute;
		top: 0;
		left: 0;
		width: 100%;
		height: 100%;
		background-color: rgba(255, 255, 255, 0.6);
	}
	.cws-games-wrapper .overlay-wrapper img {
		position: absolute;
		top: 50%;
		left: 50%;
		transform: translate(-50%, -50%);
	}
	.green {
		color: green;
	}
	.red {
		color: red;
	}
</style>

<ul class="nav-tab-wrapper">
	<a href="<?= $url . '?page=cws-games'?>" class="nav-tab">Home</a>
	<a href="<?= $url . '?page=cws-games-general-settings' ?>" class="nav-tab">General Settings</a>
	<a href="<?= $url . '?page=cws-games-settings' ?>" class="nav-tab">API Settings</a>
	<a href="<?= $url . '?page=cws-games-gameslist' ?>" class="nav-tab">Games</a>
	<a href="<?= $url . '?page=cws-games-sessions' ?>" class="nav-tab">Sessions</a>
	<a href="<?= $url . '?page=cws-games-providers' ?>" class="nav-tab nav-tab-active">Providers</a>
	<a href="<?= $url . '?page=cws-games-login-rewards' ?>" class="nav-tab">Login Rewards</a>
</ul>

<div class="cws-games-wrapper">

	<h2>CWS Games - Providers</h2>
	<hr>

	<?php if (isset($prepareServers) && !empty($prepareServers)): ?>
		<?php foreach ($prepareServers as $server): ?>
			<?php

				$totalPerServer = 0;

			?>
			<h4><?= $server['providerName'] ?></h4>
			<table class="wp-list-table widefat fixed striped table-view-list sessions" style="max-width: 900px;">
				<thead>
					<tr>
						<th style="background-color: #806DF1; color: #fff;"><?= __('Currency', 'cws_games') ?></th>
						<th style="background-color: #806DF1; color: #fff;"><?= __('Tot Spin', 'cws_games') ?></th>
						<th style="background-color: #806DF1; color: #fff;"><?= __('Tot In', 'cws_games') ?></th>
						<th style="background-color: #806DF1; color: #fff;"><?= __('Tot Out', 'cws_games') ?></th>
						<th style="background-color: #806DF1; color: #fff;"><?= __('GGR', 'cws_games') ?></th>
						<th style="background-color: #806DF1; color: #fff;"><?= __('GGR exchange Euro', 'cws_games') ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($server['currencies'] as $currency => $currency_row): ?>
						<?php

						$ggr = 0;

			            $ggr += $currency_row['totIn'] ?? 0;

			            $ggr -= $currency_row['totOut'] ?? 0;

			            $totalPerServer += ($ggr);

						?>
						<tr>
							<td><?= $currency ?></td>
							<td><?= $currency_row['totSpins'] ?? 0 ?></td>
							<td><?= number_format(($currency_row['totIn'] ?? 0), 2, ',', ' '); ?></td>
							<td><?= number_format(($currency_row['totOut'] ?? 0), 2, ',', ' '); ?></td>
							<td><?= number_format(($ggr ?? 0), 2, ',', ' '); ?></td>
							<td><?= number_format(($ggr ?? 0), 2, ',', ' '); ?></td>
						</tr>
					<?php endforeach; ?>

					<tr>
						<td style="font-weight: bold;"><?= __('TOT SPINS', 'cws_games') ?></td>
						<td style="font-weight: bold;"><?= $server['serverTotalSpins'] ?? 0 ?></td>
						<td style="font-weight: bold;"></td>
						<td style="font-weight: bold;"></td>
						<td style="font-weight: bold;"><?= __('TOT') ?></td>
						<td style="font-weight: bold;"><?= number_format(($totalPerServer ?? 0), 2, ',', ' '); ?></td>
					</tr>
				</tbody>
			</table>

			<div style="text-align: center; max-width: 900px; margin-top: 10px;">
				<a class="exportProvider" href="javascript:void(0)" data-provider_id="<?= $server['providerId'] ?>" style="display: inline-block; color: #806DF1; border: 1px solid #806DF1; border-radius: 50px; padding: 10px 20px; text-decoration: none;"><?= __('Export CSV', 'cws_games'); ?></a>
			</div>

			<BR/> <BR/>
		<?php endforeach;  ?>
	<?php endif; ?>

	<div class="overlay-wrapper">
		<img src="<?= CWS_GAMES_ABSPATH_ASSETS . '/images/double-ring-loader.gif' ?>" alt="Loader" />
	</div>

</div>