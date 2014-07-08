<li class="dropdown">
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
				'Prepaid Review Modules',
				array(
					'controller' => 'products',
					'action' => 'prepaid_review_modules',
					$this->params['prefix'] => false
				)
			); ?>
		</li>

		<li class="divider"></li>

		<li class="dropdown-header">
			Other
		</li>
		<li>
			<?php echo $this->Html->link(
				'Add a testimonial',
				array(
					'controller' => 'testimonials',
					'action' => 'add',
					$this->params['prefix'] => false
				)
			); ?>
		</li>
	</ul>
</li>