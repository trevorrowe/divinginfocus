<?php

namespace Pippa;

class App {

  const root = APP_ROOT;
  const env = APP_ENV;

  public static function addIncludePath($path) {
    set_include_path(get_include_path() . PATH_SEPARATOR . self::root . $path);
  }

  public static function autoloader($class) {
    $path = self::root.'/vendor/'.strtolower(str_replace('\\', '/', $class)).'.php';
    require_once($path);
  }

  public static function boot() {

    spl_autoload_register("\Pippa\App::autoloader");

    set_include_path('');
    self::addIncludePath('/lib');
    self::addIncludePath('/app/models');

    # Load the environment.php file, with all of the users settings
    require_once(self::root . '/vendor/pippa/helpers.php');
    require_once(self::root . '/config/environment.php');
    require_once(self::root . '/config/database.php');
    require_once(self::root . '/config/routes.php');
  }

}

function route($pattern, $options = array()) {
  Router::addRoute($pattern, $options);
}
