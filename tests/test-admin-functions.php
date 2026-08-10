<?php
/**
 * Coverage for the exit-free helpers in src/admin/functions.php: plugin
 * page detection, slug sanitization, the plugin-list settings/extensions
 * links, and the version-bump migration run on update.
 *
 * admin/functions.php is normally only required when is_admin() is true,
 * which it isn't in the default (front-end) test bootstrap, so it's pulled
 * in directly here rather than faking an admin request context.
 *
 * @package Theme_My_Login
 */

if ( ! function_exists( 'tml_admin_is_plugin_page' ) ) {
	require THEME_MY_LOGIN_PATH . 'admin/functions.php';
}

class Test_Admin_Functions extends WP_UnitTestCase {

	public function tearDown(): void {
		global $plugin_page;

		$plugin_page = null;

		delete_site_option( '_tml_version' );
		delete_site_option( '_tml_installed_at' );
		delete_site_option( '_tml_updated_at' );
		delete_site_option( '_tml_previous_version' );
		delete_site_option( 'tml_login_type' );
		delete_option( 'theme_my_login' );
		delete_option( 'rewrite_rules' );

		parent::tearDown();
	}

	public function test_is_plugin_page_matches_the_theme_my_login_prefix() {
		global $plugin_page;

		$plugin_page = 'theme-my-login-extensions';

		$this->assertTrue( tml_admin_is_plugin_page() );
	}

	public function test_is_plugin_page_matches_the_tml_prefix() {
		global $plugin_page;

		$plugin_page = 'tml-licenses';

		$this->assertTrue( tml_admin_is_plugin_page() );
	}

	public function test_is_plugin_page_false_for_unrelated_page() {
		global $plugin_page;

		$plugin_page = 'some-other-plugin';

		$this->assertFalse( tml_admin_is_plugin_page() );
	}

	public function test_is_plugin_page_matches_a_specific_page_name() {
		global $plugin_page;

		$plugin_page = 'theme-my-login-extensions';

		$this->assertTrue( tml_admin_is_plugin_page( 'extensions' ) );
		$this->assertFalse( tml_admin_is_plugin_page( 'licenses' ) );
	}

	public function test_sanitize_slug_collapses_repeated_slashes() {
		$this->assertSame( 'foo/bar', tml_sanitize_slug( 'foo//bar' ) );
	}

	public function test_sanitize_slug_strips_hashes() {
		$this->assertSame( 'foobar', tml_sanitize_slug( 'foo#bar' ) );
	}

	public function test_sanitize_slug_strips_the_index_php_prefix() {
		$this->assertSame( 'foo/bar', tml_sanitize_slug( '/index.php/foo/bar' ) );
	}

	public function test_sanitize_slug_trims_leading_and_trailing_slashes() {
		$this->assertSame( 'foo', tml_sanitize_slug( '/foo/' ) );
	}

	public function test_sanitize_slug_returns_empty_string_unchanged() {
		$this->assertSame( '', tml_sanitize_slug( '' ) );
	}

	public function test_plugin_action_links_are_added_for_the_tml_plugin_file() {
		$actions = tml_admin_filter_plugin_action_links( array( 'deactivate' => 'Deactivate' ), 'theme-my-login/theme-my-login.php', array(), 'plugin-list' );

		$this->assertArrayHasKey( 'settings', $actions );
		$this->assertArrayHasKey( 'extensions', $actions );
		$this->assertStringContainsString( 'page=theme-my-login', $actions['settings'] );
	}

	public function test_plugin_action_links_are_untouched_for_other_plugins() {
		$original = array( 'deactivate' => 'Deactivate' );

		$actions = tml_admin_filter_plugin_action_links( $original, 'some-other-plugin/some-other-plugin.php', array(), 'plugin-list' );

		$this->assertSame( $original, $actions );
	}

	public function test_update_sets_version_and_install_timestamps_on_a_fresh_install() {
		tml_admin_update();

		$this->assertSame( THEME_MY_LOGIN_VERSION, get_site_option( '_tml_version' ) );
		$this->assertNotEmpty( get_site_option( '_tml_installed_at' ) );
		$this->assertNotEmpty( get_site_option( '_tml_updated_at' ) );
	}

	public function test_update_is_a_no_op_when_already_current() {
		update_site_option( '_tml_version', THEME_MY_LOGIN_VERSION );
		update_site_option( '_tml_installed_at', 12345 );

		tml_admin_update();

		$this->assertSame( 12345, get_site_option( '_tml_installed_at' ) );
	}

	public function test_update_migrates_the_pre_7_login_type_option() {
		update_site_option( '_tml_version', '6.4' );
		update_option( 'theme_my_login', array( 'login_type' => 'username' ) );

		tml_admin_update();

		$this->assertSame( 'username', get_site_option( 'tml_login_type' ) );
		$this->assertFalse( get_option( 'theme_my_login' ) );
		$this->assertSame( '6.4', get_site_option( '_tml_previous_version' ) );
	}
}
