<?php

namespace App\Model\badm;

use App\Model\Model;
use App\Model\Traits\ConversionModel;

class BadmDetailModel extends Model
{
    use ConversionModel;
    /**
     * informix
     */
    public function findAll($matricule = ''): array
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
        trim(mmat_numparc) as casier_emetteur,
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
        trim(mmat_affect) as code_affect,
        (select  mimm_dateserv from mmo_imm where mimm_nummat = mmat_nummat) as date_location
        
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
        and mmat_nummat ='" . $matricule . "'
      ";

        $result = $this->connect->executeQuery($statement);


        $data = $this->connect->fetchResults($result);

        return $this->convertirEnUtf8($data);
    }

}
