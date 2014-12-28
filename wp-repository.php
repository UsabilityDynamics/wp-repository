<?php
/**
 * Plugin Name: WP-Repository
 * Plugin URI: https://usabilitydynamics.com
 * Description: Custom Composer Repository for Wordpress
 * Author: Usability Dynamics, Inc.
 * Version: 0.3.3
 * Text Domain: wp_repository
 * Author URI: http://usabilitydynamics.com
 * GitHub Plugin URI: UsabilityDynamics/wp-repository
 *
 * Copyright 2012 - 2014 Usability Dynamics, Inc.  ( email : info@usabilitydynamics.com )
 *
 * ### Usage
 * Get packages.json file with different results based on "client" and "access-key", passed as basic authentication values.
 * ```
 * curl https://repository.usabilitydynamics.com/packages.json
 * curl https://wordpress-site@repository.usabilitydynamics.com/packages.json
 * curl https://wordpress-site:super-secret-access-token@repository.usabilitydynamics.com/packages.json
 * ```
 *
 * @param string $type
 * @return mixed
 */

add_action( 'plugins_loaded', function() {

  if (!defined('WPR_BASEURL')) {
    define('WPR_BASEURL', '_packages');
  }

  if (!defined('WPR_HOSTNAME')) {
    define('WPR_HOSTNAME', 'repository.usabilitydynamics.com');
  }

  if (!defined('WP_REPOSITORY_AUTH')) {
    define('WP_REPOSITORY_AUTH', false);
  }

  if (!defined('WP_REPOSITORY_PATH') && defined('WP_CONTENT_DIR')) {
    define('WP_REPOSITORY_PATH', wp_normalize_path(WP_CONTENT_DIR . '/uploads/repository'));
  }

  if (!defined('WP_REPOSITORY_LOG_PATH') && defined('WP_CONTENT_DIR')) {
    define('WP_REPOSITORY_LOG_PATH', wp_normalize_path(WP_CONTENT_DIR));
  }
  
  if ( !defined('ACCEPTED_GIT_BRANCH') ) {
	define('ACCEPTED_GIT_BRANCH', 'master');
  }
  
  if ( !defined( 'GIT_ACCESS_TOKEN' ) ) {
	define('GIT_ACCESS_TOKEN', '4b6cee67bfb5dd2cf27a48563cc506508827db95');
  }

});

add_filter('wpr::single_release', function ($release) {

  if (isset($_SERVER['PHP_AUTH_USER']) && stripos($_SERVER['PHP_AUTH_USER'], 'wordpress-client') === 0) {

    if ($release->type === 'wordpress-plugin') {
      unset($release->autoload);
    }

    if ($release->type === 'wordpress-theme') {
      unset($release->autoload);
    }

  }

  return $release;

});

add_filter('wpr::installer_name', function ($name, $release, $package) {

  if ($release->version === 'master') {
    return $name;
  }

  if ($release->version === 'dev-develop') {
    return $name;
  }

  // Strip out characters that don't affect installer path
  $_version = str_replace(array('v', 'RC2'), '', $release->version);

  // @todo round off version to second number
  if (in_array($release->type, array('wordpress-plugin'))) {
    // $_name = $name . '-v' . $_version;
  }

  return isset($_name) ? $_name : $name;

}, 10, 3);

add_filter('wpr::includes_url', function ($url) {
  $url = str_replace('wp-content/public/repository.usabilitydynamics.com', WPR_BASEURL, $url);
  return $url;
});

function record_request_information($type = '') {

  $_data = print_r($_SERVER, true);

  file_put_contents(WP_CONTENT_DIR . '/wp-repository/' . $type . '.log', $_data . "\n", FILE_APPEND);

  return $_data;

}

if (!function_exists('ud_get_wp_repository')) {

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
  function ud_get_wp_repository($key = false, $default = null) {
    $instance = UsabilityDynamics\WPR\Bootstrap::get_instance();
    return $key ? $instance->get($key, $default) : $instance;
  }


}

if (!function_exists('ud_check_wp_repository')) {
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
      $file = dirname(__FILE__) . '/composer.json';
      if (!file_exists($file)) {
        throw new Exception(__('Distributive is broken. composer.json is missed. Try to remove and upload plugin again.', 'wp_repository'));
      }
      $data = json_decode(file_get_contents($file), true);
      //** Be sure PHP version is correct. */
      if (!empty($data['require']['php'])) {
        preg_match('/^([><=]*)([0-9\.]*)$/', $data['require']['php'], $matches);
        if (!empty($matches[1]) && !empty($matches[2])) {
          if (!version_compare(PHP_VERSION, $matches[2], $matches[1])) {
            throw new Exception(sprintf(__('Plugin requires PHP %s or higher. Your current PHP version is %s', 'wp_repository'), $matches[2], PHP_VERSION));
          }
        }
      }
      //** Be sure vendor autoloader exists */
      if (file_exists(dirname(__FILE__) . '/vendor/autoload.php')) {
        require_once(dirname(__FILE__) . '/vendor/autoload.php');
      } else {
        throw new Exception(sprintf(__('Distributive is broken. %s file is missed. Try to remove and upload plugin again.', 'wp_repository'), dirname(__FILE__) . '/vendor/autoload.php'));
      }

      //** Be sure our Bootstrap class exists */
      if (!class_exists('\UsabilityDynamics\WPR\Bootstrap')) {
        throw new Exception(__('Distribution is broken. Plugin loader is not available. Try to remove and upload plugin again.', 'wp_repository'));
      }

      if (!class_exists('\UsabilityDynamics\WPR\Utility')) {
        throw new Exception(__('Distribution is broken. Plugin utility class is not available.', 'wp_repository'));
      }

    } catch (Exception $e) {
      $_ud_wp_repository_error = $e->getMessage();
      return false;
    }

    return true;
  }

}

