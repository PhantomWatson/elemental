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
			"type:prepaid_module",
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
					'Product.name' => 'Prepaid Student Review Module'
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
					'PrepaidReviewModule.instructor_id' => $instructor_id,
					'PrepaidReviewModule.course_id' => null
				)
			)
		);
	}

	public function assignToCourse($instructor_id, $quantity, $course_id) {
		$available_modules = $this->find('list', array(
			'conditions' => array(
				'PrepaidReviewModule.instructor_id' => $instructor_id,
				'PrepaidReviewModule.course_id' => null
			)
		));

		if (count($available_modules) < $quantity) {
			throw new BadRequestException("Cannot assign $quantity Prepaid Student Review Modules, only $available_count are available");
		}

		foreach ($available_modules as $module_id => $course_id) {
			$this->id = $module_id;
			$this->saveField('course_id', $course_id);
		}
	}


	public function releaseUnclaimedFromCourse($course_id) {
		$assigned_modules = $this->find('list', array(
			'conditions' => array(
				'PrepaidReviewModule.course_id' => $course_id,
				'PrepaidReviewModule.student_id' => null
			)
		));

		foreach ($assigned_modules as $module_id => $course_id) {
			$this->id = $module_id;
			$this->saveField('course_id', null);
		}
	}

	public function assignToAttendingStudents($course_id) {
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
		foreach ($attending_students as $reg_id => $student_id) {
			$already_assigned = $this->find(
				'count',
				array(
					'conditions' => array(
						'PrepaidReviewModule.course_id' => $course_id,
						'PrepaidReviewModule.student_id' => $student_id
					)
				)
			);
			if ($already_assigned) {
				continue;
			}

			$available_module = $this->find(
				'list',
				array(
					'conditions' => array(
						'PrepaidReviewModule.course_id' => $course_id,
						'PrepaidReviewModule.student_id' => null
					),
					'limit' => 1
				)
			);
			if (empty($available_module)) {
				/* Ruh roh. The instructor somehow managed to have more students
				 * registered and attended than was allowed in this free class.
				 * Students will have access to the review module regardless,
				 * since their access is determined by whether or not they attended
				 * a class or personally purchased the module in the past year.
				 * An automatic email to an administrator right here might be the only
				 * practical course of action. */
				continue;
			}
			$module_ids = array_keys($available_module);
			$this->id = $module_ids[0];
			$this->saveField('student_id', $student_id);
		}
	}

	public function getReport($instructor_id) {
		$modules = $this->find(
			'all',
			array(
				'conditions' => array(
					'PrepaidReviewModule.instructor_id' => $instructor_id
				),
				'contain' => false,
				'order' => 'PrepaidReviewModule.course_id DESC'
			)
		);
		App::import('Model', 'Course');
		$Course = new Course();
		$retval = array(
			'available' => 0,
			'pending' => array(),
			'used' => array()
		);
		foreach ($modules as $module) {
			$course_id = $module['PrepaidReviewModule']['course_id'];
			if ($course_id == null) {
				$retval['available']++;
				continue;
			}
			$type = $module['PrepaidReviewModule']['student_id'] == null ? 'pending' : 'used';
			if (isset($retval[$type][$course_id])) {
				$retval[$type][$course_id]['count']++;
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
				$retval[$type][$course_id] = array(
					'count' => 1,
					'start' => date('F j, Y', $start),
					'end' => date('F j, Y', $end),
					'attendance_reported' => $Course->attendanceIsReported($course_id)
				);
			}
		}
		return $retval;
	}
}