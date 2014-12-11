<div class="page-header">
	<h1>
		<?php echo $title_for_layout; ?>
	</h1>
</div>

<p class="alert alert-info">
	On this page, administrators can grant Student Review Modules to instructors for distribution to their students.
	This circumvents the online payment system, so is intended to be used after payment has been received for SRMs through other means.
</p>

<?php if (empty($instructors)): ?>
	<p class="alert alert-danger">
		Sorry, there doesn't appear to be any currently certified instructors to grant Student Review Modules to.
	</p>
<?php else: ?>
	<?php
		echo $this->Form->create(
			false,
			array(
				'id' => 'instructor_transfer_srm_form'
			)
		);
		echo $this->Form->input(
			'quantity',
			array(
				'class' => 'form-control',
				'div' => array(
					'class' => 'form-group'
				),
				'type' => 'number'
			)
		);
		echo $this->Form->input(
			'instructor_id',
			array(
				'class' => 'form-control',
				'div' => array(
					'class' => 'form-group'
				)
			)
		);
		echo $this->Form->end(array(
			'label' => 'Grant',
			'class' => 'btn btn-default'
		));
	?>
<?php endif; ?>