<?php

/**
 * Theme My Login Multisite Functions
 *
 * @package Theme_My_Login
 * @subpackage Multisite
 */

/**
 * Register the default multisite actions.
 *
 * @since 7.0
 */
function tml_ms_register_default_actions() {
	tml_register_action( 'signup', array(
		'title'             => '',
		'slug'              => 'signup',
		'callback'          => 'tml_ms_signup_handler',
		'network'           => true,
		'show_on_forms'     => false,
		'show_in_widget'    => false,
		'show_in_nav_menus' => false,
	) );

	tml_register_action( 'activate', array(
		'title'             => '',
		'slug'              => 'activate',
		'callback'          => 'tml_ms_activation_handler',
		'network'           => true,
		'show_on_forms'     => false,
		'show_in_widget'    => false,
		'show_in_nav_menus' => false,
	) );
}

/**
 * Register the default multisite forms.
 *
 * @since 7.0
 */
function tml_ms_register_default_forms() {
	tml_ms_register_user_signup_form();
	tml_ms_register_blog_signup_form();
	tml_ms_register_another_blog_signup_form();
	tml_ms_register_activation_form();
}

/**
 * Register the user signup form.
 *
 * @since 7.0
 */
function tml_ms_register_user_signup_form() {

	tml_register_form( 'user_signup', array(
		'action'      => tml_get_action_url( 'signup' ),
		'attributes'  => array(
			'id'         => 'setupform',
			'novalidate' => 'novalidate',
		),
		'render_args' => array(
			'show_links' => false,
		),
	) );

	tml_add_form_field( 'user_signup', 'stage', array(
		'type'     => 'hidden',
		'value'    => 'validate-user-signup',
		'priority' => 5,
	) );

	tml_add_form_field( 'user_signup', 'signup_hidden_fields', array(
		'type'        => 'action',
		'render_args' => array( 'validate-user' ),
	) );

	if ( tml_is_default_registration_type() ) {
		tml_add_form_field( 'user_signup', 'user_name', array(
			'type'        => 'text',
			'label'       => __( 'Username:' ),
			'description' => __( '(Must be at least 4 characters, letters and numbers only.)' ),
			'value'       => '',
			'id'          => 'user_name',
			'attributes'  => array(
				'autocapitalize' => 'none',
				'autocorrect'    => 'off',
				'maxlength'      => 60,
			),
			'priority'    => 10,
		) );
	} else {
		tml_add_form_field( 'user_signup', 'user_name', array(
			'type'     => 'hidden',
			'label'    => '',
			'value'    => 'user' . md5( microtime() ),
			'id'       => 'user_name',
			'priority' => 10,
		) );
	}

	tml_add_form_field( 'user_signup', 'user_email', array(
		'type'        => 'email',
		'label'       => __( 'Email&nbsp;Address:' ),
		'description' => __( 'We send your registration email to this address. (Double-check your email address before continuing.)' ),
		'value'       => '',
		'id'          => 'user_email',
		'attributes'  => array(
			'maxlength' => 200,
		),
		'priority'    => 15,
	) );

	tml_add_form_field( 'user_signup', 'signup_extra_fields', array(
		'type'        => 'action',
		'render_args' => array( tml_get_errors() ),
	) );

	$active_signup = tml_ms_signup_get_active_signup();

	if ( 'blog' == $active_signup ) {
		tml_add_form_field( 'user_signup', 'signup_for', array(
			'type'     => 'hidden',
			'value'    => 'blog',
			'id'       => 'signupblog',
			'priority' => 20,
		) );
	} elseif ( 'user' == $active_signup ) {
		tml_add_form_field( 'user_signup', 'signup_for', array(
			'type'     => 'hidden',
			'value'    => 'user',
			'id'       => 'signupuser',
			'priority' => 20,
		) );
	} else {
		tml_add_form_field( 'user_signup', 'signup_for', array(
			'type'     => 'radio-group',
			'options'  => array(
				'blog' => __( 'Gimme a site!' ),
				'user' => __( 'Just a username, please.' ),
			),
			'value'    => isset( $_POST['signup_for'] ) ? $_POST['signup_for'] : 'blog',
			'priority' => 20,
		) );
	}

	tml_add_form_field( 'user_signup', 'submit', array(
		'type'     => 'submit',
		'name'     => 'submit',
		'value'    => __( 'Next' ),
		'priority' => 25,
	) );
}

/**
 * Register the blog signup form.
 *
 * @since 7.0
 */
function tml_ms_register_blog_signup_form() {

	tml_register_form( 'blog_signup', array(
		'action'      => tml_get_action_url( 'signup' ),
		'attributes'  => array(
			'id' => 'setupform',
		),
		'render_args' => array(
			'show_links' => false,
		)
	) );

	tml_add_form_field( 'blog_signup', 'stage', array(
		'type'     => 'hidden',
		'value'    => 'validate-blog-signup',
		'priority' => 5,
	) );
	tml_add_form_field( 'blog_signup', 'user_name', array(
		'type'     => 'hidden',
		'priority' => 5,
	) );
	tml_add_form_field( 'blog_signup', 'user_email', array(
		'type'     => 'hidden',
		'priority' => 5,
	) );

	tml_add_form_field( 'blog_signup', 'signup_hidden_fields', array(
		'type'        => 'action',
		'render_args' => array( 'validate-site' ),
	) );

	tml_ms_add_blog_signup_form_fields( 'blog_signup' );

	tml_add_form_field( 'blog_signup', 'submit', array(
		'type'     => 'submit',
		'value'    => __( 'Signup' ),
		'priority' => 30,
	) );
}

