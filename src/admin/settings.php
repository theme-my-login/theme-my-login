<?php

/**
 * Theme My Login Admin Settings
 *
 * @package Theme_My_Login
 * @subpackage Administration
 */

/**
 * Register the settings.
 *
 * @since 7.0
 */
function tml_admin_register_settings() {

	$settings = array(
		'theme-my-login' => array(
			'sections' => tml_admin_get_settings_sections(),
			'fields'   => tml_admin_get_settings_fields(),
		),
	);

	foreach ( tml_get_extensions() as $extension ) {
		$settings[ $extension->get_name() ] = array(
			'sections' => (array) $extension->get_settings_sections(),
			'fields'   => (array) $extension->get_settings_fields(),
		);

		if ( $extension->get_license_key_option() ) {
			add_settings_field(
				$extension->get_license_key_option(),
				$extension->get_title(),
				'tml_admin_setting_callback_license_key_field',
				'theme-my-login-licenses',
				'tml_settings_licenses',
				array(
					'extension' => $extension,
					'label_for' => $extension->get_license_key_option(),
				)
			);
			register_setting(
				'theme-my-login-licenses',
				$extension->get_license_key_option(),
				'sanitize_text_field'
			);
		}
	}

	// Loop through settings
	foreach ( $settings as $group ) {

		// Loop through sections
		foreach ( $group['sections'] as $section_id => $section ) {

			// Only add section and fields if section has fields
			if ( empty( $group['fields'][ $section_id ] ) ) {
				continue;
			}

			$page = ! empty( $section['page'] ) ? $section['page'] : 'theme-my-login';

			// Add the section
			add_settings_section( $section_id, $section['title'], $section['callback'], $page );

			// Loop through fields for this section
			foreach ( $group['fields'][ $section_id ] as $field_id => $field ) {

				// Add the field
				if ( ! empty( $field['callback'] ) && ! empty( $field['title'] ) ) {
					add_settings_field( $field_id, $field['title'], $field['callback'], $page, $section_id, isset( $field['args'] ) ? $field['args'] : array() );
				}

				// Register the setting
				register_setting( $page, $field_id, $field['sanitize_callback'] );
			}
		}
	}
}

/**
 * Get the settings sections.
 *
 * @since 7.0
 *
 * @return array The settings sections.
 */
function tml_admin_get_settings_sections() {
	/**
	 * Filters the settings sections.
	 *
	 * @since 7.0
	 *
	 * @param array $sections The settings sections.
	 */
	return (array) apply_filters( 'tml_admin_get_settings_sections', array(
		'tml_settings_general' => array(
			'title'    => __( 'General', 'theme-my-login' ),
			'callback' => '__return_null',
			'page'     => 'theme-my-login',
		),
		'tml_settings_login' => array(
			'title'    => __( 'Log In' ),
			'callback' => '__return_null',
			'page'     => 'theme-my-login',
		),
		'tml_settings_registration' => array(
			'title'    => __( 'Registration', 'theme-my-login' ),
			'callback' => '__return_null',
			'page'     => 'theme-my-login',
		),
		'tml_settings_slugs' => array(
			'title'    => __( 'Slugs', 'theme-my-login' ),
			'callback' => 'tml_admin_setting_callback_slugs_section',
			'page'     => 'theme-my-login',
		),
	) );
}

/**
 * Get the settings fields.
 *
 * @since 7.0
 *
 * @return array The settings fields.
 */
