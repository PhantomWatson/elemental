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
		$this->Auth->deny(array('edit', 'add'));
	}

	public function isAuthorized($user = null) {
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

	public function add() {
		$user_id = $this->Auth->user('id');
		$bio_id = $this->Bio->field('id', array(
			'Bio.user_id' => $user_id
		));
		if (! $bio_id) {
			$this->Bio->create(array(
				'user_id' => $user_id
			));
			$this->Bio->save();
		}
		$this->redirect(array(
			'action' => 'edit'
		));
	}

	public function edit() {
		$user_id = $this->Auth->user('id');
		$bio = $this->Bio->getForUser($user_id);
		if (! $bio) {
			$this->redirect(array(
				'action' => 'add'
			));
		}

		if ($this->request->is('post') || $this->request->is('put')) {
			$this->Bio->create($this->request->data);
			$this->Bio->set('user_id', $user_id);
			if ($bio) {
				$this->Bio->id = $bio['Bio']['id'];
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
			$this->request->data = $bio;
		}
		$this->set(array(
			'title_for_layout' => 'Update Instructor Bio',
			'instructor_id' => $user_id
		));
	}

	public function view($user_id) {
		$bio = $this->Bio->find(
			'first',
			array(
				'conditions' => array(
					'Bio.user_id' => $user_id
				)
			)
		);
		if (! $bio) {
			throw new NotFoundException('Sorry, we couldn\'t find that bio.');
		}
		$this->set(array(
			'title_for_layout' => $bio['User']['name'],
			'bio' => $bio
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