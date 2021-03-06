<?php
App::uses('AppController', 'Controller');
/**
 * Users Controller
 *
 * @property User $User
 */
class UsersController extends AppController {
	public $paginate = array(
		'contain' => array(
			'Role'
		)
	);

	public function beforeFilter() {
		parent::beforeFilter();
		$this->Auth->deny(array(
			'admin_add', 'admin_index', 'admin_edit', 'admin_delete'
		));
	}

	public function login() {
		// Set a redirect back to course registration
		if (isset($_GET['course'])) {
			$this->Auth->redirectUrl(array(
				'controller' => 'courses',
				'action' => 'register',
				'id' => $_GET['course']
			));
		}

		// Redirect user if they're already registered and logged in
		if ($this->Auth->user('id')) {
			$this->Flash->notification('No need to log in again. You\'re already logged in.');
			$this->redirect('/');
		}

		if ($this->request->is('post')) {
			if ($this->Auth->login()) {
				// Set 'remember me' cookie
				if ($this->request->data['User']['auto_login'] == 1) {
					unset($this->request->data['User']['auto_login']);
					App::uses('Security', 'Utility');
					$this->request->data['User']['password'] = Security::hash($this->request->data['User']['password'], null, true);

					$this->Cookie->write('remember_me', $this->request->data['User'], true, '10 years');
				}
				$first_name = $this->User->getFirstName($this->Auth->user('id'));
				$this->Flash->success("Welcome back to Elemental, $first_name.");
				$this->redirect($this->Auth->redirectUrl());
			} else {
				$this->Flash->error('There was a problem logging you in.');
				$this->set('password_error', 'Password incorrect.');
			}
		}

		// Removes "required field" styling
		$this->User->validate = array();

		// Prevents the user from being redirected to logout
		// (if they went directly from logout back to login)
		$redirect = $this->Auth->redirectUrl();
		if (stripos($redirect, 'logout') !== false) {
			$redirect = '/';
		}

		$this->set(array(
			'title_for_layout' => 'Log in',
			'redirect' => $redirect
		));
	}

    public function logout() {
    	$this->Cookie->delete('remember_me');
    	$this->Session->destroy();
    	$this->Auth->logout();
    	$this->redirect('/');
    }

	public function admin_add() {
		if ($this->request->is('post')) {

			// Format data
			App::uses('Sanitize', 'Utility');
			$this->request->data['User']['email'] = strtolower($this->request->data['User']['email']);
			$this->request->data['User']['email'] = trim($this->request->data['User']['email']);
			$this->request->data['User'] = Sanitize::clean($this->request->data['User']);
			$password = $this->request->data['User']['password'];
			App::uses('Security', 'Utility');
			$hash = Security::hash($password, null, true);
			$this->request->data['User']['password'] = $hash;

			$this->User->create();
			if ($this->User->saveAssociated($this->request->data)) {
				$this->Flash->success('The user has been added.');
			} else {
				$this->Flash->error('The user could not be saved.');
			}

			// So the password field isn't filled out automatically when the user
			// is bounced back to the page by a validation error
			$this->request->data['User']['password'] = '';
		}

		$this->set(array(
			'roles' => $this->User->Role->find('list'),
			'title_for_layout' => 'Add User'
		));
	}

	public function register() {
		if ($this->request->is('post')) {

			// Format data
			App::uses('Sanitize', 'Utility');
			$this->request->data['User'] = Sanitize::clean($this->request->data['User']);
			$clean_email = trim(strtolower($this->request->data['User']['new_email']));

			// Set login URL
			if (isset($_GET['course'])) {
				$login_url = Router::url(array(
					'controller' => 'users',
					'action' => 'login',
					'?' => array(
						'course' => $_GET['course']
					)
				));
			} else {
				$login_url = Router::url(array(
					'controller' => 'users',
					'action' => 'login'
				));
			}

			// Format data
			$this->request->data['User']['email'] = $clean_email;
			$password = $this->request->data['User']['new_password'];
			App::uses('Security', 'Utility');
			$this->request->data['User']['password'] = Security::hash($password, null, true);
			$this->loadModel('Role');
			$this->request->data['Role']['id'] = $this->Role->getIdWithName('student');
            $recaptchaPassed = $this->verifyRecaptcha();

			if ($recaptchaPassed && $this->User->save($this->request->data)) {
				CakeLog::info(
					sprintf(
						'User account #%s created for email address %s (%s)',
						$this->User->id,
						$clean_email,
						$this->request->clientIp()
					),
					'site_activity'
				);

				// Format login data (so Session.Auth is populated and formatted correctly)
				$user = $this->User->read();
				$login_data = $user['User'];
				unset($user['User']);
				$login_data = array_merge($user, $login_data);
				$login_data['password'] = $this->request->data['User']['new_password'];

				// Attempt to log the new user in
				if ($this->Auth->login($login_data)) {
					$this->Flash->success('Welcome to Elemental! Your account has been created and you have been logged in.');
				} else {
					$this->Flash->success('Welcome to Elemental! Your account has been created. Please log in to continue.');
					$this->redirect($login_url);
				}

				// Bounce back to course registration
				if (isset($_GET['course'])) {
					$this->redirect(array(
						'controller' => 'courses',
						'action' => 'register',
						'id' => $_GET['course']
					));

				// Or otherwise redirect appropriately
				} else {
					$this->redirect($this->Auth->redirectUrl());
				}
			} else {
				$this->Flash->error('Please correct the indicated error(s).');
                if (!$recaptchaPassed) {
                    $this->set('recaptcha_error', true);
                }
			}

			// So the password field isn't filled out automatically when the user
			// is bounced back to the page by a validation error
			$this->request->data['User']['new_password'] = '';
			$this->request->data['User']['confirm_password'] = '';
		}

		$this->set(array(
			'title_for_layout' => 'Create an Account'
		));
	}

