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
Version: 7.0.14
Author: Theme My Login
Author URI: https://thememylogin.com
License: GPLv2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: theme-my-login
Network: true
*/

if ( ! file_exists( dirname( __FILE__ ) . '/build/theme-my-login.php' ) || defined( 'TML_LOAD_SOURCE' ) ) {
	include dirname( __FILE__ ) . '/src/theme-my-login.php';
} else {
	include dirname( __FILE__ ) . '/build/theme-my-login.php';
}
