<li class="dropdown">
	<a href="#" class="dropdown-toggle" data-toggle="dropdown">
		Student
		<b class="caret"></b>
	</a>
	<ul class="dropdown-menu">
		<li>
			<?php echo $this->Html->link(
				'Write a testimonial',
				array(
					'controller' => 'testimonials',
					'action' => 'add',
					$this->params['prefix'] => false
				)
			); ?>
		</li>
		<li>
			<?php echo $this->Html->link(
				'Review materials',
				array(
					'controller' => 'products',
					'action' => 'review_materials',
					$this->params['prefix'] => false
				)
			); ?>
		</li>
	</ul>
</li>