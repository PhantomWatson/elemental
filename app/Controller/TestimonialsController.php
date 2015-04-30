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
		// Admins can access everything
		if (parent::isAuthorized($user)) {
			return true;
		}

		// Students and instructors can only add
		$this->loadModel('User');
		$user_id = $this->Auth->user('id');
		if ($this->User->hasRole($user_id, 'student') || $this->User->hasRole($user_id, 'instructor')) {
			return $this->action == 'add';
		}

		return false;
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
		$user_id = $this->Auth->user('id');
		$this->loadModel('User');
		if ($this->request->is('post')) {
			$this->Testimonial->create();
			$this->request->data['Testimonial']['user_id'] = $user_id;

			// Auto-approved if posted by an admin
			$this->request->data['Testimonial']['approved'] = $this->User->hasRole($user_id, 'admin');

			if ($this->Testimonial->save($this->request->data)) {
				if ($this->User->hasRole($user_id, 'admin')) {
					$this->Flash->success('The testimonial has been added and published.');
					$this->request->data = array();
					$this->redirect(array(
						'action' => 'add'
					));
				} elseif ($this->User->hasRole($user_id, 'instructor')) {
					$this->Flash->success('The testimonial has been added. After an administrator reviews it, it will be published.');
					$this->request->data = array();
					$this->redirect(array(
						'action' => 'add'
					));
				} elseif ($this->User->hasRole($user_id, 'student')) {
					$this->Flash->success('Thanks! Your testimonial has been submitted. After an administrator reviews it, it will be published to the website.');
					$this->redirect(array(
						'controller' => 'pages',
						'action' => 'home'
					));
				}
			} else {
				$this->Flash->error('The testimonial could not be saved. Please, try again.');
			}
		} else {
			// If the user is a student, automatically suggest they use their own name
			if ($this->User->hasRole($user_id, 'student')) {
				$this->loadModel('User');
				$this->User->id = $this->Auth->user('id');
				$this->request->data['Testimonial']['author'] = $this->User->field('name');
			}
		}

		$user_roles = $this->__getUserRoles();
		$is_staff = in_array('instructor', $user_roles) || in_array('admin', $user_roles);
		$this->set(array(
			'title_for_layout' => 'Submit a Testimonial',
			'is_student' => $this->User->hasRole($user_id, 'student'),
			'has_attended' => $this->User->hasAttendedCourse($user_id),
			'is_staff' => $is_staff
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
		$this->loadModel('User');
		$this->set(array(
			'title_for_layout' => 'Edit Testimonial',
			'is_student' => $this->User->hasRole($user_id, 'student')
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
			$this->redirect(array('action' => 'manage'));
		}
		$this->Flash->error(__('Testimonial was not deleted'));
		$this->redirect(array('action' => 'manage'));
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
		if (! $this->request->is('post')) {
			throw new MethodNotAllowedException();
		}
		$this->Testimonial->id = $id;
		if (! $this->Testimonial->exists()) {
			throw new NotFoundException(__('Invalid testimonial'));
		}
		if ($this->Testimonial->saveField('approved', 1)) {
			$this->Flash->success('Testimonial approved');
			$this->Alert->refresh('admin testimonials');
			$this->redirect(array('action' => 'manage'));
		}
		$this->Flash->error(__('Testimonial was not approved'));
		$this->redirect(array('action' => 'manage'));
	}
}
