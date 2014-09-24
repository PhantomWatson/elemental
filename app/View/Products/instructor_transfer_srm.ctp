<div class="page-header">
	<h1>
		<?php echo $title_for_layout; ?>
	</h1>
</div>

<p>
	<?php echo $this->Html->link(
		'<span class="glyphicon glyphicon-arrow-left"></span> Back to Student Review Modules',
		array(
			'instructor' => true,
			'controller' => 'products',
			'action' => 'student_review_modules'
		),
		array(
			'escape' => false,
			'class' => 'btn btn-default'
		)
	); ?>
</p>

<?php if (! $available_count): ?>
	<p class="alert alert-danger">
		Sorry, you have no available pre-paid Student Review Modules.
	</p>
<?php elseif (empty($instructors)): ?>
	<p class="alert alert-danger">
		Sorry, we couldn't find any other instructor accounts for you to transfer your Student Review Modules to.
	</p>
<?php else: ?>
	<div class="jumbotron">
		<?php echo $this->Form->create(
			false,
			array(
				'id' => 'instructor_transfer_srm_form'
			)
		); ?>

		<p>
			Transfer
			<?php
				$range = range(1, $available_count);
				$options = array_combine($range, $range);
				echo $this->Form->input(
					'quantity',
					array(
						'type' => 'select',
						'options' => $options,
						'label' => false,
						'div' => false
					)
				);
			?>

			Student Review Modules to

			<?php echo $this->Form->input(
				'instructor_id',
				array(
					'label' => false,
					'div' => false
				)
			); ?>
		</p>

		<?php
			echo $this->Form->end(array(
				'label' => 'Transfer',
				'class' => 'btn btn-default'
			));
		?>
	</div>

	<?php $this->Js->buffer("
		$('#instructor_transfer_srm_form').submit(function (event) {
			if (! confirm('Are you sure you want to transfer ownership of these Student Review Modules?')) {
				return false;
			}
		});
	"); ?>
<?php endif; ?>