<?php
	$this->Js->buffer("courseAddForm.setup({
		available_psrm: $available_psrm,
		action: '".$this->request->action."'
	});");
	function dateTimeInput($view, $key = 0, $course_date = null, $is_dummy_input = false) {
		$remove_button = '<a href="#" class="remove_date btn btn-danger">';
		$remove_button .= '<span class="glyphicon glyphicon-remove glyphicon-white"></span> Remove';
		$remove_button .= '</a>';
		$time_input = $view->Form->input("CourseDate.$key.start_time", array(
			'label' => false,
			'type' => 'time',
			'timeFormat' => 12,
			'interval' => 5,
			'disabled' => $is_dummy_input,
			'div' => false,
			'class' => 'form-control',
			'default' => '18:00'
		));
		$time_input = '<span class="time_group">'.$time_input.'</span>';

		$max_year = date('Y') + 2;
		$min_year = date('Y');
		if (isset($course_date['date'])) {
			$this_date = $course_date['date'];
			if (is_array($this_date)) {
				$this_year = $course_date['date']['year'];
			} else {
				$this_year = reset(explode('-', $this_date));
			}

			// Allow min and max years to shift to accommodate this course's year
			$max_year = max($max_year, $this_year);
			$min_year = min($min_year, $this_year);
		} else {
			$this_date = null;
		}

		$date_input = $view->Form->input("CourseDate.$key.date", array(
			'label' => false,
			'type' => 'date',
			'maxYear' => $max_year,
			'minYear' => $min_year,
			'separator' => '',
			'div' => false,
			'class' => 'form-control',
			'disabled' => $is_dummy_input
		));
		$date_input = '<span class="date_group">'.$date_input.'</span>';

		$retval = $date_input.$time_input.$remove_button;
		if ($is_dummy_input) {
			 return '<div id="date_dummy_input">'.$retval.'</div>';
		}
		return '<div>'.$retval.'</div>';
	}
?>

<div class="page-header">
	<h1>
		<?php echo $title_for_layout; ?>
	</h1>
</div>

<?php if ($this->action == 'edit'): ?>
	<p>
		<?php echo $this->Html->link(
			'<span class="glyphicon glyphicon-arrow-left glyphicon-white"></span> Manage Courses',
			array(
				'action' => 'manage'
			),
			array(
				'escape' => false,
				'class' => 'btn btn-primary'
			)
		); ?>
	</p>
<?php endif; ?>

<a href="#" id="scheduling_help_toggler">
	<span class="glyphicon glyphicon-question-sign"></span> How does scheduling a course work?
</a>

<div class="alert alert-info" id="scheduling_help">
	<ul>
		<li>
			After you add a course, students will be able to register for it through the Elemental website.
		</li>
		<li>
			Once the class is full, students will be able to add themselves to a waiting list.
		</li>
		<li>
			For free classes: When students remove themselves from a course or are removed by an instructor, members of the waiting list will be automatically added up until the first day of the course.
		</li>
		<li>
			For classes with a registration fee: Members of the waiting list will be emailed when space becomes available on a first-come, first-served basis.
		</li>
	</ul>
</div>

