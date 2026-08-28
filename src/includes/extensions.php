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
 * @param array                           $args {
 *     Optional. An array of arguments for registering an extension.
 * }
 * @return Theme_My_Login_Extension The extension object.
 */
function tml_register_extension( $extension, $args = array() ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed -- kept for API/documented-signature consistency with tml_register_action()/tml_register_form().

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
	 * @param bool   $exists    Whether the extension exists or not.
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

	if ( 'plugin_information' !== $action ) {
		return $result;
	}

	$extension = tml_get_extension( $args->slug );
	if ( ! $extension ) {
		return $result;
	}

	$result = tml_extension_api_call(
		$extension->get_store_url(),
		array(
			'license' => $extension->get_license_key(),
			'item_id' => $extension->get_item_id(),
			'slug'    => $extension->get_name(),
			'url'     => home_url(),
		)
	);
	if ( $result ) {
		if ( empty( $result->version ) && ! empty( $result->new_version ) ) {
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
		$response = tml_extension_api_call(
			$extension->get_store_url(),
			array(
				'license' => $extension->get_license_key(),
				'item_id' => $extension->get_item_id(),
				'slug'    => $extension->get_name(),
				'url'     => home_url(),
			)
		);

		if ( is_object( $response ) ) {
			$basename = $extension->get_basename();

			$update = (object) array(
				'slug'             => $extension->get_name(),
				'plugin'           => $basename,
				'new_version'      => isset( $response->new_version ) ? $response->new_version : ( isset( $response->version ) ? $response->version : '' ),
				'url'              => isset( $response->url ) ? $response->url : ( isset( $response->homepage ) ? $response->homepage : '' ),
				'package'          => isset( $response->package ) ? $response->package : ( isset( $response->download_link ) ? $response->download_link : '' ),
				'icons'            => isset( $response->icons ) ? $response->icons : array(),
				'banners'          => isset( $response->banners ) ? $response->banners : array(),
				'banners_rtl'      => isset( $response->banners_rtl ) ? $response->banners_rtl : array(),
				'requires'         => isset( $response->requires ) ? $response->requires : '',
				'tested'           => isset( $response->tested ) ? $response->tested : '',
				'requires_php'     => isset( $response->requires_php ) ? $response->requires_php : '',
				'requires_plugins' => isset( $response->requires_plugins ) ? $response->requires_plugins : array(),
			);

			if ( ! empty( $response->upgrade_notice ) ) {
				$update->upgrade_notice = $response->upgrade_notice;
			}

			// This is a valid update
			if ( ! empty( $update->new_version ) && version_compare( $extension->get_version(), $update->new_version, '<' ) ) {
				$transient->response[ $basename ] = $update;
			} else {
				$transient->no_update[ $basename ] = $update;
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
	$extension = tml_get_extension( $extension );
	if ( ! $extension ) {
		return false;
	}

	$response = tml_extension_api_call(
		$extension->get_store_url(),
		array(
			'edd_action' => 'activate_license',
			'license'    => $extension->get_license_key(),
			'item_id'    => $extension->get_item_id(),
		)
	);

	if ( empty( $response ) ) {
		return new WP_Error( 'http_error', __( 'An error occurred, please try again.', 'theme-my-login' ) );
	}

	if ( false === $response->success ) {
		switch ( $response->error ) {
			case 'expired':
				$message = sprintf(
					/* translators: %s: License expiration date. */
					__( 'Your license key expired on %s.', 'theme-my-login' ),
					date_i18n( get_option( 'date_format' ), strtotime( $response->expires, time() ) )
				);
				break;

			case 'disabled':
				$message = __( 'Your license key has been disabled.', 'theme-my-login' );
				break;

			case 'missing':
			case 'missing_url':
			case 'key_mismatch':
			case 'invalid_item_id':
			case 'item_name_mismatch':
				$message = __( 'Invalid license.', 'theme-my-login' );
				break;

			case 'no_activations_left':
				$message = __( 'Your license key has reached its activation limit.', 'theme-my-login' );
				break;

			case 'license_not_activable':
				$message = __( 'You are attempting to activate a bundle license. Please use the license that corresponds to the individual extension you are attemtping to activate.', 'theme-my-login' );
				break;

			default:
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
	$extension = tml_get_extension( $extension );
	if ( ! $extension ) {
		return false;
	}

	$response = tml_extension_api_call(
		$extension->get_store_url(),
		array(
			'edd_action' => 'deactivate_license',
			'license'    => $extension->get_license_key(),
			'item_id'    => $extension->get_item_id(),
		)
	);

	if ( empty( $response ) ) {
		return new WP_Error( 'http_error', __( 'An error occurred, please try again.', 'theme-my-login' ) );
	}

	if ( false === $response->success ) {
		return new WP_Error(
			'deactivation_failed',
			sprintf(
				/* translators: %1$s: URL to deactivate the license on thememylogin.com. */
				__( 'Unable to deactivate license. Please deactivate it on <a href="%1$s" target="_blank">our site</a>.', 'theme-my-login' ),
				'https://thememylogin.com/your-account/?action=license-keys'
			)
		);
	}

	return $response->license;
}

/**
 * Check the status of an extension license.
 *
 * @since 7.0.8
 *
 * @param string|Theme_My_Login_Extension $extension The extension name or object.
 * @return bool|string|WP_Error The license status on success, false if the
 *                              extension doesn't exist or WP_Error on failure.
 */
function tml_check_extension_license( $extension ) {
	$extension = tml_get_extension( $extension );
	if ( ! $extension ) {
		return false;
	}

	$response = tml_extension_api_call(
		$extension->get_store_url(),
		array(
			'edd_action' => 'check_license',
			'license'    => $extension->get_license_key(),
			'item_id'    => $extension->get_item_id(),
			'url'        => home_url(),
		)
	);

	if ( empty( $response ) ) {
		return new WP_Error( 'http_error', __( 'An error occurred, please try again.', 'theme-my-login' ) );
	}

	return $response->license;
}

/**
 * Make an API call the store of an extension.
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
 *     @param string $url        The client WP URL.
 *     @param bool   $beta       Whether to include beta versions or not.
 * }
 * @return object|false The response object or false on failure.
 */
function tml_extension_api_call( $url, $args = array() ) {
	$args = wp_parse_args(
		$args,
		array(
			'edd_action' => 'get_version',
			'license'    => '',
			'item_id'    => '',
			'slug'       => '',
			'url'        => '',
			'beta'       => false,
		)
	);

	// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.serialize_serialize -- one-way hash input for a cache key, never unserialized; no object-injection risk.
	$cache_key = md5( serialize( $args ) );

	$response = wp_cache_get( $cache_key, 'tml_api_calls' );
	if ( ! $response ) {
		$response = wp_remote_post(
			$url,
			array(
				'timeout'   => 30,
				'sslverify' => true,
				'body'      => $args,
			)
		);

		if ( is_wp_error( $response ) || 200 !== (int) wp_remote_retrieve_response_code( $response ) ) {
			return false;
		}

		$response = json_decode( wp_remote_retrieve_body( $response ), true );
		if ( is_array( $response ) ) {
			$response = (object) $response;
		}

		if ( is_object( $response ) ) {
			if ( isset( $response->sections ) ) {
				$response->sections = maybe_unserialize( $response->sections );
			}
			if ( isset( $response->banners ) ) {
				$response->banners = maybe_unserialize( $response->banners );
			}
			if ( isset( $response->icons ) ) {
				$response->icons = maybe_unserialize( $response->icons );
			}
		} else {
			$response = false;
		}

		wp_cache_set( $cache_key, $response, 'tml_api_calls' );
	}

	return $response;
}
