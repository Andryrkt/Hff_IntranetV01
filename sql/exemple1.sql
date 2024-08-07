
select
case
when mmat_succ in (select asuc_parc from agr_succ) then asuc_num
else mmat_succ
end as agence,

trim(asuc_lib)||'-'||case (select sce.atab_lib from mmo_imm, agr_tab as sce where mimm_soc = mmat_soc and mimm_nummat = mmat_nummat and sce.atab_code = mimm_service and sce.atab_nom='SER') 
    when null then 'COMMERCIAL'
    else (select sce.atab_lib from mmo_imm, agr_tab as sce where mimm_soc = 'HF' and mimm_nummat = mmat_nummat and sce.atab_code = mimm_service and sce.atab_nom='SER')
end as service,

(select atab_lib from agr_tab where atab_code = mmat_etstock and atab_nom = 'ETM') as groupe,

(select atab_lib from agr_tab where atab_code = mmat_affect and atab_nom = 'AFF'),

mmat_marqmat as constructeur,

mmat_desi as designation,

trim(mmat_typmat) as modele,

mmat_nummat as id_materiel,

trim(mmat_numserie) as numero_serie,

trim(mmat_recalph) as numero_parc,

(select mhir_compteur from mat_hir a where a.mhir_nummat = mmat_nummat and a.mhir_daterel = (select max(b.mhir_daterel) from mat_hir b where b.mhir_nummat = a.mhir_nummat)) as HEURE,

(select mhir_cumcomp from mat_hir a where a.mhir_nummat = mmat_nummat and a.mhir_daterel = (select max(b.mhir_daterel) from mat_hir b where b.mhir_nummat = a.mhir_nummat)) as KM,

(select mhir_daterel from mat_hir a where a.mhir_nummat = mmat_nummat and a.mhir_daterel = (select max(b.mhir_daterel) from mat_hir b where b.mhir_nummat = a.mhir_nummat)) as Date_compteur,

mmat_numparc as Casier_emetteur,

year(mmat_datemser) as annee_modele,

date(mmat_datentr) as Date_achat,

(select nvl(sum(mofi_mt),0) from mat_ofi where mofi_classe = 30 and mofi_ssclasse in (10,11,12,13,14,16,17,18,19) and mofi_numbil = mbil_numbil and mofi_typmt = 'R' and mofi_lib like 'Prix d''achat') as Prix_achat,

(select nvl(sum(mofi_mt),0) from mat_ofi where mofi_classe = 30 and mofi_ssclasse = 15 and mofi_numbil = mbil_numbil and mofi_typmt = 'R') as Amortissement,

(select nvl(sum(mofi_mt),0) from mat_ofi where mofi_classe = 40 and mofi_ssclasse in (21,22,23) and mofi_numbil = mbil_numbil and mofi_typmt = 'R') as ChargeEntretien,

(select nvl(sum(mofi_mt),0) from mat_ofi where mofi_classe = 30 and mofi_ssclasse in (10,11,12,13,14,16,17,18,19) and mofi_numbil = mbil_numbil and mofi_typmt = 'R') as Droits_Taxe,

