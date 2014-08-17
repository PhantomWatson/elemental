<h1>
	<?php echo $title_for_layout; ?>
</h1>

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
					$label = 'Purchase';
					if ($report['prepaid_available']) {
						$label .= ' more';
					}
					echo $this->Html->link(
						$label,
						array(
							'controller' => 'store',
							'action' => 'student_review_module'
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
					<?php
						echo $course['count'];
						echo ' assigned to ';
						echo __n('a student', 'students', $course['count']);
						echo ' of the course on ';
						echo $this->Html->link(
							$course['start'],
							array(
								'controller' => 'courses',
								'action' => 'view',
								'id' => $course_id
							)
						);
					?>
				</td>
				<td>
				</td>
			</tr>
		<?php endforeach; ?>

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
				<?php

					echo $unpaid_total;
				?>
			</td>
			<td>
				<strong>
					Awaiting Payment
				</strong>
			</td>
			<td>
			</td>
		</tr>

		<?php foreach ($report['unpaid'] as $course_id => $course): ?>
			<tr class="detail">
				<td>
				</td>
				<td>
					<?php
						echo $course['count'];
						echo __n(' module', ' modules', $course['count']);
						echo ' for the course on ';
						echo $this->Html->link(
							$course['start'],
							array(
								'controller' => 'courses',
								'action' => 'view',
								'id' => $course_id
							)
						);
					?>
				</td>
				<td>
				</td>
			</tr>
		<?php endforeach; ?>
	</tbody>
</table>