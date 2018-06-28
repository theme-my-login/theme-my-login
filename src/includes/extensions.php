<?php

/**
 * Theme My Login Extension Functions
 *
 * @package Theme_My_Login
 * @subpackage Extensions
 */

/**
 * Register an extension.
 *
 * @since 7.0
 *
 * @param string|Theme_My_Login_Extension $extension The extension name or object.
 * @param array                        $args {
 *     Optional. An array of arguments for registering an extension.
 * }
 * @return Theme_My_Login_Extension The extension object.
 */
function tml_register_extension( $extension, $args = array() ) {

	if ( ! $extension instanceof Theme_My_Login_Extension ) {
		return false;
	}

	return theme_my_login()->register_extension( $extension );
}

/**
 * Unregister an extension.
 *
 * @since 7.0
 *
 * @param string|Theme_My_Login_Extension $extension The extension name or object.
 */
function tml_unregister_extension( $extension ) {
	theme_my_login()->unregister_extension( $extension );
}

/**
 * Get an extension.
 *
 * @since 7.0
 *
 * @param string|Theme_My_Login_Extension $extension The extension name or object.
 * @return Theme_My_Login_Extension|bool The extension object if it exists or false otherwise.
 */
function tml_get_extension( $extension ) {

	if ( $extension instanceof Theme_My_Login_Extension ) {
		return $extension;
	}

	return theme_my_login()->get_extension( $extension );
}

/**
 * Get all extensions.
 *
 * @since 7.0
 *
 * @return array The extensions.
 */
function tml_get_extensions() {
	return theme_my_login()->get_extensions();
}

/**
 * Determine if an extension exists.
 *
 * @since 7.0
 *
 * @param string $extension The extension name.
 * @return bool True if the extension exists or false otherwise.
 */
function tml_extension_exists( $extension ) {
	$exists = array_key_exists( $extension, tml_get_extensions() );

	/**
	 * Filter whether an extension exists or not.
	 *
	 * @since 7.0
	 *
	 * @param bool   $exists Whether the extension exists or not.
	 * @param string $extension The extension name.
	 */
	return apply_filters( 'tml_extension_exists', $exists, $extension );
}

/**
 * Add extensions hosted using EDD to the WP plugins API.
 *
 * @since 7.0
 *
 * @param false|object|array $result The result object or array.
 * @param string             $action The API action being requested.
 * @param object             $args   The arguments being passed to the API.
 */
function tml_add_extension_data_to_plugins_api( $result = false, $action = '', $args = array() ) {

	// Bail if not a "plugin_information" call
	if ( 'plugin_information' != $action ) {
		return $result;
	}

	// Bail if the extension doesn't exist
	if ( ! $extension = tml_get_extension( $args->slug ) ) {
		return $result;
	}

	if ( $result = tml_extension_api_call( $extension->get_store_url(), array(
		'license' => $extension->get_license_key(),
		'item_id' => $extension->get_item_id(),
		'slug'    => $extension->get_name(),
	) ) ) {
		if ( ! empty( $result->new_version ) ) {
			$result->version = $result->new_version;
		}
	}

	return $result;
}

/**
 * Add extensions hosted using EDD to the WP plugins transient.
 *
 * @since 7.0
 *
 * @param object $transient The transient data.
 * @return object The transient data.
 */
function tml_add_extension_data_to_plugins_transient( $transient = '' ) {
	if ( ! is_object( $transient ) ) {
		$transient = (object) array();
	}

	foreach ( tml_get_extensions() as $extension ) {
		$response = tml_extension_api_call( $extension->get_store_url(), array(
			'license' => $extension->get_license_key(),
			'item_id' => $extension->get_item_id(),
			'slug'    => $extension->get_name(),
		) );

		if ( is_object( $response ) ) {
			$basename = $extension->get_basename();

			if ( empty( $response->plugin ) ) {
				$response->plugin = $basename;
			}

			// This is a valid update
			if ( ! empty( $response->new_version ) && version_compare( $extension->get_version(), $response->new_version, '<' ) ) {
				$transient->response[ $basename ] = $response;

			// This is just fetching the plugin information
			} else {
				$transient->no_update[ $basename ] = $response;
			}

			$transient->last_checked = time();
		}
	}

	return $transient;
}

/**
 * Activate an extension license.
 *
 * @since 7.0
 *
 * @param string|Theme_My_Login_Extension $extension The extension name or object.
 * @return bool|string|WP_Error The license status on success, false if the
 *                              extension doesn't exist or WP_Error on failure.
 */
