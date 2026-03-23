<?php

namespace App\Model\badm;


use App\Model\Model;
use App\Model\Traits\ConversionModel;

class BadmRechercheModel extends Model
{

  use ConversionModel;

  public function findDesiSerieParc($matricule = '', string $codeSociete): array
  {
    $statement = "SELECT
                    trim(mmat_desi) as designation,
                    trim(mmat_numserie) as num_serie,
                    trim(mmat_recalph) as num_parc
                  from mat_mat
                WHERE mmat_nummat ='$matricule' and mmat_soc='$codeSociete'
      ";

    $result = $this->connect->executeQuery($statement);

    $data = $this->connect->fetchResults($result);

    return $this->convertirEnUtf8($data);
  }
}
