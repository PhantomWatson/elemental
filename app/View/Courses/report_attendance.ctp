<div class="page-header">
	<h1>
		<?php echo $title_for_layout; ?>
		<br />
		<small>
			<?php echo date('F j, Y', strtotime($course['Course']['begins'])); ?>
			in
			<?php echo h($course['Course']['city']); ?>, <?php echo $course['Course']['state']; ?>
		</small>
	</h1>
</div>

<p>
	<?php echo $this->Html->link(
		'<span class="glyphicon glyphicon-arrow-left glyphicon-white"></span> Manage Courses',
		array(
			'action' => 'manage'
		),
		array(
			'escape' => false,
			'class' => 'btn btn-primary'
		)
	); ?>
</p>

<?php if (empty($class_list)): ?>
	<p class="alert alert-danger">
		No students are registered for this course.
	</p>
<?php else: ?>
	<?php if ($course['Course']['begins'] > date('Y-m-d')): ?>
		<p class="alert alert-danger">
			This course hasn't begun yet.
		</p>
	<?php endif; ?>

	<?php echo $this->Form->create(false, array('id' => 'report_attendance_form')); ?>
	<table class="table attendance">
		<thead>
			<tr>
				<th colspan="2">
					<?php echo $this->Form->input("select_all", array(
						'id' => 'select_all',
						'label' => 'Select all',
						'type' => 'checkbox',
						'value' => 1,
						'div' => false,
						'checked' => false
					)); ?>
					<?php $this->Js->buffer("
						$('#select_all').click(function() {
							$('table.attendance tbody input[type=\"checkbox\"]').prop('checked', $(this).prop('checked'));
						});
					"); ?>
				</th>
				<th>
					Attended
				</th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($class_list as $n => $student): ?>
				<?php $attended = $student['CourseRegistration']['attended']; ?>
				<tr class="<?php echo $attended ? 'alert-success' : 'alert-danger'; ?>">
					<td>
						<?php if (! $attended && $student['User']['id']): ?>
							<?php echo $this->Form->input("user_ids.$n", array(
								'label' => false,
								'type' => 'checkbox',
								'value' => $student['User']['id'],
								'div' => false,
								'hiddenField' => false
							)); ?>
						<?php endif; ?>
					</td>
					<td>
						<?php if (empty($student['User']['name'])): ?>
							<span class="label label-danger">
								Student account not found
							</span>
						<?php else: ?>
							<label for="user_ids<?php echo $n; ?>">
								<?php echo h($student['User']['name']); ?>
							</label>
						<?php endif; ?>
					</td>
					<td>
						<span class="glyphicon glyphicon-<?php echo $attended ? 'ok' : 'remove'; ?>"></span>
					</td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>

	<?php echo $this->Form->end(array(
		'label' => 'Mark as Attended',
		'class' => 'btn btn-default',
		'div' => array('class' => 'form-group')
	)); ?>

	<?php
		// Warn instructor if this course hasn't begun yet
		if ($course['Course']['begins'] > date('Y-m-d')) {
			$this->Js->buffer("
				$('#report_attendance_form').submit(function() {
					return confirm('This course hasn\'t begun yet. Are you sure you want to report its attendance?');
				});
			");
		}
	?>

<?php endif; ?>