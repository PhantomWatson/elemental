<div class="jumbotron">
	<h1>
		<?php echo $title_for_layout; ?>
	</h1>

	<p>
		<?php echo nl2br(h($product['Product']['description'])); ?>
	</p>

	<?php if (isset($expiration) && $expiration): ?>
		<?php if ($can_access): ?>
			<p class="alert alert-info">
				Your access to the student review module will expire on <?php echo date('F jS, Y', $expiration); ?>.
			</p>
		<?php else: ?>
			<p class="alert alert-danger">
				Your access to the student review module expired on <?php echo date('F jS, Y', $expiration); ?>.
			</p>
		<?php endif; ?>
	<?php endif; ?>

	<?php if ($can_access): ?>

		<object type="application/x-shockwave-flash" data="/student_review/vizi.swf" width="898" height="495" id="vizi" style="float: none; vertical-align:middle">
			<param name="movie" value="/student_review/vizi.swf" />
			<param name="quality" value="high" />
			<param name="bgcolor" value="#ffffff" />
			<param name="play" value="true" />
			<param name="loop" value="true" />
			<param name="wmode" value="window" />
			<param name="scale" value="showall" />
			<param name="menu" value="true" />
			<param name="devicefont" value="false" />
			<param name="salign" value="" />
			<param name="allowScriptAccess" value="sameDomain" />
			<a href="http://www.adobe.com/go/getflash">
				<img src="https://www.adobe.com/images/shared/download_buttons/get_flash_player.gif" alt="Get Adobe Flash player" />
			</a>
		</object>

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
				$cost = $product['Product']['cost'];
				echo $this->Html->link(
					'Renew access for $'.number_format($cost, 2),
					'#',
					array(
						'class' => 'btn btn-primary btn-large',
						'id' => 'purchase_student_review'
					)
				);
			?>
		</p>

		<?php
			$this->Html->script('https://checkout.stripe.com/checkout.js', array('inline' => false));
			$this->Html->script('purchase.js', array('inline' => false));
			$this->Html->script('main.js', array('inline' => false));
			$this->Js->buffer("
				studentReviewPurchase_student.init({
					button_selector: '#purchase_student_review',
					confirmation_message: 'Confirm payment of $".number_format($cost, 2)." for renewed Student Review Module access?',
					cost_dollars: $cost,
					description: 'Review Module access renewal (\${$cost})',
					key: '".Configure::read('Stripe.Public')."',
					post_data: {
						student_id: '$user_id'
					},
					post_url: '/purchases/complete_purchase/srm_student'
				});
			");
		?>
	<?php endif; ?>

</div>