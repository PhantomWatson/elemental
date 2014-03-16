<div class="jumbotron">
	<h1>
		<?php echo $title_for_layout; ?>
	</h1>
	<p>
		
	</p>
	<?php if ($logged_in): ?>
		<?php if ($can_access): ?>
			<?php
				$lessons = array(
					1 => 1, 
					2 => 2, 
					3 => 3, 
					4 => 4, 
					'5a' => 5, 
					'5b' => '5b', 
					6 => 6, 
					'7a' => 7, 
					'7b' => '7b', 
					8 => 8, 
					9 => 9
				);
			?>
			<ul>
				<?php foreach ($lessons as $label => $path): ?>
					<li>
						<a href="/vizi/instructor_training/Lesson <?php echo $path; ?>/Current Lesson/index.html" target="instructor_training_iframe">
							Lesson <?php echo $label; ?>
						</a>
					</li>
				<?php endforeach; ?>
			</ul>
			<iframe src="" name="instructor_training_iframe" height="499" width="902"></iframe>
		<?php endif; ?>
	<?php else: ?>
		<p class="alert alert-info">
			For information about becoming certified as an Elemental instructor, 
			<?php echo $this->Html->link(
				'contact us',
				array(
					'controller' => 'pages',
					'action' => 'contact'
				)
			); ?>.
		</p>
		<p>
			<?php echo $this->Html->link(
				'Log in',
				array(
					'controller' => 'users',
					'action' => 'login'
				),
				array(
					'class' => 'btn btn-primary btn-large'
				)
			); ?>
		</p>
	<?php endif; ?>
</div>

<?php if ($logged_in && ! $can_access): ?>
	<p class="alert alert-info">
		You are not currently authorized to access the instructor training module. 
		For assistance or for more information about becoming certified as an Elemental instructor, 
		<?php echo $this->Html->link(
			'contact us',
			array(
				'controller' => 'pages',
				'action' => 'contact'
			)
		); ?>.
	</p>
<?php endif; ?>