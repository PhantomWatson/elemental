<?php
App::uses('AppController', 'Controller');
class CoursePaymentsController extends AppController {
	public $name = 'CoursePayments';

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
			throw new BadRequestException('Order ID not received.');
		}

		// Get sellerData
		foreach (explode(',', $jwt_decoded->request->sellerData) as $datum) {
			list($var, $val) = explode(":", $datum);
			$seller_data[$var] = $val;
		}

		// Record course payment
		$this->CoursePayment->create(array(
			'course_id' => $seller_data['course_id'],
			'user_id' => $seller_data['user_id'],
			'order_id' => $order_id,
			'jwt' => serialize($jwt_decoded)
		));
		if (! $this->CoursePayment->save()) {
			throw new InternalErrorException('Course payment could not be saved');
		}

		// If order is okay, send 200 OK response and this order ID
		$this->set(array(
			'order_id' => $order_id
		));
	}
}