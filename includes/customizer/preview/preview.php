<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
 * Email customizer preview document.
 *
 * Included from Cev_Initialise_Customizer_Settings::set_up_preview(), so
 * $this is the customizer settings instance. The email itself renders in an
 * iframe (see preview_account_email()) so its own <head> styles apply.
 */
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
	<head>
		<meta charset="<?php bloginfo( 'charset' ); ?>" />
		<meta name="viewport" content="width=device-width" />
		<style type="text/css" id="cev_designer_custom_css">.woocommerce-store-notice.demo_store, .mfp-hide {display: none;} body.cev_preview_body {margin: 0;}</style>
	</head>
	<body class="cev_preview_body">
		<div id="overlay"></div>
		<div id="cev_preview_wrapper" style="display: block;">
			<?php $this->preview_account_email(); ?>
		</div>
		<?php
		// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Core WordPress/WooCommerce hook, not owned by this plugin.
		do_action( 'woomail_footer' );
		wp_footer();
		?>
	</body>
</html>
