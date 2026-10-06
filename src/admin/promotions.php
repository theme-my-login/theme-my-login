<?php

/**
 * Theme My Login Admin Promotions
 *
 * Promotions shown on Theme My Login's own admin screens.
 *
 * @package Theme_My_Login
 * @subpackage Administration
 */

/**
 * Determine whether a notice has been dismissed.
 *
 * @since 7.3
 *
 * @param string $notice The notice key.
 * @return bool True if the notice has been dismissed.
 */
function tml_admin_notice_is_dismissed( $notice ) {
	return in_array( $notice, (array) get_site_option( '_tml_dismissed_notices', array() ), true );
}

/**
 * Get the suggestion for an extension, for display under a related setting.
 *
 * Nothing is suggested when the extension is installed or suggestions have
 * been hidden.
 *
 * @since 7.3
 *
 * @param string $slug  The extension slug, as used in its store URL.
 * @param string $title The extension title.
 * @param string $text  What the extension does.
 * @return string The suggestion HTML, or an empty string.
 */
function tml_admin_get_extension_suggestion( $slug, $title, $text ) {
	if (
		tml_extension_exists( 'tml-' . $slug )
		|| tml_extension_exists( 'theme-my-login-' . $slug )
		|| tml_admin_notice_is_dismissed( 'extension-suggestions' )
	) {
		return '';
	}

	return sprintf(
		'<span class="tml-extension-suggestion">%1$s <a href="%2$s" target="_blank">%3$s</a></span>',
		esc_html( $text ),
		esc_url( 'https://thememylogin.com/extensions/' . $slug . '/' ),
		/* translators: %s: Extension title. */
		esc_html( sprintf( __( 'Get %s', 'theme-my-login' ), $title ) )
	);
}

/**
 * Render the link that hides every extension suggestion.
 *
 * @since 7.3
 */
function tml_admin_extension_suggestions_toggle() {
	global $plugin_page;

	if ( 'theme-my-login' !== $plugin_page || tml_admin_notice_is_dismissed( 'extension-suggestions' ) ) {
		return;
	}
	?>
	<p class="tml-extension-suggestions-hide">
		<a href="#" data-notice="extension-suggestions" data-nonce="<?php echo esc_attr( wp_create_nonce( 'extension-suggestions' ) ); ?>"><?php esc_html_e( 'Hide extension suggestions', 'theme-my-login' ); ?></a>
	</p>
	<?php
}

/**
 * Determine whether it is time to ask for a review.
 *
 * @since 7.3
 *
 * @param int|null $now The current time. Defaults to now.
 * @return bool True if the review request should be shown.
 */
function tml_admin_review_request_is_due( $now = null ) {
	$installed_at = (int) get_site_option( '_tml_installed_at' );

	if ( ! $installed_at || tml_admin_notice_is_dismissed( 'review' ) ) {
		return false;
	}

	$now = null === $now ? time() : $now;

	return $now >= max( $installed_at + 30 * DAY_IN_SECONDS, (int) get_site_option( '_tml_review_later', 0 ) );
}

/**
 * Render the review request.
 *
 * @since 7.3
 */
function tml_admin_review_notice() {
	if ( ! tml_admin_review_request_is_due() ) {
		return;
	}
	?>
	<div class="notice notice-info tml-review-notice is-dismissible" data-nonce="<?php echo esc_attr( wp_create_nonce( 'tml-review-request' ) ); ?>">
		<p><?php esc_html_e( 'You have been using Theme My Login for a while now. If it has been useful, would you leave a review on WordPress.org? It helps other people find it.', 'theme-my-login' ); ?></p>
		<p>
			<a class="button button-primary" href="https://wordpress.org/support/plugin/theme-my-login/reviews/#new-post" target="_blank" data-choice="reviewed"><?php esc_html_e( 'Leave a Review', 'theme-my-login' ); ?></a>
			<a class="button" href="#" data-choice="later"><?php esc_html_e( 'Maybe Later', 'theme-my-login' ); ?></a>
			<a class="button-link" href="#" data-choice="never"><?php esc_html_e( 'Don&#8217;t Ask Again', 'theme-my-login' ); ?></a>
		</p>
	</div>
	<?php
}

/**
 * Handle an answer to the review request.
 *
 * @since 7.3
 */
function tml_admin_ajax_review_request() {
	if ( empty( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'tml-review-request' ) ) {
		wp_send_json_error( null, 400 );
	}
	if ( ! current_user_can( 'activate_plugins' ) ) {
		wp_send_json_error( null, 403 );
	}

	$choice = isset( $_POST['choice'] ) ? sanitize_key( $_POST['choice'] ) : '';

	if ( 'later' === $choice ) {
		update_site_option( '_tml_review_later', time() + 90 * DAY_IN_SECONDS );
	} elseif ( in_array( $choice, array( 'reviewed', 'never' ), true ) ) {
		tml_admin_dismiss_notice( 'review' );
	} else {
		wp_send_json_error( null, 400 );
	}

	wp_send_json_success();
}

