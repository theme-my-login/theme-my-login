<?php
/**
 * Coverage for src/includes/ms-functions.php: the multisite signup/
 * activation form registration, the default multisite actions, the
 * small pure filters (welcome-email/pre-insert-user-data), the signup/
 * activation shortcode content renderers, and the exit-free branches of
 * tml_ms_signup_handler().
 *
 * Only loaded/run under `-c tests/phpunit/multisite.xml` (is_multisite()
 * gates the require of ms-functions.php itself — see
 * src/theme-my-login.php), so this file lives in tests/multisite/,
 * excluded from the main single-site testsuite.
 *
 * tml_ms_signup_handler()'s reachable guard redirects (illegal_names,
 * is_main_site()) and tml_ms_activation_handler()'s branches are now also
 * covered, via the wp_redirect-filter-throw technique — see
 * test-login-handler.php's doc comment (in the main tests/ dir) for why
 * that works without process isolation. is_multisite() itself is always
 * true for this whole file (that's what makes it "the multisite suite"),
 * so that specific guard branch in both handlers is unreachable here by
 * construction. The 'gimmeanotherblog' stage (creates a real site via
 * wpmu_create_blog()) and the wpmu_signup_user()/wpmu_signup_blog()
 * success calls are still left out — safe to add later, just scoped out
 * of this pass to keep it to the highest-value subset.
 *
 * tml_ms_activation_handler() does `define( 'WP_INSTALLING', true )`
 * unconditionally on every call, which raises a "Constant already
 * defined" E_WARNING on the second and subsequent calls within the same
 * process — suppressed the same way test-password-reset-handler.php
 * suppresses the analogous setcookie() warning, since it's a test-
 * environment artifact (a constant can only ever be defined once per
 * process), not something under test.
 *
 * @package Theme_My_Login
 */

if ( ! class_exists( 'TML_Test_Redirect_Exception' ) ) {
	class TML_Test_Redirect_Exception extends Exception {}
}

class Test_MS_Functions extends WP_UnitTestCase {

	public function tearDown(): void {
		$_GET     = array();
		$_POST    = array();
		$_REQUEST = array();

		delete_site_option( 'registration' );
		delete_site_option( 'tml_registration_type' );
		delete_site_option( 'tml_user_passwords' );

		tml_register_default_forms();
		tml_ms_register_default_forms();
		tml_register_default_actions();
		tml_ms_register_default_actions();

		parent::tearDown();
	}

	// tml_ms_register_default_actions()

	public function test_register_default_actions_registers_signup_and_activate() {
		tml_ms_register_default_actions();

		$signup   = tml_get_action( 'signup' );
		$activate = tml_get_action( 'activate' );

		$this->assertNotFalse( $signup );
		$this->assertTrue( $signup->network );
		$this->assertFalse( $signup->show_on_forms );

		$this->assertNotFalse( $activate );
		$this->assertTrue( $activate->network );
	}

	// tml_ms_register_default_forms() / individual form registration

	public function test_register_default_forms_registers_all_four_ms_forms() {
		tml_ms_register_default_forms();

		$this->assertNotFalse( tml_get_form( 'user_signup' ) );
		$this->assertNotFalse( tml_get_form( 'blog_signup' ) );
		$this->assertNotFalse( tml_get_form( 'another_blog_signup' ) );
		$this->assertNotFalse( tml_get_form( 'activate' ) );
	}

	public function test_user_signup_form_uses_a_text_username_field_by_default() {
		tml_ms_register_user_signup_form();

		$this->assertSame( 'text', tml_get_form_field( 'user_signup', 'user_name' )->get_type() );
	}

	public function test_user_signup_form_hides_the_username_field_for_non_default_registration_types() {
		update_site_option( 'tml_registration_type', 'email' );

		tml_ms_register_user_signup_form();

		$this->assertSame( 'hidden', tml_get_form_field( 'user_signup', 'user_name' )->get_type() );
	}

	public function test_user_signup_form_signup_for_field_is_hidden_when_only_blog_signup_is_active() {
		update_site_option( 'registration', 'blog' );

		tml_ms_register_user_signup_form();

		$this->assertSame( 'hidden', tml_get_form_field( 'user_signup', 'signup_for' )->get_type() );
		$this->assertSame( 'blog', tml_get_form_field( 'user_signup', 'signup_for' )->get_value() );
	}

