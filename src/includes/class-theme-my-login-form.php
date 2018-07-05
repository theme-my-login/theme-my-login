<?php

/**
 * Theme My Login Form Class
 *
 * @package Theme_My_Login
 * @subpackage Forms
 */

/**
 * Class used to implement the form object.
 *
 * @since 7.0
 */
class Theme_My_Login_Form {

	/**
	 * The form name.
	 *
	 * @var string
	 */
	protected $name;

	/**
	 * The form action.
	 *
	 * @var string
	 */
	protected $action;

	/**
	 * The form method.
	 *
	 * @var string
	 */
	protected $method;

	/**
	 * The form fields.
	 *
	 * @var array
	 */
	protected $fields = array();

	/**
	 * The form attributes.
	 *
	 * @var array
	 */
	protected $attributes = array();

	/**
	 * The form errors.
	 *
	 * @var WP_Error
	 */
	protected $errors;

	/**
	 * The form links.
	 *
	 * @var array
	 */
	protected $links = array();

	/**
	 * The arguments used for rendering the field.
	 *
	 * @see Theme_My_Login_Form::render()
	 *
	 * @var array
	 */
	public $render_args = array();

	/**
	 * Create a new instance.
	 *
	 * @since 7.0
	 *
	 * @param string $name The form name.
	 * @param array  $args {
	 *     Optional. An array of arguments.
	 *
	 *     @type string $action The form action. Default empty.
	 *     @type string $method The form method. Default 'post'.
	 * }
	 */
	public function __construct( $name, $args = array() ) {

		$this->set_name( $name );

		$args = wp_parse_args( $args, array(
			'action' => '',
			'method' => 'post',
		) );

		$this->set_action( $args['action'] );
		$this->set_method( $args['method'] );

		$this->errors = new WP_Error;

		// Add the default links
		foreach ( tml_get_actions() as $action ) {
			if ( $action->show_on_forms && $action->get_name() != $this->get_name() ) {
				$this->add_link( $action->get_name(), array(
					'text' => true === $action->show_on_forms ? $action->get_title() : $action->show_on_forms,
					'url'  => $action->get_url(),
				) );
			}
		}

		if ( ! empty( $args['render_args'] ) ) {
			$this->render_args = $args['render_args'];
		}
	}

	/**
	 * Get the form name.
	 *
	 * @since 7.0
	 *
	 * @return string The form name.
	 */
	public function get_name() {
		return $this->name;
	}

	/**
	 * Set the form name.
	 *
	 * @since 7.0
	 *
	 * @param string $name The form name.
	 */
	protected function set_name( $name ) {
		$this->name = sanitize_key( $name );
	}

	/**
	 * Get the form action.
	 *
	 * @since 7.0
	 *
	 * @return string The form action.
	 */
	public function get_action() {
		/**
		 * Filters the form action.
		 *
		 * @since 7.0
		 *
		 * @param string              $action The form action.
		 * @param Theme_My_Login_Form $this   The form object.
		 */
		return apply_filters( 'tml_get_form_action', $this->action, $this );
	}

	/**
	 * Set the form action.
	 *
	 * @since 7.0
	 *
	 * @param string $action The form action.
	 */
	public function set_action( $action ) {
		$this->action = $action;
	}

	/**
	 * Get the form method.
	 *
	 * @since 7.0
	 *
	 * @return string The form method.
	 */
	public function get_method() {
		/**
		 * Filters the form method.
		 *
		 * @since 7.0
		 *
		 * @param string              $method The form method.
		 * @param Theme_My_Login_Form $this   The form object.
		 */
		return apply_filters( 'tml_get_form_method', $this->method, $this );
	}

	/**
	 * Set the form method.
	 *
	 * @since 7.0
	 *
	 * @param string $method The form method.
	 */
	public function set_method( $method ) {
		$method = strtolower( $method );
		if ( ! in_array( $method, array( 'get', 'post' ) ) ) {
			$method = 'post';
		}
		$this->method = $method;
	}

	/**
	 * Add an attribute.
	 *
	 * @since 7.0
	 *
	 * @param string $key The attribute key.
	 * @param string $value The attribute value.
	 */
	public function add_attribute( $key, $value = null ) {
		$this->attributes[ $key ] = $value;
	}

