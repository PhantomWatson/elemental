<?php
/**
 * Application level Controller
 *
 * This file is application-wide controller file. You can put all
 * application-wide controller-related methods here.
 *
 * PHP 5
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       app.Controller
 * @since         CakePHP(tm) v 0.2.9
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
App::uses('Controller', 'Controller');

/**
 * Application Controller
 *
 * Add your application-wide methods in the class below, your controllers
 * will inherit them.
 *
 * @package		app.Controller
 * @link		http://book.cakephp.org/2.0/en/controllers.html#the-app-controller
 */
class AppController extends Controller {
	public $components = array(
		'Security',
		'DebugKit.Toolbar',
		'Flash',
		'Session',
		'Auth' => array(
			'authenticate' => array(
				'Form' => array(
					'contain' => array(
						'Role'
					),
					'fields' => array('username' => 'email')
				)
			),
			'authorize' => array('Controller'),
			'authError' => 'Please log in to access that page'
		),
		'Cookie',
		'Alert'
	);
	public $uses = array('User');
	public $maintenance_mode = false;

	public function isAuthorized($user = null) {
		// Admins can access everything
        if ($this->User->hasRole($user['id'], 'admin')) {
        	return true;
        }

        // Default deny
        return false;
    }

	/**
	 * Displays a Flash message and redirects back to home page for anyone other than admins
	 */
	private function __maintenanceModeBlock() {
		$this->loadModel('User');
		if ($this->User->hasRole($this->Auth->user('id'), 'admin')) {
			return;
		}

		$this->Session->delete('FlashMessage'); // Prevent these messages from stacking up
		$this->Flash->set('The website is currently undergoing an upgrade and some pages are temporarily inaccessible. Please check back later.');
		if ($this->request->params['controller'] == 'pages' && $this->request->params['action'] == 'home') {
			return;
		}

		$this->redirect(array(
			'admin' => false,
			'instructor' => false,
			'controller' => 'pages',
			'action' => 'home'
		));
	}

	public function beforeFilter() {
		$this->Auth->allow();

		$this->Security->blackHoleCallback = 'blackhole';

		if ($this->Auth->loggedIn()) {
			$this->Auth->authError = 'You are not authorized to access that location.';
		}

		// Using "rijndael" encryption because the default "cipher" type of encryption fails to decrypt when PHP has the Suhosin patch installed.
        // See: http://cakephp.lighthouseapp.com/projects/42648/tickets/471-securitycipher-function-cannot-decrypt
		$this->Cookie->type('rijndael');
		$this->Cookie->key = Configure::read('cookie_key');

		// Prevents cookies from being accessible in Javascript
		$this->Cookie->httpOnly = true;

    	// Log in with cookie
		if (! $this->Auth->loggedIn() && $this->Cookie->read('remember_me')) {
			$cookie = $this->Cookie->read('remember_me');
			if (isset($cookie['email']) && isset($cookie['password'])) {
				$user = $this->User->find('first', array(
					'conditions' => array(
						'User.email' => $cookie['email'],
						'User.password' => $cookie['password']
					),
					'fields' => array(
						'User.id',
						'User.name',
						'User.certified'
					),
					'contain' => array(
						'Role'
					)
				));

				$login_data = $user['User'];
				$login_data['Role'] = $user['Role'];
				$login_data['User']['certified'] = $user['User']['certified'];

				$login_successful = $this->Auth->login($login_data);
				if ($user && ! $login_successful) {
					$this->redirect(array(
						'controller' => 'users',
						'action' => 'logout'
					));
				}
			}
		}

		if ($this->layout == 'default') {
			$user_roles = $this->getUserRoles();
			$user_id = $this->Auth->user('id');
			$this->set(array(
				'user_roles' => $user_roles,
				'certified' => in_array('instructor', $user_roles) && $this->User->isCertified($user_id)
			));
			if ($this->Auth->loggedIn()) {
				$this->Alert->setAlerts();
			}
		}

		if ($this->maintenance_mode) {
			$this->__maintenanceModeBlock();
		}
	}

	public function getUserRoles() {
		$user_roles = array();
		$session_roles = $this->Auth->user('Role');
		if (! empty($session_roles)) {
			foreach ($session_roles as $session_role) {
				$user_roles[] = $session_role['name'];
			}
		}
		return $user_roles;
	}

	public function blackhole($type) {
		if ($type == 'secure') {
			$this->redirect('https://' . $_SERVER['SERVER_NAME'] . $this->here);
		}
	}

    /**
     * Code harvested from /Plugin/Stripe/Controller/Component/StripeComponent.php and adapted to Stripe_Charge::retrieve()
     */
    protected function __retrieveCharge($charge_id) {
        $error = null;
        try {
            Stripe::setApiKey($this->Stripe->key);
            $charge = Stripe_Charge::retrieve($charge_id);

        } catch(Stripe_CardError $e) {
            $body = $e->getJsonBody();
            $err = $body['error'];
            CakeLog::error(
                'Charge::Stripe_CardError: ' . $err['type'] . ': ' . $err['code'] . ': ' . $err['message'],
                'stripe'
            );
            $error = $err['message'];

        } catch (Stripe_InvalidRequestError $e) {
            $body = $e->getJsonBody();
            $err = $body['error'];
            CakeLog::error(
                'Charge::Stripe_InvalidRequestError: ' . $err['type'] . ': ' . $err['message'],
                'stripe'
            );
            $error = $err['message'];

        } catch (Stripe_AuthenticationError $e) {
            CakeLog::error('Charge::Stripe_AuthenticationError: API key rejected!', 'stripe');
            $error = 'Payment processor API key error.';

        } catch (Stripe_ApiConnectionError $e) {
            CakeLog::error('Charge::Stripe_ApiConnectionError: Stripe could not be reached.', 'stripe');
            $error = 'Network communication with payment processor failed, try again later';

        } catch (Stripe_Error $e) {
            CakeLog::error('Charge::Stripe_Error: Stripe could be down.', 'stripe');
            $error = 'Payment processor error, try again later.';

        } catch (Exception $e) {
            CakeLog::error('Charge::Exception: Unknown error.', 'stripe');
            $error = 'There was an error, try again later.';
        }

        if ($error !== null) {
            // an error is always a string
            return (string)$error;
        }

        CakeLog::info('Stripe: charge id ' . $charge_id, 'stripe');

        return $charge;
    }
}