<?php

namespace controllers;


use models\Trip;
use models\User;

class AdminController
{
    public function main()
    {
        session_start();
        if (isset($_SESSION['user_id']))
        {
            if (User::has_access($_SESSION['user_id']))
            {
                //redirect to admin main
            }
            else
            {
                //redirect to user main
            }
        }
        else
        {
            //redirect to guest main
        }
        session_write_close();
    }

    /*
     * data = [
     *  'trip_id' => 1,
     *  'departure_country' => '',
     *  'departure_city' => '',
     *  'departure_airport' => '',
     *  'departure_terminal' => '',
     *  'departure_date' => '',
     *  'departure_time' => '',
     *  'arrival_country' => '',
     *  'arrival_city' => '',
     *  'arrival_airport' => '',
     *  'arrival_terminal' => '',
     *  'arrival_date' => '',
     *  'arrival_time' => '',
     *  'luggage_price' => 1,
     *  'general_econom_places_count' => 0,
     *  'econom_place_price_default' => 0,
     *  'econom_place_price_child' => 0,
     *  'econom_place_price_baby' => 0
     *  'general_comfort_places_count' => 0,
     *  'comfort_place_price_default' => 0,
     *  'comfort_place_price_child' => 0,
     *  'comfort_place_price_baby' => 0,
     *  'general_business_places_count' => 0,
     *  'business_place_price_default' => 0,
     *  'business_place_price_child' => 0,
     *  'business_place_price_baby' => 0,
     *  'aviaCompany_name' => ''
     * ]
     *
     */
    public function addTrip()
    {
        Trip::addTrip($_POST);
        //redirect to admin trips list
    }

    /*
     * data = [
     *  'trip_id' => 1
     * ]
     */
    public function deleteTrip()
    {
        Trip::deleteTrip($_POST['trip_id']);
        //redirect to admin trips list
    }

    /*
     * data = [
     *  'trip_id' => 1
     * ]
     */
    public function editTrip()
    {
        $trip = Trip::getTrip($_POST['trip_id']);
        //redirect to admin edit page with trip data
    }

    /*
     * data = [
     *  'trip_id' => 1
     *  'departure_date' => '',
     *  'departure_time' => '',
     *  'departure_terminal' => '',
     *  'arrival_date' => '',
     *  'arrival_time' => '',
     *  'arrival_terminal' => '',
     *  'luggage_price' => 1,
     *  'econom_place_price_default' => 0,
     *  'econom_place_price_child' => 0,
     *  'econom_place_price_baby' => 0,
     *  'comfort_place_price_default' => 0,
     *  'comfort_place_price_child' => 0,
     *  'comfort_place_price_baby' => 0,
     *  'business_place_price_default' => 0,
     *  'business_place_price_child' => 0,
     *  'business_place_price_baby' => 0,
     *  'aviaCompany_name' => '',
     * ]
     */
    public function updateTrip()
    {
        Trip::updateTrip($_POST);
        //redirect to admin trips list
    }

    /*
     * data = [
     *  'user_id' => 1,
     *  'role_id' => 1
     * ]
     */
    public function changeUserRole()
    {
        User::changeRole($_POST);
        //redirect to admin users list
    }
}