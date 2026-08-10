<?php
/**
 * WordPress test suite DB config, driven entirely by environment variables
 * so the same file works locally and in CI.
 */

// Point at a full WP core checkout for ABSPATH. Defaults to the
// `roots/wordpress-no-content` package pulled in via composer; override with
// WP_TESTS_ABSPATH to reuse an existing WP install instead (e.g. a local dev
// site) and skip downloading a second copy of core.
define( 'ABSPATH', getenv( 'WP_TESTS_ABSPATH' ) ?: dirname( __DIR__ ) . '/vendor/roots/wordpress-no-content/' );

define( 'DB_NAME', getenv( 'WP_TESTS_DB_NAME' ) ?: 'wordpress_test' );
define( 'DB_USER', getenv( 'WP_TESTS_DB_USER' ) ?: 'root' );
define( 'DB_PASSWORD', getenv( 'WP_TESTS_DB_PASSWORD' ) ?: '' );
define( 'DB_HOST', getenv( 'WP_TESTS_DB_HOST' ) ?: '127.0.0.1' );
define( 'DB_CHARSET', 'utf8' );
define( 'DB_COLLATE', '' );

$table_prefix = 'wptests_';

define( 'WP_TESTS_DOMAIN', 'example.org' );
define( 'WP_TESTS_EMAIL', 'admin@example.org' );
define( 'WP_TESTS_TITLE', 'Test Blog' );

// Set by tests/phpunit/multisite.xml's <env> block to switch the wp-phpunit
// bootstrap into a network install; unset (single-site) otherwise.
if ( getenv( 'WP_TESTS_MULTISITE' ) ) {
	define( 'WP_TESTS_MULTISITE', true );
}

define( 'WP_PHP_BINARY', 'php' );

define( 'WPLANG', '' );

// Opt-in: WP_DEBUG=false (the default here, matching production) makes core's
// wp_debug_mode() lower error_reporting() to exclude E_DEPRECATED, so native
// PHP deprecations (e.g. from testing under a newer PHP than production) are
// silently swallowed before they ever reach PHPUnit's error handler — a test
// run can look clean while the same code warns loudly on a real server. Set
// WP_TESTS_STRICT_DEPRECATIONS=1 (see the composer "test:strict" script) to
// turn WP_DEBUG on for the run so those deprecations surface as risky tests
// instead, tripped up by beStrictAboutOutputDuringTests.
if ( getenv( 'WP_TESTS_STRICT_DEPRECATIONS' ) ) {
	define( 'WP_DEBUG', true );
	define( 'WP_DEBUG_DISPLAY', true );
	define( 'WP_DEBUG_LOG', false );
}
