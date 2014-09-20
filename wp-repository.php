<?php
/**
 * Plugin Name: Repository Manager
 * Plugin URI: http://usabilitydynamics.com/plugins/
 * Description: Composer and stuff.
 * Author: Usability Dynamics, Inc.
 * Version: 1.0
 * Author URI: http://usabilitydynamics.com
 *
 * /home/ud/storage/repositories
 *
 */

// @legacy
add_action( 'init', function() {

  function get_repository_includes() {

    $_list = array();

    foreach (glob("repository/*.json") as $filename) {

      $_list[ 'repository/' . str_replace( '', '', basename( $filename ) ) ] = array(
        'sha1' => sha1( filemtime( $filename ) ),
        'updated' => filemtime( $filename ),
        'description' => 'Updated ' . time_ago( filemtime( $filename ) ) . '.'
      );

    }

    return $_list;

  }

});


add_action( 'template_redirect', function() {
	global $wp_query, $wp;

	if( $_SERVER[ 'REQUEST_URI' ] === '/package.json' ) {
		render_main_package();
	}

});


function render_main_package() {

	header( 'Cache-Control:no-cache' );
	header( 'Content-Type:application/json' );
	header( 'Last-Modified:Thu, 08 May 2014 22:01:01 GMT' );
	header( 'Date:Thu, 08 May 2014 22:10:21 GMT' );

	$_response = array(
		"ok" => true,
		"includes" => get_repository_includes()
	);

	die( json_encode( $_response ) );

}