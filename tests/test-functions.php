<?php
/**
 * Coverage for the pure/exit-free functions in src/includes/functions.php
 * that aren't already covered by test-url-filters.php (body_class,
 * logout_url, lostpassword_url, tml_validate_redirect,
 * tml_array_map_recursive) or test-request-helpers.php
 * (tml_get_request_value()).
 *
 * AJAX senders (tml_send_ajax_success/error) end in wp_send_json_*() ->
 * a plain die() since DOING_AJAX is never defined here — same policy as
 * elsewhere in this suite, deliberately not covered.
 *
 * @package Theme_My_Login
 */

class Test_Functions extends WP_UnitTestCase {

	public function tearDown(): void {
		global $wp, $pagenow;

		unset( $wp->query_vars['action'] );
		$pagenow = null;
		$_SERVER['REQUEST_METHOD'] = 'GET';
		unset( $_REQUEST['ajax'], $_POST['user_pass1'], $_POST['user_pass2'], $_POST['user_login'], $_POST['user_email'] );

		delete_site_option( 'tml_user_passwords' );
		delete_site_option( 'tml_registration_type' );
		delete_site_option( 'tml_auto_login' );

		wp_dequeue_style( 'theme-my-login' );
		wp_deregister_style( 'theme-my-login' );
		wp_dequeue_script( 'theme-my-login' );
		wp_deregister_script( 'theme-my-login' );

		$this->set_permalink_structure( '' );

		parent::tearDown();
	}

	// theme_my_login() / tml_get_data() / tml_set_data()

	public function test_theme_my_login_returns_the_same_singleton_instance() {
		$this->assertInstanceOf( 'Theme_My_Login', theme_my_login() );
		$this->assertSame( theme_my_login(), theme_my_login() );
	}

	public function test_get_and_set_data_round_trip() {
		tml_set_data( 'test-key', 'test-value' );

		$this->assertSame( 'test-value', tml_get_data( 'test-key' ) );
	}

	public function test_get_data_returns_the_default_when_unset() {
		$this->assertSame( 'fallback', tml_get_data( 'never-set', 'fallback' ) );
	}

	// tml_is_wp_login() / tml_is_get_request() / tml_is_post_request() / tml_is_ajax_request()

	public function test_is_wp_login_reflects_pagenow() {
		global $pagenow;

		$pagenow = 'wp-login.php';
		$this->assertTrue( tml_is_wp_login() );

		$pagenow = 'index.php';
		$this->assertFalse( tml_is_wp_login() );
	}

	public function test_is_get_and_post_request_reflect_the_request_method() {
		$_SERVER['REQUEST_METHOD'] = 'GET';
		$this->assertTrue( tml_is_get_request() );
		$this->assertFalse( tml_is_post_request() );

		$_SERVER['REQUEST_METHOD'] = 'POST';
		$this->assertFalse( tml_is_get_request() );
		$this->assertTrue( tml_is_post_request() );
	}

	public function test_is_ajax_request_reflects_the_ajax_request_value() {
		$this->assertFalse( tml_is_ajax_request() );

		$_REQUEST['ajax'] = '1';
		$this->assertTrue( tml_is_ajax_request() );
	}

	// tml_flush_rewrite_rules()

	public function test_flush_rewrite_rules_clears_the_rewrite_rules_option() {
		update_option( 'rewrite_rules', array( 'foo' => 'bar' ) );

		tml_flush_rewrite_rules();

		$this->assertSame( '', get_option( 'rewrite_rules' ) );
	}

	// tml_add_error() / tml_get_errors() / tml_set_errors() / tml_has_errors()

	public function test_error_helpers_are_no_ops_without_a_current_form() {
		unset( $GLOBALS['wp']->query_vars['action'] );

		tml_add_error( 'some_error', 'Some message.' );

		$this->assertFalse( tml_has_errors() );
		$this->assertSame( array(), tml_get_errors()->get_error_codes() );
	}

	public function test_error_helpers_delegate_to_the_current_form() {
		global $wp;

		$wp->query_vars['action'] = 'login';
		tml_register_login_form();

		tml_add_error( 'some_error', 'Some message.' );

		$this->assertTrue( tml_has_errors() );
		$this->assertSame( array( 'Some message.' ), tml_get_errors()->get_error_messages( 'some_error' ) );

		tml_set_errors( new WP_Error( 'reset', 'Fresh.' ) );
		$this->assertSame( array( 'Fresh.' ), tml_get_errors()->get_error_messages( 'reset' ) );
	}

