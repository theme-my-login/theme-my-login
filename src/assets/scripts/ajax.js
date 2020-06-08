( function ( $ ) {

	$( '.tml' ).on( 'submit', 'form[data-ajax="1"]', function( e ) {
		var form = $( this ),
			input = form.find( ':input' ),
			submit = form.find( ':submit' ),
			container = $( e.delegateTarget ),
			notices = container.find( '.tml-alerts' );

		e.preventDefault();

		notices.empty();

		input.prop( 'readonly', true );
		submit.prop( 'disabled', true );

		$.ajax( {
			data: form.serialize(),
			method: form.attr( 'method' ) || 'get',
			url: form.attr( 'action' )
		} )
		.always( function() {
			input.prop( 'readonly', false );
			submit.prop( 'disabled', false );
		} )
		.done( function( response ) {
			if ( response.success ) {
				if ( response.data.refresh ) {
					location.reload( true );
				} else if ( response.data.redirect ) {
					location.href = response.data.redirect;
				} else if ( response.data.notice ) {
					notices.hide().html( response.data.notice ).fadeIn();
				}
			} else {
				notices.hide().html( response.data.errors ).fadeIn();
			}
		} )
		.fail( function( jqXHR, textStatus, errorThrown ) {
			if ( jqXHR.responseJSON.data.errors ) {
				notices.hide().html( jqXHR.responseJSON.data.errors ).fadeIn();
			}
		} );
	} );
} )( jQuery );