/**
 * Register another blog signup form.
 *
 * @since 7.0
 */
function tml_ms_register_another_blog_signup_form() {

	tml_register_form( 'another_blog_signup', array(
		'action'      => tml_get_action_url( 'signup' ),
		'attributes'  => array(
			'id' => 'setupform',
		),
		'render_args' => array(
			'show_links' => false,
		)
	) );

	tml_add_form_field( 'another_blog_signup', 'stage', array(
		'type'     => 'hidden',
		'value'    => 'gimmeanotherblog',
		'priority' => 5,
	) );

	tml_add_form_field( 'another_blog_signup', 'signup_hidden_fields', array(
		'type'        => 'action',
		'render_args' => array( 'create-another-site' ),
	) );

	tml_ms_add_blog_signup_form_fields( 'another_blog_signup' );

	tml_add_form_field( 'another_blog_signup', 'submit', array(
		'type'     => 'submit',
		'value'    => __( 'Create Site' ),
		'priority' => 30,
	) );
}

/**
 * Add the blog signup form fields.
 *
 * @since 7.0
 *
 * @param string $form The form name.
 */
function tml_ms_add_blog_signup_form_fields( $form ) {
	$current_network = get_network();

	if ( ! is_subdomain_install() ) {
		$control_after = '<br />';
		if ( ! is_user_logged_in() ) {
			$control_after .= '<p>(<strong>' . sprintf( __( 'Your address will be %s.' ), $current_network->domain . $current_network->path . __( 'sitename' ) ) . '</strong>) ';
			$control_after .= __( 'Must be at least 4 characters, letters and numbers only. It cannot be changed, so choose carefully!' ) . '</p>';
		}

		tml_add_form_field( $form, 'blogname', array(
			'type'        => 'text',
			'label'       => __( 'Site Name: '),
			'value'       => '',
			'id'          => 'blogname',
			'attributes'  => array(
				'maxlength' => 60,
			),
			'priority'    => 10,
			'render_args' => array(
				'control_before' => '<span class="prefix_address">' . $current_network->domain . $current_network->path . '</span>',
				'control_after'  => $control_after,
			),
		) );
	} else {
		$control_after = '<span class="suffix_address">.' . ( $site_domain = preg_replace( '|^www\.|', '', $current_network->domain ) ) . '</span><br />';
		if ( ! is_user_logged_in() ) {
			$control_after .= '<p>(<strong>' . sprintf( __( 'Your address will be %s.' ), __( 'domain' ) . '.' . $site_domain . $current_network->path ) . '</strong>) ';
			$control_after .= __( 'Must be at least 4 characters, letters and numbers only. It cannot be changed, so choose carefully!' ) . '</p>';
		}

		tml_add_form_field( $form, 'blogname', array(
			'type'        => 'text',
			'label'       => __( 'Site Domain:' ),
			'value'       => '',
			'id'          => 'blogname',
			'attributes'  => array(
				'maxlength' => 60,
			),
			'priority'    => 10,
			'render_args' => array(
				'control_after' => $control_after,
			),
		) );
	}

	tml_add_form_field( $form, 'blog_title', array(
		'type'     => 'text',
		'label'    => __( 'Site Title:' ),
		'id'       => 'blog_title',
		'priority' => 15,
	) );

	if ( $language_field = tml_ms_render_blog_signup_language_field() ) {
		tml_add_form_field( $form, 'site_language', array(
			'type'     => 'custom',
			'content'  => $language_field,
			'priority' => 20,
		) );
	}

	tml_add_form_field( $form, 'blog_public', array(
		'type'        => 'radio-group',
		'label'       => __( 'Privacy:' ),
		'value'       => isset( $_POST['blog_public'] ) && 0 == $_POST['blog_public'] ? 0 : 1,
		'options'     => array(
			'1' => __( 'Yes' ),
			'0' => __( 'No'  ),
		),
		'priority'    => 25,
		'render_args' => array(
			'control_before' => __( 'Allow search engines to index this site.' ) . '<br style="clear:both" />',
		),
	) );

	tml_add_form_field( $form, 'signup_blogform', array(
		'type'        => 'action',
		'render_args' => array( tml_get_errors() ),
	) );
}

/**
 * Register the activation form.
 *
 * @since 7.0
 */
