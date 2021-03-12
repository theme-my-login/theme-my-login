<?php

/**
 * Theme My Login Form Functions
 *
 * @package Theme_My_Login
 * @subpackage Forms
 */

/**
 * Register the default forms.
 *
 * @since 7.0
 */
function tml_register_default_forms() {
	if ( is_admin() ) {
		return;
	}

	tml_register_login_form();
	tml_register_registration_form();
	tml_register_lost_password_form();
	tml_register_password_reset_form();
}

/**
 * Register the login form.
 *
 * @since 7.0
 */
function tml_register_login_form() {

	tml_register_form( 'login', array(
		'action'     => tml_get_action_url( 'login' ),
		'attributes' => array_filter( array(
			'data-ajax' => tml_use_ajax() ? 1 : 0,
		) ),
	) );

	tml_add_form_field( 'login', 'log', array(
		'type'       => 'text',
		'label'      => tml_get_username_label( 'login' ),
		'value'      => tml_get_request_value( 'log', 'post' ),
		'id'         => 'user_login',
		'attributes' => array(
			'autocapitalize' => 'off',
		),
		'priority'   => 10,
	) );

	tml_add_form_field( 'login', 'pwd', array(
		'type'     => 'password',
		'label'    => __( 'Password' ),
		'value'    => '',
		'id'       => 'user_pass',
		'priority' => 15,
	) );

	tml_add_form_field( 'login', 'login_form', array(
		'type'     => 'action',
		'priority' => 20,
	) );

	tml_add_form_field( 'login', 'rememberme', array(
		'type'     => 'checkbox',
		'label'    => __( 'Remember Me' ),
		'value'    => 'forever',
		'id'       => 'rememberme',
		'priority' => 25,
	) );

	tml_add_form_field( 'login', 'submit', array(
		'type'     => 'submit',
		'value'    => __( 'Log In' ),
		'priority' => 30,
	) );

	$redirect_to = tml_get_request_value( 'redirect_to' );

	tml_add_form_field( 'login', 'redirect_to', array(
		'type'     => 'hidden',
		'value'    => ! empty( $redirect_to ) ? $redirect_to : admin_url(),
		'priority' => 30,
	) );
}

/**
 * Register the registration form.
 *
 * @since 7.0
 */
function tml_register_registration_form() {

	tml_register_form( 'register', array(
		'action'     => tml_get_action_url( 'register' ),
		'attributes' => array_filter( array(
			'novalidate' => 'novalidate',
			'data-ajax' => tml_use_ajax() ? 1 : 0,
		) ),
	) );

	if ( tml_is_default_registration_type() ) {
		tml_add_form_field( 'register', 'user_login', array(
			'type'       => 'text',
			'label'      => __( 'Username' ),
			'value'      => tml_get_request_value( 'user_login', 'post' ),
			'id'         => 'user_login',
			'attributes' => array(
				'autocapitalize' => 'off',
			),
			'priority'   => 10,
		) );
	} else {
		tml_add_form_field( 'register', 'user_login', array(
			'type'     => 'hidden',
			'label'    => '',
			'value'    => 'user' . md5( microtime() ),
			'id'       => 'user_login',
			'priority' => 10,
		) );
	}

	tml_add_form_field( 'register', 'user_email', array(
		'type'     => 'email',
		'label'    => __( 'Email' ),
		'value'    => tml_get_request_value( 'user_email', 'post' ),
		'id'       => 'user_email',
		'priority' => 15,
	) );

	if ( tml_allow_user_passwords() ) {
		tml_add_form_field( 'register', 'user_pass1', array(
			'type'       => 'password',
			'label'      => __( 'Password' ),
			'id'         => 'pass1',
			'attributes' => array(
				'autocomplete' => 'off',
			),
			'priority'   => 20,
		) );

		tml_add_form_field( 'register', 'user_pass2', array(
			'type'       => 'password',
			'label'      => __( 'Confirm Password', 'theme-my-login' ),
			'id'         => 'pass2',
			'attributes' => array(
				'autocomplete' => 'off',
			),
			'priority'   => 20,
		) );

		tml_add_form_field( 'register', 'indicator', array(
			'type'     => 'custom',
			'content'  => '<div id="pass-strength-result" class="hide-if-no-js" aria-live="polite">' . __( 'Strength indicator' ) . '</div>',
			'priority' => 20,
		) );

		tml_add_form_field( 'register', 'indicator_hint', array(
			'type'     => 'custom',
			'content'  => '<p class="description indicator-hint">' . wp_get_password_hint() . '</p>',
			'priority' => 20,
		) );
	}

	tml_add_form_field( 'register', 'register_form', array(
		'type'     => 'action',
		'priority' => 25,
	) );

	if ( ! tml_allow_user_passwords() ) {
		tml_add_form_field( 'register', 'reg_passmail', array(
			'type'     => 'custom',
			'content'  => '<p id="reg_passmail">' . __( 'Registration confirmation will be emailed to you.' ) . '</p>',
			'priority' => 30,
		) );
	}

	tml_add_form_field( 'register', 'submit', array(
		'type'     => 'submit',
		'value'    => __( 'Register' ),
		'priority' => 35,
	) );

	tml_add_form_field( 'register', 'redirect_to', array(
		'type'     => 'hidden',
		'value'    => apply_filters( 'registration_redirect', tml_get_request_value( 'redirect_to' ) ),
		'priority' => 35,
	) );
}