	// tml_get_username_label()

	public function test_username_label_defaults_to_username_or_email() {
		global $wp;

		$wp->query_vars['action'] = 'login';

		$this->assertSame( 'Username or Email Address', tml_get_username_label() );
	}

	public function test_username_label_for_username_login_type() {
		update_site_option( 'tml_login_type', 'username' );

		$this->assertSame( 'Username', tml_get_username_label( 'login' ) );

		delete_site_option( 'tml_login_type' );
	}

	public function test_username_label_for_email_login_type() {
		update_site_option( 'tml_login_type', 'email' );

		$this->assertSame( 'Email', tml_get_username_label( 'login' ) );

		delete_site_option( 'tml_login_type' );
	}

	public function test_username_label_for_register_action_is_always_username() {
		update_site_option( 'tml_login_type', 'email' );

		$this->assertSame( 'Username', tml_get_username_label( 'register' ) );

		delete_site_option( 'tml_login_type' );
	}

	public function test_username_label_can_be_filtered() {
		add_filter( 'tml_get_username_label', function () {
			return 'Filtered';
		} );

		$this->assertSame( 'Filtered', tml_get_username_label( 'login' ) );

		remove_all_filters( 'tml_get_username_label' );
	}

	// tml_validate_new_user_password()

	public function test_validate_new_user_password_is_a_no_op_when_passwords_are_not_allowed() {
		$errors = tml_validate_new_user_password();

		$this->assertFalse( $errors->has_errors() );
	}

	public function test_validate_new_user_password_requires_both_fields() {
		update_site_option( 'tml_user_passwords', true );

		$errors = tml_validate_new_user_password();

		$this->assertTrue( $errors->has_errors() );
		$this->assertContains( 'empty_password', $errors->get_error_codes() );
	}

	public function test_validate_new_user_password_rejects_a_backslash() {
		update_site_option( 'tml_user_passwords', true );

		// The check is against stripslashes($_POST['user_pass1']), so a
		// backslash that should still be visible after WP's own unslashing
		// needs to arrive here doubled, the same way addslashes() would have
		// escaped it on the way in.
		$_POST['user_pass1'] = 'pass\\\\word';
		$_POST['user_pass2'] = 'pass\\\\word';

		$errors = tml_validate_new_user_password();

		$this->assertContains( 'password_backslash', $errors->get_error_codes() );
	}

	public function test_validate_new_user_password_rejects_a_mismatch() {
		update_site_option( 'tml_user_passwords', true );

		$_POST['user_pass1'] = 'password-one';
		$_POST['user_pass2'] = 'password-two';

		$errors = tml_validate_new_user_password();

		$this->assertContains( 'password_mismatch', $errors->get_error_codes() );
	}

	public function test_validate_new_user_password_passes_with_matching_passwords() {
		update_site_option( 'tml_user_passwords', true );

		$_POST['user_pass1'] = 'matching-password';
		$_POST['user_pass2'] = 'matching-password';

		$errors = tml_validate_new_user_password();

		$this->assertFalse( $errors->has_errors() );
	}

	// tml_add_password_notice_to_new_user_notification_email()

	public function test_password_notice_is_added_when_user_passwords_are_allowed() {
		update_site_option( 'tml_user_passwords', true );

		$email = tml_add_password_notice_to_new_user_notification_email( array( 'message' => 'Original.' ) );

		$this->assertStringContainsString( 'already set your own password', $email['message'] );
	}

	public function test_password_notice_is_untouched_when_user_passwords_are_disallowed() {
		$email = tml_add_password_notice_to_new_user_notification_email( array( 'message' => 'Original.' ) );

		$this->assertSame( 'Original.', $email['message'] );
	}

	// tml_set_user_login()

	public function test_set_user_login_is_untouched_for_the_default_registration_type() {
		$_SERVER['REQUEST_METHOD'] = 'POST';
		$_POST['user_login']       = 'someuser';
		$_POST['user_email']       = 'someone@example.org';

		$this->assertSame( 'someuser', tml_set_user_login( 'someuser' ) );
	}

