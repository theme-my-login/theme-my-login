<?php

/**
 * Theme My Login Extension Class
 *
 * @package Theme_My_Login
 * @subpackage Extensions
 */

/**
 * Class used to implement an extension object.
 *
 * @since 7.0
 */
abstract class Theme_My_Login_Extension {

	/**
	 * The extension name.
	 *
	 * @var string
	 */
	protected $name = 'tml-extension';

	/**
	 * The extension title.
	 *
	 * @var string
	 */
	protected $title;

	/**
	 * The extension version.
	 *
	 * @var string
	 */
	protected $version = '1.0';

	/**
	 * The main extension file.
	 *
	 * @var string
	 */
	protected $file;

	/**
	 * The extension path.
	 *
	 * @var string
	 */
	protected $path;

	/**
	 * The extension URL.
	 *
	 * @var string
	 */
	protected $url;

	/**
	 * The extension homepage URL.
	 *
	 * @var string
	 */
	protected $homepage_url;

	/**
	 * The extension documentation URL.
	 *
	 * @var string
	 */
	protected $documentation_url;

	/**
	 * The extension support URL.
	 *
	 * @var string
	 */
	protected $support_url;

	/**
	 * The extension store URL.
	 *
	 * @var string
	 */
	protected $store_url;

	/**
	 * The extension item ID.
	 *
	 * @var int
	 */
	protected $item_id;

	/**
	 * The option used to store the license key.
	 *
	 * @var string.
	 */
	protected $license_key_option;

	/**
	 * The option key used to store the license status.
	 *
	 * @var string
	 */
	protected $license_status_option;

	/**
	 * Create the extension instance.
	 *
	 * @since 7.0
	 *
	 * @param string $file The path to the extension file.
	 */
	public function __construct( $file ) {
		$this->file = $file;
		$this->path = plugin_dir_path( $file );
		$this->url  = plugin_dir_url( $file );

		$this->set_properties();
		$this->include_files();
		$this->add_actions();
		$this->add_filters();

		register_activation_hook( $file, array( $this, 'activate' ) );
		register_deactivation_hook( $file, array( $this, 'deactivate' ) );

		if ( is_admin() ) {
			$this->update();
		}
	}

	/**
	 * Set class properties.
	 *
	 * @since 7.0
	 */
	protected function set_properties() {}

	/**
	 * Include the extension files.
	 *
	 * @since 7.0
	 */
	protected function include_files() {}

	/**
	 * Add the extension actions.
	 *
	 * @since 7.0
	 */
	protected function add_actions() {}

	/**
	 * Add the extension filters.
	 *
	 * @since 7.0
	 */
	protected function add_filters() {}

	/**
	 * Get the extension name.
	 *
	 * @since 7.0
	 *
	 * @return string The extension name.
	 */
	public function get_name() {
		return $this->name;
	}

	/**
	 * Get the extension slug.
	 *
	 * @since 7.0
	 *
	 * @return string The extension slug.
	 */
	public function get_slug() {
		if ( isset( $this->slug ) ) {
			return $this->slug;
		}
		return str_replace( array( 'theme-my-login-', 'tml-' ), '', $this->name );
	}

	/**
	 * Get the extension title.
	 *
	 * @since 7.0
	 *
	 * @return string The extension title.
	 */
	public function get_title() {
		return $this->title;
	}

	/**
	 * Get the extension version.
	 *
	 * @since 7.0
	 *
	 * @return string The extension version.
	 */
	public function get_version() {
		return $this->version;
	}

	/**
	 * Get the extension basename.
	 *
	 * @since 7.0
	 *
	 * @return string The extension basename.
	 */
	public function get_basename() {
		return plugin_basename( $this->file );
	}

	/**
	 * Get the main extension file.
	 *
	 * @since 7.0
	 *
	 * @return string The main extension file.
	 */
	public function get_file() {
		return $this->file;
	}

	/**
	 * Get the extension path.
	 *
	 * @since 7.0
	 *
	 * @return string The extension path.
	 */
	public function get_path() {
		return $this->path;
	}

	/**
	 * Get the extension URL.
	 *
	 * @since 7.0
	 *
	 * @return string The extension URL.
	 */
	public function get_url() {
		return $this->url;
	}

