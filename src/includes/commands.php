<?php

/**
 * Theme My Login Command Functions
 *
 * @package Theme_My_Login
 * @subpackage CLI
 */

/**
 * Adds a Theme My Login action menu item.
 *
 * ## OPTIONS
 *
 * <menu>
 * : The name, slug, or term ID for the menu.
 *
 * <action>
 * : The name of the Theme My Login action.
 *
 * [--title=<title>]
 * : Set a custom title for the menu item.
 *
 * [--description=<description>]
 * : Set a custom description for the menu item.
 *
 * [--attr-title=<attr-title>]
 * : Set a custom title attribute for the menu item.
 *
 * [--target=<target>]
 * : Set a custom link target for the menu item.
 *
 * [--classes=<classes>]
 * : Set a custom link classes for the menu item.
 *
 * [--position=<position>]
 * : Specify the position of this menu item.
 *
 * [--parent-id=<parent-id>]
 * : Make this menu item a child of another menu item.
 *
 * ## EXAMPLES
 *
 *     $ wp menu item add-tml-action sidebar-menu login
 *     Success: Menu item added.
 */
WP_CLI::add_command( 'menu item add-tml-action', function( $args, $assoc_args ) {

	list( $menu, $action ) = $args;

	$menu = wp_get_nav_menu_object( $menu );
	if ( ! $menu || is_wp_error( $menu ) ) {
		WP_CLI::error( 'Invalid menu.' );
	}
	if ( ! $action = tml_get_action( $action ) ) {
		WP_CLI::error( 'Invalid action.' );
	}

	$default_args = [
		'position'    => 0,
		'title'       => $action->get_title(),
		'description' => '',
		'parent-id'   => 0,
		'attr-title'  => '',
		'target'      => '',
		'classes'     => '',
		'xfn'         => '',
		'status'      => 'publish',
	];

	$menu_item_args = [];
	foreach ( $default_args as $key => $default_value ) {
		$menu_item_args[ 'menu-item-' . $key ] = \WP_CLI\Utils\get_flag_value( $assoc_args, $key, $default_value );
	}
	$menu_item_args['menu-item-object-id'] = -1;
	$menu_item_args['menu-item-object']    = $action->get_name();
	$menu_item_args['menu-item-type']      = 'tml_action';
	$menu_item_args['menu-item-url']       = $action->get_url();

	$ret = wp_update_nav_menu_item( $menu->term_id, 0, $menu_item_args );
	if ( is_wp_error( $ret ) ) {
		WP_CLI::error( $ret->get_error_message() );
	} elseif ( ! $ret ) {
		WP_CLI::error( "Couldn't add menu item." );
	} else {
		if ( ! is_object_in_term( $ret, 'nav_menu', (int) $menu->term_id ) ) {
			wp_set_object_terms( $ret, array( (int) $menu->term_id ), 'nav_menu' );
		}
		if ( ! empty( $assoc_args['porcelain'] ) ) {
			WP_CLI::line( $ret );
		} else {
			WP_CLI::success( 'Menu item added.' );
		}
	}
});
