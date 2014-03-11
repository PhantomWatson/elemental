<h1 class="page_title">
	<?php echo $title_for_layout; ?>
</h1>

<p>
	If you have forgotten the password to your ElementalProtection.org account, you can enter your email address below (the same one that
	you used to register an account) and we'll send you an email with a link to reset your password.
	If you need assistance, please
	<?php echo $this->Html->link('contact us', array(
		'controller' => 'pages',
		'action' => 'contact'
	)); ?>.
</p>

<div class="form">
	<fieldset>
		<?php
			echo $this->Form->create('User', array(
				'controller' => 'users',
				'action' => 'forgot_password'
			));
			echo $this->Form->input('email', array(
				'label' => 'Email Address',
				'class' => 'form-control',
				'div' => array('class' => 'form-group')
			));
			echo $this->Form->end(array(
				'label' => 'Send password-resetting email',
				'class' => 'btn btn-default'
			));
		?>
	</fieldset>
</div>