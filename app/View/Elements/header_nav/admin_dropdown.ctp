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

		<li class="divider"></li>
		<li class="dropdown-header">
			Users
		</li>
		<li>
			<?php echo $this->Html->link(
				'Manage Users',
				array(
					'admin' => true,
					'controller' => 'users',
					'action' => 'index'
				)
			); ?>
		</li>
		<li>
			<?php echo $this->Html->link(
				'Add a User',
				array(
					'controller' => 'users',
					'action' => 'add',
					'admin' => true
				)
			); ?>
		</li>
	</ul>
</li>