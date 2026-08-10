<?php
/**
 * Coverage for src/includes/forms.php: the field layout of the four
 * built-in forms (login, register, lostpassword, resetpass) and the
 * generic form/field registry wrapper functions.
 *
 * tml_register_default_forms() already runs once during the WP test
 * bootstrap's 'init' hook, so each test re-registers the form it's
 * checking directly rather than relying on that one-time bootstrap
 * state, keeping tests independent of each other.
 *
 * Note: the theme_my_login() singleton's form registry is NOT reset
 * between tests by WP_UnitTestCase (unlike options/DB state), and it's
 * shared by every other test file in the process (e.g. test-shortcode.php,
 * test-widget.php expect fully-populated 'login'/'register'/etc. forms
 * to exist). So tearDown() clears every input this file's tests can
 * vary (GET/REQUEST/COOKIE, current_screen, site options) and then
 * re-registers all four default forms from that clean state, leaving
 * the shared registry exactly as later, unrelated test files expect it.
 *
 * @package Theme_My_Login
 */

class Test_Forms extends WP_UnitTestCase {

	public function tearDown(): void {
		unset( $_GET['checkemail'], $_REQUEST['redirect_to'], $_POST['user_login'], $_POST['user_email'] );
		unset( $_COOKIE[ 'wp-resetpass-' . COOKIEHASH ] );
		unset( $GLOBALS['current_screen'] );

		delete_site_option( 'tml_registration_type' );
		delete_site_option( 'tml_user_passwords' );

		tml_register_login_form();
		tml_register_registration_form();
		tml_register_lost_password_form();
		tml_register_password_reset_form();

		parent::tearDown();
	}

	public function test_register_login_form_builds_the_expected_fields() {
		tml_register_login_form();

		$form = tml_get_form( 'login' );

		$this->assertInstanceOf( 'Theme_My_Login_Form', $form );
		$this->assertSame( 'text', $form->get_field( 'log' )->get_type() );
		$this->assertSame( 'password', $form->get_field( 'pwd' )->get_type() );
		$this->assertNotFalse( $form->get_field( 'login_form' ) );
		$this->assertNotFalse( $form->get_field( 'rememberme' ) );
		$this->assertNotFalse( $form->get_field( 'submit' ) );
	}

	public function test_register_login_form_defaults_redirect_to_the_admin_url() {
		tml_register_login_form();

		$this->assertSame( admin_url(), tml_get_form_field( 'login', 'redirect_to' )->get_value() );
	}

	public function test_register_login_form_uses_the_redirect_to_request_value_when_present() {
		$_REQUEST['redirect_to'] = 'https://example.org/somewhere';

		tml_register_login_form();

		$this->assertSame( 'https://example.org/somewhere', tml_get_form_field( 'login', 'redirect_to' )->get_value() );
	}

	public function test_register_login_form_hides_the_form_and_links_when_checkemail_is_set() {
		$_GET['checkemail'] = 'confirm';

		tml_register_login_form();

		$this->assertSame( array(
			'show_form'  => false,
			'show_links' => false,
		), tml_get_form( 'login' )->render_args );
	}

	public function test_register_registration_form_uses_a_text_username_field_by_default() {
		tml_register_registration_form();

		$this->assertSame( 'text', tml_get_form_field( 'register', 'user_login' )->get_type() );
	}

	public function test_register_registration_form_hides_the_username_field_for_non_default_registration_types() {
		update_site_option( 'tml_registration_type', 'email' );

		tml_register_registration_form();

		$this->assertSame( 'hidden', tml_get_form_field( 'register', 'user_login' )->get_type() );
	}

	public function test_register_registration_form_omits_password_fields_by_default() {
		tml_register_registration_form();

		$this->assertFalse( tml_get_form_field( 'register', 'user_pass1' ) );
		$this->assertFalse( tml_get_form_field( 'register', 'user_pass2' ) );
		$this->assertNotFalse( tml_get_form_field( 'register', 'reg_passmail' ) );
	}

	public function test_register_registration_form_adds_password_fields_when_user_passwords_are_allowed() {
		update_site_option( 'tml_user_passwords', true );

		tml_register_registration_form();

		$this->assertNotFalse( tml_get_form_field( 'register', 'user_pass1' ) );
		$this->assertNotFalse( tml_get_form_field( 'register', 'user_pass2' ) );
		$this->assertFalse( tml_get_form_field( 'register', 'reg_passmail' ) );
	}

