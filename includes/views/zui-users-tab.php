<?php
/**
 * Unverified Users tab body (ZUI redesign, Free).
 *
 * Visual design mirrors the CEV Pro plugin's Unverified Users tab
 * (section header → stats line → single card containing the bulk
 * toolbar + the DataTables-enhanced log table with avatar/name/email
 * cells, relative-time cells, and a kebab actions menu). Pro-only
 * features (Send reminder, Export CSV, Maintenance, sidebar nav) are
 * intentionally omitted — only the design is matched, not the features.
 *
 * The original JS hook IDs/classes are preserved so the existing
 * admin.js handlers continue to work without modification:
 *   - #bulk_action            (select)
 *   - .apply_bulk_action      (Apply button)
 *   - #select_all             (master checkbox)
 *   - .row_checkbox           (row checkbox)
 *   - .delete_button[data-id] (per-row delete — wired inside the kebab menu)
 *   - #userLogTable           (table id, used by DataTables)
 *
 * @package Customer_Email_Verification_For_WooCommerce
 * @since 2.7
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $wpdb;

// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.PreparedSQLPlaceholders.UnquotedComplexPlaceholder
$cev_user_rows = $wpdb->get_results(
	$wpdb->prepare(
		"SELECT id, email, last_updated FROM {$wpdb->prefix}cev_user_log WHERE 1 = %d ORDER BY last_updated DESC",
		1
	),
	ARRAY_A
);
// phpcs:enable

$cev_total_pending = is_array( $cev_user_rows ) ? count( $cev_user_rows ) : 0;

// Inline Lucide-style icon helper (matches Pro's `$cev_icon` pattern).
$cev_icon = function ( $name ) {
	$paths = array(
		'user-x'      => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><line x1="17" y1="8" x2="22" y2="13"></line><line x1="22" y1="8" x2="17" y2="13"></line>',
		'users'       => '<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path>',
		'wrench'      => '<path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"></path>',
		'trash'       => '<polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>',
		'chevron'     => '<polyline points="6 9 12 15 18 9"></polyline>',
		'lock'        => '<rect width="18" height="11" x="3" y="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path>',
		'download'    => '<path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line>',
		'mail'        => '<rect x="2" y="4" width="20" height="16" rx="2"></rect><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"></path>',
	);
	$d = isset( $paths[ $name ] ) ? $paths[ $name ] : '';
	return '<svg class="zui-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $d . '</svg>';
};

$cev_print_html = function ( $html ) {
	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Callers pass SVG / HTML built from constant maps with no user input; wp_kses_post would strip required SVG attributes.
	echo $html;
};

$cev_docs_url    = 'https://docs.zorem.com/docs/customer-email-verification-free/';
$cev_support_url = 'https://www.zorem.com/support/';
?>
<div class="zui-layout">

	<aside class="zui-sidebar">

		<nav class="zui-sidebar__nav" aria-label="<?php esc_attr_e( 'Unverified Users sections', 'customer-email-verification-for-woocommerce' ); ?>">
			<button type="button" class="zui-sidebar__item is-active" data-zui-section="user-list" aria-current="page">
				<span class="zui-sidebar__icon"><?php $cev_print_html( $cev_icon( 'users' ) ); ?></span>
				<span class="zui-sidebar__label"><?php esc_html_e( 'User List', 'customer-email-verification-for-woocommerce' ); ?></span>
			</button>
			<button type="button" class="zui-sidebar__item" data-zui-section="maintenance">
				<span class="zui-sidebar__icon"><?php $cev_print_html( $cev_icon( 'wrench' ) ); ?></span>
				<span class="zui-sidebar__label"><?php esc_html_e( 'Maintenance', 'customer-email-verification-for-woocommerce' ); ?></span>
				<span class="cev-pro-tag">
					<span class="cev-pro-badge"><?php esc_html_e( 'PRO', 'customer-email-verification-for-woocommerce' ); ?></span>
					<span class="cev-pro-lock-icon"><svg class="cev-pro-lock-svg" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg></span>
				</span>
			</button>
		</nav>

		<?php /* Canonical Quick Help block — markup straight from
		        assets/zui/ZUI-COMPONENTS-PREVIEW.html (#cmp-quickhelp). */ ?>
		<div class="zui-quickhelp">
			<h4 class="zui-quickhelp__title"><?php esc_html_e( 'Quick Help', 'customer-email-verification-for-woocommerce' ); ?></h4>
			<p class="zui-quickhelp__text"><?php esc_html_e( 'Learn how to configure Customer Email Verification for best results.', 'customer-email-verification-for-woocommerce' ); ?></p>

			<div class="zui-quickhelp__links">
				<a class="zui-quickhelp__link" href="<?php echo esc_url( $cev_docs_url ); ?>" target="_blank" rel="noreferrer noopener">
					<span><?php esc_html_e( 'View Documentation', 'customer-email-verification-for-woocommerce' ); ?></span>
					<svg class="zui-icon" xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
				</a>
				<a class="zui-quickhelp__link zui-quickhelp__link--muted" href="<?php echo esc_url( $cev_support_url ); ?>" target="_blank" rel="noreferrer noopener">
					<span><?php esc_html_e( 'Get Support', 'customer-email-verification-for-woocommerce' ); ?></span>
					<svg class="zui-icon" xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
				</a>
			</div>

			<div class="zui-quickhelp__art" aria-hidden="true">
				<span class="zui-quickhelp__glow"></span>
				<span class="zui-quickhelp__sphere"></span>

				<span class="zui-quickhelp__paper zui-quickhelp__paper--1">
					<span class="zui-quickhelp__line zui-quickhelp__line--brand"></span>
					<span class="zui-quickhelp__line zui-quickhelp__line--mid"></span>
					<span class="zui-quickhelp__line zui-quickhelp__line--short"></span>
				</span>
				<span class="zui-quickhelp__paper zui-quickhelp__paper--2">
					<span class="zui-quickhelp__line zui-quickhelp__line--accent"></span>
					<span class="zui-quickhelp__line zui-quickhelp__line--mid"></span>
					<span class="zui-quickhelp__line zui-quickhelp__line--mid"></span>
					<span class="zui-quickhelp__line zui-quickhelp__line--short"></span>
				</span>

				<span class="zui-quickhelp__check">&#10003;</span>
			</div>
		</div>

	</aside>

	<main class="zui-content">
		<section class="zui-section is-active" data-zui-section="user-list">

		<div class="zui-section-header">
			<div class="zui-section-header__main">
				<span class="zui-section-header__icon"><?php $cev_print_html( $cev_icon( 'user-x' ) ); ?></span>
				<div class="zui-section-header__text">
					<div class="zui-section-header__titlewrap">
						<h2 class="zui-section-header__title">
							<?php esc_html_e( 'Unverified Users', 'customer-email-verification-for-woocommerce' ); ?>
						</h2>
					</div>
					<p class="zui-section-header__sub">
						<?php esc_html_e( 'Customers who started signing up but never verified their email address.', 'customer-email-verification-for-woocommerce' ); ?>
					</p>
				</div>
			</div>
		</div>

		<p class="cev-stats-line">
			<strong><?php echo esc_html( number_format_i18n( $cev_total_pending ) ); ?></strong>
			<?php esc_html_e( 'pending verifications', 'customer-email-verification-for-woocommerce' ); ?>
		</p>

		<?php if ( ! empty( $cev_user_rows ) ) : ?>

			<div class="zui-card cev-table-card">

				<div class="zui-bulkbar" role="toolbar" aria-label="<?php esc_attr_e( 'Bulk actions', 'customer-email-verification-for-woocommerce' ); ?>">
					<div class="zui-bulkbar__group">
						<label for="bulk_action" class="screen-reader-text">
							<?php esc_html_e( 'Select bulk action', 'customer-email-verification-for-woocommerce' ); ?>
						</label>
						<div class="zui-select-wrap">
							<select id="bulk_action" name="bulk_action" class="zui-select" aria-label="<?php esc_attr_e( 'Bulk action', 'customer-email-verification-for-woocommerce' ); ?>">
								<option value=""><?php esc_html_e( 'Bulk Action', 'customer-email-verification-for-woocommerce' ); ?></option>
								<option value="delete"><?php esc_html_e( 'Delete', 'customer-email-verification-for-woocommerce' ); ?></option>
							</select>
							<span class="zui-select-chevron"><?php $cev_print_html( $cev_icon( 'chevron' ) ); ?></span>
						</div>
						<button type="button" class="zui-btn-soft apply_bulk_action">
							<?php esc_html_e( 'Apply', 'customer-email-verification-for-woocommerce' ); ?>
						</button>
					</div>
				</div>

				<table id="userLogTable" class="zui-table display" role="table" aria-label="<?php esc_attr_e( 'Unverified user log entries', 'customer-email-verification-for-woocommerce' ); ?>">
					<thead>
						<tr role="row">
							<th scope="col" class="cev-table__check sorting_disabled" data-orderable="false">
								<label class="zui-checkbox">
									<input type="checkbox" id="select_all" class="zui-checkbox__input" aria-label="<?php esc_attr_e( 'Select all entries', 'customer-email-verification-for-woocommerce' ); ?>" />
									<span class="zui-checkbox__box"></span>
								</label>
							</th>
							<th scope="col"><?php esc_html_e( 'Customer', 'customer-email-verification-for-woocommerce' ); ?></th>
							<th scope="col"><?php esc_html_e( 'Last Updated', 'customer-email-verification-for-woocommerce' ); ?></th>
							<th scope="col" class="cev-table__actions sorting_disabled" data-orderable="false">
								<span class="screen-reader-text"><?php esc_html_e( 'Actions', 'customer-email-verification-for-woocommerce' ); ?></span>
							</th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $cev_user_rows as $cev_row ) :
							$cev_entry_id    = isset( $cev_row['id'] ) ? absint( $cev_row['id'] ) : 0;
							$cev_entry_email = isset( $cev_row['email'] ) ? sanitize_email( $cev_row['email'] ) : '';
							$cev_last        = isset( $cev_row['last_updated'] ) ? $cev_row['last_updated'] : '';

							// Friendly name from email local part (e.g. youryc10+9010 → "Youryc10+9010").
							$cev_local = $cev_entry_email;
							if ( $cev_entry_email && false !== strpos( $cev_entry_email, '@' ) ) {
								$cev_local = strstr( $cev_entry_email, '@', true );
							}
							$cev_display = $cev_local ? trim( ucwords( str_replace( array( '.', '_', '-' ), ' ', $cev_local ) ) ) : $cev_entry_email;

							// Avatar initials from the display name (first 2 word-letters).
							$cev_initials = '';
							foreach ( explode( ' ', (string) $cev_display ) as $cev_word ) {
								if ( '' !== $cev_word ) {
									$cev_initials .= mb_substr( $cev_word, 0, 1 );
								}
								if ( mb_strlen( $cev_initials ) >= 2 ) {
									break;
								}
							}
							$cev_initials = strtoupper( '' !== $cev_initials ? $cev_initials : '?' );

							// Stable per-email avatar color.
							$cev_palette = array( '#2563eb', '#7c3aed', '#0891b2', '#059669', '#d97706', '#db2777', '#475569', '#0ea5e9' );
							$cev_color   = $cev_palette[ ( $cev_entry_email ? abs( crc32( strtolower( $cev_entry_email ) ) ) : 0 ) % count( $cev_palette ) ];

							// Two-line date cell (relative "3 hours ago" + absolute date).
							$cev_abs_date = __( 'N/A', 'customer-email-verification-for-woocommerce' );
							$cev_rel_date = '';
							if ( ! empty( $cev_last ) ) {
								$cev_ts = strtotime( $cev_last );
								if ( $cev_ts ) {
									$cev_abs_date = wp_date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $cev_ts );
									/* translators: %s: human-readable time difference (e.g. "3 hours") */
									$cev_rel_date = sprintf( __( '%s ago', 'customer-email-verification-for-woocommerce' ), human_time_diff( $cev_ts, current_time( 'timestamp' ) ) );
								} else {
									$cev_abs_date = $cev_last;
								}
							}
							?>
							<tr role="row">
								<td role="gridcell" class="cev-table__check">
									<label class="zui-checkbox">
										<input type="checkbox" class="row_checkbox zui-checkbox__input" id="row_checkbox_<?php echo esc_attr( $cev_entry_id ); ?>" value="<?php echo esc_attr( $cev_entry_id ); ?>" aria-label="<?php esc_attr_e( 'Select this entry', 'customer-email-verification-for-woocommerce' ); ?>" />
										<span class="zui-checkbox__box"></span>
									</label>
								</td>
								<td role="gridcell">
									<div class="zui-cell-user">
										<span class="zui-avatar" aria-hidden="true" style="--zui-c: <?php echo esc_attr( $cev_color ); ?>; --zui-c-fg: #fff;"><?php echo esc_html( $cev_initials ); ?></span>
										<div class="zui-cell-user__meta">
											<strong><?php echo esc_html( $cev_display ); ?></strong>
											<small><?php echo esc_html( $cev_entry_email ); ?></small>
										</div>
									</div>
								</td>
								<td role="gridcell" title="<?php echo esc_attr( $cev_abs_date ); ?>">
									<?php if ( '' !== $cev_rel_date ) : ?>
										<strong><?php echo esc_html( $cev_rel_date ); ?></strong>
										<small><?php echo esc_html( $cev_abs_date ); ?></small>
									<?php else : ?>
										<strong><?php echo esc_html( $cev_abs_date ); ?></strong>
									<?php endif; ?>
								</td>
								<td role="gridcell" class="cev-table__actions">
									<div class="zui-actions">
										<button type="button" class="zui-actions-toggle" aria-haspopup="menu" aria-expanded="false" aria-label="<?php /* translators: %s: customer email address */ printf( esc_attr__( 'Open actions menu for %s', 'customer-email-verification-for-woocommerce' ), esc_attr( $cev_entry_email ) ); ?>">
											<svg class="zui-icon" viewBox="0 0 24 24" fill="currentColor" stroke="none" aria-hidden="true"><circle cx="12" cy="5" r="1.6"></circle><circle cx="12" cy="12" r="1.6"></circle><circle cx="12" cy="19" r="1.6"></circle></svg>
										</button>
										<div class="zui-actions-menu" role="menu" hidden>
											<button type="button" role="menuitem" class="zui-actions-item zui-actions-item--danger delete_button" data-id="<?php echo esc_attr( $cev_entry_id ); ?>">
												<?php $cev_print_html( $cev_icon( 'trash' ) ); ?>
												<?php esc_html_e( 'Delete', 'customer-email-verification-for-woocommerce' ); ?>
											</button>
										</div>
									</div>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>

			</div>

		<?php else : ?>

			<div class="zui-card cev-placeholder">
				<p class="cev-placeholder__title">
					<?php esc_html_e( 'No unverified users', 'customer-email-verification-for-woocommerce' ); ?>
				</p>
				<p class="cev-placeholder__text">
					<?php esc_html_e( 'Everyone who signed up has verified their email — nothing pending right now.', 'customer-email-verification-for-woocommerce' ); ?>
				</p>
			</div>

		<?php endif; ?>

		</section>

		<?php
		/* ─── Maintenance pane (all features PRO-locked) ─────────────
		 * Mirrors the Pro plugin's Maintenance tab layout — same four
		 * cards (Auto-cleanup, Auto-delete accounts, Re-engagement,
		 * Tools) — but every field is rendered with the PRO lock
		 * pattern instead of a real control. Switched on via the
		 * sidebar's Maintenance item.
		 */
		?>
		<section class="zui-section" data-zui-section="maintenance" hidden>

			<div class="zui-section-header">
				<div class="zui-section-header__main">
					<span class="zui-section-header__icon"><?php $cev_print_html( $cev_icon( 'wrench' ) ); ?></span>
					<div class="zui-section-header__text">
						<div class="zui-section-header__titlewrap">
							<h2 class="zui-section-header__title">
								<?php esc_html_e( 'Maintenance', 'customer-email-verification-for-woocommerce' ); ?>
							</h2>
						</div>
						<p class="zui-section-header__sub">
							<?php esc_html_e( 'Keep your verification log and user list tidy with automated cleanup, account deletion, re-engagement emails and admin tools.', 'customer-email-verification-for-woocommerce' ); ?>
						</p>
					</div>
				</div>
			</div>

			<div class="zui-card">
				<div class="zui-card__head">
					<h3 class="zui-card__title"><?php esc_html_e( 'Auto-cleanup unverified records', 'customer-email-verification-for-woocommerce' ); ?></h3>
				</div>
				<?php $admin_instance->render_settings_fields( $admin_instance->get_cev_maintenance_settings_data() ); ?>
			</div>

			<div class="zui-card">
				<div class="zui-card__head">
					<h3 class="zui-card__title"><?php esc_html_e( 'Auto-delete unverified user accounts', 'customer-email-verification-for-woocommerce' ); ?></h3>
				</div>
				<?php $admin_instance->render_settings_fields( $admin_instance->get_cev_user_cleanup_settings_data() ); ?>
			</div>

			<div class="zui-card">
				<div class="zui-card__head">
					<h3 class="zui-card__title"><?php esc_html_e( 'Re-engagement reminder emails', 'customer-email-verification-for-woocommerce' ); ?></h3>
					<p class="zui-card__sub">
						<?php esc_html_e( 'Bring lapsed sign-ups back. The plugin emails users who never finished verifying — at the intervals you choose — with a styled reminder and a one-click link to complete sign-up.', 'customer-email-verification-for-woocommerce' ); ?>
					</p>
				</div>
				<?php $admin_instance->render_settings_fields( $admin_instance->get_cev_reengagement_settings_data() ); ?>
			</div>

			<div class="zui-card">
				<div class="zui-card__head">
					<h3 class="zui-card__title"><?php esc_html_e( 'Tools', 'customer-email-verification-for-woocommerce' ); ?></h3>
					<p class="zui-card__sub">
						<?php esc_html_e( 'Force re-verify your customer base — e.g. after a spam attack, or on a fresh install over legacy accounts. Every verified customer is marked unverified and must re-confirm their email on their next login. Administrators, editors and shop managers are never affected.', 'customer-email-verification-for-woocommerce' ); ?>
					</p>
				</div>
				<div class="zui-row zui-row--inline cev-pro-locked-field">
					<div class="zui-row__head">
						<span class="zui-row__label"><?php esc_html_e( 'Force re-verify all customers', 'customer-email-verification-for-woocommerce' ); ?></span>
						<p class="zui-row__desc"><?php esc_html_e( 'Re-verify runs in the background; large stores are processed in batches.', 'customer-email-verification-for-woocommerce' ); ?></p>
					</div>
					<div class="zui-row__control">
						<?php $admin_instance->render_pro_locked_control(); ?>
					</div>
				</div>
			</div>

		</section>
	</main>

