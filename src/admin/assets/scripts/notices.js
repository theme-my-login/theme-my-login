( function( $ ) {
	$( initNotices );

	function initNotices() {
		$( '.tml-notice' ).on( 'click', '.notice-dismiss', function( e ) {
			var notice = $( e.delegateTarget );

			$.post( ajaxurl, {
				action: 'tml-dismiss-notice',
				notice: notice.data( 'notice' ),
				nonce: notice.data( 'nonce' )
			} );
		} );

		$( '.tml-extension-suggestions-hide' ).on( 'click', 'a', function( e ) {
			var link = $( this );

			e.preventDefault();

			$.post( ajaxurl, {
				action: 'tml-dismiss-notice',
				notice: link.data( 'notice' ),
				nonce: link.data( 'nonce' )
			} );

			$( '.tml-extension-suggestion' ).closest( '.description' ).remove();
			link.closest( '.tml-extension-suggestions-hide' ).remove();
		} );
	}
} )( jQuery );
