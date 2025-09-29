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
Version: 7.1.13
Author: Theme My Login
Author URI: https://thememylogin.com
License: GPLv2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: theme-my-login
Network: true
*/

if ( ! file_exists( __DIR__ . '/build/theme-my-login.php' ) || defined( 'TML_LOAD_SOURCE' ) ) {
	include __DIR__ . '/src/theme-my-login.php';
} else {
	include __DIR__ . '/build/theme-my-login.php';
}
