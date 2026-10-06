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

		$( '.tml-review-notice' ).on( 'click', '[data-choice], .notice-dismiss', function( e ) {
			var notice = $( e.delegateTarget ),
				choice = $( this ).data( 'choice' ) || 'later';

			if ( 'reviewed' !== choice ) {
				e.preventDefault();
			}

			$.post( ajaxurl, {
				action: 'tml-review-request',
				choice: choice,
				nonce: notice.data( 'nonce' )
			} );

			notice.fadeTo( 100, 0, function() {
				notice.slideUp( 100, function() {
					notice.remove();
				} );
			} );
		} );
	}
} )( jQuery );
