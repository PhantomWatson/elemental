<?php
App::uses('Release', 'Model');

/**
 * Release Test Case
 *
 */
class ReleaseTest extends CakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'app.release',
		'app.user',
		'app.article',
		'app.testimonial',
		'app.course_registration',
		'app.course',
		'app.course_date',
		'app.purchase',
		'app.product'
	);

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->Release = ClassRegistry::init('Release');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->Release);

		parent::tearDown();
	}

}
