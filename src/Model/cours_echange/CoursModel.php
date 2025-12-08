<?php

namespace App\Model\cours_echange;

use App\Model\Model;

class coursModel extends Model
{
    public function recupDatenow()
    {
        $datesys =  date("m/d/Y");
        $condition = " WHERE atxc_datedebut  <= '" . $datesys . "'";
        $statement = " SELECT  first 1 atxc_datedebut as date_deb from agr_txc
        $condition
        group by 1 order by atxc_datedebut desc
        ";
        $result = $this->connect->executeQuery($statement);
        $data = $this->connect->fetchResults($result);
        $resultat = $this->convertirEnUtf8($data);
        return array_column($resultat,"date_deb");
    }

    public function recupDevis()
    {
        $statement = "SELECT  atxc_deviseid||' - '||trim(adev_lib) as devis from agr_txc, agr_dev where atxc_deviseid = adev_code and atxc_deviseidbase = 'AR' and atxc_deviseid <> 'AR'
        group by 1
        ";
        $result = $this->connect->executeQuery($statement);
        $data = $this->connect->fetchResults($result);
        $resultat = $this->convertirEnUtf8($data);
       return array_column($resultat,"devis");
    }

    public function recupCours($date, $devis)
    {
        
        $statement = "SELECT  ROUND(atxc_cours, 2) as cours from agr_txc where atxc_datedebut =  '" . $date . "' 
        AND  atxc_deviseidbase = 'AR' and atxc_deviseid = '" . $devis . "'
        ";
        $result = $this->connect->executeQuery($statement);
        $data = $this->connect->fetchResults($result);
        $resultat = $this->convertirEnUtf8($data);
        return $resultat[0]['cours'];
    }
    public function recupDateSemaineNow(){
        
    }
}