mmat_nouo as Etat_achat



                from mat_mat, agr_succ, outer mat_bil
                WHERE (MMAT_SUCC in ('01', '40', '50') 
                or MMAT_SUCC IN (SELECT ASUC_PARC FROM AGR_SUCC WHERE ASUC_NUM IN ('01', '40', '50') ))
                and trim(MMAT_ETSTOCK) in ('ST','AT')
                and trim(MMAT_AFFECT) in ('IMM','VTE','LCD','SDO')
                and mmat_soc = 'HF'
                and (mmat_succ = asuc_num or mmat_succ = asuc_parc)
                and mmat_nummat = mbil_nummat
               




  union 







  select 
    case 
        when mmat_succ in (select asuc_parc from agr_succ) 
        then asuc_num 
        else mmat_succ 
    end as agence,


    trim(asuc_lib)||'-'||case (select sce.atab_lib from mmo_imm, agr_tab as sce where mimm_soc=mmat_soc and mimm_nummat=mmat_nummat and sce.atab_code=mimm_service and sce.atab_nom='SER' ) when null 
        then 'COMMERCIAL' 
        else (select sce.atab_lib from mmo_imm, agr_tab as sce where mimm_soc='HF' and mimm_nummat=mmat_nummat and sce.atab_code=mimm_service and sce.atab_nom='SER' ) 
    end as service,
    
    (select atab_lib from agr_tab where atab_code=mmat_etstock and atab_nom='ETM' ) as groupe,

    'COMMANDE',

    mmat_marqmat as constructeur,

    mmat_desi as designation, 
    
    trim(mmat_typmat) as modele,

    mmat_nummat as id_materiel, 
    
    'Encours commande',

    to_char(mmat_numcde),

    (select mhir_compteur from mat_hir a where a.mhir_nummat=mmat_nummat and a.mhir_daterel=(select max(b.mhir_daterel) from mat_hir b where b.mhir_nummat=a.mhir_nummat)) as HEURE,

    (select mhir_cumcomp from mat_hir a where a.mhir_nummat=mmat_nummat and a.mhir_daterel=(select max(b.mhir_daterel) from mat_hir b where b.mhir_nummat=a.mhir_nummat)) as KM,

    (select mhir_daterel from mat_hir a where a.mhir_nummat=mmat_nummat and a.mhir_daterel=(select max(b.mhir_daterel) from mat_hir b where b.mhir_nummat=a.mhir_nummat)) as Date_compteur,

    mmat_numparc,

    year(mmat_datemser) as annee, 

    (select date(fcde_date) from frn_cde where fcde_soc=mmat_soc and fcde_numcde=mmat_numcde),

    (select nvl(sum(mvem_achnetfl),0) from mat_vem where mvem_numcde=mmat_numcde and mvem_nummat=mmat_nummat) as Prix_achat,

    (select nvl(sum(mofi_mt),0) from mat_ofi where mofi_classe=30 and mofi_ssclasse=15 and mofi_numbil=mbil_numbil and mofi_typmt='R' ) as Amortissement,



    (select nvl(sum(mofi_mt),0) from mat_ofi where mofi_classe=40 and mofi_ssclasse in (21,22,23) and mofi_numbil=mbil_numbil and mofi_typmt='R' ) as ChargeEntretien,


    0,


    mmat_nouo as Etat_achat

    
    from mat_mat, agr_succ,

    outer mat_bil
                                
    WHERE
        (MMAT_SUCC in ('01', '40', '50')
        or MMAT_SUCC IN (SELECT ASUC_PARC FROM AGR_SUCC WHERE ASUC_NUM IN ('01', '40', '50') ))
        and trim(MMAT_AFFECT) in ('IMM','VTE','LCD','SDO')
        and mmat_soc = 'HF'
        and (mmat_succ = asuc_num or mmat_succ = asuc_parc)
        and mmat_nummat = mbil_nummat
        and trim(MMAT_ETSTOCK) in ('--')
        and MMAT_ETACHAT = 'CD' and MMAT_ETVENTE = '--'




    
 union



    select
        case
            when mmat_succ in (select asuc_parc from agr_succ) then asuc_num
            else mmat_succ
        end,


        trim(asuc_lib)||'-'|| case (select sce.atab_lib from mmo_imm, agr_tab as sce where mimm_soc = mmat_soc and mimm_nummat = mmat_nummat and sce.atab_code = mimm_service and sce.atab_nom='SER')
            when null then 'COMMERCIAL'
            else(select sce.atab_lib from mmo_imm, agr_tab as sce where mimm_soc = 'HF' and mimm_nummat = mmat_nummat and sce.atab_code = mimm_service and sce.atab_nom='SER')
        end as service,

        (select atab_lib from agr_tab where atab_code = mmat_etstock and atab_nom = 'ETM') as groupe,

        'FLOTTANT', 
        
        mmat_marqmat as constructeur,
                               
        mmat_desi as designation,

        trim(mmat_typmat) as modele,

        mmat_nummat as id_materiel,

        trim(mmat_numserie) as numero_serie,

        trim(mmat_recalph) as numero_parc,

                             
        (select mhir_compteur from mat_hir a where a.mhir_nummat = mmat_nummat and a.mhir_daterel = (select max(b.mhir_daterel) from mat_hir b where b.mhir_nummat = a.mhir_nummat)) as HEURE,

        (select mhir_cumcomp from mat_hir a where a.mhir_nummat = mmat_nummat and a.mhir_daterel = (select max(b.mhir_daterel) from mat_hir b where b.mhir_nummat = a.mhir_nummat)) as KM,

        (select mhir_daterel from mat_hir a where a.mhir_nummat = mmat_nummat and a.mhir_daterel = (select max(b.mhir_daterel) from mat_hir b where b.mhir_nummat = a.mhir_nummat)) as Date_compteur,

        mmat_numparc as Casier_emetteur,

        year(mmat_datemser) as annee_modele,

        date(mmat_datentr) as Date_achat,

        (select nvl(sum(mofi_mt),0) from mat_ofi where mofi_classe = 30 and mofi_ssclasse in (10,11,12,13,14,16,17,18,19) and mofi_numbil = mbil_numbil and mofi_typmt = 'R' and mofi_lib like 'Prix d''achat') as Prix_achat,

        (select nvl(sum(mofi_mt),0) from mat_ofi where mofi_classe = 30 and mofi_ssclasse = 15 and mofi_numbil = mbil_numbil and mofi_typmt = 'R') as Amortissement,

      

        (select nvl(sum(mofi_mt),0) from mat_ofi where mofi_classe = 40 and mofi_ssclasse in (21,22,23) and mofi_numbil = mbil_numbil and mofi_typmt = 'R') as ChargeEntretien,


        (select nvl(sum(mofi_mt),0) from mat_ofi where mofi_classe = 30 and mofi_ssclasse in (10,11,12,13,14,16,17,18,19) and mofi_numbil = mbil_numbil and mofi_typmt = 'R') as Droits_Taxe,

        mmat_nouo as Etat_achat

             from mat_mat, agr_succ, outer mat_bil
        WHERE (MMAT_SUCC in ('01', '40', '50') or MMAT_SUCC IN (SELECT ASUC_PARC FROM AGR_SUCC WHERE ASUC_NUM IN ('01', '40', '50') ))
        and trim(MMAT_ETSTOCK) in ('ST','AT')
        and trim(MMAT_AFFECT) in ('IMM','VTE','LCD','SDO')
        and mmat_soc = 'HF'
        and (mmat_succ = asuc_num or mmat_succ = asuc_parc)
        and mmat_nummat = mbil_nummat


