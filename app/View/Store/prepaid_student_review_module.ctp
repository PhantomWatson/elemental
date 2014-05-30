<div class="page-header">
	<h1>
		<?php echo $title_for_layout; ?>
	</h1>
</div>

<?php if ($step == 'prep'): ?>

	<?php echo $this->element('psrm_explanation'); ?>

	<?php echo $this->Form->create('Purchase'); ?>

	<fieldset>
		<legend>
			Quantity
		</legend>

		<p>
			How many Prepaid Student Review Modules would you like to purchase?
		</p>
		<?php
			echo $this->Form->input(
				'quantity',
				array(
					'label' => false,
					'min' => 1,
					'step' => 1,
					'type' => 'number'
				)
			);
		?>
	</fieldset>

	<?php if (isset($instructors)): ?>
		<fieldset>
			<legend>
				Instructor
			</legend>
			<p>
				Which instructor will be teaching the free course(s) that these Prepaid Student Review Modules will be applied to?
			</p>

			<?php if (empty($instructors)): ?>
				<p class="alert alert-danger">
					No instructors were found. Please contact an administrator at
					<a href="mailto:<?php echo Configure::read('admin_email'); ?>"><?php echo Configure::read('admin_email'); ?></a>
					for assistance.
				</p>
			<?php else: ?>
				<?php echo $this->Form->input(
					'instructor_id',
					array(
						'empty' => '',
						'label' => false,
						'required' => true
					)
				); ?>
			<?php endif; ?>
		</fieldset>
	<?php endif; ?>

	<?php
		echo $this->Form->end(array(
			'label' => 'Continue',
			'class' => 'btn btn-default'
		));
	?>

<?php elseif ($step == 'purchase'): ?>
	<p>
		Purchase <?php echo $quantity; ?> prepaid Student Review <?php echo __n('Module', 'Modules', $quantity); ?>
		for instructor <?php echo $instructor_name; ?>
		at $<?php echo $cost; ?> each
		for a <strong>total of $<?php echo $total; ?></strong>?
	</p>
	<a href="#" class="btn btn-success" id="purchase_button">
		Purchase
	</a>
	<a href="javascript:history.back()" class="btn btn-default">
		Go back
	</a>
	<?php
		$this->Html->script(Configure::read('google_wallet_lib'), array('inline' => false));
		$this->Js->buffer("
			$('#purchase_button').click(function(event) {
				event.preventDefault();
				google.payments.inapp.buy({
					'jwt': '$jwt',
					'success' : function(purchaseAction) {
						window.location.href = '$redirect_url';
					},
					'failure' : function(purchaseActionError) {
						console.log('There was an error processing your payment: '+purchaseActionError.response.errorType);
					}
				});
			});
		");
	?>
<?php endif; ?>