	/**
	 * Remove an attribute.
	 *
	 * @since 7.0
	 *
	 * @param string $key The attribute key.
	 */
	public function remove_attribute( $key ) {
		if ( isset( $this->attributes[ $key ] ) ) {
			unset( $this->attributes[ $key ] );
		}
	}

	/**
	 * Get an attribute.
	 *
	 * @since 7.0
	 *
	 * @param string $key The attribute key.
	 * @return string|bool The attribute value or false if it doesn't exist.
	 */
	public function get_attribute( $key ) {
		if ( isset( $this->attributes[ $key ] ) ) {
			return $this->attributes[ $key ];
		}
		return false;
	}

	/**
	 * Get all attributes.
	 *
	 * @since 7.0
	 *
	 * @return array The form attributes.
	 */
	public function get_attributes() {
		return $this->attributes;
	}

	/**
	 * Add a field.
	 *
	 * @since 7.0
	 *
	 * @param TML_Form_Field $field The field object.
	 */
	public function add_field( Theme_My_Login_Form_Field $field ) {

		$this->fields[ $field->get_name() ] = $field;

		return $field;
	}

	/**
	 * Remove a field.
	 *
	 * @since 7.0
	 *
	 * @param string|Theme_My_Login_Form_Field $field The field name or object.
	 */
	public function remove_field( $field ) {
		if ( $field instanceof Theme_My_Login_Form_Field ) {
			unset( $this->fields[ $field->get_name() ] );
		} else {
			unset( $this->fields[ $field ] );
		}
	}

	/**
	 * Get a field.
	 *
	 * @since 7.0
	 *
	 * @param string $field The field name.
	 * @return Theme_My_Login_Form_Field|bool The field object if it exists or false otherwise.
	 */
	public function get_field( $field ) {
		if ( isset( $this->fields[ $field ] ) ) {
			return $this->fields[ $field ];
		}
		return false;
	}

