<li class="dropdown user_menu">
	<a href="#" class="dropdown-toggle" data-toggle="dropdown">
		Instructor
		<b class="caret"></b>
	</a>
	<ul class="dropdown-menu">
		<li class="dropdown-header">
			Courses
		</li>
		<li>
			<?php echo $this->Html->link(
				'Classroom Module',
				array(
					'controller' => 'products',
					'action' => 'classroom_module',
					$this->params['prefix'] => false
				)
			); ?>
		</li>
		<li>
			<?php echo $this->Html->link(
				'Schedule a Course',
				array(
					'controller' => 'courses',
					'action' => 'add',
					$this->params['prefix'] => false
				)
			); ?>
		</li>
		<li>
			<?php echo $this->Html->link(
				'Manage Courses',
				array(
					'controller' => 'courses',
					'action' => 'manage',
					$this->params['prefix'] => false
				)
			); ?>
		</li>
		<li>
			<?php echo $this->Html->link(
				'Student Review Modules',
				array(
					'instructor' => true,
					'controller' => 'products',
					'action' => 'student_review_modules'
				)
			); ?>
		</li>

		<li class="divider"></li>

		<li class="dropdown-header">
			Other
		</li>
		<li>
			<?php echo $this->Html->link(
				'Update Instructor Bio',
				array(
					'controller' => 'bios',
					'action' => 'edit',
					$this->params['prefix'] => false
				)
			); ?>
		</li>
		<li>
			<?php echo $this->Html->link(
				'Add a Testimonial',
				array(
					'controller' => 'testimonials',
					'action' => 'add',
					$this->params['prefix'] => false
				)
			); ?>
		</li>
		<li>
			<?php echo $this->Html->link(
				'Instructor Agreement',
				array(
					'controller' => 'instructor_agreements',
					'action' => 'view',
					$this->params['prefix'] => false
				)
			); ?>
		</li>
	</ul>
</li>