function tml_admin_get_settings_fields() {
	$fields = array();

	// General
	$fields['tml_settings_general'] = array(
		// AJAX
		'tml_ajax' => array(
			'title'             => __( 'AJAX', 'theme-my-login' ),
			'callback'          => 'tml_admin_setting_callback_checkbox_field',
			'sanitize_callback' => 'sanitize_text_field',
			'args' => array(
				'label_for' => 'tml_ajax',
				'label'     => __( 'Enable AJAX requests', 'theme-my-login' ),
				'value'     => '1',
				'checked'   => get_site_option( 'tml_ajax', '1' ),
			),
		),
	);

	// Login
	$fields['tml_settings_login'] = array(
		// Login type
		'tml_login_type' => array(
			'title'             => __( 'Login Type', 'theme-my-login' ),
			'callback'          => 'tml_admin_setting_callback_radio_group_field',
			'sanitize_callback' => 'sanitize_text_field',
			'args' => array(
				'label_for' => 'tml_login_type',
				'legend'    => __( 'Login Type', 'theme-my-login' ),
				'options'   => array(
					'default'  => __( 'Default',       'theme-my-login' ),
					'username' => __( 'Username only', 'theme-my-login' ),
					'email'    => __( 'Email only',    'theme-my-login' ),
				),
				'checked'   => get_site_option( 'tml_login_type', 'default' ),
			),
		),
	);

	// Registration
	$fields['tml_settings_registration'] = array(
		// Registration type
		'tml_registration_type' => array(
			'title'             => __( 'Registration Type', 'theme-my-login' ),
			'callback'          => 'tml_admin_setting_callback_radio_group_field',
			'sanitize_callback' => 'sanitize_text_field',
			'args' => array(
				'label_for' => 'tml_registration_type',
				'legend'    => __( 'Registration Type', 'theme-my-login' ),
				'options'   => array(
					'default'  => __( 'Default',    'theme-my-login' ),
					'email'    => __( 'Email only', 'theme-my-login' ),
				),
				'checked'   => get_site_option( 'tml_registration_type', 'default' ),
			),
		),
		// User passwords
		'tml_user_passwords' => array(
			'title'             => __( 'Passwords', 'theme-my-login' ),
			'callback'          => 'tml_admin_setting_callback_checkbox_field',
			'sanitize_callback' => 'sanitize_text_field',
			'args' => array(
				'label_for' => 'tml_user_passwords',
				'label'     => __( 'Allow users to set their own password', 'theme-my-login' ),
				'value'     => '1',
				'checked'   => get_site_option( 'tml_user_passwords' ),
			),
		),
		// Auto-login
		'tml_auto_login' => array(
			'title'             => __( 'Auto-Login', 'theme-my-login' ),
			'callback'          => 'tml_admin_setting_callback_checkbox_field',
			'sanitize_callback' => 'sanitize_text_field',
			'args' => array(
				'label_for' => 'tml_auto_login',
				'label'     => __( 'Automatically log in users after registration', 'theme-my-login' ),
				'value'     => '1',
				'checked'   => get_site_option( 'tml_auto_login' ),
			),
		),
	);

	// Slugs
	$fields['tml_settings_slugs'] = array();
	foreach ( tml_get_actions() as $action ) {
		if ( ! $action->show_in_slug_settings ) {
			continue;
		}

		$slug_option = 'tml_' . $action->get_name() . '_slug';

		$fields['tml_settings_slugs'][ $slug_option ] = array(
			'title'             => $action->get_title(),
			'callback'          => 'tml_admin_setting_callback_input_field',
			'sanitize_callback' => 'sanitize_text_field',
			'args' => array(
				'label_for'   => $slug_option,
				'value'       => get_site_option( $slug_option, $action->get_slug() ),
				'input_class' => 'regular-text code',
				'description' => sprintf( '<a href="%1$s">%1$s</a>', $action->get_url() ),
			),
		);
	}

	/**
	 * Filters the settings fields.
	 *
	 * @since 7.0
	 *
	 * @param array $fields The settings fields.
	 */
	return (array) apply_filters( 'tml_admin_get_settings_fields', $fields );
}

/**
 * Render the "Slugs" section.
 *
 * @since 7.0.5
 */
function tml_admin_setting_callback_slugs_section() {
?>

<p><?php esc_html_e( 'The slugs defined here will be used to generate the URL to the corresponding action. You can see this URL below the slug field. If you would like to use pages for these actions, simply make sure the slug for the action below matches the slug of the page you would like to use for that action.', 'theme-my-login' ); ?></p>

<?php
}

/**
 * Render an input setting field.
 *
 * @since 7.0
 *
 * @param array $args {
 *     Optional. An array of arguments for rendering an input.
 *
 *     @type string $label_for   The input name/ID.
 *     @type string $value       The input value.
 *     @type string $description A short description of the input.
 *     @type string $input_type  The type of input. Default is "text".
 *     @type string $input_class The input class. Default is "regular-text".
 * }
 */
