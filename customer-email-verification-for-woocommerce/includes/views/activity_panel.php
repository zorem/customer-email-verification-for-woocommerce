<?php
$support_link = class_exists( 'customer_email_verification_pro' ) ? 'https://www.zorem.com/?support=1' : 'https://wordpress.org/support/plugin/customer-email-verification-for-woocommerce/#new-topic-0' ;
?>
<div class="menu-container">
	<button class="menu-button">
		<span class="menu-icon">
			<span class="dashicons dashicons-menu-alt"></span>
		</span>
	</button>
	<div class="popup-menu">
		<?php
		// Define the support link
		$support_link = 'https://www.zorem.com/?support=1';
		// Plugin directory URL
		$plugin_url = esc_url( woo_customer_email_verification()->plugin_dir_url() );
		?>
		<a href="<?php echo esc_url( $support_link ); ?>" class="menu-item" target="_blank">
			<span class="menu-icon">
				<img src="<?php echo esc_url( $plugin_url ); ?>assets/images/get-support-icon-20.svg" alt="Get Support">
			</span>
			Get Support
		</a>
		<a href="https://docs.zorem.com/docs/customer-email-verification-free/" class="menu-item" target="_blank">
		<span class="menu-icon">
			<img src="<?php echo esc_url( $plugin_url ); ?>assets/images/documentation-icon-20.svg" alt="Documentation">
		</span>
			Documentation
		</a>
	</div>
</div>
