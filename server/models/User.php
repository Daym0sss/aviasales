<?php

namespace models;

use app\Database;
use Mpdf\Mpdf;

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

    public static function getTicketPdf($data)
    {
        $mpdf = new Mpdf(['tempDir' => $_SERVER['DOCUMENT_ROOT'] . "/kursach/server"]);
        $user_id = $_POST['user_id'];
        $trip_id = $_POST['trip_id'];
        $connection = Database::getInstance();
        $pdf_data = "<h1>Ticket for flight</h1> <br>";

        $pdf_data .= "<h2>Flight information</h2>";
        $trip = Trip::getTrip($trip_id);
        $trip_information = "<table border='1'> <tr> <th>Departure point</th> <th>Arrival point</th> <th>Departure date/time</th> <th>Arrival date/time</th> <th>Luggage price</th> <th>Aviacompany name</th> </tr>";
        $trip_information .= "<tr>";
        $trip_information .= "<td>" . $trip["departure_country"] . "/" . $trip["departure_city"] . "/" . $trip["departure_airport"] . "/Terminal " . $trip["departure_terminal"] . "</td>";
        $trip_information .= "<td>" . $trip["arrival_country"] . "/" . $trip["arrival_city"] . "/" . $trip["arrival_airport"] . "/Terminal " . $trip["arrival_terminal"] . "</td>";
        $trip_information .= "<td>" . $trip["departure_date"] . " " . $trip["departure_time"] . "</td>";
        $trip_information .= "<td>" . $trip["arrival_date"] . " " . $trip["arrival_time"] . "</td>";
        $trip_information .= "<td>" . $trip["luggage_price"] . "</td>";
        $trip_information .= "<td>" . $trip["aviaCompany_name"] . "</td>";
        $trip_information .= "</tr>";
        $trip_information .= "</table> <br>";
        $pdf_data .= $trip_information;

        $pdf_data .= "<h2>Passengers information</h2>";
        $sql = "SELECT * FROM passangers WHERE trip_id = " . $trip_id . " AND user_id = " . $user_id;
        $result = $connection->query($sql);
        $passangers = [];
        foreach ($result as $row)
        {
            $passangers []= $row;
        }

        $passangers_information = "<h3>Flight class: " . $passangers[0]["class"] . "</h3>";
        $passangers_information .= "<table border='1'> <tr> <th>First name</th> <th>Second name</th> <th>Last name</th> <th>Passport number</th> <th>Place number</th> <th>Type</th> <th>Price</th> </tr>";
        $result_price = 0;
        foreach ($passangers as $passanger)
        {
            $passangers_information .= "<tr>";
            $passangers_information .= "<td>" . $passanger["firstName"] . "</td>";
            $passangers_information .= "<td>" . $passanger["secondName"] . "</td>";
            $passangers_information .= "<td>" . $passanger["lastName"] . "</td>";
            $passangers_information .= "<td>" . $passanger["passport_num"] . "</td>";
            $passangers_information .= "<td>" . $passanger["place_num"] . "</td>";
            $passanger_price = 0;
            $price_str = "";
            if ($passanger["is_default"])
            {
                $passangers_information .= "<td> Adult </td>";
                $passanger_price = $trip[$passangers[0]["class"] . "_place_price_default"];
            }
            else if ($passanger["is_child"])
            {
                $passangers_information .= "<td> Child </td>";
                $passanger_price = $trip[$passangers[0]["class"] . "_place_price_child"];
            }
            else
            {
                $passangers_information .= "<td> Baby </td>";
                $passanger_price = $trip[$passangers[0]["class"] . "_place_price_baby"];
            }

            $price_str = $passanger_price;
            $result_price += $passanger_price;
            if ($passanger["has_luggage"])
            {
                $price_str .= " + luggage(" . $trip["luggage_price"] . " BYN)";
                $result_price += $trip["luggage_price"];
            }

            $passangers_information .= "<td>" . $price_str . "</td>";
            $passangers_information .= "</tr>";
        }
        $passangers_information .= "</table>";
        $pdf_data .= $passangers_information . "<br>";
        $pdf_data .= "<h3>Total price: " . $result_price . " BYN</h3>";

        $mpdf->writeHTML($pdf_data);
        $mpdf->Output();
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