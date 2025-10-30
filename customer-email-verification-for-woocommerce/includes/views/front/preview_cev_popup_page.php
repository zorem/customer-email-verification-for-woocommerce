<?php
// === Safe fallback defaults ===
$email = __( 'johny@example.com', 'customer-email-verification-for-woocommerce' );
$try_again = __( 'Try Again', 'customer-email-verification-for-woocommerce' );

// === Make sure the message object always exists ===
if ( ! isset( $cev_verification_widget_message ) || ! is_object( $cev_verification_widget_message ) ) {
	$cev_verification_widget_message = new cev_verification_widget_message();
}

if ( ! isset( $cev_verification_widget_message->defaults ) || ! is_array( $cev_verification_widget_message->defaults ) ) {
	$cev_verification_widget_message->defaults = array(
		'cev_verification_popup_background_color'        => '#ffffff',
		'cev_verification_popup_overlay_background_color'=> '#000000',
		'cev_verification_header'                        => __( 'Verify it\'s you.', 'customer-email-verification-for-woocommerce' ),
		/* translators: %s: Try again link */
		'cev_verification_widget_footer'                 => sprintf( __( 'Didn’t receive an email? <strong>%s</strong>', 'customer-email-verification-for-woocommerce' ), $try_again ),
	);
}

// === Safe options ===
$cev_button_color_widget_header      = get_option( 'cev_button_color_widget_header', '#212121' );
$cev_button_text_color_widget_header = get_option( 'cev_button_text_color_widget_header', '#ffffff' );
$cev_button_text_size_widget_header  = get_option( 'cev_button_text_size_widget_header', '14' );
$cev_widget_header_image_width       = get_option( 'cev_widget_header_image_width', '150' );
$cev_button_text_header_font_size    = get_option( 'cev_button_text_header_font_size', '22' );

// === Safe style defaults ===
$popup_background_color = get_option(
	'cev_verification_popup_background_color',
	$cev_verification_widget_message->defaults['cev_verification_popup_background_color']
);

$overlay_background_color = get_option(
	'cev_verification_popup_overlay_background_color',
	$cev_verification_widget_message->defaults['cev_verification_popup_overlay_background_color']
);

// === Safe heading ===
$heading_default = $cev_verification_widget_message->defaults['cev_verification_header'];
$heading = get_option( 'cev_verification_header', $heading_default );

// === Safe footer ===
$footer_default = $cev_verification_widget_message->defaults['cev_verification_widget_footer'];
$footer_message = get_option( 'cev_verification_widget_footer', $footer_default );
$footer_message = str_replace( '{cev_resend_verification}', $try_again, $footer_message );

// === Safe verification code length ===
$code_length = get_option( 'cev_verification_code_length', 4 );
$digits = ( '2' === $code_length ) ? 6 : 4; // fallback

$image = get_option(
	'cev_verification_image',
	woo_customer_email_verification()->plugin_dir_url() . 'assets/css/images/email-verification-icon.svg'
);
?>

<div class="cev-authorization-grid__visual">
	<div class="cev-authorization-grid__holder">
		<div class="cev-authorization-grid__inner">
			<div class="cev-authorization" style="background: <?php echo esc_attr( $popup_background_color ); ?>;">
				<form class="cev_pin_verification_form" method="post">
					<section class="cev-authorization__holder">
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
								$message = sprintf(
									__( 'We sent a verification code. To verify your email address, please check your inbox and enter the code below.', 'customer-email-verification-for-woocommerce' )
								);
								$message = apply_filters( 'cev_verification_popup_message', $message, $email );
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
						</div>
					</section>

					<footer class="cev-authorization__footer">
						<?php echo wp_kses_post( $footer_message ); ?>
					</footer>
				</form>
			</div>
		</div>
	</div>
</div>

<style>
	.cev-authorization-grid__visual {
		background-color: <?php echo esc_attr( woo_customer_email_verification()->hex2rgba( $overlay_background_color, '0.7' ) ); ?>;
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
