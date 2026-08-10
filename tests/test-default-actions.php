<?php
/**
 * Smoke tests covering the actions TML registers out of the box.
 *
 * These exist mainly as a version-compatibility tripwire: if a WordPress
 * core change breaks action registration, URL generation, or the
 * `users_can_register`-driven visibility, one of these should fail loudly
 * before we bump "Tested up to".
 *
 * @package Theme_My_Login
 */

class Test_Default_Actions extends WP_UnitTestCase {

	public function test_core_actions_are_registered() {
		$actions = tml_get_actions();

		foreach ( array( 'login', 'logout', 'register', 'lostpassword', 'resetpass' ) as $slug ) {
			$this->assertArrayHasKey( $slug, $actions, "Expected the '$slug' action to be registered." );
		}
	}

	public function test_login_action_url_is_scoped_to_the_login_action() {
		$url = tml_get_action_url( 'login' );

		$this->assertStringStartsWith( home_url(), $url );
		$this->assertStringContainsString( 'action=login', $url );
	}

	public function test_register_action_visibility_follows_users_can_register_option() {
		update_option( 'users_can_register', 0 );
		tml_register_default_actions();
		$this->assertFalse( (bool) tml_get_action( 'register' )->show_on_forms );

		update_option( 'users_can_register', 1 );
		tml_register_default_actions();
		$this->assertNotFalse( tml_get_action( 'register' )->show_on_forms );

		// The action registry is a process-global singleton that isn't reset
		// between tests, unlike 'users_can_register' itself (which the DB
		// transaction rollback undoes) — restore it explicitly so the 'register'
		// action's show_on_forms doesn't leak its non-default value into later
		// tests/test files.
		update_option( 'users_can_register', 0 );
		tml_register_default_actions();
	}

	public function test_unknown_action_returns_false() {
		$this->assertFalse( tml_get_action( 'not-a-real-action' ) );
	}
}
