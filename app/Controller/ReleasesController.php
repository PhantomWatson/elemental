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

	public function add() {
		if ($this->request->course_id) {
			$course_id = $this->request->course_id;
			$this->loadModel('Course');
			if ($this->Course->exists($course_id)) {
				$user_id = $this->Auth->user('id');
				$this->loadModel('User');
				$this->User->id = $user_id;
				$this->set(array(
					'course_id' => $course_id,
					'date' => date('jS').' day of '.date('F, Y'),
					'title_for_layout' => 'Release of Liability',
					'user_name' => $this->User->field('name')
				));
			} else {
				throw new NotFoundException('Invalid course specified');
			}
		} else {
			throw new NotFoundException('Course not specified');
		}

		if ($this->request->is('post')) {
			$this->Release->create();
			$this->request->data['Release']['user_id'] = $user_id;
			if ($this->Release->save($this->request->data)) {
				$this->Flash->success('Your liability release has been submitted');
				$this->redirect(array(
					'controller' => 'courses',
					'action' => 'register',
					$course_id
				));
			} else {
				$this->Flash->error('There was an error submitting your liability release. Please check for details below.');
			}
		}
	}
}
