<?php

/**
 * Theme My Login Functions
 *
 * @package Theme_My_Login
 * @subpackage Functions
 */

/**
 * Get the Theme My Login instance.
 *
 * @since 7.0
 *
 * @return Theme_My_Login
 */
function theme_my_login() {
	return Theme_My_Login::get_instance();
}

/**
 * Parse the request.
 *
 * @since 7.0.10
 *
 * @param $wp WP The WordPress object.
 */
function tml_parse_request( $wp ) {

	if ( ! isset( $wp->query_vars['action'] ) ) {
		return;
	}

	$action = $wp->query_vars['action'];

	// Fix some alias actions
	if ( 'retrievepassword' == $action ) {
		$action = 'lostpassword';
	} elseif ( 'rp' == $action ) {
		$action = 'resetpass';
	}

	// Ensure that the permalink action is passed
	if ( ! empty( $wp->did_permalink ) && false === strpos( $wp->matched_query, "action=$action" ) ) {

		// Get the action from the matched query
		preg_match( '/action=([^&]+)/', $wp->matched_query, $matches );
		if ( ! empty( $matches ) ) {
			$action = $matches[1];
		}
	}

	// Bail if not a TML action
	if ( ! tml_action_exists( $action ) ) {
		return;
	}

	// Set the proper action
	if ( $action != $wp->query_vars['action'] ) {
		$wp->set_query_var( 'action', $action );
	}
}

/**
 * Parse the query.
 *
 * @since 7.0
 *
 * @param $wp_query WP_Query The query object.
 */
function tml_parse_query( $wp_query ) {

	// Bail if not in the main loop
	if ( ! $wp_query->is_main_query() ) {
		return;
	}

	// Bail if not home
	if ( ! $wp_query->is_home || $wp_query->is_posts_page ) {
		return;
	}

	// Bail if not handling a TML action
	if ( ! tml_is_action() ) {
		return;
	}

	// Tell WordPress that this is a page
	$wp_query->is_tml_action = true;
	$wp_query->is_page       = true;
	$wp_query->is_singular   = true;
	$wp_query->is_single     = false;
	$wp_query->is_home       = false;

	// No need to calculate found rows
	$wp_query->set( 'no_found_rows', true );

	// Matter of fact, no need to query anything
	$wp_query->set( 'post__in', array( 0 ) );
}

/**
 * Add TML data to the query.
 *
 * @since 7.0
 *
 * @param array    $posts    The posts.
 * @param WP_Query $wp_query The query object.
 * @return array The posts.
 */
function tml_the_posts( $posts, $wp_query ) {

	// Bail if not in the main loop
	if ( ! $wp_query->is_main_query() ) {
		return $posts;
	}

	// Bail if not a TML action
	if ( ! $wp_query->is_tml_action ) {
		return $posts;
	}

	if ( ! $post = tml_action_has_page() ) {
		// Fake a post
		$post = array(
			'ID'             => 0,
			'post_type'      => 'page',
			'post_content'   => sprintf( '[theme-my-login action="%s"]', tml_get_action()->get_name() ),
			'post_title'     => tml_get_action_title(),
			'post_name'      => tml_get_action_slug(),
			'ping_status'    => 'closed',
			'comment_status' => 'closed',
			'filter'         => 'raw',
		);
		$post = new WP_Post( (object) $post );
	}

	return array( $post );
}

/**
 * Remove undesired actions and filters on TML pages.
 *
 * @since 7.0
 */
function tml_remove_default_actions_and_filters() {

	if ( ! tml_is_action() ) {
		return;
	}

	// Remove actions
	remove_action( 'wp_head', 'feed_links',                       2 );
	remove_action( 'wp_head', 'feed_links_extra',                 3 );
	remove_action( 'wp_head', 'rsd_link',                        10 );
	remove_action( 'wp_head', 'wlwmanifest_link',                10 );
	remove_action( 'wp_head', 'parent_post_rel_link',            10 );
	remove_action( 'wp_head', 'start_post_rel_link',             10 );
	remove_action( 'wp_head', 'adjacent_posts_rel_link_wp_head', 10 );
	remove_action( 'wp_head', 'rel_canonical',                   10 );

	// Remove filters
	remove_filter( 'template_redirect', 'redirect_canonical' );
}

