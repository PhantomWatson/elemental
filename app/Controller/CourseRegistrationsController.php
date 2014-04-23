<?php
App::uses('AppController', 'Controller');
/**
 * CourseRegistrations Controller
 *
 * @property CourseRegistration $CourseRegistration
 */
class CourseRegistrationsController extends AppController {

	public function beforeFilter() {
		parent::beforeFilter();

		$this->Auth->deny(
			'delete',
			'take_off_waiting_list'
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
		$instructor_owned_actions = array(
			'take_off_waiting_list'
		);
		if (in_array($this->action, $instructor_owned_actions) && isset($this->params['named']['id'])) {
			$instructor_id = $this->CourseRegistration->getInstructorId($this->params['named']['id']);
			if ($user['id'] == $instructor_id) {
				return true;
			}
			return parent::isAuthorized($user);
		}

		// Instructors can remove students from a class list,
		// or the students can remove themselves
		if ($this->action == 'delete' && isset($this->params['named']['id'])) {
			$reg_id = $this->params['named']['id'];
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
		if ($user_id != $registration_user_id && ! $user_is_instructor) {
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
		if (! $this->CourseRegistration->exists($id)) {
			throw new NotFoundException('Course registration not found. It looks like you already canceled your registration.');
		}

		$expected_hash = $this->CourseRegistration->getUnregisterHash($id);
		if ($hash != $expected_hash) {
			throw new ForbiddenException('Error unregistering. Security code incorrect.');
		}

		$this->CourseRegistration->id = $id;
		$this->CourseRegistration->delete();
		$course_id = $this->CourseRegistration->field('course_id');
		$user_id = $this->CourseRegistration->field('user_id');
		$this->__sendRefundEmail($course_id, $user_id);
		$this->loadModel('Course');
		$this->Course->elevateWaitingListMembers($course_id);

		$this->set(array(
			'title_for_layout' => 'Cancel Class Registration'
		));
	}
}
