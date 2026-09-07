<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WC_Customer_Email_Verification_Admin {		
	
	public $my_account_id;
	
	/**
	* Initialize the main plugin function
	*/
	public function __construct() {	
		$this->my_account_id = get_option( 'woocommerce_myaccount_page_id' );
	}
	
	/**
	 * Instance of this class.
	 *
	 * @var object Class Instance
	 */
	private static $instance;
	
	/**
	 * Get the class instance
	 *
	 * @return woo_customer_email_verification_Admin
	*/
	public static function get_instance() {

		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}
	
	/*
	* Init from parent mail class
	*/
	public function init() {
		add_action( 'wp_ajax_cev_settings_form_update', array( $this, 'cev_settings_form_update_fun') );
		add_filter( 'manage_users_columns', array( $this, 'add_column_users_list' ), 10, 1 );
		add_filter( 'manage_users_custom_column', array( $this, 'add_details_in_custom_users_list' ), 10, 3 );
		add_action( 'show_user_profile', array( $this, 'show_cev_fields_in_single_user' ) );
		add_action( 'edit_user_profile', array( $this, 'show_cev_fields_in_single_user' ) );
		add_action( 'admin_head', array( $this, 'cev_manual_verify_user' ) );      

		/*** Sort and Filter Users ***/
		add_action('restrict_manage_users', array( $this, 'filter_user_by_verified' ));	
		add_filter('pre_get_users', array( $this, 'filter_users_by_user_by_verified_section' ));
		
		/*** Bulk actions for Users ***/
		add_filter( 'bulk_actions-users', array( $this, 'add_custom_bulk_actions_for_user' ) );
		add_filter( 'handle_bulk_actions-users', array( $this, 'users_bulk_action_handler' ), 10, 3 );
		add_action( 'admin_notices', array( $this, 'user_bulk_action_notices' ) );
		
		if ( isset( $_GET['page'] ) && 'customer-email-verification-for-woocommerce' == $_GET['page'] ) {
			// Hook for add admin body class in settings page
			add_filter( 'admin_body_class', array( $this, 'cev_post_admin_body_class' ), 100 );
		}
		
		add_action( 'wp_ajax_cev_manualy_user_verify_in_user_menu', array( $this, 'cev_manualy_user_verify_in_user_menu') );

		// Delete user Ajax
		add_action( 'wp_ajax_delete_user', array( $this, 'cev_delete_user' ), 4);
		add_action( 'wp_ajax_delete_users', array( $this, 'cev_delete_users' ), 4);
	}
	public function cev_delete_user() {
		// Check nonce for security
		check_ajax_referer('delete_user_nonce', 'nonce');
	
		if (isset($_POST['id'])) {
			global $wpdb;
			$id = intval($_POST['id']);
			$table_name = $wpdb->prefix . 'cev_user_log';
			$result = $wpdb->delete($table_name, array('id' => $id), array('%d'));
	
			if (false !== $result) {
				echo json_encode(array('success' => true));
			} else {
				echo json_encode(array('success' => false));
			}
		}
		wp_die(); // Required to terminate immediately and return a proper response
	}
	
	public function cev_delete_users() {
		// Check nonce for security
		check_ajax_referer('delete_user_nonce', 'nonce');
		global $wpdb;
		$table_name = $wpdb->prefix . 'cev_user_log';
		
		// Initialize an array to hold the results
		$results = [
			'success' => 0,
			'failure' => 0,
			'messages' => []
		];
	
		// Check if 'ids' is set and is an array
		if (isset($_POST['ids']) && is_array($_POST['ids'])) {
			// Sanitize and validate input IDs
			$ids = array_map('intval', $_POST['ids']); // Sanitize IDs
			
			foreach ($ids as $id) {
				if ($id <= 0) {
					$results['failure']++;
					$results['messages'][] = "Invalid ID: $id";
					continue; // Skip to the next ID
				}
	
				// Perform the delete operation
				$result = $wpdb->delete($table_name, ['id' => $id], ['%d']);
				if ($result) {
					$results['success']++;
				} else {
					$results['failure']++;
					$results['messages'][] = "Failed to delete ID: $id";
				}
			}
		} else {
			$results['failure']++;
			$results['messages'][] = 'Invalid IDs';
		}
	
		// Return a single JSON response
		echo json_encode($results);
		wp_die(); // Required to terminate immediately and return a proper response
	}
	/*
	* Admin Menu add function
	* WC sub menu
	*/
	public function register_woocommerce_menu() {
		add_submenu_page( 'woocommerce', 'Customer Verification', 'Email Verification', 'manage_woocommerce', 'customer-email-verification-for-woocommerce', array( $this, 'wc_customer_email_verification_page_callback' ) ); 
	}
	
	/*
	* Add class in body tag
	*/
	public function cev_post_admin_body_class( $body_class ) {
		$body_class .= ' customer-email-verification-for-woocommerce';
		return $body_class;
	}
	
	/**
	* Load admin styles.
	*/
	public function admin_styles( $hook ) {						
		
		if ( !isset( $_GET['page'] ) ) {
			return;
		}
		
		if ( 'customer-email-verification-for-woocommerce' != $_GET['page'] ) {
			return;
		}

		// ----- Zorem UI shared admin design library -----
		// Used directly from assets/zui/ (single CSS + JS bundle). Loaded
		// first so the plugin's own admin assets can depend on it. The
		// VERSION file is the cache-bust token, so a library refresh busts
		// the browser cache automatically.
		$zui_url = woo_customer_email_verification()->plugin_dir_url() . 'assets/zui/';
		$zui_dir = woo_customer_email_verification()->get_plugin_path() . '/assets/zui/';
		$zui_ver = is_readable( $zui_dir . 'VERSION' )
			? trim( file_get_contents( $zui_dir . 'VERSION' ) )
			: woo_customer_email_verification()->version;
		wp_enqueue_style( 'zui', $zui_url . 'css/zui.css', array(), $zui_ver );
		wp_enqueue_script( 'zui', $zui_url . 'js/zui.js', array(), $zui_ver, true );

		// Additional library components that ship in assets/zui/css/components/
		// but aren't (yet) imported by the zui.css aggregator. The library
		// stylesheet is read-only for this plugin, so we enqueue each missing
		// Phase-1 component as its own handle, each depending on `zui` so
		// they always load after the foundation tokens/reset.
		$cev_zui_components = array( 'bulk-bar', 'actions-menu', 'avatar', 'table', 'datatables', 'upsell' );
		foreach ( $cev_zui_components as $cev_zui_component ) {
			wp_enqueue_style(
				'zui-' . $cev_zui_component,
				$zui_url . 'css/components/' . $cev_zui_component . '.css',
				array( 'zui' ),
				$zui_ver
			);
		}

		// CEV-only overrides on top of the shared library (scoped to .cev-admin).
		// Depends on the last library component so its rules always load LAST
		// and win the cascade for any colour / spacing adjustments.
		wp_enqueue_style( 'cev-admin-overrides', woo_customer_email_verification()->plugin_dir_url() . 'assets/css/cev-admin.css', array( 'zui', 'zui-bulk-bar', 'zui-actions-menu', 'zui-avatar', 'zui-table', 'zui-datatables', 'zui-upsell' ), time() );

		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		wp_register_script( 'select2', WC()->plugin_url() . '/assets/js/select2/select2.full' . $suffix . '.js', array( 'jquery' ), '4.0.3' );
		wp_enqueue_script( 'select2');
		wp_enqueue_style( 'wp-color-picker' );

		// Legacy admin.css is no longer enqueued — the new ZUI-based views
		// (header / settings / users / addons) get their styling from the
		// shared library (`zui` handle) + plugin overrides in `cev-admin.css`
		// (`cev-admin-overrides` handle). Leaving admin.css loaded conflicted
		// with the library's button / form / table rules.

		wp_enqueue_script( 'customer_email_verification_script', woo_customer_email_verification()->plugin_dir_url() . 'assets/js/admin.js', array( 'jquery','wp-util' ), time() , true);
		
		wp_localize_script( 'customer_email_verification_script', 'customer_email_verification_script', array() );
		wp_localize_script('customer_email_verification_script', 'cev_vars', array(
			'delete_user_nonce' => wp_create_nonce('delete_user_nonce'),
			'ajax_url' => admin_url('admin-ajax.php')
		)); 
		
		wp_register_script( 'selectWoo', WC()->plugin_url() . '/assets/js/selectWoo/selectWoo.full' . $suffix . '.js', array( 'jquery' ), '1.0.4' );
		wp_register_script( 'wc-enhanced-select', WC()->plugin_url() . '/assets/js/admin/wc-enhanced-select' . $suffix . '.js', array( 'jquery', 'selectWoo' ), WC_VERSION );
		wp_register_script( 'wc-jquery-blockui', WC()->plugin_url() . '/assets/js/jquery-blockui/jquery.blockUI' . $suffix . '.js', array( 'jquery' ), '2.70', true );
		
		

		wp_enqueue_script( 'selectWoo');
		wp_enqueue_script( 'wc-enhanced-select');

		wp_enqueue_script('datatables-js', 'https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js', array('jquery'), time(), true);
		wp_enqueue_style('datatables-css', 'https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css', array(), time());
		
		wp_enqueue_style( 'woocommerce_admin_styles', WC()->plugin_url() . '/assets/css/admin.css', array(), time());
		
		wp_enqueue_script( 'wc-jquery-tiptip', WC()->plugin_url() . '/assets/js/jquery-tiptip/jquery.tipTip.min.js', array( 'jquery' ), time(), true );
		wp_enqueue_script( 'wc-jquery-tiptip' );
		wp_enqueue_script( 'wc-jquery-blockui' );
		wp_enqueue_script( 'wp-color-picker' );		
		wp_enqueue_script( 'jquery-ui-sortable' );		
		wp_enqueue_script('media-upload');
		wp_enqueue_script('thickbox');		
		wp_enqueue_style('thickbox');								
	}
	
	/*
	* Callback for Customer Email Verification page
	*/
	public function wc_customer_email_verification_page_callback() { 
		wp_enqueue_script( 'customer_email_verification_table_rows' );	
		$ignore = get_transient( 'cev_settings_admin_notice_ignore' );
		$dismissable_url = esc_url(  add_query_arg( 'cev-pro-settings-ignore-notice', 'true' ) ); 
		$tab = isset( $_GET['tab'] ) ? sanitize_text_field( $_GET['tab'] ) : 'email-verification';

		$breadcrumb_text = __( 'Settings', 'customer-email-verification-for-woocommerce' );
		if ( 'unverified-users' === $tab ) {
			 $breadcrumb_text = __( 'Unverified Users', 'customer-email-verification-for-woocommerce' );
		} elseif ( 'add-ons' === $tab ) {
			 $breadcrumb_text = __( 'Go Pro', 'customer-email-verification-for-woocommerce' );
		}
		$admin_instance = $this;
		$plugin_path    = woo_customer_email_verification()->get_plugin_path();
		?>
		<div class="zui-scope cev-admin" id="cev-admin-app">

			<?php include $plugin_path . '/includes/views/zui-page-header.php'; ?>

			<?php /* All three tab panels live in the DOM at once; the inline JS
			        below toggles their visibility on tab click so navigating
			        between tabs does NOT reload the page. */ ?>

			<div class="cev-tab-panel" data-cev-tab="email-verification" <?php echo ( 'email-verification' === $tab ) ? '' : 'hidden'; ?>>
				<?php include $plugin_path . '/includes/views/zui-settings-page.php'; ?>
			</div>

			<div class="cev-tab-panel" data-cev-tab="unverified-users" <?php echo ( 'unverified-users' === $tab ) ? '' : 'hidden'; ?>>
				<?php require $plugin_path . '/includes/views/zui-users-tab.php'; ?>
			</div>

			<div class="cev-tab-panel" data-cev-tab="add-ons" <?php echo ( 'add-ons' === $tab ) ? '' : 'hidden'; ?>>
				<?php require $plugin_path . '/includes/views/zui-addons-tab.php'; ?>
			</div>

		</div>

		<script>
		// Client-side tab switching for the ZUI header. Tabs are real
		// ?tab= links so direct-deep-linking still works, but in-page
		// clicks toggle [hidden] on `.cev-tab-panel[data-cev-tab]` and
		// rewrite the URL via history.pushState — no page reload.
		( function () {
			var root = document.getElementById( 'cev-admin-app' );
			if ( ! root ) { return; }

			var tabs   = root.querySelectorAll( '.zui-tabs__item[data-cev-tab]' );
			var panels = root.querySelectorAll( '.cev-tab-panel[data-cev-tab]' );

			function activate( slug ) {
				tabs.forEach( function ( t ) {
					var match = t.getAttribute( 'data-cev-tab' ) === slug;
					t.classList.toggle( 'is-active', match );
					if ( match ) {
						t.setAttribute( 'aria-current', 'page' );
					} else {
						t.removeAttribute( 'aria-current' );
					}
				} );
				var activePanel = null;
				panels.forEach( function ( p ) {
					if ( p.getAttribute( 'data-cev-tab' ) === slug ) {
						p.removeAttribute( 'hidden' );
						activePanel = p;
					} else {
						p.setAttribute( 'hidden', '' );
					}
				} );

				// DataTables computes column widths from the table's bounding
				// box, so any table rendered inside a `[hidden]` panel collapses
				// to zero-width columns. Destroy + re-init now that the panel
				// is on screen, and re-scan for ZUI widgets that init lazily.
				// Defer to next tick so the browser has applied the visibility
				// change before DataTables re-measures.
				if ( activePanel ) {
					setTimeout( function () {
						if ( 'unverified-users' === slug && typeof window.cevInitUserLogTable === 'function' ) {
							try { window.cevInitUserLogTable(); } catch ( err ) {}
						}
						try {
							if ( window.ZUI && typeof ZUI.scan === 'function' ) {
								ZUI.scan( activePanel );
							}
						} catch ( err ) {}
					}, 0 );
				}
			}

			tabs.forEach( function ( t ) {
				t.addEventListener( 'click', function ( e ) {
					e.preventDefault();
					var slug = this.getAttribute( 'data-cev-tab' );
					if ( ! slug ) { return; }
					activate( slug );
					try {
						var url = new URL( window.location.href );
						url.searchParams.set( 'tab', slug );
						window.history.pushState( { cevTab: slug }, '', url );
					} catch ( err ) { /* old browser — skip URL update */ }
				} );
			} );

			window.addEventListener( 'popstate', function () {
				var url  = new URL( window.location.href );
				var slug = url.searchParams.get( 'tab' ) || 'email-verification';
				activate( slug );
			} );
		} )();
		</script>
		<?php
	}

	public function get_html_menu_tab( $arrays ) {
		$tab = isset( $_GET['tab'] ) ? sanitize_text_field( $_GET['tab'] ) : 'email-verification';
		foreach ( (array) $arrays as $id => $array ) {
			if ( isset( $array['type'] ) && 'link' == $array['type'] ) {
				?>
				<a class="menu_cev_link" href="<?php esc_html_e( esc_url( $array['link'] ) ); ?>"><?php esc_html_e( $array['title'] ); ?></a>
				<?php
			} else {
				?>
				<input class="cev_tab_input" id="<?php echo esc_html( $id ); ?>" name="<?php echo esc_html( $array['name'] ); ?>" type="radio"  data-tab="<?php echo esc_html( $array['data-tab'] ); ?>" data-label="<?php echo esc_html( $array['data-label'] ); ?>" <?php echo ( $tab == $array['data-tab'] ? 'checked' : '' ); ?> />
				<label class="<?php echo esc_html( $array['class'] ); ?>" for="<?php echo esc_html( $id ); ?>"><?php echo esc_html( $array['title'] ); ?></label>
				<?php
			}
		}
	}

	/**
	 * Render top tabs as ZUI link tabs.
	 *
	 * Renders the top tab strip inside the ZUI header. Tabs are real
	 * `?tab=<slug>` links that reload the page — the existing body's
	 * radio-based panel switcher reads `$_GET['tab']` to pick the active
	 * panel, so a server-side reload keeps everything in sync.
	 *
	 * @param array $arrays Tab configuration from get_cev_tab_settings_data().
	 * @return void
	 */
	public function render_zui_menu_tabs( $arrays ) {
		$current_tab = isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : 'email-verification';
		$cev_page    = isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : 'customer-email-verification-for-woocommerce';

		foreach ( (array) $arrays as $tab_id => $tab_data ) {

			// External link tab (e.g. Customize → email customizer).
			if ( isset( $tab_data['type'] ) && 'link' === $tab_data['type'] ) {
				?>
				<a class="zui-tabs__item" href="<?php echo esc_url( $tab_data['link'] ); ?>">
					<?php echo esc_html( $tab_data['title'] ); ?>
				</a>
				<?php
				continue;
			}

			// Standard tab → reload link with ?tab=<slug>.
			$tab_slug  = isset( $tab_data['data-tab'] ) ? $tab_data['data-tab'] : '';
			$is_active = ( $current_tab === $tab_slug );
			$tab_url   = admin_url( 'admin.php?page=' . $cev_page . '&tab=' . $tab_slug );
			?>
			<a
				class="zui-tabs__item<?php echo $is_active ? ' is-active' : ''; ?>"
				href="<?php echo esc_url( $tab_url ); ?>"
				data-cev-tab="<?php echo esc_attr( $tab_slug ); ?>"
				<?php echo $is_active ? 'aria-current="page"' : ''; ?>
			>
				<?php echo esc_html( $tab_data['title'] ); ?>
			</a>
			<?php
		}
	}
	
	public function get_cev_tab_settings_data() {
		
		$cev_customizer_settings = new cev_initialise_customizer_settings();
			
		$setting_data = array(
			'setting_tab' => array(					
				'title'		=> __( 'Settings', 'customer-email-verification-for-woocommerce' ),
				'show'      => true,
				'class'     => 'cev_tab_label first_label',
				'data-tab'  => 'email-verification',
				'data-label' => __( 'Settings', 'customer-email-verification-for-woocommerce' ),
				'name'  => 'tabs',				
			),
			
			'customize' => array(					
				'title'		=> __( 'Customize', 'customer-email-verification-for-woocommerce' ),
				'type'		=> 'link',
				'link'		=> $cev_customizer_settings->get_customizer_url( 'cev_main_controls_section', 'settings' ),
				'show'      => true,
				'class'     => 'tab_label',
				'data-tab'  => 'trackship',
				'data-label' => __( 'Customize', 'customer-email-verification-for-woocommerce' ),
				'name'  => 'tabs',
			),
			'user_tab' => array(
				'title'		=> __( 'Unverified Users', 'customer-email-verification-for-woocommerce' ),
				'show'      => true,
				'class'     => 'cev_tab_label',
				'data-tab'  => 'unverified-users',
				'data-label' => __( 'Unverified Users', 'customer-email-verification-for-woocommerce' ),
				'name'  => 'tabs',
			),
						
			'Add_tab' => array(					
				'title'		=> __( 'Go Pro', 'customer-email-verification-for-woocommerce' ) . ' ✨',
				'show'      => true,
				'class'     => 'cev_tab_label',
				'data-tab'  => 'add-ons',
				'data-label' => __( 'Go Pro', 'customer-email-verification-for-woocommerce' ),
				'name'  => 'tabs',
			),
		);
		
		return $setting_data;
	}
		
	/*
	* get html get_html of fields
	*/	
	public function get_html( $arrays ) { 
		
		$checked = '';
		?>
		<ul class="settings_ul">
			<?php 
			foreach ( ( array ) $arrays as $id => $array ) {	
				if ( $array['show'] ) {
					$class = isset( $array['class'] ) ? $array['class'] : '';
					?>
					<li class="<?php esc_html_e( $class ); ?>">
					<?php 
					if ( 'desc' != $array['type'] && 'checkbox' != $array['type'] && 'checkbox_select' != $array['type'] ) { 
						?>
					<label class="settings_label">
						<?php 
						esc_html_e( $array['title'] );
						if ( isset( $array['tooltip'] ) ) {
							?>
							<span class="woocommerce-help-tip tipTip" title="<?php esc_html_e( $array['tooltip'] ); ?>"></span>
						<?php } ?>
					</label>
					<?php
					}
					if ( isset( $array['type'] ) && 'dropdown' == $array['type'] ) { 
						$multiple = isset( $array['multiple'] ) ? 'multiple' : '';
						$field_id = isset( $array['multiple'] ) ? $array['multiple'] : $id;
						?>
						<fieldset>
							<select class="select select2" id="<?php esc_html_e( $field_id ); ?>" name="<?php esc_html_e( $id ); ?>" <?php esc_html_e( $multiple ); ?>>  
								<?php 
								foreach ( ( array ) $array['options'] as $key => $val ) {
									$selected = ( get_option( $id, $array['Default'] ) == ( string ) $key ) ? 'selected' : '';										
									?>
									<option value="<?php esc_html_e( $key ); ?>" <?php esc_html_e( $selected ); ?> ><?php esc_html_e( $val ); ?></option>
									<?php 
								} 
								?>
							</select>
						</fieldset>	
						<?php
					} elseif ( isset( $array['type'] ) && 'multiple_select' == $array['type'] ) {
						?>
						<div class="multiple_select_container">	
							<select multiple class="wc-enhanced-select" name="<?php esc_html_e( $id ); ?>[]" id="<?php esc_html_e( $id ); ?>">
							<?php
							foreach ( (array) $array['options'] as $key => $val ) :
								$multi_checkbox_data = get_option( $id );
								$checked = isset( $multi_checkbox_data[$key] ) && 1 == $multi_checkbox_data[$key] ? 'selected' : '' ; 
								?>
								<option value="<?php esc_html_e( $key ); ?>" <?php esc_html_e( $checked ); ?>>
									<?php esc_html_e( $val ); ?>
								</option>
							<?php 
							endforeach;
							?>
							</select>	
						</div>
					<?php
					} elseif ( 'checkbox' == $array['type'] ) { 
						$checked = ( get_option( $id, $array['Default'] ) ) ? 'checked' : '';
						?>
						<label class="" for="<?php esc_html_e( $id ); ?>">
							<input type="hidden" name="<?php esc_html_e( $id ); ?>" value="0"/>
							<input type="checkbox" id="<?php esc_html_e( $id ); ?>" name="<?php esc_html_e( $id ); ?>" class="" <?php esc_html_e( $checked ); ?> value="1"/>
							<span class="label">
								<?php 
								esc_html_e( $array['title'] );
								if ( isset( $array['tooltip'] ) ) {
									?>
									<span class="woocommerce-help-tip tipTip" title="<?php esc_html_e( $array['tooltip'] ); ?>"></span>
								<?php } ?>
							</span>								
						</label>	
						<?php																						
					} elseif ( 'checkbox_select' == $array['type'] ) { 
						$checked = ( get_option( $id, $array['Default'] ) ) ? 'checked' : '';
						?>
						<label class="" for="<?php esc_html_e( $id ); ?>">
							<input type="hidden" name="<?php esc_html_e( $id ); ?>" value="0"/>
							<input type="checkbox" id="<?php esc_html_e( $id ); ?>" name="<?php esc_html_e( $id ); ?>" class="" <?php esc_html_e( $checked ); ?> value="1"/>
							<span class="label">
								<?php 
								esc_html_e( $array['title'] );
								if ( !empty( $array['select'] ) ) {
									?>
									<select name="<?php esc_html_e( $array['select']['id'] ); ?>" style="width: auto;">
										<?php
										foreach ( $array['select']['options'] as $key => $val ) {
											$selected = ( get_option( $array['select']['id'], '' ) == $key ) ? 'selected' : '';
											?>
											<option value="<?php esc_html_e( $key ); ?>" <?php esc_html_e( $selected ); ?>><?php esc_html_e( $val ); ?></option>	
											<?php	
										}	
										?>
									</select>
								<?php
								}
								if ( isset( $array['tooltip'] ) ) {
									?>
									<span class="woocommerce-help-tip tipTip" title="<?php esc_html_e( $array['tooltip'] ); ?>"></span>
								<?php } ?>
							</span>								
						</label>	
						<?php																						
					} elseif ( 'multiple_checkbox' == $array['type'] ) { 
						$op = 1;	
						foreach ( ( array ) $array['options'] as $key => $val ) {
																
							$multi_checkbox_data = get_option( $id );
							if ( isset( $multi_checkbox_data[ $key ] ) && 1 == $multi_checkbox_data[ $key ] ) {
								$checked = 'checked';
							} else {
								$checked='';
							}
							?>
							
							<span class="multiple_checkbox">
								<label class="" for="<?php esc_html_e( $key ); ?>">
									<input type="hidden" name="<?php esc_html_e( $id ); ?>[<?php esc_html_e( $key ); ?>]" value="0"/>
									<input type="checkbox" id="<?php esc_html_e( $key ); ?>" name="<?php esc_html_e( $id ); ?>[<?php esc_html_e( $key ); ?>]" class="" <?php esc_html_e( $checked ); ?> value="1"/>
									<span class="multiple_label"><?php esc_html_e( $val ); ?></span>	
									</br>
								</label>																		
							</span>												
						<?php								
						}																
					} elseif ( 'textarea' == $array['type'] ) {
						$placeholder = ( !empty( $array['placeholder'] ) ) ? $array['placeholder'] : '';		
						?>
						
						<fieldset>
							<textarea placeholder="<?php esc_html_e( $placeholder ); ?>" class="input-text regular-input" name="<?php esc_html_e( $id ); ?>" id="<?php esc_html_e( $id ); ?>"><?php esc_html_e( get_option( $id, $array['Default'] ) ); ?></textarea>                                
						</fieldset>
						<span class="" style="font-size: 12px;"><?php esc_html_e( $array['desc_tip'] ); ?></span>
					<?php
					} elseif ( 'tag_block' == $array['type'] ) {
						?>
						<fieldset class="tag_block">
							<code>{customer_email_verification_code}</code><code>{cev_user_verification_link}</code><code>{cev_resend_email_link}</code><code>{cev_display_name}</code><code>{cev_user_login}</code><code>{cev_user_email}</code> 								
						</fieldset>
					<?php
					} elseif ( 'desc' == $array['type'] ) {
						?>
						<p class="section_desc"><?php esc_html_e( $array['title'] ); ?></p>
					<?php
					} else { 
						$placeholder = ( !empty( $array['placeholder'] ) ) ? $array['placeholder'] : '';
						?>
						<fieldset>
							<input class="input-text regular-input " type="text" name="<?php esc_html_e( $id ); ?>" id="<?php esc_html_e( $id ); ?>" style="" value="<?php esc_html_e( get_option( $id, $array['Default'] ) ); ?>" placeholder="<?php esc_html_e( $placeholder ); ?>">
						</fieldset>
					<?php } ?>
					</li>
					<?php
				}
			}
			?>
		</ul>		
	<?php 
	}
	
	/*
	* Get settings tab array data
	* return array
	*/
	public function get_cev_settings_data() {	
	
		$page_list = wp_list_pluck( get_pages(), 'post_title', 'ID' );
		global $wp_roles;
		$all_roles = $wp_roles->roles;
		$all_roles_array = array();
		foreach ( $all_roles as $key=>$role ) {
			if ( 'administrator' != $key ) {
				$role = array( $key => $role['name'] );
				$all_roles_array = array_merge($all_roles_array, $role);	
			}
		}	
		$form_data = array(			
			'cev_verification_success_message' => array(
				'type'		=> 'textarea',
				'title'		=> __( 'Email verification success message', 'customer-email-verification-for-woocommerce' ),				
				'show'		=> true,
				'tooltip'	=> __('the message that will appear on the top of the my-account or checkout page after successful email verification', 'customer-email-verification-for-woocommerce'),
				'Default'   => __('Your email is verified!', 'customer-email-verification-for-woocommerce' ),
				'id'        => '',
				'placeholder' => __('Your email is verified!', 'customer-email-verification-for-woocommerce' ),	
				'desc_tip'      => '',
				'class'     => 'top',
			),	
			'cev_skip_verification_for_selected_roles' => array(
				'type'		=> 'multiple_select',
				'title'		=> __( 'Skip email verification for the selected user roles:', 'customer-email-verification-for-woocommerce' ),
				'options'   => $all_roles_array,				
				'show' => true,
				'Default'   => '',				
				'class'     => 'top',
			),			
			// 'cev_enter_account_after_registration' => array(
			// 	'type'		=> 'checkbox',
			// 	'show' => true,
			// 	'tooltip' 		=> __('Allow your customers to access their account for the first time after registration before they verify the email address', 'customer-emial-verification-for-woocommerce'),
			// 	'title' => __( 'Allow first login after registration without email verification', 'customer-emial-verification-for-woocommerce' ),				
			// 	'Default'   => '',
			// 	'class'     => '',
			// ),
			// 'cev_email_for_verification' => array(
			// 	'type'		=> 'checkbox',
			// 	'show' => true,
			// 	'tooltip' 		=> __('if you select this option, the verification message, code and link will be added in New Account Emails. The separate email verification will be sent only when the customer (or admin) resend verification', 'customer-emial-verification-for-woocommerce'),
			// 	'title' => __( 'Verification code in new account email', 'customer-emial-verification-for-woocommerce' ),				
			// 	'Default'   => '',
			// 	'class'     => '',
			// ),
			// 'cev_redirect_page_after_varification' => array(
			// 	'type'		=> 'dropdown',
			// 	'title'		=> __( 'Page to redirect after successful verification', 'customer-email-verification-for-woocommerce' ),				
			// 	'class'		=> 'redirect_page border_class',
			// 	'show' => true,
			// 	'tooltip'	=> __('select a page to redirect users after successful verification. In case the email verification was during checkout, the user will be directed to checkout', 'customer-email-verification-for-woocommerce'),
			// 	'Default'   => get_option( 'woocommerce_myaccount_page_id' ),	
			// 	'options'   => $page_list, 				
			// ),							
		);
		$form_data = apply_filters( 'cev_general_settings_options', $form_data );
		return $form_data;
	}

	/*
	* Get settings tab array data
	* return array
	*/
	public function get_cev_settings_data_new() {
		
		global $wp_roles;
		$all_roles = $wp_roles->roles;
		$all_roles_array = array();
		
		foreach ( $all_roles as $key => $role ) {
			if ( 'administrator' != $key ) {
				$role = array( $key => $role['name'] );
				$all_roles_array = array_merge( $all_roles_array, $role );	
			}
		}
		
		$form_data_2 = array(		
			'cev_verification_success_message' => array(
				'type'		=> 'textarea',
				'title'		=> __( 'Email verification success message', 'customer-email-verification-for-woocommerce' ),				
				'show'		=> true,
				'tooltip'	=> __('the message that will appear on the top of the my-account or checkout page after successful email verification', 'customer-email-verification-for-woocommerce'),
				'Default'   => __('Your email is verified!', 'customer-email-verification-for-woocommerce' ),
				'id'        => '',
				'placeholder' => __('Your email is verified!', 'customer-email-verification-for-woocommerce' ),	
				'desc_tip'      => '',
				'class'     => 'top',
			),	
			'cev_skip_verification_for_selected_roles' => array(
				'type'		=> 'multiple_select',
				'title'		=> __( 'Skip email verification for the selected user roles:', 'customer-email-verification-for-woocommerce' ),
				'options'   => $all_roles_array,				
				'show' => true,
				'Default'   => '',				
				'class'     => 'top',
			),			
		);
		
		return $form_data_2;
	}

	/**
	 * AJAX save — strict allowlist.
	 *
	 * Only the FREE fields declared by `get_cev_free_field_keys()` are read
	 * from $_POST and persisted. PRO-locked field keys are ignored even if
	 * a hostile actor crafts a form submission with those names.
	 */
	public function cev_settings_form_update_fun() {
		if ( empty( $_POST ) ) {
			return;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Missing,WordPress.Security.NonceVerification.Recommended,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$cev_nonce = isset( $_POST['cev_settings_form_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['cev_settings_form_nonce'] ) ) : '';
		if ( ! wp_verify_nonce( $cev_nonce, 'cev_settings_form_nonce' ) ) {
			return;
		}

		foreach ( $this->get_cev_free_field_keys() as $cev_key => $cev_sanitizer ) {
			// phpcs:ignore WordPress.Security.NonceVerification.Missing,WordPress.Security.NonceVerification.Recommended
			if ( ! isset( $_POST[ $cev_key ] ) ) {
				continue;
			}
			// phpcs:ignore WordPress.Security.NonceVerification.Missing,WordPress.Security.NonceVerification.Recommended
			$cev_raw = wp_unslash( $_POST[ $cev_key ] );
			if ( 'textarea' === $cev_sanitizer ) {
				$cev_value = sanitize_textarea_field( $cev_raw );
			} elseif ( 'absint' === $cev_sanitizer ) {
				$cev_value = absint( $cev_raw );
			} else {
				$cev_value = sanitize_text_field( $cev_raw );
			}
			update_option( $cev_key, $cev_value );
		}
	}

	/**
	 * Settings — section registry.
	 *
	 * Source-order sidebar entries for the Settings tab. Each section's
	 * `pro_only` flag is computed dynamically from its field schema —
	 * if every (non-desc/separator) field carries `pro => true`, the
	 * sidebar item gets the PRO badge + lock icon but stays clickable.
	 *
	 * @return array
	 */
	public function get_cev_sections_list() {
		$sections = array(
			'signup_verification'   => array(
				'icon'  => 'user-plus',
				'label' => __( 'Signup Verification', 'customer-email-verification-for-woocommerce' ),
				'sub'   => __( 'Configure how customer emails are verified at registration.', 'customer-email-verification-for-woocommerce' ),
			),
			'checkout_verification' => array(
				'icon'  => 'shopping-cart',
				'label' => __( 'Checkout Verification', 'customer-email-verification-for-woocommerce' ),
				'sub'   => __( 'Email verification rules applied during checkout.', 'customer-email-verification-for-woocommerce' ),
			),
			'general'               => array(
				'icon'  => 'sliders',
				'label' => __( 'General', 'customer-email-verification-for-woocommerce' ),
				'sub'   => __( 'Verification code, expiry, resend limits, and customer messages.', 'customer-email-verification-for-woocommerce' ),
			),
			'login_auth'            => array(
				'icon'  => 'lock',
				'label' => __( 'Login Authentication', 'customer-email-verification-for-woocommerce' ),
				'sub'   => __( 'Require a one-time code at login for risky sign-ins.', 'customer-email-verification-for-woocommerce' ),
			),
			'two_factor'            => array(
				'icon'  => 'shield',
				'label' => __( 'Two-Factor Authentication', 'customer-email-verification-for-woocommerce' ),
				'sub'   => __( 'Let customers secure their account with an authenticator app.', 'customer-email-verification-for-woocommerce' ),
			),
			'spam_protection'       => array(
				'icon'  => 'shield-alert',
				'label' => __( 'Spam Protection', 'customer-email-verification-for-woocommerce' ),
				'sub'   => __( 'Block disposable emails, bad domains, and known abusers.', 'customer-email-verification-for-woocommerce' ),
			),
			'advanced'              => array(
				'icon'  => 'sliders-h',
				'label' => __( 'Advanced', 'customer-email-verification-for-woocommerce' ),
				'sub'   => __( 'Developer and data-handling options.', 'customer-email-verification-for-woocommerce' ),
			),
		);

		foreach ( $sections as $cev_key => $cev_meta ) {
			$cev_method = 'get_cev_' . $cev_key . '_settings_data';
			if ( method_exists( $this, $cev_method ) ) {
				$cev_fields                 = $this->$cev_method();
				$sections[ $cev_key ]['fields']   = $cev_fields;
				$sections[ $cev_key ]['pro_only'] = $this->section_is_pro_only( $cev_fields );
			} else {
				$sections[ $cev_key ]['fields']   = array();
				$sections[ $cev_key ]['pro_only'] = false;
			}
		}

		return $sections;
	}

	/**
	 * Free-field allowlist for the AJAX save handler.
	 *
	 * Keys are option names. Values pick the sanitizer:
	 *   'text'     → sanitize_text_field (default)
	 *   'textarea' → sanitize_textarea_field
	 *   'absint'   → absint
	 *
	 * @return array
	 */
	public function get_cev_free_field_keys() {
		return array(
			'cev_enable_email_verification'    => 'absint',
			'cev_verification_success_message' => 'textarea',
		);
	}

	/**
	 * Returns true when every non-decorative field in a section is PRO-locked.
	 *
	 * Desc + separator rows are skipped — they're informational decoration.
	 *
	 * @param array $fields
	 * @return bool
	 */
	public function section_is_pro_only( $fields ) {
		foreach ( (array) $fields as $cev_field ) {
			$cev_type = isset( $cev_field['type'] ) ? $cev_field['type'] : 'text';
			if ( 'desc' === $cev_type || 'separator' === $cev_type ) {
				continue;
			}
			if ( empty( $cev_field['pro'] ) ) {
				return false;
			}
		}
		return true;
	}

	/* =========================================================
	 * Section schemas — one method per sidebar section.
	 *
	 * Every field array supports:
	 *   type        → toogel | dropdown | textarea | checkbox_select |
	 *                  segmented | multiple_select | template_picker |
	 *                  toggle_select | sms_gateway | shortcode |
	 *                  category_messages | desc | separator | text
	 *   title       → translatable label
	 *   description → translatable one-line hint shown under the label
	 *   tooltip     → translatable hover text on the ? icon
	 *   pro         → bool — when true the control slot renders the
	 *                  cev-pro-tag (badge + lock) and no real input
	 *   Default     → default value (FREE fields only)
	 *   options     → key/label pairs (dropdown / multiple_select)
	 * =========================================================
	 */

	public function get_cev_signup_verification_settings_data() {
		return array(
			'cev_enable_email_verification' => array(
				'type'        => 'toogel',
				'title'       => __( 'Enable Signup Verification', 'customer-email-verification-for-woocommerce' ),
				'description' => __( 'Require new users to confirm their email during signup.', 'customer-email-verification-for-woocommerce' ),
				'tooltip'     => __( 'Master switch — when on, every new account must verify the email address before the signup completes.', 'customer-email-verification-for-woocommerce' ),
				'name'        => 'cev_enable_email_verification',
				'id'          => 'cev_enable_email_verification',
				'Default'     => 1,
			),
			'cev_signup_form_type' => array(
				'type'        => 'segmented',
				'title'       => __( 'Signup form type', 'customer-email-verification-for-woocommerce' ),
				'description' => __( 'Choose the standard WooCommerce form or a modern, ready-made Smart Form.', 'customer-email-verification-for-woocommerce' ),
				'tooltip'     => __( 'WooCommerce Form keeps the default registration/login form. Smart Form swaps in a modern, branded form with built-in email/phone verification.', 'customer-email-verification-for-woocommerce' ),
				'pro'         => true,
			),
			'cev_smart_placement' => array(
				'type'        => 'shortcode',
				'title'       => __( 'Add the Smart Form to any page', 'customer-email-verification-for-woocommerce' ),
				'description' => __( 'Paste a shortcode where you want the form to appear.', 'customer-email-verification-for-woocommerce' ),
				'tooltip'     => __( 'Use this shortcode on any page or post to display the Smart Form.', 'customer-email-verification-for-woocommerce' ),
				'pro'         => true,
				'hidden'      => true,
			),
			'cev_smart_takeover_myaccount' => array(
				'type'        => 'toogel',
				'title'       => __( 'Replace the My Account page form with the Smart Form', 'customer-email-verification-for-woocommerce' ),
				'description' => __( 'When on, the Smart Form takes over the My Account login/registration page.', 'customer-email-verification-for-woocommerce' ),
				'tooltip'     => __( 'Replaces the default WooCommerce My Account form with the branded Smart Form.', 'customer-email-verification-for-woocommerce' ),
				'pro'         => true,
				'hidden'      => true,
			),
			'cev_signup_smart_template' => array(
				'type'        => 'template_picker',
				'title'       => __( 'Template', 'customer-email-verification-for-woocommerce' ),
				'description' => __( 'Pick one of the four ready-made Smart Form templates.', 'customer-email-verification-for-woocommerce' ),
				'tooltip'     => __( 'Choose a layout style for the Smart Form. Each template is fully customizable.', 'customer-email-verification-for-woocommerce' ),
				'pro'         => true,
				'hidden'      => true,
			),
			'cev_smart_reg_method' => array(
				'type'        => 'toggle_select',
				'title'       => __( 'Registration verification method', 'customer-email-verification-for-woocommerce' ),
				'description' => __( 'Verify new customers by email or by phone (SMS).', 'customer-email-verification-for-woocommerce' ),
				'tooltip'     => __( 'How a new customer proves they own their contact detail at sign-up.', 'customer-email-verification-for-woocommerce' ),
				'pro'         => true,
				'hidden'      => true,
			),
			'cev_smart_show_phone_field' => array(
				'type'        => 'toogel',
				'title'       => __( 'Show phone number field', 'customer-email-verification-for-woocommerce' ),
				'description' => __( 'Display a phone number field on the registration form.', 'customer-email-verification-for-woocommerce' ),
				'tooltip'     => __( 'Adds a phone number input to the Smart Form registration step.', 'customer-email-verification-for-woocommerce' ),
				'pro'         => true,
				'hidden'      => true,
			),
			'cev_smart_phone_required' => array(
				'type'        => 'toogel',
				'title'       => __( 'Require the phone number', 'customer-email-verification-for-woocommerce' ),
				'description' => __( 'Make the phone number mandatory on the registration form.', 'customer-email-verification-for-woocommerce' ),
				'tooltip'     => __( 'When on, customers cannot complete registration without entering a phone number.', 'customer-email-verification-for-woocommerce' ),
				'pro'         => true,
				'hidden'      => true,
			),
			'cev_smart_login_method' => array(
				'type'        => 'toggle_select',
				'title'       => __( 'Login method', 'customer-email-verification-for-woocommerce' ),
				'description' => __( 'Password, email OTP, or phone OTP for returning customers.', 'customer-email-verification-for-woocommerce' ),
				'tooltip'     => __( 'How returning customers sign in on the Smart Form.', 'customer-email-verification-for-woocommerce' ),
				'pro'         => true,
				'hidden'      => true,
			),
			'cev_smart_sms_gateway' => array(
				'type'        => 'sms_gateway',
				'title'       => __( 'SMS gateway', 'customer-email-verification-for-woocommerce' ),
				'description' => __( 'Configure MSG91 / Twilio / Vonage / WhatsApp for SMS delivery.', 'customer-email-verification-for-woocommerce' ),
				'tooltip'     => __( 'Pick a provider and enter API credentials for sending OTP SMS messages.', 'customer-email-verification-for-woocommerce' ),
				'pro'         => true,
				'hidden'      => true,
			),
		);
	}

	public function get_cev_checkout_verification_settings_data() {
		return array(
			'cev_enable_email_verification_checkout' => array(
				'type'        => 'toogel',
				'title'       => __( 'Enable Checkout Verification', 'customer-email-verification-for-woocommerce' ),
				'description' => __( 'Customers will need to confirm their email to place an order.', 'customer-email-verification-for-woocommerce' ),
				'tooltip'     => __( 'Master switch for checkout verification — when on, customers must confirm their email with an OTP before the order can be placed.', 'customer-email-verification-for-woocommerce' ),
				'pro'         => true,
			),
			'cev_verification_checkout_dropdown_option' => array(
				'type'        => 'dropdown',
				'title'       => __( 'Checkout Verification Type', 'customer-email-verification-for-woocommerce' ),
				'description' => __( 'Choose between a popup overlay or an inline form element.', 'customer-email-verification-for-woocommerce' ),
				'tooltip'     => __( 'Popup blocks the page until verified; inline embeds the OTP step in the checkout form itself.', 'customer-email-verification-for-woocommerce' ),
				'pro'         => true,
			),
			'cev_create_an_account_during_checkout' => array(
				'type'        => 'toogel',
				'title'       => __( 'Require email verification only when Create an account during checkout is selected', 'customer-email-verification-for-woocommerce' ),
				'description' => __( 'Guest checkouts will bypass email verification.', 'customer-email-verification-for-woocommerce' ),
				'tooltip'     => __( 'Only ask for the OTP when the customer ticks "Create an account" — guest orders skip verification.', 'customer-email-verification-for-woocommerce' ),
				'pro'         => true,
				'hidden'      => true,
			),
			'cev_enable_email_verification_cart_page' => array(
				'type'        => 'toogel',
				'title'       => __( 'Enable the email verification on the cart page', 'customer-email-verification-for-woocommerce' ),
				'description' => __( 'Customers can verify their email before proceeding to checkout.', 'customer-email-verification-for-woocommerce' ),
				'tooltip'     => __( 'Adds the OTP step to the cart page so the customer is already verified by the time they reach checkout.', 'customer-email-verification-for-woocommerce' ),
				'pro'         => true,
			),
			'cev_enable_email_verification_free_orders' => array(
				'type'        => 'toogel',
				'title'       => __( 'Require checkout verification only for free orders', 'customer-email-verification-for-woocommerce' ),
				'description' => __( 'Ensure free orders are tied to valid email addresses.', 'customer-email-verification-for-woocommerce' ),
				'tooltip'     => __( 'Only show the OTP step on $0 / 100%-discount orders — paid orders skip verification.', 'customer-email-verification-for-woocommerce' ),
				'pro'         => true,
				'hidden'      => true,
			),
			'cev_enable_cart_threshold_verification' => array(
				'type'        => 'toogel',
				'title'       => __( 'Require verification only above a cart amount', 'customer-email-verification-for-woocommerce' ),
				'description' => __( 'High-value orders can require extra verification.', 'customer-email-verification-for-woocommerce' ),
				'tooltip'     => __( 'Only ask for the OTP when the cart total is above the threshold you set below — useful for high-fraud-risk orders.', 'customer-email-verification-for-woocommerce' ),
				'pro'         => true,
				'hidden'      => true,
			),
			'cev_cart_verification_threshold_amount' => array(
				'type'        => 'text',
				'title'       => __( 'Cart amount threshold', 'customer-email-verification-for-woocommerce' ),
				'description' => __( 'Require OTP verification only when the cart total exceeds this amount.', 'customer-email-verification-for-woocommerce' ),
				'tooltip'     => __( 'The cart total above which the OTP step is shown. Enter a plain number in your store currency.', 'customer-email-verification-for-woocommerce' ),
				'pro'         => true,
				'hidden'      => true,
			),
			'cev_cart_verification_threshold_metric' => array(
				'type'        => 'dropdown',
				'title'       => __( 'Compare against', 'customer-email-verification-for-woocommerce' ),
				'description' => __( 'Subtotal excludes shipping and taxes; total includes them.', 'customer-email-verification-for-woocommerce' ),
				'tooltip'     => __( 'Pick which cart value the threshold is compared against — subtotal (items only) or total (with shipping and taxes).', 'customer-email-verification-for-woocommerce' ),
				'pro'         => true,
				'hidden'      => true,
			),
			'cev_enable_conditional_verification_by_category' => array(
				'type'        => 'toogel',
				'title'       => __( 'Require verification only for flagged product categories', 'customer-email-verification-for-woocommerce' ),
				'description' => __( 'Useful for digital goods, gift cards, or age-restricted items.', 'customer-email-verification-for-woocommerce' ),
				'tooltip'     => __( 'Only ask for the OTP when the cart contains products from the categories you flag below.', 'customer-email-verification-for-woocommerce' ),
				'pro'         => true,
			),
			'cev_required_verification_categories' => array(
				'type'        => 'multiple_select',
				'title'       => __( 'Categories that require verification', 'customer-email-verification-for-woocommerce' ),
				'description' => __( 'Only require OTP verification when the cart contains items from these categories.', 'customer-email-verification-for-woocommerce' ),
				'tooltip'     => __( 'Pick one or more product categories — any cart that contains an item from these will trigger the OTP step.', 'customer-email-verification-for-woocommerce' ),
				'pro'         => true,
				'hidden'      => true,
			),
			'cev_conditional_category_message' => array(
				'type'        => 'category_messages',
				'title'       => __( 'Per-category verification message (optional)', 'customer-email-verification-for-woocommerce' ),
				'description' => __( 'Show a custom OTP-required message per flagged category.', 'customer-email-verification-for-woocommerce' ),
				'tooltip'     => __( 'Optionally customise the message shown above the OTP field per category — e.g. "Age-restricted item: please verify your email".', 'customer-email-verification-for-woocommerce' ),
				'pro'         => true,
				'hidden'      => true,
			),
			'cev_disable_wooCommerce_store_api' => array(
				'type'        => 'toogel',
				'title'       => __( 'Disable WooCommerce Store API Checkout', 'customer-email-verification-for-woocommerce' ),
				'description' => __( 'Use if you rely entirely on the classic shortcode checkout.', 'customer-email-verification-for-woocommerce' ),
				'tooltip'     => __( 'Turn off the block-based checkout REST API so only the classic shortcode checkout is used — needed when the block checkout cannot enforce the OTP.', 'customer-email-verification-for-woocommerce' ),
				'pro'         => true,
			),
		);
	}

	public function get_cev_general_settings_data() {
		return array(
			'cev_verification_code_length' => array(
				'type'        => 'dropdown',
				'title'       => __( 'OTP Length', 'customer-email-verification-for-woocommerce' ),
				'description' => __( 'Number of digits in the one-time code sent to customers.', 'customer-email-verification-for-woocommerce' ),
				'tooltip'     => __( 'Number of digits in the one-time code sent to customers — 4 is quicker to type, 6 is harder to guess.', 'customer-email-verification-for-woocommerce' ),
				'pro'         => true,
			),
			'cev_verification_code_expiration' => array(
				'type'        => 'dropdown',
				'title'       => __( 'OTP Expiration', 'customer-email-verification-for-woocommerce' ),
				'description' => __( 'How long the one-time code stays valid before it expires.', 'customer-email-verification-for-woocommerce' ),
				'tooltip'     => __( 'Choose if you wish to set an expiry time on the OTP. After this period, the code is invalid and the customer must request a fresh one.', 'customer-email-verification-for-woocommerce' ),
				'pro'         => true,
			),
			'cev_redirect_limit_resend' => array(
				'type'        => 'dropdown',
				'title'       => __( 'Verification Email Resend Limit', 'customer-email-verification-for-woocommerce' ),
				'description' => __( 'How many times a customer can request a fresh code before resending is blocked.', 'customer-email-verification-for-woocommerce' ),
				'tooltip'     => __( 'How many times a customer can request a fresh code before resending is blocked — protects against email/SMS flooding.', 'customer-email-verification-for-woocommerce' ),
				'pro'         => true,
			),
			'cev_resend_limit_message' => array(
				'type'        => 'textarea',
				'title'       => __( 'Resend Limit Message', 'customer-email-verification-for-woocommerce' ),
				'description' => __( 'Message shown when the resend limit is reached.', 'customer-email-verification-for-woocommerce' ),
				'tooltip'     => __( 'Message displayed to the customer once they hit the resend limit and can no longer request a fresh OTP.', 'customer-email-verification-for-woocommerce' ),
				'pro'         => true,
			),
			'cev_verification_success_message' => array(
				'type'        => 'textarea',
				'title'       => __( 'Email Verification Success Message', 'customer-email-verification-for-woocommerce' ),
				'description' => __( 'The message that will appear after successful email verification on My Account or checkout.', 'customer-email-verification-for-woocommerce' ),
				'tooltip'     => __( 'Message shown on the My Account or checkout page right after a customer successfully verifies their email.', 'customer-email-verification-for-woocommerce' ),
				'name'        => 'cev_verification_success_message',
				'id'          => 'cev_verification_success_message',
				'Default'     => __( 'Your email is verified!', 'customer-email-verification-for-woocommerce' ),
			),
		);
	}

	public function get_cev_login_auth_settings_data() {
		return array(
			'cev_enable_login_authentication' => array(
				'type'        => 'toogel',
				'title'       => __( 'Enable Login Authentication', 'customer-email-verification-for-woocommerce' ),
				'description' => __( 'Master switch — when on, returning customers may be asked for a one-time code at login.', 'customer-email-verification-for-woocommerce' ),
				'tooltip'     => __( 'Master switch for the login OTP flow — when off, none of the conditions below are evaluated.', 'customer-email-verification-for-woocommerce' ),
				'pro'         => true,
			),
			'enable_email_otp_for_account' => array(
				'type'        => 'toogel',
				'title'       => __( 'Require OTP verification for unrecognized login', 'customer-email-verification-for-woocommerce' ),
				'description' => __( 'Ask for a one-time code only when a sign-in looks unfamiliar.', 'customer-email-verification-for-woocommerce' ),
				'tooltip'     => __( 'Only prompt for the OTP when the sign-in matches one of the "unrecognized" conditions below — known browsers/devices skip it.', 'customer-email-verification-for-woocommerce' ),
				'pro'         => true,
			),
			'login_auth_desc' => array(
				'type'  => 'desc',
				'title' => __( 'Unrecognized Login Conditions:', 'customer-email-verification-for-woocommerce' ),
			),
			'enable_email_auth_for_new_device' => array(
				'type'        => 'toogel',
				'title'       => __( 'Login from a new device', 'customer-email-verification-for-woocommerce' ),
				'description' => __( 'Trigger a one-time code when the customer signs in from a browser or device not seen before.', 'customer-email-verification-for-woocommerce' ),
				'tooltip'     => __( 'Trigger an OTP when the customer signs in from a browser fingerprint we have not seen before for this account.', 'customer-email-verification-for-woocommerce' ),
				'pro'         => true,
			),
			'enable_email_auth_for_new_location' => array(
				'type'        => 'toogel',
				'title'       => __( 'Login from a new location', 'customer-email-verification-for-woocommerce' ),
				'description' => __( 'Trigger a one-time code when the customer signs in from a new IP address or location.', 'customer-email-verification-for-woocommerce' ),
				'tooltip'     => __( 'Trigger an OTP when the IP geolocation differs from previously trusted ones for this account.', 'customer-email-verification-for-woocommerce' ),
				'pro'         => true,
			),
			'enable_email_auth_for_login_time' => array(
				'type'        => 'checkbox_select',
				'title'       => __( 'Last login more than', 'customer-email-verification-for-woocommerce' ),
				'description' => __( 'Trigger a one-time code when the customer has not logged in for longer than the selected period.', 'customer-email-verification-for-woocommerce' ),
				'tooltip'     => __( 'Treat the login as unrecognized when the customer has not signed in for longer than the period you pick.', 'customer-email-verification-for-woocommerce' ),
				'pro'         => true,
			),
			'cev_require_verify_unverified_login' => array(
				'type'        => 'toogel',
				'title'       => __( 'Require unverified logged-in customers to verify', 'customer-email-verification-for-woocommerce' ),
				'description' => __( 'A logged-in customer whose email is not verified is shown a verification popup until they confirm. Administrators, editors and shop managers are never prompted.', 'customer-email-verification-for-woocommerce' ),
				'tooltip'     => __( 'Block access to My Account for already-logged-in customers whose email is still unverified until they complete the OTP. Admins/editors/shop managers are exempt.', 'customer-email-verification-for-woocommerce' ),
				'pro'         => true,
			),
		);
	}

	public function get_cev_two_factor_settings_data() {
		return array(
			'cev_2fa_intro' => array(
				'type'  => 'desc',
				'title' => __( 'Let users add an extra layer of security with an Authenticator App.', 'customer-email-verification-for-woocommerce' ),
			),
			'cev_enable_2fa' => array(
				'type'        => 'toogel',
				'title'       => __( 'Enable Authenticator App 2FA', 'customer-email-verification-for-woocommerce' ),
				'description' => __( 'Offer Google Authenticator / Authy / 1Password setup on the customer account page. Setup is optional — no user is forced to enrol or blocked from logging in.', 'customer-email-verification-for-woocommerce' ),
				'tooltip'     => __( 'Master switch — shows the Authenticator-App setup card on the My Account page. Enrolment is voluntary; no user is locked out.', 'customer-email-verification-for-woocommerce' ),
				'pro'         => true,
			),
			'cev_login_auth_enforcement' => array(
				'type'        => 'dropdown',
				'title'       => __( 'Show 2FA option to', 'customer-email-verification-for-woocommerce' ),
				'description' => __( 'Pick which users see the Authenticator-App setup card on their account page.', 'customer-email-verification-for-woocommerce' ),
				'tooltip'     => __( 'Controls who is offered Authenticator-App setup — all customers, only selected roles, or only admins.', 'customer-email-verification-for-woocommerce' ),
				'pro'         => true,
			),
			'cev_login_auth_roles' => array(
				'type'        => 'multiple_select',
				'title'       => __( 'Roles', 'customer-email-verification-for-woocommerce' ),
				'description' => __( 'The roles the policy above applies to.', 'customer-email-verification-for-woocommerce' ),
				'tooltip'     => __( 'Only users with these roles will see the Authenticator-App setup card.', 'customer-email-verification-for-woocommerce' ),
				'pro'         => true,
				'hidden'      => true,
			),
			'cev_2fa_grace_days' => array(
				'type'        => 'dropdown',
				'title'       => __( 'Remember this device for', 'customer-email-verification-for-woocommerce' ),
				'description' => __( 'After a successful 2FA verification, skip the 6-digit code prompt on the same browser/device for this many days.', 'customer-email-verification-for-woocommerce' ),
				'tooltip'     => __( 'After a successful 2FA, the same browser/device is trusted for this many days — the customer is not asked for the 6-digit code again during that window.', 'customer-email-verification-for-woocommerce' ),
				'pro'         => true,
			),
		);
	}

	public function get_cev_spam_protection_settings_data() {
		return array(
			'cev_block_disposable_emails' => array(
				'type'        => 'toogel',
				'title'       => __( 'Block disposable / temporary emails', 'customer-email-verification-for-woocommerce' ),
				'description' => __( 'Reject signups and guest checkouts using known temporary email services (mailinator, 10minutemail, tempmail, etc.).', 'customer-email-verification-for-woocommerce' ),
				'tooltip'     => __( 'Reject signups and checkouts from known throw-away email services like mailinator, 10minutemail and tempmail.', 'customer-email-verification-for-woocommerce' ),
				'pro'         => true,
			),
			'cev_disposable_built_in_desc' => array(
				'type'   => 'desc',
				'title'  => __( 'Built-in list contains ~3,000 known disposable email domains.', 'customer-email-verification-for-woocommerce' ),
				'hidden' => true,
			),
			'cev_disposable_custom_domains' => array(
				'type'        => 'textarea',
				'title'       => __( 'Additional blocked domains', 'customer-email-verification-for-woocommerce' ),
				'description' => __( 'One domain per line. Wildcard supported: "*.ru" blocks every .ru domain.', 'customer-email-verification-for-woocommerce' ),
				'tooltip'     => __( 'Add extra domains to block on top of the built-in disposable list. Wildcards like "*.ru" are allowed.', 'customer-email-verification-for-woocommerce' ),
				'pro'         => true,
				'hidden'      => true,
			),
			'cev_disposable_excluded_domains' => array(
				'type'        => 'textarea',
				'title'       => __( 'Excluded domains (always allowed)', 'customer-email-verification-for-woocommerce' ),
				'description' => __( 'One domain per line. Always allowed, even if on the blocked list.', 'customer-email-verification-for-woocommerce' ),
				'tooltip'     => __( 'Whitelist — domains here are always allowed through, even if they match the built-in or custom blocklist.', 'customer-email-verification-for-woocommerce' ),
				'pro'         => true,
				'hidden'      => true,
			),
			'cev_disposable_block_message' => array(
				'type'        => 'textarea',
				'title'       => __( 'Disposable rejection message', 'customer-email-verification-for-woocommerce' ),
				'description' => __( 'Message shown to the customer when their email is blocked as disposable.', 'customer-email-verification-for-woocommerce' ),
				'tooltip'     => __( 'Error text shown on the signup/checkout form when the email is rejected as a disposable address.', 'customer-email-verification-for-woocommerce' ),
				'pro'         => true,
				'hidden'      => true,
			),
			'cev_validate_mx' => array(
				'type'        => 'toogel',
				'title'       => __( 'Validate email domain (MX check)', 'customer-email-verification-for-woocommerce' ),
				'description' => __( "Confirm the email's domain has a mail server before generating an OTP. Catches typos like gmial.com and dead inboxes.", 'customer-email-verification-for-woocommerce' ),
				'tooltip'     => __( 'Do a DNS MX lookup before sending the OTP — rejects typos like gmial.com and dead domains where the OTP would never arrive.', 'customer-email-verification-for-woocommerce' ),
				'pro'         => true,
			),
			'cev_mx_fail_message' => array(
				'type'        => 'textarea',
				'title'       => __( 'MX rejection message', 'customer-email-verification-for-woocommerce' ),
				'description' => __( "Message shown when the email's domain has no mail server.", 'customer-email-verification-for-woocommerce' ),
				'tooltip'     => __( "Error text shown on the signup/checkout form when the MX check fails for the customer's email domain.", 'customer-email-verification-for-woocommerce' ),
				'pro'         => true,
				'hidden'      => true,
			),
			'cev_enable_b2b_allowlist' => array(
				'type'        => 'toogel',
				'title'       => __( 'Restrict signups to specific domains (B2B mode)', 'customer-email-verification-for-woocommerce' ),
				'description' => __( 'When enabled with a non-empty Allowlist, ONLY the listed domains can sign up. Perfect for B2B / wholesale stores.', 'customer-email-verification-for-woocommerce' ),
				'tooltip'     => __( 'Turn the signup into a whitelist: only emails whose domain is in the Allowlist below can register — perfect for B2B / wholesale stores.', 'customer-email-verification-for-woocommerce' ),
				'pro'         => true,
			),
			'cev_domain_allowlist' => array(
				'type'        => 'textarea',
				'title'       => __( 'Allowed domains (only these can sign up)', 'customer-email-verification-for-woocommerce' ),
				'description' => __( 'One domain per line. Wildcard supported: "*.acme.com" matches subdomains.', 'customer-email-verification-for-woocommerce' ),
				'tooltip'     => __( 'Only these domains can sign up. One per line; wildcards like "*.acme.com" match every subdomain.', 'customer-email-verification-for-woocommerce' ),
				'pro'         => true,
				'hidden'      => true,
			),
			'cev_domain_allowlist_message' => array(
				'type'        => 'textarea',
				'title'       => __( 'Allowlist rejection message', 'customer-email-verification-for-woocommerce' ),
				'description' => __( "Message shown when a customer's email domain is not in the Allowlist.", 'customer-email-verification-for-woocommerce' ),
				'tooltip'     => __( "Error text shown on the signup/checkout form when the email's domain is not in the Allowlist.", 'customer-email-verification-for-woocommerce' ),
				'pro'         => true,
				'hidden'      => true,
			),
			'cev_enable_blocked_emails' => array(
				'type'        => 'toogel',
				'title'       => __( 'Block specific email addresses', 'customer-email-verification-for-woocommerce' ),
				'description' => __( 'Personal ban list. Useful for scrapers, abusers, or banned customers who keep re-signing up.', 'customer-email-verification-for-woocommerce' ),
				'tooltip'     => __( 'Maintain a personal ban list of full email addresses — useful for blocking scrapers, abusers, or banned customers.', 'customer-email-verification-for-woocommerce' ),
				'pro'         => true,
			),
			'cev_blocked_emails' => array(
				'type'        => 'textarea',
				'title'       => __( 'Blocked email addresses', 'customer-email-verification-for-woocommerce' ),
				'description' => __( 'One full email address per line. Exact match only.', 'customer-email-verification-for-woocommerce' ),
				'tooltip'     => __( 'One full email address per line — exact match only, no wildcards.', 'customer-email-verification-for-woocommerce' ),
				'pro'         => true,
				'hidden'      => true,
			),
			'cev_blocked_emails_message' => array(
				'type'        => 'textarea',
				'title'       => __( 'Blocked email rejection message', 'customer-email-verification-for-woocommerce' ),
				'description' => __( "Message shown when a customer's email is on the personal ban list.", 'customer-email-verification-for-woocommerce' ),
				'tooltip'     => __( 'Error text shown on the signup/checkout form when the email matches one of the addresses in your ban list.', 'customer-email-verification-for-woocommerce' ),
				'pro'         => true,
				'hidden'      => true,
			),
		);
	}

	public function get_cev_advanced_settings_data() {
		return array(
			'cev_enable_paid_order_gatekeeping' => array(
				'type'        => 'toogel',
				'title'       => __( 'Require a paid order to finish verification', 'customer-email-verification-for-woocommerce' ),
				'description' => __( 'A verified customer is kept in a "pending paid order" state — counted as verified for login and checkout, but not fully activated — until their first genuinely paid order completes.', 'customer-email-verification-for-woocommerce' ),
				'tooltip'     => __( 'Customers are only marked fully active after their first genuinely paid order completes — verification alone is not enough. Useful for stopping coupon/freebie abuse.', 'customer-email-verification-for-woocommerce' ),
				'pro'         => true,
			),
		);
	}

	/**
	 * Maintenance: auto-cleanup of stale verification log entries.
	 * Non-destructive — only deletes PENDING rows older than the retention
	 * window. Verified rows stay forever.
	 *
	 * @return array
	 */
	public function get_cev_maintenance_settings_data() {
		return array(
			'cev_auto_cleanup_enabled' => array(
				'type'        => 'toogel',
				'title'       => __( 'Enable daily auto-cleanup', 'customer-email-verification-for-woocommerce' ),
				'description' => __( 'Runs once per day in the background. Only PENDING records are deleted — verified records stay forever as proof of verification.', 'customer-email-verification-for-woocommerce' ),
				'tooltip'     => __( 'Automatically delete pending (unverified) records that have been sitting in the verification log past the retention window below.', 'customer-email-verification-for-woocommerce' ),
				'pro'         => true,
			),
			'cev_auto_cleanup_pending_days' => array(
				'type'        => 'dropdown',
				'title'       => __( 'Delete pending records after', 'customer-email-verification-for-woocommerce' ),
				'description' => __( 'Pending records older than this are deleted on the next daily cleanup run.', 'customer-email-verification-for-woocommerce' ),
				'tooltip'     => __( 'Pending records older than this are deleted on the next daily cleanup run. 14 days is the recommended balance between giving real customers time to come back and not letting bots pile up.', 'customer-email-verification-for-woocommerce' ),
				'pro'         => true,
			),
		);
	}

	/**
	 * Maintenance: auto-delete unverified WP user accounts.
	 * Destructive — deletes actual users. Always-on safeguards skip
	 * verified users, users with orders/subscriptions, elevated roles,
	 * and recently-active users.
	 *
	 * @return array
	 */
	public function get_cev_user_cleanup_settings_data() {
		return array(
			'cev_auto_delete_unverified_users' => array(
				'type'        => 'toogel',
				'title'       => __( 'Enable auto-delete of unverified accounts', 'customer-email-verification-for-woocommerce' ),
				'description' => __( 'Skip rules (always applied): verified users, users with any orders, active subscriptions, non-customer roles, or login activity in the last 30 days are never deleted.', 'customer-email-verification-for-woocommerce' ),
				'tooltip'     => __( 'Permanently deletes WP user accounts that never verified their email. DESTRUCTIVE. Built-in safeguards keep verified users, users with orders, active subscriptions, elevated roles, or recent login activity safe.', 'customer-email-verification-for-woocommerce' ),
				'pro'         => true,
			),
			'cev_auto_delete_unverified_users_days' => array(
				'type'        => 'dropdown',
				'title'       => __( 'Delete accounts after', 'customer-email-verification-for-woocommerce' ),
				'description' => __( 'Account-age threshold for deletion. Minimum is 30 days (enforced server-side).', 'customer-email-verification-for-woocommerce' ),
				'tooltip'     => __( 'Account-age threshold for deletion. Minimum 30 days is enforced server-side. 90 days is the recommended balance.', 'customer-email-verification-for-woocommerce' ),
				'pro'         => true,
			),
		);
	}

	/**
	 * Maintenance: re-engagement reminder emails to lapsed unverified
	 * signups, at the intervals chosen below.
	 *
	 * @return array
	 */
	public function get_cev_reengagement_settings_data() {
		return array(
			'cev_send_reengagement_emails' => array(
				'type'        => 'toogel',
				'title'       => __( 'Send reminder emails', 'customer-email-verification-for-woocommerce' ),
				'description' => __( 'A reminder is sent at most once per interval per user, capped at 50 sends per cron run. Customize the email subject, heading, body and CTA via the email customizer.', 'customer-email-verification-for-woocommerce' ),
				'tooltip'     => __( 'Email registered users who never finished verifying — at the chosen intervals after sign-up — to bring them back. The email content is fully customizable through the email customizer.', 'customer-email-verification-for-woocommerce' ),
				'pro'         => true,
			),
			'cev_reengagement_intervals' => array(
				'type'        => 'multiple_select',
				'title'       => __( 'Send reminder at', 'customer-email-verification-for-woocommerce' ),
				'description' => __( 'Each user gets at most one email per interval, ever.', 'customer-email-verification-for-woocommerce' ),
				'tooltip'     => __( 'Pick whatever cadence matches your brand voice — each customer is emailed at most once per interval.', 'customer-email-verification-for-woocommerce' ),
				'pro'         => true,
			),
		);
	}

	/* =========================================================
	 * Renderers
	 * ========================================================= */

	/**
	 * Render every field in a section.
	 *
	 * @param array $fields
	 * @return void
	 */
	public function render_settings_fields( $fields ) {
		foreach ( (array) $fields as $cev_key => $cev_field ) {
			$this->render_field_by_type( $cev_key, $cev_field );
		}
	}

	/**
	 * Render a single field row.
	 *
	 * - `desc` rows render as `.cev-row__subheader` (no card row, no control).
	 * - `separator` rows render as `.zui-row-divider`.
	 * - PRO-locked rows render `.zui-row--inline` with the `cev-pro-tag`
	 *   cluster in the control slot (no real input).
	 * - FREE rows render the real control via a small per-type switch.
	 *
	 * @param string $key
	 * @param array  $field
	 * @return void
	 */
	public function render_field_by_type( $key, $field ) {
		// Fields flagged `hidden => true` are conditional children that Pro
		// hides until their parent toggle is enabled. The Free build can
		// never enable the parent (it's PRO-locked), so the child stays
		// hidden — same visual default as a fresh Pro install with the
		// parent off.
		if ( ! empty( $field['hidden'] ) ) {
			return;
		}

		$cev_type        = isset( $field['type'] ) ? $field['type'] : 'text';
		$cev_title       = isset( $field['title'] ) ? $field['title'] : '';
		$cev_description = isset( $field['description'] ) ? $field['description'] : '';
		$cev_tooltip     = isset( $field['tooltip'] ) ? $field['tooltip'] : $cev_description;
		$cev_is_pro      = ! empty( $field['pro'] );

		if ( 'separator' === $cev_type ) {
			echo '<div class="zui-row-divider"></div>';
			return;
		}

		if ( 'desc' === $cev_type ) {
			echo '<div class="zui-row cev-row--subheader">';
			echo '<p class="cev-row__subheader">' . esc_html( $cev_title ) . '</p>';
			if ( '' !== $cev_description ) {
				echo '<p class="zui-row__desc">' . esc_html( $cev_description ) . '</p>';
			}
			echo '</div>';
			return;
		}

		?>
		<div class="zui-row zui-row--inline<?php echo $cev_is_pro ? ' cev-pro-locked-field' : ''; ?>">
			<div class="zui-row__head">
				<span class="zui-row__label">
					<?php echo esc_html( $cev_title ); ?>
					<?php if ( '' !== $cev_tooltip ) : ?>
						<span class="zui-tooltip" tabindex="0" role="img" aria-label="<?php echo esc_attr( $cev_tooltip ); ?>"><svg class="zui-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg><span class="zui-tooltip__bubble"><?php echo esc_html( $cev_tooltip ); ?><span class="zui-tooltip__arrow"></span></span></span>
					<?php endif; ?>
				</span>
				<?php if ( '' !== $cev_description ) : ?>
					<p class="zui-row__desc"><?php echo esc_html( $cev_description ); ?></p>
				<?php endif; ?>
			</div>
			<div class="zui-row__control">
				<?php
				if ( $cev_is_pro ) {
					$this->render_pro_locked_control();
				} else {
					$this->render_free_control( $key, $field );
				}
				?>
			</div>
		</div>
		<?php
	}

	/**
	 * Output the PRO badge + lock cluster used in every locked control slot.
	 *
	 * @return void
	 */
	public function render_pro_locked_control() {
		?>
		<span class="cev-pro-tag">
			<span class="cev-pro-badge"><?php esc_html_e( 'PRO', 'customer-email-verification-for-woocommerce' ); ?></span>
			<span class="cev-pro-lock-icon"><svg class="cev-pro-lock-svg" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg></span>
		</span>
		<?php
	}

	/**
	 * Render the actual control for a FREE field. Only the three types we
	 * actually use in the Free build (toogel, dropdown, textarea) are
	 * supported here — any other type falls through to a plain text input.
	 *
	 * @param string $key
	 * @param array  $field
	 * @return void
	 */
	public function render_free_control( $key, $field ) {
		$cev_type    = isset( $field['type'] ) ? $field['type'] : 'text';
		$cev_name    = isset( $field['name'] ) ? $field['name'] : $key;
		$cev_id      = isset( $field['id'] ) ? $field['id'] : $key;
		$cev_default = isset( $field['Default'] ) ? $field['Default'] : '';
		$cev_value   = get_option( $cev_name, $cev_default );

		if ( 'toogel' === $cev_type ) {
			$cev_checked = $cev_value ? 'checked' : '';
			?>
			<input type="hidden" name="<?php echo esc_attr( $cev_name ); ?>" value="0" />
			<label class="zui-toggle">
				<input type="checkbox" class="zui-toggle__input" id="<?php echo esc_attr( $cev_id ); ?>" name="<?php echo esc_attr( $cev_name ); ?>" value="1" <?php echo esc_attr( $cev_checked ); ?> />
				<span class="zui-toggle__track"><span class="zui-toggle__thumb"></span></span>
			</label>
			<?php
			return;
		}

		if ( 'dropdown' === $cev_type ) {
			$cev_options = isset( $field['options'] ) ? (array) $field['options'] : array();
			?>
			<div class="zui-select-wrap">
				<select class="zui-select" id="<?php echo esc_attr( $cev_id ); ?>" name="<?php echo esc_attr( $cev_name ); ?>">
					<?php foreach ( $cev_options as $cev_opt_value => $cev_opt_label ) : ?>
						<option value="<?php echo esc_attr( $cev_opt_value ); ?>" <?php selected( (string) $cev_value, (string) $cev_opt_value ); ?>><?php echo esc_html( $cev_opt_label ); ?></option>
					<?php endforeach; ?>
				</select>
				<span class="zui-select-chevron"><svg class="zui-icon" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><polyline points="6 9 12 15 18 9"/></svg></span>
			</div>
			<?php
			return;
		}

		if ( 'textarea' === $cev_type ) {
			$cev_placeholder = isset( $field['Default'] ) ? $field['Default'] : '';
			?>
			<textarea class="zui-textarea zui-input" id="<?php echo esc_attr( $cev_id ); ?>" name="<?php echo esc_attr( $cev_name ); ?>" rows="3" placeholder="<?php echo esc_attr( $cev_placeholder ); ?>"><?php echo esc_textarea( $cev_value ); ?></textarea>
			<?php
			return;
		}

		// Fallback — plain text input for any unhandled type.
		?>
		<input type="text" class="zui-input" id="<?php echo esc_attr( $cev_id ); ?>" name="<?php echo esc_attr( $cev_name ); ?>" value="<?php echo esc_attr( $cev_value ); ?>" />
		<?php
	}
		
	/**
	 * This function adds custom columns in user listing screen in wp-admin area.
	 */
	public function add_column_users_list( $column ) {
		$column['cev_verified'] = __( 'Email verification', 'customer-email-verification-for-woocommerce' );
		$column['cev_action'] = __( 'Actions', 'customer-email-verification-for-woocommerce' );
		return $column;
	}
	
	/**
	 * This function adds custom values to custom columns in user listing screen in wp-admin area.
	 */	
	public function add_details_in_custom_users_list( $val, $column_name, $user_id ) {
		
		wp_enqueue_script( 'wc-jquery-blockui' );
		
		wp_enqueue_style( 'customer_email_verification_user_admin_styles', woo_customer_email_verification()->plugin_dir_url() . 'assets/css/user-admin.css', array(), woo_customer_email_verification()->version );
				
		wp_enqueue_script( 'customer_email_verification_user_admin_script', woo_customer_email_verification()->plugin_dir_url() . 'assets/js/user-admin.js', array( 'jquery','wp-util' ), time() , true);
		
		$user_role = get_userdata( $user_id );
		$verified  = get_user_meta( $user_id, 'customer_email_verified', true );
		
		if ( 'cev_verified' === $column_name ) {
			if ( !woo_customer_email_verification()->is_admin_user( $user_id ) ) {
				if ( !woo_customer_email_verification()->is_verification_skip_for_user( $user_id ) ) {
					
					$verified_btn_css   = ( 'true' == $verified ) ? 'display:none' : '';
					$unverified_btn_css = ( 'true' != $verified ) ? 'display:none' : '';
					
					$html = '<span style="' . $unverified_btn_css . '" class="dashicons dashicons-yes cev_5 cev_verified_admin_user_action" title="Verified"></span>';
					$html .= '<span style="' . $verified_btn_css . '" class="dashicons dashicons-no no-border cev_unverified_admin_user_action cev_5" title="Unverify"></span>';					
					return $html;
				} 
			} 
			return '-';	
		}
		if ( 'cev_action' === $column_name ) {
			if ( !woo_customer_email_verification()->is_admin_user( $user_id ) ) {
				if ( !woo_customer_email_verification()->is_verification_skip_for_user( $user_id ) ) {	
					
					$verify_btn_css   = ( 'true' == $verified ) ? 'display:none' : '';
					$unverify_btn_css = ( 'true' != $verified ) ? 'display:none' : '';
					
					$html = '<span style="' . $unverify_btn_css . '" class="dashicons dashicons-no cev_dashicons_icon_unverify_user" id="' . $user_id . '" wp_nonce="' . wp_create_nonce( 'wc_cev_email' ) . ' "></span>';
					$html .= '<span style="' . $verify_btn_css . '" class="dashicons dashicons-yes small-yes cev_dashicons_icon_verify_user cev_10" id="' . $user_id . '" wp_nonce="' . wp_create_nonce( 'wc_cev_email' ) . ' "></span>';
					$html .= '<span style="' . $verify_btn_css . '" class="dashicons dashicons-image-rotate cev_dashicons_icon_resend_email" id="' . $user_id . '" wp_nonce="' . wp_create_nonce( 'wc_cev_email' ) . ' "></span></span>';
					return $html;
				}
			}			
		}		
		return $val;
	}
	
	public function cev_manualy_user_verify_in_user_menu() {
		
		if ( isset( $_POST['wp_nonce'] ) && wp_verify_nonce( wc_clean( $_POST['wp_nonce'] ), 'wc_cev_email' ) ) { 
		
			$user_id = isset( $_POST['id'] ) ? wc_clean( $_POST['id'] ) : '';
			$action_type = isset( $_POST['actin_type'] ) ? wc_clean( $_POST['actin_type'] ) : '';
			
			if ( 'unverify_user' == $action_type ) {
				delete_user_meta( $user_id, 'customer_email_verified' ); 
			}
			
			if ( 'verify_user' == $action_type ) {
				update_user_meta( $user_id, 'customer_email_verified', 'true' );
			}
			
			if ( 'resend_email' == $action_type ) {
				$current_user           = get_user_by( 'id', $user_id );
				$is_secret_code_present = get_user_meta( $user_id, 'customer_email_verification_code', true );
	
				if ( '' === $is_secret_code_present ) {
					$secret_code = md5( $user_id . time() );
					update_user_meta( $user_id, 'customer_email_verification_code', $secret_code );
				}					
				
				WC_customer_email_verification_email_Common()->wuev_user_id = $user_id; // WPCS: input var ok, CSRF ok.
				WC_customer_email_verification_email_Common()->wuev_myaccount_page_id = $this->my_account_id;
				
				WC_customer_email_verification_email_Common()->code_mail_sender( $current_user->user_email );
			}
		}
		exit;
	}
	
	/**
	 * This function manually verifies a user from wp-admin area.
	 */
	public function cev_manual_verify_user() {
		
		if ( isset( $_GET['user_id'] ) && isset( $_GET['wp_nonce'] ) && wp_verify_nonce( wc_clean( $_GET['wp_nonce'] ), 'wc_cev_email' ) ) {
			
			$user_id = wc_clean( $_GET['user_id'] );
			
			if ( isset( $_GET['wc_cev_confirm'] ) && 'true' === $_GET['wc_cev_confirm'] ) { 
				
				update_user_meta( $user_id, 'customer_email_verified', 'true' );
				add_action( 'admin_notices', array( $this, 'manual_cev_verify_email_success_admin' ) );
				
			} else {
				delete_user_meta( $user_id, 'customer_email_verified' ); 
				add_action( 'admin_notices', array( $this, 'manual_cev_verify_email_unverify_admin' ) );				
			}				
		}
		
		if ( isset( $user_id ) && isset( $_GET['wp_nonce'] ) && wp_verify_nonce( wc_clean( $_GET['wp_nonce'] ), 'wc_cev_email_confirmation' ) ) {			
			$current_user           = get_user_by( 'id', $user_id );
			$is_secret_code_present = get_user_meta( $user_id, 'customer_email_verification_code', true );

			if ( '' === $is_secret_code_present ) {
				$secret_code = md5( $user_id . time() );
				update_user_meta( $user_id, 'customer_email_verification_code', $secret_code );
			}					
			
			WC_customer_email_verification_email_Common()->wuev_user_id = $user_id; // WPCS: input var ok, CSRF ok.
			WC_customer_email_verification_email_Common()->wuev_myaccount_page_id = $this->my_account_id;
			
			WC_customer_email_verification_email_Common()->code_mail_sender( $current_user->user_email );
			add_action( 'admin_notices', array( $this, 'manual_confirmation_email_success_admin' ) );
		}		
	}
	
	public function manual_confirmation_email_success_admin() {
		$text = __( 'Verification email successfully sent.', 'customer-email-verification-for-woocommerce' );
		?>
		<div class="updated notice">
			<p><?php echo esc_html( $text ); ?></p>
		</div>
		<?php
	}
	
	public function manual_cev_verify_email_success_admin() {
		$text = __( 'User verified successfully.', 'customer-email-verification-for-woocommerce' );
		?>
		<div class="updated notice">
			<p><?php echo esc_html( $text ); ?></p>
		</div>
		<?php
	}
	public function manual_cev_verify_email_unverify_admin() {
		$text = __( 'User unverified.', 'customer-email-verification-for-woocommerce' );
		?>
		<div class="updated notice">
			<p><?php echo esc_html( $text ); ?></p>
		</div>
		<?php
	}

	// define the woocommerce_login_form_end callback 
	public function action_woocommerce_login_form_end() {
		?>
		<p class="woocommerce-LostPassword lost_password">
			<a href="<?php echo esc_url( get_home_url() ); ?>?p=reset-verification-email"><?php esc_html_e( 'Resend verification email', 'customer-email-verification-for-woocommerce' ); ?></a>
		</p>
		<?php
	} 

	public function show_cev_fields_in_single_user( $user ) { 
	
		wp_enqueue_style( 'customer_email_verification_user_admin_styles', woo_customer_email_verification()->plugin_dir_url() . 'assets/css/user-admin.css', array(), woo_customer_email_verification()->version );
				
		wp_enqueue_script( 'customer_email_verification_user_admin_script', woo_customer_email_verification()->plugin_dir_url() . 'assets/js/user-admin.js', array( 'jquery','wp-util' ), woo_customer_email_verification()->version , true);
		
		$user_id = $user->ID; 
		$verified  = get_user_meta( $user_id, 'customer_email_verified', true );
		$user_role = get_userdata( $user_id );
		?>
		
		<table class="form-table cev-admin-menu">
			<th colspan="2"><h4 class="cev_admin_user"><?php esc_html_e( 'Customer verification', 'customer-email-verification-for-woocommerce' ); ?></h4></th>
			<tr>
				<th class="cev-admin-padding"><label for="year_of_birth"><?php esc_html_e( 'Email verification status:', 'customer-email-verification-for-woocommerce' ); ?></label></th>
				<td>
				<?php 
				if ( !woo_customer_email_verification()->is_admin_user( $user_id )  && !woo_customer_email_verification()->is_verification_skip_for_user( $user_id ) ) {
					
					$verified_btn_css   = ( 'true' == $verified ) ? 'display:none' : '';
					$unverified_btn_css = ( 'true' != $verified ) ? 'display:none' : '';
					
					$html = '<span style="' . $unverified_btn_css . '" class="dashicons dashicons-yes cev_5 cev_verified_admin_user_action_single" title="Verified"></span>';
					$html .= '<span style="' . $verified_btn_css . '" class="dashicons dashicons-no no-border cev_unverified_admin_user_action_single cev_5" title="Unverify"></span>';					
					echo wp_kses_post( $html );
				} else {
					echo 'Admin';
				}
				?>
				</td>
			</tr>
			<tr>
				<td colspan="2">
				<?php					
				if ( !woo_customer_email_verification()->is_admin_user( $user_id ) && !woo_customer_email_verification()->is_verification_skip_for_user( $user_id ) ) {
					
					$verify_btn_css   = ( 'true' == $verified ) ? 'display:none' : '';
					$unverify_btn_css = ( 'true' != $verified ) ? 'display:none' : '';
				
					$text = '<span class="dashicons dashicons-yes cev-admin-dashicons" style="color:#ffffff; margin-right: 2px;"></span><span>' . __('Verify email manually', 'customer-email-verification-for-woocommerce') . '</span>'; 
					
					echo '<a style="' . esc_html( $verify_btn_css ) . '" class="button-primary cev-admin-verify-button cev_dashicons_icon_verify_user" id="' . esc_html( $user_id ) . '" wp_nonce="' . esc_html( wp_create_nonce( 'wc_cev_email' ) ) . ' "> ' . wp_kses_post( $text ) . '</a>';
						
					$text = '<span class="dashicons dashicons-image-rotate cev-admin-dashicons cev-rotate" ></span><span> ' . __('Resend verification email', 'customer-email-verification-for-woocommerce') . '</span>';
	
					echo '<a style="' . esc_html( $verify_btn_css ) . '" class="button-primary cev-admin-resend-button cev_dashicons_icon_resend_email" id="' . esc_html( $user_id ) . '" wp_nonce="' . esc_html( wp_create_nonce( 'wc_cev_email' ) ) . ' "> ' . wp_kses_post( $text ) . '</a>';
				
					$text = '<span class="dashicons dashicons-no cev-admin-dashicons"></span><span>' . __( 'Un-verify email', 'customer-email-verification-for-woocommerce' ) . '</span>';
	
					echo '<a style="' . esc_html( $unverify_btn_css ) . '" class="button-primary cev-admin-unverify-button cev_dashicons_icon_unverify_user" id="' . esc_html( $user_id ) . '" wp_nonce="' . esc_html( wp_create_nonce( 'wc_cev_email' ) ) . '">' . wp_kses_post( $text ) . '</a>';					
				}
				?>
				</td>
			
			</tr>
		</table>
		
	<?php
	}

	public function filter_user_by_verified( $which ) {
		if ( 'top' === $which ) {
			$top = ( isset($_GET['customer_email_verified_top']) ) ? wc_clean( $_GET['customer_email_verified_top'] ) : null;
			$bottom = ( isset($_GET['customer_email_verified_bottom']) ) ? wc_clean( $_GET['customer_email_verified_bottom'] ) : null;	
			
			$true_selected = '';
			$false_selected = '';
			
			if (!empty($top) || !empty($bottom)) {
				
				$section = !empty($top) ? $top : $bottom;
				if ( 'true' == $section ) {
					$true_selected = 'selected';	
				}
				if ( 'false' == $section ) {
					$false_selected = 'selected';	
				}
			}

			?>
			<select name="customer_email_verified_<?php esc_html_e( $which ); ?>" style="float:none;margin-left:10px;">
				<option value=''><?php esc_html_e( 'User verification', 'customer-email-verification-for-woocommerce' ); ?></option>
				<option <?php esc_html_e( $true_selected ); ?> value='true'><?php esc_html_e( 'Verified', 'customer-email-verification-for-woocommerce' ); ?></option>
				<option <?php esc_html_e( $false_selected ); ?> value='false'><?php esc_html_e( 'Non verified', 'customer-email-verification-for-woocommerce' ); ?></option>
			</select>
			<?php
			submit_button( __( 'Filter' ), '', $which, false );
		
		}
		
			
	}
	
	public function filter_users_by_user_by_verified_section( $query ) {
		global $pagenow;
		if ( is_admin() && 'users.php' == $pagenow ) {
			
			// figure out which button was clicked. The $which in filter_by_job_role()
			if ( isset( $_GET['customer_email_verified_top'] ) ) {
				$top = wc_clean( $_GET['customer_email_verified_top'] ) ? wc_clean( $_GET['customer_email_verified_top'] ) : null;
			}
			
			if ( isset( $_GET['customer_email_verified_bottom'] ) ) {
				$bottom = wc_clean( $_GET['customer_email_verified_bottom'] ) ? wc_clean( $_GET['customer_email_verified_bottom'] ) : null;
			}
			
			if ( !empty( $top ) || !empty( $bottom ) ) {
				
				$section = !empty($top) ? $top : $bottom;
				
				if ( 'true' == $section ) {
					// change the meta query based on which option was chosen
					$meta_query = array (array (
						'key' => 'customer_email_verified',
						'value' => $section,
						'compare' => 'LIKE'
					));
				} else {
					$meta_query = array(
					'relation' => 'OR',
					array(
						'key'     => 'customer_email_verified',
						'compare' => 'NOT EXISTS',
					),
					array(
						'key'     => 'customer_email_verified',
						'value'   => 'true',
						'compare' => '!=',
					),
				);
				}
				$query->set('meta_query', $meta_query);				
			}
		}	
	}
 
	public function add_custom_bulk_actions_for_user( $bulk_array ) {
	 
		$bulk_array['verify_users_email'] = __('Verify users email', 'customer-email-verification-for-woocommerce');
		$bulk_array['send_verification_email'] = __('Send verification email', 'customer-email-verification-for-woocommerce');
		return $bulk_array;
	 
	}

	public function users_bulk_action_handler( $redirect, $doaction, $object_ids ) {
	 
		$redirect = remove_query_arg( array( 'user_id', 'wc_cev_confirm', 'wp_nonce', 'wc_cev_confirmation', 'verify_users_emails', 'send_verification_emails' ), $redirect );

		if ( 'verify_users_email' == $doaction ) {
	 
			foreach ( $object_ids as $user_id ) {
				update_user_meta( $user_id, 'customer_email_verified', 'true' );
			}
	 
			$redirect = add_query_arg( 'verify_users_emails', count( $object_ids ), $redirect );
	 
		}
	 
		if ( 'send_verification_email' == $doaction ) {
			foreach ( $object_ids as $user_id ) {
				
				$current_user = get_user_by( 'id', $user_id );
				$this->user_id                         = $current_user->ID;
				$this->email_id                        = $current_user->user_email;
				$this->user_login                      = $current_user->user_login;
				$this->user_email                      = $current_user->user_email;
				WC_customer_email_verification_email_Common()->wuev_user_id  = $current_user->ID;
				WC_customer_email_verification_email_Common()->wuev_myaccount_page_id = $this->my_account_id;
				$this->is_user_created                 = true;		
				$is_secret_code_present                = get_user_meta( $this->user_id, 'customer_email_verification_code', true );
		
				if ( '' === $is_secret_code_present ) {
					$secret_code = md5( $this->user_id . time() );
					update_user_meta( $user_id, 'customer_email_verification_code', $secret_code );
				}
				
				$cev_email_for_verification = get_option( 'cev_email_for_verification', 0 );
				$verified = get_user_meta( $this->user_id, 'customer_email_verified', true );
				$cev_email_for_verification_mode = get_option( 'cev_email_for_verification_mode', 1 );
				
				if ( 0 == $cev_email_for_verification && 'true' != $verified ) {
					WC_customer_email_verification_email_Common()->code_mail_sender( $current_user->user_email );
				}	
			}
			$redirect = add_query_arg( 'send_verification_emails', count( $object_ids ), $redirect );
		}
	 
		return $redirect;
	 
	}
 
	public function user_bulk_action_notices() {
	 
		if ( ! empty( $_REQUEST['verify_users_emails'] ) ) {			
			printf( '<div id="message" class="updated notice is-dismissible"><p>' .
				/* translators: %s: replace with email */
				esc_html( _n( 'Verification Status updated for  %s user.',
				'Verification Status updated for  %s users.',
				intval( $_REQUEST['verify_users_emails'] )
			) ) . '</p></div>', intval( $_REQUEST['verify_users_emails'] ) );
		}
	 
		if ( ! empty( $_REQUEST['send_verification_emails'] ) ) {			
			printf( '<div id="message" class="updated notice is-dismissible"><p>' .
				/* translators: %s: replace with email */
				esc_html( _n( 'Verification email sent to %s user.',
				'Verification email sent to %s users.',
				intval( $_REQUEST['send_verification_emails'] )
			) ) . '</p></div>', intval( $_REQUEST['send_verification_emails'] ) );
	 
		}
	 
	}	
}
