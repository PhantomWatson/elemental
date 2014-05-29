<?php
App::uses('AppController', 'Controller');
/**
 * Courses Controller
 *
 * @property Course $Course
 */
class CoursesController extends AppController {
	public function beforeFilter() {
		parent::beforeFilter();

		$this->Auth->deny(
			'add',
			'edit',
			'delete',
			'manage',
			'students',
			'report_attendance'
		);
		/* The 'register' action is also restricted to
		 * logged-in users, but the error message and redirect
		 * is handled inside that method. */

		/*
		$this->Security->blackHoleCallback = 'forceSSL';
		$this->Security->requireSecure('register');
		$this->Security->requirePost('postback');
		*/
	}

	public function isAuthorized($user) {
		$instructor_owned_actions = array(
			'edit',
			'delete',
			'students',
			'report_attendance'
		);
		if (in_array($this->action, $instructor_owned_actions) && isset($this->params['named']['id'])) {
			$this->Course->id = $this->params['named']['id'];
			$instructor_id = $this->Course->field('user_id');
			if ($instructor_id == $user['id']) {
				return true;
			}
			return parent::isAuthorized($user);
		}
		return parent::isAuthorized($user);
	}

/**
 * index method
 *
 * @return void
 */
	public function index() {
		$this->paginate = array(
			'conditions' => array(
				'Course.begins >=' => date('Y-m-d')
			),
			'order' => array('Course.begins ASC'),
			'limit' => 10
		);
		$courses = $this->paginate();
		$this->loadModel('User');
		$this->loadModel('CourseRegistration');
		$user_id = $this->Auth->user('id');
		foreach ($courses as &$course) {
			$spots = $course['Course']['max_participants'];
			$registered = 0;
			foreach ($course['CourseRegistration'] as $reg) {
				if (! $reg['waiting_list']) {
					$registered++;
				}
			}
			$course['spots_left'] = max($spots - $registered, 0);
			$course['deadline'] = date('F j, Y', strtotime($course['Course']['deadline']));
			$course['deadline_passed'] = $course['Course']['deadline'] < date('Y-m-d');
			$course['percent_full'] = floor(($registered / $spots) * 100);
			if ($course['percent_full'] >= 75) {
				$course['progress_bar_class'] = 'progress-bar-danger';
			} elseif ($course['percent_full'] >= 50) {
				$course['progress_bar_class'] = 'progress-bar-warning';
			} else {
				$course['progress_bar_class'] = 'progress-bar-success';
			}
			$course_id = $course['Course']['id'];
			$course['on_class_list'] = $this->User->registeredForCourse($user_id, $course_id);
			$course['on_waiting_list'] = $this->CourseRegistration->isOnWaitingList($user_id, $course_id);
			$course['registration_id'] = $this->CourseRegistration->getRegistrationId($user_id, $course_id);
		}

		$this->set(array(
			'title_for_layout' => 'Upcoming Courses',
			'courses' => $courses,
			'courses_registered_for' => $this->User->coursesRegisteredFor($this->Auth->user('id'))
		));
	}

/**
 * view method
 *
 * @param string $id
 * @return void
 */
	public function view($course_id = null) {
		$this->Course->id = $course_id;
		if (! $this->Course->exists()) {
			throw new NotFoundException('Sorry, we couldn\'t find that course.');
		}

		$course = $this->Course->read(null, $course_id);
		$title_for_layout = 'Course Details: '.date('F j, Y', strtotime($course['CourseDate'][0]['date']));

		$this->loadModel('CourseRegistration');
		$user_id = $this->Auth->user('id');
		$registration_id = $this->CourseRegistration->getRegistrationId($user_id, $course_id);

		$this->loadModel('User');
		$is_registered = $this->User->registeredForCourse($user_id, $course_id);
		$is_on_waiting_list = $this->CourseRegistration->isOnWaitingList($user_id, $course_id);

		$this->set(compact(
			'course',
			'is_on_waiting_list',
			'is_registered',
			'registration_id',
			'title_for_layout'
		));
	}

/**
 * add method
 *
 * @return void
 */
	public function add() {
		$instructor_id = $this->Auth->user('id');
		$available_psrm = $this->PrepaidReviewModule->getAvailableCount($instructor_id);
		if ($this->request->is('post')) {
			$this->Course->create();
			$this->request->data['Course']['user_id'] = $instructor_id;
			if ($this->Course->saveAssociated($this->request->data)) {
				$this->Flash->success('The course has been added');
				$this->redirect(array('action' => 'manage'));
			} else {
				$this->Flash->error('The course could not be added. Please try again.');
			}
		}
		if (! isset($this->request->data['Course']['cost_dollars']) || empty ($this->request->data['Course']['cost_dollars'])) {
			$this->request->data['Course']['cost_dollars'] = '0';
		}
		if (! isset($this->request->data['Course']['cost_cents']) || empty ($this->request->data['Course']['cost_cents'])) {
			$this->request->data['Course']['cost_cents'] = '00';
		}
		$this->loadModel('PrepaidReviewModule');
		$this->set(array(
			'title_for_layout' => 'Schedule a Course',
			'available_psrm' => $available_psrm
		));
		$this->render('form');
	}

/**
 * edit method
 *
 * @param int $id
 * @return void
 */
	public function edit($id = null) {
		$this->Course->id = $id;
		if (!$this->Course->exists()) {
			throw new NotFoundException('Invalid course');
		}

		$instructor_id = $this->Course->field('user_id');
		$available_psrm = $this->PrepaidReviewModule->getAvailableCount($instructor_id);
		$max_participants_footnote = '';
		$class_list_count = count($this->Course->getClassList($id));
		$waiting_list_count = count($this->Course->getWaitingList($id));
		if ($this->request->is('post') || $this->request->is('put')) {
			if ($this->request->data['Course']['max_participants'] < $class_list_count) {
				$this->Course->validationErrors['max_participants'] = "Can't set class size any smaller than the number of participants already registered.";
			}

			if ($this->Course->validates() && empty($this->Course->validationErrors)) {
				if ($this->Course->save($this->request->data)) {
					$this->Flash->success('The course has been updated');
					if ($waiting_list_count && $this->request->data['Course']['max_participants'] > $class_list_count) {
						if ($this->Course->elevateWaitingListMembers($id)) {
							$this->Flash->success('Member(s) of the waiting list have been added to the course.');
						}
					}
					$this->redirect(array('action' => 'manage'));
				} else {
					$this->Flash->error('The course could not be updated. Please try again.');
				}
			} else {
				$this->Flash->error('Please correct the indicated errors before proceeding.');
			}
		} else {
			$this->request->data = $this->Course->read(null, $id);
		}
		$this->set(array(
			'title_for_layout' => 'Edit Course',
			'payments_received' => $this->Course->paymentsReceived($id)
		));
		$this->set(compact(
			'available_psrm',
			'class_list_count',
			'waiting_list_count'
		));
		$this->render('form');
	}

/**
 * delete method
 *
 * @param int $id
 * @return void
 */
	public function delete($id = null) {
		if (!$this->request->is('post')) {
			throw new MethodNotAllowedException();
		}
		$this->Course->id = $id;
		if (!$this->Course->exists()) {
			throw new NotFoundException('Course not found.');
		}
		if ($this->Course->delete()) {
			$this->Flash->success('Course deleted');
			$this->redirect(array('action' => 'manage'));
		}
		$this->Flash->error('Course was not deleted');
		$this->redirect(array('action' => 'manage'));
	}

