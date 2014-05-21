<h1>
	<?php echo $title_for_layout; ?>
</h1>

<table class="table">
	<thead>
		<tr>
			<th>
				Quantity
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
				<?php echo $report['available']; ?>
			</td>
			<td>
				Available
			</td>
			<td>
				<?php echo $this->Html->link(
					'Purchase',
					array(
						'controller' => 'store',
						'action' => 'prepaid_student_review_module'
					),
					array(
						'class' => 'btn btn-default'
					)
				); ?>
			</td>
		</tr>

		<?php if (empty($report['pending'])): ?>
			<tr>
				<td>
					0
				</td>
				<td>
					Reserved for upcoming courses
				</td>
				<td>
					<?php if ($report['available']): ?>
						<?php echo $this->Html->link(
							'Schedule a free course',
							array(
								'controller' => 'courses',
								'action' => 'add'
							),
							array(
								'class' => 'btn btn-default'
							)
						); ?>
					<?php endif; ?>
				</td>
			</tr>
		<?php else: ?>
			<?php foreach ($report['pending'] as $course_id => $course): ?>
				<tr>
					<td>
						<?php echo $course['count']; ?>
					</td>
					<td>
						Reserved for course on <?php echo $course['start']; ?>
					</td>
					<td>
						<?php if (strtotime($course['end']) < time() && ! $course['attendance_reported']): ?>
							<?php echo $this->Html->link(
								'Report attendance',
								array(
									'controller' => 'courses',
									'action' => 'report_attendance',
									'id' => $course_id
								),
								array(
									'class' => 'btn btn-default'
								)
							); ?>
						<?php endif; ?>
					</td>
				</tr>
			<?php endforeach; ?>
		<?php endif; ?>

		<?php if (empty($report['pending'])): ?>
			<tr>
				<td>
					0
				</td>
				<td>
					Assigned to students
				</td>
				<td>
				</td>
			</tr>
		<?php else: ?>
			<?php foreach ($report['used'] as $course_id => $course): ?>
				<tr>
					<td>
						<?php echo $course['count']; ?>
					</td>
					<td>
						Assigned to students of the course on <?php echo $course['start']; ?>
					</td>
					<td>
					</td>
				</tr>
			<?php endforeach; ?>
		<?php endif; ?>
	</tbody>
</table>