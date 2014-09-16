<li class="dropdown user_menu">
	<a href="#" class="dropdown-toggle" data-toggle="dropdown">
		Instructor Training
		<b class="caret"></b>
	</a>
	<ul class="dropdown-menu">
		<li>
			<?php echo $this->Html->link(
				'Training Lessons',
				array(
					'instructor' => true,
					'controller' => 'products',
					'action' => 'training'
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