<?php
App::uses('AppModel', 'Model');
class Purchase extends AppModel {
	public $displayField = 'product_id';
	public $validate = array(
		'product_id' => array(
			'numeric' => array(
				'rule' => array('numeric'),
				//'allowEmpty' => false,
				//'message' => 'Your custom message here',
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			)
		),
		'user_id' => array(
			'numeric' => array(
				'rule' => array('numeric'),
				//'allowEmpty' => false,
				//'message' => 'Your custom message here',
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			)
		),
		'order_id' => array(
			'notempty' => array(
				'rule' => array('notempty')
			)
		),
		'quantity' => array(
			'numeric' => array(
				'rule' => array('numeric'),
				'message' => 'Quantity must be numeric'
			),
			'nonzero' => array(
				'rule' => array('comparison', '>', 0),
				'message' => 'Quantity must be greater than zero'
			)
		)
	);

	public $belongsTo = array(
		'Product' => array(
			'className' => 'Product',
			'foreignKey' => 'product_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
		'User' => array(
			'className' => 'User',
			'foreignKey' => 'user_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		)
	);

	public function purchaseClassroomModule($seller_data, $order_id, $jwt_decoded) {
		// Check for required sellerData
		if (! isset($seller_data['user_id'])) {
			throw new BadRequestException('User ID missing');
		} elseif (! $this->User->exists($seller_data['user_id'])) {
			throw new BadRequestException('User #'.$seller_data['user_id'].' not found');
		} elseif (! isset($seller_data['product_id'])) {
			throw new BadRequestException('Product ID missing');
		}

		// Remove cached information
		if (isset($seller_data['product_id'])) {
			$cache_key = 'hasPurchased('.$seller_data['user_id'].', '.$seller_data['product_id'].')';
			Cache::delete($cache_key);
		}

		// Record purchase
		$this->create(array(
			'quantity' => 1,
			'product_id' => $seller_data['product_id'],
			'user_id' => $seller_data['user_id'],
			'order_id' => $order_id,
			'jwt' => serialize($jwt_decoded)
		));
		if (! $this->save()) {
			throw new InternalErrorException('Purchase could not be saved');
		}

		// Clear relevant cache keys
		$cache_key = 'getClassroomModuleAccessExpiration('.$seller_data['user_id'].')';
		Cache::delete($cache_key);

		return true;
	}
}