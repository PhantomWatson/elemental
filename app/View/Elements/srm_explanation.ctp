<p>
	When instructors create courses with no student registration fees, they must cover the cost of Elemental Student Review Modules for every attending student.
</p>

<ul>
	<li>
		<strong>Cost:</strong>
		$<?php echo $cost; ?> each
	</li>
	<li>
		<strong>Access:</strong>
		Just like with paid courses, each student will be granted one year of access to their Student Review Module upon completing an Elemental
		course and having their attendance reported by an instructor. Afterward, they can choose to purchase another year of access.
	</li>
	<li>
		<strong>Pre-paying:</strong>
		Elemental Student Review Modules can be purchased in advance. When attendance is reported for a free course, modules owned by the course's
		instructor are automatically assigned to students.
	</li>
	<li>
		<strong>Post-paying:</strong>
		If attendance is reported for a free course and there are not enough pre-paid Student Review Modules to assign to all attending students,
		unpaid modules will be created as necessary, which will need to be paid for by the course's instructor.
	</li>
	<li>
		<strong>Transferring:</strong>
		If you need pre-paid Student Review Modules to be transferred from one instructor to another, please
		<?php echo $this->Html->link(
			'contact us',
			array(
				'controller' => 'pages',
				'action' => 'contact'
			)
		); ?>.
	</li>
</ul>