<?php

namespace App\Model\dit;

use App\Model\Model;
use App\Model\Traits\ConversionModel;
use App\Service\GlobalVariablesService;

class DitDevisSoumisAValidationModel extends Model
{
    use ConversionModel;
    
    public function recupNumeroClient(string $numDevis)
    {
        $statement = " SELECT seor_numcli as numero_client
                        FROM sav_eor
                        WHERE seor_serv = 'DEV'
                        AND seor_numor = '".$numDevis."'
        ";

        $result = $this->connect->executeQuery($statement);

        $data = $this->connect->fetchResults($result);

        return $this->convertirEnUtf8($data);
    }

    public function recupNomClient(string $numDevis)
    {
        $statement = " SELECT TRIM(seor_nomcli) as nom_client
                        FROM sav_eor
                        WHERE seor_serv = 'DEV'
                        AND seor_numor = '".$numDevis."'
        ";

        $result = $this->connect->executeQuery($statement);

        $data = $this->connect->fetchResults($result);

        return $this->convertirEnUtf8($data);
    }

    public function recupNumeroDevis($numDit)
    {
        $statement = "SELECT  seor_numor  as numDevis
                from sav_eor
                where seor_refdem = '".$numDit."'
                AND seor_serv = 'DEV'
                ";

        $result = $this->connect->executeQuery($statement);

        $data = $this->connect->fetchResults($result);

        return $this->convertirEnUtf8($data);
    }

    public function recupNbAchatLocaux(string $numDevis)
    {
        $statement = " SELECT
            count(slor.slor_constp) as nbr_achat_locaux 
            from sav_lor slor
            INNER JOIN sav_eor seor ON slor.slor_numor = seor.seor_numor
            where slor.slor_constp in (".GlobalVariablesService::get('achat_locaux').")
            and seor.seor_numor = '".$numDevis."'
        ";

        $result = $this->connect->executeQuery($statement);

        $data = $this->connect->fetchResults($result);

        return $this->convertirEnUtf8($data);
    }

    public function recupNbPieceMagasin(string $numDevis)
    {
        $statement = " SELECT
            count(slor.slor_constp) as nbr_sortie_magasin 
            from sav_lor slor
            INNER JOIN sav_eor seor ON slor.slor_numor = seor.seor_numor
            where slor.slor_constp in (".GlobalVariablesService::get('pieces_magasin').") 
            and slor.slor_typlig = 'P' 
            and seor.seor_numor = '".$numDevis."'
            ";

        $result = $this->connect->executeQuery($statement);

        $data = $this->connect->fetchResults($result);

        return $this->convertirEnUtf8($data);
    }

    /**
     * Methode pour recupérer l'information du devis pour enregistrer dans le base de donnée
     *
     * @param string $numDevis
     * @param boolean $estCeForfait
     * @return void
     */
    public function recupDevisSoumisValidation(string $numDevis)
    {
        $condition = [
            'numDevis' => $numDevis
        ];
        
        $statement = RequestSoumisValidation::buildQuery($condition);

        $result = $this->connect->executeQuery($statement);

        $data = $this->connect->fetchResults($result);

        return $this->convertirEnUtf8($data);
    }


    // /**
    //  * Methode qui recupère seulement le donnée devis forfait pour utiliser dans le pdf devis forfait
    //  *
    //  * @param string $numDevis
    //  * @param boolean $estCeForfait
    //  * @return void
    //  */
    // public function recupDevisSoumisValidationForfait(string $numDevis)
    // {
    //     $condition = [
    //         'numDevis' => $numDevis
    //     ];
        
    //     $statement = RequestSoumisValidation::buildQueryForfait($condition);

    //     $result = $this->connect->executeQuery($statement);

    //     $data = $this->connect->fetchResults($result);

    //     return $this->convertirEnUtf8($data);
    // }


