<?php
App::uses('AppController', 'Controller');
class PurchasesController extends AppController {
	public $name = 'Purchases';
	public $components = array('Stripe.Stripe');

	public function complete_purchase($product) {
		$this->loadModel('Product');
		$this->loadModel('User');

		switch ($product) {
			case 'srm_student':
			case 'srm_instructor':
			case 'classroom_module':
			case 'course_registration':
				$method = "__$product";
				$retval = $this->$method();
				if (! $retval['success']) {
					$this->response->statusCode('500');
				}
				break;
			default:
				throw new NotFoundException("Sorry, but that product ('$product') was not recognized.");
		}

		$this->layout = 'json';
		$this->set('retval', $retval);
	}

	private function __srm_student() {
		$data = $_POST;

		$student_id = isset($data['student_id']) ? $data['student_id'] : null;
		$this->User->id = $student_id;
		if (! $this->User->exists()) {
			throw new NotFoundException('User #'.$student_id.' not found.');
		}

		$product_id = $this->Product->getProductId('srm renewal');
		$this->Product->id = $product_id;
		if (! $this->Product->exists()) {
			throw new NotFoundException('Product not found.');
		}

		$cost = $this->Product->field('cost');
		$student_email = $this->User->field('email');
		$charge = array(
			'amount' => $cost,
			'stripeToken' => isset($data['token']) ? $data['token'] : null,
			'description' => "Student Review Module access renewal for $student_email (user #$student_id)",
			'statement_descriptor' => 'Elemental SRM'
		);
		$result = $this->Stripe->charge($charge);

		if (is_array($result)) {
			$this->Purchase->create(array(
				'product_id' => $product_id,
				'quantity' => 1,
				'user_id' => $student_id,
				'order_id' => $result['stripe_id']
			));
			if ($this->Purchase->save()) {
				$retval = array('success' => true);
			} else {
				$retval = array(
					'success' => false,
					'message' => 'Payment was accepted, but there was an error making a record of this purchase.'
				);
				$this->response->statusCode('500');
			}
		} else {
			$retval = array(
				'success' => false,
				'message' => $result
			);
			$this->response->statusCode('500');
		}

		return $retval;
	}

	private function __srm_instructor() {
		$data = $_POST;

		$instructor_id = isset($data['instructor_id']) ? $data['instructor_id'] : null;
		$this->User->id = $instructor_id;
		if (! $this->User->exists()) {
			throw new NotFoundException('User #'.$instructor_id.' not found.');
		}

		$product_id = $this->Product->getProductId('srm');
		$this->Product->id = $product_id;
		if (! $this->Product->exists()) {
			throw new NotFoundException('Product not found.');
		}

		$quantity = $data['quantity'];
		$total_cost = $this->Product->field('cost') * $quantity;
		$instructor_email = $this->User->field('email');
		$charge = array(
			'amount' => $total_cost,
			'stripeToken' => isset($data['token']) ? $data['token'] : null,
			'description' => "Purchasing $quantity Student Review ".__n('Module', 'Modules', $quantity)." for $instructor_email (user #$instructor_id)",
			'statement_descriptor' => 'Elemental SRM'
		);
		$result = $this->Stripe->charge($charge);

		if (is_array($result)) {
			$this->Purchase->create(array(
				'product_id' => $product_id,
				'quantity' => $quantity,
				'user_id' => $data['purchaser_id'],
				'order_id' => $result['stripe_id']
			));
			if ($this->Purchase->save()) {
				$purchase_id = $this->Purchase->getLastInsertId();

				// Apply this purchase to existing unpaid StudentReviewModule records
				$this->loadModel('StudentReviewModule');
				$unpaid_modules = $this->StudentReviewModule->getAwaitingPaymentList($instructor_id);
				foreach ($unpaid_modules as $module_id => $module_course_id) {
					if ($quantity == 0) {
						break;
					}
					$this->StudentReviewModule->id = $module_id;
					$this->StudentReviewModule->saveField('purchase_id', $purchase_id);
					$quantity--;
				}

				// Create StudentReviewModule records
				for ($i = 1; $i <= $quantity; $i++) {
					$this->StudentReviewModule->create(compact(
						'purchase_id',
						'instructor_id'
					));
					$this->StudentReviewModule->save();
				}

				$retval = array('success' => true);
			} else {
				$retval = array(
					'success' => false,
					'message' => 'Payment was accepted, but there was an error making a record of this purchase.'
				);
				$this->response->statusCode('500');
			}
		} else {
			$retval = array(
				'success' => false,
				'message' => $result
			);
			$this->response->statusCode('500');
		}

		return $retval;
	}

