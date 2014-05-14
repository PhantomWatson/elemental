<div class="page-header">
	<h1>
		<?php echo $title_for_layout; ?>
	</h1>
</div>

<?php if ($step == 'prep'): ?>
	<p>
		For instructors to be able to create courses with no student registration fees, they must first have prepaid Elemental Student Review Modules for every student.
	</p>

	<ul>
		<li>
			<strong>Cost:</strong>
			$20 each
		</li>
		<li>
			<strong>Ownership:</strong>
			Prepaid Elemental Student Review Modules are each owned by a specific instructor who can use them to create free courses. If you need to transfer ownership to a different instructor, please
			<?php echo $this->Html->link(
				'contact us',
				array(
					'controller' => 'pages',
					'action' => 'contact'
				)
			); ?>.
		</li>
		<li>
			<strong>Access:</strong>
			Just like with paid courses, each student will be granted one year of access to the Student Review Module upon completing an Elemental course and having their attendance reported by an instructor.
		</li>
		<li>
			<strong>Recycling:</strong>
			If any Student Review Modules are designated for a course and then not assigned to any students (because a class didn't reach full capacity or students failed to attend), they will then be available to use in other free courses once attendance is reported.
		</li>
	</ul>

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
	Purchasetime!
<?php endif; ?>