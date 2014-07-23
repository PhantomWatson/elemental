<?php
App::uses('AppModel', 'Model');
/**
 * InstructorAgreement Model
 *
 * @property User $User
 */
class InstructorAgreement extends AppModel {
	public $displayField = 'instructor_id';
	public $validate = array(
		'instructor_id' => array(
			'numeric' => array(
				'rule' => array('numeric')
			)
		)
	);

	public $belongsTo = array(
		'User' => array(
			'className' => 'User',
			'foreignKey' => 'instructor_id'
		)
	);

	public function hasAgreed($instructor_id) {
		$result = $this->find(
			'count',
			array(
				'conditions' => array(
					'InstructorAgreement.instructor_id' => $instructor_id
				)
			)
		);
		return $result > 0;
	}
}