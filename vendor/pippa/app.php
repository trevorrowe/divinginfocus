<?php

namespace Pippa;

class App {

  const root = APP_ROOT;

  const env = APP_ENV;

  public static $log;

  public static $routes = array();

  public static $controllers = array();

  public static function autoload($class) {
    if(substr($class, 0, 6) == 'Pippa\\') {
      ## pippa framework classes
      $dir = self::root . '/vendor/';
      require($dir . strtolower(str_replace('\\', '/', $class)) . '.php');
    } else if(substr($class, strlen($class) - 10) == 'Controller') {
      ## controllers
      require Controller::controller_path($class);
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

    spl_autoload_register("\Pippa\App::autoload");

    self::$log = new Logger($log_path);

    # TODO : why is this line here?
    require(self::root . '/vendor/pippa/exception.php');

    require(self::root . '/vendor/pippa/functions.php');
    require(self::root . '/app/helpers/application_helper.php');

    require(self::root . '/config/environment.php');
    require(self::root . '/config/environments/' . self::env . '.php');

    foreach(glob(self::root . '/config/initializers/*.php') as $file)
      require($file);

    require(self::root . '/config/routes.php');

    ## add standard include paths

    set_include_path(App::root . '/app/models');
    add_include_path(App::root . '/lib');
    spl_autoload_register(function($class) {
      spl_autoload($class, '.php');
    });

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
