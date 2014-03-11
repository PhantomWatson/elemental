<?php
App::uses('CourseDate', 'Model');

/**
 * CourseDate Test Case
 *
 */
class CourseDateTestCase extends CakeTestCase {
/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array('app.course_date', 'app.course', 'app.participant');

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->CourseDate = ClassRegistry::init('CourseDate');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->CourseDate);

		parent::tearDown();
	}

}
