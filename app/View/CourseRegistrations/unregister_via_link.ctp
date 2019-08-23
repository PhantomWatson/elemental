<?php
/**
 * @var string $message
 * @var string $msg_class
 * @var string $title_for_layout
 */
?>
<div class="page-header">
	<h1>
		<?php echo $title_for_layout; ?>
	</h1>
</div>

<p class="alert alert-<?php echo $msg_class; ?>">
	<?php echo $message; ?>
</p>
