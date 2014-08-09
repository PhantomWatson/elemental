<p>
	For instructors to be able to create courses with no student registration fees, they must first have Elemental Student Review Modules for every student.
</p>

<ul>
	<li>
		<strong>Cost:</strong>
		$<?php echo $cost; ?> each
	</li>
	<li>
		<strong>Ownership:</strong>
		Elemental Student Review Modules are each owned by a specific instructor who can use them to create free courses. If you need to transfer ownership to a different instructor, please
		<?php echo $this->Html->link(
			'contact us',
			array(
				'controller' => 'pages',
				'action' => 'contact'
			)
		); ?>.
	</li>
	<li>
		<strong>Access:</strong>
		Just like with paid courses, each student will be granted one year of access to the Student Review Module upon completing an Elemental course and having their attendance reported by an instructor.
	</li>
	<li>
		<strong>Recycling:</strong>
		If any Student Review Modules are designated for a course and then not assigned to any students (because a class didn't reach full capacity or students failed to attend), they will then be available to use in other free courses once attendance is reported.
	</li>
</ul>