<?php
/**
 * Coverage for src/admin/settings.php: the settings sections/fields
 * definitions, wiring them up via tml_admin_register_settings(), the
 * field-rendering callbacks, and the exit-free guard clauses in
 * tml_admin_save_ms_settings()/tml_admin_add_settings_help_tabs().
 *
 * admin/settings.php (and its admin/ siblings it depends on) are normally
 * only required when is_admin() is true, which it isn't in the default
 * (front-end) test bootstrap, so they're pulled in directly here, same
 * pattern as test-admin-functions.php/test-admin-class.php.
 *
 * tml_admin_save_ms_settings()'s success path ends in wp_redirect()+exit
 * and is deliberately not covered, per this suite's existing policy on
 * exit/redirect paths.
 *
 * @package Theme_My_Login
 */

if ( ! function_exists( 'tml_admin_is_plugin_page' ) ) {
	require THEME_MY_LOGIN_PATH . 'admin/functions.php';
}
if ( ! class_exists( 'Theme_My_Login_Admin' ) ) {
	require THEME_MY_LOGIN_PATH . 'admin/class-theme-my-login-admin.php';
}
if ( ! function_exists( 'tml_admin_register_settings' ) ) {
	require THEME_MY_LOGIN_PATH . 'admin/settings.php';
}

/**
 * A minimal WP_Screen double: real WP_Screen has no public constructor
 * for arbitrary slugs, and all tml_admin_add_settings_help_tabs() needs
 * is something to record add_help_tab()/set_help_sidebar() calls.
 */
class TML_Test_Screen {
	public $help_tabs = array();
	public $sidebar;

	public function add_help_tab( $args ) {
		$this->help_tabs[] = $args;
	}

	public function set_help_sidebar( $content ) {
		$this->sidebar = $content;
	}
}

class Test_Settings extends WP_UnitTestCase {

	public function setUp(): void {
		parent::setUp();

		// theme_my_login_admin()'s page registry is a singleton shared by
		// the whole process (see test-admin-class.php); make sure the
		// plugin's own admin pages are present before every test here,
		// since tml_admin_save_ms_settings()/tml_admin_add_settings_help_tabs()
		// gate on has_page().
		foreach ( array( 'theme-my-login', 'theme-my-login-licenses', 'theme-my-login-extensions' ) as $slug ) {
			if ( ! theme_my_login_admin()->has_page( $slug ) ) {
				theme_my_login_admin()->add_menu_item( array(
					'page_title'  => $slug,
					'menu_title'  => $slug,
					'menu_slug'   => $slug,
					'parent_slug' => '',
				) );
			}
		}
	}

	public function tearDown(): void {
		global $plugin_page, $wp_settings_sections, $wp_settings_fields, $wp_registered_settings;

		$plugin_page             = null;
		$wp_settings_sections     = array();
		$wp_settings_fields       = array();
		$wp_registered_settings   = array();

		delete_option( 'rewrite_rules' );

		foreach ( array_keys( tml_get_extensions() ) as $name ) {
			tml_unregister_extension( $name );
		}

		remove_all_filters( 'tml_admin_get_settings_sections' );
		remove_all_filters( 'tml_admin_get_settings_fields' );

		parent::tearDown();
	}

	// tml_admin_get_settings_sections()

	public function test_get_settings_sections_returns_the_default_sections() {
		$sections = tml_admin_get_settings_sections();

		$this->assertArrayHasKey( 'tml_settings_general', $sections );
		$this->assertArrayHasKey( 'tml_settings_login', $sections );
		$this->assertArrayHasKey( 'tml_settings_registration', $sections );
		$this->assertArrayHasKey( 'tml_settings_slugs', $sections );
	}

	public function test_get_settings_sections_can_be_filtered() {
		add_filter( 'tml_admin_get_settings_sections', function () {
			return array( 'custom' => array( 'title' => 'Custom' ) );
		} );

		$this->assertSame( array( 'custom' => array( 'title' => 'Custom' ) ), tml_admin_get_settings_sections() );
	}

