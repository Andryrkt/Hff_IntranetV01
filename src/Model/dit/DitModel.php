<?php

namespace App\Model\dit;


use App\Model\Model;
use App\Model\Traits\ConversionModel;
use App\Service\GlobalVariablesService;

class DitModel extends Model
{

  use ConversionModel;

  /**
   * informix
   */
  public function findAll($matricule = '0',  $numParc = '0', $numSerie = '0')
  {
    if ($matricule === '' || $matricule === '0' || $matricule === null) {
      $conditionNummat = "";
    } else {
      $conditionNummat = "and mmat_nummat = '" . $matricule . "'";
    }


    if ($numParc === '' || $numParc === '0' || $numParc === null) {
      $conditionNumParc = "";
    } else {
      $conditionNumParc = "and mmat_recalph = '" . $numParc . "'";
    }

    if ($numSerie === '' || $numSerie === '0' || $numSerie === null) {
      $conditionNumSerie = "";
    } else {
      $conditionNumSerie = "and TRIM(mmat_numserie) = '" . $numSerie . "'";
    }




    $statement = "SELECT

        mmat_marqmat as constructeur,
        trim(mmat_desi) as designation,
        trim(mmat_typmat) as modele,
        trim(mmat_numparc) as casier_emetteur,
        mmat_nummat as num_matricule,
        trim(mmat_numserie) as num_serie,
        trim(mmat_recalph) as num_parc,

        (select mhir_compteur from mat_hir a where a.mhir_nummat = mmat_nummat and a.mhir_daterel = (select max(b.mhir_daterel) from mat_hir b where b.mhir_nummat = a.mhir_nummat)) as heure,
        (select mhir_cumcomp from mat_hir a where a.mhir_nummat = mmat_nummat and a.mhir_daterel = (select max(b.mhir_daterel) from mat_hir b where b.mhir_nummat = a.mhir_nummat)) as km,
        (select nvl(sum(mofi_mt),0) from mat_ofi where mofi_classe = 30 and mofi_ssclasse in (10,11,12,13,14,16,17,18,19) and mofi_numbil = mbil_numbil and mofi_typmt = 'R') as Prix_achat,
        (select nvl(sum(mofi_mt),0) from mat_ofi where mofi_classe = 30 and mofi_ssclasse = 15 and mofi_numbil = mbil_numbil and mofi_typmt = 'R') as Amortissement,

        (select nvl(sum(mofi_mt),0) from mat_ofi where mofi_classe = 10 and mofi_ssclasse in (100,21,22,23) and mofi_numbil = mbil_numbil and mofi_typmt = 'R') as ChiffreAffaires,
        (select nvl(sum(mofi_mt),0) from mat_ofi where mofi_classe = 40 and mofi_ssclasse in (100,110) and mofi_numbil = mbil_numbil and mofi_typmt = 'R') as ChargeLocative,
        (select nvl(sum(mofi_mt),0) from mat_ofi where mofi_classe = 40 and mofi_ssclasse in (21,22,23) and mofi_numbil = mbil_numbil and mofi_typmt = 'R') as ChargeEntretien
      
      FROM MAT_MAT
      LEFT JOIN mat_bil on mbil_nummat = mmat_nummat and mbil_dateclot <= '01/01/1900' and mbil_dateclot = '12/31/1899'
      WHERE MMAT_ETSTOCK in ('ST','AT', '--')
      " . $conditionNummat . "
      " . $conditionNumParc . "
      " . $conditionNumSerie . "
      ";


    $result = $this->connect->executeQuery($statement);

    $data = $this->connect->fetchResults($result);

    return $this->convertirEnUtf8($data);
  }


