<?php

/**
 * Theme My Login Admin Functions
 *
 * @package Theme_My_Login
 * @subpackage Functions
 */

/**
 * Get the Theme My Login Admin instance.
 *
 * @since 7.0
 *
 * @return Theme_My_Login_Admin
 */
function theme_my_login_admin() {
	return Theme_My_Login_Admin::get_instance();
}

/**
* Determine if the current page is a TML page.
*
* @since 7.0
*
* @param string $page The page name.
* @return boolean True if the current page is the specified page, false if not.
*/
function tml_admin_is_plugin_page( $page = '' ) {
	global $plugin_page;

	if ( ! empty( $page ) ) {
		return ( "theme-my-login-$page" == $plugin_page ) || ( "tml-$page" == $plugin_page );
	}

	return ( strpos( $plugin_page, 'theme-my-login' ) === 0 ) || ( strpos( $plugin_page, 'tml-' ) === 0 );
}

/**
 * Add an admin page.
 *
 * @since 7.0
 *
 * @see Theme_My_Login_Admin::add_menu_item()
 *
 * @param array $args An array of arguments for adding an admin page.
 */
function tml_admin_add_menu_item( $args = array() ) {
	theme_my_login_admin()->add_menu_item( $args );
}

/**
 * Register the admin pages.
 *
 * @since 7.0
 */
function tml_admin_add_menu_items() {

	// Bail if multisite and not in the network admin
	if ( is_multisite() && ! is_network_admin() ) {
		return;
	}

	// Add the main menu item
	tml_admin_add_menu_item( array(
		'page_title'  => esc_html__( 'Theme My Login Settings', 'theme-my-login' ),
		'menu_title'  => esc_html__( 'Theme My Login',          'theme-my-login' ),
		'menu_slug'   => 'theme-my-login',
		'menu_icon'   => 'data:image/svg+xml;base64,' . base64_encode(
			file_get_contents( THEME_MY_LOGIN_PATH . 'admin/assets/images/logo.svg' )
		),
		'parent_slug' => false,
	) );

	// Add the submenu item
	tml_admin_add_menu_item( array(
		'page_title'  => esc_html__( 'Theme My Login Settings', 'theme-my-login' ),
		'menu_title'  => esc_html__( 'General',                 'theme-my-login' ),
		'menu_slug'   => 'theme-my-login',
		'parent_slug' => 'theme-my-login',
	) );

	$has_licenses = false;

	// Add the extension menu items
	foreach ( tml_get_extensions() as $extension ) {
		$args = $extension->get_settings_page_args();
		if ( ! empty( $args ) ) {
			tml_admin_add_menu_item( $args );
		}
		if ( $extension->get_license_key_option() ) {
			$has_licenses = true;
		}
	}

	if ( $has_licenses ) {
		// Add the licenses menu item
		tml_admin_add_menu_item( array(
			'page_title'  => esc_html__( 'Theme My Login Licenses', 'theme-my-login' ),
			'menu_title'  => esc_html__( 'Licenses',                'theme-my-login' ),
			'menu_slug'   => 'theme-my-login-licenses',
			'parent_slug' => 'theme-my-login',
		) );
		add_settings_section( 'tml_settings_licenses', '', '__return_null', 'theme-my-login-licenses' );
	}

	// Add the extensions menu item
	tml_admin_add_menu_item( array(
		'page_title'  => esc_html__( 'Theme My Login Extensions', 'theme-my-login' ),
		'menu_title'  => esc_html__( 'Extensions',                'theme-my-login' ),
		'menu_slug'   => 'theme-my-login-extensions',
		'parent_slug' => 'theme-my-login',
		'function'    => 'tml_admin_extensions_page',
	) );
}

