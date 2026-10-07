<?php
/**
 * Coverage for Theme_My_Login::__construct()'s activation/deactivation hook
 * registration (src/includes/class-theme-my-login.php). It must register
 * against THEME_MY_LOGIN_FILE, the plugin's actual main file as loaded by
 * WordPress.
 *
 * @package Theme_My_Login
 */

class Test_Theme_My_Login extends WP_UnitTestCase {

	public function test_theme_my_login_file_points_to_the_real_plugin_file() {
		$this->assertFileExists( THEME_MY_LOGIN_FILE );

		$headers = get_file_data( THEME_MY_LOGIN_FILE, array( 'name' => 'Plugin Name' ) );
		$this->assertSame( 'Theme My Login', $headers['name'] );
	}

	public function test_activation_hook_is_registered_against_the_real_plugin_file() {
		$basename = plugin_basename( THEME_MY_LOGIN_FILE );

		$this->assertNotFalse(
			has_action( 'activate_' . $basename, array( theme_my_login(), 'activate' ) )
		);
	}

	public function test_deactivation_hook_is_registered_against_the_real_plugin_file() {
		$basename = plugin_basename( THEME_MY_LOGIN_FILE );

		$this->assertNotFalse(
			has_action( 'deactivate_' . $basename, array( theme_my_login(), 'deactivate' ) )
		);
	}
}
