<?php
App::uses('AppModel', 'Model');
/**
 * Testimonial Model
 *
 */
class Testimonial extends AppModel {
/**
 * Display field
 *
 * @var string
 */
	public $displayField = 'author';
/**
 * Validation rules
 *
 * @var array
 */
	public $validate = array(
		'body' => array(
			'notempty' => array(
				'rule' => array('notempty'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
	);
	
	public $belongsTo = array(
		'User' => array(
			'className' => 'User',
			'foreignKey' => 'user_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		)
	);
}
