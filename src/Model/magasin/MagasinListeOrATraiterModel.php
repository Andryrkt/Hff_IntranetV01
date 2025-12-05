<?php

namespace App\Model\magasin;

use Exception;
use App\Model\Model;
use App\Model\Traits\ConversionModel;
use App\Service\GlobalVariablesService;
use App\Controller\Traits\FormatageTrait;
use App\Model\Traits\ConditionModelTrait;

class MagasinListeOrATraiterModel extends Model
{
    use ConversionModel;
    use FormatageTrait;
    use ConditionModelTrait;


    public function recupNumeroItv($numOr, $stringItv)
    {
        $statement = " SELECT  
                        COUNT(sitv_interv) as nbItv
                        FROM sav_itv 
                        where sitv_numor='" . $numOr . "'
                        AND sitv_interv NOT IN ('" . $stringItv . "')";

        $result = $this->connect->executeQuery($statement);

        $data = $this->connect->fetchResults($result);

        return $this->convertirEnUtf8($data);
    }


    public function recupUserCreateNumOr($numOr)
    {
        $statement = " SELECT 
                        seor_usr as idUser, 
                        trim(ausr_nom) as nomUtilisateur,
                        trim(atab_lib) as nomPrenom
                        from sav_eor, agr_usr, agr_tab
                        where seor_usr = ausr_num
                        and ausr_ope = atab_code 
                        and atab_nom = 'OPE'
                        and seor_numor='" . $numOr . "'
                    ";

        $result = $this->connect->executeQuery($statement);

        $data = $this->connect->fetchResults($result);

        return $this->convertirEnUtf8($data);
    }

    public function recupDatePlanning1($numOr)
    {
        $statement = " SELECT  
                            min(ska_d_start) as datePlanning1
                        from skw 
                        inner join ska on ska.skw_id = skw.skw_id 
                        where ofh_id ='" . $numOr . "'
                        group by ofh_id 
                    ";

        $result = $this->connect->executeQuery($statement);

        $data = $this->connect->fetchResults($result);

        return $this->convertirEnUtf8($data);
    }

    public function recupDatePlanning2($numOr)
    {
        $statement = " SELECT
                            min(sitv_datepla) as datePlanning2 
                        from sav_itv 
                        where sitv_numor = '" . $numOr . "'
                        group by sitv_numor
                    ";

        $result = $this->connect->executeQuery($statement);

        $data = $this->connect->fetchResults($result);

        return $this->convertirEnUtf8($data);
    }


