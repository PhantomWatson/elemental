<?php
App::uses('AppModel', 'Model');
/**
 * CourseRegistration Model
 *
 * @property Course $Course
 */
class CourseRegistration extends AppModel {
/**
 * Display field
 *
 * @var string
 */
	public $displayField = 'course_id';
/**
 * Validation rules
 *
 * @var array
 */
	public $validate = array(
		'user_id' => array(
			'numeric' => array(
				'rule' => array('numeric'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'course_id' => array(
			'numeric' => array(
				'rule' => array('numeric'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'reminder_text' => array(
			'numeric' => array(
				'rule' => array('numeric'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'waiting_list' => array(
			'boolean' => array(
				'rule' => array('boolean'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
	);

	//The Associations below have been created with all possible keys, those that are not needed can be removed

/**
 * belongsTo associations
 *
 * @var array
 */
	public $belongsTo = array(
		'Course' => array(
			'className' => 'Course',
			'foreignKey' => 'course_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
		'User' => array(
			'className' => 'User',
			'foreignKey' => 'user_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		)
	);

	public $autoRefundDeadline = '3 days';

	public function notAlreadyRegistered($data) {
    	$count = $this->find('count', array(
			'conditions' => array(
				'CourseRegistration.email' => strtolower($data['email']),
    			'CourseRegistration.course_id' => strtolower($data['course_id'])
    		)
		));
		return $count == 0;
    }

    public function getInstructorId($id) {
    	$course_id = $this->field('course_id');
		if (! $course_id) {
			return null;
		}
		$this->Course->id = $course_id;
		return $this->Course->field('user_id');
    }

	/**
	 * Returns the security hash corresponding to the specified course registration
	 *
	 * @param int|string $id ID of record in course_registrations table
	 * @return string
	 */
    public function getUnregisterHash($id) {
		$salt = Configure::write('unregistration_hash_salt');

    	return md5($id . $salt);
    }

	public function userIsRegistered($user_id, $course_id) {
		$count = $this->find('count', array(
			'conditions' => array(
				'CourseRegistration.user_id' => $user_id,
    			'CourseRegistration.course_id' => $course_id
    		)
		));
		return $count > 0;
	}

	/**
	 * Returns TRUE if User is currently registered for Course
	 * @param int $user_id
	 * @param int $course_id
	 * @return boolean
	 */
	public function isOnWaitingList($user_id, $course_id) {
		if (! $this->User->exists($user_id)) {
			return false;
		}
		if (! $this->Course->exists($course_id)) {
			return false;
		}
		$result = $this->find('count', array(
			'conditions' => array(
				'CourseRegistration.user_id' => $user_id,
				'CourseRegistration.course_id' => $course_id,
				'CourseRegistration.waiting_list' => 1
			)
		));
		return $result > 0;
	}

	public function getRegistrationId($user_id, $course_id) {
		$result = $this->find('first', array(
			'conditions' => array(
				'CourseRegistration.user_id' => $user_id,
    			'CourseRegistration.course_id' => $course_id
    		),
    		'fields' => array(
    			'CourseRegistration.id'
    		),
    		'contain' => false
		));
		if (empty($result)) {
			return null;
		}
		return $result['CourseRegistration']['id'];
	}
}
