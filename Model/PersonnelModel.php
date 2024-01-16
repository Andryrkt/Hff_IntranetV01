<?php
class PersonnelModel
{
    private $connexion;
    public function __construct(Connexion $connexion)
    {
        $this->connexion = $connexion;
    }

    public function getDatesystem()
    {
        $d = strtotime("now");
        $Date_system = date("Y-m-d", $d);
        return $Date_system;
    }
}
