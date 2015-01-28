<div class="modal fade" id="alert_modal" tabindex="-1" role="dialog" aria-labelledby="alerts_modal_label" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title" id="alerts_modal_label">Alert</h4>
			</div>
			<div class="modal-body">
				<ul>
					<?php foreach ($alerts as $key => $alert): ?>
						<?php if ($key == 'last_checked') continue; ?>
						<li>
							<?php echo $alert; ?>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
			</div>
		</div>
	</div>
</div>