<?php
	if (! empty($flash_messages)) {
		foreach ($flash_messages as $msg) {
			$msg['message'] = str_replace('"', '\"', $msg['message']);
			$this->Js->buffer('flashMessages.insert("'.str_replace("\n", "\\n", $msg['message']).'", "'.$msg['class'].'");');
		}
	}
?>
<?php echo $this->fetch('content'); ?>
