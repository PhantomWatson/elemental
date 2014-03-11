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

		if (!$this->CourseRegistration->exists($id)) {
			throw new NotFoundException('Course registration not found. This student may have already been un-registered.');
		}

		$this->CourseRegistration->id = $id;
		$course_id = $this->CourseRegistration->field('course_id');
		$instructor_id = $this->CourseRegistration->getInstructorId($id);
		$user_is_instructor = $this->Auth->user('id') == $instructor_id;

		if ($this->CourseRegistration->delete()) {
			if ($user_is_instructor) {
				$this->Flash->success('Student un-registered from course.');
				if ($this->__elevateWaitingListMember($course_id)) {
					$this->Flash->success('Student elevated from the waiting list to the class list.');
				}
			} else {
				$this->Flash->success('Your registration has been canceled.');
			}
			$this->redirect($this->request->referer());
		}
		if ($user_is_instructor) {
			$this->Flash->error('There was an error un-registering this student from the course.');
		} else {
			$this->Flash->error('There was an error canceling your registration.');
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
		$course_id = $this->CourseRegistration->field('course_id');
		$this->CourseRegistration->delete();
		$this->__elevateWaitingListMember($course_id);

		$this->set(array(
			'title_for_layout' => 'Cancel Class Registration'
		));
	}
}
