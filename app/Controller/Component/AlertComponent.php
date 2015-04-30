<?php
App::uses('Component', 'Controller');
class AlertComponent extends Component {
	public $components = array('Auth', 'Cookie');
	public $controller;

	public function initialize(Controller $controller) {
		$this->controller = $controller;
	}

	public function setAlerts() {
		// Check for new alerts every ten minutes
		$recheck = ! $this->Cookie->check('alerts_last_checked') || $this->Cookie->read('alerts_last_checked') < strtotime('10 minutes ago');
		if ($recheck) {
			$user_roles = $this->controller->getUserRoles();
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
		$Course = ClassRegistry::init('Course');
		$course_id = $Course->instructorCanReportAttendance($instructor_id);
		if ($course_id) {
			$url = Router::url(array(
				'controller' => 'courses',
				'action' => 'report_attendance',
				'id' => $course_id,
				$this->params['prefix'] => false
			));
			$this->Cookie->write('alerts.instructor_attendance', 'Please <strong><a href="'.$url.'">report attendance</a></strong> for your recent course.');
		}

		$StudentReviewModule = ClassRegistry::init('StudentReviewModule');
		if ($StudentReviewModule->paymentNeeded($instructor_id)) {
			$url = Router::url(array(
				'instructor' => true,
				'controller' => 'products',
				'action' => 'student_review_modules'
			));
			$this->Cookie->write('alerts.instructor_srm_payment', 'Please <strong><a href="'.$url.'">submit payment</a></strong> for the Student Review Modules used in your recent course.');
		}
	}

	protected function __setAdminAlerts() {
		$this->refresh('admin testimonials');
	}

	public function refresh($type) {
		switch ($type) {
			case 'admin testimonials':
				$this->__refreshAdminTestimonials();
				break;
		}
	}

	protected function __refreshAdminTestimonials() {
		$key = 'alerts.admin_testimonials';
		$Testimonial = ClassRegistry::init('Testimonial');
		if ($Testimonial->approvalNeeded()) {
			$url = Router::url(array(
				'controller' => 'testimonials',
				'action' => 'manage',
				$this->params['prefix'] => false
			));
			$this->Cookie->write($key, 'Please <strong><a href="'.$url.'">approve or delete</a></strong> new testimonial(s).');
		} else {
			$this->Cookie->delete($key);
		}
	}
}