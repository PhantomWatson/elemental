<div class="page-header">
	<h1>
		<?php echo $title_for_layout; ?>
	</h1>
</div>

<div class="users form">
	<?php
		echo $this->Form->create('User');
		echo $this->Form->input(
			'Role',
			array(
				'class' => 'form-control',
				'div' => array(
					'class' => 'form-group roles_checkboxes'
				),
				'label' => 'Role(s)',
				'options' => $roles,
				'multiple' => 'checkbox'
			)
		);
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
			'password',
			array(
				'class' => 'form-control',
				'div' => array('class' => 'form-group'),
				'type' => 'text'
			)
		);
		echo $this->Form->end(array(
			'label' => 'Add User',
			'class' => 'btn btn-default'
		));
	?>
</div>
