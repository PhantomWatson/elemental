<?php
	if (is_array($retval)) {
		echo $this->Js->object($retval);
	} else {
		echo $retval;
	}