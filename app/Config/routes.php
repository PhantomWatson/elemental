<?php
	Router::connect('/', 			array('controller' => 'pages', 'action' => 'home'));
	Router::connect('/postback',	array('controller' => 'store', 'action' => 'postback'));
	Router::connect('/clear_cache',	array('controller' => 'pages', 'action' => 'clear_cache'));

	// Pages
	$actions = array('contact', 'terms', 'privacy', 'booking');
	foreach ($actions as $action) {
		Router::connect("/$action",	array('controller' => 'pages', 'action' => $action));
	}

	// About
	Router::connect("/about", array('controller' => 'pages', 'action' => 'about_intro'));
	Router::connect("/about/intro", array('controller' => 'pages', 'action' => 'about_intro'));
	Router::connect("/about/effectiveness", array('controller' => 'pages', 'action' => 'about_effectiveness'));
	Router::connect("/about/pedagogy", array('controller' => 'pages', 'action' => 'about_pedagogy'));
	Router::connect("/about/bios", array('controller' => 'pages', 'action' => 'about_bios'));

	// Users
	$actions = array('register', 'login', 'logout', 'account', 'change_password', 'forgot_password');
	foreach ($actions as $action) {
		Router::connect("/$action",	array('controller' => 'users', 'action' => $action));
	}
	$actions = array('edit', 'delete');
	foreach ($actions as $action) {
		Router::connect(
			"/users/$action/:id",
			array('controller' => 'users', 'action' => $action),
			array('id' => '[0-9]+', 'pass' => array('id'))
		);
	}
	Router::connect("/reset_password/*",	array('controller' => 'users', 'action' => 'reset_password'));


	$actions = array('edit', 'delete', 'approve');
	$controllers = array('articles', 'testimonials', 'courses');
	foreach ($actions as $action) {
		foreach ($controllers as $controller) {
			Router::connect(
				"/$controller/$action/:id",
				array('controller' => $controller, 'action' => $action),
				array('id' => '[0-9]+', 'pass' => array('id'))
			);
		}
	}

	Router::connect(
		"/article/:id",
		array('controller' => 'articles', 'action' => 'view'),
		array('id' => '[0-9]+', 'pass' => array('id'))
	);
	Router::connect(
		"/course/:id",
		array('controller' => 'courses', 'action' => 'view'),
		array('id' => '[0-9]+', 'pass' => array('id'))
	);
	Router::connect(
		"/register/:id",
		array('controller' => 'courses', 'action' => 'register'),
		array('id' => '[0-9]+', 'pass' => array('id'))
	);
	Router::connect(
		"/courses/students/:id",
		array('controller' => 'courses', 'action' => 'students'),
		array('id' => '[0-9]+', 'pass' => array('id'))
	);
	Router::connect(
		"/courses/attendance/:id",
		array('controller' => 'courses', 'action' => 'report_attendance'),
		array('id' => '[0-9]+', 'pass' => array('id'))
	);
	Router::connect(
		"/unregister/:id",
		array('controller' => 'course_registrations', 'action' => 'delete'),
		array('id' => '[0-9]+', 'pass' => array('id'))
	);
	Router::connect(
		"/unregister/:id/:hash",
		array('controller' => 'course_registrations', 'action' => 'unregister_via_link'),
		array('id' => '[0-9]+', 'pass' => array('id', 'hash'))
	);
	Router::connect(
		"/releases/submit",
		array('controller' => 'releases', 'action' => 'add')
	);
	Router::connect(
		"/releases/submit/:course_id",
		array('controller' => 'releases', 'action' => 'add'),
		array('course_id' => '[0-9]+', 'pass' => array('course_id'))
	);
	Router::connect(
		"/releases/edit/:course_id",
		array('controller' => 'releases', 'action' => 'edit'),
		array('course_id' => '[0-9]+', 'pass' => array('course_id'))
	);
	Router::connect(
		"/courses/complete_registration/:course_id",
		array('controller' => 'courses', 'action' => 'complete_registration'),
		array('course_id' => '[0-9]+', 'pass' => array('course_id'))
	);

	// Products
	Router::connect("/review_materials", array('controller' => 'products', 'action' => 'review_materials'));
	Router::connect("/review_materials/*", array('controller' => 'products', 'action' => 'route'));
	Router::connect("/vizi/review_materials/*", array('controller' => 'products', 'action' => 'route'));

	Router::connect("/instructor_training", array('controller' => 'products', 'action' => 'instructor_training'));
	Router::connect("/instructor_training/*", array('controller' => 'products', 'action' => 'route'));
	Router::connect("/vizi/instructor_training/*", array('controller' => 'products', 'action' => 'route'));

	Router::connect("/classroom_module", array('controller' => 'products', 'action' => 'classroom_module'));
	Router::connect("/classroom_module/*", array('controller' => 'products', 'action' => 'route'));
	Router::connect("/vizi/classroom_module/*", array('controller' => 'products', 'action' => 'route'));

	CakePlugin::routes();
	require CAKE . 'Config' . DS . 'routes.php';