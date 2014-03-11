<div class="page-header">
	<h1>
		<?php echo $title_for_layout; ?>
	</h1>
</div>

<p>
	<?php echo $this->Html->link(
		'<span class="glyphicon glyphicon-plus glyphicon-white"></span> Schedule a New Course',
		array(
			'action' => 'add'
		),
		array(
			'escape' => false,
			'class' => 'btn btn-success'
		)
	); ?>
</p>

<?php if (empty($courses)): ?>
	<div class="alert alert-info">
		You have not scheduled any courses yet.
	</div>
<?php else: ?>
	<div class="courses index manage_courses">
		<table cellpadding="0" cellspacing="0" class="table">
			<tr>
				<?php if ($is_admin): ?>
					<th>
						Instructor
					</th>
				<?php endif; ?>
				<th>
					<?php echo $this->Paginator->sort('location'); ?>
				</th>
				<th>
					<?php echo $this->Paginator->sort('begins', 'Begins');?>
				</th>
				<th>
					Participants/Limit
				</th>
				<th class="actions">
					<?php echo 'Actions';?>
				</th>
			</tr>
			<?php foreach ($courses as $course): ?>
				<?php
					if ($course['Course']['begins'] == date('Y-m-d')) {
						echo '<tr class="today">';
					} elseif ($course['Course']['begins'] < date('Y-m-d')) {
						echo '<tr class="past">';
					} else {
						echo '<tr>';
					}
				?>
					<?php if ($is_admin): ?>
						<td>
							<?php echo $course['User']['name']; ?>
							<p class="contact_info">
								<a href="mailto:<?php echo $course['User']['email']; ?>">
									<?php echo $course['User']['email']; ?>
								</a>
								<?php if (! empty($course['User']['phone'])): ?>
									<br />
									<?php echo $course['User']['phone']; ?>
								<?php endif; ?>
							</p>
						</td>
					<?php endif; ?>
					<td>
						<?php echo h($course['Course']['location']); ?>
						<br />
						<?php echo h($course['Course']['city']); ?>, <?php echo h($course['Course']['state']); ?>
					</td>
					<td class="begins">
						<?php echo date('F j, Y', strtotime($course['Course']['begins'])); ?>
					</td>
					<td class="registered">
						<?php
							$class_list_count = 0;
							$waiting_list_count = 0;
							foreach ($course['CourseRegistration'] as $reg) {
								if ($reg['waiting_list']) {
									$waiting_list_count++;
								} else {
									$class_list_count++;
								}
							}
							echo $class_list_count;
						?>
						/
						<?php echo $course['Course']['max_participants']; ?>
						<?php if ($waiting_list_count): ?>
							<br />
							<span class="footnote">
								+ <?php echo $waiting_list_count; ?> on waiting list
							</span>
						<?php endif; ?>
					</td>
					<td class="actions">
						<div class="btn-group">
							<a class="btn btn-default dropdown-toggle" data-toggle="dropdown" href="#">
						    	Actions
								<span class="caret"></span>
							</a>
							<ul class="dropdown-menu">
						    	<li>
						    		<?php echo $this->Html->link(
										'View Course Details',
										array(
											'action' => 'view',
											'id' => $course['Course']['id']
										)
									); ?>
								</li>
								<li>
									<?php echo $this->Html->link(
										'Add Student',
										array(
											'action' => 'add_students',
											'id' => $course['Course']['id']
										)
									); ?>
								</li>
								<li>
									<?php echo $this->Html->link(
										'Manage Students',
										array(
											'action' => 'students',
											'id' => $course['Course']['id']
										)
									); ?>
								</li>
								<li>
									<?php echo $this->Html->link(
										'Report Attendance',
										array(
											'action' => 'report_attendance',
											'id' => $course['Course']['id']
										)
									);	?>
								</li>
								<li>
									<?php echo $this->Html->link(
										'Edit Course',
										array(
											'action' => 'edit',
											'id' => $course['Course']['id']
										)
									); ?>
								</li>
								<li>
									<?php
										$warning = 'Are you sure you want to cancel this course?';
										if ($class_list_count) {
											$warning .= ' '.$class_list_count.__n(' student is', ' students are', $class_list_count).' already registered for it and they will receive no notification that the course has been canceled.';
										}
										echo $this->Form->postLink(
											'Cancel Course',
											array(
												'action' => 'delete',
												'id' => $course['Course']['id']
											),
											null,
											$warning
										);
									?>
								</li>
							</ul>
						</div>
					</td>
				</tr>
			<?php endforeach; ?>
		</table>

		<?php echo $this->element('pagination'); ?>
	</div>
<?php endif; ?>