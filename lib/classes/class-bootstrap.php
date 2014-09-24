<?php
/**
 * Bootstrap
 *
 * @since 0.2.0
 */
namespace UsabilityDynamics\WPR {

  if( !class_exists( 'UsabilityDynamics\WPR\Bootstrap' ) ) {

    final class Bootstrap extends \UsabilityDynamics\WP\Bootstrap {
      
      /**
       * Singleton Instance Reference.
       *
       * @protected
       * @static
       * @property $instance
       * @type UsabilityDynamics\WPR\Bootstrap object
       */
      protected static $instance = null;
      
      /**
       * Instantaite class.
       */
      public function init() {
      
        //** Be sure we do not have errors. Do not initialize plugin if we have them. */
        if( $this->has_errors() ) {
          return null;
        }
        
        if( !defined( 'WP_REPOSITORY_PATH' ) ) {
          if( function_exists( 'getenv' ) && getenv( 'WP_REPOSITORY_PATH' ) ) {
            define( 'WP_REPOSITORY_PATH', getenv( 'WP_REPOSITORY_PATH' ) );
          } else {
            define( 'WP_REPOSITORY_PATH', $this->path( 'static/packages', 'dir' ) );
          }
        }
        
        add_filter( 'upload_mimes', array( $this, 'filter_upload_mimes' ) );
        add_action( 'template_redirect', array( $this, 'action_template_redirect' ) );
        
        //** Init Settings */
        $this->settings = $this->define_settings();
        //** Init UI */
        $this->ui = $this->define_ui();
      }
      
      /**
       * 
       */
      public function filter_upload_mimes() {
        $existing_mimes['json'] = 'application/json';
        return $existing_mimes;
      }
      
      /**
       *
       */
      public function action_template_redirect() {
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
          $this->render_main_package();
        }
      }
      
      /**
       *
       * @return array
       */
      protected function get_repository_includes() {
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
      protected function render_main_package() {

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
          "includes" => $this->get_repository_includes()
        ) );

      }
      
      /**
       * Initializes and returns Settings object
       * 
       * @return object UsabilityDynamics\Settings
       */
      private function define_settings() {
        //** Initialize Settings. */
        $settings = new \UsabilityDynamics\Settings( array(
          'key'  => 'wp_repository_settings',
          'store'  => 'site_options'
        ));
        $data = $settings->get();
        $data = is_array( $data ) ? $data : array();
        //** Merge with default data. */
        $data = \UsabilityDynamics\Utility::extend( $this->get_schema( 'extra.settings.defaults' ), $data, array(
          'version' => $this->version,
          'domain' => $this->domain,
        ) );
        if( !empty( $data ) ) {
          $settings->set( $data );
        }
        // Return Instance.
        return $settings;
      }
      
      /**
       * Adds Admin UI.
       * If it's multisite and user is not super administrator, we do not add UI!
       *
       */
      private function define_ui(){
        if( is_multisite() && !is_super_admin() ) {
          return false;
        }
        return new \UsabilityDynamics\UI\Settings( $this->settings, $this->get_schema( 'extra.settings.ui' ) );
      }
      
      /**
       * Plugin Activation
       *
       */
      public function activate() {}
      
      /**
       * Plugin Deactivation
       *
       */
      public function deactivate() {}

    }

  }

}
