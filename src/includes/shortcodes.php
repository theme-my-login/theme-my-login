<?php

/**
 * Theme My Login Shortcodes
 *
 * @package Theme_My_Login
 * @subpackage Shortcodes
 */

/**
 * Render the shortcode.
 *
 * @since 7.0
 *
 * @param array $atts {
 *     Optional. An array of shortcode attributes.
 *
 *     @type string $action      The action to display. Default is current action.
 *     @type bool   $show_links  Whether the actions links should be shown or not.
 *     @type string $redirect_to The URL to redirect to after the form is submitted.
 * }
 * @return string The action content.
 */
function tml_shortcode( $atts = array() ) {
	$atts = (array) $atts;

	if ( isset( $atts['default_action'] ) ) {
		$atts['action'] = $atts['default_action'];
	}

	$atts = shortcode_atts(
		array(
			'action'      => '',
			'show_links'  => null,
			'redirect_to' => null,
		),
		$atts,
		'theme-my-login'
	);

	$content = '';

	if ( empty( $atts['action'] ) ) {
		$action = tml_is_action() ? tml_get_action() : tml_get_action( 'login' );
	} else {
		$action = tml_get_action( $atts['action'] );
		if ( ! $action ) {
			return $content;
		}
	}

	$form = tml_get_form( $action->get_name() );

	if ( $form ) {

		$args = array();

		if ( null !== $atts['show_links'] ) {
			$args['show_links'] = (bool) $atts['show_links'];
		}

		if ( null !== $atts['redirect_to'] ) {
			$redirect_to = $form->get_field( 'redirect_to' );
			if ( $redirect_to ) {
				$redirect_to->set_value( $atts['redirect_to'] );
			}
			unset( $redirect_to );
		}

		$content = $form->render( $args );

		// phpcs:disable WordPress.Security.NonceVerification.Recommended -- request_id/confirm_key are validated via wp_validate_user_request_key() in tml_confirmaction_handler(), which tml_is_action() confirms has already run (template_redirect, before this shortcode renders) without wp_die()'ing on an invalid key.
	} elseif ( 'confirmaction' === $action->get_name() && tml_is_action( 'confirmaction' ) && isset( $_GET['request_id'] ) ) {
		$content = _wp_privacy_account_request_confirmed_message( $_GET['request_id'] );
		// phpcs:enable WordPress.Security.NonceVerification.Recommended

	} elseif ( 'dashboard' === $action->get_name() ) {
		$content = '<div class="tml-dashboard">';

		$content .= '<div class="tml-dashboard-avatar">' . get_avatar( get_current_user_id() ) . '</div>';

		// translators: %s: Current user's display name.
		$content .= '<p class="tml-dashboard-greeting">' . sprintf( __( 'Howdy, %s' ), esc_html( wp_get_current_user()->display_name ) ) . '</p>'; // phpcs:ignore WordPress.WP.I18n.MissingArgDomain -- intentionally reusing WP core's translated "Howdy, %s" string (see wp-includes/admin-bar.php), not a TML-specific string.

		/**
		 * Filter the dashboard links.
		 *
		 * @since 7.1
		 *
		 * @param array $links The dashboard links.
		 */
		// phpcs:disable WordPress.WP.I18n.MissingArgDomain -- these labels intentionally reuse WP core's translated strings ("Site Admin"/"Edit Profile"/"Log Out" all exist verbatim in wp-includes), not TML-specific strings.
		$links = apply_filters(
			'tml_dashboard_links',
			array_filter(
				array(
					'site_admin' => current_user_can( 'edit_posts' ) ? array(
						'title' => __( 'Site Admin' ),
						'url'   => admin_url(),
					) : false,
					'profile'    => array(
						'title' => __( 'Edit Profile' ),
						'url'   => admin_url( 'profile.php' ),
					),
					'logout'     => array(
						'title' => __( 'Log Out' ),
						'url'   => wp_logout_url(),
					),
				)
			)
		);
		// phpcs:enable WordPress.WP.I18n.MissingArgDomain

		if ( ! empty( $links ) ) {
			$content .= '<ul class="tml-dashboard-links">';
			foreach ( $links as $link ) {
				$content .= '<li><a href="' . esc_url( $link['url'] ) . '">' . esc_html( $link['title'] ) . '</a></li>';
			}
			$content .= '</ul>';
		}

		$content .= '</div>';
	}

	/**
	 * Filter the shortcode content.
	 *
	 * @since 7.0
	 *
	 * @param string $content The shortcode content.
	 * @param string $action  The action name.
	 * @param array  $atts    The shortcode attributes.
	 */
	return apply_filters( 'tml_shortcode', $content, $action->get_name(), $atts );
}
add_shortcode( 'theme-my-login', 'tml_shortcode' );
