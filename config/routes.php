<?php

use Pippa\Route;

Route::add('/admin', array('controller' => 'admin/users'));

Route::add('/users/:username', array(
  'controller' => 'users', 
  'action' => 'show',
));

Route::defaults();
