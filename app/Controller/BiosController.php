<?php
App::uses('AppController', 'Controller');
/**
 * Bios Controller
 *
 * @property Bio $Bio
 */
class BiosController extends AppController {
	public $helpers = array('Tinymce');

	public function beforeFilter() {
		parent::beforeFilter();
		$this->Auth->deny(array('edit'));
	}

	public function isAuthorized($user) {
		$user_id = $this->Auth->user('id');
		$this->loadModel('User');
		$is_instructor = $this->User->hasRole($user_id, 'instructor');

		switch ($this->action) {
			case 'edit':
				if ($is_instructor) return true;
				break;
		}

		// Admins can access everything
		return parent::isAuthorized($user);
	}
}