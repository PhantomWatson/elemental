<?php
	echo h($student['User']['name']).',';
	echo "\n\n";

	echo 'Space has become available in the upcoming Elemental sexual assault protection course that you registered for, so you have been automatically enrolled. ';
	echo "More information about this course is available at $course_view_url. ";
	if ($registration['CourseRegistration']['waiting_list']) {
		echo 'If space becomes available in this course before it begins, you may be contacted by an instructor and invited to join. ';
	}
	$instructor_name = $instructor['User']['name'];
	$instructor_email = $instructor['User']['email'];
	echo "If you have any questions about this course, you can email $instructor_name at $instructor_email.";
	echo "\n\n";

	echo __n('Date', 'Dates', count($course['CourseDate'])).':';
	foreach ($course['CourseDate'] as $course_date) {
		echo "\n";
		echo date('l, F j, Y', strtotime($course_date['date']));
		echo ' at ';
		echo date('g:ia', strtotime($course_date['start_time']));

	}
	echo "\n\n";

	echo 'Location:';
	echo "\n";
	echo h($course['Course']['location']);
	echo "\n";
	echo nl2br(h($course['Course']['address']));
	echo "\n";
	echo h($course['Course']['city']); ?>, <?php echo $course['Course']['state'];
	echo "\n\n";

	echo 'If you will not be able to attend this course, please let us know as soon as possible by canceling your registration. ';
	echo "If you are logged in to the Elemental website, you can do this by clicking the 'Cancel Registration' button for this course's listing. ";
	echo "You can also visit this secure link to be automatically unregistered: $unreg_url. ";
	echo "\n\n";

	$instructor_name = $instructor['User']['name'];
	if ($course['Course']['message']) {
		echo "A message from this course's instructor, $instructor_name:";
		echo "\n";
		echo $course['Course']['message'];
		echo "\n\n";
	}

	if ($password) {
		$email = $student['User']['email'];
		echo "You can log in to the Elemental website with the email address $email and password \"$password\" (without quotes).";
		echo "\n\n";
	}