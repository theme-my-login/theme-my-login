<?php
/**
 * Holds the Theme My Login class
 *
 * @package Theme_My_Login
 * @since 6.0
 */

if ( ! class_exists( 'Theme_My_Login' ) ) :
/*
 * Theme My Login class
 *
 * This class contains properties and methods common to the front-end.
 *
 * @since 6.0
 */
class Theme_My_Login extends Theme_My_Login_Abstract {
	/**
	 * Holds plugin version
	 *
	 * @since 6.3.2
	 * @const string
	 */
	const VERSION = '6.4.9';

	/**
	 * Holds options key
	 *
	 * @since 6.3
	 * @access protected
	 * @var string
	 */
	protected $options_key = 'theme_my_login';

	/**
	 * Holds errors object
	 *
	 * @since 6.0
	 * @access public
	 * @var object
	 */
	public $errors;

	/**
	 * Holds current page being requested
	 *
	 * @since 6.3
	 * @access public
	 * @var string
	 */
	public $request_page;

	/**
	 * Holds current action being requested
	 *
	 * @since 6.0
	 * @access public
	 * @var string
	 */
	public $request_action;

	/**
	 * Holds current instance being requested
	 *
	 * @since 6.0
	 * @access public
	 * @var int
	 */
	public $request_instance = 0;

	/**
	 * Holds loaded instances
	 *
	 * @since 6.3
	 * @access protected
	 * @var array
	 */
	protected $loaded_instances = array();

	/**
	 * Returns singleton instance
	 *
	 * @since 6.3
	 * @access public
	 * @return object
	 */
	public static function get_object( $class = null ) {
		return parent::get_object( __CLASS__ );
	}

	/**
	 * Returns default options
	 *
	 * @since 6.3
	 * @access public
	 *
	 * @return array Default options
	 */
	public static function default_options() {
		return apply_filters( 'tml_default_options', array(
			'enable_css'     => true,
			'login_type'     => 'default',
			'active_modules' => array()
		) );
	}

	/**
	 * Returns default pages
	 *
	 * @since 6.3
	 * @access public
	 *
	 * @return array Default pages
	 */
	public static function default_pages() {
		return apply_filters( 'tml_default_pages', array(
			'login'        => __( 'Log In'        , 'theme-my-login' ),
			'logout'       => __( 'Log Out'       , 'theme-my-login' ),
			'register'     => __( 'Register'      , 'theme-my-login' ),
			'lostpassword' => __( 'Lost Password' , 'theme-my-login' ),
			'resetpass'    => __( 'Reset Password', 'theme-my-login' )
		) );
	}

	/**
	 * Loads the plugin
	 *
	 * @since 6.0
	 * @access public
	 */
	protected function load() {

		$this->request_action = isset( $_REQUEST['action'] ) ? sanitize_key( $_REQUEST['action'] ) : '';
		$this->request_instance = isset( $_REQUEST['instance'] ) ? (int) $_REQUEST['instance'] : 0;

		$this->load_instance();

		add_action( 'plugins_loaded',          array( $this, 'plugins_loaded'          ) );
		add_action( 'init',                    array( $this, 'init'                    ) );
		add_action( 'load_textdomain',         array( $this, 'load_custom_textdomain'   ), 10, 2 );
		add_action( 'widgets_init',            array( $this, 'widgets_init'            ) );
		add_action( 'wp',                      array( $this, 'wp'                      ) );
		add_action( 'pre_get_posts',           array( $this, 'pre_get_posts'           ) );
		add_action( 'template_redirect',       array( $this, 'template_redirect'       ) );
		add_action( 'wp_enqueue_scripts',      array( $this, 'wp_enqueue_scripts'      ) );
		add_action( 'wp_head',                 array( $this, 'wp_head'                 ) );
		add_action( 'wp_footer',               array( $this, 'wp_footer'               ) );
		add_action( 'wp_print_footer_scripts', array( $this, 'wp_print_footer_scripts' ) );

		add_filter( 'site_url',               array( $this, 'site_url'               ), 10, 3 );
		add_filter( 'logout_url',             array( $this, 'logout_url'             ), 10, 2 );
		add_filter( 'single_post_title',      array( $this, 'single_post_title'      )        );
		add_filter( 'the_title',              array( $this, 'the_title'              ), 10, 2 );
		add_filter( 'wp_setup_nav_menu_item', array( $this, 'wp_setup_nav_menu_item' )        );
		add_filter( 'wp_list_pages_excludes', array( $this, 'wp_list_pages_excludes' )        );
		add_filter( 'page_link',              array( $this, 'page_link'              ), 10, 2 );
		add_filter( 'authenticate',           array( $this, 'authenticate'           ), 20, 3 );

		add_shortcode( 'theme-my-login', array( $this, 'shortcode' ) );

		if ( 'username' == $this->get_option( 'login_type' ) ) {
			remove_filter( 'authenticate', 'wp_authenticate_email_password', 20 );
		} elseif ( 'email' == $this->get_option( 'login_type' ) ) {
			remove_filter( 'authenticate', 'wp_authenticate_username_password', 20 );
		}
	}


