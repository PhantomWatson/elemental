var flashMessages = {
	show: function() {
		var messages = $('#flash_messages');
		messages.find('li').addClass('in');
	},
	
	hide: function() {
		var messages = $('#flash_messages');
		if (messages.is(':visible')) {
			messages.fadeOut(500, function() {
				$('#flash_messages ul').empty();
			});
		}
	},
	
	insert: function(message, classname) {
		var msg_li = $('<li></li>');
		msg_li.addClass('alert');
		msg_li.addClass(classname);
		msg_li.addClass('fade');
		var close_button = $('<button type="button" class="close" data-dismiss="alert">&times;</button>');
		msg_li.append(close_button);
		msg_li.append(message);
	
		$('#flash_messages ul').append(msg_li);
		if ($('#flash_messages').is(':visible')) {
			msg_li.addClass('in');
		} else {
			flashMessages.show();
		}
	}
};

function setupTestimonialExcerpt() {
	$('.excerpt_embiggener').click(function(event) {
		event.preventDefault();
		$('#random_testimonial .testimonial_excerpt').fadeOut(100, function() {
			$('#random_testimonial .testimonial_full').fadeIn(300);
		});
	});
}

var courseAddForm = {
	available_psrm: 0,
	action: null,
		
	setup: function (params) {
		this.available_psrm = params.available_psrm;
		this.action = params.action;
		
		$('#scheduling_help_toggler').click(function(event) {
			event.preventDefault();
			$('#scheduling_help').slideToggle(300);
		});
		
		$('#add_date').click(function(event) {
			event.preventDefault();
			courseAddForm.addDate();
		});
		
		$('#input_dates .remove_date').click(function(event) {
			event.preventDefault();
			$(this).parent('div').remove();
		});
		
		if (this.action == 'add') {
			$('#free_vs_fee input[type="radio"]').change(function () {
				courseAddForm.toggleCostFields(true);
			});
			this.toggleCostFields(false);
		}
		
		$('#CourseCostDollars').change(function () {
			if ($('#CourseFree0').is(':checked') && $('#CourseCostDollars').val() < 20) {
				alert('If a registration fee is charged for this course, it must be at least $20.');
				$('#CourseCostDollars').val(20);
			}
		});
		$('#course_form').submit(function (event) {
			if ($('#CourseFree0').is(':checked') && $('#CourseCostDollars').val() < 20) {
				event.preventDefault();
				alert('If a registration fee is charged for this course, it must be at least $20.');
			}
		});
	},
	
	addDate: function() {
		var input = $('#date_dummy_input').clone();
		input.removeAttr('id');
		var selects = input.find('select');
		var inputs_container = $('#input_dates');
		var key = inputs_container.children().length - 1;
		
		// Enable new date input and give it a unique key
		selects.prop('disabled', false);
		selects.each(function() {
			var select = $(this);
			var name = select.attr('name');
			select.attr('name', name.replace('[0]', '['+key+']'));
		});
		
		// Set up remove button
		input.find('.remove_date').click(function(event) {
			event.preventDefault();
			$(this).parent('div').remove();
		});
		
		// Add new date input
		input.insertBefore('#add_date');
	},
	
	toggleCostFields: function (animated) {
		var cost_fields_container = $('#cost_fields');
		var cost_fields = cost_fields_container.find('input');
		var class_size = $('#CourseMaxParticipants');
		
		// Free
		if ($('#CourseFree1').is(':checked')) {
			cost_fields.prop('required', false);
			if (cost_fields_container.is(':visible')) {
				if (animated) {
					cost_fields_container.slideUp();
				} else {
					cost_fields_container.hide();
				}
			}
			
			if (this.action == 'add') {
				class_size.attr('max', this.available_psrm);
				if (class_size.val() > this.available_psrm) {
					class_size.val(this.available_psrm);
				}
			}
			
		// Fee
		} else {
			cost_fields.prop('required', true);
			if (! cost_fields_container.is(':visible')) {
				if (animated) {
					cost_fields_container.slideDown();
				} else {
					cost_fields_container.show();
				}
			}
			
			class_size.attr('max', '');
		}
	}
};

var courseList = {
	setup: function() {
		$('a.more_dates').click(function(event) {
			event.preventDefault();
			var ul = $(this).parents('ul');
			ul.children('li').slideDown(300);
			$(this).fadeTo(300, 0);
		});
	}
};

function isNumber(n) {
	return ! isNaN(parseFloat(n)) && isFinite(n);
}

var releaseForm = {
	name_field: null,
	name_blank: null,
	age_blank: null,
	guardian_fields: null,
	birth_year_field: null,
	birth_month_field: null,
	birth_day_field: null,
	
	init: function () {
		this.name_field = $('#ReleaseName');
		this.name_blank = $('#name_blank');
		this.age_blank = $('#age_blank');
		this.guardian_fields = $('#guardian_fields');
		this.birth_year_field = $('#ReleaseBirthdateYear');
		this.birth_month_field = $('#ReleaseBirthdateMonth');
		this.birth_day_field = $('#ReleaseBirthdateDay');
		
		this.updateName();
		this.name_field.change(function () {
			releaseForm.updateName();
		});
		
		this.updateAge();
		var birthdate_fields = $('#ReleaseBirthdateYear, #ReleaseBirthdateMonth, #ReleaseBirthdateDay');
		birthdate_fields.change(function () {
			releaseForm.updateAge();
		});
	},
	
	updateName: function () {
		var name = this.name_field.val();
		if (name == '') {
			this.name_blank.html('&nbsp;');
		} else {
			this.name_blank.html(name);
		}
	},
	
	updateAge: function () {
		var age = this.getAge();
		if (! age) {
			this.age_blank.html('&nbsp;');
		} else if (isNumber(age)) {
			this.age_blank.html(age);
			if (age < 18) {
				this.activateGuardianFields();
			} else {
				this.deactivateGuardianFields();
			}
		}
	},
	
	// Function helpfully provided by http://stackoverflow.com/questions/10008050/get-age-from-birthdate
	getAge: function () {
	    var today = new Date();
	    var birth_year = this.birth_year_field.val();
		var birth_month = this.birth_month_field.val() - 1;
		var birth_day = this.birth_day_field.val();
		var birthDate = new Date(birth_year, birth_month, birth_day);
	    var age = today.getFullYear() - birthDate.getFullYear();
	    var m = today.getMonth() - birthDate.getMonth();
	    if (m < 0 || (m === 0 && today.getDate() < birthDate.getDate())) {
	        age--;
	    }
	    return age;
	},
	
	activateGuardianFields: function () {
		if (! this.guardian_fields.is(':visible')) {
			this.guardian_fields.slideDown();
		}
		this.guardian_fields.find('input').prop('required', true);
	},
	
	deactivateGuardianFields: function () {
		if (this.guardian_fields.is(':visible')) {
			this.guardian_fields.slideUp();
		}
		this.guardian_fields.find('input').prop('required', false);
	}
};