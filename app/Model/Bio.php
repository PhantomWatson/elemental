<?php
App::uses('AppModel', 'Model');
/**
 * Bio Model
 *
 * @property User $User
 */
class Bio extends AppModel {
	public $displayField = 'bio';
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

	public function getForUser($user_id) {
		return $this->find(
			'first',
			array(
				'conditions' => array(
					'Bio.user_id' => $user_id
				),
				'contain' => array(
					'User' => array(
						'fields' => array(
							'User.id',
							'User.name',
							'User.email'
						)
					),
					'Image' => array(
						'fields' => array(
							'Image.id',
							'Image.filename'
						)
					)
				),
				'fields' => array(
					'Bio.id',
					'Bio.bio'
				)
			)
		);
	}

	public function getIdForUser($user_id) {
		return $this->field(
			'id',
			array(
				'Bio.user_id' => $user_id
			)
		);
	}

	public function getInstructorBios() {
		$instructors = $this->User->getCertifiedInstructorList();
		$bios = $this->find(
			'all',
			array(
				'conditions' => array(
					'Bio.user_id' => array_keys($instructors),
					'NOT' => array(
						'Bio.bio' => ''
					)
				),
				'contain' => array(
					'User' => array(
						'fields' => array(
							'User.id',
							'User.name',
							'User.email'
						)
					),
					'Image' => array(
						'fields' => array(
							'Image.id',
							'Image.filename'
						)
					)
				),
				'fields' => array(
					'Bio.id',
					'Bio.bio'
				)
			)
		);
		$retval = array();
		foreach ($bios as $bio) {
			$name_parts = explode(' ', $bio['User']['name']);
			$reversed_name = array_pop($name_parts).' '.implode(' ', $name_parts);
			$reversed_name .= $bio['User']['id'];	// Just in case two instructors have the same name
			$retval[$reversed_name] = $bio;
		}
		ksort($retval);
		return $retval;
	}
}