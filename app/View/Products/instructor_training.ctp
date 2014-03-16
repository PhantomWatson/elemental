<div class="jumbotron">
	<h1>
		<?php echo $title_for_layout; ?>
	</h1>
	<?php if ($logged_in): ?>
		<?php if ($can_access): ?>
			<p>
				To begin, select a lesson:
			</p>
			
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
			<select id="instructor_training_lesson_select">
				<option value=""></option>
				<?php foreach ($lessons as $label => $path): ?>
					<option value="<?php echo $path; ?>">
						Lesson <?php echo $label; ?>
					</option>
				<?php endforeach; ?>
			</select>
			<div id="instructor_training_iframe_wrapper" style="display: none;">
				<iframe src="" id="instructor_training_iframe" height="520" width="920"></iframe>
			</div>
			<?php $this->Js->buffer("
				$('#instructor_training_lesson_select').val('');
				$('#instructor_training_lesson_select').change(function () {
					var select = $(this);
					select.children().first().hide();
					var path = select.val();
					var iframe = $('#instructor_training_iframe');
					var iframe_wrapper = $('#instructor_training_iframe_wrapper');
					iframe.attr('src', '/vizi/instructor_training/Lesson '+path+'/Current Lesson/index.html');
					if (! iframe_wrapper.is(':visible')) {
						iframe_wrapper.slideDown(1000);
					}
				});
			"); ?>
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