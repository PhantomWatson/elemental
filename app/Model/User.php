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
				'rule' => array('notempty'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'email' => array(
			'email' => array(
				'rule' => array('email'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'password' => array(
			'notempty' => array(
				'rule' => array('notempty'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
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
			'dependent' => false,
			'conditions' => '',
			'fields' => '',
			'order' => '',
			'limit' => '',
			'offset' => '',
			'exclusive' => '',
			'finderQuery' => '',
			'counterQuery' => ''
		),
		'Testimonial' => array(
			'className' => 'Testimonial',
			'foreignKey' => 'user_id',
			'dependent' => false,
			'conditions' => '',
			'fields' => '',
			'order' => '',
			'limit' => '',
			'offset' => '',
			'exclusive' => '',
			'finderQuery' => '',
			'counterQuery' => ''
		),
		'CourseRegistration' => array(
			'className' => 'CourseRegistration',
			'foreignKey' => 'user_id',
			'dependent' => false,
			'conditions' => '',
			'fields' => '',
			'order' => '',
			'limit' => '',
			'offset' => '',
			'exclusive' => '',
			'finderQuery' => '',
			'counterQuery' => ''
		),
		'Course' => array(
			'className' => 'Course',
			'foreignKey' => 'user_id',
			'dependent' => false,
			'conditions' => '',
			'fields' => '',
			'order' => '',
			'limit' => '',
			'offset' => '',
			'exclusive' => '',
			'finderQuery' => '',
			'counterQuery' => ''
		),
		'Purchase' => array(
			'className' => 'Purchase',
			'foreignKey' => 'user_id',
			'dependent' => false,
			'conditions' => '',
			'fields' => '',
			'order' => '',
			'limit' => '',
			'offset' => '',
			'exclusive' => '',
			'finderQuery' => '',
			'counterQuery' => ''
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
	 * Returns TRUE if User is currently registered for Course
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
				'CourseRegistration.course_id' => $course_id
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

	public function canAccessInstructorTraining($user_id) {
		$this->id = $user_id;
		$role = $this->field('role');
		switch ($role) {
			case 'admin':
			case 'instructor-in-training':
				return true;
		}
		return false;
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
			'instructor-in-training' => 'Instructor in Training',
			'admin' => 'Administrator'
		);
	}

	public function canAccessReviewMaterials($user_id) {
		$expiration = $this->getReviewMaterialsAccessExpiration($user_id);
		return $expiration && $expiration > time();
	}

	public function getReviewMaterialsAccessExpiration($user_id) {
		$cache_key = "getReviewMaterialsAccessExpiration($user_id)";
		if ($cached = Cache::read($cache_key, 'day')) {
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
				$retval = strtotime($course['Course']['begins'].' + 1 year');
			}

			// Students who have purchased the review material module in the past year get access
			$Product = ClassRegistry::init('Product');
			$product_id = $Product->getReviewMaterialsId();
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
				$expiration = strtotime($purchase['Purchase']['created'].' + 1 year');
				if ($retval && $expiration > $retval) {
					$retval = $expiration;
				}
			}
		}

		Cache::write($cache_key, $retval);
		return $retval;
	}
}