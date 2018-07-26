<?php

/**
 * Theme My Login Action Class
 *
 * @package Theme_My_Login
 * @subpackage Actions
 */

/**
 * Class used to implement the action object.
 *
 * @since 7.0
 */
class Theme_My_Login_Action {

	/**
	 * The action name.
	 *
	 * @var string
	 */
	protected $name;

	/**
	 * The action title.
	 *
	 * @var string
	 */
	protected $title;

	/**
	 * The action slug.
	 *
	 * @var string
	 */
	protected $slug;

	/**
	 * The action handler.
	 *
	 * @var callable
	 */
	protected $callback;

	/**
	 * Whether this action is a network action or not.
	 *
	 * @var bool
	 */
	public $network = false;

	/**
	 * Whether a link to the action should be shown on forms or not.
	 *
	 * @var bool
	 */
	public $show_on_forms = true;

	/**
	 * Whether this action should be selectable in the widget or not.
	 *
	 * @var bool
	 */
	public $show_in_widget = true;

	/**
	 * Whether this action should be shown for use in nav menus or not.
	 *
	 * @var bool
	 */
	public $show_in_nav_menus = true;

	/**
	 * Whether to show the nav menu item or not when this action is assigned to a nav menu.
	 *
	 * @var bool
	 */
	public $show_nav_menu_item;

	/**
	 * Whether this action should be shown in the slug settings or not.
	 *
	 * @var bool
	 */
	public $show_in_slug_settings = true;

	/**
	 * Construct the instance.
	 *
	 * @since 7.0
	 *
	 * @param string $name The action name.
	 * @param array  $args {
	 *     Optional. An array of arguments.
	 *
	 *     @type string      $title                 The action title.
	 *     @type string      $slug                  The action slug.
	 *     @type callable    $callback              The action callback to fire when accessed.
	 *     @type bool|string $show_on_forms         Whether a link to the action should be shown on forms or not.
	 *     @type bool        $show_in_widget        Whether this action should be selectable in the widget or not.
	 *     @type bool        $show_in_nav_menus     Whether this action should be available for use in nav menus or not.
	 *     @type bool        $show_nav_menu_item    Whether to show an assigned nav menu item or not.
	 *     @type bool        $show_in_slug_settings Whether this action should be shown in the slug settings or not.
	 * }
	 */
	public function __construct( $name, $args = array() ) {

		$this->set_name( $name );

		$args = wp_parse_args( $args, array(
			'title'                 => '',
			'slug'                  => '',
			'callback'              => '',
			'network'               => false,
			'show_on_forms'         => true,
			'show_in_widget'        => true,
			'show_in_nav_menus'     => true,
			'show_in_slug_settings' => true,
		) );

		if ( ! isset( $args['show_nav_menu_item'] ) ) {
			$args['show_nav_menu_item'] = $args['show_in_nav_menus'];
		}

		$this->set_title( $args['title'] );
		$this->set_slug( $args['slug'] );
		$this->set_callback( $args['callback'] );

		$this->network               = (bool) $args['network'];
		$this->show_on_forms         = $args['show_on_forms'];
		$this->show_in_widget        = (bool) $args['show_in_widget'];
		$this->show_in_nav_menus     = (bool) $args['show_in_nav_menus'];
		$this->show_nav_menu_item    = (bool) $args['show_nav_menu_item'];
		$this->show_in_slug_settings = (bool) $args['show_in_slug_settings'];
	}

	/**
	 * Get the action name.
	 *
	 * @since 7.0
	 *
	 * @return string The action name.
	 */
	public function get_name() {
		return $this->name;
	}

	/**
	 * Set the action name.
	 *
	 * @since 7.0
	 *
	 * @param string $name The action name.
	 */
	protected function set_name( $name ) {
		$this->name = sanitize_key( $name );
	}

	/**
	 * Get the action title.
	 *
	 * @since 7.0
	 *
	 * @return string The action title.
	 */
	public function get_title() {
		/**
		 * Filter the action title.
		 *
		 * @since 7.0
		 *
		 * @param string $title The action title.
		 * @param string $name  The action name.
		 */
		return apply_filters( 'tml_get_action_title', $this->title, $this->get_name() );
	}

	/**
	 * Set the action title.
	 *
	 * @since 7.0
	 *
	 * @param string $title The action title.
	 */
	public function set_title( $title ) {
		$this->title = $title;
	}

	/**
	 * Get the action callback.
	 *
	 * @since 7.0.3
	 *
	 * @return callable The action callback.
	 */
	public function get_callback() {
		return $this->callback;
	}

	/**
	 * Set the action callback.
	 *
	 * @since 7.0.3
	 *
	 * @param callable $callback The action callback.
	 */
	public function set_callback( $callback ) {
		$this->callback = $callback;
	}

	/**
	 * Adds the callback to the proper hook.
	 *
	 * @since 7.0.3
	 */
	public function add_callback_hook() {
		if ( $callback = $this->get_callback() ) {
			add_action( 'tml_action_' . $this->get_name(), $callback, 15 );
		}
	}

	/**
	 * Removes the callback from the proper hook.
	 *
	 * @since 7.0.3
	 */
	public function remove_callback_hook() {
		if ( $callback = $this->get_callback() ) {
			remove_action( 'tml_action_' . $this->get_name(), $callback, 15 );
		}
	}

	/**
	 * Get the action slug.
	 *
	 * @since 7.0
	 *
	 * @return string The action slug.
	 */
	public function get_slug() {
		/**
		 * Filter the action slug.
		 *
		 * @since 7.0
		 *
		 * @param string $slug The action slug.
		 * @param string $name The action name.
		 */
		return apply_filters( 'tml_get_action_slug', $this->slug, $this->get_name() );
	}

	/**
	 * Set the action slug.
	 *
	 * @since 7.0
	 *
	 * @param string $slug The action slug.
	 */
	public function set_slug( $slug ) {
		if ( empty( $slug ) ) {
			$slug = $this->get_name();
		}
		$this->slug = $slug;
	}

	/**
	 * Get the action URL.
	 *
	 * @since 7.0
	 *
	 * @param string $scheme  The URL scheme.
	 * @param bool   $network Whether to retrieve the URL for the current network or current blog.
	 * @return string The action URL.
	 */
	public function get_url( $scheme = 'login', $network = null ) {
		if ( null === $network ) {
			$network = $this->network;
		}

		$function = $network ? 'network_home_url' : 'home_url';

		if ( tml_use_permalinks() ) {
			$path = user_trailingslashit( $this->get_slug() );
			$url  = $function( $path, $scheme );
		} else {
			$url = $function( '?action=' . $this->get_name(), $scheme );
		}

		/**
		 * Filter the action URL.
		 *
		 * @since 7.0
		 *
		 * @param string $url     The action URL.
		 * @param string $name    The action name.
		 * @param string $scheme  The URL scheme.
		 * @param bool   $network Whether to retrieve the URL for the current network or current blog.
		 */
		return apply_filters( 'tml_get_action_url', $url, $this->get_name(), $scheme, $network );
	}
}
