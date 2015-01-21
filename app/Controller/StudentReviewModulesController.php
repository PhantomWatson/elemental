<?php
App::uses('AppController', 'Controller');
class StudentReviewModulesController extends AppController {
	public $name = 'StudentReviewModules';
	public $components = array('Stripe.Stripe');

	public function complete_student_purchase() {
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
		$this->set('retval', $retval);
		$this->layout = 'json';
		$this->render('/Purchases/complete_purchase');
	}

	public function complete_instructor_purchase() {
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
			'description' => "Paying off $quantity used Student Review ".__n('Module', 'Modules', $quantity)." for $instructor_email (user #$instructor_id)"
		);
		$result = $this->Stripe->charge($charge);

		if (is_array($result)) {
			$this->loadModel('Purchase');
			$this->Purchase->create(array(
				'product_id' => $product_id,
				'quantity' => $quantity,
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
		$this->set('retval', $retval);
		$this->layout = 'json';
		$this->render('/Purchases/complete_purchase');
	}
}