	public function test_set_user_login_uses_the_email_for_the_email_registration_type() {
		update_site_option( 'tml_registration_type', 'email' );

		$_SERVER['REQUEST_METHOD'] = 'POST';
		$_POST['user_login']       = 'someuser';
		$_POST['user_email']       = 'someone@example.org';

		$this->assertSame( 'someone@example.org', tml_set_user_login( 'someuser' ) );
	}

	// tml_registration_redirect()

	public function test_registration_redirect_is_untouched_when_auto_login_is_disabled() {
		$this->assertSame( 'https://example.org/original', tml_registration_redirect( 'https://example.org/original', null ) );
	}

	public function test_registration_redirect_uses_the_login_redirect_filter_when_auto_login_is_enabled() {
		update_site_option( 'tml_auto_login', true );

		add_filter( 'login_redirect', function () {
			return 'https://example.org/auto-login-redirect';
		} );

		$this->assertSame( 'https://example.org/auto-login-redirect', tml_registration_redirect( 'https://example.org/original', null ) );

		remove_all_filters( 'login_redirect' );
	}

	// tml_enforce_login_type()

	public function test_enforce_login_type_errors_on_a_null_user_for_email_login() {
		update_site_option( 'tml_login_type', 'email' );

		$result = tml_enforce_login_type( null, 'someuser', 'password' );

		$this->assertWPError( $result );
		$this->assertSame( 'invalid_email', $result->get_error_code() );

		delete_site_option( 'tml_login_type' );
	}

	public function test_enforce_login_type_passes_through_a_resolved_user() {
		$user = new WP_Error( 'some_other_error' );

		update_site_option( 'tml_login_type', 'email' );

		$this->assertSame( $user, tml_enforce_login_type( $user, 'someuser', 'password' ) );

		delete_site_option( 'tml_login_type' );
	}

	public function test_enforce_login_type_is_a_no_op_for_the_username_login_type() {
		update_site_option( 'tml_login_type', 'username' );

		$this->assertNull( tml_enforce_login_type( null, 'someuser', 'password' ) );

		delete_site_option( 'tml_login_type' );
	}

	// tml_buffer_action_hook()

	public function test_buffer_action_hook_returns_null_for_a_non_string_argument() {
		$this->assertNull( tml_buffer_action_hook( array( 'not-a-string' ) ) );
	}

	public function test_buffer_action_hook_buffers_the_hooked_output() {
		add_action( 'tml_test_buffered_hook', function () {
			echo 'buffered content';
		} );

		$this->assertSame( 'buffered content', tml_buffer_action_hook( 'tml_test_buffered_hook' ) );

		remove_all_actions( 'tml_test_buffered_hook' );
	}

	// tml_filter_get_edit_post_link() / tml_filter_comments_array()

	public function test_edit_post_link_is_blanked_for_a_synthetic_tml_page() {
		global $wp;

		$wp->query_vars['action'] = 'login';

		$this->assertSame( '', tml_filter_get_edit_post_link( 'https://example.org/edit', 0 ) );
	}

	public function test_edit_post_link_is_untouched_for_a_real_post() {
		global $wp;

		$wp->query_vars['action'] = 'login';

		$this->assertSame( 'https://example.org/edit', tml_filter_get_edit_post_link( 'https://example.org/edit', 42 ) );
	}

	public function test_edit_post_link_is_untouched_outside_a_tml_action() {
		unset( $GLOBALS['wp']->query_vars['action'] );

		$this->assertSame( 'https://example.org/edit', tml_filter_get_edit_post_link( 'https://example.org/edit', 0 ) );
	}

	public function test_comments_array_is_emptied_during_a_tml_action() {
		global $wp;

		$wp->query_vars['action'] = 'login';

		$this->assertSame( array(), tml_filter_comments_array( array( 'a-comment' ) ) );
	}

	public function test_comments_array_is_untouched_outside_a_tml_action() {
		unset( $GLOBALS['wp']->query_vars['action'] );

		$this->assertSame( array( 'a-comment' ), tml_filter_comments_array( array( 'a-comment' ) ) );
	}

	// Customizer nav menu filters

