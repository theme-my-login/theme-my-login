<?php
/**
 * Coverage for the exit-free parts of src/admin/extensions.php: the
 * extensions feed transient (success, caching, HTTP/API error handling)
 * and the guard clauses on the license form handler / license-check
 * cron-ish gate. The AJAX license activate/deactivate handlers end in
 * tml_send_ajax_*(), which fall through to a raw die() since DOING_AJAX
 * is never defined here (see test-extensions.php's sibling note in
 * project memory) — deliberately not covered.
 *
 * admin/extensions.php is normally only required when is_admin() is
 * true, which it isn't in the default (front-end) test bootstrap, so
 * it's pulled in directly here, same pattern as test-admin-functions.php.
 *
 * @package Theme_My_Login
 */

if ( ! function_exists( 'tml_admin_get_extensions_feed' ) ) {
	require THEME_MY_LOGIN_PATH . 'admin/extensions.php';
}

class Test_Admin_Extensions extends WP_UnitTestCase {

	protected $http_request_count = 0;

	public function tearDown(): void {
		global $plugin_page;

		$plugin_page = null;
		$_POST       = array();

		foreach ( array_keys( tml_get_extensions() ) as $name ) {
			tml_unregister_extension( $name );
		}

		delete_site_option( '_tml_test_ext_license_key' );
		delete_site_option( '_tml_test_ext_license_status' );

		remove_all_filters( 'pre_http_request' );

		delete_site_transient( 'tml_extensions_feed-' . md5( http_build_query( array( 'number' => 12 ) ) ) );

		parent::tearDown();
	}

	protected function mock_http_response( $body, $code = 200 ) {
		add_filter( 'pre_http_request', function () use ( $body, $code ) {
			$this->http_request_count++;

			return array(
				'response' => array( 'code' => $code, 'message' => 'OK' ),
				'body'     => is_string( $body ) ? $body : wp_json_encode( $body ),
				'headers'  => array(),
			);
		}, 10, 3 );
	}

	protected function mock_http_error() {
		add_filter( 'pre_http_request', function () {
			$this->http_request_count++;

			return new WP_Error( 'http_request_failed', 'Connection timed out.' );
		}, 10, 3 );
	}

	public function test_get_extensions_feed_returns_the_decoded_products() {
		$this->mock_http_response( array( 'products' => array( array( 'info' => array( 'title' => 'Test Extension' ) ) ) ) );

		$feed = tml_admin_get_extensions_feed();

		$this->assertCount( 1, $feed );
		$this->assertSame( 'Test Extension', $feed[0]->info->title );
	}

	public function test_get_extensions_feed_caches_the_response_across_calls() {
		$this->mock_http_response( array( 'products' => array() ) );

		tml_admin_get_extensions_feed();
		tml_admin_get_extensions_feed();

		$this->assertSame( 1, $this->http_request_count );
	}

	public function test_get_extensions_feed_returns_a_wp_error_when_the_request_fails() {
		$this->mock_http_error();

		$feed = tml_admin_get_extensions_feed();

		$this->assertWPError( $feed );
	}

	public function test_get_extensions_feed_returns_a_wp_error_on_a_non_200_response() {
		$this->mock_http_response( array( 'products' => array() ), 404 );

		$feed = tml_admin_get_extensions_feed();

		$this->assertWPError( $feed );
		$this->assertSame( 'http_error_404', $feed->get_error_code() );
	}

	public function test_get_extensions_feed_passes_the_number_argument_to_the_transient_key() {
		$this->mock_http_response( array( 'products' => array( 1, 2, 3 ) ) );

		$feed = tml_admin_get_extensions_feed( array( 'number' => 3 ) );

		$this->assertCount( 3, $feed );

		delete_site_transient( 'tml_extensions_feed-' . md5( http_build_query( array( 'number' => 3 ) ) ) );
	}

	public function test_handle_extension_licenses_is_a_no_op_for_a_get_request() {
		$_SERVER['REQUEST_METHOD'] = 'GET';

		// Should return early without hitting check_admin_referer()/wp_die().
		tml_admin_handle_extension_licenses();

		$this->assertTrue( true );
	}

	public function test_handle_extension_licenses_is_a_no_op_for_an_unrelated_options_page() {
		$_SERVER['REQUEST_METHOD'] = 'POST';
		$_POST['option_page']     = 'some-other-options-page';

		// Should return early without hitting check_admin_referer()/wp_die().
		tml_admin_handle_extension_licenses();

		$this->assertTrue( true );
	}

	public function test_check_extension_licenses_is_a_no_op_for_a_post_request() {
		global $plugin_page;

		$_SERVER['REQUEST_METHOD'] = 'POST';
		$plugin_page               = 'theme-my-login-licenses';

		tml_admin_check_extension_licenses();

		$this->assertTrue( true );
	}

	public function test_check_extension_licenses_is_a_no_op_for_an_unrelated_page() {
		global $plugin_page;

		$_SERVER['REQUEST_METHOD'] = 'GET';
		$plugin_page               = 'some-other-page';

		tml_admin_check_extension_licenses();

		$this->assertTrue( true );
	}

	public function test_check_extension_licenses_skips_extensions_without_a_license_key() {
		global $plugin_page;

		$_SERVER['REQUEST_METHOD'] = 'GET';
		$plugin_page               = 'theme-my-login-licenses';

		$this->mock_http_response( array( 'license' => 'valid' ) );

		tml_admin_check_extension_licenses();

		$this->assertSame( 0, $this->http_request_count );
	}

	public function test_extension_update_message_prompts_for_a_license_when_no_package_is_present() {
		ob_start();
		tml_admin_extension_update_message( array(), (object) array() );
		$output = ob_get_clean();

		$this->assertStringContainsString( 'license key', $output );
	}

	public function test_extension_update_message_is_silent_when_a_package_is_present() {
		ob_start();
		tml_admin_extension_update_message( array(), (object) array( 'package' => 'https://example.org/download.zip' ) );
		$output = ob_get_clean();

		$this->assertSame( '', $output );
	}

	public function test_add_extension_update_messages_wires_the_hook_for_each_extension() {
		$extension = new TML_Test_Extension( WP_PLUGIN_DIR . '/tml-test-extension/tml-test-extension.php', array(
			'name'                  => 'tml-test-extension',
			'license_key_option'    => '_tml_test_ext_license_key',
			'license_status_option' => '_tml_test_ext_license_status',
		) );

		tml_register_extension( $extension );

		tml_admin_add_extension_update_messages();

		$this->assertNotFalse( has_action( 'in_plugin_update_message-' . $extension->get_basename(), 'tml_admin_extension_update_message' ) );

		remove_all_actions( 'in_plugin_update_message-' . $extension->get_basename() );
	}
}