	/************************************************************************************************************************
	 * Actions
	 ************************************************************************************************************************/

	/**
	 * Loads active modules
	 *
	 * @since 6.3
	 * @access public
	 */
	public function plugins_loaded() {
		foreach ( $this->get_option( 'active_modules', array() ) as $module ) {
			if ( file_exists( THEME_MY_LOGIN_PATH . '/modules/' . $module ) )
				include_once( THEME_MY_LOGIN_PATH . '/modules/' . $module );
		}
		do_action_ref_array( 'tml_modules_loaded', array( &$this ) );
	}

	/**
	 * Initializes the plugin
	 *
	 * @since 6.0
	 * @access public
	 */
	public function init() {
		global $pagenow;

		load_plugin_textdomain( 'theme-my-login', false, plugin_basename( THEME_MY_LOGIN_PATH ) . '/languages' );

		$this->errors = new WP_Error();

		if ( ! is_admin() && 'wp-login.php' != $pagenow && $this->get_option( 'enable_css' ) )
			wp_enqueue_style( 'theme-my-login', self::get_stylesheet(), false, $this->get_option( 'version' ) );
	}

	/**
	 * Registers the widget
	 *
	 * @since 6.0
	 * @access public
	 */
	public function widgets_init() {
		if ( class_exists( 'Theme_My_Login_Widget' ) )
			register_widget( 'Theme_My_Login_Widget' );
	}

