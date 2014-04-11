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
				'allowEmpty' => true,
				//'message' => 'Your custom message here',
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
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

	public function beforeSave($options) {
		$this->data['Course']['begins'] = $this->getStartDate();
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
		foreach ($waiting_list as $wl_member) {
			if ($spaces_open < 1) {
				break;
			}

			// Elevate student
			$reg_id = $wl_member['CourseRegistration']['id'];
			$this->CourseRegistration->id = $reg_id;
			$this->CourseRegistration->saveField('waiting_list', 0);

			// Send email
			$student_id = $wl_member['CourseRegistration']['user_id'];
			$course_id = $wl_member['CourseRegistration']['course_id'];
			$this->sendWaitingListElevationEmail($course_id, $student_id);

			$spaces_open--;
		}

		return ! empty($waiting_list);
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
}