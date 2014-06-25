<div class="jumbotron">
	<h1>
		<?php echo $title_for_layout; ?>
	</h1>

	<p>
		<?php echo nl2br(h($product['Product']['description'])); ?>
	</p>

	<?php if ($can_access): ?>
		<p class="alert alert-info">
			Your access to the student review module will expire on <?php echo date('F jS, Y', $expiration); ?>.
		</p>
	<?php elseif (isset($expiration) && $expiration): ?>
		<p class="alert alert-danger">
			Your access to the student review module expired on <?php echo date('F jS, Y', $expiration); ?>.
		</p>
	<?php endif; ?>

	<?php if ($can_access): ?>

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

	<?php elseif (! $logged_in): ?>

		<p class="alert alert-info">
			Review materials are available to students after attending an Elemental course.
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

	<?php elseif (! $user_attended): ?>

		<p class="alert alert-info">
			Review materials are available to students after attending an Elemental course.
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

	<?php else: ?>

		<p>
			<?php
				echo $this->Html->link(
					'Renew access for $'.number_format($product['Product']['cost'], 2),
					'#',
					array(
						'class' => 'btn btn-primary btn-large',
						'id' => 'purchase_review_materials'
					)
				);
				$this->Html->script(Configure::read('google_wallet_lib'), array('inline' => false));
				$this->Js->buffer("
					$('#purchase_review_materials').click(function(event) {
						event.preventDefault();
						google.payments.inapp.buy({
							'jwt': '$jwt',
							'success' : function(purchaseAction) {
								location.reload(true);
							},
							'failure' : function(purchaseActionError){
								alert('There was an error processing your payment: '+purchaseActionError.response.errorType);
							}
						});
					});
				");
			?>
		</p>

	<?php endif; ?>

</div>