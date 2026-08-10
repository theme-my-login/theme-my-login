<?php
/**
 * Coverage for Theme_My_Login_Form, the container every TML form (login,
 * register, lostpassword, resetpass) is built from: name/action/method
 * handling, attributes, field ordering, error/message rendering, and the
 * links every form auto-collects from the other registered actions.
 *
 * @package Theme_My_Login
 */

class Test_Form extends WP_UnitTestCase {

	public function test_name_is_sanitized() {
		$form = new Theme_My_Login_Form( 'My Form!' );

		$this->assertSame( 'myform', $form->get_name() );
	}

	public function test_method_defaults_to_post() {
		$form = new Theme_My_Login_Form( 'test' );

		$this->assertSame( 'post', $form->get_method() );
	}

	public function test_method_accepts_get() {
		$form = new Theme_My_Login_Form( 'test', array( 'method' => 'GET' ) );

		$this->assertSame( 'get', $form->get_method() );
	}

	public function test_invalid_method_falls_back_to_post() {
		$form = new Theme_My_Login_Form( 'test', array( 'method' => 'delete' ) );

		$this->assertSame( 'post', $form->get_method() );
	}

	public function test_get_action_action_can_be_filtered() {
		$form = new Theme_My_Login_Form( 'test', array( 'action' => 'https://example.org/original' ) );

		add_filter( 'tml_get_form_action', function () {
			return 'https://example.org/filtered';
		} );

		$action = $form->get_action();

		remove_all_filters( 'tml_get_form_action' );

		$this->assertSame( 'https://example.org/filtered', $action );
	}

	public function test_attributes_can_be_added_retrieved_and_removed() {
		$form = new Theme_My_Login_Form( 'test' );

		$form->add_attribute( 'class', 'tml-form' );

		$this->assertSame( 'tml-form', $form->get_attribute( 'class' ) );
		$this->assertArrayHasKey( 'class', $form->get_attributes() );

		$form->remove_attribute( 'class' );

		$this->assertFalse( $form->get_attribute( 'class' ) );
	}

	public function test_missing_attribute_returns_false() {
		$form = new Theme_My_Login_Form( 'test' );

		$this->assertFalse( $form->get_attribute( 'does-not-exist' ) );
	}

	public function test_fields_are_sorted_by_priority() {
		$form = new Theme_My_Login_Form( 'test' );

		$form->add_field( new Theme_My_Login_Form_Field( $form, 'second', array( 'priority' => 20 ) ) );
		$form->add_field( new Theme_My_Login_Form_Field( $form, 'first', array( 'priority' => 10 ) ) );

		$this->assertSame( array( 'first', 'second' ), array_values( array_map( function ( $field ) {
			return $field->get_name();
		}, $form->get_fields() ) ) );
	}

	public function test_get_field_returns_false_when_missing() {
		$form = new Theme_My_Login_Form( 'test' );

		$this->assertFalse( $form->get_field( 'does-not-exist' ) );
	}

	public function test_remove_field_by_name() {
		$form = new Theme_My_Login_Form( 'test' );

		$form->add_field( new Theme_My_Login_Form_Field( $form, 'user_login' ) );
		$form->remove_field( 'user_login' );

		$this->assertFalse( $form->get_field( 'user_login' ) );
	}

	public function test_form_starts_with_no_errors() {
		$form = new Theme_My_Login_Form( 'test' );

		$this->assertFalse( $form->has_errors() );
		$this->assertNull( $form->render_errors() );
	}

	public function test_added_error_is_reflected_in_has_errors_and_get_errors() {
		$form = new Theme_My_Login_Form( 'test' );

		$form->add_error( 'invalid_username', 'Unknown username.' );

		$this->assertTrue( $form->has_errors() );
		$this->assertSame( array( 'Unknown username.' ), $form->get_errors()->get_error_messages( 'invalid_username' ) );
	}

	public function test_render_errors_separates_errors_from_messages() {
		$form = new Theme_My_Login_Form( 'test' );

		$form->add_error( 'invalid_username', 'Unknown username.', 'error' );
		$form->add_error( 'registered', 'You have registered successfully.', 'message' );

		$output = $form->render_errors();

		$this->assertStringContainsString( 'tml-errors', $output );
		$this->assertStringContainsString( 'Unknown username.', $output );
		$this->assertStringContainsString( 'tml-messages', $output );
		$this->assertStringContainsString( 'You have registered successfully.', $output );
	}

	public function test_set_errors_replaces_existing_errors() {
		$form = new Theme_My_Login_Form( 'test' );

		$form->add_error( 'invalid_username', 'Unknown username.' );
		$form->set_errors( new WP_Error( 'reset', 'Fresh error.' ) );

		$this->assertSame( array( 'Fresh error.' ), $form->get_errors()->get_error_messages( 'reset' ) );
		$this->assertSame( array(), $form->get_errors()->get_error_messages( 'invalid_username' ) );
	}

