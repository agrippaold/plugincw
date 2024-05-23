jQuery(document).ready(function() {

	jQuery('.iziModal').iziModal();

	if (jQuery("#loginRewardModal").length > 0) {
		jQuery("#loginRewardModal").iziModal("open");
	}

    jQuery(document).on('click', '[data-toggle-modal]', function(e) {
    	e.preventDefault();
    	e.stopPropagation();

    	var modal = jQuery(this).data('toggle-modal');

    	jQuery('#'+modal).iziModal("open");
    })

    jQuery(document).on("click", ".open-search-modal", function(e) {
    	if (jQuery(".dialog-widget .dialog-close-button").length > 0) {
    		jQuery(".dialog-widget .dialog-close-button").trigger("click");
    	}

    	jQuery("#searchModal").iziModal("open");
    });

	jQuery(document).on('click', '#game-box-wrapper > span', function() {
		var el = jQuery(this);

		var tmpl = '';

		if (jQuery(".currency-switcher").length > 0) {
			tmpl = jQuery(".currency-switcher").data("template");
		}

		jQuery.ajax({
			url: cws_games_ajax_object.ajax_url,
			type: "POST",
			dataType: "json",
			data: {
        		action: 'updateWallet',
        		tmpl: tmpl
        	},
			beforeSend: function() {
				
			},
			success: function(response) {

				if (response.html) {
					jQuery(document).find(".content-holder.wallet").replaceWith(jQuery(response.html).find(".content-holder.wallet"));
				}
				
			},
    		error: function(error) {
				console.log('Something went wrong `UpdateWallet`');
			}
		}).done(function() {
			el.closest('#game-box-wrapper').find('#game-box').empty();
			el.closest('#game-box-wrapper').hide();
	    });
		
	});

	jQuery(document).on('click', '.game-launch', function(e) {
		var game_id 	= jQuery(this).data('guid'),
			gametype 	= jQuery(this).data('gametype'),
			game_div 	= jQuery(this).closest('.game__item'),
			game_parent = jQuery(game_div).parent().parent(),
			mobile 		= 0;

		if (jQuery(window).width() <= 768) {
			mobile = 1;
		}

		jQuery.ajax({
			url: cws_games_ajax_object.ajax_url,
			type: "POST",
			dataType: "json",
			data: {
        		action: 'GameLaunch',
        		game_id: game_id,
        		gametype: gametype,
        		mobile: mobile
        	},
			beforeSend: function() {
				
			},
			success: function(response) {

				if (response.status > 0 && response.url) {
					if (jQuery(game_parent).find('#game-box').length > 0) {

					} else {
						game_parent = jQuery(game_parent).parent();
					}
  
					var gameBoxWrapper = jQuery("#game-box-wrapper").first();
						gameBoxWrapper.show();

					var gameBox = gameBoxWrapper.find("#game-box").first();
						gameBox.append('<iframe width="100%" height="100%" src="'+response.url+'" allowfullscreen></iframe>');
					
				} else {

					if (response.modal) {
						jQuery('#siteModal').remove();

						jQuery('body').append(jQuery(response.modal));

						jQuery('.iziModal').iziModal({
							width: 400
						});
						jQuery('#siteModal').iziModal("open");
					}
				}
			},
    		error: function(error) {
				console.log('Something went wrong `GameLaunch`');
			}
		}).done(function() {
			
	    });
	});

	jQuery('.match-filter-button-group').on('click', '> li', function() {
		var tabs 			= jQuery(this).parent(),
			tabs_content 	= jQuery(this).parent().next(),
			target 			= jQuery(this).data('target');

		jQuery(tabs).find('li').removeClass('active');

		jQuery(tabs_content).find('.active').removeClass('active');

		jQuery(this).toggleClass('active');
		jQuery(tabs_content).find('#'+target).toggleClass('active');
	});

	jQuery(document).on('click', '.addToMyGames', function(e) {
		e.preventDefault();
		e.stopPropagation();

		var game_guid 	= jQuery(this).data('guid'),
			el 			= jQuery(this);

		jQuery.ajax({
			url: cws_games_ajax_object.ajax_url,
			type: "POST",
			dataType: "json",
			data: {
        		action: 'AddToMyGames',
        		game_guid: game_guid,
        	},
			beforeSend: function() {
				
			},
			success: function(response) {

				if (response.status > 0) {
					jQuery(el).removeClass('addToMyGames').addClass('removeFromMyGames');
				} else {
					console.log(response.status_txt);
				}

			},
    		error: function(error) {
				console.log('Something went wrong `addToMyGames`');
			}
		}).done(function() {
			
	    });
	});

	jQuery(document).on('click', '.removeFromMyGames', function(e) {
		e.preventDefault();
		e.stopPropagation();

		var game_guid 	= jQuery(this).data('guid'),
			el 			= jQuery(this);

		jQuery.ajax({
			url: cws_games_ajax_object.ajax_url,
			type: "POST",
			dataType: "json",
			data: {
        		action: 'RemoveFromMyGames',
        		game_guid: game_guid,
        	},
			beforeSend: function() {
				
			},
			success: function(response) {

				if (response.status > 0) {
					jQuery(el).removeClass('removeFromMyGames').addClass('addToMyGames');
				} else {
					console.log(response.status_txt);
				}

			},
    		error: function(error) {
				console.log('Something went wrong `removeFromMyGames`');
			}
		}).done(function() {
			
	    });
	});

	jQuery(document).on("click", ".open-currency-switcher", function(e) {
		e.preventDefault();
		e.stopPropagation();

		jQuery(this).toggleClass("active");

		if (jQuery(this).hasClass("active")) {
			jQuery(".wallet .currency-switcher").stop().show();
		} else {
			jQuery(".wallet .currency-switcher").stop().hide();
		}
	});

	jQuery(document).on("click", function(event) {
		if (jQuery(".open-currency-switcher").length > 0) {
			var container = jQuery(".open-currency-switcher");
        	var container2 = jQuery(".wallet .currency-switcher");

        	if (jQuery(container).hasClass("active")) {
	        	if (!container.is(event.target) && container.has(event.target).length === 0 &&
		            !container2.is(event.target) && container2.has(event.target).length === 0) {

		            container.removeClass("active");
		            container2.stop().hide();
		        }
	        }
		}
	});

	jQuery(document).on("click", ".wallet .currency-switcher .trigger-currency-switcher:not(.selected-currency):not(.disabled)", function(event) {
		var currency = jQuery(this).data("currency"),
			user_id = jQuery(this).data("user_id");

		var wallet = jQuery(this).closest(".currency-switcher");
		var tmpl = jQuery(wallet).data("template");

		jQuery.ajax({
			url: cws_games_ajax_object.ajax_url,
			type: "POST",
			dataType: "json",
			data: {
        		action: 'cws_games_setUserDefaultCurrency',
        		currency: currency,
        		user_id: user_id,
        		tmpl: tmpl
        	},
			beforeSend: function() {
				
			},
			success: function(response) {

				if (response.status > 0) {
					
					// if (response.html) {
					// 	jQuery(document).find(".content-holder.wallet").replaceWith(jQuery(response.html).find(".content-holder.wallet"));
					// }
					
				} else {
					console.log(response.status_txt);
				}

			},
    		error: function(error) {
				console.log('Something went wrong `GameLaunch`');
			}
		}).done(function() {
			
	    });
	});

	jQuery(document).on("click", "[data-notification_details]", function() {
		let notificationId = jQuery(this).data("notification_details");

		jQuery.ajax({
			url: cws_games_ajax_object.ajax_url,
			type: "POST",
			dataType: "json",
			data: {
        		action: 'getNotification',
        		notificationId: notificationId,
        	},
			beforeSend: function() {
				
			},
			success: function(response) {

				if (response.modal) {
					jQuery('#siteModal').remove();

					jQuery('body').append(jQuery(response.modal));

					jQuery('.iziModal').iziModal({
						width: 400
					});
					jQuery('#siteModal').iziModal("open");
				} else {
					if (response.status_txt) {
						alert(response.status_txt);
					} else {
						alert('Error !!!');
					}
				}

			},
    		error: function(error) {
				console.log('Something went wrong `getNotification`');
			}
		}).done(function() {
			
	    });
	});

	jQuery(document).on("click", ".notifications-pagination a", function(e) {
		e.preventDefault();
		e.stopPropagation();

		let parent = jQuery(this).parent();

		if (!parent.hasClass("active")) {
			var page = jQuery(this).data("page");
			var href = jQuery(this).data("href");
			var base_url = jQuery(this).data("base");

			jQuery.ajax({
				url: cws_games_ajax_object.ajax_url,
				type: "POST",
				dataType: "html",
				data: {
	        		action: 'getNotifications',
	        		base_url: base_url,
	        		page: page,
	        	},
				beforeSend: function() {
					jQuery(".woo-wallet-my-wallet-container .overlay-wrapper").stop().show();
				},
				success: function(response) {

					if (response) {
						jQuery(document).find(".woo-wallet-my-wallet-container .woo-wallet-content").replaceWith(jQuery(response).find(".woo-wallet-content"));
					}

				},
	    		error: function(error) {
					console.log('Something went wrong `getNotifications`');
				}
			}).done(function() {
				window.history.pushState('', '', href);

				jQuery(".woo-wallet-my-wallet-container .overlay-wrapper").stop().hide();
		    });
		}
	});

	jQuery(document).on("submit", "#redeem_balance", function(e) {
		e.preventDefault();
		e.stopPropagation();

		var submit = true;

		var form = jQuery(this);

		var select = jQuery(this).find("select[name='currency']");

		if (select.val() == '' || select.val() == -1) {
			jQuery(select).addClass("error");

			submit = false;
		}


		if (submit) {

			var formdata = jQuery(form).serialize();

			jQuery.ajax({
				url: cws_games_ajax_object.ajax_url,
				type: "POST",
				dataType: "json",
				data: {
	        		action: 'redeemBalance',
	        		formdata: formdata,
	        	},
				beforeSend: function() {
					jQuery(".redeemable-balance .response-message").empty();
					jQuery(".redeemable-balance .response-message").removeClass("success error");
					jQuery(".overlay-parent .overlay-wrapper").stop().show();
				},
				success: function(response) {

					if (response) {
						if (response.status > 0) {
							jQuery(".redeemable-balance .response-message").addClass("success");
							jQuery(".redeemable-balance .response-message").text(response.status_txt);

							location.reload();
						} else {
							jQuery(".redeemable-balance .response-message").addClass("error");
							jQuery(".redeemable-balance .response-message").text(response.status_txt);
						}
					}

				},
	    		error: function(error) {
					console.log('Something went wrong `redeemBalance`');
				}
			}).done(function() {
				jQuery(".overlay-parent .overlay-wrapper").stop().hide();
		    });
		}
	});

	jQuery(document).on("change", "#redeem_balance select", function() {
		jQuery(this).removeClass("error");
	});

	jQuery(document).on("click", ".woo-wallet-redeemable-items .cancel", function(e) {
		e.preventDefault();
		e.stopPropagation();

		let currency = jQuery(this).data("currency");
		let amount = jQuery(this).data("amount");
		let user_id = jQuery(this).data("user_id");

		var formdata = 'currency='+currency+'&amount='+amount+'&user_id='+user_id;

		jQuery.ajax({
				url: cws_games_ajax_object.ajax_url,
				type: "POST",
				dataType: "json",
				data: {
	        		action: 'redeemBalance',
	        		formdata: formdata,
	        	},
				beforeSend: function() {
					jQuery(".redeemable-balance .response-message").empty();
					jQuery(".redeemable-balance .response-message").removeClass("success error");
					jQuery(".overlay-parent .overlay-wrapper").stop().show();
				},
				success: function(response) {

					if (response) {
						if (response.status > 0) {
							jQuery(".redeemable-balance .response-message").addClass("success");
							jQuery(".redeemable-balance .response-message").text(response.status_txt);

							location.reload();
						} else {
							jQuery(".redeemable-balance .response-message").addClass("error");
							jQuery(".redeemable-balance .response-message").text(response.status_txt);
						}
					}

				},
	    		error: function(error) {
					console.log('Something went wrong `redeemBalance`');
				}
			}).done(function() {
				jQuery(".overlay-parent .overlay-wrapper").stop().hide();
		    });
	});

	setInterval(function() {
		jQuery.ajax({
			url: cws_games_ajax_object.ajax_url,
			type: "POST",
			dataType: "json",
			data: {
        		action: 'checkNotification',
        	},
			beforeSend: function() {
				
			},
			success: function(response) {

				if (response.modal) {
					jQuery('#siteModal').remove();

					jQuery('body').append(jQuery(response.modal));

					jQuery('.iziModal').iziModal({
						width: 400
					});
					jQuery('#siteModal').iziModal("open");
				}

			},
    		error: function(error) {
				console.log('Something went wrong `checkNotification`');
			}
		}).done(function() {
			
	    });
	}, 5000);

	if (jQuery(".cws-gameslist-filters").length > 0) {
		var customResults = [];

		jQuery(".cws-gameslist-filters input").each(function() {
			var input = jQuery(this);
			var block = jQuery(this).closest(".control-group");
			jQuery(this).autocomplete({
			    source: function(request, response) {
			        jQuery.ajax({
			            url: cws_games_ajax_object.ajax_url,
			            type: "POST",
			            data: {
			                action: "cwsGamesGetAutompleteResults",
			                query: request.term
			            },
			            success: function(data) {
			            	var formattedData = data.map(function(item) {
						        return {
						            label: item.name,
						            value: item.name,
						            name: item.name,
						            icon: item.icon,
						            provider_name: item.provider_name,
									id: item.id,
						        };
						    });
			                response(formattedData);
			            }
			        });
			    },
			    position: { my: "left top", at: "left bottom", of: "#CWSGamesFilter" },
			    search: function(event, ui) {
			    	jQuery(block).find(".loader").stop().show();
			    },
			    response: function(event, ui) {
			        var results = ui.content || [];

			        customResults = results.map(function(item) {
			            var imageBlock = '';
			            var contentBlock = '';

			            if (item.icon && item.icon != '') {
			                imageBlock = '<div class="image"><img src="' + item.icon + '" alt="' + item.name + '" /></div>';
			            }

			            contentBlock = '<div class="content"><h3>' + item.name + '</h3><span>' + item.provider_name + '</span></div>';

			            return {
			                label: item.label,
			                value: item.value,
			                customHtml: `<a href="javascript:void(0)"><div class="game-launch autocomplete-row" data-guid="${item.id}">${imageBlock}${contentBlock}</div></a>`
			            };
			        });
			    },
			    open: function(event, ui) {
			        var ul = jQuery(input).autocomplete("widget");
			        ul.empty();

			        jQuery('<li class="title">Providers</li>')
			                .appendTo(ul);
					
					const providers = `
					<div class="search-result-providers-slider">
						<div>
							<a href="#">
								<img decoding="async" loading="lazy" src="https://abnormalcode.com/starsplay/wp-content/uploads/2024/05/Game-providers-logos-1.png">
							</a>
						</div>
						<div>
							<a href="#">
								<img decoding="async" loading="lazy" src="https://abnormalcode.com/starsplay/wp-content/uploads/2024/05/Game-providers-logos-2.png">
							</a>
						</div>
						<div>
							<a href="#">
								<img decoding="async" loading="lazy" src="https://abnormalcode.com/starsplay/wp-content/uploads/2024/05/Game-providers-logos-3.png">
							</a>
						</div>
						<div>
							<a href="#">
								<img decoding="async" loading="lazy" src="https://abnormalcode.com/starsplay/wp-content/uploads/2024/05/Game-providers-logos-4.png">
							</a>
						</div>
						<div>
							<a href="#">
								<img decoding="async" loading="lazy" src="https://abnormalcode.com/starsplay/wp-content/uploads/2024/05/Game-providers-logos-5.png">
							</a>
						</div>
						<div>
							<a href="#">
								<img decoding="async" loading="lazy" src="https://abnormalcode.com/starsplay/wp-content/uploads/2024/05/Game-providers-logos-6.png">
							</a>
						</div>
					</div>
					`

					jQuery(providers).appendTo(ul);
					jQuery(".search-result-providers-slider").slick({
						infinite: true,
						slidesToShow: 5,
						slidesToScroll: 1,
						arrows: false,
						responsive: [
							{
								breakpoint: 768,
								settings: {
									slidesToShow: 3,
								},
							},
						]
					})

			        jQuery('<li class="title">Games</li>')
			                .appendTo(ul);

			        jQuery.each(customResults, function (index, item) {
			            jQuery('<li class="ui-menu-item" data-ui-autocomplete-item="' + item.value + '"></li>')
			                .append(item.customHtml)
			                .appendTo(ul);
			        });

			        jQuery(block).find(".loader").stop().hide();
			    },
			    focus: function(event, ui) {
			    	// console.log('Focus event - ui:', ui);
			        this.value = ui.item;

			        return false;
			    },
			    select: function(event, ui) {
			        // console.log('Select event - ui:', ui);
			        this.value = ui.item;

			        jQuery(input).click();

			        return false;
			    }
			});
		});
	}

	if (jQuery(".cws-games-jackpot").length > 0) {
		setInterval(function() {
			jQuery.ajax({
				url: cws_games_ajax_object.ajax_url,
				type: "POST",
				dataType: "json",
				data: {
	        		action: 'checkJackpot',
	        	},
				beforeSend: function() {
					
				},
				success: function(response) {

					if (response.jackpot) {
						updateNumber(".cws-games-jackpot .number", response.jackpot);
					}

				},
	    		error: function(error) {
					console.log('Something went wrong `checkJackpot`');
				}
			}).done(function() {
				
		    });
		}, 10000);
	}

	if (jQuery(window).width() >= 769) {
		if (jQuery(".cws-games-slick").length > 0) {
			let slidesPerRow = 5;
			let slidesToShow = 5;

			var responsive = [
				{
					breakpoint: 1240,
					settings: {
						slidesPerRow: 5,
						slidesToShow: 5,
					}
				},
				{
					breakpoint: 991,
					settings: {
						slidesPerRow: 4,
						slidesToShow: 4,
					}
				},
				{
					breakpoint: 768,
					settings: {
						slidesPerRow: 2,
						slidesToShow: 2,
					}
				}
			];

			jQuery(".cws-games-slick").each(function() {
				jQuery(this).slick({
					arrows: true,
					prevArrow: '<button class="slick-arrow-prev"><svg width="15" height="24" viewBox="0 0 15 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M14.489 12.8553L4.61427 22.7301C3.93175 23.4126 2.8281 23.4126 2.15284 22.7301L0.51189 21.0891C-0.17063 20.4066 -0.17063 19.303 0.51189 18.6277L7.51134 11.6282L0.51189 4.62879C-0.17063 3.94627 -0.17063 2.84262 0.51189 2.16736L2.14558 0.511889C2.8281 -0.17063 3.93175 -0.17063 4.60701 0.511889L14.4818 10.3866C15.1715 11.0692 15.1715 12.1728 14.489 12.8553Z" fill="black"/></svg></button>',
					nextArrow: '<button class="slick-arrow-next"><svg width="15" height="24" viewBox="0 0 15 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M14.489 12.8553L4.61427 22.7301C3.93175 23.4126 2.8281 23.4126 2.15284 22.7301L0.51189 21.0891C-0.17063 20.4066 -0.17063 19.303 0.51189 18.6277L7.51134 11.6282L0.51189 4.62879C-0.17063 3.94627 -0.17063 2.84262 0.51189 2.16736L2.14558 0.511889C2.8281 -0.17063 3.93175 -0.17063 4.60701 0.511889L14.4818 10.3866C15.1715 11.0692 15.1715 12.1728 14.489 12.8553Z" fill="black"/></svg></button>',
					dots: false,
					lazyLoad: "progressive",
					variableWidth: true,
					slidesPerRow: slidesPerRow,
					slidesToShow: slidesToShow,
					responsive: responsive
				});
			});
		}
	} else {
		if (jQuery(".cws-games-slick").length > 0) {
			let slidesPerRow = 5;
			let slidesToShow = 5;

			var responsive = [
				{
					breakpoint: 1240,
					settings: {
						slidesPerRow: 5,
						slidesToShow: 5,
					}
				},
				{
					breakpoint: 991,
					settings: {
						slidesPerRow: 4,
						slidesToShow: 4,
					}
				},
				{
					breakpoint: 768,
					settings: {
						slidesPerRow: 2,
						slidesToShow: 2,
					}
				}
			];

			jQuery(".cws-games-slick").each(function() {
				jQuery(this).slick({
					arrows: true,
					prevArrow: '<button class="slick-arrow-prev"><svg width="15" height="24" viewBox="0 0 15 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M14.489 12.8553L4.61427 22.7301C3.93175 23.4126 2.8281 23.4126 2.15284 22.7301L0.51189 21.0891C-0.17063 20.4066 -0.17063 19.303 0.51189 18.6277L7.51134 11.6282L0.51189 4.62879C-0.17063 3.94627 -0.17063 2.84262 0.51189 2.16736L2.14558 0.511889C2.8281 -0.17063 3.93175 -0.17063 4.60701 0.511889L14.4818 10.3866C15.1715 11.0692 15.1715 12.1728 14.489 12.8553Z" fill="black"/></svg></button>',
					nextArrow: '<button class="slick-arrow-next"><svg width="15" height="24" viewBox="0 0 15 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M14.489 12.8553L4.61427 22.7301C3.93175 23.4126 2.8281 23.4126 2.15284 22.7301L0.51189 21.0891C-0.17063 20.4066 -0.17063 19.303 0.51189 18.6277L7.51134 11.6282L0.51189 4.62879C-0.17063 3.94627 -0.17063 2.84262 0.51189 2.16736L2.14558 0.511889C2.8281 -0.17063 3.93175 -0.17063 4.60701 0.511889L14.4818 10.3866C15.1715 11.0692 15.1715 12.1728 14.489 12.8553Z" fill="black"/></svg></button>',
					dots: false,
					lazyLoad: "progressive",
					variableWidth: true,
					slidesPerRow: slidesPerRow,
					slidesToShow: slidesToShow,
					responsive: responsive
				});
			});
		}
	}

	updateNumber = function(element, number) {
		var current = parseFloat(jQuery(element).text().replace(/,/g, '')); // Remove thousands separator if present
	    var decimals = 2; // Number of decimal places
	    var decimalSeparator = '.'; // Decimal separator
	    var thousandsSeparator = ','; // Thousands separator

	    jQuery(element).prop('counter', current).animate({
	        counter: number
	    }, {
	        duration: 500,
	        step: function(now) {
	            var formattedNumber = now.toFixed(decimals).replace('.', decimalSeparator);
	            formattedNumber = formattedNumber.toString().replace(/\B(?=(\d{3})+(?!\d))/g, thousandsSeparator); // Add thousands separator
	            jQuery(this).text(formattedNumber);
	        },
	        complete: function() {

	        }
	    });
	};

	/* New Currency Switcher */
	jQuery(document).on("click", ".currency-switcher.new-layout .currency .icon", function(e) {
		e.preventDefault();
		e.stopPropagation();

		var wallet 		= jQuery(this).closest(".currency-switcher");
		var currency 	= jQuery(this).data("target");

		var width = jQuery(wallet).width();

		jQuery(wallet).find(".currency").removeClass("active");

		jQuery(wallet).find(".currency").css("z-index", 1);

		jQuery(wallet).find(".icons-wrapper .icon").removeClass("opacity");

		jQuery(wallet).find('.icons-wrapper .icon:not([data-target="'+currency+'"])').addClass("opacity");

		jQuery(wallet).find('[data-currency="'+currency+'"]').addClass("active").css("z-index", 2);

	});
	/* End New Currency Switcher */

	jQuery(document).on("click", ".load-more-button .elementor-button", function(e) {
		e.preventDefault();
		e.stopPropagation();

		var el 		= jQuery(this).closest(".elementor-widget-button");
		var loader 	= jQuery(el).find(".loader-overlay");
		var form 	= jQuery(el).find("form");

		var limit 	= 44;
		var isMobile = false;

		if (jQuery(window).width() <= 780) {
			isMobile = true;
		}

		if (isMobile) {
			limit = 44;
		}

		jQuery.ajax({
			url: cws_games_ajax_object.ajax_url,
			type: "POST",
			dataType: "html",
			data: {
        		action: 'cws_games_LoadMoreGames',
        		formdata: jQuery(form).serialize(),
        		loadmore_count: limit
        	},
			beforeSend: function() {
				jQuery(loader).show();
			},
			success: function(response) {

				if (response) {
					jQuery(el).replaceWith(jQuery(response));
				}
				
			},
    		error: function(error) {
				console.log('Something went wrong `cws_games_LoadMoreGames`');
			}
		}).done(function() {

	    });
	});

	jQuery(document).on("click", ".sound-toggle", function(e) {
		e.preventDefault();
		e.stopPropagation();

		jQuery.ajax({
			url: cws_games_ajax_object.ajax_url,
			type: "POST",
			dataType: "html",
			data: {
        		action: 'cws_games_soundToggle',
        		user_id: jQuery(this).data("user"),
        		sound: jQuery(this).data("sound")
        	},
			beforeSend: function() {

			},
			success: function(response) {

				if (response) {
					jQuery(document).find(".cws-games-sound").replaceWith(jQuery(response));
				}
				
			},
    		error: function(error) {
				console.log('Something went wrong `cws_games_soundToggle`');
			}
		}).done(function() {

	    });
	});

});