	/**
	 * Get the extension's homepage URL.
	 *
	 * @since 7.0.6
	 *
	 * @return string The extension's homepage URL.
	 */
	public function get_homepage_url() {
		return $this->homepage_url;
	}

	/**
	 * Get the extension's documentation URL.
	 *
	 * @since 7.0.6
	 *
	 * @return string The extension's documentation URL.
	 */
	public function get_documentation_url() {
		return $this->documentation_url;
	}

	/**
	 * Get the extension's support URL.
	 *
	 * @since 7.0.6
	 *
	 * @return string The extension's support URL.
	 */
	public function get_support_url() {
		return $this->support_url;
	}

	/**
	 * Get the extension's store URL.
	 *
	 * @since 7.0
	 *
	 * @return string The extension's store URL.
	 */
	public function get_store_url() {
		return $this->store_url;
	}

	/**
	 * Get the extension's item ID.
	 *
	 * @since 7.0
	 *
	 * @return int The extension's item ID.
	 */
	public function get_item_id() {
		return $this->item_id;
	}

	/**
	 * Get the name of the license key option for the extension.
	 *
	 * @since 7.0
	 *
	 * @return string The license key option name.
	 */
	public function get_license_key_option() {
		return $this->license_key_option;
	}

	/**
	 * Get the license key for the extension.
	 *
	 * @since 7.0
	 *
	 * @return string The license key for the extension.
	 */
	public function get_license_key() {
		return get_site_option( $this->license_key_option );
	}

	/**
	 * Set the license key for the extension.
	 *
	 * @since 7.1
	 *
	 * @param string $key The license key.
	 */
	public function set_license_key( $key = '' ) {
		update_site_option( $this->license_key_option, $key );
	}

	/**
	 * Get the name of the license status option for the extension.
	 *
	 * @since 7.0
	 *
	 * @return string The license status option name.
	 */
	public function get_license_status_option() {
		return $this->license_status_option;
	}

	/**
	 * Get the license status for the extension.
	 *
	 * @since 7.0
	 *
	 * @return string The license status for the extension.
	 */
	public function get_license_status() {
		return get_site_option( $this->license_status_option );
	}

	/**
	 * Set the license status for the extension.
	 *
	 * @since 7.0
	 *
	 * @param string $status The license status.
	 */
	public function set_license_status( $status = '' ) {
		update_site_option( $this->license_status_option, $status );
	}

	/**
	 * Get the extension settings page arguments.
	 *
	 * @since 7.0
	 *
	 * @return array The extension settings page arguments.
	 */
	public function get_settings_page_args() {
		return array();
	}

	/**
	 * Get the extension settings sections.
	 *
	 * @since 7.0
	 *
	 * @return array The extension settings sections.
	 */
	public function get_settings_sections() {
		return array();
	}

	/**
	 * Get the extension settings fields.
	 *
	 * @since 7.0
	 *
	 * @return array The extension settings fields.
	 */
	public function get_settings_fields() {
		return array();
	}

	/**
	 * Fire an action hook when the extension is activated.
	 *
	 * @since 7.0
	 */
	public function activate() {
		/**
		 * Fires when an extension is being activated.
		 *
		 * @since 7.0.14
		 *
		 * @param string $name The extension name.
		 */
		do_action( 'tml_activate_extension', $this->get_name() );

		/**
		 * Fires when the extension is being activated.
		 *
		 * @since 7.0
		 */
		do_action( 'tml_activate_' . $this->get_slug() );
	}

	/**
	 * Fire an action hook when the extension is deactivated.
	 *
	 * @since 7.0
	 */
	public function deactivate() {
		/**
		 * Fires when an extension is being deactivated.
		 *
		 * @since 7.0.14
		 *
		 * @param string $slug The extension name.
		 */
		do_action( 'tml_deactivate_extension', $this->get_name() );

		/**
		 * Fires when the extension is being deactivated.
		 *
		 * @since 7.0
		 */
		do_action( 'tml_deactivate_' . $this->get_slug() );
	}

	/**
	 * Update the extension.
	 *
	 * @since 7.0
	 */
	protected function update() {}
}