function tml_ms_register_activation_form() {

	tml_register_form( 'activate', array(
		'action'      => tml_get_action_url( 'activate' ),
		'attributes'  => array(
			'id'         => 'activateform',
			'novalidate' => 'novalidate',
		),
		'render_args' => array(
			'show_links' => false,
		),
	) );

	$key = tml_get_request_value( 'key' );

	tml_add_form_field( 'activate', 'key', array(
		'type'     => tml_allow_user_passwords() && $key ? 'hidden' : 'text',
		'label'    => tml_allow_user_passwords() && $key ? ''       : __( 'Activation Key:' ),
		'value'    => $key,
		'id'       => 'key',
		'priority' => 5,
	) );

	if ( tml_allow_user_passwords() && $key ) {
		tml_add_form_field( 'activate', 'user_pass1', array(
			'type'       => 'password',
			'label'      => __( 'Password' ),
			'id'         => 'pass1',
			'attributes' => array(
				'autocomplete' => 'off',
			),
			'priority'   => 10,
		) );

		tml_add_form_field( 'activate', 'user_pass2', array(
			'type'       => 'password',
			'label'      => __( 'Confirm Password' ),
			'id'         => 'pass2',
			'attributes' => array(
				'autocomplete' => 'off',
			),
			'priority'   => 10,
		) );

		tml_add_form_field( 'activate', 'indicator', array(
			'type'     => 'custom',
			'content'  => '<div id="pass-strength-result" class="hide-if-no-js" aria-live="polite">' . __( 'Strength indicator' ) . '</div>',
			'priority' => 10,
		) );

		tml_add_form_field( 'activate', 'indicator_hint', array(
			'type'     => 'custom',
			'content'  => '<p class="description indicator-hint">' . wp_get_password_hint() . '</p>',
			'priority' => 10,
		) );
	}

	tml_add_form_field( 'activate', 'submit', array(
		'type'     => 'submit',
		'value'    => __( 'Activate' ),
		'priority' => 15,
	) );
}

/**
 * Handle the signup action.
 *
 * @since 7.0
 */
function tml_ms_signup_handler() {

	if ( is_array( get_site_option( 'illegal_names' ) ) && isset( $_GET['new'] ) && in_array( $_GET['new'], get_site_option( 'illegal_names' ) ) ) {
		wp_redirect( network_home_url() );
		exit;
	}

	if ( ! is_multisite() ) {
		wp_redirect( wp_registration_url() );
		exit;
	}

	if ( ! is_main_site() ) {
		wp_redirect( network_site_url( 'wp-signup.php' ) );
		exit;
	}

	$active_signup = tml_ms_signup_get_active_signup();

	$newblogname = isset( $_GET['new'] ) ? strtolower( preg_replace( '/^-|-$|[^-a-zA-Z0-9]/', '', $_GET['new'] ) ) : null;

	$stage = isset( $_POST['stage'] ) ? $_POST['stage'] : 'default';
	switch ( $stage ) {
		case 'validate-user-signup':
			if ( $active_signup == 'all' || $_POST['signup_for'] == 'blog' && $active_signup == 'blog' || $_POST['signup_for'] == 'user' && $active_signup == 'user' ) {
				$result     = wpmu_validate_user_signup( $_POST['user_name'], $_POST['user_email'] );
				$user_name  = $result['user_name'];
				$user_email = $result['user_email'];
				$errors     = $result['errors'];

				tml_set_data( 'signup_result', $result );

				if ( $errors->get_error_code() ) {
					tml_set_data( 'signup_form', 'user' );
					return;
				}

				if ( 'blog' == $_POST['signup_for'] ) {
					tml_set_data( 'signup_form', 'blog' );
					return;
				}

				/** This filter is documented in wp-signup.php */
				wpmu_signup_user( $user_name, $user_email, apply_filters( 'add_signup_meta', array() ) );
			}
			break;

		case 'validate-blog-signup':
			if ( $active_signup == 'all' || $active_signup == 'blog' ) {
				$user_result = wpmu_validate_user_signup( $_POST['user_name'], $_POST['user_email'] );
				$user_name   = $user_result['user_name'];
				$user_email  = $user_result['user_email'];
				$user_errors = $user_result['errors'];

				tml_set_data( 'signup_user_result', $user_result );

				if ( $user_errors->get_error_code() ) {
					tml_set_data( 'signup_form'. 'user' );
					return;
				}

				$result     = wpmu_validate_blog_signup( $_POST['blogname'], $_POST['blog_title'] );
				$domain     = $result['domain'];
				$path       = $result['path'];
				$blogname   = $result['blogname'];
				$blog_title = $result['blog_title'];
				$errors     = $result['errors'];

				tml_set_data( 'signup_result', $result );

				if ( $errors->get_error_code() ) {
					tml_set_data( 'signup_form', 'blog' );
					return;
				}

				$public      = (int) $_POST['blog_public'];
				$signup_meta = array(
					'lang_id' => 1,
					'public'  => $public,
				);

				// Handle the language setting for the new site.
				if ( ! empty( $_POST['site_language'] ) ) {
					$languages = tml_ms_signup_get_available_languages();
					if ( in_array( $_POST['site_language'], $languages ) ) {
						$language = wp_unslash( sanitize_text_field( $_POST['site_language'] ) );
						if ( $language ) {
							$signup_meta['WPLANG'] = $language;
						}
					}
				}

				/** This filter is documented in wp-signup.php */
				$meta = apply_filters( 'add_signup_meta', $signup_meta );

				wpmu_signup_blog( $domain, $path, $blog_title, $user_name, $user_email, $meta );
			}
			break;

		case 'gimmeanotherblog':
			if ( ! is_user_logged_in() ) {
				die;
			}

			$current_user = wp_get_current_user();

			$result     = wpmu_validate_blog_signup( $_POST['blogname'], $_POST['blog_title'], $current_user );
			$domain     = $result['domain'];
			$path       = $result['path'];
			$blogname   = $result['blogname'];
			$blog_title = $result['blog_title'];
			$errors     = $result['errors'];

			tml_set_data( 'signup_result', $result );

			if ( $errors->get_error_code() ) {
				tml_set_data( 'signup_form', 'another_blog' );
				return;
			}

			$public             = (int) $_POST['blog_public'];
			$blog_meta_defaults = array(
				'lang_id' => 1,
				'public'  => $public,
			);

			// Handle the language setting for the new site.
			if ( ! empty( $_POST['site_language'] ) ) {

				$languages = tml_ms_signup_get_available_languages();

				if ( in_array( $_POST['site_language'], $languages ) ) {
					$language = wp_unslash( sanitize_text_field( $_POST['site_language'] ) );

					if ( $language ) {
						$blog_meta_defaults['WPLANG'] = $language;
					}
				}
			}

			/** This filter is documented in wp-signup.php */
			$meta_defaults = apply_filters( 'signup_create_blog_meta', $blog_meta_defaults );

			/** This filter is documented in wp-signup.php */
			$meta = apply_filters( 'add_signup_meta', $meta_defaults );

			$blog_id = wpmu_create_blog( $domain, $path, $blog_title, $current_user->ID, $meta, get_current_network_id() );

			tml_set_data( 'signup_blog_id', $blog_id );
			break;

		case 'default':
		default:
			do_action( 'preprocess_signup_form' );
			break;
	}
}

