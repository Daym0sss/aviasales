<?php

namespace models;

use app\Database;
use Cassandra\Date;

class User
{
    private $user_id;
    private $firstName;
    private $secondName;
    private $lastName;
    private $login;
    private $password;
    private $email;
    private $phone;
    private $role_id;

    public function __construct($firstName, $secondName, $lastName, $login, $password, $email, $phone)
    {
        $this->firstName = $firstName;
        $this->secondName = $secondName;
        $this->lastName = $lastName;
        $this->login = $login;
        $this->password = $password;
        $this->email = $email;
        $this->phone = $phone;
    }

    public function __get($property)
    {
        return $this->$property;
    }

    public function __set($property, $value)
    {
        $this->$property = $value;

        return $this;
    }

    public static function getToken($data)
    {
        return base64_encode($data);
    }

    public static function decodeToken($token)
    {
        return json_decode(base64_decode($token), true);
    }

    public static function getUserById($user_id)
    {
        $connection = Database::getInstance();
        $sql = "SELECT * FROM users WHERE user_id = " . $user_id;
        $result = $connection->query($sql);
        foreach ($result as $row)
        {
            return $row;
        }

        return null;
    }
    public static function loginUser($login, $password)
    {
        $connection = Database::getInstance();
        $sql = 'SELECT * FROM users WHERE login="' . $login . '" and password="' . $password . '"';
        $result = $connection->query($sql);
        foreach ($result as $row)
        {
            $user = new User($row['firstName'], $row['secondName'], $row['lastName'], $row['login'], $row['password'], $row['email'], $row['phone']);
            $user->user_id = $row['user_id'];
            $user->role_id = $row['role_id'];
            break;
        }

        return isset($user) ? $user : null;
    }

    public function registerNewUser()
    {
        $connection = Database::getInstance();
        $sql = "SELECT * FROM users";
        $result = $connection->query($sql);
        foreach ($result as $row)
        {
            if ($row['login'] == $this->login)
            {
                return null;
            }
        }

        $sql = 'INSERT INTO users(firstName, secondName, lastName, login, password, email, phone, role_id) VALUES("' . $this->firstName
            . '", "' . $this->secondName . '", "' . $this->lastName . '", "' . $this->login . '", "' . $this->password
            . '", "' . $this->email . '", "' . $this->phone . '", 3)';
        $connection->query($sql);

        $sql = 'SELECT * FROM users WHERE login = "' . $this->login . '"';
        $result = $connection->query($sql);
        foreach ($result as $row)
        {
            return $row['user_id'];
        }
    }

    public static function getFlights($user_id, $sign)
    {
        $flights = [];
        $passangers = Passanger::getPassangersByUserId($user_id);
        foreach ($passangers as $passanger)
        {
            if (!isset($flights[$passanger['trip_id']]))
            {
                $flights[$passanger['trip_id']] = [];
            }
        }

        foreach ($flights as $trip_id => $flight)
        {
            $flights[$trip_id]['trip_information'] = Trip::getTrip($trip_id);
        }

        foreach ($flights as $trip_id => $flight)
        {
            $flights[$trip_id]['passangers_information'] = Passanger::getPassangersInfo($trip_id);
        }

        $currentDate = strtotime(\date("Y-m-d"));
        if ($sign == null)
        {
            return $flights;
        }
        else if ($sign == "<")
        {
            $result = [];
            foreach ($flights as $flight)
            {
                $flightDate = strtotime($flight["trip_information"]["departure_date"]);
                if ($flightDate < $currentDate)
                {
                    $result []= $flight;
                }
            }

            return $result;
        }
        else
        {
            $result = [];
            foreach ($flights as $flight)
            {
                $flightDate = strtotime($flight["trip_information"]["departure_date"]);
                if ($flightDate > $currentDate)
                {
                    $result []= $flight;
                }
            }

            return $result;
        }
    }

    public static function updateUser($data)
    {
        $firstName = $_POST['firstName'];
        $secondName = $_POST['secondName'];
        $lastName = $_POST['lastName'];
        $phone = $_POST['phone'];
        $email = $_POST['email'];
        $user_id = $_POST['user_id'];

        $connection = Database::getInstance();
        $sql = "UPDATE users SET firstName = \"" . $firstName . "\", secondName = \"" . $secondName . "\", lastName = \"" . $lastName . "\", phone = \"" . $phone . "\", email = \"" . $email . "\" WHERE user_id = " . $user_id;
        $connection->query($sql);
    }

    public static function refuseFromFlight($data)
    {
        $connection = Database::getInstance();
        $sql = "SELECT * FROM passangers WHERE trip_id = " . $_POST['trip_id'] . " AND user_id = " . $_POST['user_id'];
        $passangers = $connection->query($sql);
        $passangers_count = 0;
        $flight_class = "";
        foreach ($passangers as $row)
        {
            $passangers_count++;
            $flight_class = $row['class'];
        }

        $class_variable = "left_" . $flight_class . "_places_count";
        $trip = Trip::getTrip($_POST['trip_id']);
        $left_places_count = $trip[$class_variable];
        $left_places_count += $passangers_count;

        $sql = "UPDATE trips SET " . $class_variable . " = " . $left_places_count . " WHERE trip_id = " . $_POST['trip_id'];
        $connection->query($sql);

        foreach ($passangers as $passanger)
        {
            $sql = 'DELETE FROM flightPlaces WHERE class = "' . $flight_class . '" AND place_name = "' . $passanger['place_num'] . '" AND flight_id = ' . $_POST['trip_id'];
            $connection->query($sql);
        }

        $sql = "DELETE FROM passangers WHERE user_id = " . $_POST['user_id'] . " AND trip_id = " . $_POST['trip_id'];
        $connection->query($sql);
    }

    public static function has_access($user_id)
    {
        $user = User::getUserById($user_id);
        if ($user['role_id'] != 1)
        {
            return false;
        }
        return true;
    }

    public static function changeRole($data)
    {
        $connection = Database::getInstance();
        $sql = 'UPDATE users SET role_id = ' . $data['role_id'] . ' WHERE user_id = ' . $data['user_id'];
        $connection->query($sql);
    }
}