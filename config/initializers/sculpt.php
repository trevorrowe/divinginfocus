<?php

require(App::root . '/vendor/sculpt/Sculpt.php');

\Sculpt\Connection::add('trowe', array(
  'adapter' => 'mysql',
  'username' => 'root',
  'dsn' => 'mysql:host=localhost;dbname=divinginfocus;unix_socket=/tmp/mysql.sock',
));

\Sculpt\Connection::add('produciton', array(
  'adapter' => 'mysql',
  'username' => 'webdev',
  'password' => 'mrdalkin',
  'dsn' => 'mysql:host=mysql.lanalot.com;dbname=divinginfocus',
));

\Sculpt\Connection::set_default(App::env);

\Sculpt\Logger::set_logger(App::$log);
