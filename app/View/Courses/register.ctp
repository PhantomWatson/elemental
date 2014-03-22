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
				Complete registration
			</td>
			<td>
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
			</td>
		</tr>
	</tbody>
</table>
