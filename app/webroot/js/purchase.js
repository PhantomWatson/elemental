var elementalPurchase = {	 
	// params must contain key, post_data, and post_url
	getStripeHandler: function (params) {
		return StripeCheckout.configure({
			key: params.key,
			image: 'http://elementalprotection.org/img/star-256px-whitebg.png',
			panelLabel: 'Continue (Total: {{amount}})',
			token: function(token) {
				var modal = elementalPurchase.getConfirmationModal(params.confirmation_message);
				$('body').append(modal);
				modal.on('shown.bs.modal', function() {
					var dialog = modal.find('.modal-dialog');
					var initModalHeight = dialog.outerHeight();
					dialog.css('margin-top', (window.screenY / 2) + initModalHeight);
				});
				modal.modal();
				modal.find('.btn-primary').click(function (event) {
					event.preventDefault();
					modal.find('.btn').addClass('disabled');
					var hangon = $('<p class=\"hangon\" style=\"display: none;\">Please wait... <img src=\"/img/loading_small.gif\" /></p>');
					modal.find('.modal-body').append(hangon);
					hangon.slideDown();
					var data = params.post_data;
					data.token = token.id;
					$.ajax({
						type: 'POST',
						url: params.post_url,
						data: data,
						success: function (data, textStatus, jqXHR) {
							if (data.success) {
								// Redirect if redirect_url is provided, refresh otherwise
								if (params.hasOwnProperty('redirect_url')) {
									window.location.href = params.redirect_url;
								} else {
									location.reload(true);
								}
							} else {
								modal.find('.btn-primary').remove();
								modal.find('.btn').removeClass('disabled');
								modal.find('.modal-title').html('Error');
								modal.find('.modal-body').html(data.message);
							}
						},
						dataType: 'json'
					});
				});
			}
		});
	},
	
	setupPurchaseButton: function (params) {
		var handler = this.getStripeHandler(params);
		
		$(params.button_selector).on('click', function(e) {
			handler.open({
				name: 'Elemental',
				description: params.description,
				amount: params.cost_dollars * 100
			});
			e.preventDefault();
		});

		$(window).on('popstate', function() {
			handler.close();
		});
	},
	
	getConfirmationModal: function (confirmation_message) {
		return $(
			'<div class="modal fade" id="confirmation_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">'+
				'<div class="modal-dialog">'+
					'<div class="modal-content">'+
						'<div class="modal-header">'+
							'<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>'+
							'<h4 class="modal-title" id="myModalLabel">Almost done!</h4>'+
						'</div>'+
						'<div class="modal-body">'+
							confirmation_message+
						'</div>'+
						'<div class="modal-footer">'+
							'<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>'+
							'<button type="button" class="btn btn-primary">Complete Purchase</button>'+
						'</div>'+
					'</div>'+
				'</div>'+
			'</div>'
		);
	}
};