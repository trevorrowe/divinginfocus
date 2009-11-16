<?php 

namespace Pippa;

define('APP_ROOT', realpath(dirname(__FILE__) . '/..'));
define('APP_ENV', getenv('APP_ENV') ? getenv('APP_ENV') : 'production');

require_once(APP_ROOT . '/vendor/pippa/app.php');

App::boot();

Router::dispatch(Request::httpRequest());
