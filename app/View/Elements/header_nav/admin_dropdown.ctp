<li class="dropdown user_menu">
	<a href="#" class="dropdown-toggle" data-toggle="dropdown">
		Admin
		<b class="caret"></b>
	</a>
	<ul class="dropdown-menu" role="menu" aria-labelledby="dropdownMenu">
		<li class="dropdown-header">
			Manage Site Content
		</li>
		<li>
			<?php echo $this->Html->link(
				'Courses',
				array(
					'controller' => 'courses',
					'action' => 'manage',
					$this->params['prefix'] => false
				)
			); ?>
		</li>
		<li>
			<?php echo $this->Html->link(
				'Users',
				array(
					'admin' => true,
					'controller' => 'users',
					'action' => 'index'
				)
			); ?>
		</li>
		<li>
			<?php echo $this->Html->link(
				'Articles',
				array(
					'controller' => 'articles',
					'action' => 'manage',
					$this->params['prefix'] => false
				)
			); ?>
		</li>
		<li>
			<?php echo $this->Html->link(
				'Testimonials',
				array(
					'controller' => 'testimonials',
					'action' => 'manage',
					$this->params['prefix'] => false
				)
			); ?>
		</li>
		<li>
			<?php echo $this->Html->link(
				'Instructor Certifications',
				array(
					'admin' => true,
					'controller' => 'certifications',
					'action' => 'index'
				)
			); ?>
		</li>
		<li>
			<?php echo $this->Html->link(
				'Grant SRMs',
				array(
					'admin' => true,
					'controller' => 'products',
					'action' => 'student_review_modules'
				)
			); ?>
		</li>
		<li>
			<?php echo $this->Html->link(
				'SRM Distribution',
				array(
					'admin' => true,
					'controller' => 'student_review_modules',
					'action' => 'overview'
				)
			); ?>
		</li>
		<li>
			<?php echo $this->Html->link(
				'Registration Refunds',
				array(
					'admin' => true,
					'controller' => 'course_payments',
					'action' => 'index'
				)
			); ?>
		</li>
	</ul>
</li>