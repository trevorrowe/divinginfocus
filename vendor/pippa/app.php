<?php

# App catchable errors
#
# - missing environments/env.php file
# - missing view template
# - missing view partial
# - bad route configuration
#

namespace Pippa;

class App {

  const root = APP_ROOT;

  const env = APP_ENV;

  public static $log;

  public static $routes = array();

  public static $controllers = array();

  public static function autoload($class) {
    if(substr($class, 0, 6) == 'Pippa\\') {
      # pippa framework classes
      $dir = self::root . '/vendor/';
      require($dir . strtolower(str_replace('\\', '/', $class)) . '.php');
    } else if(substr($class, strlen($class) - 10) == 'Controller') {
      # controllers
      $dir = self::root . '/app/controllers/';
      require($dir . underscore($class) . '.php');
    }
  }

  public static function run() {
    self::boot();
    Flash::init();
    ob_start();
    Router::dispatch(Request::get_http_request());
    Flash::clean();
  }

  public static function boot() {

    $log_path = App::root . '/log/' . App::env;

    ini_set('error_log', $log_path);

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

    # TODO : clean this up, most of this should get autoloaded
    require(self::root . '/vendor/pippa/functions.php');
    require(self::root . '/config/environment.php');
    require(self::root . '/config/environments/' . self::env . '.php');
    require(self::root . '/config/routes.php');

    self::$log = new Log($log_path);

    # determine the complete list of routeable controllers 

    $controller_dir = self::root . '/app/controllers/';
    $start = strlen($controller_dir);
    $ite = new \RecursiveDirectoryIterator($controller_dir);
    foreach(new \RecursiveIteratorIterator($ite) as $filename => $cur) {
      if(substr_compare($filename, '_controller.php', -15) == 0) {
        $stop = strlen($filename) - 15 - $start;
        array_push(self::$controllers, substr($filename, $start, $stop));
      }
    }

  }
}

class Exception extends \Exception { }
