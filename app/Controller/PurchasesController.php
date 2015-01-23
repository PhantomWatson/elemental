<?php
App::uses('AppController', 'Controller');
class PurchasesController extends AppController {
	public $name = 'Purchases';
	public $components = array('Stripe.Stripe');

	public function complete_purchase($product) {
		switch ($product) {
			case 'srm_student':
			case 'srm_instructor':
				$method = "__$product";
				$retval = $this->$method();
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

		$this->loadModel('Product');
		$product_id = $this->Product->field(
			'id',
			array(
				'name' => 'Student Review Module access renewal'
			)
		);
		$this->Product->id = $product_id;
		$cost = $this->Product->field('cost');
		$student_email = $this->User->field('email');
		$charge = array(
			'amount' => $cost,
			'stripeToken' => isset($data['token']) ? $data['token'] : null,
			'description' => "Student Review Module access renewal for $student_email (user #$student_id)"
		);
		$result = $this->Stripe->charge($charge);

		if (is_array($result)) {
			$this->loadModel('Purchase');
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

		$this->loadModel('Product');
		$product_id = $this->Product->field(
			'id',
			array(
				'name' => 'Student Review Module'
			)
		);
		$this->Product->id = $product_id;
		$quantity = $_POST['quantity'];
		$total_cost = $this->Product->field('cost') * $quantity;
		$instructor_email = $this->User->field('email');
		$charge = array(
			'amount' => $total_cost,
			'stripeToken' => isset($data['token']) ? $data['token'] : null,
			'description' => "Purchasing $quantity Student Review ".__n('Module', 'Modules', $quantity)." for $instructor_email (user #$instructor_id)"
		);
		$result = $this->Stripe->charge($charge);

		if (is_array($result)) {
			$this->loadModel('Purchase');
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
}