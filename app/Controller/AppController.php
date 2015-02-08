<?php
/**
 * Application level Controller
 *
 * This file is application-wide controller file. You can put all
 * application-wide controller-related methods here.
 *
 * PHP 5
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       app.Controller
 * @since         CakePHP(tm) v 0.2.9
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
App::uses('Controller', 'Controller');

/**
 * Application Controller
 *
 * Add your application-wide methods in the class below, your controllers
 * will inherit them.
 *
 * @package		app.Controller
 * @link		http://book.cakephp.org/2.0/en/controllers.html#the-app-controller
 */
class AppController extends Controller {
	public $components = array(
		'Security',
		'DebugKit.Toolbar',
		'Flash',
		'Session',
		'Auth' => array(
			'authenticate' => array(
				'Form' => array(
					'contain' => array(
						'Role'
					),
					'fields' => array('username' => 'email')
				)
			),
			'authorize' => array('Controller'),
			'authError' => 'Please log in to access that page'
		),
		'Cookie'
	);
	public $uses = array('User');
	public $maintenance_mode = true;

	public function isAuthorized($user = null) {
		// Admins can access everything
        if ($this->User->hasRole($user['id'], 'admin')) {
        	return true;
        }

        // Default deny
        return false;
    }

	/**
	 * Displays a Flash message and redirects back to home page for anyone other than admins
	 */
	private function __maintenanceModeBlock() {
		$this->loadModel('User');
		if ($this->User->hasRole($this->Auth->user('id'), 'admin')) {
			return;
		}

		$this->Session->delete('FlashMessage'); // Prevent these messages from stacking up
		$this->Flash->set('The website is currently undergoing an upgrade and some pages are temporarily inaccessible. Please check back later.');
		if ($this->request->params['controller'] == 'pages' && $this->request->params['action'] == 'home') {
			return;
		}

		$this->redirect(array(
			'admin' => false,
			'instructor' => false,
			'controller' => 'pages',
			'action' => 'home'
		));
	}

	public function beforeFilter() {
		$this->Auth->allow();

		$this->Security->blackHoleCallback = 'blackhole';

		if ($this->Auth->loggedIn()) {
			$this->Auth->authError = 'You are not authorized to access that location.';
		}

		// Using "rijndael" encryption because the default "cipher" type of encryption fails to decrypt when PHP has the Suhosin patch installed.
        // See: http://cakephp.lighthouseapp.com/projects/42648/tickets/471-securitycipher-function-cannot-decrypt
		$this->Cookie->type('rijndael');
		$this->Cookie->key = Configure::read('cookie_key');

		// Prevents cookies from being accessible in Javascript
		$this->Cookie->httpOnly = true;

    	// Log in with cookie
		if (! $this->Auth->loggedIn() && $this->Cookie->read('remember_me')) {
			$cookie = $this->Cookie->read('remember_me');
			if (isset($cookie['email']) && isset($cookie['password'])) {
				$user = $this->User->find('first', array(
					'conditions' => array(
						'User.email' => $cookie['email'],
						'User.password' => $cookie['password']
					),
					'fields' => array(
						'User.id',
						'User.name',
						'User.certified'
					),
					'contain' => array(
						'Role'
					)
				));

				$login_data = $user['User'];
				$login_data['Role'] = $user['Role'];
				$login_data['User']['certified'] = $user['User']['certified'];

				$login_successful = $this->Auth->login($login_data);
				if ($user && ! $login_successful) {
					$this->redirect(array(
						'controller' => 'users',
						'action' => 'logout'
					));
				}
			}
		}

		if ($this->maintenance_mode) {
			$this->__maintenanceModeBlock();
		}
	}

	public function beforeRender() {
		if ($this->layout == 'default') {
			$user_roles = $this->__getUserRoles();
			$user_id = $this->Auth->user('id');
			$this->set(array(
				'user_roles' => $user_roles,
				'certified' => in_array('instructor', $user_roles) && $this->User->isCertified($user_id)
			));
			if ($this->Auth->loggedIn()) {
				$this->__setAlerts();
			}
		}
	}

	protected function __setAlerts() {
		// Remember these alerts for at most one hour
		$recheck = ! $this->Cookie->check('alerts_last_checked') || $this->Cookie->read('alerts_last_checked') < strtotime('1 hour ago');
		if ($recheck) {
			$this->Cookie->delete('alerts');
			$user_roles = $this->__getUserRoles();
			if (in_array('instructor', $user_roles)) {
				$this->__setInstructorAlerts();
			}
			if (in_array('admin', $user_roles)) {
				$this->__setAdminAlerts();
			}
			$this->Cookie->write('alerts_last_checked', time());
		}

		$this->set('alerts', $this->Cookie->read('alerts'));
	}

	protected function __setInstructorAlerts() {
		$instructor_id = $this->Auth->user('id');
		$this->loadModel('Course');

		$course_id = $this->Course->instructorCanReportAttendance($instructor_id);
		if ($course_id) {
			$url = Router::url(array(
				'controller' => 'courses',
				'action' => 'report_attendance',
				'id' => $course_id,
				$this->params['prefix'] => false
			));
			$this->Cookie->write('alerts.instructor_attendance', 'Please <strong><a href="'.$url.'">report attendance</a></strong> for your recent course.');
		}

		$this->loadModel('StudentReviewModule');
		if ($this->StudentReviewModule->paymentNeeded($instructor_id)) {
			$url = Router::url(array(
				'instructor' => true,
				'controller' => 'products',
				'action' => 'student_review_modules'
			));
			$this->Cookie->write('alerts.instructor_srm_payment', 'Please <strong><a href="'.$url.'">submit payment</a></strong> for the Student Review Modules used in your recent course.');
		}
	}

	public function __setAdminAlerts() {
		$this->Cookie->delete('alerts.admin');

		$this->loadModel('Testimonial');
		if ($this->Testimonial->approvalNeeded()) {
			$url = Router::url(array(
				'controller' => 'testimonials',
				'action' => 'manage',
				$this->params['prefix'] => false
			));
			$this->Cookie->write('alerts.admin_testimonials', 'Please <strong><a href="'.$url.'">approve or delete</a></strong> new testimonial(s).');
		}
	}

	/**
	 * Sets up everything that the Recaptcha plugin depends on
	 */
	protected function prepareRecaptcha() {
		$this->helpers[] = 'Recaptcha.Recaptcha';
    	$this->Components->load('Recaptcha.Recaptcha')->startup($this);
		Configure::load('Recaptcha.key');
	}

	protected function __getUserRoles() {
		$user_roles = array();
		$session_roles = $this->Auth->user('Role');
		if (! empty($session_roles)) {
			foreach ($session_roles as $session_role) {
				$user_roles[] = $session_role['name'];
			}
		}
		return $user_roles;
	}

	public function blackhole($type) {
		if ($type == 'secure') {
			$this->redirect('https://' . $_SERVER['SERVER_NAME'] . $this->here);
		}
	}
}