	public function account() {
		$id = $this->Auth->user('id');
		$this->User->id = $id;
		if ($this->request->is('post')) {
			$this->request->data['User']['email'] = strtolower(trim($this->request->data['User']['email']));
			$this->User->set($this->request->data);
			$email_result = $this->User->find('first', array(
				'conditions' => array(
					'User.email' => $this->request->data['User']['email']
				),
				'fields' => array(
					'User.id'
				),
				'contain' => false
			));
			if (! empty($email_result) && $email_result['User']['id'] != $id) {
				$this->User->validationErrors['email'] = 'Sorry, a different user account has been created with that email address.';
			} elseif ($this->User->validates()) {
				$field_list = array(
					'name',
					'email',
					'phone'
				);
				$save_result = $this->User->save(null, true, $field_list);
				if ($save_result) {
					$this->Flash->success('Information updated.');
				} else {
					$this->Flash->error('Sorry, there was an error updating your information. Please try again.');
				}
			}
		} else {
			$this->request->data = $this->User->find('first', array(
				'fields' => array(
					'User.name',
					'User.email',
					'User.phone'
				),
				'conditions' => array(
					'User.id' => $id
				),
				'contain' => false
			));
		}
		$this->set(array(
			'title_for_layout' => 'My Account'
		));
	}

	public function change_password() {
		if ($this->request->is('post')) {
			$id = $this->Auth->user('id');
			$this->User->id = $id;
			$this->User->set($this->request->data);
			if ($this->User->validates()) {
				App::uses('Security', 'Utility');
				$hash = Security::hash($this->request->data['User']['new_password'], null, true);
				$result = $this->User->saveField('password', $hash);
				$result = true;
				if ($result) {
					$this->Flash->success('Password changed.');
					$this->redirect(array('controller' => 'users', 'action' => 'account'));
				} else {
					$this->Flash->error('Error changing password.');
				}
			}
		}
		$this->set('title_for_layout', 'Change Password');
	}

	public function reset_password($user_id, $reset_password_hash) {
		$this->User->id = $user_id;
		$email = $this->User->field('email');
		$expected_hash = $this->User->getResetPasswordHash($user_id, $email);
		if ($reset_password_hash != $expected_hash) {
			$this->Flash->error('Invalid password-resetting code. Make sure that you entered the correct address.');
			$this->redirect('/');
		}
		if ($this->request->is('post')) {
			$this->User->set($this->request->data);
			$recaptchaPassed = $this->verifyRecaptcha();
			if ($recaptchaPassed && $this->User->validates()) {
				App::uses('Security', 'Utility');
				$hash = Security::hash($this->request->data['User']['new_password'], null, true);
				$this->User->set('password', $hash);
				if ($this->User->save()) {
					$this->Flash->success('Password changed. You may now log in.');
					$this->redirect(array('controller' => 'users', 'action' => 'login'));
				} else {
					$this->Flash->error('There was an error changing your password.');
				}
			} elseif (!$recaptchaPassed) {
                $this->set('recaptcha_error', true);
            }
			unset($this->request->data['User']['new_password']);
			unset($this->request->data['User']['confirm_password']);
		}
		$this->set(array(
			'title_for_layout' => 'Reset Password',
			'user_id' => $user_id,
			'reset_password_hash' => $reset_password_hash,
			'user' => $this->User->find('first', array(
				'conditions' => array(
					'User.id' => $user_id
				),
				'fields' => array(
					'User.name',
					'User.email'
				),
				'contain' => false
			))
		));
	}

