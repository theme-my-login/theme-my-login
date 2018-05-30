( function( $ ) {
	$( initMetaBoxes );

	function initMetaBoxes() {
		var metaboxes = $( '.postbox' );

		if ( metaboxes.length ) {
			// Make metaboxes toggleable
			postboxes.add_postbox_toggles( pagenow );

			// Close all metaboxes by default
			$( '.postbox' ).addClass( 'closed' );

			// Find each metabox holder
			$( '.metabox-holder' ).each( function() {
				var holder = $( this );

				// Maybe disable sorting
				if ( holder.data( 'sortable' ) == 'off' ) {
					holder.find( '.meta-box-sortables' ).sortable( 'destroy' );
					holder.find( '.postbox .hndle' ).css( 'cursor', 'default' );
				}
			} );
		}
	}
} )( jQuery );
