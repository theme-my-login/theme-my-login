<?php
/**
 * Coverage for the [theme-my-login] shortcode, TML's main public surface
 * for themes/pages. A WP core change to shortcode_atts(), user state
 * functions, or the login form fields would likely show up here first.
 *
 * @package Theme_My_Login
 */

class Test_Shortcode extends WP_UnitTestCase {

	public function test_default_output_is_the_login_form_when_logged_out() {
		$content = tml_shortcode();

		$this->assertStringContainsString( '<form', $content );
		$this->assertStringContainsString( 'tml-login', $content );
		$this->assertStringContainsString( 'name="log"', $content );
	}

	public function test_explicit_register_action_renders_the_registration_form() {
		$content = tml_shortcode( array( 'action' => 'register' ) );

		$this->assertStringContainsString( 'tml-register', $content );
		$this->assertStringContainsString( 'name="user_login"', $content );
	}

	public function test_unknown_action_renders_nothing() {
		$this->assertSame( '', tml_shortcode( array( 'action' => 'not-a-real-action' ) ) );
	}

	public function test_dashboard_action_greets_the_logged_in_user() {
		$user_id = self::factory()->user->create( array( 'display_name' => 'Ada Lovelace' ) );
		wp_set_current_user( $user_id );

		$content = tml_shortcode( array( 'action' => 'dashboard' ) );

		$this->assertStringContainsString( 'Ada Lovelace', $content );
	}

	public function test_shortcode_content_is_filterable() {
		add_filter( 'tml_shortcode', function () {
			return 'filtered content';
		} );

		$this->assertSame( 'filtered content', tml_shortcode() );

		remove_all_filters( 'tml_shortcode' );
	}
}
