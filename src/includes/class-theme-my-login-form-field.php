<?php

/**
 * Theme My Login Form Field Class
 *
 * @package Theme_My_Login
 * @subpackage Forms
 */

/**
 * Class used to implement the form field object.
 *
 * @since 7.0
 */
class Theme_My_Login_Form_Field {

	/**
	 * The field name.
	 *
	 * @var string
	 */
	protected $name;

	/**
	 * The field type.
	 *
	 * @var string
	 */
	protected $type;

	/**
	 * The field value.
	 *
	 * @var string
	 */
	protected $value;

	/**
	 * The field label.
	 *
	 * @var string
	 */
	protected $label;

	/**
	 * The field description.
	 *
	 * @var string
	 */
	protected $description;

	/**
	 * The field error.
	 *
	 * @var string
	 */
	protected $error;

	/**
	 * The field content.
	 *
	 * @var string
	 */
	protected $content;

	/**
	 * The field options.
	 *
	 * @var array
	 */
	protected $options = array();

	/**
	 * The field attributes.
	 *
	 * @var array
	 */
	protected $attributes = array();

	/**
	 * The field classes.
	 *
	 * @var array
	 */
	protected $classes = array();

	/**
	 * The parent form of the field.
	 *
	 * @var Theme_My_Login_Form
	 */
	protected $form;

	/**
	 * The priority of the field within the form.
	 *
	 * @var int
	 */
	protected $priority = 10;

	/**
	 * The arguments used for rendering the field.
	 *
	 * Alternatively, if the field type is "action", this is used as the action arguments.
	 *
	 * @see Theme_My_Login_Form_Field::render()
	 *
	 * @var array
	 */
	public $render_args = array();

	/**
	 * Create a new instance.
	 *
	 * @since 7.0
	 *
	 * @param Theme_My_Login_Form $form The field's parent form.
	 * @param string              $name The field name.
	 * @param array               $args {
	 *     Optional. An array of arguments.
	 *
	 *     @type string $type        The field type.
	 *     @type string $value       The field value.
	 *     @type string $label       The field label.
	 *     @type string $description The field description.
	 *     @type string $error       The field error message.
	 *     @type string $content     The field content. Used if type is set to "custom".
	 *     @type array  $options     The field options. Used if type is set to "dropdown" or "radio-group".
	 *     @type array  $attributes  The field attributes.
	 * }
	 */
	public function __construct( Theme_My_Login_Form $form, $name, $args = array() ) {
		$this->set_form( $form );
		$this->set_name( $name );

		$args = wp_parse_args( $args, array(
			'type'        => 'text',
			'value'       => '',
			'label'       => '',
			'description' => '',
			'error'       => '',
			'content'     => '',
			'options'     => array(),
			'attributes'  => array(),
		) );

		$this->set_type( $args['type'] );
		$this->set_value( $args['value'] );
		$this->set_label( $args['label'] );
		$this->set_description( $args['description'] );
		$this->set_error( $args['error'] );
		$this->set_content( $args['content'] );
		$this->set_options( $args['options'] );

		if ( ! empty( $args['id'] ) ) {
			$this->add_attribute( 'id', $args['id'] );
		}

		if ( ! empty( $args['class'] ) ) {
			$this->add_class( $args['class'] );
		} elseif ( 'hidden' != $this->get_type() ) {
			if ( in_array( $args['type'], array( 'button', 'submit', 'reset' ) ) ) {
				$class = 'tml-button';
			} elseif ( in_array( $args['type'], array( 'checkbox', 'radio', 'radio-group' ) ) ) {
				$class = 'tml-checkbox';
			} else {
				$class = 'tml-field';
			}
			$this->add_class( $class );
		}

		if ( 'checkbox' == $args['type'] && ! empty( $args['checked'] ) ) {
			$this->add_attribute( 'checked', 'checked' );
		}

		foreach ( (array) $args['attributes'] as $key => $value ) {
			$this->add_attribute( $key, $value );
		}

		if ( isset( $args['priority'] ) ) {
			$this->set_priority( $args['priority'] );
		}

		if ( ! empty( $args['render_args'] ) ) {
			$this->render_args = $args['render_args'];
		}
	}