    // /**
    //  * Methode qui recupère seulement le donnée devis forfait pour utiliser dans le pdf devis forfait
    //  *
    //  * @param string $numDevis
    //  * @param boolean $estCeForfait
    //  * @return void
    //  */
    // public function recupDevisSoumisValidationVte(string $numDevis)
    // {
    //     $condition = [
    //         'numDevis' => $numDevis
    //     ];
        
    //     $statement = RequestSoumisValidation::buildQueryForfait($condition);

    //     $result = $this->connect->executeQuery($statement);

    //     $data = $this->connect->fetchResults($result);

    //     return $this->convertirEnUtf8($data);
    // }


    public function recupNbrItvTypeVte($numDevis)
    {
        $statement = " SELECT COUNT(sitv_interv) as nb_vte
                    FROM sav_itv 
                    where sitv_natop = 'VTE' 
                    and sitv_numor = '".$numDevis."'
        ";

        $result = $this->connect->executeQuery($statement);

        $data = $this->connect->fetchResults($result);

        return $this->convertirEnUtf8($data);
    }

    public function recupNbrItvTypeCes($numDevis)
    {
        $statement = " SELECT COUNT(sitv_interv) as nb_ces
                    FROM sav_itv 
                    where sitv_natop = 'CES' 
                    and sitv_numor = '".$numDevis."'
        ";

        $result = $this->connect->executeQuery($statement);

        $data = $this->connect->fetchResults($result);

        return $this->convertirEnUtf8($data);
    }

    public function recupNumDitIps($numDevis)
    {
        $statement = " SELECT trim(seor_refdem) as num_dit
                    FROM sav_eor 
                    where seor_serv='DEV'
                    AND seor_numor = '".$numDevis."' 
        ";

        $result = $this->connect->executeQuery($statement);

        $data = $this->connect->fetchResults($result);

        return $this->convertirEnUtf8($data);
    }

    public function recupServDebiteur($numDevis)
    {
        $statement = " SELECT sitv_succdeb as serv_debiteur
                        FROM sav_itv sitv 
                        inner join sav_eor seor on sitv.sitv_numor = seor.seor_numor and seor.seor_serv ='DEV'
                        WHERE seor.seor_numor = '".$numDevis."'
        ";

        $result = $this->connect->executeQuery($statement);

        $data = $this->connect->fetchResults($result);

        return $this->convertirEnUtf8($data);
    }

    public function recupInfoPieceClient(string $numDevis) 
    {
        $statement = " SELECT 
                        trim(slor_refp) as ref_piece,
                        trim(slor_constp) as constructeur,
                        slor_numcli as num_client,
                        slor_numor as num_devis
                        FROM sav_lor
                        WHERE slor_numor = '".$numDevis."'
        ";

        $result = $this->connect->executeQuery($statement);

        $data = $this->connect->fetchResults($result);

        return $this->convertirEnUtf8($data);
    }

    /**
     * Methode pour recupérer l'evolution de prix de chaque pièce
     *
     * @param array $infoPieceClient
     * @return void
     */
    public function recupInfoPourChaquePiece(array $infoPieceClient)
    {
        $statement = " SELECT FIRST 3 
                    trim(slor_constp) as CST, 
                    trim(slor_refp) as RefPiece, 
                    slor_datel as dateLigne,
                    slor_pxnreel as prixVente,
                    slor_typlig as type_ligne,
                    seor_serv 
                    FROM sav_lor
                    inner join sav_eor 
                    on seor_soc= slor_soc and seor_succ = slor_succ and seor_numor = slor_numor and slor_soc ='HF'
                    WHERE slor_refp = '".$infoPieceClient['ref_piece'] ."'
                    and slor_constp in (".GlobalVariablesService::get('pieces_magasin').")
                    and seor_serv = 'SAV'
                    and slor_pos in('CP','FC') 
                    and slor_numcli = '".$infoPieceClient['num_client']."'
                    ORDER BY slor_datel DESC
        ";

        $result = $this->connect->executeQuery($statement);

        $data = $this->connect->fetchResults($result);

        return $this->convertirEnUtf8($data);
    }
}