<?php
App::uses('AppController', 'Controller');
class ProductsController extends AppController {
	public $name = 'Products';
	public $uses = array('Product', 'Purchase');

	public function beforeFilter() {
		parent::beforeFilter();
	}

	public function isAuthorized($user) {
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
			$user_purchased = $this->User->hasPurchased($user_id, $product['Product']['id']);
			$can_access = $this->User->canAccessReviewMaterials($user_id);
			$expiration = $this->User->getReviewMaterialsAccessExpiration($user_id);

			if ($user_attended && ! $can_access) {
				$this->set('jwt', $this->Product->getJWT($product['Product']['id'], $user_id));
			}

			$this->set(compact(
				'user_attended',
				'user_purchased',
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
			'logged_in' => $this->Auth->loggedIn(),
			'can_access' => $this->User->canAccessInstructorTraining($user_id)
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
}