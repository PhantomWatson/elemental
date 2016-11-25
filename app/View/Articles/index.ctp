<div class="page-header">
	<h1>
		<?php echo $title_for_layout; ?>
	</h1>
</div>

<div class="articles_index">
	<table class="table table-striped">
		<?php foreach ($articles as $article): ?>
			<tr>
				<td class="date">
					<?php
						$timestamp = strtotime($article['Article']['created']);
						echo date('M j', $timestamp);
						echo '<sup>'.date('S', $timestamp).'</sup>';
						echo date(', Y', $timestamp);
					?>
				</td>
				<td>
					<?php echo $this->Html->link(
						$article['Article']['title'],
						array(
							'controller' => 'articles',
							'action' => 'view',
							'id' => $article['Article']['id']
						)
					); ?>
				</td>
			</tr>
		<?php endforeach; ?>
	</table>

	<?php echo $this->element('pagination'); ?>
</div>
