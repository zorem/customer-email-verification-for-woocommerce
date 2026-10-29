<?php

if (!defined('ABSPATH')) {
	exit;
}

/**
 * Handles signup verification logic for the plugin.
 */
class CEV_Signup_Verification {

	/**
	 * Initialize the main plugin function
	 */
	public function __construct() {
		add_action('init', array($this, 'init'));
	}

	/**
	 * Instance of this class.
	 *
	 * @var object Class Instance
	 */
	private static $instance;

	/**
	 * Get the class instance
	 *
	 * @return CEV_Signup_Verification
	 */
	public static function get_instance() {
		if (null === self::$instance) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Init function to add hooks
	 */
	public function init() {

		// Enqueue custom scripts and styles.
		add_action('wp_enqueue_scripts', array($this, 'custom_enqueue_scripts'));

		// Add OTP popup to the My Account page before the login form.
		add_action('woocommerce_before_customer_login_form', array($this, 'add_otp_popup_to_my_account'));

		// AJAX: Check if an email exists (unauthenticated users).
		add_action('wp_ajax_nopriv_check_email_exists', array($this, 'check_email_exists'));

		// AJAX: Verify OTP for both authenticated and unauthenticated users.
		add_action('wp_ajax_verify_otp', array($this, 'verify_otp'));
		add_action('wp_ajax_nopriv_verify_otp', array($this, 'verify_otp'));

		// AJAX: Resend OTP for both authenticated and unauthenticated users.
		add_action('wp_ajax_resend_otp', array($this, 'resend_otp'));
		add_action('wp_ajax_nopriv_resend_otp', array($this, 'resend_otp'));

		// Mark the new account verified when its email passed the signup OTP.
		// Priority 20 runs after the email class resets the flag to 'false'
		// on woocommerce_created_customer_notification (priority 10).
		add_action( 'woocommerce_created_customer', array( $this, 'mark_otp_verified_customer' ), 20 );
		add_action( 'woocommerce_created_customer_notification', array( $this, 'mark_otp_verified_customer' ), 20 );

	}

	/**
	 * Transient key remembering that an address just passed the signup OTP.
	 *
	 * @param string $email Email address.
	 * @return string
	 */
	private function otp_verified_key( $email ) {
		return 'cev_otp_verified_' . md5( strtolower( trim( $email ) ) );
	}

	/**
	 * Flag a newly created customer as verified if their email passed the OTP.
	 *
	 * The signup OTP runs before the account exists, so the result is held in
	 * a short-lived transient until WooCommerce creates the customer.
	 *
	 * @since 2.8.2
	 *
	 * @param int $customer_id New customer ID.
	 */
	public function mark_otp_verified_customer( $customer_id ) {
		$user = get_user_by( 'id', $customer_id );
		if ( ! $user || ! get_transient( $this->otp_verified_key( $user->user_email ) ) ) {
			return;
		}
		update_user_meta( $customer_id, 'customer_email_verified', 'true' );
		update_user_meta( $customer_id, 'cev_user_resend_times', 0 );
		if ( 'woocommerce_created_customer_notification' === current_action() ) {
			delete_transient( $this->otp_verified_key( $user->user_email ) );
		}
	}

	/**
	 * Enqueue custom scripts and styles for signup and email verification.
	 *
	 * @since 1.0.0
	 */
	public function custom_enqueue_scripts() {
		// Enqueue the JavaScript file for signup functionality.	
		wp_enqueue_script('cev-signup-script', plugins_url('../assets/js/signup-script.js', __FILE__), array('jquery'), time(), true);

		// Enqueue the CSS file for custom styles.	
		wp_enqueue_style('cev-custom-style', plugins_url('../assets/css/signup-style.css', __FILE__), array(), time());

		// Pass data to the signup script.
		wp_localize_script('cev-signup-script', 'cev_ajax', array(
			'ajax_url' => admin_url('admin-ajax.php'), // URL for AJAX requests.
			'nonce' => wp_create_nonce('verify_otp_nonce'), // Nonce for security.
			'loaderImage' => plugins_url('assets/images/Eclipse.svg', dirname(__FILE__)), // Use dirname(__FILE__) to get the correct path
			'enableEmailVerification' => (bool) get_option('cev_enable_email_verification'), // Email verification toggle.
			'password_setup_link_enabled' =>  get_option('woocommerce_registration_generate_password', 'no'), // Password setup option.

			// Validation messages.
			'cev_password_validation' => __( 'Password is required.', 'customer-email-verification-for-woocommerce' ),
			'cev_email_validation' => __( 'Email is required.', 'customer-email-verification-for-woocommerce' ),
			'cev_email_exists_validation' => __( 'An account with this email address already exists. Please use a different email or log in to your existing account.', 'customer-email-verification-for-woocommerce' ),
			'cev_valid_email_validation' => __( 'Enter a valid email address.', 'customer-email-verification-for-woocommerce' ),

			// Popup / OTP messages.
			'cev_verified_success' => __( 'Your email is verified successfully.', 'customer-email-verification-for-woocommerce' ),
			'cev_error_prefix' => __( 'Error:', 'customer-email-verification-for-woocommerce' ),
			'cev_processing' => __( 'Please wait...', 'customer-email-verification-for-woocommerce' ),
		));
		
	}
	/**
	 * Add OTP popup to the My Account page.
	 *
	 * @since 1.0.0
	 */
	public function add_otp_popup_to_my_account() {
		// Include the OTP popup view template.
		require_once woo_customer_email_verification()->get_plugin_path() . '/includes/views/cev_signup_popup.php';			
	}
	/**
	 * Handle OTP resend request.
	 *
	 * @since 1.0.0
	 */
	public function resend_otp() {
		// Verify nonce for security.
		$nonce = isset($_POST['nonce']) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';
		if (!$nonce || !wp_verify_nonce($nonce, 'verify_otp_nonce')) {
			wp_send_json_error(array(
				'verified' => false,
				'message'  => __('Nonce verification failed.', 'customer-email-verification-for-woocommerce'),
			));
		}

		// Get recipient email from POST data.
		$recipient = isset($_POST['email']) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
		if (empty($recipient)) {
			wp_send_json_error(array(
				'verified' => false,
				'message'  => __('Email address is required.', 'customer-email-verification-for-woocommerce'),
			));
		}
		// Verification Email Resend Limit: '0' disables resend, otherwise the
		// number of resends allowed per signup attempt (counter lives 24h).
		$resend_limit = (int) get_option( 'cev_redirect_limit_resend', '3' );
		$resend_key   = 'cev_signup_resend_' . md5( strtolower( $recipient ) );
		$resend_count = (int) get_transient( $resend_key );
		if ( 0 === $resend_limit || $resend_count >= $resend_limit ) {
			$limit_message = get_option( 'cev_resend_limit_message', '' );
			if ( '' === trim( (string) $limit_message ) ) {
				$limit_message = __( 'Too many attempts, please contact us for further assistance.', 'customer-email-verification-for-woocommerce' );
			}
			wp_send_json_error( array(
				'verified'      => false,
				'limit_reached' => true,
				'message'       => $limit_message,
			) );
		}
		set_transient( $resend_key, $resend_count + 1, DAY_IN_SECONDS );

		 // Send the OTP email.
		 $result = $this->send_signup_verification_email($recipient);

		 // Return success or failure based on email send result.
		if ($result) {
			 wp_send_json_success(array(
				 'email' => $recipient,
				 'message' => __('OTP has been resent successfully.', 'customer-email-verification-for-woocommerce'),
			 ));
		} else {
			 wp_send_json_error(array(
				 'verified' => false,
				 'message'  => __('Failed to resend OTP. Please try again.', 'customer-email-verification-for-woocommerce'),
			 ));
		}
		
	}
	public function check_email_exists() {

		  // Verify nonce for security.
		$nonce = isset($_POST['nonce']) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';
		if (!$nonce || !wp_verify_nonce($nonce, 'verify_otp_nonce')) {
			  wp_send_json_error(array(
				  'verified' => false,
				  'message'  => __('Nonce verification failed.', 'customer-email-verification-for-woocommerce'),
			  ));
		}
		$email = isset($_POST['email']) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
		if (!is_email($email)) {
			wp_send_json_error(array(
				'not_valid' => true,
				'message'   => __('Invalid email address.', 'customer-email-verification-for-woocommerce'),
			));
		}

		if (email_exists($email)) {
			wp_send_json_success(array(
				'exists'  => true,
				'message' => __('An account with this email already exists.', 'customer-email-verification-for-woocommerce'),
			));
		}
		$errors = new WP_Error();		
		// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Core WordPress/WooCommerce hook, not owned by this plugin.
		$errors = apply_filters( 'woocommerce_registration_errors', $errors, '', $email );
		// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Core WordPress/WooCommerce hook, not owned by this plugin.
		$errors = apply_filters( 'registration_errors', $errors, '', $email );

		if ( $errors->has_errors() ) {			
			wp_send_json_success(array(
				'validation' => false,
				'message' => $errors->get_error_message(),
			));
		}
		
		// A new signup attempt starts a fresh resend allowance.
		delete_transient( 'cev_signup_resend_' . md5( strtolower( $email ) ) );

		// Send OTP email for verification.
		$email_sent = $this->send_signup_verification_email($email);
		if ($email_sent) {
			wp_send_json_success(array(
				'email'   => $email,
				'message' => __('Verification email has been sent.', 'customer-email-verification-for-woocommerce'),
			));
		}
	}

	public function send_signup_verification_email( $recipient ) {

		woo_customer_email_verification()->install->create_user_log_table();

		/*
		 * Throttle repeat sends for the same address. Without this, every extra
		 * click on the Register button issues a brand new PIN and overwrites the
		 * stored one, which silently invalidates the code in the email the
		 * customer already received.
		 */
		if ( $this->is_verification_email_throttled( $recipient ) ) {
			// The previously sent code is still valid, so reuse it and skip the send.
			wp_send_json_success( array( 'email' => true ) );
		}

		$verification_pin = $this->get_reusable_pin( $recipient );
		if ( '' === $verification_pin ) {
			$verification_pin = WC_customer_email_verification_email_Common()->generate_verification_pin();
		}
		$expire_time =  get_option('cev_verification_code_expiration', 'never');
		// Random, unguessable value. Never derive it from the email or the clock.
		$secret_code = wp_generate_password( 32, false, false );
		if ( empty( $expire_time ) ) {
			$expire_time = 'never';
		}
		
		global $wpdb;
		
		// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQLPlaceholders.UnquotedComplexPlaceholder
		$email_exists = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$wpdb->prefix}cev_user_log WHERE email = %s", $recipient));
		// phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQLPlaceholders.UnquotedComplexPlaceholder
		$current_time = current_time('mysql');
		if ($email_exists) {
			// Update the existing record
			// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQLPlaceholders.UnquotedComplexPlaceholder
			$wpdb->update(
				"{$wpdb->prefix}cev_user_log",
				array(
					
					'pin' => $verification_pin,
					'verified' => false,
					'secret_code' => $secret_code, // add secret_code
					'last_updated' => $current_time, // add last_updated
					
				),
				array('email' => $recipient),
				array(
					'%s', // pin
					'%d', // verified
					'%s', // secret_code
					'%s', // last_updated
				),
				array('%s') // email
			);
			// phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQLPlaceholders.UnquotedComplexPlaceholder
		} else {
			// Insert a new record
			// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQLPlaceholders.UnquotedComplexPlaceholder
			$wpdb->insert(
				"{$wpdb->prefix}cev_user_log",
				array(
					'email' => $recipient,
					'pin' => $verification_pin,
					'verified' => false,
					'secret_code' => $secret_code, // add secret_code
					'last_updated' => $current_time, // add last_updated
				),
				array(
					'%s', // email
					'%s', // pin
					'%d', // verified
					'%s', // secret_code
					'%s', // last_updated
				)
			);
			// phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQLPlaceholders.UnquotedComplexPlaceholder
		}
		$result = $this->send_verification_email_message( $recipient );

		// Return result.
		wp_send_json_success(array('email' => $result));
	}

