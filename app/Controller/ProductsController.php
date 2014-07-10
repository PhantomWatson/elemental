<?php
App::uses('AppController', 'Controller');
class ProductsController extends AppController {
	public $name = 'Products';
	public $uses = array('Product', 'Purchase');

	public function beforeFilter() {
		parent::beforeFilter();
		$this->Auth->deny(array(
			'classroom_module',
			'prepaid_review_modules'
		));
	}

	public function isAuthorized($user) {
		$user_id = $this->Auth->user('id');
		$this->loadModel('User');
		$is_instructor = $this->User->hasRole($user_id, 'instructor');

		switch ($this->action) {
			case 'classroom_module':
				if ($is_instructor) return true;
				break;
			case 'prepaid_review_modules':
				if ($is_instructor) return true;
				break;
			default:
				return true;
				break;
		}

        // Admins can access everything
		return parent::isAuthorized($user);
	}

	public function review_materials() {
		/* A trailing slash is required for /app/webroot/.htaccess to
		 * correctly route the Vizi Player's (/app/webroot/vizi/review_materials/vizi.swf)
		 * requests for files stored in /app/webroot/vizi/review_materials */
		if ($this->request->url == 'review_materials') {
			$this->redirect('/review_materials/');
		}

		$logged_in = $this->Auth->loggedIn();
		$product = $this->Product->getReviewMaterials();
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
			$expiration = $this->User->getReviewMaterialsAccessExpiration($user_id);

			if ($user_attended && ! $can_access) {
				$this->set('jwt', $this->Product->getReviewModuleJWT($user_id));
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
				case 'review_materials':
					$product_id = $this->Product->getReviewMaterialsId();
					break;
			}

			// Verify that the user is authorized to access this
			$user_id = $this->Auth->user('id');
			$this->loadModel('User');
			switch ($product) {
				case 'instructor_training':
					$can_access = $this->User->canAccessInstructorTraining($user_id);
					break;
				case 'review_materials':
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
		$this->loadModel('User');
		$this->set(array(
			'title_for_layout' => 'Instructor Training',
			'can_access' => $this->User->canAccessInstructorTraining($user_id),
			'logged_in' => $this->Auth->loggedIn(),
			'release_submitted' => $this->User->hasSubmittedRelease($user_id)
		));
	}

	public function prepaid_review_modules() {
		$user_id = $this->Auth->user('id');
		$this->loadModel('PrepaidReviewModule');
		$this->set(array(
			'title_for_layout' => 'Prepaid Student Review Modules',
			'cost' => $this->PrepaidReviewModule->getCost(),
			'report' => $this->PrepaidReviewModule->getReport($user_id)
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
}