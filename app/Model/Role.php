<?php
App::uses('AppModel', 'Model');
class Role extends AppModel {
	public $displayField = 'name';
	public $hasAndBelongsToMany = array(
		'User' => array(
			'className' => 'User'
		)
    );
}