    public function recupereListeMaterielValider($criteria = [], $lesOrSelonCondition)
    {

        $designation = $this->conditionLike('slor_desi', 'designation', $criteria);
        $referencePiece = $this->conditionLike('slor_refp', 'referencePiece', $criteria);
        $constructeur = $this->conditionLike('slor_constp', 'constructeur', $criteria);
        $dateDebut = $this->conditionDateSigne('slor_datec', 'dateDebut', $criteria, '>=');
        $dateFin = $this->conditionDateSigne('slor_datec', 'dateFin', $criteria, '<=');
        $numDit = $this->conditionLike('seor_refdem', 'numDit', $criteria);
        $numOr = $this->conditionSigne('slor_numor', 'numOr', '=', $criteria);
        $piece = $this->conditionPiece('pieces', $criteria, 'slor_constp');
        $agence = $this->conditionAgenceService("slor_succdeb", 'agence', $criteria);
        $service = $this->conditionAgenceService("slor_servdeb", 'service', $criteria);
        $agenceUser = $this->conditionAgenceUser('agenceUser', $criteria);

        $statement = "SELECT 
            trim(seor_refdem) as referencedit,
            seor_numor as numeroOr,
            trim(slor_constp) as constructeur, 
            trim(slor_refp) as referencePiece, 
            trim(slor_desi) as designationi, 
            CASE WHEN slor_typlig = 'P' THEN (slor_qterel + slor_qterea + slor_qteres + slor_qtewait - slor_qrec) WHEN slor_typlig IN ('F','M','U','C') THEN slor_qterea END AS quantiteDemander,
            slor_qteres as quantiteReserver,
            slor_qterea as quantiteLivree,
            slor_qterel as quantiteReliquat,
            slor_datec as dateCreation,
            slor_nogrp/100 as numInterv,
            slor_nolign as numeroLigne,
            slor_datec, 
            slor_succdeb as agence,
            slor_servdeb as service,
            slor_succ as agenceCrediteur,
            slor_servcrt as serviceCrediteur,
            CASE 
                    WHEN 
                        (SELECT DATE(Min(ska_d_start)) FROM informix.ska, informix.skw WHERE ofh_id = sitv_numor AND ofs_id=sitv_interv AND skw.skw_id = ska.skw_id )  is Null THEN DATE(sitv_datepla)  
                    ELSE
                        (SELECT DATE(Min(ska_d_start)) FROM informix.ska, informix.skw WHERE ofh_id = sitv_numor AND ofs_id=sitv_interv AND skw.skw_id = ska.skw_id ) 
                    END as datePlanning
            , seor_usr as idUser
            , trim(ausr_nom) as nomUtilisateur
            , trim(atab_lib) as nomPrenom
            , mmat_nummat as idMateriel
            , trim(mmat_numserie) as num_serie
            , trim(mmat_recalph) as num_parc 
            , trim(mmat_marqmat) as marque
            , trim(mmat_numparc) as casie

            from sav_lor 
            inner join sav_eor on seor_soc = slor_soc and seor_succ = slor_succ and seor_numor = slor_numor and seor_soc = 'HF'
            inner join mat_mat on mmat_nummat =  seor_nummat
            inner join agr_usr on ausr_num = seor_usr
            inner join agr_tab on atab_nom = 'OPE' and atab_code = ausr_ope
            inner join sav_itv 
                on sitv_soc = slor_soc 
                and sitv_succ = slor_succ 
                and sitv_numor = slor_numor 
                and sitv_interv = slor_nogrp / 100 
                and sitv_numor || '-' || sitv_interv in ({$lesOrSelonCondition['numOrValideString']}) 
                and sitv_soc = 'HF'
            and seor_succ = slor_succ 
            and seor_numor = slor_numor
            where 
            slor_soc = 'HF'
            and seor_typeor not in('950', '501')
            $agenceUser
            $designation
            $referencePiece 
            $constructeur 
            $dateDebut
            $dateFin
            $numOr
            $numDit
            $piece
            $agence
            $service
            and slor_typlig = 'P'
            and slor_pos = 'EC'
            and seor_serv ='SAV'
            and slor_qteres = 0 and slor_qterel = 0 and slor_qterea = 0
            order by numInterv ASC, seor_dateor DESC, slor_numor DESC, numeroLigne ASC
        ";

        $result = $this->connect->executeQuery($statement);

        $data = $this->connect->fetchResults($result);

        return $this->convertirEnUtf8($data);
    }