/**
 * Render the signup action.
 *
 * @since 7.0
 *
 * @param string $content The shortcode content.
 * @param string $action  The shortcode action.
 * @param array  $atts    The shortcode attributes.
 * @return string The signup content if $action is 'signup' or the original content otherwise.
 */
function tml_ms_filter_signup_shortcode( $content = '', $action = 'signup', $atts = array() ) {
	if ( 'signup' != $action ) {
		return $content;
	}

	$content = '';

	$active_signup = tml_ms_signup_get_active_signup();

	if ( current_user_can( 'manage_network' ) ) {
		$content .= '<div class="tml-messages">' . __( 'Greetings Network Administrator!' ) . ' ';
		switch ( $active_signup ) {
			case 'none':
				$content .= __( 'The network currently disallows registrations.' );
				break;

			case 'blog':
				$content .= __( 'The network currently allows site registrations.' );
				break;

			case 'user':
				$content .= __( 'The network currently allows user registrations.' );
				break;

			default:
				$content .= __( 'The network currently allows both site and user registrations.' );
				break;
		}
		$content .= ' ' . sprintf(
			__( 'To change or disable registration go to your <a href="%s">Options page</a>.' ),
			esc_url( network_admin_url( 'settings.php' ) )
		);
		$content .= '</div>';
	}

	$newblogname = isset( $_GET['new'] ) ? strtolower( preg_replace( '/^-|-$|[^-a-zA-Z0-9]/', '', $_GET['new'] ) ) : null;

	if ( $active_signup == 'none' ) {
		$content .= __( 'Registration has been disabled.' );
	} elseif ( $active_signup == 'blog' && ! is_user_logged_in() ) {
		$content .= sprintf(
			__( 'You must first <a href="%s">log in</a>, and then you can create a new site.' ),
			wp_login_url( network_site_url( 'wp-signup.php' ) )
		);
	} else {
		$stage = isset( $_POST['stage'] ) ? $_POST['stage'] : 'default';
		switch ( $stage ) {
			case 'validate-user-signup':
				if ( $active_signup == 'all' || $_POST['signup_for'] == 'blog' && $active_signup == 'blog' || $_POST['signup_for'] == 'user' && $active_signup == 'user' ) {
					$result = tml_get_data( 'signup_result' );
					$form   = tml_get_data( 'signup_form' );
					if ( 'user' == $form ) {
						$content .= tml_ms_get_user_signup_form( $result['user_name'], $result['user_email'], $result['errors'] );
					} elseif ( 'blog' == $form ) {
						$content .= tml_ms_get_blog_signup_form( $result['user_name'], $result['user_email'] );
					} else {
						$content .= '<h2>' . sprintf( __( '%s is your new username' ), tml_is_email_login_type() ? $result['user_email'] : $result['user_name'] ) . '</h2>';
						$content .= '<p>' . __( 'But, before you can start using your new username, <strong>you must activate it</strong>.' ) . '</p>';
						$content .= '<p>' . sprintf( __( 'Check your inbox at %s and click the link given.' ), '<strong>' . $result['user_email'] . '</strong>' ) . '</p>';
						$content .= '<p>' . __( 'If you do not activate your username within two days, you will have to sign up again.' ) . '</p>';
					}
				} else {
					$content .= __( 'User registration has been disabled.' );
				}
				break;

			case 'validate-blog-signup':
				if ( $active_signup == 'all' || $active_signup == 'blog' ) {
					$user_result = tml_get_data( 'signup_user_result' );
					$blog_result = tml_get_data( 'signup_result' );
					$form        = tml_get_data( 'signup_form' );
					if ( 'user' == $form ) {
						$content .= tml_ms_get_user_signup_form( $user_result['user_name'], $user_result['user_email'], $user_result['errors'] );
					} elseif ( 'blog' == $form ) {
						$content .= tml_ms_get_blog_signup_form( $user_result['user_name'], $user_result['user_email'], $blog_result['blogname'], $blog_result['blog_title'], $blog_result['errors'] );
					} else {
						$content .= '<h2>' . sprintf( __( 'Congratulations! Your new site, %s, is almost ready.' ), "<a href='http://{$blog_result['domain']}{$blog_result['path']}'>{$blog_result['blog_title']}</a>" ) . '</h2>';
						$content .= '<p>' . __( 'But, before you can start using your site, <strong>you must activate it</strong>.' ) . '</p>';
						$content .= '<p>' . sprintf( __( 'Check your inbox at %s and click the link given.' ), '<strong>' . $user_result['user_email'] . '</strong>' ) . '</p>';
						$content .= '<p>' . __( 'If you do not activate your site within two days, you will have to sign up again.' ) . '</p>';
						$content .= '<h2>' . __( 'Still waiting for your email?' ) . '</h2>';
						$content .= '<p>' .
								__( 'If you haven&#8217;t received your email yet, there are a number of things you can do:' ) . '
								<ul id="noemail-tips">
									<li><p><strong>' . __( 'Wait a little longer. Sometimes delivery of email can be delayed by processes outside of our control.' ) . '</strong></p></li>
									<li><p>' . __( 'Check the junk or spam folder of your email client. Sometime emails wind up there by mistake.' ) . '</p></li>
									<li>' . sprintf( __( 'Have you entered your email correctly? You have entered %s, if it&#8217;s incorrect, you will not receive your email.' ), $user_result['user_email'] ) . '</li>
								</ul>
							</p>';
					}
				} else {
					$content .= __( 'Site registration has been disabled.' );
				}
				break;

			case 'gimmeanotherblog':
				$result = tml_get_data( 'signup_result' );
				$form   = tml_get_data( 'signup_form' );
				if ( 'another_blog' == $form ) {
					$content .= tml_ms_get_another_blog_signup_form( $result['blogname'], $result['blog_title'], $result['errors'] );
				} else {
					$blog_id = tml_get_data( 'signup_blog_id' );
					if ( ! is_wp_error( $blog_id ) ) {
						if ( $blog_id ) {
							switch_to_blog( $blog_id );
							$home_url  = home_url( '/' );
							$login_url = wp_login_url();
							restore_current_blog();
						} else {
							$home_url  = 'http://' . $result['domain'] . $result['path'];
							$login_url = 'http://' . $result['domain'] . $result['path'] . 'wp-login.php';
						}

						$site = sprintf( '<a href="%1$s">%2$s</a>',
							esc_url( $home_url ),
							$result['blog_title']
						);

						$content .= '<h2>' . sprintf( __( 'The site %s is yours.' ), $site ) . '</h2>';
						$content .=  sprintf(
							__( '%1$s is your new site. <a href="%2$s">Log in</a> as &#8220;%3$s&#8221; using your existing password.' ),
							sprintf(
								'<a href="%s">%s</a>',
								esc_url( $home_url ),
								untrailingslashit( $result['domain'] . $result['path'] )
							),
							esc_url( $login_url ),
							wp_get_current_user()->user_login
						);
					}
				}
				break;

			case 'default':
			default:
				$user_email = isset( $_POST['user_email'] ) ? $_POST['user_email'] : '';

				do_action( 'preprocess_signup_form' );

				if ( is_user_logged_in() && ( $active_signup == 'all' || $active_signup == 'blog' ) ) {
					$content .= tml_ms_get_another_blog_signup_form( $newblogname );
				} elseif ( ! is_user_logged_in() && ( $active_signup == 'all' || $active_signup == 'user' ) ) {
					$content .= tml_ms_get_user_signup_form( $newblogname, $user_email );
				} elseif ( ! is_user_logged_in() && ( $active_signup == 'blog' ) ) {
					$content .= __( 'Sorry, new registrations are not allowed at this time.' );
				} else {
					$content .= __( 'You are logged in already. No need to register again!' );
				}

				if ( $newblogname ) {
					$newblog = get_blogaddress_by_name( $newblogname );

					if ( $active_signup == 'blog' || $active_signup == 'all' ) {
						$content .= sprintf(
							'<p><em>' . __( 'The site you were looking for, %s, does not exist, but you can create it now!' ) . '</em></p>',
							'<strong>' . $newblog . '</strong>'
						);
					} else {
						$content .= sprintf(
							'<p><em>' . __( 'The site you were looking for, %s, does not exist.' ) . '</em></p>',
							'<strong>' . $newblog . '</strong>'
						);
					}
				}
				break;
		}
	}

	return $content;
}

