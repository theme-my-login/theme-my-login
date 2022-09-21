<?php

/**
 * Theme My Login Action Functions
 *
 * @package Theme_My_Login
 * @subpackage Actions
 */

/**
 * Register default actions.
 *
 * @since 7.0
 */
function tml_register_default_actions() {

	// Dashboard
	tml_register_action( 'dashboard', array(
		'title'              => __( 'Dashboard' ),
		'slug'               => 'dashboard',
		'callback'           => 'tml_dashboard_handler',
		'show_on_forms'      => false,
		'show_nav_menu_item' => is_user_logged_in(),
	) );

	// Login
	tml_register_action( 'login', array(
		'title'              => isset( $_GET['checkemail'] ) ? __( 'Check your email' ) : __( 'Log In' ),
		'slug'               => 'login',
		'callback'           => 'tml_login_handler',
		'ajax_callback'      => 'tml_login_handler',
		'show_on_forms'      => __( 'Log in' ),
		'show_nav_menu_item' => ! is_user_logged_in(),
	) );

	// Logout
	tml_register_action( 'logout', array(
		'title'              => __( 'Log Out' ),
		'slug'               => 'logout',
		'callback'           => 'tml_logout_handler',
		'show_on_forms'      => false,
		'show_in_widget'     => false,
		'show_nav_menu_item' => is_user_logged_in(),
	) );

	// Register
	tml_register_action( 'register', array(
		'title'              => __( 'Register' ),
		'slug'               => 'register',
		'callback'           => 'tml_registration_handler',
		'ajax_callback'      => 'tml_registration_handler',
		'show_on_forms'      => (bool) get_option( 'users_can_register' ),
		'show_nav_menu_item' => ! is_user_logged_in(),
	) );

	// Lost Password
	tml_register_action( 'lostpassword', array(
		'title'             => __( 'Lost Password' ),
		'slug'              => 'lostpassword',
		'callback'          => 'tml_lost_password_handler',
		'ajax_callback'     => 'tml_lost_password_handler',
		'network'           => true,
		'show_on_forms'     => __( 'Lost your password?' ),
		'show_in_nav_menus' => false,
	) );

	// Reset Password
	tml_register_action( 'resetpass', array(
		'title'             => __( 'Reset Password' ),
		'slug'              => 'resetpass',
		'callback'          => 'tml_password_reset_handler',
		'network'           => true,
		'show_on_forms'     => false,
		'show_in_widget'    => false,
		'show_in_nav_menus' => false,
	) );

	// Confirm Action (Data Requests)
	tml_register_action( 'confirmaction', array(
		'title'                 => __( 'Your Data Request', 'theme-my-login' ),
		'slug'                  => 'confirmaction',
		'callback'              => 'tml_confirmaction_handler',
		'show_on_forms'         => false,
		'show_in_widget'        => false,
		'show_in_nav_menus'     => false,
		'show_in_slug_settings' => false,
	) );
}

/**
 * Register an action.
 *
 * @since 7.0
 *
 * @see Theme_My_Login::register_action()
 *
 * @param string|Theme_My_Login_Action $action The action name or object.
 * @param array                        $args   Optional. An array of arguments for registering an action.
 * @return Theme_My_Login_Action The action object.
 */
function tml_register_action( $action, $args = array() ) {

	if ( ! $action instanceof Theme_My_Login_Action ) {
		$action = new Theme_My_Login_Action( $action, $args );
	}

	if ( $slug = get_site_option( 'tml_' . $action->get_name() . '_slug' ) ) {
		$action->set_slug( $slug );
	}

	return theme_my_login()->register_action( $action );
}

/**
 * Unregister an action.
 *
 * @since 7.0
 *
 * @param string|Theme_My_Login_Action $action The action name or object.
 */
function tml_unregister_action( $action ) {
	theme_my_login()->unregister_action( $action );
}

/**
 * Get an action.
 *
 * @since 7.0
 *
 * @param string|Theme_My_Login_Action $action Optional. The action name or object.
 * @return Theme_My_Login_Action|bool The action object if it exists or false otherwise.
 */
