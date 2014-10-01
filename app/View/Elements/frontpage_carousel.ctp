<div id="frontpage_carousel" class="carousel slide" data-ride="carousel">
	<ol class="carousel-indicators">
		<?php for ($i = 1; $i <= 18; $i++): ?>
			<li data-target="#frontpage_carousel" data-slide-to="<?php echo $i - 1; ?>" <?php if ($i == 1) echo 'class="active"'; ?>></li>
		<?php endfor; ?>
	</ol>

	<div class="carousel-inner">
		<?php for ($i = 1; $i <= 18; $i++): ?>
			<div class="item <?php if ($i == 1) echo 'active'; ?>">
				<img src="/img/carousel/<?php echo $i; ?>.jpg" />
			</div>
		<?php endfor; ?>
	</div>

	<a class="left carousel-control" href="#frontpage_carousel" role="button" data-slide="prev">
		<span class="glyphicon glyphicon-chevron-left"></span>
	</a>
	<a class="right carousel-control" href="#frontpage_carousel" role="button" data-slide="next">
		<span class="glyphicon glyphicon-chevron-right"></span>
	</a>
</div>