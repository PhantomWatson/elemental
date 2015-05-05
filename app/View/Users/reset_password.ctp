<div class="page-header">
	<h1>
		<?php echo $title_for_layout; ?>
	</h1>
</div>

<p>
	Enter a new password for <?php echo $user['User']['name']; ?>'s account:
</p>

<div class="form">
	<fieldset>
		<?php
			echo $this->Form->create('User', array('url' => array(
				'controller' => 'users',
				'action' => 'reset_password',
				$user_id,
				$reset_password_hash
			)));
			echo $this->Form->input('new_password', array(
				'label' => 'New Password',
				'type' => 'password',
				'autocomplete' => 'off',
				'class' => 'form-control',
				'div' => array('class' => 'form-group')
			));
			echo $this->Form->input('confirm_password', array(
				'label' => 'Confirm Password',
				'type' => 'password',
				'autocomplete' => 'off',
				'class' => 'form-control',
				'div' => array('class' => 'form-group')
			));
			echo $this->element('recaptcha');
			echo $this->Form->end(array(
				'label' => 'Reset password',
				'class' => 'btn btn-default'
			));
		?>
	</fieldset>
</div>