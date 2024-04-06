SELECT 
mmat_desi as designation,
mmat_nummat as id_materiel,
trim(mmat_numserie) as numero_serie,
(SELECT atab_lib 
FROM agr_tab 
JOIN mat_mat ON mmat_natmat = atab_code 
WHERE atab_nom = 'NAT'
AND (
    ('20328' <> '' AND mmat_nummat = '20328')
    OR ('' <> '' AND mmat_numserie = '')   OR ('' <> '' AND mmat_recalph = '')  OR ('20328' = '' AND '' = '' AND 'valeur_recalph' = '')))as groupe,
trim(mmat_recalph) as numero_parc,
mmat_marqmat as constructeur,
date(mmat_datentr) as Date_achat,
year(mmat_datemser) as annee_modele,
trim(mmat_typmat) as modele,
 MIMM_SUCLIEU  as agence,
   MIMM_SERVICE  as service,
mmat_nouo as Etat_achat,
(select nvl(sum(mofi_mt),0) from mat_ofi where mofi_classe = 40 and mofi_ssclasse in (21,22,23) and mofi_numbil = mbil_numbil and mofi_typmt = 'R') as ChargeEntretien,
(select nvl(sum(mofi_mt),0) from mat_ofi where mofi_classe = 30 and mofi_ssclasse = 15 and mofi_numbil = mbil_numbil and mofi_typmt = 'R') as Amortissement,
MHIR_COMPTEUR as HEURE,
mhir_cumcomp  as KM
FROM MAT_MAT, MMO_IMM, MAT_OFI, MAT_BIL, MAT_HIR 
WHERE ('20328' <> '' AND mmat_nummat = '20328')
    OR ('' <> '' AND mmat_numserie = '')   OR ('' <> '' AND mmat_recalph = '')  OR ('20328' = '' AND '' = '' AND 'valeur_recalph' = '')


    SELECT 
mmat_desi as designation,
mmat_nummat as id_materiel,
trim(mmat_numserie) as numero_serie,
atab_lib as groupe,
trim(mmat_recalph) as numero_parc,
mmat_marqmat as constructeur,
date(mmat_datentr) as Date_achat,
year(mmat_datemser) as annee_modele,
trim(mmat_typmat) as modele,
 MIMM_SUCLIEU  as agence,
   MIMM_SERVICE  as service,
mmat_nouo as Etat_achat,
(select nvl(sum(mofi_mt),0) from mat_ofi where mofi_classe = 40 and mofi_ssclasse in (21,22,23) and mofi_numbil = mbil_numbil and mofi_typmt = 'R') as ChargeEntretien,
(select nvl(sum(mofi_mt),0) from mat_ofi where mofi_classe = 30 and mofi_ssclasse = 15 and mofi_numbil = mbil_numbil and mofi_typmt = 'R') as Amortissement,
MHIR_COMPTEUR as HEURE,
mhir_cumcomp  as KM
FROM MAT_MAT, MMO_IMM, MAT_OFI, MAT_BIL, MAT_HIR, agr_tab
WHERE mmat_natmat = atab_code 
AND atab_nom = 'NAT' AND( ('20328' <> '' AND mmat_nummat = '20328')
OR ('' <> '' AND mmat_numserie = '')   OR ('' <> '' AND mmat_recalph = '')  OR ('20328' = '' AND '' = '' AND 'valeur_recalph' = ''))


SELECT 
  mm.mmat_desi AS designation,
  mm.mmat_nummat AS id_materiel,
  TRIM(mm.mmat_numserie) AS numero_serie,
  atb.atab_lib AS groupe,
  TRIM(mm.mmat_recalph) AS numero_parc,
  mm.mmat_marqmat AS constructeur,
  DATE(mm.mmat_datentr) AS Date_achat,
  YEAR(mm.mmat_datemser) AS annee_modele,
  TRIM(mm.mmat_typmat) AS modele,
  mi.MIMM_SUCLIEU AS agence,
  mi.MIMM_SERVICE AS service,
  mm.mmat_nouo AS Etat_achat,
  (select nvl(sum(mofi_mt),0) from mat_ofi where mofi_classe = 40 and mofi_ssclasse in (21,22,23) and mofi_numbil = mbil_numbil and mofi_typmt = 'R') as ChargeEntretien,
(select nvl(sum(mofi_mt),0) from mat_ofi where mofi_classe = 30 and mofi_ssclasse = 15 and mofi_numbil = mbil_numbil and mofi_typmt = 'R') as Amortissement,
  mh.MHIR_COMPTEUR AS HEURE,
  mh.mhir_cumcomp AS KM
FROM MAT_MAT mm, MAT_OFI 
JOIN agr_tab atb ON mm.mmat_natmat = atb.atab_code AND atb.atab_nom = 'NAT'
LEFT JOIN MMO_IMM mi ON ( ('20328' <> '' AND mm.mmat_nummat = '20328') OR ('' <> '' AND mm.mmat_numserie = '')   OR ('' <> '' AND mm.mmat_recalph = '')  OR ('20328' = '' AND '' = '' AND 'valeur_recalph' = ''))
LEFT JOIN MAT_HIR mh ON ( ('20328' <> '' AND mm.mmat_nummat = '20328') OR ('' <> '' AND mm.mmat_numserie = '')   OR ('' <> '' AND mm.mmat_recalph = '')  OR ('20328' = '' AND '' = '' AND 'valeur_recalph' = ''))
-- Incluez d'autres jointures selon les conditions de jointure appropriées
WHERE( ('20328' <> '' AND mm.mmat_nummat = '20328') OR ('' <> '' AND mm.mmat_numserie = '')   OR ('' <> '' AND mm.mmat_recalph = '')  OR ('20328' = '' AND '' = '' AND 'valeur_recalph' = ''))