	/**
	 * Get the field name.
	 *
	 * @since 7.0
	 *
	 * @return string The field name.
	 */
	public function get_name() {
		return $this->name;
	}

	/**
	 * Set the field name.
	 *
	 * @since 7.0
	 *
	 * @param string $name The field name.
	 */
	protected function set_name( $name ) {
		$this->name = sanitize_key( $name );
	}

	/**
	 * Get the field type.
	 *
	 * @since 7.0
	 *
	 * @return string The field type.
	 */
	public function get_type() {
		return $this->type;
	}

	/**
	 * Set the field type.
	 *
	 * @since 7.0
	 *
	 * @param string $type The field type.
	 */
	public function set_type( $type ) {
		if ( empty( $type ) ) {
			$type = 'text';
		}
		$this->type = $type;
	}

	/**
	 * Get the field value.
	 *
	 * @since 7.0
	 *
	 * @return string The field value.
	 */
	public function get_value() {
		return $this->value;
	}

	/**
	 * Set the field value.
	 *
	 * @since 7.0
	 *
	 * @param string $value The field value.
	 */
	public function set_value( $value ) {
		$this->value = $value;
	}

	/**
	 * Get the field label.
	 *
	 * @since 7.0
	 *
	 * @return string The field label.
	 */
	public function get_label() {
		/**
		 * Filters the form field label.
		 *
		 * @param string                    $label The field label.
		 * @param Theme_My_Login_Form_Field $field The field object.
		 */
		return apply_filters( 'tml_get_form_field_label', $this->label, $this );
	}

	/**
	 * Set the field label.
	 *
	 * @since 7.0
	 *
	 * @param string $label The field label.
	 */
	public function set_label( $label ) {
		$this->label = $label;
	}

	/**
	 * Get the field description.
	 *
	 * @since 7.0
	 *
	 * @return string The field description.
	 */
	public function get_description() {
		/**
		 * Filters the form field description.
		 *
		 * @param string                    $description The field Description.
		 * @param Theme_My_Login_Form_Field $field       The field object.
		 */
		return apply_filters( 'tml_get_form_field_description', $this->description, $this );
	}

	/**
	 * Set the field description.
	 *
	 * @since 7.0
	 *
	 * @param string $description The field description.
	 */
	public function set_description( $description ) {
		$this->description = $description;
	}

	/**
	 * Get the field error message.
	 *
	 * @since 7.0
	 *
	 * @return string The field error message.
	 */
	public function get_error() {
		return $this->error;
	}

	/**
	 * Set the field error message.
	 *
	 * @since 7.0
	 *
	 * @param string $error The field error message.
	 */
	public function set_error( $error = '' ) {
		$this->error = $error;
	}

	/**
	 * Get the field content.
	 *
	 * @since 7.0
	 *
	 * @return string The field content.
	 */
	public function get_content() {
		if ( is_callable( $this->content ) ) {
			$content = call_user_func_array( $this->content, array( $this ) );
		} else {
			$content = $this->content;
		}

		/**
		 * Filters the form field content.
		 *
		 * @param string                    $content The field content.
		 * @param Theme_My_Login_Form_Field $field   The field object.
		 */
		return apply_filters( 'tml_get_form_field_content', $content, $this );
	}

	/**
	 * Set the field content.
	 *
	 * @since 7.0
	 *
	 * @param string $content The field content or a callable function to generate it.
	 */
	public function set_content( $content = '' ) {
		$this->content = $content;
	}

	/**
	 * Get the field options.
	 *
	 * @since 7.0
	 *
	 * @return array The field options.
	 */
	public function get_options() {
		/**
		 * Filters the form field options.
		 *
		 * @param array                     $options The field options.
		 * @param Theme_My_Login_Form_Field $this    The field object.
		 */
		return apply_filters( 'tml_get_form_field_options', $this->options, $this );
	}

	/**
	 * Set the field options.
	 *
	 * @since 7.0
	 *
	 * @param array $options The field options.
	 */
	public function set_options( $options = array() ) {
		$this->options = (array) $options;
	}

