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
	}
} )( jQuery );
