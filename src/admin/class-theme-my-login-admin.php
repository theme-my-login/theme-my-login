<?php

/**
 * Theme My Login Admin Class
 *
 * @package Theme_My_Login
 * @subpackage Administration
 */

/**
 * Class used to implement the admin object.
 *
 * @since 7.0
 */
final class Theme_My_Login_Admin {

	/**
	 * The admin instance.
	 *
	 * @var Theme_My_Login
	 */
	private static $instance;

	/**
	 * The admin pages.
	 *
	 * @var array
	 */
	protected $pages = array();

	/**
	 * Get the instance.
	 *
	 * @since 7.0
	 *
	 * @return Theme_My_Login_Admin
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Add an admin page.
	 *
	 * @since 7.0
	 *
	 * @param array $args {
	 *     Optional. An array of arguments for adding an admin page.
	 *
	 *     @param string   $page_title  Required. The page title.
	 *     @param string   $menu_title  Required. The menu title.
	 *     @param string   $menu_slug   Required. The menu slug.
	 *     @param string   $capability  The required capability.
	 *     @param callable $function    The function to be called.
	 *     @param string   $parent_slug The parent slug.
	 * }
	 * @return string The resulting page's hook_suffix or false if the user does
	 *                not have the capability required.
	 */
	public function add_menu_item( $args = array() ) {
		$args = wp_parse_args( $args, array(
			'page_title'  => '',
			'menu_title'  => '',
			'menu_slug'   => '',
			'parent_slug' => 'theme-my-login',
			'capability'  => 'manage_options',
			'function'    => 'tml_admin_settings_page',
		) );

		if ( empty( $args['page_title'] ) || empty( $args['menu_title'] ) || empty( $args['menu_slug'] ) ) {
			return;
		}

		if ( empty( $args['parent_slug'] ) ) {
			$hook = add_menu_page(
				$args['page_title'],
				$args['menu_title'],
				$args['capability'],
				$args['menu_slug'],
				$args['function'],
				isset( $args['menu_icon'] ) ? $args['menu_icon'] : ''
			);
		} else {
			$hook = add_submenu_page(
				$args['parent_slug'],
				$args['page_title'],
				$args['menu_title'],
				$args['capability'],
				$args['menu_slug'],
				$args['function']
			);
		}

		$this->pages[ $args['menu_slug'] ] = $hook;

		return $hook;
	}

	/**
	 * Determine if a specific admin page has been added.
	 *
	 * @since 7.0
	 *
	 * @param string $page The page slug.
	 * @return bool True if the page is a TML admin page, false if not.
	 */
	public function has_page( $page ) {
		return ! empty( $this->pages[ $page ] );
	}

	/**
	 * Get a page hook.
	 *
	 * @since 7.0
	 *
	 * @param string $page The plugin page.
	 * @return string The page hook.
	 */
	public function get_page_hook( $page = 'theme-my-login' ) {
		if ( $this->has_page( $page ) ) {
			return $this->pages[ $page ];
		}
	}


	/**
	 * Construct the instance.
	 *
	 * @since 7.0
	 */
	protected function __construct() {
		/**
		 * Fires when TML Admin has been initialized.
		 *
		 * @since 7.0
		 *
		 * @param Theme_My_Login_Admin The TML Admin object.
		 */
		do_action( 'tml_admin_init', $this );
	}
}
