/* zorem_snackbar jQuery shim — delegates to the shared Zorem UI library's
   ZUI.snackbar() API (assets/zui/js/zui.js) when available. Existing call
   sites (`.zorem_snackbar(msg)` / `.zorem_snackbar_warning(msg)`) keep
   working unchanged; they just render as the library's glassmorphic
   floating pill instead of the old inline div. Falls back to the legacy
   inline toast only when the library isn't deployed. */
(function( $ ){

	function cevFallbackToast( msg, variant ) {
		var cls = 'zorem_snackbar show_snackbar';
		if ( 'warning' === variant || 'error' === variant ) {
			cls += ' zorem_snackbar_warning';
		}
		var $t = $( '<div></div>' ).addClass( cls ).text( msg );
		$( 'body' ).append( $t );
		setTimeout( function () { $t.remove(); }, 3000 );
	}

	function cevToast( msg, type ) {
		if ( window.ZUI && typeof window.ZUI.snackbar === 'function' ) {
			window.ZUI.snackbar( msg, { type: type } );
		} else {
			cevFallbackToast( msg, type );
		}
	}

	$.fn.zorem_snackbar = function ( msg ) {
		cevToast( msg, 'success' );
		return this;
	};

	$.fn.zorem_snackbar_warning = function ( msg ) {
		cevToast( msg, 'error' );
		return this;
	};

	$.fn.zorem_snackbar_info = function ( msg ) {
		cevToast( msg, 'info' );
		return this;
	};
})( jQuery );


jQuery(document).on("change", "#cev_enable_email_verification", function(){
	
	var accordion = jQuery(this).closest('.accordion');			
	var form = jQuery("#cev_settings_form");	

	jQuery.ajax({
		url: ajaxurl,
		data: form.serialize(),
		type: 'POST',
		dataType:"json",	
		success: function() {	
			jQuery("#cev_settings_form").zorem_snackbar( 'Your Settings have been successfully saved.' );		
		},
		error: function(response) {
			console.log(response);			
		}
	});
});

(function( $ ){
	$.fn.isInViewport = function( element ) {
		var win = $(window);
		var viewport = {
			top : win.scrollTop()			
		};
		viewport.bottom = viewport.top + win.height();
		
		var bounds = this.offset();		
		bounds.bottom = bounds.top + this.outerHeight();

		if( bounds.top >= 0 && bounds.bottom <= window.innerHeight) {
			return true;
		} else {
			return false;	
		}		
	};
})( jQuery );

jQuery(document).on("click", ".cev_settings_save", function(){

	var btn  = this;
	var form = jQuery("#cev_settings_form");

	/* Library save-button loading state (assets/zui/js/zui.js).
	   ZUI.btnSaving() adds .is-saving + swaps the label to "Saving…"
	   and disables pointer events; ZUI.btnReset() restores the
	   original label on completion. No-ops if the library isn't
	   deployed — the AJAX still runs the same. */
	if ( window.ZUI && typeof window.ZUI.btnSaving === 'function' ) {
		window.ZUI.btnSaving( btn );
	}

	jQuery.ajax({
		url: ajaxurl,
		data: form.serialize(),
		type: 'POST',
		dataType:"json",
		success: function() {
			form.find(".spinner").removeClass("active");
			if ( window.ZUI && typeof window.ZUI.btnReset === 'function' ) {
				window.ZUI.btnReset( btn );
			}
			jQuery("#cev_settings_form").zorem_snackbar( 'Your Settings have been successfully saved.' );
		},
		error: function(response) {
			console.log(response);
			if ( window.ZUI && typeof window.ZUI.btnReset === 'function' ) {
				window.ZUI.btnReset( btn );
			}
			jQuery.fn.zorem_snackbar_warning( 'Failed to save settings.' );
		}
	});
	return false;
});


jQuery( document ).ready(function() {
	jQuery('#cev_verification_popup_overlay_background_color').wpColorPicker({
		change: function(e, ui) {
			jQuery('.cev_verification_widget_preview').prop("disabled", true);
		},
	});	
	jQuery(".woocommerce-help-tip").tipTip();
	
	jQuery('#cev_verification_popup_background_color').wpColorPicker({
		change: function(e, ui) {
			jQuery('.cev_verification_widget_preview').prop("disabled", true);
		},
	});
	
});


jQuery(document).on("click", ".cev_tab_input", function(){
	"use strict";
	var tab = jQuery(this).data('tab');
	var url = window.location.protocol + "//" + window.location.host + window.location.pathname+"?page=customer-email-verification-for-woocommerce&tab="+tab;
	window.history.pushState({path:url},'',url);	
});

jQuery(document).click(function(){
	var $trigger = jQuery(".cev_dropdown");
    if($trigger !== event.target && !$trigger.has(event.target).length){
		jQuery(".cev-dropdown-content").hide();
    }   
});

