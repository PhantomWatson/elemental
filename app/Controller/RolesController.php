<?php
App::uses('AppController', 'Controller');
class RolesController extends AppController {
	public function beforeFilter() {
		parent::beforeFilter();
		$this->Auth->deny();
	}

	/**
	 * Populate roles_users join table based on User.role values (run once, then delete)
	 */
	public function populate_associations() {
		$this->loadModel('User');
		$users = $this->User->find('all');
		foreach ($users as $user) {
			switch ($user['User']['role']) {
				case 'admin':
					$role_id = 1;
					break;
				case 'instructor':
					$role_id = 2;
					break;
				case 'trainee':
					$role_id = 3;
					break;
				case 'student':
					$role_id = 4;
					break;
				case '':
					continue;
				default:
					echo "User {$user['User']['id']} has weirdo role {$user['User']['role']}<br />";
			}
			$this->User->saveAll(array(
				'User' => array(
					'id' => $user['User']['id']
				),
				'Role' => array(
					$role_id
				)
			));
		}
		$this->render('/Pages/home');
	}
}