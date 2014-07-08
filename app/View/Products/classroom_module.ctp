<h1>
	<?php echo $title_for_layout; ?>
</h1>

<?php if ($can_access): ?>

	<?php if ($warn): ?>
		<div class="alert alert-warning alert-dismissible" role="alert">
			<button type="button" class="close" data-dismiss="alert">
				<span aria-hidden="true">&times;</span>
				<span class="sr-only">Close</span>
			</button>
			Your access to the Classroom Module will expire on
			<strong><?php echo date('F j, Y', $expiration); ?></strong>.
		</div>
	<?php endif; ?>

<?php else: ?>

	<?php if ($expiration): ?>
		<div class="alert alert-danger" role="alert">
			Your access to the Classroom Module expired on
			<strong><?php echo date('F j, Y', $expiration); ?></strong>.
		</div>
	<?php endif; ?>

<?php endif; ?>