/**
 * Get the sale currently announced by thememylogin.com, if any.
 *
 * The announcement arrives with the extensions feed, so it is cached for as
 * long as the feed is.
 *
 * @since 7.3
 *
 * @param int|null $now The current time. Defaults to now.
 * @return array|null The promotion, or null if none is running.
 */
function tml_admin_get_promotion( $now = null ) {
	$promotion = get_site_transient( 'tml_promotion' );

	if ( false === $promotion ) {
		delete_site_transient( 'tml_extensions_feed-' . md5( http_build_query( array( 'number' => 12 ) ) ) );
		tml_admin_get_extensions_feed();
		$promotion = get_site_transient( 'tml_promotion' );
	}

	// A failed fetch sets nothing, so hold off an hour rather than retry on every screen load.
	if ( false === $promotion ) {
		$promotion = array();
		set_site_transient( 'tml_promotion', $promotion, HOUR_IN_SECONDS );
	}

	return tml_admin_sanitize_promotion( $promotion, null === $now ? time() : $now );
}

/**
 * Validate a promotion from the extensions feed.
 *
 * The feed is remote data, so every field is checked rather than trusted, and
 * the link may only point at thememylogin.com or one of its subdomains.
 *
 * @since 7.3
 *
 * @param mixed $promotion The promotion from the feed.
 * @param int   $now       The current time.
 * @return array|null The promotion, or null if it is invalid or not running.
 */
function tml_admin_sanitize_promotion( $promotion, $now ) {
	if ( ! is_array( $promotion ) ) {
		return null;
	}

	foreach ( array( 'name', 'code', 'discount', 'starts', 'ends', 'url' ) as $key ) {
		if ( ! isset( $promotion[ $key ] ) || ! is_scalar( $promotion[ $key ] ) ) {
			return null;
		}
	}

	$starts = (int) $promotion['starts'];
	$ends   = (int) $promotion['ends'];
	$url    = wp_parse_url( (string) $promotion['url'] );

	if (
		$now < $starts
		|| $now >= $ends
		|| ! preg_match( '/^[A-Za-z0-9_-]{1,50}$/', (string) $promotion['code'] )
		|| ! preg_match( '/^\d{1,2}(\.\d+)?%$/', (string) $promotion['discount'] )
		|| empty( $url['scheme'] ) || 'https' !== $url['scheme']
		|| empty( $url['host'] ) || ! preg_match( '/(^|\.)thememylogin\.com$/', $url['host'] )
	) {
		return null;
	}

	$timezone = isset( $promotion['timezone'] ) ? (string) $promotion['timezone'] : '';

	return array(
		'name'     => wp_strip_all_tags( substr( (string) $promotion['name'], 0, 80 ) ),
		'code'     => (string) $promotion['code'],
		'discount' => (string) $promotion['discount'],
		'starts'   => $starts,
		'ends'     => $ends,
		'timezone' => in_array( $timezone, timezone_identifiers_list(), true ) ? $timezone : 'UTC',
		'url'      => (string) $promotion['url'],
	);
}

/**
 * Render the announcement of a running sale.
 *
 * @since 7.3
 */
function tml_admin_promotion_notice() {
	$promotion = tml_admin_get_promotion();

	if ( ! $promotion ) {
		return;
	}

	$notice_key = sanitize_key( 'promotion-' . $promotion['code'] . '-' . $promotion['ends'] );

	if ( tml_admin_notice_is_dismissed( $notice_key ) ) {
		return;
	}

	// Ends are exclusive, so the last moment of the sale is a second earlier.
	$ends = wp_date( get_option( 'date_format' ), $promotion['ends'] - 1, new DateTimeZone( $promotion['timezone'] ) );
	?>
	<div class="notice notice-info tml-notice is-dismissible" data-notice="<?php echo esc_attr( $notice_key ); ?>" data-nonce="<?php echo esc_attr( wp_create_nonce( $notice_key ) ); ?>">
		<p>
			<?php
			echo wp_kses(
				sprintf(
					/* translators: 1: Sale name, 2: Discount such as 30%, 3: Discount code, 4: Last day of the sale. */
					__( '<strong>%1$s:</strong> %2$s off Theme My Login extensions with code <strong>%3$s</strong>, through %4$s.', 'theme-my-login' ),
					esc_html( $promotion['name'] ),
					esc_html( $promotion['discount'] ),
					esc_html( $promotion['code'] ),
					esc_html( $ends )
				),
				array( 'strong' => array() )
			);
			?>
		</p>
		<p><a class="button button-primary" href="<?php echo esc_url( $promotion['url'] ); ?>" target="_blank"><?php esc_html_e( 'Shop Extensions', 'theme-my-login' ); ?></a></p>
	</div>
	<?php
}
