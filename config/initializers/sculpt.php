<?php

require(\Pippa\App::root . '/vendor/sculpt/Sculpt.php');

$dsn = 'mysql://root@localhost/divinginfocus;unix_socket=/tmp/mysql.sock';

\Sculpt\connect($dsn);

\Sculpt\Sculpt::$logger = \Pippa\App::$log;

