<?php
/**
 * Coverage for src/includes/actions.php's pure/exit-free functions: the
 * full default-action registration (dashboard/confirmaction, not just the
 * subset test-default-actions.php smoke-tests), the action registry
 * wrapper functions, tml_action_has_page(), and tml_dashboard_handler()
 * (its one redirect branch, intercepted via the wp_redirect filter — see
 * test-login-handler.php's doc comment for why this works without
 * process isolation). The other handler functions
 * (tml_login_handler(), tml_registration_handler(), etc.) have their own
 * dedicated test files.
 *
 * Like the form registry (see test-forms.php), the action registry on
 * the theme_my_login() singleton is process-global and NOT reset between
 * tests by WP_UnitTestCase, so tearDown() unconditionally re-registers
 * the default actions from clean option state to avoid leaking into
 * later test files.
 *
 * @package Theme_My_Login
 */

if ( ! class_exists( 'TML_Test_Redirect_Exception' ) ) {
	class TML_Test_Redirect_Exception extends Exception {}
}

class Test_Actions extends WP_UnitTestCase {

	public function tearDown(): void {
		global $wp;

		unset( $_GET['checkemail'] );

		$_REQUEST               = array();
		$_SERVER['REQUEST_URI'] = '/';
		remove_all_filters( 'wp_redirect' );
		unset( $wp->query_vars['action'] );

		delete_site_option( 'tml_login_slug' );

		tml_register_default_actions();

		parent::tearDown();
	}

	public function test_register_default_actions_registers_the_dashboard_action() {
		tml_register_default_actions();

		$action = tml_get_action( 'dashboard' );

		$this->assertSame( 'Dashboard', $action->get_title() );
		$this->assertFalse( $action->show_on_forms );
		$this->assertFalse( $action->show_nav_menu_item );
	}

	public function test_register_default_actions_registers_the_logout_action() {
		tml_register_default_actions();

		$action = tml_get_action( 'logout' );

		$this->assertFalse( $action->show_on_forms );
		$this->assertFalse( $action->show_in_widget );
		$this->assertFalse( $action->show_nav_menu_item );
	}

	public function test_register_default_actions_registers_the_confirmaction_action() {
		tml_register_default_actions();

		$action = tml_get_action( 'confirmaction' );

		$this->assertFalse( $action->show_on_forms );
		$this->assertFalse( $action->show_in_widget );
		$this->assertFalse( $action->show_in_nav_menus );
		$this->assertFalse( $action->show_in_slug_settings );
	}

	public function test_register_default_actions_titles_the_login_action_for_checkemail() {
		$_GET['checkemail'] = 'confirm';

		tml_register_default_actions();

		$this->assertSame( 'Check your email', tml_get_action( 'login' )->get_title() );
	}

	public function test_register_action_applies_a_slug_override_from_the_site_option() {
		update_site_option( 'tml_login_slug', 'sign-in' );

		tml_register_action( 'login' );

		$this->assertSame( 'sign-in', tml_get_action( 'login' )->get_slug() );
	}

	public function test_unregister_action_removes_it_from_the_registry() {
		tml_unregister_action( 'login' );

		$this->assertFalse( tml_get_action( 'login' ) );
	}

	public function test_get_actions_returns_the_full_registry() {
		$this->assertArrayHasKey( 'login', tml_get_actions() );
		$this->assertArrayHasKey( 'logout', tml_get_actions() );
	}

	public function test_action_exists_reflects_the_registry() {
		$this->assertTrue( tml_action_exists( 'login' ) );
		$this->assertFalse( tml_action_exists( 'does-not-exist' ) );
	}

	public function test_action_exists_can_be_filtered() {
		add_filter( 'tml_action_exists', function () {
			return true;
		} );

		$this->assertTrue( tml_action_exists( 'does-not-exist' ) );

		remove_all_filters( 'tml_action_exists' );
	}

	public function test_is_action_is_false_when_no_action_is_current() {
		global $wp;

		unset( $wp->query_vars['action'] );

		$this->assertFalse( tml_is_action() );
		$this->assertFalse( tml_is_action( 'login' ) );
	}

