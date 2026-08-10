<?php
/**
 * Coverage for tml_logout_handler(): the default and custom redirect
 * destinations, the logout_redirect filter, and the wp_logout() side
 * effect. Redirect captured via the wp_redirect filter — see
 * test-login-handler.php's doc comment for why this works without
 * process isolation.
 *
 * @package Theme_My_Login
 */

if ( ! class_exists( 'TML_Test_Redirect_Exception' ) ) {
	class TML_Test_Redirect_Exception extends Exception {}
}

class Test_Logout_Handler extends WP_UnitTestCase {

	public function tearDown(): void {
		$_REQUEST = array();
		remove_all_filters( 'wp_redirect' );
		remove_all_filters( 'logout_redirect' );

		parent::tearDown();
	}

	protected function capture_redirect( callable $callback ) {
		$captured = null;
		add_filter( 'wp_redirect', function ( $location ) use ( &$captured ) {
			$captured = $location;
			throw new TML_Test_Redirect_Exception();
		} );

		try {
			$callback();
			$this->fail( 'Expected a redirect.' );
		} catch ( TML_Test_Redirect_Exception $e ) {
			return $captured;
		}
	}

	public function test_default_redirect_goes_to_the_login_url() {
		wp_set_current_user( self::factory()->user->create() );
		$_REQUEST['_wpnonce'] = wp_create_nonce( 'log-out' );

		$captured = $this->capture_redirect( 'tml_logout_handler' );

		$this->assertStringContainsString( 'action=login', $captured );
		$this->assertStringContainsString( 'loggedout=true', $captured );
	}

	public function test_custom_redirect_to_is_used() {
		wp_set_current_user( self::factory()->user->create() );
		$_REQUEST['_wpnonce']    = wp_create_nonce( 'log-out' );
		$_REQUEST['redirect_to'] = home_url( '/custom-after-logout' );

		$captured = $this->capture_redirect( 'tml_logout_handler' );

		$this->assertSame( home_url( '/custom-after-logout' ), $captured );
	}

	public function test_logout_redirect_filter_receives_the_requested_destination() {
		wp_set_current_user( self::factory()->user->create() );
		$_REQUEST['_wpnonce']    = wp_create_nonce( 'log-out' );
		$_REQUEST['redirect_to'] = home_url( '/wants-to-go-here' );

		$received_requested = 'not-called';
		add_filter( 'logout_redirect', function ( $redirect_to, $requested_redirect_to ) use ( &$received_requested ) {
			$received_requested = $requested_redirect_to;
			return $redirect_to;
		}, 10, 2 );

		$this->capture_redirect( 'tml_logout_handler' );

		$this->assertSame( home_url( '/wants-to-go-here' ), $received_requested );
	}

	public function test_the_current_user_is_logged_out() {
		wp_set_current_user( self::factory()->user->create() );
		$_REQUEST['_wpnonce'] = wp_create_nonce( 'log-out' );

		$this->capture_redirect( 'tml_logout_handler' );

		$this->assertSame( 0, get_current_user_id() );
	}
}