	// tml_admin_get_settings_fields()

	public function test_get_settings_fields_includes_the_core_fields() {
		$fields = tml_admin_get_settings_fields();

		$this->assertArrayHasKey( 'tml_ajax', $fields['tml_settings_general'] );
		$this->assertArrayHasKey( 'tml_login_type', $fields['tml_settings_login'] );
		$this->assertArrayHasKey( 'tml_registration_type', $fields['tml_settings_registration'] );
		$this->assertArrayHasKey( 'tml_user_passwords', $fields['tml_settings_registration'] );
		$this->assertArrayHasKey( 'tml_auto_login', $fields['tml_settings_registration'] );
	}

	public function test_get_settings_fields_includes_a_slug_field_per_visible_action() {
		$fields = tml_admin_get_settings_fields();

		$this->assertArrayHasKey( 'tml_login_slug', $fields['tml_settings_slugs'] );
		$this->assertArrayNotHasKey( 'tml_confirmaction_slug', $fields['tml_settings_slugs'], 'confirmaction has show_in_slug_settings = false and should be excluded.' );
	}

	public function test_get_settings_fields_can_be_filtered() {
		add_filter( 'tml_admin_get_settings_fields', function () {
			return array( 'custom' => array() );
		} );

		$this->assertSame( array( 'custom' => array() ), tml_admin_get_settings_fields() );
	}

	// tml_admin_register_settings()

	public function test_register_settings_adds_the_core_sections_and_fields() {
		global $wp_settings_sections, $wp_settings_fields;

		tml_admin_register_settings();

		$this->assertArrayHasKey( 'tml_settings_general', $wp_settings_sections['theme-my-login'] );
		$this->assertArrayHasKey( 'tml_ajax', $wp_settings_fields['theme-my-login']['tml_settings_general'] );
	}

	public function test_register_settings_registers_the_core_options() {
		tml_admin_register_settings();

		$registered = get_registered_settings();

		$this->assertArrayHasKey( 'tml_ajax', $registered );
		$this->assertArrayHasKey( 'tml_login_type', $registered );
	}

	public function test_register_settings_adds_a_license_field_for_extensions_with_a_license_key_option() {
		global $wp_settings_fields;

		tml_register_extension( new TML_Test_Extension( WP_PLUGIN_DIR . '/tml-test-extension/tml-test-extension.php', array(
			'name'                  => 'tml-test-extension',
			'license_key_option'    => '_tml_test_ext_license_key',
			'license_status_option' => '_tml_test_ext_license_status',
		) ) );

		tml_admin_register_settings();

		$this->assertArrayHasKey( '_tml_test_ext_license_key', $wp_settings_fields['theme-my-login-licenses']['tml_settings_licenses'] );

		$registered = get_registered_settings();
		$this->assertArrayHasKey( '_tml_test_ext_license_key', $registered );

		delete_site_option( '_tml_test_ext_license_key' );
		delete_site_option( '_tml_test_ext_license_status' );
	}

	public function test_register_settings_is_safe_for_an_extension_without_a_license_key_option() {
		tml_register_extension( new TML_Test_Extension( WP_PLUGIN_DIR . '/tml-test-extension/tml-test-extension.php', array(
			'name' => 'tml-test-extension',
		) ) );

		// Should not raise a warning/error for an extension with no sections/fields/license option.
		tml_admin_register_settings();

		$this->assertTrue( true );
	}

	// Field rendering callbacks

	public function test_slugs_section_callback_renders_a_description() {
		ob_start();
		tml_admin_setting_callback_slugs_section();
		$output = ob_get_clean();

		$this->assertStringContainsString( '<p>', $output );
	}

