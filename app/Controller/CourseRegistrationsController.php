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

	public function isAuthorized($user = null) {
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

		$this->Flash->success('You will receive a refund for your registration payment in 5-10 business days.');
		$this->CoursePayment->id = $payment['CoursePayment']['id'];
		$this->CoursePayment->saveField('refunded', date('Y-m-d H:i:s'));
		return true;
	}

	/**
	 * Delete method
	 *
	 * @param string|int $id ID of course registration record
	 * @param string $hash Security hash
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
				CakeLog::info(
					sprintf(
						'User #%s\'s registration #%s removed from waiting list for course #%s via button (%s)',
						$registration_user_id,
						$id,
						$course_id,
						$this->request->clientIp()
					),
					'site_activity'
				);

			// Confirmation, removal from class list
			} else {
				if ($user_is_instructor) {
                    $this->Flash->success('Your registration has been canceled.');
				} else {
                    $this->Flash->success('Student un-registered from course.');
				}

				CakeLog::info(
					sprintf(
						'User #%s\'s registration #%s removed from course #%s via button (%s)',
						$registration_user_id,
						$id,
						$course_id,
						$this->request->clientIp()
					),
					'site_activity'
				);
			}

			// Waiting list elevation
			if ($this->Course->elevateWaitingListMembers($course_id)) {
				if ($user_is_instructor) {
					$this->Flash->success('Student elevated from the waiting list to the class list.');
				}
			}

			// Send refund, if appropriate
			$this->__autoRefund($course_id, $user_id);

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
				$this->loadModel('Course');
				$this->Course->elevateWaitingListMembers($course_id);
				$message = 'You have been successfully unregistered.';
				$msg_class = 'success';
				CakeLog::info(
					sprintf(
						'Course registration #%s unregistered via link (%s)',
						$id,
						$this->request->clientIp()
					),
					'site_activity'
				);

				// Send refund, if appropriate
				$this->__autoRefund($course_id, $user_id);
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
