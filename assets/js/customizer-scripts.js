/*
 * Customizer Scripts
 *
 * Opening a CEV section switches the preview to that section's page. It no
 * longer clicks Publish: WordPress carries unsaved changes across preview
 * pages, and auto-publishing made half-finished edits go live just by opening
 * another section.
 */

jQuery(document).ready(function() {

    /**
     * Change description
     */
	jQuery(cev_customizer.trigger_click).trigger( "click" );    
	jQuery('#customize-theme-controls #accordion-section-themes').hide();	
});

(function ( api ) {
    api.section( 'cev_controls_section', function( section ) {		
        section.expanded.bind( function( isExpanded ) {				
            var url;
            if ( isExpanded ) {
                url = cev_customizer.seperate_email_preview_url;
                api.previewer.previewUrl.set( url );
            }
        } );
    } );
} ( wp.customize ) );

(function ( api ) {
    api.section( 'cev_new_account_email_section', function( section ) {		
        section.expanded.bind( function( isExpanded ) {				
            var url;
            if ( isExpanded ) {
                url = cev_customizer.my_account_email_preview_url;
                api.previewer.previewUrl.set( url );
            }
        } );
    } );
} ( wp.customize ) );

(function ( api ) {
    api.section( 'cev_verification_widget_messages', function( section ) {		
        section.expanded.bind( function( isExpanded ) {				
            var url;
            if ( isExpanded ) {
                url = cev_customizer.verification_widget_message_preview_url;				
                api.previewer.previewUrl.set( url );
            }
        } );
    } );
} ( wp.customize ) );