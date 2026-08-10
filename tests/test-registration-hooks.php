<?php
/**
 * Coverage for the hooks TML attaches to WordPress's own registration
 * pipeline (pre_user_login / register_new_user), plus tml_registration_handler()
 * itself: its registration-disabled and success redirects (intercepted via
 * the wp_redirect filter — see test-login-handler.php's doc comment for
 * why this works without process isolation) and its non-AJAX validation-
 * error branch (no exit involved at all).
 *
 * The actual registration-time side effects TML adds — rewriting the
 * login to the submitted email, setting a user-chosen password, auto-login,
 * and new-user notifications — are wired via WP core's register_new_user(),
 * which the handler calls internally; most of those are covered here by
 * calling register_new_user() directly, exercising the same code path.
 *
 * is_multisite() is TML's very first check in the handler and unconditionally
 * false in this single-site suite, so that branch (redirect to wp-signup.php)
 * is unreachable here by construction — nothing to cover in this file.
 *
 * @package Theme_My_Login
 */

if ( ! class_exists( 'TML_Test_Redirect_Exception' ) ) {
	class TML_Test_Redirect_Exception extends Exception {}
}

class Test_Registration_Hooks extends WP_UnitTestCase {

	public function setUp(): void {
		parent::setUp();

		global $phpmailer;
		$phpmailer->mock_sent = array();

		$_SERVER['REQUEST_METHOD'] = 'POST';
	}

	public function tearDown(): void {
		global $wp;

		delete_site_option( 'tml_registration_type' );
		delete_site_option( 'tml_user_passwords' );
		delete_site_option( 'tml_auto_login' );
		delete_option( 'users_can_register' );

		tml_get_form( 'register' )->set_errors( new WP_Error() );

		$_POST = array();
		$_SERVER['REQUEST_METHOD'] = 'GET';
		remove_all_filters( 'wp_redirect' );
		unset( $wp->query_vars['action'] );

		parent::tearDown();
	}

	public function test_email_registration_type_uses_the_submitted_email_as_login() {
		update_site_option( 'tml_registration_type', 'email' );

		$_POST['user_login'] = 'desired-login';
		$_POST['user_email'] = 'registrant@example.org';

		$user_id = register_new_user( 'desired-login', 'registrant@example.org' );

		$this->assertNotWPError( $user_id );
		$this->assertSame( sanitize_user( 'registrant@example.org' ), get_userdata( $user_id )->user_login );
	}

	public function test_default_registration_type_leaves_the_login_alone() {
		$_POST['user_login'] = 'cool-username';
		$_POST['user_email'] = 'cool-username@example.org';

		$user_id = register_new_user( 'cool-username', 'cool-username@example.org' );

		$this->assertNotWPError( $user_id );
		$this->assertSame( sanitize_user( 'cool-username', true ), get_userdata( $user_id )->user_login );
	}

	public function test_user_chosen_password_is_set_when_allowed() {
		update_site_option( 'tml_user_passwords', true );

		$_POST['user_login'] = 'has-a-password';
		$_POST['user_email'] = 'has-a-password@example.org';
		$_POST['user_pass1'] = 'a-user-chosen-password';
		$_POST['user_pass2'] = 'a-user-chosen-password';

		$user_id = register_new_user( 'has-a-password', 'has-a-password@example.org' );

		$this->assertNotWPError( $user_id );

		$user = get_userdata( $user_id );

		$this->assertTrue( wp_check_password( 'a-user-chosen-password', $user->user_pass, $user->ID ) );
	}

	public function test_submitted_password_is_ignored_when_not_allowed() {
		$_POST['user_login'] = 'no-chosen-password';
		$_POST['user_email'] = 'no-chosen-password@example.org';
		$_POST['user_pass1'] = 'a-user-chosen-password';

		$user_id = register_new_user( 'no-chosen-password', 'no-chosen-password@example.org' );

		$this->assertNotWPError( $user_id );

		$user = get_userdata( $user_id );

		$this->assertFalse( wp_check_password( 'a-user-chosen-password', $user->user_pass, $user->ID ) );
	}

	public function test_auto_login_logs_the_new_user_in_when_enabled() {
		update_site_option( 'tml_auto_login', true );

		$logged_in_user_id = null;
		add_action( 'set_logged_in_cookie', function ( $cookie, $expire, $expiration, $user_id ) use ( &$logged_in_user_id ) {
			$logged_in_user_id = $user_id;
		}, 10, 4 );

		$_POST['user_login'] = 'auto-login-user';
		$_POST['user_email'] = 'auto-login-user@example.org';

		$user_id = register_new_user( 'auto-login-user', 'auto-login-user@example.org' );

		$this->assertNotWPError( $user_id );
		$this->assertSame( $user_id, $logged_in_user_id );
	}

