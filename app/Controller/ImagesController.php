<?php
App::uses('AppController', 'Controller');
/**
 * Images Controller
 *
 * @property Image $Image
 */
class ImagesController extends AppController {
	public function beforeFilter() {
		parent::beforeFilter();
		$this->Auth->deny(array('edit'));
	}

	public function isAuthorized($user) {
		$user_id = $this->Auth->user('id');
		$this->loadModel('User');
		$is_instructor = $this->User->hasRole($user_id, 'instructor');

		switch ($this->action) {
			case 'upload_for_bio':
				if ($is_instructor) return true;
				break;
		}

		// Admins can access everything
		return parent::isAuthorized($user);
	}

	public function upload_for_bio() {
		$user_id = $this->Auth->user('id');
		$this->loadModel('BioImage');

		list($success, $retval) = $this->BioImage->upload($user_id);
		if (! $success) {
			$this->response->statusCode(500);
		}
		$this->layout = 'blank';
		$this->set('retval', $retval);
	}

	private function upload($dir) {
		$uploadDir = ROOT.DS.APP_DIR.DS.WEBROOT_DIR.DS.'img'.DS.$dir.DS;
		$fileTypes = array('jpg', 'jpeg', 'gif', 'png');
		$verifyToken = md5(Configure::read('image_upload_token').$_POST['timestamp']);
		if (! empty($_FILES) && $_POST['token'] == $verifyToken) {
			$tempFile = $_FILES['Filedata']['tmp_name'];
			$image_id = $this->Image->getNextId();
			$fileParts = pathinfo($_FILES['Filedata']['name']);
			$filename = $image_id.'.'.strtolower($fileParts['extension']);
			$targetFile = $uploadDir.$filename;
			if (in_array(strtolower($fileParts['extension']), $fileTypes)) {
				if ($this->Image->autoResize($tempFile)) {
					if (move_uploaded_file($tempFile, $targetFile)) {
						// Create DB entry for the image
						$this->Image->create();
						$save_result = $this->Image->save(array(
							'filename' => $filename
						));
						if ($save_result) {
							echo '{id: '.$this->Image->id.', filename: "'.$filename.'"}';
						} else {
							$this->response->statusCode(500);
							echo 'Error saving image';
						}
					} else {
						$this->response->statusCode(500);
						echo 'Could not save file.';
					}
				} else {
					$this->response->statusCode(500);
					echo 'Error resizing image';
					if (! empty($this->Image->errors)) {
						echo ': '.implode('; ', $this->Image->errors);
					}
				}
			} else {
				echo 'Invalid file type.';
			}
		} else {
			$this->response->statusCode(500);
			echo 'Security code incorrect';
		}
		$this->layout = 'blank';
		$this->render('/Pages/blank');
	}

	/**
	 * Effectively bypasses Uploadify's check for an existing file
	 * (because the filename is changed as it's being saved).
	 */
	public function file_exists() {
		exit(0);
	}

	public function filename($image_id) {
		$this->Image->id = $image_id;
		$filename = $this->Image->field('filename');
		echo $filename ? $filename : 0;
		$this->layout = 'blank';
		$this->render('/Pages/blank');
	}
}