<?php

use \Pippa\App;

require(App::root . '/vendor/sculpt/Sculpt.php');

\Sculpt\Logger::set_logger(App::$log);

\Sculpt\Connections::add_by_ini_file(App::root . '/config/sculpt.ini');

\Sculpt\Connections::set_default(App::env);
