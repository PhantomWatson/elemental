<div class="page-header">
	<h1>
		<?php echo $title_for_layout; ?>
	</h1>
</div>

<p>
	<?php echo $this->Html->link(
		'I forgot my password',
		array('action' => 'forgot_password'),
		array('class' => 'btn btn-primary')
	); ?>
</p>

<?php
	echo $this->Form->create('User', array(
		'action' => 'login',
		'inputDefaults' => array(
			'class' => 'form-control',
			'div' => array('class' => 'form-group')
		)
	));
	echo $this->Form->input('email');
	echo $this->Form->input('password');
	echo $this->Form->input('auto_login', array(
		'type' => 'checkbox',
		'label' => array('text' => ' Log me in automatically'),
		'checked' => true,
		'div' => array('class' => null),
		'class' => null
	));
	echo $this->Form->end(array(
		'label' => 'Login',
		'class' => 'btn btn-default'
	));
?>