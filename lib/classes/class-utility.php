<?php
/**
 * Utility
 *
 * @since 0.2.0
 */
namespace UsabilityDynamics\WPR {

  if( !class_exists( 'UsabilityDynamics\WPR\Utility' ) ) {

    final class Utility extends \UsabilityDynamics\WP\Bootstrap {

	    /***
	     * Determine if current request is from a browser.
	     *
	     * @return bool
	     */
	    public static function is_browser_request() {

		    if( !isset( $_SERVER[ 'HTTP_USER_AGENT' ] ) ) {
			    return false;
		    }

		    if( stripos( $_SERVER[ 'HTTP_USER_AGENT' ], 'wordpress' ) === 0 ) {
			    return false;
		    }

		    if( stripos( $_SERVER[ 'HTTP_USER_AGENT' ], 'composer' ) === 0 ) {
			    return false;
		    }

		    if( stripos( $_SERVER[ 'HTTP_USER_AGENT' ], 'curl' ) === 0 ) {
			    return false;
		    }

		    return true;

	    }

    }

  }

}
