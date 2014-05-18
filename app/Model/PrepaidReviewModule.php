<?php
App::uses('AppModel', 'Model');
class PrepaidReviewModule extends AppModel {
	public $displayField = 'course_id';
	public $validate = array(
		'purchase_id' => array(
			'notempty' => array(
				'rule' => array('notempty')
			)
		),
		'instructor_id' => array(
			'notempty' => array(
				'rule' => array('notempty')
			)
		)
	);
	public $belongsTo = array(
		'Purchase' => array(
			'className' => 'Purchase',
			'foreignKey' => 'purchase_id'
		),
		'Instructor' => array(
			'className' => 'User',
			'foreignKey' => 'instructor_id'
		),
		'Course' => array(
			'className' => 'Course',
			'foreignKey' => 'course_id'
		),
		'Student' => array(
			'className' => 'User',
			'foreignKey' => 'student_id'
		)
	);
}