<?php
App::uses('AppController', 'Controller');
class RolesController extends AppController {
	public function beforeFilter() {
		parent::beforeFilter();
		$this->Auth->deny();
	}
}