<div class="page-header">
	<h1>
		<?php echo $title_for_layout; ?>
	</h1>
</div>

<p>
	<?php echo $this->Html->link(
		'<span class="glyphicon glyphicon-plus glyphicon-white"></span> Post New Article',
		array(
			'action' => 'add'
		),
		array(
			'escape' => false,
			'class' => 'btn btn-success'
		)
	); ?>
</p>

<?php if (empty($articles)): ?>
	<div class="alert alert-info">
		No articles have been posted yet.
	</div>
<?php else: ?>
	<div class="articles index manage_articles">
		<table cellpadding="0" cellspacing="0" class="table">
			<tr>
				<th><?php echo $this->Paginator->sort('title');?></th>
				<th><?php echo $this->Paginator->sort('created', 'Posted');?></th>
				<th class="actions"><?php echo __('Actions');?></th>
			</tr>
			<?php foreach ($articles as $article): ?>
				<tr>
					<td>
						<?php echo $this->Html->link(
							$article['Article']['title'],
							array(
								'action' => 'view',
								'id' => $article['Article']['id']
							)
						); ?>
					</td>
					<td class="posted">
						<?php echo date('M j, Y g:ia', strtotime($article['Article']['created'])); ?>
					</td>
					<td class="actions">
						<?php echo $this->Html->link(
							'Edit',
							array(
								'action' => 'edit',
								'id' => $article['Article']['id']
							),
							array('class' => 'btn btn-info')
						); ?>
						<?php echo $this->Form->postLink(
							'Delete',
							array(
								'action' => 'delete',
								'id' => $article['Article']['id']
							),
							array('class' => 'btn btn-danger'),
							'Are you sure you want to delete this article?'
						); ?>
					</td>
				</tr>
			<?php endforeach; ?>
		</table>

		<?php echo $this->element('pagination'); ?>
	</div>
<?php endif; ?>