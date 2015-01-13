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
				<img src="http://www.adobe.com/images/shared/download_buttons/get_flash_player.gif" alt="Get Adobe Flash player" />
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

		<div id="confirmation_modal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="Almost done!" aria-hidden="true">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
						<h4 class="modal-title">
							Almost done!
						</h4>
					</div>
					<div class="modal-body">
						<p>
							Confirm payment of $<?php echo number_format($cost, 2); ?> for renewed Student Review Module access?
						</p>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
						<button type="button" class="btn btn-primary">Confirm</button>
					</div>
				</div>
			</div>
		</div>

		<?php
			$this->Html->script('https://checkout.stripe.com/checkout.js', array('inline' => false));
			$this->Js->buffer("
				var handler = StripeCheckout.configure({
					key: '".Configure::read('Stripe.Public')."',
					image: 'http://elementalprotection.org/img/star-256px-whitebg.png',
					panelLabel: 'Continue (Total: {{amount}})',
					token: function(token) {
						$('#confirmation_modal').on('shown.bs.modal', function() {
							var initModalHeight = $('#confirmation_modal .modal-dialog').outerHeight();
							$('#confirmation_modal .modal-dialog').css('margin-top', (window.screenY / 2) + initModalHeight);
						});
						$('#confirmation_modal').modal();
						$('#confirmation_modal .btn-primary').click(function (event) {
							event.preventDefault();
							$('#confirmation_modal .btn').addClass('disabled');
							var hangon = $('<p class=\"hangon\" style=\"display: none;\">Please wait... <img src=\"/img/loading_small.gif\" /></p>');
							$('#confirmation_modal .modal-body').append(hangon);
							hangon.slideDown();
							var data = {
								student_id: ".$user_id.",
								token: token.id
							};
							$.ajax({
								type: 'POST',
								url: '/student_review_modules/complete_student_purchase',
								data: data,
								success: function (data, textStatus, jqXHR) {
									if (data.success) {
										$('#confirmation_modal').modal('hide');
										location.reload(true);
									} else {
										$('#confirmation_modal .btn-primary').remove();
										$('#confirmation_modal .btn').removeClass('disabled');
										$('#confirmation_modal .modal-title').html('Error');
										$('#confirmation_modal .modal-body').html(data.message);
									}
								},
								dataType: 'json'
							});
						});
					}
				});

				$('#purchase_student_review').on('click', function(e) {
					handler.open({
						name: 'Elemental',
						description: 'Review Module access renewal ($".$cost.")',
						amount: ".($cost * 100)."
					});
					e.preventDefault();
				});

				$(window).on('popstate', function() {
					handler.close();
				});
			");
		?>
	<?php endif; ?>

</div>