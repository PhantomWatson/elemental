<?php
App::uses('AppModel', 'Model');
/**
 * Release Model
 *
 * @property User $User
 */
class Release extends AppModel {

/**
 * Display field
 *
 * @var string
 */
	public $displayField = 'user_id';

/**
 * Validation rules
 *
 * @var array
 */
	public $validate = array(
		'name' => array(
			'notEmpty' => array(
				'rule' => 'notBlank',
				'allowEmpty' => false,
				'required' => true
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
		),
		'age' => array(
			'numeric' => array(
				'rule' => array('numeric'),
				'message' => 'Please enter your numeric age',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
			'notEmpty' => array(
				'rule' => 'notBlank',
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'birthdate' => array(
			'rule'       => 'date',
			'message'    => 'Please enter a valid date',
			'allowEmpty' => false
		),
		'guardian_name' => array(
			'notEmpty' => array(
				'rule' => 'notBlank',
				'message' => 'This field cannot be left blank',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'guardian_phone' => array(
			'notEmpty' => array(
				'rule' => 'notBlank',
				'message' => 'This field cannot be left blank',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'no_phone' => array(
			'boolean' => array(
				'rule' => array('boolean'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'ip_address' => array(
			'notEmpty' => array(
				'rule' => 'notBlank',
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
		'User' => array(
			'className' => 'User',
			'foreignKey' => 'user_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		)
	);

	public function isSubmitted($user_id) {
		$count = $this->find('count', array(
			'conditions' => array(
				'Release.user_id' => $user_id
			)
		));
		return $count > 0;
	}

	public function getIdFromUserId($user_id) {
		$result = $this->find('list', array(
			'conditions' => array(
				'Release.user_id' => $user_id
			)
		));
		if (empty($result)) {
			return null;
		}
		$release_ids = array_keys($result);
		return $release_ids[0];
	}
}
