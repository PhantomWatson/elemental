<?php
App::uses('AppController', 'Controller');
/**
 * Certifications Controller
 *
 * @property Certification $Certification
 */
class CertificationsController extends AppController {
	public $paginate = array(
		'contain' => array(
			'User' => array(
				'fields' => array(
					'User.id',
					'User.name'
				)
			)
		),
		'order' => array(
			'Certification.date_expires' => 'DESC'
		)
	);

	public function beforeFilter() {
		parent::beforeFilter();
		$this->Auth->deny(array('instructor_index', 'admin_index', 'admin_add'));
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
		$this->set(array(
			'title_for_layout' => 'Instructor Certifications',
			'certifications' => $this->paginate()
		));
	}

	public function admin_add() {
		if ($this->request->is('post')) {
			$this->Certification->create($this->request->data);
			$granted = $this->request->data['Certification']['date_granted'];
			$granted = $granted['year'].'-'.$granted['month'].'-'.$granted['day'];
			$expires = strtotime($granted.' + 1 year');
			$this->Certification->set('date_expires', date('Y-m-d', $expires));
			if ($this->Certification->save()) {
				$this->Flash->success('Certification granted');
				$this->redirect(array(
					'admin' => true,
					'action' => 'index'
				));
			} else {
				$this->Flash->error('There was an error granting certification');
			}
		} else {
			$this->request->data['Certification']['date_granted'] = date('Y-m-d');
		}

		$this->loadModel('User');
		$instructors = $this->User->getInstructorList();
		$certified_instructors = $this->User->getCertifiedInstructorList();

		// Note currently-certified instructors
		foreach ($instructors as $instructor_id => $instructor_name) {
			if (isset($certified_instructors[$instructor_id])) {
				$instructors[$instructor_id] .= ' (currently certified)';
			}
		}

		$this->set(array(
			'title_for_layout' => 'Add Instructor Certification',
			'instructors' => $instructors
		));
	}
}