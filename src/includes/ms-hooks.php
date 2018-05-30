<?php

/**
 * Theme My Login Multisite Hooks
 *
 * @package Theme_My_Login
 * @subpackage Multisite
 */

/**
 * Add Actions
 */

// Actions and Forms
add_action( 'init', 'tml_ms_register_default_actions', 0 );
add_action( 'init', 'tml_ms_register_default_forms',   0 );

// Registration
add_action( 'wpmu_activate_user', 'tml_handle_auto_login', 10, 2 );
add_action( 'wpmu_activate_blog', 'tml_handle_auto_login', 10, 2 );

/**
 * Add Filters
 */

// Shortcodes
add_filter( 'tml_shortcode', 'tml_ms_filter_signup_shortcode',     10, 3 );
add_filter( 'tml_shortcode', 'tml_ms_filter_activation_shortcode', 10, 3 );

// URLs
add_filter( 'network_site_url', 'tml_filter_site_url', 10, 3 );

// Passwords
add_filter( 'wp_pre_insert_user_data',   'tml_ms_filter_pre_insert_user_data', 10, 1 );
add_filter( 'update_welcome_email',      'tml_ms_filter_welcome_email',        10, 4 );
add_filter( 'update_welcome_user_email', 'tml_ms_filter_welcome_user_email',   10, 3 );
