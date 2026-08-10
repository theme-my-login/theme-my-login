<?php
/**
 * Coverage for Theme_My_Login_Admin: the admin-page registry used by
 * src/admin/hooks.php to track TML's own settings/extensions/licenses
 * pages (has_page()/get_page_hook() gate menu-highlighting and
 * plugin-action-link logic elsewhere in admin/).
 *
 * class-theme-my-login-admin.php is normally only required when
 * is_admin() is true, which it isn't in the default (front-end) test
 * bootstrap, so it's pulled in directly here, same pattern as
 * test-admin-functions.php. add_menu_page()/add_submenu_page() (WP core,
 * from wp-admin/includes/plugin.php) are available regardless of
 * is_admin() in this test environment, so no further workaround is
 * needed to exercise add_menu_item().
 *
 * Theme_My_Login_Admin::$pages is a singleton-instance array that
 * persists across tests in the same process (same footgun as the form/
 * action/extension registries — see project memory), so each test uses
 * its own unique menu slug rather than resetting shared state.
 *
 * @package Theme_My_Login
 */

if ( ! class_exists( 'Theme_My_Login_Admin' ) ) {
	require THEME_MY_LOGIN_PATH . 'admin/class-theme-my-login-admin.php';
}

class Test_Admin_Class extends WP_UnitTestCase {

	public function test_tml_admin_init_fires_on_first_construction() {
		$property = new ReflectionProperty( 'Theme_My_Login_Admin', 'instance' );
		$property->setValue( null, null );

		$fired = false;
		add_action( 'tml_admin_init', function () use ( &$fired ) {
			$fired = true;
		} );

		$instance = Theme_My_Login_Admin::get_instance();

		remove_all_actions( 'tml_admin_init' );

		$this->assertTrue( $fired );
		$this->assertInstanceOf( 'Theme_My_Login_Admin', $instance );
	}

	public function test_get_instance_returns_the_same_object_every_time() {
		$this->assertSame( Theme_My_Login_Admin::get_instance(), Theme_My_Login_Admin::get_instance() );
	}

	public function test_add_menu_item_is_a_no_op_without_required_args() {
		$admin = Theme_My_Login_Admin::get_instance();

		$hook = $admin->add_menu_item( array( 'menu_slug' => 'tml-test-missing-titles' ) );

		$this->assertNull( $hook );
		$this->assertFalse( $admin->has_page( 'tml-test-missing-titles' ) );
	}

	public function test_add_menu_item_adds_a_top_level_page_without_a_parent_slug() {
		$admin = Theme_My_Login_Admin::get_instance();

		$hook = $admin->add_menu_item( array(
			'page_title'  => 'Test Top Level',
			'menu_title'  => 'Test Top Level',
			'menu_slug'   => 'tml-test-top-level',
			'parent_slug' => '',
		) );

		$this->assertNotEmpty( $hook );
		$this->assertTrue( $admin->has_page( 'tml-test-top-level' ) );
		$this->assertSame( $hook, $admin->get_page_hook( 'tml-test-top-level' ) );
	}

	public function test_add_menu_item_adds_a_submenu_page_under_the_default_parent() {
		// add_submenu_page() (WP core) returns false for a user who can't
		// 'manage_options' (the default capability), which is the logged-out
		// user WP_UnitTestCase starts each test with; it resets to logged-out
		// automatically in tear_down().
		wp_set_current_user( self::factory()->user->create( array( 'role' => 'administrator' ) ) );

		$admin = Theme_My_Login_Admin::get_instance();

		$hook = $admin->add_menu_item( array(
			'page_title' => 'Test Submenu',
			'menu_title' => 'Test Submenu',
			'menu_slug'  => 'tml-test-submenu',
		) );

		$this->assertNotEmpty( $hook );
		$this->assertTrue( $admin->has_page( 'tml-test-submenu' ) );
	}

	public function test_has_page_is_false_for_an_unregistered_page() {
		$admin = Theme_My_Login_Admin::get_instance();

		$this->assertFalse( $admin->has_page( 'tml-test-never-registered' ) );
	}

	public function test_get_page_hook_returns_null_for_an_unregistered_page() {
		$admin = Theme_My_Login_Admin::get_instance();

		$this->assertNull( $admin->get_page_hook( 'tml-test-never-registered' ) );
	}
}
