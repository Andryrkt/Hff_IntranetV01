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
 mmat_nummat as num_matricule,
        trim(mmat_numserie) as num_serie,
        trim(mmat_recalph) as num_parc,

(select mhir_compteur from mat_hir a where a.mhir_nummat = mmat_nummat and a.mhir_daterel = (select max(b.mhir_daterel) from mat_hir b where b.mhir_nummat = a.mhir_nummat)) as HEURE,
      (select mhir_cumcomp from mat_hir a where a.mhir_nummat = mmat_nummat and a.mhir_daterel = (select max(b.mhir_daterel) from mat_hir b where b.mhir_nummat = a.mhir_nummat)) as KM,
(select nvl(sum(mofi_mt),0) from mat_ofi where mofi_classe = 30 and mofi_ssclasse in (10,11,12,13,14,16,17,18,19) and mofi_numbil = mbil_numbil and mofi_typmt = 'R') as Prix_achat,
         (select nvl(sum(mofi_mt),0) from mat_ofi where mofi_classe = 30 and mofi_ssclasse = 15 and mofi_numbil = mbil_numbil and mofi_typmt = 'R') as Amortissement,

        (select nvl(sum(mofi_mt),0) from mat_ofi where mofi_classe = 10 and mofi_ssclasse in (100,21,22,23) and mofi_numbil = mbil_numbil and mofi_typmt = 'R') as ChiffreAffaires,
         (select nvl(sum(mofi_mt),0) from mat_ofi where mofi_classe = 40 and mofi_ssclasse in (100,110) and mofi_numbil = mbil_numbil and mofi_typmt = 'R') as ChargeLocative,
        (select nvl(sum(mofi_mt),0) from mat_ofi where mofi_classe = 40 and mofi_ssclasse in (21,22,23) and mofi_numbil = mbil_numbil and mofi_typmt = 'R') as ChargeEntretien

FROM MAT_MAT, mat_bil
  WHERE(
        ('".$matricule."' is not null and mmat_nummat ='".$matricule."') 
        or('" . $numSerie ."' is not null and mmat_numserie ='" .$numSerie."') 
        or('" . $numParc ."' is not null and mmat_recalph ='" . $numParc ."')
        )
and MMAT_ETSTOCK in ('ST','AT', '--')
and trim(MMAT_AFFECT) in ('IMM','LCD', 'SDO')
and (mmat_nummat = mbil_nummat and mbil_dateclot < '01/01/1900')
       and mbil_dateclot = '12/31/1899'
        
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
	sitv_pos as pos,
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

            group by 1,2,3,4,5,6,7
            order by sitv_pos desc, sitv_datdeb desc, sitv_numor, sitv_interv
            ";

$result = $this->connect->executeQuery($statement);


        $data = $this->connect->fetchResults($result);

        return $this->convertirEnUtf8($data);
    }
    


    public function recuperationNumSerieNumParc($matricule)
    {
     
        $statement = "SELECT
        mmat_nummat as num_matricule,
        trim(mmat_numserie) as num_serie,
        trim(mmat_recalph) as num_parc

        from mat_mat
        where mmat_nummat IN ".$matricule."
         and MMAT_ETSTOCK in ('ST','AT', '--')
and trim(MMAT_AFFECT) in ('IMM','LCD', 'SDO')

         
        
      ";

        $result = $this->connect->executeQuery($statement);


        $data = $this->connect->fetchResults($result);

        return $this->convertirEnUtf8($data);
    }


    public function recuperationIdMateriel($numParc = '', $numSerie = '')
    {
     
        $statement = "SELECT
        mmat_nummat as num_matricule
      

        from mat_mat
        where (
        ('" . $numSerie ."' is not null and mmat_numserie ='" .$numSerie."') 
        or('" . $numParc ."' is not null and mmat_recalph ='" . $numParc ."')
        )
        and MMAT_ETSTOCK in ('ST','AT', '--')
and trim(MMAT_AFFECT) in ('IMM','LCD', 'SDO')

        
        
      ";

      
        $result = $this->connect->executeQuery($statement);


        $data = $this->connect->fetchResults($result);


        return $this->convertirEnUtf8($data);
    }
    

   

    

    
   



    
}
