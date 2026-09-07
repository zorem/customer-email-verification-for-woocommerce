<?php
/**
 * Go Pro tab body (ZUI redesign, Free).
 *
 * Visual design ported from the React/Tailwind prototype at
 * `C:\Users\Yash zorem\Downloads\CEV-free\src\components\LicenseTab.tsx`.
 *
 * Layout:
 *   1. Centered hero (title + body + primary CTA)
 *   2. Feature comparison table — 25 features, 3 columns
 *      (Feature / CEV FREE / CEV PRO with mint-green tint + RECOMMENDED badge)
 *   3. Bottom CTA (button + supporting note)
 *   4. "Powerful Add-ons" 3-col grid of 6 plugin cards
 *
 * All cosmetics live in cev-admin.css under .cev-gopro-* (no inline
 * <style> blocks). Pro-only features are described as upsell content,
 * not implemented as functionality.
 *
 * @package Customer_Email_Verification_For_WooCommerce
 * @since 2.7
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'is_plugin_active' ) ) {
	include_once ABSPATH . 'wp-admin/includes/plugin.php';
}

$cev_plugin_url = woo_customer_email_verification()->plugin_dir_url();
$cev_pro_url    = 'https://www.zorem.com/product/customer-email-verification/?utm_source=wp-admin&utm_medium=cev-go-pro&utm_campaign=hero';
$cev_cta_url    = 'https://www.zorem.com/product/customer-email-verification/?utm_source=wp-admin&utm_medium=cev-go-pro&utm_campaign=get-started';

// Inline SVG icons (Lucide check + x — same as React prototype).
$cev_svg_check = '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><polyline points="20 6 9 17 4 12"/></svg>';
$cev_svg_x     = '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>';

// Inline icon helper for ecosystem grid logos (same Lucide source as CEV Pro).
$cev_lic_icon = function ( $name ) {
	$paths = array(
		'ast-package'   => '<path d="M11 21.73a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73z"/><path d="M12 22V12"/><path d="m3.3 7 7.703 4.734a2 2 0 0 0 1.994 0L20.7 7"/><path d="m7.5 4.27 9 5.15"/>',
		'truck'         => '<path d="M5 18H3c-.6 0-1-.4-1-1V7c0-.6.4-1 1-1h10c.6 0 1 .4 1 1v11"/><path d="M14 9h4l4 4v4c0 .6-.4 1-1 1h-2"/><circle cx="7" cy="18" r="2"/><circle cx="17" cy="18" r="2"/>',
		'phone'         => '<path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>',
		'store'         => '<path d="m2 7 4.41-4.41A2 2 0 0 1 7.83 2h8.34a2 2 0 0 1 1.42.59L22 7"/><path d="M4 12v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8"/><path d="M15 22v-4a2 2 0 0 0-2-2h-2a2 2 0 0 0-2 2v4"/><path d="M2 7h20"/><path d="M22 7v3a2 2 0 0 1-4 0V7"/><path d="M18 10a2 2 0 0 1-4 0V7"/><path d="M14 10a2 2 0 0 1-4 0V7"/><path d="M10 10a2 2 0 0 1-4 0V7"/><path d="M6 10a2 2 0 0 1-4 0V7"/>',
		'globe'         => '<circle cx="12" cy="12" r="10"/><path d="M12 2a14.5 14.5 0 0 0 0 20 14.5 14.5 0 0 0 0-20"/><path d="M2 12h20"/>',
		'mail'          => '<rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/>',
		'sparkles'      => '<path d="M12 3l1.9 5.8a2 2 0 0 0 1.3 1.3L21 12l-5.8 1.9a2 2 0 0 0-1.3 1.3L12 21l-1.9-5.8a2 2 0 0 0-1.3-1.3L3 12l5.8-1.9a2 2 0 0 0 1.3-1.3z"/>',
		'external-link' => '<path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/>',
		'search'        => '<circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>',
	);
	$d = isset( $paths[ $name ] ) ? $paths[ $name ] : '';
	return '<svg class="zui-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $d . '</svg>';
};

$cev_print_html = function ( $html ) {
	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Callers pass SVG / HTML built from constant maps with no user input; wp_kses_post would strip required SVG attributes.
	echo $html;
};

/* ─── Feature comparison data (25 rows, matches React prototype) ─── */
$cev_compare_features = array(
	array( 'title' => __( 'Basic Registration Verification', 'customer-email-verification-for-woocommerce' ),
		'desc' => __( 'Require new users to confirm their email during signup.', 'customer-email-verification-for-woocommerce' ),
		'free_type' => 'check', 'free_text' => '', 'pro' => __( 'INCLUDED', 'customer-email-verification-for-woocommerce' ), 'is_new' => false ),
	array( 'title' => __( 'Checkout Verification', 'customer-email-verification-for-woocommerce' ),
		'desc' => __( 'Require customers to confirm their email before placing an order.', 'customer-email-verification-for-woocommerce' ),
		'free_type' => 'x', 'free_text' => __( 'NOT AVAILABLE', 'customer-email-verification-for-woocommerce' ), 'pro' => __( 'INCLUDED', 'customer-email-verification-for-woocommerce' ), 'is_new' => false ),
	array( 'title' => __( 'Login Verification (New Device)', 'customer-email-verification-for-woocommerce' ),
		'desc' => __( 'Trigger OTP verification when a customer logs in from a new device.', 'customer-email-verification-for-woocommerce' ),
		'free_type' => 'x', 'free_text' => __( 'NOT AVAILABLE', 'customer-email-verification-for-woocommerce' ), 'pro' => __( 'INCLUDED', 'customer-email-verification-for-woocommerce' ), 'is_new' => false ),
	array( 'title' => __( 'OTP / Popup Verification', 'customer-email-verification-for-woocommerce' ),
		'desc' => __( 'OTP-based or popup-based email verification flow.', 'customer-email-verification-for-woocommerce' ),
		'free_type' => 'check', 'free_text' => '', 'pro' => __( 'INCLUDED', 'customer-email-verification-for-woocommerce' ), 'is_new' => false ),
	array( 'title' => __( 'Customizable Email Templates', 'customer-email-verification-for-woocommerce' ),
		'desc' => __( 'Edit the design and content of verification emails.', 'customer-email-verification-for-woocommerce' ),
		'free_type' => 'check', 'free_text' => '', 'pro' => __( 'INCLUDED', 'customer-email-verification-for-woocommerce' ), 'is_new' => false ),
	array( 'title' => __( 'Customizable Verification Popup', 'customer-email-verification-for-woocommerce' ),
		'desc' => __( 'Tailor the verification popup to match your store branding.', 'customer-email-verification-for-woocommerce' ),
		'free_type' => 'check', 'free_text' => '', 'pro' => __( 'INCLUDED', 'customer-email-verification-for-woocommerce' ), 'is_new' => false ),
	array( 'title' => __( 'Login Notification Emails', 'customer-email-verification-for-woocommerce' ),
		'desc' => __( 'Notify customers by email when their account is accessed.', 'customer-email-verification-for-woocommerce' ),
		'free_type' => 'x', 'free_text' => __( 'NOT AVAILABLE', 'customer-email-verification-for-woocommerce' ), 'pro' => __( 'INCLUDED', 'customer-email-verification-for-woocommerce' ), 'is_new' => false ),
	array( 'title' => __( 'HPOS Compatible', 'customer-email-verification-for-woocommerce' ),
		'desc' => __( 'Works with WooCommerce High-Performance Order Storage.', 'customer-email-verification-for-woocommerce' ),
		'free_type' => 'check', 'free_text' => '', 'pro' => __( 'INCLUDED', 'customer-email-verification-for-woocommerce' ), 'is_new' => false ),
	array( 'title' => __( 'WooCommerce Block Checkout', 'customer-email-verification-for-woocommerce' ),
		'desc' => __( 'Compatible with the new WooCommerce block-based checkout.', 'customer-email-verification-for-woocommerce' ),
		'free_type' => 'x', 'free_text' => __( 'NOT AVAILABLE', 'customer-email-verification-for-woocommerce' ), 'pro' => __( 'INCLUDED', 'customer-email-verification-for-woocommerce' ), 'is_new' => false ),
	array( 'title' => __( '2FA via Authenticator App', 'customer-email-verification-for-woocommerce' ),
		'desc' => __( 'Add two-factor authentication using Google Authenticator or similar apps.', 'customer-email-verification-for-woocommerce' ),
		'free_type' => 'x', 'free_text' => __( 'NOT AVAILABLE', 'customer-email-verification-for-woocommerce' ), 'pro' => __( 'AUTHENTICATOR 2FA', 'customer-email-verification-for-woocommerce' ), 'is_new' => false ),
	array( 'title' => __( 'Role-Based Verification Rules', 'customer-email-verification-for-woocommerce' ),
		'desc' => __( 'Apply different verification rules based on user roles.', 'customer-email-verification-for-woocommerce' ),
		'free_type' => 'x', 'free_text' => __( 'NOT AVAILABLE', 'customer-email-verification-for-woocommerce' ), 'pro' => __( 'ROLE RULES', 'customer-email-verification-for-woocommerce' ), 'is_new' => false ),
	array( 'title' => __( 'Verification Analytics Dashboard', 'customer-email-verification-for-woocommerce' ),
		'desc' => __( 'Track verification metrics, success rates, and failed attempts.', 'customer-email-verification-for-woocommerce' ),
		'free_type' => 'x', 'free_text' => __( 'NOT AVAILABLE', 'customer-email-verification-for-woocommerce' ), 'pro' => __( 'FULL ANALYTICS', 'customer-email-verification-for-woocommerce' ), 'is_new' => false ),
	array( 'title' => __( 'Disposable Email Blocking (3,000+)', 'customer-email-verification-for-woocommerce' ),
		'desc' => __( 'Block signups from a curated list of 3,000+ disposable email domains.', 'customer-email-verification-for-woocommerce' ),
		'free_type' => 'x', 'free_text' => __( 'NOT AVAILABLE', 'customer-email-verification-for-woocommerce' ), 'pro' => __( '3,000+ DOMAINS', 'customer-email-verification-for-woocommerce' ), 'is_new' => false ),
	array( 'title' => __( 'DNS-Based MX Validation', 'customer-email-verification-for-woocommerce' ),
		'desc' => __( 'Validate that an email domain has a working mail server before accepting.', 'customer-email-verification-for-woocommerce' ),
		'free_type' => 'x', 'free_text' => __( 'NOT AVAILABLE', 'customer-email-verification-for-woocommerce' ), 'pro' => __( 'MX VALIDATION', 'customer-email-verification-for-woocommerce' ), 'is_new' => false ),
	array( 'title' => __( 'B2B Allowlist Mode', 'customer-email-verification-for-woocommerce' ),
		'desc' => __( 'Allow only approved business domains to register or check out.', 'customer-email-verification-for-woocommerce' ),
		'free_type' => 'x', 'free_text' => __( 'NOT AVAILABLE', 'customer-email-verification-for-woocommerce' ), 'pro' => __( 'DOMAIN ALLOWLIST', 'customer-email-verification-for-woocommerce' ), 'is_new' => false ),
	array( 'title' => __( 'Block Specific Email Addresses', 'customer-email-verification-for-woocommerce' ),
		'desc' => __( 'Maintain a blocklist of email addresses that cannot register or order.', 'customer-email-verification-for-woocommerce' ),
		'free_type' => 'x', 'free_text' => __( 'NOT AVAILABLE', 'customer-email-verification-for-woocommerce' ), 'pro' => __( 'CUSTOM BLOCKLIST', 'customer-email-verification-for-woocommerce' ), 'is_new' => false ),
	array( 'title' => __( 'Auto-Cleanup Verification Log', 'customer-email-verification-for-woocommerce' ),
		'desc' => __( 'Automatically purge old verification log entries on a schedule.', 'customer-email-verification-for-woocommerce' ),
		'free_type' => 'x', 'free_text' => __( 'NOT AVAILABLE', 'customer-email-verification-for-woocommerce' ), 'pro' => __( 'AUTO CLEANUP', 'customer-email-verification-for-woocommerce' ), 'is_new' => false ),
	array( 'title' => __( 'Auto-Delete Unverified Users', 'customer-email-verification-for-woocommerce' ),
		'desc' => __( 'Automatically remove users who never complete email verification.', 'customer-email-verification-for-woocommerce' ),
		'free_type' => 'x', 'free_text' => __( 'NOT AVAILABLE', 'customer-email-verification-for-woocommerce' ), 'pro' => __( 'AUTO DELETE', 'customer-email-verification-for-woocommerce' ), 'is_new' => false ),
	array( 'title' => __( 'Re-engagement Reminder Emails', 'customer-email-verification-for-woocommerce' ),
		'desc' => __( 'Send follow-up emails to users who haven\'t verified yet.', 'customer-email-verification-for-woocommerce' ),
		'free_type' => 'x', 'free_text' => __( 'NOT AVAILABLE', 'customer-email-verification-for-woocommerce' ), 'pro' => __( 'REMINDER EMAILS', 'customer-email-verification-for-woocommerce' ), 'is_new' => false ),
	array( 'title' => __( 'Smart Form', 'customer-email-verification-for-woocommerce' ),
		'desc' => __( 'Branded login & registration with 4 ready-made templates.', 'customer-email-verification-for-woocommerce' ),
		'free_type' => 'x', 'free_text' => __( 'NOT AVAILABLE', 'customer-email-verification-for-woocommerce' ), 'pro' => __( '4 TEMPLATES', 'customer-email-verification-for-woocommerce' ), 'is_new' => true ),
	array( 'title' => __( 'Phone (SMS) verification', 'customer-email-verification-for-woocommerce' ),
		'desc' => __( 'Verify customers via SMS using MSG91, Twilio, Vonage & WhatsApp.', 'customer-email-verification-for-woocommerce' ),
		'free_type' => 'x', 'free_text' => __( 'NOT AVAILABLE', 'customer-email-verification-for-woocommerce' ), 'pro' => __( 'MSG91 / TWILIO / VONAGE / WHATSAPP', 'customer-email-verification-for-woocommerce' ), 'is_new' => true ),
	array( 'title' => __( 'Paid-Order Gatekeeping', 'customer-email-verification-for-woocommerce' ),
		'desc' => __( 'Hold customer perks until their first paid order is completed.', 'customer-email-verification-for-woocommerce' ),
		'free_type' => 'x', 'free_text' => __( 'NOT AVAILABLE', 'customer-email-verification-for-woocommerce' ), 'pro' => __( 'GATEKEEPING', 'customer-email-verification-for-woocommerce' ), 'is_new' => true ),
	array( 'title' => __( 'Force re-verify all customers', 'customer-email-verification-for-woocommerce' ),
		'desc' => __( 'One-click legacy cleanup to force every existing customer to verify again.', 'customer-email-verification-for-woocommerce' ),
		'free_type' => 'x', 'free_text' => __( 'NOT AVAILABLE', 'customer-email-verification-for-woocommerce' ), 'pro' => __( 'LEGACY CLEANUP', 'customer-email-verification-for-woocommerce' ), 'is_new' => true ),
	array( 'title' => __( 'Polylang compatibility', 'customer-email-verification-for-woocommerce' ),
		'desc' => __( 'Works alongside existing WPML support for multilingual stores.', 'customer-email-verification-for-woocommerce' ),
		'free_type' => 'x', 'free_text' => __( 'NOT AVAILABLE', 'customer-email-verification-for-woocommerce' ), 'pro' => __( 'POLYLANG + WPML', 'customer-email-verification-for-woocommerce' ), 'is_new' => true ),
	array( 'title' => __( 'Priority Support', 'customer-email-verification-for-woocommerce' ),
		'desc' => __( 'Priority ticket handling and dedicated help center access.', 'customer-email-verification-for-woocommerce' ),
		'free_type' => 'x', 'free_text' => __( 'STANDARD ONLY', 'customer-email-verification-for-woocommerce' ), 'pro' => __( 'PRIORITY SUPPORT', 'customer-email-verification-for-woocommerce' ), 'is_new' => false ),
);

