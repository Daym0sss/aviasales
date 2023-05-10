<?php

namespace controllers;


use app\Database;
use models\Passanger;
use models\Trip;
use models\User;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

require $_SERVER['DOCUMENT_ROOT'] . '/kursach/server/app/Database.php';
require $_SERVER['DOCUMENT_ROOT'] . '/kursach/server/models/Trip.php';
require $_SERVER['DOCUMENT_ROOT'] . '/kursach/server/models/User.php';
require $_SERVER['DOCUMENT_ROOT'] . '/kursach/server/models/Passanger.php';

class UserController
{
    public function index()
    {
        session_start();
        $departurePoints = Trip::getDeparturePoints();
        $arrivalPoints = Trip::getArrivalPoints();
        if (isset($_SESSION['user_id']))
        {
            $role_id = User::getUserById($_SESSION['user_id'])['role_id'];
        }
        else
        {
            $role_id = null;
        }
        $loader = new FilesystemLoader($_SERVER['DOCUMENT_ROOT'] . '/kursach/client/views');
        $twig = new Environment($loader);
        $template = $twig->load('index.html.twig');
        echo $template->render([
            'departurePoints' => $departurePoints,
            'arrivalPoints' => $arrivalPoints,
            'user_id' => isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null,
            'name' => isset($_SESSION['name']) ? $_SESSION['name'] : null,
            'role_id' => $role_id
        ]);
        session_write_close();
    }

    public function profile()
    {
        session_start();
        if (isset($_SESSION['user_id']))
        {
            $user = User::getUserById($_SESSION['user_id']);
            $loader = new FilesystemLoader($_SERVER['DOCUMENT_ROOT'] . '/kursach/client/views/user');
            $twig = new Environment($loader);
            $template = $twig->load('profile.html.twig');
            echo $template->render([
                'user' => $user,
                'user_id' => $_SESSION['user_id'],
                'name' => $_SESSION['name']
            ]);
            session_write_close();
        }
        else
        {
            header('Location: http://localhost/kursach/server/');
        }
    }

    public function checkIfAuthorized()
    {
        if ($_SERVER['REQUEST_METHOD'] == "POST")
        {
            session_start();
            $data = [];
            if (isset($_SESSION['user_id']))
            {
                $data['user_id'] = $_SESSION['user_id'];
                $data['role'] = User::getUserById($_SESSION['user_id'])['role_id'];
            }
            else
            {
                $data['user_id'] = null;
                $data['role'] = null;
            }
            session_write_close();

            echo json_encode(['data' => $data]);
        }
        else
        {
            header('Location: http://localhost/kursach/server/');
        }
    }

    public function getLoginPage()
    {
        session_start();
        $loader = new FilesystemLoader($_SERVER['DOCUMENT_ROOT'] . '/kursach/client/views/user');
        $twig = new Environment($loader);
        $template = $twig->load('login.html.twig');
        echo $template->render([
            'user_id' => isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null,
            'name' => isset($_SESSION['name']) ? $_SESSION['name'] : null,
        ]);
        session_write_close();
    }

    public function getRegisterPage()
    {
        session_start();
        $loader = new FilesystemLoader($_SERVER['DOCUMENT_ROOT'] . '/kursach/client/views/user');
        $twig = new Environment($loader);
        $template = $twig->load('register.html.twig');
        echo $template->render([
            'user_id' => isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null,
            'name' => isset($_SESSION['name']) ? $_SESSION['name'] : null,
        ]);
        session_write_close();
    }

    /*
     * data = [
     *  'login' => '',
     *  'password' => '',
     * ]
     */
    public function login()
    {
        if ($_SERVER['REQUEST_METHOD'] == "POST")
        {
            $login = $_POST['login'];
            $password = $_POST['password'];

            $user = User::loginUser($login, $password);
            if ($user != null)
            {
                session_start();
                $_SESSION['user_id'] = $user->user_id;
                $_SESSION['name'] = $user->firstName . " " . $user->lastName;
                session_write_close();
                echo json_encode(['message' => 'You are logged in']);
            }
            else
            {
                echo json_encode(['message' => 'Wrong credentials']);
            }
        }
        else
        {
            header('Location: http://localhost/kursach/server/');
        }
    }

