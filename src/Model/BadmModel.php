<?php

namespace App\Model;

use Exception;
use App\Model\OdbcCrudModel;

class BadmModel extends Model
{



    // public function recuperationCaracterMaterielAll(): array
    // {
    //     $statement = "select MMAT_DESI, MMAT_NUMMAT, MMAT_NUMSERIE, MMAT_RECALPH, MMAT_MARQMAT, MMAT_DATENTR, YEAR(MMAT_DATEMSER) As Annee_model, MMAT_TYPMAT, MMAT_NUMPARC, MMAT_NOUO from MAT_MAT";


    //     $result = $this->connect->executeQuery($statement);


    //     return $this->connect->fetchResults($result);
    // }

    // public function recupIdMateriel(int $idMateriel, string $numSerie): array
    // {
    //     $statement = "select MMAT_DESI, MMAT_NUMMAT, MMAT_NUMSERIE, MMAT_RECALPH, MMAT_MARQMAT, MMAT_DATENTR, YEAR(MMAT_DATEMSER) As Annee_model, MMAT_TYPMAT, MMAT_NUMPARC, MMAT_NOUO from MAT_MAT  where  MMAT_NUMMAT = '" . $idMateriel . "' ";


    //     $result = $this->connect->executeQuery($statement);


    //     return $this->connect->fetchResults($result);
    // }

    // public function recupNumParc(string $numParc): array
    // {
    //     $statement = "select MMAT_DESI, MMAT_NUMMAT, MMAT_NUMSERIE, MMAT_RECALPH, MMAT_MARQMAT, MMAT_DATENTR, YEAR(MMAT_DATEMSER) As Annee_model, MMAT_TYPMAT, MMAT_NUMPARC, MMAT_NOUO from MAT_MAT  where MMAT_RECALPH = '" . $numParc . "'  ";


    //     $result = $this->connect->executeQuery($statement);


    //     return $this->connect->fetchResults($result);
    // }

    // public function recupNumSerie(string $numSerie): array
    // {
    //     $statement = "select MMAT_DESI, MMAT_NUMMAT, MMAT_NUMSERIE, MMAT_RECALPH, MMAT_MARQMAT, MMAT_DATENTR, YEAR(MMAT_DATEMSER) As Annee_model, MMAT_TYPMAT, MMAT_NUMPARC, MMAT_NOUO from MAT_MAT  where MMAT_RECALPH = '" . $numSerie . "'  ";


    //     $result = $this->connect->executeQuery($statement);


    //     return $this->connect->fetchResults($result);
    // }

    // public function amortissement(): array
    // {
    //     $statement = "select SUM(MOFI_MT) AS somme_totale   from MAT_OFI";

    //     $result = $this->connect->executeQuery($statement);


    //     return $this->connect->fetchResults($result);
    // }

    // public function recupheureKilomettreMachine()
    // {
    //     $statement = "select MHIR_COMPTEUR, MHIR_CUMCOMP  from MAT_HIR";

    //     $result = $this->connect->executeQuery($statement);


    //     return $this->connect->fetchResults($result);
    // }

    /**
     * sql server
     */
    public function recupTypeMouvement(): array
    {
        $statement  = "SELECT Description FROM Type_Mouvement";
        $execTypeDoc = $this->connexion->query($statement);
        $tab = [];
        while ($donnee = odbc_fetch_array($execTypeDoc)) {
            $tab[] = $donnee;
        }
        return $tab;
    }


    /**
     * informix
     */
    public function recupAgence(): array
    {
        $statement = "SELECT DISTINCT asuc_num, asuc_lib 
        from agr_succ  where asuc_numsoc = 'HF' AND asuc_num IN ('01', '40', '50','90','91','92')
        order by asuc_num";

        $result = $this->connect->executeQuery($statement);


        return $this->connect->fetchResults($result);
    }

    function convertirEnUtf8($element)
    {
        if (is_array($element)) {
            foreach ($element as $key => $value) {
                $element[$key] = $this->convertirEnUtf8($value);
            }
        } elseif (is_string($element)) {
            return mb_convert_encoding($element, 'UTF-8', 'ISO-8859-1');
        }
        return $element;
    }


    /**
     * Informix
     */


