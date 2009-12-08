<?php 

namespace Pippa;

$start = microtime(true);

define('APP_ROOT', realpath(dirname(__FILE__) . '/..'));
define('APP_ENV', getenv('APP_ENV') ? getenv('APP_ENV') : 'development');

require(APP_ROOT . '/vendor/pippa/app.php');

App::run();

Log::timing($start, microtime(true));