/**
 * Register the lost password form.
 *
 * @since 7.0
 */
function tml_register_lost_password_form() {

	tml_register_form( 'lostpassword', array(
		'action'     => tml_get_action_url( 'lostpassword' ),
		'attributes' => array_filter( array(
			'data-ajax' => tml_use_ajax() ? 1 : 0,
		) ),
	) );

	tml_add_form_field( 'lostpassword', 'user_login', array(
		'type'       => 'text',
		'label'      => tml_get_username_label( 'lostpassword' ),
		'value'      => tml_get_request_value( 'user_login', 'post' ),
		'id'         => 'user_login',
		'attributes' => array(
			'autocapitalize' => 'off',
		),
		'priority'   => 10,
	) );

	tml_add_form_field( 'lostpassword', 'lostpassword_form', array(
		'type'     => 'action',
		'priority' => 15,
	) );

	tml_add_form_field( 'lostpassword', 'submit', array(
		'type'     => 'submit',
		'value'    => __( 'Get New Password' ),
		'priority' => 20,
	) );

	tml_add_form_field( 'lostpassword', 'redirect_to', array(
		'type'     => 'hidden',
		'value'    => apply_filters( 'lostpassword_redirect', tml_get_request_value( 'redirect_to' ) ),
		'priority' => 20,
	) );
}

/**
 * Register the password reset form.
 *
 * @since 7.0
 */
function tml_register_password_reset_form() {

	tml_register_form( 'resetpass', array(
		'action'      => tml_get_action_url( 'resetpass' ),
		'attributes'  => array(
			'autocomplete' => 'off',
		),
		'render_args' => array(
			'show_links' => false,
		),
	) );

	tml_add_form_field( 'resetpass', 'pass1', array(
		'type'       => 'password',
		'label'      => __( 'New password' ),
		'id'         => 'pass1',
		'attributes' => array(
			'autocomplete' => 'off',
			'aria-describedby' => 'pass-strength-result',
		),
		'priority'   => 10,
	) );

	tml_add_form_field( 'resetpass', 'pass2', array(
		'type'       => 'password',
		'label'      => __( 'Confirm new password' ),
		'id'         => 'pass2',
		'attributes' => array(
			'autocomplete' => 'off',
			'aria-describedby' => 'pass-strength-result',
		),
		'priority'   => 10,
	) );

	tml_add_form_field( 'resetpass', 'indicator', array(
		'type'     => 'custom',
		'content'  => '<div id="pass-strength-result" class="hide-if-no-js" aria-live="polite">' . __( 'Strength indicator' ) . '</div>',
		'priority' => 10,
	) );

	tml_add_form_field( 'resetpass', 'indicator_hint', array(
		'type'     => 'custom',
		'content'  => '<p class="description indicator-hint">' . wp_get_password_hint() . '</p>',
		'priority' => 10,
	) );

	tml_add_form_field( 'resetpass', 'resetpass_form', array(
		'type'        => 'action',
		'priority'    => 15,
		'render_args' => array( wp_get_current_user() )
	) );

	tml_add_form_field( 'resetpass', 'submit', array(
		'type'     => 'submit',
		'value'    => __( 'Save Password' ),
		'priority' => 20,
	) );

	$rp_cookie = 'wp-resetpass-' . COOKIEHASH;
	if ( isset( $_COOKIE[ $rp_cookie ] ) && 0 < strpos( $_COOKIE[ $rp_cookie ], ':' ) ) {
		list( $rp_login, $rp_key ) = explode( ':', wp_unslash( $_COOKIE[ $rp_cookie ] ), 2 );

		tml_add_form_field( 'resetpass', 'rp_key', array(
			'type'     => 'hidden',
			'value'    => $rp_key,
			'priority' => 20,
		) );
	}
}

