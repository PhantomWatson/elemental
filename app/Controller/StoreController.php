<?php
App::uses('AppController', 'Controller');
class StoreController extends AppController {
	public $name = 'Store';
	public $uses = array('Product', 'Purchase');

	public function beforeFilter() {
		parent::beforeFilter();
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
		if (isset($jwt_decoded->response->orderId)) {
			$order_id = $jwt_decoded->response->orderId;
		} else {
			throw new BadRequestException('Order ID not received');
		}

		// Get sellerData
		foreach (explode(',', $jwt_decoded->request->sellerData) as $datum) {
			list($var, $val) = explode(":", $datum);
			$seller_data[$var] = $val;
		}

		// Run product-specific processing
		if (! isset($seller_data['type'])) {
			throw new BadRequestException('Purchase type not specified');
		}
		switch ($seller_data['type']) {
			case 'course':
				$this->Purchase->purchaseCourseRegistration($seller_data, $order_id, $jwt_decoded);
				break;
			case 'student_review_module_renewal':
				$this->Purchase->purchaseStudentReviewModuleRenewal($seller_data, $order_id, $jwt_decoded);
				break;
			case 'student_review_module':
				$this->Purchase->purchaseStudentReviewModule($seller_data, $order_id, $jwt_decoded);
				break;
			case 'classroom_module':
				$this->Purchase->purchaseClassroomModule($seller_data, $order_id, $jwt_decoded);
				break;
			default:
				throw new BadRequestException('Unrecognized purchase type: '.$seller_data['type']);
		}

		// If order is okay, send 200 OK response and this order ID
		$this->set(array(
			'order_id' => $order_id
		));
	}

	public function student_review_module() {
		$step = 'prep';
		$user_id = $this->Auth->user('id');
		$this->loadModel('User');
		$is_instructor = $this->User->hasRole($user_id, 'instructor');
		$this->loadModel('StudentReviewModule');
		$cost = $this->StudentReviewModule->getCost();

		if ($this->request->is('post')) {
			// Create temporary validation rule
			$this->Purchase->validate['instructor_id'] = array(
				'notempty' => array(
					'rule' => 'notEmpty',
					'required' => true,
					'message' => 'Please select an instructor'
				)
			);
			$this->Purchase->create($this->request->data);
			$validates = $this->Purchase->validates();
			$valid_quantity = true;
			if (! isset($this->request->data['Purchase']['quantity'])) {
				// For some reason, using Purchase->validate['quantity'] didn't work
				$this->Purchase->validationErrors['quantity'] = 'Please specify a quantity!';
				$valid_quantity = false;
				$this->request->data['Purchase']['quantity'] = 10;
			}
			if ($validates && $valid_quantity) {
				$step = 'purchase';
				$quantity = $this->request->data['Purchase']['quantity'];
				$instructor_id = $this->request->data['Purchase']['instructor_id'];
				$this->loadModel('User');
				$this->User->id = $instructor_id;
				if ($is_instructor) {
					$redirect_url = array(
						'instructor' => true,
						'controller' => 'products',
						'action' => 'student_review_modules'
					);
				} else {
					$redirect_url = array(
						'controller' => 'store',
						'action' => 'student_review_module'
					);
				}
				$this->set(array(
					'instructor_name' => $this->User->field('name'),
					'quantity' => $quantity,
					'redirect_url' => Router::url($redirect_url, true),
					'total' => number_format(($quantity * $cost), 2)
				));
			}

		} else {
			$this->request->data['Purchase']['quantity'] = 10;
		}

		if ($step == 'prep') {
			if (! isset($this->request->data['Purchase']['instructor_id'])) {
				$this->request->data['Purchase']['instructor_id'] = $this->Auth->user('id');
			}
			$this->loadModel('User');
			$this->set(array(
				'instructors' => $this->User->getCertifiedInstructorList()
			));
		}
		$this->set(array(
			'cost' => $cost,
			'step' => $step,
			'title_for_layout' => 'Purchase Student Review Modules',
			'user_id' => $user_id
		));
	}
}