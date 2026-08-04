<?php

namespace App\Model\magasin;

use App\Model\Model;
use App\Model\Traits\ConversionModel;
use App\Controller\Traits\FormatageTrait;
use App\Model\Traits\ConditionModelTrait;


class MagasinListeOrLivrerModel extends Model
{
    use ConversionModel;
    use FormatageTrait;
    use ConditionModelTrait;

    public function getDatePlanningPourDa(string $numOr)
    {
        $statement = "SELECT distinct(slor_numor) as num_or,
                CASE 
                    WHEN 
                        (SELECT DATE(Min(ska_d_start)) FROM ska, skw WHERE ofh_id = sitv_numor AND ofs_id=sitv_interv AND skw.skw_id = ska.skw_id )  is Null THEN DATE(sitv_datepla)  
                    ELSE
                        (SELECT DATE(Min(ska_d_start)) FROM ska, skw WHERE ofh_id = sitv_numor AND ofs_id=sitv_interv AND skw.skw_id = ska.skw_id ) 
                END as datePlanning
                    FROM sav_lor
                INNER JOIN sav_itv on sitv_numor = slor_numor and slor_soc = sitv_soc and slor_succ = sitv_succ and slor_soc = 'HF'
                    WHERE  slor_numor = '$numOr'
                        and slor_typlig = 'P'
                        -- and slor_refp not like ('PREST%')
                    ";

        $result = $this->connect->executeQuery($statement);

        $data = $this->connect->fetchResults($result);

        return $this->convertirEnUtf8($data);
    }

