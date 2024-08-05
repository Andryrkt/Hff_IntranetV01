<?php

namespace App\Model\magasin;

use App\Controller\Traits\FormatageTrait;
use App\Model\Model;
use App\Model\Traits\ConversionModel;

class MagasinModel extends Model
{ 
    use ConversionModel;
    use FormatageTrait;


    
    public function recupereListeMaterielValider( $numOrValide = "", $criteria = [])
    {
    
        if ($numOrValide === "") {
            $numOrValide = '0';
        }

        if(!empty($criteria['designation'])){
            $designation = " and slor_desi like '%" . $criteria['designation'] . "%'";
        } else {
            $designation = null;
        }
        if(!empty($criteria['referencePiece'])){
            $referencePiece = " and slor_refp like '%" . $criteria['referencePiece'] . "%'";
        } else {
            $referencePiece = null;
        }

        if(!empty($criteria['constructeur'])){
            $constructeur = " and slor_constp  ='" . $criteria['constructeur'] . "'";
        } else {
            $constructeur = null;
        }

        if(!empty($criteria['dateDebut'])){
            $dateDebut = " and slor_datec >='" . $criteria['dateDebut']->format('m/d/Y') ."'";
        } else {
            $dateDebut = null;
        }

        if(!empty($criteria['dateFin'])){
            $dateFin = " and slor_datec <= '" .$criteria['dateFin']->format('m/d/Y')."'";
        } else {
            $dateFin = null;
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
            $designation
            $referencePiece 
            $constructeur 
            $dateDebut
            $dateFin
            and slor_typlig = 'P'
            and slor_pos = 'EC'
            and seor_serv ='SAV'
            --and slor_qtewait > 0
            and slor_constp not like 'Z%'
            and slor_constp not like 'LUB'
        ";

        $result = $this->connect->executeQuery($statement);


        $data = $this->connect->fetchResults($result);

        return $this->convertirEnUtf8($data);
    }


    public function recuperationConstructeur()
    {
        $statement = " SELECT DISTINCT
            trim(slor_constp) as constructeur
           
            from sav_lor 
            inner join sav_eor on seor_soc = slor_soc 
            and seor_succ = slor_succ 
            and seor_numor = slor_numor
            where 
            slor_soc = 'HF'
            and slor_succ = '01'
            and slor_typlig = 'P'
    	    and slor_constp <> '---'
            and slor_constp not like 'Z%'
            and slor_constp not like 'LUB'
            ";

        $result = $this->connect->executeQuery($statement);

        $data = $this->connect->fetchResults($result);

        return array_combine(array_column($this->convertirEnUtf8($data), 'constructeur'), array_column($this->convertirEnUtf8($data), 'constructeur'));
    }


    public function recupNumOr($criteria = [])
    {   
        if(!empty($criteria['niveauUrgence'])){
            $niveauUrgence = " and id_niveau_urgence = '" . $criteria['niveauUrgence']->getId() . "'";
        } else {
            $niveauUrgence = null;
        }

        if(!empty($criteria['numDit'])){
            $numDit = " and numero_demande_dit = '" . $criteria['numDit'] ."'";
        } else {
            $numDit = null;
        }

        if(!empty($criteria['numOr'])){
            $numOr = " and numero_or = '" . $criteria['numOr'] . "'";
        } else {
            $numOr = null;
        }

        $statement = "SELECT 
        numero_or 
        FROM demande_intervention
        WHERE date_validation_or is not null
        and date_validation_or <>'' 
        {$niveauUrgence}
        {$numDit}
        {$numOr}
        ";


        $execQueryNumOr = $this->connexion->query($statement);

        $numOr = array();

        while ($row_num_or = odbc_fetch_array($execQueryNumOr)) {
            $numOr[] = $row_num_or;
        }

        return $numOr;
    }
}