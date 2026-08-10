<?php
/**
 * Coverage for Theme_My_Login_Widget, including the show/hide logic that
 * decides whether the widget renders at all on a given TML page.
 *
 * @package Theme_My_Login
 */

class Test_Widget extends WP_UnitTestCase {

	/** @var array */
	private $widget_args = array(
		'before_widget' => '<div class="widget">',
		'after_widget'  => '</div>',
		'before_title'  => '<h2>',
		'after_title'   => '</h2>',
	);

	public function tearDown(): void {
		global $wp;

		unset( $wp->query_vars['action'] );
		wp_set_current_user( 0 );

		parent::tearDown();
	}

	public function test_widget_is_registered_under_the_expected_id_base() {
		$widget = new Theme_My_Login_Widget();

		$this->assertSame( 'theme-my-login', $widget->id_base );
	}

	public function test_renders_login_form_when_logged_out() {
		$widget = new Theme_My_Login_Widget();

		ob_start();
		$widget->widget( $this->widget_args, array() );
		$output = ob_get_clean();

		$this->assertStringContainsString( 'name="log"', $output );
	}

	public function test_hidden_for_logged_in_users_on_the_login_page() {
		global $wp;

		$user_id = self::factory()->user->create();
		wp_set_current_user( $user_id );
		$wp->query_vars['action'] = 'login';

		$widget = new Theme_My_Login_Widget();

		ob_start();
		$widget->widget( $this->widget_args, array( 'action' => 'login' ) );
		$output = ob_get_clean();

		$this->assertSame( '', $output );
	}

	public function test_tml_show_widget_filter_can_force_it_hidden() {
		add_filter( 'tml_show_widget', '__return_false' );

		$widget = new Theme_My_Login_Widget();

		ob_start();
		$widget->widget( $this->widget_args, array() );
		$output = ob_get_clean();

		remove_filter( 'tml_show_widget', '__return_false' );

		$this->assertSame( '', $output );
	}
}
