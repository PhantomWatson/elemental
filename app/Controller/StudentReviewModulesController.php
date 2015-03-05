<?php
App::uses('AppController', 'Controller');
class StudentReviewModulesController extends AppController {
	public $name = 'StudentReviewModules';

	public function beforeFilter() {
		parent::beforeFilter();
		$this->Auth->deny(array(
			'overview'
		));
	}

	public function overview($mode = 'instructors') {
		if ($mode == 'instructors') {
			$this->loadModel('Role');
			$role = $this->Role->find(
				'first',
				array(
					'conditions' => array(
						'name' => 'instructor'
					),
					'contain' => array(
						'User' => array(
							'fields' => array(
								'User.id',
								'User.name',
								'User.email'
							),
							'order' => 'User.name',
							'StudentReviewModule'
						)
					),
					'fields' => array(
						'Role.id'
					)
				)
			);
			$instructors = $role['User'];
			foreach ($instructors as &$instructor) {
				$instructor['srm_totals'] = array(
					'total' => 0,
					'paid' => 0,
					'granted' => 0,
					'awaiting_payment' => 0,
					'assigned' => 0,
					'available' => 0
				);
				foreach ($instructor['StudentReviewModule'] as $srm) {
					$instructor['srm_totals']['total']++;
					$instructor['srm_totals']['paid'] += $srm['purchase_id'] ? 1 : 0;
					$instructor['srm_totals']['granted'] += $srm['override_admin_id'] ? 1 : 0;
					$instructor['srm_totals']['awaiting_payment'] += (! $srm['purchase_id'] && ! $srm['override_admin_id']) ? 1 : 0;
					$instructor['srm_totals']['assigned'] += $srm['student_id'] ? 1 : 0;
					$instructor['srm_totals']['available'] += ! $srm['student_id'] ? 1 : 0;
				}
			}
			$this->set('instructors', $instructors);
		} elseif ($mode == 'students') {

		} else {
			throw new NotFoundException('Sorry, your request was not understood.');
		}

		$this->set(array(
			'title_for_layout' => 'Student Review Modules Overview',
			'mode' => $mode
		));
	}
}