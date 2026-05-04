<?php
/**
 * Go Pro tab — matches AST Go Pro v2 design exactly
 * No Usage Tracking section
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$more_plugins = array(
	0 => array(
		'title' => 'Advanced Shipment Tracking Pro',
		'description' => 'AST PRO provides powerful features to easily add tracking info to WooCommerce orders, automate the fulfillment workflows and keep your customers happy and informed.',
		'url' => 'https://www.zorem.com/product/woocommerce-advanced-shipment-tracking/?utm_source=wp-admin&utm_medium=cev-go-pro&utm_campaign=add-ons',
		'image' => 'ast.png',
		'file' => 'ast-pro/ast-pro.php'
	),
	1 => array(
		'title' => 'TrackShip for WooCommerce',
		'description' => 'Take control of your post-shipping workflows, reduce time spent on customer service and provide a superior post-purchase experience to your customers.Beyond automatic shipment tracking, TrackShip brings...',
		'url' => 'https://wordpress.org/plugins/trackship-for-woocommerce/#utm_source=wp-admin&utm_medium=cev-go-pro&utm_campaign=add-ons',
		'image' => 'trackship.png',
		'file' => 'trackship-for-woocommerce/trackship-for-woocommerce.php'
	),
	2 => array(
		'title' => 'SMS for WooCommerce',
		'description' => 'Keep your customers informed by sending them automated SMS text messages with order & delivery updates. You can send SMS notifications to customers when the order status is updated or when...',
		'url' => 'https://www.zorem.com/products/sms-for-woocommerce/#utm_source=wp-admin&utm_medium=cev-go-pro&utm_campaign=add-ons',
		'image' => 'smswoo.png',
		'file' => 'sms-for-woocommerce/sms-for-woocommerce.php'
	),
	3 => array(
		'title' => 'Zorem Local Pickup Pro',
		'description' => 'The Advanced Local Pickup (ALP) helps you manage the local pickup orders workflow more conveniently by extending the WooCommerce Local Pickup shipping method.',
		'url' => 'https://www.zorem.com/product/zorem-local-pickup-pro/#utm_source=wp-admin&utm_medium=cev-go-pro&utm_campaign=add-ons',
		'image' => 'alp.png',
		'file' => 'advanced-local-pickup-pro/advanced-local-pickup-pro.php'
	),
	4 => array(
		'title' => 'Country Based Restriction for WooCommerce',
		'description' => 'The country-based restrictions plugin by zorem works by the WooCommerce Geolocation or the shipping country added by the customer and allows you to restrict products on your store to sell or n...',
		'url' => 'https://www.zorem.com/product/country-based-restriction-pro/#utm_source=wp-admin&utm_medium=cev-go-pro&utm_campaign=add-ons',
		'image' => 'cbr.png',
		'file' => 'country-based-restriction-pro-addon/country-based-restriction-pro-addon.php'
	),
	5 => array(
		'title' => 'Email Reports for WooCommerce',
		'description' => 'The Sales Report Email Pro will help know how well your store is performing and how your products are selling by sending you a daily, weekly, or monthly sales report by email, directly from your...',
		'url' => 'https://www.zorem.com/product/email-reports-for-woocommerce/#utm_source=wp-admin&utm_medium=cev-go-pro&utm_campaign=add-ons',
		'image' => 'sre.png',
		'file' => 'sales-report-email-pro/sales-report-email-pro.php'
	),
);

$plugin_url = woo_customer_email_verification()->plugin_dir_url();

?>
<section id="cev_content_addons" class="cev_tab_section">
	<div class="tab_container_without_bg">

		<!-- Go Pro v2 layout (same as AST) -->
		<div class="cev-go-pro-v2">

			<style>
				/* ===== Scoped styles for .cev-go-pro-v2 ===== */
				.cev-go-pro-v2 .gopro-hero {
					text-align: center;
					padding: 50px 20px 40px;
				}
				.cev-go-pro-v2 .gopro-hero h1 {
					font-size: 34px;
					font-weight: 700;
					color: #1a1a2e;
					margin: 0 0 12px;
					line-height: 1.25;
				}
				.cev-go-pro-v2 .gopro-hero p {
					font-size: 15px;
					color: #555;
					max-width: 560px;
					margin: 0 auto;
					line-height: 1.6;
				}
				.cev-go-pro-v2 .gopro-hero p strong {
					color: #333;
				}
				.cev-go-pro-v2 .gopro-hero p a {
					color: #2563eb;
					text-decoration: none;
					font-weight: 600;
				}

				/* Comparison table */
				.cev-go-pro-v2 .gopro-comparison {
					margin: 0 auto 30px;
					background: #fff;
					border: 1px solid #e5e7eb;
					border-radius: 8px;
					overflow: hidden;
				}
				.cev-go-pro-v2 .gopro-comp-header {
					display: grid;
					grid-template-columns: 1fr 180px 180px;
					align-items: center;
					background: #f9fafb;
					border-bottom: 1px solid #e5e7eb;
				}
				.cev-go-pro-v2 .gopro-comp-header-label {
					padding: 16px 24px;
					font-weight: 700;
					text-transform: uppercase;
					letter-spacing: 0.06em;
					font-size: 15px;
					color: #1a1a2e;
				}
				.cev-go-pro-v2 .gopro-comp-header-col {
					text-align: center;
					padding: 16px 12px;
				}
				.cev-go-pro-v2 .gopro-comp-header-col .comp-header-badge {
					display: inline-block;
					font-size: 9px;
					font-weight: 700;
					text-transform: uppercase;
					letter-spacing: 0.06em;
					padding: 2px 8px;
					border-radius: 4px;
					margin-bottom: 4px;
				}
				.cev-go-pro-v2 .gopro-comp-header-col .comp-header-badge.badge-current {
					background: transparent;
					color: #9ca3af;
				}
				.cev-go-pro-v2 .gopro-comp-header-col .comp-header-badge.badge-recommended {
					background: #16a34a;
					color: #fff;
				}
				.cev-go-pro-v2 .gopro-comp-header-col .comp-header-title {
					display: block;
					font-size: 15px;
					font-weight: 700;
					color: #1a1a2e;
				}
				.cev-go-pro-v2 .gopro-comp-header-col.is-pro {
					background: #f8fdf9;
				}
				.cev-go-pro-v2 .gopro-comp-row {
					display: grid;
					grid-template-columns: 1fr 180px 180px;
					border-bottom: 1px solid #f0f1f3;
					align-items: center;
				}
				.cev-go-pro-v2 .gopro-comp-row:last-child {
					border-bottom: 0;
				}
				.cev-go-pro-v2 .gopro-comp-feature {
					padding: 18px 24px;
				}
				.cev-go-pro-v2 .gopro-comp-feature strong {
					display: block;
					font-size: 14px;
					font-weight: 700;
					color: #1a1a2e;
					margin-bottom: 2px;
				}
				.cev-go-pro-v2 .gopro-comp-feature span {
					font-size: 12px;
					color: #9ca3af;
					line-height: 1.4;
				}
				.cev-go-pro-v2 .gopro-comp-cell {
					text-align: center;
					padding: 18px 12px;
					display: flex;
					flex-direction: column;
					align-items: center;
					gap: 4px;
				}
				.cev-go-pro-v2 .gopro-comp-cell .comp-icon {
					width: 28px;
					height: 28px;
					border-radius: 50%;
					display: flex;
					align-items: center;
					justify-content: center;
				}
				.cev-go-pro-v2 .gopro-comp-cell .comp-icon svg {
					width: 20px;
					height: 20px;
				}
				.cev-go-pro-v2 .gopro-comp-cell .comp-icon.icon-x {
					background: #4b5563;
				}
				.cev-go-pro-v2 .gopro-comp-cell .comp-icon.icon-check {
					background: #dcfce7;
				}
				.cev-go-pro-v2 .gopro-comp-cell .comp-status {
					font-size: 11px;
					font-weight: 600;
					text-transform: uppercase;
					letter-spacing: 0.03em;
					color: #9ca3af;
				}
				.cev-go-pro-v2 .gopro-comp-cell.is-pro {
					background: #f8fdf9;
				}
				.cev-go-pro-v2 .gopro-comp-cell.is-pro .comp-status {
					color: #16a34a;
					font-size: 12px;
				}

				/* CTA */
				.cev-go-pro-v2 .gopro-cta {
					text-align: center;
					padding: 0px 20px 10px;
				}
				.cev-go-pro-v2 .gopro-cta-btn {
					display: inline-block;
					background: #3b64d3;
					color: #fff !important;
					font-size: 15px;
					font-weight: 700;
					text-transform: uppercase;
					letter-spacing: 0.04em;
					padding: 14px 40px;
					border-radius: 3px;
					text-decoration: none;
					transition: background 0.2s;
					border: none;
					cursor: pointer;
				}
				.cev-go-pro-v2 .gopro-cta-btn:hover {
					background: #2d4faa;
					color: #fff !important;
				}
				.cev-go-pro-v2 .gopro-cta-sub {
					margin-top: 14px;
					font-size: 13px;
					color: #9ca3af;
				}

				/* Benefit cards */
				.cev-go-pro-v2 .gopro-benefits {
					border-radius: 8px;
					padding: 10px 0 0;
				}
				.cev-go-pro-v2 .gopro-benefits-grid {
					display: grid;
					grid-template-columns: 1fr 1fr;
					gap: 24px;
				}
				.cev-go-pro-v2 .gopro-benefit-card {
					background: #fff;
					border-radius: 8px;
					padding: 28px 24px 24px;
					border: 1px solid #e5e7eb;
				}
				.cev-go-pro-v2 .gopro-benefit-icon {
					width: 44px;
					height: 44px;
					border-radius: 8px;
					display: flex;
					align-items: center;
					justify-content: center;
					margin-bottom: 18px;
					border: 1px solid #e5e7eb;
				}
				.cev-go-pro-v2 .gopro-benefit-icon svg {
					width: 22px;
					height: 22px;
				}
				.cev-go-pro-v2 .gopro-benefit-icon.icon-blue {
					background: #eff6ff;
				}
				.cev-go-pro-v2 .gopro-benefit-icon.icon-purple {
					background: #f3f0ff;
				}
				.cev-go-pro-v2 .gopro-benefit-card h3 {
					font-size: 17px;
					font-weight: 700;
					color: #1a1a2e;
					margin: 0 0 16px;
					line-height: 1.3;
				}
				.cev-go-pro-v2 .gopro-benefit-row {
					margin-bottom: 14px;
				}
				.cev-go-pro-v2 .gopro-benefit-row:last-child {
					margin-bottom: 0;
				}
				.cev-go-pro-v2 .gopro-benefit-label {
					font-size: 12px;
					font-weight: 700;
					text-transform: uppercase;
					letter-spacing: 0.03em;
					margin-right: 6px;
				}
				.cev-go-pro-v2 .gopro-benefit-label.label-before {
					color: #dc2626;
				}
				.cev-go-pro-v2 .gopro-benefit-label.label-after {
					color: #16a34a;
				}
				.cev-go-pro-v2 .gopro-benefit-text {
					font-size: 13px;
					color: #555;
					line-height: 1.55;
				}

				/* Addons section */
				.cev-go-pro-v2 .gopro-addons {
					border-radius: 8px;
					padding: 20px 0px 20px;
				}
				.cev-go-pro-v2 .gopro-addons-header {
					display: flex;
					justify-content: space-between;
					align-items: flex-start;
					margin-bottom: 24px;
				}
				.cev-go-pro-v2 .gopro-addons-header h2 {
					font-size: 22px;
					font-weight: 700;
					color: #1a1a2e;
					margin: 0 0 4px;
					line-height: 1.3;
				}
				.cev-go-pro-v2 .gopro-addons-header p {
					font-size: 14px;
					color: #9ca3af;
					margin: 0;
				}
				.cev-go-pro-v2 .gopro-addons-track {
					display: grid;
					grid-template-columns: repeat(3, 1fr);
					gap: 20px;
				}
				.cev-go-pro-v2 .gopro-addon-card {
					background: #fff;
					border: 1px solid #e5e7eb;
					border-radius: 8px;
					padding: 0;
					display: flex;
					flex-direction: column;
					box-sizing: border-box;
				}
				.cev-go-pro-v2 .gopro-addon-card-top {
					display: flex;
					align-items: center;
					gap: 14px;
					padding: 20px 20px 0;
				}
				.cev-go-pro-v2 .gopro-addon-card-icon {
					border-radius: 8px;
					display: flex;
					align-items: center;
					justify-content: center;
					flex-shrink: 0;
					border: 1px solid #e5e7eb;
					width: 60px;
					height: 60px;
				}
				.cev-go-pro-v2 .gopro-addon-card-icon img {
					width: 45px;
					height: 45px;
					object-fit: contain;
				}
				.cev-go-pro-v2 .gopro-addon-card h3 {
					font-size: 15px;
					font-weight: 700;
					color: #1a1a2e;
					margin: 0;
					line-height: 1.35;
				}
				.cev-go-pro-v2 .gopro-addon-card-body {
					padding: 14px 20px 0;
					flex-grow: 1;
				}
				.cev-go-pro-v2 .gopro-addon-card-body p {
					font-size: 13px;
					color: #6b7280;
					line-height: 1.6;
					margin: 0;
					display: -webkit-box;
					-webkit-line-clamp: 3;
					-webkit-box-orient: vertical;
					overflow: hidden;
				}
				.cev-go-pro-v2 .gopro-addon-card-footer {
					padding: 16px 20px 20px;
				}
				.cev-go-pro-v2 .gopro-addon-card-btn {
					display: block;
					text-align: center;
					padding: 10px;
					font-size: 14px;
					font-weight: 600;
					color: #2563eb;
					border: 1px solid #e5e7eb;
					border-radius: 8px;
					background: #fff;
					text-decoration: none;
					transition: border-color 0.2s, background 0.2s;
				}
				.cev-go-pro-v2 .gopro-addon-card-btn:hover {
					border-color: #2563eb;
					background: #eff6ff;
					color: #2563eb;
				}
				.cev-go-pro-v2 .gopro-addon-card-btn.is-active {
					color: #16a34a;
					border-color: #bbf7d0;
					background: #f0fdf4;
					cursor: default;
				}

				@media (max-width: 960px) {
					.cev-go-pro-v2 .gopro-addons-track {
						grid-template-columns: repeat(2, 1fr);
					}
				}
				@media (max-width: 680px) {
					.cev-go-pro-v2 .gopro-hero h1 {
						font-size: 26px;
					}
					.cev-go-pro-v2 .gopro-comp-header {
						grid-template-columns: 1fr 100px 100px;
					}
					.cev-go-pro-v2 .gopro-comp-header-label {
						padding: 12px;
						font-size: 9px;
					}
					.cev-go-pro-v2 .gopro-comp-header-col .comp-header-title {
						font-size: 12px;
					}
					.cev-go-pro-v2 .gopro-comp-row {
						grid-template-columns: 1fr 100px 100px;
					}
					.cev-go-pro-v2 .gopro-comp-feature {
						padding: 14px 12px;
					}
					.cev-go-pro-v2 .gopro-comp-feature strong {
						font-size: 12px;
					}
					.cev-go-pro-v2 .gopro-comp-feature span {
						font-size: 10px;
					}
					.cev-go-pro-v2 .gopro-comp-cell {
						padding: 14px 6px;
					}
					.cev-go-pro-v2 .gopro-comp-cell .comp-status {
						font-size: 9px;
					}
					.cev-go-pro-v2 .gopro-benefits-grid {
						grid-template-columns: 1fr;
					}
					.cev-go-pro-v2 .gopro-benefits {
						padding: 24px 16px;
					}
					.cev-go-pro-v2 .gopro-addons {
						padding: 24px 0px 28px;
					}
					.cev-go-pro-v2 .gopro-addons-track {
						grid-template-columns: 1fr;
					}
				}
			</style>

			<!-- Hero -->
			<div class="gopro-hero">
				<h1><?php esc_html_e( 'Take Your Email Verification to the Next Level', 'customer-email-verification-for-woocommerce' ); ?></h1>
				<p>
					<?php
					echo wp_kses_post(
						__(
							'Stop worrying about fake registrations and unauthorized access. Switch from <strong>basic email verification</strong> to a <a href="https://www.zorem.com/product/customer-email-verification/?utm_source=wp-admin&utm_medium=cev-go-pro&utm_campaign=hero" target="_blank">fully secured verification powerhouse</a>.',
							'customer-email-verification-for-woocommerce'
						)
					);
					?>
				</p>
			</div>

			<!-- Feature Comparison Table -->
			<div class="gopro-comparison">
				<div class="gopro-comp-header">
					<div class="gopro-comp-header-label"><?php esc_html_e( 'Feature Comparison', 'customer-email-verification-for-woocommerce' ); ?></div>
					<div class="gopro-comp-header-col">
						<span class="comp-header-badge badge-current"><?php esc_html_e( 'Current', 'customer-email-verification-for-woocommerce' ); ?></span>
						<span class="comp-header-title"><?php esc_html_e( 'CEV FREE', 'customer-email-verification-for-woocommerce' ); ?></span>
					</div>
					<div class="gopro-comp-header-col is-pro">
						<span class="comp-header-badge badge-recommended"><?php esc_html_e( 'Recommended', 'customer-email-verification-for-woocommerce' ); ?></span>
						<span class="comp-header-title"><?php esc_html_e( 'CEV PRO', 'customer-email-verification-for-woocommerce' ); ?></span>
					</div>
				</div>
				<?php
				$comp_features = array(
					array(
						'title'     => __( 'Email Verification at Checkout', 'customer-email-verification-for-woocommerce' ),
						'desc'      => __( 'Require email verification before completing checkout.', 'customer-email-verification-for-woocommerce' ),
						'free'      => __( 'Not Available', 'customer-email-verification-for-woocommerce' ),
						'pro'       => __( 'Full Checkout Verification', 'customer-email-verification-for-woocommerce' ),
					),
					array(
						'title'     => __( 'OTP Length & Expiration', 'customer-email-verification-for-woocommerce' ),
						'desc'      => __( 'Customize OTP digit length and set expiration time.', 'customer-email-verification-for-woocommerce' ),
						'free'      => __( 'Not Available', 'customer-email-verification-for-woocommerce' ),
						'pro'       => __( 'Fully Configurable', 'customer-email-verification-for-woocommerce' ),
					),
					array(
						'title'     => __( 'Verification Email Resend Limit', 'customer-email-verification-for-woocommerce' ),
						'desc'      => __( 'Limit the number of resend attempts to prevent abuse.', 'customer-email-verification-for-woocommerce' ),
						'free'      => __( 'Not Available', 'customer-email-verification-for-woocommerce' ),
						'pro'       => __( 'Custom Limits', 'customer-email-verification-for-woocommerce' ),
					),
					array(
						'title'     => __( 'Login Authentication', 'customer-email-verification-for-woocommerce' ),
						'desc'      => __( 'Require OTP verification for unrecognized login attempts.', 'customer-email-verification-for-woocommerce' ),
						'free'      => __( 'Not Available', 'customer-email-verification-for-woocommerce' ),
						'pro'       => __( 'OTP-Based Login', 'customer-email-verification-for-woocommerce' ),
					),
					array(
						'title'     => __( 'Unrecognized Login Detection', 'customer-email-verification-for-woocommerce' ),
						'desc'      => __( 'Detect new devices, new locations, and inactive logins.', 'customer-email-verification-for-woocommerce' ),
						'free'      => __( 'Not Available', 'customer-email-verification-for-woocommerce' ),
						'pro'       => __( 'Smart Detection', 'customer-email-verification-for-woocommerce' ),
					),
					array(
						'title'     => __( 'WooCommerce Store API Checkout', 'customer-email-verification-for-woocommerce' ),
						'desc'      => __( 'Disable Store API checkout to prevent bypassing verification.', 'customer-email-verification-for-woocommerce' ),
						'free'      => __( 'Not Available', 'customer-email-verification-for-woocommerce' ),
						'pro'       => __( 'API Protection', 'customer-email-verification-for-woocommerce' ),
					),
					array(
						'title'     => __( 'Premium Support', 'customer-email-verification-for-woocommerce' ),
						'desc'      => __( 'Priority ticket handling and dedicated help center access.', 'customer-email-verification-for-woocommerce' ),
						'free'      => __( 'Standard Only', 'customer-email-verification-for-woocommerce' ),
						'pro'       => __( 'Priority Support', 'customer-email-verification-for-woocommerce' ),
					),
				);
				foreach ( $comp_features as $feat ) :
					?>
				<div class="gopro-comp-row">
					<div class="gopro-comp-feature">
						<strong><?php echo esc_html( $feat['title'] ); ?></strong>
						<span><?php echo esc_html( $feat['desc'] ); ?></span>
					</div>
					<div class="gopro-comp-cell">
						<span class="comp-icon icon-x">
							<svg fill="none" viewBox="0 0 24 24" stroke="#fff" stroke-width="2.5"><line x1="16" y1="8" x2="8" y2="16"/><line x1="8" y1="8" x2="16" y2="16"/></svg>
						</span>
						<span class="comp-status"><?php echo esc_html( $feat['free'] ); ?></span>
					</div>
					<div class="gopro-comp-cell is-pro">
						<span class="comp-icon icon-check">
							<svg fill="none" viewBox="0 0 24 24" stroke="#16a34a" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
						</span>
						<span class="comp-status"><?php echo esc_html( $feat['pro'] ); ?></span>
					</div>
				</div>
				<?php endforeach; ?>
			</div>

			<!-- CTA -->
			<div class="gopro-cta">
				<a href="https://www.zorem.com/product/customer-email-verification/?utm_source=wp-admin&utm_medium=cev-go-pro&utm_campaign=get-started" class="gopro-cta-btn" target="_blank"><?php esc_html_e( 'GET STARTED WITH PRO', 'customer-email-verification-for-woocommerce' ); ?></a>
			</div>

		</div>
		<!-- End .cev-go-pro-v2 hero + comparison + benefits -->

		<!-- Powerful Add-ons -->
		<div class="cev-go-pro-v2">
			<div class="gopro-addons">
				<div class="gopro-addons-header">
					<div>
						<h2><?php esc_html_e( 'Powerful Add-ons', 'customer-email-verification-for-woocommerce' ); ?></h2>
						<p><?php esc_html_e( "Extend your store's capabilities with our ecosystem", 'customer-email-verification-for-woocommerce' ); ?></p>
					</div>
				</div>
				<div class="gopro-addons-track">
					<?php
					$icon_colors = array( '#ecfdf5', '#ede9fe', '#eff6ff', '#fef3c7', '#fce7f3', '#e0f2fe' );
					foreach ( $more_plugins as $index => $addon ) :
						$icon_bg = isset( $icon_colors[ $index ] ) ? $icon_colors[ $index ] : '#f3f4f6';
						?>
						<div class="gopro-addon-card">
							<div class="gopro-addon-card-top">
								<div class="gopro-addon-card-icon" style="background:<?php echo esc_attr( $icon_bg ); ?>">
									<img src="<?php echo esc_url( $plugin_url ); ?>assets/images/<?php echo esc_attr( $addon['image'] ); ?>" alt="<?php echo esc_attr( $addon['title'] ); ?>">
								</div>
								<h3><?php echo esc_html( $addon['title'] ); ?></h3>
							</div>
							<div class="gopro-addon-card-body">
								<p><?php echo esc_html( $addon['description'] ); ?></p>
							</div>
							<div class="gopro-addon-card-footer">
								<?php if ( is_plugin_active( $addon['file'] ) ) : ?>
									<span class="gopro-addon-card-btn is-active"><?php esc_html_e( 'Active', 'customer-email-verification-for-woocommerce' ); ?></span>
								<?php else : ?>
									<a href="<?php echo esc_url( $addon['url'] ); ?>" class="gopro-addon-card-btn" target="_blank"><?php esc_html_e( 'Learn More', 'customer-email-verification-for-woocommerce' ); ?></a>
								<?php endif; ?>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
		</div>

	</div>
</section>
