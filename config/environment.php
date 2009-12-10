<?php

### config

# asset_timestamps
# caching
# 


use \Pippa\App as App;

### register the default spl autoloader

spl_autoload_extensions('.php');
spl_autoload_register(function($class) {
  spl_autoload($class, '.php');
});

set_include_path(App::root . '/app/models');
add_include_path(App::root . '/lib');
