<?php
/**
 * Coverage for src/includes/extensions.php: the extension registry
 * (register/unregister/get/exists), the EDD-flavored license activation,
 * deactivation, and check calls, the shared tml_extension_api_call()
 * request-scoped and persistent caching, and the WP
 * plugins-API/update-transient integrations.
 *
 * Theme_My_Login_Extension is abstract, so tests use a minimal concrete
 * subclass with test-only license option names and store URL.
 *
 * @package Theme_My_Login
 */

class TML_Test_Extension extends Theme_My_Login_Extension {
	public function __construct( $file, $args = array() ) {
		foreach ( $args as $key => $value ) {
			$this->$key = $value;
		}
		parent::__construct( $file );
	}
}

class Test_Extensions extends WP_UnitTestCase {

	protected $http_request_count = 0;

	public function tearDown(): void {
		foreach ( array_keys( tml_get_extensions() ) as $name ) {
			tml_unregister_extension( $name );
		}

		delete_site_option( '_tml_test_ext_license_key' );
		delete_site_option( '_tml_test_ext_license_status' );

		remove_all_filters( 'pre_http_request' );
		remove_all_filters( 'tml_extension_exists' );

		wp_cache_flush_group( 'tml_api_calls' );

		global $wpdb;
		$wpdb->query( $wpdb->prepare( "DELETE FROM $wpdb->options WHERE option_name LIKE %s", $wpdb->esc_like( '_site_transient_tml_api_call-' ) . '%' ) );

		parent::tearDown();
	}

	protected function make_extension( $args = array() ) {
		return new TML_Test_Extension( WP_PLUGIN_DIR . '/tml-test-extension/tml-test-extension.php', array_merge( array(
			'name'                  => 'tml-test-extension',
			'version'               => '1.0',
			'store_url'             => 'https://example.org/edd-api',
			'item_id'               => 123,
			'license_key_option'    => '_tml_test_ext_license_key',
			'license_status_option' => '_tml_test_ext_license_status',
		), $args ) );
	}