	public function test_customize_nav_menu_available_item_types_adds_the_tml_action_type() {
		$item_types = tml_filter_customize_nav_menu_available_item_types( array() );

		$this->assertCount( 1, $item_types );
		$this->assertSame( 'tml_action', $item_types[0]['type'] );
	}

	public function test_customize_nav_menu_available_items_ignores_other_types() {
		$items = tml_filter_customize_nav_menu_available_items( array( 'existing' ), 'post_type', 'page', 0 );

		$this->assertSame( array( 'existing' ), $items );
	}

	public function test_customize_nav_menu_available_items_ignores_pages_after_the_first() {
		$items = tml_filter_customize_nav_menu_available_items( array(), 'tml_action', '', 1 );

		$this->assertSame( array(), $items );
	}

	public function test_customize_nav_menu_available_items_lists_visible_actions_and_skips_hidden_ones() {
		$items = tml_filter_customize_nav_menu_available_items( array(), 'tml_action', '', 0 );

		$ids = wp_list_pluck( $items, 'object' );

		$this->assertContains( 'login', $ids );
		$this->assertNotContains( 'lostpassword', $ids, 'lostpassword has show_in_nav_menus = false and should be excluded.' );
	}

	// tml_nav_menu_css_class()

	public function test_nav_menu_css_class_marks_the_current_tml_action_item() {
		global $wp;

		$wp->query_vars['action'] = 'login';

		$item = (object) array( 'type' => 'tml_action', 'object' => 'login' );

		$classes = tml_nav_menu_css_class( array( 'existing' ), $item );

		$this->assertContains( 'current-menu-item', $classes );
		$this->assertContains( 'current_page_item', $classes );
	}

	public function test_nav_menu_css_class_is_untouched_for_a_non_current_action() {
		global $wp;

		$wp->query_vars['action'] = 'login';

		$item = (object) array( 'type' => 'tml_action', 'object' => 'logout' );

		$this->assertSame( array( 'existing' ), tml_nav_menu_css_class( array( 'existing' ), $item ) );
	}

	// tml_setup_nav_menu_item()

	public function test_setup_nav_menu_item_builds_a_new_item_from_an_action_object() {
		$menu_item = tml_setup_nav_menu_item( tml_get_action( 'login' ) );

		$this->assertSame( 'tml_action', $menu_item->type );
		$this->assertSame( 'login', $menu_item->object );
		$this->assertSame( 0, $menu_item->ID );
	}

	public function test_setup_nav_menu_item_invalidates_an_unknown_action() {
		$menu_item = tml_setup_nav_menu_item( (object) array(
			'type'   => 'tml_action',
			'object' => 'does-not-exist',
			'ID'     => 7,
		) );

		$this->assertTrue( $menu_item->_invalid );
	}

	public function test_setup_nav_menu_item_invalidates_an_action_hidden_from_nav_menu_items() {
		$menu_item = tml_setup_nav_menu_item( (object) array(
			'type'   => 'tml_action',
			'object' => 'lostpassword',
			'ID'     => 9,
		) );

		$this->assertTrue( $menu_item->_invalid );
	}

	public function test_setup_nav_menu_item_resolves_a_visible_action() {
		$menu_item = tml_setup_nav_menu_item( (object) array(
			'type'   => 'tml_action',
			'object' => 'login',
			'ID'     => 11,
		) );

		$this->assertSame( 11, $menu_item->object_id );
		$this->assertObjectNotHasProperty( '_invalid', $menu_item );
		$this->assertStringContainsString( 'action=login', $menu_item->url );
	}

	public function test_setup_nav_menu_item_adds_a_nonce_for_the_logout_action() {
		$menu_item = tml_setup_nav_menu_item( (object) array(
			'type'   => 'tml_action',
			'object' => 'logout',
			'ID'     => 13,
		) );

		$this->assertStringContainsString( '_wpnonce=', $menu_item->url );
	}

	// tml_add_rewrite_tags() / tml_add_rewrite_rules()

	public function test_add_rewrite_tags_registers_the_action_tag() {
		global $wp_rewrite;

		tml_add_rewrite_tags();

		$this->assertContains( '%action%', $wp_rewrite->rewritecode );
	}

