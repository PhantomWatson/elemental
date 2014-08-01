<div class="page-header">
	<h1>
		<?php echo $title_for_layout; ?>
	</h1>
</div>

<p>
	<?php echo $this->Html->link(
		'<span class="glyphicon glyphicon-arrow-left glyphicon-white"></span> Manage Users',
		array(
			'action' => 'manage'
		),
		array(
			'escape' => false,
			'class' => 'btn btn-primary'
		)
	); ?>
</p>

<div class="users form">
	<?php echo $this->Form->create('User');?>
	<fieldset>
		<?php
			echo $this->Form->input('id');
			echo $this->Form->input(
				'name',
				array(
					'class' => 'form-control',
					'div' => array('class' => 'form-group')
				)
			);
			echo $this->Form->input(
				'email',
				array(
					'class' => 'form-control',
					'div' => array('class' => 'form-group')
				)
			);
			echo $this->Form->input(
				'phone',
				array(
					'class' => 'form-control',
					'div' => array('class' => 'form-group')
				)
			);
			echo $this->Form->input(
				'Role',
				array(
					'between' => '<p class="footnote">Note: If a user\'s role is changed, they may need to log out and back in before the change takes effect.</p>',
					'class' => 'form-control',
					'div' => array(
						'class' => 'form-group roles_checkboxes'
					),
					'label' => 'Role(s)',
					'options' => $roles,
					'multiple' => 'checkbox'
				)
			);
			echo $this->Tinymce->input('Bio.bio',
				array(
					'label' => 'Instructor Bio',
					'div' => array(
						'id' => 'bio_fields_container',
						'class' => 'form-group'
					),
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
	</fieldset>
	<?php echo $this->Form->end(array(
		'label' => 'Update',
		'class' => 'btn btn-default'
	)); ?>
</div>
<?php $this->Js->buffer("
	adminUserEditForm.init();
"); ?>