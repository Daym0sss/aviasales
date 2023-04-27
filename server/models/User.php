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
                $flights[$passanger['trip_id']] []= [];
            }
        }

        foreach ($flights as $trip_id => $flight)
        {
            $flight['trip_information'] = Trip::getTrip($trip_id);
        }

        foreach ($flights as $trip_id => $flight)
        {
            $flight['passangers_information'] = Passanger::getPassangersInfo($trip_id);
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

    public static function has_access($user_id)
    {
        $user = self::getUserById($user_id);
        if ($user->role_id != 1)
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