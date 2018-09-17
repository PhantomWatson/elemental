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
		$this->refresh('instructor_report_attendance');
	}

	protected function __setAdminAlerts() {
		$this->refresh('admin testimonials');
	}

	public function refresh($type) {
		switch ($type) {
			case 'admin_testimonials':
				$this->__refreshAdminTestimonials();
				break;
			case 'instructor_srm_payment':
				$this->__refreshInstructorSrmPayment();
				break;
			case 'instructor_report_attendance':
				$this->__refreshInstructorReportAttendance();
				break;
		}
        $this->controller->set('alerts', $this->Cookie->read('alerts'));
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

	protected function __refreshInstructorSrmPayment() {
        $instructor_id = $this->Auth->user('id');
		$key = 'alerts.instructor_srm_payment';
		$StudentReviewModule = ClassRegistry::init('StudentReviewModule');
		if ($StudentReviewModule->paymentNeeded($instructor_id)) {
			$url = Router::url(array(
				'instructor' => true,
				'controller' => 'products',
				'action' => 'student_review_modules'
			));
			$this->Cookie->write($key, 'Please <strong><a href="'.$url.'">submit payment</a></strong> for the Student Review Modules used in your recent course.');
		} else {
			$this->Cookie->delete($key);
		}
	}

	protected function __refreshInstructorReportAttendance() {
		$key = 'alerts.instructor_attendance';
		$instructor_id = $this->Auth->user('id');
		$Course = ClassRegistry::init('Course');
		$course_id = $Course->instructorCanReportAttendance($instructor_id);
        $class_list = $Course->getClassList($course_id);
		if ($course_id && ! empty($class_list)) {
			$url = Router::url(array(
				'controller' => 'courses',
				'action' => 'report_attendance',
				'id' => $course_id,
				$this->params['prefix'] => false
			));
			$this->Cookie->write($key, 'Please <strong><a href="'.$url.'">report attendance</a></strong> for your recent course.');
		} else {
			$this->Cookie->delete($key);
		}
	}
}