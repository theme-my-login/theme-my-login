(function($) {
	var form = $('#wp-auth-check-form');

	if (form.length) {
		form.data('src', tmlAdmin.interim_login_url);
	}

	$(initNotices);

	function initNotices() {
		$('.tml-notice').on('click', '.notice-dismiss', function(e) {
			var notice = $(e.delegateTarget);

			$.post(ajaxurl, {
				action: 'tml-dismiss-notice',
				notice: notice.data('notice')
			});
		});
	}
})(jQuery);
