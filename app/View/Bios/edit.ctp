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

	<?php echo $this->Form->end(array(
		'label' => 'Update',
		'class' => 'btn btn-default'
	)); ?>
</div>