/**
 * Get the page template for a TML action.
 *
 * @since 7.0
 *
 * @param string $template The page template path.
 * @return string The page template path.
 */
function tml_page_template( $template = 'page.php' ) {
	if ( ! $action = tml_get_action() ) {
		return $template;
	}

	$slug = $action->get_name();

	$templates = array(
		"$slug.php",
		"theme-my-login-$slug.php",
		"tml-$slug.php",
		"page-$slug.php",
		'theme-my-login.php',
		'tml.php',
		'page.php',
	);

	if ( tml_action_has_page() ) {
		$template = get_page_template_slug();
		if ( $template && 0 === validate_file( $template ) ) {
			array_unshift( $templates, $template );
		}
	}

	/**
	 * Filters the template hierarchy used for TML pages.
	 *
	 * @since 7.0
	 *
	 * @param array $templates The page template hierarchy.
	 */
	$templates = apply_filters( 'tml_page_templates', $templates );

	if ( $tml_template = locate_template( $templates ) ) {
		return $tml_template;
	}

	return $template;
}

/**
 * Add body classes to TML actions.
 *
 * @since 7.0
 *
 * @param array $classes The body classes.
 * @return array The body classes.
 */
function tml_body_class( $classes ) {
	if ( $action = tml_get_action() ) {
		$classes[] = 'tml-action';
		$classes[] = 'tml-action-' . $action->get_name();
	}
	return $classes;
}

/**
 * Enqueue styles for TML.
 *
 * @since 7.0
 */
function tml_enqueue_styles() {
	$suffix = SCRIPT_DEBUG ? '' : '.min';

	wp_enqueue_style( 'theme-my-login', THEME_MY_LOGIN_URL . "assets/styles/theme-my-login$suffix.css", array(), THEME_MY_LOGIN_VERSION );
}

/**
 * Enqueue scripts for TML.
 *
 * @since 7.0
 *
 */
function tml_enqueue_scripts() {
	$suffix = SCRIPT_DEBUG ? '' : '.min';

	$dependencies = array( 'jquery' );
	if ( tml_is_action( 'resetpass' ) || ( tml_is_action( 'register' ) && tml_allow_user_passwords() ) ) {
		$dependencies[] = 'password-strength-meter';
	}

	/**
	 * Filter the dependencies of the main TML script.
	 *
	 * @since 7.0.15
	 *
	 * @param array $dependencies The dependencies of the main TML script.
	 */
	$dependencies = apply_filters( 'tml_script_dependencies', $dependencies );

	wp_enqueue_script( 'theme-my-login', THEME_MY_LOGIN_URL . "assets/scripts/theme-my-login$suffix.js", $dependencies, THEME_MY_LOGIN_VERSION, true );

	/**
	 * Filter the main TML script data.
	 *
	 * @since 7.0.15
	 *
	 * @param array $data The main TML script data.
	 */
	$data = apply_filters( 'tml_script_data', array(
		'action' => tml_is_action() ? tml_get_action()->get_name() : '',
		'errors' => tml_get_errors()->get_error_codes(),
	) );

	wp_localize_script( 'theme-my-login', 'themeMyLogin', $data );

	if ( tml_is_action() ) {
		/** This action is documented in wp-login.php */
		do_action( 'login_enqueue_scripts' );
	}
}

/**
 * Do the `login_head` action hook.
 *
 * @since 7.0.13
 */
function tml_do_login_head() {
	global $wp_version;

	if ( ! tml_is_action() ) {
		return;
	}

	// This is already attached to "wp_head"
	remove_action( 'login_head', 'wp_print_head_scripts', 9 );

	// Don't index TML actions
	if ( version_compare( $wp_version, '5.7', '>=' ) ) {
		add_filter( 'wp_robots', 'wp_robots_sensitive_page' );
		add_action( 'login_head', 'wp_strict_cross_origin_referrer' );
	} else {
		add_action( 'login_head', 'wp_sensitive_page_meta' );
	}

	/** This action is documented in wp-login.php */
	do_action( 'login_head' );
}

/**
 * Add rewrite tags for TML.
 *
 * @since 7.0
 */
function tml_add_rewrite_tags() {
	add_rewrite_tag( '%action%', '([^/]+)' );
}

/**
 * Add rewrite rules for TML.
 *
 * @since 7.0
 */
