<?php

use \Pippa\App as App;

### register the default spl autoloader

set_include_path(App::root . '/app/models');

spl_autoload_register(function($class) {
  spl_autoload($class, '.php');
});