/**
* Enqueue admin scripts.
*
* @since 7.0
*/
function tml_admin_enqueue_style_and_scripts() {
	$suffix = SCRIPT_DEBUG ? '' : '.min';

	wp_enqueue_style( 'theme-my-login-admin', THEME_MY_LOGIN_URL . "admin/assets/styles/theme-my-login-admin$suffix.css", array(), THEME_MY_LOGIN_VERSION );

	wp_enqueue_script( 'theme-my-login-admin', THEME_MY_LOGIN_URL . "admin/assets/scripts/theme-my-login-admin$suffix.js", array( 'jquery', 'postbox' ), THEME_MY_LOGIN_VERSION );
	wp_localize_script( 'theme-my-login-admin', 'tmlAdmin', array(
		'ajaxUrl'         => admin_url( 'admin-ajax.php' ),
		'interimLoginUrl' => site_url( add_query_arg( array(
			'interim-login' => 1,
			'wp_lang'       => get_user_locale(),
		), 'wp-login.php' ), 'login' ),
	) );
}

/**
 * Display admin notices.
 *
 * @since 7.0
 */
function tml_admin_notices() {
	global $plugin_page;

	$screen = get_current_screen();

	// Bail if not on Dashboard or a TML page
	if ( 'dashboard' != $screen->id && 'theme-my-login' != $screen->parent_base ) {
		return;
	}

	// Bail if the user cannot activate plugins
	if ( ! current_user_can( 'activate_plugins' ) ) {
		return;
	}

	$is_pre_7 = ( $previous_version = tml_get_previous_version() ) && version_compare( $previous_version, '7.0', '<' );

	if ( 'theme-my-login-extensions' == $plugin_page && $is_pre_7 ) {
		?>

		<div class="notice notice-info">
			<p><?php _e( 'As a token of our gratitude, we would like to offer your an incentive for upgrading Theme My Login to version 7.0. For a limited time, we are offering a <strong>20% discount</strong> when you use the code <strong>SAVINGFACE</strong> at checkout. Act now - this offer won\'t last!', 'theme-my-login' ); ?></p>
		</div>

		<?php
	}

	$response = tml_admin_get_extensions_feed();
	if ( ! empty( $response ) && ! is_wp_error( $response ) ) {
		$extension = reset( $response );

		$notice_key = 'new_extension-' . $extension->info->slug;

		if ( ! in_array( $notice_key, get_site_option( '_tml_dismissed_notices', array() ) ) ) : ?>

		<div class="notice notice-info tml-notice is-dismissible" data-notice="<?php echo $notice_key; ?>" data-nonce="<?php echo wp_create_nonce( $notice_key ); ?>">
			<?php echo implode( "\n", array(
				'<p>' . __( 'A new <strong>Theme My Login</strong> extension is available!', 'theme-my-login' ) . '</p>',
				'<p>' . sprintf( '<strong>%s</strong>: %s',
					$extension->info->title,
					$extension->info->excerpt
				) . '</p>',
				'<p>' . sprintf( '<a class="button button-primary" href="%s">%s</a>',
					$extension->info->link,
					__( 'Get This Extension', 'theme-my-login' )
				) . '</p>',
			) ); ?>
		</div>

		<?php endif;
	}
}

/**
 * Handle saving of notice dismissals.
 *
 * @since 7.0.8
 */
function tml_admin_ajax_dismiss_notice() {
	if ( empty( $_POST['nonce'] ) || empty( $_POST['notice'] ) || ! wp_verify_nonce( $_POST['nonce'], $_POST['notice'] ) ) {
		wp_send_json_error( null, 400 );
	}
	if ( ! current_user_can( 'activate_plugins' ) ) {
		wp_send_json_error( null, 403 );
	}
	$dismissed_notices = get_site_option( '_tml_dismissed_notices', array() );
	$dismissed_notices[] = sanitize_key( $_POST['notice'] );
	update_site_option( '_tml_dismissed_notices', $dismissed_notices );
	wp_send_json_success();
}

/**
 * Update TML.
 *
 * @since 7.0
 */
