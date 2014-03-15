<div class="page-header">
	<h1>
		<?php echo $title_for_layout; ?>
	</h1>
</div>

<div class="users form">
	<?php echo $this->Form->create('User');?>
	<?php
		echo $this->Form->input('role', array(
			'options' => $roles
		));
		echo $this->Form->input('name');
		echo $this->Form->input('email');
		echo $this->Form->input('phone');
		echo $this->Form->input('password');
	?>
	<?php echo $this->Form->end(__('Submit'));?>
</div>
