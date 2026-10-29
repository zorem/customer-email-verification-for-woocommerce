<?php
/**
 * Verification stats.
 *
 * Counts customers who verified their email (from any path: signup OTP,
 * verification link, PIN, admin verify) and exposes the numbers for the
 * Unverified Users tab and the upgrade-notice rules.
 *
 * @package Customer_Email_Verification_For_WooCommerce
 * @since 2.8.2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WC_CEV_Verification_Stats {

	/**
	 * User meta: timestamp of the first time the user became verified.
	 */
	const VERIFIED_AT_META = 'cev_verified_at';

	/**
	 * Transient caching the counts shown in the admin.
	 */
	const COUNTS_TRANSIENT = 'cev_verification_counts';

	/**
	 * Instance of this class.
	 *
	 * @var WC_CEV_Verification_Stats
	 */
	private static $instance;

	/**
	 * Get the class instance.
	 *
	 * @return WC_CEV_Verification_Stats
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Register hooks.
	 */
	public function init() {
		// Every verification path ends in customer_email_verified = 'true'.
		add_action( 'added_user_meta', array( $this, 'maybe_record_verification' ), 10, 4 );
		add_action( 'updated_user_meta', array( $this, 'maybe_record_verification' ), 10, 4 );

	}

	/**
	 * Stamp the verification time the first time a user becomes verified.
	 *
	 * @param int    $meta_id    Meta row ID.
	 * @param int    $user_id    User ID.
	 * @param string $meta_key   Meta key.
	 * @param mixed  $meta_value New value.
	 */
	public function maybe_record_verification( $meta_id, $user_id, $meta_key, $meta_value ) {
		if ( 'customer_email_verified' !== $meta_key || 'true' !== $meta_value ) {
			return;
		}
		if ( get_user_meta( $user_id, self::VERIFIED_AT_META, true ) ) {
			return;
		}
		update_user_meta( $user_id, self::VERIFIED_AT_META, time() );
		update_option( 'cev_has_verified_customer', 1, false );
		delete_transient( self::COUNTS_TRANSIENT );
	}

	/**
	 * Verification counts, cached for an hour.
	 *
	 * @return array {
	 *     @type int $total      Customers with a verified email.
	 *     @type int $this_month Customers who verified since the 1st of this month.
	 *     @type int $pending    Signups waiting for their code (cev_user_log rows).
	 * }
	 */
	public function get_counts() {
		$counts = get_transient( self::COUNTS_TRANSIENT );
		if ( is_array( $counts ) ) {
			return $counts;
		}

		$total_query = new WP_User_Query(
			array(
				// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- Count only, cached for an hour.
				'meta_key'    => 'customer_email_verified',
				// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value -- Count only, cached for an hour.
				'meta_value'  => 'true',
				'number'      => 1,
				'fields'      => 'ID',
				'count_total' => true,
			)
		);

		$month_start = strtotime( gmdate( 'Y-m-01 00:00:00' ) );
		$month_query = new WP_User_Query(
			array(
				// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- Count only, cached for an hour.
				'meta_query'  => array(
					array(
						'key'     => self::VERIFIED_AT_META,
						'value'   => $month_start,
						'compare' => '>=',
						'type'    => 'NUMERIC',
					),
				),
				'number'      => 1,
				'fields'      => 'ID',
				'count_total' => true,
			)
		);

		global $wpdb;
		// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQLPlaceholders.UnquotedComplexPlaceholder
		$pending = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->prefix}cev_user_log WHERE 1 = %d", 1 ) );
		// phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQLPlaceholders.UnquotedComplexPlaceholder

		$counts = array(
			'total'      => (int) $total_query->get_total(),
			'this_month' => (int) $month_query->get_total(),
			'pending'    => (int) $pending,
		);

		set_transient( self::COUNTS_TRANSIENT, $counts, HOUR_IN_SECONDS );
		return $counts;
	}

	/**
	 * Drop the cached counts (e.g. after deleting pending rows).
	 */
	public function flush_counts() {
		delete_transient( self::COUNTS_TRANSIENT );
	}

}

/**
 * Accessor for the stats instance.
 *
 * @since 2.8.2
 *
 * @return WC_CEV_Verification_Stats
 */
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound -- Follows the plugin's existing accessor naming.
function cev_verification_stats() {
	return WC_CEV_Verification_Stats::get_instance();
}
