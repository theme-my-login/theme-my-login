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
