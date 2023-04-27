<?php

namespace models;

class Role
{
    private $role_id;
    private $role_name;

    public function __get($property)
    {
        return $this->$property;
    }

    public function __set($property, $value)
    {
        $this->$property = $value;

        return $this;
    }
}