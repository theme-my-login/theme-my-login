( function( $ ) {
	$( function() {
		$( '.tml-license-field' ).on( 'input', function() {
			var input = $( this ),
				activate = input.siblings( '.tml-license-button' ).filter( '[data-action="activate"]' ),
				status = input.siblings( '.tml-license-status' );

			if ( this.value.length == 32 ) {
				activate.show();
			} else {
				activate.hide();
				status
					.html( '' )
					.add( input )
					.addClass( 'tml-license-inactive' )
					.removeClass( 'tml-license-valid tml-license-invalid' );
			}
		} );

		$( '.tml-license-button' ).on( 'click', function( e ) {
			var button = $( this ),
				action = button.data( 'action' ),
				otherButton = button.siblings( '.tml-license-button' ),
				input = button.siblings( '.tml-license-field' ),
				spinner = button.siblings( '.spinner' ),
				status = button.siblings( '.tml-license-status' ),
				nonce = button.closest("form").find("#_wpnonce").val();

			e.preventDefault();

			button.hide();
			spinner.addClass( 'is-active' );

			$.post( tmlAdmin.ajaxUrl, {
				action: 'tml-' + action + '-extension-license',
				extension: button.data( 'extension' ),
				key: input.val(),
				_wpnonce: nonce
			} ).done( function( response ) {
				if ( response.success ) {
					otherButton.show();
					status
						.show()
						.html( response.data )
						.add( input )
						.addClass(
							action == 'activate'
							? 'tml-license-valid'
							: 'tml-license-inactive'
						)
						.removeClass(
							action == 'activate'
							? 'tml-license-invalid'
							: 'tml-license-valid tml-license-invalid'
						);
				} else {
					button.show();
					status
						.show()
						.html( response.data )
						.add( input )
						.addClass( 'tml-license-invalid' )
						.removeClass( 'tml-license-valid' );
				}
			} ).fail( function ( jqXHR, textStatus, errorThrown ) {
				alert( errorThrown );
				button.show();
			} ).always( function() {
				spinner.removeClass( 'is-active' );
			} );
		} );
	} );
} )( jQuery );
