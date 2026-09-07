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

		$version    = woo_customer_email_verification()->version;
		$option_key = 'wc_cev_pro_ignore_notice_' . $version;
		$query_arg  = 'wc-cev-pro-ignore-notice-' . $version;

		// Check if the notice has been dismissed for the current plugin version.
		if ( get_option( $option_key ) ) {
			return;
		}

		// Check if we are on the "customer-email-verification-for-woocommerce" settings page.
		// If so, do not display the notice there.
		if ( isset( $_GET['page'] ) && 'customer-email-verification-for-woocommerce' === $_GET['page'] ) {
			return;
		}

		// Generate the dismissable URL with a query parameter to ignore the notice.
		$dismissable_url = esc_url( add_query_arg( $query_arg, 'true' ) );
		?>
		
		<style>
		.cev-dismissable-notice {
			border-left-color: #3b64d3 !important;
			padding-right: 38px;
		}
		.cev-dismissable-notice h3.cev-notice-title {
			margin-top: 10px;
			margin-bottom: 4px;
			font-size: 15px;
			font-weight: 700;
			color: #3b64d3;
		}
		.cev-notice-actions {
			display: flex;
			align-items: center;
			flex-wrap: wrap;
			gap: 8px;
			margin: 12px 0 14px;
		}
		.cev-notice-btn {
			display: inline-flex;
			align-items: center;
			gap: 6px;
			padding: 7px 16px !important;
			height: auto !important;
			line-height: 1.4 !important;
			font-size: 13px !important;
			font-weight: 600 !important;
			border-radius: 6px !important;
			text-decoration: none !important;
			cursor: pointer;
			transition: background .15s, border-color .15s, box-shadow .15s;
			box-shadow: none !important;
			outline-offset: 2px;
		}
		.cev-notice-btn-upgrade {
			background: #3b64d3 !important;
			border: 1px solid #3b64d3 !important;
			color: #fff !important;
		}
		.cev-notice-btn-upgrade:hover,
		.cev-notice-btn-upgrade:focus {
			background: #2d50b5 !important;
			border-color: #2d50b5 !important;
			color: #fff !important;
		}
		.cev-notice-btn-dismiss {
			background: #fff !important;
			border: 1px solid #c5cfe8 !important;
			color: #3b64d3 !important;
		}
		.cev-notice-btn-dismiss:hover,
		.cev-notice-btn-dismiss:focus {
			background: #eef2ff !important;
			border-color: #3b64d3 !important;
			color: #3b64d3 !important;
		}
		</style>

		<?php
		// Display the notice only if the Pro version of the plugin is not active.
		if ( ! class_exists( 'customer_email_verification_pro' ) ) {
			?>
			<div class="notice notice-info cev-dismissable-notice is-dismissible">
				<a href="<?php echo esc_url( $dismissable_url ); ?>" class="notice-dismiss">
					<span class="screen-reader-text"><?php esc_html_e( 'Dismiss this notice.', 'customer-email-verification-for-woocommerce' ); ?></span>
				</a>

				<h3 class="cev-notice-title">✉️ <?php esc_html_e( 'Upgrade to Customer Email Verification PRO – Secure Every Step of the Customer Journey!', 'customer-email-verification-for-woocommerce' ); ?></h3>
				<p><?php esc_html_e( 'Get advanced verification features to protect your store from spam, fake signups, and fraudulent orders:', 'customer-email-verification-for-woocommerce' ); ?></p>
				<p>
					✅ <?php esc_html_e( 'Verify emails during signup and checkout', 'customer-email-verification-for-woocommerce' ); ?><br>
					✅ <?php esc_html_e( 'OTP-based login authentication for secure access', 'customer-email-verification-for-woocommerce' ); ?><br>
					✅ <?php esc_html_e( 'Customizable verification popup & emails', 'customer-email-verification-for-woocommerce' ); ?><br>
					✅ <?php esc_html_e( 'Block fake registrations and unauthorized activity', 'customer-email-verification-for-woocommerce' ); ?>
				</p>
				<p>🎁 <?php esc_html_e( 'Special Offer: Get 20% OFF with coupon code CEVPRO20 – limited time only!', 'customer-email-verification-for-woocommerce' ); ?></p>

				<div class="cev-notice-actions">
					<a class="cev-notice-btn cev-notice-btn-upgrade" href="https://www.zorem.com/product/customer-email-verification/" target="_blank" rel="noreferrer noopener">
						👉 <?php esc_html_e( 'Upgrade to CEV PRO Now', 'customer-email-verification-for-woocommerce' ); ?>
					</a>
					<a class="cev-notice-btn cev-notice-btn-dismiss" href="<?php echo esc_url( $dismissable_url ); ?>">
						<?php esc_html_e( 'Dismiss', 'customer-email-verification-for-woocommerce' ); ?>
					</a>
				</div>
			</div>
		<?php
		}
	}

	/*
	* Hide admin notice when the "ignore" parameter is set in the URL.
	* This prevents the notice from being displayed again.
	*/
	public function cev_pro_notice_ignore() {
		$version       = woo_customer_email_verification()->version;
		$option_key    = 'wc_cev_pro_ignore_notice_' . $version;
		$query_arg     = 'wc-cev-pro-ignore-notice-' . $version;
		$query_arg_alt = str_replace( '.', '_', $query_arg );

		// WordPress converts dots to underscores in query parameter names,
		// so check for both forms.
		if ( isset( $_GET[ $query_arg ] ) || isset( $_GET[ $query_arg_alt ] ) ) {
			update_option( $option_key, 'true' );
			wp_safe_redirect( remove_query_arg( array( $query_arg, $query_arg_alt ) ) );
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
