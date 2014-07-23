<?php
App::uses('AppController', 'Controller');
/**
 * InstructorAgreements Controller
 *
 * @property InstructorAgreement $InstructorAgreements
 */
class InstructorAgreementsController extends AppController {
	public function beforeFilter() {
		parent::beforeFilter();
		$this->Auth->deny();
	}

	public function isAuthorized($user = null) {
		if ($this->User->hasRole($user['id'], 'instructor')) {
			return true;
		}

		return parent::isAuthorized($user);
	}

	public function view() {
		$this->set('title_for_layout', 'Certified Elemental Instructor License Agreement');
	}
}