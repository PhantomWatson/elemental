<?php
	echo 'A refund needs to be issued for someone who paid an Elemental course registration fee and then later canceled.';
	echo "\n\n";
	echo 'Details:';
	foreach ($details as $label => $deet) {
		echo "\n$label: $deet";
	}
	echo "\n\n";
	echo 'Refunds can be applied through the Google Merchant Center at http://checkout.google.com/sell.';