    public function recupereListeMaterielValider(array $criteria, string $numeroOrItv, string $numeroOr)
    {
        $statement = "SELECT 
            TRIM(seor_refdem) AS referencedit,
            seor_numor AS numeroOr,
            seor_dateor AS dateCreation,
            COALESCE(pln.date_planning_ska, DATE(sitv_datepla)) AS datePlanning,
            seor_succ AS agenceCrediteur,
            seor_servcrt AS serviceCrediteur,
            sitv_succdeb AS agenceDebiteur,
            sitv_servdeb AS serviceDebiteur,
            sitv_interv AS numInterv,
            slor_nolign AS numeroLigne,
            slor_constp AS constructeur,
            TRIM(slor_refp) AS referencePiece,
            TRIM(slor_desi) AS designationi,
            sit.situation AS situationtest,
            seor_usr AS idUser,
            TRIM(ausr_nom) AS nomUtilisateur,
            TRIM(atab_lib) AS nomPrenom,
            mmat_nummat AS idMateriel,
            TRIM(mmat_numserie) AS num_serie,
            TRIM(mmat_recalph) AS num_parc,
            TRIM(mmat_marqmat) AS marque,
            TRIM(mmat_numparc) AS casie,
            SUM(
                CASE
                    WHEN slor_typlig = 'P' THEN (slor_qterel + slor_qterea + slor_qteres + slor_qtewait - slor_qrec)
                    WHEN slor_typlig IN ('F','M','U','C') THEN slor_qterea
                END
            ) AS quantiteDemander,
            SUM(slor_qteres) AS qteALivrer,
            SUM(slor_qterea) AS quantiteLivree
        FROM {$this->dbIps}:Informix.sav_lor AS lor
        INNER JOIN {$this->dbIps}:Informix.sav_eor AS eor ON eor.seor_numor = slor_numor AND eor.seor_soc = slor_soc AND eor.seor_succ = slor_succ
        INNER JOIN {$this->dbIps}:Informix.mat_mat AS mat ON mat.mmat_nummat = eor.seor_nummat
        INNER JOIN {$this->dbIps}:Informix.agr_usr AS usr ON usr.ausr_num = eor.seor_usr
        INNER JOIN {$this->dbIps}:Informix.agr_tab AS tab ON tab.atab_nom = 'OPE' AND tab.atab_code = usr.ausr_ope
        INNER JOIN {$this->dbIps}:Informix.sav_itv AS itv ON itv.sitv_soc = slor_soc AND itv.sitv_succ = slor_succ AND itv.sitv_numor = slor_numor AND itv.sitv_interv = slor_nogrp / 100 AND itv.sitv_numor || '-' || itv.sitv_interv IN ($numeroOrItv)
        INNER JOIN (
            SELECT
                ls.slor_numor AS numero_or,
                SUM(ls.slor_qteres) AS total_qteres
            FROM {$this->dbIps}:Informix.sav_lor AS ls
            WHERE ls.slor_numor IN ($numeroOr)
                {$this->conditionPiece('pieces',$criteria, 'ls.slor_constp')}
            GROUP BY ls.slor_numor
        ) AS qte ON qte.numero_or = lor.slor_numor AND qte.total_qteres > 0
        INNER JOIN (
            SELECT
                ss.slor_numor AS numero_or,
                ss.slor_nogrp AS num_groupe,
                CASE
                    WHEN SUM(ss.slor_qteres) > 0
                        AND SUM(
                            CASE
                                WHEN ss.slor_typlig = 'P' THEN (ss.slor_qterel + ss.slor_qterea + ss.slor_qteres + ss.slor_qtewait - ss.slor_qrec)
                                WHEN ss.slor_typlig IN ('F','M','U','C') THEN ss.slor_qterea
                            END
                        ) = SUM(ss.slor_qteres + ss.slor_qterea)
                        THEN 'COMPLET' --  somme_qte_dispo > 0 AND somme_qte_dem = (somme_qte_dispo + somme_qte_livree)
                    WHEN SUM(ss.slor_qteres) > 0
                        AND SUM(
                            CASE
                                WHEN ss.slor_typlig = 'P' THEN (ss.slor_qterel + ss.slor_qterea + ss.slor_qteres + ss.slor_qtewait - ss.slor_qrec)
                                WHEN ss.slor_typlig IN ('F','M','U','C') THEN ss.slor_qterea
                            END
                        ) > SUM(ss.slor_qteres + ss.slor_qterea)
                        THEN 'INCOMPLET' --  somme_qte_dispo > 0 AND somme_qte_dem > (somme_qte_dispo + somme_qte_livree)
                END AS situation
            FROM {$this->dbIps}:Informix.sav_lor AS ss
            WHERE ss.slor_numor IN ($numeroOr)
                {$this->conditionPiece('pieces',$criteria, 'ss.slor_constp')}
            GROUP BY ss.slor_numor, ss.slor_nogrp
        ) AS sit ON sit.numero_or = lor.slor_numor AND sit.num_groupe = lor.slor_nogrp
        LEFT JOIN (
            SELECT
                skw.ofh_id AS num_or_planning,
                skw.ofs_id AS num_interv_planning,
                DATE(MIN(ska.ska_d_start)) AS date_planning_ska
            FROM {$this->dbIps}:Informix.ska AS ska
            INNER JOIN {$this->dbIps}:Informix.skw AS skw ON skw.skw_id = ska.skw_id
            WHERE skw.ofh_id IN ($numeroOr)
            GROUP BY skw.ofh_id, skw.ofs_id
        ) AS pln ON pln.num_or_planning = itv.sitv_numor AND pln.num_interv_planning = itv.sitv_interv
        WHERE seor_typeor NOT IN ('950', '501')
            AND sit.situation IS NOT NULL
            {$this->conditionPiece('pieces',$criteria, 'slor_constp')}
            {$this->conditionAgenceUser('agenceUser',$criteria)}
            {$this->conditionOrCompletOuNonOrALivrer('orCompletNon',$criteria, 'sit')}
            {$this->conditionLike('slor_desi', 'designation',$criteria)}
            {$this->conditionLike('slor_refp', 'referencePiece',$criteria)}
            {$this->conditionLike('slor_constp', 'constructeur',$criteria)}
            {$this->conditionDateSigne('slor_datec', 'dateDebut',$criteria, '>=')}
            {$this->conditionDateSigne('slor_datec', 'dateFin',$criteria, '<=')}
            {$this->conditionSigne('slor_numor', 'numOr', '=',$criteria)}
            {$this->conditionLike('seor_refdem', 'numDit',$criteria)}
            {$this->conditionAgenceService('slor_succdeb', 'agence',$criteria)}
            {$this->conditionAgenceService('slor_servdeb', 'service',$criteria)}
        GROUP BY 1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22
        ORDER BY seor_numor ASC, sitv_interv ASC, slor_nolign ASC
        ";

        // dd($statement);
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

    public function service(string $agence): array
    {
        $statement = "  SELECT DISTINCT
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
                            slor_succdeb||'-'||(select trim(asuc_lib) from agr_succ where asuc_numsoc = slor_soc and asuc_num = slor_succdeb) as agence
                        FROM informix.sav_lor
                        WHERE slor_succdeb||'-'||(select trim(asuc_lib) from agr_succ where asuc_numsoc = slor_soc and asuc_num = slor_succdeb) <> ''
                        AND slor_soc = 'HF'
                    ";

        if ($codeAgence <> "''") {
            $statement .= " AND slor_succdeb IN ($codeAgence)";
        }

        $result = $this->connect->executeQuery($statement);

        $data = $this->connect->fetchResults($result);

        return array_column($this->convertirEnUtf8($data), 'agence');
    }
}
