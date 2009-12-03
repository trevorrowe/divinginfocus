<?php

# App catchable errors
#
# - missing environments/env.php file
# - missing view template
# - missing view partial
# - bad route configuration
#

namespace Pippa;

/**
 * @package Pippa
 */

/**
 * 
 */
class App {

  const root = APP_ROOT;

  const env = APP_ENV;

  public static function add_include_path($path) {
    set_include_path(get_include_path() . PATH_SEPARATOR . self::root . $path);
  }

  public static function autoload($class) {
    # only autoload classes in the Pippa Framework
    if(substr($class, 0, 6) == 'Pippa\\') {
      $dir = self::root . '/vendor/';
      require($dir . strtolower(str_replace('\\', '/', $class)) . '.php');
    }
  }

  public static function run() {
    self::boot();
    Router::dispatch(Request::get_http_request());
  }

  public static function boot() {

    ini_set('error_log', App::root . '/log/' . App::env . '.log');

    require(self::root . '/vendor/hopnote/Hopnote.php');
    \Hopnote::register_handlers('72f3e257342bd683d986a4ef5f70be84', array(
      'environment' => self::env,
      'deployed' => self::env == 'production',
      'fatals' => TRUE,
      'root' => self::root,
      'errors' => E_ALL | E_STRICT,
      'fivehundred' => self::root . '/public/500.html',
    ));

    spl_autoload_register("\Pippa\App::autoload");

    self::add_include_path('/lib');
    self::add_include_path('/app/models');
    
    # Load the environment.php file, with all of the users settings
    require(self::root . '/vendor/pippa/helpers.php');
    require(self::root . '/config/environment.php');
    require(self::root . '/config/environments/' . self::env . '.php');
    require(self::root . '/config/routes.php');
  }

}