function tml_add_rewrite_rules() {
	if ( ! tml_use_permalinks() ) {
		return;
	}
	foreach ( tml_get_actions() as $action ) {
		$url = 'index.php?action=' . $action->get_name();
		if ( $page = tml_action_has_page( $action ) ) {
			$url .= '&pagename=' . get_page_uri( $page );
		}
		add_rewrite_rule( tml_get_action_slug( $action ) . '/?$', $url, 'top' );
	}
}

/**
 * Flushes rewrite rules so that they will be rebuilt on the next page load.
 *
 * @since 7.0
 */
function tml_flush_rewrite_rules() {
	update_option( 'rewrite_rules', '' );
}

/**
 * Filter the site URL.
 *
 * @since 7.0
 *
 * @see site_url()
 * @see network_site_url()
 *
 * @param string $url    The URL.
 * @param string $path   The path.
 * @param string $scheme The URL scheme.
 * @return string The filtered URL.
 */
function tml_filter_site_url( $url, $path, $scheme ) {
	global $pagenow;

	// Bail if currently visiting wp-login.php
	if ( 'wp-login.php' == $pagenow ) {
		return $url;
	}

	// Bail if currently customizing
	if ( is_customize_preview() ) {
		return $url;
	}

	// Parse the URL
	$parsed_url = parse_url( $url );

	// Determine the path
	$path = '';
	if ( ! empty( $parsed_url['path'] ) ) {
		$path = basename( trim( $parsed_url['path'], '/' ) );
	}

	// Parse the query
	$query = array();
	if ( ! empty( $parsed_url['query'] ) ) {
		parse_str( $parsed_url['query'], $query );

		// Encode the query args
		$query = tml_array_map_recursive( 'rawurlencode', $query );
	}

	/**
	 * Bail if the URL is an interim-login URL
	 *
	 * @see https://core.trac.wordpress.org/ticket/31821
	 */
	if ( isset( $query['interim-login'] ) ) {
		return $url;
	}

	// Determine the action
	switch ( $path ) {
		case 'wp-login.php' :
			// Determine the action
			$action = isset( $query['action'] ) ? $query['action'] : 'login';

			// Fix some alias actions
			if ( 'retrievepassword' == $action ) {
				$action = 'lostpassword';
			} elseif ( 'rp' == $action ) {
				$action = 'resetpass';
			}

			// Unset the action
			unset( $query['action'] );
			break;

		case 'wp-signup.php' :
			$action = 'signup';
			break;

		case 'wp-activate.php' :
			$action = 'activate';
			break;

		default :
			return $url;
	}

	// Bail if not a TML action
	if ( ! tml_action_exists( $action ) ) {
		return $url;
	}

	// Get the URL
	$url = tml_get_action_url( $action, $scheme, 'network_site_url' == current_filter() );

	// Add the query
	$url = add_query_arg( $query, $url );

	return $url;
}

/**
 * Filter the logout URL.
 *
 * @since 7.0
 *
 * @see wp_logout_url()
 *
 * @param string $url      The URL.
 * @param string $redirect The redirect.
 * @return string The logout URL.
 */
function tml_filter_logout_url( $url, $redirect ) {

	// Bail if logout action doesn't exist for some reason
	if ( ! tml_action_exists( 'logout' ) ) {
		return $url;
	}

	// Get the logout URL
	$url = tml_get_action_url( 'logout' );

	// Add the redirect query argument if needed
	if ( ! empty( $redirect ) ) {
		$url = add_query_arg( 'redirect_to', urlencode( $redirect ), $url );
	}

	// Add the nonce
	$url = wp_nonce_url( $url, 'log-out' );

	return $url;
}

/**
 * Filter the lost password URL.
 *
 * @since 7.0.11
 *
 * @see wp_lostpassword_url()
 *
 * @param string $url      The URL.
 * @param string $redirect The redirect.
 * @return string The lostpassword URL.
 */
function tml_filter_lostpassword_url( $url, $redirect ) {
	global $pagenow;

	// Bail if currently visiting wp-login.php
	if ( 'wp-login.php' == $pagenow ) {
		return $url;
	}

	// Bail if logout action doesn't exist for some reason
	if ( ! tml_action_exists( 'lostpassword' ) ) {
		return $url;
	}

	// Get the lostpassword URL
	$url = tml_get_action_url( 'lostpassword' );

	// Add the redirect query argument if needed
	if ( ! empty( $redirect ) ) {
		$url = add_query_arg( 'redirect_to', urlencode( $redirect ), $url );
	}

	return $url;
}

