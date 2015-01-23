<h1>
	<?php echo $title_for_layout; ?>
</h1>

<p>
	<?php echo $this->Html->link(
		'View Student Review Module',
		array(
			'controller' => 'products',
			'action' => 'student_review',
			$this->params['prefix'] => false
		),
		array(
			'class' => 'btn btn-default'
		)
	); ?>
</p>

<?php echo $this->element('srm_explanation'); ?>

<h2>
	Your Modules:
</h2>

<table class="table" id="srm_report">
	<thead>
		<tr>
			<th>
				#
			</th>
			<th>
				Status
			</th>
			<th>
				Actions
			</th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td>
				<?php echo $report['prepaid_available']; ?>
			</td>
			<td>
				<strong>
					Prepaid
				</strong>
				and not assigned to students
			</td>
			<td>
				<?php
					echo $this->Html->link(
						'Purchase More',
						array(
							'instructor' => false,
							'controller' => 'store',
							'action' => 'student_review_module'
						),
						array(
							'class' => 'btn btn-default'
						)
					);
					if ($report['prepaid_available'] > 0) {
						echo $this->Html->link(
							'Transfer',
							array(
								'instructor' => true,
								'controller' => 'products',
								'action' => 'transfer_srm'
							),
							array(
								'class' => 'btn btn-default'
							)
						);
					}
				?>
			</td>
		</tr>

		<tr>
			<td>
				<?php
					$total = 0;
					foreach ($report['used'] as $course_id => $course) {
						$total += $course['count'];
					}
					echo $total;
				?>
			</td>
			<td>
				<strong>
					Assigned
				</strong>
				to students

				<?php if (! empty($report['used'])): ?>
					<a href="#" class="details_toggler btn btn-default btn-xs">
						Details
					</a>
					<ul class="details">
						<?php
							foreach ($report['used'] as $course_id => $course) {
								echo '<li>'.$course['count'].' assigned to ';
								echo __n('a student', 'students', $course['count']);
								echo ' of the course on ';
								echo $this->Html->link(
									$course['start'],
									array(
										'instructor' => false,
										'controller' => 'courses',
										'action' => 'view',
										'id' => $course_id
									)
								);
								echo '</li>';
							}
						?>
					</ul>
				<?php endif; ?>
			</td>
			<td>
			</td>
		</tr>

		<?php
			$unpaid_total = 0;
			foreach ($report['unpaid'] as $course_id => $course) {
				$unpaid_total += $course['count'];
			}
		?>
		<?php if ($unpaid_total): ?>
			<tr class="unpaid">
		<?php else: ?>
			<tr>
		<?php endif; ?>
			<td>
				<?php echo $unpaid_total; ?>
			</td>
			<td>
				<strong>
					Awaiting Payment
				</strong>

				<?php if (! empty($report['used'])): ?>
					<a href="#" class="details_toggler btn btn-default btn-xs">
						Details
					</a>
					<ul class="details">
						<?php
							foreach ($report['unpaid'] as $course_id => $course) {
								echo '<li>'.$course['count'];
								echo __n(' module', ' modules', $course['count']);
								echo ' for the course on ';
								echo $this->Html->link(
									$course['start'],
									array(
										'instructor' => false,
										'controller' => 'courses',
										'action' => 'view',
										'id' => $course_id
									)
								);
								echo '</li>';
							}
						?>
					</ul>
				<?php endif; ?>
			</td>
			<td>
				<?php
					if ($unpaid_total > 0) {
						echo $this->Html->link(
							'Pay ($'.($cost*$unpaid_total).')',
							'#',
							array(
								'class' => 'btn btn-default',
								'id' => 'pay_outstanding'
							)
						);

						$this->Html->script('https://checkout.stripe.com/checkout.js', array('inline' => false));
						$this->Html->script('purchase.js', array('inline' => false));
						$this->Html->script('instructor.js', array('inline' => false));
						$purchase_noun = __n('SRM', 'SRMs', $unpaid_total);
						$this->Js->buffer("
							studentReviewPurchase_instructor.init({
								button_selector: '#pay_outstanding',
								confirmation_message: 'Confirm payment of $".number_format($cost * $unpaid_total, 2)." for $purchase_noun?',
								cost_dollars: ".($cost * $unpaid_total).",
								description: 'Pay for $unpaid_total $purchase_noun',
								key: '".Configure::read('Stripe.Public')."',
								post_data: {
									purchaser_id: '$user_id',
									instructor_id: '$user_id',
									quantity: '$unpaid_total'
								},
								post_url: '/purchases/complete_purchase/srm_instructor'
							});
						");
					}
				?>
			</td>
		</tr>
	</tbody>
</table>

<?php $this->Js->buffer("srm_overview.init();"); ?>