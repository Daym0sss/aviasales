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
        $loader = new FilesystemLoader($_SERVER['DOCUMENT_ROOT'] . '/kursach/client/views');
        $twig = new Environment($loader);
        $template = $twig->load('index.html.twig');
        echo $template->render([
            'departurePoints' => $departurePoints,
            'arrivalPoints' => $arrivalPoints,
            'user_id' => isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null,
            'name' => isset($_SESSION['name']) ? $_SESSION['name'] : null,
        ]);
        session_write_close();
    }

    public function profile()
    {
        session_start();
        $user = User::getUserById($_SESSION['user_id']);
        $loader = new FilesystemLoader($_SERVER['DOCUMENT_ROOT'] . '/kursach/client/views/user');
        $twig = new Environment($loader);
        $template = $twig->load('profile.html.twig');
        echo $template->render([
            'user' => $user
        ]);
        session_write_close();
    }

    public function getLoginPage()
    {
        $loader = new FilesystemLoader($_SERVER['DOCUMENT_ROOT'] . '/kursach/client/views/user');
        $twig = new Environment($loader);
        $template = $twig->load('login.html.twig');
        echo $template->render();
    }

    public function getRegisterPage()
    {
        $loader = new FilesystemLoader($_SERVER['DOCUMENT_ROOT'] . '/kursach/client/views/user');
        $twig = new Environment($loader);
        $template = $twig->load('register.html.twig');
        echo $template->render();
    }

    /*
     * data = [
     *  'login' => '',
     *  'password' => '',
     * ]
     */
    public function login()
    {
        $login = $_POST['login'];
        $password = $_POST['password'];

        $user = User::loginUser($login, $password);
        if ($user != null)
        {
            session_start();
            $_SESSION['user_id'] = $user->user_id;
            $_SESSION['name'] = $user->login;
            session_write_close();
            echo json_encode(['message' => 'You are logged in']);
        }
        else
        {
            echo json_encode(['message' => 'Wrong credentials']);
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
        $user = new User($_POST['firstName'], $_POST['secondName'], $_POST['lastName'], $_POST['login'], $_POST['password'], $_POST['email'], $_POST['phone']);
        $user_id = $user->registerNewUser();
        if ($user_id)
        {
            session_start();
            $_SESSION['user_id'] = $user_id;
            $_SESSION['name'] = $user->login;
            session_write_close();
            echo json_encode(['message' => 'You have been registered']);
        }
        else
        {
            echo json_encode(['message' => 'User with this login already exists']);
        }
    }

    public function logout()
    {
        session_start();
        unset($_SESSION['user_id']);
        unset($_SESSION['name']);
        session_write_close();
        header('Location: http://localhost/kursach/server/');
    }

    /*
     * 'data' = [
     *  'token' => ''
     * ]
     */
    public function getBookedFlights()
    {
        session_start();
        /*if ($_POST['period'] == "all")
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
        }*/
        $flights = User::getFlights($_SESSION['user_id'], "all");
        session_write_close();

        echo json_encode(['flights' => $flights]);
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
            'user_id' => $_POST['user_id'],
            'class' => $_POST['class'],
        ]);
    }

    public function registerForFlight()
    {
        $counter = 1;
        while(isset($_POST['firstName' . $counter]))
        {
            $passanger_params = [];
            $passanger_params['firstName'] = $_POST['firstName' . $counter];
            $passanger_params['secondName'] = $_POST['secondName' . $counter];
            $passanger_params['lastName'] = $_POST['lastName' . $counter];
            $passanger_params['passport_num'] = $_POST['passport_num' . $counter];
            $passanger_params['type'] = $_POST['passanger_type' . $counter];
            $passanger_params['has_luggage'] = isset($_POST['hasLuggage' . $counter]);
            $passanger_params['user_id'] = $_POST['user_id'];
            $passanger_params['trip_id'] = $_POST['trip_id'];
            $passanger_params['place_num'] = $_POST['place_letter' . $counter] . $_POST['place_num' . $counter];
            $passanger_params['class'] = $_POST['class'];
            Passanger::addPassanger($passanger_params);
            $counter++;
        }
        $user = User::getUserById($_POST['user_id']);

        Trip::reduceFreePlacesCount($_POST['trip_id'], $counter - 1, $_POST['class']);
        $counter = 1;
        while(isset($_POST['place_nun' . $counter]))
        {
            Trip::takeFlightPlace([
                'trip_id' => $_POST['trip_id'],
                'class' => $_POST['class'],
                'place_num' => $_POST['place_num' . $counter]
            ]);
            $counter++;
        }
    }

    public static function getAllUsers()
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