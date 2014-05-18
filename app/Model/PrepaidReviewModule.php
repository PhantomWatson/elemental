<?php
App::uses('AppModel', 'Model');
class PrepaidReviewModule extends AppModel {
	public $displayField = 'course_id';
	public $validate = array(
		'purchase_id' => array(
			'notempty' => array(
				'rule' => array('notempty')
			)
		),
		'instructor_id' => array(
			'notempty' => array(
				'rule' => array('notempty')
			)
		)
	);
	public $belongsTo = array(
		'Purchase' => array(
			'className' => 'Purchase',
			'foreignKey' => 'purchase_id'
		),
		'Instructor' => array(
			'className' => 'User',
			'foreignKey' => 'instructor_id'
		),
		'Course' => array(
			'className' => 'Course',
			'foreignKey' => 'course_id'
		),
		'Student' => array(
			'className' => 'User',
			'foreignKey' => 'student_id'
		)
	);

	public function getJWT($quantity, $user_id, $instructor_id) {
		$seller_identifier = Configure::read('google_waller_seller_id');
		$seller_secret = Configure::read('google_wallet_seller_secret');

		$purchase_name = 'Prepaid Elemental Student Review '.__n('Module', 'Modules', $quantity).' ('.$quantity.')';
		App::import('Model', 'Product');
		$ProductObj = new Product();
		$product = $ProductObj->find(
			'first',
			array(
				'conditions' => array(
					'Product.name' => 'Prepaid Student Review Module'
				),
				'contain' => false,
				'fields' => array(
					'Product.cost',
					'Product.description'
				)
			)
		);
		$total = $quantity * $product['Product']['cost'];

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
				"name" => $purchase_name,
				"description" => $product['Product']['description'],
				"price" => $total,
				"currencyCode" => "USD",
				"sellerData" => "type:prepaid_module,user_id:$user_id,instructor_id:$instructor_id"
			)
		);
		return JWT::encode($payload, $seller_secret);
	}
}