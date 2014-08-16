<?php
	if ($this->Paginator->hasPrev() || $this->Paginator->hasNext()) {
		echo '<ul class="pagination">';
	}
	if ($this->Paginator->hasPrev()) {
		echo $this->Paginator->prev(
			'< prev',
			array(
				'tag' => 'li'
			),
			null,
			array(
				'class' => 'prev disabled'
			)
		);
	}
	echo $this->Paginator->numbers(array(
		'currentClass' => 'active',
		'currentTag' => 'a',
		'separator' => '',
		'tag' => 'li'
	));
	if ($this->Paginator->hasNext()) {
		echo $this->Paginator->next(
			'next >',
			array(
				'tag' => 'li'
			),
			null,
			array(
				'class' => 'next disabled'
			)
		);
	}
	if ($this->Paginator->hasPrev() || $this->Paginator->hasNext()) {
		echo '</ul>';
	}