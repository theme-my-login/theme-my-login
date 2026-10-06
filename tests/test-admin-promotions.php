<?php
/**
 * Coverage for src/admin/promotions.php: extension suggestions on the
 * settings screen, the review request and its AJAX answers, and sale
 * announcements read from the extensions feed.
 *
 * The admin files only load when is_admin() is true, so they're pulled in
 * directly, same as the other admin tests.
 *
 * @package Theme_My_Login
 */

if ( ! function_exists( 'tml_admin_get_extensions_feed' ) ) {
	require THEME_MY_LOGIN_PATH . 'admin/extensions.php';
}
if ( ! function_exists( 'tml_admin_notice_is_dismissed' ) ) {
	require THEME_MY_LOGIN_PATH . 'admin/promotions.php';
}
if ( ! function_exists( 'tml_admin_dismiss_notice' ) ) {
	require THEME_MY_LOGIN_PATH . 'admin/functions.php';
}
if ( ! function_exists( 'tml_admin_get_settings_fields' ) ) {
	require THEME_MY_LOGIN_PATH . 'admin/settings.php';
}

class Test_Admin_Promotions extends WP_UnitTestCase {

	protected $feed_requests = 0;

	public function tearDown(): void {
		global $plugin_page;

		$plugin_page = null;

		foreach ( array_keys( tml_get_extensions() ) as $name ) {
			tml_unregister_extension( $name );
		}

		delete_site_option( '_tml_dismissed_notices' );
		delete_site_option( '_tml_installed_at' );
		delete_site_option( '_tml_review_later' );
		delete_site_transient( 'tml_promotion' );
		delete_site_transient( 'tml_extensions_feed-' . md5( http_build_query( array( 'number' => 12 ) ) ) );

		remove_all_filters( 'pre_http_request' );

		parent::tearDown();
	}

	public function test_suggestion_links_to_the_extension() {
		$suggestion = tml_admin_get_extension_suggestion( 'moderation', 'Moderation', 'Approve new users.' );

		$this->assertStringContainsString( 'Approve new users.', $suggestion );
		$this->assertStringContainsString( 'href="https://thememylogin.com/extensions/moderation/"', $suggestion );
		$this->assertStringContainsString( 'Get Moderation', $suggestion );
	}

	public function test_suggestion_is_hidden_when_the_extension_is_installed() {
		tml_register_extension( new TML_Test_Extension( WP_PLUGIN_DIR . '/tml-moderation/tml-moderation.php', array( 'name' => 'tml-moderation' ) ) );

		$this->assertSame( '', tml_admin_get_extension_suggestion( 'moderation', 'Moderation', 'Approve new users.' ) );
	}

	public function test_suggestion_is_hidden_for_an_extension_registered_under_its_old_name() {
		tml_register_extension( new TML_Test_Extension( WP_PLUGIN_DIR . '/theme-my-login-moderation/theme-my-login-moderation.php', array( 'name' => 'theme-my-login-moderation' ) ) );

		$this->assertSame( '', tml_admin_get_extension_suggestion( 'moderation', 'Moderation', 'Approve new users.' ) );
	}

	public function test_suggestions_are_hidden_once_dismissed() {
		tml_admin_dismiss_notice( 'extension-suggestions' );

		$this->assertSame( '', tml_admin_get_extension_suggestion( 'moderation', 'Moderation', 'Approve new users.' ) );
	}

	public function test_settings_fields_carry_suggestions_for_missing_extensions() {
		$fields = tml_admin_get_settings_fields();

		$this->assertStringContainsString( '/extensions/2fa/', $fields['tml_settings_login']['tml_login_type']['args']['description'] );
		$this->assertStringContainsString( '/extensions/moderation/', $fields['tml_settings_registration']['tml_registration_type']['args']['description'] );
		$this->assertStringContainsString( '/extensions/recaptcha/', $fields['tml_settings_registration']['tml_registration_type']['args']['description'] );
		$this->assertStringContainsString( '/extensions/security/', $fields['tml_settings_registration']['tml_user_passwords']['args']['description'] );
		$this->assertStringContainsString( '/extensions/redirection/', $fields['tml_settings_registration']['tml_auto_login']['args']['description'] );
	}

	public function test_hide_link_shows_on_the_main_settings_page_only() {
		global $plugin_page;

		$plugin_page = 'theme-my-login';
		ob_start();
		tml_admin_extension_suggestions_toggle();
		$this->assertStringContainsString( 'data-notice="extension-suggestions"', ob_get_clean() );

		$plugin_page = 'theme-my-login-licenses';
		ob_start();
		tml_admin_extension_suggestions_toggle();
		$this->assertSame( '', ob_get_clean() );
	}
}
