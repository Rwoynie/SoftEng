<?php

require '../vendor/autoload.php';

$router = require '../src/Routes/index.php';

$router->get('admin/dashboard', 'AdminController@dashboard');