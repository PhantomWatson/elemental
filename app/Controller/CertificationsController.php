<?php
App::uses('AppController', 'Controller');
/**
 * Certifications Controller
 *
 * @property Certification $Certification
 */
class CertificationsController extends AppController {

	public function beforeFilter() {
		parent::beforeFilter();
		$this->Auth->deny(array('instructor_index', 'admin_index'));
	}

	public function isAuthorized($user) {
		$user_id = $this->Auth->user('id');
		$this->loadModel('User');

		switch ($this->action) {
			case 'instructor_index':
				$is_instructor = $this->User->hasRole($user_id, 'instructor');
				if ($is_instructor) return true;
				break;
		}

		// Admins can access everything
		return parent::isAuthorized($user);
	}

	public function instructor_index() {

	}

	public function admin_index() {

	}
}