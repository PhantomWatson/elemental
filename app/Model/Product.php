<?php
App::uses('AppModel', 'Model');
class Product extends AppModel {
	public $displayField = 'name';
	public $validate = array(
		'name' => array(
			'notempty' => array(
				'rule' => 'notBlank',
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'description' => array(
			'notempty' => array(
				'rule' => 'notBlank',
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'cost' => array(
			'numeric' => array(
				'rule' => array('numeric'),
				//'allowEmpty' => false,
				//'message' => 'Your custom message here',
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		)
	);

	public $hasMany = array(
		'Purchase' => array(
			'className' => 'Purchase',
			'foreignKey' => 'product_id',
			'conditions' => '',
			'fields' => '',
			'order' => 'Purchase.created DESC'
		)
	);

	public function afterFind($results, $primary = false) {
		foreach ($results as $key => $val) {

			// Convert cost from cents into dollars
			if (isset($val['Product']['cost'])) {
				$cost = $results[$key]['Product']['cost'] / 100;
				$cost = number_format($cost, 2);
				$results[$key]['Product']['cost'] = $cost;
			}
		}
		return $results;
	}

	public function getClassroomModuleAccessExpiration($user_id) {
		$product_id = $this->getProductId('classroom module');
		$purchase = $this->Purchase->find('first', array(
			'conditions' => array(
				'Purchase.user_id' => $user_id,
				'Purchase.product_id' => $product_id
			),
			'contain' => false,
			'fields' => array(
				'Purchase.id',
                'Purchase.created'
			),
			'order' => 'Purchase.created DESC'
		));

		// No purchase? Expiration inapplicable
		if (empty($purchase)) {
            return false;
        }

        $year_after_purchase = strtotime($purchase['Purchase']['created'].' + 1 year + 2 days');
        App::import('Model', 'Course');
        $Course = new Course();
        $most_recent_course = $Course->find('first', array(
            'conditions' => array(
                'Course.user_id' => $user_id
            ),
            'contain' => false,
            'fields' => array(
                'Course.begins'
            ),
            'order' => 'Course.begins DESC'
        ));

        // No courses? Expiration is a year after purchase
        if (empty($most_recent_course)) {
            return $year_after_purchase;
        }

        // Courses scheduled after purchase? Expiration is one year after the latest-scheduled course began
        $course = $most_recent_course['Course'];
        $year_after_teaching = strtotime($course['begins'].' + 1 year + 2 days');
        return max($year_after_purchase, $year_after_teaching);
	}

	public function getProductId($product) {
		switch ($product) {
			case 'srm renewal':
				return 2;
			case 'srm':
				return 3;
			case 'classroom module':
				return 4;
		}

		return false;
	}
}