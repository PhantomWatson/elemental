<?php
App::uses('AppController', 'Controller');
/**
 * Testimonials Controller
 *
 * @property Testimonial $Testimonial
 */
class TestimonialsController extends AppController {

	public function beforeFilter() {
		parent::beforeFilter();
		$this->Auth->deny(array('add', 'edit', 'delete', 'manage', 'approve'));
	}

	public function isAuthorized($user) {
		// Students and instructors can only add
		if (in_array($user['role'], array('student', 'instructor'))) {
			return $this->action == 'add';
		}
		
        // Admins can access everything
		return parent::isAuthorized($user);
	}
	
/**
 * index method
 *
 * @return void
 */
	public function index() {
		$this->Testimonial->recursive = 0;
		$this->set(array(
			'testimonials' => $this->paginate(),
			'title_for_layout' => 'Testimonials'
		));
	}

/**
 * add method
 *
 * @return void
 */
	public function add() {
		$role = $this->Auth->user('role');
		if ($this->request->is('post')) {
			$this->Testimonial->create();
			$this->request->data['Testimonial']['user_id'] = $this->Auth->user('id');
			
			// Auto-approved if posted by an admin
			$this->request->data['Testimonial']['approved'] = ($role == 'admin');
			
			if ($this->Testimonial->save($this->request->data)) {
				if ($role == 'student') {
					$this->Flash->success('Thanks! Your testimonial has been submitted. After an administrator reviews it, it will be published to the website.');
					$this->redirect(array(
						'controller' => 'pages', 
						'action' => 'home'
					));
				} elseif ($role == 'instructor') {
					$this->Flash->success('The testimonial has been added. After an administrator reviews it, it will be published.');
					$this->request->data = array();
					$this->redirect(array(
						'action' => 'add'
					));
				} elseif ($role == 'admin') {
					$this->Flash->success('The testimonial has been added and published.');
					$this->request->data = array();
					$this->redirect(array(
						'action' => 'add'
					));
				}
			} else {
				$this->Flash->error('The testimonial could not be saved. Please, try again.');
			}
		} else {
			// If the user is a student, automatically suggest they use their own name
			if ($role == 'student') {
				$this->loadModel('User');
				$this->User->id = $this->Auth->user('id');
				$this->request->data['Testimonial']['author'] = $this->User->field('name');
			}
		}
		$this->set(array(
			'title_for_layout' => 'Submit a Testimonial',
			'role' => $role
		));
		$this->render('form');
	}

/**
 * edit method
 *
 * @param string $id
 * @return void
 */
	public function edit($id = null) {
		$this->Testimonial->id = $id;
		if (!$this->Testimonial->exists()) {
			throw new NotFoundException('Invalid testimonial');
		}
		if ($this->request->is('post') || $this->request->is('put')) {
			if ($this->Testimonial->save($this->request->data)) {
				$this->Flash->success('Updated testimonial');
				$this->redirect(array('action' => 'index'));
			} else {
				$this->Flash->error('The testimonial could not be updated. Please try again.');
			}
		} else {
			$this->request->data = $this->Testimonial->read(null, $id);
		}
		$this->set(array(
			'title_for_layout' => 'Edit Testimonial',
			'role' => $this->Auth->user('role')
		));
		$this->render('form');
	}

/**
 * delete method
 *
 * @param string $id
 * @return void
 */
	public function delete($id = null) {
		if (!$this->request->is('post')) {
			throw new MethodNotAllowedException();
		}
		$this->Testimonial->id = $id;
		if (!$this->Testimonial->exists()) {
			throw new NotFoundException(__('Invalid testimonial'));
		}
		if ($this->Testimonial->delete()) {
			$this->Flash->success(__('Testimonial deleted'));
			$this->redirect(array('action' => 'index'));
		}
		$this->Flash->error(__('Testimonial was not deleted'));
		$this->redirect($this->request->referer());
	}
	
	public function manage() {
		$this->paginate = array(
			'contain' => array('User'),
			'order' => 'Testimonial.approved ASC'
		);
		$this->set(array(
			'title_for_layout' => 'Manage Testimonials',
			'testimonials' => $this->paginate()
		));
	}
	
	public function approve($id = null) {
		if (!$this->request->is('post')) {
			throw new MethodNotAllowedException();
		}
		$this->Testimonial->id = $id;
		if (!$this->Testimonial->exists()) {
			throw new NotFoundException(__('Invalid testimonial'));
		}
		if ($this->Testimonial->saveField('approved', 1)) {
			$this->Flash->success('Testimonial approved');
			$this->redirect(array('action' => 'manage'));
		}
		$this->Flash->error(__('Testimonial was not approved'));
		$this->redirect(array('action' => 'manage'));
	}
}
