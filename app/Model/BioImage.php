<?php
App::uses('Image', 'Model');
class BioImage extends Image {
	public $name = 'BioImage';
	public $displayField = 'filename';
	public $useTable = 'images';
	public $hasOne = array(
		'Bio' => array(
			'foreignKey' => 'image_id'
		)
	);
	public $validate = array(
		'image' => array(
			'upload' => array(
				'rule' => 'isFileUpload',
				'message' => 'No file was selected to upload.',
				'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
			'complete' => array(
				'rule' => 'isCompletedUpload',
				'message' => 'File was not successfully uploaded.',
				'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
			'php_size_limit' => array(
				'rule' => 'isUnderPhpSizeLimit',
				'message' => 'File exceeds upload filesize limit'
			),
			'form_size_limit' => array(
				'rule' => 'isUnderFormSizeLimit',
				'message' => 'File exceeds form upload filesize limit'
			),
			'temp_dir' => array(
				'rule' => array('tempDirExists', false), // false skips this check if a file is not uploaded
				'message' => 'The system temporary directory is missing'
			),
			'written' => array(
				'rule' => array('isSuccessfulWrite', false),
				'message' => 'File was not successfully written to the server'
			),
			'php_ext' => array(
				'rule' => array('noPhpExtensionErrors', false),
				'message' => 'File was not uploaded because of a faulty PHP extension'
			),
			'mime_type' => array(
				'rule' => array('isValidMimeType', array('image/png', 'image/jpeg', 'image/gif'), false),
				'message' => 'File is not in one of the required image formats (png, jpeg, or gif)'
			),
			'dir_writable' => array(
				'rule' => array('isWritable', false),
				'message' => 'File upload directory was not writable'
			),
			'dir_exists' => array(
				'rule' => array('isValidDir', false),
				'message' => 'File upload directory does not exist'
			),
			'valid_extension' => array(
				'rule' => array('isValidExtension', array('jpg', 'jpeg', 'gif', 'png'), false),
        		'message' => 'File has an invalid extension (must be .JPG, .JPEG, .GIF, or .PNG)'
			)
		)
	);

	/**
	 * After a new bio image is uploaded, delete any other images this user previously uploaded
	 */
	public function afterSave($created, $options = array()) {
		$new_filename = $this->data['BioImage']['filename'];
		$bio_id = $this->data['BioImage']['id'];

		if (! $bio_id) {
			return;
		}
		App::uses('Folder', 'Utility');
		App::uses('File', 'Utility');
		$path = ROOT.DS.APP_DIR.DS.WEBROOT_DIR.DS.'img'.DS.'bios';
		$dir = new Folder($path);
		$files = $dir->find("$bio_id\.*");
		foreach ($files as $uploaded_filename) {
			if ($uploaded_filename != $new_filename) {
				$file = new File($path.DS.$uploaded_filename);
				$file->delete();
			}
		}
	}

	/**
	 * Attempts to upload an image for this user's bio and returns array(success / failure, array / error msg)
	 */
	public function upload($user_id) {
		$bio_id = $this->Bio->field('id', array(
			'user_id' => $user_id
		));
		if (! $bio_id) {
			return array(false, 'No bio found for user '.$user_id);
		}

		$verifyToken = md5(Configure::read('image_upload_token').$_POST['timestamp']);
		if ($_POST['token'] != $verifyToken) {
			return array(false, 'Security code incorrect');
		}

		$uploadDir = ROOT.DS.APP_DIR.DS.WEBROOT_DIR.DS.'img'.DS.'bios';
		$fileParts = pathinfo($_FILES['Filedata']['name']);
		$filename = $bio_id.'.'.strtolower($fileParts['extension']);
		$targetFile = $uploadDir.DS.$filename;
		$fileTypes = array('jpg', 'jpeg', 'gif', 'png');
		if (! in_array(strtolower($fileParts['extension']), $fileTypes)) {
			return array(false, 'Invalid file type.');
		}

		$tempFile = $_FILES['Filedata']['tmp_name'];
		if (! $this->autoResize($tempFile)) {
			$msg = 'Error resizing image';
			if (! empty($this->errors)) {
				$msg .= ': '.implode('; ', $this->errors);
			}
			return array(false, $msg);
		}

		if (! move_uploaded_file($tempFile, $targetFile)) {
			return array(false, 'Could not save file.');
		}

		$this->create();
		$save_result = $this->save(array(
			'filename' => $filename,
			'user_id' => $user_id
		));
		if (! $save_result) {
			return array(false, 'Error saving image');
		}

		$image = array(
			'id' => $this->id,
			'bio_id' => $bio_id,
			'filename' => $filename
		);
		return array(true, $image);
	}
}