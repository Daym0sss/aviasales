<?php

namespace models;


use app\Database;

class Trip
{
    private $trip_id;
    private $departure_country;
    private $departure_city;
    private $departure_airport;
    private $departure_terminal;
    private $departure_date;
    private $departure_time;
    private $arrival_country;
    private $arrival_city;
    private $arrival_airport;
    private $arrival_terminal;
    private $arrival_date;
    private $arrival_time;
    private $luggage_price;
    private $general_econom_places_count;
    private $left_econom_places_count;
    private $econom_place_price_default;
    private $econom_place_price_child;
    private $econom_place_price_baby;
    private $general_comfort_places_count;
    private $left_comfort_places_count;
    private $comfort_place_price_default;
    private $comfort_place_price_child;
    private $comfort_place_price_baby;
    private $general_business_places_count;
    private $left_business_places_count;
    private $business_place_price_default;
    private $business_place_price_child;
    private $business_place_price_baby;
    private $aviaCompany_name;

    public function __get($property)
    {
        return $this->$property;
    }

    public function __set($property, $value)
    {
        $this->$property = $value;

        return $this;
    }

    public static function getArrivalPoints()
    {
        $connection = Database::getInstance();
        $sql = "SELECT * FROM trips";
        $result = $connection->query($sql);
        $points = [];
        foreach ($result as $row)
        {
            if (!in_array($row['arrival_country'] . " " . $row['arrival_city'] . " " . $row['arrival_airport'], $points))
            {
                $points []= $row['arrival_country'] . " " . $row['arrival_city'] . " " . $row['arrival_airport'];
            }
        }

        return $points;
    }

    public static function getDeparturePoints()
    {
        $connection = Database::getInstance();
        $sql = "SELECT * FROM trips";
        $result = $connection->query($sql);
        $points = [];
        foreach ($result as $row)
        {
            if (!in_array($row['departure_country'] . " " . $row['departure_city'] . " " . $row['departure_airport'], $points))
            {
                $points []= $row['departure_country'] . " " . $row['departure_city'] . " " . $row['departure_airport'];
            }
        }

        return $points;
    }

    public static function getFreePlaces($trip_id, $class)
    {
        $connection = Database::getInstance();
        $sql = 'SELECT * FROM trips WHERE trip_id = ' . $trip_id;
        $result = $connection->query($sql);
        $trip = [];
        foreach ($result as $row)
        {
            $trip = $row;
        }

        $places_count = $trip['general_' . $class . '_places_count'];
        $all_places = [];
        $letters = ["A", "B", "C", "D"];
        $i = 1;
        $row_counter = 1;
        while($i <= $places_count)
        {
            $letter_counter = 0;
            while($i <= $places_count && $letter_counter < count($letters))
            {
                $all_places [] = $letters[$letter_counter] . $row_counter;
                $i++;
                $letter_counter++;
            }
            $row_counter++;
        }


        $sql = 'SELECT * FROM flightPlaces where class = "' . $class . '" AND flight_id = ' . $trip_id;
        $result = $connection->query($sql);
        $takenPlaces = [];
        foreach($result as $row)
        {
            $takenPlaces []= $row['place_name'];
        }

        return array_values(array_diff($all_places, $takenPlaces));
    }

    public static function getTrip($trip_id)
    {
        $connection = Database::getInstance();
        $sql = 'SELECT * FROM trips WHERE trip_id=' . $trip_id;
        $result = $connection->query($sql);
        foreach ($result as $row)
        {
            return $row;
        }

        return null;
    }

    public static function getFlightsByUserChoice($flight_params)
    {
        $flights = [];
        $departurePoint = $flight_params['departurePoint'];
        $departureData = explode(' ', $departurePoint);
        $arrivalPoint = $flight_params['arrivalPoint'];
        $arrivalData = explode(' ', $arrivalPoint);

        $departure_country = $departureData[0];
        $departure_city = $departureData[1];
        $departure_airport = "";
        for ($i = 2;$i < count($departureData);$i++)
        {
            $departure_airport .= $departureData[$i] . ' ';
        }
        $departure_airport = substr($departure_airport, 0, strlen($departure_airport) - 1);
        $departure_date = $flight_params['departureDate'];

        $arrival_country = $arrivalData[0];
        $arrival_city = $arrivalData[1];
        $arrival_airport = "";
        for($i = 2;$i < count($arrivalData);$i++)
        {
            $arrival_airport .= $arrivalData[$i] . ' ';
        }
        $arrival_airport = substr($arrival_airport, 0, strlen($arrival_airport) - 1);

        $passangers_count = $flight_params['defaultCount'] + $flight_params['childCount'] + $flight_params['babyCount'];
        $flight_class = $flight_params['class'];
        $flight_class_places_variable = "left_" . $flight_class . "_places_count";

        $connection = Database::getInstance();
        $sql = 'SELECT * FROM trips WHERE departure_country = "' . $departure_country . '" AND departure_city = "' . $departure_city . '" AND departure_airport = "'
            . $departure_airport . '" AND departure_date = "' . $departure_date . '" AND arrival_country = "' . $arrival_country . '" AND arrival_city = "'
            . $arrival_city . '" AND arrival_airport = "' . $arrival_airport . '" AND ' . $flight_class_places_variable . ' >= ' . $passangers_count;
        $result = $connection->query($sql);
        foreach ($result as $row)
        {
            $flights []= $row;
        }

        return $flights;
    }