function tml_get_action( $action = '' ) {
	global $wp;

	if ( $action instanceof Theme_My_Login_Action ) {
		return $action;
	}

	if ( empty( $action ) ) {
		if ( ! empty( $wp->query_vars['action'] ) ) {
			$action = $wp->query_vars['action'];
		}
	}

	return theme_my_login()->get_action( $action );
}

/**
 * Get all actions.
 *
 * @since 7.0
 *
 * @return array The actions.
 */
function tml_get_actions() {
	return theme_my_login()->get_actions();
}

/**
 * Determine if an action exists.
 *
 * @since 7.0
 *
 * @param string $action The action name.
 * @return bool True if the action exists or false otherwise.
 */
function tml_action_exists( $action ) {
	$exists = array_key_exists( $action, tml_get_actions() );

	/**
	 * Filter whether an action exists or not.
	 *
	 * @since 7.0
	 *
	 * @param bool   $exists Whether the action exists or not.
	 * @param string $action The action name.
	 */
	return apply_filters( 'tml_action_exists', $exists, $action );
}

/**
 * Determine if a TML action is being requested.
 *
 * @since 7.0
 *
 * @param string $action Optional. The action to check.
 * @return bool Is a TML action being requested?
 */
function tml_is_action( $action = '' ) {
	$is_action      = false;
	$current_action = tml_get_action();

	if ( $current_action && array_key_exists( $current_action->get_name(), tml_get_actions() ) ) {
		if ( empty( $action ) ) {
			$is_action = true;
		} elseif ( $action == $current_action->get_name() ) {
			$is_action = true;
		}
	}

	/**
	 * Filter whether a TML action is being requested or not.
	 *
	 * @since 7.0
	 *
	 * @param bool   $is_action Whether a TML action is being requested or not.
	 * @param string $action    The action name.
	 */
	return apply_filters( 'tml_is_action', $is_action, $action );
}

/**
 * Get the action title.
 *
 * @since 7.0
 *
 * @param string|Theme_My_Login_Action $action The action name or object.
 * @return string The action title.
 */
function tml_get_action_title( $action = '' ) {
	if ( ! $action = tml_get_action( $action ) ) {
		return;
	}
	return $action->get_title();
}

/**
 * Get the action slug.
 *
 * @since 7.0
 *
 * @param string|Theme_My_Login_Action $action The action name or object.
 * @return string The action slug.
 */
function tml_get_action_slug( $action = '' ) {
	if ( ! $action = tml_get_action( $action ) ) {
		return;
	}
	return $action->get_slug();
}

/**
 * Get the action URL.
 *
 * @since 7.0
 *
 * @param string|Theme_My_Login_Action $action  The action name or object.
 * @param string                       $scheme  The URL scheme.
 * @param bool                         $network Whether to retrieve the URL for the current network or current blog.
 * @return string The action URL.
 */
function tml_get_action_url( $action = '', $scheme = 'login', $network = null ) {
	if ( ! $action = tml_get_action( $action ) ) {
		return;
	}
	return $action->get_url( $scheme, $network );
}

/**
 * Determine if an action has an associated page.
 *
 * @since 7.0.1
 *
 * @param string|Theme_My_Login_Action $action The action name of object.
 * @return bool|WP_Post The page object if one is found, false otherwise.
 */
function tml_action_has_page( $action = '' ) {

	if ( ! $action = tml_get_action( $action ) ) {
		return false;
	}

	$pages = wp_cache_get( 'tml_pages' );
	if ( false === $pages ) {
		$pages = get_posts( [
			'post_name__in'          => array_map( 'tml_get_action_slug', tml_get_actions() ),
			'post_type'              => 'page',
			'nopaging'               => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
		] );
		wp_cache_set( 'tml_pages', $pages );
	}

	if ( $pages = wp_list_filter( $pages, [ 'post_name' => $action->get_slug() ] ) ) {
		return reset( $pages );
	}

	return false;
}

/**
 * Handle an action.
 *
 * @since 7.0
 */