/**
 * Get signup form for a user.
 *
 * @since 7.0
 *
 * @param string   $user_name  The username.
 * @param string   $user_email The user's email.
 * @param WP_Error $errors     The WP_Error object.
 */
function tml_ms_get_user_signup_form( $user_name = '', $user_email = '', $errors = '' ) {
	if ( ! is_wp_error( $errors ) ) {
		$errors  = new WP_Error();
	}

	$signup_for = isset( $_POST['signup_for'] ) ? esc_html( $_POST['signup_for'] ) : 'blog';

	/** This filter is documented in wp-signup.php */
	$filtered_results = apply_filters( 'signup_user_init', compact( 'user_name', 'user_email', 'errors' ) );
	$user_name        = $filtered_results['user_name'];
	$user_email       = $filtered_results['user_email'];
	$errors           = $filtered_results['errors'];

	$form = tml_get_form( 'user_signup' );

	$fields = compact( 'user_name', 'user_email' );
	foreach ( $fields as $field => $value ) {
		if ( ! $field = $form->get_field( $field ) ) {
			continue;
		}

		if ( $error = $errors->get_error_message( $field->get_name() ) ) {
			$field->set_error( $error );
		} else {
			if ( null !== $value ) {
				$field->set_value( $value );
			}
		}
	}

	return $form->render( array(
		'before' => '<h2>' . sprintf(
			__( 'Get your own %s account in seconds' ),
			get_network()->site_name
		) . '</h2>',
	) );
}

