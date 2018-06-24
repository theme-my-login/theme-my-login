<?php

/**
 * Theme My Login Compatibility Functions
 *
 * Note I proposed a patch that, if accepted, would negate the need for this file.
 * @link https://core.trac.wordpress.org/ticket/31039
 *
 * @package Theme_My_Login
 * @subpackage Compatibility
 */

/**
 * Handles validating the lost password request and retrieving the password reset key.
 *
 * @since 7.0
 *
 * @return True on success, WP_Error on error.
 */
function tml_retrieve_password() {
	$errors = new WP_Error();

	if ( empty( $_POST['user_login'] ) || ! is_string( $_POST['user_login'] ) ) {
		$errors->add( 'empty_username', __( '<strong>ERROR</strong>: Enter a username or email address.' ) );
	} elseif ( strpos( $_POST['user_login'], '@' ) ) {
		$user_data = get_user_by( 'email', trim( wp_unslash( $_POST['user_login'] ) ) );
		if ( empty( $user_data ) ) {
			$errors->add( 'invalid_email', __( '<strong>ERROR</strong>: There is no user registered with that email address.' ) );
		}
	} else {
		$login     = trim( $_POST['user_login'] );
		$user_data = get_user_by( 'login', $login );
	}

	/** This action is documented in wp-login.php */
	do_action( 'lostpassword_post', $errors );

	if ( $errors->get_error_code() ) {
		return $errors;
	}

	if ( ! $user_data ) {
		$errors->add( 'invalidcombo', __( '<strong>ERROR</strong>: Invalid username or email.' ) );
		return $errors;
	}

	$key = get_password_reset_key( $user_data );
	if ( is_wp_error( $key ) ) {
		return $key;
	}

	/**
	 * Fires after a password reset key is retrieved.
	 *
	 * @since unknown
	 *
	 * @param WP_User $user_data The user object.
	 * @param string  $key       The password reset key.
	 */
	do_action( 'retrieved_password_key', $user_data, $key );

	return true;
}

/**
 * Sends the retrieve password notification.
 *
 * @since 7.0
 *
 * @param WP_User $user The user object.
 * @param string $key   The password reset key.
 */
function tml_retrieve_password_notification( $user, $key ) {
	if ( is_multisite() ) {
		$site_name = get_network()->site_name;
	} else {
		/*
		 * The blogname option is escaped with esc_html on the way into the database
		 * in sanitize_option we want to reverse this for the plain text arena of emails.
		 */
		$site_name = wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );
	}

	$message = __( 'Someone has requested a password reset for the following account:' ) . "\r\n\r\n";
	/* translators: %s: site name */
	$message .= sprintf( __( 'Site Name: %s' ), $site_name ) . "\r\n\r\n";
	/* translators: %s: user login */
	$message .= sprintf( __( 'Username: %s' ), $user->user_login ) . "\r\n\r\n";
	$message .= __( 'If this was a mistake, just ignore this email and nothing will happen.' ) . "\r\n\r\n";
	$message .= __( 'To reset your password, visit the following address:' ) . "\r\n\r\n";
	$message .= '<' . network_site_url( "wp-login.php?action=rp&key=$key&login=" . rawurlencode( $user->user_login ), 'login' ) . ">\r\n";

	/* translators: Password reset email subject. %s: Site name */
	$title = sprintf( __( '[%s] Password Reset' ), $site_name );

	/**
	 * Filters the subject of the password reset email.
	 *
	 * @since 2.8.0
	 * @since 4.4.0 Added the `$user_login` and `$user_data` parameters.
	 *
	 * @param string  $title      Default email title.
	 * @param string  $user_login The username for the user.
	 * @param WP_User $user       WP_User object.
	 */
	$title = apply_filters( 'retrieve_password_title', $title, $user->user_login, $user );

	/**
	 * Filters the message body of the password reset mail.
	 *
	 * If the filtered message is empty, the password reset email will not be sent.
	 *
	 * @since 2.8.0
	 * @since 4.1.0 Added `$user_login` and `$user_data` parameters.
	 *
	 * @param string  $message    Default mail message.
	 * @param string  $key        The activation key.
	 * @param string  $user_login The username for the user.
	 * @param WP_User $user       WP_User object.
	 */
	$message = apply_filters( 'retrieve_password_message', $message, $key, $user->user_login, $user );

	$retrieve_password_email = array(
		'to'      => $user->user_email,
		'subject' => $title,
		'message' => $message,
		'headers' => '',
	);

	/**
	 * Filters the contents of the password retrieval email.
	 *
	 * @since 7.0.6
	 *
	 * @param array   $retrieve_password_email {
	 *     Used to build wp_mail().
	 *
	 *     @type string $to      The recipient of the email.
	 *     @type string $subject The subject of the email.
	 *     @type string $message The body of the email.
	 *     @type string $headers The headers of the email.
	 * }
	 * @param WP_User $user     The user object..
	 * @param string  $blogname The site title.
	 */
	$retrieve_password_email = apply_filters( 'tml_retrieve_password_email', $retrieve_password_email, $user, $blogname );

	if ( $retrieve_password_email['message'] && ! wp_mail(
		$retrieve_password_email['to'],
		wp_specialchars_decode( sprintf( $retrieve_password_email['subject'], $blogname ) ),
		$retrieve_password_email['message'],
		$retrieve_password_email['headers']
	) ) {
		wp_die( __( 'The email could not be sent.' ) . "<br />\n" . __( 'Possible reason: your host may have disabled the mail() function.' ) );
	}
}
