<table class="table">
	<thead>
		<tr>
			<th>Name</th>
			<th>Email</th>
			<th>Phone</th>
			<th>Actions</th>
		</tr>
	</thead>
	<tbody>
		<?php $email_addresses = array(); ?>
		<?php foreach ($list as $student): ?>
			<tr>
				<td>
					<?php if (empty($student['User']['name'])): ?>
						<span class="label label-danger">
							Name not found
						</span>
					<?php else: ?>
						<?php echo h($student['User']['name']); ?>
					<?php endif; ?>
				</td>
				<td>
					<?php if (empty($student['User']['email'])): ?>
						<span class="label label-danger">
							Email not found
						</span>
					<?php else: ?>
						<?php echo $this->Text->autoLinkEmails($student['User']['email']); ?>
						<?php $email_addresses[] = $student['User']['email']; ?>
					<?php endif; ?>
				</td>
				<td>
					<?php echo h($student['User']['phone']); ?>
				</td>
				<td>
					<?php echo $this->Form->postLink(
						'Remove',
						array(
							'controller' => 'course_registrations',
							'action' => 'delete',
							'id' => $student['CourseRegistration']['id']
						),
						array(
							'escape' => false,
							'class' => 'btn btn-danger'
						),
						'Are you sure you want to remove this student\'s registration? They will NOT be notified.'
					); ?>
				</td>
			</tr>
		<?php endforeach; ?>
	</tbody>
	<tfoot>
		<tr>
			<td>&nbsp;</td>
			<td colspan="4">
				<strong>
					<a href="mailto:<?php echo implode('; ', $email_addresses); ?>">
						Email all students
					</a>
				</strong>
			</td>
			<td>&nbsp;</td>
		</tr>
	</tfoot>
</table>