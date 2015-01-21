var studentReviewPurchase_instructor = {
	init: function (params) {
		var handler = elementalPurchase.getStripeHandler({
			key: params.key,
			post_data: {
				instructor_id: params.instructor_id,
				quantity: params.quantity
			},
			post_url: params.post_url
		});

		$('#pay_outstanding').on('click', function(e) {
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
	}
};