	private function __classroom_module() {
		$data = $_POST;

		$instructor_id = $data['instructor_id'];
		$this->User->id = $instructor_id;
		if (! $this->User->exists()) {
			throw new NotFoundException('User #'.$instructor_id.' not found.');
		}

		$product_id = $this->Product->getProductId('classroom module');
		$this->Product->id = $product_id;
		if (! $this->Product->exists()) {
			throw new NotFoundException('Product "Classroom Module" not found.');
		}

		// Remove cached information
		$cache_key = "hasPurchased($instructor_id, $product_id)";
		Cache::delete($cache_key);

		// Record purchase
		$total_cost = $this->Product->field('cost');
		$instructor_email = $this->User->field('email');
		$charge = array(
			'amount' => $total_cost,
			'stripeToken' => isset($data['token']) ? $data['token'] : null,
			'description' => "Purchasing Classroom Module for $instructor_email (user #$instructor_id)",
			'statement_descriptor' => 'Elemental Classroom'
		);
		$result = $this->Stripe->charge($charge);

		if (is_array($result)) {
			$this->Purchase->create(array(
				'product_id' => $product_id,
				'quantity' => 1,
				'user_id' => $instructor_id,
				'order_id' => $result['stripe_id']
			));
			if ($this->Purchase->save()) {
				$retval = array('success' => true);
			} else {
				$retval = array(
					'success' => false,
					'message' => 'Payment was accepted, but there was an error making a record of this purchase.'
				);
				$this->response->statusCode('500');
			}
		} else {
			$retval = array(
				'success' => false,
				'message' => $result
			);
			$this->response->statusCode('500');
		}

		// Clear relevant cache keys
		$cache_key = 'getClassroomModuleAccessExpiration('.$instructor_id.')';
		Cache::delete($cache_key);

		return $retval;
	}

	private function __course_registration() {
		$data = $_POST;

		$student_id = $data['student_id'];
		$this->User->id = $student_id;
		if (! $this->User->exists()) {
			return array(
				'success' => false,
				'message' => 'User #'.$student_id.' not found.'
			);
		}

		$course_id = $data['course_id'];
		$this->loadModel('Course');
		$this->Course->id = $course_id;
		if (! $this->Course->exists()) {
			return array(
				'success' => false,
				'message' => 'Course #'.$course_id.' not found.'
			);
		}

		// Make sure that registration is allowed
		$deadline = $this->Course->field('deadline');
		if ($deadline < date('Y-m-d')) {
			return array(
				'success' => false,
				'message' => 'Sorry, the deadline for registering for that course has passed.'
			);
		}
		if ($this->Course->isFull($course_id)) {
			return array(
				'success' => false,
				'message' => 'Sorry, this course has no available spots left.'
			);
		}

		// Prevent accidental double-payment
		$this->loadModel('CoursePayment');
		$already_purchased = $this->CoursePayment->find(
			'count',
			array(
				'conditions' => array(
					'CoursePayment.course_id' => $course_id,
					'CoursePayment.user_id' => $user_id,
					'CoursePayment.refunded' => null
				)
			)
		);
		if ($already_purchased) {
			return array(
				'success' => false,
				'message' => 'You have already paid this course\'s registration fee.'
			);
		}

		// Make purchase
		$cost = $this->Course->field('cost');
		$student_email = $this->User->field('email');
		$charge = array(
			'amount' => $cost,
			'stripeToken' => isset($data['token']) ? $data['token'] : null,
			'description' => "Paying for course registration for $student_email (user #$student_id)",
			'statement_descriptor' => 'Elemental Registration'
		);
		$result = $this->Stripe->charge($charge);

		// Record purchase
		if (is_array($result)) {
			$this->CoursePayment->create(array(
				'course_id' => $course_id,
				'user_id' => $student_id,
				'order_id' => $result['stripe_id']
			));
			if ($this->CoursePayment->save()) {
				return array('success' => true);
			} else {
				return array(
					'success' => false,
					'message' => 'Payment was accepted, but there was an error making a record of this purchase.'
				);
			}
		}

		return array(
			'success' => false,
			'message' => $result
		);
	}
}