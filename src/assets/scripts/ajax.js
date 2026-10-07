( function ( $ ) {

	$( '.tml' ).on( 'submit', 'form[data-ajax="1"]', function( e ) {
		var form = $( this ),
			input = form.find( ':input' ),
			submit = form.find( ':submit' ),
			container = $( e.delegateTarget ),
			notices = container.find( '.tml-alerts' );

		function showErrors( errors ) {
			if ( ! errors ) {
				errors = $( '<ul class="tml-errors"><li class="tml-error"></li></ul>' );
				errors.find( 'li' ).text( themeMyLogin.ajaxErrorMessage );
			}
			notices.hide().html( errors ).fadeIn();
		}

		e.preventDefault();

		notices.empty();

		input.prop( 'readonly', true );
		submit.prop( 'disabled', true );

		$.ajax( {
			data: form.serialize() + '&ajax=1',
			method: form.attr( 'method' ) || 'get',
			url: form.attr( 'action' )
		} )
		.always( function() {
			input.prop( 'readonly', false );
			submit.prop( 'disabled', false );
		} )
		.done( function( response ) {
			if ( response && response.success ) {
				if ( response.data.refresh ) {
					location.reload( true );
				} else if ( response.data.redirect ) {
					location.href = response.data.redirect;
				} else if ( response.data.notice ) {
					notices.hide().html( response.data.notice ).fadeIn();
				}
			} else {
				showErrors( response && response.data && response.data.errors );
			}
		} )
		.fail( function( jqXHR ) {
			var json = jqXHR.responseJSON;

			showErrors( json && json.data && json.data.errors );
		} );
	} );
} )( jQuery );
