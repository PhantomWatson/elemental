<div class="page-header">
	<h1>
		<?php echo $title_for_layout; ?>
	</h1>
</div>

<p>
	Note that refunds take <a href="https://support.stripe.com/questions/how-long-do-refunds-take-to-reach-my-customer">5-10 business days</a> to be received.
</p>

<p>
	<?php
		$selected_filter = isset($this->request->named['filter']) ? $this->request->named['filter'] : null;
		echo $this->Html->link(
			'All',
			array(
				'filter' => null
			),
			array(
				'class' => 'btn btn-'.(! $selected_filter ? 'primary' : 'default')
			)
		);
		$buttons = array(
			'Refundable' => 'refundable',
			'Refunded' => 'refunded'
		);
		foreach ($buttons as $label => $filter) {
			echo $this->Html->link(
				$label,
				compact('filter'),
				array(
					'class' => 'btn btn-'.($selected_filter == $filter ? 'primary' : 'default')
				)
			);
		}
	?>
</p>

<?php if (empty($payments)): ?>
	<p class="alert alert-info">
		No results were found.
	</p>
<?php else: ?>

	<p>
		<?php echo $this->element('pagination'); ?>
	</p>

	<table class="table" id="refunds">
		<thead>
			<tr>
				<th>
					<?php echo $this->Paginator->sort('Course.begins', 'Course'); ?>
				</th>
				<th>
					<?php echo $this->Paginator->sort('User.name', 'Student'); ?>
				</th>
				<th>
					Withdrew
				</th>
				<th>
					Attended
				</th>
				<th>
					<?php echo $this->Paginator->sort('CoursePayment.created', 'Payment Date'); ?>
				</th>
				<th>
					<?php echo $this->Paginator->sort('CoursePayment.refunded', 'Refund'); ?>
				</th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($payments as $payment): ?>
				<tr>
					<td>
						<?php if (empty($payment['Course'])): ?>
							N/A
						<?php else: ?>
							<?php
								$timestamp = strtotime($payment['Course']['begins']);
								echo date('F j, Y', $timestamp);
							?>
							<br />
							<?php echo $payment['Course']['city']; ?>, <?php echo $payment['Course']['state']; ?>
						<?php endif; ?>
					</td>
					<td>
						<?php if (empty($payment['User'])): ?>
							N/A
						<?php else: ?>
							<?php echo $payment['User']['name']; ?> (#<?php echo $payment['User']['id']; ?>)
							<br />
							<a href="mailto:<?php echo $payment['User']['email']; ?>">
								<?php echo $payment['User']['email']; ?>
							</a>
						<?php endif; ?>
					</td>
					<td>
						<?php if ($payment['CourseRegistration']['id']): ?>
							<span class="glyphicon glyphicon-remove-sign text-warning" title="Has not withdrawn"></span>
						<?php else: ?>
							<span class="glyphicon glyphicon-ok-sign text-success" title="Withdrew"></span>
						<?php endif; ?>
					</td>
					<td>
						<?php if ($payment['CourseRegistration']['id'] && $payment['CourseRegistration']['attended']): ?>
							<span class="glyphicon glyphicon-ok-sign text-success" title="Attended"></span>
						<?php else: ?>
							<span class="glyphicon glyphicon-remove-sign text-warning" title="Has not attended"></span>
						<?php endif; ?>
					</td>
					<td>
						<?php $timestamp = strtotime($payment['CoursePayment']['created']); ?>
						<span title="<?php echo date('h:ia', $timestamp); ?>">
							<?php echo date('F j, Y', $timestamp); ?>
						</span>
					</td>
					<td>
						<?php if ($payment['CoursePayment']['refunded']): ?>
							<?php $timestamp = strtotime($payment['CoursePayment']['refunded']); ?>
							<span class="text-success" title="<?php echo date('h:ia', $timestamp); ?>">
								<?php echo 'Refunded on '.date('F j, Y', $timestamp); ?>
							</span>
						<?php else: ?>
							<?php if ($payment['CoursePayment']['jwt']): ?>
								<span class="text-warning">
									Refund via Google Wallet
								</span>
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
						<?php endif; ?>
					</td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>

	<?php echo $this->element('pagination'); ?>

<?php endif; ?>