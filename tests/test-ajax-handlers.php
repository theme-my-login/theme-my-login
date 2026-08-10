<?php
/**
 * Coverage for TML's AJAX response paths: the core
 * tml_send_ajax_success()/tml_send_ajax_error() wrappers, the AJAX
 * branches of the login/registration/lostpassword handlers, and the
 * extension license activate/deactivate AJAX handlers.
 *
 * Extends WP_Ajax_UnitTestCase (bundled with wp-phpunit specifically for
 * this) instead of WP_UnitTestCase: its set_up() filters wp_doing_ajax()
 * true and overrides wp_die_ajax_handler so wp_send_json_*() throws a
 * catchable WPAjaxDieStopException/WPAjaxDieContinueException instead of
 * a real die(), with the JSON payload captured in $this->_last_response.
 * No production code changes needed for this — see project memory for
 * why defining DOING_AJAX globally would be a bad idea regardless.
 *
 * admin/extensions.php is only required when is_admin() is true, same
 * gating as elsewhere in this suite, so it's pulled in directly here.
 *
 * @package Theme_My_Login
 */

if ( ! function_exists( 'tml_admin_get_extensions_feed' ) ) {
	require THEME_MY_LOGIN_PATH . 'admin/extensions.php';
}

class Test_Ajax_Handlers extends WP_Ajax_UnitTestCase {

	public function tearDown(): void {
		global $wp;

		tml_get_form( 'login' )->set_errors( new WP_Error() );
		tml_get_form( 'register' )->set_errors( new WP_Error() );
		tml_get_form( 'lostpassword' )->set_errors( new WP_Error() );

		$_GET = $_POST = $_REQUEST = array();
		$_SERVER['REQUEST_METHOD'] = 'GET';
		unset( $wp->query_vars['action'] );

		delete_option( 'users_can_register' );

		foreach ( array_keys( tml_get_extensions() ) as $name ) {
			tml_unregister_extension( $name );
		}

		remove_all_filters( 'pre_http_request' );

		parent::tearDown();
	}

	protected function assertAjaxSuccess() {
		$response = json_decode( $this->_last_response, true );
		$this->assertTrue( $response['success'] );

		return $response['data'];
	}

	protected function assertAjaxError() {
		$response = json_decode( $this->_last_response, true );
		$this->assertFalse( $response['success'] );

		return $response['data'];
	}

	// tml_send_ajax_success() / tml_send_ajax_error()

	public function test_send_ajax_success_outputs_the_data() {
		try {
			ob_start();
			tml_send_ajax_success( array( 'foo' => 'bar' ) );
			$this->fail( 'Expected wp_send_json_success() to abort execution.' );
		} catch ( WPAjaxDieContinueException $e ) {
			$data = $this->assertAjaxSuccess();
			$this->assertSame( 'bar', $data['foo'] );
		}
	}

	public function test_send_ajax_success_data_is_filterable() {
		add_filter( 'tml_ajax_success_data', function () {
			return array( 'filtered' => true );
		} );

		try {
			ob_start();
			tml_send_ajax_success( array( 'foo' => 'bar' ) );
		} catch ( WPAjaxDieContinueException $e ) {
			$data = $this->assertAjaxSuccess();
			$this->assertTrue( $data['filtered'] );
			$this->assertArrayNotHasKey( 'foo', $data );
		}

		remove_all_filters( 'tml_ajax_success_data' );
	}

	public function test_send_ajax_error_outputs_the_data() {
		try {
			ob_start();
			tml_send_ajax_error( 'Something went wrong.' );
			$this->fail( 'Expected wp_send_json_error() to abort execution.' );
		} catch ( WPAjaxDieContinueException $e ) {
			$data = $this->assertAjaxError();
			$this->assertSame( 'Something went wrong.', $data );
		}
	}

	// tml_login_handler() AJAX branches

	public function test_login_handler_ajax_success() {
		global $wp;

		$wp->query_vars['action'] = 'login';

		$user = self::factory()->user->create_and_get( array( 'user_pass' => 'a-correct-password' ) );
		wp_set_password( 'a-correct-password', $user->ID );

		$_POST['log']         = $user->user_login;
		$_POST['pwd']         = 'a-correct-password';
		$_REQUEST['ajax']     = '1';
		$_REQUEST['redirect_to'] = 'https://example.org/custom-redirect';
		$_COOKIE[ TEST_COOKIE ] = 'WP Cookie check';
		// wp-phpunit's own bootstrap echoes status text before any test runs,
		// so headers_sent() is always true in this CLI runner — skip the
		// (irrelevant here) "did output leak" branch this would otherwise hit.
		$_COOKIE[ LOGGED_IN_COOKIE ] = 'dummy-value-to-skip-the-cookie-delivery-check';

		try {
			ob_start();
			tml_login_handler();
			$this->fail( 'Expected a successful AJAX login to send a JSON response.' );
		} catch ( WPAjaxDieContinueException $e ) {
			// wp_signon() only sets the auth cookie for the *next* request
			// (via setcookie(), not wp_set_current_user()) — same as real
			// WP behavior, which is why login normally redirects — so
			// is_user_logged_in() is correctly still false here; the
			// success signal is the redirect data itself.
			$data = $this->assertAjaxSuccess();
			$this->assertStringContainsString( 'custom-redirect', $data['redirect'] );
		}
	}

