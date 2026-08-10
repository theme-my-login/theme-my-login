<?php
/**
 * Coverage for the option-driven behavior toggles in includes/options.php.
 *
 * These wrap get_site_option()/get_option() with plugin-specific defaults
 * and filters, so a WP core change to option storage or filter ordering
 * would show up here.
 *
 * @package Theme_My_Login
 */

class Test_Options extends WP_UnitTestCase {

	public function tearDown(): void {
		delete_site_option( 'tml_login_type' );
		delete_site_option( 'tml_registration_type' );
		delete_site_option( 'tml_use_permalinks' );
		delete_site_option( 'tml_ajax' );

		parent::tearDown();
	}

	public function test_login_type_defaults_to_default() {
		$this->assertTrue( tml_is_default_login_type() );
		$this->assertFalse( tml_is_email_login_type() );
		$this->assertFalse( tml_is_username_login_type() );
	}

	public function test_login_type_reflects_the_site_option() {
		update_site_option( 'tml_login_type', 'email' );

		$this->assertSame( 'email', tml_get_login_type() );
		$this->assertTrue( tml_is_email_login_type() );
		$this->assertFalse( tml_is_default_login_type() );
	}

	public function test_registration_type_defaults_to_default() {
		$this->assertTrue( tml_is_default_registration_type() );
		$this->assertFalse( tml_is_email_registration_type() );
	}

	public function test_use_ajax_defaults_to_false() {
		$this->assertFalse( tml_use_ajax() );

		update_site_option( 'tml_ajax', true );

		$this->assertTrue( tml_use_ajax() );
	}

	public function test_use_permalinks_respects_the_tml_use_permalinks_filter() {
		add_filter( 'tml_use_permalinks', '__return_false' );

		$this->assertFalse( tml_use_permalinks() );

		remove_filter( 'tml_use_permalinks', '__return_false' );
	}
}
