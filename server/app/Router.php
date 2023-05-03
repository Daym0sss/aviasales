<?php

namespace app;

use controllers\UserController;
use controllers\FlightController;
use controllers\AdminController;
use models\User;

require $_SERVER['DOCUMENT_ROOT'] . '/kursach/server/controllers/UserController.php';
require $_SERVER['DOCUMENT_ROOT'] . '/kursach/server/controllers/FlightController.php';
require $_SERVER['DOCUMENT_ROOT'] . '/kursach/server/controllers/AdminController.php';

class Router
{
     private $routes = [
        '/login' =>
        [
            'controller' => UserController::class,
            'method' => 'getLoginPage'
        ],
        '/register' =>
        [
            'controller' => UserController::class,
            'method' => 'getRegisterPage'
        ],
        '/' =>
        [
            'controller' => UserController::class,
            'method' => 'index'
        ],
        '/user/edit' =>
        [
            'controller' => UserController::class,
            'method' => 'edit'
        ],
        '/user/update' =>
        [
            'controller' => UserController::class,
            'method' => 'update'
        ],
        '/user/profile' =>
        [
            'controller' => UserController::class,
            'method' => 'profile'
        ],
        '/user/login' =>
        [
            'controller' => UserController::class,
            'method' => 'login'
        ],
        '/user/register' =>
        [
            'controller' => UserController::class,
            'method' => 'register'
        ],
        '/user/logout' =>
        [
            'controller' => UserController::class,
            'method' => 'logout'
        ],
        '/user/chooseATicket' =>
        [
            'controller' => UserController::class,
            'method' => 'chooseATicket'
        ],
        '/user/registerForFlight' =>
        [
            'controller' => UserController::class,
            'method' => 'registerForFlight'
        ],
         '/user/getBookedFlights' =>
         [
            'controller' => UserController::class,
            'method' => 'getBookedFlights'
         ],
         '/user/refuseFromFlight' =>
         [
             'controller' => UserController::class,
             'method' => 'refuseFromFlight'
         ],
         '/user/getTicketPdf' =>
         [
             'controller' => UserController::class,
             'method' => 'getTicketPdf'
         ],
         '/flight/getDeparturePoints' =>
         [
             'controller' => FlightController::class,
             'method' => 'getDeparturePoints'
         ],
         '/flight/getArrivalPoints' =>
         [
             'controller' => FlightController::class,
             'method' => 'getArrivalPoints'
         ],
         '/flight/getFlightsByUserChoice' =>
         [
            'controller' => FlightController::class,
            'method' => 'getFlightsByUserChoice'
         ],
         '/flight/getFreePlaces' =>
         [
             'controller' => FlightController::class,
             'method' => 'getFreePlaces'
         ],
         '/admin/main' =>
         [
             'controller' => AdminController::class,
             'method' => 'main'
         ],
         '/admin/trips' =>
         [
             'controller' => AdminController::class,
             'method' => 'getTrips'
         ],
         '/admin/addTrip' =>
         [
             'controller' => AdminController::class,
             'method' => 'addTrip'
         ],
         '/admin/editTrip' =>
         [
             'controller' => AdminController::class,
             'method' => 'editTrip'
         ],
         '/admin/updateTrip' =>
         [
             'controller' => AdminController::class,
             'method' => 'updateTrip'
         ],
         '/admin/deleteTrip' =>
         [
             'controller' => AdminController::class,
             'method' => 'deleteTrip'
         ],
         '/admin/users' =>
         [
             'controller' => AdminController::class,
             'method' => 'getUsers'
         ],
         '/admin/changeUserRole' =>
         [
             'controller' => AdminController::class,
             'method' => 'changeUserRole'
         ]
    ];

    private $controller;
    private $method;
    public function getRoute()
    {
        $request = substr($_SERVER['REQUEST_URI'],15);
        if (array_key_exists($request, $this->routes))
        {
            $this->controller = $this->routes[$request]['controller'];
            $this->method = $this->routes[$request]['method'];
            $this->route();
        }
        else
        {
            $this->error404();
        }
    }

    public function route()
    {
        $controller = new $this->controller();
        $controller->{$this->method}();
    }

    public function error404()
    {
        header("HTTP/1.0 404 Not Found");
    }
}