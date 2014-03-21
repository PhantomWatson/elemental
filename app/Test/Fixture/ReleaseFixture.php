<?php
/**
 * ReleaseFixture
 *
 */
class ReleaseFixture extends CakeTestFixture {

/**
 * Fields
 *
 * @var array
 */
	public $fields = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'key' => 'primary'),
		'user_id' => array('type' => 'integer', 'null' => false, 'default' => null),
		'course_id' => array('type' => 'integer', 'null' => false, 'default' => null),
		'age' => array('type' => 'integer', 'null' => false, 'default' => null, 'length' => 2),
		'guardian_name' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 100, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'guardian_phone' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 20, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'no_phone' => array('type' => 'boolean', 'null' => false, 'default' => '0'),
		'ip_address' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 39, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'created' => array('type' => 'datetime', 'null' => false, 'default' => null),
		'indexes' => array(
			'PRIMARY' => array('column' => 'id', 'unique' => 1)
		),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'InnoDB')
	);

/**
 * Records
 *
 * @var array
 */
	public $records = array(
		array(
			'id' => 1,
			'user_id' => 1,
			'course_id' => 1,
			'age' => 1,
			'guardian_name' => 'Lorem ipsum dolor sit amet',
			'guardian_phone' => 'Lorem ipsum dolor ',
			'no_phone' => 1,
			'ip_address' => 'Lorem ipsum dolor sit amet',
			'created' => '2014-03-22 00:48:08'
		),
	);

}