    public function recupeServiceDestinataire()
    {
        $statement = "SELECT DISTINCT
         case  when mmat_succ in (select asuc_parc from agr_succ) then asuc_num else mmat_succ end as agence,
        trim(asuc_lib)||'-'||case (select sce.atab_lib from mmo_imm, agr_tab as sce where mimm_soc = mmat_soc and mimm_nummat = mmat_nummat and sce.atab_code = mimm_service and sce.atab_nom='SER') 
        when null then 'COMMERCIAL' 
        else(select sce.atab_lib from mmo_imm, agr_tab as sce where mimm_soc = 'HF' and mimm_nummat = mmat_nummat and sce.atab_code = mimm_service and sce.atab_nom='SER')
        end as service,
        
        case (select mimm_service  from mmo_imm where mimm_soc = mmat_soc and mimm_nummat = mmat_nummat) when null then 'COM' 
        else(select mimm_service  from mmo_imm where mimm_soc = mmat_soc and mimm_nummat = mmat_nummat)
        end as code_service

        from mat_mat, agr_succ, outer mat_bil
        WHERE (MMAT_SUCC in ('01', '40', '50','90','91','92') or MMAT_SUCC IN (SELECT ASUC_PARC FROM AGR_SUCC WHERE ASUC_NUM IN ('01', '40', '50','90','91','92') ))
        
        
         and trim(MMAT_ETSTOCK) in ('ST','AT')
         and trim(MMAT_AFFECT) in ('IMM','VTE','LCD','SDO')
        and mmat_soc = 'HF'
        -- and mmat_marqmat not like 'Z%'
        and (mmat_succ = asuc_num or mmat_succ = asuc_parc)
        and mmat_nummat = mbil_nummat
        and mbil_dateclot = '12/31/1899'
        and mmat_datedisp < '12/31/2999'
        ";

        $result = $this->connect->executeQuery($statement);


        $services = $this->connect->fetchResults($result);

        $tableauUtf8 = $this->convertirEnUtf8($services);

        return $tableauUtf8;
    }

    /**
     * informix
     */
    public function recupeCasierDestinataire()
    {
        $statement = "SELECT distinct
        trim((case  when mmat_succ in (select asuc_parc from agr_succ) then asuc_num else mmat_succ end)||' '||asuc_lib) as agence,
         trim(mmat_numparc) as casier
       
         
         from mat_mat, agr_succ
         WHERE (MMAT_SUCC in ('01', '40', '50','90','91','92') or MMAT_SUCC IN (SELECT ASUC_PARC FROM AGR_SUCC WHERE ASUC_NUM IN ('01', '40', '50','90','91','92') ))
         
         
          and trim(MMAT_ETSTOCK) in ('ST','AT')
          and trim(MMAT_AFFECT) in ('IMM','VTE','LCD','SDO')
         and mmat_soc = 'HF'
         -- and mmat_marqmat not like 'Z%'
         and (mmat_succ = asuc_num or mmat_succ = asuc_parc)
         and mmat_datedisp < '12/31/2999'
         and  trim(mmat_numparc) IS NOT NULL
         ";

        $result = $this->connect->executeQuery($statement);


        $services = $this->connect->fetchResults($result);

        $tableauUtf8 = $this->convertirEnUtf8($services);

        return $tableauUtf8;

        //return $services;
    }