	public function register($course_id = null) {
		// Check course exists
		$this->Course->id = $course_id;
		if (! $this->Course->exists()) {
			throw new NotFoundException('Sorry, we couldn\'t find that course.');
		}

		// Check deadline
		$course = $this->Course->read();
		if ($course['Course']['deadline'] < date('Y-m-d')) {
			throw new ForbiddenException('Sorry, the deadline to register for that course has passed.');
		}

		// Check user session
		$user_id = $this->Auth->user('id');
		if (! $user_id) {
			$login_url = Router::url(array(
				'controller' => 'users',
				'action' => 'login',
				'?' => array(
					'course' => $course_id
				)
			));
			$this->Flash->notification('Before registering, you\'ll need to create an account or <a href="'.$login_url.'">log into your existing account</a>.');
			$this->redirect(array(
				'controller' => 'users',
				'action' => 'register',
				'?' => array(
					'course' => $course_id
				)
			));
		}

		$this->loadModel('Release');
		$this->loadModel('CourseRegistration');
		$this->loadModel('CoursePayment');
		$registration_id = $this->CourseRegistration->getRegistrationId($user_id, $course_id);
		$registration_completed = ($registration_id != null);
		$is_on_waiting_list = $this->CourseRegistration->isOnWaitingList($user_id, $course_id);
		$in_class = $registration_completed && ! $is_on_waiting_list;
		$is_full = $this->Course->isFull($course_id);
		$can_elevate = $is_on_waiting_list && ! $is_full;
		$release_submitted = $this->Release->isSubmitted($user_id, $course_id);
		$is_free = $course['Course']['cost'] == 0;
		$paid = $this->CoursePayment->isPaid($user_id, $course_id);
		$actions_pending = ! ($release_submitted && ($is_free || $paid || $is_full));

		// Determine what the intro message should say
		if ($registration_completed) {
			if ($is_on_waiting_list) {
				if ($is_full) {
					$intro_msg_class = 'alert alert-warning';
					$intro_msg = 'You are on this course\'s waiting list. An instructor will contact you if space becomes available.';
				} else {
					$intro_msg_class = 'alert alert-info';
					$intro_msg = 'Good news! Space has become available in this course. But before you can register, you must complete the following steps:';
				}
			} else {
				$intro_msg_class = 'alert alert-success';
				$intro_msg = 'You are registered for this course.';
			}
		} elseif ($is_full) {
			$intro_msg_class = 'alert alert-warning';
			$intro_msg = '<strong>This course is full</strong>, but you can still add yourself to the waiting list by completing the following steps. If you do, we\'ll contact you in the event that space becomes available.';
		} else {
			$intro_msg_class = 'alert alert-info';
			$intro_msg = 'Before you can register for this course, you must complete the following steps:';
		}

		$this->set(compact(
			'actions_pending',
			'can_elevate',
			'course',
			'in_class',
			'intro_msg',
			'intro_msg_class',
			'is_free',
			'is_full',
			'is_on_waiting_list',
			'paid',
			'registration_completed',
			'registration_id',
			'release_submitted'
		));
		$this->set(array(
			'jwt' => $this->Course->getJWT($course_id, $user_id),
			'title_for_layout' => 'Register for a Course'
		));
	}

