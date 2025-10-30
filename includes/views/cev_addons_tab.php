<?php

$more_plugins = array(
	0 => array(
		'title' => 'Advanced Shipment Tracking Pro',
		'description' => 'AST PRO provides powerful features to easily add tracking info to WooCommerce orders, automate the fulfillment workflows and keep your customers happy and informed. AST allows you to easily add tracking and fulfill your orders straight from the Orders page, while editing orders, and allows customers to view the tracking i from the View Order page.',
		'url' => 'https://www.zorem.com/product/woocommerce-advanced-shipment-tracking/?utm_source=wp-admin&utm_medium=ast-pro&utm_campaign=add-ons',
		'image' => 'ast.png',
		'width' => '140px',
		'file' => 'ast-pro/ast-pro.php'
	),
	1 => array(
		'title' => 'TrackShip for WooCommerce',
		'description' => 'Take control of your post-shipping workflows, reduce time spent on customer service and provide a superior post-purchase experience to your customers.Beyond automatic shipment tracking, TrackShip brings a branded tracking experience into your store, integrates into your workflow, and takes care of all the touch points with your customers after shipping.',
		'url' => 'https://wordpress.org/plugins/trackship-for-woocommerce/#utm_source=wp-admin&utm_medium=CEV&utm_campaign=add-ons',
		'image' => 'trackship.png',
		'width' => '90px',
		'file' => 'trackship-for-woocommerce/trackship-for-woocommerce.php'
	),
	2 => array(
		'title' => 'SMS for WooCommerce',
		'description' => 'Keep your customers informed by sending them automated SMS text messages with order & delivery updates. You can send SMS notifications to customers when the order status is updated or when the shipment is out for delivery and more…',
		'url' => 'https://www.zorem.com/products/sms-for-woocommerce/#utm_source=wp-admin&utm_medium=CEV&utm_campaign=add-ons',
		'image' => 'smswoo.png',
		'width' => '90px',
		'file' => 'sms-for-woocommerce/sms-for-woocommerce.php'
	),
	3 => array(
		'title' => 'Zorem Local Pickup Pro',
		'description' => 'The Advanced Local Pickup (ALP) helps you manage the local pickup orders workflow more conveniently by extending the WooCommerce Local Pickup shipping method. The Pro you set up multiple pickup locations, , split the business hours, apply discounts by pickup location, display local pickup message on the products pages, allow customers to choose pickup location per product, force products to be local pickup only and more…',
		'url' => 'https://www.zorem.com/product/zorem-local-pickup-pro/#utm_source=wp-admin&utm_medium=CEV&utm_campaign=add-ons',
		'image' => 'alp.png',
		'width' => '60px',
		'file' => 'advanced-local-pickup-pro/advanced-local-pickup-pro.php'
	),
	4 => array(
		'title' => 'Email Reports for WooCommerce',
		'description' => 'The Sales Report Email Pro will help know how well your store is performing and how your products are selling by sending you a daily, weekly, or monthly sales report by email, directly from your WooCommerce store.',
		'url' => 'https://www.zorem.com/product/email-reports-for-woocommerce/#utm_source=wp-admin&utm_medium=CEV&utm_campaign=add-ons',
		'image' => 'erw.png',
		'width' => '60px',
		'file' => 'advanced-order-status-manager/advanced-order-status-manager.php'
	),
	5 => array(
		'title' => 'Country Based Restriction for WooCommerce',
		'description' => 'The country-based restrictions plugin by zorem works by the WooCommerce Geolocation or the shipping country added by the customer and allows you to restrict products on your store to sell or not to sell to specific countries.',
		'url' => 'https://www.zorem.com/product/country-based-restriction-pro/#utm_source=wp-admin&utm_medium=CEV&utm_campaign=add-ons',
		'image' => 'cbr.png',
		'width' => '70px',
		'file' => 'country-based-restriction-pro-addon/country-based-restriction-pro-addon.php'
	),	
);

?>
<section id="cev_content_addons" class="cev_tab_section">
	<div class="tab_container_without_bg" style="">        	
		<section id="content_tab_addons" class="">        				
			
			<div class="cev-pro-section">
				<div class="cev-row">
					<div class="cev-col-6">
						<div class="cev_col_inner">
							<h1 class="cev_pro_landing_header">Customer Email Verification Pro</h1>
							<div class="cev_pro_features_wrapper">
								<ul class="cev_pro_features_list">
									<li>Email verification on checkout</li>
									<li>Verification for free orders only</li>
									<li>Automatic account email verification</li>
									<li>Checkout verification type</li>
									<li>Fully customize the verification process</li>
								</ul>

								<ul class="cev_pro_features_list">
									<li>Delay new-account email</li>
									<li>Re-verify customer email address</li>
									<li>Verification expiration</li>
									<li>Limit resend</li>
									<li>Bulk resend and auto-delete</li>
								</ul>
							</div>
							<a href="https://www.zorem.com/product/customer-email-verification/" class="button button-primary btn_cev3" target="_blank">Upgrade Now <span class="dashicons dashicons-arrow-right-alt2"></span></a>							
						</div>
					</div>									
					<div class="cev-col-6">
						<div class="cev_pro_landing_banner">
							<img src="<?php echo esc_url( woo_customer_email_verification()->plugin_dir_url() ); ?>assets/images/cev-banner.png?<?php echo esc_html_e( time() ); ?>">
						</div>
					</div>
				</div>
			</div>
			
			<div class="plugins_section zorem_plugin_section">				
				<div class="zorem_plugin_container">
					<?php foreach ( $more_plugins as $mplugin ) { ?>
						<div class="zorem_single_plugin">
							<div class="free_plugin_inner">
								<div class="plugin_image">
									<img src="<?php echo esc_url( woo_customer_email_verification()->plugin_dir_url() ); ?>assets/images/<?php esc_html_e( $mplugin['image'] ); ?>?<?php echo esc_html_e( time() ); ?>">
									<h3 class="plugin_title"><?php esc_html_e( $mplugin['title'] ); ?></h3>
								</div>
								<div class="plugin_description">
									<p><?php esc_html_e( $mplugin['description'] ); ?></p>
									<?php 
									if ( is_plugin_active( $mplugin['file'] ) ) {
										?>
									<button type="button" class="button button button-primary btn_green">Active</button>
								<?php } else { ?>
									<a href="<?php esc_html_e( $mplugin['url'] ); ?>" class="button button-primary btn_cev2" target="blank">Buy Now</a>
								<?php } ?>								
								</div>
							</div>	
						</div>	
					<?php } ?>						
				</div>
			</div>
		</section>						
	</div>
</section>
