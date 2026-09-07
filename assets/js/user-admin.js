/* zorem_snackbar_user_admin shim — delegates to ZUI.snackbar() when the
   shared Zorem UI library is loaded (assets/zui/js/zui.js). Falls back
   to the legacy inline toast div when the library isn't deployed. */
(function( $ ){

	function cevUserAdminFallback( msg ) {
		var $t = $( '<div></div>' ).addClass( 'zorem_snackbar_user_admin show_snackbar_user_admin' ).text( msg );
		$( 'body' ).append( $t );
		setTimeout( function () { $t.remove(); }, 3000 );
	}

	$.fn.zorem_snackbar_user_admin = function ( msg ) {
		if ( window.ZUI && typeof window.ZUI.snackbar === 'function' ) {
			window.ZUI.snackbar( msg, { type: 'success' } );
		} else {
			cevUserAdminFallback( msg );
		}
		return this;
	};
})( jQuery );

jQuery(document).on('click', '.cev_dashicons_icon_unverify_user', function(e) {	
	"use strict";
	
	jQuery(this).parent("td").block({
		message: null,
			overlayCSS: {
				background: "#fff",
				opacity: .6
			}	
    });
	
	var id = jQuery(this).attr('id');
	var unverify_btn = jQuery(this);
	var ajax_data = {
		action: 'cev_manualy_user_verify_in_user_menu',
		id: id,
		wp_nonce : jQuery(this).attr('wp_nonce'),
		actin_type: 'unverify_user',	
	};

	jQuery.ajax({           
		url : ajaxurl,
		type : 'post',
		data : ajax_data,
		success : function( response ) {	
			unverify_btn.parent("td").unblock();
			unverify_btn.hide();
			unverify_btn.parent('td').find('.cev_dashicons_icon_verify_user').show();
			unverify_btn.parent('td').find('.cev_dashicons_icon_resend_email').show();
			unverify_btn.parent('td').prev().find('.cev_verified_admin_user_action').hide();
			unverify_btn.parent('td').prev().find('.cev_unverified_admin_user_action').show();
			
			jQuery('.cev_verified_admin_user_action_single').hide();
			jQuery('.cev_unverified_admin_user_action_single').show();
		}
	}); 	
});	

jQuery(document).on('click', '.cev_dashicons_icon_verify_user', function(e) {	
	"use strict";
	
	jQuery(this).parent("td").block({
		message: null,
			overlayCSS: {
				background: "#fff",
				opacity: .6
			}	
    });
	
	var verify_btn = jQuery(this);
	var ajax_data = {
		action: 'cev_manualy_user_verify_in_user_menu',
		id: jQuery(this).attr("id"),
		wp_nonce : jQuery(this).attr('wp_nonce'),
		actin_type: 'verify_user',
				
	};
	
	jQuery.ajax({           
		url : ajaxurl,
		type : 'post',
		data : ajax_data,
		success : function( response ) {
			verify_btn.parent("td").unblock();
			verify_btn.hide(); 
			verify_btn.parent('td').find('.cev_dashicons_icon_resend_email').hide();
			verify_btn.parent('td').find('.cev_dashicons_icon_unverify_user').show();
			verify_btn.parent('td').prev().find('.cev_verified_admin_user_action').show();
			verify_btn.parent('td').prev().find('.cev_unverified_admin_user_action').hide();
			jQuery('.cev_verified_admin_user_action_single').show();
			jQuery('.cev_unverified_admin_user_action_single').hide();	
		}
	}); 	
});	
jQuery(document).on('click', '.cev_dashicons_icon_resend_email', function(e) {	
	"use strict";
	
	jQuery(this).parent("td").block({
		message: null,
			overlayCSS: {
				background: "#fff",
				opacity: .6
			}	
    });
	var resend_btn = jQuery(this);
	var ajax_data = {
		action: 'cev_manualy_user_verify_in_user_menu',
		id: jQuery(this).attr("id"),
		wp_nonce : jQuery(this).attr('wp_nonce'),
		actin_type: 'resend_email',				
	};
	
	
	jQuery.ajax({           
		url : ajaxurl,
		type : 'post',
		data : ajax_data,
		success : function( response ) {
		resend_btn.parent("td").unblock();
		jQuery(this).zorem_snackbar_user_admin( 'Resend Email Successfully Send.' );
		}
	}); 	
});	