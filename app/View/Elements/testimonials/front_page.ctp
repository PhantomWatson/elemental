<div id="random_testimonial">
	<?php echo $this->Html->link(
		'Read more &raquo;',
		array(
			'controller' => 'testimonials',
			'action' => 'index'
		),
		array(
			'class' => 'btn btn-default',
			'escape' => false
		)
	); ?>
	<h2>
		Testimonials
		<div id="testimonial-pager"></div>
	</h2>

	<?php if (empty($testimonials)): ?>
		<p>
			Stay tuned for success stories from some of Elemental's past participants.
		</p>
	<?php else: ?>
		<?php
			$this->Js->buffer("
				$('#random_testimonial .cycle-slideshow').on('cycle-paused', function() {
					$('#testimonial-pager').addClass('paused');
				});
				$('#random_testimonial .cycle-slideshow').on('cycle-resumed', function() {
					$('#testimonial-pager').removeClass('paused');
				});
			");
			$this->Html->script('vendor/jquery.cycle2.min.js', array('inline' => false));
		?>
		<div
			class="cycle-slideshow"
			data-cycle-timeout="5000"
			data-cycle-speed="1000"
			data-cycle-manual-speed="300"
			data-cycle-slides="div.testimonial"
			data-cycle-pager="#testimonial-pager"
			data-cycle-pause-on-hover="#random_testimonial"
		>
			<?php foreach ($testimonials as $testimonial): ?>
				<?php
					$body = nl2br(h($testimonial['Testimonial']['body']));
					$length = strlen(utf8_decode($body));
				?>
				<div class="testimonial">
					<blockquote>
						<?php echo $body; ?>
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
				</div>
			<?php endforeach; ?>
		</div>

	<?php endif; ?>
</div>