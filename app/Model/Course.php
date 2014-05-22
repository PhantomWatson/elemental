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
				'rule' => array('notempty'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'address' => array(
			'notempty' => array(
				'rule' => array('notempty'),
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
			'usingPrepaidReviewModules' => array(
				'rule' => array('validateFreeClassSize')
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
			'dependent' => true,
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
			'dependent' => true,
			'conditions' => '',
			'fields' => '',
			'order' => '',
			'limit' => '',
			'offset' => '',
			'exclusive' => '',
			'finderQuery' => '',
			'counterQuery' => ''
		),
		'PrepaidReviewModule' => array(
			'className' => 'PrepaidReviewModule',
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
		$this->data['Course']['begins'] = $this->getStartDate();

		// If editing a free course, handle growing/shrinking of free classes
		if ($this->data['Course']['cost'] == 0 && isset($this->data['Course']['id'])) {
			$this->adjustReservedPrepaidReviewModules();
		}

		return true;
	}

	public function afterSave($created, $options = array()) {
		// If adding a free course, reserve PRMs
		if ($created && $this->data['Course']['cost'] == 0) {
			$instructor_id = $this->data['Course']['user_id'];
			$quantity = $this->data['Course']['max_participants'];
			$course_id = $this->id;
			$this->PrepaidReviewModule->assignToCourse($instructor_id, $quantity, $course_id);
		}
	}

	/**
	 * Handles when a free class size changes and either more
	 * PRMs need to be reserved for this course or reserved
	 * PRMs need to be put back into the 'available' pool
	 */
	public function adjustReservedPrepaidReviewModules() {
		$this->id = $this->data['Course']['id'];
		$new_size = $this->data['Course']['max_participants'];
		$old_size = $this->field('max_participants');
		$growth = $new_size - $old_size;

		// If growing
		if ($growth > 0) {
			$instructor_id = $this->field('instructor_id');
			$this->PrepaidReviewModule->assignToCourse($instructor_id, $growth, $this->id);

		// If shrinking
		} elseif ($growth < 0) {
			$shrinkage = -1 * $growth; // I WAS IN THE POOL
			$this->PrepaidReviewModule->releaseUnclaimedFromCourse($this->id, $shrinkage);
		}
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

	public function validateFreeClassSize($check) {
		$new_size = $check['max_participants'];
		$free = $this->data['Course']['cost'] == 0;

		// If course has a registration fee, any class size is allowed
		if (! $free) {
			return true;
		}

		App::uses('CakeSession', 'Model/Datasource');
		$Session = new CakeSession();
		$logged_in_user_id = $Session->read('Auth.User.id');

		// If editing
		if (isset($this->data['Course']['id'])) {
			$course_id = $this->data['Course']['id'];

			// In case an administrator is editing another instructor's course,
			// pull original instructor ID from DB
			$this->id = $course_id;
			$instructor_id = $this->field('instructor_id');

		// If adding
		} else {
			$course_id = null;
			$instructor_id = $logged_in_user_id;
		}

		$available_modules = $this->PrepaidReviewModule->getAvailableCount($instructor_id);

		// If editing
		if ($course_id) {
			$old_size = $this->field('max_participants');

			// No validation needed if not increasing class size
			if ($new_size <= $old_size) {
				return true;
			}

			// Growth limited by available modules
			$growth = $new_size - $old_size;
			if ($growth <= $available_modules) {
				return true;
			}

		// If adding
		} elseif ($new_size <= $available_modules) {
			return true;
		}

		// Not enough prepaid modules
		$is_instructor = $instructor_id == $logged_in_user_id;
		if ($available_modules) {
			$message = $is_instructor
				? 'You only have '
				: 'This instructor only has ';
			$message .= ' enough available Prepaid Review Modules to ';
			$limit = $old_size + $available_modules;
			if ($course_id) {
				$message .= 'increase this class size to '.$limit;
			} else {
				$message .= 'create a free course for '.$limit.' '.__n('student', 'students', $limit);
			}
		} else {
			$message = $is_instructor
				? 'You have '
				: 'This instructor has ';
			$message .= 'no available Prepaid Review Modules';
		}
		$this->validate['usingPrepaidReviewModules']['message'] = $message;
		return false;
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

	/**
	 * Generates the JSON Web Token for a Google Wallet purchase button
	 * @param int $course_id
	 * @param int $user_id
	 * @throws NotFoundException
	 * @return string
	 */
	public function getJWT($course_id, $user_id) {
		$course = $this->find('first', array(
			'conditions' => array(
				'Course.id' => $course_id
			),
			'contain' => array(
				'CourseDate'
			)
		));
		if (empty($course)) {
			throw new NotFoundException('Course #'.$course_id.' not found.');
		}

		// Gather variables for purchase description
		$dates = '';
		$count = count($course['CourseDate']);
		foreach ($course['CourseDate'] as $i => $date) {
			$timestamp = strtotime($date['date']);
			if ($count == 2 && $i == 1) {
				$dates .= ' and ';
			} elseif ($count > 2) {
				if ($i == $count - 1) {
					$dates .= ', and ';
				} elseif ($i > 0) {
					$dates .= ', ';
				}
			}
			$dates .= date('F j, Y', $timestamp);
		}
		$location = $course['Course']['city'].', '.$course['Course']['state'];

		$seller_identifier = Configure::read('google_waller_seller_id');
		$seller_secret = Configure::read('google_wallet_seller_secret');

		// Generate a JWT (JSON Web Token) for this item
		// $payload parameters reference: https://developers.google.com/commerce/wallet/digital/docs/jsreference#jwt
		App::import('Vendor', 'JWT');
		$payload = array(
			"iss" => $seller_identifier,
			"aud" => "Google",
			"typ" => "google/payments/inapp/item/v1",
			"exp" => time() + 3600,
			"iat" => time(),
			"request" => array(
				"name" => 'Elemental Course Registration',
				"description" => 'Registration for an Elemental Sexual Assault Protection course taking place on '.$dates.' in '.$location,
				"price" => $course['Course']['cost'],
				"currencyCode" => "USD",
				"sellerData" => "type:course,user_id:$user_id,course_id:$course_id"
			)
		);
		return JWT::encode($payload, $seller_secret);
	}

	public function sendRefundEmail($course_id, $user_id) {
		$payment = $this->CoursePayment->find('first', array(
			'conditions' => array(
				'CoursePayment.course_id' => $course_id,
				'CoursePayment.user_id' => $user_id
			),
			'contain' => false
		));
		if (empty($payment)) {
			// No payment received, so no refund is necessary
			return true;
		}

		$course = $this->find('first', array(
			'conditions' => array(
				'Course.id' => $course_id
			),
			'contain' => false
		));
		$User = ClassRegistry::init('User');
		$user = $User->find('first', array(
			'conditions' => array(
				'User.id' => $user_id
			),
			'contain' => false
		));
		$pts = strtotime($payment['CoursePayment']['created']);
		$payment_time = date('F j, Y, g:ia', $pts).' EST';
		$course_url = Router::url(array(
			'controller' => 'courses',
			'action' => 'view',
			'id' => $course_id
		), true);
		$details = array(
			'Student Name' => $user['User']['name'],
			'Student Email' => $user['User']['email'],
			'Student Phone' => $user['User']['phone'],
			'Paid' => $payment_time,
			'Cost' => '$'.$course['Course']['cost'],
			'Course' => $course_url
		);

		// Send email
		App::uses('CakeEmail', 'Network/Email');
		$Email = new CakeEmail('default');
		$Email->to(Configure::read('refund_email'));
		$Email->replyTo(Configure::read('admin_email'));
		$Email->returnPath(Configure::read('admin_email'));
		$Email->subject('Elemental: Refund needed');
		$Email->template('refund', 'default');
		$Email->viewVars(compact('details'));
		return $Email->send();
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
}