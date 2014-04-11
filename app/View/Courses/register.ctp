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

	<?php if (! $registration_completed && $is_full): ?>
		<div class="alert alert-danger">
			<strong>This course is full</strong>, but you can still add yourself to the waiting list. If you do, we'll contact you in the event that space becomes available.
		</div>
	<?php endif; ?>

	<p>
		Before you can register for this course, you must complete the following steps:
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
							array('class' => 'btn btn-info')
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

			<?php if (! $is_free): ?>
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
									$this->Html->script(Configure::read('google_wallet_lib'), array('inline' => false));
									$this->Js->buffer("
										$('#course_payment').click(function(event) {
											event.preventDefault();
											google.payments.inapp.buy({
												'jwt': '$jwt',
												'success' : function(purchaseAction) {
													alert('Payment received');
													location.reload();
												},
												'failure' : function(purchaseActionError){
													alert('There was an error processing your payment: '+purchaseActionError.response.errorType);
												}
											});
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
					<?php if ($registration_completed): ?>
						<span class="glyphicon glyphicon-ok"></span>
					<?php else: ?>
						<span class="glyphicon glyphicon-remove"></span>
					<?php endif; ?>
					Complete registration
				</td>
				<td>
					<?php if ($registration_completed): ?>
						<?php echo $this->Form->postLink(
							'Cancel Registration',
							array(
								'controller' => 'course_registrations',
								'action' => 'delete',
								'id' => $registration_id
							),
							array(
								'class' => 'btn btn-danger'
							),
							'Are you sure you want to cancel your registration to this course?'
						); ?>
					<?php else: ?>
						<?php if ($release_submitted && ($is_free || $paid)): ?>
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
						<?php else: ?>
							<button type="button" class="btn btn-default disabled">
								Actions pending
							</button>
						<?php endif; ?>
					<?php endif; ?>
				</td>
			</tr>
		</tbody>
	</table>
</div>