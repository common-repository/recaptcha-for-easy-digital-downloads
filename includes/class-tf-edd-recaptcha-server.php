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

if ( ! class_exists( 'TF_EDD_Recaptcha_Server' ) ) {

	/**
	 * Register settings for reCAPTCH via EDD settings API
	 *
	 * @since 1.0.0
	 */
	class TF_EDD_Recaptcha_Server {

		/**
		 * A reference to an instance of this class.
		 *
		 * @since 1.0.0
		 * @var   object
		 */
		private static $instance = null;

		/**
		 * Remote captcha verification request parameters
		 *
		 * @since 1.0.0
		 * @var   array
		 */
		private $request_data = array();

		/**
		 * reCAPTCHA verify API host
		 *
		 * @since 1.0.0
		 * @var   string
		 */
		private $api_host = 'https://www.google.com/recaptcha/api/siteverify';

		/**
		 * POST key for captcha response
		 *
		 * @since 1.0.0
		 * @var   string
		 */
		private $post_key = 'g-recaptcha-response';

		/**
		 * Sets up needed actions/filters for the plugin to initialize.
		 *
		 * @since 1.0.0
		 */
		public function __construct() {

			add_action( 'edd_process_register_form', array( $this, 'captcha_handle' ) );
			add_filter( 'init', array( $this, 'login_captcha_handle' ), 0 );

		}

		/**
		 * Handle captcha validation for login form
		 * Preprocess captcha and if failed - prevent passing nonce validation
		 *
		 * @since  1.0.0
		 * @return void|null
		 */
		public function login_captcha_handle() {

			if ( ! isset( $_POST['edd_action'] ) || 'user_login' !== $_POST['edd_action'] ) {
				return;
			}

			$is_captcha_valid = $this->captcha_handle();

			if ( ! $is_captcha_valid ) {
				$_POST['edd_login_nonce'] = false;
			}

		}

		/**
		 * Handle register form captcha validation
		 *
		 * @since  1.0.0
		 * @return bool
		 */
		public function captcha_handle() {

			if ( ! $this->is_captcha_validation_required() ) {
				return true;
			}

			if ( empty( $_POST[ $this->post_key ] ) ) {
				edd_set_error(
					'empty_captcha',
					__( 'Please, pass reCAPTCHA validation', 'recaptcha-for-edd' )
				);
				return false;
			}

			$this->prepare_request_data();

			$result = $this->is_captcha_valid();

			if ( false === $result ) {
				edd_set_error(
					'fail_captcha',
					__( 'reCAPTCHA validation failed, please try again', 'recaptcha-for-edd' )
				);
				return false;
			}

			return true;

		}

		/**
		 * Check, if captcha validation is required on current hook
		 *
		 * @since  1.0.0
		 * @return bool
		 */
		private function is_captcha_validation_required() {

			$hook    = current_filter();
			$setting = '';

			switch ( $hook ) {
				case 'edd_process_register_form':
					$setting = 'tf_recaptcha_register';
					break;

				case 'init':
					$setting = 'tf_recaptcha_login';
					break;
			}

			if ( empty( $setting ) ) {
				return false;
			}

			$is_required = tf_edd_recaptcha_settings()->get_option( $setting );

			if ( ! $is_required ) {
				return false;
			}

			return true;

		}

		/**
		 * Prepare remote request arguments
		 *
		 * @since  1.0.0
		 * @return void
		 */
		private function prepare_request_data() {

			$defaults = array(
				'secret'   => tf_edd_recaptcha_settings()->get_option( 'tf_recaptcha_secret_key' ),
				'response' => false,
				'remoteip' => false,
			);

			$user = array(
				'response' => esc_attr( $_POST[ $this->post_key ] ),
				'remoteip' => $this->get_client_ip(),
			);

			$this->request_data = array_merge( $defaults, $user );

		}

		/**
		 * Prepare and make remote cpatcha verification request
		 *
		 * @since  1.0.0
		 * @return bool
		 */
		private function is_captcha_valid() {

			if ( ! $this->request_data['secret'] || ! $this->request_data['response'] ) {
				return false;
			}

			$request = wp_remote_post( $this->api_host, array( 'body' => $this->request_data ) );

			if ( empty( $request['body'] ) ) {
				return false;
			}

			$result = json_decode( $request['body'], true );

			if ( empty( $result ) || ! $result['success'] ) {
				return false;
			}

			return true;

		}

		/**
		 * Get remote client IP address
		 *
		 * @since  1.0.0
		 * @return string
		 */
		private function get_client_ip() {

			$ip = '';

			if ( $_SERVER['HTTP_CLIENT_IP'] ) {
				$ip = $_SERVER['HTTP_CLIENT_IP'];
			} else if ( $_SERVER['HTTP_X_FORWARDED_FOR'] ) {
				$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
			} else if ( $_SERVER['HTTP_X_FORWARDED'] ) {
				$ip = $_SERVER['HTTP_X_FORWARDED'];
			} else if ( $_SERVER['HTTP_FORWARDED_FOR'] ) {
				$ip = $_SERVER['HTTP_FORWARDED_FOR'];
			} else if ( $_SERVER['HTTP_FORWARDED'] ) {
				$ip = $_SERVER['HTTP_FORWARDED'];
			} else if ( $_SERVER['REMOTE_ADDR'] ) {
				$ip = $_SERVER['REMOTE_ADDR'];
			} else {
				$ip = 'UNKNOWN';
			}

			return preg_replace( '/[^0-9a-fA-F:., ]/', '', $ip );
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

	function tf_edd_recaptcha_server() {
		return TF_EDD_Recaptcha_Server::get_instance();
	}

	tf_edd_recaptcha_server();
}