function tml_action_handler() {
	if ( ! tml_is_action() ) {
		return;
	}

	// Redirect to https login if forced to use SSL
	if ( force_ssl_admin() && ! is_ssl() ) {
		if ( 0 === strpos($_SERVER['REQUEST_URI'], 'http') ) {
			wp_safe_redirect( set_url_scheme( $_SERVER['REQUEST_URI'], 'https' ) );
			exit();
		} else {
			wp_safe_redirect( 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] );
			exit();
		}
	}

	// Redirect other actions from the login action
	if ( $action = tml_get_request_value( 'action' ) ) {
		if ( tml_is_action( 'login' ) && 'login' != $action ) {
			// Fix some alias actions
			if ( 'retrievepassword' == $action ) {
				$action = 'lostpassword';
			} elseif ( 'rp' == $action ) {
				$action = 'resetpass';
			}

			// Get the action URL
			if ( $url = tml_get_action_url( $action ) ) {
				// Add the query string to the URL
				if ( $query = parse_url( $_SERVER['REQUEST_URI'], PHP_URL_QUERY ) ) {
					parse_str( $query, $args );
					unset( $args['action'] );
					$url = add_query_arg( array_map( 'rawurlencode', $args ), $url );
				}
				wp_redirect( $url );
				exit;
			}
		}
	}

	nocache_headers();

	// Set a test cookie to test if cookies are enabled
	$secure = ( 'https' === parse_url( wp_login_url(), PHP_URL_SCHEME ) );
	setcookie( TEST_COOKIE, 'WP Cookie check', 0, COOKIEPATH, COOKIE_DOMAIN, $secure );
	if ( SITECOOKIEPATH != COOKIEPATH ) {
		setcookie( TEST_COOKIE, 'WP Cookie check', 0, SITECOOKIEPATH, COOKIE_DOMAIN, $secure );
	}

	// Add the testcookie field to the login form
	tml_add_form_field( 'login', 'testcookie', array(
		'type'     => 'hidden',
		'value'    => 1,
		'priority' => 30,
	) );

	/** This action is documented in wp-login.php */
	do_action( 'login_init' );

	/** This action is documented in wp-login.php */
	do_action( 'login_form_' . tml_get_action()->get_name() );

	if ( tml_is_ajax_request() ) {
		/**
		 * Fires when a TML action is being requested.
		 *
		 * @since 7.1
		 */
		do_action( 'tml_action_ajax_' . tml_get_action()->get_name() );
	} else {
		/**
		 * Fires when a TML action is being requested.
		 *
		 * @since 7.0.3
		 */
		do_action( 'tml_action_' . tml_get_action()->get_name() );
	}
}

/**
 * Handle the 'dashboard' action.
 *
 * @since 7.1
 */
function tml_dashboard_handler() {
	if ( ! is_user_logged_in() ) {
		wp_redirect( wp_login_url( $_SERVER['REQUEST_URI'] ) );
		exit;
	}
}

/**
 * Handle the 'login' action.
 *
 * @since 7.0
 */