/* ─── Add-on cross-sell tiles (same shape as CEV Pro's ecosystem grid) ─── */
$cev_addons = array(
	array(
		'name'   => 'Advanced Shipment Tracking',
		'slug'   => 'ast-pro/ast-pro.php',
		'icon'   => 'ast-package',
		'accent' => '#2563EB',
		'tint'   => '#DBEAFE',
		'desc'   => __( 'Add tracking info to WooCommerce orders, automate fulfillment workflows and keep customers informed — fulfill orders straight from the Orders page.', 'customer-email-verification-for-woocommerce' ),
		'url'    => 'https://www.zorem.com/product/woocommerce-advanced-shipment-tracking/?utm_source=wp-admin&utm_medium=cev-go-pro&utm_campaign=add-ons',
		'badge'  => __( 'Recommended', 'customer-email-verification-for-woocommerce' ),
		'stat'   => '40k+ active stores',
	),
	array(
		'name'   => 'TrackShip for WooCommerce',
		'slug'   => 'trackship-for-woocommerce/trackship-for-woocommerce.php',
		'icon'   => 'truck',
		'accent' => '#0d9488',
		'tint'   => '#CCFBF1',
		'desc'   => __( 'Take control of post-shipping workflows, reduce customer-service time and deliver a premium branded post-purchase tracking experience.', 'customer-email-verification-for-woocommerce' ),
		'url'    => 'https://wordpress.org/plugins/trackship-for-woocommerce/',
		'badge'  => '',
		'stat'   => '15k+ active stores',
	),
	array(
		'name'   => 'SMS For WooCommerce',
		'slug'   => 'sms-for-woocommerce/sms-for-woocommerce.php',
		'icon'   => 'phone',
		'accent' => '#2563EB',
		'tint'   => '#DBEAFE',
		'desc'   => __( 'Keep customers informed with automated SMS messages for order & delivery updates, status changes and out-for-delivery alerts.', 'customer-email-verification-for-woocommerce' ),
		'url'    => 'https://www.zorem.com/product/sms-alert-for-woocommerce/?utm_source=wp-admin&utm_medium=cev-go-pro&utm_campaign=add-ons',
		'badge'  => '',
		'stat'   => '8k+ installs',
	),
	array(
		'name'   => 'Advanced Local Pickup Pro',
		'slug'   => 'advanced-local-pickup-pro/advanced-local-pickup-pro.php',
		'icon'   => 'store',
		'accent' => '#16A34A',
		'tint'   => '#DCFCE7',
		'desc'   => __( 'Manage local pickup orders: multiple pickup locations, split business hours, per-location discounts, pickup messages and more.', 'customer-email-verification-for-woocommerce' ),
		'url'    => 'https://www.zorem.com/product/advanced-local-pickup-pro/?utm_source=wp-admin&utm_medium=cev-go-pro&utm_campaign=add-ons',
		'badge'  => '',
		'stat'   => '4k+ stores',
	),
	array(
		'name'   => 'Sales Report Email Pro',
		'slug'   => 'sales-report-email-pro/sales-report-email-pro.php',
		'icon'   => 'mail',
		'accent' => '#9333EA',
		'tint'   => '#F3E8FF',
		'desc'   => __( 'Receive elegant daily, weekly or monthly sales reports by email — charts, average-cart metrics and order analytics on auto-pilot.', 'customer-email-verification-for-woocommerce' ),
		'url'    => 'https://www.zorem.com/product/email-reports-for-woocommerce/?utm_source=wp-admin&utm_medium=cev-go-pro&utm_campaign=add-ons',
		'badge'  => '',
		'stat'   => '3k+ stores',
	),
	array(
		'name'   => 'Country Based Restrictions Pro',
		'slug'   => 'country-base-restrictions-pro-addon/country-base-restrictions-pro-addon.php',
		'icon'   => 'globe',
		'accent' => '#EA580C',
		'tint'   => '#FFEDD5',
		'desc'   => __( 'Control catalog visibility dynamically — allow, restrict or filter product availability across specific countries with safe IP geolocation.', 'customer-email-verification-for-woocommerce' ),
		'url'    => 'https://www.zorem.com/products/country-based-restriction-pro/?utm_source=wp-admin&utm_medium=cev-go-pro&utm_campaign=add-ons',
		'badge'  => '',
		'stat'   => '3k+ active',
	),
);
?>
<div class="cev-gopro">

	<?php /* ─── Hero ─── */ ?>
	<div class="cev-gopro-hero">
		<h1 class="cev-gopro-hero__title">
			<?php esc_html_e( 'Take Your Email Verification to the Next Level', 'customer-email-verification-for-woocommerce' ); ?>
		</h1>
		<p class="cev-gopro-hero__body">
			<?php
			echo wp_kses_post(
				sprintf(
					/* translators: 1: opening strong, 2: closing strong, 3: opening accent span, 4: closing accent span */
					__( 'Stop spam signups and fraudulent orders. Upgrade from %1$sbasic verification%2$s to a %3$scomplete account security suite%4$s.', 'customer-email-verification-for-woocommerce' ),
					'<strong>',
					'</strong>',
					'<a href="' . esc_url( $cev_pro_url ) . '" target="_blank" rel="noreferrer noopener">',
					'</a>'
				)
			);
			?>
		</p>
		<a href="<?php echo esc_url( $cev_cta_url ); ?>" class="cev-gopro-hero__btn" target="_blank" rel="noreferrer noopener">
			<?php esc_html_e( 'Get Started With Pro', 'customer-email-verification-for-woocommerce' ); ?>
		</a>
	</div>

	<?php /* ─── Feature Comparison Table ─── */ ?>
	<div class="cev-gopro-compare">
		<div class="cev-gopro-compare__header">
			<div class="cev-gopro-compare__label">
				<?php esc_html_e( 'Feature Comparison', 'customer-email-verification-for-woocommerce' ); ?>
			</div>
			<div class="cev-gopro-compare__col">
				<span class="cev-gopro-compare__eyebrow"><?php esc_html_e( 'Current', 'customer-email-verification-for-woocommerce' ); ?></span>
				<span class="cev-gopro-compare__title"><?php esc_html_e( 'CEV FREE', 'customer-email-verification-for-woocommerce' ); ?></span>
			</div>
			<div class="cev-gopro-compare__col cev-gopro-compare__col--pro">
				<span class="cev-gopro-compare__badge"><?php esc_html_e( 'Recommended', 'customer-email-verification-for-woocommerce' ); ?></span>
				<span class="cev-gopro-compare__title"><?php esc_html_e( 'CEV PRO', 'customer-email-verification-for-woocommerce' ); ?></span>
			</div>
		</div>

		<?php foreach ( $cev_compare_features as $cev_feat ) : ?>
			<div class="cev-gopro-compare__row">
				<div class="cev-gopro-compare__feature">
					<strong>
						<?php echo esc_html( $cev_feat['title'] ); ?>
						<?php if ( ! empty( $cev_feat['is_new'] ) ) : ?>
							<span class="cev-gopro-new"><?php esc_html_e( 'NEW', 'customer-email-verification-for-woocommerce' ); ?></span>
						<?php endif; ?>
					</strong>
					<span><?php echo esc_html( $cev_feat['desc'] ); ?></span>
				</div>
				<div class="cev-gopro-compare__cell">
					<?php if ( 'check' === $cev_feat['free_type'] ) : ?>
						<span class="cev-gopro-compare__dot cev-gopro-compare__dot--check">
							<?php $cev_print_html( $cev_svg_check ); ?>
						</span>
					<?php else : ?>
						<span class="cev-gopro-compare__dot cev-gopro-compare__dot--x">
							<?php $cev_print_html( $cev_svg_x ); ?>
						</span>
						<?php if ( ! empty( $cev_feat['free_text'] ) ) : ?>
							<span class="cev-gopro-compare__status cev-gopro-compare__status--muted">
								<?php echo esc_html( $cev_feat['free_text'] ); ?>
							</span>
						<?php endif; ?>
					<?php endif; ?>
				</div>
				<div class="cev-gopro-compare__cell cev-gopro-compare__cell--pro">
					<span class="cev-gopro-compare__dot cev-gopro-compare__dot--check cev-gopro-compare__dot--pro">
						<?php $cev_print_html( $cev_svg_check ); ?>
					</span>
					<span class="cev-gopro-compare__status cev-gopro-compare__status--pro">
						<?php echo esc_html( $cev_feat['pro'] ); ?>
					</span>
				</div>
			</div>
		<?php endforeach; ?>
	</div>

	<?php /* ─── Bottom CTA ─── */ ?>
	<div class="cev-gopro-bottom-cta">
		<a href="<?php echo esc_url( $cev_cta_url ); ?>" class="cev-gopro-hero__btn" target="_blank" rel="noreferrer noopener">
			<?php esc_html_e( 'Get Started With Pro', 'customer-email-verification-for-woocommerce' ); ?>
		</a>
		<p class="cev-gopro-bottom-cta__note">
			<?php esc_html_e( 'Join store owners protecting their WooCommerce signups and orders.', 'customer-email-verification-for-woocommerce' ); ?>
		</p>
	</div>

	<?php /* ─── Ecosystem grid (uses the shared library's .zui-lic-eco + .zui-lic-plugin
	            components — same design as CEV Pro's License tab, including the
	            filter pills + search field at the top). ─── */ ?>
	<div class="zui-lic-eco" id="cev-gopro-eco">
		<div class="zui-lic-eco__head">
			<div class="zui-lic-eco__heading">
				<span class="zui-lic-eco__icon"><?php $cev_print_html( $cev_lic_icon( 'sparkles' ) ); ?></span>
				<div>
					<h3><?php esc_html_e( 'Powerful Add-ons', 'customer-email-verification-for-woocommerce' ); ?></h3>
					<p><?php esc_html_e( "Extend your store's capabilities with our ecosystem.", 'customer-email-verification-for-woocommerce' ); ?></p>
				</div>
			</div>
			<div class="zui-lic-eco__filters" role="tablist" aria-label="<?php esc_attr_e( 'Filter add-ons', 'customer-email-verification-for-woocommerce' ); ?>">
				<button type="button" class="zui-lic-eco__filter is-active" data-cev-filter="all">
					<?php esc_html_e( 'All', 'customer-email-verification-for-woocommerce' ); ?>
				</button>
				<button type="button" class="zui-lic-eco__filter" data-cev-filter="active">
					<?php esc_html_e( 'Active', 'customer-email-verification-for-woocommerce' ); ?>
				</button>
				<button type="button" class="zui-lic-eco__filter" data-cev-filter="addons">
					<?php esc_html_e( 'Add-ons', 'customer-email-verification-for-woocommerce' ); ?>
				</button>
			</div>
		</div>

		<div class="zui-lic-eco__search">
			<span class="zui-lic-eco__search-icon"><?php $cev_print_html( $cev_lic_icon( 'search' ) ); ?></span>
			<input type="text" id="cev-gopro-eco-search" class="zui-input" placeholder="<?php esc_attr_e( 'Search companion plugins…', 'customer-email-verification-for-woocommerce' ); ?>" autocomplete="off" />
		</div>

		<div class="zui-lic-eco__grid" id="cev-gopro-eco-grid">
			<?php foreach ( $cev_addons as $cev_eco ) :
				$cev_eco_active = is_plugin_active( $cev_eco['slug'] );
				?>
				<div class="zui-card zui-lic-plugin" data-name="<?php echo esc_attr( $cev_eco['name'] ); ?>" data-active="<?php echo $cev_eco_active ? '1' : '0'; ?>">
					<div class="zui-lic-plugin__head">
						<span class="zui-lic-plugin__logo" style="background:<?php echo esc_attr( $cev_eco['tint'] ); ?>;color:<?php echo esc_attr( $cev_eco['accent'] ); ?>">
							<?php $cev_print_html( $cev_lic_icon( $cev_eco['icon'] ) ); ?>
						</span>
						<div class="zui-lic-plugin__id">
							<h4 class="zui-lic-plugin__name"><?php echo esc_html( $cev_eco['name'] ); ?></h4>
							<span class="zui-lic-plugin__type"><?php esc_html_e( 'WOO EXTENSION', 'customer-email-verification-for-woocommerce' ); ?></span>
						</div>
						<?php if ( ! empty( $cev_eco['badge'] ) && ! $cev_eco_active ) : ?>
							<span class="zui-lic-plugin__badge"><?php echo esc_html( $cev_eco['badge'] ); ?></span>
						<?php endif; ?>
					</div>
					<div class="zui-lic-plugin__body">
						<p class="zui-lic-plugin__desc"><?php echo esc_html( $cev_eco['desc'] ); ?></p>
						<div class="zui-lic-plugin__foot">
							<?php if ( $cev_eco_active ) : ?>
								<span class="zui-lic-plugin__active"><span class="zui-lic-plugin__dot"></span><?php esc_html_e( 'Active', 'customer-email-verification-for-woocommerce' ); ?></span>
								<span class="zui-lic-plugin__stat"><?php esc_html_e( 'Active in this store', 'customer-email-verification-for-woocommerce' ); ?></span>
							<?php else : ?>
								<a class="zui-lic-plugin__get" href="<?php echo esc_url( $cev_eco['url'] ); ?>" target="_blank" rel="noopener noreferrer">
									<span><?php esc_html_e( 'Get Extension', 'customer-email-verification-for-woocommerce' ); ?></span>
									<?php $cev_print_html( $cev_lic_icon( 'external-link' ) ); ?>
								</a>
								<span class="zui-lic-plugin__stat"><?php echo esc_html( $cev_eco['stat'] ); ?></span>
							<?php endif; ?>
						</div>
					</div>
				</div>
			<?php endforeach; ?>
			<div class="zui-lic-eco__empty" id="cev-gopro-eco-empty" hidden>
				<?php esc_html_e( 'No plugins matched your search.', 'customer-email-verification-for-woocommerce' ); ?>
			</div>
		</div>
	</div>

