<?php
/**
 * Application model for Cake.
 *
 * This file is application-wide model file. You can put all
 * application-wide model-related methods here.
 *
 * PHP 5
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       app.Model
 * @since         CakePHP(tm) v 0.2.9
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */

App::uses('Model', 'Model');

/**
 * Application model for Cake.
 *
 * Add your application-wide methods in the class below, your models
 * will inherit them.
 *
 * @package       app.Model
 */
class AppModel extends Model {
	public $actsAs = array('Containable');

	public function _isUnique($check) {
		$values = array_values($check);
		$value = array_pop($values);
		$fields = array_keys($check);
		$field = array_pop($fields);
		if ($field == 'email') {
			$value == strtolower($value);
		}
		if (isset($this->data[$this->name]['id'])) {
			$results = $this->field('id', array(
				$this->name.'.'.$field => $value,
				$this->name.'.id <>' => $this->data[$this->name]['id']
			));
		} else {
			$results = $this->field('id', array(
				"$this->name.$field" => $value
			));
		}
		return empty($results);
	}
}