function tml_admin_setting_callback_input_field( $args ) {
	$args = wp_parse_args( $args, array(
		'label_for'   => '',
		'value'       => '',
		'description' => '',
		'input_type'  => 'text',
		'input_class' => 'regular-text',
	) );
?>

	<input type="<?php echo esc_attr( $args['input_type'] ); ?>" name="<?php echo esc_attr( $args['label_for'] ); ?>" id="<?php echo esc_attr( $args['label_for'] ); ?>" value="<?php echo esc_attr( $args['value'] ); ?>" class="<?php echo esc_attr( $args['input_class'] ); ?>" />

	<?php if ( ! empty( $args['description'] ) ) : ?>
		<p class="description"><?php echo $args['description']; ?></p>
	<?php endif; ?>

<?php
}

/**
 * Render a checkbox setting field.
 *
 * @since 7.0
 *
 * @param array $args {
 *     Optional. An array of arguments for rendering a checkbox.
 *
 *     @type string $label_for   The checkbox name/ID.
 *     @type stirng $label       The checkbox label text.
 *     @type string $value       The checkbox value.
 *     @type bool   $checked     Whether the checkbox is checked or not.
 *     @type string $description A short description of the checkbox.
 * }
 */
function tml_admin_setting_callback_checkbox_field( $args ) {
	$args = wp_parse_args( $args, array(
		'label_for'   => '',
		'label'       => '',
		'value'       => '1',
		'checked'     => '',
		'description' => '',
	) );
?>

	<input type="checkbox" name="<?php echo esc_attr( $args['label_for'] ); ?>" id="<?php echo esc_attr( $args['label_for'] ); ?>" value="<?php echo $args['value']; ?>" <?php checked( ! empty( $args['checked'] ) ); ?> /> <label for="<?php echo esc_attr( $args['label_for'] ); ?>"><?php echo esc_html( $args['label'] ); ?></label>

	<?php if ( ! empty( $args['description'] ) ) : ?>
		<p class="description"><?php echo $args['description']; ?></p>
	<?php endif; ?>

<?php
}

/**
 * Render a checkbox group setting field.
 *
 * @since 7.0
 *
 * @param array $args {
 *     Optional. An array of arguments for rendering a checkbox group.
 *
 *     @type string $legend      The legend text.
 *     @type array  $options     An array of options for the checkbox group.
 *     @type string $description A short description of the checkbox group.
 * }
 */
function tml_admin_setting_callback_checkbox_group_field( $args ) {
	$args = wp_parse_args( $args, array(
		'legend'      => '',
		'options'     => array(),
		'description' => '',
	) );

	$options = array();
	foreach ( (array) $args['options'] as $option_name => $option ) {
		$options[] = sprintf(
			'<label><input type="checkbox" name="%1$s" value="%2$s"%3$s> %4$s</label>',
			esc_attr( $option_name ),
			esc_attr( $option['value'] ),
			checked( ! empty( $option['checked'] ), true, false ),
			esc_html( $option['label'] )
		);
	}
?>

	<fieldset>
		<legend class="screen-reader-text"><span><?php echo esc_html( $args['legend'] ); ?></span></legend>
		<?php echo implode( "<br />\n", $options ); ?>
	</fieldset>

	<?php if ( ! empty( $args['description'] ) ) : ?>
		<p class="description"><?php echo $args['description']; ?></p>
	<?php endif; ?>

<?php
}

/**
 * Render a dropdown setting field.
 *
 * @since 7.0
 *
 * @param array $args {
 *     Optional. An array of arguments for rendering a dropdown.
 *
 *     @type string $label_for   The dropdown name/ID.
 *     @type array  $options     An array of options for the dropdown.
 *     @type string $selected    The value of the selected option.
 *     @type string $description A short description of the dropdown.
 * }
 */