/**
 * Filter the post edit link.
 *
 * @since 7.0
 *
 * @see get_edit_post_link()
 *
 * @param string $link    The edit post link.
 * @param int    $post_id The post ID.
 * @return string The edit post link.
 */
function tml_filter_get_edit_post_link( $link, $post_id ) {
	if ( tml_is_action() && 0 === $post_id ) {
		$link = '';
	}
	return $link;
}

/**
 * Filter the comments.
 *
 * @since 7.0.10
 *
 * @param array $comments The comments.
 * @return array The comments.
 */
function tml_filter_comments_array( $comments ) {
	if ( tml_is_action() ) {
		return array();
	}
	return $comments;
}

/**
 * Add TML action type to the Customizer menu editor.
 *
 * @since 7.0
 *
 * @param array $item_types The available item types.
 * @return array The available item types.
 */
function tml_filter_customize_nav_menu_available_item_types( $item_types ) {
	$item_types[] = array(
		'title'      => __( 'Theme My Login Actions', 'theme-my-login' ),
		'type_label' => __( 'Theme My Login Action',  'theme-my-login' ),
		'type'       => 'tml_action',
		'object'     => 'tml_action',
	);
	return $item_types;
}

/**
 * Add TML actions to the Customizer menu editor.
 *
 * @since 7.0
 *
 * @param array  $items  The avaialble items.
 * @param string $type   The item type.
 * @param string $object The object.
 * @param int    $page   The current page.
 * @return array The available items.
 */
function tml_filter_customize_nav_menu_available_items( $items, $type, $object, $page ) {
	if ( 'tml_action' == $type && 0 == $page ) {
		foreach ( tml_get_actions() as $action ) {
			if ( ! $action->show_in_nav_menus ) {
				continue;
			}
			$items[] = array(
				'id'         => 'tml_action-' . $action->get_name(),
				'title'      => $action->get_title(),
				'type'       => 'tml_action',
				'type_label' => __( 'TML Action', 'theme-my-login' ),
				'object'     => $action->get_name(),
				'object_id'  => $action->get_name(),
				'url'        => $action->get_url(),
			);
		}
	}
	return $items;
}

/**
 * Set up TML action nav menu items.
 *
 * @since 7.0
 *
 * @param object $menu_item The menu item object.
 * @return object The menu item object.
 */
function tml_setup_nav_menu_item( $menu_item ) {
	// Item to be added
	if ( $menu_item instanceof Theme_My_Login_Action ) {
		$menu_item = (object) array(
			'ID'               => 0,
			'db_id'            => 0,
			'menu_item_parent' => 0,
			'post_parent'      => 0,
			'type'             => 'tml_action',
			'type_label'       => __( 'TML Action', 'theme-my-login' ),
			'object'           => $menu_item->get_name(),
			'object_id'        => -1, // Needed for AJAX save to work
			'title'            => $menu_item->get_title(),
			'url'              => $menu_item->get_url(),
			'target'           => '',
			'attr_title'       => '',
			'description'      => '',
			'classes'          => array(),
			'xfn'              => '',
		);

	// Existing menu item
	} elseif ( 'tml_action' == $menu_item->type ) {
		$menu_item->object_id  = $menu_item->ID;
		$menu_item->type_label = __( 'TML Action', 'theme-my-login' );
		if ( ! $action = tml_get_action( $menu_item->object ) ) {
			$menu_item->_invalid = true;
		} else {
			$menu_item->url = $action->get_url();
			if ( 'logout' == $menu_item->object ) {
				$menu_item->url = wp_nonce_url( $menu_item->url, 'log-out' );
			}
			if ( ! is_admin() && ! $action->show_nav_menu_item ) {
				$menu_item->_invalid = true;
			}
		}

	// Legacy menu item
	} elseif ( 'page' == $menu_item->object ) {
		$slug = get_post_field( 'post_name', $menu_item->object_id );
		if ( $action = tml_get_action( $slug ) ) {
			if ( 'logout' == $action->get_name() ) {
				$menu_item->url = wp_nonce_url( $action->get_url(), 'log-out' );
			}
			if ( ! is_admin() && ! $action->show_nav_menu_item ) {
				$menu_item->_invalid = true;
			}
		}
	}

	return $menu_item;
}