union





 select
    case 
        when mmat_succ in (select asuc_parc from agr_succ) 
        then asuc_num 
        else mmat_succ 
    end as agence, 
    
    trim(asuc_lib)||'-'||case (select sce.atab_lib from mmo_imm, agr_tab as sce where mimm_soc = mmat_soc and mimm_nummat = mmat_nummat and sce.atab_code = mimm_service and sce.atab_nom='SER') 
        when null then 'COMMERCIAL' 
        else (select sce.atab_lib from mmo_imm, agr_tab as sce where mimm_soc = 'HF' and mimm_nummat = mmat_nummat and sce.atab_code = mimm_service and sce.atab_nom='SER') 
    end as service, 
    
    (select atab_lib from agr_tab where atab_code = mmat_etstock and atab_nom = 'ETM') as groupe,
    
    'COMMANDE', 
    
    mmat_marqmat as constructeur, 
    
    mmat_desi as designation, 
    
    trim(mmat_typmat) as modele, 
    
    mmat_nummat as id_materiel,
    
    'Encours commande'as numero_serie,
    
    to_char(mmat_numcde),
    
    (select mhir_compteur from mat_hir a where a.mhir_nummat = mmat_nummat and a.mhir_daterel = (select max(b.mhir_daterel) from mat_hir b where b.mhir_nummat = a.mhir_nummat)) as HEURE,

    (select mhir_cumcomp from mat_hir a where a.mhir_nummat = mmat_nummat and a.mhir_daterel = (select max(b.mhir_daterel) from mat_hir b where b.mhir_nummat = a.mhir_nummat)) as KM,
    
    (select mhir_daterel from mat_hir a where a.mhir_nummat = mmat_nummat and a.mhir_daterel = (select max(b.mhir_daterel) from mat_hir b where b.mhir_nummat = a.mhir_nummat)) as Date_compteur,
    
    mmat_numparc as numero_parc,
    
    year(mmat_datemser) as annee_modele,
    
    (select date(fcde_date) from frn_cde where fcde_soc = mmat_soc and fcde_numcde =mmat_numcde),
    
    (select nvl(sum(mvem_achnetfl),0) from mat_vem where mvem_numcde = mmat_numcde and mvem_nummat = mmat_nummat) as Prix_achat,
    
    (select nvl(sum(mofi_mt),0) from mat_ofi where mofi_classe = 30 and mofi_ssclasse = 15 and mofi_numbil = mbil_numbil and mofi_typmt = 'R') as Amortissement,
    
    (select nvl(sum(mofi_mt),0) from mat_ofi where mofi_classe = 40 and mofi_ssclasse in (21,22,23) and mofi_numbil = mbil_numbil and mofi_typmt = 'R') as ChargeEntretien,
    
    0,
        
    mmat_nouo as Etat_achat
    
    
    from mat_mat, agr_succ, 
    outer mat_bil 
    WHERE (MMAT_SUCC in ('01', '40', '50') 
    or MMAT_SUCC IN (SELECT ASUC_PARC FROM AGR_SUCC WHERE ASUC_NUM IN ('01', '40', '50') )) 
    and trim(MMAT_AFFECT) in ('IMM','VTE','LCD','SDO') and mmat_soc = 'HF' 
    and (mmat_succ = asuc_num or mmat_succ = asuc_parc) 
    and mmat_nummat = mbil_nummat 
    and trim(MMAT_ETSTOCK) in ('--') 
    and MMAT_ETACHAT = 'CD' and MMAT_ETVENTE = '--'



   order by 1, mmat_nummat 