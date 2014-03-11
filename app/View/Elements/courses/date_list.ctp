<?php 
	$dates = array();
	foreach ($course['CourseDate'] as $course_date) {
		$dates[] = date('F j, Y', strtotime($course_date['date']));
	}
	if (count($dates) > 1) {
		$dates[count($dates) - 1] = 'and '.$dates[count($dates) - 1];	
	}
	if (count($dates) > 2) {
		echo implode('; ', $dates);
	} else {
		echo implode(' ', $dates);	
	}
?>