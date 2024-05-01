<?php

namespace App\Controller\Traits;

trait IncrementationTrait
{
     /**
     * Incrimentation de Numero_DOM (DOMAnnéeMoisNuméro)
     */
    private function autoINcriment(string $nomDemande)
    {
        //NumDOM auto
        $YearsOfcours = date('y'); //24
        $MonthOfcours = date('m'); //01
        $AnneMoisOfcours = $YearsOfcours . $MonthOfcours; //2401
        //var_dump($AnneMoisOfcours);
        // dernier NumDOM dans la base
        if ($nomDemande === 'BDM') {
            $Max_Num = $this->badm->RecupereNumBDM();
        } elseif ($nomDemande === 'CAS') {
            $Max_Num = $this->casier->RecupereNumCAS()['numCas'];
        } else {
            $Max_Num = $nomDemande . $AnneMoisOfcours . '0000';
        }

        //var_dump($Max_Num);
        //$Max_Num = 'CAS24040000';
        //num_sequentielless
        $vNumSequential =  substr($Max_Num, -4); // lay 4chiffre msincrimente
        //var_dump($vNumSequential);
        $DateAnneemoisnum = substr($Max_Num, -8);
        //var_dump($DateAnneemoisnum);
        $DateYearsMonthOfMax = substr($DateAnneemoisnum, 0, 4);
        //var_dump($DateYearsMonthOfMax);
        if ($DateYearsMonthOfMax == $AnneMoisOfcours) {
            $vNumSequential =  $vNumSequential + 1;
        } else {
            if ($AnneMoisOfcours > $DateYearsMonthOfMax) {
                $vNumSequential = 1;
            }
        }
        //var_dump($vNumSequential);
        $Result_Num = $nomDemande . $AnneMoisOfcours . $this->CompleteChaineCaractere($vNumSequential, 4, "0", "G");
        //var_dump($Result_Num);

        return $Result_Num;
    }
}