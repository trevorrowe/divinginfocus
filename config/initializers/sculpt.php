<?php

namespace Sculpt;

require(\App::root . '/vendor/sculpt/Sculpt.php');

Logger::set_logger(\App::$log);

#\Sculpt\Connection::load_ini_file(App::root . '/config/database.ini');

\App::$cache->set('database_connections', function() {
  $ini_path = \App::root . '/config/database.ini';
  return parse_ini_file($ini_path, true);
});

foreach(\App::$cache->get('database_connections') as $name => $details) {
  Connection::add($name, $details);
}

Connection::set_default(\App::env);
