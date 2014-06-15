<div class="page-header">
	<h1>
		<?php echo $title_for_layout; ?>
	</h1>
</div>

<p>
	<?php echo $this->Html->link(
		'<span class="glyphicon glyphicon-arrow-left glyphicon-white"></span> Manage Users',
		array(
			'action' => 'manage'
		),
		array(
			'escape' => false,
			'class' => 'btn btn-primary'
		)
	); ?>
</p>

<div class="users form">
	<?php echo $this->Form->create('User');?>
	<fieldset>
		<?php
			echo $this->Form->input('id');
			echo $this->Form->input(
				'name',
				array(
					'class' => 'form-control',
					'div' => array('class' => 'form-group')
				)
			);
			echo $this->Form->input(
				'email',
				array(
					'class' => 'form-control',
					'div' => array('class' => 'form-group')
				)
			);
			echo $this->Form->input(
				'phone',
				array(
					'class' => 'form-control',
					'div' => array('class' => 'form-group')
				)
			);
			echo $this->Form->input(
				'role',
				array(
					'class' => 'form-control',
					'div' => array('class' => 'form-group'),
					'options' => $roles
				)
			);
		?>
	</fieldset>
	<?php echo $this->Form->end(array(
		'label' => 'Update',
		'class' => 'btn btn-default'
	)); ?>
</div>