    /*
     * data = [
     *  'firstName' => '',
     *  'secondName' => '',
     *  'lastName' => '',
     *  'login' => '',
     *  'password => '',
     *  'email' => '',
     *  'phone' => ''
     * ]
     */
    public function register()
    {
        if ($_SERVER['REQUEST_METHOD'] == "POST")
        {
            $user = new User($_POST['firstName'], $_POST['secondName'], $_POST['lastName'], $_POST['login'], $_POST['password'], $_POST['email'], $_POST['phone']);
            $user_id = $user->registerNewUser();
            if ($user_id)
            {
                session_start();
                $_SESSION['user_id'] = $user_id;
                $_SESSION['name'] = $user->firstName . " " . $user->lastName;
                session_write_close();
                echo json_encode(['message' => 'You have been registered']);
            }
            else
            {
                echo json_encode(['message' => 'User with this login already exists']);
            }
        }
        else
        {
            header('Location: http://localhost/kursach/server/');
        }
    }

    public function logout()
    {
        if ($_SERVER['REQUEST_METHOD'] == "POST")
        {
            session_start();
            unset($_SESSION['user_id']);
            unset($_SESSION['name']);
            session_write_close();
            header('Location: http://localhost/kursach/server/');
        }
        else
        {
            header('Location: http://localhost/kursach/server/');
        }
    }

    /*
     * 'data' = [
     *  'token' => ''
     * ]
     */
    public function getBookedFlights()
    {
        if ($_SERVER['REQUEST_METHOD'] == "POST")
        {
            session_start();
            if ($_POST['period'] == "all")
            {
                $sign = null;
            }
            elseif($_POST['period'] == "previous")
            {
                $sign = "<";
            }
            else
            {
                $sign = ">";
            }
            $flights = User::getFlights($_SESSION['user_id'], $sign);
            session_write_close();

            echo json_encode(['flights' => $flights]);
        }
        else
        {
            header('Location: http://localhost/kursach/server/');
        }
    }

    /*
     * datq = [
     *  'trip_id' => 1,
     *  'token' => '',
     *  'flight_class' => '',
     *  'passangers' => [
     *      'place_num' => ''
     *      'type' => 'default' | 'child' | 'baby'
     *      'has_luggage' => true | false,
     *      'firstName' => '',
     *      'secondName' => '',
     *      'lastName' => '',
     *      'passport_num' => ''
     *   ]
     * ]
     */

    /*
     * data = [
     *  'defaultCount' => 0,
     *  'childCount' => 0,
     *  'babyCount' => 0,
     *  'trip_id' => 1,
     *  'user_id' => 1
     *  'class' => ''
     * ]
     */
    public function chooseATicket()
    {
        if ($_SERVER['REQUEST_METHOD'] == "POST")
        {
            session_start();
            $trip = Trip::getTrip($_POST['trip_id']);
            $passangers = [];
            for($i = 0;$i < $_POST['defaultCount'];$i++)
            {
                $passangers []= [
                    'type' => 'default'
                ];
            }
            for($i = 0;$i < $_POST['childCount'];$i++)
            {
                $passangers []= [
                    'type' => 'child'
                ];
            }
            for($i = 0;$i < $_POST['babyCount'];$i++)
            {
                $passangers []= [
                    'type' => 'baby'
                ];
            }

            $loader = new FilesystemLoader($_SERVER['DOCUMENT_ROOT'] . '/kursach/client/views');
            $twig = new Environment($loader);
            $template = $twig->load('chooseTicket.html.twig');
            echo $template->render([
                'flight' => $trip,
                'passangers' => $passangers,
                'user_id' => $_SESSION['user_id'],
                'name' => $_SESSION['name'],
                'class' => $_POST['class'],
            ]);
            session_write_close();
        }
        else
        {
            header('Location: http://localhost/kursach/server/');
        }
    }