	/**
	 * Get all fields.
	 *
	 * @since 7.0
	 *
	 * @return array The form fields.
	 */
	public function get_fields() {
		$priorities    = array();
		$sorted_fields = array();

		// Prioritize the fields
		foreach( $this->fields as $field ) {
			$priority = $field->get_priority();
			if ( ! isset( $priorities[ $priority ] ) ) {
				$priorities[ $priority ] = array();
			}
			$priorities[ $priority ][] = $field;
		}

		ksort( $priorities );

		// Sort the fields
		foreach ( $priorities as $priority => $fields ) {
			foreach ( $fields as $field ) {
				$sorted_fields[] = $field;
			}
		}
		unset( $priorities );

		return $sorted_fields;
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
	public function add_error( $code, $message, $severity = 'error' ) {
		$this->errors->add( $code, $message, $severity );
	}

	/**
	 * Get the errors.
	 *
	 * @since 7.0
	 *
	 * @return WP_Error The form errors.
	 */
	public function get_errors() {
		return $this->errors;
	}

	/**
	 * Set the errors.
	 *
	 * @since 7.0
	 *
	 * @param WP_Error $errors The errors.
	 */
	public function set_errors( WP_Error $errors ) {
		$this->errors = $errors;
	}

	/**
	 * Determine if the form has errors.
	 *
	 * @since 7.0
	 *
	 * @return bool True if the form has errors, false if not.
	 */
	public function has_errors() {
		return (bool) $this->errors->get_error_code();
	}

	/**
	 * Render the errors.
	 *
	 * @since 7.0
	 *
	 * @return string The errors markup.
	 */
	public function render_errors() {

		if ( ! $this->has_errors() ) {
			return;
		}

		$errors   = array();
		$messages = array();

		foreach ( $this->errors->get_error_codes() as $code ) {

			$severity = $this->errors->get_error_data( $code );

			foreach ( $this->errors->get_error_messages( $code ) as $error ) {
				if ( 'message' == $severity ) {
					$messages[] = $error;
				} else {
					$errors[] = $error;
				}
			}
		}

		$output = '';

		if ( ! empty( $errors ) ) {
			$output .= sprintf( '<ul class="tml-errors"><li class="tml-error">%s</li></ul>',
				apply_filters( 'login_errors', implode( "</li>\n<li class=\"tml-error\">", $errors ) )
			);
		}

		if ( ! empty( $messages ) ) {
			$output .= sprintf( '<ul class="tml-messages"><li class="tml-message">%s</li></ul>',
				apply_filters( 'login_messages', implode( "</li>\n<li class=\"tml-message\">", $messages ) )
			);
		}

		return $output;
	}

	/**
	 * Add a link.
	 *
	 * @since 7.0
	 *
	 * @param string $link The link name.
	 * @param array  $args {
	 *     Optional. An array of arguments for adding a link.
	 *
	 *     @type string $text The link text.
	 *     @type string $url  The link URL.
	 * }
	 */
	public function add_link( $link, $args = array() ) {
		$args = wp_parse_args( $args, array(
			'text' => '',
			'url'  => '',
		) );

		$link = sanitize_key( $link );

		$this->links[ $link ] = $args;
	}

	/**
	 * Remove a link.
	 *
	 * @since 7.0
	 *
	 * @param string $link The link name.
	 */
	public function remove_link( $link ) {
		unset( $this->links[ $link ] );
	}

	/**
	 * Get a link.
	 *
	 * @since 7.0
	 *
	 * @param string $link The link name.
	 * @return array|bool The link data if it exists or false otherwise.
	 */
	public function get_link( $link ) {
		if ( isset( $this->links[ $link ] ) ) {
			return $this->links[ $link ];
		}
		return false;
	}

	/**
	 * Get all links.
	 *
	 * @since 7.0
	 *
	 * @return array The form links.
	 */
	public function get_links() {
		/**
		 * Filter the form links.
		 *
		 * @since 7.0
		 *
		 * @param array               $links The form links.
		 * @param Theme_My_Login_Form $this  The form object.
		 */
		return apply_filters( 'tml_get_form_links', $this->links, $this );
	}

	/**
	 * Render the links.
	 *
	 * @since 7.0
	 *
	 * @return string The links markup.
	 */
	public function render_links() {

		if ( ! $links = $this->get_links() ) {
			return;
		}

		$output = '<ul class="tml-links">';

		foreach ( $links as $name => $link ) {
			$output .= sprintf( '<li class="tml-%s-link"><a href="%s">%s</a></li>',
				esc_attr( $name ),
				esc_url( $link['url'] ),
				esc_html( $link['text'] )
			);
		}

		$output .= '</ul>';

		return $output;
	}

	/**
	 * Render the form.
	 *
	 * @since 7.0
	 *
	 * @param array $args {
	 *     Optional. An array of arguments for rendering a form.
	 *
	 *     @type string $container       The form container element.
	 *     @type string $container_class The form container class.
	 *     @type string $container_id    The form container ID.
	 *     @type string $before          The content to render before the form.
	 *     @type string $after           The content to render after the form.
	 * }
	 * @return string The form markup.
	 */
	public function render( $args = array() ) {
		$defaults = wp_parse_args( $this->render_args, array(
			'container'       => 'div',
			'container_class' => 'tml tml-%s',
			'container_id'    => '',
			'before'          => '',
			'after'           => '',
			'show_links'      => true,
		) );

		$args = wp_parse_args( $args, $defaults );

		$output = $args['before'];

		if ( ! empty( $args['container'] ) ) {
			$output .= '<' . $args['container'];
			if ( ! empty( $args['container_id'] ) ) {
				$output .= ' id="' . esc_attr( sprintf( $args['container_id'], $this->name ) ) . '"';
			}
			if ( ! empty( $args['container_class'] ) ) {
				$output .= ' class="' . esc_attr( sprintf( $args['container_class'], $this->name ) ) . '"';
			}
			$output .= ">\n";
		}

		$output .= $this->render_errors();

		$output .= '<form name="' . esc_attr( $this->get_name() ) . '" action="' . esc_url( $this->get_action() ) . '" method="' . esc_attr( $this->get_method() ) . '"';
		foreach ( $this->get_attributes() as $key => $value ) {
			$output .= ' ' . $key . '="' . esc_attr( $value ) . '"';
		}
		$output .= ">\n";

		foreach ( $this->get_fields() as $field ) {
			$output .= $field->render() . "\n";
		}

		$output .= "</form>\n";

		if ( $args['show_links'] ) {
			$output .= $this->render_links();
		}

		if ( ! empty( $args['container'] ) ) {
			$output .= '</' . $args['container'] . ">\n";
		}

		$output .= $args['after'];

		return $output;
	}
}
