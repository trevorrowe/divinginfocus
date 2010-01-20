<?php 

namespace Pippa;

$start = microtime(true);

define('APP_ROOT', realpath(dirname(__FILE__) . '/..'));
define('APP_ENV', getenv('APP_ENV') ? getenv('APP_ENV') : 'development');

require(APP_ROOT . '/vendor/pippa/lib/app.php');

App::run();

App::$log->timing(microtime(true) - $start);