  public function historiqueMateriel(int $idMateriel, string $reparationRealise)
  {
    $estPneumatique = in_array($reparationRealise, ['ATE POL TANA']);
    $estPiece = in_array($reparationRealise, ['ATE TANA', 'ATE STAR', 'ATE MAS']);
    $constructeurPneumatique = GlobalVariablesService::get('pneumatique') . ",'PNE'";
    $conditionConstructeur = "";

    if ($estPneumatique) {
      $conditionConstructeur = "AND slor_constp IN ($constructeurPneumatique)";
    } else if ($estPiece) {
      $conditionConstructeur = "AND slor_constp NOT IN ($constructeurPneumatique)";
    }

    $statement = "SELECT
              TRIM(seor_succ) AS codeAgence,
              TRIM(seor_servcrt) AS codeService,
              sitv_datdeb AS dateDebut,
              sitv_numor AS numeroOr, 
              sitv_interv AS numeroIntervention, 
              TRIM(sitv_comment) AS commentaire,
              sitv_pos AS pos,
              SUM(
                slor_pxnreel * (
                CASE 
                  WHEN slor_typlig = 'P' 
                    THEN (slor_qterel + slor_qterea + slor_qteres + slor_qtewait - slor_qrec) 
                  WHEN slor_typlig IN ('F','M','U','C') 
                    THEN slor_qterea 
                END)
              ) AS somme
            FROM sav_eor, sav_lor, sav_itv, agr_succ, agr_tab ser, mat_mat, agr_tab ope, OUTER agr_tab sec
            WHERE seor_numor = slor_numor
              AND seor_serv <> 'DEV'
              AND sitv_numor = slor_numor
              AND sitv_interv = slor_nogrp/100
              AND (seor_succ = asuc_num)
              AND (seor_servcrt = ser.atab_code AND ser.atab_nom = 'SER')
              AND (sitv_typitv = sec.atab_code AND sec.atab_nom = 'TYI')
              AND (seor_ope = ope.atab_code AND ope.atab_nom = 'OPE')
              AND sitv_pos IN ('FC','FE','CP','ST', 'EC')
              AND (seor_nummat = mmat_nummat)
              AND mmat_nummat ='$idMateriel'
              $conditionConstructeur
            GROUP BY 1,2,3,4,5,6,7
            ORDER BY sitv_pos DESC, sitv_datdeb DESC, sitv_numor, sitv_interv
    ";

    $result = $this->connect->executeQuery($statement);


    $data = $this->connect->fetchResults($result);

    return $this->convertirEnUtf8($data);
  }

  public function getNumeroMatriculePasMateriel()
  {
    $statement = "SELECT mmat_nummat as numero_matricule 
              from informix.mat_mat 
              where mmat_reffou in ('IMMODIV','PRESTDIV') OR (mmat_recalph = 'EQPABS')
              or mmat_nummat = '7711'
              order by mmat_nummat
              ";

    $result = $this->connect->executeQuery($statement);

    $data = $this->connect->fetchResults($result);

    return array_column($this->convertirEnUtf8($data), 'numero_matricule');
  }


  public function recuperationNumSerieNumParc($matricule)
  {

    $statement = "SELECT
        mmat_nummat as num_matricule,
        trim(mmat_numserie) as num_serie,
        trim(mmat_recalph) as num_parc

        from mat_mat
        where mmat_nummat IN " . $matricule . "
        and MMAT_ETSTOCK in ('ST','AT', '--')
        and trim(MMAT_AFFECT) in ('IMM','LCD', 'SDO', 'VTE')
      ";

    $result = $this->connect->executeQuery($statement);


    $data = $this->connect->fetchResults($result);

    return $this->convertirEnUtf8($data);
  }

  public function recupNumSerieParc($matricule)
  {
    $statement = "SELECT
        mmat_nummat as num_matricule,
        trim(mmat_numserie) as num_serie,
        trim(mmat_recalph) as num_parc

        from mat_mat
        where mmat_nummat ='" . $matricule . "'
        and MMAT_ETSTOCK in ('ST','AT', '--')
        and trim(MMAT_AFFECT) in ('IMM','LCD', 'SDO', 'VTE')
      ";

    $result = $this->connect->executeQuery($statement);


    $data = $this->connect->fetchResults($result);

    return $this->convertirEnUtf8($data);
  }

  public function recupNumSerieParcPourDa($matricule)
  {
    $statement = "SELECT
        trim(mmat_numserie) as num_serie,
        trim(mmat_recalph) as num_parc

        from mat_mat
        where mmat_nummat ='" . $matricule . "'";

    $result = $this->connect->executeQuery($statement);


    $data = $this->connect->fetchResults($result);

    return $this->convertirEnUtf8($data);
  }

