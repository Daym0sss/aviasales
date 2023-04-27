<?php

use app\Router;

require './vendor/autoload.php';
require $_SERVER['DOCUMENT_ROOT'] . '/kursach/server/app/Router.php';

$router = new Router();
$router->getRoute();
