( function ( $ ) {

	$( '.tml' ).on( 'click', '.tml-toggle-pwd', function() {
		var button = $( this ),
			input = button.siblings( 'input' ),
			isHidden = 'password' === input.attr( 'type' );

		input.attr( 'type', isHidden ? 'text' : 'password' );

		button
			.attr( 'aria-label', isHidden ? themeMyLogin.hidePasswordLabel : themeMyLogin.showPasswordLabel )
			.find( '.dashicons' )
				.toggleClass( 'dashicons-hidden', isHidden )
				.toggleClass( 'dashicons-visibility', ! isHidden );
	} );
} )( jQuery );