	public function test_register_lost_password_form_builds_the_expected_fields() {
		tml_register_lost_password_form();

		$form = tml_get_form( 'lostpassword' );

		$this->assertNotFalse( $form->get_field( 'message' ) );
		$this->assertSame( 'text', $form->get_field( 'user_login' )->get_type() );
		$this->assertNotFalse( $form->get_field( 'lostpassword_form' ) );
		$this->assertNotFalse( $form->get_field( 'submit' ) );
	}

	public function test_register_password_reset_form_builds_the_expected_fields() {
		tml_register_password_reset_form();

		$form = tml_get_form( 'resetpass' );

		$this->assertNotFalse( $form->get_field( 'pass1' ) );
		$this->assertNotFalse( $form->get_field( 'pass2' ) );
		$this->assertNotFalse( $form->get_field( 'resetpass_form' ) );
		$this->assertFalse( $form->get_field( 'rp_key' ) );
	}

	public function test_register_password_reset_form_adds_the_reset_key_from_the_cookie() {
		$_COOKIE[ 'wp-resetpass-' . COOKIEHASH ] = 'somelogin:somekey';

		tml_register_password_reset_form();

		$this->assertSame( 'somekey', tml_get_form_field( 'resetpass', 'rp_key' )->get_value() );
	}

	public function test_register_default_forms_registers_all_four_forms_on_the_front_end() {
		// tml_register_default_forms() re-registers (fully overwrites) each
		// form, so no explicit unregister/cleanup is needed either side.
		tml_register_default_forms();

		$this->assertNotFalse( tml_get_form( 'login' ) );
		$this->assertNotFalse( tml_get_form( 'register' ) );
		$this->assertNotFalse( tml_get_form( 'lostpassword' ) );
		$this->assertNotFalse( tml_get_form( 'resetpass' ) );
	}

	public function test_register_default_forms_is_a_no_op_in_the_admin() {
		tml_unregister_form( 'login' );

		$GLOBALS['current_screen'] = new class {
			public function in_admin() {
				return true;
			}
		};

		tml_register_default_forms();

		$this->assertFalse( tml_get_form( 'login' ) );
	}

	public function test_register_form_accepts_a_name_and_wraps_it_in_a_form_object() {
		$form = tml_register_form( 'custom-form' );

		$this->assertInstanceOf( 'Theme_My_Login_Form', $form );
		$this->assertSame( $form, tml_get_form( 'custom-form' ) );

		tml_unregister_form( 'custom-form' );
	}

	public function test_get_form_falls_back_to_the_current_action() {
		global $wp;

		tml_register_login_form();
		$wp->query_vars['action'] = 'login';

		$this->assertSame( tml_get_form( 'login' ), tml_get_form() );

		unset( $wp->query_vars['action'] );
	}

	public function test_form_exists_reflects_the_registry() {
		tml_register_login_form();

		$this->assertTrue( tml_form_exists( 'login' ) );
		$this->assertFalse( tml_form_exists( 'does-not-exist' ) );
	}

	public function test_add_form_field_returns_nothing_when_the_form_does_not_exist() {
		$this->assertNull( tml_add_form_field( 'does-not-exist', 'field' ) );
	}

	public function test_remove_form_field_is_a_no_op_when_the_form_does_not_exist() {
		// Should not raise a warning/error when the form doesn't exist.
		tml_remove_form_field( 'does-not-exist', 'field' );

		$this->assertTrue( true );
	}

	public function test_get_form_field_returns_false_when_the_form_does_not_exist() {
		$this->assertFalse( tml_get_form_field( 'does-not-exist', 'field' ) );
	}

	public function test_get_form_fields_returns_false_when_the_form_does_not_exist() {
		$this->assertFalse( tml_get_form_fields( 'does-not-exist' ) );
	}

	public function test_get_form_fields_returns_all_fields_for_an_existing_form() {
		tml_register_login_form();

		$names = array_map( function ( $field ) {
			return $field->get_name();
		}, tml_get_form_fields( 'login' ) );

		$this->assertContains( 'log', $names );
		$this->assertContains( 'submit', $names );
	}
}
