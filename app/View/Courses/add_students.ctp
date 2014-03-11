<div class="page-header">
	<h1>
		<?php echo $title_for_layout; ?>
	</h1>
</div>

<div class="alert alert-info">
	<p>
		This form will add the specified student to the course taking place 
		<strong>
			in
			<?php echo h($course['Course']['city']); ?>, <?php echo $course['Course']['state']; ?>
			on
			<?php echo date('F j, Y', strtotime($course['Course']['begins'])); ?>.
		</strong>
		The student will be sent an email with information about this course and, if they did not
		already create an account with the same email address, the randomly-generated password they
		can use to log in.
	</p>
	<p>
		Note: If the user already had an account, this will not overwrite their account information.
	</p>
</div>

<?php if ($class_full): ?>
	<p class="alert alert-danger">
		<strong>This class is currently full,</strong> so this student will be added to the waiting list.
	</p>
<?php endif; ?>

<fieldset>
	<?php
		echo $this->Form->create('User', array(
			'url' => array(
				'controller' => 'courses',
				'action' => 'add_students',
				'id' => $course_id
			)
		));
		echo $this->Form->input('name', array(
			'class' => 'form-control', 
			'div' => array('class' => 'form-group')
		));
		echo $this->Form->input('phone', array(
			'class' => 'form-control', 
			'div' => array('class' => 'form-group')
		));
		echo $this->Form->input('email', array(
			'class' => 'form-control', 
			'div' => array('class' => 'form-group')
		));
	?>
</fieldset>
<?php echo $this->Form->end(array(
	'label' => 'Submit',
	'class' => 'btn btn-default',
	'div' => array('class' => 'form-group')
)); ?>