if (!function_exists('ud_my_wp_plugin_message')) {
  /**
   * Renders admin notes in case there are errors on plugin init
   *
   * @author Usability Dynamics, Inc.
   * @since 0.2.0
   */
  function ud_wp_repository_message() {
    global $_ud_wp_repository_error;
    if (!empty($_ud_wp_repository_error)) {
      $message = sprintf(__('<p><b>%s</b> can not be initialized. %s</p>', 'wp_repository'), 'WP-Repository', $_ud_wp_repository_error);
      echo '<div class="error fade" style="padding:11px;">' . $message . '</div>';
    }
  }

  add_action('admin_notices', 'ud_wp_repository_message');
}

if (ud_check_wp_repository()) {

  if ($_SERVER['SERVER_NAME'] == WPR_HOSTNAME) {
    header('Vary: X-Repository-Type,X-Repository-Key,accept');
    header('Cache-Control: no-cache');

    if (!isset($_SERVER['HTTP_AUTHORIZATION']) && isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
      $_SERVER['HTTP_AUTHORIZATION'] = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
    }

    if (isset($_SERVER['HTTP_AUTHORIZATION']) && !empty($_SERVER['HTTP_AUTHORIZATION'])) {
      list($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']) = explode(':', base64_decode(substr($_SERVER['HTTP_AUTHORIZATION'], 6)));
    }

    header('Cache-Control: no-cache');
    header('Pagespeed:off');

    $_authorized = false;

    if (!isset($_SERVER['PHP_AUTH_USER'])) {

      header('X-Repository-Authorized: false');

      if (UsabilityDynamics\WPR\Utility::is_browser_request() && defined('WP_REPOSITORY_AUTH') && WP_REPOSITORY_AUTH) {
        header('WWW-Authenticate: Basic realm="Repository"');
        header('HTTP/1.0 401 Unauthorized');
        // die('WP_REPOSITORY_PATH:' . WP_REPOSITORY_PATH );
        // die('WPR_BASEURL:' . WPR_BASEURL );
        exit; // must exit here to force browser to re-access url with authentication, otherwise stored credentials don't get sent
      }

    } else {

      header('X-Repository-Version: 1.2.3');
      header('X-Repository-Authorized: true');
      header('X-Repository-Type: ' . $_SERVER['PHP_AUTH_USER']);
      header('X-Repository-Key: ' . $_SERVER['PHP_AUTH_PW']);

      $_authorized = true;

    }

    if (strpos($_SERVER['REQUEST_URI'], '/downloads/') === 0) {
      // record_request_information( 'downloads.json' );

      $_package = str_replace(array('/downloads/', '.zip'), '', $_SERVER['REQUEST_URI']);

      // header( 'Content-Disposition: attachment; filename=UsabilityDynamics-private-plugin.zip' );

      //header( 'Location:https://api.github.com/repos/' . $_package . '/zipball/master?access_token=dfb7e688a4f52c7fd97794d8f5d0ede11666a91b', 302 );
      header('Location:https://api.github.com/repos/UsabilityDynamics/private-plugin/zipball/master?access_token=dfb7e688a4f52c7fd97794d8f5d0ede11666a91b', 302);
      die();
      //wp_send_json( $_data );

    }

    if (strpos($_SERVER['REQUEST_URI'], '/' . WPR_BASEURL . '/') === 0) {
      // record_request_information( 'package.json' );

      $_package = str_replace(array('/' . WPR_BASEURL . '/', '.json'), '', $_SERVER['REQUEST_URI']);

      $_content = file_get_contents(WP_REPOSITORY_PATH . '/' . $_package . '.json');
      $_data = json_decode($_content, false);

      $_response = array(
        "ok" => true,
        "packages" => array()
      );

      foreach ($_data->packages as $name => $_package) {
        $_response['packages'][$name] = UsabilityDynamics\WPR::parse_package($_package, $name);
      }

      wp_send_json($_response);

    }

    if ($_SERVER['REQUEST_URI'] === '/packages.json') {

      // record_request_information( 'packages.json' );

      if ($_authorized) {

        add_filter('wpr::main_package', function ($main_package) {
          if (file_exists(__DIR__ . '/tmp/packages-private.json')) {
            $_private = json_decode(file_get_contents(__DIR__ . '/tmp/packages-private.json'));
            //$main_package['includes'] = array_merge( (array) $main_package['includes'], (array) $_private );
            $main_package['packages'] = array_merge((array)$main_package['packages'], (array)$_private);
          }

          return $main_package;

        });

      } else {

        add_filter('wpr::main_package', function ($main_package) {
          $main_package['packages'] = array_merge((array)$main_package['packages'], array());
          return $main_package;
        });

      }

    }

  }

  //** Initialize. */
  ud_get_wp_repository();
}