    /**
     * informix
     */
    public function findAll($matricule = '',  $numParc = '', $numSerie = ''): array
    {
        $statement = "SELECT
        case  when mmat_succ in (select asuc_parc from agr_succ) then asuc_num else mmat_succ end as agence,
        trim(asuc_lib)||'-'||case (select sce.atab_lib from mmo_imm, agr_tab as sce where mimm_soc = mmat_soc and mimm_nummat = mmat_nummat and sce.atab_code = mimm_service and sce.atab_nom='SER') 
        when null then 'COMMERCIAL' 
        else(select sce.atab_lib from mmo_imm, agr_tab as sce where mimm_soc = 'HF' and mimm_nummat = mmat_nummat and sce.atab_code = mimm_service and sce.atab_nom='SER')
        end as service,
        
        case (select mimm_service  from mmo_imm where mimm_soc = mmat_soc and mimm_nummat = mmat_nummat) when null then 'COM' 
        else(select mimm_service  from mmo_imm where mimm_soc = mmat_soc and mimm_nummat = mmat_nummat)
        end as code_service,
        trim((select atab_lib from agr_tab where atab_code = mmat_etstock and atab_nom = 'ETM')) as groupe1,
        trim((select atab_lib from agr_tab where atab_code = mmat_affect and atab_nom = 'AFF')) as affectation,
        mmat_marqmat as constructeur,
        --trim(mmat_natmat)||' - '||(select trim(atab_lib) from agr_tab where atab_code = mmat_natmat and atab_nom = 'NAT'),
        trim(mmat_desi) as designation,
        trim(mmat_typmat) as modele,
        mmat_nummat as num_matricule,
        trim(mmat_numserie) as num_serie,
        trim(mmat_recalph) as num_parc ,
        --(select mhir_compteur from mat_hir a where a.mhir_nummat = mmat_nummat and a.mhir_daterel = (select max(b.mhir_daterel) from mat_hir b where b.mhir_nummat = a.mhir_nummat)),
        (select mhir_compteur from mat_hir a where a.mhir_nummat = mmat_nummat and a.mhir_daterel = (select max(b.mhir_daterel) from mat_hir b where b.mhir_nummat = a.mhir_nummat)) as HEURE,
        (select mhir_cumcomp from mat_hir a where a.mhir_nummat = mmat_nummat and a.mhir_daterel = (select max(b.mhir_daterel) from mat_hir b where b.mhir_nummat = a.mhir_nummat)) as KM,
        (select mhir_daterel from mat_hir a where a.mhir_nummat = mmat_nummat and a.mhir_daterel = (select max(b.mhir_daterel) from mat_hir b where b.mhir_nummat = a.mhir_nummat)) as Date_compteur,
        mmat_numparc as casier_emetteur,
        year(mmat_datemser) as annee,
        date(mmat_datentr) as date_achat,
        (select nvl(sum(mofi_mt),0) from mat_ofi where mofi_classe = 30 and mofi_ssclasse in (10,11,12,13,14,16,17,18,19) and mofi_numbil = mbil_numbil and mofi_typmt = 'R' and mofi_lib like 'Prix d''achat') as Prix_achat,
        (select nvl(sum(mofi_mt),0) from mat_ofi where mofi_classe = 30 and mofi_ssclasse = 15 and mofi_numbil = mbil_numbil and mofi_typmt = 'R') as Amortissement,
        (select fcde_lib from frn_cde where fcde_soc = mmat_soc and fcde_numcde = mbil_numcde),
        (select nvl(sum(mofi_mt),0) from mat_ofi where mofi_classe = 40 and mofi_ssclasse in (21,22,23) and mofi_numbil = mbil_numbil and mofi_typmt = 'R') as Charge_Entretien,
        (select fcde_devise from frn_cde where fcde_soc = mmat_soc and fcde_numcde = mmat_numcde),
        (select ffac_txdev from frn_fac, mat_vem WHERE ffac_soc = mmat_soc AND mvem_numcde = mmat_numcde and mvem_nummat = mmat_nummat and mvem_numfac = ffac_numfac),
        --(select fcde_txdev from frn_cde where fcde_soc = mmat_soc and fcde_numcde = mmat_numcde),
        --(select nvl(sum(mofi_mt),0) from mat_ofi where mofi_classe = 30 and mofi_ssclasse = 10 and mofi_numbil = mbil_numbil and mofi_typmt = 'R') as Droit_taxe,
        (select nvl(sum(mofi_mt),0) from mat_ofi where mofi_classe = 30 and mofi_ssclasse in (10,11,12,13,14,16,17,18,19) and mofi_numbil = mbil_numbil and mofi_typmt = 'R') as Droits_Taxe,
        
        (select mtxt_comment from mat_txt where mtxt_code = 'LOC' and mtxt_nummat = mmat_nummat and trim(mtxt_comment)<>' '),
        (select mtxt_comment from mat_txt where mtxt_code = 'CLT' and mtxt_nummat = mmat_nummat and trim(mtxt_comment)<>' ' and mtxt_nolign = 10 ),
        (select commentaire_materiel from hff_lien_materiel where  id_materiel = mmat_nummat),
        (select lien_materiel from hff_lien_materiel where  id_materiel = mmat_nummat),
        
        mmat_nouo,
        
        (select mtxt_comment from mat_txt where mtxt_code = 'PRI' and mtxt_nummat = mmat_nummat and trim(mtxt_comment)<>' '),
        (select mtxt_comment from mat_txt where mtxt_code = 'FLA' and mtxt_nummat = mmat_nummat and trim(mtxt_comment)<>' '),
        trim((select atab_lib from agr_tab where atab_code = mmat_natmat and atab_nom = 'NAT')) as famille,
        trim(mmat_affect) as code_affect
        
        from mat_mat, agr_succ, outer mat_bil
        WHERE (MMAT_SUCC in ('01', '40', '50','90','91','92') or MMAT_SUCC IN (SELECT ASUC_PARC FROM AGR_SUCC WHERE ASUC_NUM IN ('01', '40', '50','90','91','92') ))
        
        
         and trim(MMAT_ETSTOCK) in ('ST','AT')
         and trim(MMAT_AFFECT) in ('IMM','VTE','LCD','SDO')
        and mmat_soc = 'HF'
        -- and mmat_marqmat not like 'Z%'
        and (mmat_succ = asuc_num or mmat_succ = asuc_parc)
        and mmat_nummat = mbil_nummat
        and mbil_dateclot = '12/31/1899'
        and mmat_datedisp < '12/31/2999'
         and(('" . $matricule . "' is not null and mmat_nummat ='" . $matricule . "') or('" . $numSerie . "' is not null and mmat_numserie ='" . $numSerie . "') or('" . $numParc . "' is not null and mmat_recalph ='" . $numParc . "'))
      ";

        $result = $this->connect->executeQuery($statement);


        return $this->connect->fetchResults($result);
    }




