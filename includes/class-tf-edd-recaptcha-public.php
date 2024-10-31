<?php
/**
 * Define public related hooks
 *
 * @package recaptcha-for-edd
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists( 'TF_EDD_Recaptcha_Public' ) ) {

	/**
	 * Sets up public part of plugin.
	 *
	 * @since 1.0.0
	 */
	class TF_EDD_Recaptcha_Public {

		/**
		 * A reference to an instance of this class.
		 *
		 * @since 1.0.0
		 * @var   object
		 */
		private static $instance = null;

		/**
		 * Sets up needed actions/filters for the plugin to initialize.
		 *
		 * @since 1.0.0
		 */
		public function __construct() {

			add_filter( 'wp_enqueue_scripts', array( $this, 'register_js' ) );
			add_filter( 'edd_register_form_fields_before_submit', array( $this, 'show_register_captha' ) );
			add_filter( 'edd_login_fields_after', array( $this, 'show_login_captha' ) );

		}

		/**
		 * Print captcha markup in register form
		 *
		 * @since  1.0.0
		 * @return void
		 */
		public function show_register_captha() {
			echo $this->get_captcha_markup( 'register' );
		}

		/**
		 * Print captcha markup in login form
		 *
		 * @since  1.0.0
		 * @return void
		 */
		public function show_login_captha() {
			echo $this->get_captcha_markup( 'login' );
		}

		/**
		 * Get cpatcha HTML-markup and print JS file into footer
		 *
		 * @since  1.0.0
		 * @param  string $where form, where captcha called, currently allowed only login and register.
		 * @return string|bool false
		 */
		public function get_captcha_markup( $where = 'register' ) {

			if ( ! in_array( $where, array( 'register', 'login' ) ) ) {
				return false;
			}

			$is_allowed = tf_edd_recaptcha_settings()->get_option( 'tf_recaptcha_' . $where );

			if ( ! $is_allowed ) {
				return false;
			}

			$key = tf_edd_recaptcha_settings()->get_option( 'tf_recaptcha_public_key' );

			if ( ! $key ) {
				return __( 'Public key not provided', 'recaptcha-for-edd' );
			}

			wp_enqueue_script( 'tf-edd-recaptcha' );

			$style = apply_filters( 'tf_edd_recaptcha_default_style', 'clear:both;padding:10px 0;' );

			return sprintf( '<div class="g-recaptcha" data-sitekey="%s" style="%s"></div>', $key, $style );

		}

		/**
		 * Print captcha-related JS into page footer
		 *
		 * @since  1.0.0
		 * @return void
		 */
		public function register_js() {
			wp_register_script( 'tf-edd-recaptcha', 'https://www.google.com/recaptcha/api.js', false, false, true );
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

	function tf_edd_recaptcha_public() {
		return TF_EDD_Recaptcha_Public::get_instance();
	}

	tf_edd_recaptcha_public();
}