	public function forgot_password() {
		$this->Components->load('Email')->startup($this);
		if ($this->request->is('post')) {
			$admin_email = Configure::read('admin_email');
			$email = strtolower(trim($this->request->data['User']['email']));
			if (empty($email)) {
				$this->Flash->error('Please enter the email address you registered with to have your password reset. Email <a href="mailto:'.$admin_email.'">'.$admin_email.'</a> for assistance.');
			} else {
				$user_id = $this->User->findIdByEmail($email);
				if ($user_id) {
					if ($this->User->sendPasswordResetEmail($user_id)) {
						$this->Flash->success('Message sent. You should be shortly receiving an email with a link to reset your password.');
					} else {
						$this->Flash->error('Whoops. There was an error sending your password-resetting email out. Please try again, and if it continues to not work, email <a href="mailto:'.$admin_email.'">'.$admin_email.'</a> for assistance.');
					}
				} else {
					$this->Flash->error('We couldn\'t find an account registered with the email address <b>'.$email.'</b>. Make sure you spelled it correctly. Email <a href="mailto:'.$admin_email.'">'.$admin_email.'</a> for assistance.');
				}
			}
		}
		$this->set(array(
			'title_for_layout' => 'Forgot Password'
		));
	}

	public function admin_index() {
		$this->User->recursive = 0;
		if (isset($_GET['search']) && ! empty($_GET['search'])) {
			$this->paginate['conditions']['User.name LIKE'] = '%'.$_GET['search'].'%';
			$title_for_layout = 'Users named "'.$_GET['search'].'"';
		} elseif (isset($this->request->named['role'])) {
			$role_id = $this->User->Role->getIdWithName($this->request->named['role']);
			$this->User->bindModel(
				array(
					'hasOne' => array('RolesUser')
				),
				false
			);
			$this->paginate['conditions']['RolesUser.role_id'] = $role_id;
			$this->paginate['contain'][] = 'RolesUser';
			$title_for_layout = ucwords($this->request->named['role']).'s';
		} else {
			$title_for_layout = 'Users';
		}
		$this->set(array(
			'title_for_layout' => $title_for_layout,
			'users' => $this->paginate()
		));
	}

	public function admin_edit($id) {
		$this->helpers[] = 'Tinymce';
		$this->User->id = $id;
		if ($this->request->is('post') || $this->request->is('put')) {
			$this->request->data['User']['email'] = strtolower(trim($this->request->data['User']['email']));
			$this->request->data['Bio']['id'] = $this->User->Bio->getIdForUser($id);
			$user_data = $this->request->data;

			// Ignore bio section if user is not an instructor
			$instructor_role_id = $this->User->Role->getIdWithName('instructor');
			$is_instructor = array_search($instructor_role_id, $this->request->data['Role']['Role']);
			if ($is_instructor === false) {
				unset($user_data['Bio']);
			}

			$this->User->set($user_data);
			$email_result = $this->User->find('first', array(
				'conditions' => array('email' => $this->request->data['User']['email']),
				'fields' => array('id'),
				'contain' => false
			));
			if (! empty($email_result) && $email_result['User']['id'] != $id) {
				$this->Flash->error('Please correct the indicated errors.');
				$this->User->validationErrors['email'] = 'Sorry, a different user account has been created with that email address.';
			} elseif ($this->User->validates()) {
				if ($this->User->saveAssociated()) {
					$this->Flash->success('Information updated.');
					$this->redirect(array(
						'admin' => true,
						'action' => 'index'
					));
				} else {
					$this->Flash->error('Sorry, there was an error updating that user\'s information. Please try again.');
				}
			} else {
				$this->Flash->error('Please correct the indicated errors.');
			}

		} else {
			$this->request->data = $this->User->find('first', array(
				'fields' => array(
					'User.id',
					'User.name',
					'User.email',
					'User.phone'
				),
				'conditions' => array(
					'User.id' => $id
				),
				'contain' => array(
					'Role',
					'Bio' => array(
						'fields' => array(
							'Bio.bio'
						),
						'Image' => array(
							'fields' => array(
								'Image.id',
								'Image.filename'
							)
						)
					)
				)
			));
		}
		$this->set(array(
			'roles' => $this->User->Role->find('list'),
			'title_for_layout' => 'Edit User',
			'instructor_id' => $id
		));
	}

	public function admin_delete($id = null) {
		if (!$this->request->is('post')) {
			throw new MethodNotAllowedException();
		}
		$this->User->id = $id;
		if (!$this->User->exists()) {
			throw new NotFoundException(__('Invalid user selected'));
		}
		if ($this->User->delete()) {
			$this->Flash->success(__('User account removed'));
			$this->redirect(array(
				'admin' => true,
				'action' => 'index'
			));
		}
		$this->Flash->error(__('User account was not deleted'));
		$this->redirect(array(
			'admin' => true,
			'action' => 'index'
		));
	}
}
