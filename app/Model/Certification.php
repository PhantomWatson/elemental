<?php
App::uses('AppModel', 'Model');
/**
 * Certification Model
 *
 * @property User $User
 */
class Certification extends AppModel {
	public $displayField = 'date_expires';
	public $validate = array(
		'user_id' => array(
			'numeric' => array(
				'rule' => array('numeric'),
				'message' => 'User not specified'
			)
		)
	);
	public $belongsTo = array(
		'User' => array(
			'className' => 'User',
			'foreignKey' => 'user_id'
		),
		'Image' => array(
			'className' => 'Image',
			'foreignKey' => 'image_id'
		)
	);

	public function getExpiration($instructor_id) {
		$result = $this->find(
			'first',
			array(
				'conditions' => array(
					'Certification.instructor_id' => $instructor_id
				),
				'fields' => array(
					'Certification.date_expires'
				),
				'order' => 'Certification.date_expires DESC'
			)
		);
		return empty($result) ? null : $result['Certification']['date_expires'];
	}

	public function hasValid($instructor_id) {
		$expiration = $this->getExpiration($instructor_id);
		return $expiration != null && $expiration >= date('Y-m-d');
	}
}