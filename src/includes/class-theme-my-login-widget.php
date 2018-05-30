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
		$title = apply_filters( 'widget_title', empty( $instance['title'] ) ? tml_get_action_title( $instance['action'] ) : $instance['title'], $instance, $this->id_base );

		if ( is_user_logged_in() ) {
			return;
		}

		if ( tml_is_action() ) {
			return;
		}

		echo $args['before_widget'];

		if ( $title ) {
			echo $args['before_title'] . $title . $args['after_title'];
		}

		echo tml_shortcode( array(
			'action'      => $instance['action'],
			'show_links'  => $instance['show_links'],
			'redirect_to' => $_SERVER['REQUEST_URI'],
		) );

		echo $args['after_widget'];
	}

	/**
	* Displays the widget admin form
	*
	* @since 6.0
	* @access public
	*/
	public function form( $instance ) {
		$instance = wp_parse_args( $instance, array(
			'title'      => '',
			'action'     => 'login',
			'show_links' => true,
		) );

		$actions = wp_list_filter( tml_get_actions(), array(
			'show_in_widget' => true,
		) );
		?>

		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?>
				<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $instance['title'] ); ?>" />
			</label>
		</p>

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

		$new_instance = wp_parse_args( (array) $new_instance, array(
			'title'      => '',
			'action'     => 'login',
			'show_links' => false,
		) );

		$instance['title']      = sanitize_text_field( $new_instance['title']  );
		$instance['action']     = sanitize_text_field( $new_instance['action'] );
		$instance['show_links'] = (bool) $new_instance['show_links'];

		return $instance;
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
