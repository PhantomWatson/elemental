<?php
App::uses('AppModel', 'Model');
/**
 * Course Model
 *
 * @property CourseRegistration $CourseRegistration
 */
class Course extends AppModel {
/**
 * Display field
 *
 * @var string
 */
	public $displayField = 'begins';
/**
 * Validation rules
 *
 * @var array
 */
	public $validate = array(
		'location' => array(
			'notempty' => array(
				'rule' => 'notBlank',
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'address' => array(
			'notempty' => array(
				'rule' => 'notBlank',
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'max_participants' => array(
			'naturalNumber' => array(
				'rule' => array('naturalNumber'),
				'allowEmpty' => false,
				//'message' => 'Your custom message here',
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
			'notLessThanExistingClass' => array(
				'rule' => array('validateEditedClassSize'),
				'on' => 'update'
			)
		),
		'cost' => array(
			'naturalNumber' => array(
				'rule' => array('naturalNumber', true), // TRUE means zero is accepted
				'allowEmpty' => true,
				//'message' => 'Your custom message here',
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
			'minimumCost' => array(
				'rule' => array('validateCost')
			)
		),
		'user_id' => array(
			'numeric' => array(
				'rule' => array('numeric'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		)
	);

	//The Associations below have been created with all possible keys, those that are not needed can be removed

/**
 * hasMany associations
 *
 * @var array
 */
	public $hasMany = array(
		'CourseDate' => array(
			'className' => 'CourseDate',
			'foreignKey' => 'course_id',
			'dependent' => true,
			'conditions' => '',
			'fields' => '',
			'order' => array('CourseDate.date ASC', 'CourseDate.start_time ASC'),
			'limit' => '',
			'offset' => '',
			'exclusive' => '',
			'finderQuery' => '',
			'counterQuery' => ''
		),
		'CoursePayment' => array(
			'className' => 'CoursePayment',
			'foreignKey' => 'course_id',
			'dependent' => false,
			'conditions' => '',
			'fields' => '',
			'order' => array('CoursePayment.created ASC'),
			'limit' => '',
			'offset' => '',
			'exclusive' => '',
			'finderQuery' => '',
			'counterQuery' => ''
		),
		'CourseRegistration' => array(
			'className' => 'CourseRegistration',
			'foreignKey' => 'course_id',
			'dependent' => false,
			'conditions' => '',
			'fields' => '',
			'order' => '',
			'limit' => '',
			'offset' => '',
			'exclusive' => '',
			'finderQuery' => '',
			'counterQuery' => ''
		),
		'StudentReviewModule' => array(
			'className' => 'StudentReviewModule',
			'foreignKey' => 'course_id',
			'dependent' => false
		)
	);

	public $belongsTo = array(
		'User' => array(
			'className' => 'User',
			'foreignKey' => 'user_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		)
	);

	public function beforeSave($options = array()) {
		$start_date = $this->getStartDate();
		if ($start_date) {
			$this->data['Course']['begins'] = $start_date;
		}

		return true;
	}

	public function beforeValidate($options = array()) {
		if (isset($this->data['Course']['cost_dollars'])) {
			$dollars = $this->data['Course']['cost_dollars'];
			if (empty($dollars)) {
				$dollars = 0;
			} elseif (! $this->isWholeNumber($dollars)) {
				$this->validationErrors['cost_dollars'] = "$dollars is not a whole number";
				return false;
			}

			// Multiply by 100 so the cost is stored in cents
			$cost = $dollars * 100;

			if (isset($this->data['Course']['cost_cents'])) {
				$cents = $this->data['Course']['cost_cents'];
				if (empty($cents)) {
					$cents = 0;
				} elseif (! $this->isWholeNumber($cents)) {
					$this->validationErrors['cost_cents'] = "$cents is not a whole number";
					return false;
				}

				$cost += $cents;
			}

			$this->data['Course']['cost'] = $cost;
		}
	}

	public function validateEditedClassSize($check) {
		$new_size = $check['max_participants'];
		$course_id = $this->data['Course']['id'];
		$class_size = count($this->getClassList($course_id));
		if ($new_size < $class_size) {
			return "You cannot reduce the size of this course below $class_size, since $class_size students have already registered.";
		}
		return true;
	}

	public function validateCost($check) {
		$cost = $check['cost'];
		if ($cost > 0 && $cost < 500) {
			return 'If a registration fee is charged for this course, it must be at least $5.';
		}
		return true;
	}

	public function afterFind($results, $primary = false) {
		foreach ($results as $key => $val) {

			// Convert cost from cents into dollars
			if (isset($val['Course']['cost'])) {
				$cost = $results[$key]['Course']['cost'] / 100;
				$cost = number_format($cost, 2);
				$results[$key]['Course']['cost'] = $cost;
				list($dollars, $cents) = explode('.', $cost);
				$results[$key]['Course']['cost_dollars'] = $dollars;
				$results[$key]['Course']['cost_cents'] = $cents;
			}
		}
		return $results;
	}

	public function isWholeNumber($var) {
		return (is_numeric($var) && (intval($var) == floatval($var)));
	}

	public function getStartDate() {
		if (! isset($this->data['CourseDate']) || empty($this->data['CourseDate'])) {
			return null;
		}

		$dates = array();
		foreach ($this->data['CourseDate'] as $date) {
			if (isset($date['CourseDate']['date'])) {
				$dates[] = $date['CourseDate']['date'];
			} elseif (isset($date['date'])) {
				$dates[] = $date['date'];
			}
		}
		sort($dates);
		$first_date = reset($dates);
		if (is_array($first_date)) {
			$first_date = $first_date['year'].'-'.$first_date['month'].'-'.$first_date['day'];
		}
		return $first_date;
	}

	/**
	 * Returns the last date of a course
	 * @param int $course_id
	 * @return string|null
	 */
	public function getEndDate($course_id) {
		$result = $this->CourseDate->find(
			'first',
			array(
				'conditions' => array(
					'CourseDate.course_id' => $course_id
				),
				'contain' => false,
				'fields' => array(
					'CourseDate.date'
				),
				'order' => array(
					'CourseDate.date' => 'DESC'
				)
			)
		);
		return $result ? $result['CourseDate']['date'] : null;
	}

	public function getClassList($course_id, $waiting_list = false) {
		return $this->CourseRegistration->find('all', array(
			'conditions' => array(
				'CourseRegistration.course_id' => $course_id,
				'CourseRegistration.waiting_list' => $waiting_list
			),
			'order' => 'CourseRegistration.waiting_list ASC',
			'contain' => array(
				'User' => array(
					'fields' => array(
						'User.id',
						'User.name',
						'User.email',
						'User.phone'
					)
				)
			)
		));
	}

	public function getWaitingList($course_id) {
		return $this->getClassList($course_id, true);
	}

	public function elevateWaitingListMembers($course_id) {
		$this->id = $course_id;

		if (! $this->exists($course_id)) {
			return false;
		}

		// Confirm that the course hasn't begun yet
		$result = $this->CourseDate->find('first', array(
			'conditions' => array(
				'CourseDate.course_id' => $course_id
			),
			'order' => array(
				'CourseDate.date ASC'
			),
			'fields' => array(
				'CourseDate.id',
				'CourseDate.date'
			),
			'limit' => 1,
			'contain' => false
		));
		if (empty($result)) {
			return false;
		}
		$begins = $result['CourseDate']['date'];
		if ($begins <= date('Y-m-d')) {
			return false;
		}

		// Confirm that there's open space in this course
		$size = $this->field('max_participants');
		$class_list = $this->CourseRegistration->find('count', array(
			'conditions' => array(
				'CourseRegistration.course_id' => $course_id,
				'CourseRegistration.waiting_list' => 0
			)
		));
		$spaces_open = $size - $class_list;
		if ($spaces_open < 1) {
			return false;
		}

		// Confirm that there's someone on the waiting list
		$waiting_list = $this->CourseRegistration->find('all', array(
			'conditions' => array(
				'CourseRegistration.course_id' => $course_id,
				'CourseRegistration.waiting_list' => 1
			),
			'order' => array(
				'CourseRegistration.created ASC'
			),
			'contain' => false
		));
		$free_class = $this->field('cost') == 0;
		foreach ($waiting_list as $wl_member) {
			if ($spaces_open < 1) {
				break;
			}

			$student_id = $wl_member['CourseRegistration']['user_id'];
			$course_id = $wl_member['CourseRegistration']['course_id'];

			// Automatically elevate students
			if ($free_class) {

				$reg_id = $wl_member['CourseRegistration']['id'];
				$this->CourseRegistration->id = $reg_id;
				$this->CourseRegistration->saveField('waiting_list', 0);
				$this->sendWaitingListElevationEmail($course_id, $student_id);
				$spaces_open--;

			// Send out emails announcing open space (that students must pay to fill)
			} else {

				$this->sendWaitingListOpeningEmail($course_id, $student_id);
			}
		}

		// Return true iff a student was automatically elevated
		if ($free_class) {
			return ! empty($waiting_list);
		}

		return false;
	}

	public function sendWaitingListOpeningEmail($course_id, $student_id) {
		// Get course
		$this->id = $course_id;
		$course = $this->read();

		// Get student
		$this->User->id = $student_id;
		$student = $this->User->read();

		// Get instructor
        $instructor_id = $course['Course']['user_id'];
        $instructor = $this->User->find('first', array(
            'conditions' => array(
                'User.id' => $instructor_id
            ),
            'contain' => false
        ));

		// Get registration info
		$registration = $this->CourseRegistration->find('first', array(
			'conditions' => array(
				'CourseRegistration.course_id' => $course_id,
				'CourseRegistration.user_id' => $student_id
			),
			'contain' => false
		));

		if (empty($course) || empty($student) || empty($registration)) {
			return false;
		}

		// Get URLs
		$course_view_url = Router::url(array(
			'controller' => 'courses',
			'action' => 'view',
			'id' => $course['Course']['id']
		), true);
		$reg_url = Router::url(array(
			'controller' => 'courses',
			'action' => 'register',
			'id' => $course['Course']['id']
		), true);

		// Send email
		App::uses('CakeEmail', 'Network/Email');
		$Email = new CakeEmail('default');
		$Email->to($student['User']['email']);
		$Email->replyTo($instructor['User']['email']);
		$Email->returnPath($instructor['User']['email']);
		$Email->subject('Space is available in an upcoming Elemental course');
		$Email->template('wl_opening', 'default');
		$Email->viewVars(compact(
			'student',
			'course_view_url',
			'reg_url'
		));
		return $Email->send();
	}

	public function sendWaitingListElevationEmail($course_id, $student_id) {
		// Get course
		$this->id = $course_id;
		$course = $this->read();

		// Get student
		$this->User->id = $student_id;
		$student = $this->User->read();

		// Get registration info
		$registration = $this->CourseRegistration->find('first', array(
			'conditions' => array(
				'CourseRegistration.course_id' => $course_id,
				'CourseRegistration.user_id' => $student_id
			),
			'contain' => false
		));

		if (empty($course) || empty($student) || empty($registration)) {
			return false;
		}

		// Get hash used in the secure unregistering link
		$reg_id = $registration['CourseRegistration']['id'];
		$unregister_hash = $this->CourseRegistration->getUnregisterHash($reg_id);

		// Get instructor email
		$instructor_id = $course['Course']['user_id'];
		$this->User->id = $instructor_id;
		$instructor = $this->User->read();

		// Get URLs
		$course_view_url = Router::url(array(
			'controller' => 'courses',
			'action' => 'view',
			'id' => $course['Course']['id']
		), true);
		$unreg_url = Router::url(array(
			'controller' => 'course_registrations',
			'action' => 'unregister_via_link',
			'id' => $registration['CourseRegistration']['id'],
			'hash' => $unregister_hash
		), true);

		// Send email
		App::uses('CakeEmail', 'Network/Email');
		$Email = new CakeEmail('default');
		$Email->to($student['User']['email']);
		$Email->replyTo($instructor['User']['email']);
		$Email->returnPath($instructor['User']['email']);
		$Email->subject('You have been registered for an Elemental course');
		$Email->template('elevated', 'default');
		$Email->viewVars(compact(
			'course',
			'student',
			'registration',
			'instructor',
			'course_view_url',
			'unreg_url'
		));
		return $Email->send();
	}

	/**
	 * Sends email with registration information.
	 * If an account has been created for this student by an instructor, the generated $password will be non-null.
	 * Outputs either error message strings or an array of the results of sending the email.
	 * @param int $course_id
	 * @param int $user_id
	 * @param string|null $password
	 * @return string|array
	 */
	public function sendRegistrationEmail($course_id, $student_id, $password = null) {
		// Get course
		$this->id = $course_id;
		$course = $this->read();
		if (empty($course)) {
			return 'Cannot send email to student. Invalid course selected.';
		}

		// Get student
		$this->User->id = $student_id;
		$student = $this->User->read();
		if (empty($student)) {
			return 'Cannot send email to student. Invalid student selected.';
		}

		// Get registration info
		$registration = $this->CourseRegistration->find('first', array(
			'conditions' => array(
				'CourseRegistration.course_id' => $course_id,
				'CourseRegistration.user_id' => $student_id
			),
			'contain' => false
		));
		if (empty($registration)) {
			return 'Cannot send email to student. Student not registered for course.';
		}

		// Get hash used in the secure unregistering link
		$reg_id = $registration['CourseRegistration']['id'];
		$unregister_hash = $this->CourseRegistration->getUnregisterHash($reg_id);

		// Get instructor email
		$instructor_id = $course['Course']['user_id'];
		$this->User->id = $instructor_id;
		$instructor = $this->User->read();

		// Get URLs
		$course_view_url = Router::url(array(
			'controller' => 'courses',
			'action' => 'view',
			'id' => $course['Course']['id']
		), true);
		$login_url = Router::url(array(
			'controller' => 'users',
			'action' => 'login'
		), true);
		$unreg_url = Router::url(array(
			'controller' => 'course_registrations',
			'action' => 'unregister_via_link',
			'id' => $registration['CourseRegistration']['id'],
			'hash' => $unregister_hash
		), true);

		// Send email
		App::uses('CakeEmail', 'Network/Email');
		$Email = new CakeEmail('default');
		$Email->to($student['User']['email']);
		$Email->replyTo($instructor['User']['email']);
		$Email->returnPath($instructor['User']['email']);
		if ($registration['CourseRegistration']['waiting_list']) {
			$Email->subject('You have been added to the waiting list for an Elemental course');
		} else {
			$Email->subject('You have been registered for an Elemental course');
		}
		$Email->template('registered', 'default');
		$Email->viewVars(compact(
			'course',
			'student',
			'registration',
			'instructor',
			'password',
			'course_view_url',
			'login_url',
			'unreg_url'
		));
		return $Email->send();
	}

	public function isFull($course_id) {
		$this->id = $course_id;
		$max_participants = $this->field('max_participants');
		$registered_count = $this->CourseRegistration->find('count', array(
			'conditions' => array(
				'CourseRegistration.course_id' => $course_id,
				'CourseRegistration.waiting_list' => 0,
			)
		));
		return $registered_count >= $max_participants;
	}

	public function paymentsReceived($course_id) {
		return $this->CoursePayment->find('count', array(
			'conditions' => array(
				'CoursePayment.course_id' => $course_id
			)
		));
	}

	public function attendanceIsReported($course_id) {
		$this->id = $course_id;
		return $this->field('attendance_reported');
	}

	public function sendSrmAvailableEmails($course_id) {
		$attending_students = $this->CourseRegistration->find(
			'all',
			array(
				'conditions' => array(
					'CourseRegistration.course_id' => $course_id,
					'CourseRegistration.attended' => true
				),
				'fields' => array(
					'CourseRegistration.id'
				),
				'contain' => array(
					'User' => array(
						'fields' => array(
							'User.name',
							'User.email'
						)
					)
				)
			)
		);

		if (empty($attending_students)) {
			return;
		}

		$this->id = $course_id;
		$this->User->id = $this->field('user_id');
		$instructor_email = $this->User->field('email');
		$review_module_link = Router::url(
			array(
				'controller' => 'products',
				'action' => 'student_review'
			),
			true
		);
		$contact_url = Router::url(
			array(
				'controller' => 'pages',
				'action' => 'contact'
			),
			true
		);

		foreach ($attending_students as $student) {
			$student_name = $student['User']['name'];
			$student_email = $student['User']['email'];

			// Send email
			App::uses('CakeEmail', 'Network/Email');
			$Email = new CakeEmail('default');
			$Email->to($student_email);
			$Email->replyTo($instructor_email);
			$Email->returnPath($instructor_email);
			$Email->subject('Elemental: Student Review Module now available');
			$Email->template('review_module_available', 'default');
			$Email->viewVars(compact(
				'student_name',
				'review_module_link',
				'contact_url',
				'instructor_email'
			));
			if (! $Email->send()) {
				throw new InternalErrorException('Error sending email to student ('.$student_email.')');
			}
		}
	}

	/**
	 * Returns the ID for a course that the instructor is due to report attendance for, or FALSE if none are found.
	 * @param int $instructor_id
	 * @return boolean|int
	 */
	public function instructorCanReportAttendance($instructor_id) {
		return $this->field(
			'id',
			array(
				'user_id' => $instructor_id,
				'attendance_reported' => false,
				'begins <=' => date('Y-m-d')
			)
		);
	}

	/**
	 * Returns array of course IDs that an instructor has reported attendance for
	 * @param int $instructor_id
	 * @return array
	 */
	public function getCoursesWithReportedAttendance($instructor_id) {
		$results = $this->find(
			'list',
			array(
				'conditions' => array(
					'Course.user_id' => $instructor_id,
					'Course.attendance_reported' => true
				)
			)
		);
		return empty($results) ? array() : array_keys($results);
	}
}
