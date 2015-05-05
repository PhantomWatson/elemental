<?php
App::uses('Component', 'Controller');
class CourseManagementComponent extends Component {
    public $components = array('Flash');
	public $controller;

	public function initialize(Controller $controller) {
		$this->controller = $controller;
	}
}