/**
 * Get signup form for a blog.
 *
 * @since 7.0
 *
 * @param string   $user_name  The username.
 * @param string   $user_email The user's email.
 * @param string   $blogname   The blogname.
 * @param string   $blog_title The title of the site.
 * @param WP_Error $errors     The WP_Error object.
 * @return string The blog signup form.
 */
function tml_ms_get_blog_signup_form( $user_name = '', $user_email = '', $blogname = '', $blog_title = '', $errors = '' ) {
	if ( ! is_wp_error( $errors ) ) {
		$errors  = new WP_Error();
	}

	/** This filter is documented in wp-signup.php */
	$filtered_results = apply_filters( 'signup_blog_init', compact( 'user_name', 'user_email', 'blogname', 'blog_title', 'errors' ) );
	$user_name        = $filtered_results['user_name'];
	$user_email       = $filtered_results['user_email'];
	$blogname         = $filtered_results['blogname'];
	$blog_title       = $filtered_results['blog_title'];
	$errors           = $filtered_results['errors'];

	if ( tml_is_default_registration_type() && empty( $blogname ) ) {
		$blogname = $user_name;
	}

	$form = tml_get_form( 'blog_signup' );

	$fields = compact( 'user_name', 'user_email', 'blogname', 'blog_title' );
	foreach ( $fields as $field => $value ) {
		if ( ! $field = $form->get_field( $field ) ) {
			continue;
		}

		if ( $error = $errors->get_error_message( $field->get_name() ) ) {
			$field->set_error( $error );
		} else {
			if ( null !== $value ) {
				$field->set_value( $value );
			}
		}
	}

	return $form->render();
}

/**
 * Get the signup form for another blog.
 *
 * @since 7.0
 *
 * @param string   $blogname   The blogname.
 * @param string   $blog_title The title of the site.
 * @param WP_Error $errors     The WP_Error object.
 * @return string The blog signup form.
 */
function tml_ms_get_another_blog_signup_form( $blogname = '', $blog_title = '', $errors = '' ) {
	$current_user = wp_get_current_user();

	if ( ! is_wp_error( $errors ) ) {
		$errors = new WP_Error();
	}

	/** This filter is documented in wp-signup.php */
	$filtered_results = apply_filters( 'signup_another_blog_init', compact( 'blogname', 'blog_title', 'errors' ) );
	$blogname         = $filtered_results['blogname'];
	$blog_title       = $filtered_results['blog_title'];
	$errors           = $filtered_results['errors'];

	$before = '<h2>' . sprintf(
		__( 'Get <em>another</em> %s site in seconds' ),
		get_network()->site_name
	) . '</h2>';

	$before .= '<p>' . sprintf( __( 'Welcome back, %s. By filling out the form below, you can <strong>add another site to your account</strong>. There is no limit to the number of sites you can have, so create to your heart&#8217;s content, but write responsibly!' ), $current_user->display_name ) . '</p>';

	$blogs = get_blogs_of_user( $current_user->ID );
	if ( ! empty( $blogs ) ) {
		$before .= '<p>' . __( 'Sites you are already a member of:' ) . '</p><ul>';
		foreach ( $blogs as $blog ) {
			$home_url = get_home_url( $blog->userblog_id );
			$before .= '<li><a href="' . esc_url( $home_url ) . '">' . $home_url . '</a></li>';
		}
		$before .= '</ul>';
	}

	$before .= '<p>' . __( 'If you&#8217;re not going to use a great site domain, leave it for a new user. Now have at it!' ) . '</p>';

	$form = tml_get_form( 'another_blog_signup' );

	$fields = compact( 'blogname', 'blog_title' );
	foreach ( $fields as $field => $value ) {
		if ( ! $field = $form->get_field( $field ) ) {
			continue;
		}

		if ( $error = $errors->get_error_message( $field->get_name() ) ) {
			$field->set_error( $error );
		} else {
			if ( null !== $value ) {
				$field->set_value( $value );
			}
		}
	}

	return $form->render( compact( 'before' ) );
}