function tml_login_handler() {

	$errors = new WP_Error;

	$secure_cookie = '';

	// If the user wants ssl but the session is not ssl, force a secure cookie.
	if ( ! empty( $_POST['log'] ) && ! force_ssl_admin() ) {
		$user_name = sanitize_user( wp_unslash( $_POST['log'] ) );
		$user      = get_user_by( 'login', $user_name );

		if ( ! $user && strpos( $user_name, '@' ) ) {
			$user = get_user_by( 'email', $user_name );
		}

		if ( $user ) {
			if ( get_user_option( 'use_ssl', $user->ID ) ) {
				$secure_cookie = true;
				force_ssl_admin( true );
			}
		}
	}

	if ( ! empty( $_REQUEST['redirect_to'] ) ) {
		$redirect_to = $_REQUEST['redirect_to'];
		// Redirect to https if user wants ssl
		if ( $secure_cookie && false !== strpos( $redirect_to, 'wp-admin' ) ) {
			$redirect_to = preg_replace( '|^http://|', 'https://', $redirect_to );
		}
	} else {
		$redirect_to = admin_url();
	}

	$reauth = empty( $_REQUEST['reauth'] ) ? false : true;

	$user = wp_signon( array(), $secure_cookie );

	if ( empty( $_COOKIE[ LOGGED_IN_COOKIE ] ) ) {
		if ( headers_sent() ) {
			$user = new WP_Error( 'test_cookie', sprintf(
					__( '<strong>Error</strong>: Cookies are blocked due to unexpected output. For help, please see <a href="%1$s">this documentation</a> or try the <a href="%2$s">support forums</a>.' ),
					__( 'https://wordpress.org/support/article/cookies/' ),
					__( 'https://wordpress.org/support/forums/' )
				)
			);
		} elseif ( isset( $_POST['testcookie'] ) && empty( $_COOKIE[ TEST_COOKIE ] ) ) {
			// If cookies are disabled we can't log in even with a valid user+pass
			$user = new WP_Error( 'test_cookie', sprintf(
					__( '<strong>Error</strong>: Cookies are blocked or not supported by your browser. You must <a href="%s">enable cookies</a> to use WordPress.' ),
					__( 'https://wordpress.org/support/article/cookies#enable-cookies-your-browser' )
				)
			);
		}
	}
	/** This filter is documented in wp-login.php */
	$redirect_to = apply_filters( 'login_redirect', $redirect_to, isset( $_REQUEST['redirect_to'] ) ? $_REQUEST['redirect_to'] : '', $user );

	if ( ! is_wp_error( $user ) && ! $reauth ) {

		if ( ( empty( $redirect_to ) || 'wp-admin/' == $redirect_to || admin_url() == $redirect_to ) ) {

			// If the user doesn't belong to a blog, send them to user admin. If the user can't edit posts, send them to their profile.
			if ( is_multisite() && ! get_active_blog_for_user( $user->ID ) && ! is_super_admin( $user->ID ) ) {
				$redirect_to = user_admin_url();

			} elseif ( is_multisite() && ! $user->has_cap( 'read' ) ) {
				$redirect_to = get_dashboard_url( $user->ID );

			} elseif ( ! $user->has_cap( 'edit_posts' ) ) {
				if ( tml_action_exists( 'dashboard' ) ) {
					$redirect_to = tml_get_action_url( 'dashboard' );
				} else {
					$redirect_to = $user->has_cap( 'read' ) ? admin_url( 'profile.php' ) : home_url();
				}
			}

			if ( tml_is_ajax_request() ) {
				tml_send_ajax_success( array(
					'redirect' => $redirect_to,
				) );
			} else {
				wp_redirect( $redirect_to );
				exit;
			}
		}

		if ( tml_is_ajax_request() ) {
			tml_send_ajax_success( array(
				'redirect' => tml_validate_redirect( $redirect_to ),
			) );
		} else {
			wp_safe_redirect( $redirect_to );
			exit;
		}
	} else {
		$errors = $user;
	}

	// Clear errors if loggedout is set.
	if ( ! empty( $_GET['loggedout'] ) || $reauth ) {
		$errors = new WP_Error;
	}

	if ( empty( $_POST ) && $errors->get_error_codes() === array( 'empty_username', 'empty_password' ) ) {
		$errors = new WP_Error;
	}

	// Some parts of this script use the main login form to display a message
	if ( isset( $_GET['loggedout'] ) && true == $_GET['loggedout'] ) {
		$errors->add( 'loggedout', __( 'You are now logged out.' ), 'message' );

	} elseif ( isset( $_GET['registration'] ) && 'disabled' == $_GET['registration'] ) {
		$errors->add( 'registerdisabled', __( '<strong>Error</strong>: User registration is currently not allowed.' ) );

	} elseif ( isset( $_GET['checkemail'] ) && 'confirm' == $_GET['checkemail'] ) {
		$errors->add( 'confirm', sprintf( __( 'Check your email for the confirmation link, then visit the <a href="%s">login page</a>.' ), wp_login_url() ) , 'message' );

	} elseif ( isset( $_GET['checkemail'] ) && 'registered' == $_GET['checkemail'] ) {
		if ( tml_allow_user_passwords() ) {
			$errors->add( 'registered', __( 'Registration complete. You may now log in.', 'theme-my-login' ), 'message' );
		} else {
			$errors->add( 'registered', sprintf( __( 'Registration complete. Please check your email, then visit the <a href="%s">login page</a>.' ), wp_login_url() ), 'message' );
		}

	} elseif ( isset( $_GET['resetpass'] ) && 'complete' == $_GET['resetpass'] ) {
		$errors->add( 'password_reset', __( 'Your password has been reset.' ), 'message' );

	} elseif ( strpos( $redirect_to, 'about.php?updated' ) ) {
		$errors->add( 'updated', __( '<strong>You have successfully updated WordPress!</strong> Please log back in to see what&#8217;s new.' ), 'message' );
	}

	/** This filter is documented in wp-login.php */
	$errors = apply_filters( 'wp_login_errors', $errors, $redirect_to );

	tml_set_errors( $errors );

	// Clear any stale cookies.
	if ( $reauth ) {
		wp_clear_auth_cookie();
	}

	if ( tml_is_ajax_request() ) {
		tml_send_ajax_error( array(
			'errors' => tml_get_form()->render_errors(),
		) );
	}
}

