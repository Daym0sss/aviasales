<?php

namespace app;

class Database
{
    public static $instance;

    public static function getInstance()
    {
        if (self::$instance == null)
        {
            self::$instance = new \mysqli("localhost", "root", "mynewpassword", "kursach");
        }
        return self::$instance;
    }
}