	protected function mock_http_response( $body, $code = 200 ) {
		add_filter( 'pre_http_request', function ( $preempt, $parsed_args, $url ) use ( $body, $code ) {
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

	protected function capture_http_request_args( $body ) {
		$captured = new stdClass();

		add_filter( 'pre_http_request', function ( $preempt, $parsed_args, $url ) use ( $body, &$captured ) {
			$this->http_request_count++;
			$captured->args = $parsed_args['body'];

			return array(
				'response' => array( 'code' => 200, 'message' => 'OK' ),
				'body'     => is_string( $body ) ? $body : wp_json_encode( $body ),
				'headers'  => array(),
			);
		}, 10, 3 );

		return $captured;
	}

	public function test_register_extension_adds_it_to_the_registry() {
		$extension = $this->make_extension();

		$registered = tml_register_extension( $extension );

		$this->assertSame( $extension, $registered );
		$this->assertSame( $extension, tml_get_extension( 'tml-test-extension' ) );
		$this->assertArrayHasKey( 'tml-test-extension', tml_get_extensions() );
	}

	public function test_register_extension_rejects_a_non_extension_argument() {
		$this->assertFalse( tml_register_extension( 'not-an-extension' ) );
	}

	public function test_unregister_extension_by_name() {
		tml_register_extension( $this->make_extension() );
		tml_unregister_extension( 'tml-test-extension' );

		$this->assertFalse( tml_get_extension( 'tml-test-extension' ) );
	}

	public function test_unregister_extension_by_object() {
		$extension = $this->make_extension();

		tml_register_extension( $extension );
		tml_unregister_extension( $extension );

		$this->assertFalse( tml_get_extension( 'tml-test-extension' ) );
	}

	public function test_get_extension_passes_through_an_object_unchanged() {
		$extension = $this->make_extension();

		$this->assertSame( $extension, tml_get_extension( $extension ) );
	}

	public function test_extension_exists_reflects_the_registry() {
		$this->assertFalse( tml_extension_exists( 'tml-test-extension' ) );

		tml_register_extension( $this->make_extension() );

		$this->assertTrue( tml_extension_exists( 'tml-test-extension' ) );
	}

	public function test_extension_exists_can_be_filtered() {
		add_filter( 'tml_extension_exists', function () {
			return true;
		} );

		$this->assertTrue( tml_extension_exists( 'does-not-exist' ) );
	}

	public function test_activate_extension_license_returns_false_for_an_unknown_extension() {
		$this->assertFalse( tml_activate_extension_license( 'does-not-exist' ) );
	}

	public function test_activate_extension_license_returns_the_license_on_success() {
		$extension = tml_register_extension( $this->make_extension() );

		$this->mock_http_response( array( 'success' => true, 'license' => 'valid' ) );

		$this->assertSame( 'valid', tml_activate_extension_license( $extension ) );
	}

	public function test_activate_extension_license_returns_a_wp_error_for_a_known_edd_error() {
		$extension = tml_register_extension( $this->make_extension() );

		$this->mock_http_response( array( 'success' => false, 'error' => 'disabled' ) );

		$result = tml_activate_extension_license( $extension );

		$this->assertWPError( $result );
		$this->assertSame( 'disabled', $result->get_error_code() );
	}

	public function test_activate_extension_license_returns_a_wp_error_when_the_request_fails() {
		$extension = tml_register_extension( $this->make_extension() );

		$this->mock_http_error();

		$result = tml_activate_extension_license( $extension );

		$this->assertWPError( $result );
		$this->assertSame( 'http_error', $result->get_error_code() );
	}

	public function test_activate_extension_license_returns_a_wp_error_on_a_non_200_response() {
		$extension = tml_register_extension( $this->make_extension() );

		$this->mock_http_response( array( 'success' => true, 'license' => 'valid' ), 500 );

		$result = tml_activate_extension_license( $extension );

		$this->assertWPError( $result );
		$this->assertSame( 'http_error', $result->get_error_code() );
	}

	public function test_deactivate_extension_license_returns_the_license_on_success() {
		$extension = tml_register_extension( $this->make_extension() );

		$this->mock_http_response( array( 'success' => true, 'license' => 'valid' ) );

		$this->assertSame( 'valid', tml_deactivate_extension_license( $extension ) );
	}

	public function test_deactivate_extension_license_returns_a_wp_error_on_failure() {
		$extension = tml_register_extension( $this->make_extension() );

		$this->mock_http_response( array( 'success' => false ) );

		$result = tml_deactivate_extension_license( $extension );

		$this->assertWPError( $result );
		$this->assertSame( 'deactivation_failed', $result->get_error_code() );
	}

	public function test_check_extension_license_returns_the_license_on_success() {
		$extension = tml_register_extension( $this->make_extension() );

		$this->mock_http_response( array( 'license' => 'valid' ) );

		$this->assertSame( 'valid', tml_check_extension_license( $extension ) );
	}

	public function test_check_extension_license_returns_a_wp_error_when_the_request_fails() {
		$extension = tml_register_extension( $this->make_extension() );

		$this->mock_http_error();

		$result = tml_check_extension_license( $extension );

		$this->assertWPError( $result );
	}

	public function test_extension_api_call_caches_the_response_across_calls() {
		$extension = tml_register_extension( $this->make_extension() );

		$this->mock_http_response( array( 'license' => 'valid' ) );

		tml_check_extension_license( $extension );
		tml_check_extension_license( $extension );

		$this->assertSame( 1, $this->http_request_count );
	}

	public function test_extension_api_call_persists_version_checks_in_a_site_transient() {
		tml_register_extension( $this->make_extension() );

		$this->mock_http_response( array( 'new_version' => '2.0' ) );

		tml_add_extension_data_to_plugins_transient( (object) array() );

		// Simulate a fresh request: the non-persistent object cache is gone,
		// but the site transient survives.
		wp_cache_flush_group( 'tml_api_calls' );

		tml_add_extension_data_to_plugins_transient( (object) array() );

		$this->assertSame( 1, $this->http_request_count );
	}

	public function test_extension_api_call_does_not_persist_license_checks_across_requests() {
		$extension = tml_register_extension( $this->make_extension() );

		$this->mock_http_response( array( 'license' => 'valid' ) );

		tml_check_extension_license( $extension );

		// Simulate a fresh request: license checks must always hit the store
		// live, so this should not be served from a persistent cache.
		wp_cache_flush_group( 'tml_api_calls' );

		tml_check_extension_license( $extension );

		$this->assertSame( 2, $this->http_request_count );
	}

	public function test_clear_extension_version_check_cache_forces_a_live_recheck() {
		tml_register_extension( $this->make_extension() );

		$this->mock_http_response( array( 'new_version' => '2.0' ) );

		tml_add_extension_data_to_plugins_transient( (object) array() );

		// Simulate WordPress invalidating its own update_plugins transient
		// (e.g. after installing/updating a plugin).
		delete_site_transient( 'update_plugins' );

		wp_cache_flush_group( 'tml_api_calls' );

		tml_add_extension_data_to_plugins_transient( (object) array() );

		$this->assertSame( 2, $this->http_request_count );
	}

	public function test_add_extension_data_to_plugins_api_ignores_unrelated_actions() {
		$result = tml_add_extension_data_to_plugins_api( 'original', 'query_plugins', (object) array( 'slug' => 'tml-test-extension' ) );

		$this->assertSame( 'original', $result );
	}

	public function test_add_extension_data_to_plugins_api_ignores_unknown_extensions() {
		$result = tml_add_extension_data_to_plugins_api( 'original', 'plugin_information', (object) array( 'slug' => 'does-not-exist' ) );

		$this->assertSame( 'original', $result );
	}

	public function test_add_extension_data_to_plugins_api_falls_back_to_new_version() {
		tml_register_extension( $this->make_extension() );

		$this->mock_http_response( array( 'new_version' => '2.0' ) );

		$result = tml_add_extension_data_to_plugins_api( 'original', 'plugin_information', (object) array( 'slug' => 'tml-test-extension' ) );

		$this->assertSame( '2.0', $result->version );
	}

	public function test_add_extension_data_to_plugins_api_sends_the_site_url() {
		tml_register_extension( $this->make_extension() );

		$captured = $this->capture_http_request_args( array( 'new_version' => '2.0' ) );

		tml_add_extension_data_to_plugins_api( 'original', 'plugin_information', (object) array( 'slug' => 'tml-test-extension' ) );

		$this->assertSame( home_url(), $captured->args['url'] );
	}

	public function test_add_extension_data_to_plugins_transient_flags_an_available_update() {
		$extension = tml_register_extension( $this->make_extension() );

		$this->mock_http_response( array( 'new_version' => '2.0', 'package' => 'https://example.org/download.zip' ) );

		$transient = tml_add_extension_data_to_plugins_transient( (object) array() );

		$this->assertArrayHasKey( $extension->get_basename(), $transient->response );
		$this->assertSame( '2.0', $transient->response[ $extension->get_basename() ]->new_version );
	}

	public function test_add_extension_data_to_plugins_transient_reports_no_update_when_current() {
		$extension = tml_register_extension( $this->make_extension() );

		$this->mock_http_response( array( 'new_version' => '1.0' ) );

		$transient = tml_add_extension_data_to_plugins_transient( (object) array() );

		$this->assertArrayHasKey( $extension->get_basename(), $transient->no_update );
		$this->assertArrayNotHasKey( $extension->get_basename(), (array) ( $transient->response ?? array() ) );
	}

	public function test_add_extension_data_to_plugins_transient_sends_the_site_url() {
		tml_register_extension( $this->make_extension() );

		$captured = $this->capture_http_request_args( array( 'new_version' => '2.0', 'package' => 'https://example.org/download.zip' ) );

		tml_add_extension_data_to_plugins_transient( (object) array() );

		$this->assertSame( home_url(), $captured->args['url'] );
	}

	public function test_extension_api_call_unserializes_serialized_array_fields() {
		tml_register_extension( $this->make_extension() );

		$this->mock_http_response( array(
			'new_version' => '2.0',
			'icons'       => serialize( array( '1x' => 'https://example.org/icon.png' ) ),
			'banners'     => serialize( array( 'low' => 'https://example.org/banner.png' ) ),
		) );

		$transient = tml_add_extension_data_to_plugins_transient( (object) array() );
		$update    = $transient->response[ $this->make_extension()->get_basename() ];

		$this->assertSame( array( '1x' => 'https://example.org/icon.png' ), $update->icons );
		$this->assertSame( array( 'low' => 'https://example.org/banner.png' ), $update->banners );
	}

	public function test_extension_api_call_blocks_object_injection_in_serialized_fields() {
		tml_register_extension( $this->make_extension() );

		$this->mock_http_response( array(
			'new_version' => '2.0',
			'icons'       => serialize( new TML_Test_Extension( WP_PLUGIN_DIR . '/tml-test-extension/tml-test-extension.php' ) ),
		) );

		$transient = tml_add_extension_data_to_plugins_transient( (object) array() );
		$update    = $transient->response[ $this->make_extension()->get_basename() ];

		$this->assertNotInstanceOf( TML_Test_Extension::class, $update->icons );
	}
}