	public function test_add_rewrite_rules_is_a_no_op_when_permalinks_are_off() {
		global $wp_rewrite;

		$this->set_permalink_structure( '' );

		$wp_rewrite->extra_rules_top = array();
		tml_add_rewrite_rules();

		$this->assertSame( array(), $wp_rewrite->extra_rules_top );
	}

	public function test_add_rewrite_rules_adds_a_rule_for_each_action_when_permalinks_are_on() {
		global $wp_rewrite;

		$this->set_permalink_structure( '/%postname%/' );

		$wp_rewrite->extra_rules_top = array();
		tml_add_rewrite_rules();

		$found = false;
		foreach ( array_keys( $wp_rewrite->extra_rules_top ) as $regex ) {
			if ( false !== strpos( $regex, 'login' ) ) {
				$found = true;
			}
		}
		$this->assertTrue( $found, 'Expected a rewrite rule for the login action.' );
	}

	// tml_parse_request()

	public function test_parse_request_is_a_no_op_without_an_action_query_var() {
		$wp = new WP();

		// Should not raise a warning/error when there's no 'action' query var.
		tml_parse_request( $wp );

		$this->assertTrue( true );
	}

	public function test_parse_request_ignores_an_unknown_action() {
		$wp              = new WP();
		$wp->query_vars['action'] = 'not-a-real-action';

		tml_parse_request( $wp );

		$this->assertSame( 'not-a-real-action', $wp->query_vars['action'] );
	}

	public function test_parse_request_fixes_the_retrievepassword_alias() {
		$wp                       = new WP();
		$wp->query_vars['action'] = 'retrievepassword';

		tml_parse_request( $wp );

		$this->assertSame( 'lostpassword', $wp->query_vars['action'] );
	}

	public function test_parse_request_fixes_the_rp_alias() {
		$wp                       = new WP();
		$wp->query_vars['action'] = 'rp';

		tml_parse_request( $wp );

		$this->assertSame( 'resetpass', $wp->query_vars['action'] );
	}

	// tml_parse_query() / tml_the_posts() bail branches, tested directly

	public function test_parse_query_bails_on_a_non_main_query() {
		$wp_query = new WP_Query();
		$wp_query->is_home = true;

		tml_parse_query( $wp_query );

		$this->assertObjectNotHasProperty( 'is_tml_action', $wp_query );
	}

	public function test_the_posts_bails_on_a_non_main_query() {
		$wp_query = new WP_Query();
		$wp_query->is_tml_action = true;

		$this->assertSame( array( 'unchanged' ), tml_the_posts( array( 'unchanged' ), $wp_query ) );
	}

	public function test_the_posts_bails_when_not_a_tml_action() {
		global $wp_query, $wp_the_query;

		$wp_query     = new WP_Query();
		$wp_the_query = $wp_query;
		$wp_query->is_tml_action = false;

		$this->assertSame( array( 'unchanged' ), tml_the_posts( array( 'unchanged' ), $wp_query ) );
	}

	// tml_parse_query() + tml_the_posts() chained together, the way the
	// 'parse_query'/'the_posts' hooks run them in a real request.

	public function test_a_tml_action_query_marks_the_query_and_synthesizes_a_page() {
		global $wp, $wp_query, $wp_the_query;

		$wp->query_vars['action'] = 'login';

		$wp_query                = new WP_Query();
		$wp_the_query             = $wp_query;
		$wp_query->is_home        = true;
		$wp_query->is_posts_page  = false;

		tml_parse_query( $wp_query );

		$this->assertTrue( $wp_query->is_tml_action );
		$this->assertTrue( $wp_query->is_page );
		$this->assertTrue( $wp_query->is_singular );
		$this->assertFalse( $wp_query->is_single );
		$this->assertFalse( $wp_query->is_home );

		$posts = tml_the_posts( array(), $wp_query );

		$this->assertCount( 1, $posts );
		$this->assertInstanceOf( 'WP_Post', $posts[0] );
		$this->assertSame( 0, $posts[0]->ID );
		$this->assertSame( 'login', $posts[0]->post_name );
	}

	// tml_page_template()

	public function test_page_template_does_not_stomp_on_the_block_theme_canvas() {
		$canvas = ABSPATH . WPINC . '/template-canvas.php';

		$this->assertSame( $canvas, tml_page_template( $canvas ) );
	}

