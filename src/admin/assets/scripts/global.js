( function( $ ) {

	$( initAuthCheckForm );

	function initAuthCheckForm() {
		var authCheckForm = $( '#wp-auth-check-form' );

		if ( authCheckForm.length ) {
			authCheckForm.attr( 'data-src', tmlAdmin.interimLoginUrl );
		}
	}
} )( jQuery );
