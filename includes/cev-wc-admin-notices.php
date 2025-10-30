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
		
	// Display an admin notice for Pro version update.
		if ( is_plugin_active( 'woocommerce-subscriptions/woocommerce-subscriptions.php' ) ) {
			add_action( 'admin_notices', array( $this, 'admin_notice_pro_update' ) );
		}
		// Handle dismiss action for the Pro version update notice.
		add_action('admin_init', array( $this, 'cev_pro_notice_ignore' ) );

	}

	/*
	* Display an admin notice on plugin install or update
	*/
	public function admin_notice_pro_update() { 		
		
		// Check if the notice has been dismissed before, and if so, do not display it.
		if ( get_option('wc_introducing_analytics_for_wooCommerce_subscriptions_ignore') ) {
			return;
		}
		
		// Check if we are on the "customer-email-verification-for-woocommerce" settings page.
		// If so, do not display the notice there.
		if ( isset( $_GET['page'] ) && 'customer-email-verification-for-woocommerce' === $_GET['page'] ) {
			return;
		}
		
		// Generate the dismissable URL with a query parameter to ignore the notice.
		$dismissable_url = esc_url( add_query_arg( 'wc-introducing-analytics-for-wooCommerce-subscriptions-ignore-notice', 'true' ) );
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
			<div class="notice updated notice-success cev-dismissable-notice">
				<!-- Dismiss button -->
				<a href="<?php echo esc_url( $dismissable_url ); ?>" class="notice-dismiss">
					<span class="screen-reader-text">Dismiss this notice.</span>
				</a>
				
				<!-- Notice content -->
				<h3 style="margin-top: 10px; color:#3b64d3;font-size:16px">🚀 Introducing Analytics for WooCommerce Subscriptions</h3>
				<p>Get powerful insights with <a href="https://woocommerce.com/products/analytics-for-woocommerce-subscriptions/" target="_blank">Analytics for WooCommerce Subscriptions</a> — the all-in-one dashboard to track signups, renewals, cancellations, and recurring revenue.</p>
				<p>Discover which products and customers drive the most value, reduce churn, and grow your subscription income with data-driven decisions.</p>
				<!-- Upgrade button -->
				<a class="button-primary btn_pro_notice" target="_blank" 
					href="https://woocommerce.com/products/analytics-for-woocommerce-subscriptions/" style="background:#3b64d3;font-size:14px; border:1px solid #3b64d3; margin-bottom:10px; color:#fff;">👉 Learn More on WooCommerce</a>
				
				<!-- Dismiss button -->
				<a class="button-primary ast_notice_btn" href="<?php esc_html_e( $dismissable_url ); ?>" style="background:#3b64d3;font-size:14px; border:1px solid #3b64d3; margin-bottom:10px;" >Dismiss</a>
			</div>
		<?php
		}
	}

	/*
	* Hide admin notice when the "ignore" parameter is set in the URL.
	* This prevents the notice from being displayed again.
	*/
	public function cev_pro_notice_ignore() {
		if ( isset( $_GET['wc-introducing-analytics-for-wooCommerce-subscriptions-ignore-notice'] ) ) {
			update_option( 'wc_introducing_analytics_for_wooCommerce_subscriptions_ignore', 'true' );
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
