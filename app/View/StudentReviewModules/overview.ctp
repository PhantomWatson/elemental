<div class="page-header">
	<h1>
		<?php echo $title_for_layout; ?>
	</h1>
</div>

<?php if ($mode == 'instructors'): ?>
	<?php if (empty($instructors)): ?>
		<p class="alert alert-info">
			Sorry, no instructors were found.
		</p>
	<?php else: ?>
		<table class="table instructors" id="srm_overview">
			<thead>
				<tr>
					<th>
						Instructor
					</th>
					<th>
						Total
					</th>
					<th>
						Paid for
						<br />
						online
					</th>
					<th>
						Granted
						<br />
						by admin
					</th>
					<th>
						Awaiting
						<br />
						payment
					</th>
					<th>
						Assigned
						<br />
						to students
					</th>
					<th>
						Available
						<br />
						to use
					</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($instructors as $instructor): ?>
					<tr>
						<td>
							<?php echo $instructor['name']; ?>
							<br />
							<a href="mailto:<?php echo $instructor['email']; ?>">
								<?php echo $instructor['email']; ?>
							</a>
						</td>
						<td>
							<?php echo $instructor['srm_totals']['total']; ?>
						</td>
						<td>
							<?php echo $instructor['srm_totals']['paid']; ?>
						</td>
						<td>
							<?php echo $instructor['srm_totals']['granted']; ?>
						</td>
						<td>
							<?php echo $instructor['srm_totals']['awaiting_payment']; ?>
						</td>
						<td>
							<?php echo $instructor['srm_totals']['assigned']; ?>
						</td>
						<td>
							<?php echo $instructor['srm_totals']['available']; ?>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	<?php endif; ?>
<?php endif; ?>