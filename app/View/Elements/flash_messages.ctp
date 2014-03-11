<?php 
	/* This creates the hidden #flash_messages container and fills it with 
	 * flash messages and displayed via a javascript animation if there are
	 * messages to display. Regardless, the container is put onto the page
	 * so that asyncronous activity can load messages into it as needed. */
	if (! empty($flash_messages)) {
		$this->Js->buffer("flashMessages.show();");
	}
?>
<div id="flash_messages">
	<ul>
		<?php if (! empty($flash_messages)): ?>
			<?php foreach ($flash_messages as $msg): ?>
				<?php
					switch ($msg['class']) {
						case 'success':
							$class = 'alert-success';
							break;
						case 'error':
							$class = 'alert-danger';
							break;
						default:
							$class = 'alert-info';
					}
				?>
				<li class="alert <?php echo $class; ?> fade">
					<button type="button" class="close" data-dismiss="alert">
						&times;
					</button>
					<?php echo $msg['message']; ?>
				</li>
			<?php endforeach; ?>
		<?php endif; ?>
	</ul>
</div>