	public function test_is_action_matches_the_current_action() {
		global $wp;

		$wp->query_vars['action'] = 'login';

		$this->assertTrue( tml_is_action() );
		$this->assertTrue( tml_is_action( 'login' ) );
		$this->assertFalse( tml_is_action( 'logout' ) );

		unset( $wp->query_vars['action'] );
	}

	public function test_get_action_title_returns_null_for_an_unknown_action() {
		$this->assertNull( tml_get_action_title( 'does-not-exist' ) );
	}

	public function test_get_action_title_returns_the_title_of_a_known_action() {
		$this->assertSame( 'Log In', tml_get_action_title( 'login' ) );
	}

	public function test_get_action_slug_returns_null_for_an_unknown_action() {
		$this->assertNull( tml_get_action_slug( 'does-not-exist' ) );
	}

	public function test_get_action_slug_returns_the_slug_of_a_known_action() {
		$this->assertSame( 'login', tml_get_action_slug( 'login' ) );
	}

	public function test_get_action_url_returns_null_for_an_unknown_action() {
		$this->assertNull( tml_get_action_url( 'does-not-exist' ) );
	}

	public function test_get_action_url_delegates_to_the_action_object() {
		$this->assertStringContainsString( 'action=login', tml_get_action_url( 'login' ) );
	}

	public function test_action_has_page_returns_false_for_an_unknown_action() {
		$this->assertFalse( tml_action_has_page( 'does-not-exist' ) );
	}

	public function test_action_has_page_returns_false_when_no_matching_page_exists() {
		$this->assertFalse( tml_action_has_page( 'login' ) );
	}

	public function test_action_has_page_finds_a_page_matching_the_action_slug() {
		$page_id = self::factory()->post->create( array(
			'post_type' => 'page',
			'post_name' => 'login',
		) );

		$page = tml_action_has_page( 'login' );

		$this->assertInstanceOf( 'WP_Post', $page );
		$this->assertSame( $page_id, $page->ID );
	}

	public function test_action_handler_redirects_bracketed_query_args_without_error() {
		global $wp;

		$wp->query_vars['action'] = 'login';

		$_REQUEST['action']     = 'lostpassword';
		$_SERVER['REQUEST_URI'] = '/login/?action=lostpassword&x[]=1';

		$captured = null;
		add_filter( 'wp_redirect', function ( $location ) use ( &$captured ) {
			$captured = $location;
			throw new TML_Test_Redirect_Exception();
		} );

		try {
			tml_action_handler();
			$this->fail( 'Expected the login action to redirect to the lost password action.' );
		} catch ( TML_Test_Redirect_Exception $e ) {
			$this->assertStringContainsString( 'action=lostpassword', $captured );
			$this->assertStringContainsString( 'x%5B0%5D=1', $captured );
		}
	}

	public function test_action_handler_is_a_no_op_when_no_tml_action_is_current() {
		global $wp;

		unset( $wp->query_vars['action'] );

		// Should return early without touching cookies/headers/redirects.
		tml_action_handler();

		$this->assertTrue( true );
	}

	public function test_dashboard_handler_redirects_logged_out_users_to_login() {
		$_SERVER['REQUEST_URI'] = '/some-admin-page/';

		$captured = null;
		add_filter( 'wp_redirect', function ( $location ) use ( &$captured ) {
			$captured = $location;
			throw new TML_Test_Redirect_Exception();
		} );

		try {
			tml_dashboard_handler();
			$this->fail( 'Expected a logged-out visitor to be redirected to the login page.' );
		} catch ( TML_Test_Redirect_Exception $e ) {
			$this->assertStringContainsString( 'action=login', $captured );
		}

		remove_all_filters( 'wp_redirect' );
	}

	public function test_dashboard_handler_is_a_no_op_for_logged_in_users() {
		wp_set_current_user( self::factory()->user->create() );

		// Should return early without attempting a redirect.
		tml_dashboard_handler();

		$this->assertTrue( true );
	}
}
