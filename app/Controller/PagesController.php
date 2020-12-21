<?php
/**
 * Static content controller.
 *
 * This file will render views from views/pages/
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
 * @package       app.Controller
 * @since         CakePHP(tm) v 0.2.9
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
App::uses('AppController', 'Controller');
class PagesController extends AppController {
	public $name = 'Pages';
	public $uses = array();

	public function beforeFilter() {
		parent::beforeFilter();
	}

	public function home() {
		$this->loadModel('Article');
		$this->loadModel('Testimonial');
		$this->loadModel('Course');
		$courses = $this->Course->find('all', array(
			'conditions' => array(
				'Course.begins >=' => date('Y-m-d')
			),
			'fields' => array(
				'Course.id',
				'Course.location',
				'Course.city',
				'Course.state',
			),
			'order' => array('Course.begins ASC'),
			'limit' => 10,
			'contain' => array(
				'CourseDate'
			)
		));
		// Remove any courses without dates
		foreach ($courses as $k => $course) {
			if (empty($course['CourseDate'])) {
				unset($courses[$k]);
			}
		}

		$this->set(array(
			'title_for_layout' => '',
			'article' => $this->Article->find('first', array(
				'order' => 'Article.created DESC',
				'contain' => false
			)),
			'more_articles' => $this->Article->find('all', array(
				'fields' => array(
					'Article.id',
					'Article.title',
					'Article.created'
				),
				'order' => 'Article.created DESC',
				'contain' => false,
				'limit' => 6,
				'offset' => 1
			)),
			'testimonials' => $this->Testimonial->find('all', array(
				'conditions' => array('Testimonial.approved' => 1),
				'contain' => false,
				'order' => 'rand()',
				'limit' => 10
			)),
			'courses' => $courses
		));
	}

	public function about() {
		$this->redirect(array('action' => 'about_intro'));
	}

	public function about_intro() {
		$this->set(array(
			'title_for_layout' => 'Introduction to Elemental Sexual Assault Protection'
		));
	}

	public function about_bios() {
		$this->set(array(
			'title_for_layout' => 'The People Behind Elemental'
		));
	}

	public function about_effectiveness() {
		$this->set(array(
			'title_for_layout' => 'Elemental\'s Effectiveness'
		));
	}

	public function about_pedagogy() {
		$this->set(array(
			'title_for_layout' => 'The Pedagogy Behind Elemental'
		));
	}

	public function terms() {
		$this->set(array(
			'title_for_layout' => 'Web Site Terms and Conditions of Use'
		));
	}

	public function privacy() {
		$this->set(array(
			'title_for_layout' => 'Privacy Policy'
		));
	}

	public function contact() {
		$this->set(['title_for_layout' => 'Contact Us']);
	}

	public function booking() {
		$this->set(array(
				'title_for_layout' => 'Bring Elemental to Your Campus'
		));
	}

	public function clear_cache($key = null) {
		if ($key) {
			if (Cache::delete($key)) {
				$this->Flash->success('Cache cleared ('.$key.')');
			} else {
				$this->Flash->success('Error clearing cache ('.$key.')');
			}
		} else {
			if (Cache::clear(false, 'default') && Cache::clear(false, 'day') && clearCache()) {
				$this->Flash->success('Cache cleared');
			} else {
				$this->Flash->success('Error clearing cache');
			}
		}

		$this->set(array(
			'title_for_layout' => 'Clear Cache'
		));
		return $this->render('/Pages/home');
	}

    public function faq() {
        $this->set(array(
            'title_for_layout' => 'Frequently Asked Questions'
        ));
    }

    public function scholarly_work() {
        $this->set(array(
            'title_for_layout' => 'Scholarly Work'
        ));
    }
}
