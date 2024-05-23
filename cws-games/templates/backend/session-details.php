
<style>
	.row-actions {
		left: initial;
	}
	.cws-games-wrapper {
		position: relative;
		min-height: 600px;
	}
	.cws-games-wrapper .wp-list-table.sessions {
		max-width: 600px;
		margin-top: 40px;
	}
	.cws-games-wrapper .wp-list-table.sessions tr th {
		font-weight: bold;
	}
</style>

<div class="cws-games-wrapper">

	<h2>Session Details</h2>
	<hr>

	<a href="<?= add_query_arg( array('page' => 'cws-games-sessions'), admin_url('admin.php') ) ?>"><i class="dashicons-before dashicons-arrow-left-alt"></i> <?= __('Back to Sessions', 'cws_games') ?></a>

	<table class="wp-list-table widefat fixed striped table-view-list sessions">
		<tbody>
			<tr>
				<th><?= __('ID Session', 'cws_games') ?></th>
				<td><?= $session->id_session ?? '' ?></td>
			</tr>
			<tr>
				<th><?= __('ID Game', 'cws_games') ?></th>
				<td><?= $session->gameId ?? '' ?></td>
			</tr>
			<tr>
				<th><?= __('Game Name', 'cws_games') ?></th>
				<td><?= $session->gameName ?? '' ?></td>
			</tr>
			<tr>
				<th><?= __('Casino', 'cws_games') ?></th>
				<td><?= $session->providerName ?? '' ?></td>
			</tr>
			<tr>
				<th><?= __('Tot In', 'cws_games') ?></th>
				<td><?= $session->totIn ?? '' ?></td>
			</tr>
			<tr>
				<th><?= __('Tot Out', 'cws_games') ?></th>
				<td><?= $session->totOut ?? '' ?></td>
			</tr>
			<tr>
				<th><?= __('Tot Spins', 'cws_games') ?></th>
				<td><?= $session->totSpins ?? '' ?></td>
			</tr>
			<tr>
				<th><?= __('User ID', 'cws_games') ?></th>
				<td><?= $session->userId ?? '' ?></td>
			</tr>
			<tr>
				<th><?= __('User Name', 'cws_games') ?></th>
				<td><?= $session->userFullName ?? '' ?></td>
			</tr>
			<tr>
				<th><?= __('Date', 'cws_games') ?></th>
				<td><?= wp_date( get_option('date_format'), strtotime($session->date_creation ?? ''), get_option('timezone_string') ) ?></td>
			</tr>
		</tbody>
	</table>

	<?php

		if (isset($session->transactions) && !empty($session->transactions)) {
			$CWS_GamesTransactions_Table = new CWS_GamesTransactions_Table;
		   	echo '<div class="wrap">';
		   	$CWS_GamesTransactions_Table->prepare_rows($session->transactions);
		   	$CWS_GamesTransactions_Table->prepare_items();
		   	$CWS_GamesTransactions_Table->display();
		   	echo '</div>';
		}

	?>
</div>