function tml_admin_update() {
	$version = tml_get_installed_version();

	// Bail if no update needed
	if ( version_compare( $version, THEME_MY_LOGIN_VERSION, '>=' ) ) {
		return;
	}

	// 7.0 upgrade
	if ( version_compare( $version, '7.0', '<' ) ) {
		// Initial migration
		$options = get_option( 'theme_my_login', array() );
		if ( ! empty( $options ) ) {
			if ( ! empty( $options['login_type'] ) ) {
				update_site_option( 'tml_login_type', $options['login_type'] );
			}
			delete_option( 'theme_my_login' );
		}
	}

	// Set the first time install date
	if ( ! get_site_option( '_tml_installed_at' ) ) {
		update_site_option( '_tml_installed_at', current_time( 'timestamp' ) );
	}

	// Set the update date
	update_site_option( '_tml_updated_at', current_time( 'timestamp' ) );

	// Store the previous version
	if ( ! empty( $version ) ) {
		update_site_option( '_tml_previous_version', $version );
	}

	// Bump the installed version
	update_site_option( '_tml_version', THEME_MY_LOGIN_VERSION );

	// Force permalinks to be regenerated
	tml_flush_rewrite_rules();
}

/**
 * Sanitize a slug.
 *
 * @since 7.0
 *
 * @param string $slug The slug.
 * @return string The slug.
 */
function tml_sanitize_slug( $slug ) {
	if ( ! empty( $slug ) ) {
		$slug = preg_replace( '#/+#', '/', '/' . str_replace( '#', '', $slug ) );
		$slug = trim( preg_replace( '|^/index\.php/|', '', $slug ), '/' );
	}
	return $slug;
}

/**
 * Add the nav menu meta box.
 *
 * @since 7.0
 */
function tml_admin_add_nav_menu_meta_box() {
	add_meta_box( 'tml_actions',
		__( 'Theme My Login Actions', 'theme-my-login' ),
		'tml_admin_nav_menu_meta_box',
		'nav-menus',
		'side',
		'default'
	);
}

/**
 * Render the nav menu meta box.
 *
 * @since 7.0
 */
function tml_admin_nav_menu_meta_box() {
	global $_nav_menu_placeholder, $nav_menu_selected_id;

	$_nav_menu_placeholder = 0 > $_nav_menu_placeholder ? $_nav_menu_placeholder - 1 : -1;

	$actions = wp_list_filter( tml_get_actions(), array(
		'show_in_nav_menus' => true
	) );
	?>

	<div id="tml-action" class="posttypediv">
		<div class="tabs-panel tabs-panel-active">
			<ul class="categorychecklist form-no-clear">
				<?php echo walk_nav_menu_tree( array_map( 'wp_setup_nav_menu_item', $actions ), 0, (object) array(
					'walker' => new Walker_Nav_Menu_Checklist(),
				) ); ?>
			</ul>
		</div>
		<p class="button-controls wp-clearfix">
			<span class="add-to-menu">
				<input type="submit"<?php wp_nav_menu_disabled_check( $nav_menu_selected_id ); ?> class="button submit-add-to-menu right" value="<?php esc_attr_e( 'Add to Menu' ); ?>" name="add-tml-action-menu-item" id="submit-tml-action" />
				<span class="spinner"></span>
			</span>
		</p>
	</div>

	<?php
}

/**
 * Filter the edit nav menu walker.
 *
 * @since 7.0
 *
 * @param string $walker The name of the walker class.
 * @return string The name of the walker class.
 */
function tml_admin_filter_edit_nav_menu_walker( $walker )  {
	$walker = 'Theme_My_Login_Walker_Nav_Menu_Edit';
	if ( ! class_exists( $walker ) ) {
		require_once THEME_MY_LOGIN_PATH . 'admin/class-theme-my-login-walker-nav-menu-edit.php';
	}
	return $walker;
}

/**
 * Filter the plugin action links.
 *
 * @since 7.0.12
 *
 * @param array  $actions The plugin action links.
 * @param string $file    The path to the plugin file.
 * @param array  $data    The plugin data.
 * @param string $context The plugin context.
 * @return array The plugin action links.
 */
function tml_admin_filter_plugin_action_links( $actions, $file, $data, $context ) {
	if ( 'theme-my-login/theme-my-login.php' == $file ) {
		$actions['settings'] = sprintf( '<a href="%1$s">%2$s</a>',
			admin_url( 'admin.php?page=theme-my-login' ),
			__( 'Settings' )
		);
		$actions['extensions'] = sprintf( '<a href="%1$s">%2$s</a>',
			admin_url( 'admin.php?page=theme-my-login-extensions' ),
			__( 'Extensions', 'theme-my-login' )
		);
	}
	return $actions;
}
