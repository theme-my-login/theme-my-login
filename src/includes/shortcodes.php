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

	$atts = shortcode_atts( array(
		'action'      => '',
		'show_links'  => null,
		'redirect_to' => null,
	), $atts, 'theme-my-login' );

	$content = '';

	if ( empty( $atts['action'] ) ) {
		$action = tml_is_action() ? tml_get_action() : tml_get_action( 'login' );
	} elseif ( ! $action = tml_get_action( $atts['action'] ) ) {
		return $content;
	}

	if ( $form = tml_get_form( $action->get_name() ) ) {

		$args = array();

		if ( null !== $atts['show_links'] ) {
			$args['show_links'] = (bool) $atts['show_links'];
		}

		if ( null !== $atts['redirect_to'] ) {
			if ( $redirect_to = $form->get_field( 'redirect_to' ) ) {
				$redirect_to->set_value( $atts['redirect_to'] );
			}
			unset( $redirect_to );
		}

		$content = $form->render( $args );

	} elseif ( 'confirmaction' == $action->get_name() && isset( $_GET['request_id'] ) ) {
		$content = _wp_privacy_account_request_confirmed_message( $_GET['request_id'] );
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
