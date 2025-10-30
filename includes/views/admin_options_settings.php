<?php
/**
 * Html code for settings tab
 */

 $ignore = get_transient( 'cev_settings_admin_notice_ignore' );
 $dismissable_url = esc_url(  add_query_arg( 'cev-pro-settings-ignore-notice', 'true' ) ); 
?>

<section id="cev_content_settings" class="cev_tab_section">
	
	<form method="post" id="cev_settings_form" action="" enctype="multipart/form-data"><?php #nonce ?>
		<div class="accordion_container">
			<div class="accordion_set">
				<div class="accordion heading cev-main-settings">
					<?php
					$checked = get_option( 'cev_enable_email_verification', 1 ) ? 'checked' : ''; 
					$disable_toggle_class = get_option( 'cev_enable_email_verification', 1 ) ? '' : 'disable_toggle';
					?>
					<div class="accordion-toggle">
						<input type="hidden" name="cev_enable_email_verification" value="0"/>
						<input class="tgl tgl-flat-cev" id="cev_enable_email_verification" name="cev_enable_email_verification" type="checkbox" <?php esc_html_e( $checked ); ?> value="1"/>
						<label class="tgl-btn tgl-panel-label" for="cev_enable_email_verification"></label>
					</div>
					<div class="accordion-open accordion-label <?php esc_html_e( $disable_toggle_class ); ?>">
						<?php esc_html_e( 'Signup Verification', 'customer-email-verification-for-woocommerce' ); ?>						
					</div>
				</div>
				<div class="panel options add-tracking-option active">
					<?php $this->get_html( $this->get_cev_settings_data() ); ?>
				</div>
			</div>
			<button name="save" class="button-primary woocommerce-save-button cev_settings_save" type="submit" value="Save changes"><?php esc_html_e( 'Save Changes', 'customer-email-verification-for-woocommerce' ); ?></button>
			
		</div>		
		<?php wp_nonce_field( 'cev_settings_form_nonce', 'cev_settings_form_nonce' ); ?>
		<input type="hidden" name="action" value="cev_settings_form_update">		
	</form>
</section>