/**
 * Render the signup language field.
 *
 * @since 7.0
 *
 * @return string The signup language field.
 */
function tml_ms_render_blog_signup_language_field() {
	$markup = '';

	// Site Language.
	$languages = tml_ms_signup_get_available_languages();
	if ( ! empty( $languages ) ) {

		$lang = get_site_option( 'WPLANG' );

		if ( isset( $_POST['site_language'] ) ) {
			$lang = $_POST['site_language'];
		}

		// Use US English if the default isn't available.
		if ( ! in_array( $lang, $languages ) ) {
			$lang = '';
		}

		$markup .= wp_dropdown_languages( array(
			'echo'                        => false,
			'name'                        => 'site_language',
			'id'                          => 'site-language',
			'selected'                    => $lang,
			'languages'                   => $languages,
			'show_available_translations' => false,
		) );
	}

	return $markup;
}

/**
 * Get the languages available during the site/user signup process.
 *
 * @since 7.0
 *
 * @return array The available languages.
 */
function tml_ms_signup_get_available_languages() {
	/** This filter is documented in wp-signup.php */
	$languages = (array) apply_filters( 'signup_get_available_languages', get_available_languages() );

	/*
	 * Strip any non-installed languages and return.
	 *
	 * Re-call get_available_languages() here in case a language pack was installed
	 * in a callback hooked to the 'signup_get_available_languages' filter before this point.
	 */
	return array_intersect_assoc( $languages, get_available_languages() );
}

/**
 * Get the activate signup status.
 *
 * @since 7.0
 *
 * @return string The active signup status.
 */
function tml_ms_signup_get_active_signup() {
	/** This filter is documented in wp-signup.php */
	return apply_filters( 'wpmu_active_signup', get_site_option( 'registration', 'none' ) );
}

/**
 * Handle the activate action.
 *
 * @since 7.0
 */
function tml_ms_activation_handler() {
	global $wp_object_cache;

	define( 'WP_INSTALLING', true );

	if ( ! is_multisite() ) {
		wp_redirect( wp_registration_url() );
		exit;
	}

	if ( is_object( $wp_object_cache ) ) {
		$wp_object_cache->cache_enabled = false;
	}

	if ( ! empty( $_GET['key'] ) || ! empty( $_POST['key'] ) ) {
		$key = ! empty( $_GET['key'] ) ? $_GET['key'] : $_POST['key'];

		if ( tml_allow_user_passwords() ) {
			$errors = tml_validate_new_user_password();
			if ( $errors->get_error_code() ) {
				tml_set_data( 'activation_result', $errors );
				return;
			}
		}

		$result = wpmu_activate_signup( $key );

		if ( ! is_wp_error( $result ) ) {
			$activation_redirect = null;
			if ( tml_allow_user_passwords() ) {
				$activation_redirect = isset( $result['blog_id'] ) ? get_home_url( $result['blog_id'] ) : network_home_url();
			}

			/**
			 * Filters the URL to redirect to after activation.
			 *
			 * @since 7.0
			 *
			 * @param string $url     The URL ro redirect to after activation.
			 * @param int    $user_id The user ID.
			 * @param int    $blog_id The blog ID.
			 */
			$activation_redirect = apply_filters( 'tml_ms_activation_redirect', $activation_redirect, $result['user_id'], isset( $result['blog_id'] ) ? $result['blog_id'] : null );

			if ( ! empty( $activation_redirect ) ) {
				wp_safe_redirect( $activation_redirect );
				exit;
			}
		}

		tml_set_data( 'activation_result', $result );
	}
}

/**
 * Render the activate action.
 *
 * @since 7.0
 *
 * @param string $content The shortcode content.
 * @param string $action  The shortcode action.
 * @param array  $atts    The shortcode attributes.
 * @return string The signup content if $action is 'signup' or the original content otherwise.
 */
