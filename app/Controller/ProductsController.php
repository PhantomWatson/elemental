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
		if (Configure::read('debug') == 0) {
			$this->Security->requireSecure('classroom_module', 'instructor_student_review_modules');
		}
	}

	public function isAuthorized($user = null) {
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
		$product = $this->Product->find('first', array(
			'conditions' => array(
				'Product.id' => $this->Product->getProductId('srm')
			),
			'contain' => false
		));
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
			if ($this->User->hasRole($user_id, 'instructor') || $this->User->hasRole($user_id, 'admin')) {
				$expiration = false;
			} else {
				$expiration = $this->User->getReviewModuleAccessExpiration($user_id);
			}

			$this->set(compact(
				'user_attended',
				'can_access',
				'expiration'
			));
			$this->set(array(
				'user_id' => $this->Auth->user('id')
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

			// Verify that the user is authorized to access this
			$user_id = $this->Auth->user('id');
			$this->loadModel('User');
			switch ($product) {
				case 'instructor_training':
					$can_access = $this->User->canAccessInstructorTraining($user_id);
					break;
				case 'student_review':
					$can_access = $this->User->canAccessReviewMaterials($user_id);
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
		$this->Alert->refresh('instructor_srm_payment');
		$user_id = $this->Auth->user('id');
		$this->loadModel('StudentReviewModule');
		$this->loadModel('User');
		$this->User->id = $user_id;
		$this->set(array(
			'title_for_layout' => 'Student Review Modules',
			'cost' => $this->StudentReviewModule->getCost(),
			'report' => $this->StudentReviewModule->getReport($user_id),
			'user_id' => $user_id,
			'email' => $this->User->field('email')
		));
	}

	public function admin_student_review_modules() {
		$admin_id = $this->Auth->user('id');
		$instructors = $this->User->getCertifiedInstructorList();
		if ($this->request->is('post')) {
			$quantity = $this->request->data['quantity'];
			$instructor_id = $this->request->data['instructor_id'];
			$this->loadModel('StudentReviewModule');
			$success = $this->StudentReviewModule->grant($instructor_id, $admin_id, $quantity);
			if ($success) {
				$message = "$quantity Student Review ".__n('Module', 'Modules', $quantity).' granted to '.$instructors[$instructor_id];
				$this->Flash->success($message);
				$this->request->data = array();
			}
		}
		$this->set(array(
			'title_for_layout' => 'Grant Student Review Modules',
			'instructors' => $instructors
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
        $is_admin = $this->User->hasRole($user_id, 'admin');
		if (! $is_admin && ! $this->InstructorAgreement->hasAgreed($user_id)) {
			$this->Flash->error('Before accessing the classroom module, you must first agree to the Certified Elemental Instructor License Agreement.');
			$this->redirect(array(
				'controller' => 'instructor_agreements',
				'action' => 'view'
			));
		}

        $this->loadModel('Product');
        $product_id = $this->Product->getProductId('classroom module');
        $this->loadModel('User');
        $has_purchased = $this->User->hasPurchased($user_id, $product_id);
        if ($has_purchased) {
            $expiration = $this->Product->getClassroomModuleAccessExpiration($user_id);
            $expired = $expiration < time();
        } else {
            $expiration = null;
            $expired = null;
        }

        $has_upcoming_course = $this->User->hasUpcomingCourse($user_id);
        if (! ($expired || $has_upcoming_course || $is_admin)) {
            $this->Flash->error('Schedule an upcoming course in order to access the Elemental classroom module.');
            $this->redirect(array(
                'controller' => 'courses',
                'action' => 'add'
            ));
        }

		$product_id = $this->Product->getProductId('classroom module');
		$this->Product->id = $product_id;
		$this->loadModel('User');
		$this->User->id = $user_id;
		$this->set(array(
		    'title_for_layout' => 'Classroom Module',
            'expired' => $expired,
            'expiration' => $expiration,
            'has_purchased' => $has_purchased,
            'is_admin' => $is_admin,
            'user_id' => $user_id,
			'cost' => $this->Product->field('cost'),
			'email' => $this->User->field('email')
		));
	}

	public function instructor_transfer_srm() {
		$this->loadModel('StudentReviewModule');
		$instructor_id = $this->Auth->user('id');
		$this->loadModel('User');
		$instructors = $this->User->getCertifiedInstructorList();

		if ($this->request->is('post')) {
			$quantity = $this->request->data['quantity'];
			$recipient_instructor_id = $this->request->data['instructor_id'];
			$success = $this->StudentReviewModule->transfer($instructor_id, $recipient_instructor_id, $quantity);
			if ($success) {
				$message = "$quantity Student Review ".__n('Module', 'Modules', $quantity).' transferred to '.$instructors[$recipient_instructor_id];
				$this->Flash->success($message);
				$this->redirect(array(
					'instructor' => true,
					'controller' => 'products',
					'action' => 'student_review_modules'
				));
			}
		}

		$available_count = $this->StudentReviewModule->getAvailableCount($instructor_id);

		// Exclude the logged-in instructor
		unset($instructors[$instructor_id]);

		$this->set(array(
			'title_for_layout' => 'Transfer Pre-paid Student Review Modules',
			'instructors' => $instructors,
			'available_count' => $available_count
		));
	}
}
