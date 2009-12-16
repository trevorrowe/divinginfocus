<?php

route('/', array('controller' => 'home'));
route(':controller');
route(':controller/:id', array('id' => "\d+(-.+)?", 'action' => 'show'));
route(':controller/:action');
route(':controller/:action/:id');
