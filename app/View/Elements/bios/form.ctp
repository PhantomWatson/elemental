<?php
	$upload_max = ini_get('upload_max_filesize');
	$post_max = ini_get('post_max_size');
?>

<fieldset>
	<div class="form-group">
		<?php
			$label = ($this->request->controller == 'users') ? 'Instructor Bio' : false;
			echo $this->Tinymce->input('Bio.bio',
				array(
					'label' => $label,
					'div' => array(
						'class' => 'form-group'
					)
				),
				array(
					'language' => 'en',
					'theme_advanced_buttons1' => 'bold,italic,underline,separator,link,unlink,separator,undo,redo,cleanup,code',
					'theme_advanced_statusbar_location' => 'none',
					'valid_elements' => 'p,br,a[href|target=_blank],strong/b,i/em,u,img[src|style|alt|title]',
					'width' => 500,

					/* These three prevent links to other pages on this same domain
					 * from being converted to relative URLs. */
					'relative_urls' => false,
					'remove_script_host' => false,
					'convert_urls' => false
				)
			);
		?>
	</div>
</fieldset>

<div class="form-group">
	<a href="#" id="image_upload_button">Select image</a>
	<span class="text-info">
		Images must be of type JPG, GIF, or PNG
		<?php if ($post_max): ?>
			and cannot exceed <?php echo $post_max; ?>B
		<?php endif; ?>
	</span>
</div>

<div class="form-group" id="bio_image_container">
	<?php
		if (isset($this->request->data['Image']['filename'])) {
			$image_filename = $this->request->data['Image']['filename'];
		} elseif (isset($this->request->data['Bio']['Image']['filename'])) {
			$image_filename = $this->request->data['Bio']['Image']['filename'];
		} else {
			$image_filename = false;
		}
	?>
	<?php if ($image_filename): ?>
		<img src="/img/bios/<?php echo $image_filename; ?>" alt="Your uploaded image" />
	<?php endif; ?>
</div>

<?php
	echo $this->Html->script('/uploadifive/jquery.uploadifive.min.js', array('inline' => false));
	echo $this->Html->css('/uploadifive/uploadifive.css', null, array('inline' => false));
	$this->Js->buffer("
		bioForm.setupUpload({
			token: '".md5(Configure::read('image_upload_token').time())."',
			post_max: '{$post_max}B',
			timestamp: ".time().",
			instructor_id: $instructor_id
		});
	");