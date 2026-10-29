<?php
// This template is include()d from inside a class method, so the variables
// below are function-scoped, not globals. PHPCS analyses the file in isolation
// and cannot see that, hence the false positives.
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
/**
 * CEV PRO limited-time offer banner (Settings + Unverified Users tabs).
 *
 * Left: "SAVE 30% on CEV Pro" block. Right: headline, copy with the
 * coupon code, a live countdown to the offer end and the upgrade CTA.
 * The caller decides whether the offer is still running; the countdown
 * hides the whole banner itself once it reaches zero.
 *
 * Expects from the including scope:
 *   $cev_offer_end_ts  (int) Offer end as a Unix timestamp.
 *   $cev_offer_visible (bool) Whether the current tab shows the banner.
 *
 * @package Customer_Email_Verification_For_WooCommerce
 * @since 2.8.2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$cev_offer_url = woo_customer_email_verification()->get_pro_upgrade_url( 'offer-banner' );

$cev_offer_left  = max( 0, $cev_offer_end_ts - time() );
$cev_offer_units = array(
	'days'  => array( (int) floor( $cev_offer_left / DAY_IN_SECONDS ), __( 'Days', 'customer-email-verification-for-woocommerce' ) ),
	'hours' => array( (int) floor( ( $cev_offer_left % DAY_IN_SECONDS ) / HOUR_IN_SECONDS ), __( 'Hrs', 'customer-email-verification-for-woocommerce' ) ),
	'mins'  => array( (int) floor( ( $cev_offer_left % HOUR_IN_SECONDS ) / MINUTE_IN_SECONDS ), __( 'Min', 'customer-email-verification-for-woocommerce' ) ),
	'secs'  => array( (int) ( $cev_offer_left % MINUTE_IN_SECONDS ), __( 'Sec', 'customer-email-verification-for-woocommerce' ) ),
);
?>
<section class="cev-offer" id="cev-offer-banner" data-cev-offer-end="<?php echo esc_attr( $cev_offer_end_ts ); ?>" aria-labelledby="cev-offer-title" <?php echo $cev_offer_visible ? '' : 'hidden'; ?>>
	<div class="cev-offer__badge">
		<span class="cev-offer__save"><?php esc_html_e( 'Save', 'customer-email-verification-for-woocommerce' ); ?></span>
		<span class="cev-offer__pct">30<small>%</small></span>
		<span class="cev-offer__on"><?php esc_html_e( 'on CEV Pro', 'customer-email-verification-for-woocommerce' ); ?></span>
	</div>

	<div class="cev-offer__body">
		<h3 class="cev-offer__title" id="cev-offer-title"><?php esc_html_e( 'Stop fake signups with CEV Pro', 'customer-email-verification-for-woocommerce' ); ?></h3>
		<p class="cev-offer__text">
			<?php esc_html_e( 'Verify emails during signup and checkout, secure logins with one-time codes, send customizable reminder emails and block fake registrations before they reach your store. Apply coupon', 'customer-email-verification-for-woocommerce' ); ?>
			<code class="cev-offer__code">CEVPRO30</code>
			<?php esc_html_e( 'at checkout — valid until November 15, 2026.', 'customer-email-verification-for-woocommerce' ); ?>
		</p>

		<div class="cev-offer__foot">
			<span class="cev-offer__ends">
				<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
				<?php esc_html_e( 'Offer ends in', 'customer-email-verification-for-woocommerce' ); ?>
			</span>
			<span class="cev-offer__timer" role="timer">
				<?php foreach ( $cev_offer_units as $cev_unit_key => $cev_unit ) : ?>
					<span class="cev-offer__unit">
						<strong data-cev-offer-unit="<?php echo esc_attr( $cev_unit_key ); ?>"><?php echo esc_html( str_pad( (string) $cev_unit[0], 2, '0', STR_PAD_LEFT ) ); ?></strong>
						<small><?php echo esc_html( $cev_unit[1] ); ?></small>
					</span>
				<?php endforeach; ?>
			</span>
			<a class="zui-btn-primary cev-offer__cta" href="<?php echo esc_url( $cev_offer_url ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Upgrade now', 'customer-email-verification-for-woocommerce' ); ?></a>
		</div>
	</div>
</section>

<script>
( function () {
	var banner = document.getElementById( 'cev-offer-banner' );
	if ( ! banner ) { return; }

	var end   = parseInt( banner.getAttribute( 'data-cev-offer-end' ), 10 ) * 1000;
	var cells = {};
	banner.querySelectorAll( '[data-cev-offer-unit]' ).forEach( function ( el ) {
		cells[ el.getAttribute( 'data-cev-offer-unit' ) ] = el;
	} );

	function pad( n ) {
		return ( n < 10 ? '0' : '' ) + n;
	}

	function tick() {
		var left = Math.max( 0, Math.floor( ( end - Date.now() ) / 1000 ) );
		if ( 0 === left ) {
			banner.setAttribute( 'data-cev-offer-expired', '1' );
			banner.setAttribute( 'hidden', '' );
			clearInterval( timer );
			return;
		}
		cells.days.textContent  = pad( Math.floor( left / 86400 ) );
		cells.hours.textContent = pad( Math.floor( ( left % 86400 ) / 3600 ) );
		cells.mins.textContent  = pad( Math.floor( ( left % 3600 ) / 60 ) );
		cells.secs.textContent  = pad( left % 60 );
	}

	var timer = setInterval( tick, 1000 );
	tick();
} )();
</script>
