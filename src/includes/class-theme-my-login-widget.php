<?php

/**
 * Theme My Login Widget
 *
 * @package Theme_My_Login
 * @subpackage Widgets
 */

/**
 * Class used to implement the TML widget.
 *
 * @since 6.0
 */
class Theme_My_Login_Widget extends WP_Widget {

	/**
	 * Construct the instance.
	 *
	 * @since 6.0
	 */
	public function  __construct() {
		parent::__construct( 'theme-my-login', __( 'Theme My Login', 'theme-my-login' ), array(
			'classname'   => 'widget_theme_my_login',
			'description' => __( 'A login form for your site.', 'theme-my-login' ),
		) );
	}

	/**
	 * Render the widget.
	 *
	 * @since 6.0
	 *
	 * @param array $args
	 * @param array $instance
	 */
	public function widget( $args, $instance ) {
		$instance = wp_parse_args( $instance, $this->defaults() );

		$show_widget = ( is_user_logged_in() && 'login' != $instance['action'] ) || ! tml_is_action();

		/**
		 * Filters whether to show the widget or not.
		 *
		 * @since 7.0.5
		 *
		 * @param bool  $show_widget Whether to show the widget or not.
		 * @param array $instance    The widget instance settings.
		 */
		$show_widget = apply_filters( 'tml_show_widget', $show_widget, $instance );

		if ( ! $show_widget ) {
			return;
		}

		if ( is_user_logged_in() ) {
			$title = _x( 'Welcome', 'Howdy', 'theme-my-login' );
		} else {
			$title = tml_get_action_title( $instance['action'] );
		}
		$title = apply_filters( 'widget_title', $title, $instance, $this->id_base );

		echo $args['before_widget'];

		if ( $title ) {
			echo $args['before_title'] . $title . $args['after_title'];
		}

		if ( is_user_logged_in() ) :
			/**
			 * Filters the size of the avatar shwon in the widget when logged in.
			 *
			 * @since 7.0.5
			 *
			 * @param int $avatar_size The size of the avatar shown in the widget when logged in.
			 */
			$avatar_size = apply_filters( 'tml_widget_avatar_size', 64 );

			/**
			 * Filters the links shown in the widget when logged in.
			 *
			 * @since 7.0.5
			 *
			 * @param array $user_links The links shown in the widget when logged in.
			 */
			$user_links = apply_filters( 'tml_widget_user_links', array_filter( array(
				'site_admin' => current_user_can( 'edit_posts' ) ? array(
					'title'  => __( 'Site Admin' ),
					'url'    => admin_url(),
				) : false,
				'dashboard'  => array(
					'title'  => __( 'Dashboard' ),
					'url'    => tml_get_action_url( 'dashboard' ),
				),
				'profile'    => array(
					'title'  => __( 'Edit Profile' ),
					'url'    => admin_url( 'profile.php' ),
				),
				'logout'     => array(
					'title'  => __( 'Log Out' ),
					'url'    => wp_logout_url(),
				),
			) ) );
			?>

			<div class="tml tml-user-panel">
				<?php if ( ! empty( $avatar_size ) ) : ?>
					<div class="tml-user-avatar"><?php echo get_avatar( get_current_user_id(), $avatar_size ); ?></div>
				<?php endif; ?>

				<?php if ( ! empty( $user_links ) ) : ?>
					<ul class="tml-user-links">

					<?php foreach ( $user_links as $name => $user_link ) : ?>

						<li class="tml-user-link-<?php echo esc_attr( $name ); ?>">
							<a href="<?php echo esc_url( $user_link['url'] ); ?>"><?php echo esc_html( $user_link['title'] ); ?></a>
						</li>

					<?php endforeach; ?>

					</ul>
				<?php endif; ?>

				<?php
				/**
				 * Fires at the end of the logged in widget.
				 *
				 * @since 7.0.5
				 *
				 * @param array $instance The widget instance settings.
				 */
				do_action( 'tml_widget_user_panel', $instance );
				?>

			</div>

		<?php else :

			echo tml_shortcode( array(
				'action'      => $instance['action'],
				'show_links'  => $instance['show_links'],
				'redirect_to' => $_SERVER['REQUEST_URI'],
			) );

		endif;

		echo $args['after_widget'];
	}

	/**
	* Displays the widget admin form
	*
	* @since 6.0
	* @access public
	*/
	public function form( $instance ) {
		$instance = wp_parse_args( $instance, $this->defaults() );

		$actions = wp_list_filter( tml_get_actions(), array(
			'show_in_widget' => true,
		) );
		?>

		<p>
			<label for="<?php echo $this->get_field_id( 'action' ); ?>"><?php _e( 'Action:' ); ?>
				<select class="widefat" id="<?php echo $this->get_field_id( 'action' ); ?>" name="<?php echo $this->get_field_name( 'action' ); ?>">
					<?php foreach ( $actions as $action ) : ?>
						<option value="<?php echo esc_attr( $action->get_name() ); ?>"<?php selected( $action->get_name(), $instance['action'] ); ?>><?php echo esc_html( $action->get_title() ); ?></option>
					<?php endforeach; ?>
				</select>
			</label>
		</p>

		<p>
			<input class="checkbox" id="<?php echo $this->get_field_id( 'show_links' ); ?>" name="<?php echo $this->get_field_name( 'show_links' ); ?>" type="checkbox" value="1"<?php checked( ! empty( $instance['show_links'] ) ); ?> />
			<label for="<?php echo $this->get_field_id( 'show_links' ); ?>"><?php _e( 'Show action links?', 'theme-my-login' ); ?></label>
		</p>

		<?php
	}

	/**
	* Updates the widget.
	*
	* @since 6.0
	*
	* @param array $new_instance The new settings.
	* @param array $old_instance The old settings.
	* @return array The updated settings.
	*/
	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		$new_instance = wp_parse_args( (array) $new_instance, $this->defaults() );

		$instance['action']     = sanitize_text_field( $new_instance['action'] );
		$instance['show_links'] = (bool) $new_instance['show_links'];

		return $instance;
	}

	/**
	 * Get the default instance arguments.
	 *
	 * @since 7.0.6
	 *
	 * @return array The default instance arguments.
	 */
	public function defaults() {
		return array(
			'action' => 'login',
			'show_links' => false,
		);
	}

	/**
	 * Register the widget.
	 *
	 * @since 7.0
	 */
	public static function register() {
		register_widget( 'Theme_My_Login_Widget' );
	}
}
