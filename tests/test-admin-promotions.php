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

	public function test_review_request_waits_thirty_days_from_install() {
		update_site_option( '_tml_installed_at', 1000 );

		$this->assertFalse( tml_admin_review_request_is_due( 1000 + 30 * DAY_IN_SECONDS - 1 ) );
		$this->assertTrue( tml_admin_review_request_is_due( 1000 + 30 * DAY_IN_SECONDS ) );
	}

	public function test_review_request_waits_after_maybe_later() {
		update_site_option( '_tml_installed_at', 1000 );
		update_site_option( '_tml_review_later', 1000 + 100 * DAY_IN_SECONDS );

		$this->assertFalse( tml_admin_review_request_is_due( 1000 + 99 * DAY_IN_SECONDS ) );
		$this->assertTrue( tml_admin_review_request_is_due( 1000 + 100 * DAY_IN_SECONDS ) );
	}

	public function test_review_request_never_returns_once_dismissed_or_without_an_install_time() {
		$this->assertFalse( tml_admin_review_request_is_due( PHP_INT_MAX ) );

		update_site_option( '_tml_installed_at', 1000 );
		tml_admin_dismiss_notice( 'review' );

		$this->assertFalse( tml_admin_review_request_is_due( PHP_INT_MAX ) );
	}
}

class Test_Admin_Review_Request_Ajax extends WP_Ajax_UnitTestCase {

	public function setUp(): void {
		parent::setUp();

		wp_set_current_user( self::factory()->user->create( array( 'role' => 'administrator' ) ) );
	}

	public function tearDown(): void {
		$_POST = $_REQUEST = array();

		delete_site_option( '_tml_dismissed_notices' );
		delete_site_option( '_tml_review_later' );

		wp_set_current_user( 0 );

		parent::tearDown();
	}

	protected function answer( $choice, $nonce = null ) {
		$_POST['choice'] = $choice;
		$_POST['nonce']  = null === $nonce ? wp_create_nonce( 'tml-review-request' ) : $nonce;

		try {
			ob_start();
			tml_admin_ajax_review_request();
		} catch ( WPAjaxDieContinueException $e ) {
			unset( $e );
		} catch ( WPAjaxDieStopException $e ) {
			unset( $e );
		}

		return json_decode( $this->_last_response, true );
	}

	public function test_maybe_later_asks_again_in_ninety_days() {
		$response = $this->answer( 'later' );

		$this->assertTrue( $response['success'] );
		$this->assertEqualsWithDelta( time() + 90 * DAY_IN_SECONDS, get_site_option( '_tml_review_later' ), 5 );
		$this->assertFalse( tml_admin_notice_is_dismissed( 'review' ) );
	}

	public function test_reviewed_and_never_stop_asking() {
		$this->answer( 'reviewed' );
		$this->assertTrue( tml_admin_notice_is_dismissed( 'review' ) );

		delete_site_option( '_tml_dismissed_notices' );
		$this->_last_response = '';

		$this->answer( 'never' );
		$this->assertTrue( tml_admin_notice_is_dismissed( 'review' ) );
	}

	public function test_a_bad_nonce_changes_nothing() {
		$response = $this->answer( 'never', 'nope' );

		$this->assertFalse( $response['success'] );
		$this->assertFalse( tml_admin_notice_is_dismissed( 'review' ) );
	}
}
