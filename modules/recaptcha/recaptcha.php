<?php
/**
 * Plugin Name: reCAPTCHA
 * Description: Enabling this module will initialize reCAPTCHA. You will then have to configure the settings via the "reCAPTCHA" tab.
 *
 * Holds Theme My Login Recaptcha class
 *
 * @package Theme_My_Login
 * @subpackage Theme_My_Login_Recaptcha
 * @since 6.3
 */

if ( ! class_exists( 'Theme_My_Login_Recaptcha' ) ) :
/**
 * Theme My Login Custom Permalinks class
 *
 * Adds the ability to set permalinks for default actions.
 *
 * @since 6.3
 */
class Theme_My_Login_Recaptcha extends Theme_My_Login_Abstract {
	/**
	 * Holds options key
	 *
	 * @since 6.3
	 * @access protected
	 * @var string
	 */
	protected $options_key = 'theme_my_login_recaptcha';

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
		return array(
			'public_key'  => '',
			'private_key' => '',
			'theme'       => 'light'
		);
	}

	/**
	 * Loads the module
	 *
	 * @since 6.3
	 * @access protected
	 */
	protected function load() {
		if ( ! ( $this->get_option( 'public_key' ) || $this->get_option( 'private_key' ) ) )
			return;

		add_action( 'wp_enqueue_scripts', array( $this, 'wp_enqueue_scripts' ) );

		add_action( 'register_form',       array( $this, 'recaptcha_display'   ) );
		add_filter( 'registration_errors', array( $this, 'registration_errors' ) );

		if ( is_multisite() ) {
			add_action( 'signup_extra_fields',       array( $this, 'recaptcha_display'    ) );
			add_filter( 'wpmu_validate_user_signup', array( $this, 'wpmu_validate_signup' ) );
			add_filter( 'wpmu_validate_blog_signup', array( $this, 'wpmu_validate_signup' ) );
		}
	}

	/**
	 * Enqueues scripts
	 *
	 * @since 6.3
	 */
	function wp_enqueue_scripts() {
		wp_enqueue_script( 'recaptcha', 'https://www.google.com/recaptcha/api.js' );
	}

	/**
	 * Retrieves reCAPTCHA errors
	 *
	 * @since 6.3
	 *
	 * @param WP_Error $errors WP_Error object
	 * @return WP_Error WP_Error object
	 */
	public function registration_errors( $errors ) {
		$response = isset( $_POST['g-recaptcha-response'] ) ? $_POST['g-recaptcha-response'] : '';
		$result   = $this->recaptcha_validate( $response );

		if ( is_wp_error( $result ) ) {

			$error_code = $result->get_error_message();

			switch ( $error_code ) {
				case 'missing-input-secret' :
				case 'invalid-input-secret' :
					$errors->add( 'recaptcha', __( '<strong>ERROR</strong>: Invalid reCAPTCHA secret key.', 'theme-my-login' ), $error_code );
					break;
				case 'missing-input-response' :
				case 'invalid-input-response' :
					$errors->add( 'recaptcha', __( '<strong>ERROR</strong>: Please check the box to prove that you are not a robot.', 'theme-my-login' ), $error_code );
					break;
				case 'recaptcha-not-reachable' :
				default :
					$errors->add( 'recaptcha', __( '<strong>ERROR</strong>: Unable to reach the reCAPTCHA server.', 'theme-my-login' ), $error_code );
					break;
			}
		}
		return $errors;
	}

	/**
	 * Retrieves reCAPTCHA errors for multisite
	 *
	 * @since 6.3.7
	 *
	 * @param array $result Signup parameters
	 * @return array Signup parameters
	 */
	public function wpmu_validate_signup( $result ) {
		$result['errors'] = $this->registration_errors( $result['errors'] );
		return $result;
	}

	/**
	 * Displays reCAPTCHA
	 *
	 * @since 6.3
	 * @access public
	 */
	public function recaptcha_display( $errors = null ) {
		if ( is_multisite() ) {
			if ( $error = $errors->get_error_message( 'recaptcha' ) )
				echo '<p class="error">' . $error . '</p>';
		}
		echo '<div class="g-recaptcha" data-sitekey="' . esc_attr( $this->get_option( 'public_key' ) ) . '" data-theme="' . esc_attr( $this->get_option( 'theme' ) ) . '"></div>';
	}

	/**
	 * Validates reCAPTCHA
	 *
	 * @since 6.3
	 * @access public
	 */
	public function recaptcha_validate( $response, $remote_ip = '' ) {

		if ( empty( $remote_ip ) )
			$remote_ip = $_SERVER['REMOTE_ADDR'];

		$response = wp_remote_post( 'https://www.google.com/recaptcha/api/siteverify', array(
			'body' => array(
				'secret'   => $this->get_option( 'private_key' ),
				'response' => $response,
				'remoteip' => $remote_ip
			)
		) );

		$response_code    = wp_remote_retrieve_response_code( $response );
		$response_message = wp_remote_retrieve_response_message( $response );
		$response_body    = wp_remote_retrieve_body( $response );

		if ( 200 == $response_code ) {

			$result = json_decode( $response_body, true );

			if ( $result['success'] )
				return true;

			return new WP_Error( 'recaptcha', reset( $result['error-codes'] ) );
		}

		return new WP_Error( 'recaptcha', 'recaptcha-not-reachable' );
	}
}

Theme_My_Login_Recaptcha::get_object();

endif;

if ( is_admin() )
	include_once( dirname( __FILE__ ) . '/admin/recaptcha-admin.php' );