  public function recuperationIdMateriel($numParc = '', $numSerie = '')
  {
    if ($numParc === '' || $numParc === '0' || $numParc === null) {
      $conditionNumParc = "";
    } else {
      $conditionNumParc = "and mmat_recalph = '" . $numParc . "'";
    }

    if ($numSerie === '' || $numSerie === '0' || $numSerie === null) {
      $conditionNumSerie = "";
    } else {
      $conditionNumSerie = "and TRIM(mmat_numserie) = '" . $numSerie . "'";
    }

    $statement = "SELECT
        mmat_nummat as num_matricule
        from mat_mat
        where  MMAT_ETSTOCK in ('ST','AT', '--')
        and trim(MMAT_AFFECT) in ('IMM','LCD', 'SDO', 'VTE', 'CAS')
        " . $conditionNumParc . "
        " . $conditionNumSerie . "
        ";


    $result = $this->connect->executeQuery($statement);


    $data = $this->connect->fetchResults($result);


    return $this->convertirEnUtf8($data);
  }


  public function recuperationSectionValidation()
  {

    $statement = "SELECT trim(Atab_Code) AS ATAB_CODE,
                  trim(Atab_lib)  AS ATAB_LIB
                  FROM AGR_TAB
                  WHERE Atab_nom = 'TYI'
      ";

    $result = $this->connect->executeQuery($statement);

    $data = $this->connect->fetchResults($result);

    return $this->convertirEnUtf8($data);
  }



  public function RecupereCommandeOr($numero_or)
  {
    $statement = "SELECT
        slor_numcf,
        fcde_date,
        slor_typcf,
        fcde_posc,
        fcde_posl

      from sav_lor
      inner join frn_cde on frn_cde.fcde_numcde = slor_numcf
      where
      slor_soc = 'HF'
      --and slor_succ = '01'
      and slor_constp not like '%Z'
      and slor_numor in (select seor_numor from sav_eor where seor_serv = 'SAV')
      and slor_numor = '" . $numero_or . "'
      group by 1,2,3,4,5";

    $result = $this->connect->executeQuery($statement);

    $data = $this->connect->fetchResults($result);

    return $this->convertirEnUtf8($data);
  }


  public function recupQuantite($numOr)
  {
    $statement = "SELECT 
            trim(seor_refdem) as referenceDIT,
            seor_numor as numeroOr,
            sum(CASE WHEN slor_typlig = 'P' THEN (slor_qterel + slor_qterea + slor_qteres + slor_qtewait - slor_qrec) WHEN slor_typlig IN ('F','M','U','C') THEN slor_qterea END) AS quantiteDemander,
            sum(slor_qteres) as quantiteReserver,
            sum(sliv_qteliv) as quantiteLivree,
            sum(slor_qterel) as quantiteReliquat
            from sav_lor 
            inner join sav_eor on seor_soc = slor_soc and seor_succ = slor_succ 
            and seor_numor = slor_numor
            left join sav_liv on sliv_soc = slor_soc and sliv_succ = slor_succ and sliv_numor = seor_numor and slor_nolign = sliv_nolign
            
            where 
            slor_soc = 'HF'
            --and slor_succ = '01'
            and slor_typlig = 'P'
            and seor_serv ='SAV'
            and slor_constp not like 'Z%'
            and slor_constp not like 'LUB'
            and slor_constp not like 'ZST'
            and seor_numor  = '" . $numOr . "'
            group by 1,2;
        ";

    $result = $this->connect->executeQuery($statement);

    $data = $this->connect->fetchResults($result);

    return $this->convertirEnUtf8($data);
  }


  public function recupQuantiteStatutAchatLocaux($numOr)
  {
    $statement = "SELECT 
            trim(seor_refdem) as referenceDIT,
            seor_numor as numeroOr,
            sum(CASE WHEN slor_typlig = 'P' THEN (slor_qterel + slor_qterea + slor_qteres + slor_qtewait - slor_qrec) WHEN slor_typlig IN ('F','M','U','C') THEN slor_qterea END) AS quantiteDemander,
            sum(slor_qteres) as quantiteReserver,
            sum(sliv_qteliv) as quantiteLivree,
            sum(slor_qterel) as quantiteReliquat
            from sav_lor 
            inner join sav_eor on seor_soc = slor_soc and seor_succ = slor_succ 
            and seor_numor = slor_numor
            left join sav_liv on sliv_soc = slor_soc and sliv_succ = slor_succ and sliv_numor = seor_numor and slor_nolign = sliv_nolign
            
            where 
            slor_soc = 'HF'
            --and slor_succ = '01'
            and slor_typlig = 'P'
            and seor_serv ='SAV'
            and slor_constp not like 'Z%'
            and slor_constp not like 'LUB'
            and slor_constp like 'ZST'
            and seor_numor  = '" . $numOr . "'
            group by 1,2;
        ";

    $result = $this->connect->executeQuery($statement);

    $data = $this->connect->fetchResults($result);

    return $this->convertirEnUtf8($data);
  }

