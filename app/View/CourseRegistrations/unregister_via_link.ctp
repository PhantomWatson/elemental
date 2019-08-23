<?php
/**
 * @var bool $confirmationNeeded
 * @var string $message
 * @var string $msg_class
 * @var string $title_for_layout
 */
?>
<div class="page-header">
	<h1>
		<?php echo $title_for_layout; ?>
	</h1>
</div>

<?php if (isset($confirmationNeeded)): ?>
	<p>
		We're sorry to hear that you need to cancel your registration. To confirm your cancellation, please click the button
		below.
	</p>
	<div class="text-center">
		<?php
			echo $this->Form->create();
			echo $this->Form->hidden('confirm', array('value' => 1));
			echo $this->Form->end(array(
				'label' => 'Confirm Cancellation',
				'class' => 'btn btn-primary',
				'div' => array('class' => 'form-group')
			));
		?>
	</div>
<?php else: ?>
	<p class="alert alert-<?php echo $msg_class; ?>">
		<?php echo $message; ?>
	</p>
<?php endif; ?>