/**
 * Register a form.
 *
 * @since 7.0
 *
 * @see Theme_My_Login::register_form()
 *
 * @param string|Theme_My_Login_Form $form The form name or object.
 * @param array                      $args Optional. An array of arguments for registering a form.
 * @return Theme_My_Login_Form The form object.
 */
function tml_register_form( $form, $args = array() ) {

	if ( ! $form instanceof Theme_My_Login_Form ) {
		$form = new Theme_My_Login_Form( $form, $args );
	}

	return theme_my_login()->register_form( $form );
}

/**
 * Unregister a form.
 *
 * @since 7.0
 *
 * @param string|Theme_My_Login_Form $form The form name or object.
 */
function tml_unregister_form( $form ) {
	theme_my_login()->unregister_form( $form );
}

/**
 * Get a form.
 *
 * @since 7.0
 *
 * @param string|Theme_My_Login_Form $form Optional. The form name or object.
 * @return Theme_My_Login_Form|bool The form object or false if it doesn't exist.
 */
function tml_get_form( $form = '' ) {

	if ( $form instanceof Theme_My_Login_Form ) {
		return $form;
	}

	if ( empty( $form ) ) {
		if ( $action = tml_get_action() ) {
			$form = $action->get_name();
		}
	}

	return theme_my_login()->get_form( $form );
}

/**
 * Get all forms.
 *
 * @since 7.0
 *
 * @return array The forms.
 */
function tml_get_forms() {
	return theme_my_login()->get_forms();
}

/**
 * Determine if a form exists.
 *
 * @since 7.0
 *
 * @param string $form The form name.
 * @return bool True if the form exists or false otherwise.
 */
function tml_form_exists( $form ) {
	return apply_filters( 'tml_form_exists', array_key_exists( $form, tml_get_forms() ) );
}

/**
 * Add a form field.
 *
 * @since 7.0
 *
 * @see Theme_My_Login_Form::add_field()
 *
 * @param string|Theme_My_Login_Form       $form  The form name or object.
 * @param string|Theme_My_Login_Form_Field $field The field name or object.
 * @param array                            $args  Optional. An array of arguments for registering a form field.
 * @return Theme_My_Login_Form_Field The field object.
 */
function tml_add_form_field( $form, $field, $args = array() ) {

	if ( ! $form = tml_get_form( $form ) ) {
		return;
	}

	if ( ! $field instanceof Theme_My_Login_Form_Field ) {
		$field = new Theme_My_Login_Form_Field( $form, $field, $args );
	}

	return $form->add_field( $field );
}

/**
 * Remove a form field.
 *
 * @since 7.0
 *
 * @param string|Theme_My_Login_Form       $form  The form name or object.
 * @param string|Theme_My_Login_Form_Field $field The field name or object.
 */
function tml_remove_form_field( $form, $field ) {

	if ( ! $form = tml_get_form( $form ) ) {
		return;
	}

	$form->remove_field( $field );
}

/**
 * Get a form field.
 *
 * @since 7.0
 *
 * @param string|Theme_My_Login_Form $form  The form name or object.
 * @param string                     $field The field name.
 * @return Theme_My_Login_Form_Field|bool The field object false if it doesn't exist.
 */
function tml_get_form_field( $form, $field ) {

	if ( ! $form = tml_get_form( $form ) ) {
		return false;
	}

	return $form->get_field( $field );
}

/**
 * Get all form fields.
 *
 * @since 7.0
 *
 * @param string|Theme_My_Login_Form $form The form name or object.
 * @return array The form fields or false if the form doesn't exist.
 */
function tml_get_form_fields( $form ) {

	if ( ! $form = tml_get_form( $form ) ) {
		return false;
	}

	return $form->get_fields();
}