  public function recupQuantiteQuatreStatutOr($numOr)
  {
    $statement = "SELECT 
            trim(seor_refdem) as referenceDIT,
            seor_numor as numeroOr,
            sum(CASE WHEN slor_typlig = 'P' THEN (slor_qterel + slor_qterea + slor_qteres + slor_qtewait - slor_qrec) WHEN slor_typlig IN ('F','M','U','C') THEN slor_qterea END) AS quantiteDemander,
            sum(slor_qteres) as quantiteReserver,
            sum(sliv_qteliv) as quantiteLivree,
            sum(slor_qterel) as quantiteReliquat,
            sum(slor_qterea) as qteLiv
            from sav_lor 
            inner join sav_eor on seor_soc = slor_soc and seor_succ = slor_succ 
            and seor_numor = slor_numor
            left join sav_liv on sliv_soc = slor_soc and sliv_succ = slor_succ and sliv_numor = seor_numor and slor_nolign = sliv_nolign
            
            where 
            slor_soc = 'HF'
            --and slor_succ = '01'
            and slor_typlig = 'P'
            and seor_serv ='SAV'
            and slor_constp not like 'Z%'
            and slor_constp not like 'LUB'
            and seor_numor  = '" . $numOr . "'
            group by 1,2;
        ";

    $result = $this->connect->executeQuery($statement);

    $data = $this->connect->fetchResults($result);

    return $this->convertirEnUtf8($data);
  }

  public function recupOrSoumisValidation($numOr)
  {
    $statement = "SELECT
          slor_numor,
          sitv_datdeb,
          trim(seor_refdem) as NUMERo_DIT,
          sitv_interv as NUMERO_ITV,
          trim(sitv_comment) as LIBELLE_ITV,
          count(slor_constp) as NOMBRE_LIGNE,
          Sum(
              CASE
                  WHEN slor_typlig = 'P' 
                  THEN (slor_qterel + slor_qterea + slor_qteres + slor_qtewait - slor_qrec)
                  WHEN slor_typlig IN ('F', 'M', 'U', 'C') THEN slor_qterea
              END 
              * 
              CASE
                  WHEN slor_typlig = 'P' THEN slor_pxnreel
                  WHEN slor_typlig IN ('F', 'M', 'U', 'C') THEN slor_pxnreel
              END
          ) as MONTANT_ITV,

          Sum(
              CASE
                  WHEN slor_typlig = 'P'
                  AND slor_constp NOT like 'Z%'
                  AND slor_constp <> 'LUB' THEN (nvl (slor_qterel, 0) + nvl (slor_qterea, 0) + nvl (slor_qteres, 0) + nvl (slor_qtewait, 0) - nvl (slor_qrec, 0))
              END 
              * 
              CASE
                  WHEN slor_typlig = 'P' THEN slor_pxnreel
                  WHEN slor_typlig IN ('F', 'M', 'U', 'C') THEN slor_pxnreel
              END
          ) AS MONTANT_PIECE,

          Sum(
              CASE
                  WHEN slor_typlig = 'M' THEN slor_qterea
              END * CASE
                  WHEN slor_typlig = 'P' THEN slor_pxnreel
                  WHEN slor_typlig IN ('F', 'M', 'U', 'C') THEN slor_pxnreel
              END
          ) AS MONTANT_MO,

          Sum(
              CASE
                  WHEN slor_constp = 'ZST' THEN (
                      slor_qterel + slor_qterea + slor_qteres + slor_qtewait - slor_qrec
                  )
              END * CASE
                  WHEN slor_typlig = 'P' THEN slor_pxnreel
                  WHEN slor_typlig IN ('F', 'M', 'U', 'C') THEN slor_pxnreel
              END
          ) AS MONTANT_ACHATS_LOCAUX,

          Sum(
              CASE
                  WHEN slor_constp <> 'ZST'
                  AND slor_constp like 'Z%' THEN slor_qterea
              END * CASE
                  WHEN slor_typlig = 'P' THEN slor_pxnreel
                  WHEN slor_typlig IN ('F', 'M', 'U', 'C') THEN slor_pxnreel
              END
          ) AS MONTANT_DIVERS,

          Sum(
              CASE
                  WHEN 
                    slor_typlig = 'P'
                    AND slor_constp NOT like 'Z%'
                    AND slor_constp = 'LUB' 
                  THEN (nvl (slor_qterel, 0) + nvl (slor_qterea, 0) + nvl (slor_qteres, 0) + nvl (slor_qtewait, 0) - nvl (slor_qrec, 0))
              END 
              * 
              CASE
                  WHEN slor_typlig = 'P' THEN slor_pxnreel
                  WHEN slor_typlig IN ('F', 'M', 'U', 'C') THEN slor_pxnreel
              END
          ) AS MONTANT_LUBRIFIANTS

          from sav_eor, sav_lor, sav_itv
          WHERE
              seor_numor = slor_numor
              AND seor_serv <> 'DEV'
              AND sitv_numor = slor_numor
              AND sitv_interv = slor_nogrp / 100
              AND seor_soc = 'HF'
              AND slor_soc=seor_soc
              AND sitv_soc=seor_soc
          --AND sitv_pos NOT IN('FC', 'FE', 'CP', 'ST')
          --AND sitv_servcrt IN ('ATE','FOR','GAR','MAN','CSP','MAS','LR6','LST')
          AND seor_numor = '" . $numOr . "'
          --AND SEOR_SUCC = '01'
          group by 1, 2, 3, 4, 5
          order by slor_numor, sitv_interv
        ";

    $result = $this->connect->executeQuery($statement);

    $data = $this->connect->fetchResults($result);

    return $this->convertirEnUtf8($data);
  }

