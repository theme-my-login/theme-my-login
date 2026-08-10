<?php
/**
 * Coverage for the non-AJAX login handler: the failure path, and the
 * success path's two redirect branches, intercepted via the wp_redirect
 * filter (thrown from inside the callback, which aborts execution
 * before the handler's own `exit;` is ever reached — see project memory
 * for why this works and doesn't require process isolation).
 *
 * @package Theme_My_Login
 */

if ( ! class_exists( 'TML_Test_Redirect_Exception' ) ) {
	class TML_Test_Redirect_Exception extends Exception {}
}

class Test_Login_Handler extends WP_UnitTestCase {

	public function tearDown(): void {
		global $wp;

		// Forms are process-lifetime singletons (not reset between tests
		// the way the DB is), so a form's errors from one test would
		// otherwise leak into the next.
		tml_get_form( 'login' )->set_errors( new WP_Error() );

		$_POST    = array();
		$_REQUEST = array();
		remove_all_filters( 'wp_redirect' );
		unset( $wp->query_vars['action'] );

		parent::tearDown();
	}

	public function test_invalid_credentials_set_an_error_without_logging_in() {
		global $wp;

		// tml_get_form()/tml_get_errors() resolve the current form from the
		// 'action' query var, same as they would on a real login request.
		$wp->query_vars['action'] = 'login';

		$_POST['log'] = 'no-such-user';
		$_POST['pwd'] = 'wrong-password';
		$_REQUEST = $_POST;

		tml_login_handler();

		$this->assertFalse( is_user_logged_in() );
		$this->assertNotEmpty( tml_get_errors()->get_error_codes() );
	}

	protected function valid_login_post( $user ) {
		wp_set_password( 'a-correct-password', $user->ID );

		$_POST['log'] = $user->user_login;
		$_POST['pwd'] = 'a-correct-password';
		// wp-phpunit's own bootstrap echoes status text before any test
		// runs, so headers_sent() is permanently true in this CLI runner;
		// a non-empty LOGGED_IN_COOKIE skips the (irrelevant here) "did
		// output leak before cookies were set" branch this would hit.
		$_COOKIE[ LOGGED_IN_COOKIE ] = 'dummy-value-to-skip-the-cookie-delivery-check';
	}

	public function test_valid_credentials_redirect_to_a_custom_destination() {
		global $wp;

		$wp->query_vars['action'] = 'login';

		$user = self::factory()->user->create_and_get();
		$this->valid_login_post( $user );
		$_REQUEST['redirect_to'] = 'https://example.org/custom-redirect';

		$captured = null;
		add_filter( 'wp_redirect', function ( $location ) use ( &$captured ) {
			$captured = $location;
			throw new TML_Test_Redirect_Exception();
		} );

		try {
			tml_login_handler();
			$this->fail( 'Expected the success path to attempt a redirect.' );
		} catch ( TML_Test_Redirect_Exception $e ) {
			$this->assertStringContainsString( 'custom-redirect', $captured );
		}
	}

	public function test_valid_credentials_with_no_redirect_to_go_to_the_admin_url() {
		global $wp;

		$wp->query_vars['action'] = 'login';

		// A subscriber can't edit_posts, so the admin_url()-fallback branch
		// routes them to the 'dashboard' action instead of wp-admin.
		$user = self::factory()->user->create_and_get( array( 'role' => 'subscriber' ) );
		$this->valid_login_post( $user );

		$captured = null;
		add_filter( 'wp_redirect', function ( $location ) use ( &$captured ) {
			$captured = $location;
			throw new TML_Test_Redirect_Exception();
		} );

		try {
			tml_login_handler();
			$this->fail( 'Expected the success path to attempt a redirect.' );
		} catch ( TML_Test_Redirect_Exception $e ) {
			$this->assertStringContainsString( 'action=dashboard', $captured );
		}
	}
}
