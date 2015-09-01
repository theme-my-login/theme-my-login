(function($) {
	var form = $('#wp-auth-check-form');

	if (form.length)
		form.data('src', tmlAdmin.interim_login_url);
})(jQuery);
