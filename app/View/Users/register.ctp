<div class="page-header">
	<h1>
		<?php echo $title_for_layout; ?>
	</h1>
</div>

<?php /*
<p class="alert alert-info">
	With an account, you can register for upcoming courses, ...
</p>
*/ ?>

<div class="users form">
	<?php
		// Retain course_id (or anything else) from $_GET
		$url = (isset($_GET) && ! empty($_GET) ? array('?' => $_GET) : null); 
		echo $this->Form->create('User', array(
			'url' => $url
		));
	?>
	<fieldset>
		<?php
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
			echo $this->Form->input('password', array(
				'class' => 'form-control', 
				'div' => array('class' => 'form-group')
			));
		?>
		<div class="form-group">
			<?php echo $this->element('recaptcha'); ?>
		</div>
	</fieldset>
	<?php echo $this->Form->end(array(
		'label' => 'Submit',
		'class' => 'btn btn-default',
		'div' => array('class' => 'form-group')
	)); ?>
</div>