</div>

<script>
/**
 * Add-ons grid filter + search (matches the CEV Pro License tab UX).
 * Pure client-side display logic — no AJAX, no DB hits.
 *   - Filter pills: All / Active / Add-ons (toggles by data-active="1|0")
 *   - Search field: case-insensitive substring on data-name
 *   - Empty state appears when no card matches the combined filter+query
 */
( function () {
	var root = document.getElementById( 'cev-gopro-eco' );
	if ( ! root ) { return; }

	var pills  = root.querySelectorAll( '.zui-lic-eco__filter[data-cev-filter]' );
	var search = document.getElementById( 'cev-gopro-eco-search' );
	var cards  = root.querySelectorAll( '.zui-lic-plugin' );
	var empty  = document.getElementById( 'cev-gopro-eco-empty' );

	var currentFilter = 'all';
	var currentQuery  = '';

	function applyFilters() {
		var shown = 0;
		cards.forEach( function ( card ) {
			var isActive  = '1' === card.getAttribute( 'data-active' );
			var name      = ( card.getAttribute( 'data-name' ) || '' ).toLowerCase();

			var passFilter = true;
			if ( 'active' === currentFilter ) {
				passFilter = isActive;
			} else if ( 'addons' === currentFilter ) {
				passFilter = ! isActive;
			}

			var passQuery = '' === currentQuery || -1 !== name.indexOf( currentQuery );

			if ( passFilter && passQuery ) {
				card.removeAttribute( 'hidden' );
				card.style.display = '';
				shown++;
			} else {
				card.setAttribute( 'hidden', '' );
				card.style.display = 'none';
			}
		} );

		if ( empty ) {
			if ( 0 === shown ) {
				empty.removeAttribute( 'hidden' );
			} else {
				empty.setAttribute( 'hidden', '' );
			}
		}
	}

	pills.forEach( function ( pill ) {
		pill.addEventListener( 'click', function () {
			pills.forEach( function ( p ) { p.classList.remove( 'is-active' ); } );
			pill.classList.add( 'is-active' );
			currentFilter = pill.getAttribute( 'data-cev-filter' ) || 'all';
			applyFilters();
		} );
	} );

	if ( search ) {
		search.addEventListener( 'input', function () {
			currentQuery = search.value.trim().toLowerCase();
			applyFilters();
		} );
	}
} )();
</script>