/**
 * Handle the 'logout' action.
 *
 * @since 7.0
 */
function tml_logout_handler() {

	check_admin_referer( 'log-out' );

	$user = wp_get_current_user();

	wp_logout();

	if ( ! empty( $_REQUEST['redirect_to'] ) ) {
		$redirect_to           = $_REQUEST['redirect_to'];
		$requested_redirect_to = $redirect_to;
	} else {
		$redirect_to           = add_query_arg(
			array(
				'loggedout' => 'true',
				'wp_lang'   => get_user_locale( $user ),
			),
			wp_login_url()
		);
		$requested_redirect_to = '';
	}

	/** This filter is documented in wp-login.php */
	$redirect_to = apply_filters( 'logout_redirect', $redirect_to, $requested_redirect_to, $user );

	wp_safe_redirect( $redirect_to );
	exit;
}

/**
 * Handle the 'register' action.
 *
 * @since 7.0
 */
function tml_registration_handler() {

	if ( is_multisite() ) {
		/** This filter is documented in wp-login.php */
		$signup_location = apply_filters( 'wp_signup_location', network_site_url( 'wp-signup.php' ) );

		wp_redirect( $signup_location );
		exit;
	}

	if ( ! get_option( 'users_can_register' ) ) {
		if ( tml_is_ajax_request() ) {
			tml_add_error( 'registerdisabled', __( '<strong>Error</strong>: User registration is currently not allowed.' ) );
			tml_send_ajax_error( array(
				'errors' => tml_get_form()->render_errors(),
			) );
		} else {
			wp_redirect( site_url( 'wp-login.php?registration=disabled' ) );
			exit;
		}
	}

	if ( tml_is_post_request() ) {
		$user_login = tml_get_request_value( 'user_login', 'post' );
		$user_email = tml_get_request_value( 'user_email', 'post' );
		$user_id = register_new_user( $user_login, $user_email );
		if ( ! is_wp_error( $user_id ) ) {
			$redirect_to = ! empty( $_POST['redirect_to'] ) ? $_POST['redirect_to'] : site_url( 'wp-login.php?checkemail=registered' );

			/**
			 * Filter the registration redirect.
			 *
			 * @since 7.0.10
			 *
			 * @param string $redirect_to The registration redirect.
			 * @param WP_User $user       The user object.
			 */
			$redirect_to = apply_filters( 'tml_registration_redirect', $redirect_to, get_userdata( $user_id ) );

			if ( tml_is_ajax_request() ) {
				wp_send_json_success( array(
					'redirect' => tml_validate_redirect( $redirect_to ),
				) );
			} else {
				wp_safe_redirect( $redirect_to );
				exit;
			}
		} else {
			tml_set_errors( $user_id );
			if ( tml_is_ajax_request() ) {
				tml_send_ajax_error( array(
					'errors' => tml_get_form()->render_errors(),
				) );
			}
		}
	}
}

/**
 * Handle the 'lostpassword' action.
 *
 * @since 7.0
 */
function tml_lost_password_handler() {

	if ( tml_is_post_request() ) {
		$errors = retrieve_password();
		if ( ! is_wp_error( $errors ) ) {
			if ( tml_is_ajax_request() ) {
				tml_add_error( 'confirm', sprintf( __( 'Check your email for the confirmation link, then visit the <a href="%s">login page</a>.' ), wp_login_url() ), 'message' );
				tml_send_ajax_success( array(
					'notice' => tml_get_form()->render_errors(),
				) );
			} else {
				$redirect_to = ! empty( $_REQUEST['redirect_to'] ) ? $_REQUEST['redirect_to'] : site_url( 'wp-login.php?checkemail=confirm' );
				wp_safe_redirect( $redirect_to );
				exit;
			}
		} else {
			tml_set_errors( $errors );
			if ( tml_is_ajax_request() ) {
				tml_send_ajax_error( array(
					'errors' => tml_get_form()->render_errors(),
				) );
			}
		}
	}

	if ( isset( $_REQUEST['error'] ) ) {
		if ( 'invalidkey' == $_REQUEST['error'] ) {
			tml_add_error( 'invalidkey', __( '<strong>Error</strong>: Your password reset link appears to be invalid. Please request a new link below.' ) );
		} elseif ( 'expiredkey' == $_REQUEST['error'] ) {
			tml_add_error( 'expiredkey', __( '<strong>Error</strong>: Your password reset link has expired. Please request a new link below.' ) );
		}
	}

	/** This filter is documented in wp-login.php */
	do_action( 'lost_password', tml_get_errors() );
}

