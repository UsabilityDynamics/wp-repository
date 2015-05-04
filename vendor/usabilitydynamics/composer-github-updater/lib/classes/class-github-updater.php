<?php
/**
 * Bootstrap
 *
 * @since 0.2.0
 */
namespace UsabilityDynamics\Composer {

  if( !class_exists( 'UsabilityDynamics\Composer\Github_Updater' ) ) {

    final class Github_Updater {
     
      /**
       *
       *
       */
      private $config;
      
      /**
       *
       *
       */
      private $client;
     
      /**
       *
       */
      public function __construct( $access_token, $organizations, $path, $cache = true ) {
        //** Prepare path */
        $path = rtrim( $path, '/\\' ) . '/';
        //** Prepare organizations list */
        if( !is_array( $organizations ) ) {
          $organizations = explode( ',', $organizations );
          foreach( $organizations as $k => $v ) {
            $organizations[ $k ] = trim( $v );
          }
        }
        //** Setup our config */
        $this->config = array(
          'client' => $path . 'cache',
          'github_access_token' => $access_token,
          'path_to_files' => $path,
          'organizations' => $organizations,
        );
        //** If we want to clear cache, we remove the directory here */
        if( !$cache ){
          shell_exec( 'rm -rf ' . $this->config[ 'client' ] );
        }
        /** Setup our client */
        $this->client = new \Github\Client(
          new \Github\HttpClient\CachedHttpClient( array(
            'cache_dir' => $this->config[ 'client' ]
          ) )
        );
        /** And authenticate it */
        $this->client->authenticate( $this->config[ 'github_access_token' ], \Github\Client::AUTH_HTTP_TOKEN );
      }
      
      /**
       *
       */
      public function run() {
        //** Make sure the directory exists */
        if( !is_dir( $this->config[ 'path_to_files' ] ) ){
          return false;
        }
        
        //** Get the repos which have composer.json files */
        $repos = array();
        foreach( $this->config[ 'organizations' ] as $organization ){
          $api = $this->client->api( 'organization' );
          $pager = new \Github\ResultPager( $this->client );
          try{
            $results = $pager->fetchAll( $api, 'repositories', array( $organization ) );
          }catch( \Exception $e ){
            continue;
          }
          foreach( $results as $row ){
            try{
              $composer_file = $this->client->api( 'repos' )->contents()->show( $organization, $row[ 'name' ], 'composer.json', $row[ 'default_branch' ] );
            }catch( \Exception $e ){
              continue;
            }
            $composer_data = @json_decode( @base64_decode( $composer_file[ 'content' ] ) );
            /** Ok, make sure we have a valid composer file */
            if( is_object( $composer_data ) && isset( $composer_data->name ) ){
              if( !isset( $repos[ $organization ] ) ){
                $repos[ $organization ] = array();
              }
              $repos[ $organization ][ $row[ 'name' ] ] = array(
                ( $row[ 'default_branch' ] == 'master' ? '' : 'dev-' ) . $row[ 'default_branch' ] => $composer_data
              );
            }
          }
        }
        
        /** Ok, now we're going to get all the tags */
        foreach( $repos as $organization => &$data ){
          foreach( $data as $name => &$composer_files ){
            /** Branches first */
            $branches = $this->client->api( 'repos' )->branches( $organization, $name );
            /** Ok, loop through branches, and get the composer json files */
            foreach( $branches as $branch ){
              /** Skip it if we've already declared the branch */
              if( in_array( $branch[ 'name' ], array_keys( $composer_files ) ) ){
                continue;
              }
              if( in_array( 'dev-' . $branch[ 'name' ], array_keys( $composer_files ) ) ){
                continue;
              }
              try{
                $composer_file = $this->client->api( 'repos' )->contents()->show( $organization, $name, 'composer.json', $branch[ 'commit' ][ 'sha' ] );
              }catch( \Exception $e ){
                continue;
              }
              $composer_data = @json_decode( @base64_decode( $composer_file[ 'content' ] ) );
              /** Ok, make sure we have a valid composer file */
              if( is_object( $composer_data ) && isset( $composer_data->name ) ){
                $composer_files[ ( $branch[ 'name' ] != 'master' ? 'dev-' : '' ) . $branch[ 'name' ] ] = $composer_data;
              }
            }
            /** Now do tags */
            $tags = $this->client->api( 'repos' )->tags( $organization, $name );
            /** Ok, loop through the tags, and get the composer files */
            foreach( $tags as $tag ){
              try{
                $composer_file = $this->client->api( 'repos' )->contents()->show( $organization, $name, 'composer.json', $tag[ 'commit' ][ 'sha' ] );
              }catch( \Exception $e ){
                continue;
              }
              $composer_data = @json_decode( @base64_decode( $composer_file[ 'content' ] ) );
              /** Ok, make sure we have a valid composer file */
              if( is_object( $composer_data ) && isset( $composer_data->name ) ){
                $composer_files[ $tag[ 'name' ] ] = $composer_data;
              }
            }
          }
        }
        
        //** Ok, now we're going to loop through the repositories/tags, and set everything up to be json encoded */
        $files = array();
        foreach( $repos as $organization => &$data ){
          $filename = strtolower( $organization ) . '-github.json';
          $filedata = new \stdClass();
          $filedata->ok = true;
          $filedata->packages = new \stdClass();
          foreach( $data as $name => &$composer_files ){
            $filedata->packages->{$name} = new \stdClass();
            foreach( $composer_files as $tag => &$composer_file ){
              /** Overwrite the name */
              $composer_file->name = strtolower( $organization ) . '/' . $name;
              /** Overwrite the version */
              $composer_file->version = $tag;
              /** Overwrite the dist */
              $dist_tag = str_ireplace( 'dev-', '', $tag );
              $composer_file->dist = new \stdClass();
              $composer_file->dist->url = "https://github.com/{$organization}/{$name}/archive/{$dist_tag}.zip";
              $composer_file->dist->type = "zip";
              $composer_file->source = new \stdClass();
              $composer_file->source->url = "git@github.com:{$organization}/{$name}";
              $composer_file->source->type = "git";
              $composer_file->source->reference = $dist_tag;
              /** Add it to the thing */
              $filedata->packages->{$name}->{$tag} = $composer_file;
            }
          }
          /** Put the contents out there */
          file_put_contents( $this->config[ 'path_to_files' ] . $filename, json_encode( $filedata ) );
        }
        
        return true;
      }
      
    }

  }

}
