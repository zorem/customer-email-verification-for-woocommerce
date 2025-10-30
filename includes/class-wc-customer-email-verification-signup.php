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

		// Authenticate user via email link (logged-in check included).
		add_action( 'wp', array( $this, 'authenticate_user_by_email_link' ) );	

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
			'cev_password_validation' => __( 'Password is required.', 'customer-email-verification' ),
			'cev_email_validation' => __( 'Email is required.', 'customer-email-verification' ),
			'cev_email_exists_validation' => __( 'An account with this email address already exists. Please use a different email or log in to your existing account.', 'customer-email-verification' ),
			'cev_valid_email_validation' => __( 'Enter a valid email address.', 'customer-email-verification' ),
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
		$nonce = isset($_POST['nonce']) ? sanitize_text_field($_POST['nonce']) : '';
		if (!$nonce || !wp_verify_nonce($nonce, 'verify_otp_nonce')) {
			wp_send_json_error(array(
				'verified' => false,
				'message'  => __('Nonce verification failed.', 'customer-email-verification'),
			));
		}

		// Get recipient email from POST data.
		$recipient = isset($_POST['email']) ? sanitize_email($_POST['email']) : '';
		if (empty($recipient)) {
			wp_send_json_error(array(
				'verified' => false,
				'message'  => __('Email address is required.', 'customer-email-verification'),
			));
		}
		 // Send the OTP email.
		 $result = $this->send_signup_verification_email($recipient);

		 // Return success or failure based on email send result.
		if ($result) {
			 wp_send_json_success(array(
				 'email' => $recipient,
				 'message' => __('OTP has been resent successfully.', 'customer-email-verification'),
			 ));
		} else {
			 wp_send_json_error(array(
				 'verified' => false,
				 'message'  => __('Failed to resend OTP. Please try again.', 'customer-email-verification'),
			 ));
		}
		
	}
	public function check_email_exists() {

		  // Verify nonce for security.
		$nonce = isset($_POST['nonce']) ? sanitize_text_field($_POST['nonce']) : '';
		if (!$nonce || !wp_verify_nonce($nonce, 'verify_otp_nonce')) {
			  wp_send_json_error(array(
				  'verified' => false,
				  'message'  => __('Nonce verification failed.', 'customer-email-verification'),
			  ));
		}
		$email = isset($_POST['email']) ? sanitize_email($_POST['email']) : '';
		if (!is_email($email)) {
			wp_send_json_error(array(
				'not_valid' => true,
				'message'   => __('Invalid email address.', 'customer-email-verification'),
			));
		}

		if (email_exists($email)) {
			wp_send_json_success(array(
				'exists'  => true,
				'message' => __('An account with this email already exists.', 'customer-email-verification'),
			));
		}
		$errors = new WP_Error();		
		$errors = apply_filters( 'woocommerce_registration_errors', $errors, '', $email );
		$errors = apply_filters( 'registration_errors', $errors, '', $email );

		if ( $errors->has_errors() ) {			
			wp_send_json_success(array(
				'validation' => false,
				'message' => $errors->get_error_message(),
			));
		}
		
		// Send OTP email for verification.
		$email_sent = $this->send_signup_verification_email($email);
		if ($email_sent) {
			wp_send_json_success(array(
				'email'   => $email,
				'message' => __('Verification email has been sent.', 'customer-email-verification'),
			));
		}
	}

	public function send_signup_verification_email( $recipient ) {

		woo_customer_email_verification()->install->create_user_log_table();
		$verification_pin = WC_customer_email_verification_email_Common()->generate_verification_pin();
		$cev_initialise_customizer_settings = new cev_initialise_customizer_settings();	
		$expire_time =  get_option('cev_verification_code_expiration', 'never');
		$secret_code = md5( $recipient . time() );
		if ( empty( $expire_time ) ) {
			$expire_time = 'never';
		}
		
		global $wpdb;
		
		$email_exists = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$wpdb->prefix}cev_user_log WHERE email = %s", $recipient));
		$current_time = current_time('mysql');
		if ($email_exists) {
			// Update the existing record
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
		} else {
			// Insert a new record
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
		}
		 WC_customer_email_verification_email_Common()->registerd_user_email  = $recipient;
		$result = false;		
		
		$email_subject = get_option( 'cev_verification_email_subject', $cev_initialise_customizer_settings->defaults['cev_verification_email_subject'] );
		$email_subject = WC_customer_email_verification_email_Common()->maybe_parse_merge_tags( $email_subject );
		
		$email_heading = get_option( 'cev_verification_email_heading', $cev_initialise_customizer_settings->defaults['cev_verification_email_heading'] );
		
		$mailer = WC()->mailer();
		ob_start();
	
		//do_action( 'woocommerce_email_header',  $email_heading,  $email ); 	
		$mailer->email_header( $email_heading, $recipient );		
		$email_body = get_option( 'cev_verification_email_body', $cev_initialise_customizer_settings->defaults['cev_verification_email_body'] );
		$email_body = WC_customer_email_verification_email_Common()->maybe_parse_merge_tags( $email_body );
		$email_body = apply_filters( 'cev_verification_email_content', $email_body );
		$email_body = wpautop( $email_body );
		$email_body = wp_kses_post( $email_body );
		echo wp_kses_post( $email_body );
		
		$mailer->email_footer( $recipient );
		
		$email_body = ob_get_clean();
		$email_abstract_object = new WC_Email();
		
		
		$email_body = apply_filters( 'woocommerce_mail_content', $email_abstract_object->style_inline( wptexturize( $email_body ) ) );		
			
		$email_body = apply_filters( 'wc_cev_decode_html_content', $email_body );		
		
		$result = $mailer->send( $recipient, $email_subject, $email_body );

		// Return result.
		wp_send_json_success(array('email' => $result));
	}

	/**
	 * Get the from address for outgoing emails.
	 *
	 * @return string
	 */
	public function get_from_address() {
		$from_address = apply_filters( 'woocommerce_email_from_address', get_option( 'woocommerce_email_from_address' ), $this );
		$from_address = apply_filters( 'cev_email_from_address', $from_address, $this );
		return sanitize_email( $from_address );
	}
	
	/**
	 * Get the from name for outgoing emails.
	 *
	 * @return string
	 */
	public function get_from_name() {
		$from_name = apply_filters( 'woocommerce_email_from_name', get_option( 'woocommerce_email_from_name' ), $this );
		$from_name = apply_filters( 'cev_email_from_name', $from_name, $this );
		return wp_specialchars_decode( esc_html( $from_name ), ENT_QUOTES );
	}

	public function verify_otp() {
		
		global $wpdb;
		// Verify nonce
		
		$nonce = isset($_POST['nonce']) ? sanitize_text_field($_POST['nonce']) : '';
		if (!$nonce || !wp_verify_nonce($nonce, 'verify_otp_nonce')) {
			wp_send_json_error(array('verified' => false, 'message' => __('Nonce verification failed.', 'customer-email-verification')));
		}
		if (!isset($_POST['otp'])) {
			wp_send_json_error(array('verified' => false));
		}
		$otp = isset($_POST['otp']) ? sanitize_text_field($_POST['otp']) : '';
		$email = isset($_POST['email']) ? sanitize_text_field($_POST['email']) : '';
		
		// Assume the OTP is "123456" for demonstration purposes
		if ( '' == $otp ) {
			echo json_encode( array( 'success' => 'false' ));
			die();
		}
		$row = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}cev_user_log WHERE email = %s AND pin = %s", $email, $otp));
		
		if ($row) {
			if ($row->verified) {
				wp_send_json_error(array('verified' => false, 'message' => __('Already verified.', 'customer-email-verification')));
			} else {
				$wpdb->delete(
					"{$wpdb->prefix}cev_user_log",
					array('id' => $row->id),
					array('%d')
				);
				wp_send_json_success(array('verified' => true, 'message' => __('Registration and verification successful', 'customer-email-verification'), 'redirect_url' =>  home_url() . '/my-account/'));
						
				
			}
		} else {
			wp_send_json_error(array('verified' => false, 'message' =>  __('The OTP you entered is incorrect. Please check your email and try again.', 'customer-email-verification')));
		}
	}
	public function authenticate_user_by_email_link() {
		global $wpdb;
		if ( isset( $_GET['cusomer_email_verify'] ) && '' !== $_GET['cusomer_email_verify'] ) {
			$cusomer_email_verify = wc_clean( $_GET['cusomer_email_verify'] );
			$user_meta = explode( '@', base64_decode( $cusomer_email_verify ) ); 
			$email_secret_code = $user_meta[0];
	
			$result = $wpdb->get_row(
				$wpdb->prepare(
					"SELECT email, password FROM {$wpdb->prefix}cev_user_log WHERE secret_code = %s",
					$email_secret_code
				),
				ARRAY_A
			);
	
			if ($result) {
				$email = $result['email'];
				$password = $result['password'];
				$user = get_user_by( 'email', $email );
	
				if ( $user && !is_wp_error( $user ) ) {
					// User exists
					update_user_meta( $user->ID, 'customer_email_verified', 'true' );
					update_user_meta( $user->ID, 'cev_user_resend_times', 0 );
					wc_maybe_store_user_agent($user->user_login, $user);
					wp_send_json_success(array('verified' => true, 'message' => __('Verification successful', 'customer-email-verification'), 'redirect_url' => home_url()));
				} else {
					// User does not exist, create a new customer
					$new_customer = wc_create_new_customer( sanitize_email( $email ), '', $password );
					if ( !is_wp_error($new_customer) ) {
						update_user_meta($new_customer, 'customer_email_verified', 'true');
						update_user_meta($new_customer, 'cev_user_resend_times', 0);
	
						wp_set_current_user($new_customer);
						wp_set_auth_cookie($new_customer);
						do_action('wp_login', $email, get_userdata($new_customer));
	
						// wp_send_json_success(array('verified' => true, 'message' => 'Registration and verification successful', 'redirect_url' => home_url() . '/my-account/'));
						wp_redirect( home_url() . '/my-account/' );
						exit;
					} else {
						wp_send_json_error(array('verified' => false, 'message' =>  __('Error creating user', 'customer-email-verification')));
					}
				}	
			}
		}
	}
	
	public function generate_verification_pin() {
		
		$code_length = get_option('cev_verification_code_length', 4);
		
		if ( '1' == $code_length ) {
			$digits = 4;
		} else if ( '2' == $code_length ) {
			$digits = 6;
		} else {
			$digits = 9;
		}
		
		$i = 0; //counter
		$pin = ''; //our default pin is blank.
		while ( $i < $digits ) {
			//generate a random number between 0 and 9.
			$pin .= mt_rand(0, 9);
			$i++;
		}		
		return $pin;
	}	
}

// Initialize the class
CEV_Signup_Verification::get_instance()->init();