</div>

<script>
/**
 * Kebab actions menu toggle (Unverified Users tab).
 *
 * Uses jQuery (already loaded by DataTables) for reliable cross-browser
 * delegated handling, and runs in the click event's CAPTURE phase with
 * stopImmediatePropagation() so the ZUI library's own .zui-actions
 * auto-init can't double-fire and immediately close what we just opened.
 *
 * Works even if zui.js / the ZUI library hasn't been deployed yet.
 */
( function () {
	if ( typeof window.jQuery === 'undefined' ) { return; }

	jQuery( function ( $ ) {

		function closeAll() {
			$( '.zui-scope.cev-admin .zui-actions-menu' ).attr( 'hidden', 'hidden' );
			$( '.zui-scope.cev-admin .zui-actions-toggle' ).attr( 'aria-expanded', 'false' );
		}

		// CAPTURE phase — fires before the library's bubble-phase handler.
		document.addEventListener( 'click', function ( e ) {
			var toggle = e.target.closest && e.target.closest( '.zui-scope.cev-admin .zui-actions-toggle' );
			if ( ! toggle ) { return; }

			e.preventDefault();
			e.stopPropagation();
			e.stopImmediatePropagation();

			var menu     = toggle.parentNode.querySelector( '.zui-actions-menu' );
			if ( ! menu ) { return; }

			var wasOpen = ! menu.hasAttribute( 'hidden' );
			closeAll();
			if ( ! wasOpen ) {
				menu.removeAttribute( 'hidden' );
				toggle.setAttribute( 'aria-expanded', 'true' );
			}
		}, true );

		// Close on click outside any .zui-actions cluster.
		$( document ).on( 'click', function ( e ) {
			if ( 0 === $( e.target ).closest( '.zui-scope.cev-admin .zui-actions' ).length ) {
				closeAll();
			}
		} );

		// Close on Escape.
		$( document ).on( 'keydown', function ( e ) {
			if ( 'Escape' === e.key || 27 === e.keyCode ) {
				closeAll();
			}
		} );

		// When the library fires its sidebar section-switch event,
		// re-fit the DataTable so columns don't collapse from the
		// brief `hidden` state on the User List section.
		document.addEventListener( 'zui:section', function ( e ) {
			var key = e && e.detail && e.detail.section;
			if ( 'user-list' === key && typeof window.cevInitUserLogTable === 'function' ) {
				setTimeout( function () {
					try { window.cevInitUserLogTable(); } catch ( err ) {}
				}, 0 );
			}
		} );
	} );
} )();
</script>
