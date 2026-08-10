<?php
/**
 * Coverage for Theme_My_Login_Action, the object every registered action
 * (login, register, lostpassword, ...) is built from: title/slug filters,
 * callback hook wiring, and URL generation in both plain and permalink
 * modes. tml_get_actions()/tml_register_action() are covered in
 * test-default-actions.php; this exercises the class directly.
 *
 * @package Theme_My_Login
 */

class Test_Action extends WP_UnitTestCase {

	public function tearDown(): void {
		$this->set_permalink_structure( '' );

		parent::tearDown();
	}

	public function test_name_is_sanitized() {
		$action = new Theme_My_Login_Action( 'My Action!' );

		$this->assertSame( 'myaction', $action->get_name() );
	}

	public function test_title_can_be_filtered() {
		$action = new Theme_My_Login_Action( 'test', array( 'title' => 'Original' ) );

		add_filter( 'tml_get_action_title', function () {
			return 'Filtered';
		} );

		$title = $action->get_title();

		remove_all_filters( 'tml_get_action_title' );

		$this->assertSame( 'Filtered', $title );
	}

	public function test_slug_defaults_to_the_action_name() {
		$action = new Theme_My_Login_Action( 'test' );

		$this->assertSame( 'test', $action->get_slug() );
	}

	public function test_slug_can_be_explicitly_set() {
		$action = new Theme_My_Login_Action( 'test', array( 'slug' => 'custom-slug' ) );

		$this->assertSame( 'custom-slug', $action->get_slug() );
	}

	public function test_slug_can_be_filtered() {
		$action = new Theme_My_Login_Action( 'test' );

		add_filter( 'tml_get_action_slug', function () {
			return 'filtered-slug';
		} );

		$slug = $action->get_slug();

		remove_all_filters( 'tml_get_action_slug' );

		$this->assertSame( 'filtered-slug', $slug );
	}

	public function test_add_callback_hook_wires_the_callback_to_the_action_hook() {
		$called = false;

		$action = new Theme_My_Login_Action( 'test', array(
			'callback' => function () use ( &$called ) {
				$called = true;
			},
		) );

		$action->add_callback_hook();
		do_action( 'tml_action_test' );

		$this->assertTrue( $called );
	}

	public function test_remove_callback_hook_unwires_the_callback() {
		$called = false;

		$action = new Theme_My_Login_Action( 'test', array(
			'callback' => function () use ( &$called ) {
				$called = true;
			},
		) );

		$action->add_callback_hook();
		$action->remove_callback_hook();
		do_action( 'tml_action_test' );

		$this->assertFalse( $called );
	}

	public function test_add_ajax_callback_hook_wires_the_ajax_callback_to_its_own_hook() {
		$called = false;

		$action = new Theme_My_Login_Action( 'test', array(
			'ajax_callback' => function () use ( &$called ) {
				$called = true;
			},
		) );

		$action->add_ajax_callback_hook();
		do_action( 'tml_action_ajax_test' );

		$this->assertTrue( $called );
	}

	public function test_remove_ajax_callback_hook_unwires_the_ajax_callback() {
		$called = false;

		$action = new Theme_My_Login_Action( 'test', array(
			'ajax_callback' => function () use ( &$called ) {
				$called = true;
			},
		) );

		$action->add_ajax_callback_hook();
		$action->remove_ajax_callback_hook();
		do_action( 'tml_action_ajax_test' );

		$this->assertFalse( $called );
	}

	public function test_no_callback_hook_is_added_when_none_is_set() {
		$action = new Theme_My_Login_Action( 'test' );

		// Should be a no-op, not a PHP warning/error, when there's no callback.
		$action->add_callback_hook();
		$action->remove_callback_hook();

		$this->assertFalse( has_action( 'tml_action_test' ) );
	}

	public function test_url_uses_the_action_query_var_when_permalinks_are_off() {
		$this->set_permalink_structure( '' );

		$action = new Theme_My_Login_Action( 'test' );

		$url = $action->get_url();

		$this->assertStringContainsString( 'action=test', $url );
	}

	public function test_url_uses_the_slug_as_a_path_when_permalinks_are_on() {
		$this->set_permalink_structure( '/%postname%/' );

		$action = new Theme_My_Login_Action( 'test', array( 'slug' => 'custom-slug' ) );

		$url = $action->get_url();

		$this->assertStringNotContainsString( 'action=', $url );
		$this->assertStringContainsString( 'custom-slug', $url );
	}

	public function test_url_can_be_forced_to_the_network_home_regardless_of_the_action_default() {
		$action = new Theme_My_Login_Action( 'test', array( 'network' => false ) );

		$site_url    = $action->get_url( 'login', false );
		$network_url = $action->get_url( 'login', true );

		$this->assertStringContainsString( 'action=test', $site_url );
		$this->assertStringContainsString( 'action=test', $network_url );
	}

	public function test_url_can_be_filtered() {
		$action = new Theme_My_Login_Action( 'test' );

		add_filter( 'tml_get_action_url', function () {
			return 'https://example.org/filtered';
		} );

		$url = $action->get_url();

		remove_all_filters( 'tml_get_action_url' );

		$this->assertSame( 'https://example.org/filtered', $url );
	}
}
