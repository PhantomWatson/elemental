var elementalPurchase = {	 
	// params must contain key, post_data, and post_url
	getStripeHandler: function (params) {
		return StripeCheckout.configure({
			key: params.key,
			image: 'http://elementalprotection.org/img/star-256px-whitebg.png',
			panelLabel: 'Continue (Total: {{amount}})',
			token: function(token) {
				var modal = $('#confirmation_modal');
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
								modal.modal('hide');
								location.reload(true);
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
	}
};