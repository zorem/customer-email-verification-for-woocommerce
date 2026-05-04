<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WC_CEV_Admin_Notices_Under_WC_Admin {

	/**
	 * Instance of this class.
	 *
	 * @var object Class Instance
	 */
	private static $instance;
	
	/**
	 * Initialize the main plugin function
	*/
	public function __construct() {
		$this->init();
	}
	
	/**
	 * Get the class instance
	 *
	 * @return WC_CEV_Admin_Notices_Under_WC_Admin
	*/
	public static function get_instance() {

		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}
	
	/*
	* init from parent mail class
	*/
	public function init() {
		add_action( 'admin_notices', array( $this, 'admin_notice_pro_update' ) );
		
		// Handle dismiss action for the Pro version update notice.
		add_action('admin_init', array( $this, 'cev_pro_notice_ignore' ) );

	}

	/*
	* Display an admin notice on plugin install or update
	*/
	public function admin_notice_pro_update() { 		
		
		// Check if the notice has been dismissed before, and if so, do not display it.
		if ( get_option('wc_cev_pro_ignore_notice_2.6.8') ) {
			return;
		}
		
		// Check if we are on the "customer-email-verification-for-woocommerce" settings page.
		// If so, do not display the notice there.
		if ( isset( $_GET['page'] ) && 'customer-email-verification-for-woocommerce' === $_GET['page'] ) {
			return;
		}
		
		// Generate the dismissable URL with a query parameter to ignore the notice.
		$dismissable_url = esc_url( add_query_arg( 'wc-cev-pro-ignore-notice-2.6.8', 'true' ) );
		?>
		
		<style>		
		/* Styling for the dismissable admin notice */
		.wp-core-ui .notice.cev-dismissable-notice {
			position: relative;
			padding-right: 38px;
			border-left-color: #3b64d3;
		}
		
		/* Styling for the dismiss button */
		.wp-core-ui .notice.cev-dismissable-notice a.notice-dismiss {
			padding: 9px;
			text-decoration: none;
		} 
		
		/* Styling for the Pro upgrade button */
		.wp-core-ui .button-primary.btn_pro_notice {
			background: transparent;
			color: #395da4;
			border-color: #395da4;
			text-transform: uppercase;
			padding: 0 11px;
			font-size: 12px;
			height: 30px;
			line-height: 28px;
			margin: 5px 0 15px;
		}
		</style>
		
		<?php 
		// Display the notice only if the Pro version of the plugin is not active.
		if ( !class_exists( 'customer_email_verification_pro' ) ) { 
			?>
			<div class="notice updated notice-success cev-dismissable-notice is-dismissible">
				<!-- Dismiss button -->
				<a href="<?php echo esc_url( $dismissable_url ); ?>" class="notice-dismiss">
					<span class="screen-reader-text">Dismiss this notice.</span>
				</a>
				
				<!-- Notice content -->
				<h3 style="margin-top: 10px; color:#3b64d3;font-size:16px">✉️ Upgrade to Customer Email Verification PRO – Secure Every Step of the Customer Journey!</h3>
				<p>Get advanced verification features to protect your store from spam, fake signups, and fraudulent orders:</p>
				<p>✅ Verify emails during signup and checkout<br>✅ OTP-based login authentication for secure access<br>✅ Customizable verification popup & emails<br>✅ Block fake registrations and unauthorized activity<br></p>
				<p>🎁 Special Offer: Get 20% OFF with coupon code CEVPRO20 – limited time only!</p>
				<!-- Upgrade button -->
				<a class="button-primary btn_pro_notice" target="_blank" 
					href="https://www.zorem.com/product/customer-email-verification/" style="background:#3b64d3;font-size:14px; border:1px solid #3b64d3; margin-bottom:10px; color:#fff;">👉 Upgrade to CEV PRO Now</a>
				
				<!-- Dismiss button -->
				<a class="button-primary ast_notice_btn" href="<?php echo esc_url( $dismissable_url ); ?>" style="background:#3b64d3;font-size:14px; border:1px solid #3b64d3; margin-bottom:10px;" >Dismiss</a>
			</div>
		<?php
		}
	}

	/*
	* Hide admin notice when the "ignore" parameter is set in the URL.
	* This prevents the notice from being displayed again.
	*/
	public function cev_pro_notice_ignore() {
		// WordPress converts dots to underscores in query parameter names
		// Check for both versions to handle the conversion
		$dismissed = false;
		if ( isset( $_GET['wc-cev-pro-ignore-notice-2.6.8'] ) ) {
			$dismissed = true;
		} elseif ( isset( $_GET['wc-cev-pro-ignore-notice-2_6_8'] ) ) {
			$dismissed = true;
		}
		
		if ( $dismissed ) {
			update_option( 'wc_cev_pro_ignore_notice_2.6.8', 'true' );
			// Redirect to clean up the URL
			wp_safe_redirect( remove_query_arg( array( 'wc-cev-pro-ignore-notice-2.6.8', 'wc-cev-pro-ignore-notice-2_6_8' ) ) );
			exit;
		}
	}
}

/**
 * Returns an instance of WC_CEV_Admin_Notices_Under_WC_Admin.
 *
 * @since 1.6.5
 * @version 1.6.5
 *
 * @return WC_CEV_Admin_Notices_Under_WC_Admin
*/
function WC_CEV_Admin_Notices_Under_WC_Admin() {
	static $instance;

	if ( ! isset( $instance ) ) {		
		$instance = new WC_CEV_Admin_Notices_Under_WC_Admin();
	}

	return $instance;
}

/**
 * Register this class globally.
 *
 * Backward compatibility.
*/
WC_CEV_Admin_Notices_Under_WC_Admin();
