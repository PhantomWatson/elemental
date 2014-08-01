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
							'User.name'
						)
					)
				),
				'fields' => array(
					'Bio.id',
					'Bio.bio',
					'Bio.picture'
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
}
