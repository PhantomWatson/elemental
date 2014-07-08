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
	public function getJWT($product_id, $user_id) {
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
				"sellerData" => "type:module,user_id:$user_id,product_id:$product_id"
			)
		);
		return JWT::encode($payload, $seller_secret);
	}

	public function getReviewMaterials() {
		$cache_key = "getReviewMaterials()";
		if ($cached = Cache::read($cache_key)) {
			return $cached;
		}

		$retval = $this->find('first', array(
			'conditions' => array(
				'Product.name' => 'Student Review Module'
			),
			'contain' => false
		));
		Cache::write($cache_key, $retval);
		return $retval;
	}

	public function getReviewMaterialsId() {
		$cache_key = "getReviewMaterialsId()";
		if ($cached = Cache::read($cache_key)) {
			return $cached;
		}

		$product = $this->getReviewMaterials();
		$retval = $product['Product']['id'];
		Cache::write($cache_key, $retval);
		return $retval;
	}

	public function getClassroomModuleId() {
		$cache_key = "getClassroomModuleId()";
		if ($cached = Cache::read($cache_key)) {
			return $cached;
		}

		$product = $this->find('first', array(
			'conditions' => array(
				'Product.name' => 'Classroom Module'
			),
			'contain' => false,
			'fields' => array(
				'Product.id'
			)
		));

		if (empty($product)) {
			throw new NotFoundException('Classroom Module not found');
		}

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
		$product_id = $this->getClassroomModuleId();
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
}