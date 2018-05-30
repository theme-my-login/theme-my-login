( function ( $ ) {

	$( initFocus );

	function initFocus() {
		var userLogin, key;

		if ( ! themeMyLogin.action ) {
			return;
		}

		userLogin = $( '#user_login' );

		switch ( themeMyLogin.action ) {
			case 'activate' :
				key = $( '#key' );
				if ( key.length ) {
					key.focus();
				}
				break;

			case 'lostpassword' :
			case 'retrievepassword' :
			case 'register' :
				userLogin.focus();
				break;

			case 'resetpass' :
			case 'rp' :
				$( '#pass1' ).focus();
				break;

			case 'login' :
				if ( -1 != themeMyLogin.errors.indexOf( 'invalid_username' ) ) {
					userLogin.val( '' );
				}

				if ( userLogin.val() ) {
					$( '#user_pass' ).focus();
				} else {
					userLogin.focus();
				}
				break;
		}
	}
} )( jQuery );
