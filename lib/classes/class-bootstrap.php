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
       * Repository Path
       */
      protected $repository_path = null;
      protected $default_repository_path = null;
      
      /**
       * Instantaite class.
       */
      public function init() {
      
        //** Be sure we do not have errors. Do not initialize plugin if we have them. */
        if( $this->has_errors() ) {
          return null;
        }
        
        add_filter( 'upload_mimes', array( $this, 'filter_upload_mimes' ) );
        add_action( 'template_redirect', array( $this, 'action_template_redirect' ) );
        
        //** Init Settings */
        $this->settings = $this->define_settings();
        
        $this->default_repository_path = $this->path( 'static/packages', 'dir' );

        $this->repository_path = defined( 'WP_REPOSITORY_PATH' ) ? WP_REPOSITORY_PATH : false;

	      if( empty( $this->repository_path ) ) {
          if( function_exists( 'getenv' ) && getenv( 'WP_REPOSITORY_PATH' ) ) {
            $this->repository_path = getenv( 'WP_REPOSITORY_PATH' );
            if( !defined( 'WP_REPOSITORY_PATH' ) ) {
              define( 'WP_REPOSITORY_PATH', $this->repository_path );
            }
          } else {
            $this->repository_path = $this->get( 'repository_path' );
          }
          if( empty( $this->repository_path ) ) {
            $this->repository_path = $this->default_repository_path;  
          }
        }
        
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

        if( empty( $this->repository_path ) || !is_dir( $this->repository_path ) ) {
          return $_list;
        }
        
        $path = trailingslashit( $this->repository_path );
        
        $url_path = str_ireplace( wp_normalize_path( ABSPATH ), '', wp_normalize_path( $path ) );
        $url_path = '/' . ltrim( $url_path, '/\\' );
        
        $su = site_url();
        $hu = home_url();
        if( $hu != $su && strlen( $su ) > strlen( $hu ) ) {
          $diff = trim( str_ireplace( $hu, '', $su ), '/\\' );
        } elseif ( $hu != $su && strlen( $su ) < strlen( $hu ) ) {
          $diff = trim( str_ireplace( $su, '', $hu ), '/\\' );
        }
        
        if( !empty( $diff ) ) {
          $url_path = '/' . $diff . $url_path;
        }
        
        foreach ( glob( $path . "*.json" ) as $filename ) {
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
       *
       */
      public function parse_ui_field( $field ) {
        if( $field[ 'id' ] == 'repository_path' ) {
          $field[ 'desc' ] = '';
          if( empty( $this->repository_path ) ) {
            $field[ 'desc' ] .= sprintf( __( 'If empty, the following path is being used: <b>%s</b>', $this->domain ), $this->default_repository_path ) . '</br>';
          }
          $field[ 'desc' ] .= sprintf( __( 'Path also can be defined via environment variable <b>%s</b> or constant with the same name', $this->domain ), 'WP_REPOSITORY_PATH' ) . '</br>';
          $field[ 'desc' ] .= sprintf( __( 'Path\'s defining priorities: 1) constant, 2) environment variable, 3) current option field, 4) default path <b>%s</b>', $this->domain ), wp_normalize_path( $this->path( 'static/packages', 'dir' ) ) ) . '</br>';
          $field[ 'desc' ] .= '<b>' . __( 'Note: path must goes to current WordPress directory installation to prevent invalid packages links!', $this->domain ) . '</b>';
        }
        return $field;
      }
      
      /**
       *
       */
      public function maybe_render_updater() {
        $repository_path = $this->repository_path;
        $github_access_token = $this->get( 'github_access_token' );
        $organizations = $this->get( 'organizations' );
        if( !empty( $github_access_token ) && !empty( $organizations ) && !empty( $repository_path ) ) {
          if( !is_array( $organizations ) ) {
            $organizations = explode( ',', $organizations );
            foreach( $organizations as $k => $v ) {
              $organizations[ $k ] = trim( $v );
            }
          }
          include_once( $this->path( 'static/views/admin.updater.php', 'dir' ) );
        }
      }
      
      /**
       *
       */
      public function admin_enqueue_scripts() {
        $github_access_token = $this->get( 'github_access_token' );
        $organizations = $this->get( 'organizations' );
        if( !empty( $github_access_token ) && !empty( $organizations ) ) {
          wp_enqueue_script( 'wp-repository-settings', $this->path( 'static/scripts/admin.settings.js' ), array( 'jquery' ) );
          wp_localize_script( 'wp-repository-settings', '_ud_wpr_settings', array(
            'ajax_url' => $this->path( 'files-updater.php' ),
            'is_defined_constant' => defined( 'WP_REPOSITORY_PATH' ) ? true : false,
            'current_path' => $this->repository_path,
            'default_path' => $this->default_repository_path,
          ) );
        }
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
        //echo "<pre>"; print_r( $data ); echo "</pre>"; die();
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
        
        add_filter( "ud:ui:field", array( $this, 'parse_ui_field' ) );
        add_action( 'ud:ui:settings:view:tab:settings:top', array( $this, 'maybe_render_updater' ) );
        add_action( 'ud:ui:settings:render', array( $this, 'admin_enqueue_scripts' ) );

	      if( class_exists( 'UsabilityDynamics\UI\Settings' ) ) {
		      return new \UsabilityDynamics\UI\Settings( $this->settings, $this->get_schema( 'extra.settings.ui' ) );
	      }

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