	public function test_user_signup_form_signup_for_field_is_hidden_when_only_user_signup_is_active() {
		update_site_option( 'registration', 'user' );

		tml_ms_register_user_signup_form();

		$this->assertSame( 'hidden', tml_get_form_field( 'user_signup', 'signup_for' )->get_type() );
		$this->assertSame( 'user', tml_get_form_field( 'user_signup', 'signup_for' )->get_value() );
	}

	public function test_user_signup_form_signup_for_field_is_a_radio_group_when_both_are_active() {
		update_site_option( 'registration', 'all' );

		tml_ms_register_user_signup_form();

		$this->assertSame( 'radio-group', tml_get_form_field( 'user_signup', 'signup_for' )->get_type() );
	}

	public function test_blog_signup_form_has_the_expected_fields() {
		tml_ms_register_blog_signup_form();

		$this->assertNotFalse( tml_get_form_field( 'blog_signup', 'blogname' ) );
		$this->assertNotFalse( tml_get_form_field( 'blog_signup', 'blog_title' ) );
		$this->assertNotFalse( tml_get_form_field( 'blog_signup', 'blog_public' ) );
		$this->assertNotFalse( tml_get_form_field( 'blog_signup', 'submit' ) );
	}

	public function test_another_blog_signup_form_has_the_expected_fields() {
		tml_ms_register_another_blog_signup_form();

		$this->assertNotFalse( tml_get_form_field( 'another_blog_signup', 'blogname' ) );
		$this->assertNotFalse( tml_get_form_field( 'another_blog_signup', 'blog_title' ) );
	}

	public function test_activation_form_shows_a_text_key_field_by_default() {
		tml_ms_register_activation_form();

		$this->assertSame( 'text', tml_get_form_field( 'activate', 'key' )->get_type() );
		$this->assertFalse( tml_get_form_field( 'activate', 'user_pass1' ) );
	}

	public function test_activation_form_adds_password_fields_when_a_key_is_present_and_passwords_are_allowed() {
		update_site_option( 'tml_user_passwords', true );
		$_REQUEST['key'] = 'some-activation-key';

		tml_ms_register_activation_form();

		$this->assertSame( 'hidden', tml_get_form_field( 'activate', 'key' )->get_type() );
		$this->assertNotFalse( tml_get_form_field( 'activate', 'user_pass1' ) );
		$this->assertNotFalse( tml_get_form_field( 'activate', 'user_pass2' ) );
	}

	// tml_ms_signup_get_active_signup()

	public function test_signup_get_active_signup_reflects_the_registration_option() {
		update_site_option( 'registration', 'blog' );

		$this->assertSame( 'blog', tml_ms_signup_get_active_signup() );
	}

	public function test_signup_get_active_signup_defaults_to_none() {
		delete_site_option( 'registration' );

		$this->assertSame( 'none', tml_ms_signup_get_active_signup() );
	}

	public function test_signup_get_active_signup_can_be_filtered() {
		add_filter( 'wpmu_active_signup', function () {
			return 'filtered';
		} );

		$this->assertSame( 'filtered', tml_ms_signup_get_active_signup() );

		remove_all_filters( 'wpmu_active_signup' );
	}

	// tml_ms_render_blog_signup_language_field()

	public function test_render_blog_signup_language_field_renders_a_dropdown_when_languages_are_available() {
		// The bundled test core ships several available locales.
		$markup = tml_ms_render_blog_signup_language_field();

		$this->assertStringContainsString( '<select name="site_language"', $markup );
	}

	// Small pure filters

	public function test_filter_pre_insert_user_data_hashes_the_submitted_password_when_allowed() {
		update_site_option( 'tml_user_passwords', true );
		$_POST['user_pass1'] = 'a-submitted-password';

		$data = tml_ms_filter_pre_insert_user_data( array( 'user_pass' => 'original-hash' ) );

		$this->assertTrue( wp_check_password( 'a-submitted-password', $data['user_pass'] ) );
	}

	public function test_filter_pre_insert_user_data_is_untouched_when_passwords_are_not_allowed() {
		$data = tml_ms_filter_pre_insert_user_data( array( 'user_pass' => 'original-hash' ) );

		$this->assertSame( 'original-hash', $data['user_pass'] );
	}

