<p>
	<?php echo h($user['User']['name']); ?>,
</p>

<p>
	Someone (presumably you) has requested that your password for
	<a href="http://elementalprotection.org">ElementalProtection.org</a>
	be reset so you can log in again. Please visit the following URL, where you will
	be prompted to enter in a new password to overwrite your old one.
</p>

<p>
	<a href="<?php echo $reset_url; ?>">
		<?php echo $reset_url; ?>
	</a>
</p>