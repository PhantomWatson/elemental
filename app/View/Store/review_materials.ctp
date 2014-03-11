<div class="page-header">
	<h1>
		<?php echo $title_for_layout; ?>
	</h1>
</div>

<p>
	<?php echo nl2br(h($product['Product']['description'])); ?>
</p>

<?php
	$this->Html->script(
		Configure::read('google_wallet_lib'),
		array('inline' => false)
	);
	/* example contents of purchaseAction in successHandler:
		{
		  "request": {
		    "name": "Piece of Cake",
		    "description": "Virtual chocolate cake to fill your virtual tummy",
		    "price": "10.50",
		    "currencyCode": "USD",
		    "sellerData": "user_id:1224245,offer_code:3098576987,affiliate:aksdfbovu9j"
		  },
		  "response": {
		    "orderId": "3485709183457474939449"
		  },
		  "jwt": "eyJhbGciOiAiSFMyNTYiLCAidHlwIjogIkpXVCJ9.eyJleHAiOiAxMzA5OTkxNzY0LCAiYXVkIjogImdvb2cucGF5bWVudHMuaW5hcHAuYnV5SXRlbSIsICJpc3MiOiAiMTA4NzM2NjAzNTQ2MjAwOTQ3MTYiLCAic2VsbGVyRGF0YSI6ICJfc2VsbGVyX2RhdGFfIiwgIml0ZW1EZXNjcmlwdGlvbiI6ICJUaGUgc2FmZXRpZXN0IHdheSB0byBkaXNwbGF5IHlvdXIgZmxhaXIiLCAiaXRlbVByaWNlIjogIjMuOTkiLCAiaXNvQ3VycmVuY3lDb2RlIjogIlVTRCIsICJpYXQiOiAxMzA5OTkxMTY0LCAiaXRlbU5hbWUiOiAiU2FmZXR5bW91c2UgUGF0Y2gifQ.E1VH0T9DvQn4GdCjyVavnlurpx0iklQXlqeI1_tAMa8"
		}
		*/
	
		/* example contents of purchaseActionError in failureHandler:
		{
		  "request": {
		    "name": "Piece of Cake",
		    "description": "Virtual chocolate cake to fill your virtual tummy",
		    "price": "10.50",
		    "currencyCode": "USD",
		    "sellerData": "user_id:1224245,offer_code:3098576987,affiliate:aksdfbovu9j"
		  },
		  "response": {
		    "errorType": "PURCHASE_CANCELLED"
		  }
		}
		Types of errors:
		MERCHANT_ERROR - purchase request contains errors such as a badly formatted JWT
		PURCHASE_CANCELLED - buyer cancelled purchase or declined payment
		POSTBACK_ERROR - failure to acknowledge postback notification
		INTERNAL_SERVER_ERROR - internal Google error
		*/
	$this->Js->buffer("
		var successHandler = function(purchaseAction) {
			if (window.console != undefined) {
				console.log('Purchase completed successfully.');
			}	
		};
	
		var failureHandler = function(purchaseActionError){
			if (window.console != undefined) {
				console.log('Purchase did not complete.');
			}
		};
		
		var generatedJwt = '$jwt';
	
		$('#buy_button').click(function(event) {
			event.preventDefault();
			google.payments.inapp.buy({
				'jwt': generatedJwt,
				'success' : successHandler,
				'failure' : failureHandler
			});
		});
	");
?>

<script>
	
</script>

<a href="#" id="buy_button">
	Buy
</a>