/**
 * Handle the 'resetpass' action.
 *
 * @since 7.0
 */
function tml_password_reset_handler() {

	list( $rp_path ) = explode( '?', wp_unslash( $_SERVER['REQUEST_URI'] ) );
	$rp_cookie = 'wp-resetpass-' . COOKIEHASH;

	if ( isset( $_GET['key'] ) ) {
		$value = sprintf( '%s:%s', wp_unslash( $_GET['login'] ), wp_unslash( $_GET['key'] ) );
		setcookie( $rp_cookie, $value, 0, $rp_path, COOKIE_DOMAIN, is_ssl(), true );
		wp_safe_redirect( remove_query_arg( array( 'key', 'login' ) ) );
		exit;
	}

	if ( isset( $_COOKIE[ $rp_cookie ] ) && 0 < strpos( $_COOKIE[ $rp_cookie ], ':' ) ) {
		list( $rp_login, $rp_key ) = explode( ':', wp_unslash( $_COOKIE[ $rp_cookie ] ), 2 );
		$user = check_password_reset_key( $rp_key, $rp_login );
		if ( isset( $_POST['pass1'] ) && ! hash_equals( $rp_key, $_POST['rp_key'] ) ) {
			$user = false;
		}
	} else {
		$user = false;
	}

	if ( ! $user || is_wp_error( $user ) ) {
		setcookie( $rp_cookie, ' ', time() - YEAR_IN_SECONDS, $rp_path, COOKIE_DOMAIN, is_ssl(), true );
		if ( $user && $user->get_error_code() === 'expired_key' ) {
			wp_redirect( site_url( 'wp-login.php?action=lostpassword&error=expiredkey' ) );
		} else {
			wp_redirect( site_url( 'wp-login.php?action=lostpassword&error=invalidkey' ) );
		}
		exit;
	}

	$errors = new WP_Error;

	if ( isset( $_POST['pass1'] ) && $_POST['pass1'] != $_POST['pass2'] ) {
		$errors->add( 'password_reset_mismatch', __( '<strong>Error</strong>: The passwords do not match.' ) );
	}

	/** This action is documented in wp-login.php */
	do_action( 'validate_password_reset', $errors, $user );

	if ( ( ! $errors->get_error_code() ) && isset( $_POST['pass1'] ) && ! empty( $_POST['pass1'] ) ) {
		reset_password( $user, $_POST['pass1'] );
		setcookie( $rp_cookie, ' ', time() - YEAR_IN_SECONDS, $rp_path, COOKIE_DOMAIN, is_ssl(), true );
		wp_redirect( site_url( 'wp-login.php?resetpass=complete' ) );
		exit;
	} else {
		tml_set_errors( $errors );
	}
}

/**
 * Handle the 'confirmaction' action.
 *
 * @since 7.0
 */
function tml_confirmaction_handler() {
	if ( ! isset( $_GET['request_id'] ) ) {
		wp_die( __( 'Missing request ID.' ) );
	}

	if ( ! isset( $_GET['confirm_key'] ) ) {
		wp_die( __( 'Missing confirm key.' ) );
	}

	$request_id = (int) $_GET['request_id'];
	$key        = sanitize_text_field( wp_unslash( $_GET['confirm_key'] ) );
	$result     = wp_validate_user_request_key( $request_id, $key );

	if ( is_wp_error( $result ) ) {
		wp_die( $result );
	}

	/** This action is documented in wp-login.php */
	do_action( 'user_request_action_confirmed', $request_id );
}
