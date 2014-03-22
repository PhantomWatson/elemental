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

	public function notAlreadyRegistered($data) {
    	$result = $this->find('first', array(
			'conditions' => array(
				'CourseRegistration.email' => strtolower($data['email']),
    			'CourseRegistration.course_id' => strtolower($data['course_id'])
    		),
			'fields' => array('id'),
			'contains' => false
		));
		return empty($result);
    }

    public function getInstructorId($id) {
    	$course_id = $this->field('course_id');
		if (! $course_id) {
			return null;
		}
		$this->Course->id = $course_id;
		return $this->Course->field('user_id');
    }

    public function getUnregisterHash($id) {
    	return md5($id.'unregister_me_securely');
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
}
