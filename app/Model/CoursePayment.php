<?php
App::uses('AppModel', 'Model');
class CoursePayment extends AppModel {
	public $displayField = 'course_id';
	public $validate = array(
		'course_id' => array(
			'numeric' => array(
				'rule' => array('numeric'),
				//'allowEmpty' => false,
				//'message' => 'Your custom message here',
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			)
		),
		'user_id' => array(
			'numeric' => array(
				'rule' => array('numeric'),
				//'allowEmpty' => false,
				//'message' => 'Your custom message here',
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			)
		),
		'order_id' => array(
			'notempty' => array(
				'rule' => 'notBlank'
			)
		)
	);

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

	public function isPaid($user_id, $course_id) {
		$result = $this->find('count', array(
			'conditions' => array(
				'CoursePayment.user_id' => $user_id,
				'CoursePayment.course_id' => $course_id,
				'CoursePayment.refunded' => null
			)
		));
		return $result > 0;
	}
}