	public function test_input_field_callback_renders_the_expected_attributes() {
		ob_start();
		tml_admin_setting_callback_input_field( array(
			'label_for'   => 'my_field',
			'value'       => 'my-value',
			'description' => 'A description.',
		) );
		$output = ob_get_clean();

		$this->assertStringContainsString( "name='my_field'", $output );
		$this->assertStringContainsString( "id='my_field'", $output );
		$this->assertStringContainsString( "value='my-value'", $output );
		$this->assertStringContainsString( "type='text'", $output );
		$this->assertStringContainsString( 'A description.', $output );
	}

	public function test_input_field_callback_renders_boolean_attributes_bare() {
		ob_start();
		tml_admin_setting_callback_input_field( array(
			'label_for'  => 'my_field',
			'attributes' => array( 'required' => true ),
		) );
		$output = ob_get_clean();

		$this->assertStringContainsString( 'required', $output );
		$this->assertStringNotContainsString( "required='1'", $output );
	}

	public function test_checkbox_field_callback_renders_checked_when_true() {
		ob_start();
		tml_admin_setting_callback_checkbox_field( array(
			'label_for' => 'my_checkbox',
			'label'     => 'My Checkbox',
			'checked'   => true,
		) );
		$output = ob_get_clean();

		$this->assertStringContainsString( "checked='checked'", $output );
		$this->assertStringContainsString( 'My Checkbox', $output );
	}

	public function test_checkbox_field_callback_omits_checked_when_false() {
		ob_start();
		tml_admin_setting_callback_checkbox_field( array(
			'label_for' => 'my_checkbox',
			'checked'   => '',
		) );
		$output = ob_get_clean();

		$this->assertStringNotContainsString( 'checked=', $output );
	}

	public function test_checkbox_group_field_callback_renders_an_option_per_entry() {
		ob_start();
		tml_admin_setting_callback_checkbox_group_field( array(
			'legend'  => 'My Group',
			'options' => array(
				'a' => array( 'value' => 'a', 'label' => 'Option A', 'checked' => true ),
				'b' => array( 'value' => 'b', 'label' => 'Option B', 'checked' => false ),
			),
		) );
		$output = ob_get_clean();

		$this->assertStringContainsString( 'Option A', $output );
		$this->assertStringContainsString( 'Option B', $output );
		$this->assertStringContainsString( "checked='checked'", $output );
	}

	public function test_dropdown_field_callback_marks_the_selected_option() {
		ob_start();
		tml_admin_setting_callback_dropdown_field( array(
			'label_for' => 'my_dropdown',
			'options'   => array( 'a' => 'Option A', 'b' => 'Option B' ),
			'selected'  => 'b',
		) );
		$output = ob_get_clean();

		$this->assertStringContainsString( 'value="b" selected=\'selected\'', $output );
	}

	public function test_radio_group_field_callback_marks_the_checked_option() {
		ob_start();
		tml_admin_setting_callback_radio_group_field( array(
			'label_for' => 'my_radio',
			'options'   => array( 'a' => 'Option A', 'b' => 'Option B' ),
			'checked'   => 'a',
		) );
		$output = ob_get_clean();

		$this->assertStringContainsString( 'value="a" checked=\'checked\'', $output );
		$this->assertStringNotContainsString( 'value="b" checked=\'checked\'', $output );
	}

	public function test_license_key_field_callback_is_a_no_op_for_an_unknown_extension() {
		ob_start();
		tml_admin_setting_callback_license_key_field( array( 'extension' => 'does-not-exist' ) );
		$output = ob_get_clean();

		$this->assertSame( '', $output );
	}

	public function test_license_key_field_callback_shows_active_status_for_a_valid_license() {
		$extension = tml_register_extension( new TML_Test_Extension( WP_PLUGIN_DIR . '/tml-test-extension/tml-test-extension.php', array(
			'name'                  => 'tml-test-extension',
			'license_key_option'    => '_tml_test_ext_license_key',
			'license_status_option' => '_tml_test_ext_license_status',
		) ) );
		$extension->set_license_key( 'a-license-key' );
		$extension->set_license_status( 'valid' );

		ob_start();
		tml_admin_setting_callback_license_key_field( array( 'label_for' => '_tml_test_ext_license_key', 'extension' => $extension ) );
		$output = ob_get_clean();

		$this->assertStringContainsString( 'tml-license-valid', $output );
		$this->assertStringContainsString( 'Active', $output );
		$this->assertStringContainsString( 'readonly="readonly"', $output );

		delete_site_option( '_tml_test_ext_license_key' );
		delete_site_option( '_tml_test_ext_license_status' );
	}

