<?php
/**
 * Customizer Setup and Custom Controls
 *
 */

class Cev_Initialise_Customizer_Settings {

	public $defaults;

	public function __construct() {
		// Store raw strings, not translated here!
		$this->defaults = $this->cev_generate_defaults();

		// Register controls
		add_action( 'customize_register', array( $this, 'cev_register_sample_default_controls' ) );

		// Only proceed if this is our own request.
		if ( ! self::is_own_customizer_request() && ! self::is_own_preview_request() ) {
			return;
		}

		add_action( 'customize_register', array( wc_cev_customizer(), 'cev_add_customizer_sections' ) );
		add_filter( 'customize_loaded_components', array( wc_cev_customizer(), 'remove_unrelated_components' ), 99, 2 );
		add_filter( 'customize_section_active', array( wc_cev_customizer(), 'remove_unrelated_sections' ), 10, 2 );
		add_action( 'woomail_footer', array( wc_cev_customizer(), 'unhook_divi' ), 10 );
		add_action( 'customize_preview_init', array( wc_cev_customizer(), 'unhook_flatsome' ), 50 );
		add_filter( 'customize_controls_enqueue_scripts', array( wc_cev_customizer(), 'enqueue_customizer_scripts' ) );
		add_action( 'parse_request', array( $this, 'set_up_preview' ) );
		add_action( 'customize_preview_init', array( $this, 'enqueue_preview_scripts' ) );
	}

	/**
	 * Enqueue preview CSS.
	 */
	public function enqueue_preview_scripts() {
		wp_enqueue_style( 'cev-preview-styles', woo_customer_email_verification()->plugin_dir_url() . 'assets/css/preview-styles.css', array(), time() );
	}

	/**
	 * Are we opening the custom preview?
	 */
	public static function is_own_preview_request() {
		return isset( $_REQUEST['cev-email-preview'] ) && '1' === $_REQUEST['cev-email-preview'];
	}

	/**
	 * Are we opening our own customizer controls?
	 */
	public static function is_own_customizer_request() {
		return isset( $_REQUEST['section'] ) && 'cev_main_controls_section' === $_REQUEST['section'];
	}

	/**
	 * Get Customizer URL.
	 */
	public static function get_customizer_url( $section ) {
		$customizer_url = add_query_arg(
			array(
				'cev-customizer' => '1',
				'section'        => $section,
				'url'            => urlencode( add_query_arg( array( 'cev-email-preview' => '1' ), home_url( '/' ) ) ),
			),
			admin_url( 'customize.php' )
		);

		return $customizer_url;
	}

	/**
	 * Store raw default strings.
	 */
	public function cev_generate_defaults() {
		return array(
			'cev_verification_email_heading' => 'Please Verify Your Email Address',
			'cev_verification_email_subject' => 'Please Verify Your Email Address on {site_title}',
			'cev_verification_email_body'    => 'Thank you for signing up for {site_title}. To activate your account, we need to verify your email address. <p>Your verification code: <strong>{cev_user_verification_pin}</strong></p>',
		);
	}

	/**
	 * Register Customizer controls.
	 */
	public function cev_register_sample_default_controls( $wp_customize ) {
		require_once trailingslashit( dirname( __FILE__ ) ) . 'custom-controls.php';

		// Email Subject
		$wp_customize->add_setting(
			'cev_verification_email_subject',
			array(
				'default'           => $this->defaults['cev_verification_email_subject'],
				'transport'         => 'refresh',
				'type'              => 'option',
				'sanitize_callback' => '',
			)
		);

		$wp_customize->add_control(
			'cev_verification_email_subject',
			array(
				'label'       => __( 'Subject', 'customer-email-verification-for-woocommerce' ),
				'section'     => 'cev_controls_section',
				'type'        => 'text',
				'priority'    => 1,
				'input_attrs' => array(
					'placeholder' => $this->defaults['cev_verification_email_subject'],
				),
			)
		);

		// Email Heading
		$wp_customize->add_setting(
			'cev_verification_email_heading',
			array(
				'default'           => $this->defaults['cev_verification_email_heading'],
				'transport'         => 'refresh',
				'type'              => 'option',
				'sanitize_callback' => '',
			)
		);

		$wp_customize->add_control(
			'cev_verification_email_heading',
			array(
				'label'       => __( 'Email Heading', 'customer-email-verification-for-woocommerce' ),
				'section'     => 'cev_controls_section',
				'type'        => 'text',
				'priority'    => 6,
				'input_attrs' => array(
					'placeholder' => $this->defaults['cev_verification_email_heading'],
				),
			)
		);

		// Email Body
		$wp_customize->add_setting(
			'cev_verification_email_body',
			array(
				'default'           => $this->defaults['cev_verification_email_body'],
				'transport'         => 'refresh',
				'type'              => 'option',
				'sanitize_callback' => '',
			)
		);

		$wp_customize->add_control(
			'cev_verification_email_body',
			array(
				'label'       => __( 'Verification Message', 'customer-email-verification-for-woocommerce' ),
				'section'     => 'cev_controls_section',
				'type'        => 'textarea',
				'input_attrs' => array(
					'placeholder' => $this->defaults['cev_verification_email_body'],
				),
			)
		);

		// Available variables block
		$wp_customize->add_setting(
			'cev_email_code_block',
			array(
				'default'           => '',
				'transport'         => 'postMessage',
				'sanitize_callback' => '',
			)
		);

		$wp_customize->add_control(
			new WP_Customize_cev_codeinfoblock_Control(
				$wp_customize,
				'cev_email_code_block',
				array(
					'label'       => __( 'Available variables', 'customer-email-verification-for-woocommerce' ),
					'description' => '<code>{site_title}<br>{cev_user_verification_pin}</code>',
					'section'     => 'cev_controls_section',
				)
			)
		);
	}

	/**
	 * Handle preview request.
	 */
	public function set_up_preview() {
		if ( ! self::is_own_preview_request() ) {
			return;
		}
		include woo_customer_email_verification()->get_plugin_path() . '/includes/customizer/preview/preview.php';
		exit;
	}

	/**
	 * Preview email content.
	 */
	public function preview_account_email() {
		$wc_emails = WC_Emails::instance();
		$emails    = $wc_emails->get_emails();
		WC_customer_email_verification_email_Common()->wuev_user_id = 1;

		$email_heading = get_option( 'cev_verification_email_heading', $this->defaults['cev_verification_email_heading'] );
		$email_heading = __( $email_heading, 'customer-email-verification-for-woocommerce' );
		$email_heading = WC_customer_email_verification_email_Common()->maybe_parse_merge_tags( $email_heading );

		$email_content = get_option( 'cev_verification_email_body', $this->defaults['cev_verification_email_body'] );
		$email_content = __( $email_content, 'customer-email-verification-for-woocommerce' );
		$email_content = WC_customer_email_verification_email_Common()->maybe_parse_merge_tags( $email_content );
		$email_content = apply_filters( 'cev_verification_email_content', $email_content );
		$email_content = wpautop( $email_content );
		$email_content = wp_kses_post( $email_content );

		$mailer = WC()->mailer();
		$email  = new WC_Email();
		$email->id = 'Customer_New_Account';

		$message = apply_filters( 'woocommerce_mail_content', $email->style_inline( $mailer->wrap_message( $email_heading, $email_content ) ) );
		$message = apply_filters( 'wc_cev_decode_html_content', $message );

		echo wp_kses_post( $message );
	}
}

$cev_customizer_settings = new Cev_Initialise_Customizer_Settings();
