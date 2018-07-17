<script src="https://www.google.com/recaptcha/api.js" async defer></script>
<div class="input required recaptcha <?php if (isset($recaptcha_error)) echo 'error'; ?>">
	<?php if (! isset($label)) $label = 'Spam Protection'; ?>
	<?php if ($label !== false): ?>
		<label for="recaptcha_response_field">
			<?php echo $label; ?>
		</label>
	<?php endif; ?>
    <div class="g-recaptcha" data-sitekey="<?= Configure::read('Recaptcha.publicKey') ?>"></div>
    <?php if (isset($recaptcha_error)): ?>
        <p class="error-message">
            There was an error completing that CAPTCHA challenge. Please try again.
        </p>
    <?php endif; ?>
</div>
