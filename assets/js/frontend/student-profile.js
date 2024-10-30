jQuery(function ($) {

	$.fn.toggleStateSelect = function (object) {
		var val = object.val();
		if (val == 'US') {
			$('.register-user_state').slideDown();
		} else {
			$('.register-user_state').slideUp();
		}
	};

	$.fn.toggleStateSelect($('#user_country'));

	$(document).on('change', '#user_country', function () {
		$.fn.toggleStateSelect($(this));
	});

});