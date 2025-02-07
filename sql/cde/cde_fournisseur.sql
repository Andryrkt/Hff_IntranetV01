-- REQ liste fournisseur
SELECT  
FBSE_NUMFOU AS num_fournisseur,
upper(FBSE_NOMFOU) As nom_fournisseur
FROM FRN_BSE, FRN_FOU
WHERE FBSE_NUMFOU = FFOU_NUMFOU 
AND FFOU_SOC = 'HF'
and FBSE_NOMFOU like '%" . $nomFournisseur . "%' 
order by FBSE_NOMFOU 

-- REQ liste fournisseur ameliorer avec jointure JOIN
SELECT  
    FBSE_NUMFOU AS num_fournisseur,
    UPPER(FBSE_NOMFOU) AS nom_fournisseur
FROM 
    FRN_BSE
JOIN 
    FRN_FOU ON FBSE_NUMFOU = FFOU_NUMFOU
WHERE 
    FFOU_SOC = 'HF'
    AND FBSE_NOMFOU LIKE CONCAT('%', ?, '%')
ORDER BY 
    FBSE_NOMFOU;



-- REQ liste commandes fournisseur non réceptionnées

SELECT
FCDE_SUCC||FCDE_SERV  AS code_agence_service,
TRIM(ASUC_LIB)||' - '||trim(ATAB_LIB) AS libelle_agence_service,
FCDE_NUMCDE AS num_cde,
DATE(FCDE_DATE) AS date_cde,
TO_CHAR(FCDE_NUMFOU) AS num_fournisseur ,
FBSE_NOMFOU AS nom_fournisseur,
FCDE_LIB  AS libelle_cde,
FCDE_TTC AS prix_cde_ttc,
FCDE_TTC*FCDE_TXDEV AS prix_cde_ttc_devise,
FCDE_DEVISE AS devise_cde,
FCDE_TYPCDE AS type_cde
FROM FRN_CDE, FRN_CDL, AGR_SUCC, AGR_TAB, FRN_BSE
WHERE FCDE_SUCC = ASUC_NUM
AND FCDE_NUMFOU = FBSE_NUMFOU
AND ATAB_CODE = FCDE_SERV AND ATAB_NOM = 'SER'
AND (FCDE_NUMCDE = FCDL_NUMCDE AND FCDE_SOC = FCDL_SOC AND FCDE_SUCC = FCDL_SUCC)
AND FCDE_NUMCDE  IN (select FLLF_NUMCDE from FRN_LLF WHERE FLLF_SOC = FCDE_SOC AND FLLF_SUCC = FCDE_SUCC)
--and date(FCDE_DATE) >= $dateDebut 
--and date(FCDE_DATE) <= $dateFin
--and FCDE_NUMFOU = $numeroFourniseur
AND FCDE_SOC = 'HF'
AND FCDE_SERV IN ('NEG')
AND (FCDE_TYPCDE <> 'CIS' OR (fcde_typcde = 'CIS' AND Length(to_char(fcde_numfou)) = 7))
AND FCDE_NUMCDE IN (select FCDL_NUMCDE from FRN_CDL WHERE FCDE_SOC = FCDL_SOC)
AND FCDE_TTC <> 0
GROUP by 1,2,3,4,5,6,7,8,9,10,11

UNION ALL

SELECT
FCDE_SUCC||FCDE_SERV  AS code_agence_service,
TRIM(ASUC_LIB)||' - '||TRIM(ATAB_LIB) AS libelle_agence_service,
FCDE_NUMCDE AS num_cde,
DATE(FCDE_DATE) AS date_cde,
TO_CHAR(FCDE_NUMFOU) AS num_fournisseur,
FBSE_NOMFOU AS nom_fournisseur,
FCDE_LIB AS libelle_cde,
Sum(FCDL_SOLDE*FCDL_PXACH) AS prix_cde_ttc,
Sum(FCDL_SOLDE*FCDL_PXACH*FCDE_TXDEV) AS prix_cde_ttc_devise,
FCDE_DEVISE AS devise_cde,
FCDE_TYPCDE AS type_cde
FROM FRN_CDE, FRN_CDL, AGR_SUCC, AGR_TAB, FRN_BSE
WHERE FCDE_SUCC = ASUC_NUM
AND FCDE_NUMFOU = FBSE_NUMFOU
AND ATAB_CODE = FCDE_SERV AND ATAB_NOM = 'SER'
AND (FCDE_NUMCDE = FCDL_NUMCDE AND FCDE_SOC = FCDL_SOC AND FCDE_SUCC = FCDL_SUCC)
AND FCDE_NUMCDE IN (select FLLF_NUMCDE from FRN_LLF WHERE FLLF_SOC = FCDE_SOC AND FLLF_SUCC = FCDE_SUCC)
--and date(FCDE_DATE) >= $dateDebut
--and date(FCDE_DATE) <= $dateFin
--and FCDE_NUMFOU = $numeroFourniseur
AND FCDE_SOC = 'HF'
AND FCDE_SERV IN ('NEG')
AND (FCDE_TYPCDE <> 'CIS' OR (fcde_typcde = 'CIS' AND Length(to_char(fcde_numfou)) = 7))
AND FCDE_NUMCDE IN (select FCDL_NUMCDE from FRN_CDL WHERE FCDE_SOC = FCDL_SOC)
AND (FCDL_QTE <> FCDL_QTELI AND FCDL_QTE <> 0 AND FCDL_SOLDE <> 0)
GROUP by 1,2,3,4,5,6,7,10,11
ORDER by 4, 3

-- table cdefnr_soumis_a_validation
CREATE TABLE cdefnr_soumis_a_validation (
    id INT IDENTITY (1, 1),
    numero_commande_fournisseur VARCHAR(8),
    code_fournisseur VARCHAR(8),
    libelle_fournisseur VARCHAR(200),
    numeroVersion INT,
    date_commande DATE,
    montant_commande DECIMAL(18, 2),
    devise_commande VARCHAR(3),
    date_heure_Soumission DATETIME2,
    statut VARCHAR(50)
    CONSTRAINT PK_cdefnr_soumis_a_validation PRIMARY KEY (id)
);

INSERT INTO type_document(typeDocument, date_creation, date_modification, heure_creation, heure_modification, libelle_document)
VALUES('CDEFRN', '2025-01-07', '2025-01-07', '10:32:16.6800000', '10:32:16.6800000', 'COMMANDE FOURNISSEUR')