function tml_admin_setting_callback_dropdown_field( $args ) {
	$args = wp_parse_args( $args, array(
		'label_for'   => '',
		'options'     => array(),
		'selected'    => '',
		'description' => '',
	) );

	$options = array();
	foreach ( (array) $args['options'] as $value => $label ) {
		$options[] = sprintf(
			'<option value="%1$s"%2$s>%3$s</option>',
			esc_attr( $value ),
			selected( $args['selected'], $value, false ),
			esc_html( $label )
		);
	}
?>

	<select name="<?php echo esc_attr( $args['label_for'] ); ?>" id="<?php echo esc_attr( $args['label_for'] ); ?>">
		<?php echo implode( "<br />\n", $options ); ?>
	</select>

	<?php if ( ! empty( $args['description'] ) ) : ?>
		<p class="description"><?php echo $args['description']; ?></p>
	<?php endif; ?>

<?php
}

/**
 * Render a radio group setting field.
 *
 * @since 7.0
 *
 * @param array $args {
 *     Optional. An array of arguments for rendering a radio group.
 *
 *     @type string $label_for   The radio button name/ID.
 *     @type string $legend      The legend text.
 *     @type array  $options     An array of options for the radio group.
 *     @type string $checked     The value of the checked option.
 *     @type string $description A short description of the radio group.
 * }
 */
function tml_admin_setting_callback_radio_group_field( $args ) {
	$args = wp_parse_args( $args, array(
		'label_for'   => '',
		'legend'      => '',
		'options'     => array(),
		'checked'     => '',
		'description' => '',
	) );

	$options = array();
	foreach ( (array) $args['options'] as $value => $label ) {
		$options[] = sprintf(
			'<label><input type="radio" name="%1$s" value="%2$s"%3$s> %4$s</label>',
			esc_html( $args['label_for'] ),
			esc_attr( $value ),
			checked( $args['checked'], $value, false ),
			esc_html( $label )
		);
	}
?>

	<fieldset>
		<legend class="screen-reader-text"><span><?php echo esc_html( $args['legend'] ); ?></span></legend>
		<?php echo implode( "<br />\n", $options ); ?>
	</fieldset>

	<?php if ( ! empty( $args['description'] ) ) : ?>
		<p class="description"><?php echo $args['description']; ?></p>
	<?php endif; ?>

<?php
}

/**
 * Render an extension license key field.
 *
 * @since 7.0
 *
 * @param array $args {
 *     Optional. An array of arguments.
 *
 *     @type string                          $label_for The key field name/ID.
 *     @type string|Theme_My_Login_Extention $extension The extension name or object.
 * }
 */
function tml_admin_setting_callback_license_key_field( $args ) {
	$args = wp_parse_args( $args, array(
		'label_for' => '',
		'extension' => '',
	) );

	if ( ! $extension = tml_get_extension( $args['extension'] ) ) {
		return;
	}

	$license = $extension->get_license_key();
	$status  = $extension->get_license_status();

	if ( 'valid' == $status ) {
		$class = 'tml-license-valid';
		$text  = __( 'Active', 'theme-my-login' );
	} elseif ( 'invalid' == $status ) {
		$class = 'tml-license-invalid';
		$text  = __( 'Invalid', 'theme-my-login' );
	} else {
		$class = 'tml-license-inactive';
		$text  = __( 'Inactive', 'theme-my-login' );
	}

	echo sprintf(
		'<input type="text" name="%1$s" id="%1$s" value="%2$s" class="regular-text code tml-license-field %3$s" maxlength="32" %4$s />',
		esc_attr( $args['label_for'] ),
		esc_attr( $license ),
		esc_attr( $class ),
		'valid' == $status ? 'readonly="readonly"' : ''
	) . "\n";

	submit_button(
		__( 'Deactivate', 'theme-my-login' ),
		'secondary tml-license-button',
		'tml_deactivate_license[' . $extension->get_name() . ']',
		false,
		array(
			'data-action'    => 'deactivate',
			'data-extension' => $extension->get_name(),
			'style'          => ( empty( $license ) || 'valid' != $status ) ? 'display: none;' : '',
		)
	);

	submit_button(
		__( 'Activate', 'theme-my-login' ),
		'secondary tml-license-button',
		'tml_activate_license[' . $extension->get_name() . ']',
		false,
		array(
			'data-action'    => 'activate',
			'data-extension' => $extension->get_name(),
			'style'          => ( empty( $license ) || 'valid' == $status ) ? 'display: none;' : '',
		)
	);

	echo '<div class="spinner"></div>';

	printf(
		'<p class="tml-license-status %1$s" style="%3$s">%2$s</p>',
		esc_attr( $class ),
		esc_html( $text ),
		empty( $license ) ? 'display: none;' : ''
	);
}

