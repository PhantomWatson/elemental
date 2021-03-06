<?php
App::uses('AppModel', 'Model');
/**
 * CourseDate Model
 *
 * @property Course $Course
 */
class CourseDate extends AppModel {
/**
 * Display field
 *
 * @var string
 */
	public $displayField = 'date';
/**
 * Validation rules
 *
 * @var array
 */
	public $validate = array(
		'course_id' => array(
			'numeric' => array(
				'rule' => array('numeric'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'date' => array(
			'date' => array(
				'rule' => array('date'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'start_time' => array(
			'time' => array(
				'rule' => array('time'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'end_time' => array(
			'time' => array(
				'rule' => array('time'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		)
	);

	//The Associations below have been created with all possible keys, those that are not needed can be removed

/**
 * belongsTo associations
 *
 * @var array
 */
	public $belongsTo = array(
		'Course' => array(
			'className' => 'Course',
			'foreignKey' => 'course_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		)
	);

    /**
     * Returns the IDs of all courses with dates on and after today
     *
     * @return array
     */
	public function getCurrentAndUpcomingCourses() {
	    $results = $this->find('all', array(
	        'conditions' => array(
                'CourseDate.date >=' => date('Y-m-d')
            ),
            'fields' => array('CourseDate.course_id')
        ));
	    if ($results) {
            $course_ids = array();
	        foreach ($results as $result) {
	            $course_id = $result['CourseDate']['course_id'];
	            if (! in_array($course_id, $course_ids)) {
                    $course_ids[] = $course_id;
                }
            }

            return $course_ids;
        }

        return array();
    }
}
