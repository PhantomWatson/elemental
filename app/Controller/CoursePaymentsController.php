<?php
App::uses('AppController', 'Controller');
class CoursePaymentsController extends AppController {
	public $name = 'CoursePayments';
	public $components = array('Stripe.Stripe');

	public function beforeFilter() {
		parent::beforeFilter();
		$this->Auth->deny();
	}

	public function admin_refund($course_payment_id = null) {
		$this->CoursePayment->id = $course_payment_id;
		if (! $this->CoursePayment->exists()) {
			$this->Flash->error('Could not refund payment. No record found for course registration payment #'.$course_payment_id);
			$this->redirect($this->request->referer());
		}

		$refunded = $this->CoursePayment->field('refunded');
		if ($refunded) {
			$this->Flash->error('Could not refund payment. Records indicate that this payment has already been refunded.');
			$this->redirect($this->request->referer());
		}

		$charge_id = $this->CoursePayment->field('order_id');
		$charge = $this->__retrieveCharge($charge_id);
		if (is_string($charge)) {
			$this->Flash->error('There was a problem retrieving information about that payment: '.$charge);
			$this->redirect($this->request->referer());
		}

		$params = array(
			//'id' => $charge_id,
			'reason' => null,
			'metadata' => array(
				'type' => 'Course registration cancellation refund',
			)
		);

		$this->loadModel('User');
		$admin_id = $this->Auth->user('id');
		$this->User->id = $admin_id;
		$admin_name = $this->User->field('name');
		$params['metadata']['admin'] = "$admin_name (#$admin_id)";

		$student_id = $this->CoursePayment->field('user_id');
		$this->User->id = $student_id;
		$student_name = $this->User->field('name');
		$student_email = $this->User->field('email');
		$params['metadata']['student'] = "$student_name, $student_email (#$student_id)";

		$this->loadModel('Course');
		$course_id = $this->CoursePayment->field('course_id');
		$this->Course->id = $course_id;
		$course_date = $this->Course->field('begins');
		$params['metadata']['course'] = "$course_date (#$course_id)";

		try {
			$refund = $charge->refunds->create($params);
		} catch (Exception $e) {
			$this->Flash->error('There was a problem issuing that refund: '.$e->getMessage());
			$this->redirect($this->request->referer());
		}

		if (is_string($refund)) {
			$this->Flash->error('There was a problem issuing that refund: '.$refund);
		} else {
			$this->Flash->success('That charge has been refunded.');
			$this->CoursePayment->saveField('refunded', date('Y-m-d H:i:s'));
		}

		$this->redirect($this->request->referer());
	}

	public function admin_index() {
		$filter = isset($this->request->named['filter']) ? $this->request->named['filter'] : null;
		switch ($filter) {
			case 'refundable':
				$conditions = array(
					'CoursePayment.refunded' => null,
					'CoursePayment.jwt' => null
				);
				break;
			case 'refunded':
				$conditions = array(
					'CoursePayment.refunded NOT' => null
				);
				break;
			default:
				$conditions = array();
		}
		$this->paginate = array(
			'conditions' => $conditions,
			'contain' => array(
				'Course' => array(
					'fields' => array(
						'Course.begins',
						'Course.city',
						'Course.state'
					)
				),
				'User' => array(
					'fields' => array(
						'User.id',
						'User.name',
						'User.email'
					)
				)
			),
			'fields' => array(
				'CoursePayment.id',
				'CoursePayment.jwt',
				'CoursePayment.refunded',
				'CoursePayment.created',
				'CourseRegistration.id',
				'CourseRegistration.attended'
			),
			'joins' => array(
				array(
					'table' => 'course_registrations',
					'alias' => 'CourseRegistration',
					'type' => 'LEFT',
					'conditions' => array(
						'CourseRegistration.user_id = CoursePayment.user_id',
						'CourseRegistration.course_id = CoursePayment.course_id'
					)
				)
			),
			'order' => array(
				'CoursePayment.created' => 'DESC'
			)
		);
		$this->set(array(
			'title_for_layout' => 'Course Registration Payments',
			'payments' => $this->paginate()
		));
	}
}