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

<p class="alert alert-info">
	Instructors who have been certified will remain certified until one year after the most recent class that they've reported attendance for.
</p>

<?php if (empty($certifications)): ?>
	<p class="alert alert-info">
		No certifications found
	</p>
<?php else: ?>
	<table class="table" id="certifications">
		<thead>
			<tr>
				<th>
					Instructor
				</th>
				<th>
					<?php echo $this->Paginator->sort('date_granted', 'Certification Granted'); ?>
				</th>
				<th>
					<?php echo $this->Paginator->sort('date_expires', 'Certification Expires'); ?>
				</th>
				<th>
				    Classroom Module
			    </th>
			</tr>
		</thead>
		<tbody>
			<?php
				$current_date = date('Y-m-d');
				foreach ($certifications as $cert):
			?>
				<?php $date_expires = $cert['Certification']['date_expires']; ?>
				<?php echo ($date_expires < $current_date) ? '<tr class="expired">' : '<tr>'; ?>
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
					<td class="classroom_module_status">
					    <?php
                            if (empty($cert['User']['Purchase'])) {
                                echo '<span class="text-danger">Not purchased</span>';
                            } else {
                                $purchase_date = strtotime($cert['User']['Purchase'][0]['created']);
                                $expiration_date = strtotime($cert['User']['Purchase'][0]['created'].' + 1 year + 2 days');
                                if ($expiration_date > time()) {
                                    echo '<span class="text-success">Purchased <span class="details">(expires '.date('F j, Y', $expiration_date).')</span></span>';
                                } else {
                                    echo '<span class="text-danger">Expired '.date('F j, Y', $expiration_date).'</span>';
                                }
                            }
                        ?>
				    </td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>

	<?php echo $this->element('pagination'); ?>
<?php endif; ?>