	public function test_filter_welcome_email_swaps_in_the_submitted_password() {
		update_site_option( 'tml_user_passwords', true );
		$_POST['user_pass1'] = 'a-submitted-password';

		$user_id = self::factory()->user->create();

		$message = tml_ms_filter_welcome_email( 'Your password is PASSGOESHERE.', 1, $user_id, 'PASSGOESHERE' );

		$this->assertStringContainsString( 'a-submitted-password', $message );
		$this->assertStringNotContainsString( 'PASSGOESHERE', $message );
	}

	public function test_filter_welcome_email_swaps_username_for_email_on_email_login_type() {
		update_site_option( 'tml_login_type', 'email' );

		$user_id = self::factory()->user->create( array( 'user_login' => 'someuser', 'user_email' => 'someone@example.org' ) );

		$message = tml_ms_filter_welcome_email( 'Log in as someuser.', 1, $user_id, 'irrelevant' );

		$this->assertStringContainsString( 'someone@example.org', $message );
		$this->assertStringNotContainsString( 'Log in as someuser', $message );

		delete_site_option( 'tml_login_type' );
	}

	public function test_filter_welcome_user_email_swaps_in_the_submitted_password() {
		update_site_option( 'tml_user_passwords', true );
		$_POST['user_pass1'] = 'a-submitted-password';

		$user_id = self::factory()->user->create();

		$message = tml_ms_filter_welcome_user_email( 'Your password is PASSWORD.', $user_id, 'irrelevant' );

		$this->assertStringContainsString( 'a-submitted-password', $message );
	}

	// tml_ms_filter_signup_shortcode() / tml_ms_filter_activation_shortcode()

	public function test_signup_shortcode_filter_ignores_other_actions() {
		$this->assertSame( 'original', tml_ms_filter_signup_shortcode( 'original', 'login', array() ) );
	}

	public function test_signup_shortcode_filter_reports_disabled_registration() {
		update_site_option( 'registration', 'none' );

		$content = tml_ms_filter_signup_shortcode( '', 'signup', array() );

		$this->assertStringContainsString( 'Registration has been disabled.', $content );
	}

	public function test_activation_shortcode_filter_ignores_other_actions() {
		$this->assertSame( 'original', tml_ms_filter_activation_shortcode( 'original', 'signup', array() ) );
	}

	public function test_activation_shortcode_filter_renders_the_key_form_without_a_key() {
		tml_ms_register_activation_form();

		$content = tml_ms_filter_activation_shortcode( '', 'activate', array() );

		$this->assertStringContainsString( 'Activation Key Required', $content );
		$this->assertStringContainsString( '<form', $content );
	}

	public function test_activation_shortcode_filter_reports_an_already_active_account() {
		$_GET['key'] = 'some-key';

		$signup = (object) array( 'domain' => '', 'path' => '', 'user_login' => 'someuser', 'user_email' => 'someone@example.org' );
		$error  = new WP_Error( 'already_active', 'Already active.', $signup );

		tml_set_data( 'activation_result', $error );

		$content = tml_ms_filter_activation_shortcode( '', 'activate', array() );

		$this->assertStringContainsString( 'Your account is now active!', $content );
	}

	// tml_ms_signup_handler() — exit-free branches only

	public function test_signup_handler_default_stage_fires_preprocess_signup_form() {
		$fired = false;
		add_action( 'preprocess_signup_form', function () use ( &$fired ) {
			$fired = true;
		} );

		tml_ms_signup_handler();

		remove_all_actions( 'preprocess_signup_form' );

		$this->assertTrue( $fired );
	}

	public function test_signup_handler_validate_user_signup_records_errors_for_an_empty_username() {
		update_site_option( 'registration', 'all' );

		$_POST['stage']       = 'validate-user-signup';
		$_POST['signup_for']  = 'user';
		$_POST['user_name']   = '';
		$_POST['user_email']  = '';

		tml_ms_signup_handler();

		$this->assertSame( 'user', tml_get_data( 'signup_form' ) );

		$result = tml_get_data( 'signup_result' );
		$this->assertTrue( $result['errors']->has_errors() );
	}

