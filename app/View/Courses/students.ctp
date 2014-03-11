<?php
	$spots_available = $course['Course']['max_participants'] - count($class_list);
?>
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
	<?php echo $this->Html->link(
		'<span class="glyphicon glyphicon-plus glyphicon-white"></span> Add Students',
		array(
			'action' => 'add_students',
			'id' => $course['Course']['id']
		),
		array(
			'escape' => false,
			'class' => 'btn btn-success'
		)
	); ?>
</p>

<?php if (empty($class_list) && empty($waiting_list)): ?>
	<p class="alert alert-info">
		No students are registered for this course yet.
	</p>
<?php else: ?>

	<?php if (! empty($class_list)): ?>
		<h2>
			Class List
		</h2>

		<?php if ($spots_available <= 0): ?>
			<p class="alert alert-info">
				This course is full.
			</p>
		<?php elseif (empty($waiting_list)): ?>
			<p class="alert alert-info">
				<?php echo $spots_available; ?> more <?php echo __n('student', 'students', $spots_available); ?> can be added to this course.
			</p>
		<?php endif; ?>

		<?php echo $this->element('courses/student_list', array(
			'list_label' => 'Class List',
			'list' => $class_list
		)); ?>
	<?php endif; ?>

	<?php if (! empty($waiting_list)): ?>
		<h2>
			Waiting List
		</h2>
		<?php echo $this->element('courses/student_list', array(
			'list_label' => 'Waiting List',
			'list' => $waiting_list
		)); ?>
	<?php endif; ?>

<?php endif; ?>