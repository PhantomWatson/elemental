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
				<?php if ($report['available']): ?>
					[Buy more]
				<?php else: ?>
					[Buy review modules]
				<?php endif; ?>
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
						[Schedule a free course]
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
						<?php if (strtotime($course['end']) < time()): ?>
							[Report attendance]
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