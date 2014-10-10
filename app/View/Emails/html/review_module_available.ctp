<p>
	<?php echo $student_name; ?>,
</p>

<p>
	Thank you for attending a recent Elemental course. You now have one year of free access to the Elemental Student Review Module.
</p>

<p>
	This is an online multimedia summary of the physical and verbal techniques taught during the course you attended. It includes a video review of all of the techniques covered, summaries of the main points to remember for each defense, and a suggested review schedule.
</p>

<p>
	<strong>
		To access the Student Review Module, visit
		<a href="<?php echo $review_module_link; ?>"><?php
			echo $review_module_link;
		?></a>.
	</strong>
</p>

<p>
	If you have any questions about the material covered in your course, your instructor can be reached at
	<a href="mailto:<?php echo $instructor_email; ?>"><?php
		echo $instructor_email;
	?></a>.
	If you have any trouble using the website, please let us know through the contact form at
	<a href="<?php echo $contact_url; ?>">
		<?php echo $contact_url; ?>
	</a>
	or by emailing
	<a href="mailto:<?php echo Configure::read('admin_email'); ?>"><?php
		echo Configure::read('admin_email');
	?></a>.
</p>