    public function registerForFlight()
    {
        if ($_SERVER['REQUEST_METHOD'] == "POST")
        {
            $passangersPlaces = [];
            $freePlaces = Trip::getFreePlaces($_POST['trip_id'], $_POST['class']);
            $firstPassangerPlace = $freePlaces[0];
            $passangersPlaces []= $firstPassangerPlace;
            $placeNumber = $freePlaces[0][1];
            $letters = ["A", "B", "C", "D"];
            $letter = $freePlaces[0][0];

            for($i = 1;$i < $_POST['passangersCount'] - 1;$i++)
            {
                if ($placeNumber == 4)
                {
                    $placeNumber = 1;
                    if (array_search($letter, $letters) == 3)
                    {
                        $letter = $letters[0];
                    }
                    else
                    {
                        $letter = $letters[array_search($letter, $letters) + 1];
                    }
                }
                else
                {
                    $placeNumber++;
                }
                $passangersPlaces []= $letter . $placeNumber;
            }

            for($counter = 1;$counter < $_POST['passangersCount'];$counter++)
            {
                $passanger_params = [];
                $passanger_params['firstName'] = $_POST['firstName' . $counter];
                $passanger_params['secondName'] = $_POST['secondName' . $counter];
                $passanger_params['lastName'] = $_POST['lastName' . $counter];
                $passanger_params['passport_num'] = $_POST['passport_num' . $counter];
                $passanger_params['type'] = $_POST['passanger_type' . $counter];
                $passanger_params['has_luggage'] = isset($_POST['hasLuggage' . $counter]);
                $passanger_params['user_id'] = (int) $_POST['user_id'];
                $passanger_params['trip_id'] = (int) $_POST['trip_id'];
                $passanger_params['place_num'] = $passangersPlaces[$counter - 1];
                $passanger_params['class'] = $_POST['class'];
                Passanger::addPassanger($passanger_params);
            }

            Trip::reduceFreePlacesCount($_POST['trip_id'], $_POST['passangersCount'] - 1, $_POST['class']);

            for($counter = 1;$counter < $_POST['passangersCount'];$counter++)
            {
                Trip::takeFlightPlace([
                    'trip_id' => $_POST['trip_id'],
                    'class' => $_POST['class'],
                    'place_num' => $passangersPlaces[$counter - 1]
                ]);
            }

            echo json_encode(['message' => 'You have been registered for the flight']);
        }
        else
        {
            header('Location: http://localhost/kursach/server/');
        }
    }

    public function edit()
    {
        if ($_SERVER['REQUEST_METHOD'] == "POST")
        {
            session_start();
            $user_id = $_POST['user_id'];
            $loader = new FilesystemLoader($_SERVER['DOCUMENT_ROOT'] . '/kursach/client/views/user');
            $twig = new Environment($loader);
            $template = $twig->load('edit.html.twig');
            echo $template->render([
                'user' => User::getUserById($user_id),
                'user_id' => $_SESSION['user_id'],
                'name' => $_SESSION['name']
            ]);

            session_write_close();
        }
        else
        {
            header('Location: http://localhost/kursach/server/');
        }
    }

    public function update()
    {
        if ($_SERVER['REQUEST_METHOD'] == "POST")
        {
            User::updateUser($_POST);
            header('Location: http://localhost/kursach/server/user/profile');
        }
        else
        {
            header('Location: http://localhost/kursach/server/');
        }
    }

    public function refuseFromFlight()
    {
        if ($_SERVER['REQUEST_METHOD'] == "POST")
        {
            User::refuseFromFlight($_POST);
            header('Location: http://localhost/kursach/server/user/profile');
        }
        else
        {
            header('Location: http://localhost/kursach/server/');
        }
    }

    public function getTicketPdf()
    {
        if ($_SERVER['REQUEST_METHOD'] == "POST")
        {
            User::getTicketPdf($_POST);
        }
        else
        {
            header('Location: http://localhost/kursach/server/');
        }
    }

    public static function getAllUsers()
    {
        if ($_SERVER['REQUEST_METHOD'] == "GET")
        {
            header('Location: http://localhost/kursach/server/');
        }
        else
        {
            $users = [];
            $connection = Database::getInstance();
            $sql = "SELECT * FROM users";
            $result = $connection->query($sql);
            foreach ($result as $row)
            {
                $users [] = $row;
            }

            return $users;
        }
    }
}