	/**
	 * Return the pending code for this address when it was issued recently.
	 *
	 * Lets a customer use the code from an earlier email (for example one the
	 * store manager resent from the Unverified Users list) when they come back
	 * and register again, instead of every new attempt invalidating it.
	 *
	 * @since 2.8.2
	 *
	 * @param string $recipient Email address.
	 * @return string Existing PIN, or '' when a new one should be generated.
	 */
	public function get_reusable_pin( $recipient ) {
		/**
		 * Filter how long (seconds) a pending signup code stays reusable. 0 disables reuse.
		 *
		 * @since 2.8.2
		 */
		// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Plugin's established cev_ filter prefix.
		$reuse_window = (int) apply_filters( 'cev_signup_pin_reuse_window', DAY_IN_SECONDS, $recipient );
		// Never hand out a code past its OTP Expiration.
		$lifetime = $this->get_code_lifetime();
		if ( 0 < $lifetime ) {
			$reuse_window = min( $reuse_window, $lifetime );
		}
		if ( 1 > $reuse_window ) {
			return '';
		}

		global $wpdb;
		// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQLPlaceholders.UnquotedComplexPlaceholder
		$row = $wpdb->get_row( $wpdb->prepare( "SELECT pin, last_updated FROM {$wpdb->prefix}cev_user_log WHERE email = %s", $recipient ) );
		// phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQLPlaceholders.UnquotedComplexPlaceholder

		if ( ! $row || '' === (string) $row->pin ) {
			return '';
		}

		$elapsed = strtotime( current_time( 'mysql' ) ) - strtotime( $row->last_updated );
		return ( 0 <= $elapsed && $elapsed < $reuse_window ) ? (string) $row->pin : '';
	}

