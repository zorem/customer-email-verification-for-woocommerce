<?php
// This template is include()d from inside a class method, so the variables
// below are function-scoped, not globals. PHPCS analyses the file in isolation
// and cannot see that, hence the false positives.
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
/**
 * Go Pro tab body (Free) — same layout as AST Free's Go Pro tab.
 *
 * Layout:
 *   1. Gradient hero (eyebrow, headline, CTA, trust items, plan badge)
 *   2. Stats strip (4 numbers)
 *   3. Six PRO feature cards
 *   4. Before / after outcomes
 *   5. Finale CTA banner (price + guarantee)
 *   6. Documentation & help + "Powerful Add-ons" grid (zui-lic-* library)
 *
 * All cosmetics live in cev-admin.css under .cev-gp-* (ported from AST's
 * .ast-gp-*). Every number shown here is verifiable: WordPress.org installs
 * and rating, the bundled disposable-domain list, and the refund policy.
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
$cev_pro_url    = woo_customer_email_verification()->get_pro_upgrade_url( 'go-pro-tab' );

// Cross-sell links: same source/medium as the upgrade links, own campaign.
$cev_addon_utm = array(
	'utm_source'   => 'cev-free',
	'utm_medium'   => 'in-plugin',
	'utm_campaign' => 'add-ons',
	'utm_content'  => 'go-pro-tab',
);

/**
 * Filter the purchase facts shown on the Go Pro tab.
 *
 * @since 2.8.2
 *
 * @param array $details price (string), guarantee_days (int), rating (string).
 */
$cev_offer_defaults = array(
	'price'          => '$119',
	'guarantee_days' => 14,
	'rating'         => '4.4',
);
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Plugin's established cev_ hook prefix (public API used by CEV PRO and integrations).
$cev_offer = apply_filters( 'cev_pro_offer_details', $cev_offer_defaults );

/* translators: 1: yearly price incl. currency symbol, e.g. $119. 2: number of days in the money-back guarantee. */
$cev_offer_line = sprintf( __( 'From %1$s per year (plus applicable tax) · %2$d-day money-back guarantee', 'customer-email-verification-for-woocommerce' ), $cev_offer['price'], (int) $cev_offer['guarantee_days'] );

/* translators: %s: average star rating out of 5, e.g. 4.4. */
$cev_rating_line = sprintf( __( 'Free plugin rated %s out of 5 on WordPress.org', 'customer-email-verification-for-woocommerce' ), $cev_offer['rating'] );

/* translators: %d: number of days in the money-back guarantee. */
$cev_guarantee_label = sprintf( __( '%d-day money-back guarantee', 'customer-email-verification-for-woocommerce' ), (int) $cev_offer['guarantee_days'] );

// Inline Lucide icons (the library's icon helper is namespaced, so the
// template keeps its own small map like the other CEV views).
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
		'zap'           => '<polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/>',
		'shield-check'  => '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="m9 12 2 2 4-4"/>',
		'shield'        => '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>',
		'shield-alert'  => '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>',
		'life-buoy'     => '<circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="4"/><line x1="4.93" y1="4.93" x2="9.17" y2="9.17"/><line x1="14.83" y1="14.83" x2="19.07" y2="19.07"/><line x1="14.83" y1="9.17" x2="19.07" y2="4.93"/><line x1="4.93" y1="19.07" x2="9.17" y2="14.83"/>',
		'rocket'        => '<path d="M4.5 16.5c-1.5 1.26-2 5-2 5s3.74-.5 5-2c.71-.84.7-2.13-.09-2.91a2.18 2.18 0 0 0-2.91-.09z"/><path d="m12 15-3-3a22 22 0 0 1 2-3.95A12.88 12.88 0 0 1 22 2c0 2.72-.78 7.5-6 11a22.35 22.35 0 0 1-4 2z"/><path d="M9 12H4s.55-3.03 2-4c1.62-1.08 5 0 5 0"/><path d="M12 15v5s3.03-.55 4-2c1.08-1.62 0-5 0-5"/>',
		'refresh'       => '<polyline points="23 4 23 10 17 10"/><polyline points="1 20 1 14 7 14"/><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"/>',
		'star'          => '<polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>',
		'shopping-cart' => '<circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>',
		'lock'          => '<rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>',
		'smartphone'    => '<rect width="14" height="20" x="5" y="2" rx="2" ry="2"/><line x1="12" y1="18" x2="12.01" y2="18"/>',
		'bar-chart'     => '<line x1="12" y1="20" x2="12" y2="10"/><line x1="18" y1="20" x2="18" y2="4"/><line x1="6" y1="20" x2="6" y2="16"/>',
		'check'         => '<polyline points="20 6 9 17 4 12"/>',
		'x'             => '<line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>',
		'frown'         => '<circle cx="12" cy="12" r="10"/><path d="M16 16s-1.5-2-4-2-4 2-4 2"/><line x1="9" y1="9" x2="9.01" y2="9"/><line x1="15" y1="9" x2="15.01" y2="9"/>',
		'smile'         => '<circle cx="12" cy="12" r="10"/><path d="M8 14s1.5 2 4 2 4-2 4-2"/><line x1="9" y1="9" x2="9.01" y2="9"/><line x1="15" y1="9" x2="15.01" y2="9"/>',
	);
	$d = isset( $paths[ $name ] ) ? $paths[ $name ] : '';
	return '<svg class="zui-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $d . '</svg>';
};

