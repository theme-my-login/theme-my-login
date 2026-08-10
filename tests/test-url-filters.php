<?php
/**
 * Coverage for the pure, exit-free filters TML hooks onto WP core's own
 * URL/body-class filters (logout_url, lostpassword_url, body_class), plus
 * the small redirect/array helpers in functions.php. These wrap core
 * filters directly, so a WP core signature or behavior change would show
 * up here.
 *
 * @package Theme_My_Login
 */

class Test_Url_Filters extends WP_UnitTestCase {

	public function tearDown(): void {
		global $wp, $pagenow;

		unset( $wp->query_vars['action'] );
		$pagenow = null;

		parent::tearDown();
	}

	public function test_body_class_is_unchanged_outside_a_tml_action() {
		global $wp;

		unset( $wp->query_vars['action'] );

		$this->assertSame( array( 'existing' ), tml_body_class( array( 'existing' ) ) );
	}

	public function test_body_class_adds_action_classes_during_a_tml_action() {
		global $wp;

		$wp->query_vars['action'] = 'login';

		$classes = tml_body_class( array() );

		$this->assertContains( 'tml-action', $classes );
		$this->assertContains( 'tml-action-login', $classes );
	}

	public function test_logout_url_is_replaced_with_the_tml_action_url() {
		$url = tml_filter_logout_url( 'https://example.org/wp-login.php?action=logout', '' );

		$this->assertStringContainsString( 'action=logout', $url );
		$this->assertStringNotContainsString( 'wp-login.php', $url );
	}

	public function test_logout_url_includes_the_redirect_and_a_nonce() {
		$url = tml_filter_logout_url( 'https://example.org/wp-login.php?action=logout', 'https://example.org/somewhere' );

		$this->assertStringContainsString( 'redirect_to=', $url );
		$this->assertStringContainsString( '_wpnonce=', $url );
	}

	public function test_lostpassword_url_is_replaced_with_the_tml_action_url() {
		global $pagenow;

		$pagenow = 'index.php';

		$url = tml_filter_lostpassword_url( 'https://example.org/wp-login.php?action=lostpassword', '' );

		$this->assertStringContainsString( 'action=lostpassword', $url );
		$this->assertStringNotContainsString( 'wp-login.php', $url );
	}

	public function test_lostpassword_url_is_untouched_on_wp_login_php() {
		global $pagenow;

		$pagenow = 'wp-login.php';

		$original = 'https://example.org/wp-login.php?action=lostpassword';

		$this->assertSame( $original, tml_filter_lostpassword_url( $original, '' ) );
	}

	public function test_validate_redirect_allows_a_same_site_url() {
		$same_site = home_url( '/somewhere' );

		$this->assertSame( $same_site, tml_validate_redirect( $same_site ) );
	}

	public function test_validate_redirect_falls_back_for_an_external_url() {
		$this->assertSame( admin_url(), tml_validate_redirect( 'https://evil.example/phish' ) );
	}

	public function test_array_map_recursive_applies_the_callback_to_every_leaf() {
		$input = array(
			'a' => 'one',
			'b' => array(
				'c' => 'two',
				'd' => 'three',
			),
		);

		$result = tml_array_map_recursive( 'strtoupper', $input );

		$this->assertSame( array(
			'a' => 'ONE',
			'b' => array(
				'c' => 'TWO',
				'd' => 'THREE',
			),
		), $result );
	}
}
