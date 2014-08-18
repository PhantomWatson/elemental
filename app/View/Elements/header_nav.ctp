<?php
	$logged_in = (boolean) $this->Session->read('Auth.User.id');
	$certified = $this->Session->read('Auth.User.certified');
	$primary_links = array(
		array(
			'label' => 'Home',
			'url' => array(
				'controller' => 'pages',
				'action' => 'home',
				$this->params['prefix'] => false
			)
		),
		array(
			'label' => 'Courses',
			'url' => array(
				'controller' => 'courses',
				'action' => 'index',
				$this->params['prefix'] => false
			)
		),
		array(
			'label' => 'Instructors',
			'url' => array(
				'controller' => 'bios',
				'action' => 'index',
				$this->params['prefix'] => false
			)
		),
		array(
			'label' => 'Contact',
			'url' => array(
				'controller' => 'pages',
				'action' => 'contact',
				$this->params['prefix'] => false
			)
		)
	);
?>


<nav class="navbar navbar-default navbar-inverse navbar-fixed-top" role="navigation">
	<div class="container">
		<div class="navbar-header">
			<button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#navbar-collapse-1">
				<span class="sr-only">Toggle navigation</span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
			</button>

			<a class="navbar-brand" href="/">
				<img src="/img/star.svg" />
				Elemental
			</a>
		</div>

		<div class="collapse navbar-collapse" id="navbar-collapse-1">
			<ul class="nav navbar-nav">
				<?php
					$current_controller = $this->request->params['controller'];
					$current_action = $this->request->params['action'];
					$current_page = $current_controller.'/'.$current_action;
				?>
				<?php foreach ($primary_links as $link): ?>
					<?php
						$linked_page = $link['url']['controller'].'/'.$link['url']['action'];
					?>
					<li class="<?php echo ($current_page == $linked_page) ? 'active' : ''; ?>">
						<?php echo $this->Html->link($link['label'], $link['url']); ?>
					</li>
				<?php endforeach; ?>

				<li class="dropdown <?php echo (stripos($current_action, 'about') === 0) ? 'active' : ''; ?>">
					<?php echo $this->Html->link(
						'About <b class="caret"></b>',
						array(
							'controller' => 'pages',
							'action' => 'about_intro',
							$this->params['prefix'] => false
						),
						array(
							'escape' => false,
							'class' => 'dropdown-toggle',
							'data-toggle' => 'dropdown'
						)
					); ?>
					<ul class="dropdown-menu">
						<li>
							<?php echo $this->Html->link(
								'Intro to Elemental',
								array(
									'controller' => 'pages',
									'action' => 'about_intro',
									$this->params['prefix'] => false
								)
							); ?>
						</li>
						<li>
							<?php echo $this->Html->link(
								'Pedagogy',
								array(
									'controller' => 'pages',
									'action' => 'about_pedagogy',
									$this->params['prefix'] => false
								)
							); ?>
						</li>
						<li>
							<?php echo $this->Html->link(
								'Effectiveness',
								array(
									'controller' => 'pages',
									'action' => 'about_effectiveness',
									$this->params['prefix'] => false
								)
							); ?>
						</li>
						<li>
							<?php echo $this->Html->link(
								'The People Behind Elemental',
								array(
									'controller' => 'pages',
									'action' => 'about_bios',
									$this->params['prefix'] => false
								)
							); ?>
						</li>
					</ul>
				</li>

				<?php if ($logged_in): ?>
					<?php if (in_array('admin', $user_roles)): ?>
						<?php echo $this->element('header_nav/admin_dropdown'); ?>
					<?php endif; ?>

					<?php if (in_array('instructor', $user_roles) && $certified): ?>
						<?php echo $this->element('header_nav/instructor_certified_dropdown'); ?>
					<?php elseif (in_array('instructor', $user_roles) && ! $certified): ?>
						<?php echo $this->element('header_nav/instructor_uncertified_dropdown'); ?>
					<?php endif; ?>

					<?php if (in_array('trainee', $user_roles)): ?>
						<?php echo $this->element('header_nav/instructor_in_training_dropdown'); ?>
					<?php endif; ?>

					<?php if (in_array('student', $user_roles)): ?>
						<?php echo $this->element('header_nav/student_dropdown'); ?>
					<?php endif; ?>

					<li class="<?php echo ($current_page == 'users/account') ? 'active' : ''; ?>">
						<?php echo $this->Html->link(
							'Account',
							array(
								'controller' => 'users',
								'action' => 'account',
								$this->params['prefix'] => false
							)
						); ?>
					</li>

					<li class="<?php echo ($current_page == 'users/logout') ? 'active' : ''; ?>">
						<?php echo $this->Html->link(
							'Logout',
							array(
								'controller' => 'users',
								'action' => 'logout',
								$this->params['prefix'] => false
							)
						); ?>
					</li>
				<?php else: ?>
					<li class="<?php echo ($current_page == 'users/login') ? 'active' : ''; ?>">
						<?php echo $this->Html->link(
							'Login',
							array(
								'controller' => 'users',
								'action' => 'login',
								$this->params['prefix'] => false
							)
						); ?>
					</li>
					<li class="<?php echo ($current_page == 'users/add') ? 'active' : ''; ?>">
						<?php echo $this->Html->link(
							'Create Account',
							array(
								'controller' => 'users',
								'action' => 'register',
								$this->params['prefix'] => false
							)
						); ?>
					</li>
				<?php endif; ?>

				<?php /* DROPDOWN MENU TEMPLATE
					<li class="dropdown">
						<a href="#" class="dropdown-toggle" data-toggle="dropdown">
							Dropdown
							<b class="caret"></b>
						</a>
						<ul class="dropdown-menu">
							<li>
								<a href="#">
									Action
								</a>
							</li>
							<li class="divider"></li>
							<li class="nav-header">
								Nav header
							</li>
							<li>
								<a href="#">
									Separated link
								</a>
							</li>
						</ul>
					</li>
				*/ ?>
			</ul>
		</div>
	</div>
</nav>