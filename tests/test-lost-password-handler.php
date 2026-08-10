<?php
/**
 * Coverage for the non-AJAX lost-password handler: the failure path,
 * the success path's redirect (intercepted via the wp_redirect filter —
 * see test-login-handler.php's doc comment for why this works without
 * process isolation), and the invalidkey/expiredkey error branches.
 *
 * @package Theme_My_Login
 */

if ( ! class_exists( 'TML_Test_Redirect_Exception' ) ) {
	class TML_Test_Redirect_Exception extends Exception {}
}

class Test_Lost_Password_Handler extends WP_UnitTestCase {

	public function tearDown(): void {
		global $wp;

		// Forms are process-lifetime singletons (not reset between tests
		// the way the DB is), so a form's errors from one test would
		// otherwise leak into the next.
		tml_get_form( 'lostpassword' )->set_errors( new WP_Error() );

		$_POST   = array();
		$_SERVER['REQUEST_METHOD'] = 'GET';
		unset( $wp->query_vars['action'] );

		parent::tearDown();
	}

	public function test_unknown_username_sets_an_error() {
		global $wp;

		$wp->query_vars['action'] = 'lostpassword';

		$_SERVER['REQUEST_METHOD'] = 'POST';
		$_POST['user_login']       = 'no-such-user';

		tml_lost_password_handler();

		$this->assertSame( array( 'invalidcombo' ), tml_get_errors()->get_error_codes() );
	}

	public function test_known_username_generates_no_error() {
		global $wp;

		$wp->query_vars['action'] = 'lostpassword';

		$user = self::factory()->user->create_and_get();

		$_SERVER['REQUEST_METHOD'] = 'POST';
		$_POST['user_login']       = $user->user_login;

		// A successful request redirects (and exits) for a real browser
		// request; here we only care that no error is produced beforehand,
		// so intercept the redirect and short-circuit before the exit.
		add_filter( 'wp_redirect', function ( $location ) {
			throw new TML_Test_Redirect_Exception( $location );
		} );

		try {
			tml_lost_password_handler();
			$this->fail( 'Expected the success path to attempt a redirect.' );
		} catch ( TML_Test_Redirect_Exception $e ) {
			$this->assertEmpty( tml_get_errors()->get_error_codes() );
			$this->assertStringContainsString( 'checkemail=confirm', $e->getMessage() );
		}
	}

	public function test_invalidkey_request_param_adds_an_error() {
		global $wp;

		$wp->query_vars['action'] = 'lostpassword';

		$_REQUEST['error'] = 'invalidkey';

		tml_lost_password_handler();

		$this->assertContains( 'invalidkey', tml_get_errors()->get_error_codes() );

		unset( $_REQUEST['error'] );
	}

	public function test_expiredkey_request_param_adds_an_error() {
		global $wp;

		$wp->query_vars['action'] = 'lostpassword';

		$_REQUEST['error'] = 'expiredkey';

		tml_lost_password_handler();

		$this->assertContains( 'expiredkey', tml_get_errors()->get_error_codes() );

		unset( $_REQUEST['error'] );
	}

	public function test_lost_password_action_fires_with_the_current_errors() {
		global $wp;

		$wp->query_vars['action'] = 'lostpassword';

		$received = 'not-called';
		add_action( 'lost_password', function ( $errors ) use ( &$received ) {
			$received = $errors;
		} );

		tml_lost_password_handler();

		remove_all_actions( 'lost_password' );

		$this->assertInstanceOf( 'WP_Error', $received );
	}
}