	public function test_login_handler_ajax_failure() {
		global $wp;

		$wp->query_vars['action'] = 'login';

		$_POST['log']     = 'no-such-user';
		$_POST['pwd']     = 'wrong-password';
		$_REQUEST['ajax'] = '1';

		try {
			ob_start();
			tml_login_handler();
			$this->fail( 'Expected a failed AJAX login to send a JSON error response.' );
		} catch ( WPAjaxDieContinueException $e ) {
			$data = $this->assertAjaxError();
			$this->assertStringContainsString( 'tml-errors', $data['errors'] );
			$this->assertFalse( is_user_logged_in() );
		}
	}

	// tml_registration_handler() AJAX branches

	public function test_registration_handler_ajax_disabled() {
		global $wp;

		$wp->query_vars['action'] = 'register';
		update_option( 'users_can_register', 0 );

		$_SERVER['REQUEST_METHOD'] = 'POST';
		$_REQUEST['ajax']          = '1';

		try {
			ob_start();
			tml_registration_handler();
			$this->fail( 'Expected disabled registration to send a JSON error response.' );
		} catch ( WPAjaxDieContinueException $e ) {
			$data = $this->assertAjaxError();
			$this->assertStringContainsString( 'not allowed', $data['errors'] );
		}
	}

	public function test_registration_handler_ajax_success() {
		global $wp;

		$wp->query_vars['action'] = 'register';
		update_option( 'users_can_register', 1 );

		$_SERVER['REQUEST_METHOD'] = 'POST';
		$_REQUEST['ajax']          = '1';
		$_POST['user_login']       = 'ajaxregistrant';
		$_POST['user_email']       = 'ajaxregistrant@example.org';

		try {
			ob_start();
			tml_registration_handler();
			$this->fail( 'Expected a successful AJAX registration to send a JSON response.' );
		} catch ( WPAjaxDieContinueException $e ) {
			$data = $this->assertAjaxSuccess();
			$this->assertNotEmpty( $data['redirect'] );
			$this->assertNotFalse( get_user_by( 'login', 'ajaxregistrant' ) );
		}
	}

	public function test_registration_handler_ajax_validation_error() {
		global $wp;

		$wp->query_vars['action'] = 'register';
		update_option( 'users_can_register', 1 );

		$_SERVER['REQUEST_METHOD'] = 'POST';
		$_REQUEST['ajax']          = '1';
		$_POST['user_login']       = '';
		$_POST['user_email']       = 'not-an-email';

		try {
			ob_start();
			tml_registration_handler();
			$this->fail( 'Expected an invalid AJAX registration to send a JSON error response.' );
		} catch ( WPAjaxDieContinueException $e ) {
			$this->assertAjaxError();
		}
	}

	// tml_lost_password_handler() AJAX branches

	public function test_lost_password_handler_ajax_success() {
		global $wp;

		$wp->query_vars['action'] = 'lostpassword';

		$user = self::factory()->user->create_and_get();

		$_SERVER['REQUEST_METHOD'] = 'POST';
		$_REQUEST['ajax']          = '1';
		$_POST['user_login']       = $user->user_login;

		try {
			ob_start();
			tml_lost_password_handler();
			$this->fail( 'Expected a successful AJAX lost-password request to send a JSON response.' );
		} catch ( WPAjaxDieContinueException $e ) {
			$data = $this->assertAjaxSuccess();
			$this->assertStringContainsString( 'Check your email', $data['notice'] );
		}
	}

	public function test_lost_password_handler_ajax_failure() {
		global $wp;

		$wp->query_vars['action'] = 'lostpassword';

		$_SERVER['REQUEST_METHOD'] = 'POST';
		$_REQUEST['ajax']          = '1';
		$_POST['user_login']       = 'no-such-user';

		try {
			ob_start();
			tml_lost_password_handler();
			$this->fail( 'Expected a failed AJAX lost-password request to send a JSON error response.' );
		} catch ( WPAjaxDieContinueException $e ) {
			$this->assertAjaxError();
		}
	}

