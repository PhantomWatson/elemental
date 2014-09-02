<div class="page-header">
	<h1>
		<?php echo $title_for_layout; ?>
	</h1>
</div>

<div class="bio row">
	<?php if (empty($bio['Image'])): ?>
		<?php echo $bio['Bio']['bio']; ?>
	<?php else: ?>
		<div class="col-sm-4">
			<img src="/img/bios/<?php echo $bio['Image']['filename']; ?>" />
		</div>
		<div class="col-sm-8">
			<?php echo $bio['Bio']['bio']; ?>
		</div>
	<?php endif; ?>
</div>