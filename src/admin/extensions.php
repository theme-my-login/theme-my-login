<?php

/**
 * Theme MY Login Extensions Admin
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

	$url = add_query_arg( $args, THEME_MY_LOGIN_EXTENSIONS_API_URL );

	$response = wp_remote_get( $url );
	if ( is_wp_error( $response ) ) {
		return $response;
	}

	$code    = wp_remote_retrieve_response_code( $response );
	$message = wp_remote_retrieve_response_message( $response );

	if ( '200' != $code ) {
		return new WP_Error( 'http_error_' . $code, $message );
	}

	$response = json_decode( wp_remote_retrieve_body( $response ) );

	return $response->products;
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
 * Render the extensions styles.
 *
 * @since 7.0
 */
function tml_admin_extensions_styles() {
	global $plugin_page;

	if ( 'theme-my-login-extensions' != $plugin_page ) {
		return;
	}
	?>

	<style type="text/css">
		.tml-extensions-wrap {
			margin: 0 -15px;
		}

		.tml-extensions-wrap:after {
			content: "";
			clear: both;
			display: table;
		}

		.tml-extensions-wrap * {
			box-sizing: border-box;
		}

		.tml-extension {
			background-color: #fff;
			border: 1px solid #ccc;
			box-shadow: 0 0 5px rgba(0, 0, 0, 0.15);
			float: left;
			margin: 15px;
		}

		.tml-extension-image {
			height: auto;
			max-width: 100%;
		}

		.tml-extension-body {
			padding: 15px;
		}

		.tml-extension-title {
			margin: 0 0 15px;
			padding: 0;
		}

		.tml-extension-button {
			background-color: #8d50c3;
			color: #fff;
			display: block;
			font-size: 1.1em;
			padding: 10px;
			text-align: center;
			text-decoration: none;
		}

		.tml-extension-button:hover {
			color: #fff;
			background-color: #7a3cb0;
		}

		.tml-extension-button:active,
		.tml-extension-button:focus {
			box-shadow: 0 0 0 0.2em rgba(141, 80, 195, 0.5);
			color: #fff;
		}

		.tml-view-all-extensions-wrap {
			padding: 15px 0;
			text-align: center;
		}

		.tml-view-all-extensions-link {
			display: inline-block;
			font-size: 1.5em;
			text-decoration: none;
		}

		@media (min-width: 576px) {
			.tml-extension {
				width: 40%;
			}
		}

		@media (min-width: 783px) {
			.tml-extension {
				width: 30%;
			}
		}
	</style>

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