jQuery(document).on("click", ".cev-dropdown-menu", function(){	
	jQuery('.cev-dropdown-content').show();
});


jQuery(document).on("click", ".cev-dropdown-content li a", function(){
	var tab = jQuery(this).data('tab');
	var label = jQuery(this).data('label');
	var section = jQuery(this).data('section');
	jQuery('.inner_tab_section').hide();
	jQuery('.cev_nav_div').find("[data-tab='" + tab + "']").prop('checked', true); 
	jQuery('#'+section).show();
	jQuery('.zorem-layout-cev__header-breadcrumbs .header-breadcrumbs-last-cev').text(label);
	var url = window.location.protocol + "//" + window.location.host + window.location.pathname+"?page=customer-email-verification-for-woocommerce&tab="+tab;
	window.history.pushState({path:url},'',url);
	jQuery(".cev-dropdown-content").hide();
});

( function( $, data, wp, ajaxurl ) {
	"use strict";
		
	var $cev_verification_widget_settings_form = $("#cev_verification_widget_settings_form");	
			
	var cev_settings_pro_init = {
		
		init: function() {									
			$cev_verification_widget_settings_form.on( 'click', '.cev_verification_widget_settings_save', this.save_wc_cev_verification_widget_settings_form );					
		},

		save_wc_cev_verification_widget_settings_form: function( event ) {
			
			event.preventDefault();
			
			$cev_verification_widget_settings_form.find(".spinner").addClass("active");
			var ajax_data = $cev_verification_widget_settings_form.serialize();
			
			$.post( ajaxurl, ajax_data, function(response) {
					jQuery('.cev_verification_widget_preview').prop("disabled", false);

				$cev_verification_widget_settings_form.find(".spinner").removeClass("active");
				jQuery("#cev_verification_widget_settings_form").zorem_snackbar( 'Your Settings have been successfully saved.' );		
			});
			
		}
	};		
	
	$(window).load(function(e) {
        cev_settings_pro_init.init();
    });	
})( jQuery, customer_email_verification_script, wp, ajaxurl );

jQuery(document).on("click", ".cev_verification_widget_preview", function(){
	"use strict";	
	document.getElementById('cev_preview_iframe').contentDocument.location.reload(true);
	jQuery('#cev_preview_iframe').load(function(){
		jQuery('.cev_page_preview_popup').show();	
		var iframe = document.getElementById("cev_preview_iframe");
	});	
});

jQuery(document).on("click", ".cev-popup-close", function(){	
	"use strict";
	jQuery('.cev_page_preview_popup').hide();
});

jQuery(document).on("click", ".cev_popup_close_icon", function(){	
	jQuery('.cev_page_preview_popup').hide();	
});

jQuery( document ).on( "click", "#activity-panel-tab-help", function() {
	jQuery(this).addClass( 'is-active' );
	jQuery( '.woocommerce-layout__activity-panel-wrapper' ).addClass( 'is-open is-switching' );
});

