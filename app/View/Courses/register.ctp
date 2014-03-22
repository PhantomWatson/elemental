<div class="page-header">
	<h1>
		<?php echo $title_for_layout; ?>
	</h1>
</div>

<p>
	Before you can register for a course, you must complete the following steps:
</p>

<table cellpadding="0" cellspacing="0" class="table">
	<tbody>
		<tr>
			<td>
				<?php if ($release_submitted): ?>
					<span class="glyphicon glyphicon-ok"></span>
				<?php else: ?>
					<span class="glyphicon glyphicon-remove"></span>
				<?php endif; ?>
				Submit liability release
			</td>
			<td>
				<?php if ($release_submitted): ?>
					<?php echo $this->Html->link(
						'Edit',
						array(
							'controller' => 'releases',
							'action' => 'edit',
							'course_id' => $course['Course']['id']
						),
						array('class' => 'btn btn-info')
					); ?>
				<?php else: ?>
					<?php echo $this->Html->link(
						'Go',
						array(
							'controller' => 'releases',
							'action' => 'add',
							'course_id' => $course['Course']['id']
						),
						array('class' => 'btn btn-success')
					); ?>
				<?php endif; ?>
			</td>
		</tr>
		<tr>
			<td>
				<?php if ($registration_completed): ?>
					<span class="glyphicon glyphicon-ok"></span>
				<?php else: ?>
					<span class="glyphicon glyphicon-remove"></span>
				<?php endif; ?>
				Complete registration
			</td>
			<td>
				<?php if ($registration_completed): ?>
					<?php echo $this->Form->postLink(
						'Cancel Registration',
						array(
							'controller' => 'course_registrations',
							'action' => 'delete',
							'id' => $registration_id
						),
						array(
							'class' => 'btn btn-danger'
						),
						'Are you sure you want to cancel your registration to this course?'
					); ?>
				<?php else: ?>
					<?php if ($release_submitted): ?>
						<?php echo $this->Html->link(
							'Go',
							array(
								'controller' => 'courses',
								'action' => 'complete_registration',
								'course_id' => $course['Course']['id']
							),
							array('class' => 'btn btn-success')
						); ?>
					<?php else: ?>
						<button type="button" class="btn btn-default disabled">
							Actions pending
						</button>
					<?php endif; ?>
				<?php endif; ?>
			</td>
		</tr>
	</tbody>
</table>
