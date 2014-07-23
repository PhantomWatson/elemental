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
		if ($this->action == 'view' && $this->User->hasRole($user['id'], array('instructor', 'trainee'))) {
			return true;
		}

		return parent::isAuthorized($user);
	}

	public function view($agree = false) {
		$instructor_id = $this->Auth->user('id');

		if ($this->request->is('post') && $agree) {
			$already_agreed = $this->InstructorAgreement->find(
				'count',
				array(
					'conditions' => array(
						'InstructorAgreement.instructor_id' => $instructor_id
					)
				)
			);
			if (! $already_agreed) {
				$this->InstructorAgreement->create(array(
					'instructor_id' => $instructor_id
				));
				if (! $this->InstructorAgreement->save()) {
					$this->Flash->error('Sorry, there was an error recording your agreement. Please try again or contact an administrator for assistance.');
				}
			}
		}

		$date_agreed = $this->InstructorAgreement->field(
			'created',
			array(
				'InstructorAgreement.instructor_id' => $instructor_id
			)
		);
		$this->set(array(
			'title_for_layout' => 'Certified Elemental Instructor License Agreement',
			'date_agreed' => $date_agreed
		));
	}
}