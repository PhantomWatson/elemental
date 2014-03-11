<div class="pagination">
	<ul>
		<?php
			if ($this->Paginator->hasPrev()) {
				echo $this->Paginator->prev(
					'< prev', 
					array('tag' => 'li'), 
					null, 
					array('class' => 'prev disabled')
				);
			}
			echo $this->Paginator->numbers(array(
				'separator' => '',
				'tag' => 'li'
			));
			if ($this->Paginator->hasNext()) {
				echo $this->Paginator->next(
					'next >', 
					array('tag' => 'li'), 
					null, 
					array('class' => 'next disabled')
				);
			}
		?>
	</ul>
</div>