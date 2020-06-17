<?php

/**
 * Theme My Login Options
 *
 * @package Theme_My_Login
 * @subpackage Core
 */

/**
 * Get the installed TML version.
 *
 * @since 7.0
 *
 * @return string|bool The installed TML version.
 */
function tml_get_installed_version() {
	if ( $options = get_option( 'theme_my_login' ) ) {
		if ( isset( $options['version'] ) ) {
			return $options['version'];
		}
	}
	return get_site_option( '_tml_version' );
}

/**
 * Get the previous TML version.
 *
 * @since 7.0
 *
 * @return string|bool The previous TML version.
 */
function tml_get_previous_version() {
	return get_site_option( '_tml_previous_version' );
}

/**
 * Determine whether to use permalinks or not.
 *
 * @since 7.0
 *
 * @return bool Whether to use permalinks or not.
 */
function tml_use_permalinks() {
	global $wp_rewrite;

	if ( ! $wp_rewrite instanceof WP_Rewrite ) {
		$wp_rewrite = new WP_Rewrite;
	}

	$use_permalinks = $wp_rewrite->using_permalinks() && get_site_option( 'tml_use_permalinks', true );

	/**
	 * Filter whether to use permalinks or not.
	 *
	 * @since 7.0
	 *
	 * @param bool $use_permalinks Whether to use permalinks or not.
	 */
	return (bool) apply_filters( 'tml_use_permalinks', $use_permalinks );
}

/**
 * Determine if forms should use AJAX or not.
 *
 * @since 7.1.1
 *
 * @return bool Whether forms should use AJAX or not.
 */
function tml_use_ajax() {
	$use_ajax = get_site_option( 'tml_ajax', 1 );

	/**
	 * Filter whether forms should use AJAX or not.
	 *
	 * @since 7.1.1
	 *
	 * @param bool $enable_ajax Whether forms should use AJAX or not.
	 */
	return (bool) apply_filters( 'tml_use_ajax', $use_ajax );
}

/**
 * Get the login type.
 *
 * @since 7.0
 *
 * @return string The login type.
 */
function tml_get_login_type() {
	$login_type = get_site_option( 'tml_login_type', 'default' );

	/**
	 * Filter the login type.
	 *
	 * @since 7.0
	 *
	 * @param string $login_type The login type.
	 */
	return apply_filters( 'tml_get_login_type', $login_type );
}

/**
 * Determine if using the default login type.
 *
 * @since 7.0
 *
 * @return bool Whether using the default login type or not.
 */
function tml_is_default_login_type() {
	return 'default' == tml_get_login_type();
}

/**
 * Determine if using the email login type.
 *
 * @since 7.0
 *
 * @return bool Whether using the email login type or not.
 */
function tml_is_email_login_type() {
	return 'email' == tml_get_login_type();
}

/**
 * Determine if using the username login type.
 *
 * @since 7.0
 *
 * @return bool Whether using the username login type or not.
 */
function tml_is_username_login_type() {
	return 'username' == tml_get_login_type();
}

/**
 * Get the registration type.
 *
 * @since 7.0
 *
 * @return string The registration type.
 */
function tml_get_registration_type() {
	$registration_type = get_site_option( 'tml_registration_type', 'default' );

	/**
	 * Filter the registration type.
	 *
	 * @since 7.0
	 *
	 * @param string $registration_type The registration type.
	 */
	return apply_filters( 'tml_get_registration_type', $registration_type );
}

/**
 * Determine if using the default registration type.
 *
 * @since 7.0
 *
 * @return bool Whether using the default registration type or not.
 */
function tml_is_default_registration_type() {
	return 'default' == tml_get_registration_type();
}

/**
 * Determine if using the email registration type.
 *
 * @since 7.0
 *
 * @return bool Whether using the email registration type or not.
 */
function tml_is_email_registration_type() {
	return 'email' == tml_get_registration_type();
}

/**
 * Determine if users can set their own password upon registration or not.
 *
 * @since 7.0
 *
 * @return bool Whether users can set their own password upon registration or not.
 */
function tml_allow_user_passwords() {
	$user_passwords = (bool) get_site_option( 'tml_user_passwords' );

	/**
	 * Filter whether users can set their own password upon registration or not.
	 *
	 * @since 7.0
	 *
	 * @param bool $user_passwords Whether users can set their own password upon registration or not.
	 */
	return (bool) apply_filters( 'tml_allow_user_passwords', $user_passwords );
}

/**
 * Determine if users should be logged in after registration or not.
 *
 * @since 7.0
 *
 * @return bool Whether users should be logged in after registration or not.
 */
function tml_allow_auto_login() {
	$auto_login = get_site_option( 'tml_auto_login' );

	/**
	 * Filter whether users should be logged in after registration or not.
	 *
	 * @since 7.0
	 *
	 * @param bool $auto_login Whether users should be logged in after registration or not.
	 */
	return (bool) apply_filters( 'tml_allow_auto_login', $auto_login );
}