    public function getListeOrTraiterPol($criteria = [], $lesOrSelonCondition)
    {

        $designation = $this->conditionLike('slor_desi', 'designation', $criteria);
        $referencePiece = $this->conditionLike('slor_refp', 'referencePiece', $criteria);
        $constructeur = $this->conditionLike('slor_constp', 'constructeur', $criteria);
        $dateDebut = $this->conditionDateSigne('slor_datec', 'dateDebut', $criteria, '>=');
        $dateFin = $this->conditionDateSigne('slor_datec', 'dateFin', $criteria, '<=');
        $numDit = $this->conditionLike('seor_refdem', 'numDit', $criteria);
        $numOr = $this->conditionSigne('slor_numor', 'numOr', '=', $criteria);
        $value = GlobalVariablesService::get('pneumatique');
        if (!empty($value)) {
            $piece = " AND slor_constp in ($value)";
        } else {
            $piece = "";
        };
        $agence = $this->conditionAgenceService("slor_succdeb", 'agence', $criteria);
        $service = $this->conditionAgenceService("slor_servdeb", 'service', $criteria);
        $agenceUser = $this->conditionAgenceUser('agenceUser', $criteria);

        $statement = "SELECT 
            trim(seor_refdem) as referencedit,
            seor_numor as numeroOr,
            trim(slor_constp) as constructeur, 
            trim(slor_refp) as referencePiece, 
            trim(slor_desi) as designationi, 
            CASE WHEN slor_typlig = 'P' THEN (slor_qterel + slor_qterea + slor_qteres + slor_qtewait - slor_qrec) WHEN slor_typlig IN ('F','M','U','C') THEN slor_qterea END AS quantiteDemander,
            slor_qteres as quantiteReserver,
            slor_qterea as quantiteLivree,
            slor_qterel as quantiteReliquat,
            slor_datec as dateCreation,
            slor_nogrp/100 as numInterv,
            slor_nolign as numeroLigne,
            slor_datec, 
            slor_succdeb as agence,
            slor_servdeb as service,
            slor_succ as agenceCrediteur,
            slor_servcrt as serviceCrediteur,
            CASE 
                    WHEN 
                        (SELECT DATE(Min(ska_d_start)) FROM informix.ska, informix.skw WHERE ofh_id = sitv_numor AND ofs_id=sitv_interv AND skw.skw_id = ska.skw_id )  is Null THEN DATE(sitv_datepla)  
                    ELSE
                        (SELECT DATE(Min(ska_d_start)) FROM informix.ska, informix.skw WHERE ofh_id = sitv_numor AND ofs_id=sitv_interv AND skw.skw_id = ska.skw_id ) 
                    END as datePlanning
            , seor_usr as idUser
            , trim(ausr_nom) as nomUtilisateur
            , trim(atab_lib) as nomPrenom
            , mmat_nummat as idMateriel
            , trim(mmat_numserie) as num_serie
            , trim(mmat_recalph) as num_parc 
            , trim(mmat_marqmat) as marque
            , trim(mmat_numparc) as casie

            from sav_lor 
            inner join sav_eor on seor_soc = slor_soc and seor_succ = slor_succ and seor_numor = slor_numor and seor_soc = 'HF'
            inner join mat_mat on mmat_nummat =  seor_nummat
            inner join agr_usr on ausr_num = seor_usr
            inner join agr_tab on atab_nom = 'OPE' and atab_code = ausr_ope
            inner join sav_itv 
                on sitv_soc = slor_soc 
                and sitv_succ = slor_succ 
                and sitv_numor = slor_numor 
                and sitv_interv = slor_nogrp / 100 
                and sitv_numor || '-' || sitv_interv in ('" . $lesOrSelonCondition['numOrValideString'] . "') 
                and sitv_soc = 'HF'
            and seor_succ = slor_succ 
            and seor_numor = slor_numor
            where 
            slor_soc = 'HF'
            and seor_typeor not in('950', '501')
            $agenceUser
            $designation
            $referencePiece 
            $constructeur 
            $dateDebut
            $dateFin
            $numOr
            $numDit
            $piece
            $agence
            $service
            and slor_typlig = 'P'
            and slor_pos = 'EC'
            and seor_serv ='SAV'
            and slor_qteres = 0 and slor_qterel = 0 and slor_qterea = 0
            order by numInterv ASC, seor_dateor DESC, slor_numor DESC, numeroLigne ASC
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
        try {
            if (!$this->connexion) {
                throw new Exception("Connexion ODBC non initialisée");
            }

            // Construction des critères (votre code existant)
            if (!empty($criteria['niveauUrgence'])) {
                $niveauUrgence = " AND d.id_niveau_urgence = '" . $criteria['niveauUrgence']->getId() . "'";
            } else {
                $niveauUrgence = null;
            }

            if (!empty($criteria['numDit'])) {
                $numDit = " and d.numero_demande_dit = '" . $criteria['numDit'] . "'";
            } else {
                $numDit = null;
            }

            if (!empty($criteria['numOr'])) {
                $numOr = " d.and numero_or = '" . $criteria['numOr'] . "'";
            } else {
                $numOr = null;
            }

            // REQUÊTE CORRIGÉE avec le bon statut
            $statement = "SELECT distinct CONCAT(numeroOR,'-',numeroItv) as numero_complet , numeroOR as numero_or
                        from ors_soumis_a_validation o
                        inner join demande_intervention d on d.numero_or = o.numeroOR
                        where numeroversion = (select max(numeroversion) from ors_soumis_a_validation oo 
                        where oo.numeroOR = o.numeroOR) 
                        and o.statut like 'Valid%'
                        -- and (d.date_validation_or <> '1900-01-01' or d.date_validation_or is not null or d.date_validation_or <> '')
                        --AND (d.etat_facturation NOT LIKE 'Compl%' OR d.etat_facturation IS NULL)
                        {$niveauUrgence}
                        {$numDit}
                        {$numOr}";

            $execQueryNumOr = $this->connexion->query($statement);

            if (!$execQueryNumOr) {
                $error = odbc_errormsg($this->connexion);
                throw new Exception("Erreur ODBC: " . $error);
            }


            $numOr = [];
            $numORTouCourt = [];
            while ($row_num_or = odbc_fetch_array($execQueryNumOr)) {
                $numOr[] = $row_num_or['numero_complet'];
                $numORTouCourt[] = $row_num_or['numero_or'];
            }


            return [$numOr, $numORTouCourt];
        } catch (Exception $e) {
            error_log("Erreur dans recupNumOr: " . $e->getMessage());
            return [];
        }
    }


