<?php
	$this->Js->buffer("courseList.setup();");
?>

<div class="page-header">
	<h1>
		<?php echo $title_for_layout; ?>
	</h1>
</div>

<?php if (empty($courses)): ?>
	<div class="alert alert-info">
		No courses are currently scheduled. Please check back again later.
	</div>
<?php else: ?>
	<?php echo $this->element('pagination'); ?>

	<div class="courses index upcoming_courses">
		<table cellpadding="0" cellspacing="0">
			<tbody>
				<?php foreach ($courses as $course): ?>
					<tr <?php if ($course['deadline_passed']): ?>class="deadline_passed"<?php endif; ?>>
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
                                <?php echo h($course['Course']['location']); ?>
                                <br />
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
							<?php if (! $course['deadline_passed']): ?>
								<?php if ($course['spots_left']): ?>
									<span class="spots_left">
										<?php echo $course['spots_left'].__n(' spot', ' spots', $course['spots_left']); ?> left
									</span>
								<?php else: ?>
									<span class="class_full">
										Class is full
									</span>
								<?php endif; ?>
								<div class="progress progress-striped">
									<div class="progress-bar <?php echo $course['progress_bar_class']; ?>" style="width: <?php echo $course['percent_full']; ?>%;"></div>
								</div>
							<?php endif; ?>
						</td>
						<td class="actions">
							<?php if ($course['on_class_list']): ?>
								<p>
									<?php echo $this->Html->link(
										'Registered',
										array(
											'controller' => 'courses',
											'action' => 'register',
											'id' => $course['Course']['id']
										),
										array(
											'class' => 'btn btn-success'
										)
									); ?>
								</p>
							<?php elseif ($course['on_waiting_list']): ?>
								<p>
									<?php echo $this->Html->link(
										'On Waiting List',
										array(
											'controller' => 'courses',
											'action' => 'register',
											'id' => $course['Course']['id']
										),
										array(
											'class' => 'btn btn-success'
										)
									); ?>
								</p>
							<?php else: ?>
								<?php if (! $course['deadline_passed']): ?>
									<?php
										$label = $course['spots_left'] ? 'Register' : 'Join the Waiting List';
										echo $this->Html->link(
											$label,
											array(
												'controller' => 'courses',
												'action' => 'register',
												'id' => $course['Course']['id']
											),
											array(
												'class' => 'btn btn-primary'
											)
										);
									?>
								<?php endif; ?>
								<span class="deadline">
									<?php if ($course['deadline_passed']): ?>
										Registration deadline passed
										<br />
										<?php echo $course['deadline']; ?>
									<?php else: ?>
										by <?php echo $course['deadline']; ?>
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
<?php endif; ?>