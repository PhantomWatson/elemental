<div class="page-header">
	<h1>
		<?php echo $title_for_layout; ?>
	</h1>
</div>

<div id="bio_form">
	<?php
		echo $this->Form->create('Bio');
		echo $this->element('bios/form');
		echo $this->Form->end(array(
			'label' => 'Update',
			'class' => 'btn btn-default'
		));
	?>
</div>