	public function complete_registration($course_id) {
		$this->Course->id = $course_id;
		$course = $this->Course->read();
		$user_id = $this->Auth->user('id');
		$joining_waiting_list = $this->Course->isFull($course_id);

		// Confirm receipt of release form
		$this->loadModel('Release');
		if (! $this->Release->isSubmitted($user_id, $course_id)) {
			$this->Flash->error('Before you complete your registration, you must first submit a liability release agreement.');
			$this->redirect($this->referer());
		}

		// Confirm payment (unless if joining the waiting list)
		if (! $joining_waiting_list && floatval($course['Course']['cost']) > 0) {
			$this->loadModel('CoursePayment');
			if (! $this->CoursePayment->isPaid($user_id, $course_id)) {
				$this->Flash->error('Before you complete your registration, you must first pay a registration fee of $'.$course['Course']['cost']);
				$this->redirect($this->referer());
			}
		}

		// Prevent double-registration, detect elevation condition
		$elevate_registration = false;
		$redirect = false;
		$this->loadModel('CourseRegistration');
		$registration_id = $this->CourseRegistration->getRegistrationId($user_id, $course_id);
		$on_waiting_list = $this->CourseRegistration->isOnWaitingList($user_id, $course_id);
		if ($on_waiting_list) {
			if ($joining_waiting_list) {
				$this->Flash->set('You\'re already on this course\'s waiting list.');
				$redirect = true;
			} else {
				$elevate_registration = true;
			}
		} elseif ($registration_id) {
			$this->Flash->set('You\'re already registered for this course.');
			$redirect = true;
		}
		if ($redirect) {
			$this->redirect(array(
				'action' => 'register',
				'id' => $course_id
			));
		}

		// User is moving from the waiting list to the class list
		if ($elevate_registration) {
			$this->CourseRegistration->id = $registration_id;
			$result = $this->CourseRegistration->saveField('waiting_list', 0);

		// User is creating a new registration
		} else {
			$this->CourseRegistration->create(array(
				'course_id' => $course_id,
				'user_id' => $user_id,
				'waiting_list' => $joining_waiting_list
			));
			$result = $this->CourseRegistration->save();
		}
		if ($result) {
			if ($joining_waiting_list) {
				$message = 'You are now on this course\'s waiting list. You will be contacted by an instructor if space becomes available.';
			} else {
				$message = 'You are now registered for this course.';
			}
			if ($this->__sendRegisteredEmail($course_id, $user_id)) {
				$message .= ' You should be receiving an email shortly with information about your registration.';
			} else {
				$this->Flash->error('Error sending registration email.');
			}
			$this->Flash->success($message);
		} else {
			$this->Flash->error('There was an error registering you for this course. Please try again or <a href="/contact">contact us</a> if you need assistance.');
		}
		$this->redirect(array(
			'action' => 'register',
			'id' => $course_id
		));
	}


