<?php
App::uses('AppModel', 'Model');
/**
 * User Model
 *
 * @property Article $Article
 */
class User extends AppModel {
/**
 * Display field
 *
 * @var string
 */
	public $displayField = 'name';
/**
 * Validation rules
 *
 * @var array
 */
	public $validate = array(
		'name' => array(
			'notempty' => array(
				'rule' => array('notempty')
			),
		),
		'email' => array(
			'email' => array(
				'rule' => array('email'),
				'message' => 'That doesn\'t appear to be an email address'
			),
			'emailUnclaimed' => array(
				'rule' => array('_isUnique'),
				'message' => 'Sorry, another account has already been created with that email address'
			)
		),
		'password' => array(
			'notempty' => array(
				'rule' => array('notempty')
			),
		),
		'new_password' => array(
			'notempty' => array(
				'rule' => array('notempty'),
        		'message' => 'Please enter a password.'
			),
        	'validNewPassword' => array(
        		'rule' => array('validNewPassword'),
        		'message' => 'Sorry, those passwords did not match.'
			)
		),
		'new_email' => array(
			'email' => array(
				'rule' => array('email'),
				'message' => 'That doesn\'t appear to be an email address'
			),
        	'validNewEmail' => array(
        		'rule' => array('validNewEmail'),
        		'message' => 'Sorry, those email addresses did not match.'
			),
			'emailUnclaimed' => array(
				'rule' => array('_isUnique'),
				'message' => 'An account has already been created with that email address'
			)
		)
	);

	//The Associations below have been created with all possible keys, those that are not needed can be removed

/**
 * hasMany associations
 *
 * @var array
 */
	public $hasMany = array(
		'Article' => array(
			'className' => 'Article',
			'foreignKey' => 'user_id',
			'dependent' => false
		),
		'Testimonial' => array(
			'className' => 'Testimonial',
			'foreignKey' => 'user_id',
			'dependent' => false
		),
		'CourseRegistration' => array(
			'className' => 'CourseRegistration',
			'foreignKey' => 'user_id',
			'dependent' => false
		),
		'Course' => array(
			'className' => 'Course',
			'foreignKey' => 'user_id',
			'dependent' => false
		),
		'Purchase' => array(
			'className' => 'Purchase',
			'foreignKey' => 'user_id',
			'dependent' => false
		),
		'StudentReviewModule' => array(
			'className' => 'StudentReviewModule',
			'foreignKey' => 'instructor_id',
			'dependent' => false
		)
	);
	public $hasOne = array(
		'Release' => array(
			'dependent' => true
		),
		'InstructorAgreement' => array(
			'className' => 'InstructorAgreement',
			'foreignKey' => 'instructor_id',
			'dependent' => true
		),
		'Bio' => array(
			'dependent' => true
		),
		'Certification' => array(
			'dependent' => true,
			'foreignKey' => 'instructor_id',
			'order' => array(
				'Certification.date_expires' => 'DESC'
			)
		)
	);
	public $hasAndBelongsToMany = array(
		'Role' => array(
			'className' => 'Role',
			'dependent' => true
		)
    );

	/*
	public function beforeSave($options = array()) {
		if (! $this->id) {
			$passwordHasher = new SimplePasswordHasher();
            $this->data['User']['password'] = $passwordHasher->hash($this->data['User']['password']);
        }
        return true;
    }
    */

	public function findIdByEmail($email) {
		$result = $this->find('first', array(
			'conditions' => array('User.email' => $email),
			'fields' => array('User.id'),
			'contain' => false
		));
		if ($result) {
			return $result['User']['id'];
		}
		return false;
	}

	/**
	 * Returns TRUE if User is currently registered for Course (class list, not waiting list)
	 * @param int $user_id
	 * @param int $course_id
	 * @return boolean
	 */
	public function registeredForCourse($user_id, $course_id) {
		if (! $this->exists($user_id)) {
			return false;
		}
		if (! $this->CourseRegistration->Course->exists($course_id)) {
			return false;
		}
		$result = $this->CourseRegistration->find('count', array(
			'conditions' => array(
				'CourseRegistration.user_id' => $user_id,
				'CourseRegistration.course_id' => $course_id,
				'CourseRegistration.waiting_list' => 0
			)
		));
		return ! empty($result);
	}

	/**
	 * Returns an array of the course IDs => registration IDs that the specified user is registered for
	 * @param int $user_id
	 * @return array
	 */
	public function coursesRegisteredFor($user_id) {
		if (empty($user_id)) {
			return array();
		}
		$results = $this->CourseRegistration->find('all', array(
			'conditions' => array(
				'CourseRegistration.user_id' => $user_id
			),
			'fields' => array(
				'CourseRegistration.id',
				'CourseRegistration.course_id'
			),
			'contain' => false
		));
		if (empty($results)) {
			return array();
		}
		$registrations = array();
		foreach ($results as $result) {
			$course_id = $result['CourseRegistration']['course_id'];
			$reg_id = $result['CourseRegistration']['id'];
			$registrations[$course_id] = $reg_id;
		}
		return $registrations;
	}