/**
 * Add classes to TML action nav menu items.
 *
 * @since 7.0
 *
 * @param array   $classes The nav menu item classes.
 * @param WP_Post $item    The nav menu item.
 * @return array The nav menu item classes.
 */
function tml_nav_menu_css_class( $classes, $item ) {
	if ( 'tml_action' == $item->type ) {
		if ( tml_is_action( $item->object ) ) {
			$classes[] = 'current-menu-item';
			$classes[] = 'current_page_item';
		}
	}
	return $classes;
}

/**
 * Validate the password of a new user.
 *
 * @since 7.0
 *
 * @param WP_Error $errors The registration errors.
 * @return WP_Error The registration errors.
 */
function tml_validate_new_user_password( $errors = null ) {

	if ( empty( $errors ) ) {
		$errors = new WP_Error();
	}

	if ( tml_allow_user_passwords() ) {
		if ( empty( $_POST['user_pass1'] ) || empty( $_POST['user_pass2'] ) ) {
			$errors->add( 'empty_password', __( '<strong>Error</strong>: Please enter a password.', 'theme-my-login' ) );

		} elseif ( false !== strpos( stripslashes( $_POST['user_pass1'] ), "\\" ) ) {
			$errors->add( 'password_backslash', __( '<strong>Error</strong>: Passwords may not contain the character "\\".', 'theme-my-login' ) );

		} elseif ( $_POST['user_pass1'] !== $_POST['user_pass2'] ) {
			$errors->add( 'password_mismatch', __( '<strong>Error</strong>: Passwords don&#8217;t match. Please enter the same password in both password fields.', 'theme-my-login' ) );
		}
	}

	return $errors;
}

/**
 * Filter the new user notification email.
 *
 * @since 7.0
 *
 * @param array $email The new user notification email parameters.
 * @return array The new user notification email parameters.
 */
function tml_add_password_notice_to_new_user_notification_email( $email ) {
	if ( tml_allow_user_passwords() ) {
		$email['message'] .= "\r\n" . __( 'If you have already set your own password, you may disregard this email and use the password you have already set.', 'theme-my-login' );
	}
	return $email;
}

/**
 * Filter the user login before saving.
 *
 * @since 7.0
 *
 * @param string $sanitized_user_login The sanitized user login.
 * @return string The sanitized user login.
 */
function tml_set_user_login( $sanitized_user_login ) {
	if ( tml_is_email_registration_type() && tml_is_post_request() ) {
		if ( isset( $_POST['user_login'] ) && sanitize_user( $_POST['user_login'] ) == $sanitized_user_login && isset( $_POST['user_email'] ) ) {
			$sanitized_user_login = sanitize_user( $_POST['user_email'] );
		}
	}
	return $sanitized_user_login;
}

/**
 * Filter the registration redirect when auto-login is enabled.
 *
 * @since 7.0.1
 *
 * @param string  $url  The registration redirect URL.
 * @param WP_User $user The user object.
 * @return string The registration redirect URL.
 */
function tml_registration_redirect( $url, $user ) {
	if ( tml_allow_auto_login() ) {
		$url = apply_filters( 'login_redirect', home_url(), tml_get_request_value( 'redirect_to' ), $user );
	}
	return $url;
}

/**
 * Buffer an action hook call so that it's content can be returned.
 *
 * @since 7.0
 *
 * @return string The result of the action call.
 */
function tml_buffer_action_hook() {
	$args = func_get_args();
	if ( ! is_string( $args[0] ) ) {
		return;
	}
	ob_start();
	call_user_func_array( 'do_action', $args );
	return ob_get_clean();
}

/**
 * Get the username label.
 *
 * @since 7.0
 *
 * @param string $action Optional. The action.
 * @return string The username label.
 */
