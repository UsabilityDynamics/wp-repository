<?php
/**
 * Plugin Name: WP-Repository
 * Plugin URI: https://usabilitydynamics.com
 * Description: Custom Composer Repository for Wordpress
 * Author: Usability Dynamics, Inc.
 * Version: 0.3.2
 * Text Domain: wp_repository
 * Author URI: http://usabilitydynamics.com
 *
 * Copyright 2012 - 2014 Usability Dynamics, Inc.  ( email : info@usabilitydynamics.com )
 *
 */

if( !function_exists( 'ud_get_wp_repository' ) ) {

	/**
	 * Returns  Instance
	 *
	 * @author Usability Dynamics, Inc.
	 * @since 0.2.0
	 *
	 * @param bool $key
	 * @param null $default
	 *
	 * @return
	 */
  function ud_get_wp_repository( $key = false, $default = null ) {
    $instance = \UsabilityDynamics\WPR\Bootstrap::get_instance();
    return $key ? $instance->get( $key, $default ) : $instance;
  }

}

if( !function_exists( 'ud_check_wp_repository' ) ) {
  /**
   * Determines if plugin can be initialized.
   *
   * @author Usability Dynamics, Inc.
   * @since 0.2.0
   */
  function ud_check_wp_repository() {
    global $_ud_wp_repository_error;

    try {
      //** Be sure composer.json exists */
      $file = dirname( __FILE__ ) . '/composer.json';
      if( !file_exists( $file ) ) {
        throw new Exception( __( 'Distributive is broken. composer.json is missed. Try to remove and upload plugin again.', 'wp_repository' ) );
      }
      $data = json_decode( file_get_contents( $file ), true );
      //** Be sure PHP version is correct. */
      if( !empty( $data[ 'require' ][ 'php' ] ) ) {
        preg_match( '/^([><=]*)([0-9\.]*)$/', $data[ 'require' ][ 'php' ], $matches );
        if( !empty( $matches[1] ) && !empty( $matches[2] ) ) {
          if( !version_compare( PHP_VERSION, $matches[2], $matches[1] ) ) {
            throw new Exception( sprintf( __( 'Plugin requires PHP %s or higher. Your current PHP version is %s', 'wp_repository' ), $matches[2], PHP_VERSION ) );
          }
        }
      }
      //** Be sure vendor autoloader exists */
      if ( file_exists( dirname( __FILE__ ) . '/vendor/autoload.php' ) ) {
        require_once ( dirname( __FILE__ ) . '/vendor/autoload.php' );
      } else {
        throw new Exception( sprintf( __( 'Distributive is broken. %s file is missed. Try to remove and upload plugin again.', 'wp_repository' ), dirname( __FILE__ ) . '/vendor/autoload.php' ) );
      }

      //** Be sure our Bootstrap class exists */
      if( !class_exists( '\UsabilityDynamics\WPR\Bootstrap' ) ) {
        throw new Exception( __( 'Distributive is broken. Plugin loader is not available. Try to remove and upload plugin again.', 'wp_repository' ) );
      }

    } catch( Exception $e ) {
      $_ud_wp_repository_error = $e->getMessage();
      return false;
    }
    return true;
  }

}

if( !function_exists( 'ud_my_wp_plugin_message' ) ) {
  /**
   * Renders admin notes in case there are errors on plugin init
   *
   * @author Usability Dynamics, Inc.
   * @since 0.2.0
   */
  function ud_wp_repository_message() {
    global $_ud_wp_repository_error;
    if( !empty( $_ud_wp_repository_error ) ) {
      $message = sprintf( __( '<p><b>%s</b> can not be initialized. %s</p>', 'wp_repository' ), 'WP-Repository', $_ud_wp_repository_error );
      echo '<div class="error fade" style="padding:11px;">' . $message . '</div>';
    }
  }
  add_action( 'admin_notices', 'ud_wp_repository_message' );
}

if( ud_check_wp_repository() ) {
  //** Initialize. */
  ud_get_wp_repository();
}