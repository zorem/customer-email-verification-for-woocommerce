<?php

$email = __( 'johny@example.com', 'customer-email-verification-for-woocommerce' );

if ( ! isset( $cev_verification_widget_message ) || ! is_object( $cev_verification_widget_message ) ) {
	$cev_verification_widget_message = new cev_verification_widget_message();
}
if ( ! isset( $cev_verification_widget_message->defaults ) || ! is_array( $cev_verification_widget_message->defaults ) ) {
	$cev_verification_widget_message->defaults = array(
		'cev_verification_popup_background_color' => '#ffffff',
		'cev_verification_header'                => __( 'Verify it\'s you.', 'customer-email-verification-for-woocommerce' ),
		'cev_verification_widget_footer'         => __( 'Didn’t receive an email? Try Again', 'customer-email-verification-for-woocommerce' ),
	);
}

// === Other options ===
$cev_button_color_widget_header      = get_option( 'cev_button_color_widget_header', '#212121' );
$cev_button_text_color_widget_header = get_option( 'cev_button_text_color_widget_header', '#ffffff' );
$cev_button_text_size_widget_header  = get_option( 'cev_button_text_size_widget_header', '14' );
$cev_widget_header_image_width       = get_option( 'cev_widget_header_image_width', '150' );
$cev_button_text_header_font_size    = get_option( 'cev_button_text_header_font_size', '22' );

$background_color = get_option(
	'cev_verification_popup_background_color',
	$cev_verification_widget_message->defaults['cev_verification_popup_background_color']
);
$heading = get_option(
	'cev_verification_header',
	$cev_verification_widget_message->defaults['cev_verification_header']
);
$footer_message = get_option(
	'cev_verification_widget_footer',
	$cev_verification_widget_message->defaults['cev_verification_widget_footer']
);
$footer_message = WC_customer_email_verification_email_Common()->maybe_parse_merge_tags( $footer_message );

// === Image ===
$image = get_option(
	'cev_verification_image',
	woo_customer_email_verification()->plugin_dir_url() . 'assets/css/images/email-verification-icon.svg'
);
?>

<div class="cev-authorization-grid__visual">
	<div class="cev-authorization-grid__holder">
		<div class="cev-authorization-grid__inner">
			<div class="cev-authorization" style="background: <?php echo esc_attr( $background_color ); ?>;">
				<form class="cev_pin_verification_form" method="post">
					<section class="cev-authorization__holder">

						<div class="popup_image" style="width: <?php echo esc_attr( $cev_widget_header_image_width ); ?>px;">
							<?php if ( ! empty( $image ) ) : ?>
								<img src="<?php echo esc_url( $image ); ?>" alt="<?php esc_attr_e( 'Verification icon', 'customer-email-verification-for-woocommerce' ); ?>">
							<?php endif; ?>
						</div>

						<div class="cev-authorization__heading">
							<span class="cev-authorization__title" style="font-size: <?php echo esc_attr( $cev_button_text_header_font_size ); ?>px;">
								<?php echo esc_html( $heading ); ?>
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
							<div class="cev-pin-verification__row">
								<div class="cev-field cev-field_size_extra-large cev-field_icon_left cev-field_event_right cev-field_text_center">
									<h5 class="required-field">
										<?php
										$codelength = apply_filters( 'cev_verification_code_length', __( '4-digit code', 'customer-email-verification-for-woocommerce' ) );
										echo esc_html( $codelength );
										?>
										*
									</h5>
									<input
										class="cev_pin_box"
										id="cev_pin1"
										name="cev_pin1"
										type="text"
										placeholder="<?php echo esc_attr( sprintf( 'Enter %s', $codelength ) ); ?>"
									>
								</div>
							</div>

							<div class="cev-pin-verification__failure js-pincode-invalid" style="display: none;">
								<div class="cev-alert cev-alert_theme_red">
									<span class="js-pincode-error-message"><?php esc_html_e( 'Verification code does not match', 'customer-email-verification-for-woocommerce' ); ?></span>
								</div>
							</div>

							<div class="cev-pin-verification__events">
								<input type="hidden" name="cev_user_id" value="<?php echo esc_attr( get_current_user_id() ); ?>">
								<?php wp_nonce_field( 'cev_verify_user_email_with_pin', 'cev_verify_user_email_with_pin' ); ?>
								<input type="hidden" name="action" value="cev_verify_user_email_with_pin">

								<button
									class="cev-button cev-button_size_promo cev-button_type_block cev-pin-verification__button is-disabled"
									id="SubmitPinButton"
									type="submit"
									style="
										background-color: <?php echo esc_attr( $cev_button_color_widget_header ); ?>;
										color: <?php echo esc_attr( $cev_button_text_color_widget_header ); ?>;
										font-size: <?php echo esc_attr( $cev_button_text_size_widget_header ); ?>px;
									"
								>
									<?php esc_html_e( 'Verify code', 'customer-email-verification-for-woocommerce' ); ?>
								</button>
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
