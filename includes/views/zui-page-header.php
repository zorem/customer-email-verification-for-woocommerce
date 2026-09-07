<?php
/**
 * Admin Page Header (ZUI shell)
 *
 * Renders the Zorem UI header bar (brand + actions) and the top tab
 * strip for the Customer Email Verification (Free) admin page. Lives
 * INSIDE the .zui-scope wrapper (the --zui-* design tokens are scoped
 * to .zui-scope).
 *
 * The shared assets/zui/brand.php is READ-ONLY for this plugin and
 * only registers the Pro slug. We therefore hardcode the Free-build
 * brand inline below — this is the documented fallback path in
 * `zui_get_plugin_brand()` ("Returns NULL if the slug is unknown so
 * the caller can fall back to a hardcoded brand").
 *
 * Expected in scope (from wc_customer_email_verification_page_callback()):
 *   $admin_instance  object  The admin class instance (for tab data).
 *
 * @package Customer_Email_Verification_For_WooCommerce
 * @since 2.7
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Hardcoded brand for the FREE build. Icon source matches the Pro
// plugin's CEV brand entry (shield-check, Lucide). The Free build
// keeps a blue emblem so it stays visually distinct from Pro's red.
//
// All icons (shield-check brand + book Docs button + menu toggle)
// are inlined as SVG below — same Lucide source as assets/zui/icons.php —
// so the header renders identically whether or not the shared ZUI
// library is uploaded to this site.
$cev_brand_name    = 'CEV';
$cev_brand_badge   = 'FREE';
$cev_brand_tagline = __( 'Customer Email Verification', 'customer-email-verification-for-woocommerce' );
$cev_emblem_bg     = '#DBEAFE';
$cev_emblem_color  = '#2563EB';

$cev_docs_url = 'https://docs.zorem.com/docs/customer-email-verification-free/';
?>
<header class="zui-header" id="cev-set-header">
	<div class="zui-header__bar">

		<div class="zui-header__lead">
			<button type="button" class="zui-header__menu-toggle" data-zui-drawer-toggle aria-label="<?php esc_attr_e( 'Open settings menu', 'customer-email-verification-for-woocommerce' ); ?>">
				<svg class="zui-icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
			</button>

			<div class="zui-brand">
				<span class="zui-brand__emblem" aria-hidden="true" style="--zui-brand-emblem-bg:<?php echo esc_attr( $cev_emblem_bg ); ?>;--zui-brand-emblem-color:<?php echo esc_attr( $cev_emblem_color ); ?>">
					<svg class="zui-icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z"/><path d="m9 12 2 2 4-4"/></svg>
				</span>
				<span class="zui-brand__name"><?php echo esc_html( $cev_brand_name ); ?></span>
				<span class="zui-brand__badge"><?php echo esc_html( $cev_brand_badge ); ?></span>
				<span class="zui-brand__tagline"><?php echo esc_html( $cev_brand_tagline ); ?></span>
			</div>
		</div>

		<div class="zui-header-actions">
			<a class="zui-header-action zui-header-action--with-label" href="<?php echo esc_url( $cev_docs_url ); ?>" target="_blank" rel="noreferrer noopener">
				<svg class="zui-icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
				<span class="zui-header-action__label"><?php esc_html_e( 'Docs Portal', 'customer-email-verification-for-woocommerce' ); ?></span>
			</a>
		</div>

	</div>

	<nav class="zui-tabs" aria-label="<?php esc_attr_e( 'CEV modules', 'customer-email-verification-for-woocommerce' ); ?>">
		<?php
		if ( isset( $admin_instance ) && method_exists( $admin_instance, 'render_zui_menu_tabs' ) ) {
			$admin_instance->render_zui_menu_tabs( $admin_instance->get_cev_tab_settings_data() );
		}
		?>
	</nav>
</header>
