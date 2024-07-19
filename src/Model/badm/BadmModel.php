<?php

namespace App\Model\badm;

use PDO;
use PDOException;
use App\Model\Model;
use App\Model\Traits\ConversionModel;


class BadmModel extends Model
{


    use BadmModelTrait;
    use ConversionModel;


    /**
     * Informix: recupère l'agence
     *
     * @return array
     */
    public function recupAgence()
    {
        $statement = "SELECT DISTINCT 
        trim(trim(asuc_num)||' '|| trim(asuc_lib)) as agence 
        from
        agr_succ , agr_tab a
        where asuc_numsoc = 'HF' and a.atab_nom = 'SER'
        and a.atab_code not in (select b.atab_code from agr_tab b where substr(b.atab_nom,10,2) = asuc_num and b.atab_nom like 'SERBLOSUC%')
        and asuc_num in ('01', '20', '30', '40', '50','60','80','90','91','92') 
        order by 1";

        $result = $this->connect->executeQuery($statement);


        $services = $this->connect->fetchResults($result);




        return $this->convertirEnUtf8($services);
    }

    /**
     * convertir en UTF_8
     *
     * @param [type] $element
     * @return void
     */
    private function convertirEnUtf8($element)
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
     *
     * @return array
     */
    public function recupeAgenceServiceDestinataire()
    {

        $statement = "SELECT DISTINCT 
        trim(trim(asuc_num)||' '|| trim(asuc_lib)) as agence, 
        trim(trim(atab_code)||' '|| trim(atab_lib)) as service
        from
        agr_succ , agr_tab a
        where asuc_numsoc = 'HF' and a.atab_nom = 'SER'
        and a.atab_code not in (select b.atab_code from agr_tab b where substr(b.atab_nom,10,2) = asuc_num and b.atab_nom like 'SERBLOSUC%')
        order by 1";

        $result = $this->connect->executeQuery($statement);

        $services = $this->connect->fetchResults($result);

        return $this->convertirEnUtf8($services);

        //return $tableauUtf8;
    }



