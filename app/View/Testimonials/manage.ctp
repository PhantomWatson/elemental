<div class="page-header">
	<h1>
		<?php echo $title_for_layout; ?>
	</h1>
</div>

<p>
	<?php echo $this->Html->link(
		'<span class="glyphicon glyphicon-plus glyphicon-white"></span> Post New Testimonial', 
		array(
			'action' => 'add'
		),
		array(
			'escape' => false, 
			'class' => 'btn btn-success'
		)
	); ?>
</p>

<div class="alert alert-info">
	Unapproved testimonials appear at the top. Click on 'approve' to publish them to the website.
</div>

<?php if (empty($testimonials)): ?>
	<div class="alert alert-info">
		No testimonials have been posted yet.
	</div>
<?php else: ?>
	<div class="manage_testimonials index">
		<table cellpadding="0" cellspacing="0" class="table">
			<tr>
				<th><?php echo $this->Paginator->sort('user_id', 'Posted by');?></th>
				<th><?php echo $this->Paginator->sort('author', 'Student');?></th>
				<th><?php echo $this->Paginator->sort('body', 'Testimonial');?></th>
				<th><?php echo $this->Paginator->sort('created', 'Posted');?></th>
				<th class="actions"><?php echo __('Actions');?></th>
			</tr>
			<?php foreach ($testimonials as $testimonial): ?>
				<tr>
					<td>
						<?php echo h($testimonial['User']['name']); ?>
					</td>
					<td>
						<?php if (empty($testimonial['Testimonial']['author'])): ?>
							<span class="author_anonymous">
								Anonymous
							</span>
						<?php else: ?> 
							<?php echo h($testimonial['Testimonial']['author']); ?>
						<?php endif; ?>
					</td>
					<td class="testimonial_body">
						<?php echo nl2br(h($testimonial['Testimonial']['body'])); ?>
					</td>
					<td>
						<?php echo date('M j, Y g:ia', strtotime($testimonial['Testimonial']['created'])); ?>
					</td>
					<td class="actions">
						<?php if (! $testimonial['Testimonial']['approved']): ?>
							<?php echo $this->Form->postLink(
								'Approve', 
								array(
									'action' => 'approve', 
									'id' => $testimonial['Testimonial']['id']
								),
								array(
									'class' => 'btn btn-success',
									'escape' => false
								)
							); ?>
							<br />
						<?php endif; ?>
						<?php echo $this->Html->link(
							'Edit', 
							array(
								'action' => 'edit', 
								'id' => $testimonial['Testimonial']['id']
							),
							array('class' => 'btn btn-info')
						); ?>
						<br />
						<?php echo $this->Form->postLink(
							'Delete', 
							array(
								'action' => 'delete', 
								'id' => $testimonial['Testimonial']['id']
							), 
							array('class' => 'btn btn-danger'), 
							'Are you sure you want to delete this testimonial?'
						); ?>
					</td>
				</tr>
			<?php endforeach; ?>
		</table>
	
		<?php echo $this->element('pagination'); ?>
	</div>
<?php endif; ?>