    public function recupereAutocompletionDesignation($designations)
    {
        $statement = "SELECT DISTINCT
            
            trim(slor_desi) as designationi

            from sav_lor 
            
            where 
            slor_soc = 'HF'

            and slor_desi like '%" . $designations . "%'
            and slor_typlig = 'P'
            and slor_pos = 'EC'
            and slor_constp not like 'Z%'
            and slor_constp not like 'LUB'
        ";

        $result = $this->connect->executeQuery($statement);


        $data = $this->connect->fetchResults($result);

        return $this->convertirEnUtf8($data);
    }


    public function recuperAutocompletionRefPiece($refPiece)
    {
        $statement = "SELECT 
            trim(slor_refp) as referencePiece
        
            from sav_lor 
            
            where 
            slor_soc = 'HF'

            and slor_refp like '%" . $refPiece . "%'
            and slor_typlig = 'P'
            and slor_pos = 'EC'
            and slor_constp not like 'Z%'
            and slor_constp not like 'LUB'
        ";

        $result = $this->connect->executeQuery($statement);


        $data = $this->connect->fetchResults($result);

        return $this->convertirEnUtf8($data);
    }

    public function agence()
    {
        $statement = "  SELECT DISTINCT
                            slor_succdeb||'-'||(select trim(asuc_lib) from agr_succ where asuc_numsoc = slor_soc and asuc_num = slor_succdeb) as agence
                        FROM sav_lor
                        WHERE slor_succdeb||'-'||(select trim(asuc_lib) from agr_succ where asuc_numsoc = slor_soc and asuc_num = slor_succdeb) <> ''
                        AND slor_soc = 'HF'
                    ";

        $result = $this->connect->executeQuery($statement);

        $data = $this->connect->fetchResults($result);

        return array_column($this->convertirEnUtf8($data), 'agence');
    }



    public function service($agence)
    {
        if ($agence === null) {
            return []; // Si aucune agence, retourner un tableau vide
        }

        // Reverted to string concatenation as executeQuery might not support parameters
        $statement = " SELECT DISTINCT
                            slor_servdeb||'-'||(select trim(atab_lib) from agr_tab where atab_nom = 'SER' and atab_code = slor_servdeb) as service
                        FROM sav_lor
                        WHERE slor_servdeb||'-'||(select trim(atab_lib) from agr_tab where atab_nom = 'SER' and atab_code = slor_servdeb) <> ''
                        AND slor_soc = 'HF'
                        AND slor_succdeb||'-'||(select trim(asuc_lib) from agr_succ where asuc_numsoc = slor_soc and asuc_num = slor_succdeb) = '$agence'
            ";


        $result = $this->connect->executeQuery($statement);

        $data = $this->connect->fetchResults($result);

        $dataUtf8 = $this->convertirEnUtf8($data);


        return array_map(function ($item) {
            return [
                "value" => $item['service'],
                "text"  => $item['service']
            ];
        }, $dataUtf8);
    }

    public function agenceUser(string $codeAgence)
    {
        $statement = "  SELECT DISTINCT
                            slor_succdeb||'-'||(select trim(asuc_lib) from informix.agr_succ where asuc_numsoc = slor_soc and asuc_num = slor_succdeb) as agence
                        FROM informix.sav_lor
                        WHERE slor_succdeb||'-'||(select trim(asuc_lib) from informix.agr_succ where asuc_numsoc = slor_soc and asuc_num = slor_succdeb) <> ''
                        AND slor_soc = 'HF'
                    ";

        if ($codeAgence <> "''") {
            $statement .= " AND slor_succdeb IN ($codeAgence) ";
        }

        $result = $this->connect->executeQuery($statement);

        $data = $this->connect->fetchResults($result);

        return array_column($this->convertirEnUtf8($data), 'agence');
    }
}
