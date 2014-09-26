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
				'Role',
				array(
					'between' => '<p class="footnote">Note: If a user\'s role is changed, they may need to log out and back in before the change takes effect.</p>',
					'class' => 'form-control',
					'div' => array(
						'class' => 'form-group roles_checkboxes'
					),
					'label' => 'Role(s)',
					'options' => $roles,
					'multiple' => 'checkbox'
				)
			);
		?>
		<div id="bio_fields_container">
			<?php echo $this->element('bios/form'); ?>
		</div>
	</fieldset>
	<?php echo $this->Form->end(array(
		'label' => 'Update',
		'class' => 'btn btn-default'
	)); ?>
</div>
<?php $this->Js->buffer("
	adminUserEditForm.init();
"); ?>