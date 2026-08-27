( function ( $ ) {

	var $pass2 = $( '#pass2' );

	if ( ! $pass2.length ) {
		return;
	}

	$pass2.closest( '.tml-field-wrap' ).hide();

	$( '#pass1' ).on( 'input', function() {
		$pass2.val( $( this ).val() );
	} );

} )( jQuery );
