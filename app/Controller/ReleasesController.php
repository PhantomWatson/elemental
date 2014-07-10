<?php
App::uses('AppController', 'Controller');
/**
 * Releases Controller
 *
 * @property Release $Release
 */
class ReleasesController extends AppController {
	public function beforeFilter() {
		parent::beforeFilter();
		$this->Auth->deny();
	}

	public function isAuthorized($user = null) {
		return true;
	}

	private function __processForm() {
		$course_id = $this->request->course_id;
		$user_id = $this->Auth->user('id');
		$this->Release->create($this->request->data);
		$ip_address = $this->request->clientIp();
		$this->Release->set(compact('user_id', 'course_id', 'ip_address'));

		// Check to see if this should overwrite an existing release
		$existing_release_id = $this->Release->getIdFromUidCid($user_id, $course_id);
		if ($existing_release_id) {
			$this->Release->set('id', $existing_release_id);
		}

		// Remove requirement for guardian info if user is 18+
		if ($this->request->data['Release']['age'] >= 18) {
			unset($this->Release->validate['guardian_name']);
			unset($this->Release->validate['guardian_phone']);
		}

		if ($this->Release->save()) {
			if ($course_id) {
				$this->Flash->success('Your liability release has been submitted');
				$this->redirect(array(
					'controller' => 'courses',
					'action' => 'register',
					$course_id
				));
			} else {
				$this->render('success');
			}
		} else {
			$this->Flash->error('There was an error submitting your liability release. Please check for details below.');
		}
	}

	private function __setupForm() {
		if ($this->request->course_id) {
			$course_id = $this->request->course_id;
			$this->loadModel('Course');
			if (! $this->Course->exists($course_id)) {
				throw new NotFoundException('Invalid course specified');
			}
		}
		$user_id = $this->Auth->user('id');
		$this->loadModel('User');
		$this->User->id = $user_id;
		$this->set('title_for_layout', 'Release of Liability');
	}

	public function add() {
		$this->__setupForm();

		if ($this->request->is('post')) {
			$this->__processForm();
		} else {
			$this->request->data['Release']['name'] = $this->Auth->user('name');
		}
		$date = date('jS').' day of '.date('F, Y');
		$this->set('date', $date);
	}

	public function edit() {
		$this->__setupForm();
		$course_id = $this->request->course_id;
		$user_id = $this->Auth->user('id');
		$release_id = $this->Release->getIdFromUidCid($user_id, $course_id);
		if (! $release_id) {
			throw new NotFoundException('Cannot edit liability release. Liability release not found');
		}
		$this->Release->id = $release_id;
		$submitted = $this->Release->field('modified');
		$timestamp = strtotime($submitted);
		$date = date('jS', $timestamp).' day of '.date('F, Y', $timestamp);
		$this->set('date', $date);

		if ($this->request->is('post') || $this->request->is('put')) {
			$this->__processForm();
		} else {
			$this->request->data = $this->Release->read();
		}
		$this->render('add');
	}
}
