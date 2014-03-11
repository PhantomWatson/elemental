<div class="page-header">
	<h1>
		<?php echo $title_for_layout; ?>
	</h1>
</div>

<?php if (empty($testimonials)): ?>
	<p class="alert alert-info">
		No testimonials were found. Please check back again later.
	</p>
<?php else: ?>
	<?php echo $this->element('pagination'); ?>
	
	<ul class="list-unstyled testimonial_index">
		<?php foreach ($testimonials as $testimonial): ?>
			<li>
				<blockquote>
					<?php echo nl2br(h($testimonial['Testimonial']['body'])); ?>
				</blockquote>
				<p class="author">
					<?php if (empty($testimonial['Testimonial']['author'])): ?>
						<span class="author_anonymous">
							Anonymous
						</span>
					<?php else: ?> 
						<?php echo h($testimonial['Testimonial']['author']); ?>
					<?php endif; ?>
				</p>
			</li>
		<?php endforeach; ?>
	</ul>
	
	<?php echo $this->element('pagination'); ?>
<?php endif; ?>