<?php

/**
 * The Theme My Login Plugin
 *
 * @package Theme_My_Login
 */

/*
Plugin Name: Theme My Login
Plugin URI: https://thememylogin.com
Description: Creates an alternate login, registration and password recovery experience within your theme.
Version: 7.1.3
Author: Theme My Login
Author URI: https://thememylogin.com
License: GPLv2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: theme-my-login
Network: true
*/

/**
 * Stores the version of TML.
 *
 * @since 7.0
 */
define( 'THEME_MY_LOGIN_VERSION', '7.1.3' );

/**
 * Stores the path to TML.
 *
 * @since 6.4.4
 */
define( 'THEME_MY_LOGIN_PATH', plugin_dir_path( __FILE__ ) );

/**
 * Stores the URL to TML.
 *
 * @since 7.0
 */
define( 'THEME_MY_LOGIN_URL',  plugin_dir_url( __FILE__ ) );

/**
 * Stores the URL to TML's extensions directory.
 *
 * @since 7.0
 */
define( 'THEME_MY_LOGIN_EXTENSIONS_URL', 'https://thememylogin.com/extensions' );

/**
 * Stores the URL to TML's extensions API.
 *
 * @since 7.0
 */
define( 'THEME_MY_LOGIN_EXTENSIONS_API_URL', 'https://thememylogin.com/edd-api/products' );

/**
 * Require files.
 */
require THEME_MY_LOGIN_PATH . 'includes/class-theme-my-login.php';
require THEME_MY_LOGIN_PATH . 'includes/class-theme-my-login-action.php';
require THEME_MY_LOGIN_PATH . 'includes/class-theme-my-login-form.php';
require THEME_MY_LOGIN_PATH . 'includes/class-theme-my-login-form-field.php';
require THEME_MY_LOGIN_PATH . 'includes/class-theme-my-login-extension.php';
require THEME_MY_LOGIN_PATH . 'includes/class-theme-my-login-widget.php';
require THEME_MY_LOGIN_PATH . 'includes/actions.php';
require THEME_MY_LOGIN_PATH . 'includes/forms.php';
require THEME_MY_LOGIN_PATH . 'includes/extensions.php';
require THEME_MY_LOGIN_PATH . 'includes/compat.php';
require THEME_MY_LOGIN_PATH . 'includes/functions.php';
require THEME_MY_LOGIN_PATH . 'includes/options.php';
require THEME_MY_LOGIN_PATH . 'includes/shortcodes.php';
require THEME_MY_LOGIN_PATH . 'includes/hooks.php';

if ( is_multisite() ) {
	require THEME_MY_LOGIN_PATH . 'includes/ms-functions.php';
	require THEME_MY_LOGIN_PATH . 'includes/ms-hooks.php';
}

if ( defined( 'WP_CLI' ) && WP_CLI ) {
	require THEME_MY_LOGIN_PATH . 'includes/commands.php';
}

/**
 * Load custom functions file.
 */
if ( file_exists( WP_PLUGIN_DIR . '/theme-my-login-custom.php' ) ) {
	include WP_PLUGIN_DIR . '/theme-my-login-custom.php';
}

// Prepare for something amazing!
theme_my_login();

/**
 * Require admin files.
 */
if ( is_admin() ) {
	require THEME_MY_LOGIN_PATH . 'admin/class-theme-my-login-admin.php';
	require THEME_MY_LOGIN_PATH . 'admin/functions.php';
	require THEME_MY_LOGIN_PATH . 'admin/settings.php';
	require THEME_MY_LOGIN_PATH . 'admin/extensions.php';
	require THEME_MY_LOGIN_PATH . 'admin/hooks.php';

	// Prepare for something somewhat amazing!
	theme_my_login_admin();
}