	/**
	 * OTP Expiration setting in seconds; 0 means codes never expire.
	 *
	 * @since 2.8.2
	 *
	 * @return int
	 */
	public function get_code_lifetime() {
		$expiration = get_option( 'cev_verification_code_expiration', 'never' );
		return ( 'never' === $expiration || '' === (string) $expiration ) ? 0 : absint( $expiration );
	}

	/**
	 * Whether a code issued at $issued_at (MySQL time) is past its expiry.
	 *
	 * @since 2.8.2
	 *
	 * @param string $issued_at cev_user_log.last_updated value.
	 * @return bool
	 */
	public function is_code_expired( $issued_at ) {
		$lifetime = $this->get_code_lifetime();
		if ( 0 === $lifetime || empty( $issued_at ) ) {
			return false;
		}
		return ( strtotime( current_time( 'mysql' ) ) - strtotime( $issued_at ) ) > $lifetime;
	}

	/**
	 * Build and send the signup verification email to one address.
	 *
	 * The PIN merge tag reads the pending code for this address from
	 * cev_user_log; an address with no pending row gets a sample code
	 * (used by the admin test email).
	 *
	 * @since 2.8.2
	 *
	 * @param string $recipient Email address.
	 * @return bool Whether WooCommerce accepted the email for sending.
	 */
	public function send_verification_email_message( $recipient ) {
		$cev_initialise_customizer_settings = new cev_initialise_customizer_settings();

		WC_customer_email_verification_email_Common()->registerd_user_email  = $recipient;
		$result = false;

		$email_subject = get_option( 'cev_verification_email_subject', $cev_initialise_customizer_settings->defaults['cev_verification_email_subject'] );
		$email_subject = WC_customer_email_verification_email_Common()->maybe_parse_merge_tags( $email_subject );
		
		$email_heading = get_option( 'cev_verification_email_heading', $cev_initialise_customizer_settings->defaults['cev_verification_email_heading'] );
		
		$mailer = WC()->mailer();
		ob_start();
	
		// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Core WordPress/WooCommerce hook, not owned by this plugin.
		//do_action( 'woocommerce_email_header',  $email_heading,  $email ); 	
		$mailer->email_header( $email_heading, $recipient );		
		$email_body = get_option( 'cev_verification_email_body', $cev_initialise_customizer_settings->defaults['cev_verification_email_body'] );
		$email_body = WC_customer_email_verification_email_Common()->maybe_parse_merge_tags( $email_body );
		// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Established public filter of this plugin; renaming breaks existing integrations.
		$email_body = apply_filters( 'cev_verification_email_content', $email_body );
		$email_body = wpautop( $email_body );
		$email_body = wp_kses_post( $email_body );
		echo wp_kses_post( $email_body );
		
		$mailer->email_footer( $recipient );
		
		$email_body = ob_get_clean();
		$email_abstract_object = new WC_Email();
		
		
		// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Core WordPress/WooCommerce hook, not owned by this plugin.
		$email_body = apply_filters( 'woocommerce_mail_content', $email_abstract_object->style_inline( wptexturize( $email_body ) ) );		
			
		// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Established public filter of this plugin; renaming breaks existing integrations.
		$email_body = apply_filters( 'wc_cev_decode_html_content', $email_body );		
		
		$result = $mailer->send( $recipient, $email_subject, $email_body );

		return (bool) $result;
	}

