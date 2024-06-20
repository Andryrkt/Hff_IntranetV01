<?php

namespace App\Model\dit;


use App\Model\Model;
use App\Model\Traits\ConversionModel;

class DitModel extends Model
{


   use ConversionModel;

    /**
     * informix
     */
    public function findAll($matricule = '',  $numParc = '', $numSerie = '')
    {
        $statement = "SELECT
       
    
        mmat_marqmat as constructeur,
       
        trim(mmat_desi) as designation,
        trim(mmat_typmat) as modele,
        trim(mmat_numparc) as casier_emetteur,

        (select mhir_compteur from mat_hir a where a.mhir_nummat = mmat_nummat and a.mhir_daterel = (select max(b.mhir_daterel) from mat_hir b where b.mhir_nummat = a.mhir_nummat)) as HEURE,
        (select mhir_cumcomp from mat_hir a where a.mhir_nummat = mmat_nummat and a.mhir_daterel = (select max(b.mhir_daterel) from mat_hir b where b.mhir_nummat = a.mhir_nummat)) as KM,

mmat_nummat as num_matricule,
        trim(mmat_numserie) as num_serie,
        trim(mmat_recalph) as num_parc 
        
        
        from mat_mat, agr_succ, outer mat_bil
        WHERE (MMAT_SUCC in ('01', '20', '30', '40', '50', '60', '80', '90','91','92') or MMAT_SUCC IN (SELECT ASUC_PARC FROM AGR_SUCC WHERE ASUC_NUM IN ('01', '20', '30', '40', '50', '60', '80', '90','91','92') ))
        
        
         and trim(MMAT_ETSTOCK) in ('ST','AT')
         and trim(MMAT_AFFECT) in ('IMM','VTE','LCD','SDO')
        and mmat_soc = 'HF'
        -- and mmat_marqmat not like 'Z%'
        and (mmat_succ = asuc_num or mmat_succ = asuc_parc)
        and mmat_nummat = mbil_nummat
        and mbil_dateclot = '12/31/1899'
        and mmat_datedisp < '12/31/2999'
        and(
        ('".$matricule."' is not null and mmat_nummat ='".$matricule."') 
        or('" . $numSerie ."' is not null and mmat_numserie ='" .$numSerie."') 
        or('" . $numParc ."' is not null and mmat_recalph ='" . $numParc ."')
        )
      ";

        $result = $this->connect->executeQuery($statement);


        $data = $this->connect->fetchResults($result);

        return $this->convertirEnUtf8($data);
    }


    public function historiqueMateriel($idMateriel) {

      $statement = " SELECT

            trim(seor_succ) as codeAgence,
            --trim(asuc_lib),
            trim(seor_servcrt) as codeService,
            --trim(ser.atab_lib),
            sitv_datdeb as dateDebut,
            sitv_numor as numeroOr, 
            sitv_interv as numeroIntervention, 
            trim(sitv_comment) as commentaire,
            sum(slor_qterea*slor_pmp) as somme

            FROM  sav_eor, sav_lor, sav_itv, agr_succ, agr_tab ser, mat_mat, agr_tab ope, outer agr_tab sec

            where seor_numor = slor_numor
            and seor_serv <> 'DEV'
            and sitv_numor = slor_numor
            and sitv_interv = slor_nogrp/100
            and (seor_succ = asuc_num) -- or mmat_succ = asuc_parc)
            and (seor_servcrt = ser.atab_code and ser.atab_nom = 'SER')
            and (sitv_typitv = sec.atab_code and sec.atab_nom = 'TYI')
            and (seor_ope = ope.atab_code and ope.atab_nom = 'OPE')
            and sitv_pos in ('FC','FE','CP','ST')
            and sitv_servcrt in ('ATE','FOR','GAR','MAN','CSP','MAS')
            and (seor_nummat = mmat_nummat)

            and mmat_nummat ='$idMateriel'

            group by 1,2,3,4,5,6
            order by sitv_datdeb desc, sitv_numor, sitv_interv
            ";

$result = $this->connect->executeQuery($statement);


        $data = $this->connect->fetchResults($result);

        return $this->convertirEnUtf8($data);
    }
    



    

   

    

    
   



    
}
