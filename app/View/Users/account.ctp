<div class="page-header">
	<h1>
		<?php echo $title_for_layout; ?>
	</h1>
</div>

<p>
	<?php echo $this->Html->link(
		'Change Password',
		array(
			'controller' => 'users',
			'action' => 'change_password'
		),
		array(
			'class' => 'btn btn-primary'
		)
	); ?>
</p>

<div class="form" id="account_form">
	<fieldset>
		<?php
			echo $this->Form->create('User', array(
				'url' => array(
					'controller' => 'users',
					'action' => 'account'
				)
			));
			echo $this->Form->input('name', array(
				'label' => 'Name',
				'class' => 'form-control',
				'div' => array('class' => 'form-group')
			));
			echo $this->Form->input('email', array(
				'label' => 'Email',
				'class' => 'form-control',
				'div' => array('class' => 'form-group')
			));
			echo $this->Form->input('phone', array(
				'label' => 'Phone Number',
				'class' => 'form-control',
				'div' => array('class' => 'form-group'),
				'type' => 'tel'
			));
		?>
	</fieldset>
	<?php echo $this->Form->end(array(
		'label' => 'Update Information',
		'class' => 'btn btn-default'
	)); ?>
</div>