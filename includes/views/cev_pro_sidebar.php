<?php
/**
 * PRO Upgrade Sidebar
 * Displays the Upgrade to PRO card and Documentation link
 * Only visible on Settings tab
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<!-- Upgrade to PRO Card -->
<div class="cev-sidebar-card cev-sidebar-upgrade">
	<div class="cev-sidebar-upgrade-icon">
		<span class="dashicons dashicons-email-alt"></span>
	</div>
	<h3><?php esc_html_e( 'Unlock Advanced Email Verification with CEV PRO', 'customer-email-verification-for-woocommerce' ); ?></h3>
	<p><?php esc_html_e( 'Upgrade to CEV PRO to secure your WooCommerce store with advanced email verification and login authentication beyond the basics.', 'customer-email-verification-for-woocommerce' ); ?></p>
	<ul class="cev-sidebar-features">
		<li><?php esc_html_e( 'Email verification at checkout', 'customer-email-verification-for-woocommerce' ); ?></li>
		<li><?php esc_html_e( 'OTP-based login authentication', 'customer-email-verification-for-woocommerce' ); ?></li>
		<li><?php esc_html_e( 'OTP expiration & resend limits', 'customer-email-verification-for-woocommerce' ); ?></li>
		<li><?php esc_html_e( 'Unrecognized login detection', 'customer-email-verification-for-woocommerce' ); ?></li>
		<li><?php esc_html_e( 'Fully customizable verification emails', 'customer-email-verification-for-woocommerce' ); ?></li>
	</ul>
	<a href="https://www.zorem.com/product/customer-email-verification/" class="cev-sidebar-upgrade-btn button-primary" target="_blank">
		<?php esc_html_e( 'UPGRADE TO CEV PRO→', 'customer-email-verification-for-woocommerce' ); ?>
	</a>
	<?php // Removed "TRUSTED BY 30,000+ STORES" tagline as per customization request. ?>
</div>

<!-- Need Help / Documentation Card -->
<div class="cev-sidebar-card cev-sidebar-help">
	<div class="cev-sidebar-help-icon">
		<span class="dashicons dashicons-editor-help"></span>
	</div>
	<h4><?php esc_html_e( 'Need help?', 'customer-email-verification-for-woocommerce' ); ?></h4>
	<p><?php esc_html_e( 'Check our documentation or contact our support team if you have any questions.', 'customer-email-verification-for-woocommerce' ); ?></p>
	<a href="https://docs.zorem.com/docs/customer-email-verification-pro/" class="cev-sidebar-doc-link" target="_blank">
		<?php esc_html_e( 'View Documentation →', 'customer-email-verification-for-woocommerce' ); ?>
	</a>
</div>
