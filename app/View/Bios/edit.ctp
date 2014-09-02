<?php
	$upload_max = ini_get('upload_max_filesize');
	$post_max = ini_get('post_max_size');

	/*
	 * Instructor picture cannot exceed <?php echo $post_max; ?>B
	 */
?>
<div class="page-header">
	<h1>
		<?php echo $title_for_layout; ?>
	</h1>
</div>

<div id="bio_form">
	<?php echo $this->Form->create('Bio'); ?>

	<fieldset>
		<div class="form-group">
			<?php echo $this->Tinymce->input('Bio.bio',
				array(
					'label' => false,
					'div' => false
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
			); ?>
		</div>
	</fieldset>

	<div class="form-group">
		<a href="#" id="image_upload_button">Select image</a>
	</div>

	<div class="form-group" id="bio_image_container">

	</div>

	<?php echo $this->Form->end(array(
		'label' => 'Update',
		'class' => 'btn btn-default'
	)); ?>
</div>
<?php
	echo $this->Html->script('/uploadifive/jquery.uploadifive.min.js', array('inline' => false));
	echo $this->Html->css('/uploadifive/uploadifive.css', null, array('inline' => false));
	$this->Js->buffer("
		bioForm.setupUpload({
			token: '".md5(Configure::read('image_upload_token').time())."',
			post_max: '{$post_max}B',
			timestamp: ".time()."
		});
	");