<?php
	$url = admin_url('admin.php');
?>

<style>
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

	.form-table th {
    vertical-align: middle;
    text-align: left;
    padding: 1px 10px 2px 10px;
    width: 200px;
    line-height: 1.1;
    font-weight: 600;
}

.form-table td {
    margin-bottom: 2px;
    padding: 1px 10px 2px 10px;
    line-height: 1.1;
    vertical-align: middle;
	height: 5px;

	
}

.cws-games-plugin-field {
    margin-bottom: 10px;
}

.cws-games-plugin-field label {
    font-weight: bold;
}

.cws-games-plugin-field-input {
    display: inline-block;
    margin-left: 10px;
}

.cws-games-plugin-field select,
.cws-games-plugin-field input[type="text"] {
    width: 400px;
    padding: 5px;
	
}
	
.btn-clear {
    display: inline-block;
    text-decoration: none;
    font-size: 13px;
    line-height: 2.15384615;
    min-height: 30px;
    margin: 0;
	position: relative;
	left: 734px;
    padding: 0 10px;
    cursor: pointer;
    border-width: 1px;
    border-style: solid;
    border-radius: 3px;
    border-color: #2271b1;
    white-space: nowrap;
    box-sizing: border-box;
	background: #2271b1;
    color: #fff;
}

#status-message {
    display: inline-block;
    margin-left: 30px;
}

#status-message > span {
    font-size: 15px;
    line-height: 30px;
}
	
</style>

<ul class="nav-tab-wrapper">
	<a href="<?= $url . '?page=cws-games'?>" class="nav-tab">Home</a>
	<a href="<?= $url . '?page=cws-games-general-settings' ?>" class="nav-tab">General Settings</a>
	<a href="<?= $url . '?page=cws-games-settings' ?>" class="nav-tab nav-tab-active">API Settings</a>
	<a href="<?= $url . '?page=cws-games-gameslist' ?>" class="nav-tab">Games</a>
</ul>


<div class="cws-games-wrapper">

	<h2>CWS Games - API Settings</h2> 

	<hr>

	<a href="<?= get_site_url() . "/tmp/request.txt" ?>" class="button button-primary" target="_blank">API Logs</a>
	<a href="javascript:void(0)" id="clearLogFile" class="button button-primary">Delete API Logs</a>
    <a href="javascript:void(0)" id="getSettings" class="button button-primary">Sync BackOffice Settings</a>
    <div id="status-message">
        <span></span>
    </div>

	<form action="options.php" method="post">
    <?php 
		settings_fields('cws_games_plugin');
		do_settings_sections('cws_games_plugin');

    $options = get_option('cws_games_plugin');
    $server_data = $options['servers'] ?? array();

		// cws_games_plugin_display_server_fields($server_data);
		settings_errors();  

	?>
	<BR><BR>
    <input name="submit" class="button button-primary" type="submit" value="<?php esc_attr_e('Save Settings'); ?>" />
</form> 

	<div class="overlay-wrapper">
		<img src="<?= CWS_GAMES_ABSPATH_ASSETS . '/images/double-ring-loader.gif' ?>" alt="Loader" />
	</div>

</div>

<script>
    // Add an event listener to the "Clear Fields" buttons in the 'paid' table
    const btnClearPaid = document.querySelectorAll('.btn-clear-paid');
    btnClearPaid.forEach((button) => {
        button.addEventListener('click', () => {
            clearFields(button, 'cws-games-table-paid');
        });
    });

    // Add an event listener to the "Clear Fields" buttons in the 'free' table
    const btnClearFree = document.querySelectorAll('.btn-clear-free');
    btnClearFree.forEach((button) => {
        button.addEventListener('click', () => {
            clearFields(button, 'cws-games-table-free');
        });
    });

    function clearFields(button, tableName) {

        const serverContainer = button.closest('.server-container');

   
        const table = serverContainer.querySelector(`.${tableName}`);

  
        const inputElements = table.querySelectorAll('input[type="text"]');

		const selectElements = table.querySelectorAll('select');

     
        inputElements.forEach((inputElement) => {
            inputElement.value = '';
        });

		selectElements.forEach((selectElement) => {
            selectElement.value = '';
        });
    }
</script>