/**
 * Render the settings page.
 *
 * @since 7.0
 */
function tml_admin_settings_page() {
	global $title, $plugin_page;

	if ( 'theme-my-login' == $plugin_page ) {
		tml_flush_rewrite_rules();
	}

	settings_errors();
?>

<div class="wrap">
	<h1><?php echo esc_html( $title ) ?></h1>
	<hr class="wp-header-end">

	<form id="tml-settings" action="<?php echo is_network_admin() ? '' : 'options.php'; ?>" method="post">

		<?php settings_fields( $plugin_page ); ?>

		<?php do_settings_sections( $plugin_page ); ?>

		<?php submit_button(); ?>
	</form>
</div>

<?php
}

/**
 * Handle the network settings page.
 *
 * @since 7.0
 */
function tml_admin_save_ms_settings() {

	if ( ! tml_is_post_request() ) {
		return;
	}

	$action      = isset( $_REQUEST['action']      ) ? $_REQUEST['action']      : '';
	$option_page = isset( $_REQUEST['option_page'] ) ? $_REQUEST['option_page'] : '';

	if ( ! theme_my_login_admin()->has_page( $option_page ) ) {
		return;
	}

	/* This filter is documented in wp-admin/options.php */
	$whitelist_options = apply_filters( 'whitelist_options', array() );

	if ( ! isset( $whitelist_options[ $option_page ] ) ) {
		wp_die( __( '<strong>Error</strong>: Options page not found.' ) );
	}

	foreach ( $whitelist_options[ $option_page ] as $option ) {
		$option = trim( $option );
		$value  = null;
		if ( isset( $_POST[ $option ] ) ) {
			$value = $_POST[ $option ];
			if ( ! is_array( $value ) ) {
				$value = trim( $value );
			}
			$value = wp_unslash( $value );
		}
		update_site_option( $option, $value );
	}

	tml_flush_rewrite_rules();

	if ( ! count( get_settings_errors() ) ) {
		add_settings_error( 'general', 'settings_updated', __( 'Settings saved.' ), 'updated' );
	}
	set_transient( 'settings_errors', get_settings_errors(), 30 );

	$goback = add_query_arg( 'settings-updated', 'true', wp_get_referer() );
	wp_redirect( $goback );
	exit;
}

/**
 * Add contextual help to settings pages.
 *
 * @since 7.0.5
 *
 * @param WP_Screen $srceen The current screen object.
 */