    function insererDansBaseDeDonnees($tab)
    {
        $sql = "INSERT INTO Demande_Mouvement_Materiel (
            Numero_Demande_BADM,
            Code_Mouvement,
            ID_Materiel,
            Nom_Session_Utilisateur,
            Date_Demande,
            Heure_Demande,
            Agence_Service_Emetteur,
            Casier_Emetteur,
            Agence_Service_Destinataire,
            Casier_Destinataire,
            Motif_Arret_Materiel,
            Etat_Achat,
            Date_Mise_Location,
            Cout_Acquisition,
            Amortissement,
            Valeur_Net_Comptable,
            Nom_Client,
            Modalite_Paiement,
            Prix_Vente_HT,
            Motif_Mise_Rebut,
            Heure_machine,
            KM_machine,
            Code_Statut
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        // Exécution de la requête
        $stmt = odbc_prepare($this->connexion->connect(), $sql);
        if (!$stmt) {
            echo "Erreur de préparation : " . odbc_errormsg($this->connexion->connect());
            return;
        }

        $success = odbc_execute($stmt, array_values($tab));

        // if ($success) {
        //     echo "Données insérées avec succès.";
        // } else {
        //     echo "Erreur lors de l'insertion des données : " . odbc_errormsg($this->connexion->connect());
        // }
    }





    /**
     * @Andryrkt 
     * cette fonction récupère les données dans la base de donnée  
     * rectifier les caractère spéciaux et return un tableau
     * pour listeDomRecherhce
     * limiter l'accées des utilisateurs
     */
    public function RechercheBadmModelAll(): array
    {
        $sql = $this->connexion->query("SELECT 
        dmm.ID_Demande_Mouvement_Materiel, 
        sd.Description AS Statut,
        dmm.Numero_Demande_BADM, 
        dmm.Code_Mouvement, 
        dmm.ID_Materiel,
        dmm.Date_Demande,
        dmm.Agence_Service_Emetteur, 
        dmm.Casier_Emetteur,
        dmm.Agence_Service_Destinataire ,
        dmm.Casier_Destinataire, 
        dmm.Motif_Arret_Materiel, 
        dmm.Etat_Achat, 
        dmm.Date_Mise_Location, 
        dmm.Cout_Acquisition, 
        dmm.Amortissement, 
        dmm.Valeur_Net_Comptable, 
        dmm.Nom_Client, 
        dmm.Modalite_Paiement, 
        dmm.Prix_Vente_HT, 
        dmm.Motif_Mise_Rebut, 
        dmm.Heure_machine, 
        dmm.KM_machine,
        FROM Demande_Mouvement_Materiel dmm,  Statut_demande sd
        WHERE dmm.Code_Statut = sd.Code_Statut
        AND sd.Code_Application = 'BDM'                                                                     
        ORDER BY Numero_Demande_BADM DESC
    
    ");


        // Définir le jeu de caractères source et le jeu de caractères cible

        $tab = [];
        while ($donner = odbc_fetch_array($sql)) {

            $tab[] = $donner;
        }


        // Parcourir chaque élément du tableau $tab
        foreach ($tab as $key => &$value) {
            // Parcourir chaque valeur de l'élément et nettoyer les données
            foreach ($value as &$inner_value) {
                $inner_value = $this->clean_string($inner_value);
            }
        }


        $this->TestCaractereSpeciaux($tab);


        return $this->decode_entities_in_array($tab);
    }


    private function clean_string($string)
    {
        return mb_convert_encoding($string, 'UTF-8', 'ISO-8859-1');
    }

    private function TestCaractereSpeciaux(array $tab)
    {
        function contains_special_characters($string)
        {
            // Expression régulière pour vérifier les caractères spéciaux
            return preg_match('/[^\x20-\x7E\t\r\n]/', $string);
        }

        // Parcours de chaque élément du tableau $tab
        foreach ($tab as $key => $value) {
            // Parcours de chaque valeur de l'élément
            foreach ($value as $inner_value) {
                // Vérification de la présence de caractères spéciaux
                if (contains_special_characters($inner_value)) {
                    echo "Caractère spécial trouvé dans la valeur : $inner_value<br>";
                }
            }
        }
    }

    /**
     * c'est une foncion qui décode les caractères speciaux en html
     */
    private function decode_entities_in_array($array)
    {
        // Parcourir chaque élément du tableau
        foreach ($array as $key => $value) {
            // Si la valeur est un tableau, appeler récursivement la fonction
            if (is_array($value)) {
                $array[$key] = $this->decode_entities_in_array($value);
            } else {
                // Si la valeur est une chaîne, appliquer la fonction decode_entities()
                $array[$key] = html_entity_decode($value);
            }
        }
        return $array;
    }
}
