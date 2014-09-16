<li class="dropdown user_menu">
	<a href="#" class="dropdown-toggle" data-toggle="dropdown">
		Student
		<b class="caret"></b>
	</a>
	<ul class="dropdown-menu">
		<li>
			<?php echo $this->Html->link(
				'Write a Testimonial',
				array(
					'controller' => 'testimonials',
					'action' => 'add',
					$this->params['prefix'] => false
				)
			); ?>
		</li>
		<li>
			<?php echo $this->Html->link(
				'Review Materials',
				array(
					'controller' => 'products',
					'action' => 'student_review',
					$this->params['prefix'] => false
				)
			); ?>
		</li>
	</ul>
</li>