jQuery(document).click(function(){
	var $trigger = jQuery(".woocommerce-layout__activity-panel");
    if($trigger !== event.target && !$trigger.has(event.target).length){
		jQuery('#activity-panel-tab-help').removeClass( 'is-active' );
		jQuery( '.woocommerce-layout__activity-panel-wrapper' ).removeClass( 'is-open is-switching' );
    }   
});
jQuery( document ).on( "click", ".close_btn", function() {
	jQuery( '.cev_pro_banner' ).hide();
});

  var table; // Define the DataTable variable

  // Idempotent DataTable init for #userLogTable. Exposed on window so the
  // tab switcher can re-init when the Unverified Users tab is shown after
  // the table was rendered inside a `[hidden]` panel (DataTables can't
  // compute column widths against a zero-width bounding box, so we
  // destroy + reinit once the panel is on screen).
  window.cevInitUserLogTable = function () {
	  var $tbl = jQuery('#userLogTable');
	  if (!$tbl.length) { return null; }
	  if (jQuery.fn.DataTable.isDataTable($tbl)) {
		  $tbl.DataTable().destroy();
	  }
	  return $tbl.DataTable({
		  searching: false,
		  lengthChange: false,
		  pageLength: 50,
		  autoWidth: false,
		  columnDefs: [
			  {
				  orderable: false,
				  targets: [0, 1, 2, 3]
			  },
			  {
				  width: '20px',
				  orderable: false,
				  targets: 0
			  },
			  {
				  className: 'text-right',
				  targets: -1
			  },
			  {
				  targets: '_all',
				  createdCell: function (td, cellData, rowData, row, col) {
					  if (col === 3) {
						  jQuery(td).css('text-align', 'right');
					  }
					  if (row === 0) {
						  if (col === 0) {
							  jQuery(td).css('width', '20px');
						  } else {
							  jQuery(td).css('width', '300px');
						  }
					  }
				  }
			  }
		  ],
		  rowCallback: function (row, data, index) {
			  jQuery(row).hover(
				  function () {
					  jQuery(this).addClass('hover-row');
				  },
				  function () {
					  jQuery(this).removeClass('hover-row');
				  }
			  );
		  }
	  });
  };

  jQuery(document).ready(function() {
	  var table = window.cevInitUserLogTable();
	  
	  
	  jQuery(document).on("click", ".cev_tab_input", function(){
		  "use strict";
		  var tab = jQuery(this).data('tab');
		  var url = window.location.protocol + "//" + window.location.host + window.location.pathname+"?page=customer-email-verification-for-woocommerce&tab="+tab;
		  window.history.pushState({path:url},'',url);
		  
	  });
  
	  var selectAllChk = document.getElementById('select_all');
	  if ( selectAllChk ) {
		  selectAllChk.addEventListener('click', function() {
			  var checkboxes = document.querySelectorAll('.row_checkbox');
			  for (var checkbox of checkboxes) {
				  checkbox.checked = this.checked;
			  }
		  });
	  }
  
	  jQuery('.apply_bulk_action').on('click', function() {
		  var action = jQuery('#bulk_action').val();
		  if (action === 'delete') {
			  var selectedIds = [];
			  jQuery('.row_checkbox:checked').each(function() {
				  selectedIds.push(jQuery(this).val());
			  });
  
			  if (selectedIds.length > 0) {
				  if (confirm('Are you sure you want to delete the selected users?')) {
					  // AJAX call to delete selected users
					  jQuery.ajax({
						  url: cev_vars.ajax_url,
						  method: 'POST',
						  data: {
							  action: 'delete_users',
							  nonce: cev_vars.delete_user_nonce,
							  ids: selectedIds
						  },
						  success: function(response) {
							  response = JSON.parse(response);
							  if (response.success) {
								  // Remove deleted rows from the table
								  for (var id of selectedIds) {
									  table.row(jQuery('input[value="' + id + '"]').closest('tr')).remove().draw();
								  }
								  jQuery.fn.zorem_snackbar('Users deleted successfully.');
							  } else {
								  jQuery.fn.zorem_snackbar_warning('Error deleting users.');
							  }
						  },
						  error: function() {
							  jQuery.fn.zorem_snackbar_warning('Error deleting users.');
						  }
					  });
				  }
			  } else {
				  jQuery.fn.zorem_snackbar_warning('No users selected for deletion');
			  }
		  }
	  });
  
	  jQuery(document).on('click', '.delete_button', function() {
		  var button = jQuery(this);
		  var userId = button.data('id');
		  if (confirm('Are you sure you want to delete this user?')) {
			  // AJAX call to delete the user
			  jQuery.ajax({
				  url: cev_vars.ajax_url,
				  method: 'POST',
				  data: {
					  action: 'delete_user',
					  nonce: cev_vars.delete_user_nonce,
					  id: userId
				  },
				  success: function(response) {
					  response = JSON.parse(response);
					  if (response.success) {
						  // Remove the row from the table
						  table.row(button.closest('tr')).remove().draw();
						  jQuery.fn.zorem_snackbar('User deleted successfully.');
					  } else {
						  jQuery.fn.zorem_snackbar_warning('Error deleting user.');
					  }
				  },
				  error: function() {
					  jQuery.fn.zorem_snackbar_warning('Error deleting user.');
				  }
			  });
		  }
	  });
	  
  });
  /* activity-panel custom popup create start */
document.addEventListener('DOMContentLoaded', () => {
    const menuButton = document.querySelector('.menu-button');
    const popupMenu = document.querySelector('.popup-menu');

    if ( menuButton && popupMenu ) {
        menuButton.addEventListener('click', () => {
            popupMenu.style.display = popupMenu.style.display === 'block' ? 'none' : 'block';
        });

        document.addEventListener('click', (e) => {
            if (!menuButton.contains(e.target) && !popupMenu.contains(e.target)) {
                popupMenu.style.display = 'none';
            }
        });
    }
});


jQuery(document).ready(function ($) {
    var $toggle = $("#cev_enable_email_verification");
    var $panel = $(".panel.options.add-tracking-option.active");

    function updatePanelVisibility() {
        if ($toggle.is(":checked")) {
            $panel.slideDown();
        } else {
            $panel.slideUp();
        }
    }

    // Initial check on page load
    updatePanelVisibility();

    // Listen for change events on the toggle
    $toggle.on("change", updatePanelVisibility);
});

document.addEventListener('DOMContentLoaded', function () {
    const tabs = document.querySelectorAll('.cev_tab_input');
    const breadcrumb = document.querySelector('.breadcums_page_heading');

    tabs.forEach(tab => {
        tab.addEventListener('click', function () {
            breadcrumb.textContent = this.getAttribute('data-label');
        });
    });
});
/* activity-panel custom popup create end */