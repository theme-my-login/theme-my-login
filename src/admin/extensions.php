<?php

/**
 * Theme My Login Extensions Admin
 *
 * @package Theme_My_Login
 * @subpackage Administration
 */

/**
 * Get the extensions feed.
 *
 * @since 7.0
 *
 * @param array $args {
 *     Optional. An array of arguments for fetching extensions from the server.
 * }
 * @return array|WP_Error The extensions array or WP_Error on failure.
 */
function tml_admin_get_extensions_feed( $args = array() ) {
	$args = wp_parse_args( $args, array(
		'number' => 12,
	) );

	$transient_key = 'tml_extensions_feed-' . md5( http_build_query( $args ) );

	$feed = get_site_transient( $transient_key );
	if ( false === $feed ) {
		$url = add_query_arg( $args, THEME_MY_LOGIN_EXTENSIONS_API_URL );

		$response = wp_remote_get( $url, array(
			'timeout' => 30,
		) );
		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$code    = wp_remote_retrieve_response_code( $response );
		$message = wp_remote_retrieve_response_message( $response );

		if ( '200' != $code ) {
			return new WP_Error( 'http_error_' . $code, $message );
		}

		$response = json_decode( wp_remote_retrieve_body( $response ) );

		$feed = $response->products;

		set_site_transient( $transient_key, $feed, DAY_IN_SECONDS / 2 );
	}
	return $feed;
}

/**
 * Render the extensions page.
 *
 * @since 7.0
 */
function tml_admin_extensions_page() {
	global $title, $plugin_page;

	$extensions = tml_admin_get_extensions_feed();
?>

<div class="wrap">
	<h1><?php echo esc_html( $title ) ?></h1>
	<hr class="wp-header-end">

	<?php if ( is_wp_error( $extensions ) ) : ?>

		<h3><?php echo esc_html_e( 'Whoops! Looks like there was an error fetching extensions from the server. Please try again.', 'theme-my-login' ); ?></h3>
		<p><?php echo esc_html( sprintf( __( 'Error: %s', 'theme-my-login' ), $extensions->get_error_message() ) ); ?></p>

	<?php else : ?>

		<div class="tml-extensions-wrap">
			<?php foreach ( $extensions as $extension ) : ?>

				<div class="tml-extension">
					<div class="tml-extension-header">
						<?php if ( ! empty( $extension->info->thumbnail ) ) : ?>
							<a href="<?php echo esc_url( $extension->info->link ); ?>">
								<img class="tml-extension-image" src="<?php echo esc_url( $extension->info->thumbnail ); ?>" />
							</a>
						<?php endif; ?>
					</div>
					<div class="tml-extension-body">
						<h2 class="tml-extension-title"><?php echo esc_html( $extension->info->title ); ?></h2>

						<?php if ( ! empty( $extension->info->excerpt ) ) : ?>
							<p><?php echo esc_html( $extension->info->excerpt ); ?></p>
						<?php endif; ?>

						<a class="tml-extension-button" href="<?php echo esc_url( $extension->info->link ); ?>"><?php esc_html_e( 'Get This Extension', 'theme-my-login' ); ?></a>
					</div>
				</div>

			<?php endforeach; ?>
		</div>

	<?php endif; ?>

	<div class="tml-view-all-extensions-wrap">
		<a class="tml-view-all-extensions-link" href="<?php echo THEME_MY_LOGIN_EXTENSIONS_URL; ?>"><?php esc_html_e( 'View All Extensions', 'theme-my-login' ); ?></a>
	</div>
</div>

<?php
}

/**
 * Handle extension license activation and deactivation.
 *
 * @since 7.0
 */
