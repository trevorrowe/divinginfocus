<?php 

namespace Framework;

#$request = new Request('home/123');
#$request = new Request('home/abc');
#$request = new Request('home');
#$request = new Request('abc/xyz/123');
$request = new Request('abc/123/edit');
#$request = new Request('');

// foreach(App::$routes as $route) {
//   if($params = $route->match($request)) {
//     print_r($params);
//     exit();
//   }
// }

$params = array(
  'controller' => 'abc',
  'action' => 'show',
  'id' => 123,
);

$params = array(
  'controller' => 'home',
  'id' => '123',
);

foreach(Route::$routes as $route) {
  if($route->testParams($params)) {
    echo $route->buildPath($params) . "\n";
    exit();
  }
}
