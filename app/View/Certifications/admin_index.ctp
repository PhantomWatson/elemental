<div class="page-header">
	<h1>
		<?php echo $title_for_layout; ?>
	</h1>
</div>

<p>
	<?php echo $this->Html->link(
		'<span class="glyphicon glyphicon-plus glyphicon-white"></span> Add a New Certification',
		array(
			'admin' => true,
			'controller' => 'certifications',
			'action' => 'add'
		),
		array(
			'escape' => false,
			'class' => 'btn btn-success'
		)
	); ?>
</p>

<?php if (empty($certifications)): ?>
	<p class="alert alert-info">
		No certifications found
	</p>
<?php else: ?>
	<table class="table">
		<thead>
			<tr>
				<th>
					Instructor
				</th>
				<th>
					Certification Granted
				</th>
				<th>
					Certification Expires
				</th>
			</tr>
		</thead>
		<tbody>
			<?php
				$current_date = date('Y-m-d');
				foreach ($certifications as $cert):
			?>
				<?php $date_expires = $cert['Certification']['date_expires']; ?>
				<?php echo ($date_expires < $current_date) ? '<tr class="expired>' : '<tr>'; ?>
					<td>
						<?php echo $cert['User']['name']; ?>
					</td>
					<td>
						<?php
							$date = $cert['Certification']['date_granted'];
							echo date('F j, Y', strtotime($date));
						?>
					</td>
					<td>
						<?php echo date('F j, Y', strtotime($date_expires)); ?>
					</td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
<?php endif; ?>