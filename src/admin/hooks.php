<?php

/**
 * Theme My Login Admin Hooks
 *
 * @package Theme_My_Login
 * @subpackage Administration
 */

/**
 * Add actions
 */

// General
add_action( 'admin_enqueue_scripts', 'tml_admin_enqueue_style_and_scripts' );

// Notices
add_action( 'admin_notices',              'tml_admin_notices'             );
add_action( 'wp_ajax_tml-dismiss-notice', 'tml_admin_ajax_dismiss_notice' );

// Extensions
add_action( 'admin_init', 'tml_admin_handle_extension_licenses'     );
add_action( 'admin_init', 'tml_admin_check_extension_licenses'      );
add_action( 'admin_init', 'tml_admin_add_extension_update_messages' );

// Settings
if ( is_multisite() ) {
	add_action( 'network_admin_menu', 'tml_admin_add_menu_items'    );
	add_action( 'admin_init',         'tml_admin_register_settings' );
	add_action( 'admin_init',         'tml_admin_save_ms_settings'  );
} else {
	add_action( 'admin_menu', 'tml_admin_add_menu_items'    );
	add_action( 'admin_init', 'tml_admin_register_settings' );
}
add_action( 'current_screen', 'tml_admin_add_settings_help_tabs' );

// Update
add_action( 'admin_init', 'tml_admin_update' );

// Nav menus
add_action( 'admin_head-nav-menus.php', 'tml_admin_add_nav_menu_meta_box', 10 );

// AJAX
add_action( 'wp_ajax_tml-activate-extension-license',   'tml_admin_ajax_activate_extension_license' );
add_action( 'wp_ajax_tml-deactivate-extension-license', 'tml_admin_ajax_deactivate_extension_license' );

/**
 * Add filters
 */

// General
add_filter( 'plugin_action_links', 'tml_admin_filter_plugin_action_links', 10, 4 );
