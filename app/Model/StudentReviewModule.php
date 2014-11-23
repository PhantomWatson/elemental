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
		),
		'override_admin_id' => array(
			'naturalNumber' => array(
				'rule' => array('naturalNumber'),
				'allowEmpty' => true
			)
		),
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
		),
		'Admin' => array(
			'className' => 'User',
			'foreignKey' => 'override_admin_id'
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

	/**
	 * Generates the JWT for a user to pay off however many SRMs are currently in use and unpaid, or null if no payment is needed
	 * @param int $instructor_id
	 * @return string|null
	 */
	public function getAwaitingPaymentJWT($instructor_id) {
		$count = $this->find(
			'count',
			array(
				'conditions' => array(
					'StudentReviewModule.instructor_id' => $instructor_id,
					'StudentReviewModule.purchase_id' => null,
					'StudentReviewModule.override_admin_id' => null
				)
			)
		);
		if ($count) {
			return $this->getJWT($count, $instructor_id, $instructor_id);
		}
		return null;
	}

	public function getAwaitingPaymentList($instructor_id) {
		return $this->find(
			'list',
			array(
				'conditions' => array(
					'StudentReviewModule.instructor_id' => $instructor_id,
					'StudentReviewModule.purchase_id' => null,
					'StudentReviewModule.override_admin_id' => null
				),
				'order' => 'StudentReviewModule.created ASC'
			)
		);
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
					'OR' => array(
						'StudentReviewModule.purchase_id NOT' => null,
						'StudentReviewModule.override_admin_id NOT' => null
					)
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
			'unpaid' => array(),
			'used' => array()
		);
		foreach ($modules as $module) {
			$paid = $module['StudentReviewModule']['purchase_id'] != null;
			$admin_pay_override = $module['StudentReviewModule']['override_admin_id'] != null;
			$assigned = $module['StudentReviewModule']['student_id'] != null;

			// Treat SRMs with an admin payment override as paid
			$paid = $paid || $admin_pay_override;

			// Prepaid and available
			if ($paid && ! $assigned) {
				$retval['prepaid_available']++;
				continue;
			}

			// Used
			$course_id = $module['StudentReviewModule']['course_id'];
			foreach (array('unpaid', 'used') as $type) {
				if ($type == 'unpaid' && $paid) {
					continue;
				}
				if ($type == 'used' && ! $assigned) {
					continue;
				}

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
						'end' => date('F j, Y', $end)
					);
				}
			}
		}
		return $retval;
	}

	public function transfer($sender_id, $recipient_id, $quantity) {
		$available = $this->getAvailableCount($sender_id);
		if ($quantity > $available) {
			throw new ForbiddenException("Cannot transfer $quantity Student Review Modules. Instructor only has $available available.");
		}

		App::import('Model','User');
		$User = new User();
		if (! $User->hasRole($recipient_id, 'instructor')) {
			throw new ForbiddenException("Cannot transfer Student Review Modules to that user. User is not a certified instructor.");
		}

		$available_paid_modules = $this->find(
			'list',
			array(
				'conditions' => array(
					'StudentReviewModule.instructor_id' => $sender_id,
					'StudentReviewModule.student_id' => null,
					'StudentReviewModule.purchase_id NOT' => null,
				)
			)
		);
		$sender_module_ids = array_keys($available_paid_modules);
		$recipient_unpaid_modules = $this->getAwaitingPaymentList($recipient_id);
		$recipient_unpaid_module_ids = array_keys($recipient_unpaid_modules);

		foreach ($sender_module_ids as $sender_module_id) {
			if ($quantity < 1) {
				break;
			}

			$this->id = $sender_module_id;

			// Apply this to existing StudentReviewModule records awaiting payment
			if (! empty($recipient_unpaid_module_ids)) {

				// Apply sender's purchase ID to recipient's existing module
				$sender_purchase_id = $this->field('purchase_id');
				$this->id = array_pop($recipient_unpaid_module_ids);
				$this->field('purchase_id', $sender_purchase_id);

				// Remove sender's module
				$this->delete($sender_module_id);

				$quantity--;
				continue;
			}

			// Switch ownership of unused, purchased SRMs
			if (! $this->saveField('instructor_id', $recipient_id)) {
				return false;
			}
			$quantity--;
		}

		return true;
	}

	/**
	 * Grants SRMs to an instructor as if they had purchased it.
	 * @param int $instructor_id
	 * @param int $quantity
	 * @return boolean
	 */
	public function grant($instructor_id, $admin_id, $quantity) {
		App::import('Model','User');
		$User = new User();
		if (! $User->hasRole($instructor_id, 'instructor')) {
			throw new ForbiddenException("Cannot grant Student Review Modules to that user. User is not a certified instructor.");
		}

		// Apply this to existing StudentReviewModule records awaiting payment
		$unpaid_modules = $this->getAwaitingPaymentList($instructor_id);
		foreach ($unpaid_modules as $module_id => $module_course_id) {
			if ($quantity < 1) {
				break;
			}
			$this->id = $module_id;
			if (! $this->saveField('override_admin_id', $admin_id)) {
				return false;
			}
			$quantity--;
		}

		// Create StudentReviewModule records
		$data = array(
			'purchase_id' => null,
			'override_admin_id' => $admin_id,
			'instructor_id' => $instructor_id,
			'course_id' => null,
			'student_id' => null
		);
		for ($n = 1; $n <= $quantity; $n++) {
			$this->create($data);
			if (! $this->save()) {
				return false;
			}
		}

		return true;
	}
}