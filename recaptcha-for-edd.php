<?php
/**
 * Plugin Name: reCAPTCHA for Easy Digital Downloads
 * Plugin URI:
 * Description: Add reCAPTCHA anti-spam checking for Easy Digital Downloads registration and login forms.
 * Version:     1.0.0
 * Author:      TeFox
 * Author URI:
 * Text Domain: recaptcha-for-edd
 * License:     GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path: /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists( 'TF_EDD_Recaptcha' ) ) {

	/**
	 * Base plugin class
	 *
	 * @since 1.0.0
	 */
	class TF_EDD_Recaptcha {

		/**
		 * A reference to an instance of this class.
		 *
		 * @since 1.0.0
		 * @var   object
		 */
		private static $instance = null;

		/**
		 * Holder for plugin folder URL
		 *
		 * @since 1.0.0
		 * @var   string
		 */
		public $plugin_url = null;
		/**
		 * Holder for plugin folder path
		 *
		 * @since 1.0.0
		 * @var   string
		 */
		public $plugin_dir = null;

		/**
		 * Trigger checks is EDD is active or not
		 *
		 * @since 1.0.0
		 * @var   bool
		 */
		public $has_edd = null;

		/**
		 * Sets up needed actions/filters for the plugin to initialize.
		 *
		 * @since 1.0.0
		 */
		public function __construct() {

			if ( ! $this->has_edd() ) {
				add_action( 'admin_notices', array( $this, 'no_edd_notice' ) );
				return;
			}

			$this->includes();

		}

		/**
		 * Include globally required files
		 *
		 * @since 1.0.0
		 */
		public function includes() {
			require_once $this->plugin_dir( 'includes/class-tf-edd-recaptcha-settings.php' );
			require_once $this->plugin_dir( 'includes/class-tf-edd-recaptcha-public.php' );
			require_once $this->plugin_dir( 'includes/class-tf-edd-recaptcha-server.php' );
		}

		/**
		 * Get plugin URL (or some plugin dir/file URL)
		 *
		 * @since  1.0.0
		 * @param  string $path dir or file inside plugin dir.
		 * @return string
		 */
		public function plugin_url( $path = null ) {
			if ( ! $this->plugin_url ) {
				$this->plugin_url = trailingslashit( plugin_dir_url( __FILE__ ) );
			}
			if ( null != $path ) {
				return $this->plugin_url . $path;
			}
			return $this->plugin_url;
		}

		/**
		 * Get plugin dir path (or some plugin dir/file path)
		 *
		 * @since  1.0.0
		 * @param  string $path dir or file inside plugin dir.
		 * @return string
		 */
		public function plugin_dir( $path = null ) {
			if ( ! $this->plugin_dir ) {
				$this->plugin_dir = trailingslashit( plugin_dir_path( __FILE__ ) );
			}
			if ( null != $path ) {
				return $this->plugin_dir . $path;
			}
			return $this->plugin_dir;
		}

		/**
		 * Check if EDD is active
		 *
		 * @since  1.0.0
		 * @return bool
		 */
		public function has_edd() {
			if ( null == $this->has_edd ) {
				$this->has_edd = in_array(
					'easy-digital-downloads/easy-digital-downloads.php',
					apply_filters( 'active_plugins', get_option( 'active_plugins' ) )
				);
			}
			return $this->has_edd;
		}

		/**
		 * Show notice if EDD plugin is not activate
		 *
		 * @since  1.0.0
		 * @return void
		 */
		public function no_edd_notice() {
			$class   = 'error';
			$message = __( 'reCAPTCHA for Easy Digital Downloads is enabled but not effective. It requires Easy Digital Downloads in order to work.', 'recaptcha-for-edd' );
			printf( '<div class="%s"><p>%s</p></div>', $class, $message );
		}

		/**
		 * Returns the instance.
		 *
		 * @since  1.0.0
		 * @return object
		 */
		public static function get_instance() {

			// If the single instance hasn't been set, set it now.
			if ( null == self::$instance )
				self::$instance = new self;

			return self::$instance;
		}
	}

	function tf_edd_recaptcha() {
		return TF_EDD_Recaptcha::get_instance();
	}

	tf_edd_recaptcha();
}
