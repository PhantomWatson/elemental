<?php
App::uses('AppController', 'Controller');
/**
 * CourseRegistrations Controller
 *
 * @property CourseRegistration $CourseRegistration
 */
class CourseRegistrationsController extends AppController {
	public $components = array('Stripe.Stripe');

	public function beforeFilter() {
		parent::beforeFilter();

		$this->Auth->deny(
			'delete'
		);
	}

	private function __sendRefundEmail($course_id, $registration_user_id, $user_is_instructor = false) {
		$this->loadModel('Course');
		$this->Course->id = $course_id;
		$course_is_free = $this->Course->field('cost') == 0;
		if (! $course_is_free) {
			if ($this->Course->sendRefundEmail($course_id, $registration_user_id)) {
				if ($user_is_instructor) {
					$this->Flash->set('An email has been sent to an Elemental administrator, who will execute a refund for this student\'s registration fee in the next 5 business days.');
				} else {
					$this->Flash->set('Your registration fee will be refunded within the next 5 business days. Please contact '.Configure::read('refund_email').' if you have any questions.');
				}
			} else {
				$this->Flash->error('There was an error contacting an Elemental administrator to request a registration fee refund. Please email '.Configure::read('refund_email').' for assistance.');
			}
		}
	}

	public function isAuthorized($user) {
		// Instructors can remove students from a class list,
		// or the students can remove themselves
		if ($this->action == 'delete' && isset($this->params['pass'][0])) {
			$reg_id = $this->params['pass'][0];
			$instructor_id = $this->CourseRegistration->getInstructorId($reg_id);
			$this->CourseRegistration->id = $reg_id;
			$student_id = $this->CourseRegistration->field('user_id');
			if ($user['id'] == $instructor_id || $user['id'] == $student_id) {
				return true;
			}
			return parent::isAuthorized($user);
		}

		return parent::isAuthorized($user);
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

	private function __autoRefund($course_id, $user_id) {
		$this->loadModel('CoursePayment');
		$payment = $this->CoursePayment->find(
			'first',
			array(
				'conditions' => compact('course_id', 'user_id'),
				'contain' => false,
				'fields' => array(
					'id',
					'refunded',
					'order_id',
					'user_id'
				)
			)
		);

		// Payment was never made, or was already refunded
		if (empty($payment) || $payment['CoursePayment']['refunded']) {
			return false;
		}

		$this->loadModel('Course');
		$this->Course->id = $course_id;
		$course_begins = $this->Course->field('begins');
		$limit = $this->CourseRegistration->autoRefundDeadline;
		$deadline = strtotime("$course_begins - $limit");

		// Past the refund deadline
		if (time() > $deadline) {
			return false;
		}

		$charge = $this->__retrieveCharge($payment['CoursePayment']['order_id']);
		if (is_string($charge)) {
			$this->Flash->error('There was a problem refunding your course payment. Please <a href="/contact">contact an administrator</a> for assistance.');
			return false;
		}

		$params = array(
			'reason' => null,
			'metadata' => array(
				'type' => 'Course registration cancellation automatic refund',
			)
		);

		$this->loadModel('User');
		$student_id = $payment['CoursePayment']['user_id'];
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
			$this->Flash->error('There was a problem refunding your course payment. Please <a href="/contact">contact an administrator</a> for assistance.');
			return false;
		}

		if (is_string($refund)) {
			$this->Flash->error('There was a problem refunding your course payment. Please <a href="/contact">contact an administrator</a> for assistance.');
			return false;
		}

		$this->Flash->success('Your registration payment has been refunded.');
		$this->CoursePayment->saveField('refunded', date('Y-m-d H:i:s'));
		return true;
	}

/**
 * delete method
 *
 * @param string $id
 * @return void
 */
	public function delete($id = null, $hash = null) {
		if (! $id && isset($this->request->named['id'])) {
			$id = $this->request->named['id'];
		}

		if (! $this->request->is('post')) {
			throw new MethodNotAllowedException();
		}

		if (! $this->CourseRegistration->exists($id)) {
			throw new NotFoundException('Course registration not found. This student may have already been un-registered.');
		}

		$this->CourseRegistration->id = $id;
		$course_id = $this->CourseRegistration->field('course_id');
		$instructor_id = $this->CourseRegistration->getInstructorId($id);
		$user_id = $this->Auth->user('id');
		$user_is_instructor = $user_id == $instructor_id;
		$registration_user_id = $this->CourseRegistration->field('user_id');
		$this->loadModel('User');
		$user_is_admin = $this->User->hasRole($user_id, 'admin');
		if ($user_id != $registration_user_id && ! $user_is_instructor && ! $user_is_admin) {
			throw new ForbiddenException('You are not authorized to cancel that student\'s class registration');
		}
		$is_on_waiting_list = $this->CourseRegistration->isOnWaitingList($user_id, $course_id);

		// Successful cancellation
		$this->loadModel('Course');
		if ($this->CourseRegistration->delete()) {

			// Confirmation, removal from waiting list
			if ($is_on_waiting_list) {
				if ($user_is_instructor) {
					$this->Flash->success('You have been removed from the waiting list.');
				} else {
					$this->Flash->success('Student removed from waiting list.');
				}

			// Confirmation, removal from class list
			} else {
				if ($user_is_instructor) {
					$this->Flash->success('Student un-registered from course.');
				} else {
					$this->Flash->success('Your registration has been canceled.');
				}
			}

			// Waiting list elevation
			if ($this->Course->elevateWaitingListMembers($course_id)) {
				if ($user_is_instructor) {
					$this->Flash->success('Student elevated from the waiting list to the class list.');
				}
			}

			$this->__sendRefundEmail($course_id, $registration_user_id, $user_is_instructor);

		// Unsuccessful cancellation
		} else {
			if ($is_on_waiting_list) {
				if ($user_is_instructor) {
					$this->Flash->error('There was an error un-registering this student from the course.');
				} else {
					$this->Flash->error('There was an error canceling your registration.');
				}
			} else {
				if ($user_is_instructor) {
					$this->Flash->error('There was an error removing this student from the waiting list.');
				} else {
					$this->Flash->error('There was an error removing you from the waiting list.');
				}
			}
		}

		$this->redirect($this->request->referer());
	}

	public function unregister_via_link($id = null, $hash = null) {
		if ($this->CourseRegistration->exists($id)) {
			$expected_hash = $this->CourseRegistration->getUnregisterHash($id);
			if ($hash != $expected_hash) {
				throw new ForbiddenException('Error unregistering. Security code incorrect.');
			}

			$this->CourseRegistration->id = $id;
			$course_id = $this->CourseRegistration->field('course_id');
			$user_id = $this->CourseRegistration->field('user_id');
			if ($this->CourseRegistration->delete()) {
				$this->__sendRefundEmail($course_id, $user_id);
				$this->loadModel('Course');
				$this->Course->elevateWaitingListMembers($course_id);
				$message = 'You have been successfully unregistered.';
				$msg_class = 'success';
			} else {
				$message = 'There was an error canceling your registration.';
				$msg_class = 'danger';
			}
		} else {
			$message = 'Course registration not found. It looks like you already canceled your registration.';
			$msg_class = 'info';
		}

		$this->set(array(
			'title_for_layout' => 'Cancel Course Registration',
			'message' => $message,
			'msg_class' => $msg_class
		));
	}
}
