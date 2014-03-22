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
			'add_students',
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
			'add_students'.
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
		$this->set(array(
			'title_for_layout' => 'Upcoming Courses',
			'courses' => $this->paginate(),
			'courses_registered_for' => $this->User->coursesRegisteredFor($this->Auth->user('id'))
		));
	}

/**
 * view method
 *
 * @param string $id
 * @return void
 */
	public function view($id = null) {
		$this->Course->id = $id;
		if (!$this->Course->exists()) {
			throw new NotFoundException('Sorry, we couldn\'t find that course.');
		}
		$course = $this->Course->read(null, $id);
		$this->loadModel('User');
		$courses_registered_for = $this->User->coursesRegisteredFor($this->Auth->user('id'));
		$this->set(array(
			'title_for_layout' => 'Course Details: '.date('F j, Y', strtotime($course['CourseDate'][0]['date'])),
			'course' => $course,
			'registration_id' => isset($courses_registered_for[$id])
				? $courses_registered_for[$id]
				: false
		));
	}

/**
 * add method
 *
 * @return void
 */
	public function add() {
		if ($this->request->is('post')) {
			$this->Course->create();
			$this->request->data['Course']['user_id'] = $this->Auth->user('id');
			if ($this->Course->saveAssociated($this->request->data)) {
				$this->Flash->success('The course has been added');
				$this->redirect(array('action' => 'manage'));
			} else {
				$this->Flash->error('The course could not be added. Please try again.');
			}
		}
		$this->set(array(
			'title_for_layout' => 'Schedule a Course'
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
			'title_for_layout' => 'Edit Course'
		));
		$this->set(compact(
			'class_list_count', 'waiting_list_count'
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

	public function register($id = null) {
		// Check course exists
		$this->Course->id = $id;
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
					'course' => $id
				)
			));
			$this->Flash->notification('Before registering, you\'ll need to create an account or <a href="'.$login_url.'">log into your existing account</a>.');
			$this->redirect(array(
				'controller' => 'users',
				'action' => 'register',
				'?' => array(
					'course' => $id
				)
			));
		}

		$this->loadModel('Release');
		$this->set(array(
			'course' => $course,
			'release_submitted' => $this->Release->isSubmitted($user_id, $id),
			'title_for_layout' => 'Register for a Course'
		));
	}

	private function complete_registration($course_id) {
		$this->Course->id = $course_id;
		$course = $this->Course->read();
		$user_id = $this->Auth->user('id');

		// Confirm receipt of release form
		if (! $this->Release->isSubmitted($user_id, $course_id)) {
			$this->Flash->error('Before you complete your registration, you msut first submit a liability release agreement.');
			$this->redirect($this->referer());
		}

		$course_full = count($course['CourseRegistration']) >= $course['Course']['max_participants'];
		$this->loadModel('CourseRegistration');
		$this->CourseRegistration->create(array(
			'course_id' => $course_id,
			'user_id' => $user_id,
			'waiting_list' => $course_full
		));
		if ($this->CourseRegistration->save()) {
			if ($course_full) {
				$message = 'You are now on this course\'s waiting list. You may be contacted by an instructor if space becomes available.';
			} else {
				$message = 'You are now registered for this course.';
			}
			if ($this->__sendRegisteredEmail($course_id, $user_id)) {
				$message .= 'You should be receiving an email shortly with information about your registration.';
			} else {
				$this->Flash->error('Error sending registration email.');
			}
			$this->Flash->success($message);
		} else {
			$this->Flash->error('There was an error registering you for this course. Please try again or <a href="/contact">contact us</a> if you need assistance.');
		}
		$this->redirect(array(
			'action' => 'register',
			'id' => $id
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

	public function add_students($course_id) {
		$this->Course->id = $course_id;
		if (! $this->Course->exists($course_id)) {
			throw new NotFoundException('Invalid course selected.');
		}

		$course = $this->Course->read();
		$class_full = $course['Course']['max_participants'] <= count($course['CourseRegistration']);
		$password = null;

		if ($this->request->is('post') || $this->request->is('put')) {

			// Clean data
			App::uses('Sanitize', 'Utility');
			$this->request->data['User']['name'] = trim($this->request->data['User']['name']);
			$this->request->data['User']['phone'] = trim($this->request->data['User']['phone']);
			$this->request->data['User']['email'] = trim(strtolower($this->request->data['User']['email']));
			$this->request->data['User']['role'] = 'student';
			$this->request->data['User'] = Sanitize::clean($this->request->data['User']);

			$this->loadModel('User');
			$this->User->create($this->request->data);
			if ($this->User->validates()) {
				$user_id = $this->User->findIdByEmail($this->request->data['User']['email']);

				// If user account already exists
				if ($user_id) {

					// Add details if they're missing from the existing account
					$this->User->id = $user_id;
					foreach (array('name', 'phone') as $field) {
						$instructor_provided = ! empty($this->request->data['User'][$field]);
						$existing_field = $this->User->field($field);
						$user_provided = ! empty($existing_field);
						if ($instructor_provided && ! $user_provided) {
							$this->User->saveField($field, $this->request->data['User'][$field]);
							$this->Flash->success(ucwords($field).' added for student.');
						}

					}

				// If user account must be created
				} else {
					$password = $this->User->randomPassword();
					$this->request->data['User']['password'] = $this->Auth->password($password);
					if ($this->User->save($this->request->data)) {
						$user_id = $this->User->id;
					} else {
						$this->Flash->error('There was an error creating an account for this user.');
					}
				}

				// If there are no errors
				if ($user_id) {
					// Already registered for course
					if ($this->User->registeredForCourse($user_id, $course_id)) {
						$this->Flash->notification($this->request->data['User']['name'].' was already registered for this course.');

					// Register for course
					} else {
						$this->loadModel('CourseRegistration');
						$this->CourseRegistration->create(array(
							'user_id' => $user_id,
							'course_id' => $course_id,
							'waiting_list' => $class_full
						));
						if ($this->CourseRegistration->save()) {
							$message = $this->request->data['User']['name'].' has been registered for this course.';
							$this->request->data = array();
							if ($this->__sendRegisteredEmail($course_id, $user_id, $password)) {
								$message .= ' The student should be receiving an email shortly with information about this registration.';
							} else {
								$this->Flash->error('Error sending email to student.');
							}
							$this->Flash->success($message);
						} else {
							$this->Flash->error('There was an error registering this student for this course.');
						}
					}
				}
			}
		}

		$this->set(array(
			'title_for_layout' => 'Add a Student to a Course',
			'course_id' => $course_id,
			'course' => $course,
			'class_full' => $class_full
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

		if ($this->request->is('post')) {
			if (empty($this->request->data['user_ids'])) {
				$this->Flash->error('No students selected.');
			} else {
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
					// send email to student
				}
				$this->Flash->success('Attendance reported.');
				$this->request->data = array();
			}
		}

		$this->set(array(
			'title_for_layout' => 'Report Attendance',
			'course' => $this->Course->read(),
			'class_list' => $this->Course->getClassList($course_id)
		));
	}
}