	public function randomPassword($length = 8) {
		$alphabet = 'abcdefghijklmnopqrstuwxyzABCDEFGHIJKLMNOPQRSTUWXYZ0123456789';
		$pass = '';
		$alphaLength = strlen($alphabet) - 1;
		for ($i = 0; $i < $length; $i++) {
			$n = rand(0, $alphaLength);
			$pass .= $alphabet[$n];
		}
		return $pass;
	}

	/**
	 * Returns TRUE if the user has attended any course
	 * @param int $user_id
	 * @return boolean
	 */
	public function hasAttendedCourse($user_id) {
		$attended_course = $this->CourseRegistration->find('count', array(
			'conditions' => array(
				'CourseRegistration.user_id' => $user_id,
				'CourseRegistration.attended' => true
			)
		));
		return $attended_course > 0;
	}

	/**
	 * Returns TRUE if user $user_id has purchased product $product_id
	 * @param int $user_id
	 * @param int $product_id
	 * @return boolean
	 */
	public function hasPurchased($user_id, $product_id) {
		$cache_key = "hasPurchased($user_id, $product_id)";
		if ($cached = Cache::read($cache_key)) {
			return $cached;
		}

		$result = $this->Purchase->find('count', array(
			'conditions' => array(
				'Purchase.user_id' => $user_id,
				'Purchase.product_id' => $product_id
			)
		));
		$retval = $result > 0;
		Cache::write($cache_key, $retval);
		return $retval;
	}

	public function hasSubmittedRelease($user_id) {
		$count = $this->Release->find(
			'count',
			array(
				'conditions' => array(
					'Release.user_id' => $user_id
				)
			)
		);
		return $count > 0;
	}

	public function canAccessInstructorTraining($user_id = null) {
		if (! $user_id || ! $this->hasSubmittedRelease($user_id)) {
			return false;
		}
		return ($this->hasRole($user_id, 'admin') || $this->hasRole($user_id, 'trainee'));
	}

	public function sendPasswordResetEmail($user_id) {
		$this->id = $user_id;
		$user = $this->find('first', array(
			'conditions' => array(
				'User.id' => $user_id
			),
			'fields' => array(
				'User.name',
				'User.email'
			),
			'contain' => false
		));
		$reset_password_hash = $this->getResetPasswordHash($user_id, $user['User']['email']);
		App::uses('CakeEmail', 'Network/Email');
		$email = new CakeEmail('default');
		$reset_url = Router::url(array(
			'controller' => 'users',
			'action' => 'reset_password',
			$user_id,
			$reset_password_hash
		), true);
		$email->to($user['User']['email'])
			->subject('Elemental: Reset Password')
			->template('forgot_password')
			->emailFormat('both')
			->helpers(array('Html', 'Text'))
			->viewVars(compact(
				'user',
				'reset_url'
			));
		return $email->send();
	}

	public function getResetPasswordHash($user_id, $email = null) {
		$salt = Configure::read('password_reset_salt');
		return md5($user_id.$email.$salt);
	}

	/**
	 * Returns an array of roles to be used as options in <select> form fields
	 * @return array
	 */
	public function getRoleOptions() {
		return array(
			'student' => 'Student',
			'instructor' => 'Instructor',
			'trainee' => 'Instructor in Training',
			'admin' => 'Administrator'
		);
	}

	public function canAccessReviewMaterials($user_id) {
		if ($this->hasRole($user_id, 'instructor') || $this->hasRole($user_id, 'admin')) {
			return true;
		}
		$expiration = $this->getReviewModuleAccessExpiration($user_id);
		return $expiration && $expiration > time();
	}

	public function getReviewModuleAccessExpiration($user_id) {
		$cache_key = "getReviewModuleAccessExpiration($user_id)";
		if ($cached = Cache::read($cache_key)) {
			return $cached;
		}

		$retval = false;

		// Only students who have attended courses can access review materials
		$courses_attended = $this->CourseRegistration->find('list', array(
			'conditions' => array(
				'CourseRegistration.user_id' => $user_id,
				'CourseRegistration.attended' => true
			)
		));
		if (! empty($courses_attended)) {

			// Students who have attended in the last year get free access
			$course = $this->Course->find('first', array(
				'conditions' => array(
					'Course.id' => array_values($courses_attended)
				),
				'contain' => false,
				'fields' => array(
					'Course.begins'
				),
				'order' => 'Course.begins DESC'
			));
			if (! empty($course)) {
				$retval = strtotime($course['Course']['begins'].' + 1 year + 2 days');
			}

			// Students who have purchased the review material module in the past year get access
			$Product = ClassRegistry::init('Product');
			$product_id = $Product->getProductId('srm renewal');
			$purchase = $this->Purchase->find('first', array(
				'conditions' => array(
					'Purchase.user_id' => $user_id,
					'Purchase.product_id' => $product_id
				),
				'contain' => false,
				'fields' => array(
					'Purchase.created'
				),
				'order' => 'Purchase.created DESC'
			));
			if (! empty($purchase)) {

				// Base expiration date on purchase instead of class attendance if purchase was more recent
				$expiration = strtotime($purchase['Purchase']['created'].' + 1 year + 2 days');
				if ($retval && $expiration > $retval) {
					$retval = $expiration;
				}
			}
		}

		Cache::write($cache_key, $retval);
		return $retval;
	}

