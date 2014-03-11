<?php
App::uses('Participant', 'Model');

/**
 * Participant Test Case
 *
 */
class ParticipantTestCase extends CakeTestCase {
/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array('app.participant', 'app.course');

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->Participant = ClassRegistry::init('Participant');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->Participant);

		parent::tearDown();
	}

}
