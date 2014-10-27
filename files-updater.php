<?php
/**
 *
 *
 */

$success = true;
$message = 'Repository files have been successfully updated.';

try {

  /** Try to load classmap */
  if ( file_exists( dirname( __FILE__ ) . '/vendor/autoload.php' ) ) {
    require_once ( dirname( __FILE__ ) . '/vendor/autoload.php' );
  } else {
    throw new Exception( 'WP-Repository Distributive is broken. ' . dirname( __FILE__ ) . '/vendor/autoload.php file is missed. Try to remove and upload plugin again.' );
  }
  
  if( !class_exists( 'UsabilityDynamics\Composer\Github_Updater' ) ) {
    throw new Exception( 'UsabilityDynamics\Composer\Github_Updater is not found. Be sure WP-Repository plugin is installed correctly.' );
  }
  
  /** Set github acces token */
  if( empty( $_REQUEST[ 'access_token' ] ) ) {
    throw new Exception( 'Parameter \'access_token\' is not set. Be sure you set GitHub Access Token in settings' );
  }
  $github_access_token = $_REQUEST[ 'access_token' ];

  /** Set organizations */
  if( empty( $_REQUEST[ 'organizations' ] ) ) {
    throw new Exception( 'Parameter \'organizations\' is not set. Be sure you set GitHub Organizations in settings' );
  }
  $organizations = $_REQUEST[ 'organizations' ];
  
  /** Use or not cache */
  $cache = isset( $_REQUEST[ 'nocache' ] ) && $_REQUEST[ 'nocache' ] == 'true' ? false : true;
  
  /** Set path where composer repository files are stored. */
  $path = !empty( $_REQUEST[ 'path' ] ) ? urldecode( $_REQUEST[ 'path' ] ) : dirname( __FILE__ ) . '/static/packages';
  
  if( !is_dir( $path ) ) {
    throw new Exception( 'Directory ' . $path . ' does not exist. Create directory or change path before proceed.' );
  }
  
  $updater = new \UsabilityDynamics\Composer\Github_Updater( $github_access_token, $organizations, $path, $cache );
  
  if( !$updater->run() ) {
    throw new Exception( 'There is an error on doing request to Github or with local files permissions. Check your Github API Settings and be sure that defined Repository directory has valid file permissions.' );
  }
  
} catch ( Exception $e ) {
  $success = false;
  $message = 'Could not update repository files. ' . $e->getMessage();
}

@header( 'Content-Type: application/json; charset=UTF-8' );
echo json_encode( array(
  'ok' => $success,
  'message' => $message,
) );
die();