	public function test_page_template_is_untouched_outside_a_tml_action() {
		unset( $GLOBALS['wp']->query_vars['action'] );

		$this->assertSame( 'original.php', tml_page_template( 'original.php' ) );
	}

	// tml_enqueue_styles() / tml_enqueue_scripts()

	public function test_enqueue_styles_registers_the_stylesheet() {
		tml_enqueue_styles();

		$this->assertTrue( wp_style_is( 'theme-my-login', 'enqueued' ) );
	}

	public function test_enqueue_scripts_depends_on_jquery_by_default() {
		unset( $GLOBALS['wp']->query_vars['action'] );

		tml_enqueue_scripts();

		$this->assertContains( 'jquery', wp_scripts()->registered['theme-my-login']->deps );
		$this->assertNotContains( 'password-strength-meter', wp_scripts()->registered['theme-my-login']->deps );
	}

	public function test_enqueue_scripts_adds_the_password_strength_meter_for_resetpass() {
		global $wp;

		$wp->query_vars['action'] = 'resetpass';

		tml_enqueue_scripts();

		$this->assertContains( 'password-strength-meter', wp_scripts()->registered['theme-my-login']->deps );
	}

	public function test_enqueue_scripts_fires_login_enqueue_scripts_during_a_tml_action() {
		global $wp;

		$wp->query_vars['action'] = 'login';

		$fired = false;
		add_action( 'login_enqueue_scripts', function () use ( &$fired ) {
			$fired = true;
		} );

		tml_enqueue_scripts();

		remove_all_actions( 'login_enqueue_scripts' );

		$this->assertTrue( $fired );
	}

	// tml_do_login_head() / tml_do_login_footer()

	public function test_do_login_head_is_a_no_op_outside_a_tml_action() {
		unset( $GLOBALS['wp']->query_vars['action'] );

		$fired = false;
		add_action( 'login_head', function () use ( &$fired ) {
			$fired = true;
		} );

		tml_do_login_head();

		remove_all_actions( 'login_head' );

		$this->assertFalse( $fired );
	}

	public function test_do_login_head_fires_during_a_tml_action() {
		global $wp;

		$wp->query_vars['action'] = 'login';

		$fired = false;
		add_action( 'login_head', function () use ( &$fired ) {
			$fired = true;
		} );

		// tml_do_login_head() also wires up core's wp_strict_cross_origin_referrer()
		// to 'login_head', which echoes a <meta> tag as a real side effect — buffer
		// it away since this test only cares that our own hook ran.
		ob_start();
		tml_do_login_head();
		ob_end_clean();

		remove_all_actions( 'login_head' );

		$this->assertTrue( $fired );
	}

	public function test_do_login_footer_is_a_no_op_outside_a_tml_action() {
		unset( $GLOBALS['wp']->query_vars['action'] );

		$fired = false;
		add_action( 'login_footer', function () use ( &$fired ) {
			$fired = true;
		} );

		tml_do_login_footer();

		remove_all_actions( 'login_footer' );

		$this->assertFalse( $fired );
	}

	public function test_do_login_footer_fires_during_a_tml_action() {
		global $wp;

		$wp->query_vars['action'] = 'login';

		$fired = false;
		add_action( 'login_footer', function () use ( &$fired ) {
			$fired = true;
		} );

		tml_do_login_footer();

		remove_all_actions( 'login_footer' );

		$this->assertTrue( $fired );
	}

	// tml_remove_default_actions_and_filters()

	public function test_remove_default_actions_and_filters_is_a_no_op_outside_a_tml_action() {
		unset( $GLOBALS['wp']->query_vars['action'] );

		tml_remove_default_actions_and_filters();

		$this->assertNotFalse( has_action( 'wp_head', 'feed_links' ) );
	}

	public function test_remove_default_actions_and_filters_strips_head_links_during_a_tml_action() {
		global $wp;

		$wp->query_vars['action'] = 'login';

		tml_remove_default_actions_and_filters();

		$this->assertFalse( has_action( 'wp_head', 'feed_links' ) );
		$this->assertFalse( has_filter( 'template_redirect', 'redirect_canonical' ) );
	}

