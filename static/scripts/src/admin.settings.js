/**
 * Updater
 *
 */
( function( $, strings ){
  
  var btn = $( '#wpr_check_updates_for_repository' );
  var spinner = $( '#wpr_spinner' );
  
  btn.click( function( e ){
  
    var access_token = jQuery( '[name="a_access_token"]' ).val();
    var path = jQuery( '[name="a_path"]' ).val();
    var nocache = jQuery( '[name="a_nocache"]' ).is(':checked');
    var organizations = typeof jQuery( '[name="a_organizations"]:checked' ).val() != 'undefined' ? 
      jQuery( '[name="a_organizations"]:checked' ).val() : jQuery( '[name="a_organizations"]' ).val();
    
    btn.prop( 'disabled', true );
    spinner.show();
    
    $.ajax({
      type: 'POST',
      url: strings.ajax_url,
      data: {
        access_token: access_token,
        organizations: organizations,
        path: path,
        nocache: nocache
      },
      success: function( r ) {
        //** @todo: show response in more sexy way. */
        if( r.ok == true ) {
          alert( r.message );
        } else {
          alert( r.message );
        }
      }
    }).done( function() {
      btn.prop( 'disabled', false );
      spinner.hide();
    } );
    
    return false;
    
  } );
  
} )( jQuery, _ud_wpr_settings );