jQuery(document).ready(function() {

	jQuery('#gameslist-import').on('click', function(e) {
		e.preventDefault();
    	e.stopPropagation();

    	var importSource = jQuery('#importSource').val();
    	var selectedOption = jQuery('#importSource option:selected');
    	var importServerID = selectedOption.attr('data-serverid');

		jQuery.ajax({
			url: cws_games_ajax_object.ajax_url,
			type: "POST",
			dataType: "json",
			data: {
        		action: 'importGames',
        	},
			beforeSend: function() {
				jQuery('.gameslist-importer .wp-message').empty();
				jQuery('.gameslist-importer .wp-message').removeClass(['updated', 'error']);
				jQuery('.cws-games-wrapper .overlay-wrapper').stop().show();
			},
			success: function(response) {
				
				if (response.status_txt) {
					jQuery('.gameslist-importer > p').text(response.status_txt);
					jQuery('.gameslist-importer > p').addClass(response.class);
				}

				jQuery('.gameslist-importer .wp-message').append('<p>'+response.status_txt+'</p>');

				if (response.status == 200) {
					jQuery('.gameslist-importer .wp-message').addClass('updated');
				} else {
					jQuery('.gameslist-importer .wp-message').addClass('error');
				}

				if (response.status == 200) {
					location.reload();
				}

			},
    		error: function(error) {
				console.log('Something went wrong `importGames`');
			}
		}).done(function() {
			jQuery('.cws-games-wrapper .overlay-wrapper').stop().hide();
	    });
	});
	
	jQuery('#clearLogFile').on('click', function(e) {
		e.preventDefault();
    	e.stopPropagation();

		jQuery.ajax({
			url: cws_games_ajax_object.ajax_url,
			type: "POST",
			dataType: "json",
			data: {
        		action: 'clearLogFile',
        	},
			beforeSend: function() {
				jQuery('.cws-games-wrapper .overlay-wrapper').stop().show();
			},
			success: function(response) {

				location.reload();

			},
    		error: function(error) {
				console.log('Something went wrong `clearLogFile`');
			}
		}).done(function() {
			jQuery('.cws-games-wrapper .overlay-wrapper').stop().hide();
	    });
	});

	jQuery(document).on("click", "#export_csv", function(e) {
		e.preventDefault();
		e.stopPropagation();

		var form = jQuery(this).closest("form");

		// var formdata = new FormData(form);
		var formdata = jQuery(form).serialize();

		jQuery.ajax({
			url: cws_games_ajax_object.ajax_url,
			type: "POST",
			dataType: "json",
			data: {
				action: 'cws_games_sessions_export_csv',
				formdata: formdata
			},
			beforeSend: function() {
				jQuery('.cws-games-wrapper .overlay-wrapper').stop().show();
			},
			success: function(response) {

				if (response.status > 0) {

					if (response.csv) {
						window.open(response.csv, '_blank');
					}

				} else {
					alert(response.status_txt);
				}

			},
			error: function(error) {
				console.log('Something went wrong `cws_games_sessions_export_csv`');
				console.log(error);
			}
		}).done(function() {
			jQuery('.cws-games-wrapper .overlay-wrapper').stop().hide();
	    });
	});

	jQuery(document).on("click", ".exportProvider", function(e) {
		e.preventDefault();
		e.stopPropagation();

		var providerId = jQuery(this).data('provider_id');

		console.log(providerId);

		jQuery.ajax({
			url: cws_games_ajax_object.ajax_url,
			type: "POST",
			dataType: "json",
			data: {
				action: 'cws_games_sessions_export_csv_by_provider',
				providerId: providerId
			},
			beforeSend: function() {
				jQuery('.cws-games-wrapper .overlay-wrapper').stop().show();
			},
			success: function(response) {

				if (response.status > 0) {

					if (response.csv) {
						window.open(response.csv, '_blank');
					}

				} else {
					alert(response.status_txt);
				}

			},
			error: function(error) {
				console.log('Something went wrong `cws_games_sessions_export_csv_by_provider`');
				console.log(error);
			}
		}).done(function() {
			jQuery('.cws-games-wrapper .overlay-wrapper').stop().hide();
	    });
	});

	jQuery(document).on("click", "#deleteCsvFiles", function(e) {
		e.preventDefault();
		e.stopPropagation();

		jQuery.ajax({
			url: cws_games_ajax_object.ajax_url,
			type: "POST",
			dataType: "json",
			data: {
				action: 'cws_games_sessions_delete_csv_files',
			},
			beforeSend: function() {
				jQuery('.cws-games-wrapper .overlay-wrapper').stop().show();
			},
			success: function(response) {

				if (response.status_txt) {
					alert(response.status_txt);
				}

			},
			error: function(error) {
				console.log('Something went wrong `cws_games_sessions_delete_csv_files`');
				console.log(error);
			}
		}).done(function() {
			jQuery('.cws-games-wrapper .overlay-wrapper').stop().hide();
	    });
	});


	jQuery(document).on("click", "#apiTokenButton", function(e) {
		e.preventDefault();
		e.stopPropagation();

		var backofficeUser = jQuery('#backoffice_user').val();
		var backofficePass = jQuery('#backoffice_pass').val();

		jQuery.ajax({
			url: cws_games_ajax_object.ajax_url,
			type: "POST",
			dataType: "json",
			data: {
				action: 'getAccessToken',
				user: backofficeUser,
				password: backofficePass
			},
			success: function(response) {
				
				if (response.accessToken) {
					jQuery('#apiToken').val(response.accessToken);
					jQuery('#statusMessage').text(response.status);

				} else {
					jQuery('#statusMessage').text(response.status);
				}
			},
			error: function(error) {				
				console.log('Something went wrong `getAccessToken`');
				console.log(error);
			}
		})
	});

	jQuery(document).on("click", "#getSettings", function(e) {
		e.preventDefault();
		e.stopPropagation();

		jQuery.ajax({
			url: cws_games_ajax_object.ajax_url,
			type: "POST",
			dataType: "json",
			data: {
				action: 'getSettings',
			},
			success: function(response) {
				var success = response['success'];
				var errors = response['errors'];

				if (success && success.length != 0) {
					jQuery('#status-message > span').text(success['txt_status']).addClass(success['class']);

				} else {
					jQuery('#status-message > span').text(errors['txt_status']).addClass(errors['class']);
				}
			},
			error: function(error) {				
				console.log('Something went wrong `getSettings`');
				console.log(error);
			}
		})
	});

	jQuery(document).on("change", ".cws-games-wrapper .default_currency", function() {
		var el = jQuery(this);

		jQuery(".cws-games-wrapper .default_currency").each(function() {
			if (!jQuery(this).is(el)) {
				jQuery(this).prop("checked", false);
			}
		});
	});
});