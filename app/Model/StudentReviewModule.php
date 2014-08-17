<?php
App::uses('AppModel', 'Model');
class StudentReviewModule extends AppModel {
	public $displayField = 'course_id';
	public $validate = array(
		'purchase_id' => array(
			'naturalNumber' => array(
				'rule' => array('naturalNumber'),
				'allowEmpty' => true
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

		$purchase_name = 'Elemental Student Review '.__n('Module', 'Modules', $quantity).' ('.$quantity.')';
		App::import('Model', 'Product');
		$ProductObj = new Product();
		$product = $ProductObj->find(
			'first',
			array(
				'conditions' => array(
					'Product.name' => 'Student Review Module'
				),
				'contain' => false,
				'fields' => array(
					'Product.id',
					'Product.cost',
					'Product.description'
				)
			)
		);
		$total = $quantity * $product['Product']['cost'];
		$product_id = $product['Product']['id'];

		// Generate a JWT (JSON Web Token) for this item
		// $payload parameters reference: https://developers.google.com/commerce/wallet/digital/docs/jsreference#jwt
		App::import('Vendor', 'JWT');
		$seller_data = array(
			"type:student_review_module",
			"user_id:$user_id",
			"instructor_id:$instructor_id",
			"product_id:$product_id",
			"quantity:$quantity"
		);
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
				"sellerData" => implode(',', $seller_data)
			)
		);
		return JWT::encode($payload, $seller_secret);
	}

	public function getCost() {
		App::import('Model', 'Product');
		$ProductObj = new Product();
		$result = $ProductObj->find(
			'first',
			array(
				'conditions' => array(
					'Product.name' => 'Student Review Module'
				),
				'contain' => false,
				'fields' => array(
					'Product.cost'
				)
			)
		);
		return $result ? $result['Product']['cost'] : false;
	}

	public function getAvailableCount($instructor_id) {
		return $this->find(
			'count',
			array(
				'conditions' => array(
					'StudentReviewModule.instructor_id' => $instructor_id,
					'StudentReviewModule.course_id' => null
				)
			)
		);
	}

	public function assignToAttendingStudents($course_id) {
		// Abort if this is not a free course
		$this->Course->id = $course_id;
		$cost = $this->Course->field('cost');
		if ($cost > 0) {
			return;
		}

		App::import('Model', 'CourseRegistration');
		$CourseRegistration = new CourseRegistration();
		$attending_students = $CourseRegistration->find(
			'list',
			array(
				'conditions' => array(
					'CourseRegistration.course_id' => $course_id,
					'CourseRegistration.attended' => true
				),
				'fields' => array(
					'CourseRegistration.id',
					'CourseRegistration.user_id'
				)
			)
		);

		App::uses('CakeSession', 'Model/Datasource');
		$instructor_id = CakeSession::read("Auth.User.id");
		if (! $instructor_id) {
			throw new InternalErrorException('Error: Instructor ID could not be retrieved.');
		}

		$available_paid_modules = $this->find(
			'list',
			array(
				'conditions' => array(
					'StudentReviewModule.instructor_id' => $instructor_id,
					'StudentReviewModule.student_id' => null,
					'StudentReviewModule.purchase_id NOT' => null,
				)
			)
		);

		foreach ($attending_students as $reg_id => $student_id) {
			// Skip if this student has already been assigned a SRM
			$already_assigned = $this->find(
				'count',
				array(
					'conditions' => array(
						'StudentReviewModule.course_id' => $course_id,
						'StudentReviewModule.student_id' => $student_id
					)
				)
			);
			if ($already_assigned) {
				continue;
			}

			// Create a unpaid module if no paid modules are available
			if (empty($available_paid_modules)) {
				$this->create(compact(
					'instructor_id',
					'course_id',
					'student_id'
				));
				if (! $this->save()) {
					throw new InternalErrorException('Error assigning student review module to student');
				}
			} else {
				$module_ids = array_keys($available_paid_modules);
				$module_id = end($module_ids);
				$this->id = $module_id;
				$this->saveField('student_id', $student_id);
				$this->saveField('course_id', $course_id);
				array_pop($available_paid_modules);
			}
		}
	}

	public function getReport($instructor_id) {
		$modules = $this->find(
			'all',
			array(
				'conditions' => array(
					'StudentReviewModule.instructor_id' => $instructor_id
				),
				'contain' => false,
				'order' => 'StudentReviewModule.course_id DESC'
			)
		);
		App::import('Model', 'Course');
		$Course = new Course();
		$retval = array(
			'prepaid_available' => 0,
			'unpaid' => 0,
			'used' => array()
		);
		foreach ($modules as $module) {
			// Unpaid
			$paid = $module['StudentReviewModule']['purchase_id'] !== null;
			if (! $paid) {
				$retval['unpaid']++;
			}

			// Prepaid and available
			if ($paid && ! $module['StudentReviewModule']['student_id']) {
				$retval['prepaid_available']++;
				continue;
			}

			// Used
			$course_id = $module['StudentReviewModule']['course_id'];
			if (isset($retval['used'][$course_id])) {
				$retval['used'][$course_id]['count']++;
			} else {
				$dates = $this->Course->CourseDate->find(
					'list',
					array(
						'conditions' => array(
							'CourseDate.course_id' => $course_id
						),
						'order' => 'CourseDate.date ASC'
					)
				);
				$start = reset($dates);
				$start = strtotime($start);
				$end = end($dates);
				$end = strtotime($end);
				$retval['used'][$course_id] = array(
					'count' => 1,
					'start' => date('F j, Y', $start),
					'end' => date('F j, Y', $end)
				);
			}
		}
		return $retval;
	}
}