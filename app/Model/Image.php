<?php
App::uses('AppModel', 'Model');
class Image extends AppModel {
	public $name = 'Image';
	public $displayField = 'filename';
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

	private $__errors = array();
	private $__fileToDelete = null;
	public $errors = array();
	public $maxHeight = 2000;
	public $maxWidth = 2000;
	public $fullQuality = 90;

	/**
	 * Returns the ID for the next Image to be added to the database.
	 */
	public function getNextId() {
		$result = $this->query('SHOW TABLE STATUS LIKE \'images\'');
		return $result[0]['TABLES']['Auto_increment'];
	}

	/**
	 * Resizes the image only if it exceeds maximum dimensions, returns FALSE on error or TRUE otherwise
	 * @param string $filepath
	 * @return boolean
	 */
	public function autoResize($filepath) {
		list($width, $height, $type, $attr) = getimagesize($filepath);
		if ($width < $this->maxWidth && $height < $this->maxHeight) {
			// No resize necessary
			return true;
		}

		// Make longest side fit inside the maximum dimensions
		if ($width >= $height) {
			$new_width = $this->maxWidth;
			$new_height = 0;
		} else {
			$new_width = 0;
			$new_height = $this->maxHeight;
		}
		if ($this->resize($filepath, $filepath, $new_width, $new_height, $this->fullQuality)) {
			return true;
		}
		return false;
	}

    /**
     * Determines image type, calculates scaled image size, and returns resized image. If no width or height is
     * specified for the new image, the dimensions of the original image will be used, resulting in a copy
     * of the original image.
     *
     * @param string $source_file absolute path to original image file
     * @param string $new_filename absolute path to new image file to be created
     * @param integer $new_width (optional) width to scale new image (default 0)
     * @param integer $new_height (optional) height to scale image (default 0)
     * @param integer $quality quality of new image (default 100, resizePng will recalculate this value)
     *
     * @access public
     *
     * @return returns new image on success, false on failure
     */
    public function resize($source_file, $new_filename, $new_width = 0, $new_height = 0, $quality = 100) {
        if(! ($image_params = getimagesize($source_file))) {
            $this->errors[] = 'Original file is not a valid image: ' . $source_file;
            return false;
        }

        $width = $image_params[0];
        $height = $image_params[1];

        if(0 != $new_width && 0 == $new_height) {
            $scaled_width = $new_width;
            $scaled_height = floor($new_width * $height / $width);
        } elseif(0 != $new_height && 0 == $new_width) {
            $scaled_height = $new_height;
            $scaled_width = floor($new_height * $width / $height);
        } elseif(0 == $new_width && 0 == $new_height) { //assume we want to create a new image the same exact size
            $scaled_width = $width;
            $scaled_height = $height;
        } else { //assume we want to create an image with these exact dimensions, most likely resulting in distortion
            $scaled_width = $new_width;
            $scaled_height = $new_height;
        }

        //create image
        $ext = $image_params[2];
        switch($ext) {
            case IMAGETYPE_GIF:
                $return = $this->__resizeGif($source_file, $new_filename, $scaled_width, $scaled_height, $width, $height, $quality);
                break;
            case IMAGETYPE_JPEG:
                $return = $this->__resizeJpeg($source_file, $new_filename, $scaled_width, $scaled_height, $width, $height, $quality);
                break;
            case IMAGETYPE_PNG:
                $return = $this->__resizePng($source_file, $new_filename, $scaled_width, $scaled_height, $width, $height, $quality);
                break;
            default:
                $return = $this->__resizeJpeg($source_file, $new_filename, $scaled_width, $scaled_height, $width, $height, $quality);
                break;
        }

        return $return;
    }

    private function __resizeGif($original, $new_filename, $scaled_width, $scaled_height, $width, $height) {
        $error = false;

        if(!($src = imagecreatefromgif($original))) {
            $this->errors[] = 'There was an error creating your resized image (gif).';
            $error = true;
        }

        if(!($tmp = imagecreatetruecolor($scaled_width, $scaled_height))) {
            $this->errors[] = 'There was an error creating your true color image (gif).';
            $error = true;
        }

        if(!imagecopyresampled($tmp, $src, 0, 0, 0, 0, $scaled_width, $scaled_height, $width, $height)) {
            $this->errors[] = 'There was an error creating your true color image (gif).';
            $error = true;
        }

        if(!($new_image = imagegif($tmp, $new_filename))) {
            $this->errors[] = 'There was an error writing your image to file (gif).';
            $error = true;
        }

        imagedestroy($tmp);

        if(false == $error) {
            return $new_image;
        }

        return false;
    }

    private function __resizeJpeg($original, $new_filename, $scaled_width, $scaled_height, $width, $height, $quality) {
        $error = false;

        if(!($src = imagecreatefromjpeg($original))) {
            $this->errors[] = 'There was an error creating your resized image (jpg).';
            $error = true;
        }

        if(!($tmp = imagecreatetruecolor($scaled_width, $scaled_height))) {
            $this->errors[] = 'There was an error creating your true color image (jpg).';
            $error = true;
        }

        if(!imagecopyresampled($tmp, $src, 0, 0, 0, 0, $scaled_width, $scaled_height, $width, $height)) {
            $this->errors[] = 'There was an error creating your true color image (jpg).';
            $error = true;
        }

        if(!($new_image = imagejpeg($tmp, $new_filename, $quality))) {
            $this->errors[] = 'There was an error writing your image to file (jpg).';
            $error = true;
        }

        imagedestroy($tmp);

        if(false == $error) {
            return $new_image;
        }

        return false;
    }

    private function __resizePng($original, $new_filename, $scaled_width, $scaled_height, $width, $height, $quality) {
        $error = false;
        /**
         * we need to recalculate the quality for imagepng()
         * the quality parameter in imagepng() is actually the compression level,
         * so the higher the value (0-9), the lower the quality. this is pretty much
         * the opposite of how imagejpeg() works.
         */
        $quality = ceil($quality / 10); // 0 - 100 value
        if(0 == $quality) {
            $quality = 9;
        } else {
            $quality = ($quality - 1) % 9;
        }


        if(!($src = imagecreatefrompng($original))) {
            $this->errors[] = 'There was an error creating your resized image (png).';
            $error = true;
        }

        if(!($tmp = imagecreatetruecolor($scaled_width, $scaled_height))) {
            $this->errors[] = 'There was an error creating your true color image (png).';
            $error = true;
        }

        imagealphablending($tmp, false);

        if(!imagecopyresampled($tmp, $src, 0, 0, 0, 0, $scaled_width, $scaled_height, $width, $height)) {
            $this->errors[] = 'There was an error creating your true color image (png).';
            $error = true;
        }

        imagesavealpha($tmp, true);

        if(!($new_image = imagepng($tmp, $new_filename, $quality))) {
            $this->errors[] = 'There was an error writing your image to file (png).';
            $error = true;
        }

        imagedestroy($tmp);

        if(false == $error) {
            return $new_image;
        }

        return false;
    }

	public function beforeDelete($cascade = true) {
		$this->__fileToDelete = $this->field('filename');
	}
}