    /**
     * INformix | recupère le code et le libeler agence
     *
     * @return array
     */
    public function recupeCasierDestinataireInformix()
    {
        $statement = "SELECT distinct
        trim((case  when mmat_succ in (select asuc_parc from agr_succ) then asuc_num else mmat_succ end)) as code_agence,
        trim((case  when mmat_succ in (select asuc_parc from agr_succ) then asuc_num else mmat_succ end)||' '||asuc_lib) as agence
        
         from mat_mat, agr_succ
         WHERE (MMAT_SUCC in ('01','20','30','40', '50','60','80','90','91','92') or MMAT_SUCC IN (SELECT ASUC_PARC FROM AGR_SUCC WHERE ASUC_NUM IN ('01', '20', '30', '40', '50', '60', '80', '90','91','92') ))


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
     * sqlServer: recupération casier
     * @return  array
     */
    public function recupeCasierDestinataireSqlServer()
    {
        $sql = "SELECT 
        cm.Agence_Rattacher, 
        cm.Casier
        FROM 
        Casier_Materiels cm
        WHERE 
        cm.Agence_Rattacher IN ('01','20', '30', '40', '50', '60', '80', '90', '91', '92')";

        $execTypeDoc = $this->connexion->query($sql);
        $tab = [];
        while ($donnee = odbc_fetch_array($execTypeDoc)) {
            $tab[] = $donnee;
        }
        $tableauUtf8 = $this->convertirEnUtf8($tab);
        return $tableauUtf8;
    }

    /**
     * informix
     */
    public function findAll($matricule = '',  $numParc = '', $numSerie = '')
    {
        $statement = "SELECT
        case  when mmat_succ in (select asuc_parc from agr_succ) then asuc_num else mmat_succ end as agence,
        trim(asuc_lib)||'-'||case (select sce.atab_lib from mmo_imm, agr_tab as sce where mimm_soc = mmat_soc and mimm_nummat = mmat_nummat and sce.atab_code = mimm_service and sce.atab_nom='SER') 
        when null then 'COMMERCIAL' 
        else(select sce.atab_lib from mmo_imm, agr_tab as sce where mimm_soc = 'HF' and mimm_nummat = mmat_nummat and sce.atab_code = mimm_service and sce.atab_nom='SER')
        end as service,
        
        case (select mimm_service  from mmo_imm where mimm_soc = mmat_soc and mimm_nummat = mmat_nummat) when null then 'LCD' 
        else(select mimm_service  from mmo_imm where mimm_soc = mmat_soc and mimm_nummat = mmat_nummat)
        end as code_service,
        trim((select atab_lib from agr_tab where atab_code = mmat_etstock and atab_nom = 'ETM')) as groupe1,
        trim((select atab_lib from agr_tab where atab_code = mmat_affect and atab_nom = 'AFF')) as affectation,
        mmat_marqmat as constructeur,
      
        trim(mmat_desi) as designation,
        trim(mmat_typmat) as modele,
        mmat_nummat as num_matricule,
        trim(mmat_numserie) as num_serie,
        trim(mmat_recalph) as num_parc ,
     
        (select mhir_compteur from mat_hir a where a.mhir_nummat = mmat_nummat and a.mhir_daterel = (select max(b.mhir_daterel) from mat_hir b where b.mhir_nummat = a.mhir_nummat)) as HEURE,
        (select mhir_cumcomp from mat_hir a where a.mhir_nummat = mmat_nummat and a.mhir_daterel = (select max(b.mhir_daterel) from mat_hir b where b.mhir_nummat = a.mhir_nummat)) as KM,
        (select mhir_daterel from mat_hir a where a.mhir_nummat = mmat_nummat and a.mhir_daterel = (select max(b.mhir_daterel) from mat_hir b where b.mhir_nummat = a.mhir_nummat)) as Date_compteur,
        trim(mmat_numparc) as casier_emetteur,
        year(mmat_datemser) as annee,
        date(mmat_datentr) as date_achat,
        (select nvl(sum(mofi_mt),0) from mat_ofi where mofi_classe = 30 and mofi_ssclasse in (10,11,12,13,14,16,17,18,19) and mofi_numbil = mbil_numbil and mofi_typmt = 'R' and mofi_lib like 'Prix d''achat') as Prix_achat,
        (select nvl(sum(mofi_mt),0) from mat_ofi where mofi_classe = 30 and mofi_ssclasse = 15 and mofi_numbil = mbil_numbil and mofi_typmt = 'R') as Amortissement,
     
        (select nvl(sum(mofi_mt),0) from mat_ofi where mofi_classe = 40 and mofi_ssclasse in (21,22,23) and mofi_numbil = mbil_numbil and mofi_typmt = 'R') as Charge_Entretien,
       
        (select nvl(sum(mofi_mt),0) from mat_ofi where mofi_classe = 30 and mofi_ssclasse in (10,11,12,13,14,16,17,18,19) and mofi_numbil = mbil_numbil and mofi_typmt = 'R') as Droits_Taxe,
        
        (select mtxt_comment from mat_txt where mtxt_code = 'LOC' and mtxt_nummat = mmat_nummat and trim(mtxt_comment)<>' '),
        (select mtxt_comment from mat_txt where mtxt_code = 'CLT' and mtxt_nummat = mmat_nummat and trim(mtxt_comment)<>' ' and mtxt_nolign = 10 ),
        (select commentaire_materiel from hff_lien_materiel where  id_materiel = mmat_nummat),
        (select lien_materiel from hff_lien_materiel where  id_materiel = mmat_nummat),
        
        mmat_nouo,
        
       
        trim((select atab_lib from agr_tab where atab_code = mmat_natmat and atab_nom = 'NAT')) as famille,
        trim(mmat_affect) as code_affect,
        (select  mimm_dateserv from mmo_imm where mimm_nummat = mmat_nummat) as date_location
        
        from mat_mat, agr_succ, outer mat_bil
        WHERE (MMAT_SUCC in ('01', '02', '20', '30', '40', '50', '60', '80', '90','91','92') or MMAT_SUCC IN (SELECT ASUC_PARC FROM AGR_SUCC WHERE ASUC_NUM IN ('01','02', '20', '30', '40', '50', '60', '80', '90','91','92') ))
        
        
         and trim(MMAT_ETSTOCK) in ('ST','AT')
         and trim(MMAT_AFFECT) in ('IMM','VTE','LCD','SDO')
        and mmat_soc = 'HF'
        and (mmat_succ = asuc_num or mmat_succ = asuc_parc)
        and mmat_nummat = mbil_nummat
        and mbil_dateclot = '12/31/1899'
        and mmat_datedisp < '12/31/2999'
         and(('" . $matricule . "' is not null and mmat_nummat ='" . $matricule . "') or('" . $numSerie . "' is not null and mmat_numserie ='" . $numSerie . "') or('" . $numParc . "' is not null and mmat_recalph ='" . $numParc . "'))
      ";

        $result = $this->connect->executeQuery($statement);


        $data = $this->connect->fetchResults($result);

        return $this->convertirEnUtf8($data);
    }



    /**
     * insertion dans le base de donner
     *
     * @param [type] $tab
     * @return void
     */
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
            Code_Statut,
            Num_Parc,
            Nom_Image,
            Nom_Fichier,
            ID_Statut_Demande
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        // Exécution de la requête
        $stmt = odbc_prepare($this->connexion->getConnexion(), $sql);
        if (!$stmt) {
            echo "Erreur de préparation : " . odbc_errormsg($this->connexion->getConnexion());
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
     * recupère tous les agence service autoriser
     *
     * @param string $user
     * @return array
     */
    public function recupCodeAgenceServiceAutoriser($user)
    {
        $statement = "SELECT UPPER(Code_AgenceService_IRIUM)
        FROM Agence_service_autorise 
		where Session_Utilisateur = '" . $user . "'";

        $execTypeDoc = $this->connexion->query($statement);
        $tab = [];
        while ($donnee = odbc_fetch_array($execTypeDoc)) {
            $tab[] = $donnee;
        }
        return $tab;
    }

    public function recupeSessionAutoriser($utilisateur)
    {
        $statement = "SELECT DISTINCT Session_Utilisateur AS utilisateur
        FROM Agence_service_autorise 
        WHERE Session_Utilisateur = '" . $utilisateur . "'
		";

        $execTypeDoc = $this->connexion->query($statement);
        $tab = [];
        while ($donnee = odbc_fetch_array($execTypeDoc)) {
            $tab[] = $donnee;
        }
        return $tab;
    }



    /**
     * récupération de OR
     * 
     * @return array
     */
    public function recupeOr($numMat)
    {
        $statement = " SELECT 
            trim(asuc_lib) AS agence, 
            trim(ser.atab_lib) As Service, 
            slor_numor,
            sitv_datdeb As Date, 
            (trim(seor_refdem)||' - '||trim(seor_lib)) As Seor_refdem_lib, 
            sitv_interv,
            trim(sitv_comment) As stiv_comment,
            (CASE seor_natop
            WHEN 'VTE' THEN trim(to_char(seor_numcli)||' - '||trim(seor_nomcli))
            WHEN 'CES' THEN (select trim(sitv_succdeb)||trim(sitv_servdeb)||' - '||trim(asuc_lib)||' / '||trim(atab_lib) from agr_succ, agr_tab WHERE asuc_num = sitv_succdeb AND atab_nom = 'SER' AND atab_code = sitv_servdeb)
            END) AS Agence_Service,
            Sum
            (
            CASE WHEN slor_typlig = 'P' THEN (nvl(slor_qterel,0) + nvl(slor_qterea,0) + nvl(slor_qteres,0) + nvl(slor_qtewait,0) - nvl(slor_qrec,0)) WHEN slor_typlig IN ('F','M','U','C') THEN slor_qterea END
            *
            CASE WHEN slor_typlig = 'P' THEN slor_pxnreel WHEN slor_typlig IN ('F','M','U','C') THEN slor_pxnreel END
            ) as Montant_Total,
            
            Sum
            (
            CASE WHEN slor_typlig = 'P' and slor_constp not like 'Z%' THEN (nvl(slor_qterel,0) + nvl(slor_qterea,0) + nvl(slor_qteres,0) + nvl(slor_qtewait,0) - nvl(slor_qrec,0)) END
            *
            CASE WHEN slor_typlig = 'P' THEN slor_pxnreel WHEN slor_typlig IN ('F','M','U','C') THEN slor_pxnreel END
            ) AS Montant_Pieces,
            Sum
            (
            CASE WHEN slor_typlig = 'P' and slor_constp not like 'Z%' THEN slor_qterea END
            *
            CASE WHEN slor_typlig = 'P' THEN slor_pxnreel WHEN slor_typlig IN ('F','M','U','C') THEN slor_pxnreel END
            ) AS Montant_Pieces_Livrees

            from sav_eor, sav_lor, sav_itv, agr_succ, agr_tab ser, mat_mat
            WHERE seor_numor = slor_numor
            AND seor_serv <> 'DEV'
            AND sitv_numor = slor_numor
            AND sitv_interv = slor_nogrp/100
            AND (seor_succ = asuc_num)
            AND (seor_servcrt = ser.atab_code AND ser.atab_nom = 'SER')
            AND sitv_pos NOT IN ('FC','FE','CP','ST')
            AND sitv_servcrt IN ('ATE','FOR','GAR','MAN','CSP','MAS')
            AND (seor_nummat = mmat_nummat)
            and seor_nummat = $numMat
            group by 1,2,3,4,5,6,7,8
            order by slor_numor, sitv_interv
        ";

        $result = $this->connect->executeQuery($statement);


        $data = $this->connect->fetchResults($result);

        return $this->convertirEnUtf8($data);
    }

    /**
     * recupérer tous l'id materiel dans sql server
     *
     * @return array
     */
    public function recupeIdMateriel()
    {
        $statement = "SELECT DISTINCT ID_Materiel from Demande_Mouvement_Materiel where  ID_Statut_Demande NOT IN (9, 18, 22, 24, 26, 32, 33, 34, 35)";

        $execTypeDoc = $this->connexion->query($statement);
        $tab = [];
        while ($donnee = odbc_fetch_array($execTypeDoc)) {
            $tab[] = $donnee;
        }
        return $tab;
    }

    

    /**
     * recupérer l'agence et service destinataire selon l'id materiel
     *
     * @return array
     */
    public function recupAgenceServDest($id_materiel)
    {
        $statement = "SELECT DISTINCT  Agence_Service_Destinataire from Demande_Mouvement_Materiel WHERE ID_Materiel='{$id_materiel}'";

        $execTypeDoc = $this->connexion->query($statement);
        $tab = [];
        while ($donnee = odbc_fetch_array($execTypeDoc)) {
            $tab[] = $donnee;
        }
        return $tab;
    }

/**
 * recuperation id_statut_demande OUVERT
 *
 * @return void
 */
    public function idOuvertStatutDemande()
    {
        $statement = "SELECT ID_Statut_Demande from Statut_demande Where Description='OUVERT' AND Code_Application='BDM'";

        $execTypeDoc = $this->connexion->query($statement);

        return odbc_fetch_array($execTypeDoc);
    }


    
}
