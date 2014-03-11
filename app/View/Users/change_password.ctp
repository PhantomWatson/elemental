<div class="page-header">
	<h1>
		<?php echo $title_for_layout; ?>
	</h1>
</div>

<p>
	<?php echo $this->Html->link(
		'<span class="glyphicon glyphicon-arrow-left glyphicon-white"></span> Back to Account',
		array(
			'action' => 'account'
		),
		array(
			'escape' => false,
			'class' => 'btn btn-primary'
		)
	); ?>
</p>

<div class="form">
	<fieldset>
		<?php
			echo $this->Form->create('User', array(
				'url' => array(
					'controller' => 'users',
					'action' => 'change_password'
				)
			));
			echo $this->Form->input('new_password', array(
				'label' => 'New Password',
				'type' => 'password',
				'value' => '',
				'class' => 'form-control',
				'div' => array('class' => 'form-group')
			));
			echo $this->Form->input('confirm_password', array(
				'type' => 'password',
				'value' => '',
				'class' => 'form-control',
				'div' => array('class' => 'form-group')
			));
		?>
	</fieldset>
	<?php echo $this->Form->end(array(
		'label' => 'Change Password',
		'class' => 'btn btn-default'
	)); ?>
</div>