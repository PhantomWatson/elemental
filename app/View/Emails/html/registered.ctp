<p>
	<?php echo h($student['User']['name']); ?>,
</p>

<p>
	<?php $action = $registration['CourseRegistration']['waiting_list'] ? 'added to the waiting list' : 'registered'; ?>
	You have been <?php echo $action; ?> for <a href="<?php echo $course_view_url; ?>">an upcoming Elemental sexual assault protection course</a>.

	<?php if ($registration['CourseRegistration']['waiting_list']): ?>
		If space becomes available in this course before it begins, you will receive an email telling you that you have been automatically enrolled.
	<?php endif; ?>

	If you have any questions about this course, you can email <?php echo $instructor['User']['name']; ?> at <a href="mailto:<?php echo $instructor['User']['email']; ?>"><?php echo $instructor['User']['email']; ?></a>.
</p>

<p>
	<strong>
		<?php echo __n('Date', 'Dates', count($course['CourseDate'])); ?>:
	</strong>
</p>
<ul>
	<?php foreach ($course['CourseDate'] as $course_date): ?>
		<li>
			<?php echo date('l, F j, Y', strtotime($course_date['date'])); ?>
			at
			<?php echo date('g:ia', strtotime($course_date['start_time'])); ?>
		</li>
	<?php endforeach; ?>
</ul>

<p>
	<strong>
		Location:
	</strong>
	<br >
	<?php echo h($course['Course']['location']); ?>
	<br />
	<?php echo nl2br(h($course['Course']['address'])); ?>
	<br />
	<?php echo h($course['Course']['city']); ?>, <?php echo $course['Course']['state']; ?>
</p>

<p>
	If you will not be able to attend this course, please let us know as soon as possible by canceling your registration.
	If you are logged in to the Elemental website, you can do this by clicking the 'Cancel Registration' button for <a href="<?php echo $course_view_url; ?>">this course's listing</a>.
	You can also visit this secure link to be automatically unregistered: <?php echo $this->Html->link($unreg_url, $unreg_url); ?>.
	If you cancel your registration, you will still be able to re-register up until <?php echo date('F j, Y', strtotime($course['Course']['deadline'])); ?>.
</p>

<?php if ($course['Course']['message']): ?>
	<p>
		A message from this course's instructor, <?php echo $course['User']['name']; ?>:
	</p>
	<blockquote>
		<?php echo $course['Course']['message']; ?>
	</blockquote>
<?php endif; ?>

<?php if ($password): ?>
	<p>
		You can
		<a href="<?php echo $login_url; ?>">log in to the Elemental website</a>
		with the email address
		<strong>
			<?php echo $student['User']['email']; ?>
		</strong>
		and password
		<strong>
			"<?php echo $password; ?>"
		</strong>
		(without quotes).
	</p>
<?php endif; ?>