    public static function reduceFreePlacesCount($trip_id, $passangers_count, $flight_class)
    {
        $key = "left_" . $flight_class . "_places_count";
        $connection = Database::getInstance();
        $trip = Trip::getTrip($trip_id);
        $current_free_places = $trip[$key];
        $current_free_places -= $passangers_count;
        $sql = 'UPDATE trips SET ' . $key . ' = ' . $current_free_places . ' WHERE trip_id = ' . $trip_id;
        $connection->query($sql);
    }

    public static function takeFlightPlace($place)
    {
        $connection = Database::getInstance();
        $sql ='INSERT INTO flightPlaces(flight_id, class, place_name) VALUES(' . $place['trip_id'] . ', "' . $place['class'] . '", "' . $place['place_num'] . '")';
        $connection->query($sql);
    }
    public static function getAllTrips()
    {
        $trips = [];
        $connection = Database::getInstance();
        $sql = "SELECT * FROM trips";
        $result = $connection->query($sql);
        foreach ($result as $row)
        {
            $trips []= $row;
        }

        return $trips;
    }

    public static function addTrip($data)
    {
        $connection = Database::getInstance();
        $sql = 'INSERT INTO trips(departure_country, departure_city, departure_airport, departure_terminal, departure_date, departure_time, arrival_country, '
            . 'arrival_city, arrival_airport, arrival_terminal, arrival_date, arrival_time, luggage_price, general_econom_places_count, left_econom_places_count, '
            . 'econom_place_price_default, econom_place_price_child, econom_place_price_baby, general_comfort_places_count, left_comfort_places_count, comfort_place_price_default, '
            . 'comfort_place_price_child, comfort_place_price_baby, general_business_places_count, left_business_places_count, business_place_price_default, '
            . 'business_place_price_child, business_place_price_baby, aviaCompany_name) VALUES("' . $data['departure_country'] . '", "'
            . $data['departure_city'] . '", "' . $data['departure_airport'] . '", "' . $data['departure_terminal'] . '", "' . $data['departure_date'] . '", "'
            . $data['departure_time'] . '", "' . $data['arrival_country'] . '", "' . $data['arrival_city'] . '", "' . $data['arrival_airport'] . '", "'
            . $data['arrival_terminal'] . '", "' . $data['arrival_date'] . '", "' . $data['arrival_time'] . '", ' . $data['luggage_price'] . ', ' . $data['general_econom_places_count']
            . ', ' . $data['general_econom_places_count'] . ', ' . $data['econom_place_price_default'] . ', ' . $data['econom_place_price_child'] . ', ' . $data['econom_place_price_baby'] . ', ' . $data['general_comfort_places_count']
            . ', ' . $data['general_comfort_places_count'] . ', ' . $data['comfort_place_price_default'] . ', ' . $data['comfort_place_price_child'] . ', ' . $data['comfort_place_price_baby'] . ',' . $data['general_business_places_count']
            . ', ' . $data['general_business_places_count'] . ', ' . $data['business_place_price_default'] . ', ' . $data['business_place_price_child'] . ', ' . $data['business_place_price_baby'] . ', "' . $data['aviaCompany_name'] . '")';

        $connection->query($sql);
    }

    public static function deleteTrip($trip_id)
    {
        $connection = Database::getInstance();
        $sql = "DELETE FROM passangers WHERE trip_id = " . $trip_id;
        $connection->query($sql);

        $sql = "DELETE FROM flightPlaces WHERE flight_id = " . $trip_id;
        $connection->query($sql);

        $sql = 'DELETE FROM trips WHERE trip_id = ' . $trip_id;
        $connection->query($sql);
    }

    public static function updateTrip($data)
    {
        $connection = Database::getInstance();
        $sql = 'UPDATE trips SET departure_date = "' . $data['departure_date'] . '", departure_time = "' . $data['departure_time'] . '", departure_terminal = "'
            . $data['departure_terminal'] . '", arrival_date = "' . $data['arrival_date'] . '", arrival_time = "' . $data['arrival_time'] . '", arrival_terminal = "'
            . $data['arrival_terminal'] . '", luggage_price = ' . $data['luggage_price'] . ', econom_place_price_default = ' . $data['econom_place_price_default']
            . ', econom_place_price_child = ' . $data['econom_place_price_child'] . ', econom_place_price_baby = ' . $data['econom_place_price_baby']
            . ', comfort_place_price_default = ' . $data['comfort_place_price_default'] . ', comfort_place_price_child = ' . $data['comfort_place_price_child']
            . ', comfort_place_price_baby = ' . $data['comfort_place_price_baby'] . ', business_place_price_default = ' . $data['business_place_price_default']
            . ', business_place_price_child = ' . $data['business_place_price_child'] . ', business_place_price_baby = ' . $data['business_place_price_baby']
            . ' WHERE trip_id = ' . $data['trip_id'];

        $connection->query($sql);
    }
}