<?php 

namespace Pippa;

App::$routes = array();

route('/');
route(':controller');
route(':controller/:action/:id');
route(':controller/:action');

$request = new Request('/admin/users/edit');

foreach(App::$routes as $route) {
  if($route->matches_request($request)) {
     print_r($request->params);
     exit();
  }
}

echo "failed\n";
exit;

$params = array(
  'controller' => 'abc',
  'action' => 'show',
  'id' => 123,
);

$params = array(
  'controller' => 'index',
  'action' => 'index',
  'id' => '123',
);

foreach(App::$routes as $route) {
  if($route->matches_params($params)) {
    echo $route->build_url($params) . "\n";
    exit;
  }
}
echo "failed\n";
