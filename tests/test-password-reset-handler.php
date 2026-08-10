<?php
/**
 * Coverage for tml_password_reset_handler(): the key/login GET redirect,
 * the invalid-key redirect, the password validation error branches (no
 * exit involved), and the success path (redirect intercepted via the
 * wp_redirect filter — see test-login-handler.php's doc comment for why
 * this works without process isolation).
 *
 * The handler calls setcookie() directly, which raises a real E_WARNING
 * ("headers already sent") in this CLI test runner — see project memory
 * on why headers_sent() is permanently true here. Suppressed the same
 * way WP_Ajax_UnitTestCase does it, since it's an artifact of the test
 * environment, not something under test.
 *
 * @package Theme_My_Login
 */

if ( ! class_exists( 'TML_Test_Redirect_Exception' ) ) {
	class TML_Test_Redirect_Exception extends Exception {}
}

class Test_Password_Reset_Handler extends WP_UnitTestCase {

	protected $rp_cookie;
	protected $error_level;

	public function setUp(): void {
		parent::setUp();

		$this->rp_cookie   = 'wp-resetpass-' . COOKIEHASH;
		$this->error_level = error_reporting();
		error_reporting( $this->error_level & ~E_WARNING );

		$_SERVER['REQUEST_URI'] = '/wp-login.php?action=resetpass';
	}

	public function tearDown(): void {
		error_reporting( $this->error_level );

		$_GET    = array();
		$_POST   = array();
		$_COOKIE = array();
		unset( $_COOKIE[ $this->rp_cookie ] );

		remove_all_filters( 'wp_redirect' );
		remove_all_actions( 'validate_password_reset' );

		parent::tearDown();
	}

	protected function expect_redirect_containing( $needle ) {
		$captured = null;
		add_filter( 'wp_redirect', function ( $location ) use ( &$captured ) {
			$captured = $location;
			throw new TML_Test_Redirect_Exception();
		} );

		try {
			tml_password_reset_handler();
			$this->fail( 'Expected a redirect.' );
		} catch ( TML_Test_Redirect_Exception $e ) {
			$this->assertStringContainsString( $needle, $captured );
		}
	}

	public function test_key_and_login_in_the_url_set_a_cookie_and_redirect() {
		$_GET['key']   = 'some-reset-key';
		$_GET['login'] = 'some-user';

		$this->expect_redirect_containing( 'wp-login.php?action=resetpass' );
	}

	public function test_missing_cookie_redirects_as_an_invalid_key() {
		$this->expect_redirect_containing( 'error=invalidkey' );
	}

	public function test_malformed_cookie_redirects_as_an_invalid_key() {
		$_COOKIE[ $this->rp_cookie ] = 'no-colon-in-here';

		$this->expect_redirect_containing( 'error=invalidkey' );
	}

	public function test_unknown_user_in_the_cookie_redirects_as_an_invalid_key() {
		$_COOKIE[ $this->rp_cookie ] = 'no-such-user:some-key';

		$this->expect_redirect_containing( 'error=invalidkey' );
	}

	protected function set_up_a_valid_reset_key() {
		$user = self::factory()->user->create_and_get();
		$key  = get_password_reset_key( $user );

		$_COOKIE[ $this->rp_cookie ] = $user->user_login . ':' . $key;

		return array( $user, $key );
	}

	public function test_mismatched_passwords_set_an_error_without_a_redirect() {
		global $wp;

		list( $user, $key ) = $this->set_up_a_valid_reset_key();

		$wp->query_vars['action'] = 'resetpass';
		tml_register_password_reset_form();

		$_POST['rp_key'] = $key;
		$_POST['pass1']  = 'one-password';
		$_POST['pass2']  = 'a-different-password';

		$redirected = false;
		add_filter( 'wp_redirect', function ( $location ) use ( &$redirected ) {
			$redirected = true;
			return $location;
		} );

		tml_password_reset_handler();

		$this->assertFalse( $redirected );
		$this->assertContains( 'password_reset_mismatch', tml_get_errors()->get_error_codes() );

		unset( $wp->query_vars['action'] );
	}

	public function test_matching_passwords_reset_the_password_and_redirect() {
		global $wp;

		list( $user, $key ) = $this->set_up_a_valid_reset_key();

		$_POST['rp_key'] = $key;
		$_POST['pass1']  = 'a-brand-new-password';
		$_POST['pass2']  = 'a-brand-new-password';

		$this->expect_redirect_containing( 'resetpass=complete' );

		$this->assertTrue( wp_check_password( 'a-brand-new-password', get_userdata( $user->ID )->user_pass, $user->ID ) );
	}

	public function test_validate_password_reset_action_fires() {
		list( $user, $key ) = $this->set_up_a_valid_reset_key();

		$received_user = null;
		add_action( 'validate_password_reset', function ( $errors, $user ) use ( &$received_user ) {
			$received_user = $user;
		}, 10, 2 );

		// No $_POST['pass1'], so this falls through to tml_set_errors()
		// without a redirect — just proving the action fires either way.
		tml_password_reset_handler();

		$this->assertSame( $user->ID, $received_user->ID );
	}
}