	// tml_filter_site_url()

	public function test_filter_site_url_is_untouched_on_wp_login_php() {
		global $pagenow;

		$pagenow = 'wp-login.php';

		$url = 'https://example.org/wp-login.php?action=login';

		$this->assertSame( $url, tml_filter_site_url( $url, '', 'login' ) );
	}

	public function test_filter_site_url_is_untouched_for_an_interim_login_request() {
		global $pagenow;

		$pagenow = 'index.php';

		$url = 'https://example.org/wp-login.php?action=login&interim-login=1';

		$this->assertSame( $url, tml_filter_site_url( $url, '', 'login' ) );
	}

	public function test_filter_site_url_rewrites_a_wp_login_url_to_the_tml_action_url() {
		global $pagenow;

		$pagenow = 'index.php';

		$url = tml_filter_site_url( 'https://example.org/wp-login.php?action=login', '', 'login' );

		$this->assertStringContainsString( 'action=login', $url );
	}

	public function test_filter_site_url_fixes_the_retrievepassword_alias() {
		global $pagenow;

		$pagenow = 'index.php';

		$url = tml_filter_site_url( 'https://example.org/wp-login.php?action=retrievepassword', '', 'login' );

		$this->assertStringContainsString( 'action=lostpassword', $url );
	}

	public function test_filter_site_url_is_untouched_for_an_unrecognized_path() {
		global $pagenow;

		$pagenow = 'index.php';

		$url = 'https://example.org/some-other-script.php';

		$this->assertSame( $url, tml_filter_site_url( $url, '', 'login' ) );
	}

	public function test_filter_site_url_is_untouched_for_wp_signup_since_signup_is_not_a_tml_action() {
		global $pagenow;

		$pagenow = 'index.php';

		$url = 'https://example.org/wp-signup.php';

		$this->assertSame( $url, tml_filter_site_url( $url, '', 'login' ) );
	}

	// tml_set_new_user_password()

	public function test_set_new_user_password_is_a_no_op_when_user_passwords_are_not_allowed() {
		$user_id = self::factory()->user->create();
		$before  = get_userdata( $user_id )->user_pass;

		$_POST['user_pass1'] = 'a-new-password';
		tml_set_new_user_password( $user_id );

		$this->assertSame( $before, get_userdata( $user_id )->user_pass );
	}

	public function test_set_new_user_password_is_a_no_op_without_a_submitted_password() {
		update_site_option( 'tml_user_passwords', true );

		$user_id = self::factory()->user->create();
		$before  = get_userdata( $user_id )->user_pass;

		tml_set_new_user_password( $user_id );

		$this->assertSame( $before, get_userdata( $user_id )->user_pass );
	}

	public function test_set_new_user_password_sets_the_submitted_password() {
		update_site_option( 'tml_user_passwords', true );

		$user_id = self::factory()->user->create();

		$_POST['user_pass1'] = 'a-brand-new-password';
		tml_set_new_user_password( $user_id );

		$this->assertTrue( wp_check_password( 'a-brand-new-password', get_userdata( $user_id )->user_pass, $user_id ) );
	}

	// tml_handle_auto_login() bail branch (the enabled branch sets real auth cookies via setcookie(), out of scope here)

	public function test_handle_auto_login_is_a_no_op_when_auto_login_is_disabled() {
		$user_id = self::factory()->user->create();

		// Should not raise a warning/error, and should not attempt to set an auth cookie.
		tml_handle_auto_login( $user_id );

		$this->assertTrue( true );
	}

	// tml_send_new_user_notifications() bail branch (the enabled branch sends real mail via wp_new_user_notification(), out of scope here)

	public function test_send_new_user_notifications_is_a_no_op_when_both_notifications_are_disabled() {
		global $phpmailer;

		add_filter( 'tml_send_new_user_notification', '__return_false' );
		add_filter( 'tml_send_new_user_admin_notification', '__return_false' );

		$user_id = self::factory()->user->create();

		$phpmailer->mock_sent = array();
		tml_send_new_user_notifications( $user_id );

		$this->assertEmpty( $phpmailer->mock_sent );

		remove_all_filters( 'tml_send_new_user_notification' );
		remove_all_filters( 'tml_send_new_user_admin_notification' );
	}
}
