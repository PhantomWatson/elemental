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
	setup: function() {
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
			console.log('removing');
			$(this).parent('div').remove();
		});
		
		// Add new date input
		input.insertBefore('#add_date');
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
	age_field: null,
	age_blank: null,
	guardian_fields: null,
	
	init: function () {
		this.name_field = $('#ReleaseName');
		this.name_blank = $('#name_blank');
		this.age_field = $('#ReleaseAge');
		this.age_blank = $('#age_blank');
		this.guardian_fields = $('#guardian_fields');
		this.updateName();
		this.name_field.change(function () {
			releaseForm.updateName();
		});
		this.updateAge();
		this.age_field.change(function () {
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
		var age = this.age_field.val();
		if (age == '') {
			this.age_blank.html('&nbsp;');
		} else if (isNumber(age)) {
			this.age_blank.html(age);
			if (age < 18) {
				this.activateGuardianFields();
			} else {
				this.deactivateGuardianFields();
			}
		} else {
			this.age_blank.html('&nbsp;');
			this.age_field.val('');
			alert('Your age must be numeric');
		}
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