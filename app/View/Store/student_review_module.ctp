<div class="page-header">
	<h1>
		<?php echo $title_for_layout; ?>
	</h1>
</div>

<?php if ($step == 'prep'): ?>

	<?php echo $this->element('srm_explanation'); ?>

	<?php echo $this->Form->create('Purchase'); ?>

	<fieldset>
		<legend>
			Quantity
		</legend>

		<p>
			How many Student Review Modules would you like to purchase?
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
				Which instructor will be teaching the free course(s) that these Student Review Modules will be applied to?
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
		Purchase <?php echo $quantity; ?> Student Review <?php echo __n('Module', 'Modules', $quantity); ?>
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
		$this->Html->script('https://checkout.stripe.com/checkout.js', array('inline' => false));
		$this->Html->script('purchase.js', array('inline' => false));
		$this->Html->script('instructor.js', array('inline' => false));
		$purchase_noun = __n('SRM', 'SRMs', $quantity);
		$user_id_for_js = $user_id ? "'$user_id'" : 'null';
		$this->Js->buffer("
			studentReviewPurchase_instructor.init({
				button_selector: '#purchase_button',
				confirmation_message: 'Confirm payment of $".number_format($cost * $quantity, 2)." for $quantity $purchase_noun?',
				cost_dollars: ".($cost * $quantity).",
				description: 'Pay for $quantity $purchase_noun',
				key: '".Configure::read('Stripe.Public')."',
				post_data: {
					purchaser_id: $user_id_for_js,
					instructor_id: '".$this->request->data['Purchase']['instructor_id']."',
					quantity: '$quantity'
				},
				post_url: '/purchases/complete_purchase/srm_instructor',
				redirect_url: '$redirect_url'
			});
		");
	?>
<?php endif; ?>