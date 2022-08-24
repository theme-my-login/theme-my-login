<?php

/**
 * Theme My Login Hooks
 *
 * @package Theme_My_Login
 * @subpackage Core
 */

/**
 * Add Actions
 */

// Actions and Forms
add_action( 'init', 'tml_register_default_actions', 0 );
add_action( 'init', 'tml_register_default_forms',   0 );

// Rewrite
add_action( 'init', 'tml_add_rewrite_tags'  );
add_action( 'init', 'tml_add_rewrite_rules' );

// Widgets
add_action( 'widgets_init', 'Theme_My_Login_Widget::register' );

// Request
add_action( 'parse_request', 'tml_parse_request' );

// Query
add_action( 'parse_query', 'tml_parse_query' );

// Pages
add_action( 'wp', 'tml_remove_default_actions_and_filters' );

// Template
add_action( 'template_redirect',  'tml_action_handler',   0 );
add_action( 'wp_enqueue_scripts', 'tml_enqueue_styles',  10 );
add_action( 'wp_enqueue_scripts', 'tml_enqueue_scripts', 10 );
add_action( 'wp_head',            'tml_do_login_head',   10 );
add_action( 'wp_footer',          'tml_do_login_footer', 10 );

// Registration
add_action( 'pre_user_login',    'tml_set_user_login'        );
add_action( 'register_new_user', 'tml_set_new_user_password' );
add_action( 'register_new_user', 'tml_handle_auto_login'     );

add_action( 'register_new_user',      'tml_send_new_user_notifications', 10, 1 );
add_action( 'edit_user_created_user', 'tml_send_new_user_notifications', 10, 2 );

remove_action( 'register_new_user',      'wp_send_new_user_notifications' );
remove_action( 'edit_user_created_user', 'wp_send_new_user_notifications' );

// Activation
add_action( 'tml_activate', 'tml_flush_rewrite_rules' );

// Deactivation
add_action( 'tml_deactivate', 'tml_flush_rewrite_rules' );

/**
 * Add Filters
 */

// Pages
add_filter( 'the_posts',          'tml_the_posts',                 10, 2 );
add_filter( 'page_template',      'tml_page_template',             10, 3 );
add_filter( 'body_class',         'tml_body_class',                10, 2 );
add_filter( 'get_edit_post_link', 'tml_filter_get_edit_post_link', 10, 2 );
add_filter( 'comments_array',     'tml_filter_comments_array',     10, 1 );

// URLs
add_filter( 'site_url',         'tml_filter_site_url',         10, 3 );
add_filter( 'network_site_url', 'tml_filter_site_url',         10, 3 );
add_filter( 'logout_url',       'tml_filter_logout_url',       10, 2 );
add_filter( 'lostpassword_url', 'tml_filter_lostpassword_url', 10, 2 );

// Authentication
add_filter( 'authenticate', 'tml_enforce_login_type', 20, 3 );
if ( tml_is_username_login_type() ) {
	remove_filter( 'authenticate', 'wp_authenticate_email_password', 20 );
} elseif ( tml_is_email_login_type() ) {
	remove_filter( 'authenticate', 'wp_authenticate_username_password', 20 );
}

// Registration
if ( ! tml_is_wp_login() ) {
	add_filter( 'registration_errors', 'tml_validate_new_user_password', 10 );
}
add_filter( 'tml_registration_redirect', 'tml_registration_redirect', 10, 2 );

// Notifications
add_filter( 'wp_new_user_notification_email', 'tml_add_password_notice_to_new_user_notification_email' );

// Customizer
add_filter( 'customize_nav_menu_available_item_types', 'tml_filter_customize_nav_menu_available_item_types', 10, 1 );
add_filter( 'customize_nav_menu_available_items',      'tml_filter_customize_nav_menu_available_items',      10, 4 );

// Nav menus
add_filter( 'wp_setup_nav_menu_item', 'tml_setup_nav_menu_item'       );
add_filter( 'nav_menu_css_class',     'tml_nav_menu_css_class', 10, 2 );

// Extensions
add_filter( 'plugins_api',                           'tml_add_extension_data_to_plugins_api',       10, 3 );
add_filter( 'pre_set_site_transient_update_plugins', 'tml_add_extension_data_to_plugins_transient', 10, 1 );
