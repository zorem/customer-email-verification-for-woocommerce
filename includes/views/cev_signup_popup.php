<?php
// === Safe static values ===
$email = __( 'johny@example.com', 'customer-email-verification-for-woocommerce' );

// === Make sure widget style object exists ===
if ( ! isset( $cev_verification_widget_style ) || ! is_object( $cev_verification_widget_style ) ) {
	$cev_verification_widget_style = new cev_verification_widget_message();
}
if ( ! isset( $cev_verification_widget_style->defaults ) || ! is_array( $cev_verification_widget_style->defaults ) ) {
	$cev_verification_widget_style->defaults = array(
		'cev_verification_popup_background_color'         => '#ffffff',
		'cev_verification_popup_overlay_background_color' => '#000000',
	);
}

// === Same for message object ===
if ( ! isset( $cev_verification_widget_message ) || ! is_object( $cev_verification_widget_message ) ) {
	$cev_verification_widget_message = new cev_verification_widget_message();
}
if ( ! isset( $cev_verification_widget_message->defaults ) || ! is_array( $cev_verification_widget_message->defaults ) ) {
	
	$cev_verification_widget_message->defaults = array(
	'cev_verification_header'         => __( 'Verify it\'s you.', 'customer-email-verification-for-woocommerce' ),
	'cev_verification_message'        => __( 'We sent a verification code. To verify your email address, please check your inbox and enter the code below.', 'customer-email-verification-for-woocommerce' ),
	'cev_verification_widget_footer'  => __( "Didn't receive an email? {cev_resend_verification}", 'customer-email-verification-for-woocommerce' ),
	);

}

// === Get all options safely ===
$background_color = get_option( 'cev_verification_popup_background_color', $cev_verification_widget_style->defaults['cev_verification_popup_background_color'] );
$overlay_color    = get_option( 'cev_verification_popup_overlay_background_color', $cev_verification_widget_style->defaults['cev_verification_popup_overlay_background_color'] );

$cev_button_color_widget_header      = get_option( 'cev_button_color_widget_header', '#212121' );
$cev_widget_header_image_width       = get_option( 'cev_widget_header_image_width', '150' );
$cev_button_text_header_font_size    = get_option( 'cev_button_text_header_font_size', '22' );
$cev_enable_email_verification       = get_option( 'cev_enable_email_verification' );
$password_setup_link_enabled         = get_option( 'woocommerce_registration_generate_password', 'no' );

$heading = get_option( 'cev_verification_header', $cev_verification_widget_message->defaults['cev_verification_header'] );
$message = get_option( 'cev_verification_message', $cev_verification_widget_message->defaults['cev_verification_message'] );
$footer_message = get_option( 'cev_verification_widget_footer', $cev_verification_widget_message->defaults['cev_verification_widget_footer'] );
$footer_message = WC_customer_email_verification_email_Common()->maybe_parse_merge_tags( $footer_message );
// === Code length ===
$code_length = get_option( 'cev_verification_code_length', 4 );
$digits = ( '2' === $code_length ) ? 6 : 4;

// === Image ===
$image = get_option(
	'cev_verification_image',
	woo_customer_email_verification()->plugin_dir_url() . 'assets/css/images/email-verification-icon.svg'
);

// === Content alignment ===
$content_align = get_option( 'cev_content_align', 'center' );
?>

<div id="otp-popup" style="display: none;" class="cev-authorization-grid__visual">
	<div class="otp_popup_inn">
		<div class="otp_content">
			<div class="cev-authorization-grid__holder">
				<div class="cev-authorization-grid__inner">
					<div class="cev-authorization" style="background: <?php echo esc_attr( $background_color ); ?>;">
						<form class="cev_pin_verification_form" method="post">
							<section class="cev-authorization__holder">
								<div class="back_btn">
									<svg fill="#000000" version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg" viewBox="-6.07 -6.07 72.87 72.87">
										<polygon points="0,30.365 29.737,60.105 29.737,42.733 60.731,42.729 60.731,18.001 29.737,17.999 29.737,0.625 "></polygon>
									</svg>
								</div>

								<div class="popup_image" style="width: <?php echo esc_attr( $cev_widget_header_image_width ); ?>px;">
									<?php if ( ! empty( $image ) ) : ?>
										<img src="<?php echo esc_url( $image ); ?>" alt="<?php esc_attr_e( 'Verification icon', 'customer-email-verification-for-woocommerce' ); ?>">
									<?php endif; ?>
								</div>

								<div class="cev-authorization__heading">
									<span class="cev-authorization__title" style="font-size: <?php echo esc_attr( $cev_button_text_header_font_size ); ?>px;">
										<?php echo wp_kses_post( $heading ); ?>
									</span>
									<span class="cev-authorization__description">
										<?php
										echo wp_kses_post( $message );
										?>
									</span>
								</div>

								<div class="cev-pin-verification">
									<div class="otp-container">
										<?php for ( $i = 1; $i <= $digits; $i++ ) : ?>
											<input type="text" maxlength="1" class="otp-input" id="<?php echo esc_attr( 'otp_input_' . $i ); ?>" />
										<?php endfor; ?>
									</div>

									<input type="hidden" name="cev_enable_email_verification_popup" id="cev_enable_email_verification_popup" value="<?php echo esc_attr( $cev_enable_email_verification ); ?>">
									<input type="hidden" name="password_setup_link_enabled" id="password_setup_link_enabled" value="<?php echo esc_attr( $password_setup_link_enabled ); ?>">

									<div class="error_mesg"></div>

									<button id="verify-otp-button" style="display: none;" type="button"><?php esc_html_e( 'Verify', 'customer-email-verification' ); ?></button>

									<p class="resend_sucsess" style="color: green; display:none"><?php esc_html_e( 'Otp Sent Successfully', 'customer-email-verification' ); ?></p>
								</div>
							</section>

							<footer class="cev-authorization__footer" style="text-align: <?php echo esc_attr( $content_align ); ?>;">
								<?php echo wp_kses_post( $footer_message ); ?>
							</footer>
						</form>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<style>
	.cev-authorization-grid__visual {
		background-color: <?php echo esc_attr( woo_customer_email_verification()->hex2rgba( $overlay_color, '0.7' ) ); ?> !important;
	}
	html {
		background: none;
	}
	footer#footer {
		display: none;
	}
	.customize-partial-edit-shortcut-button {
		display: none;
	}
</style>
