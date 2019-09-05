<?php

/**
 * Theme My Login Nav Menu Edit Walker
 *
 * @package Theme_My_Login_Restrictions
 * @subpackage Walker
 */

class Theme_My_Login_Walker_Nav_Menu_Edit extends Walker_Nav_Menu_Edit {

	/**
	 * Start the element output
	 *
	 * @since 7.0
	 *
	 * @param string $output The current output of the walker.
	 * @param object $item   The menu item.
	 * @param int    $depth  The current walker depth.
	 * @param array  $args   An array of arguments for walking the tree.
	 * @param int    $id     The nav menu ID.
	 */
	function start_el( &$output, $item, $depth = 0, $args = array(), $id = 0 ) {
		$item_output = '';

		// Get the item content from the parent class
		parent::start_el( $item_output, $item, $depth, $args, $id );

		// Start an output buffer
		ob_start();

		/**
		 * Fires just before the move field of a nav menu item in the menu editor.
		 *
		 * @since unknown
		 *
		 * @param int    $item_id The item ID.
		 * @param object $item    The nav menu item.
		 * @param int    $depth   The current walker depth.
		 * @param array  $args    An array of arguments for walking the tree.
		 */
		do_action( 'wp_nav_menu_item_custom_fields', $item->ID, $item, $depth, $args );

		// Get the contents of the output buffer
		$custom_fields = ob_get_clean();

		// Append the contents of the output buffer to the nav menu item and
		// append that to the walker output
		$output .= preg_replace( '/(?=<(fieldset|p)[^>]+class="[^"]*field-move)/',
			$custom_fields,
			$item_output
		);
	}
}
