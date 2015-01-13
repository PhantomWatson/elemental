<?php
App::uses('AppController', 'Controller');
class StudentReviewModulesController extends AppController {
	public $name = 'StudentReviewModules';
	public $components = array('Stripe.Stripe');

	public function complete_student_purchase() {
		$data = $_POST;

		$this->User->id = $data['student_id'];
		if (! $this->User->exists()) {
			throw new NotFoundException('User #'.$data['student_id'].' not found.');
		}

		$this->loadModel('Product');
		$product_id = $this->Product->field(
			'id',
			array(
				'name' => 'Student Review Module access renewal'
			)
		);
		$this->Product->id = $product_id;
		$cost = $this->Product->field('cost') / 100; // Convert from cents to dollars
		$student_email = $this->User->field('email');
		$charge = array(
			'amount' => $cost,
			'stripeToken' => $data['token'],
			'description' => 'Student Review Module access renewal for '.$student_email
		);
		$result = $this->Stripe->charge($charge);

		if (is_array($result)) {
			$this->loadModel('Purchase');
			$this->Purchase->create(array(
				'product_id' => $product_id,
				'quantity' => 1,
				'user_id' => $data['student_id'],
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
	}
}