<?php
App::uses('AppModel', 'Model');
class Role extends AppModel {
	public $displayField = 'name';
	public $hasAndBelongsToMany = array(
		'User' => array(
			'className' => 'User'
		)
    );

	public function getIdWithName($role_name) {
		$result = $this->find(
			'list',
			array(
				'conditions' => array(
					'Role.name' => $role_name
				)
			)
		);
		if (empty($result)) {
			return false;
		}
		$role_ids = array_keys($result);
		return $role_ids[0];
	}
}