function tml_get_username_label( $action = '' ) {

	if ( empty( $action ) ) {
		$action = tml_get_action();
	}

	switch ( $action ) {
		case 'register':
			$label = __( 'Username' );
			break;

		case 'lostpassword':
		case 'login':
		default:
			if ( tml_is_username_login_type() ) {
				$label = __( 'Username' );
			} elseif ( tml_is_email_login_type() ) {
				$label = __( 'Email' );
			} else {
				$label = __( 'Username or Email Address' );
			}
	}

	return apply_filters( 'tml_get_username_label', $label, $action );
}

/**
 * Enforce the login type.
 *
 * @since 7.0
 *
 * @param null|WP_Error|WP_User $user
 * @param string                $username
 * @param string                $password
 * @return null|WP_User|WP_Error
 */
function tml_enforce_login_type( $user, $username, $password ) {
	if ( tml_is_email_login_type() && null == $user ) {
		return new WP_Error( 'invalid_email', __( '<strong>Error</strong>: Invalid email address.' ) );
	}
	return $user;
}

/**
 * Set the password of a new user.
 *
 * @since 7.0
 *
 * @param int $user_id The user ID.
 */
function tml_set_new_user_password( $user_id ) {
	if ( ! tml_allow_user_passwords() ) {
		return;
	}

	if ( ! $password = tml_get_request_value( 'user_pass1', 'post' ) ) {
		return;
	}

	wp_set_password( $password, $user_id );
	update_user_option( $user_id, 'default_password_nag', false, true );
}

/**
 * Handle auto-login after registration.
 *
 * @since 7.0
 *
 * @param int $user_id The user ID.
 */
function tml_handle_auto_login( $user_id ) {
	if ( ! tml_allow_auto_login() ) {
		return;
	}

	if ( 'wpmu_activate_blog' == current_filter() ) {
		$user_id = func_get_arg( 1 );
	}

	wp_set_auth_cookie( $user_id );
}

/**
 * Send the new user notifications.
 *
 * @since 7.0.7
 */
function tml_send_new_user_notifications( $user_id, $notify = 'both' ) {

	/**
	 * Filters whether to send the new user notification or not.
	 *
	 * @since 7.0.7
	 *
	 * @param bool $send_user_notification Whether to send the new user notification or not.
	 */
	$send_user_notification = (bool) apply_filters( 'tml_send_new_user_notification', true );

	/**
	 * Filters whether to send the new user admin notification or not.
	 *
	 * @since 7.0.7
	 *
	 * @param bool $send_admin_notification Whether to send the new user admin notification or not.
	 */
	$send_admin_notification = (bool) apply_filters( 'tml_send_new_user_admin_notification', true );

	// Bail if both are disabled
	if ( ! ( $send_user_notification || $send_admin_notification ) ) {
		return;
	}

	// Set to both if empty
	if ( empty( $notify ) ) {
		$notify = 'admin';

	// Set to admin if set to both and user is disabled
	} elseif ( 'both' == $notify ) {
		if ( ! $send_user_notification ) {
			$notify = 'admin';
		} elseif ( ! $send_admin_notification ) {
			$notify = 'user';
		}
	}

	// Bail if type is admin and it is disabled
	if ( 'admin' == $notify && ! $send_admin_notification ) {
		return;

	// Bail if type is user and it is disabled
	} elseif ( 'user' == $notify && ! $send_user_notification ) {
		return;
	}

	wp_new_user_notification( $user_id, null, $notify );
}

/**
 * Add an error.
 *
 * @since 7.0
 *
 * @param string $code     The error code.
 * @param string $message  The error message.
 * @param string $severity The error severity.
 */
function tml_add_error( $code, $message, $data = '' ) {
	if ( ! $form = tml_get_form() ) {
		return;
	}
	$form->add_error( $code, $message, $data );
}

/**
 * Get the errors.
 *
 * @since 7.0
 *
 * @return WP_Error
 */
function tml_get_errors() {
	if ( ! $form = tml_get_form() ) {
		return new WP_Error;
	}
	return $form->get_errors();
}

/**
 * Set the errors.
 *
 * @since 7.0
 *
 * @param WP_Error $errors The errors.
 */
function tml_set_errors( WP_Error $errors ) {
	if ( ! $form = tml_get_form() ) {
		return;
	}
	$form->set_errors( $errors );
}

/**
 * Determine if there are errors.
 *
 * @since 7.0
 *
 * @return bool
 */
function tml_has_errors() {
	if ( ! $form = tml_get_form() ) {
		return false;
	}
	return $form->has_errors();
}

