<?php

use \Pippa\App;

require(App::root . '/vendor/sculpt/Sculpt.php');

\Sculpt\Logger::set_logger(App::$log);

\Sculpt\Connection::load_ini_file(App::root . '/config/database.ini');

\Sculpt\Connection::set_default(App::env);
