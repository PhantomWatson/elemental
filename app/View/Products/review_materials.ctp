<div class="jumbotron">
	<h1>
		<?php echo $title_for_layout; ?>
	</h1>
	<p>
		<?php echo nl2br(h($product['Product']['description'])); ?>
	</p>
	<?php if ($logged_in): ?>
		<?php if ($user_attended): ?>
			<?php if ($user_purchased): ?>
				<script language="javascript">AC_FL_RunContent = 0;</script>
				<script src="/review_materials/AC_RunActiveContent.js" language="javascript"></script>
				<p id="vizi_player">
					<script language="javascript">
						if (AC_FL_RunContent == 0) {
							alert("This page requires AC_RunActiveContent.js.");
						} else {
							AC_FL_RunContent( 'codebase','http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=9,0,0,0','width','898','height','495','align','left','id','ViziRECOVERED10-27-09','src','/review_materials/vizi','quality','high','bgcolor','#ffffff','name','ViziRECOVERED10-27-09','allowscriptaccess','sameDomain','allowfullscreen','false','pluginspage','http://www.macromedia.com/go/getflashplayer','movie','/review_materials/vizi' ); //end AC code
						}
					</script>
				</p>
				<noscript>
					<object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=9,0,0,0" width="898" height="495" align="left" id="ViziRECOVERED10-27-09">
						<param name="allowScriptAccess" value="sameDomain" />
						<param name="allowFullScreen" value="false" />
						<param name="movie" value="/review_materials/vizi.swf" />
						<param name="quality" value="high" />
						<param name="bgcolor" value="#ffffff" />
						<embed src="/review_materials/vizi.swf" width="898" height="495" align="left" quality="high" bgcolor="#ffffff" name="ViziRECOVERED10-27-09" allowscriptaccess="sameDomain" allowfullscreen="false" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer" />                        
					</object>
				</noscript>
				<map name="Map">
					<area shape="rect" coords="575,16,889,56" href="/">
				</map>
			<?php else: ?>
				<p>
					<?php
						echo $this->Html->link(
							'Purchase for $'.number_format($product['Product']['cost'], 2),
							array(
								'controller' => 'store',
								'action' => 'review_materials'
							),
							array(
								'class' => 'btn btn-primary btn-large',
								'id' => 'purchase_review_materials'
							)
						);
						$this->Html->script(Configure::read('google_wallet_lib'), array('inline' => false));
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
							$('#purchase_review_materials').click(function(event) {
								event.preventDefault();
								google.payments.inapp.buy({
									'jwt': '$jwt',
									'success' : function(purchaseAction) {
										if (window.console != undefined) {
											console.log('Purchase completed successfully.');
										}
									},
									'failure' : function(purchaseActionError){
										if (window.console != undefined) {
											console.log('Purchase did not complete.');
										}
									}
								});
							});
						");
					?>
				</p>
			<?php endif; ?>
		<?php endif; ?>
	<?php else: ?>
		<p class="alert alert-info">
			Review materials are available for purchase after attending an Elemental course.
		</p>
		<p>
			<?php echo $this->Html->link(
				'Log in',
				array(
					'controller' => 'users',
					'action' => 'login'
				),
				array(
					'class' => 'btn btn-primary btn-large'
				)
			); ?>

			<?php echo $this->Html->link(
				'Register for an Upcoming Course',
				array(
					'controller' => 'courses',
					'action' => 'index'
				),
				array(
					'class' => 'btn btn-primary btn-large'
				)
			); ?>
		</p>
	<?php endif; ?>
</div>

<?php if ($logged_in && ! $user_attended): ?>
	<p class="alert alert-info">
		Review materials are available for purchase after attending an Elemental course.
		Soon after a course concludes, the instructor will record your attendance and
		you will be granted access. If 48 hours have passed after a course that you attended
		and you still cannot access review materials, please contact your instructor.
	</p>
	<p>
		<?php echo $this->Html->link(
			'Register for an Upcoming Course',
			array(
				'controller' => 'courses',
				'action' => 'index'
			),
			array(
				'class' => 'btn btn-primary btn-large'
			)
		); ?>
	</p>
<?php endif; ?>