	/**
	 * Get the parent form of the field.
	 *
	 * @since 7.0
	 *
	 * @return Theme_My_Login_Form The parent form of the field.
	 */
	public function get_form() {
		return $this->form;
	}

	/**
	 * Set the parent form of the field.
	 *
	 * @since 7.0
	 *
	 * @param Theme_My_Login_Form $form The parent form of the field.
	 */
	public function set_form( Theme_My_Login_Form $form ) {
		$this->form = $form;
	}

	/**
	 * Add an attribute.
	 *
	 * @since 7.0
	 *
	 * @param string $key   The attribute key.
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
	 * @return array The field attributes.
	 */
	public function get_attributes() {
		return $this->attributes;
	}

	/**
	 * Add a class.
	 *
	 * @since 7.0.13
	 *
	 * @param array|string $class The class or an array of classes.
	 */
	public function add_class( $class ) {
		if ( ! is_array( $class ) ) {
			$class = explode( ' ', $class );
		}
		$this->classes = array_unique( array_merge( $this->classes, $class ) );
	}

	/**
	 * Remove a class.
	 *
	 * @since 7.0.13
	 *
	 * @param string $class The class.
	 */
	public function remove_class( $class ) {
		$classes = array_flip( $this->classes );
		if ( isset( $classes[ $class ] ) ) {
			unset( $classes[ $class ] );
			$this->classes = array_keys( $classes );
		}
	}

	/**
	 * Determine if the field has a given class.
	 *
	 * @since 7.0.13
	 *
	 * @param string $class The class.
	 * @return bool True if the field has the given class, false if not.
	 */
	public function has_class( $class ) {
		return in_array( $class, $this->classes );
	}

	/**
	 * Get all classes.
	 *
	 * @since 7.0.13
	 *
	 * @return array The field classes.
	 */
	public function get_classes() {
		return $this->classes;
	}

	/**
	 * Set the priority of the field.
	 *
	 * @since 7.0
	 *
	 * @param int $priority The field priority.
	 */
	public function set_priority( $priority ) {
		$this->priority = (int) $priority;
	}

	/**
	 * Get the priority of the field.
	 *
	 * @since 7.0
	 *
	 * @return int The field priority.
	 */
	public function get_priority() {
		return $this->priority;
	}

