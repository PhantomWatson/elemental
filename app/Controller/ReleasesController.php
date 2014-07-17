<?php
App::uses('AppController', 'Controller');
/**
 * Releases Controller
 *
 * @property Release $Release
 */
class ReleasesController extends AppController {
	public function beforeFilter() {
		parent::beforeFilter();
		$this->Auth->deny();
	}

	public function isAuthorized($user = null) {
		return true;
	}

	// Function provided by http://stackoverflow.com/questions/3776682/php-calculate-age
	private function __getAge($birthdate) {
		$birthdate = array_values($birthdate);
		$age = (date("md", date("U", mktime(0, 0, 0, $birthdate[0], $birthdate[1], $birthdate[2]))) > date("md")
			? ((date("Y") - $birthdate[2]) - 1)
			: (date("Y") - $birthdate[2]));
		return $age;
	}

	private function __processForm() {
		$user_id = $this->Auth->user('id');
		$this->Release->create($this->request->data);
		$ip_address = $this->request->clientIp();
		$this->Release->set(compact('user_id', 'ip_address'));

		// Check to see if this should overwrite an existing release
		$existing_release_id = $this->Release->getIdFromUserId($user_id);
		if ($existing_release_id) {
			$this->Release->set('id', $existing_release_id);
		}

		// Remove requirement for guardian info if user is 18+
		$age = $this->__getAge($this->request->data['Release']['birthdate']);
		if ($age >= 18) {
			unset($this->Release->validate['guardian_name']);
			unset($this->Release->validate['guardian_phone']);
		}

		if ($this->Release->save()) {
			if (isset($this->request->course_id)) {
				$action = $this->action == 'add' ? 'submitted' : 'updated';
				$this->Flash->success('Your liability release has been '.$action);
				$this->redirect(array(
					'controller' => 'courses',
					'action' => 'register',
					$this->request->course_id
				));
			} else {
				$this->render('success');
			}
		} else {
			$this->Flash->error('There was an error submitting your liability release. Please check for details below.');
		}
	}

	private function __setupForm() {
		if (isset($this->request->course_id)) {
			$course_id = $this->request->course_id;
			$this->loadModel('Course');
			if (! $this->Course->exists($course_id)) {
				throw new NotFoundException('Invalid course specified');
			}
		}
		$user_id = $this->Auth->user('id');
		$this->loadModel('User');
		$this->User->id = $user_id;
		$this->set('title_for_layout', 'Release of Liability');
	}

	public function add() {
		$this->__setupForm();

		if ($this->request->is('post')) {
			$this->__processForm();
		} else {
			$this->request->data['Release']['name'] = $this->Auth->user('name');
			$this->request->data['Release']['birthdate'] = date('Y-m-d');
		}
		$date = date('jS').' day of '.date('F, Y');
		$this->set('date', $date);
	}

	public function edit() {
		$this->__setupForm();
		$user_id = $this->Auth->user('id');
		$release_id = $this->Release->getIdFromUserId($user_id);
		if (! $release_id) {
			throw new NotFoundException('Cannot edit liability release. Liability release not found');
		}
		$this->Release->id = $release_id;
		$submitted = $this->Release->field('modified');
		$timestamp = strtotime($submitted);
		$date = date('jS', $timestamp).' day of '.date('F, Y', $timestamp);
		$this->set('date', $date);

		if ($this->request->is('post') || $this->request->is('put')) {
			$this->__processForm();
		} else {
			$this->request->data = $this->Release->read();
		}
		$this->render('add');
	}

	/**
	 * For releases with an age but no birthdate (0000-00-00), determines the approximate birthyear and sets the birthdate to Jan 1 of that year.
	 * This is (most likely) before the actual birthdate, so it might increase the user's recorded age. This can be corrected by the user editing
	 * their own release, but is a non-issue, since the only time this discrepancy would make a difference (a 17yo being recorded as an 18yo),
	 * the user would have already submitted a release with their parents' information included before this function was first run.
	 *
	 * This was created after Release.age was replaced by Release.birthdate so that existing releases would be populated with a very
	 * approximately correct birthdate.
	 */
	public function set_birthdates() {
		$releases = $this->Release->find('list');
		foreach ($releases as $id => $user_id) {
			$this->Release->id = $id;
			$birthdate = $this->Release->field('birthdate');
			if ($birthdate == '0000-00-00') {
				$age = $this->Release->field('age');
				if ($age) {
					$submitted = strtotime($this->Release->field('modified'));
					$age_seconds = $age * 60 * 60 * 24 * 365;
					$birth_timestamp = $submitted - $age_seconds;
					$birthdate = date('Y', $birth_timestamp).'-01-01';
					$this->Release->saveField('birthdate', $birthdate);
					$this->Flash->success("Set #$id's DOB to $birthdate.");
				} else {
					$this->Flash->set("Can't set DOB for #$id, age is not provided.");
				}
			} else {
				$this->Flash->set("Skipping #$id, DOB is already set to $birthdate.");
			}
		}
		$this->render('/Pages/home');
	}
}