	public function getInstructorList() {
		$results = $this->Role->find('first', array(
			'conditions' => array(
				'name' => 'instructor'
			),
			'contain' => array(
				'User' => array(
					'fields' => array(
						'User.id',
						'User.name'
					),
					'order' => 'User.name'
				)
			),
			'fields' => array(
				'Role.id'
			)
		));
		$retval = array();
		foreach ($results['User'] as $user) {
			$retval[$user['id']] = $user['name'];
		}
		return $retval;
	}

	public function getCertifiedInstructorList() {
		$results = $this->Certification->find(
			'all',
			array(
				'conditions' => array(
					'Certification.date_expires >' => date('Y-m-d')
				),
				'contain' => array(
					'User' => array(
						'fields' => array(
							'User.id',
							'User.name'
						)
					)
				),
				'fields' => array(
					'Certification.id'
				)
			)
		);
		$retval = array();
		foreach ($results as $result) {
			$id = $result['User']['id'];
			$name = $result['User']['name'];
			$retval[$id] = $name;
		}
		asort($retval);
		return $retval;
	}

	public function isCertified($user_id) {
		$result = $this->Certification->find(
			'count',
			array(
				'conditions' => array(
					'Certification.date_expires >' => date('Y-m-d'),
					'Certification.instructor_id' => $user_id
				)
			)
		);
		return $result > 0;
	}

	/**
	 * Determines if user current has the specified role (or any of the specified roles if $role_name is an array)
	 * @param int $user_id
	 * @param string|array $role_name
	 * @return boolean
	 */
	public function hasRole($user_id, $role_name) {
		if (is_array($role_name)) {
			$cache_key = "hasRole($user_id, ".implode(', ', $role_name).")";
		} else {
			$cache_key = "hasRole($user_id, $role_name)";
		}
		if ($cached = Cache::read($cache_key)) {
			return $cached;
		}
		$result = $this->find(
			'first',
			array(
				'conditions' => array(
					'User.id' => $user_id
				),
				'contain' => array(
					'Role' => array(
						'fields' => array(
							'Role.name'
						)
					)
				),
				'fields' => array(
					'User.id'
				)
			)
		);
		if (isset($result['Role'])) {
			foreach ($result['Role'] as $role) {
				if ((is_array($role_name) && in_array($role['name'], $role_name)) || $role['name'] == $role_name) {
					Cache::write($cache_key, true);
					return true;
				}
			}
		}
		Cache::write($cache_key, false);
		return false;
	}

	public function grantStudentRole($user_id) {
		$role_id = $this->Role->getIdWithName('student');
		$result = $this->RolesUser->find(
			'count',
			array(
				'conditions' => array(
					'RolesUser.role_id' => $role_id,
					'RolesUser.user_id' => $user_id
				)
			)
		);

		// User is already a student
		if ($result) {
			return;
		}

		$this->RolesUser->create();
		$this->RolesUser->save(array(
			'RolesUser' => array(
				'role_id' => $role_id,
				'user_id' => $user_id
			)
		));
		$cache_key = "hasRole($user_id, student)";
		Cache::delete($cache_key);
	}

	public function canAccessClassroomModule($user_id) {
		App::import('Model','Product');
		$Product = new Product();
		$expiration = $Product->getClassroomModuleAccessExpiration($user_id);
		return $expiration && $expiration > time();
	}

	public function validNewPassword($check) {
		return $this->data[$this->name]['new_password'] == $this->data[$this->name]['confirm_password'];
	}

	public function validNewEmail($check) {
		return $this->data[$this->name]['new_email'] == $this->data[$this->name]['confirm_email'];
	}

	public function getFirstName($user_id) {
		$name = $this->field('name', array(
			'User.id' => $user_id
		));
		$name_split = explode(' ', $name);
		return $name_split[0];
	}

    /**
     * Returns true if the user is the instructor of a course
     * taking place on or after today
     *
     * @param int $instructor_id
     * @return bool
     */
    public function hasUpcomingCourse($instructor_id) {
        App::import('Model', 'Course');
        $Course = new Course();
        $count = $Course->find(
            'count',
            array(
                'conditions' => array(
                    'Course.user_id' => $instructor_id,
                    'Course.begins >=' => date('Y-m-d')
                )
            )
        );
        return $count > 0;
    }
}
