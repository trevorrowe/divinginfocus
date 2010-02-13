<?php

use Pippa\Route;

Route::add('/photos/:version/:a/:b/:c/:d/:e/:f/:g/:h/:filename', array(
  'controller' => 'photos',
  'action' => 'thumbnail',
));

Route::add('/admin', array('controller' => 'admin/users'));

Route::add('/users/:username', array(
  'controller' => 'users', 
  'action' => 'show',
));

Route::defaults();
