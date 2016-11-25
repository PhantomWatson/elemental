<div class="input required recaptcha <?php if (isset($recaptcha_error)) echo 'error'; ?>">
	<?php if (! isset($label)) $label = 'Spam Protection'; ?>
	<?php if ($label !== false): ?>
		<label for="recaptcha_response_field">
			<?php echo $label; ?>
		</label>
	<?php endif; ?>
	<?php echo $this->Recaptcha->display(); ?>
    <?php if (isset($recaptcha_error)): ?>
        <p class="error-message">
            There was an error completing that CAPTCHA challenge. Please try again.
        </p>
    <?php endif; ?>
</div>