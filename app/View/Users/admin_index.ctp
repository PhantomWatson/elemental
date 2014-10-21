<div class="page-header">
	<h1>
		<?php echo $title_for_layout; ?>
	</h1>
</div>

<p>
	<?php echo $this->Html->link(
		'Add a New User',
		array(
			'admin' => true,
			'controller' => 'users',
			'action' => 'add'
		),
		array(
			'class' => 'btn btn-success'
		)
	); ?>
</p>

<?php
	$selected_role = isset($this->request->named['role']) ? $this->request->named['role'] : null;
	echo $this->Html->link(
		'All Users',
		array(
			'admin' => true,
			'controller' => 'users',
			'action' => 'index'
		),
		array(
			'class' => 'btn btn-'.(! $selected_role ? 'primary' : 'default')
		)
	);
	$buttons = array(
		'Students' => 'student',
		'Instructors' => 'instructor',
		'Trainees' => 'trainee',
		'Administrators' => 'admin'
	);
	foreach ($buttons as $label => $role) {
		echo $this->Html->link(
			$label,
			array(
				'admin' => true,
				'controller' => 'users',
				'action' => 'index',
				'role' => $role
			),
			array(
				'class' => 'btn btn-'.($selected_role == $role ? 'primary' : 'default')
			)
		);
	}
?>

<div id="user_search_form">
	<a href="#" class="btn btn-default">
		<span class="glyphicon glyphicon-search"></span>
		Search for User
	</a>
	<form class="form-inline" role="form" style="display: none;">
		<input type="text" name="search" class="form-control" placeholder="Enter user's name">
		<button type="submit" class="btn btn-default">Search</button>
	</form>
</div>

<?php if (empty($users)): ?>
	<div class="alert alert-info">
		No users were found.
	</div>
<?php else: ?>
	<div class="index manage_users">
		<table cellpadding="0" cellspacing="0" class="table">
			<tr>
				<th>
					<?php echo $this->Paginator->sort('name');?>
				</th>
				<th>
					Role
				</th>
				<th>
					<?php echo $this->Paginator->sort('created', 'Account Created');?>
				</th>
				<th class="actions">
					<?php echo __('Actions');?>
				</th>
			</tr>
			<?php foreach ($users as $user): ?>
				<tr>
					<td>
						<?php echo h($user['User']['name']); ?>
						<p class="contact_info">
							<a href="mailto:<?php echo $user['User']['email']; ?>">
								<?php echo $user['User']['email']; ?>
							</a>
							<?php if (! empty($user['User']['phone'])): ?>
								<br />
								<?php echo $user['User']['phone']; ?>
							<?php endif; ?>
						</p>
					</td>
					<td>
						<?php
							$roles = array();
							foreach ($user['Role'] as $role) {
								$roles[] = ucwords($role['name']);
							}
							echo implode('<br />', $roles);
						?>
					</td>
					<td>
						<?php echo date('M j, Y g:ia', strtotime($user['User']['created'])); ?>
					</td>
					<td class="actions">
						<?php echo $this->Html->link(
							'Edit',
							array(
								'admin' => true,
								'action' => 'edit',
								'id' => $user['User']['id']
							),
							array('class' => 'btn btn-info')
						); ?>
						<?php echo $this->Form->postLink(
							'Remove',
							array(
								'admin' => true,
								'action' => 'delete',
								'id' => $user['User']['id']
							),
							array('class' => 'btn btn-danger'),
							'Are you sure you want to remove this user\'s account?'
						); ?>
					</td>
				</tr>
			<?php endforeach; ?>
		</table>

		<?php echo $this->element('pagination'); ?>
	</div>
<?php endif; ?>

<?php $this->Js->buffer("adminUserIndex.init();"); ?>