	// Extension license AJAX handlers

	protected function make_licensed_extension() {
		return tml_register_extension( new TML_Test_Extension( WP_PLUGIN_DIR . '/tml-test-extension/tml-test-extension.php', array(
			'name'                  => 'tml-test-extension',
			'store_url'             => 'https://example.org/edd-api',
			'license_key_option'    => '_tml_test_ext_license_key',
			'license_status_option' => '_tml_test_ext_license_status',
		) ) );
	}

	protected function mock_license_http_response( $body, $code = 200 ) {
		add_filter( 'pre_http_request', function () use ( $body, $code ) {
			return array(
				'response' => array( 'code' => $code, 'message' => 'OK' ),
				'body'     => wp_json_encode( $body ),
				'headers'  => array(),
			);
		}, 10, 3 );
	}

	public function test_ajax_activate_license_fails_the_nonce_check() {
		$this->make_licensed_extension();

		$_POST['_wpnonce'] = 'not-a-valid-nonce';

		try {
			ob_start();
			tml_admin_ajax_activate_extension_license();
			$this->fail( 'Expected an invalid nonce to send a JSON error response.' );
		} catch ( WPAjaxDieContinueException $e ) {
			$data = $this->assertAjaxError();
			$this->assertStringContainsString( 'There was an authentication problem', $data );
		}

		delete_site_option( '_tml_test_ext_license_key' );
		delete_site_option( '_tml_test_ext_license_status' );
	}

	public function test_ajax_activate_license_succeeds_for_an_administrator() {
		$extension = $this->make_licensed_extension();

		wp_set_current_user( self::factory()->user->create( array( 'role' => 'administrator' ) ) );

		$_POST['_wpnonce']    = $_REQUEST['_wpnonce'] = wp_create_nonce( 'theme-my-login-licenses-options' );
		$_POST['extension']   = 'tml-test-extension';
		$_POST['key']         = 'a-license-key';

		$this->mock_license_http_response( array( 'success' => true, 'license' => 'valid' ) );

		try {
			ob_start();
			tml_admin_ajax_activate_extension_license();
			$this->fail( 'Expected a successful activation to send a JSON response.' );
		} catch ( WPAjaxDieContinueException $e ) {
			$data = $this->assertAjaxSuccess();
			$this->assertSame( 'Active', $data );
			$this->assertSame( 'valid', $extension->get_license_status() );
		}

		delete_site_option( '_tml_test_ext_license_key' );
		delete_site_option( '_tml_test_ext_license_status' );
	}

	public function test_ajax_activate_license_rejects_a_non_admin_user() {
		$this->make_licensed_extension();

		$_POST['_wpnonce']  = $_REQUEST['_wpnonce'] = wp_create_nonce( 'theme-my-login-licenses-options' );
		$_POST['extension'] = 'tml-test-extension';
		$_POST['key']       = 'a-license-key';

		try {
			ob_start();
			tml_admin_ajax_activate_extension_license();
			$this->fail( 'Expected a logged-out user to be rejected with a JSON error response.' );
		} catch ( WPAjaxDieContinueException $e ) {
			$data = $this->assertAjaxError();
			$this->assertStringContainsString( 'not allowed', $data );
		}

		delete_site_option( '_tml_test_ext_license_key' );
		delete_site_option( '_tml_test_ext_license_status' );
	}

	public function test_ajax_deactivate_license_succeeds_for_an_administrator() {
		$extension = $this->make_licensed_extension();
		$extension->set_license_key( 'a-license-key' );
		$extension->set_license_status( 'valid' );

		wp_set_current_user( self::factory()->user->create( array( 'role' => 'administrator' ) ) );

		$_POST['_wpnonce']  = $_REQUEST['_wpnonce'] = wp_create_nonce( 'theme-my-login-licenses-options' );
		$_POST['extension'] = 'tml-test-extension';

		$this->mock_license_http_response( array( 'success' => true, 'license' => 'deactivated' ) );

		try {
			ob_start();
			tml_admin_ajax_deactivate_extension_license();
			$this->fail( 'Expected a successful deactivation to send a JSON response.' );
		} catch ( WPAjaxDieContinueException $e ) {
			$data = $this->assertAjaxSuccess();
			$this->assertSame( 'Inactive', $data );
			$this->assertSame( '', $extension->get_license_status() );
		}

		delete_site_option( '_tml_test_ext_license_key' );
		delete_site_option( '_tml_test_ext_license_status' );
	}
}
