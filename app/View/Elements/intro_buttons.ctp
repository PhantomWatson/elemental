<?php
	echo $this->Html->link(
		'<span class="glyphicon glyphicon-info-sign"></span> Learn More',
		array(
			'controller' => 'pages',
			'action' => 'about_intro'
		),
		array(
			'class' => 'btn btn-primary btn-block btn-large col-xs-12 col-sm-5',
			'escape' => false
		)
	);

	echo $this->Html->link(
		'<span class="glyphicon glyphicon-play"></span> Student Review Materials',
		array(
			'controller' => 'products',
			'action' => 'student_review'
		),
		array(
			'class' => 'btn btn-primary btn-block btn-large col-xs-12 col-sm-6',
			'escape' => false
		)
	);

	echo $this->Html->link(
		'<span class="glyphicon glyphicon-user"></span> Become an Instructor',
		array(
			'instructor' => true,
			'controller' => 'products',
			'action' => 'certification'
		),
		array(
			'class' => 'btn btn-primary btn-block btn-large col-xs-12 col-sm-5',
			'escape' => false
		)
	);

	echo $this->Html->link(
		'<span class="glyphicon glyphicon-road"></span> Bring Elemental to Your Campus',
		array(
			'controller' => 'pages',
			'action' => 'booking'
		),
		array(
			'class' => 'btn btn-primary btn-block btn-large col-xs-12 col-sm-6',
			'escape' => false
		)
	);