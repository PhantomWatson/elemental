<?php
App::uses('AppModel', 'Model');
class Product extends AppModel {
	public $displayField = 'name';
	public $validate = array(
		'name' => array(
			'notempty' => array(
				'rule' => array('notempty'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'description' => array(
			'notempty' => array(
				'rule' => array('notempty'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'cost' => array(
			'numeric' => array(
				'rule' => array('numeric'),
				//'allowEmpty' => false,
				//'message' => 'Your custom message here',
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		)
	);

	public $hasMany = array(
		'Purchase' => array(
			'className' => 'Purchase',
			'foreignKey' => 'product_id',
			'conditions' => '',
			'fields' => '',
			'order' => 'Purchase.created DESC'
		)
	);

	public function afterFind($results, $primary = false) {
		foreach ($results as $key => $val) {

			// Convert cost from cents into dollars
			if (isset($val['Product']['cost'])) {
				$cost = $results[$key]['Product']['cost'] / 100;
				$cost = number_format($cost, 2);
				$results[$key]['Product']['cost'] = $cost;
			}
		}
		return $results;
	}

	/**
	 * Generates the JSON Web Token for a Google Wallet purchase button
	 * @param int $product_id
	 * @param int $user_id
	 * @throws NotFoundException
	 * @return string
	 */
	public function getReviewModuleRenewalJWT($user_id) {
		$product_id = $this->getReviewModuleRenewalId();
		$product = $this->find('first', array(
			'conditions' => array('Product.id' => $product_id),
			'contain' => false
		));
		if (empty($product)) {
			throw new NotFoundException('Product with ID '.$product_id.' not found.');
		}

		$seller_identifier = Configure::read('google_waller_seller_id');
		$seller_secret = Configure::read('google_wallet_seller_secret');

		// Generate a JWT (JSON Web Token) for this item
		// $payload parameters reference: https://developers.google.com/commerce/wallet/digital/docs/jsreference#jwt
		App::import('Vendor', 'JWT');
		App::import('Model', 'User');
		$User = new User();
		$User->id = $user_id;
		$email = $User->field('email');
		$user_name = $User->field('name');
		$seller_data = array(
			'type:review_module_renewal',
			"user_id:$user_id",
			"user_name:$user_name",
			"email:$email",
			"product_id:$product_id",
			'quantity:1'
		);
		$payload = array(
			"iss" => $seller_identifier,
			"aud" => "Google",
			"typ" => "google/payments/inapp/item/v1",
			"exp" => time() + 3600,
			"iat" => time(),
			"request" => array(
				"name" => $product['Product']['name'],
				"description" => $product['Product']['description'],
				"price" => $product['Product']['cost'],
				"currencyCode" => "USD",
				"sellerData" => implode(',', $seller_data)
			)
		);
		return JWT::encode($payload, $seller_secret);
	}

	public function getReviewModuleRenewal() {
		$cache_key = "getReviewModuleRenewal()";
		if ($cached = Cache::read($cache_key)) {
			return $cached;
		}

		$retval = $this->find('first', array(
			'conditions' => array(
				'Product.name' => 'Student Review Module access renewal'
			),
			'contain' => false
		));
		Cache::write($cache_key, $retval);
		return $retval;
	}

	public function getReviewModuleRenewalId() {
		$cache_key = "getReviewModuleRenewalId()";
		if ($cached = Cache::read($cache_key)) {
			return $cached;
		}

		$product = $this->getReviewModuleRenewal();
		$retval = $product['Product']['id'];
		Cache::write($cache_key, $retval);
		return $retval;
	}

	public function getClassroomModuleAccessExpiration($user_id) {
		$cache_key = "getClassroomModuleAccessExpiration($user_id)";
		if ($cached = Cache::read($cache_key)) {
			return $cached;
		}

		$retval = false;

		// Users who have purchased the module in the past year get access
		$product_id = $this->getProductId('classroom module');
		$purchase = $this->Purchase->find('first', array(
			'conditions' => array(
				'Purchase.user_id' => $user_id,
				'Purchase.product_id' => $product_id
			),
			'contain' => false,
			'fields' => array(
				'Purchase.created'
			),
			'order' => 'Purchase.created DESC'
		));
		if (! empty($purchase)) {
			$retval = strtotime($purchase['Purchase']['created'].' + 1 year + 2 days');
		}

		Cache::write($cache_key, $retval);
		return $retval;
	}

	public function getProductId($product) {
		switch ($product) {
			case 'srm renewal':
				return 2;
			case 'srm':
				return 3;
			case 'classroom module':
				return 4;
		}

		return false;
	}
}