$cev_print_html = function ( $html ) {
	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Callers pass SVG / HTML built from constant maps with no user input; wp_kses_post would strip required SVG attributes.
	echo $html;
};

/* ─── 2) Stats strip — every figure is verifiable ─── */
$cev_stats = array(
	array(
		'value'  => '2,000+',
		'label'  => __( 'Stores Use CEV', 'customer-email-verification-for-woocommerce' ),
		'sub'    => __( 'Active installs on WordPress.org', 'customer-email-verification-for-woocommerce' ),
		'icon'   => 'store',
		'tint'   => '#eff6ff',
		'accent' => '#2563eb',
	),
	array(
		'value'  => '430+',
		'label'  => __( 'Disposable Domains Blocked', 'customer-email-verification-for-woocommerce' ),
		'sub'    => __( 'Built into PRO spam protection', 'customer-email-verification-for-woocommerce' ),
		'icon'   => 'shield-alert',
		'tint'   => '#fef2f2',
		'accent' => '#dc2626',
	),
	array(
		'value'  => $cev_offer['rating'],
		'label'  => __( 'Average Rating', 'customer-email-verification-for-woocommerce' ),
		'sub'    => __( 'WordPress.org reviews', 'customer-email-verification-for-woocommerce' ),
		'icon'   => 'star',
		'tint'   => '#fef3c7',
		'accent' => '#d97706',
	),
	array(
		/* translators: %d: number of days. */
		'value'  => sprintf( __( '%d days', 'customer-email-verification-for-woocommerce' ), (int) $cev_offer['guarantee_days'] ),
		'label'  => __( 'Money-Back Guarantee', 'customer-email-verification-for-woocommerce' ),
		'sub'    => __( 'On every CEV PRO license', 'customer-email-verification-for-woocommerce' ),
		'icon'   => 'shield-check',
		'tint'   => '#ecfdf5',
		'accent' => '#059669',
	),
);

/* ─── 3) PRO features ─── */
$cev_powers = array(
	array(
		'icon'   => 'shopping-cart',
		'tint'   => '#eff6ff',
		'accent' => '#2563eb',
		'title'  => __( 'Checkout Verification', 'customer-email-verification-for-woocommerce' ),
		'desc'   => __( 'Ask guests to confirm their email before they pay, on the classic and the block checkout. Stops fake and cash-on-delivery fraud orders.', 'customer-email-verification-for-woocommerce' ),
	),
	array(
		'icon'   => 'lock',
		'tint'   => '#f5f3ff',
		'accent' => '#7c3aed',
		'title'  => __( 'Login Authentication', 'customer-email-verification-for-woocommerce' ),
		'desc'   => __( 'Ask for a one-time code when someone signs in from a new device or location, or after a long time away.', 'customer-email-verification-for-woocommerce' ),
	),
	array(
		'icon'   => 'shield',
		'tint'   => '#ecfdf5',
		'accent' => '#059669',
		'title'  => __( 'Two-Factor Authentication', 'customer-email-verification-for-woocommerce' ),
		'desc'   => __( 'Let customers protect their account with Google Authenticator, Authy or 1Password.', 'customer-email-verification-for-woocommerce' ),
	),
	array(
		'icon'   => 'shield-alert',
		'tint'   => '#fef2f2',
		'accent' => '#dc2626',
		'title'  => __( 'Spam & Fraud Protection', 'customer-email-verification-for-woocommerce' ),
		'desc'   => __( 'Block 430+ disposable email domains, mistyped domains and known abusers, and score every signup for risk.', 'customer-email-verification-for-woocommerce' ),
	),
	array(
		'icon'   => 'smartphone',
		'tint'   => '#fce7f3',
		'accent' => '#db2777',
		'title'  => __( 'Smart Form & SMS', 'customer-email-verification-for-woocommerce' ),
		'desc'   => __( 'Branded login and registration templates, with phone verification by SMS through Twilio, Vonage, MSG91 or WhatsApp.', 'customer-email-verification-for-woocommerce' ),
	),
	array(
		'icon'   => 'bar-chart',
		'tint'   => '#e0f2fe',
		'accent' => '#0284c7',
		'title'  => __( 'Analytics & Automation', 'customer-email-verification-for-woocommerce' ),
		'desc'   => __( 'See signups, verifications and blocked spam over time, and clean up or remind customers who never verified, automatically.', 'customer-email-verification-for-woocommerce' ),
	),
);