function tml_admin_add_settings_help_tabs( $screen ) {
	global $plugin_page;

	$help_tabs = $sidebar_links = array();

	if ( ! theme_my_login_admin()->has_page( $plugin_page ) ) {
		return;
	}

	// Core page
	if ( 'theme-my-login' == $plugin_page ) {
		$help_tabs['overview'] = array(
			'id'      => 'theme-my-login-overview',
			'title'   => __( 'Overview' ),
			'content' => '<p>' . implode( '</p><p>', array(
				__( 'Welcome to Theme My Login!', 'theme-my-login' ),
				__( 'Below, you can configure how you would like users to register and log in to your site.', 'theme-my-login' ),
				__( 'Additionally, you can change the slugs that are used to generate the URLs that represent specific actions.', 'theme-my-login' ),
				__( 'You must click the Save Changes button at the bottom of the screen for new settings to take effect.' ),
			) ) . '</p>',
		);

		$sidebar_links['documentation'] = array(
			'title' => __( 'View Documentation', 'theme-my-login' ),
			'url'   => 'https://docs.thememylogin.com',
		);

		$sidebar_links['support'] = array(
			'title' => __( 'Get Support', 'theme-my-login' ),
			'url'   => 'https://wordpress.org/support/plugin/theme-my-login',
		);

	// Licenses page
	} elseif ( 'theme-my-login-licenses' == $plugin_page ) {
		$help_tabs['overview'] = array(
			'id'      => 'theme-my-login-licenses-overview',
			'title'   => __( 'Overview' ),
			'content' => '<p>' . implode( '</p><p>', array(
				__( 'When you purchase extensions for Theme My Login, you will enter your license keys on this page.', 'theme-my-login' ),
				__( 'After you enter your license keys and click the Save Changes button at the bottom of the screen, you will see a new button next to each field with a license in it.', 'theme-my-login' ),
				__( 'If you have not yet activated your license, this button will say "Activate". Click this button to activate your license.', 'theme-my-login' ),
				__( 'If you have already activated your license, this button will say "Deactivate". Click this button to deactivate your license.', 'theme-my-login' ),
			) ) . '</p>',
		);

		$sidebar_links['documentation'] = array(
			'title' => __( 'View Documentation', 'theme-my-login' ),
			'url'   => 'https://docs.thememylogin.com/article/59-how-do-i-install-an-extension',
		);
		$sidebar_links['support'] = array(
			'title' => __( 'Get Support', 'theme-my-login' ),
			'url'   => 'https://thememylogin.com/support',
		);

	// Extensions page
	} elseif ( 'theme-my-login-extensions' == $plugin_page ) {
		$help_tabs['overview'] = array(
			'id'      => 'theme-my-login-extensions-overview',
			'title'   => __( 'Overview' ),
			'content' => '<p>' . implode( '</p><p>', array(
				__( 'This page shows you all of the extensions available to purchase for Theme My Login.', 'theme-my-login' ),
				__( 'Once you purchase an extension, you download it from your email receipt or your account page on our website. Then, you install it just like a normal WordPress plugin.', 'theme-my-login' ),
			) ) . '</p>',
		);

		$sidebar_links['documentation'] = array(
			'title' => __( 'View Documentation', 'theme-my-login' ),
			'url'   => 'https://docs.thememylogin.com/article/59-how-do-i-install-an-extension',
		);
		$sidebar_links['store'] = array(
			'title' => __( 'Go to the Extensions Store', 'theme-my-login' ),
			'url'   => 'https://thememylogin.com/extensions',
		);
		$sidebar_links['account'] = array(
			'title' => __( 'View your Theme My Login account', 'theme-my-login' ),
			'url'   => 'https://thememylogin.com/your-account',
		);

	// Extension page
	} elseif ( $extension = tml_get_extension( $plugin_page ) ) {
		$help_tabs['overview'] = array(
			'id'      => $plugin_page . '-overview',
			'title'   => __( 'Overview' ),
			'content' => '<p>' . implode( '</p><p>', array(
				sprintf(
					__( 'On this page, you can configure the settings for the Theme My Login %s extension.', 'theme-my-login' ),
					$extension->get_title()
				),
				__( 'You must click the Save Changes button at the bottom of the screen for new settings to take effect.' ),
			) ) . '</p>',
		);

		if ( $documentation_url = $extension->get_documentation_url() ) {
			$sidebar_links['documentation'] = array(
				'title' => __( 'View Documentation', 'theme-my-login' ),
				'url'   => $documentation_url,
			);
		}

		if ( $support_url = $extension->get_support_url() ) {
			$sidebar_links['support'] = array(
				'title' => __( 'Get Support', 'theme-my-login' ),
				'url'   => $support_url,
			);
		}

		$settings_page = $extension->get_settings_page_args();
		if ( ! empty( $settings_page['help_tabs'] ) ) {
			$help_tabs = array_merge( $help_tabs, $settings_page['help_tabs'] );
		}
		if ( ! empty( $settings_page['help_sidebar_links'] ) ) {
			$sidebar_links = array_merge( $sidebar_links, $settings_page['help_sidebar_links'] );
		}
	}

	// Add the help tabs
	if ( ! empty( $help_tabs ) ) {
		foreach ( $help_tabs as $help_tab ) {
			$screen->add_help_tab( $help_tab );
		}
	}

	// Add the sidebar links
	if ( ! empty( $sidebar_links ) ) {
		$sidebar_content = '<p><strong>' . __( 'For more information:' ) . '</strong></p>';
		foreach ( $sidebar_links as $sidebar_link ) {
			$sidebar_content .= sprintf( '<p><a href="%s">%s</a></p>',
				$sidebar_link['url'],
				$sidebar_link['title']
			);
		}
		$screen->set_help_sidebar( $sidebar_content );
	}
}
