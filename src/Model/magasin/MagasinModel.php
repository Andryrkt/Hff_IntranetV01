<?php

namespace App\Model\magasin;

use App\Model\Model;
use App\Model\Traits\ConversionModel;

class MagasinModel extends Model
{ 
    use ConversionModel;
    
    public function recupereListeMaterielValider( $numOrValide = "")
    {
        
        if ($numOrValide === "") {
            $numOrValide = '0';
        }


        $statement = "SELECT 
            seor_numor as numeroOr,
            trim(slor_constp) as constructeur, 
            trim(slor_refp) as referencePiece, 
            trim(slor_desi) as designationi, 
            slor_qtewait as quantite,
            slor_qteres as quantiteReserver,
            slor_qterea as quantiteLivree,
            slor_qterel as quantiteReliquat,
            slor_datec as dateCreation

            from sav_lor 
            inner join sav_eor on seor_soc = slor_soc 
            and seor_succ = slor_succ 
            and seor_numor = slor_numor
            where 
            slor_soc = 'HF'

            and slor_succ = '01'
            and slor_numor in (". $numOrValide .")
            and slor_typlig = 'P'
            and slor_pos = 'EC'
            and seor_serv ='SAV'
            and slor_qtewait > 0
            and slor_constp not like 'Z%'
            and slor_constp not like 'LUB'
        ";


        $result = $this->connect->executeQuery($statement);


        $data = $this->connect->fetchResults($result);

        return $this->convertirEnUtf8($data);
    }
}