/* ─── 4) Before / after ─── */
$cev_outcomes = array(
	array(
		'side'    => 'before',
		'eyebrow' => __( 'CEV Free', 'customer-email-verification-for-woocommerce' ),
		'title'   => __( 'Verification at signup only', 'customer-email-verification-for-woocommerce' ),
		'icon'    => 'frown',
		'items'   => array(
			__( 'Guests can check out with any email address, real or not', 'customer-email-verification-for-woocommerce' ),
			__( 'Throwaway inboxes from disposable email services can still sign up', 'customer-email-verification-for-woocommerce' ),
			__( 'A stolen password is enough to sign in to a customer account', 'customer-email-verification-for-woocommerce' ),
			__( 'Abandoned signups pile up until you delete them by hand', 'customer-email-verification-for-woocommerce' ),
		),
	),
	array(
		'side'    => 'after',
		'eyebrow' => __( 'With CEV PRO', 'customer-email-verification-for-woocommerce' ),
		'title'   => __( 'Every step is verified', 'customer-email-verification-for-woocommerce' ),
		'icon'    => 'smile',
		'items'   => array(
			__( 'Guests confirm their email before the order is placed', 'customer-email-verification-for-woocommerce' ),
			__( '430+ disposable domains are blocked before an account is created', 'customer-email-verification-for-woocommerce' ),
			__( 'Unfamiliar sign-ins need a one-time code or an authenticator app', 'customer-email-verification-for-woocommerce' ),
			__( 'Old signups are cleaned up and reminded automatically', 'customer-email-verification-for-woocommerce' ),
		),
	),
);

