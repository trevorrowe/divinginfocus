<?php

# TODO : config : mailer domain
# TODO : config : flash coookie name    [_pippa_flash]
# TODO : config : session coookie name  [_pippa_session]
# TODO : config : session cookie domain [$_REQUEST['HTTP_HOST']]
# TODO : config : session hmac key      *required*
# TODO : config : session secret key    *required*

class App {

  const root = APP_ROOT;

  const env = APP_ENV;

  public static $session;

  public static $cfg;

  public static $log;

  public static $cache;

  public static $routes = array();

  public static function autoload($class) {

    if($class == 'Pippa\App') {
      throw new Exception($class);
    }

    if(substr($class, 0, 6) == 'Pippa\\') {
      $class_path = str_replace('\\', '/', $class);
      require(App::root . "/vendor/pippa/lib/{$class_path}.php");
      return;
    }

    # loading application controllers
    if(substr($class, strlen($class) - 10) == 'Controller') {
      require \Pippa\Controller::controller_path($class);
      return;
    }

    # loading application maielrs
    if(substr($class, strlen($class) - 6) == 'Mailer') {
      require(App::root . '/app/mailers/' . underscore($class) . '.php');
      return;
    }

    # loading application helpers
    if(substr($class, strlen($class) - 6) == 'Helper') {
      require App::root . '/app/helpers/' . underscore($class) . '.php';
      return;
    }

  }

  public static function run() {

    self::boot();

    self::$session = new \Pippa\Session();

    \Pippa\Flash::init();

    ob_start();

    \Pippa\Router::dispatch(\Pippa\Request::get_http_request());

    \Pippa\Flash::clean();

    self::$session->save();
  }

  public static function boot() {

    spl_autoload_register("App::autoload");

    foreach(glob(App::root . '/vendor/pippa/lib/functions/*.php') as $file)
      require($file);

    $log_path = App::root . '/log/' . App::env . '.log';

    ini_set('error_log', $log_path);

    self::$log = new \Pippa\Logger($log_path);

    require(App::root . '/config/routes.php');

    ## add standard include paths

    set_include_path(App::root . '/app/models');
    add_include_path(App::root . '/lib');
    spl_autoload_register(function($class) {
      spl_autoload(underscore($class), '.php');
    });

    ## setup the app cache

    self::$cache = new \Pippa\Cache\File('pippa_app_cache');

    self::$cache->load();

    # Things to consider static caching:
    #
    # * controller action
    # * helpers (framework and user provided)
    # * helper defined methods?
    # * environment config

    ## determine the complete list of available controllers

    self::$cache->set('controller_names', function() {
      $names = array();
      $controller_dir = App::root . '/app/controllers/';
      $start = strlen($controller_dir);
      $ite = new \RecursiveDirectoryIterator($controller_dir);
      foreach(new \RecursiveIteratorIterator($ite) as $filename => $cur) {
        if(substr_compare($filename, '_controller.php', -15) == 0) {
          $stop = strlen($filename) - 15 - $start;
          $names[] = substr($filename, $start, $stop);
        }
      }
      return $names;
    });

    ## determine what error pages are provided by the application

    self::$cache->set('error_pages', function() {
      $error_pages = array();
      foreach(glob(App::root . '/public/*.html') as $filename) {
        $parts = explode('.', basename($filename)); 
        if(is_numeric($parts[0]))
          $error_pages[] = (int) $parts[0];
      }
      return $error_pages;
    });

    ## register all of the framework provided helpers

    \Pippa\Helper::register('Pippa\Helpers\Tags');
    \Pippa\Helper::register('Pippa\Helpers\Forms');
    \Pippa\Helper::register('Pippa\Helpers\Flashes');
    \Pippa\Helper::register('Pippa\Helpers\Pagination');
    \Pippa\Helper::register('ApplicationHelper');

    ## load the application's intializers

    foreach(glob(App::root . '/config/initializers/*.php') as $file)
      require($file);

  }
}