  public function recupererNumdevis($numOr)
  {
    $statement = "SELECT  seor_numdev  
                from sav_eor
                where seor_numor = '" . $numOr . "'";

    $result = $this->connect->executeQuery($statement);

    $data = $this->connect->fetchResults($result);

    return $this->convertirEnUtf8($data);
  }

  public function recupAgenceServiceDebiteur($numOr)
  {
    $statement = " SELECT 
          slor_succdeb || '-' || slor_servdeb AS agServDebiteur
          FROM sav_lor
          WHERE slor_numor = '" . $numOr . "'";

    $result = $this->connect->executeQuery($statement);

    $data = $this->connect->fetchResults($result);

    return array_column($this->convertirEnUtf8($data), 'agservdebiteur');
  }

  public function recupNbNumor($numDit)
  {
    $statement = "SELECT 
            count(seor_numor) AS nbOr
            from sav_eor 
            where seor_refdem='" . $numDit . "'
        ";

    $result = $this->connect->executeQuery($statement);

    $data = $this->connect->fetchResults($result);

    return $this->convertirEnUtf8($data);
  }

  public function recupMarqueCasierMateriel($matricule)
  {
    $statement = "SELECT
          mmat_nummat as num_matricule,
          trim(mmat_numserie) as num_serie,
          trim(mmat_recalph) as num_parc ,
          trim(mmat_marqmat) as marque,
          trim(mmat_desi) as designation,
          trim(mmat_typmat) as modele,
          trim(mmat_numparc) as casier

          from mat_mat
          where mmat_nummat ='" . $matricule . "'
          and MMAT_ETSTOCK in ('ST','AT', '--')
          and trim(MMAT_AFFECT) in ('IMM','LCD', 'SDO', 'VTE')
      ";

    $result = $this->connect->executeQuery($statement);

    $data = $this->connect->fetchResults($result);

    return $this->convertirEnUtf8($data);
  }

  public function recupInfoMateriel(string $numOr)
  {
    $statement = "SELECT 
        TRIM(mmat_desi) AS designation, 
        TRIM(mmat_numserie) AS numserie,
        TRIM(mmat_recalph) AS identite
      FROM sav_eor
      INNER JOIN mat_mat on mmat_nummat = seor_nummat and seor_soc = 'HF'
      WHERE seor_numor = '$numOr'";

    $result = $this->connect->executeQuery($statement);

    $data = $this->connect->fetchResults($result);

    return $this->convertirEnUtf8($data ? $data[0] : []);
  }

  public function getPosition(string $numDit)
  {
    $statement = "SELECT seor_pos as position from informix.sav_eor  where seor_refdem = '$numDit'";

    $result = $this->connect->executeQuery($statement);
    $data = $this->connect->fetchResults($result);

    return array_column($this->convertirEnUtf8($data), 'position');
  }
}
