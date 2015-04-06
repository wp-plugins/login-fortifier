jQuery( document ).ready( function( $ ) {
    $( '#wp-submit' ).click( function( e ) {
        $( '#loginform' ).append( wplf_trans.login_fortifier_field );
        $( '#lostpasswordform' ).append( wplf_trans.login_fortifier_field );
        //e.preventDefault();
    } );
} );

