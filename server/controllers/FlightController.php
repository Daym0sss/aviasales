<?php

namespace controllers;


use models\Trip;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class FlightController
{
    public function getDeparturePoints()
    {
        if ($_SERVER["REQUEST_METHOD"] == "POST")
        {
            $points = Trip::getDeparturePoints();

            echo json_encode(['departure_points' => $points]);
        }
        else
        {
            header('Location: http://localhost/kursach/server/');
        }
    }

    public function getArrivalPoints()
    {
        if ($_SERVER["REQUEST_METHOD"] == "POST")
        {
            $points = Trip::getArrivalPoints();

            echo json_encode(['arrival_points' => $points]);
        }
        else
        {
            header('Location: http://localhost/kursach/server/');
        }

    }

    /*
     * data = [
     *  'trip_id' => 1,
     *  'class' => ''
     * ]
     */
    public function getFreePlaces()
    {
        if ($_SERVER["REQUEST_METHOD"] == "POST")
        {
            $places = Trip::getFreePlaces($_POST['trip_id'], $_POST['class']);

            echo json_encode(['taken_places' => $places]);
        }
        else
        {
            header('Location: http://localhost/kursach/server/');
        }
    }

    /*
     * data = [
     *  'departurePoint' => ''
     *  'departureDate' => ''
     *  'arrivalPoint' => ''
     *
     *  'defaultCount' => 0
     *  'childCount' => 0
     *  'babyCount' => 0
     *  'class' => ''
     * ]
     */
    public function getFlightsByUserChoice()
    {
        if ($_SERVER["REQUEST_METHOD"] == "POST")
        {
            $flights = Trip::getFlightsByUserChoice($_POST);
            session_start();
            $flight_class_places_variable = $_POST['class'] . "_place_price";
            for($i = 0;$i < count($flights);$i++)
            {
                $flights[$i]["requested_price"] = $flights[$i][$flight_class_places_variable . "_default"] * $_POST['defaultCount']
                    + $flights[$i][$flight_class_places_variable . "_child"] * $_POST['childCount']
                    + $flights[$i][$flight_class_places_variable . "_baby"] * $_POST['babyCount'];
            }

            $loader = new FilesystemLoader($_SERVER['DOCUMENT_ROOT'] . '/kursach/client/views');
            $twig = new Environment($loader);
            $template = $twig->load('flightsFound.html.twig');
            echo $template->render([
                'flights' => $flights,
                'user_id' => isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null,
                'name' => isset($_SESSION['name']) ? $_SESSION['name'] : null,
                'defaultCount' => $_POST['defaultCount'],
                'childCount' => $_POST['childCount'],
                'babyCount' => $_POST['babyCount'],
                'class' => $_POST['class']
            ]);
            session_write_close();
        }
        else
        {
            header('Location: http://localhost/kursach/server/');
        }
    }
}