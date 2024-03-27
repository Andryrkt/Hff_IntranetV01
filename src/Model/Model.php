<?php

namespace App\Model;

class Model
{
    protected $connexion;
    /**
     * 
     */
    public function __construct()
    {
        $this->connexion = new Connexion();
    }
}