	public function test_signup_handler_validate_user_signup_redirects_to_the_blog_form_when_valid() {
		update_site_option( 'registration', 'all' );

		$_POST['stage']      = 'validate-user-signup';
		$_POST['signup_for'] = 'blog';
		$_POST['user_name']  = 'avalidnewuser123';
		$_POST['user_email'] = 'valid-signup@example.org';

		tml_ms_signup_handler();

		$this->assertSame( 'blog', tml_get_data( 'signup_form' ) );
	}

	// tml_ms_signup_handler() redirect guards

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
		} finally {
			remove_all_filters( 'wp_redirect' );
		}
	}

	public function test_signup_handler_redirects_illegal_blog_names_home() {
		update_site_option( 'illegal_names', array( 'badname' ) );

		$_GET['new'] = 'badname';

		$captured = $this->capture_redirect( 'tml_ms_signup_handler' );

		$this->assertSame( network_home_url(), $captured );

		delete_site_option( 'illegal_names' );
	}

	public function test_signup_handler_redirects_a_subsite_to_the_network_signup() {
		$sub_site_id = self::factory()->blog->create();
		switch_to_blog( $sub_site_id );

		// TML's own tml_filter_site_url() rewrites network_site_url('wp-signup.php')
		// to the registered 'signup' action's own URL, so the captured
		// location won't literally contain "wp-signup.php".
		$captured = $this->capture_redirect( 'tml_ms_signup_handler' );

		$this->assertStringContainsString( 'signup', $captured );

		restore_current_blog();
	}

	public function test_signup_handler_subsite_redirect_is_filterable() {
		$sub_site_id = self::factory()->blog->create();
		switch_to_blog( $sub_site_id );

		add_filter( 'tml_redirect_subsite_to_network_signup', '__return_false' );

		// With the redirect disabled, the handler should fall through to
		// the normal (non-redirecting) signup logic instead.
		tml_ms_signup_handler();

		remove_all_filters( 'tml_redirect_subsite_to_network_signup' );
		restore_current_blog();

		$this->assertTrue( true );
	}

	// tml_ms_activation_handler()

	public function test_activation_handler_is_a_no_op_without_a_key() {
		$error_level = error_reporting();
		error_reporting( $error_level & ~E_WARNING );

		// Should return early without attempting anything.
		tml_ms_activation_handler();

		error_reporting( $error_level );

		$this->assertTrue( true );
	}

	public function test_activation_handler_redirects_on_successful_activation() {
		$error_level = error_reporting();
		error_reporting( $error_level & ~E_WARNING );

		// The redirect only happens when tml_user_passwords is enabled —
		// otherwise $activation_redirect stays null/falsy and the handler
		// falls through to tml_set_data('activation_result', ...) instead.
		update_site_option( 'tml_user_passwords', true );

		wpmu_signup_user( 'msactivationuser', 'msactivationuser@example.org', array() );
		global $wpdb;
		$signup = $wpdb->get_row( $wpdb->prepare( "SELECT activation_key FROM $wpdb->signups WHERE user_login = %s", 'msactivationuser' ) );

		$_GET['key']        = $signup->activation_key;
		// tml_allow_user_passwords() being true also means the handler
		// requires (and validates) a submitted password before activating.
		$_POST['user_pass1'] = 'a-brand-new-password';
		$_POST['user_pass2'] = 'a-brand-new-password';

		$captured = $this->capture_redirect( 'tml_ms_activation_handler' );

		$this->assertNotEmpty( $captured );

		delete_site_option( 'tml_user_passwords' );
		error_reporting( $error_level );
	}

	public function test_activation_handler_records_the_result_without_a_redirect_by_default() {
		$error_level = error_reporting();
		error_reporting( $error_level & ~E_WARNING );

		wpmu_signup_user( 'msactivationuser2', 'msactivationuser2@example.org', array() );
		global $wpdb;
		$signup = $wpdb->get_row( $wpdb->prepare( "SELECT activation_key FROM $wpdb->signups WHERE user_login = %s", 'msactivationuser2' ) );

		$_GET['key'] = $signup->activation_key;

		// No tml_user_passwords, so this should NOT redirect — it should
		// return normally after recording the activation result.
		tml_ms_activation_handler();

		error_reporting( $error_level );

		$result = tml_get_data( 'activation_result' );
		$this->assertNotWPError( $result );
		$this->assertSame( 'msactivationuser2', get_userdata( $result['user_id'] )->user_login );
	}
}
