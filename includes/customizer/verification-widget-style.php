<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
/**
 * Customizer Setup and Custom Controls
 *
 */

/**
 * Adds the individual sections, settings, and controls to the theme customizer
 */
class Cev_Verification_Widget_Style {
	// Get our default values	
	private static $order_ids  = null;
	
	public function __construct() {
		
	}			
		
	
	/**
	 * Checks to see if we are opening our custom customizer preview
	 *	 
	 * @return bool
	 */
	public static function is_own_preview_request() {
		// Read-only screen/preview check, not form processing.
		// phpcs:ignore WordPress.Security.NonceVerification.Missing,WordPress.Security.NonceVerification.Recommended
		$cev_action = isset( $_REQUEST['action'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ) : '';

		return 'preview_cev_verification_lightbox' === $cev_action;
	}
	
	/**
	 * Checks to see if we are opening our custom customizer controls
	 *	 
	 * @return bool
	 */
	public static function is_own_customizer_request() {
		// Read-only screen/preview check, not form processing.
		// phpcs:ignore WordPress.Security.NonceVerification.Missing,WordPress.Security.NonceVerification.Recommended
		$cev_section = isset( $_REQUEST['section'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['section'] ) ) : '';

		return 'cev_verification_widget_style' === $cev_section;
	}		
}

/**
 * Initialise our Customizer settings
 */

new Cev_Verification_Widget_Style();
