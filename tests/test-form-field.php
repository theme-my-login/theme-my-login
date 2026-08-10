<?php
/**
 * Coverage for Theme_My_Login_Form_Field::render(), the markup TML actually
 * puts on the page for every field type. This is pure output generation
 * (esc_attr/esc_html + string concatenation) with no exits, so it's cheap
 * to exercise directly and a good tripwire for accidental markup breakage.
 *
 * @package Theme_My_Login
 */

class Test_Form_Field extends WP_UnitTestCase {

	/** @var Theme_My_Login_Form */
	private $form;

	public function setUp(): void {
		parent::setUp();

		$this->form = new Theme_My_Login_Form( 'test' );
	}

	private function field( $name, $args = array() ) {
		return new Theme_My_Login_Form_Field( $this->form, $name, $args );
	}

	public function test_text_field_renders_name_type_and_escaped_value() {
		$output = $this->field( 'user_login', array(
			'type'  => 'text',
			'value' => 'jane"doe',
		) )->render();

		$this->assertStringContainsString( 'name="user_login"', $output );
		$this->assertStringContainsString( 'type="text"', $output );
		$this->assertStringContainsString( 'value="jane&quot;doe"', $output );
	}

	public function test_textarea_field_escapes_its_value() {
		$output = $this->field( 'bio', array(
			'type'  => 'textarea',
			'value' => '</textarea><script>alert(1)</script>',
		) )->render();

		$this->assertStringContainsString( '&lt;/textarea&gt;&lt;script&gt;alert(1)&lt;/script&gt;', $output );
		$this->assertStringNotContainsString( '<script>', $output );
	}

	public function test_label_is_linked_to_the_field_id() {
		$output = $this->field( 'user_login', array(
			'label' => 'Username',
			'id'    => 'user_login',
		) )->render();

		$this->assertStringContainsString( '<label class="tml-label" for="user_login">Username</label>', $output );
	}

	public function test_error_is_rendered_for_text_fields() {
		$output = $this->field( 'user_login', array(
			'error' => 'This field is required.',
		) )->render();

		$this->assertStringContainsString( '<span class="tml-error">This field is required.</span>', $output );
	}

	public function test_checkbox_is_checked_only_when_requested() {
		$checked   = $this->field( 'rememberme', array( 'type' => 'checkbox', 'checked' => true ) )->render();
		$unchecked = $this->field( 'rememberme', array( 'type' => 'checkbox' ) )->render();

		$this->assertStringContainsString( 'checked="checked"', $checked );
		$this->assertStringNotContainsString( 'checked="checked"', $unchecked );
	}

	public function test_dropdown_marks_the_matching_option_selected() {
		$output = $this->field( 'role', array(
			'type'    => 'dropdown',
			'value'   => 'editor',
			'options' => array(
				'subscriber' => 'Subscriber',
				'editor'     => 'Editor',
			),
		) )->render();

		$this->assertMatchesRegularExpression( '/<option value="editor" selected="selected">Editor<\/option>/', $output );
		$this->assertStringNotContainsString( 'value="subscriber" selected', $output );
	}

	public function test_radio_group_marks_the_matching_option_checked() {
		$output = $this->field( 'login_type', array(
			'type'    => 'radio-group',
			'value'   => 'email',
			'options' => array(
				'username' => 'Username',
				'email'    => 'Email',
			),
		) )->render();

		$this->assertMatchesRegularExpression( '/value="email"[^>]*checked="checked"/', $output );
		$this->assertDoesNotMatchRegularExpression( '/value="username"[^>]*checked="checked"/', $output );
	}

	public function test_hidden_field_has_no_wrapper() {
		$output = $this->field( 'redirect_to', array(
			'type'  => 'hidden',
			'value' => 'https://example.org',
		) )->render();

		$this->assertStringNotContainsString( 'tml-field-wrap', $output );
		$this->assertStringContainsString( 'type="hidden"', $output );
	}

	public function test_custom_field_supports_a_callable_content_generator() {
		$output = $this->field( 'notice', array(
			'type'    => 'custom',
			'content' => function () {
				return '<p>Custom markup</p>';
			},
		) )->render();

		$this->assertStringContainsString( '<p>Custom markup</p>', $output );
	}

	public function test_tml_before_and_after_form_field_filters_wrap_the_output() {
		add_filter( 'tml_before_form_field', function ( $output ) {
			return $output . 'BEFORE|';
		} );
		add_filter( 'tml_after_form_field', function ( $output ) {
			return $output . '|AFTER';
		} );

		$output = $this->field( 'user_login' )->render();

		remove_all_filters( 'tml_before_form_field' );
		remove_all_filters( 'tml_after_form_field' );

		$this->assertStringContainsString( 'BEFORE|', $output );
		$this->assertStringContainsString( '|AFTER', $output );
	}
}
