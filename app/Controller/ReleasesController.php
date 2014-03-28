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
		if ($this->request->is('post')) {
			$this->Release->create();
			$this->request->data['Release']['user_id'] = $this->Auth->user('id');
			if ($this->Release->save($this->request->data)) {
				$this->Flash->success('Your liability release has been submitted');
				$this->redirect(array(
					'controller' => 'courses',
					'action' => 'register',
					$this->request->data['Release']['course_id']
				));
			} else {
				$this->Flash->error('There was an error submitting your liability release');
			}
		} else {
			if ($this->request->named['course_id']) {
				$course_id = $this->request->named['course_id'];
				$this->loadModel('Course');
				if ($this->Course->exists($course)) {
					$this->set('course_id', $course_id);
				} else {
					throw new NotFoundException('Invalid course specified');
				}
			} else {
				throw new NotFoundException('Course not specified');
			}
		}
		$this->set(array(
			'title_for_layout' => 'Release of Liability'
		));
	}
}
