<?php
/**
 * Setting management class
 *
 * @package recaptcha-for-edd
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists( 'TF_EDD_Recaptcha_Settings' ) ) {

	/**
	 * Register settings for reCAPTCH via EDD settings API
	 *
	 * @since 1.0.0
	 */
	class TF_EDD_Recaptcha_Settings {

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

			add_filter( 'edd_settings_tabs', array( $this, 'register_tab' ) );
			add_filter( 'edd_registered_settings', array( $this, 'register_settings' ) );

		}

		/**
		 * Register tab for recaptcha settings
		 *
		 * @since  1.0.0
		 * @param  array $tabs current tabs array.
		 * @return array
		 */
		public function register_tab( $tabs ) {

			$tabs['tf-recaptcha'] = __( 'reCAPTCHA', 'recaptcha-for-edd' );

			return $tabs;
		}

		/**
		 * Add recaptcha-related settings
		 *
		 * @since  1.0.0
		 * @param  array $settings current settings array.
		 * @return array
		 */
		public function register_settings( $settings ) {

			$docs = 'https://www.google.com/recaptcha/admin';

			$keys_descr = sprintf(
				__( 'Set reCAPTCHA keys. Create your own keys you can %s', 'recaptcha-for-edd' ),
				'<a href="' . esc_url( $docs ) . '">' . __( 'here', 'recaptcha-for-edd' ) . '</a>'
			);

			$settings['tf-recaptcha'] = array(
				'tf_recaptcha_register' => array(
					'id'   => 'tf_recaptcha_register',
					'name' => __( 'reCAPTCHA in register form', 'recaptcha-for-edd' ),
					'desc' => __( 'Add reCAPTCHA validation into EDD register account form.', 'recaptcha-for-edd' ),
					'type' => 'checkbox',
				),
				'tf_recaptcha_login' => array(
					'id'   => 'tf_recaptcha_login',
					'name' => __( 'reCAPTCHA in login form', 'recaptcha-for-edd' ),
					'desc' => __( 'Add reCAPTCHA validation into EDD login into account form.', 'recaptcha-for-edd' ),
					'type' => 'checkbox',
				),
				'tf_recaptcha_keys' => array(
					'id' => 'tf_recaptcha_keys',
					'name' => '<span class="field-section-title">' . $keys_descr . '</span>',
					'type' => 'header'
				),
				'tf_recaptcha_public_key' => array(
					'id'   => 'tf_recaptcha_public_key',
					'name' => __( 'Public key', 'recaptcha-for-edd' ),
					'desc' => __( 'Put your public key for reCAPTCHA here.', 'recaptcha-for-edd' ),
					'type' => 'text',
				),
				'tf_recaptcha_secret_key' => array(
					'id'   => 'tf_recaptcha_secret_key',
					'name' => __( 'Secret key', 'recaptcha-for-edd' ),
					'desc' => __( 'Put your secret key for reCAPTCHA here.', 'recaptcha-for-edd' ),
					'type' => 'text',
				),
			);

			return $settings;

		}

		/**
		 * Get single option from EDD-related options array
		 *
		 * @param  string $option  option name.
		 * @param  mixed  $default default option value.
		 * @return mixed
		 */
		public function get_option( $option, $default = false ) {

			$settings = get_option( 'edd_settings', array() );

			if ( isset( $settings[ $option ] ) ) {
				return $settings[ $option ];
			}

			return $default;

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

	function tf_edd_recaptcha_settings() {
		return TF_EDD_Recaptcha_Settings::get_instance();
	}

	tf_edd_recaptcha_settings();
}
