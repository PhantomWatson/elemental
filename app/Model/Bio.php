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
}