	/**
	 * Used to add/remove filters from login page
	 *
	 * @since 6.1.1
	 * @access public
	 */
	public function wp() {
		if ( self::is_tml_page() ) {
			do_action( 'login_init' );

			remove_action( 'wp_head', 'feed_links',                       2 );
			remove_action( 'wp_head', 'feed_links_extra',                 3 );
			remove_action( 'wp_head', 'rsd_link'                            );
			remove_action( 'wp_head', 'wlwmanifest_link'                    );
			remove_action( 'wp_head', 'parent_post_rel_link',            10 );
			remove_action( 'wp_head', 'start_post_rel_link',             10 );
			remove_action( 'wp_head', 'adjacent_posts_rel_link_wp_head', 10 );
			remove_action( 'wp_head', 'rel_canonical'                       );

			// Don't index any of these forms
			add_action( 'login_head', 'wp_no_robots' );

			if ( force_ssl_admin() && ! is_ssl() ) {
				if ( 0 === strpos( $_SERVER['REQUEST_URI'], 'http' ) ) {
					wp_redirect( set_url_scheme( $_SERVER['REQUEST_URI'], 'https' ) );
					exit;
				} else {
					wp_redirect( 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] );
					exit;
				}
			}

			nocache_headers();
		}
	}

	/**
	 * Exclude TML pages from search
	 *
	 * @since 6.1.13
	 * @access public
	 */
	public function pre_get_posts( $query ) {

		// Bail if in admin area
		if ( is_admin() )
			return;

		// Bail if not the main query
		if ( ! $query->is_main_query() )
			return;

		// Bail if not a search
		if ( ! $query->is_search )
			return;

		// Get the requested post type
		$post_type = $query->get( 'post_type' );

		// Bail if not querying pages
		if ( ! empty( $post_type ) && ! in_array( 'page', (array) $post_type ) )
			return;

		// Get TML pages
		$pages = get_posts( array(
			'post_type'      => 'page',
			'post_status'    => 'any',
			'meta_key'       => '_tml_action',
			'posts_per_page' => -1
		) );

		// Get the page IDs
		$pages = wp_list_pluck( $pages, 'ID' );

		// Get any currently exclude posts
		$excludes = (array) $query->get( 'post__not_in' );

		// Merge the excludes
		$excludes = array_merge( $excludes, $pages );

		// Set the excludes
		$query->set( 'post__not_in', $excludes );
	}

	/**
	 * Proccesses the request
	 *
	 * Callback for "template_redirect" hook in template-loader.php
	 *
	 * @since 6.3
	 * @access public
	 */
	public function template_redirect() {
		if ( ! $this->request_action && self::is_tml_page() )
			$this->request_action = self::get_page_action( get_the_id() );

		do_action_ref_array( 'tml_request', array( &$this ) );

		// allow plugins to override the default actions, and to add extra actions if they want
		do_action( 'login_form_' . $this->request_action );

		if ( has_action( 'tml_request_' . $this->request_action ) ) {
			do_action_ref_array( 'tml_request_' . $this->request_action, array( &$this ) );
		} else {
			$http_post = ( 'POST' == $_SERVER['REQUEST_METHOD'] );
			switch ( $this->request_action ) {
				case 'postpass' :
					if ( ! array_key_exists( 'post_password', $_POST ) ) {
						wp_safe_redirect( wp_get_referer() );
						exit();
					}

					require_once( ABSPATH . 'wp-includes/class-phpass.php' );
					$hasher = new PasswordHash( 8, true );

					$expire = apply_filters( 'post_password_expires', time() + 10 * DAY_IN_SECONDS );
					if ( $referer ) {
						$secure = ( 'https' === parse_url( $referer, PHP_URL_SCHEME ) );
					} else {
						$secure = false;
					}
					setcookie( 'wp-postpass_' . COOKIEHASH, $hasher->HashPassword( wp_unslash( $_POST['post_password'] ) ), $expire, COOKIEPATH, COOKIE_DOMAIN, $secure );

					wp_safe_redirect( wp_get_referer() );
					exit;

					break;
				case 'logout' :
					check_admin_referer( 'log-out' );

					$user = wp_get_current_user();

					wp_logout();

					if ( ! empty( $_REQUEST['redirect_to'] ) ) {
						$redirect_to = $requested_redirect_to = $_REQUEST['redirect_to'];
					} else {
						$redirect_to = site_url( 'wp-login.php?loggedout=true' );
						$requested_redirect_to = '';
					}

					$redirect_to = apply_filters( 'logout_redirect', $redirect_to, $requested_redirect_to, $user );
					wp_safe_redirect( $redirect_to );
					exit;
					break;
				case 'lostpassword' :
				case 'retrievepassword' :
					if ( $http_post ) {
						$this->errors = self::retrieve_password();
						if ( ! is_wp_error( $this->errors ) ) {
							$redirect_to = ! empty( $_REQUEST['redirect_to'] ) ? $_REQUEST['redirect_to'] : site_url( 'wp-login.php?checkemail=confirm' );
							wp_safe_redirect( $redirect_to );
							exit;
						}
					}

					if ( isset( $_REQUEST['error'] ) ) {
						if ( 'invalidkey' == $_REQUEST['error'] )
							$this->errors->add( 'invalidkey', __( 'Your password reset link appears to be invalid. Please request a new link below.', 'theme-my-login' ) );
						elseif ( 'expiredkey' == $_REQUEST['error'] )
							$this->errors->add( 'expiredkey', __( 'Your password reset link has expired. Please request a new link below.', 'theme-my-login' ) );
					}

					do_action( 'lost_password' );
					break;
				case 'resetpass' :
				case 'rp' :
					// Dirty hack for now
					global $rp_login, $rp_key;

					$rp_cookie = 'wp-resetpass-' . COOKIEHASH;
					if ( isset( $_GET['key'] ) ) {
						$value = sprintf( '%s:%s', wp_unslash( $_GET['login'] ), wp_unslash( $_GET['key'] ) );
						setcookie( $rp_cookie, $value, 0, '/', COOKIE_DOMAIN, is_ssl(), true );
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
						setcookie( $rp_cookie, ' ', time() - YEAR_IN_SECONDS, '/', COOKIE_DOMAIN, is_ssl(), true );
						if ( $user && $user->get_error_code() === 'expired_key' )
							wp_redirect( site_url( 'wp-login.php?action=lostpassword&error=expiredkey' ) );
						else
							wp_redirect( site_url( 'wp-login.php?action=lostpassword&error=invalidkey' ) );
						exit;
					}

					if ( isset( $_POST['pass1'] ) && $_POST['pass1'] != $_POST['pass2'] )
						$this->errors->add( 'password_reset_mismatch', __( 'The passwords do not match.', 'theme-my-login' ) );

					do_action( 'validate_password_reset', $this->errors, $user );

					if ( ( ! $this->errors->get_error_code() ) && isset( $_POST['pass1'] ) && ! empty( $_POST['pass1'] ) ) {
						reset_password( $user, $_POST['pass1'] );
						setcookie( $rp_cookie, ' ', time() - YEAR_IN_SECONDS, '/', COOKIE_DOMAIN, is_ssl(), true );
						$redirect_to = site_url( 'wp-login.php?resetpass=complete' );
						wp_safe_redirect( $redirect_to );
						exit;
					}

					wp_enqueue_script( 'utils' );
					wp_enqueue_script( 'user-profile' );
					break;
				case 'register' :
					if ( ! get_option( 'users_can_register' ) ) {
						$redirect_to = site_url( 'wp-login.php?registration=disabled' );
						wp_redirect( $redirect_to );
						exit;
					}

					$user_login = '';
					$user_email = '';
					if ( $http_post ) {
						if ( 'email' == $this->get_option( 'login_type' ) ) {
							$user_login = isset( $_POST['user_email'] ) ? $_POST['user_email'] : '';
						} else {
							$user_login = isset( $_POST['user_login'] ) ? $_POST['user_login'] : '';
						}
						$user_email = isset( $_POST['user_email'] ) ? $_POST['user_email'] : '';

						$this->errors = register_new_user( $user_login, $user_email );
						if ( ! is_wp_error( $this->errors ) ) {
							$redirect_to = ! empty( $_POST['redirect_to'] ) ? $_POST['redirect_to'] : site_url( 'wp-login.php?checkemail=registered' );
							wp_safe_redirect( $redirect_to );
							exit;
						}
					}
					break;
				case 'login' :
				default:
					$secure_cookie = '';
					$interim_login = isset( $_REQUEST['interim-login'] );

					// If the user wants ssl but the session is not ssl, force a secure cookie.
					if ( ! empty( $_POST['log'] ) && ! force_ssl_admin() ) {
						$user_name = sanitize_user( $_POST['log'] );
						if ( $user = get_user_by( 'login', $user_name ) ) {
							if ( get_user_option( 'use_ssl', $user->ID ) ) {
								$secure_cookie = true;
								force_ssl_admin( true );
							}
						}
					}

					if ( ! empty( $_REQUEST['redirect_to'] ) ) {
						$redirect_to = $_REQUEST['redirect_to'];
						// Redirect to https if user wants ssl
						if ( $secure_cookie && false !== strpos( $redirect_to, 'wp-admin' ) )
							$redirect_to = preg_replace( '|^http://|', 'https://', $redirect_to );
					} else {
						$redirect_to = admin_url();
					}

					$reauth = empty( $_REQUEST['reauth'] ) ? false : true;

					if ( $http_post && isset( $_POST['log'] ) ) {

						$user = wp_signon( '', $secure_cookie );

						$redirect_to = apply_filters( 'login_redirect', $redirect_to, isset( $_REQUEST['redirect_to'] ) ? $_REQUEST['redirect_to'] : '', $user );

						if ( ! is_wp_error( $user ) && ! $reauth ) {
							if ( ( empty( $redirect_to ) || $redirect_to == 'wp-admin/' || $redirect_to == admin_url() ) ) {
								// If the user doesn't belong to a blog, send them to user admin. If the user can't edit posts, send them to their profile.
								if ( is_multisite() && ! get_active_blog_for_user( $user->ID ) && ! is_super_admin( $user->ID ) )
									$redirect_to = user_admin_url();
								elseif ( is_multisite() && ! $user->has_cap( 'read' ) )
									$redirect_to = get_dashboard_url( $user->ID );
								elseif ( ! $user->has_cap( 'edit_posts' ) )
									$redirect_to = $user->has_cap( 'read' ) ? admin_url( 'profile.php' ) : home_url();

								wp_redirect( $redirect_to );
								exit;
							}
							wp_safe_redirect( $redirect_to );
							exit;
						}

						$this->errors = $user;
					}

					// Clear errors if loggedout is set.
					if ( ! empty( $_GET['loggedout'] ) || $reauth )
						$this->errors = new WP_Error();

					// Some parts of this script use the main login form to display a message
					if		( isset( $_GET['loggedout'] ) && true == $_GET['loggedout'] )
						$this->errors->add( 'loggedout', __( 'You are now logged out.', 'theme-my-login' ), 'message' );
					elseif	( isset( $_GET['registration'] ) && 'disabled' == $_GET['registration'] )
						$this->errors->add( 'registerdisabled', __( 'User registration is currently not allowed.', 'theme-my-login' ) );
					elseif	( isset( $_GET['checkemail'] ) && 'confirm' == $_GET['checkemail'] )
						$this->errors->add( 'confirm', __( 'Check your e-mail for the confirmation link.', 'theme-my-login' ), 'message' );
					elseif ( isset( $_GET['resetpass'] ) && 'complete' == $_GET['resetpass'] )
						$this->errors->add( 'password_reset', __( 'Your password has been reset.', 'theme-my-login' ), 'message' );
					elseif	( isset( $_GET['checkemail'] ) && 'registered' == $_GET['checkemail'] )
						$this->errors->add( 'registered', __( 'Registration complete. Please check your e-mail.', 'theme-my-login' ), 'message' );
					elseif	( $interim_login )
						$this->errors->add( 'expired', __( 'Your session has expired. Please log-in again.', 'theme-my-login' ), 'message' );
					elseif ( strpos( $redirect_to, 'about.php?updated' ) )
						$this->errors->add('updated', __( '<strong>You have successfully updated WordPress!</strong> Please log back in to experience the awesomeness.', 'theme-my-login' ), 'message' );
					elseif	( $reauth )
						$this->errors->add( 'reauth', __( 'Please log in to continue.', 'theme-my-login' ), 'message' );

					// Clear any stale cookies.
					if ( $reauth )
						wp_clear_auth_cookie();
					break;
			} // end switch
		} // endif has_filter()
	}

	/**
	 * Calls "login_enqueue_scripts" on login page
	 *
	 * Callback for "wp_enqueue_scripts" hook
	 *
	 * @since 6.3
	 */
	public function wp_enqueue_scripts() {
		if ( self::is_tml_page() )
			do_action( 'login_enqueue_scripts' );
	}

	/**
	 * Calls "login_head" hook on login page
	 *
	 * Callback for "wp_head" hook
	 *
	 * @since 6.0
	 * @access public
	 */
	public function wp_head() {
		if ( self::is_tml_page() ) {
			// This is already attached to "wp_head"
			remove_action( 'login_head', 'wp_print_head_scripts', 9 );

			do_action( 'login_head' );
		}
	}

	/**
	 * Calls "login_footer" hook on login page
	 *
	 * Callback for "wp_footer" hook
	 *
	 * @since 6.3
	 */
	public function wp_footer() {
		if ( self::is_tml_page() ) {
			// This is already attached to "wp_footer"
			remove_action( 'login_footer', 'wp_print_footer_scripts', 20 );

			do_action( 'login_footer' );
		}
	}

	/**
	 * Prints javascript in the footer
	 *
	 * @since 6.0
	 * @access public
	 */
	public function wp_print_footer_scripts() {
		if ( ! self::is_tml_page() )
			return;

		switch ( $this->request_action ) {
			case 'lostpassword' :
			case 'retrievepassword' :
			case 'register' :
			?>
<script type="text/javascript">
try{document.getElementById('user_login').focus();}catch(e){}
if(typeof wpOnload=='function')wpOnload()
</script>
<?php
				break;
			case 'resetpass' :
			case 'rp' :
			?>
<script type="text/javascript">
try{document.getElementById('pass1').focus();}catch(e){}
if(typeof wpOnload=='function')wpOnload()
</script>
<?php
				break;
			case 'login' :
				$user_login = '';
				if ( isset($_POST['log']) )
					$user_login = ( 'incorrect_password' == $this->errors->get_error_code() || 'empty_password' == $this->errors->get_error_code() ) ? esc_attr( stripslashes( $_POST['log'] ) ) : '';
			?>
<script type="text/javascript">
function wp_attempt_focus() {
setTimeout( function() {
try {
<?php if ( $user_login ) { ?>
d = document.getElementById('user_pass');
d.value = '';
<?php } else { ?>
d = document.getElementById('user_login');
<?php if ( 'invalid_username' == $this->errors->get_error_code() ) { ?>
if ( d.value != '' )
d.value = '';
<?php
}
} ?>
d.focus();
d.select();
} catch(e){}
}, 200 );
}

wp_attempt_focus();
if(typeof wpOnload=='function')wpOnload()
</script>
<?php
				break;
		}
	}

	/************************************************************************************************************************
	 * Filters
	 ************************************************************************************************************************/

	/**
	 * Rewrites URL's containing wp-login.php created by site_url()
	 *
	 * @since 6.0
	 * @access public
	 *
	 * @param string $url The URL
	 * @param string $path The path specified
	 * @param string $orig_scheme The current connection scheme (HTTP/HTTPS)
	 * @param int $blog_id Blog ID
	 * @return string The modified URL
	 */
	public function site_url( $url, $path, $orig_scheme ) {
		global $pagenow;

		// Bail if currently viewing wp-login.php
		if ( 'wp-login.php' == $pagenow )
			return $url;

		// Bail if the URL isn't a login URL
		if ( false === strpos( $url, 'wp-login.php' ) )
			return $url;

		// Parse the query string from the URL
		parse_str( parse_url( $url, PHP_URL_QUERY ), $query );

		/**
		 * Bail if the URL is an interim-login URL
		 *
		 * This only works using the javascript workaround as implemented in
		 * admin/theme-my-login-admin.php and admin/js/theme-my-login-admin.js.
		 *
		 * @see http://core.trac.wordpress.org/ticket/31821
		 */
		if ( isset( $query['interim-login'] ) )
			return $url;

		// Determine the action
		$action = isset( $query['action'] ) ? $query['action'] : 'login';

		// Get the action's page link
		$url = self::get_page_link( $action, $query );

		// Change the connection scheme to HTTPS, if needed
		if ( 'https' == strtolower( $orig_scheme ) )
			$url = preg_replace( '|^http://|', 'https://', $url );

		return $url;
	}

	/**
	 * Filters logout URL to allow for logout permalink
	 *
	 * This is needed because WP doesn't pass the action parameter to site_url
	 *
	 * @since 6.3
	 * @access public
	 *
	 * @param string $logout_url Logout URL
	 * @param string $redirect Redirect URL
	 * @return string Logout URL
	 */
	public function logout_url( $logout_url, $redirect ) {
		$logout_url = self::get_page_link( 'logout' );
		if ( $redirect )
			$logout_url = add_query_arg( 'redirect_to', urlencode( $redirect ), $logout_url );
		return $logout_url;
	}

	/**
	 * Changes single_post_title() to reflect the current action
	 *
	 * Callback for "single_post_title" hook in single_post_title()
	 *
	 * @see single_post_title()
	 * @since 6.0
	 * @access public
	 *
	 * @param string $title The current post title
	 * @return string The modified post title
	 */
	function single_post_title( $title ) {
		if ( self::is_tml_page( 'login' ) && is_user_logged_in() )
			$title = $this->get_instance()->get_title( 'login' );
		return $title;
	}

	/**
	 * Changes the_title() to reflect the current action
	 *
	 * Callback for "the_title" hook in the_title()
	 *
	 * @see the_title()
	 * @since 6.0
	 * @acess public
	 *
	 * @param string $title The current post title
	 * @param int $post_id The current post ID
	 * @return string The modified post title
	 */
	public function the_title( $title, $post_id = 0 ) {
		if ( is_admin() )
			return $title;

		if ( self::is_tml_page( 'login', $post_id ) && is_user_logged_in() ) {
			if ( in_the_loop() )
				$title = $this->get_instance()->get_title( 'login' );
		}
		return $title;
	}

	/**
	 * Hide Login & Register if user is logged in, hide Logout if not
	 *
	 * Callback for "wp_setup_nav_menu_item" hook in wp_setup_nav_menu_item()
	 *
	 * @see wp_setup_nav_menu_item()
	 * @since 6.0
	 * @access public
	 *
	 * @param object $menu_item The menu item
	 * @return object The (possibly) modified menu item
	 */
	public function wp_setup_nav_menu_item( $menu_item ) {
		if ( is_admin() )
			return $menu_item;

		if ( 'page' != $menu_item->object )
			return $menu_item;

		// User  is logged in
		if ( is_user_logged_in() ) {

			// Hide login, register and lost password
			if ( self::is_tml_page( array( 'login', 'register', 'lostpassword' ), $menu_item->object_id ) ) {
				$menu_item->_invalid = true;
			}

		// User is not logged in
		} else {

			// Hide Logout
			if ( self::is_tml_page( 'logout', $menu_item->object_id ) ) {
				$menu_item->_invalid = true;
			}
		}

		return $menu_item;
	}

	/**
	 * Excludes pages from wp_list_pages
	 *
	 * @since 6.3.7
	 *
	 * @param array $exclude Page IDs to exclude
	 * @return array Page IDs to exclude
	 */
	public function wp_list_pages_excludes( $exclude ) {
		$pages = get_posts( array(
			'post_type'      => 'page',
			'post_status'    => 'any',
			'meta_key'       => '_tml_action',
			'posts_per_page' => -1
		) );
		$pages = wp_list_pluck( $pages, 'ID' );

		return array_merge( $exclude, $pages );
	}

	/**
	 * Adds nonce to logout link
	 *
	 * @since 6.3.7
	 *
	 * @param string $link Page link
	 * @param int $post_id Post ID
	 * @return string Page link
	 */
	public function page_link( $link, $post_id ) {
		if ( self::is_tml_page( 'logout', $post_id ) )
			$link = add_query_arg( '_wpnonce', wp_create_nonce( 'log-out' ), $link );
		return $link;
	}

	/**
	 * Add proper message in case of e-mail login error
	 *
	 * @since 6.4.5
	 *
	 * @param null|WP_Error|WP_User $user
	 * @param string                $username
	 * @param string                $password
	 * @return null|WP_User|WP_Error
	 */
	public function authenticate( $user, $username, $password ) {
		if ( 'email' == $this->get_option( 'login_type' ) && null == $user ) {
			return new WP_Error( 'invalid_email', __( '<strong>ERROR</strong>: Invalid email address.', 'theme-my-login' ) );
		}

		return $user;
	}


	/************************************************************************************************************************
	 * Utilities
	 ************************************************************************************************************************/

	/**
	 * Handler for "theme-my-login" shortcode
	 *
	 * Optional $atts contents:
	 *
	 * - instance - A unqiue instance ID for this instance.
	 * - default_action - The action to display. Defaults to "login".
	 * - login_template - The template used for the login form. Defaults to "login-form.php".
	 * - register_template - The template used for the register form. Defaults to "register-form.php".
	 * - lostpassword_template - The template used for the lost password form. Defaults to "lostpassword-form.php".
	 * - resetpass_template - The template used for the reset password form. Defaults to "resetpass-form.php".
	 * - user_template - The templated used for when a user is logged in. Defalts to "user-panel.php".
	 * - show_title - True to display the current title, false to hide. Defaults to true.
	 * - show_log_link - True to display the login link, false to hide. Defaults to true.
	 * - show_reg_link - True to display the register link, false to hide. Defaults to true.
	 * - show_pass_link - True to display the lost password link, false to hide. Defaults to true.
	 * - logged_in_widget - True to display the widget when logged in, false to hide. Defaults to true.
	 * - logged_out_widget - True to display the widget when logged out, false to hide. Defaults to true.
	 * - show_gravatar - True to display the user's gravatar, false to hide. Defaults to true.
	 * - gravatar_size - The size of the user's gravatar. Defaults to "50".
	 *
	 * @since 6.0
	 * @access public
	 *
	 * @param string|array $atts Attributes passed from the shortcode
	 * @return string HTML output from Theme_My_Login_Template->display()
	 */
	public function shortcode( $atts = '' ) {
		static $did_main_instance = false;

		$atts = wp_parse_args( $atts );

		if ( self::is_tml_page() && in_the_loop() && is_main_query() && ! $did_main_instance ) {
			$instance = $this->get_instance();

			if ( ! empty( $this->request_instance ) )
				$instance->set_active( false );

			if ( ! empty( $this->request_action ) )
				$atts['default_action'] = $this->request_action;

			if ( ! isset( $atts['show_title'] ) )
				$atts['show_title'] = false;

			foreach ( $atts as $option => $value ) {
				$instance->set_option( $option, $value );
			}

			$did_main_instance = true;
		} else {
			$instance = $this->load_instance( $atts );
		}
		return $instance->display();
	}

	/**
	 * Determines if $action is for $page
	 *
	 * @since 6.3
	 *
	 * @param array|string $action An action or array of actions to check
	 * @param int|object Post ID or object
	 * @return bool True if $action is for $page, false otherwise
	 */
	public static function is_tml_page( $action = '', $page = '' ) {
		if ( ! $page = get_post( $page ) )
			return false;

		if ( 'page' != $page->post_type )
			return false;

		if ( ! $page_action = self::get_page_action( $page->ID ) )
			return false;

		if ( empty( $action ) )
			return true;

		if ( in_array( $page_action, (array) $action ) )
			return true;

		return false;
	}

	/**
	 * Returns link for a login page
	 *
	 * @since 6.3
	 * @access public
	 *
	 * @param string $action The action
	 * @param string|array $query Optional. Query arguments to add to link
	 * @return string Login page link with optional $query arguments appended
	 */
	public static function get_page_link( $action, $query = '' ) {
		$page_id = self::get_page_id( $action );

		if ( $page_id ) {
			$link = get_permalink( $page_id );
		} elseif ( $page_id = self::get_page_id( 'login' ) ) {
			$link = add_query_arg( 'action', $action, get_permalink( $page_id ) );
		} else {
			// Remove site_url filter so we can use wp-login.php
			remove_filter( 'site_url', array( self::get_object(), 'site_url' ), 10, 3 );

			$link = site_url( "wp-login.php?action=$action" );
		}

		if ( ! empty( $query ) ) {
			$args = wp_parse_args( $query );

			if ( isset( $args['action'] ) && $action == $args['action'] )
				unset( $args['action'] );

			$link = add_query_arg( array_map( 'rawurlencode', $args ), $link );
		}

		$link = set_url_scheme( $link, 'login' );

		return apply_filters( 'tml_page_link', $link, $action, $query );
	}

	/**
	 * Retrieves a page ID for an action
	 *
	 * @since 6.3
	 *
	 * @param string $action The action
	 * @return int|bool The page ID if exists, false otherwise
	 */
	public static function get_page_id( $action ) {
		global $wpdb;

		if ( 'rp' == $action )
			$action = 'resetpass';
		elseif ( 'retrievepassword' == $action )
			$action = 'lostpassword';

		if ( ! $page_id = wp_cache_get( $action, 'tml_page_ids' ) ) {
			$page_id = $wpdb->get_var( $wpdb->prepare( "SELECT p.ID FROM $wpdb->posts p LEFT JOIN $wpdb->postmeta pmeta ON p.ID = pmeta.post_id WHERE p.post_type = 'page' AND pmeta.meta_key = '_tml_action' AND pmeta.meta_value = %s", $action ) );
			if ( ! $page_id )
				return null;
			wp_cache_add( $action, $page_id, 'tml_page_ids' );
		}
		return apply_filters( 'tml_page_id', $page_id, $action );
	}

	/**
	 * Get the action for a page
	 *
	 * @since 6.3
	 *
	 * @param int|object Post ID or object
	 * @return string|bool Action name if exists, false otherwise
	 */
	public static function get_page_action( $page ) {
		if ( ! $page = get_post( $page ) )
			return false;

		return get_post_meta( $page->ID, '_tml_action', true );
	}

	/**
	 * Enqueues the specified sylesheet
	 *
	 * First looks in theme/template directories for the stylesheet, falling back to plugin directory
	 *
	 * @since 6.0
	 * @access public
	 *
	 * @param string $file Filename of stylesheet to load
	 * @return string Path to stylesheet
	 */
	public static function get_stylesheet( $file = 'theme-my-login.css' ) {
		if ( file_exists( get_stylesheet_directory() . '/' . $file ) )
			$stylesheet = get_stylesheet_directory_uri() . '/' . $file;
		elseif ( file_exists( get_template_directory() . '/' . $file ) )
			$stylesheet = get_template_directory_uri() . '/' . $file;
		else
			$stylesheet = plugins_url( $file, dirname( __FILE__ ) );
		return $stylesheet;
	}

	/**
	 * Retrieves active instance object
	 *
	 * @since 6.3
	 * @access public
	 *
	 * @return object Instance object
	 */
	public function get_active_instance() {
		return $this->get_instance( (int) $this->request_instance );
	}

	/**
	 * Retrieves a loaded instance object
	 *
	 * @since 6.3
	 * @access public
	 *
	 * @param int $id Instance ID
	 * @return object Instance object

	 */
	public function get_instance( $id = 0 ) {
		if ( isset( $this->loaded_instances[$id] ) )
			return $this->loaded_instances[$id];
	}

	/**
	 * Sets an instance object
	 *
	 * @since 6.3
	 * @access public
	 *
	 * @param object $object Instance object
	 */
	public function set_instance( $object ) {
		$this->loaded_instances[] = $object;
	}

	/**
	 * Instantiates an instance
	 *
	 * @since 6.3
	 * @access public
	 *
	 * @param array|string $args Array or query string of arguments

	 * @return object Instance object
	 */
	public function load_instance( $args = '' ) {

		$instance = new Theme_My_Login_Template( $args );
		$instance->set_option( 'instance', count( $this->loaded_instances ) );

		if ( $instance->get_option( 'instance' ) === $this->request_instance ) {
			$instance->set_active();
			$instance->set_option( 'default_action', $this->request_action );
		}

		$this->loaded_instances[] = $instance;

		return $instance;
	}

	/**
	 * Load a custom translation file for current language if available.
	 *
	 * Note that custom translation files inside the plugin folder
	 * will be removed on plugin updates. If you're creating custom
	 * translation files, please place them in a '/theme-my-login/'
	 * directory within the global language folder.
	 *
	 * @since 6.4.4
	 *
	 * @param string $domain The domain for which a language file is being loaded.
	 * @param string $mofile Full path to the target mofile.
	 */
	public function load_custom_textdomain( $domain, $mofile ) {
		if ( 'theme-my-login' === $domain ) {
			remove_action( 'load_textdomain', array( $this, 'load_custom_textdomain' ), 10, 2 );

			// Look in global /wp-content/languages/theme-my-login folder for a translation
			// and load it if available.
			$mofile = basename( $mofile );
			if ( file_exists( WP_LANG_DIR . '/theme-my-login/' . $mofile ) ) {
				load_textdomain( 'theme-my-login', WP_LANG_DIR . '/theme-my-login/' . $mofile );
			}

			add_action( 'load_textdomain', array( $this, 'load_custom_textdomain' ), 10, 2 );
		}
	}

	/**
	 * Handles sending password retrieval email to user.
	 *
	 * @since 6.0
	 * @access public
	 * @uses $wpdb WordPress Database object
	 *
	 * @return bool|WP_Error True: when finish. WP_Error on error
	 */
	public static function retrieve_password() {
		global $wpdb, $wp_hasher;

		$errors = new WP_Error();

		if ( empty( $_POST['user_login'] ) ) {
			$errors->add( 'empty_username', __( '<strong>ERROR</strong>: Enter a username or e-mail address.', 'theme-my-login' ) );
		} else if ( strpos( $_POST['user_login'], '@' ) ) {
			$user_data = get_user_by( 'email', trim( wp_unslash( $_POST['user_login'] ) ) );
			if ( empty( $user_data ) )
				$errors->add( 'invalid_email', __( '<strong>ERROR</strong>: There is no user registered with that email address.', 'theme-my-login' ) );
		} else {
			$login = trim( $_POST['user_login'] );
			$user_data = get_user_by( 'login', $login );
		}

		do_action( 'lostpassword_post', $errors );

		if ( $errors->get_error_code() )
			return $errors;

		if ( ! $user_data ) {
			$errors->add( 'invalidcombo', __( '<strong>ERROR</strong>: Invalid username or e-mail.', 'theme-my-login' ) );
			return $errors;
		}

		// redefining user_login ensures we return the right case in the email
		$user_login = $user_data->user_login;
		$user_email = $user_data->user_email;
		$key = get_password_reset_key( $user_data );

		if ( is_wp_error( $key ) ) {
			return $key;
		}

		$message = __( 'Someone requested that the password be reset for the following account:', 'theme-my-login' ) . "\r\n\r\n";
		$message .= network_home_url( '/' ) . "\r\n\r\n";
		$message .= sprintf( __( 'Username: %s', 'theme-my-login' ), $user_login ) . "\r\n\r\n";
		$message .= __( 'If this was a mistake, just ignore this email and nothing will happen.', 'theme-my-login' ) . "\r\n\r\n";
		$message .= __( 'To reset your password, visit the following address:', 'theme-my-login' ) . "\r\n\r\n";
		$message .= '<' . network_site_url( "wp-login.php?action=rp&key=$key&login=" . rawurlencode( $user_login ), 'login' ) . ">\r\n";

		if ( is_multisite() ) {
			$blogname = $GLOBALS['current_site']->site_name;
		} else {
			// The blogname option is escaped with esc_html on the way into the database in sanitize_option
			// we want to reverse this for the plain text arena of emails.
			$blogname = wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );
		}

		$title = sprintf( __( '[%s] Password Reset', 'theme-my-login' ), $blogname );

		$title = apply_filters( 'retrieve_password_title', $title, $user_login, $user_data );
		$message = apply_filters( 'retrieve_password_message', $message, $key, $user_login, $user_data );

		if ( $message && ! wp_mail( $user_email, $title, $message ) )
			wp_die( __( 'The e-mail could not be sent.', 'theme-my-login' ) . "<br />\n" . __( 'Possible reason: your host may have disabled the mail() function...', 'theme-my-login' ) );

		return true;
	}
}
endif; // Class exists
