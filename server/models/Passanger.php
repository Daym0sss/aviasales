<?php

namespace models;


use app\Database;

class Passanger
{
    private $passanger_id;
    private $user_id;
    private $trip_id;
    private $place_num;
    private $is_default;
    private $is_child;
    private $is_baby;
    private $has_luggage;
    private $firstName;
    private $secondName;
    private $lastName;
    private $passport_num;


    public function __get($property)
    {
        return $this->$property;
    }

    public function __set($property, $value)
    {
        $this->$property = $value;

        return $this;
    }

    public static function getPassangersByUserId($user_id)
    {
        $connection = Database::getInstance();
        $sql = "SELECT * FROM passangers WHERE user_id=" . $user_id;
        $result = $connection->query($sql);
        $passangers = [];
        foreach ($result as $row)
        {
            $passangers []= $row;
        }

        return $passangers;
    }

    public static function getPassangersInfo($trip_id)
    {
        $connection = Database::getInstance();
        $sql = "SELECT * FROM passangers WHERE trip_id=" . $trip_id;
        $result = $connection->query($sql);
        $passangers = [];
        foreach ($result as $row)
        {
            $passangers []= $row;
        }

        return $passangers;
    }

    public static function addPassanger($passanger_params)
    {
        if ($passanger_params['type'] == 'default')
        {
            $is_default = 1;
            $is_child = 0;
            $is_baby = 0;
        }
        else if ($passanger_params['type'] == 'child')
        {
            $is_default = 0;
            $is_child = 1;
            $is_baby = 0;
        }
        else
        {
            $is_default = 0;
            $is_child = 0;
            $is_baby = 1;
        }

        if ($passanger_params['has_luggage'])
        {
            $passanger_params['has_luggage'] = 1;
        }
        else
        {
            $passanger_params['has_luggage'] = 0;
        }

        $connection = Database::getInstance();
        $sql = "INSERT INTO passangers(user_id, trip_id, place_num, is_default, is_child, is_baby, has_luggage, firstName, secondName, lastName, passport_num, class) "
            . "VALUES(" . $passanger_params['user_id'] . ", " . $passanger_params['trip_id'] . ", \"" . $passanger_params['place_num'] . "\", " . $is_default . ", "
            . $is_child . ", " . $is_baby . ", " . $passanger_params['has_luggage'] . ", \"" . $passanger_params['firstName'] . "\", \"" . $passanger_params['secondName']
            . "\", \"" . $passanger_params['lastName'] . "\", \"" . $passanger_params['passport_num'] . "\", \"" . $passanger_params['class'] . "\")";

        $connection->query($sql);
    }
}