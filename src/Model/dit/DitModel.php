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
        trim(mmat_recalph) as num_parc,


          (select nvl(sum(mofi_mt),0) from mat_ofi where mofi_classe = 30 and mofi_ssclasse in (10,11,12,13,14,16,17,18,19) and mofi_numbil = mbil_numbil and mofi_typmt = 'R') as Prix_achat,
          (select nvl(sum(mofi_mt),0) from mat_ofi where mofi_classe = 30 and mofi_ssclasse = 15 and mofi_numbil = mbil_numbil and mofi_typmt = 'R') as Amortissement,

          (select nvl(sum(mofi_mt),0) from mat_ofi where mofi_classe = 10 and mofi_ssclasse in (100,21,22,23) and mofi_numbil = mbil_numbil and mofi_typmt = 'R') as ChiffreAffaires,
          (select nvl(sum(mofi_mt),0) from mat_ofi where mofi_classe = 40 and mofi_ssclasse in (100,110) and mofi_numbil = mbil_numbil and mofi_typmt = 'R') as ChargeLocative,
          (select nvl(sum(mofi_mt),0) from mat_ofi where mofi_classe = 40 and mofi_ssclasse in (21,22,23) and mofi_numbil = mbil_numbil and mofi_typmt = 'R') as ChargeEntretien

        from mat_mat, agr_succ, outer mat_bil
        where (MMAT_SUCC in ('01', '20', '30', '40', '50', '60', '80', '90','91','92') or MMAT_SUCC in (SELECT ASUC_PARC FROM AGR_SUCC WHERE ASUC_NUM in ('01', '20', '30', '40', '50', '60', '80', '90','91','92') ) )
         and MMAT_ETSTOCK in ('ST','AT')
        and trim(MMAT_AFFECT) in ('IMM','VTE','LCD','SDO')

        and decode(trim(mmat_natmat),'ATT' , 'ENGINS DE CHANT. ET TERRAS.', 'ATI','ENGINS DE CHANT. ET TERRAS.', 'BHL' , 'ENGINS DE CHANT. ET TERRAS.', 'BULL' , 'ENGINS DE CHANT. ET TERRAS.',  'DUMP', 'ENGINS DE CHANT. ET TERRAS.', 'HEX' , 'ENGINS DE CHANT. ET TERRAS.', 'WHL' , 'ENGINS DE CHANT. ET TERRAS.', 'TRAC', 'ENGINS DE CHANT. ET TERRAS.', 'CHAF', 'ENGINS DE CHANT. ET TERRAS.',  'COPA', 'ENGINS DE CHANT. ET TERRAS.', 'NIV', 'ENGINS DE CHANT. ET TERRAS.',  'CAMC' , 'EQPT TRANSPORT', 'CAMI' , 'EQPT TRANSPORT', 'CAMP' , 'EQPT TRANSPORT', 'HIAB' , 'EQPT TRANSPORT', 'PORT' , 'EQPT TRANSPORT',   'QUAD', 'EQPT TRANSPORT', 'CAMT' , 'EQPT TRANSPORT','REMO' , 'EQPT TRANSPORT',  'CAMG' , 'EQPT TRANSPORT', 'GRUA' , 'EQP LEVAGE', 'GRU' , 'EQP LEVAGE', 'GRUR' , 'EQP LEVAGE', 'GRUT' , 'EQP LEVAGE', 'ELEV' , 'EQP LEVAGE', 'LIFT', 'EQP LEVAGE','LEVA' , 'EQP LEVAGE',  'GREN' , 'GROUPE ELECTROGENE', 'GRRE' , 'GROUPE ELECTROGENE', 'GRES' , 'GROUPE ELECTROGENE', 'ALTE', 'GROUPE ELECTROGENE', 'INVE', 'GROUPE ELECTROGENE', 'TRSF', 'GROUPE ELECTROGENE', 'AIGU' , 'EQPT DE CHANTIER', 'BETO' , 'EQPT DE CHANTIER', 'CITE' , 'EQPT DE CHANTIER', 'COMP' , 'EQPT DE CHANTIER', 'DIV' , 'EQPT DE CHANTIER', 'MAPI', 'EQPT DE CHANTIER',  'MAPE', 'EQPT DE CHANTIER', 'MAHY', 'EQPT DE CHANTIER','BRIS', 'EQPT DE CHANTIER','BROY', 'EQPT DE CHANTIER','CAC', 'EQPT DE CHANTIER',  'CONC' , 'EQPT DE CHANTIER','FORA', 'EQPT DE CHANTIER',  'TRUE' , 'EQPT DE CHANTIER', 'MAE' , 'EQPT DE CHANTIER', 'MOTA' , 'EQPT DE CHANTIER', 'MOTE' , 'EQPT DE CHANTIER', 'NETHP' , 'EQPT DE CHANTIER', 'OUTB' , 'EQPT DE CHANTIER', 'POMP' , 'EQPT DE CHANTIER',  'SOUD' , 'EQPT DE CHANTIER', 'PINC', 'EQPT DE CHANTIER', 'ACCE','EQPT DE CHANTIER',  'OUTI' , 'EQPT DE CHANTIER', 'NETH' , 'EQPT DE CHANTIER', 'POAS' , 'EQPT DE CHANTIER', 'AGRI', 'MAT. AGRICOLE', 'CHAR', 'MAT. AGRICOLE', 'AVIO', 'AVIATION',  'ACCP', 'AUTRES', 'BAT', 'AUTRES', 'BGLW', 'AUTRES', 'CHAU', 'AUTRES', 'CLIM', 'AUTRES', 'DEBR', 'AUTRES', 'DIVE', 'AUTRES', 'BGLW', 'AUTRES', 'GOMM', 'AUTRES', 'HEL', 'AUTRES', 'IMMO', 'AUTRES', 'MCB', 'AUTRES', 'MIDL', 'AUTRES', 'MOTH', 'AUTRES', 'MOTI', 'AUTRES', 'MOTO', 'AUTRES', 'ONDC', 'AUTRES', 'ONDR', 'AUTRES', 'PRES', 'AUTRES', 'PSO', 'AUTRES', 'RESA', 'AUTRES', 'SAMA', 'AUTRES', 'SCIE', 'AUTRES', 'SECH', 'AUTRES', 'SURP', 'AUTRES', 'TAMA', 'AUTRES', 'TGAZ', 'AUTRES', 'TRON', 'AUTRES', 'VEH', 'AUTRES', 'VL', 'AUTRES')  IN ('ENGINS DE CHANT. ET TERRAS.', 'EQPT TRANSPORT', 'EQP LEVAGE', 'GROUPE ELECTROGENE', 'EQPT DE CHANTIER', 'MAT. AGRICOLE')
        and mmat_soc = 'HF'
        and (mmat_succ = asuc_num or mmat_succ = asuc_parc)
        and (mmat_nummat = mbil_nummat and mbil_dateclot < '01/01/1900')
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
