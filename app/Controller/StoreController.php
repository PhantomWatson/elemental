<?php
App::uses('AppController', 'Controller');
class StoreController extends AppController {
	public $name = 'Store';
	public $uses = array('Product', 'Purchase');
	
	public function beforeFilter() {
		parent::beforeFilter();
		$this->Auth->deny('review_materials');
	}

	public function isAuthorized($user) {
        // Admins can access everything
		return parent::isAuthorized($user);
	}
	
	public function review_materials() {
		$user_id = $this->Auth->user('id');
		$this->loadModel('User');
		$user_attended = $this->User->hasAttendedCourse($user_id);
		
		$this->set(array(
			'title_for_layout' => 'Purchase Review Materials',
			'product' => $this->Product->find('first', array(
				'conditions' => array(
					'Product.name LIKE' => '%Review%' 
				),
				'contain' => false
			)),
			'user_attended' => $user_attended
		));
		
	}
	
	/**
	 * Postback method for confirming payments
	 */
	public function postback() {
		if (! $this->request->is('post')) {
			throw new MethodNotAllowedException('Request must be POST.');
		}
		if (! isset($_POST['jwt'])) {
			throw new BadRequestException('Postback data not received.');
		}
		
		// Decode order data
		$jwt = $_POST['jwt'];
		App::import('Vendor', 'JWT');
		$seller_secret = Configure::read('google_wallet_seller_secret');
		$jwt_decoded = JWT::decode($jwt, $seller_secret);
		
		/* Reference: https://developers.google.com/commerce/wallet/digital/docs/tutorial#5
		 * I think that $_POST['jwt'] should be provided.
		 * It includes a copy of the JSON from the call to buy(), plus an order ID
		 * Example expected results of $jwt_decoded:
		{
		  "iss": "Google",
		  "aud": "1337133713371337",
		  "typ": "google/payments/inapp/item/v1/postback/buy",
		  "iat": "1309988959",
		  "exp": "1409988959",
		  "request": {
		    "name": "Piece of Cake",
		    "description": "Virtual chocolate cake to fill your virtual tummy",
		    "price": "10.50",
		    "currencyCode": "USD",
		    "sellerData": "user_id:1224245,offer_code:3098576987,affiliate:aksdfbovu9j"
		  },
		  "response": {
		    "orderId": "3485709183457474939449"
		  }
		}
		*/
		if (isset($jwt_decoded->response->orderId)) {
			$order_id = $jwt_decoded->response->orderId;
		} else {
			throw new BadRequestException('Order ID not received.');
		}
		
		// Get sellerData
		foreach (explode(',', $jwt_decoded->request->sellerData) as $datum) {
			list($var, $val) = explode(":", $datum);
			$seller_data[$var] = $val;
		}
		
		// Remove cached information
		$cache_key = 'hasPurchased('.$seller_data['user_id'].', '.$seller_data['product_id'].')';
		Cache::delete($cache_key);
		
		// Record purchase
		$this->Purchase->create(array(
			'product_id' => $seller_data['product_id'],
			'user_id' => $seller_data['user_id'],
			'order_id' => $order_id,
			'jwt' => serialize($jwt_decoded)
		));
		if (! $this->Purchase->save()) {
			throw new InternalErrorException('Purchase could not be saved');
		}
		
		// If order is okay, send 200 OK response and this order ID
		$this->set(array(
			'order_id' => $order_id
		));
	}
	

}
