<?php
	$spots = $course['Course']['max_participants'];
	$registered = count($course['CourseRegistration']);
	$spots_left = max($spots - $registered, 0);
	$deadline = date('F j, Y', strtotime($course['Course']['deadline']));
	$deadline_passed = $course['Course']['deadline'] < date('Y-m-d');
	$has_dates = ! empty($course['CourseDate']);
	if ($has_dates) {
		$last_key = count($course['CourseDate']) - 1;
		$class_has_ended = $course['CourseDate'][$last_key]['date'] < date('Y-m-d');
	}

	// Testing
	//$has_dates = false;
	//$class_has_ended = true;
	//$deadline_passed = true;
	//$spots_left = 0;

	$can_register = ! $registration_id && $has_dates && ! $deadline_passed;
?>

<div class="courses view view_course">
	<div class="jumbotron">
		<h1>
			<?php if (! $has_dates): ?>
				Sorry, this course has not been scheduled yet.
			<?php elseif ($class_has_ended): ?>
				This course has concluded.
			<?php elseif ($deadline_passed): ?>
				The deadline to register for this course has passed.
			<?php elseif ($spots_left): ?>
				<?php echo $spots_left.__n(' spot is', ' spots are', $spots_left); ?>
				left in this course.
			<?php else: ?>
				This course is full.
			<?php endif; ?>
		</h1>

		<?php if (! $class_has_ended && ($is_registered || $is_on_waiting_list)): ?>

			<p>
				 <?php echo $this->Html->link(
					$is_registered ? 'Registered' : 'On Waiting List',
					array(
						'controller' => 'courses',
						'action' => 'register',
						'id' => $course['Course']['id']
					),
					array(
						'class' => 'btn btn-primary'
					)
				); ?>
			</p>

		<?php elseif ($can_register): ?>

			<?php
				$action_button_label = $spots_left ? 'Register' : 'Join the Waiting List';
				$action_button_class = $spots_left ? 'register btn btn-primary' : 'waiting_list btn btn-warning';
			?>
			<p>
				<?php echo $this->Html->link(
					$action_button_label,
					array(
						'controller' => 'courses',
						'action' => 'register',
						'id' => $course['Course']['id']
					),
					array(
						'id' => 'course_action_button',
						'class' => $action_button_class
					)
				); ?>
				<span class="deadline">
					by <?php echo date('F j, Y', strtotime($course['Course']['deadline'])); ?>
				</span>
			</p>

		<?php endif; ?>
	</div>

	<table>
		<tbody>
			<?php if ($has_dates): ?>
				<tr>
					<th>When</th>
					<td class="when">
						<ul class="list-unstyled">
							<?php foreach ($course['CourseDate'] as $course_date): ?>
								<li>
									<span class="date">
										<?php echo date('l, F j, Y', strtotime($course_date['date'])); ?>
									</span>
									<span class="time">
										<?php echo date('g:ia', strtotime($course_date['start_time'])); ?>
									</span>
								</li>
							<?php endforeach; ?>
						</ul>
					</td>
				</tr>
			<?php endif; ?>
			<tr>
				<th>Where</th>
				<td>
					<p>
						<?php echo h($course['Course']['location']); ?>
						(<?php echo h($course['Course']['city']); ?>, <?php echo $course['Course']['state']; ?>)
					</p>
					<p>
						<?php echo nl2br(h($course['Course']['address'])); ?>
					</p>
				</td>
			</tr>
			<tr>
				<th>Cost</th>
				<td>
					<p>
						<?php if ($course['Course']['cost'] == 0): ?>
							Free
						<?php else: ?>
							$<?php echo $course['Course']['cost']; ?>
						<?php endif; ?>
					</p>
				</td>
			</tr>
			<?php if (! empty($course['Course']['details'])): ?>
				<tr>
					<th>Details</th>
					<td>
						<?php echo nl2br(h($course['Course']['details'])); ?>
					</td>
				</tr>
			<?php endif; ?>
			<?php if (! empty($course['User']['email'])): ?>
				<tr>
					<th>Contact</th>
					<td>
						<?php echo $this->Text->autoLinkEmails($course['User']['email']); ?>
					</td>
				</tr>
			<?php endif; ?>
		</tbody>
	</table>
</div>