function tml_admin_handle_extension_licenses() {

	if ( ! tml_is_post_request() ) {
		return;
	}

	if ( tml_get_request_value('option_page') !== 'theme-my-login-licenses' ) {
		return;
	}

	check_admin_referer( 'theme-my-login-licenses-options' );

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( __( 'Sorry, you are not allowed to manage options for this site.' ) );
	}

	// Loop through all extensions
	foreach ( tml_get_extensions() as $extension ) {

		// Handle license activations
		if ( isset( $_POST['tml_activate_license'][ $extension->get_name() ] ) ) {
			if ( $response = tml_activate_extension_license( $extension ) ) {
				if ( is_wp_error( $response ) ) {
					$extension->set_license_status();
					add_settings_error( 'tml_activate_license',
						$response->get_error_code(),
						$response->get_error_message()
					);
				} else {
					$extension->set_license_status( $response );
				}
			}
		}

		// Handle license deactivations
		if ( isset( $_POST['tml_deactivate_license'][ $extension->get_name() ] ) ) {
			if ( $response = tml_deactivate_extension_license( $extension ) ) {
				if ( is_wp_error( $response ) ) {
					add_settings_error( 'tml_deactivate_license',
						$response->get_error_code(),
						$response->get_error_message()
					);
				} else {
					$extension->set_license_status();
				}
			}
		}
	}
}

/**
 * Handle AJAX activation of extension licenses.
 *
 * @since 7.1
 */
function tml_admin_ajax_activate_extension_license() {
	if ( ! $extension = tml_get_extension( tml_get_request_value( 'extension', 'post' ) ) ) {
		tml_send_ajax_error( __( 'Invalid extension.', 'theme-my-login' ) );
	}

	if ( ! current_user_can( 'manage_options' ) ) {
		tml_send_ajax_error( __( 'Sorry, you are not allowed to manage options for this site.' ) );
	}

	$extension->set_license_key( tml_get_request_value( 'key', 'post' ) );

	$response = tml_activate_extension_license( $extension );

	if ( is_wp_error( $response ) ) {
		$extension->set_license_key();
		$extension->set_license_status();

		tml_send_ajax_error( $response->get_error_message() );
	}

	$extension->set_license_status( $response );

	tml_send_ajax_success( __( 'Active', 'theme-my-login' ) );
}

/**
 * Handle AJAX deactivation of extension licenses.
 *
 * @since 7.1
 */
function tml_admin_ajax_deactivate_extension_license() {
	if ( ! $extension = tml_get_extension( tml_get_request_value( 'extension', 'post' ) ) ) {
		tml_send_ajax_error( __( 'Invalid extension.', 'theme-my-login' ) );
	}

	if ( ! current_user_can( 'manage_options' ) ) {
		tml_send_ajax_error( __( 'Sorry, you are not allowed to manage options for this site.' ) );
	}

	$response = tml_deactivate_extension_license( $extension );

	if ( is_wp_error( $response ) ) {
		tml_send_ajax_error( $response->get_error_message() );
	}

	$extension->set_license_status();

	tml_send_ajax_success( __( 'Inactive', 'theme-my-login' ) );
}

/**
 * Check that all of the licenses are valid.
 *
 * @since 7.0.8
 */
function tml_admin_check_extension_licenses() {
	global $plugin_page;

	if ( tml_is_post_request() ) {
		return;
	}

	if ( 'theme-my-login-licenses' != $plugin_page ) {
		return;
	}

	foreach ( tml_get_extensions() as $extension ) {
		if ( ! $extension->get_license_key() ) {
			continue;
		}
		if ( 'valid' != $extension->get_license_status() ) {
			continue;
		}
		$status = tml_check_extension_license( $extension );
		if ( ! is_wp_error( $status ) ) {
			$extension->set_license_status( $status );
		}
	}
}

/**
 * Add the extension update messages.
 *
 * @since 7.0.12
 */
function tml_admin_add_extension_update_messages() {
	foreach ( tml_get_extensions() as $extension ) {
		add_action( 'in_plugin_update_message-' . $extension->get_basename(), 'tml_admin_extension_update_message', 10, 2 );
	}
}

/**
 * Display the extension update message.
 *
 * @since 7.0.12
 */
function tml_admin_extension_update_message( $data, $response ) {
	if ( empty( $response->package ) ) {
		echo ' ' . sprintf(
			'<em>In order to enable automatic updates, please enter and activate your <a href="%1$s">license key</a>.',
			admin_url( 'admin.php?page=theme-my-login-licenses' )
		);
	}
}
