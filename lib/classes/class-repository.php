<?php
/**
 * Utility
 *
 * @since 0.2.0
 */
namespace UsabilityDynamics {

  if( !class_exists( 'UsabilityDynamics\WPR' ) ) {

    class WPR {

	    /**
	     * Converts a raw composer.json object into a repository-ready object.
	     *
	     * @param null $package
	     * @param $name
	     *
	     * @return mixed|null|object|void
	     */
	    static public function parse_package( $package = null, $name ) {

		    if( !isset( $package ) || !is_object( $package ) ) {
			    $package = (object) array();
		    }

		    foreach( $package as $version => $release ) {

			    if( !isset( $release->extra ) ) {
				    $release->extra = (object) array();
			    }

			    if( !isset( $release->extra->{'installer-name'} ) ) {
				    $release->extra->{"installer-name"} = apply_filters( 'wpr::installer_name', str_replace( '/', '-', $name ), $release, $package );
			    };

			    if( isset( $release->extra->settings ) ) {
				    unset( $release->extra->settings );
			    }

			    if( !isset( $release->license ) ) {
				    $release->license = 'MIT';
			    }

			    if( isset( $release->settings ) ) {
				    unset( $release->settings );
			    }

			    $package->{$version} = apply_filters( 'wpr::single_release', $release );

		    }

		    $package = apply_filters( 'wpr::single_package', $package );

		    return $package;

	    }

    }

  }

}
