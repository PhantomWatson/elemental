<div class="page-header">
	<h1>
		<?php echo $title_for_layout; ?>
	</h1>
</div>

<?php if (empty($payments)): ?>
	<p class="alert alert-info">
		No records of class registration payments were found in the database.
	</p>
<?php else: ?>
	<table class="table">
		<thead>
			<tr>
				<th>
					Course
				</th>
				<th>
					Student
				</th>
				<th>
					Attended
				</th>
				<th>
					Payment Date
				</th>
				<th>
					Refund
				</th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($payments as $payment): ?>
				<tr>
					<td>
						<?php echo $payment['Course']['begins']; ?> in <?php echo $payment['Course']['city']; ?>, <?php echo $payment['Course']['state']; ?>
					</td>
					<td>
						<?php echo $payment['User']['name']; ?>: <?php echo $payment['User']['email']; ?> (#<?php echo $payment['User']['id']; ?>)
					</td>
					<td>
						Attended?
					</td>
					<td>
						<?php echo $payment['CoursePayment']['created']; ?>
					</td>
					<td>
						<?php if ($payment['CoursePayment']['refunded']): ?>
							Refunded on <?php echo $payment['CoursePayment']['refunded']; ?>
						<?php else: ?>
							<?php echo $this->Html->link(
								'Refund',
								array(
									'admin' => true,
									'controller' => 'course_payments',
									'action' => 'refund',
									$payment['CoursePayment']['id']
								),
								array(
									'class' => 'btn btn-default'
								),
								'Are you sure you want to issue a full refund for this payment?'
							); ?>
						<?php endif; ?>
					</td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
<?php endif; ?>