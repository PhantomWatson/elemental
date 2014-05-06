<div class="page-header">
	<h1>
		<?php echo $title_for_layout; ?>
	</h1>
</div>
<div class="articles form">
	<?php
		echo $this->Form->create('Article');
		if ($this->request->params['action'] == 'edit') {
			echo $this->Form->input('id');
		}
	?>
	<fieldset>
		<?php echo $this->Form->input(
			'title',
			array(
				'class' => 'form-control',
				'div' => array('class' => 'form-group')
			)
		); ?>

		<div class="form-group">
			<?php echo $this->Tinymce->input('Article.body',
				array('label' => false, 'div' => false),
				array(
					'language' => 'en',
					'theme_advanced_buttons1' => 'bold,italic,underline,separator,link,unlink,separator,undo,redo,cleanup,code',
					'theme_advanced_statusbar_location' => 'none',
					// If this is changed, also change Event::allowed_tags
					'valid_elements' => 'p,br,a[href|target=_blank],strong/b,i/em,u,img[src|style|alt|title]',

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
		'label' => 'Post',
		'class' => 'btn btn-default'
	)); ?>
</div>