	/**
	 * Redirects http:// address to https://
	 * Suggested by http://techno-geeks.org/2009/03/using-the-security-component-in-cakephp-for-ssl/
	 */
	public function forceSSL() {
		$this->redirect('https://' . $_SERVER['SERVER_NAME'] . $this->here);
	}

	public function manage() {
		// Admins can access all courses
		if ($this->Auth->user('role') == 'admin') {
			$conditions = null;
		// Instructors can only access their own courses
		} else {
			$conditions = array(
				'Course.user_id' => $this->Auth->user('id'),
			);
		}
		$this->paginate = array(
			'conditions' => $conditions,
			'order' => array('Course.created DESC')
		);
		$this->set(array(
			'title_for_layout' => 'Manage Courses',
			'courses' => $this->paginate(),
			'is_admin' => $this->Auth->user('role') == 'admin'
		));
	}

	/**
	 * Wrapper for Course::sendRegistrationEmail() that outputs helpful flash messages
	 * @param int $course_id
	 * @param int $user_id
	 * @param string|null $password
	 * @return boolean
	 */
	private function __sendRegisteredEmail($course_id, $student_id, $password = null) {
		$result = $this->Course->sendRegistrationEmail($course_id, $student_id, $password);
		if (gettype($result) == 'string') {
			$this->Flash->error($result);
			return false;
		}
		return true;
	}

	public function students($course_id) {
		$this->Course->id = $course_id;
		if (! $this->Course->exists()) {
			throw new NotFoundException('Invalid course selected.');
		}

		$this->set(array(
			'title_for_layout' => 'Students Registered for Course',
			'course' => $this->Course->read(),
			'class_list' => $this->Course->getClassList($course_id),
			'waiting_list' => $this->Course->getWaitingList($course_id)
		));
	}

	public function report_attendance($course_id) {
		$this->Course->id = $course_id;
		if (! $this->Course->exists()) {
			throw new NotFoundException('Invalid course selected.');
		}

		$course = $this->Course->read();
		$course_has_begun = $course['Course']['begins'] <= date('Y-m-d');
		$attendance_already_reported = $this->Course->attendanceIsReported($course_id);

		if ($this->request->is('post')) {
			if (! $course_has_begun) {
				throw new ForbiddenException('Cannot report attendance on a course before it begins');
			}

			if ($attendance_already_reported) {
				throw new ForbiddenException('Cannot report attendance: Attendance has already been reported for this course');
			}

			if (! empty($this->request->data['user_ids'])) {
				$this->loadModel('CourseRegistration');
				$registrations = $this->CourseRegistration->find('list', array(
					'conditions' => array(
						'CourseRegistration.course_id' => $course_id,
						'CourseRegistration.user_id' => $this->request->data['user_ids']
					)
				));
				foreach ($registrations as $reg_id => $display_field) {
					$this->CourseRegistration->id = $reg_id;
					$this->CourseRegistration->saveField('attended', 1);
					$user_id = $this->CourseRegistration->field('user_id');
					$cache_key = "getReviewMaterialsAccessExpiration($user_id)";
					Cache::delete($cache_key);
					// send email to student
				}
			}

			$this->Course->saveField('attendance_reported', true);
			if ($course['Course']['cost'] == 0) {
				$this->PrepaidReviewModule->assignToAttendingStudents($course_id);
			}
			$this->Flash->success('Attendance reported.');
			$this->request->data = array();
		}

		$this->set(array(
			'title_for_layout' => 'Report Attendance',
			'course' => $course,
			'class_list' => $this->Course->getClassList($course_id),
			'course_has_begun' => $course_has_begun,
			'attendance_already_reported' => $attendance_already_reported
		));
	}
}
