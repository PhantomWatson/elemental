<?php
App::uses('AppController', 'Controller');
class ProductsController extends AppController {
	public $name = 'Products';
	public $uses = array('Product', 'Purchase');

	public function beforeFilter() {
		parent::beforeFilter();
		$this->Auth->deny(array(
			'classroom_module',
			'instructor_student_review_modules'
		));
	}

	public function isAuthorized($user) {
		$user_id = $this->Auth->user('id');
		$this->loadModel('User');
		$is_instructor = $this->User->hasRole($user_id, 'instructor');

		switch ($this->action) {
			case 'classroom_module':
			case 'instructor_student_review_modules':
			case 'instructor_transfer_srm':
				if ($is_instructor) return true;
		}

        // Admins can access everything
		return parent::isAuthorized($user);
	}

	public function student_review() {
		/* A trailing slash is required for /app/webroot/.htaccess to
		 * correctly route the Vizi Player's (/app/webroot/vizi/student_review/vizi.swf)
		 * requests for files stored in /app/webroot/vizi/student_review */
		if ($this->request->url == 'student_review') {
			$this->redirect('/student_review/');
		}

		$logged_in = $this->Auth->loggedIn();
		$product = $this->Product->getReviewModuleRenewal();
		$this->set(array(
			'title_for_layout' => 'Student Review Materials',
			'logged_in' => $logged_in,
			'product' => $product
		));

		if ($logged_in) {
			$user_id = $this->Auth->user('id');
			$this->loadModel('User');
			$user_attended = $this->User->hasAttendedCourse($user_id);
			$can_access = $this->User->canAccessReviewMaterials($user_id);
			$expiration = $this->User->getReviewModuleAccessExpiration($user_id);

			if ($user_attended && ! $can_access) {
				$this->set('jwt', $this->Product->getReviewModuleRenewalJWT($user_id));
			}

			$this->set(compact(
				'user_attended',
				'can_access',
				'expiration'
			));
		} else {
			$this->set('can_access', false);
		}
	}

	public function instructor_certification() {
		$this->set(array(
			'title_for_layout' => 'Instructor Certification'
		));
	}

	public function route() {
		$url = urldecode($this->request->url);

		// Strip out leading /vizi if present
		if(stripos($url, 'vizi/') === 0) {
			$url = substr($url, 5);
		}

		if ($this->Auth->loggedIn()) {

			// Get product info
			$path_split = explode('/', $url);
			$product = reset($path_split);
			switch ($product) {
				case 'student_review':
					$product_id = $this->Product->getReviewModuleRenewalId();
					break;
			}

			// Verify that the user is authorized to access this
			$user_id = $this->Auth->user('id');
			$this->loadModel('User');
			switch ($product) {
				case 'instructor_training':
					$can_access = $this->User->canAccessInstructorTraining($user_id);
					break;
				case 'student_review':
					$can_access = $this->User->hasPurchased($user_id, $product_id);
					break;
				case 'classroom_module':
					$can_access = $this->User->canAccessClassroomModule($user_id);
					break;
				default:
					throw new NotFoundException('Error: Unrecognized product');
			}

			// Serve media
			if ($can_access) {
				$this->response->file(WWW_ROOT.'vizi/'.$url);
				return $this->response;
			} else {
				$this->Flash->error('Sorry, your account does not have access to that product.');
			}
		} else {
			$this->Flash->error('Please log in to view that product.');
		}

		$this->redirect('/');
	}

	public function instructor_training() {
		/* A trailing slash is required for /app/webroot/.htaccess to
		 * correctly route the Vizi Player's (/app/webroot/vizi/instructor_training/.../vizi.swf)
		 * requests for files stored in /app/webroot/vizi/instructor_training */
		if ($this->request->url == 'instructor_training') {
			$this->redirect('/instructor_training/');
		}

		$user_id = $this->Auth->user('id');

		$this->loadModel('InstructorAgreement');
		if (! $this->InstructorAgreement->hasAgreed($user_id)) {
			$this->Flash->error('Before accessing the training module, you must first agree to the Certified Elemental Instructor License Agreement.');
			$this->redirect(array(
				$this->params['prefix'] => false,
				'controller' => 'instructor_agreements',
				'action' => 'view'
			));
		}

		$this->loadModel('User');
		$this->set(array(
			'title_for_layout' => 'Instructor Training',
			'can_access' => $this->User->canAccessInstructorTraining($user_id),
			'logged_in' => $this->Auth->loggedIn(),
			'release_submitted' => $this->User->hasSubmittedRelease($user_id)
		));
	}

	public function instructor_student_review_modules() {
		$user_id = $this->Auth->user('id');
		$this->loadModel('StudentReviewModule');
		$this->set(array(
			'title_for_layout' => 'Student Review Modules',
			'cost' => $this->StudentReviewModule->getCost(),
			'report' => $this->StudentReviewModule->getReport($user_id),
			'unpaid_jwt' => $this->StudentReviewModule->getUnpaidJWT($user_id)
		));
	}

	public function classroom_module() {
		/* A trailing slash is required for /app/webroot/.htaccess to
		 * correctly route the Vizi Player's (/app/webroot/vizi/classroom_module/vizi.swf)
		 * requests for files stored in /app/webroot/vizi/classroom_module */
		if ($this->request->url == 'classroom_module') {
			$this->redirect('/classroom_module/');
		}

		$user_id = $this->Auth->user('id');

		$this->loadModel('InstructorAgreement');
		if (! $this->InstructorAgreement->hasAgreed($user_id)) {
			$this->Flash->error('Before accessing the classroom module, you must first agree to the Certified Elemental Instructor License Agreement.');
			$this->redirect(array(
				'controller' => 'instructor_agreements',
				'action' => 'view'
			));
		}

		$expiration = $this->Product->getClassroomModuleAccessExpiration($user_id);
		$can_access = $expiration > time();

		$this->set(array(
			'title_for_layout' => 'Classroom Module',
			'can_access' => $can_access,
			'expiration' => $expiration
		));

		if ($can_access) {
			$this->set(array(
				'warn' => $expiration < strtotime('+30 days')
			));
		} else {
			$product_id = $this->Product->getClassroomModuleId();
			$this->Product->id = $product_id;
			$this->set(array(
				'cost' => $this->Product->field('cost'),
				'jwt' => $this->Product->getClassroomModuleJWT($user_id)
			));
		}
	}

	public function instructor_transfer_srm() {
		if ($this->request->is('post')) {

		}

		$instructor_id = $this->Auth->user('id');
		$this->loadModel('StudentReviewModule');
		$available_count = $this->StudentReviewModule->getAvailableCount($instructor_id);
		$this->loadModel('User');
		$instructors = $this->User->getInstructorList();

		// Exclude the logged-in instructor
		unset($instructors[$instructor_id]);

		$this->set(array(
			'title_for_layout' => 'Transfer Pre-paid Student Review Modules',
			'instructors' => $instructors,
			'available_count' => $available_count
		));
	}
}