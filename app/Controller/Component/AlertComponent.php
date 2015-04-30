<?php
App::uses('Component', 'Controller');
class AlertComponent extends Component {
	public $components = array('Cookie');
	public $controller;

	public function initialize(Controller $controller) {
		$this->controller = $controller;
	}

	public function setAlerts() {
		// Check for new alerts every ten minutes
		$recheck = ! $this->Cookie->check('alerts_last_checked') || $this->Cookie->read('alerts_last_checked') < strtotime('10 minutes ago');
		if ($recheck) {
			$user_roles = $this->controller->__getUserRoles();
			if (in_array('instructor', $user_roles)) {
				$this->__setInstructorAlerts();
			}
			if (in_array('admin', $user_roles)) {
				$this->__setAdminAlerts();
			}
			$this->Cookie->write('alerts_last_checked', time());
		}

		$this->controller->set('alerts', $this->Cookie->read('alerts'));
	}

	protected function __setInstructorAlerts() {
		$instructor_id = $this->Auth->user('id');
		$this->loadModel('Course');

		$course_id = $this->Course->instructorCanReportAttendance($instructor_id);
		if ($course_id) {
			$url = Router::url(array(
				'controller' => 'courses',
				'action' => 'report_attendance',
				'id' => $course_id,
				$this->params['prefix'] => false
			));
			$this->Cookie->write('alerts.instructor_attendance', 'Please <strong><a href="'.$url.'">report attendance</a></strong> for your recent course.');
		}

		$this->loadModel('StudentReviewModule');
		if ($this->StudentReviewModule->paymentNeeded($instructor_id)) {
			$url = Router::url(array(
				'instructor' => true,
				'controller' => 'products',
				'action' => 'student_review_modules'
			));
			$this->Cookie->write('alerts.instructor_srm_payment', 'Please <strong><a href="'.$url.'">submit payment</a></strong> for the Student Review Modules used in your recent course.');
		}
	}

	public function __setAdminAlerts() {
		$this->loadModel('Testimonial');
		if ($this->Testimonial->approvalNeeded()) {
			$url = Router::url(array(
				'controller' => 'testimonials',
				'action' => 'manage',
				$this->params['prefix'] => false
			));
			$this->Cookie->write('alerts.admin_testimonials', 'Please <strong><a href="'.$url.'">approve or delete</a></strong> new testimonial(s).');
		}
	}
}