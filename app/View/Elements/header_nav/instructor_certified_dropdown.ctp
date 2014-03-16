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
			<li>
				<?php echo $this->Html->link(
					'Schedule a course',
					array(
						'controller' => 'courses',
						'action' => 'add',
						$this->params['prefix'] => false
					)
				); ?>
			</li>
		</li>
		<li>
			<li>
				<?php echo $this->Html->link(
					'Manage courses',
					array(
						'controller' => 'courses',
						'action' => 'manage',
						$this->params['prefix'] => false
					)
				); ?>
			</li>
		</li>
		
		<li class="divider"></li>
		<li class="dropdown-header">
			Certification
		</li>
		<li>
			<a href="#">
				Check current status
			</a>
		</li>
		<li>
			<a href="#">
				Get recertified
			</a>
		</li>
		
		<li class="divider"></li>
		<li class="dropdown-header">
			Other
		</li>
		<li>
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
		</li>
	</ul>
</li>