<?php
	$this->Js->buffer("courseList.setup();"); 
?>

<div class="page-header">
	<h1>
		<?php echo $title_for_layout; ?>
	</h1>
</div>

<?php echo $this->element('pagination'); ?>

<div class="courses index upcoming_courses">
	<table cellpadding="0" cellspacing="0">
		<tbody>
			<?php foreach ($courses as $course): ?>	
				<?php 
					$spots = $course['Course']['max_participants'];
					$registered = count($course['CourseRegistration']);
					$spots_left = max($spots - $registered, 0);
					$deadline = date('F j, Y', strtotime($course['Course']['deadline']));
					$deadline_passed = $course['Course']['deadline'] < date('Y-m-d');
					$percent_full = floor(($registered / $spots) * 100);
					if ($percent_full >= 75) {
						$progress_bar_class = 'progress-bar-danger';
					} elseif ($percent_full >= 50) {
						$progress_bar_class = 'progress-bar-warning';
					} else {
						$progress_bar_class = 'progress-bar-success';
					}
					$course_id = $course['Course']['id'];
					$reg_id = isset($courses_registered_for[$course_id]) ? $courses_registered_for[$course_id] : false;
				?>
				<tr <?php if ($deadline_passed): ?>class="deadline_passed"<?php endif; ?>>
					<td class="information">
						<ul class="list-unstyled dates">
							<?php foreach ($course['CourseDate'] as $k => $course_date): ?>
								<li>
									<?php echo date('F j, Y', strtotime($course_date['date'])); ?>
									<?php if ($k == 0 && count($course['CourseDate']) > 1): ?>
										<a href="#" class="more_dates" title="Click to show all dates for this class">
											...
										</a>
									<?php endif; ?>
								</li>
							<?php endforeach; ?>
						</ul>
						<span class="location">
							<?php echo h($course['Course']['city']); ?>, <?php echo h($course['Course']['state']); ?>
						</span>
						<?php echo $this->Html->link(
							'Details',
							array(
								'controller' => 'courses',
								'action' => 'view',
								'id' => $course['Course']['id']
							)
						); ?>
					</td>
					<td class="availability">
						<?php if (! $deadline_passed): ?>
							<?php if ($spots_left): ?>
								<span class="spots_left">
									<?php echo $spots_left.__n(' spot', ' spots', $spots_left); ?> left
								</span>
							<?php else: ?>
								<span class="class_full">
									Class is full
								</span>
							<?php endif; ?>
							<div class="progress progress-striped">
								<div class="progress-bar <?php echo $progress_bar_class; ?>" style="width: <?php echo $percent_full; ?>%;"></div>
							</div>
						<?php endif; ?>
					</td>
					<td class="actions">
						<?php if ($reg_id): ?>
							<p>
								<span class="">
									You are registered
								</span>
							</p>
							<p>
								<?php echo $this->Form->postLink(
									'Cancel Registration',
									array(
										'controller' => 'course_registrations',
										'action' => 'delete',
										'id' => $reg_id
									),
									array(
										'class' => 'btn btn-danger'
									),
									'Are you sure you want to cancel your registration to this course?'
								); 
								?>
							</p>
						<?php else: ?>
							<?php if (! $deadline_passed): ?>
								<?php echo $this->Html->link(
									'Register',
									array(
										'controller' => 'courses',
										'action' => 'view',
										'id' => $course['Course']['id']
									),
									array(
										'class' => $spots_left ? 'btn btn-primary' : 'btn btn-warning'
									)
								); ?>
							<?php endif; ?>
							<span class="deadline">
								<?php if ($deadline_passed): ?>
									Registration deadline passed
									<br />
									<?php echo $deadline; ?>
								<?php else: ?>
									by <?php echo $deadline; ?>
									<?php if (! $spots_left): ?>
										<br />
										to be added to the waiting list
									<?php endif; ?>
								<?php endif; ?>
							</span>
						<?php endif; ?>
					</td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
</div>
	
<?php echo $this->element('pagination'); ?>