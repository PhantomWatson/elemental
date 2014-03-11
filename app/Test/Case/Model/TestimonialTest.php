<?php
App::uses('Testimonial', 'Model');

/**
 * Testimonial Test Case
 *
 */
class TestimonialTestCase extends CakeTestCase {
/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array('app.testimonial');

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->Testimonial = ClassRegistry::init('Testimonial');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->Testimonial);

		parent::tearDown();
	}

}
