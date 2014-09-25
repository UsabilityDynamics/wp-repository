/**
 * Updater
 *
 */
( function( $, strings ){
  
  var btn = $( '#wpr_check_updates_for_repository' );
  var spinner = $( '#wpr_spinner' );
  btn.click( function( e ){
    btn.prop( 'disabled', true );
    spinner.show();
    $.ajax({
      type: 'POST',
      url: strings.ajax_url,
      data: {
        action: 'wprepository_check_updates'
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