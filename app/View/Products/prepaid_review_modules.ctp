<h1>
	<?php echo $title_for_layout; ?>
</h1>

<table class="table" id="psrm_report">
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
				<?php echo $report['available']; ?>
			</td>
			<td>
				<strong>
					Available
				</strong>
			</td>
			<td>
				<?php
					$label = 'Purchase';
					if ($report['available']) {
						$label .= ' more';
					}
					echo $this->Html->link(
						$label,
						array(
							'controller' => 'store',
							'action' => 'prepaid_student_review_module'
						),
						array(
							'class' => 'btn btn-default'
						)
					);
				?>
			</td>
		</tr>

		<tr>
			<td>
				<?php
					$total = 0;
					foreach ($report['pending'] as $course_id => $course) {
						$total += $course['count'];
					}
					echo $total;
				?>
			</td>
			<td>
				<strong>
					Reserved
				</strong>
				for upcoming courses
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

		<?php foreach ($report['pending'] as $course_id => $course): ?>
			<tr class="detail">
				<td>
				</td>
				<td>
					<?php echo $course['count']; ?>
					reserved for course on
					<?php echo $this->Html->link(
						$course['start'],
						array(
							'controller' => 'courses',
							'action' => 'view',
							'id' => $course_id
						)
					); ?>
					<?php if (strtotime($course['end']) < time() && ! $course['attendance_reported']): ?>
						<span class="label label-danger">Attendance not reported</span>
					<?php endif; ?>
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
			</td>
			<td>
			</td>
		</tr>

		<?php foreach ($report['used'] as $course_id => $course): ?>
			<tr class="detail">
				<td>
				</td>
				<td>
					<?php echo $course['count']; ?>
					assigned to students of the course on
					<?php echo $this->Html->link(
						$course['start'],
						array(
							'controller' => 'courses',
							'action' => 'view',
							'id' => $course_id
						)
					); ?>
				</td>
				<td>
				</td>
			</tr>
		<?php endforeach; ?>
	</tbody>
</table>