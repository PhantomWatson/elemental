<div class="page-header">
	<h1>
		<?php echo $title_for_layout; ?>
	</h1>
</div>

<?php echo $this->Form->create(
	'Dummy',
	array(
		'url' => array(
			'controller' => 'pages',
			'action' => 'contact'
		)
	)
); ?>

<?php
	if (count($categories) > 1) {
		echo $this->Form->input(
			'category',
			array(
				'label' => 'Category',
				'options' => $categories
			)
		);
	}
?>

<?php echo $this->Form->input(
	'name',
	array(
		'default' => $this->Session->read('Auth.User.name'),
		'class' => 'form-control',
		'div' => array('class' => 'form-group')
	)
); ?>

<?php echo $this->Form->input(
	'email',
	array(
		'default' => $this->Session->read('Auth.User.email'),
		'class' => 'form-control',
		'div' => array('class' => 'form-group')
	)
); ?>

<?php echo $this->Form->input(
	'body',
	array(
		'label' => 'Message',
		'type' => 'textarea',
		'style' => 'width: 400px; height: 200px;',
		'class' => 'form-control',
		'div' => array('class' => 'form-group')
	)
); ?>

<?php echo $this->element('recaptcha', array('label' => false)); ?>

<?php echo $this->Form->submit(
	'Send',
	array(
		'class' => 'btn btn-default'
	)
); ?>

<?php echo $this->Form->end(); ?>
