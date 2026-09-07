<?php
/**
 * Settings tab body (ZUI redesign, schema-driven).
 *
 * Mirrors the CEV Pro Settings tab end-to-end: seven sidebar sections
 * (Signup Verification / Checkout Verification / General / Login
 * Authentication / Two-Factor Authentication / Spam Protection /
 * Advanced) and every field from the Pro schema. Pro-only fields render
 * as the `cev-pro-tag` cluster (badge + lock icon, no real control);
 * Free fields render real working controls.
 *
 * Pro-only sidebar items carry the same PRO badge + lock cluster but
 * stay clickable so users can navigate into a locked section and see
 * which features are available in the Pro version.
 *
 * Field schemas + the actual row markup live on the admin class
 * (`render_settings_fields()` → `render_field_by_type()`). This view
 * just lays out the layout shell and loops.
 *
 * Expected in scope (from `wc_customer_email_verification_page_callback()`):
 *   $admin_instance  object  The admin class instance — exposes
 *                            get_cev_sections_list() / render_settings_fields().
 *
 * @package Customer_Email_Verification_For_WooCommerce
 * @since 2.7
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! isset( $admin_instance ) || ! is_object( $admin_instance ) ) {
	return;
}

$cev_sections = $admin_instance->get_cev_sections_list();

// Sidebar / section-header icons (Lucide source — same as Pro's icons.php).
$cev_icons = array(
	'user-plus'     => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" y1="8" x2="19" y2="14"/><line x1="22" y1="11" x2="16" y2="11"/>',
	'shopping-cart' => '<circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>',
	'sliders'       => '<line x1="4" y1="21" x2="4" y2="14"/><line x1="4" y1="10" x2="4" y2="3"/><line x1="12" y1="21" x2="12" y2="12"/><line x1="12" y1="8" x2="12" y2="3"/><line x1="20" y1="21" x2="20" y2="16"/><line x1="20" y1="12" x2="20" y2="3"/><line x1="2" y1="14" x2="6" y2="14"/><line x1="10" y1="8" x2="14" y2="8"/><line x1="18" y1="16" x2="22" y2="16"/>',
	'sliders-h'     => '<line x1="21" y1="4" x2="14" y2="4"/><line x1="10" y1="4" x2="3" y2="4"/><line x1="21" y1="12" x2="12" y2="12"/><line x1="8" y1="12" x2="3" y2="12"/><line x1="21" y1="20" x2="16" y2="20"/><line x1="12" y1="20" x2="3" y2="20"/><line x1="14" y1="2" x2="14" y2="6"/><line x1="8" y1="10" x2="8" y2="14"/><line x1="16" y1="18" x2="16" y2="22"/>',
	'lock'          => '<rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>',
	'shield'        => '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>',
	'shield-alert'  => '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>',
	'check'         => '<polyline points="20 6 9 17 4 12"/>',
);

$cev_render_icon = function ( $name ) use ( $cev_icons ) {
	$cev_d = isset( $cev_icons[ $name ] ) ? $cev_icons[ $name ] : '';
	return '<svg class="zui-icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">' . $cev_d . '</svg>';
};

$cev_lock_svg = '<svg class="cev-pro-lock-svg" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>';

// Check icon at stroke-width="3" as used by the ZUI upsell panel component.
$cev_upsell_check_svg = '<svg class="zui-icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>';

$cev_print = function ( $html ) {
	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Callers pass SVG built from constant maps with no user input.
	echo $html;
};

// PRO promo card — feature checklist.
$cev_pro_features = array(
	array( 'label' => __( 'Email verification at checkout', 'customer-email-verification-for-woocommerce' ),         'is_new' => false ),
	array( 'label' => __( 'OTP-based login authentication', 'customer-email-verification-for-woocommerce' ),         'is_new' => false ),
	array( 'label' => __( 'OTP expiration & resend limits', 'customer-email-verification-for-woocommerce' ),         'is_new' => false ),
	array( 'label' => __( 'Unrecognized login detection', 'customer-email-verification-for-woocommerce' ),           'is_new' => false ),
	array( 'label' => __( 'Fully customizable verification emails', 'customer-email-verification-for-woocommerce' ), 'is_new' => false ),
	array( 'label' => __( '2FA via Authenticator App', 'customer-email-verification-for-woocommerce' ),              'is_new' => false ),
	array( 'label' => __( 'Role-based verification rules', 'customer-email-verification-for-woocommerce' ),          'is_new' => false ),
	array( 'label' => __( 'Verification analytics dashboard', 'customer-email-verification-for-woocommerce' ),       'is_new' => false ),
	array( 'label' => __( 'Disposable email blocking (3,000+)', 'customer-email-verification-for-woocommerce' ),     'is_new' => false ),
	array( 'label' => __( 'DNS-based MX validation', 'customer-email-verification-for-woocommerce' ),                'is_new' => false ),
	array( 'label' => __( 'B2B allowlist mode', 'customer-email-verification-for-woocommerce' ),                     'is_new' => false ),
	array( 'label' => __( 'Block specific email addresses', 'customer-email-verification-for-woocommerce' ),         'is_new' => false ),
	array( 'label' => __( 'Auto-delete unverified users', 'customer-email-verification-for-woocommerce' ),           'is_new' => false ),
	array( 'label' => __( 'Re-engagement reminder emails', 'customer-email-verification-for-woocommerce' ),          'is_new' => false ),
	array( 'label' => __( 'Smart Form — 4 branded templates', 'customer-email-verification-for-woocommerce' ),       'is_new' => true ),
	array( 'label' => __( 'Phone (SMS) verification', 'customer-email-verification-for-woocommerce' ),               'is_new' => true ),
	array( 'label' => __( 'Paid-Order Gatekeeping', 'customer-email-verification-for-woocommerce' ),                 'is_new' => true ),
	array( 'label' => __( 'Force re-verify all customers', 'customer-email-verification-for-woocommerce' ),          'is_new' => true ),
	array( 'label' => __( 'Polylang compatibility', 'customer-email-verification-for-woocommerce' ),                 'is_new' => true ),
);

$cev_upgrade_url = 'https://www.zorem.com/product/customer-email-verification/';
$cev_docs_url    = 'https://docs.zorem.com/docs/customer-email-verification-free/';
$cev_support_url = 'https://www.zorem.com/support/';

$cev_first      = true;
$cev_first_slug = '';
foreach ( $cev_sections as $cev_slug => $cev_meta ) {
	if ( '' === $cev_first_slug ) {
		$cev_first_slug = $cev_slug;
	}
}
?>

<div class="zui-layout">

	<aside class="zui-sidebar">

		<nav class="zui-sidebar__nav" aria-label="<?php esc_attr_e( 'Settings sections', 'customer-email-verification-for-woocommerce' ); ?>">
			<?php foreach ( $cev_sections as $cev_slug => $cev_meta ) :
				$cev_is_first = ( $cev_slug === $cev_first_slug );
				$cev_is_pro   = ! empty( $cev_meta['pro_only'] );
				?>
				<button type="button" class="zui-sidebar__item<?php echo $cev_is_first ? ' is-active' : ''; ?><?php echo $cev_is_pro ? ' cev-sidebar-pro' : ''; ?>" data-zui-section="<?php echo esc_attr( $cev_slug ); ?>"<?php echo $cev_is_first ? ' aria-current="page"' : ''; ?>>
					<span class="zui-sidebar__icon"><?php $cev_print( $cev_render_icon( $cev_meta['icon'] ) ); ?></span>
					<span class="zui-sidebar__label"><?php echo esc_html( $cev_meta['label'] ); ?></span>
					<?php if ( $cev_is_pro ) : ?>
						<span class="cev-pro-tag">
							<span class="cev-pro-badge"><?php esc_html_e( 'PRO', 'customer-email-verification-for-woocommerce' ); ?></span>
							<span class="cev-pro-lock-icon"><?php $cev_print( $cev_lock_svg ); ?></span>
						</span>
					<?php endif; ?>
				</button>
			<?php endforeach; ?>
		</nav>

		<?php /* Canonical Quick Help block — markup from
		        assets/zui/ZUI-COMPONENTS-PREVIEW.html (#cmp-quickhelp). */ ?>
		<div class="zui-quickhelp">
			<h4 class="zui-quickhelp__title"><?php esc_html_e( 'Quick Help', 'customer-email-verification-for-woocommerce' ); ?></h4>
			<p class="zui-quickhelp__text"><?php esc_html_e( 'Learn how to configure Customer Email Verification for best results.', 'customer-email-verification-for-woocommerce' ); ?></p>

			<div class="zui-quickhelp__links">
				<a class="zui-quickhelp__link" href="<?php echo esc_url( $cev_docs_url ); ?>" target="_blank" rel="noreferrer noopener">
					<span><?php esc_html_e( 'View Documentation', 'customer-email-verification-for-woocommerce' ); ?></span>
					<svg class="zui-icon" xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
				</a>
				<a class="zui-quickhelp__link zui-quickhelp__link--muted" href="<?php echo esc_url( $cev_support_url ); ?>" target="_blank" rel="noreferrer noopener">
					<span><?php esc_html_e( 'Get Support', 'customer-email-verification-for-woocommerce' ); ?></span>
					<svg class="zui-icon" xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
				</a>
			</div>

			<div class="zui-quickhelp__art" aria-hidden="true">
				<span class="zui-quickhelp__glow"></span>
				<span class="zui-quickhelp__sphere"></span>

				<span class="zui-quickhelp__paper zui-quickhelp__paper--1">
					<span class="zui-quickhelp__line zui-quickhelp__line--brand"></span>
					<span class="zui-quickhelp__line zui-quickhelp__line--mid"></span>
					<span class="zui-quickhelp__line zui-quickhelp__line--short"></span>
				</span>
				<span class="zui-quickhelp__paper zui-quickhelp__paper--2">
					<span class="zui-quickhelp__line zui-quickhelp__line--accent"></span>
					<span class="zui-quickhelp__line zui-quickhelp__line--mid"></span>
					<span class="zui-quickhelp__line zui-quickhelp__line--mid"></span>
					<span class="zui-quickhelp__line zui-quickhelp__line--short"></span>
				</span>

				<span class="zui-quickhelp__check">&#10003;</span>
			</div>
		</div>

	</aside>

	<main class="zui-content">

		<form method="post" id="cev_settings_form" action="" enctype="multipart/form-data">

			<?php foreach ( $cev_sections as $cev_slug => $cev_meta ) :
				$cev_is_first      = ( $cev_slug === $cev_first_slug );
				$cev_section_pro   = ! empty( $cev_meta['pro_only'] );
				$cev_section_label = isset( $cev_meta['label'] ) ? $cev_meta['label'] : '';
				$cev_section_sub   = isset( $cev_meta['sub'] ) ? $cev_meta['sub'] : '';
				$cev_section_icon  = isset( $cev_meta['icon'] ) ? $cev_meta['icon'] : 'sliders';
				$cev_section_data  = isset( $cev_meta['fields'] ) ? $cev_meta['fields'] : array();
				?>
				<section class="zui-section<?php echo $cev_is_first ? ' is-active' : ''; ?>" data-zui-section="<?php echo esc_attr( $cev_slug ); ?>"<?php echo $cev_is_first ? '' : ' hidden'; ?>>

					<div class="zui-section-header">
						<div class="zui-section-header__main">
							<span class="zui-section-header__icon"><?php $cev_print( $cev_render_icon( $cev_section_icon ) ); ?></span>
							<div class="zui-section-header__text">
								<div class="zui-section-header__titlewrap">
									<h2 class="zui-section-header__title"><?php echo esc_html( $cev_section_label ); ?></h2>
								</div>
								<p class="zui-section-header__sub"><?php echo esc_html( $cev_section_sub ); ?></p>
							</div>
						</div>
						<?php if ( ! $cev_section_pro ) : ?>
							<div class="zui-section-header__actions">
								<span class="zui-savebtn-wrap">
									<button type="submit" class="zui-savebtn woocommerce-save-button cev_settings_save" name="save">
										<span class="zui-savebtn__label"><?php esc_html_e( 'Save changes', 'customer-email-verification-for-woocommerce' ); ?></span>
										<span class="zui-savebtn__spinner spinner"></span>
									</button>
								</span>
							</div>
						<?php endif; ?>
					</div>

					<div class="zui-card">
						<?php $admin_instance->render_settings_fields( $cev_section_data ); ?>
					</div>

				</section>
			<?php endforeach; ?>

			<?php wp_nonce_field( 'cev_settings_form_nonce', 'cev_settings_form_nonce' ); ?>
			<input type="hidden" name="action" value="cev_settings_form_update" />

		</form>

		<?php /* ─── Full-width PRO promo — ZUI library upsell panel ─── */ ?>
		<section class="zui-upsell" aria-labelledby="cev-upsell-title">

			<header class="zui-upsell__head">
				<span class="zui-upsell__emblem" aria-hidden="true"><?php $cev_print( $cev_render_icon( 'shield' ) ); ?></span>
				<div class="zui-upsell__head-text">
					<span class="zui-upsell__eyebrow"><?php esc_html_e( 'CEV PRO', 'customer-email-verification-for-woocommerce' ); ?></span>
					<h3 class="zui-upsell__title" id="cev-upsell-title">
						<?php esc_html_e( 'Unlock Advanced Email Verification with CEV PRO', 'customer-email-verification-for-woocommerce' ); ?>
					</h3>
					<p class="zui-upsell__sub">
						<?php esc_html_e( 'Secure your WooCommerce store with advanced email verification, login authentication, analytics, and anti-spam controls — beyond the basics included in the free plugin.', 'customer-email-verification-for-woocommerce' ); ?>
					</p>
				</div>
			</header>

			<ul class="zui-upsell__features">
				<?php foreach ( $cev_pro_features as $cev_feature ) : ?>
					<li class="zui-upsell__feature">
						<span class="zui-upsell__check" aria-hidden="true"><?php $cev_print( $cev_upsell_check_svg ); ?></span>
						<span class="zui-upsell__label">
							<?php echo esc_html( $cev_feature['label'] ); ?>
							<?php if ( ! empty( $cev_feature['is_new'] ) ) : ?>
								<span class="zui-upsell__new"><?php esc_html_e( 'NEW', 'customer-email-verification-for-woocommerce' ); ?></span>
							<?php endif; ?>
						</span>
					</li>
				<?php endforeach; ?>
			</ul>

			<footer class="zui-upsell__foot">
				<div class="zui-upsell__offer">
					<span class="zui-upsell__offer-label"><?php esc_html_e( 'Launch offer', 'customer-email-verification-for-woocommerce' ); ?></span>
					<span class="zui-upsell__offer-body">
						<?php esc_html_e( 'Get 20% off — use code', 'customer-email-verification-for-woocommerce' ); ?>
						<span class="zui-upsell__code">CEVPRO20</span>
						<?php esc_html_e( 'at checkout.', 'customer-email-verification-for-woocommerce' ); ?>
					</span>
					<span class="zui-upsell__offer-note"><?php esc_html_e( '★ for new customers only', 'customer-email-verification-for-woocommerce' ); ?></span>
				</div>
				<a class="zui-upsell__cta" href="<?php echo esc_url( $cev_upgrade_url ); ?>" target="_blank" rel="noreferrer noopener">
					<?php esc_html_e( 'Upgrade to CEV PRO', 'customer-email-verification-for-woocommerce' ); ?>
					<span aria-hidden="true">→</span>
				</a>
			</footer>

		</section>

	</main>

</div>
