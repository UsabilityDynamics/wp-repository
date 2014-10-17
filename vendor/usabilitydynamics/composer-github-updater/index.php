<?php

if( empty( $_REQUEST[ 'github_access_token' ] ) ) {
  echo 'parameter \'github_access_token\' is not set';
  die();
}
$github_access_token = $_REQUEST[ 'github_access_token' ];

if( empty( $_REQUEST[ 'organizations' ] ) ) {
  echo 'parameter \'organizations\' is not set';
  die();
}
$organizations = $_REQUEST[ 'organizations' ];

/** Pull in our autoloader */
require_once( 'vendor/autoload.php' );

/** Pull in my local functions if they exist */
if( file_exists( '/sites/_includes/loader.php' ) ){
  require_once( '/sites/_includes/loader.php' );
}

$cache = isset( $_REQUEST[ 'nocache' ] ) ? false : true;
$updater = new \UsabilityDynamics\Composer\Github_Updater( $github_access_token, $organizations, '../', $cache );
$updater->run();

echo 'done';
die();
