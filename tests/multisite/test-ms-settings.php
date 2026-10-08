<?php
/**
 * Multisite coverage for src/admin/settings.php: the network's own
 * registration setting overrides the per-site "Anyone can register" option,
 * so the TML screen links to Network Settings instead of offering it.
 *
 * @package Theme_My_Login
 */

if ( ! function_exists( 'tml_admin_is_plugin_page' ) ) {
	require THEME_MY_LOGIN_PATH . 'admin/functions.php';
}
if ( ! class_exists( 'Theme_My_Login_Admin' ) ) {
	require THEME_MY_LOGIN_PATH . 'admin/class-theme-my-login-admin.php';
}
if ( ! function_exists( 'tml_admin_get_extension_suggestion' ) ) {
	require THEME_MY_LOGIN_PATH . 'admin/promotions.php';
}
if ( ! function_exists( 'tml_admin_register_settings' ) ) {
	require THEME_MY_LOGIN_PATH . 'admin/settings.php';
}

class Test_MS_Settings extends WP_UnitTestCase {

	public function test_get_settings_fields_omits_the_membership_option() {
		$fields = tml_admin_get_settings_fields();

		$this->assertArrayNotHasKey( 'users_can_register', $fields['tml_settings_registration'] );
		$this->assertArrayHasKey( 'tml_registration_type', $fields['tml_settings_registration'] );
	}

	public function test_registration_section_callback_links_to_network_settings() {
		ob_start();
		tml_admin_setting_callback_registration_section();
		$output = ob_get_clean();

		$this->assertStringContainsString( 'href="' . esc_url( network_admin_url( 'settings.php' ) ) . '"', $output );
	}
}