<div class="courses form">
	<?php
		echo $this->Form->create('Course', array('id' => 'course_form'));

		// Create note about available PSRMs (which will be used in multiple places in this file)
		$available_psrm_note = '';
		if ($available_psrm) {
			$available_psrm_note .= '<span class="label label-success">'.$available_psrm.'</span>';
		} else {
			$available_psrm_note .= '<span class="label label-danger">0</span>';
		}
		$available_psrm_note .= ' <span class="after_label">prepaid student review '.__n('module is', 'modules are', $available_psrm).' available. ';
		$available_psrm_note .= $this->Html->link(
			'Get more <span class="glyphicon glyphicon-new-window"></span>',
			array(
				'controller' => 'store',
				'action' => 'prepaid_student_review_module'
			),
			array(
				'escape' => false,
				'target' => '_blank'
			)
		);
		$available_psrm_note .= '</span>';
	?>

	<fieldset>
		<legend>
			Class Type and Size
		</legend>
		<div class="form-group" id="free_vs_fee">
			<?php if ($this->request->action == 'add'): ?>
				<label>
					Cost to Attend
				</label>
				<input name="data[Course][free]" id="CourseFree_" value="" type="hidden">
				<div class="radio">
					<label for="CourseFree1">
						<?php
							$attributes = '';
							if ($available_psrm) {
								if ($this->data['Course']['free']) {
									$attributes .= 'checked="checked" ';
								}
							} else {
								$attributes .= 'disabled="disabled" ';
							}
						?>
						<input name="data[Course][free]" id="CourseFree1" value="1" type="radio" <?php echo $attributes; ?> />
						Free course
						<div>
							<?php echo $available_psrm_note; ?>
						</div>
					</label>
				</div>
				<div class="radio">
					<label for="CourseFree0">
						<?php
							$attributes = '';
							if (! $this->data['Course']['free']) {
								 $attributes .= 'checked="checked"';
							}
						?>
						<input name="data[Course][free]" id="CourseFree0" value="0" type="radio" <?php echo $attributes; ?> />
						Registration fee
					</label>
				</div>
			<?php elseif ($this->request->action == 'edit'): ?>
				<?php if ($this->data['Course']['free']): ?>
					<label>
						Free course
					</label>
				<?php else: ?>
					<label for="CourseCostDollars">
						Registration fee
					</label>
				<?php endif; ?>
			<?php endif; ?>
		</div>

		<?php
			if ($this->request->action == 'add' || ! $this->data['Course']['free']) {
				if (isset($payments_received) && $payments_received) {
					$warning = $payments_received.__n(' student has', ' students have', $payments_received).' already paid.';
					$warning = ' <span class="label label-info">'.$warning.'</span><br />';
				} else {
					$warning = '';
				}
				$cents = $this->Form->input('cost_cents', array(
					'class' => 'form-control',
					'div' => false,
					'label' => false,
					'maxlength' => 2
				));
				echo $this->Form->input('cost_dollars', array(
					'after' => '<span class="currency_symbol">.</span>'.$cents,
					'between' => $warning.'<span class="currency_symbol">$</span></a>',
					'class' => 'form-control',
					'div' => array(
						'class' => 'form-group cost',
						'id' => 'cost_fields'
					),
					'label' => false,
					'maxlength' => 3
				));
			}


			// Class size
			$class_size_footnotes = '';
			if ($this->request->action == 'edit') {
				if (isset($class_list_count) && $class_list_count > 0) {
					$class_size_footnotes .= '<br /><span class="label label-info">Note</span> There '.__n("is 1 participant", "are $class_list_count participants", $class_list_count).' currently registered. The class size cannot be reduced below this number.';
					$minimum_participants = $class_list_count;
				}
				if (! $this->data['Course']['free'] && isset($waiting_list_count) && $waiting_list_count > 0) {
					$class_size_footnotes .= '<br /><span class="label label-info">Note</span> There '.__n('is 1 participant', "are $waiting_list_count participants", $waiting_list_count).' on the waiting list who will be automatically moved into the course if the participant limit is increased. ';
				}
			}

			if (! isset($minimum_participants)) {
				$minimum_participants = 1;
			}
			echo $this->Form->input('max_participants', array(
				'after' => $class_size_footnotes,
				'class' => 'form-control',
				'div' => array('class' => 'form-group'),
				'label' => 'Maximum Number of Participants',
				'min' => $minimum_participants,
				'step' => 1
			));
		?>
	</fieldset>

	<fieldset>
		<legend>
			When
		</legend>
		<div class="form-group">
			<label>
				Date(s)
			</label>
			<?php echo dateTimeInput($this, 0, null, true); ?>
			<div id="input_dates" class="input">
				<?php
					if (! isset($this->request->data['CourseDate']) || empty($this->request->data['CourseDate'])) {
						$this->Js->buffer("courseAddForm.addDate();");
					} else {
						foreach ($this->request->data['CourseDate'] as $k => $course_date) {
							echo dateTimeInput($this, $k, $course_date);
						}
					}
				?>
				<a id="add_date" class="btn btn-success" href="#">
					<span class="glyphicon glyphicon-plus"></span> Add date
				</a>
			</div>
		</div>

		<?php
			echo $this->Form->input('deadline', array(
				'label' => 'Deadline to Register',
				'between' => '<div class="footnote">This is the last day that students will be able to register through the website.</div>',
				'maxYear' => (date('Y') + 2),
				'minYear' => date('Y'),
				'separator' => '',
				'class' => 'form-control',
				'div' => array('class' => 'form-group deadline_inputs')
			));
		?>
	</fieldset>

	<fieldset>
		<legend>
			Where
		</legend>
		<?php
			echo $this->Form->input('location', array(
				'label' => 'Name of Location',
				'class' => 'form-control',
				'div' => array('class' => 'form-group')
			));
			echo $this->Form->input('address', array(
				'label' => 'Street Address',
				'class' => 'form-control',
				'div' => array('class' => 'form-group')
			));
			echo $this->Form->input('city', array(
				'class' => 'form-control',
				'div' => array('class' => 'form-group')
			));
			$states = array(
				"AK", "AL", "AR", "AZ", "CA", "CO", "CT", "DC",
				"DE", "FL", "GA", "HI", "IA", "ID", "IL", "IN", "KS", "KY", "LA",
				"MA", "MD", "ME", "MI", "MN", "MO", "MS", "MT", "NC", "ND", "NE",
				"NH", "NJ", "NM", "NV", "NY", "OH", "OK", "OR", "PA", "RI", "SC",
				"SD", "TN", "TX", "UT", "VA", "VT", "WA", "WI", "WV", "WY"
			);
			echo $this->Form->input('state', array(
				'type' => 'select',
				'options' => array_combine($states, $states),
				'class' => 'form-control',
				'div' => array('class' => 'form-group')
			));
		?>
	</fieldset>

	<fieldset>
		<legend>
			Messages
		</legend>
		<?php
			echo $this->Form->input('details', array(
				'between' => '<div class="footnote">This optional description of the course will be included in its listing on the Elemental website.</div>',
				'class' => 'form-control',
				'div' => array('class' => 'form-group')
			));
			echo $this->Form->input('message', array(
				'label' => 'Message to Students',
				'between' => '<div class="footnote">This optional message will be included in emails that are sent to students who register for this course.</div>',
				'class' => 'form-control',
				'div' => array('class' => 'form-group')
			));
		?>
	</fieldset>

	<?php
		$label = ($this->action == 'edit') ? 'Update' : 'Submit';
		echo $this->Form->end(array(
			'label' => $label,
			'class' => 'btn btn-default'
		));
	?>
</div>