-- mat_mat
SELECT 
  mm.mmat_desi AS designation,
  mm.mmat_nummat AS id_materiel,
  TRIM(mm.mmat_numserie) AS numero_serie,
  TRIM(mm.mmat_recalph) AS numero_parc,
  mm.mmat_marqmat AS constructeur,
  DATE(mm.mmat_datentr) AS Date_achat,
  YEAR(mm.mmat_datemser) AS annee_modele,
  TRIM(mm.mmat_typmat) AS modele,
  
  mm.mmat_nouo AS Etat_achat,
 
FROM MAT_MAT mm
WHERE ('20328' <> '' AND mm.mmat_nummat = '20328')
   OR ('valeur_numserie' <> '' AND mm.mmat_numserie = 'valeur_numserie')   
   OR ('valeur_recalph' <> '' AND mm.mmat_recalph = 'valeur_recalph');




--requete groupe

SELECT atab_lib as groupe 
FROM agr_tab 
JOIN mat_mat ON mmat_natmat = atab_code 
WHERE atab_nom = 'NAT'
AND (
    ('20328' <> '' AND mmat_nummat = '20328')
    OR ('' <> '' AND mmat_numserie = '')   OR ('' <> '' AND mmat_recalph = '')  OR ('20328' = '' AND '' = '' AND 'valeur_recalph' = ''))

--resuete cout d'aquisition ou chargement entretien
SELECT NVL(SUM(mofi.mofi_mt), 0) as cout_aquisition
   FROM mat_ofi mofi , mat_bil mbil
   WHERE mofi.mofi_classe = 40 AND mofi.mofi_ssclasse IN (21,22,23) 
     AND mofi.mofi_numbil = mbil.mbil_numbil AND mofi.mofi_typmt = 'R'

--amortissement
SELECT NVL(SUM(mofi.mofi_mt), 0)  AS Amortissement
   FROM mat_ofi mofi, mat_bil mbil
   WHERE mofi.mofi_classe = 30 AND mofi.mofi_ssclasse = 15 
     AND mofi.mofi_numbil = mbil.mbil_numbil AND mofi.mofi_typmt = 'R'

--agence et service
SELECT mi.MIMM_SUCLIEU AS agence,
  mi.MIMM_SERVICE AS service
FROM MMO_IMM mi, mat_mat mm
WHERE (('20328' <> '' AND mm.mmat_nummat = '20328')
   OR ('' <> '' AND mm.mmat_numserie = '')   
   OR ('' <> '' AND mm.mmat_recalph = '')) AND mi.mimm_soc = mm.mmat_soc;

--heure et km
SELECT mh.MHIR_COMPTEUR AS HEURE,
  mh.mhir_cumcomp AS KM
  FROM MAT_HIR mh, mat_mat mm
  WHERE (('20328' <> '' AND mm.mmat_nummat = '20328')
   OR ('' <> '' AND mm.mmat_numserie = 'valeur_numserie')   
   OR ('' <> '' AND mm.mmat_recalph = ''))  AND mh.mhir_nummat = mm.mmat_nummat ;


--mitambatra
SELECT 
  mm.mmat_desi AS designation,
  mm.mmat_nummat AS id_materiel,
  TRIM(mm.mmat_numserie) AS numero_serie,
  atb.atab_lib AS groupe,
  TRIM(mm.mmat_recalph) AS numero_parc,
  mm.mmat_marqmat AS constructeur,
  DATE(mm.mmat_datentr) AS Date_achat,
  YEAR(mm.mmat_datemser) AS annee_modele,
  TRIM(mm.mmat_typmat) AS modele,
  mi.MIMM_SUCLIEU AS agence,
  mi.MIMM_SERVICE AS service,
  mm.mmat_nouo AS Etat_achat,
  (SELECT NVL(SUM(mofi.mofi_mt), 0) 
   FROM mat_ofi mofi 
   WHERE mofi.mofi_classe = 40 AND mofi.mofi_ssclasse IN (21,22,23) 
     AND mofi.mofi_numbil = mm.mbil_numbil AND mofi.mofi_typmt = 'R') AS ChargeEntretien,
  (SELECT NVL(SUM(mofi.mofi_mt), 0) 
   FROM mat_ofi mofi 
   WHERE mofi.mofi_classe = 30 AND mofi.mofi_ssclasse = 15 
     AND mofi.mofi_numbil = mm.mbil_numbil AND mofi.mofi_typmt = 'R') AS Amortissement,
  mh.MHIR_COMPTEUR AS HEURE,
  mh.mhir_cumcomp AS KM
FROM MAT_MAT mm
JOIN agr_tab atb ON mm.mmat_natmat = atb.atab_code AND atb.atab_nom = 'NAT'
LEFT JOIN MMO_IMM mi ON mi.some_join_condition 
LEFT JOIN MAT_HIR mh ON 
WHERE ('20328' <> '' AND mm.mmat_nummat = '20328')
   OR ('' <> '' AND mm.mmat_numserie = '')   
   OR ('' <> '' AND mm.mmat_recalph = '');
