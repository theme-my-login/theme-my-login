<?php
/**
 * Keeps every place that declares the WordPress/PHP floor in agreement.
 *
 * @package Theme_My_Login
 */

class Test_Version_Floors extends WP_UnitTestCase {

	const HEADERS = array(
		'wp'  => 'Requires at least',
		'php' => 'Requires PHP',
	);

	private function root() {
		return dirname( __DIR__ );
	}

	private function readme_floors() {
		return get_file_data( $this->root() . '/src/readme.txt', self::HEADERS );
	}

	/**
	 * @dataProvider data_plugin_files
	 */
	public function test_plugin_header_floors_match_the_readme( $file ) {
		$header = get_file_data( $this->root() . '/' . $file, self::HEADERS );

		foreach ( $header as $value ) {
			$this->assertNotSame( '', $value );
		}
		$this->assertSame( $this->readme_floors(), $header );
	}

	public function data_plugin_files() {
		return array(
			'root loader' => array( 'theme-my-login.php' ),
			'source'      => array( 'src/theme-my-login.php' ),
		);
	}

	public function test_shipped_composer_php_constraint_matches_the_readme() {
		$composer = json_decode( file_get_contents( $this->root() . '/src/composer.json' ), true );

		$this->assertSame( '>=' . $this->readme_floors()['php'], $composer['require']['php'] );
	}

	public function test_phpcs_lints_at_the_declared_php_floor() {
		$phpcs = file_get_contents( $this->root() . '/phpcs.xml.dist' );

		$this->assertMatchesRegularExpression( '/name="testVersion" value="' . preg_quote( $this->readme_floors()['php'], '/' ) . '-"/', $phpcs );
	}

	public function test_phpcs_minimum_wp_version_matches_the_readme() {
		$phpcs = file_get_contents( $this->root() . '/phpcs.xml.dist' );

		$this->assertMatchesRegularExpression( '/name="minimum_supported_wp_version" value="' . preg_quote( $this->readme_floors()['wp'], '/' ) . '"/', $phpcs );
	}
}
