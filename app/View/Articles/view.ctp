<div class="page-header">
	<h1>
		<?php echo $title_for_layout; ?>
	</h1>
</div>

<p>
	<?php echo $this->Html->link(
		'&laquo; Browse all articles', 
		array(
			'controller' => 'articles',
			'action' => 'index'
		),
		array(
			'class' => '',
			'escape' => false
		)
	); ?>
</p>

<p class="article_posted_time">
	Posted on 
	<?php
		$timestamp = strtotime($article['Article']['created']);
		echo date('M j', $timestamp);
		echo '<sup>'.date('S', $timestamp).'</sup>';
		echo date(', Y', $timestamp);
	?>
</p>

<div class="articles view">
	<?php echo $article['Article']['body']; ?>
</div>