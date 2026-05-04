<?php
/**
 * Html code for settings tab
 * Matches PRO version layout with locked PRO features
 * PRO badge (#3b64d3) + dashicons lock icon right-aligned per row
 * Sidebar inside section so CSS sibling tab selectors still work
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$ignore = get_transient( 'cev_settings_admin_notice_ignore' );
?>

<section id="cev_content_settings" class="cev_tab_section">
	<div class="cev-settings-columns">
		<div class="cev-settings-main">

			<form method="post" id="cev_settings_form" action="" enctype="multipart/form-data">
				<div class="settings_accordion accordion_container">

					<?php /* ═══ Section 1: Email Verification Settings ═══ */ ?>
					<div class="accordion_set">
						<div class="accordion heading cev-main-settings">
							<div class="accordion-open accordion-label first_label">
								<?php esc_html_e( 'Email verification settings', 'customer-email-verification-for-woocommerce' ); ?>
							</div>
						</div>
						<div class="panel">
							<ul class="settings_ul">

								<?php $checked = get_option( 'cev_enable_email_verification', 1 ) ? 'checked' : ''; ?>
								<li class="toogel">
									<div class="accordion-toggle">
										<input type="hidden" name="cev_enable_email_verification" value="0"/>
										<input class="tgl tgl-flat-cev" id="cev_enable_email_verification" name="cev_enable_email_verification" type="checkbox" <?php esc_html_e( $checked ); ?> value="1"/>
										<label class="tgl-btn tgl-panel-label" for="cev_enable_email_verification"></label>
									</div>
									<label class="settings_label">
										<?php esc_html_e( 'Enable Signup Verification', 'customer-email-verification-for-woocommerce' ); ?>
									</label>
								</li>

								<li><div class="cev_separator"></div></li>

								<li class="toogel cev-pro-locked-field">
									<div class="accordion-toggle">
										<input class="tgl tgl-flat-cev cev-pro-toggle" type="checkbox" disabled />
										<label class="tgl-btn tgl-panel-label cev-pro-no-click"></label>
									</div>
									<label class="settings_label">
										<?php esc_html_e( 'Enable Checkout Verification', 'customer-email-verification-for-woocommerce' ); ?>
									</label>
									<span class="cev-pro-tag">
										<span class="cev-pro-badge"><?php esc_html_e( 'PRO', 'customer-email-verification-for-woocommerce' ); ?></span>
										<span class="cev-pro-lock-icon"></span>
									</span>
								</li>

								<li class="cev-pro-locked-field">
									<label class="settings_label">
										<?php esc_html_e( 'Checkout Verification Type', 'customer-email-verification-for-woocommerce' ); ?>
									</label>
									<fieldset>
										<select class="select select2" disabled>
											<option selected><?php esc_html_e( 'Inline', 'customer-email-verification-for-woocommerce' ); ?></option>
											<option><?php esc_html_e( 'Popup', 'customer-email-verification-for-woocommerce' ); ?></option>
										</select>
									</fieldset>
								</li>

								<li class="cev-pro-locked-field">
									<label>
										<input type="checkbox" disabled />
										<span class="label"><?php esc_html_e( 'Require email verification only when Create an account during checkout is selected', 'customer-email-verification-for-woocommerce' ); ?></span>
									</label>
								</li>

								<li class="cev-pro-locked-field">
									<label>
										<input type="checkbox" disabled />
										<span class="label"><?php esc_html_e( 'Require checkout verification only for free orders', 'customer-email-verification-for-woocommerce' ); ?></span>
									</label>
								</li>

								<li class="cev-pro-locked-field"><div class="cev_separator"></div></li>

								<li class="toogel cev-pro-locked-field">
									<div class="accordion-toggle">
										<input class="tgl tgl-flat-cev cev-pro-toggle" type="checkbox" disabled />
										<label class="tgl-btn tgl-panel-label cev-pro-no-click"></label>
									</div>
									<label class="settings_label">
										<?php esc_html_e( 'Disable WooCommerce Store API Checkout', 'customer-email-verification-for-woocommerce' ); ?>
									</label>
									<span class="cev-pro-tag">
										<span class="cev-pro-badge"><?php esc_html_e( 'PRO', 'customer-email-verification-for-woocommerce' ); ?></span>
										<span class="cev-pro-lock-icon"></span>
									</span>
								</li>

							</ul>
						</div>
					</div>

					<?php /* ═══ Section 2: General Settings ═══ */ ?>
					<div class="accordion_set">
						<div class="accordion heading cev-main-settings">
							<div class="accordion-open accordion-label second_label">
								<?php esc_html_e( 'General Settings', 'customer-email-verification-for-woocommerce' ); ?>
							</div>
						</div>
						<div class="panel">
							<ul class="settings_ul">

								<li class="toogel cev-pro-locked-field">
									<label class="settings_label">
										<?php esc_html_e( 'OTP Length', 'customer-email-verification-for-woocommerce' ); ?>
									</label>
									<span class="cev-pro-tag">
										<span class="cev-pro-badge"><?php esc_html_e( 'PRO', 'customer-email-verification-for-woocommerce' ); ?></span>
										<span class="cev-pro-lock-icon"></span>
									</span>
								</li>
								<li class="cev-pro-locked-field">
									<fieldset>
										<select class="select select2" disabled>
											<option selected><?php esc_html_e( '4-digit', 'customer-email-verification-for-woocommerce' ); ?></option>
										</select>
									</fieldset>
								</li>

								<li class="toogel cev-pro-locked-field">
									<label class="settings_label">
										<?php esc_html_e( 'OTP Expiration', 'customer-email-verification-for-woocommerce' ); ?>
										<span class="woocommerce-help-tip tipTip" title="<?php esc_attr_e( 'Choose if you wish to set expiry time to the OTP.', 'customer-email-verification-for-woocommerce' ); ?>"></span>
									</label>
									<span class="cev-pro-tag">
										<span class="cev-pro-badge"><?php esc_html_e( 'PRO', 'customer-email-verification-for-woocommerce' ); ?></span>
										<span class="cev-pro-lock-icon"></span>
									</span>
								</li>
								<li class="cev-pro-locked-field">
									<fieldset>
										<select class="select select2" disabled>
											<option selected><?php esc_html_e( '10 min', 'customer-email-verification-for-woocommerce' ); ?></option>
										</select>
									</fieldset>
								</li>

								<li><div class="cev_separator"></div></li>

								<li class="toogel cev-pro-locked-field">
									<label class="settings_label">
										<?php esc_html_e( 'Verification Email Resend Limit', 'customer-email-verification-for-woocommerce' ); ?>
									</label>
									<span class="cev-pro-tag">
										<span class="cev-pro-badge"><?php esc_html_e( 'PRO', 'customer-email-verification-for-woocommerce' ); ?></span>
										<span class="cev-pro-lock-icon"></span>
									</span>
								</li>
								<li class="cev-pro-locked-field">
									<fieldset>
										<select class="select select2" disabled>
											<option selected><?php esc_html_e( 'Allow 3 Attempts', 'customer-email-verification-for-woocommerce' ); ?></option>
										</select>
									</fieldset>
								</li>

								<li class="toogel cev-pro-locked-field">
									<label class="settings_label">
										<?php esc_html_e( 'Resend Limit Message', 'customer-email-verification-for-woocommerce' ); ?>
										<span class="woocommerce-help-tip tipTip" title="<?php esc_attr_e( 'Limit the number of resend attempts.', 'customer-email-verification-for-woocommerce' ); ?>"></span>
									</label>
									<span class="cev-pro-tag">
										<span class="cev-pro-badge"><?php esc_html_e( 'PRO', 'customer-email-verification-for-woocommerce' ); ?></span>
										<span class="cev-pro-lock-icon"></span>
									</span>
								</li>
								<li class="cev-pro-locked-field">
									<fieldset>
										<textarea class="input-text regular-input" disabled placeholder="<?php esc_attr_e( 'Too many attempts, please contact us for further assistance', 'customer-email-verification-for-woocommerce' ); ?>"></textarea>
									</fieldset>
								</li>

								<li><div class="cev_separator"></div></li>

								<li class="top">
									<label class="settings_label">
										<?php esc_html_e( 'Email Verification Success Message', 'customer-email-verification-for-woocommerce' ); ?>
										<span class="woocommerce-help-tip tipTip" title="<?php esc_attr_e( 'The message that will appear on the top of the my-account or checkout page after successful email verification', 'customer-email-verification-for-woocommerce' ); ?>"></span>
									</label>
									<fieldset>
										<textarea placeholder="<?php esc_attr_e( 'Your email is verified!', 'customer-email-verification-for-woocommerce' ); ?>" class="input-text regular-input" name="cev_verification_success_message" id="cev_verification_success_message"><?php echo esc_textarea( get_option( 'cev_verification_success_message', __( 'Your email is verified!', 'customer-email-verification-for-woocommerce' ) ) ); ?></textarea>
									</fieldset>
								</li>

							</ul>
						</div>
					</div>

					<?php /* ═══ Section 3: Login Authentication ═══ */ ?>
					<div class="accordion_set">
						<div class="accordion heading cev-main-settings">
							<div class="accordion-open accordion-label second_label">
								<?php esc_html_e( 'Login Authentication', 'customer-email-verification-for-woocommerce' ); ?>
							</div>
						</div>
						<div class="panel">
							<ul class="settings_ul">

								<li class="toogel cev-pro-locked-field">
									<div class="accordion-toggle">
										<input class="tgl tgl-flat-cev cev-pro-toggle" type="checkbox" disabled />
										<label class="tgl-btn tgl-panel-label cev-pro-no-click"></label>
									</div>
									<label class="settings_label">
										<?php esc_html_e( 'Enable Login Authentication', 'customer-email-verification-for-woocommerce' ); ?>
									</label>
									<span class="cev-pro-tag">
										<span class="cev-pro-badge"><?php esc_html_e( 'PRO', 'customer-email-verification-for-woocommerce' ); ?></span>
										<span class="cev-pro-lock-icon"></span>
									</span>
								</li>

								<li class="cev-pro-locked-field">
									<label>
										<input type="checkbox" checked disabled />
										<span class="label"><?php esc_html_e( 'Require OTP verification for unrecognized login', 'customer-email-verification-for-woocommerce' ); ?></span>
									</label>
								</li>

								<li class="cev-pro-locked-field"><div class="cev_separator"></div></li>

								<li class="cev-pro-locked-field">
									<p class="section_desc"><?php esc_html_e( 'Unrecognized Login Conditions:', 'customer-email-verification-for-woocommerce' ); ?></p>
								</li>

								<li class="cev-pro-locked-field">
									<label>
										<input type="checkbox" checked disabled />
										<span class="label"><?php esc_html_e( 'Login from a new device', 'customer-email-verification-for-woocommerce' ); ?></span>
									</label>
								</li>

								<li class="cev-pro-locked-field">
									<label>
										<input type="checkbox" checked disabled />
										<span class="label"><?php esc_html_e( 'Login from a new location', 'customer-email-verification-for-woocommerce' ); ?></span>
									</label>
								</li>

								<li class="cev-pro-locked-field enable_email_auth_for_login_time">
									<label>
										<input type="checkbox" checked disabled />
										<span class="label">
											<?php esc_html_e( 'Last login more than', 'customer-email-verification-for-woocommerce' ); ?>
											<select style="width: auto;" disabled>
												<option selected><?php esc_html_e( '15 Days', 'customer-email-verification-for-woocommerce' ); ?></option>
											</select>
										</span>
									</label>
								</li>

							</ul>
						</div>
					</div>

				</div>

				<div class="save_btn">
					<button name="save" class="button-primary woocommerce-save-button cev_settings_save" type="submit" value="Save changes"><?php esc_html_e( 'Save', 'customer-email-verification-for-woocommerce' ); ?></button>
				</div>

				<?php wp_nonce_field( 'cev_settings_form_nonce', 'cev_settings_form_nonce' ); ?>
				<input type="hidden" name="action" value="cev_settings_form_update">
			</form>

		</div>
		<div class="cev-settings-sidebar">
			<?php require_once( dirname(__FILE__) . '/cev_pro_sidebar.php' ); ?>
		</div>
	</div>
</section>
