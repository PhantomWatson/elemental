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

	/**
	 * Code harvested from /Plugin/Stripe/Controller/Component/StripeComponent.php and adapted to Stripe_Charge::retrieve()
	 */
	private function __retrieveCharge($charge_id) {
		$error = null;
		try {
			Stripe::setApiKey($this->Stripe->key);
			$charge = Stripe_Charge::retrieve($charge_id);

		} catch(Stripe_CardError $e) {
			$body = $e->getJsonBody();
			$err = $body['error'];
			CakeLog::error(
				'Charge::Stripe_CardError: ' . $err['type'] . ': ' . $err['code'] . ': ' . $err['message'],
				'stripe'
			);
			$error = $err['message'];

		} catch (Stripe_InvalidRequestError $e) {
			$body = $e->getJsonBody();
			$err = $body['error'];
			CakeLog::error(
				'Charge::Stripe_InvalidRequestError: ' . $err['type'] . ': ' . $err['message'],
				'stripe'
			);
			$error = $err['message'];

		} catch (Stripe_AuthenticationError $e) {
			CakeLog::error('Charge::Stripe_AuthenticationError: API key rejected!', 'stripe');
			$error = 'Payment processor API key error.';

		} catch (Stripe_ApiConnectionError $e) {
			CakeLog::error('Charge::Stripe_ApiConnectionError: Stripe could not be reached.', 'stripe');
			$error = 'Network communication with payment processor failed, try again later';

		} catch (Stripe_Error $e) {
			CakeLog::error('Charge::Stripe_Error: Stripe could be down.', 'stripe');
			$error = 'Payment processor error, try again later.';

		} catch (Exception $e) {
			CakeLog::error('Charge::Exception: Unknown error.', 'stripe');
			$error = 'There was an error, try again later.';
		}

		if ($error !== null) {
			// an error is always a string
			return (string)$error;
		}

		CakeLog::info('Stripe: charge id ' . $charge_id, 'stripe');

		return $charge;
	}

	public function admin_index() {
		$this->paginate = array(
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