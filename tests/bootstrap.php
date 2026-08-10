<?php
/**
 * PHPUnit bootstrap file for Theme My Login.
 *
 * @package Theme_My_Login
 */

$_tests_dir = getenv( 'WP_TESTS_DIR' );

if ( ! $_tests_dir ) {
	$_tests_dir = getenv( 'WP_PHPUNIT__DIR' );
}

if ( ! $_tests_dir ) {
	$_tests_dir = dirname( __DIR__ ) . '/vendor/wp-phpunit/wp-phpunit';
}

require_once $_tests_dir . '/includes/functions.php';

/**
 * Load the plugin from source, bypassing the built `build/` copy so tests
 * always run against the code that's actually being changed.
 */
function _tml_manually_load_plugin() {
	define( 'TML_LOAD_SOURCE', true );
	require dirname( __DIR__ ) . '/theme-my-login.php';
}
tests_add_filter( 'muplugins_loaded', '_tml_manually_load_plugin' );

require $_tests_dir . '/includes/bootstrap.php';
