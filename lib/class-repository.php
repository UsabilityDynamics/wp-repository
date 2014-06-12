<?php
/**
 * MyModule
 *
 * usabilitydynamics.com-repository
 * http://github.com/UsabilityDynamics/usabilitydynamics.com-repository
 */
namespace UsabilityDynamics\Corporate {

  if( !class_exists( 'UsabilityDynamics\Corporate\Repository' ) ) {

    class Repository {

      /**
       * Module Path.
       *
       * @public
       * @static
       * @property $path
       * @type {string}
       */
      static public $path = null;

      /**
       * Intialize MyModule.
       *
       * @param $parent
       * @param $module
       *
       */
      public function __construct( $parent = array(), $module = array() ) {

        try {

          // Initialize Module.

        }  catch( \Exception $error ) {
          trigger_error($error->getMesage() );
        }

      }

    }

  }

}