<div class="page-header">
	<h1>
		<?php echo $title_for_layout; ?>
	</h1>
</div>

<p>
	Students who have attended an Elemental course are invited to share personal testimonials about their experiences.
</p>

<?php if ($is_staff || ($is_student && $has_attended)): ?>
	<div class="testimonials form">
		<?php
			echo $this->Form->create('Testimonial');
			if ($this->request->params['action'] == 'edit') {
				echo $this->Form->input('id');
			}
		?>
		<fieldset>
			<?php
				echo $this->Form->input('author', array(
					'label' => $is_student ? 'Your name (optional)' : 'Student\'s name (optional)',
					'class' => 'form-control',
					'div' => array('class' => 'form-group')
				));
				echo $this->Form->input('body', array(
					'label' => $is_student ? 'Tell us about your experience with Elemental' : 'Testimonial',
					'class' => 'form-control',
					'div' => array('class' => 'form-group')
				));
			?>
		</fieldset>
		<?php echo $this->Form->end(array(
			'label' => 'Submit',
			'class' => 'btn btn-default'
		)); ?>
	</div>
<?php elseif (! $has_attended): ?>
	<p>
		<?php echo $this->Html->link(
			'Register for an Upcoming Course',
			array(
				'controller' => 'courses',
				'action' => 'index'
			),
			array(
				'class' => 'btn btn-primary btn-large'
			)
		); ?>
	</p>
<?php endif; ?>