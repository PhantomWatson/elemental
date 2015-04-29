<div id="register">
	<div class="page-header">
		<h1>
			<?php echo $title_for_layout; ?>
		</h1>
		<?php
			$dates = array();
			foreach ($course['CourseDate'] as $k => $course_date) {
				$dates[] = date('F j, Y', strtotime($course_date['date']));
			}
			$list = $this->Text->toList($dates);
			if (count($dates) > 2) {
				// Serial commas are important, damn it
				$list = str_replace(' and', ', and', $list);
			}
			echo $list;
		?>
		in
		<?php echo h($course['Course']['city']); ?>, <?php echo h($course['Course']['state']); ?>
		<br />
		<?php echo $this->Html->link(
			'More Details',
			array(
				'controller' => 'courses',
				'action' => 'view',
				'id' => $course['Course']['id']
			)
		); ?>
	</div>

	<p class="<?php echo $intro_msg_class; ?>">
		<?php echo $intro_msg; ?>
	</p>

	<table cellpadding="0" cellspacing="0" class="table">
		<tbody>
			<tr>
				<td>
					<?php if ($release_submitted): ?>
						<span class="glyphicon glyphicon-ok"></span>
					<?php else: ?>
						<span class="glyphicon glyphicon-remove"></span>
					<?php endif; ?>
					Submit liability release
				</td>
				<td>
					<?php if ($release_submitted): ?>
						<?php echo $this->Html->link(
							'Edit',
							array(
								'controller' => 'releases',
								'action' => 'edit',
								'course_id' => $course['Course']['id']
							),
							array('class' => 'btn btn-default')
						); ?>
					<?php else: ?>
						<?php echo $this->Html->link(
							'Go',
							array(
								'controller' => 'releases',
								'action' => 'add',
								'course_id' => $course['Course']['id']
							),
							array('class' => 'btn btn-success')
						); ?>
					<?php endif; ?>
				</td>
			</tr>

			<?php if ($paid || (! $is_free && ! $is_full)): ?>
				<tr>
					<td>
						<?php if ($paid): ?>
							<span class="glyphicon glyphicon-ok"></span>
						<?php else: ?>
							<span class="glyphicon glyphicon-remove"></span>
						<?php endif; ?>
						Pay $<?php echo $course['Course']['cost']; ?> course fee
					</td>
					<td>
						<?php if (! $paid): ?>
							<?php if ($release_submitted): ?>
								<a href="#" class="btn btn-success" id="course_payment">
									Pay
								</a>
								<?php
									$this->Html->script('https://checkout.stripe.com/checkout.js', array('inline' => false));
									$this->Html->script('purchase.js', array('inline' => false));
									$course_id = $course['Course']['id'];
									$complete_reg_url = Router::url(
										array(
											'controller' => 'courses',
											'action' => 'complete_registration',
											'course_id' => $course_id
										),
										true
									);
									$cost = $course['Course']['cost'];
									$this->Js->buffer("
										elementalPurchase.setupPurchaseButton({
											button_selector: '#course_payment',
											confirmation_message: 'Confirm payment of \$$cost to register for this course?',
											cost_dollars: $cost,
											description: 'Course registration (\${$cost})',
											key: '".Configure::read('Stripe.Public')."',
											post_data: {
												student_id: '$user_id',
												course_id: '$course_id'
											},
											post_url: '/purchases/complete_purchase/course_registration',
											redirect_url: '$complete_reg_url',
											email: '$email'
										});
									");
								?>
							<?php else: ?>
								<button type="button" class="btn btn-default disabled">
									Actions pending
								</button>
							<?php endif; ?>
						<?php endif; ?>
					</td>
				</tr>
			<?php endif; ?>

			<tr>
				<td>
					<?php if (! $registration_completed || $can_elevate): ?>
						<span class="glyphicon glyphicon-remove"></span>
					<?php else: ?>
						<span class="glyphicon glyphicon-ok"></span>
					<?php endif; ?>
					<?php
						if ($is_full && ! $in_class) {
							echo 'Join waiting list';
						} else {
							echo 'Complete registration';
						}
					?>
				</td>
				<td>
					<?php
						if ($registration_completed && ! $can_elevate):
					?>
						<?php
							$label = $is_on_waiting_list ? 'Remove Self From Waiting List' : 'Cancel Registration';
							$confirmation = $is_on_waiting_list
								? 'Are you sure you want to remove yourself from this course\'s waiting list?'
								: 'Are you sure you want to cancel your registration to this course?';
							if (! $has_begun) {
								echo $this->Form->postLink(
									$label,
									array(
										'controller' => 'course_registrations',
										'action' => 'delete',
										'id' => $registration_id
									),
									array(
										'class' => 'btn btn-danger'
									),
									$confirmation
								);
							}
						?>
					<?php else: ?>
						<?php if ($actions_pending): ?>
							<button type="button" class="btn btn-default disabled">
								Actions pending
							</button>
						<?php else: ?>
							<?php
								$label = $is_full ? 'Join Waiting List' : 'Register';
								echo $this->Html->link(
									$label,
									array(
										'controller' => 'courses',
										'action' => 'complete_registration',
										'course_id' => $course['Course']['id']
									),
									array('class' => 'btn btn-success')
								);
							?>
						<?php endif; ?>
					<?php endif; ?>
				</td>
			</tr>
		</tbody>
	</table>
</div>