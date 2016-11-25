<?php
	echo h($student['User']['name']).',';
	echo "\n\n";

	$action = $registration['CourseRegistration']['waiting_list']
		? 'added to the waiting list'
		: 'registered';
	$date = date('F j, Y', strtotime($course['Course']['begins']));
	echo "You have been $action for an upcoming Elemental sexual assault protection course. ";
	echo "More information about this course is available at $course_view_url. ";
	if ($registration['CourseRegistration']['waiting_list']) {
		echo 'If space becomes available in this course before it begins, you will receive an email telling you that you have been automatically enrolled. ';
	}
	$instructor_name = $instructor['User']['name'];
	$instructor_email = $instructor['User']['email'];
	echo "Please email instructor {$instructor['User']['name']} at {$instructor['User']['email']} if you have any questions.";
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

    if ($course['Course']['message']) {
        echo "A Message From the Instructor:";
        echo "\n";
        echo $course['Course']['message'];
        echo "\n\n";
    }

    echo 'Cancellation:';
    echo "\n";
	if ($registration['CourseRegistration']['waiting_list']) {
		$cancelling = 'removing yourself from the waiting list';
		$button_label = 'Remove Self from Waiting List';
	} else {
		$cancelling = 'canceling your registration';
		$button_label = 'Cancel Registration';
	}
	echo 'If you will not be able to attend this course, please let us know as soon as possible by '.$cancelling.'. ';
	if (! $registration['CourseRegistration']['waiting_list']) {
		echo 'If you cancel your registration, you will still be able to re-register up until '.date('F j, Y', strtotime($course['Course']['deadline'])).'.';
	}
    echo "\n";
    echo "$button_label: $unreg_url";
	echo "\n\n";

	if ($password) {
	    echo 'Logging In:';
        echo "\n";
		$email = $student['User']['email'];
		echo "You can log in to the Elemental website with the email address $email and password \"$password\" (without quotes) by visiting $login_url.";
		echo "\n\n";
	}