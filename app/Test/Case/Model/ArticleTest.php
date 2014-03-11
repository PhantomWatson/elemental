<?php
App::uses('Article', 'Model');

/**
 * Article Test Case
 *
 */
class ArticleTestCase extends CakeTestCase {
/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array('app.article', 'app.user');

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->Article = ClassRegistry::init('Article');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->Article);

		parent::tearDown();
	}

}
