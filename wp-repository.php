<?php
/**
 * Plugin Name: Repository Manager
 * Plugin URI: http://usabilitydynamics.com/plugins/
 * Description: Composer and stuff.
 * Author: Usability Dynamics, Inc.
 * Version: 0.1.0
 * Author URI: http://usabilitydynamics.com
 *
 * /home/ud/storage/repositories
 *
 */

add_filter('upload_mimes', function ( $existing_mimes=array() ) {
	$existing_mimes['json'] = 'application/json';
	return $existing_mimes;
});

// @legacy
add_action( 'init', function() {

	if( !defined( 'WP_REPOSITORY_PATH' ) ) {

		if( function_exists( 'getenv' ) && getenv( 'WP_REPOSITORY_PATH' ) ) {
			define( 'WP_REPOSITORY_PATH', getenv( 'WP_REPOSITORY_PATH' ) );
		} else {
			define( 'WP_REPOSITORY_PATH', WP_CONTENT_DIR . '/static/repository' );
		}

	}

});

add_action( 'template_redirect', function() {
	global $wp_query;

	$_subdomain = false;
	$_basePath = false;

	if( $_SERVER[ 'REQUEST_URI' ] === '/packages.json' ) {
		$_basePath = true;
	}

	if( strpos( $_SERVER[ 'HTTP_HOST' ], 'repository' ) === 0 ) {
		$_subdomain = true;
	}

	if( $_subdomain && $_basePath ) {
		render_main_package();
	}

});

/**
 *
 * @return array
 */
function get_repository_includes() {

	$_list = array();

	if( !defined( 'WP_REPOSITORY_PATH' ) || !is_dir( WP_REPOSITORY_PATH ) ) {
		return $_list;
	}
  
  $url_path = plugin_dir_url( trailingslashit( str_ireplace( '/www/', '/public_html/', WP_REPOSITORY_PATH ) ) . 'packages.json' );
  $url_path = str_ireplace( home_url(), '', $url_path );

	foreach (glob( WP_REPOSITORY_PATH . "/*.json") as $filename) {
		$_list[ $url_path . basename( $filename ) ] = array(
			'sha1' => sha1( filemtime( $filename ) ),
			'updated' => filemtime( $filename ),
			'description' => 'Updated ' . human_time_diff( filemtime( $filename ) ) . '.'
		);

	}

	return $_list;

}

/**
 *
 */
function render_main_package() {

	nocache_headers();

	//header( 'Cache-Control:no-cache' );
	//header( 'Content-Type:application/json' );
	//header( 'Last-Modified: ' . gmdate('D, d M Y H:i:s', time() .' GMT', true, 200 ) );

	if( function_exists( 'http_response_code' )) {
		http_response_code( 200 );
	} else {
		header( "HTTP/1.0 200 OK" );
	}

	// thanks WordPrsss
	wp_send_json( array(
		"ok" => true,
		"includes" => get_repository_includes()
	) );

}