/* ─── 6) Documentation & help ─── */
$cev_help_links = array(
	array(
		'label' => __( 'CEV documentation', 'customer-email-verification-for-woocommerce' ),
		'url'   => 'https://docs.zorem.com/docs/customer-email-verification-free/',
	),
	array(
		'label' => __( 'WordPress.org support forum', 'customer-email-verification-for-woocommerce' ),
		'url'   => 'https://wordpress.org/support/plugin/customer-email-verification-for-woocommerce/',
	),
	array(
		'label' => __( 'Compare Free and PRO', 'customer-email-verification-for-woocommerce' ),
		'url'   => $cev_pro_url,
	),
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
		'url'    => add_query_arg( $cev_addon_utm, 'https://www.zorem.com/product/woocommerce-advanced-shipment-tracking/' ),
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
		'slug'   => array( 'sms-for-woocommerce/sms-for-woocommerce.php', 'zorem-sms-for-woocommerce/zorem-sms-for-woocommerce.php', 'sms-for-woocommerce/zorem-sms-for-woocommerce.php' ),
		'icon'   => 'phone',
		'accent' => '#2563EB',
		'tint'   => '#DBEAFE',
		'desc'   => __( 'Keep customers informed with automated SMS messages for order & delivery updates, status changes and out-for-delivery alerts.', 'customer-email-verification-for-woocommerce' ),
		'url'    => add_query_arg( $cev_addon_utm, 'https://www.zorem.com/product/sms-for-woocommerce/' ),
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
		'url'    => add_query_arg( $cev_addon_utm, 'https://www.zorem.com/product/zorem-local-pickup-pro/' ),
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
		'url'    => add_query_arg( $cev_addon_utm, 'https://www.zorem.com/product/email-reports-for-woocommerce/' ),
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
		'url'    => add_query_arg( $cev_addon_utm, 'https://www.zorem.com/product/country-based-restriction-pro/' ),
		'badge'  => '',
		'stat'   => '3k+ active',
	),
);
?>
<div class="cev-gp" id="cev-gp">

	<?php /* 1) HERO — gradient banner with eyebrow, headline and CTA */ ?>
	<div class="cev-gp-hero">
		<div class="cev-gp-hero__orb cev-gp-hero__orb--a"></div>
		<div class="cev-gp-hero__orb cev-gp-hero__orb--b"></div>
		<div class="cev-gp-hero__orb cev-gp-hero__orb--c"></div>

		<div class="cev-gp-hero__inner">
			<span class="cev-gp-hero__eyebrow">
				<?php $cev_print_html( $cev_lic_icon( 'sparkles' ) ); ?>
				<span><?php esc_html_e( 'Upgrade to CEV PRO', 'customer-email-verification-for-woocommerce' ); ?></span>
			</span>
			<h1 class="cev-gp-hero__title"><?php esc_html_e( 'Stop Fake Signups. Block Fraud Orders. Secure Every Login.', 'customer-email-verification-for-woocommerce' ); ?></h1>
			<p class="cev-gp-hero__lead">
				<?php esc_html_e( 'CEV Free verifies emails when customers register. CEV PRO extends that to checkout, login and spam protection, and automates the clean-up.', 'customer-email-verification-for-woocommerce' ); ?>
			</p>

			<div class="cev-gp-hero__actions">
				<a class="cev-gp-btn cev-gp-btn--primary" href="<?php echo esc_url( $cev_pro_url ); ?>" target="_blank" rel="noopener noreferrer">
					<?php $cev_print_html( $cev_lic_icon( 'zap' ) ); ?>
					<span><?php esc_html_e( 'Get CEV PRO', 'customer-email-verification-for-woocommerce' ); ?></span>
				</a>
			</div>

			<div class="cev-gp-hero__trust">
				<span class="cev-gp-hero__trust-item"><?php $cev_print_html( $cev_lic_icon( 'shield-check' ) ); ?><?php echo esc_html( $cev_guarantee_label ); ?></span>
				<span class="cev-gp-hero__trust-item"><?php $cev_print_html( $cev_lic_icon( 'life-buoy' ) ); ?><?php esc_html_e( 'Priority email support', 'customer-email-verification-for-woocommerce' ); ?></span>
				<span class="cev-gp-hero__trust-item"><?php $cev_print_html( $cev_lic_icon( 'refresh' ) ); ?><?php esc_html_e( 'Your settings carry over from Free', 'customer-email-verification-for-woocommerce' ); ?></span>
			</div>
		</div>

		<div class="cev-gp-hero__badge">
			<span class="cev-gp-hero__badge-dot"></span>
			<div>
				<div class="cev-gp-hero__badge-eyebrow"><?php esc_html_e( 'Plan Status', 'customer-email-verification-for-woocommerce' ); ?></div>
				<div class="cev-gp-hero__badge-val"><?php esc_html_e( 'Free Edition', 'customer-email-verification-for-woocommerce' ); ?></div>
			</div>
		</div>
	</div>

	<?php /* 2) STATS STRIP */ ?>
	<div class="cev-gp-stats">
		<?php foreach ( $cev_stats as $cev_stat ) : ?>
			<div class="cev-gp-stat">
				<span class="cev-gp-stat__icon" style="background: <?php echo esc_attr( $cev_stat['tint'] ); ?>; color: <?php echo esc_attr( $cev_stat['accent'] ); ?>;">
					<?php $cev_print_html( $cev_lic_icon( $cev_stat['icon'] ) ); ?>
				</span>
				<div class="cev-gp-stat__num"><?php echo esc_html( $cev_stat['value'] ); ?></div>
				<div class="cev-gp-stat__label"><?php echo esc_html( $cev_stat['label'] ); ?></div>
				<div class="cev-gp-stat__sub"><?php echo esc_html( $cev_stat['sub'] ); ?></div>
			</div>
		<?php endforeach; ?>
	</div>

	<?php /* 3) PRO FEATURES */ ?>
	<div class="cev-gp-section">
		<div class="cev-gp-section__head">
			<span class="cev-gp-section__eyebrow"><?php esc_html_e( 'Why CEV PRO', 'customer-email-verification-for-woocommerce' ); ?></span>
			<h2 class="cev-gp-section__title"><?php esc_html_e( 'Six upgrades your store security is missing', 'customer-email-verification-for-woocommerce' ); ?></h2>
			<p class="cev-gp-section__sub"><?php esc_html_e( 'Each one closes a gap that fake accounts, fraud orders and account takeovers slip through today.', 'customer-email-verification-for-woocommerce' ); ?></p>
		</div>

		<div class="cev-gp-powers">
			<?php foreach ( $cev_powers as $cev_power ) : ?>
				<div class="cev-gp-power">
					<span class="cev-gp-power__icon" style="background: <?php echo esc_attr( $cev_power['tint'] ); ?>; color: <?php echo esc_attr( $cev_power['accent'] ); ?>;">
						<?php $cev_print_html( $cev_lic_icon( $cev_power['icon'] ) ); ?>
					</span>
					<h3 class="cev-gp-power__title"><?php echo esc_html( $cev_power['title'] ); ?></h3>
					<p class="cev-gp-power__desc"><?php echo esc_html( $cev_power['desc'] ); ?></p>
					<span class="cev-gp-power__chip"><?php $cev_print_html( $cev_lic_icon( 'check' ) ); ?><?php esc_html_e( 'Included in PRO', 'customer-email-verification-for-woocommerce' ); ?></span>
				</div>
			<?php endforeach; ?>
		</div>
	</div>

	<?php /* 4) BEFORE / AFTER */ ?>
	<div class="cev-gp-section">
		<div class="cev-gp-section__head">
			<span class="cev-gp-section__eyebrow"><?php esc_html_e( 'Before / After', 'customer-email-verification-for-woocommerce' ); ?></span>
			<h2 class="cev-gp-section__title"><?php esc_html_e( 'What changes the day you upgrade', 'customer-email-verification-for-woocommerce' ); ?></h2>
		</div>

		<div class="cev-gp-outcomes">
			<?php foreach ( $cev_outcomes as $cev_outcome ) : ?>
				<div class="cev-gp-outcome cev-gp-outcome--<?php echo esc_attr( $cev_outcome['side'] ); ?>">
					<div class="cev-gp-outcome__head">
						<span class="cev-gp-outcome__icon"><?php $cev_print_html( $cev_lic_icon( $cev_outcome['icon'] ) ); ?></span>
						<div>
							<span class="cev-gp-outcome__eyebrow"><?php echo esc_html( $cev_outcome['eyebrow'] ); ?></span>
							<h3 class="cev-gp-outcome__title"><?php echo esc_html( $cev_outcome['title'] ); ?></h3>
						</div>
					</div>
					<ul class="cev-gp-outcome__list">
						<?php foreach ( $cev_outcome['items'] as $cev_item ) : ?>
							<li>
								<span class="cev-gp-outcome__bullet"><?php $cev_print_html( $cev_lic_icon( 'before' === $cev_outcome['side'] ? 'x' : 'check' ) ); ?></span>
								<span><?php echo esc_html( $cev_item ); ?></span>
							</li>
						<?php endforeach; ?>
					</ul>
				</div>
			<?php endforeach; ?>
		</div>
	</div>

	<?php /* 5) FINALE CTA */ ?>
	<div class="cev-gp-cta">
		<div class="cev-gp-cta__orb"></div>
		<div class="cev-gp-cta__content">
			<span class="cev-gp-cta__eyebrow"><?php $cev_print_html( $cev_lic_icon( 'rocket' ) ); ?><?php esc_html_e( 'Ready when you are', 'customer-email-verification-for-woocommerce' ); ?></span>
			<h2 class="cev-gp-cta__title"><?php esc_html_e( 'Unlock every feature for your store today', 'customer-email-verification-for-woocommerce' ); ?></h2>
			<p class="cev-gp-cta__sub"><?php echo esc_html( $cev_offer_line ); ?></p>
		</div>
		<div class="cev-gp-cta__action">
			<a class="cev-gp-btn cev-gp-btn--primary cev-gp-btn--xl" href="<?php echo esc_url( $cev_pro_url ); ?>" target="_blank" rel="noopener noreferrer">
				<?php $cev_print_html( $cev_lic_icon( 'zap' ) ); ?>
				<span><?php esc_html_e( 'Get Started With PRO', 'customer-email-verification-for-woocommerce' ); ?></span>
			</a>
			<span class="cev-gp-cta__note"><?php echo esc_html( $cev_rating_line ); ?></span>
		</div>
	</div>

	<?php /* 6) HELP + ADD-ONS (shared zui-lic-* components) */ ?>
	<div class="zui-lic">
		<div class="zui-lic-help">
			<h3 class="zui-lic-help__title"><?php esc_html_e( 'Documentation & Help', 'customer-email-verification-for-woocommerce' ); ?></h3>
			<ul class="zui-lic-help__list">
				<?php foreach ( $cev_help_links as $cev_help ) : ?>
					<li><a href="<?php echo esc_url( $cev_help['url'] ); ?>" target="_blank" rel="noopener noreferrer"><span><?php echo esc_html( $cev_help['label'] ); ?></span><?php $cev_print_html( $cev_lic_icon( 'external-link' ) ); ?></a></li>
				<?php endforeach; ?>
			</ul>
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
					$cev_eco_active = false;
					foreach ( (array) $cev_eco['slug'] as $cev_eco_slug ) {
						if ( is_plugin_active( $cev_eco_slug ) ) {
							$cev_eco_active = true;
							break;
						}
					}
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