	/**
	 * Render the field.
	 *
	 * @since 7.0
	 *
	 * @param array $args {
	 *     Optional. An array of arguments for rendering a form field.
	 *
	 *     @type string $before         The content to render before the field.
	 *     @type string $after          The content to render after the field.
	 *     @type string $control_before The content to render before the control.
	 *     @type string $control_after  The content to render after the control.
	 * }
	 */
	public function render( $args = array() ) {
		$is_hidden = ( 'hidden' == $this->get_type() );

		if ( 'action' == $this->get_type() ) {
			return tml_buffer_action_hook( $this->get_name(), $this->render_args );
		}

		$defaults = wp_parse_args( $this->render_args, array(
			'before'         => $is_hidden ? '' : '<div class="tml-field-wrap tml-%s-wrap">',
			'after'          => $is_hidden ? '' : '</div>',
			'control_before' => '',
			'control_after'  => '',
		) );

		/**
		 * Fires before a form field is rendered.
		 *
		 * @since 7.0.13
		 *
		 * @param string                    $form_name  The form name.
		 * @param string                    $field_name The field name.
		 * @param Theme_My_Login_Form_Field $field      The field object.
		 */
		do_action( 'tml_render_form_field', $this->form->get_name(), $this->name, $this );

		$args = wp_parse_args( $args, $defaults );

		$output = '';

		if ( ! empty( $args['before'] ) ) {
			$output .= sprintf( $args['before'], $this->get_name() ) . "\n";
		}

		/**
		 * Filter the content before the field.
		 *
		 * @since 7.0.13
		 *
		 * @param string                    $output     The output.
		 * @param string                    $form_name  The form name.
		 * @param string                    $field_name The field name
		 * @param Theme_My_Login_Form_Field $field      The form object.
		 */
		$output = apply_filters( 'tml_before_form_field', $output, $this->form->get_name(), $this->name, $this );

		$attributes = '';
		foreach ( $this->get_attributes() as $key => $value ) {
			$attributes .= ' ' . $key . '="' . esc_attr( $value ) . '"';
		}
		if ( $classes = $this->get_classes() ) {
			$attributes .= ' class="' . implode( ' ', $classes ) . '"';
		}

		$label = '';
		if ( $this->get_label() ) {
			if ( $this->get_attribute( 'id' ) ) {
				$label = sprintf(
					'<label class="tml-label" for="%1$s">%2$s</label>',
					$this->get_attribute( 'id' ),
					$this->get_label()
				) . "\n";
			} else {
				$label = sprintf(
					'<span class="tml-label">%s</span>',
					$this->get_label()
				) . "\n";
			}
		}

		$error = '';
		if ( $this->get_error() ) {
			$error = '<span class="tml-error">' . $this->get_error() . '</span>';
		}

		switch ( $this->get_type() ) {
			case 'custom' :
				$output .= $label;
				$output .= $this->get_content();
				break;

			case 'checkbox' :
				$output .= $args['control_before'];
				$output .= '<input name="' . $this->get_name() . '" type="checkbox" value="' . esc_attr( $this->get_value() ) . '"' . $attributes . ">\n";
				$output .= $args['control_after'];
				$output .= $label;
				break;

			case 'radio-group' :
				$output .= $label;
				$output .= $error;
				$output .= $args['control_before'];

				$options = array();
				foreach ( $this->get_options() as $value => $label ) {
					$id = $this->get_name() . '_' . $value;

					$option = '<input name="' . $this->get_name() . '" id="' . $id . '" type="radio" value="' . esc_attr( $value ) . '"' . $attributes;
					if ( $this->get_value() == $value ) {
						$option .= ' checked="checked"';
					}
					$option .= '>' . "\n";
					$option .= '<label class="tml-label" for="' . $id . '">' . $label . "</label>\n";

					$options[] = $option;
				}
				$output .= implode( '<br />', $options );

				$output .= $args['control_after'];
				break;

			case 'dropdown' :
				$output .= $label;
				$output .= $error;
				$output .= $args['control_before'];
				$output .= '<select name="' . $this->get_name() . '"' . $attributes . ">\n";
				foreach ( $this->get_options() as $value => $option ) {
					$output .= '<option value="' . esc_attr( $value ) . '"';
					if ( $this->get_value() == $value ) {
						$output .= ' selected="selected"';
					}
					$output .= '>' . esc_html( $option ) . "</option>\n";
				}
				$output .= "</select>\n";
				$output .= $args['control_after'];
				break;

			case 'textarea' :
				$output .= $label;
				$output .= $args['control_before'];
				$output .= '<textarea name="' . $this->get_name() . '"' . $attributes . '>' . $this->get_value() . "</textarea>\n";
				$output .= $args['control_after'];
				break;

			case 'button' :
			case 'submit' :
				$output .= $args['control_before'];
				$output .= '<button name="' . $this->get_name() . '" type="' . $this->get_type() . '"' . $attributes . '>' . $this->get_value() . "</button>\n";
				$output .= $args['control_after'];
				break;

			default :
				$output .= $label;
				$output .= $error;
				$output .= $args['control_before'];
				$output .= '<input name="' . $this->get_name() . '" type="' . $this->get_type() . '" value="' . esc_attr( $this->get_value() ) . '"' . $attributes . ">\n";
				$output .= $args['control_after'];
		}

		if ( $this->get_description() ) {
			$output .= '<span class="tml-description">' . $this->get_description() . "</span>\n";
		}

		/**
		 * Filter the content after the field.
		 *
		 * @since 7.0.13
		 *
		 * @param string                    $output     The output.
		 * @param string                    $form_name  The form name.
		 * @param string                    $field_name The field name
		 * @param Theme_My_Login_Form_Field $field      The form object.
		 */
		$output = apply_filters( 'tml_after_form_field', $output, $this->form->get_name(), $this->name, $this );

		if ( ! empty( $args['after'] ) ) {
			$output .= $args['after'] . "\n";
		}

		return $output;
	}
}