function tml_ms_filter_activation_shortcode( $content = '', $action = 'signup', $atts = array() ) {
	if ( 'activate' != $action ) {
		return $content;
	}

	$content = '';

	if ( empty( $_GET['key'] ) && empty( $_POST['key'] ) ) {
		$content .= tml_get_form( 'activate' )->render( array(
			'before' => '<h2>' . __( 'Activation Key Required' ) . '</h2>',
		) );
	} else {
		$result = tml_get_data( 'activation_result' );
		if ( is_wp_error( $result ) ) {
			if ( 'already_active' == $result->get_error_code() || 'blog_taken' == $result->get_error_code() ) {
				$signup = $result->get_error_data();

				$content .= '<h2>' . __( 'Your account is now active!' ) . '</h2>';
				$content .= '<p class="lead-in">';
				if ( $signup->domain . $signup->path == '' ) {
					$content .= sprintf(
						__( 'Your account has been activated. You may now <a href="%1$s">log in</a> to the site using your chosen username of &#8220;%2$s&#8221;. Please check your email inbox at %3$s for your password and login instructions. If you do not receive an email, please check your junk or spam folder. If you still do not receive an email within an hour, you can <a href="%4$s">reset your password</a>.' ),
						network_site_url( 'wp-login.php', 'login' ),
						$signup->user_login,
						$signup->user_email,
						wp_lostpassword_url()
					);
				} else {
					$content .= sprintf(
						__( 'Your site at %1$s is active. You may now log in to your site using your chosen username of &#8220;%2$s&#8221;. Please check your email inbox at %3$s for your password and login instructions. If you do not receive an email, please check your junk or spam folder. If you still do not receive an email within an hour, you can <a href="%4$s">reset your password</a>.' ),
						sprintf( '<a href="http://%1$s">%1$s</a>', $signup->domain ),
						$signup->user_login,
						$signup->user_email,
						wp_lostpassword_url()
					);
				}
				$content .= '</p>';
			} elseif ( tml_allow_user_passwords() && strpos( $result->get_error_code(), 'password' ) !== false ) {
				$form = tml_get_form( 'activate' );

				if ( isset( $_POST['user_pass1'] ) && isset( $_POST['user_pass2'] ) ) {
					$form->set_errors( $result );
				}

				$content .= $form->render( array(
					'before' => '<h2>' . __( 'New Password' ) . '</h2>',
				) );
			} else {
				$content .= '<h2>' . __( 'An error occurred during the activation' ) . '</h2>';
				$content .= '<p>' . $result->get_error_message() . '</p>';
			}
		} else {
			$url  = isset( $result['blog_id'] ) ? get_home_url( (int) $result['blog_id'] ) : '';
			$user = get_userdata( (int) $result['user_id'] );

			$content .= '<h2>' . __( 'Your account is now active!' ) . '</h2>';

			$content .= '<div id="signup-welcome">';
			$content .= '<p><span class="h3">' . __( 'Username:' ) . '</span> ' . ( tml_is_default_registration_type() ? $user->user_login : $user->user_email ) . '</p>';
			$content .= '<p><span class="h3">' . __( 'Password:' ) . '</span> ' . ( tml_allow_user_passwords() ? '****' : $result['password'] ) . '</p>';
			$content .= '</div>';

			if ( $url && $url != network_home_url( '', 'http' ) ) {
				switch_to_blog( (int) $result['blog_id'] );
				$login_url = wp_login_url();
				restore_current_blog();

				$content .= '<p class="view">';
				$content .= sprintf( __( 'Your account is now activated. <a href="%1$s">View your site</a> or <a href="%2$s">Log in</a>' ), $url, esc_url( $login_url ) );
				$content .= '</p>';
			} else {
				$content .= '<p class="view">';
				$content .= sprintf( __( 'Your account is now activated. <a href="%1$s">Log in</a> or go back to the <a href="%2$s">homepage</a>.' ), network_site_url( 'wp-login.php', 'login' ), network_home_url() );
				$content .= '</p>';
			}
		}
	}

	return $content;
}

/**
 * Filter the user data before it is inserter by wp_insert_user().
 *
 * @since 7.0
 *
 * @param array $data The user data to insert.
 * @return array The user data to insert.
 */
function tml_ms_filter_pre_insert_user_data( $data = array() ) {
	if ( tml_allow_user_passwords() && ! empty( $_POST['user_pass1'] ) ) {
		$data['user_pass'] = wp_hash_password( $_POST['user_pass1'] );
	}
	return $data;
}

/**
 * Filter the contents of the welcome email.
 *
 * @since 7.0
 *
 * @param string $message  The welcome email message.
 * @param int    $blog_id  The blog ID.
 * @param int    $user_id  The user ID.
 * @param string $password The user password.
 * @return string The welcome email message.
 */
function tml_ms_filter_welcome_email( $message, $blog_id, $user_id, $password ) {
	$user = get_userdata( $user_id );
	if ( tml_allow_user_passwords() && ! empty( $_POST['user_pass1'] ) ) {
		$message = str_replace( $password, $_POST['user_pass1'], $message );
	}
	if ( tml_is_email_login_type() ) {
		$message = str_replace( $user->user_login, $user->user_email, $message );
	}
	return $message;
}

/**
 * Filter the contents of the welcome user email.
 *
 * @since 7.0
 *
 * @param string $message  The welcome user message.
 * @param int    $user_id  The user ID.
 * @param string $password The user password.
 * @return string The welcome user message.
 */
function tml_ms_filter_welcome_user_email( $message, $user_id, $password ) {
	$user = get_userdata( $user_id );
	if ( tml_allow_user_passwords() && ! empty( $_POST['user_pass1'] ) ) {
		$message = str_replace( 'PASSWORD', $_POST['user_pass1'], $message );
	}
	if ( tml_is_email_login_type() ) {
		$message = str_replace( 'USERNAME', $user->user_email, $message );
	}
	return $message;
}
