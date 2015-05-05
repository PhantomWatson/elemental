<?php
App::uses('Component', 'Controller');
class FlashComponent extends Component {
	public $components = array('Session');

	public function beforeRender(Controller $controller) {
		$this->__prepareFlashMessages($controller);
	}

	// Adds a string message with a class of 'success', 'error', or 'notification' (default)
	// OR adds a variable to dump and the class 'dump'
	public function set($message, $class = 'notification') {
		// Dot notation doesn't seem to allow for the equivalent of $messages['error'][] = $message
		$stored_messages = $this->Session->read('FlashMessage');
		$stored_messages[] = compact('message', 'class');
		$this->Session->write('FlashMessage', $stored_messages);
	}

	public function success($message) {
		$this->set($message, 'success');
	}

	public function error($message) {
		$this->set($message, 'error');
	}

	public function notification($message) {
		$this->set($message, 'notification');
	}

	public function dump($variable) {
		$this->set($variable, 'dump');
	}

	// Sets an array to be displayed by the element 'flash_messages'
	private function __prepareFlashMessages($controller) {
		$stored_messages = $this->Session->read('FlashMessage');
		$this->Session->delete('FlashMessage');
		if ($auth_error = $this->Session->read('Message.auth')) {
			$stored_messages[] = array(
				'message' => $auth_error['message'],
				'class' => 'error'
			);
			$this->Session->delete('Message.auth');
		}
		if ($stored_messages) {
			foreach ($stored_messages as &$message) {
				if ($message['class'] == 'dump') {
					$message = array(
						'message' => '<pre>'.print_r($message['message'], true).'</pre>',
						'class' => 'notification'
					);
				}
			}
		}
		$controller->set('flash_messages', $stored_messages);
	}

    /**
     * Moves flash messages into the $messages variable set in the view
     * so that they can be output in an alternate way
     */
    public function manualOutput() {
        $messages = $this->Session->read('FlashMessage');
        $this->Session->delete('FlashMessage');
        $this->set('messages', $messages);
    }
}