	public function test_license_key_field_callback_shows_inactive_status_without_a_license() {
		$extension = tml_register_extension( new TML_Test_Extension( WP_PLUGIN_DIR . '/tml-test-extension/tml-test-extension.php', array(
			'name'                  => 'tml-test-extension',
			'license_key_option'    => '_tml_test_ext_license_key',
			'license_status_option' => '_tml_test_ext_license_status',
		) ) );

		ob_start();
		tml_admin_setting_callback_license_key_field( array( 'label_for' => '_tml_test_ext_license_key', 'extension' => $extension ) );
		$output = ob_get_clean();

		$this->assertStringContainsString( 'tml-license-inactive', $output );
		$this->assertStringContainsString( 'Inactive', $output );
	}

	// tml_admin_settings_page()

	public function test_settings_page_flushes_rewrite_rules_for_the_core_page() {
		global $plugin_page;

		update_option( 'rewrite_rules', array( 'foo' => 'bar' ) );
		$plugin_page = 'theme-my-login';

		ob_start();
		tml_admin_settings_page();
		ob_get_clean();

		$this->assertSame( '', get_option( 'rewrite_rules' ) );
	}

	public function test_settings_page_does_not_flush_rewrite_rules_for_other_pages() {
		global $plugin_page;

		update_option( 'rewrite_rules', array( 'foo' => 'bar' ) );
		$plugin_page = 'theme-my-login-licenses';

		ob_start();
		tml_admin_settings_page();
		ob_get_clean();

		$this->assertSame( array( 'foo' => 'bar' ), get_option( 'rewrite_rules' ) );
	}

	public function test_settings_page_renders_a_form() {
		global $plugin_page;

		$plugin_page = 'theme-my-login';

		ob_start();
		tml_admin_settings_page();
		$output = ob_get_clean();

		$this->assertStringContainsString( '<form id="tml-settings"', $output );
	}

	// tml_admin_save_ms_settings()

	public function test_save_ms_settings_is_a_no_op_for_a_get_request() {
		$_SERVER['REQUEST_METHOD'] = 'GET';

		tml_admin_save_ms_settings();

		$this->assertTrue( true );
	}

	public function test_save_ms_settings_is_a_no_op_for_an_unregistered_options_page() {
		$_SERVER['REQUEST_METHOD'] = 'POST';
		$_REQUEST['option_page']   = 'not-a-real-tml-page';

		// Should return early without hitting check_admin_referer()/wp_die().
		tml_admin_save_ms_settings();

		unset( $_REQUEST['option_page'] );
		$this->assertTrue( true );
	}

	public function test_save_ms_settings_is_a_no_op_when_option_page_is_unset() {
		$_SERVER['REQUEST_METHOD'] = 'POST';
		unset( $_REQUEST['option_page'] );

		// Should return early on the empty() check, without ever reaching
		// has_page()/check_admin_referer()/wp_die().
		tml_admin_save_ms_settings();

		$this->assertTrue( true );
	}

