<div class="page-header">
	<h1>
		<?php echo $title_for_layout; ?>
	</h1>
</div>

<div id="release_form">
	<h2>
		Acknowledgement of Risk
	</h2>
	<p>
		I, <span class="variable_text" id="name_blank">&nbsp;</span>, affirm my desire to participate in the sexual assault protection program called Elemental. During the program, I understand I will be introduced to a variety of sexually charged situations and taught to defend myself verbally and physically.
	</p>
	<p>
		These situations will include, but are not limited to, verbal sexual advances and pressure, sexual language, simulated attempted kissing, grabbing, and close-quarter self-defense situations from standing positions as well as on couches and beds.
	</p>
	<p>
		I understand I will practice a variety of physical self-defense techniques drawn from martial arts, such as palm-heel strikes, elbow strikes, knee strikes, throws, and grappling techniques. These techniques will be practiced in both a "shadow-boxing" fashion as well as with padded attackers and other partners in class. I understand there is a risk of physical injury or emotional distress in participating in the sexually charged situations and practicing self-defense techniques.
	</p>

	<h2>
		Release of All Claims
	</h2>
	<p>
		In consideration of my participation in the sexual assault protection program, I hereby agree to release and, on behalf of myself, my heirs, representatives, executors, administrators, and assigns, hereby do release and hold harmless the program's instructors, Elemental Sexual Assault Protection LLC, Vizi Courseware, the School or University, its Board of Trustees, officers, employees, contractors, agents, and volunteers from any liability, actions, causes of action, claims, and demands of any nature whatsoever, which I may have against the program's instructors, Elemental Sexual Assault Protection LLC, Vizi Courseware, the School or University, its Board of Trustees, officers, employees, contractors, agents, and volunteers as a result of any personal injury, property damage, permanent disability, or death I may suffer in connection with my participation in the sexual assault protection program.
	</p>
	<h2>
		Medical Authorization
	</h2>
	<p>
		In the event of a medical emergency in which I am not reasonably available to provide competent consent to medical treatment, the program's instructors, officers, employee or agents of Elemental Sexual Assault Protection LLC or the School or University are hereby authorized to consent to emergency medical treatment on my behalf. I further understand that the School or University does not maintain health or accident insurance to provide coverage for me and that I should consider obtaining the protection of such insurance on an individual basis. I understand and agree that all costs of any such emergency medical treatment are my responsibility and not that of the School or University. I, for myself, my heirs, representatives, executors, administrators, and assigns, hereby release and hold harmless the program's instructors, Elemental Sexual Assault Protection LLC, Vizi Courseware, the School or University, its Board of Trustees, officers, employees, contractors, agents, and volunteers from any liability, actions, causes of action, claims, or demands of any nature whatsoever, in connection with any decision of any of the program's instructors, Elemental Sexual Assault Protection LLC, the School or University's officers, employees, contractors, agents, and volunteers to obtain emergency medical treatment for me.
	</p>

	<h2>
		Miscellaneous
	</h2>
	<p>
		This Release shall be governed by and interpreted in accordance with the laws of the state of Indiana. I agree that in the event that any clause or provision of this Release shall be held to be invalid by any court of competent jurisdiction, the invalidity of such clause or provision shall not otherwise affect the remaining provisions of this Release, which shall continue to be enforceable.
	</p>
	<p>
		I further understand that the terms of this Release are legally binding and I certify that I am <span class="variable_text" id="age_blank">&nbsp;</span> years old and that I am signing this Release, after having carefully read the same, of my own free will.
	</p>
	<p>
		In witness whereof, this instrument is duly executed this <?php echo $date; ?>.
	</p>


	<?php
		echo $this->Form->create('Release');
		echo $this->Form->input(
			'name',
			array(
				'class' => 'form-control',
				'div' => array(
					'class' => 'form-group'
				),
				'label' => 'Student\'s name'
			)
		);
		echo $this->Form->input(
			'age',
			array(
				'class' => 'form-control',
				'div' => array(
					'class' => 'form-group'
				),
				'label' => 'Student\'s age',
				'type' => 'number'
			)
		);
	?>

	<div id="guardian_fields">
		<?php
			echo $this->Form->input(
				'guardian_name',
				array(
					'after' => '<span class="footnote">Required if student is under 18 years old</span>',
					'class' => 'form-control',
					'div' => array(
						'class' => 'form-group'
					),
					'label' => 'Parent or guardian\'s name',
					'required' => false
				)
			);
			echo $this->Form->input(
				'guardian_phone',
				array(
					'class' => 'form-control',
					'div' => array(
						'class' => 'form-group'
					),
					'label' => 'Parent or guardian\'s phone number',
					'required' => false,
					'type' => 'tel'
				)
			);
		?>
	</div>

	<?php
		echo $this->Form->end(
			array(
				'class' => 'btn btn-primary',
				'label' => 'Submit'
			)
		);

		$this->Js->buffer("releaseForm.init();");
	?>

	<p>

		REQUIRED IF STUDENT
		UNDER 18 YEARS OLD
	</p>
</div>