	/**
	 * Check whether a verification email was already sent to this address moments ago.
	 *
	 * Guards against duplicate clicks on the Register button, each of which would
	 * otherwise send another email and regenerate the stored PIN.
	 *
	 * @param string $recipient Email address the code would be sent to.
	 * @return bool True when the last send is still inside the throttle window.
	 */
	public function is_verification_email_throttled( $recipient ) {

		// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Established public filter of this plugin; renaming breaks existing integrations.
		$throttle_seconds = (int) apply_filters( 'cev_verification_email_throttle_seconds', 30, $recipient );
		if ( 1 > $throttle_seconds ) {
			return false;
		}

		global $wpdb;

		// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQLPlaceholders.UnquotedComplexPlaceholder
		$last_sent = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT last_updated FROM {$wpdb->prefix}cev_user_log WHERE email = %s",
				$recipient
			)
		);
		// phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQLPlaceholders.UnquotedComplexPlaceholder

		if ( empty( $last_sent ) ) {
			return false;
		}

		$elapsed = strtotime( current_time( 'mysql' ) ) - strtotime( $last_sent );

		return 0 <= $elapsed && $elapsed < $throttle_seconds;
	}

	/**
	 * Get the from address for outgoing emails.
	 *
	 * @return string
	 */
	public function get_from_address() {
		// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Core WordPress/WooCommerce hook, not owned by this plugin.
		$from_address = apply_filters( 'woocommerce_email_from_address', get_option( 'woocommerce_email_from_address' ), $this );
		// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Established public filter of this plugin; renaming breaks existing integrations.
		$from_address = apply_filters( 'cev_email_from_address', $from_address, $this );
		return sanitize_email( $from_address );
	}
	
	/**
	 * Get the from name for outgoing emails.
	 *
	 * @return string
	 */
	public function get_from_name() {
		// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Core WordPress/WooCommerce hook, not owned by this plugin.
		$from_name = apply_filters( 'woocommerce_email_from_name', get_option( 'woocommerce_email_from_name' ), $this );
		// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Established public filter of this plugin; renaming breaks existing integrations.
		$from_name = apply_filters( 'cev_email_from_name', $from_name, $this );
		return wp_specialchars_decode( esc_html( $from_name ), ENT_QUOTES );
	}

	public function verify_otp() {
		
		global $wpdb;
		// Verify nonce
		
		$nonce = isset($_POST['nonce']) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';
		if (!$nonce || !wp_verify_nonce($nonce, 'verify_otp_nonce')) {
			wp_send_json_error(array('verified' => false, 'message' => __('Nonce verification failed.', 'customer-email-verification-for-woocommerce')));
		}
		$otp = isset($_POST['otp']) ? sanitize_text_field( wp_unslash( $_POST['otp'] ) ) : '';
		if ( '' === $otp ) {
			wp_send_json_error(array('verified' => false));
		}
		$email = isset($_POST['email']) ? sanitize_text_field( wp_unslash( $_POST['email'] ) ) : '';
		
		// Assume the OTP is "123456" for demonstration purposes
		if ( '' == $otp ) {
			echo wp_json_encode( array( 'success' => 'false' ));
			die();
		}

		// Cap wrong guesses per address so the short PIN cannot be brute-forced.
		$attempts_key = $this->otp_attempts_key( $email );
		$attempts     = (int) get_transient( $attempts_key );
		if ( $attempts >= $this->get_max_otp_attempts() ) {
			wp_send_json_error( array( 'verified' => false, 'message' => __( 'Too many incorrect attempts. Please wait a while, then request a new code.', 'customer-email-verification-for-woocommerce' ) ) );
		}

		// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQLPlaceholders.UnquotedComplexPlaceholder
		$row = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}cev_user_log WHERE email = %s AND pin = %s", $email, $otp));
		// phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQLPlaceholders.UnquotedComplexPlaceholder
		
		if ( $row && $this->is_code_expired( $row->last_updated ) ) {
			wp_send_json_error( array( 'verified' => false, 'message' => __( 'This code has expired. Click Resend to get a new code.', 'customer-email-verification-for-woocommerce' ) ) );
		}

		if ($row) {
			if ($row->verified) {
				wp_send_json_error(array('verified' => false, 'message' => __('Already verified.', 'customer-email-verification-for-woocommerce')));
			} else {
				// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQLPlaceholders.UnquotedComplexPlaceholder
				$wpdb->delete(
					"{$wpdb->prefix}cev_user_log",
					array('id' => $row->id),
					array('%d')
				);
				// phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQLPlaceholders.UnquotedComplexPlaceholder
				delete_transient( $attempts_key );
				// The account is created by the registration form submit that
				// follows; mark_otp_verified_customer() picks this up.
				set_transient( $this->otp_verified_key( $email ), 1, 15 * MINUTE_IN_SECONDS );
				wp_send_json_success(array('verified' => true, 'message' => __('Registration and verification successful', 'customer-email-verification-for-woocommerce'), 'redirect_url' =>  home_url() . '/my-account/'));
						
				
			}
		} else {
			++$attempts;
			set_transient( $attempts_key, $attempts, HOUR_IN_SECONDS );
			if ( $attempts >= $this->get_max_otp_attempts() ) {
				// Burn the pending code so the next send issues a fresh PIN
				// instead of reusing the one that was being guessed.
				// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQLPlaceholders.UnquotedComplexPlaceholder
				$wpdb->delete( "{$wpdb->prefix}cev_user_log", array( 'email' => $email ), array( '%s' ) );
				// phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQLPlaceholders.UnquotedComplexPlaceholder
			}
			wp_send_json_error(array('verified' => false, 'message' =>  __('The OTP you entered is incorrect. Please check your email and try again.', 'customer-email-verification-for-woocommerce')));
		}
	}

	/**
	 * Transient key counting wrong OTP guesses for an address.
	 *
	 * @param string $email Email address.
	 * @return string
	 */
	private function otp_attempts_key( $email ) {
		return 'cev_otp_attempts_' . md5( strtolower( trim( $email ) ) );
	}

	/**
	 * Wrong OTP guesses allowed per address within an hour.
	 *
	 * @return int
	 */
	private function get_max_otp_attempts() {
		// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Plugin's established cev_ filter prefix.
		return max( 1, (int) apply_filters( 'cev_signup_otp_max_attempts', 5 ) );
	}
	
	public function generate_verification_pin() {
		
		// OTP Length setting: '2' = 6 digits, anything else (incl. unset) = 4 digits.
		$digits = '2' === (string) get_option( 'cev_verification_code_length', '1' ) ? 6 : 4;
		
		$i = 0; //counter
		$pin = ''; //our default pin is blank.
		while ( $i < $digits ) {
			//generate a random number between 0 and 9.
			$pin .= wp_rand( 0, 9 );
			$i++;
		}		
		return $pin;
	}	
}

// Initialize the class
CEV_Signup_Verification::get_instance()->init();
