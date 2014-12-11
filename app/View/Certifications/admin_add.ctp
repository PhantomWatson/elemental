<div class="page-header">
	<h1>
		<?php echo $title_for_layout; ?>
	</h1>
</div>

<p>
	<?php echo $this->Html->link(
		'<span class="glyphicon glyphicon-arrow-left"></span> Back to Certifications',
		array(
			'admin' => true,
			'controller' => 'certifications',
			'action' => 'index'
		),
		array(
			'escape' => false,
			'class' => 'btn btn-default'
		)
	); ?>
</p>

<?php if (empty($instructors)): ?>
	<p class="alert alert-info">
		No instructors found
	</p>
<?php else: ?>
	<?php
		echo $this->Form->create();
		echo $this->Form->input(
			'instructor_id',
			array(
				'class' => 'form-control',
				'div' => array('class' => 'form-group'),
				'empty' => true,
				'required' => true
			)
		);
		echo $this->Form->input(
			'date_granted',
			array(
				'between' => '<br />',
				'class' => 'form-control',
				'div' => array('class' => 'form-group form-inline'),
				'label' => 'Date Certification Granted',
				'minYear' => 2012,
				'maxYear' => date('Y')
			)
		);
		echo $this->Form->end(array(
			'label' => 'Grant Certification',
			'class' => 'btn btn-primary',
			'div' => array('class' => 'form-group')
		));
	?>
<?php endif; ?>