	public function test_constructor_auto_populates_links_for_other_actions() {
		$form = new Theme_My_Login_Form( 'login' );

		$this->assertNotFalse( $form->get_link( 'lostpassword' ) );
		$this->assertFalse( $form->get_link( 'login' ) );
	}

	public function test_links_can_be_added_retrieved_and_removed() {
		$form = new Theme_My_Login_Form( 'test' );

		$form->add_link( 'Custom Link!', array( 'text' => 'Custom', 'url' => 'https://example.org' ) );

		$link = $form->get_link( 'customlink' );

		$this->assertSame( 'Custom', $link['text'] );
		$this->assertSame( 'https://example.org', $link['url'] );

		$form->remove_link( 'customlink' );

		$this->assertFalse( $form->get_link( 'customlink' ) );
	}

	public function test_get_links_can_be_filtered() {
		$form = new Theme_My_Login_Form( 'test' );

		add_filter( 'tml_get_form_links', function () {
			return array();
		} );

		$links = $form->get_links();

		remove_all_filters( 'tml_get_form_links' );

		$this->assertSame( array(), $links );
	}

	public function test_render_links_renders_a_list_item_per_link() {
		$form = new Theme_My_Login_Form( 'test' );

		$form->add_link( 'custom', array( 'text' => 'Custom Link', 'url' => 'https://example.org/' ) );

		$output = $form->render_links();

		$this->assertStringContainsString( 'tml-links', $output );
		$this->assertStringContainsString( 'tml-custom-link', $output );
		$this->assertStringContainsString( 'href="https://example.org/"', $output );
		$this->assertStringContainsString( 'Custom Link', $output );
	}

	public function test_render_links_returns_nothing_when_there_are_no_links() {
		$form = new Theme_My_Login_Form( 'test' );

		foreach ( array_keys( $form->get_links() ) as $link ) {
			$form->remove_link( $link );
		}

		$this->assertSame( '', (string) $form->render_links() );
	}

	public function test_render_form_wraps_fields_in_a_form_element() {
		$form = new Theme_My_Login_Form( 'login', array(
			'action' => 'https://example.org/login',
			'method' => 'post',
		) );

		$form->add_field( new Theme_My_Login_Form_Field( $form, 'user_login', array( 'type' => 'text' ) ) );

		$output = $form->render_form();

		$this->assertStringContainsString( '<form name="login"', $output );
		$this->assertStringContainsString( 'action="https://example.org/login"', $output );
		$this->assertStringContainsString( 'method="post"', $output );
		$this->assertStringContainsString( 'name="user_login"', $output );
		$this->assertStringContainsString( '</form>', $output );
	}

	public function test_render_produces_the_container_with_the_form_name_class() {
		$form = new Theme_My_Login_Form( 'login' );

		$output = $form->render();

		$this->assertStringContainsString( '<div class="tml tml-login">', $output );
		$this->assertStringContainsString( 'tml-alerts', $output );
		$this->assertStringContainsString( '</div>', $output );
	}

	public function test_render_can_hide_the_form_and_the_links() {
		$form = new Theme_My_Login_Form( 'login' );

		$form->add_field( new Theme_My_Login_Form_Field( $form, 'user_login' ) );

		$output = $form->render( array(
			'show_form'  => false,
			'show_links' => false,
		) );

		$this->assertStringNotContainsString( '<form', $output );
		$this->assertStringNotContainsString( 'tml-links', $output );
	}

	public function test_render_wraps_before_and_after_content() {
		$form = new Theme_My_Login_Form( 'test' );

		$output = $form->render( array(
			'before' => 'BEFORE|',
			'after'  => '|AFTER',
		) );

		$this->assertStringStartsWith( 'BEFORE|', $output );
		$this->assertStringEndsWith( '|AFTER', $output );
	}

	public function test_render_fires_tml_render_form_action() {
		$fired = array();

		add_action( 'tml_render_form', function ( $name, $form ) use ( &$fired ) {
			$fired[] = array( $name, $form );
		}, 10, 2 );

		$form = new Theme_My_Login_Form( 'test' );
		$form->render();

		remove_all_actions( 'tml_render_form' );

		$this->assertCount( 1, $fired );
		$this->assertSame( 'test', $fired[0][0] );
		$this->assertSame( $form, $fired[0][1] );
	}

	public function test_render_applies_before_and_after_form_filters() {
		add_filter( 'tml_before_form', function ( $output ) {
			return $output . 'BEFORE-FILTER|';
		} );
		add_filter( 'tml_after_form', function ( $output ) {
			return $output . '|AFTER-FILTER';
		} );

		$form   = new Theme_My_Login_Form( 'test' );
		$output = $form->render();

		remove_all_filters( 'tml_before_form' );
		remove_all_filters( 'tml_after_form' );

		$this->assertStringContainsString( 'BEFORE-FILTER|', $output );
		$this->assertStringContainsString( '|AFTER-FILTER', $output );
	}
}