function tml_activate_extension_license( $extension ) {
	if ( ! $extension = tml_get_extension( $extension ) ) {
		return false;
	}

	$response = tml_extension_api_call( $extension->get_store_url(), array(
		'edd_action' => 'activate_license',
		'license'    => $extension->get_license_key(),
		'item_id'    => $extension->get_item_id(),
	) );

	if ( empty( $response ) ) {
		return new WP_Error( 'http_error', __( 'An error occurred, please try again.', 'theme-my-login' ) );
	}

	if ( false === $response->success ) {
		switch ( $response->error ) {
			case 'expired' :
				$message = sprintf(
					__( 'Your license key expired on %s.', 'theme_my_login' ),
					date_i18n( get_option( 'date_format' ), strtotime( $license_data->expires, current_time( 'timestamp' ) ) )
				);
				break;

			case 'revoked' :
				$message = __( 'Your license key has been disabled.', 'theme-my-login' );
				break;

			case 'missing' :
			case 'item_name_mismatch' :
				$message = __( 'Invalid license.', 'theme-my-login' );
				break;

			case 'invalid' :
			case 'site_inactive' :
				$message = __( 'Your license is not active for this URL.', 'theme-my-login' );
				break;

			case 'no_activations_left':
				$message = __( 'Your license key has reached its activation limit.', 'theme-my-login' );
				break;

			default :
				$message = __( 'An error occurred, please try again.', 'theme-my-login' );
				break;
		}
		return new WP_Error( $response->error, $message );
	}

	return $response->license;
}

/**
 * Deactivate an extension license.
 *
 * @since 7.0
 *
 * @param string|Theme_My_Login_Extension $extension The extension name or object.
 * @return bool|string|WP_Error The license status on success, false if the
 *                              extension doesn't exist or WP_Error on failure.
 */
function tml_deactivate_extension_license( $extension ) {
	if ( ! $extension = tml_get_extension( $extension ) ) {
		return false;
	}

	$response = tml_extension_api_call( $extension->get_store_url(), array(
		'edd_action' => 'deactivate_license',
		'license'    => $extension->get_license_key(),
		'item_id'    => $extension->get_item_id(),
	) );

	if ( empty( $response ) ) {
		return new WP_Error( 'http_error', __( 'An error occurred, please try again.', 'theme-my-login' ) );
	}

	return $response->license;
}

/**
 * Check an extenstion's license status.
 *
 * @since 7.0.8
 *
 * @param string|Theme_My_Login_Extension $extension The extension name or object.
 * @return bool|string|WP_Error The license status on success, false if the
 *                              extension doesn't exist or WP_Error on failure.
 */
function tml_check_extension_license( $extension ) {
	if ( ! $extension = tml_get_extension( $extension ) ) {
		return false;
	}

	$response = tml_extension_api_call( $extension->get_store_url(), array(
		'edd_action' => 'check_license',
		'license'    => $extension->get_license_key(),
		'item_id'    => $extension->get_item_id(),
		'url'        => home_url(),
	) );

	if ( empty( $response ) ) {
		return new WP_Error( 'http_error', __( 'An error occurred, please try again.', 'theme-my-login' ) );
	}

	return $response->license;
}

/**
 * Make an API call to an extension's store.
 *
 * @since 7.0
 *
 * @param string $url  The store URL.
 * @param array  $args {
 *     Optional. An array of arguments for making an API call.
 *
 *     @param string $edd_action The API action.
 *     @param string $license    The extension license key.
 *     @param int    $item_id    The extension item ID.
 *     @param string $slug       The extension slug.
 *     @param string $url
 *     @param bool   $beta       Whether to include beta versions or not.
 * }
 * @return object|false The response object or false on failure.
 */
function tml_extension_api_call( $url, $args = array() ) {
	$args = wp_parse_args( $args, array(
		'edd_action' => 'get_version',
		'license'    => '',
		'item_id'    => '',
		'slug'       => '',
		'url'        => '',
		'beta'       => false,
	) );

	$response = wp_remote_post( $url, array(
		'timeout'   => 30,
		'sslverify' => true,
		'body'      => $args,
	) );

	if ( is_wp_error( $response ) || 200 != wp_remote_retrieve_response_code( $response ) ) {
		return false;
	}

	$response = json_decode( wp_remote_retrieve_body( $response ) );

	if ( is_object( $response ) ) {
		if ( isset( $response->sections ) ) {
			$response->sections = maybe_unserialize( $response->sections );
		}
		if ( isset( $response->banners ) ) {
			$response->banners = maybe_unserialize( $response->banners );
		}
	} else {
		$response = false;
	}

	return $response;
}
