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

			if ($user_attended && ! $user_purchased) {
				$this->set('jwt', $this->Product->getJWT($product['Product']['id'], $user_id));
			}

			$this->set(compact(
				'user_attended',
				'user_purchased'
			));
		}
	}

	public function instructor_certification() {
		$this->set(array(
			'title_for_layout' => 'Instructor Certification'
		));
	}

	public function route() {
		$url = $this->request->url;
		
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
			
			if (isset($product_id)) {
				
				// Verify that the user is authorized to access this
				$user_id = $this->Auth->user('id');
				$this->loadModel('User');
				$user_purchased = $this->User->hasPurchased($user_id, $product_id);
				
				// Serve media
				if ($user_purchased) {
					$this->response->file(WWW_ROOT.'vizi/'.$url);
					return $this->response;
				} else {
					$this->Flash->error('Sorry, your account does not have access to that product.');
				}
			} else {
				$this->Flash->error('Sorry, that product was not found.');	
			}
		} else {
			$this->Flash->error('Please log in to view that product.');	
		}
		
		$this->redirect('/');
	}

	public function instructor_training() {
		
	}
}