	public function test_save_ms_settings_saves_allowed_options_and_redirects() {
		$_SERVER['REQUEST_METHOD'] = 'POST';
		$_REQUEST['option_page']   = 'theme-my-login';
		$_REQUEST['_wpnonce']      = wp_create_nonce( 'theme-my-login-options' );
		$_POST['tml_ajax']         = '1';

		add_filter( 'allowed_options', function ( $allowed ) {
			$allowed['theme-my-login'] = array( 'tml_ajax' );
			return $allowed;
		} );

		$captured = null;
		add_filter( 'wp_redirect', function ( $location ) use ( &$captured ) {
			$captured = $location;
			throw new TML_Test_Redirect_Exception();
		} );

		try {
			tml_admin_save_ms_settings();
			$this->fail( 'Expected a redirect after saving.' );
		} catch ( TML_Test_Redirect_Exception $e ) {
			$this->assertStringContainsString( 'settings-updated=true', $captured );
			$this->assertSame( '1', get_site_option( 'tml_ajax' ) );
		}

		remove_all_filters( 'allowed_options' );
		remove_all_filters( 'wp_redirect' );
		delete_site_option( 'tml_ajax' );
		unset( $_REQUEST['option_page'], $_REQUEST['_wpnonce'], $_POST['tml_ajax'] );
	}

	// tml_admin_add_settings_help_tabs()

	public function test_add_settings_help_tabs_is_a_no_op_for_an_unregistered_page() {
		global $plugin_page;

		$plugin_page = 'not-a-real-tml-page';
		$screen      = new TML_Test_Screen();

		tml_admin_add_settings_help_tabs( $screen );

		$this->assertSame( array(), $screen->help_tabs );
	}

	/**
	 * current_screen fires on every wp-admin page, not just TML's own, and
	 * WP core only populates $plugin_page when $_GET['page'] is set (i.e.
	 * on plugin-owned admin pages) — it's null on core screens like the
	 * Dashboard. This is the exact scenario from the WP.org report of a
	 * PHP 8.5 "null as array offset" deprecation notice.
	 *
	 * @see https://wordpress.org/support/topic/php-8-5-4/
	 */
	public function test_add_settings_help_tabs_is_a_no_op_when_plugin_page_is_unset() {
		global $plugin_page;

		$plugin_page = null;
		$screen      = new TML_Test_Screen();

		tml_admin_add_settings_help_tabs( $screen );

		$this->assertSame( array(), $screen->help_tabs );
	}

	public function test_add_settings_help_tabs_for_the_core_settings_page() {
		global $plugin_page;

		$plugin_page = 'theme-my-login';
		$screen      = new TML_Test_Screen();

		tml_admin_add_settings_help_tabs( $screen );

		$this->assertCount( 1, $screen->help_tabs );
		$this->assertSame( 'theme-my-login-overview', $screen->help_tabs[0]['id'] );
		$this->assertStringContainsString( 'View Documentation', $screen->sidebar );
	}

	public function test_add_settings_help_tabs_for_the_licenses_page() {
		global $plugin_page;

		$plugin_page = 'theme-my-login-licenses';
		$screen      = new TML_Test_Screen();

		tml_admin_add_settings_help_tabs( $screen );

		$this->assertSame( 'theme-my-login-licenses-overview', $screen->help_tabs[0]['id'] );
	}

	public function test_add_settings_help_tabs_for_the_extensions_page() {
		global $plugin_page;

		$plugin_page = 'theme-my-login-extensions';
		$screen      = new TML_Test_Screen();

		tml_admin_add_settings_help_tabs( $screen );

		$this->assertSame( 'theme-my-login-extensions-overview', $screen->help_tabs[0]['id'] );
	}

	public function test_add_settings_help_tabs_for_a_registered_extension_page() {
		global $plugin_page;

		tml_register_extension( new TML_Test_Extension( WP_PLUGIN_DIR . '/tml-test-extension/tml-test-extension.php', array(
			'name'         => 'tml-test-extension',
			'support_url'  => 'https://example.org/support',
		) ) );

		theme_my_login_admin()->add_menu_item( array(
			'page_title'  => 'tml-test-extension',
			'menu_title'  => 'tml-test-extension',
			'menu_slug'   => 'tml-test-extension',
			'parent_slug' => '',
		) );

		$plugin_page = 'tml-test-extension';
		$screen      = new TML_Test_Screen();

		tml_admin_add_settings_help_tabs( $screen );

		$this->assertSame( 'tml-test-extension-overview', $screen->help_tabs[0]['id'] );
		$this->assertStringContainsString( 'support', $screen->sidebar );
	}
}