/**
 * Get arbitrary data.
 *
 * @since 7.0
 *
 * @param string $name The property name.
 * @param mixed  $default The value to return if the property is not set.
 * @return mixed The property value or $default if not set.
 */
function tml_get_data( $name, $default = false ) {
	return theme_my_login()->get_data( $name, $default );
}

/**
 * Set arbitrary data.
 *
 * @since 7.0
 *
 * @param string|array $name  The property name or an array of properties.
 * @param mixed        $value The property value.
 */
function tml_set_data( $name, $value = '' ) {
	theme_my_login()->set_data( $name, $value );
}

/**
 * Get a value from the request.
 *
 * @since 7.0
 *
 * @param string $key  The key of the value to retrieve.
 * @param string $type Optional. The request type. Can be either 'get',
 *                     'post' or 'any'. Default 'any'.
 * @return mixed The requested value.
 */
function tml_get_request_value( $key, $type = 'any' ) {
	$type  = strtoupper( $type );
	if ( 'POST' == $type && array_key_exists( $key, $_POST ) ) {
		$value = $_POST[ $key ];
	} elseif ( 'GET' == $type && array_key_exists( $key, $_GET ) ) {
		$value = $_GET[ $key ];
	} elseif ( array_key_exists( $key, $_REQUEST ) ) {
		$value = $_REQUEST[ $key ];
	} else {
		$value = '';
	}

	if ( is_string( $value ) ) {
		$value = wp_unslash( $value );
	}

	return $value;
}

/**
 * Determine if the current request is a wp-login.php request.
 *
 * @since 7.0
 *
 * @return bool
 */
function tml_is_wp_login() {
	global $pagenow;

	return ( 'wp-login.php' == $pagenow );
}

/**
 * Determine if the current request is a GET request.
 *
 * @since 7.0
 *
 * @return bool
 */
function tml_is_get_request() {
	return 'GET' === strtoupper( $_SERVER['REQUEST_METHOD'] );
}

/**
 * Determine if the current request is a POST request.
 *
 * @since 7.0
 *
 * @return bool
 */
function tml_is_post_request() {
	return 'POST' === strtoupper( $_SERVER['REQUEST_METHOD'] );
}

/**
 * Determine if the current request is an AJAX request.
 *
 * @since 7.1
 *
 * @return bool
 */
function tml_is_ajax_request() {
	return (bool) tml_get_request_value( 'ajax' );
}

/**
 * Send an AJAX success response.
 *
 * @since 7.1
 *
 * @param mixed $data Data to send with the response.
 */
function tml_send_ajax_success( $data = null ) {
	/**
	 * Filters the AJAX success data.
	 *
	 * @since 7.1
	 *
	 * @param mixed $data The AJAX success data.
	 */
	$data = apply_filters( 'tml_ajax_success_data', $data );

	wp_send_json_success( $data );
}

/**
 * Send an AJAX error response.
 *
 * @since 7.1
 *
 * @param mixed $data Data to send with the response.
 */
function tml_send_ajax_error( $data = null ) {
	/**
	 * Filters the AJAX error data.
	 *
	 * @since 7.1
	 *
	 * @param mixed $data The AJAX error data.
	 */
	$data = apply_filters( 'tml_ajax_error_data', $data );

	wp_send_json_error( $data );
}

/**
 * Validate a URL for redirection.
 *
 * @since 7.1
 *
 * @param string $url The URL to validate.
 * @return string The validated URL.
 */
function tml_validate_redirect( $url ) {
	return wp_validate_redirect( wp_sanitize_redirect( $url ),
		/** This filter is documented in wp-includes/pluggable.php */
		apply_filters( 'wp_safe_redirect_fallback', admin_url(), 302 )
	);
}

/**
 * Map a user defined callback to an array recursively.
 *
 * @since 7.0.16
 *
 * @param string $callback The name of the callback.
 * @param array  $array    The array to be mapped.
 * @return array The resulting array.
 */
function tml_array_map_recursive( $callback, $array ) {
	foreach ( $array as $key => $value ) {
		if ( is_array( $value ) ) {
			$array[ $key ] = tml_array_map_recursive( $callback, $value );
		} else {
			$array[ $key ] = call_user_func( $callback, $value );
		}
	}
	return $array;
}
