<div id="front_page_course_list">
	<?php echo $this->Html->link(
		'View all &raquo;',
		array(
			'controller' => 'courses',
			'action' => 'index'
		),
		array(
			'class' => 'btn btn-default',
			'escape' => false
		)
	); ?>
	<h2>
		Upcoming Courses
	</h2>
	<?php if (empty($courses)): ?>
		<div class="alert alert-info">
			No courses are currently scheduled.
		</div>
	<?php else: ?>
		<p class="footnote">
			Click a date for more details about a course.
		</p>
		<table class="table">
			<tbody>
				<?php foreach ($courses as $course): ?>
					<tr>
						<td>
							<?php echo $this->Html->link(
								date('F j, Y', strtotime($course['CourseDate'][0]['date'])),
								array(
									'controller' => 'courses',
									'action' => 'view',
									'id' => $course['Course']['id']
								)
							); ?>
						</td>
						<td>
							<?php echo h($course['Course']['location']); ?>
							<br />
							<?php echo h($course['Course']['city']); ?>, <?php echo $course['Course']['state']; ?>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	<?php endif; ?>
</div>