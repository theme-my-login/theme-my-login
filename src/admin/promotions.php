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
