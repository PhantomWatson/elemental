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

	public function edit() {
		$user_id = $this->Auth->user('id');
		$existing_record = $this->Bio->getForUser($user_id);

		if ($this->request->is('post') || $this->request->is('put')) {
			$this->Bio->create($this->request->data);
			$this->Bio->set('user_id', $user_id);
			if ($existing_record) {
				$this->Bio->id = $existing_record['Bio']['id'];
			}
			if ($this->Bio->save()) {
				$this->Flash->success('Bio updated');
				$this->redirect(array(
					'action' => 'view',
					'user_id' => $user_id
				));
			} else {
				$this->Flash->error('There was an error updating your bio. Please try again or contact an administrator for assistance.');
			}
		} else {
			$this->request->data = $existing_record;
		}
		$this->set(array(
			'title_for_layout' => 'Update Instructor Bio'
		));
	}

	/**
	 * Instructors bio page
	 */
	public function index() {
		$this->set(array(
			'title_for_layout' => 'Certified Elemental Instructors',
			'bios' => $this->Bio->getInstructorBios()
		));
	}
}