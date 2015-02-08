<?php
App::uses('AppController', 'Controller');
class StoreController extends AppController {
	public $name = 'Store';
	public $uses = array('Product', 'Purchase');

	public function beforeFilter() {
		parent::beforeFilter();

		$this->Security->requireSecure('student_review_module');
	}

	public function student_review_module() {
		$step = 'prep';
		$user_id = $this->Auth->user('id');
		$this->loadModel('User');
		$is_instructor = $this->User->hasRole($user_id, 'instructor');
		$this->loadModel('StudentReviewModule');
		$cost = $this->StudentReviewModule->getCost();

		if ($this->request->is('post')) {
			// Create temporary validation rule
			$this->Purchase->validate['instructor_id'] = array(
				'notempty' => array(
					'rule' => 'notEmpty',
					'required' => true,
					'message' => 'Please select an instructor'
				)
			);
			$this->Purchase->create($this->request->data);
			$validates = $this->Purchase->validates();
			$valid_quantity = true;
			if (! isset($this->request->data['Purchase']['quantity'])) {
				// For some reason, using Purchase->validate['quantity'] didn't work
				$this->Purchase->validationErrors['quantity'] = 'Please specify a quantity!';
				$valid_quantity = false;
				$this->request->data['Purchase']['quantity'] = 10;
			}
			if ($validates && $valid_quantity) {
				$step = 'purchase';
				$quantity = $this->request->data['Purchase']['quantity'];
				$instructor_id = $this->request->data['Purchase']['instructor_id'];
				$this->User->id = $instructor_id;
				if ($is_instructor) {
					$redirect_url = array(
						'instructor' => true,
						'controller' => 'products',
						'action' => 'student_review_modules'
					);
				} else {
					$redirect_url = array(
						'controller' => 'store',
						'action' => 'student_review_module'
					);
				}
				$this->set(array(
					'instructor_name' => $this->User->field('name'),
					'quantity' => $quantity,
					'redirect_url' => Router::url($redirect_url, true),
					'total' => number_format(($quantity * $cost), 2)
				));
			}

		} else {
			$this->request->data['Purchase']['quantity'] = 10;
		}

		if ($step == 'prep') {
			if (! isset($this->request->data['Purchase']['instructor_id'])) {
				$this->request->data['Purchase']['instructor_id'] = $this->Auth->user('id');
			}
			$this->set(array(
				'instructors' => $this->User->getCertifiedInstructorList()
			));
		}
		$this->User->id = $user_id;
		$this->set(array(
			'cost' => $cost,
			'step' => $step,
			'title_for_layout' => 'Purchase Student Review Modules',
			'user_id' => $user_id,
			'email' => $this->User->field('email')
		));
	}
}