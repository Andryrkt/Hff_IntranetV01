<?php

namespace App\Model\badm;

use App\Model\Model;
use App\Model\Traits\ConversionModel;

class CasierModel extends Model
{
  use ConversionModel;
  /**
   * informix
   */
  public function findAll($matricule = '',  $numParc = '', $numSerie = ''): array
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
        
        mmat_nouo,
        
     
        trim((select atab_lib from agr_tab where atab_code = mmat_natmat and atab_nom = 'NAT')) as famille,
        trim(mmat_affect) as code_affect,
        (select  mimm_dateserv from mmo_imm where mimm_nummat = mmat_nummat) as date_location
        
        from mat_mat, agr_succ, outer mat_bil
        WHERE (MMAT_SUCC in ('01', '02', '20', '30', '40', '50', '60', '80', '90','91','92') or MMAT_SUCC IN (SELECT ASUC_PARC FROM AGR_SUCC WHERE ASUC_NUM IN ('01','02', '20', '30', '40', '50', '60', '80', '90','91','92') ))
        
        
         and trim(MMAT_ETSTOCK) in ('ST','AT')
         and trim(MMAT_AFFECT) in ('IMM','VTE','LCD','SDO')
        and mmat_soc = 'HF'
        -- and mmat_marqmat not like 'Z%'
        and (mmat_succ = asuc_num or mmat_succ = asuc_parc)
        and mmat_nummat = mbil_nummat
        and mbil_dateclot = '12/31/1899'
        and mmat_datedisp < '12/31/2999'
        and (MMAT_ETACHAT = 'FA' and MMAT_ETVENTE = '--')
        " . $conditionNummat . "
      " . $conditionNumParc . "
      " . $conditionNumSerie . "
      ";

    $result = $this->connect->executeQuery($statement);


    $data = $this->connect->fetchResults($result);

    return $this->convertirEnUtf8($data);
  }
}
