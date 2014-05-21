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

	public function purchaseCourseRegistration($seller_data, $order_id, $jwt_decoded) {
		// Check for required sellerData
		App::import('Model','Course');
		$Course = new Course();
		if (! isset($seller_data['user_id'])) {
			throw new BadRequestException('User ID missing');
		} elseif (! $this->User->exists($seller_data['user_id'])) {
			throw new BadRequestException('User #'.$seller_data['user_id'].' not found');
		} elseif (! isset($seller_data['course_id'])) {
			throw new BadRequestException('Course ID missing');
		} elseif (! $Course->exists($seller_data['course_id'])) {
			throw new BadRequestException('Course #'.$seller_data['course_id'].' not found');
		}

		// Make sure that registration is allowed
		$Course->id = $seller_data['course_id'];
		$deadline = $Course->field('deadline');
		if ($deadline < date('Y-m-d')) {
			throw new ForbiddenException('Sorry, the deadline for registering for that course has passed');
		}
		if ($Course->isFull($seller_data['course_id'])) {
			throw new ForbiddenException('Sorry, this course has no available spots left');
		}

		// Record purchase
		App::import('Model','CoursePayment');
		$CoursePayment = new CoursePayment();
		$CoursePayment->create(array(
			'course_id' => $seller_data['course_id'],
			'user_id' => $seller_data['user_id'],
			'order_id' => $order_id,
			'jwt' => serialize($jwt_decoded)
		));
		if (! $CoursePayment->save()) {
			throw new InternalErrorException('Purchase could not be saved');
		}

		return true;
	}

	public function purchaseStudentReviewModule($seller_data, $order_id, $jwt_decoded) {
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
		$cache_key = 'getReviewMaterialsAccessExpiration('.$seller_data['user_id'].')';
		Cache::delete($cache_key);

		return true;
	}

	public function purchasePrepaidStudentReviewModule($seller_data, $order_id, $jwt_decoded) {
		// Check for required sellerData
		if (! isset($seller_data['instructor_id'])) {
			throw new BadRequestException('Instructor ID missing');
		} elseif (! $this->User->exists($seller_data['instructor_id'])) {
			throw new BadRequestException('Instructor #'.$seller_data['instructor_id'].' not found');
		} elseif (! isset($seller_data['product_id'])) {
			throw new BadRequestException('Product ID missing');
		} elseif (! isset($seller_data['quantity'])) {
			throw new BadRequestException('Quantity missing');
		}

		// Record purchase
		if (! isset($seller_data['user_id']) || empty($seller_data['user_id'])) {
			$seller_data['user_id'] = null;
			unset($this->validate['user_id']);
		}
		$this->create(array(
			'quantity' => $seller_data['quantity'],
			'product_id' => $seller_data['product_id'],
			'user_id' => $seller_data['user_id'],
			'order_id' => $order_id,
			'jwt' => serialize($jwt_decoded)
		));
		if (! $this->save()) {
			throw new InternalErrorException('Purchase could not be saved');
		}
		$purchase_id = $this->id;

		// Create PrepaidReviewModule records
		App::import('Model','PrepaidReviewModule');
		$PrepaidReviewModule = new PrepaidReviewModule();
		for ($i = 1; $i <= $seller_data['quantity']; $i++) {
			$PrepaidReviewModule->create(array(
				'purchase_id' => $purchase_id,
				'instructor_id' => $seller_data['instructor_id']
			));
			$PrepaidReviewModule->save();
		}

		return true;
	}
}