	public function test_auto_login_does_nothing_when_disabled() {
		$logged_in = false;
		add_action( 'set_logged_in_cookie', function () use ( &$logged_in ) {
			$logged_in = true;
		} );

		$_POST['user_login'] = 'no-auto-login-user';
		$_POST['user_email'] = 'no-auto-login-user@example.org';

		register_new_user( 'no-auto-login-user', 'no-auto-login-user@example.org' );

		$this->assertFalse( $logged_in );
	}

	public function test_new_user_notifications_are_sent_by_default() {
		global $phpmailer;

		$_POST['user_login'] = 'notify-me';
		$_POST['user_email'] = 'notify-me@example.org';

		register_new_user( 'notify-me', 'notify-me@example.org' );

		$this->assertNotEmpty( $phpmailer->mock_sent );
	}

	public function test_new_user_notifications_can_be_fully_disabled() {
		global $phpmailer;

		add_filter( 'tml_send_new_user_notification', '__return_false' );
		add_filter( 'tml_send_new_user_admin_notification', '__return_false' );

		$_POST['user_login'] = 'notify-nobody';
		$_POST['user_email'] = 'notify-nobody@example.org';

		register_new_user( 'notify-nobody', 'notify-nobody@example.org' );

		remove_filter( 'tml_send_new_user_notification', '__return_false' );
		remove_filter( 'tml_send_new_user_admin_notification', '__return_false' );

		$this->assertEmpty( $phpmailer->mock_sent );
	}

	// tml_registration_handler() itself, not just register_new_user()

	public function test_handler_redirects_to_wp_login_when_registration_is_disabled() {
		global $wp;

		$wp->query_vars['action'] = 'register';
		update_option( 'users_can_register', 0 );

		$captured = null;
		add_filter( 'wp_redirect', function ( $location ) use ( &$captured ) {
			$captured = $location;
			throw new TML_Test_Redirect_Exception();
		} );

		try {
			tml_registration_handler();
			$this->fail( 'Expected the disabled-registration branch to attempt a redirect.' );
		} catch ( TML_Test_Redirect_Exception $e ) {
			$this->assertStringContainsString( 'registration=disabled', $captured );
		}
	}

	public function test_handler_redirects_to_checkemail_on_success() {
		global $wp;

		$wp->query_vars['action'] = 'register';
		update_option( 'users_can_register', 1 );

		$_POST['user_login'] = 'handlersuccessuser';
		$_POST['user_email'] = 'handlersuccessuser@example.org';

		$captured = null;
		add_filter( 'wp_redirect', function ( $location ) use ( &$captured ) {
			$captured = $location;
			throw new TML_Test_Redirect_Exception();
		} );

		try {
			tml_registration_handler();
			$this->fail( 'Expected a successful registration to attempt a redirect.' );
		} catch ( TML_Test_Redirect_Exception $e ) {
			$this->assertStringContainsString( 'checkemail=registered', $captured );
			$this->assertNotFalse( get_user_by( 'login', 'handlersuccessuser' ) );
		}
	}

	public function test_handler_redirects_to_a_custom_destination_when_submitted() {
		global $wp;

		$wp->query_vars['action'] = 'register';
		update_option( 'users_can_register', 1 );

		$_POST['user_login']    = 'handlercustomredirect';
		$_POST['user_email']    = 'handlercustomredirect@example.org';
		$_POST['redirect_to']   = 'https://example.org/after-registration';

		$captured = null;
		add_filter( 'wp_redirect', function ( $location ) use ( &$captured ) {
			$captured = $location;
			throw new TML_Test_Redirect_Exception();
		} );

		try {
			tml_registration_handler();
			$this->fail( 'Expected a successful registration to attempt a redirect.' );
		} catch ( TML_Test_Redirect_Exception $e ) {
			$this->assertSame( 'https://example.org/after-registration', $captured );
		}
	}

	public function test_handler_sets_errors_without_redirecting_on_validation_failure() {
		global $wp;

		$wp->query_vars['action'] = 'register';
		update_option( 'users_can_register', 1 );

		$_POST['user_login'] = '';
		$_POST['user_email'] = 'not-an-email';

		$redirected = false;
		add_filter( 'wp_redirect', function ( $location ) use ( &$redirected ) {
			$redirected = true;
			return $location;
		} );

		tml_registration_handler();

		$this->assertFalse( $redirected );
		$this->assertNotEmpty( tml_get_errors()->get_error_codes() );
	}
}
