<div class="page-header">
	<h1>
		<?php echo $title_for_layout; ?>
	</h1>
</div>

<p>
	Interested in becoming certified and teaching Elemental courses?
	<?php echo $this->Html->link(
		'Learn more about becoming a Certified Elemental Instructor',
		array(
			'instructor' => true,
			'controller' => 'products',
			'action' => 'certification'
		),
		array(
			'class' => '',
			'escape' => false
		)
	); ?>.
</p>

<div id="bios">
	<?php if (empty($bios)): ?>

		<p>
			Please check back later for information about Elemental's instructors.
		</p>

	<?php else: ?>

		<?php foreach ($bios as $bio): ?>
			<section>
				<div class="panel panel-default">
					<div class="panel-heading">
						<h2 class="panel-title">
							<?php echo $bio['User']['name']; ?>
						</h2>

						<?php if ($bio['User']['email']): ?>
							<a href="mailto:<?php echo $bio['User']['email']; ?>">
								<?php echo $bio['User']['email']; ?>
							</a>
						<?php endif; ?>
					</div>
					<div class="panel-body">
						<?php if (! empty($bio['Image'])): ?>
							<img src="/img/bios/<?php echo $bio['Image']['filename']; ?>" class="bio_img" />
						<?php endif; ?>
						<?php echo $bio['Bio']['bio']; ?>
					</div>
				</div>
			</section>
		<?php endforeach; ?>

	<?php endif; ?>
</div>