<?php

namespace App\Model;

use App\Model\OdbcCrudModel;

class BadmModel extends Model
{



    public function recuperationCaracterMaterielAll(): array
    {
        $statement = "select MMAT_DESI, MMAT_NUMMAT, MMAT_NUMSERIE, MMAT_RECALPH, MMAT_MARQMAT, MMAT_DATENTR, YEAR(MMAT_DATEMSER) As Annee_model, MMAT_TYPMAT, MMAT_NUMPARC, MMAT_NOUO from MAT_MAT";


        $result = $this->connect->executeQuery($statement);


        return $this->connect->fetchResults($result);
    }

    public function recupIdMateriel(int $idMateriel, string $numSerie): array
    {
        $statement = "select MMAT_DESI, MMAT_NUMMAT, MMAT_NUMSERIE, MMAT_RECALPH, MMAT_MARQMAT, MMAT_DATENTR, YEAR(MMAT_DATEMSER) As Annee_model, MMAT_TYPMAT, MMAT_NUMPARC, MMAT_NOUO from MAT_MAT  where  MMAT_NUMMAT = '" . $idMateriel . "' ";


        $result = $this->connect->executeQuery($statement);


        return $this->connect->fetchResults($result);
    }

    public function recupNumParc(string $numParc): array
    {
        $statement = "select MMAT_DESI, MMAT_NUMMAT, MMAT_NUMSERIE, MMAT_RECALPH, MMAT_MARQMAT, MMAT_DATENTR, YEAR(MMAT_DATEMSER) As Annee_model, MMAT_TYPMAT, MMAT_NUMPARC, MMAT_NOUO from MAT_MAT  where MMAT_RECALPH = '" . $numParc . "'  ";


        $result = $this->connect->executeQuery($statement);


        return $this->connect->fetchResults($result);
    }

    public function recupNumSerie(string $numSerie): array
    {
        $statement = "select MMAT_DESI, MMAT_NUMMAT, MMAT_NUMSERIE, MMAT_RECALPH, MMAT_MARQMAT, MMAT_DATENTR, YEAR(MMAT_DATEMSER) As Annee_model, MMAT_TYPMAT, MMAT_NUMPARC, MMAT_NOUO from MAT_MAT  where MMAT_RECALPH = '" . $numSerie . "'  ";


        $result = $this->connect->executeQuery($statement);


        return $this->connect->fetchResults($result);
    }

    public function amortissement(): array
    {
        $statement = "select SUM(MOFI_MT) AS somme_totale   from MAT_OFI";

        $result = $this->connect->executeQuery($statement);


        return $this->connect->fetchResults($result);
    }

    public function recupheureKilomettreMachine()
    {
        $statement = "select MHIR_COMPTEUR, MHIR_CUMCOMP  from MAT_HIR";

        $result = $this->connect->executeQuery($statement);


        return $this->connect->fetchResults($result);
    }
}
