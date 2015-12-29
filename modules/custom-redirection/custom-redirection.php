<?php
/**
 * Plugin Name: Custom Redirection
 * Description: Enabling this module will initialize custom redirection. You will then have to configure the settings via the "Redirection" tab.
 *
 * Holds Theme My Login Custom Redirection class
 *
 * @package Theme_My_Login
 * @subpackage Theme_My_Login_Custom_Redirection
 * @since 6.0
 */

if ( ! class_exists( 'Theme_My_Login_Custom_Redirection' ) ) :
/**
 * Theme My Login Custom Redirection class
 *
 * Adds the ability to redirect users when logging in/out based upon their "user role".
 *
 * @since 6.0
 */
class Theme_My_Login_Custom_Redirection extends Theme_My_Login_Abstract {
	/**
	 * Holds options key
	 *
	 * @since 6.3
	 * @access protected
	 * @var string
	 */
	protected $options_key = 'theme_my_login_redirection';

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
	 * Called on Theme_My_Login_Abstract::__construct
	 *
	 * @since 6.0
	 * @access protected
	 */
	protected function load() {
		add_action( 'login_form',      array( $this, 'login_form'      )        );
		add_filter( 'login_redirect',  array( $this, 'login_redirect'  ), 10, 3 );
		add_filter( 'logout_redirect', array( $this, 'logout_redirect' ), 10, 3 );
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
		global $wp_roles;

		if ( empty( $wp_roles ) )
			$wp_roles = new WP_Roles;

		$options = array();
		foreach ( $wp_roles->get_names() as $role => $label ) {
			if ( 'pending' != $role ) {
				$options[$role] = array(
					'login_type' => 'default',
					'login_url' => '',
					'logout_type' => 'default',
					'logout_url' => ''
				);
			}
		}
		return $options;
	}

	/**
	 * Get the redirect URL for a user.
	 *
	 * @since 6.4.1
	 *
	 * @param WP_User $user User object
	 * @param string $type Optional. Type of redirect. Accepts 'login'
	 *                               or 'logout'. Default is 'login'.
	 * @param string $default Optional. Default URL if somehow not found
	 * @return string Redirect URL
	 */
	public function get_redirect_for_user( $user, $type = 'login', $default = '' ) {
		// Make sure we have a default
		if ( empty( $default ) )
			$default = admin_url( 'profile.php' );

		// Bail if $user is not a WP_User
		if ( ! $user instanceof WP_User )
			return $default;

		// Make sure $type is valid
		if ( ! ( 'login' == $type || 'logout' == $type ) )
			$type = 'login';

		// Make sure the user has a role
		if ( is_multisite() && empty( $user->roles ) ) {
			$user->roles = array( 'subscriber' );
		}

		// Get the user's role
		$user_role = reset( $user->roles );

		// Get the redirection settings for the user's role
		$redirection = $this->get_option( $user_role, array() );

		// Determine which redirection type is being used
		switch ( $redirection["{$type}_type"] ) {

			case 'referer' :
				// Get the referer
				if ( ! $referer = wp_get_original_referer() )
					$referer = wp_get_referer();

				// Strip unwanted arguments from the referer
				$referer = Theme_My_Login_Common::strip_query_args( $referer );

				// Is the URL a single post type?
				if ( $page_id = url_to_postid( $referer ) ) {
					// Bail if the referer is TML page
					if ( Theme_My_Login::is_tml_page( null, $page_id ) )
						return $default;
				}

				// Send 'em back to the referer
				$redirect_to = $referer;
				break;

			case 'custom' :
				// Send 'em to the specified URL
				$redirect_to = $redirection["{$type}_url"];

				// Allow a few user specific variables
				$redirect_to = str_replace(
					array(
						'%user_id%',
						'%user_nicename%'
					),
					array(
						$user->ID,
						$user->user_nicename
					),
					$redirect_to
				);
				break;
		}

		// Make sure $redirect_to isn't empty
		if ( empty( $redirect_to ) )
			$redirect_to = $default;

		return $redirect_to;
	}

	/**
	 * Adds "_wp_original_referer" field to login form
	 *
	 * Callback for "login_form" hook in file "login-form.php", included by method Theme_My_Login_Template::display()
	 *
	 * @see Theme_My_Login_Template::display()
	 * @since 6.0
	 * @access public
	 */
	public function login_form() {
		if ( ! empty( $_REQUEST['redirect_to'] ) ) {
			$referer = wp_unslash( $_REQUEST['redirect_to'] );
		} elseif ( wp_get_original_referer() ) {
			$referer = wp_get_original_referer();
		} else {
			$referer = Theme_My_Login::is_tml_page() ? wp_get_referer() : wp_unslash( $_SERVER['REQUEST_URI'] );
		}
		echo '<input type="hidden" name="_wp_original_http_referer" value="' . esc_attr( $referer ) . '" />';
	}

	/**
	 * Handles login redirection
	 *
	 * Callback for "login_redirect" hook in method Theme_My_Login::the_request()
	 *
	 * @see Theme_My_Login::the_request()
	 * @since 6.0
	 * @access public
	 *
	 * @param string $redirect_to Default redirect
	 * @param string $request Requested redirect
	 * @param WP_User|WP_Error WP_User if user logged in, WP_Error otherwise
	 * @return string New redirect
	 */
	public function login_redirect( $redirect_to, $request, $user ) {
		// Return the redirect URL for the user
		return $this->get_redirect_for_user( $user, 'login', $redirect_to );
	}

	/**
	 * Handles logout redirection
	 *
	 * Callback for "logout_redirect" hook in method Theme_My_Login::the_request()
	 *
	 * @see Theme_My_Login::the_request()
	 * @since 6.0
	 * @access public
	 *
	 * @param string $redirect_to Default redirect
	 * @param string $request Requested redirect
	 * @param WP_User|WP_Error WP_User if user logged in, WP_Error otherwise
	 * @return string New redirect
	 */
	public function logout_redirect( $redirect_to, $request, $user ) {
		// Get the redirect URL for the user
		$redirect_to = $this->get_redirect_for_user( $user, 'logout', $redirect_to );

		// Make sure we're not trying to redirect to an admin URL
		if ( false !== strpos( $redirect_to, 'wp-admin' ) )
			$redirect_to = add_query_arg( 'loggedout', 'true', wp_login_url() );

		// Return the redirect URL for the user
		return $redirect_to;
	}
}

Theme_My_Login_Custom_Redirection::get_object();

endif;

if ( is_admin() )
	include_once( dirname( __FILE__ ) . '/admin/custom-redirection-admin.php' );
