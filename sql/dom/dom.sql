TABLE Demande_ordre_mission
ADD agence_emetteur_id INT
ALTER TABLE Demande_ordre_mission
ADD service_emetteur_id INT
ALTER TABLE Demande_ordre_mission
ADD agence_debiteur_id INT
ALTER TABLE Demande_ordre_mission
ADD service_debiteur_id INT

ALTER TABLE Demande_ordre_mission ADD site_id INT

ALTER TABLE Demande_ordre_mission ADD category_id INT

UPDATE Demande_ordre_mission
SET
    Sous_Type_Document = CASE
        WHEN Sous_Type_Document = 'MISSION' THEN '2'
        WHEN Sous_Type_Document = 'COMPLEMENT' THEN '3'
        WHEN Sous_Type_Document = 'FORMATION' THEN '4'
        WHEN Sous_Type_Document = 'MUTATION' THEN '5'
        WHEN Sous_Type_Document = 'FRAIS EXCEPTIONNEL' THEN '10'
        ELSE '0'
    END;

ALTER TABLE Demande_ordre_mission
ALTER COLUMN Sous_Type_Document INT;

UPDATE Personnel
SET
    agence_service_irium_id = CASE
        WHEN Code_AgenceService_Sage = 'AB11' THEN '1'
        WHEN Code_AgenceService_Sage = 'AB21' THEN '2'
        WHEN Code_AgenceService_Sage = 'AB51' THEN '3'
        WHEN Code_AgenceService_Sage = 'AC11' THEN '4'
        WHEN Code_AgenceService_Sage = 'AC12' THEN '5'
        WHEN Code_AgenceService_Sage = 'AC14' THEN '6'
        WHEN Code_AgenceService_Sage = 'AC16' THEN '7'
        WHEN Code_AgenceService_Sage = 'AG11' THEN '8'
        WHEN Code_AgenceService_Sage = 'BB21' THEN '9'
        WHEN Code_AgenceService_Sage = 'BC11' THEN '74'
        WHEN Code_AgenceService_Sage = 'BC15' THEN '11'
        WHEN Code_AgenceService_Sage = 'CB21' THEN '12'
        WHEN Code_AgenceService_Sage = 'CC11' THEN '13'
        WHEN Code_AgenceService_Sage = 'CC21' THEN '14'
        WHEN Code_AgenceService_Sage = 'DA11' THEN '15'
        WHEN Code_AgenceService_Sage = 'DA12' THEN '16'
        WHEN Code_AgenceService_Sage = 'DA13' THEN '17'
        WHEN Code_AgenceService_Sage = 'DA14' THEN '18'
        WHEN Code_AgenceService_Sage = 'DA15' THEN '19'
        WHEN Code_AgenceService_Sage = 'DA16' THEN '20'
        WHEN Code_AgenceService_Sage = 'DA17' THEN '21'
        WHEN Code_AgenceService_Sage = 'DA18' THEN '22'
        WHEN Code_AgenceService_Sage = 'EB51' THEN '23'
        WHEN Code_AgenceService_Sage = 'EC11' THEN '24'
        WHEN Code_AgenceService_Sage = 'ED10' THEN '25'
        WHEN Code_AgenceService_Sage = 'FB21' THEN '26'
        WHEN Code_AgenceService_Sage = 'FC11' THEN '27'
        WHEN Code_AgenceService_Sage = 'HB21' THEN '28'
        WHEN Code_AgenceService_Sage = 'HB51' THEN '29'
        WHEN Code_AgenceService_Sage = 'HE11' THEN '30'
        WHEN Code_AgenceService_Sage = 'HE12' THEN '31'
        WHEN Code_AgenceService_Sage = 'MB21' THEN '32'
        WHEN Code_AgenceService_Sage = 'MC11' THEN '33'
        WHEN Code_AgenceService_Sage = 'MC13' THEN '34'
        WHEN Code_AgenceService_Sage = 'MC21' THEN '35'
        WHEN Code_AgenceService_Sage = 'OD32' THEN '36'
        WHEN Code_AgenceService_Sage = 'RB21' THEN '37'
        WHEN Code_AgenceService_Sage = 'RB51' THEN '38'
        WHEN Code_AgenceService_Sage = 'RC11' THEN '39'
        WHEN Code_AgenceService_Sage = 'RC21' THEN '40'
        WHEN Code_AgenceService_Sage = 'RC22' THEN '41'
        WHEN Code_AgenceService_Sage = 'RC23' THEN '42'
        WHEN Code_AgenceService_Sage = 'RC24' THEN '43'
        WHEN Code_AgenceService_Sage = 'TD11' THEN '44'
        WHEN Code_AgenceService_Sage = 'TD12' THEN '45'
        WHEN Code_AgenceService_Sage = 'TD16' THEN '46'
        WHEN Code_AgenceService_Sage = 'TD31' THEN '47'
        WHEN Code_AgenceService_Sage = 'AB41' THEN '48'
        WHEN Code_AgenceService_Sage = 'MB41' THEN '49'
        WHEN Code_AgenceService_Sage = 'BB41' THEN '50'
        WHEN Code_AgenceService_Sage = 'OD33' THEN '58'
        WHEN Code_AgenceService_Sage = 'PB21' THEN '59'
        WHEN Code_AgenceService_Sage = 'PC11' THEN '60'
        WHEN Code_AgenceService_Sage = 'OD10' THEN '61'
        WHEN Code_AgenceService_Sage = 'AC17' THEN '62'
        WHEN Code_AgenceService_Sage = 'AB71' THEN '63'
        WHEN Code_AgenceService_Sage = 'SB21' THEN '64'
        WHEN Code_AgenceService_Sage = 'SC11' THEN '65'
        WHEN Code_AgenceService_Sage = 'SA12' THEN '66'
        WHEN Code_AgenceService_Sage = 'SA18' THEN '67'
        WHEN Code_AgenceService_Sage = 'MC14' THEN '68'
        WHEN Code_AgenceService_Sage = 'RC25' THEN '69'
        WHEN Code_AgenceService_Sage = 'RC26' THEN '70'
        WHEN Code_AgenceService_Sage = 'TD32' THEN '71'
        WHEN Code_AgenceService_Sage = 'TD33' THEN '72'
        ELSE '0'
    END;

UPDATE Demande_ordre_mission
SET
    category_id = CASE
        WHEN Categorie = 'AGENTS DE MAITRISE, EMPLOYES SPECIALISES' THEN '1'
        WHEN Categorie = 'CADRE HC' THEN '2'
        WHEN Categorie = 'CHEF DE SERVICE' THEN '3'
        WHEN Categorie = 'OUVRIERS ET CHAUFFEURS' THEN '4'
        WHEN Categorie = 'TOUTE CATEGORIE' THEN '5'
        WHEN Categorie = 'CHAUFFEURS PORTE CHAR' THEN '6'
        WHEN Categorie = 'AIDE CHAUFFEUR' THEN '7'
        ELSE category_id
    END;

UPDATE Demande_ordre_mission
SET
    site_id = CASE
        WHEN Site = 'AUTRES VILLES' THEN '1'
        WHEN Site = 'HORS TANA MOINS DE 24H' THEN '2'
        WHEN Site = 'ZONES ENCLAVEES' THEN '3'
        WHEN Site = 'ZONES TOURISTIQUES' THEN '4'
        WHEN Site = 'FORT-DAUPHIN' THEN '5'
        WHEN Site = 'AUTRES SITE ENCLAVES' THEN '6'
        WHEN Site = 'HORS TANA' THEN '7'
        WHEN Site = 'TANA' THEN '8'
        ELSE NULL
    END;

UPDATE Demande_ordre_mission
SET
    agence_emetteur_id = CASE
        WHEN LEFT(
            Code_AgenceService_Debiteur,
            2
        ) = '01' THEN '1'
        WHEN LEFT(
            Code_AgenceService_Debiteur,
            2
        ) = '02' THEN '2'
        WHEN LEFT(
            Code_AgenceService_Debiteur,
            2
        ) = '20' THEN '3'
        WHEN LEFT(
            Code_AgenceService_Debiteur,
            2
        ) = '30' THEN '4'
        WHEN LEFT(
            Code_AgenceService_Debiteur,
            2
        ) = '40' THEN '5'
        WHEN LEFT(
            Code_AgenceService_Debiteur,
            2
        ) = '50' THEN '6'
        WHEN LEFT(
            Code_AgenceService_Debiteur,
            2
        ) = '60' THEN '7'
        WHEN LEFT(
            Code_AgenceService_Debiteur,
            2
        ) = '80' THEN '8'
        WHEN LEFT(
            Code_AgenceService_Debiteur,
            2
        ) = '90' THEN '9'
        WHEN LEFT(
            Code_AgenceService_Debiteur,
            2
        ) = '91' THEN '10'
        WHEN LEFT(
            Code_AgenceService_Debiteur,
            2
        ) = '92' THEN '11'
        ELSE '0'
    END;

UPDATE Demande_ordre_mission
SET
    service_emetteur_id = CASE
        WHEN RIGHT(
            Code_AgenceService_Debiteur,
            3
        ) = 'NEG' THEN '1'
        WHEN RIGHT(
            Code_AgenceService_Debiteur,
            3
        ) = 'COM' THEN '2'
        WHEN RIGHT(
            Code_AgenceService_Debiteur,
            3
        ) = 'ATE' THEN '3'
        WHEN RIGHT(
            Code_AgenceService_Debiteur,
            3
        ) = 'CSP' THEN '4'
        WHEN RIGHT(
            Code_AgenceService_Debiteur,
            3
        ) = 'GAR' THEN '5'
        WHEN RIGHT(
            Code_AgenceService_Debiteur,
            3
        ) = 'FOR' THEN '6'
        WHEN RIGHT(
            Code_AgenceService_Debiteur,
            3
        ) = 'ASS' THEN '7'
        WHEN RIGHT(
            Code_AgenceService_Debiteur,
            3
        ) = 'MAN' THEN '8'
        WHEN RIGHT(
            Code_AgenceService_Debiteur,
            3
        ) = 'LCD' THEN '9'
        WHEN RIGHT(
            Code_AgenceService_Debiteur,
            3
        ) = 'DIR' THEN '10'
        WHEN RIGHT(
            Code_AgenceService_Debiteur,
            3
        ) = 'FIN' THEN '11'
        WHEN RIGHT(
            Code_AgenceService_Debiteur,
            3
        ) = 'PER' THEN '12'
        WHEN RIGHT(
            Code_AgenceService_Debiteur,
            3
        ) = 'INF' THEN '13'
        WHEN RIGHT(
            Code_AgenceService_Debiteur,
            3
        ) = 'IMM' THEN '14'
        WHEN RIGHT(
            Code_AgenceService_Debiteur,
            3
        ) = 'TRA' THEN '15'
        WHEN RIGHT(
            Code_AgenceService_Debiteur,
            3
        ) = 'APP' THEN '16'
        WHEN RIGHT(
            Code_AgenceService_Debiteur,
            3
        ) = 'UMP' THEN '17'
        WHEN RIGHT(
            Code_AgenceService_Debiteur,
            3
        ) = 'ENG' THEN '19'
        WHEN RIGHT(
            Code_AgenceService_Debiteur,
            3
        ) = 'VAN' THEN '20'
        WHEN RIGHT(
            Code_AgenceService_Debiteur,
            3
        ) = 'GIR' THEN '21'
        WHEN RIGHT(
            Code_AgenceService_Debiteur,
            3
        ) = 'THO' THEN '22'
        WHEN RIGHT(
            Code_AgenceService_Debiteur,
            3
        ) = 'TSI' THEN '23'
        WHEN RIGHT(
            Code_AgenceService_Debiteur,
            3
        ) = 'LTV' THEN '24'
        WHEN RIGHT(
            Code_AgenceService_Debiteur,
            3
        ) = 'LFD' THEN '25'
        WHEN RIGHT(
            Code_AgenceService_Debiteur,
            3
        ) = 'LBV' THEN '26'
        WHEN RIGHT(
            Code_AgenceService_Debiteur,
            3
        ) = 'MAH' THEN '27'
        WHEN RIGHT(
            Code_AgenceService_Debiteur,
            3
        ) = 'NOS' THEN '28'
        WHEN RIGHT(
            Code_AgenceService_Debiteur,
            3
        ) = 'TUL' THEN '29'
        WHEN RIGHT(
            Code_AgenceService_Debiteur,
            3
        ) = 'AMB' THEN '30'
        WHEN RIGHT(
            Code_AgenceService_Debiteur,
            3
        ) = 'FLE' THEN '31'
        WHEN RIGHT(
            Code_AgenceService_Debiteur,
            3
        ) = 'TSD' THEN '32'
        WHEN RIGHT(
            Code_AgenceService_Debiteur,
            3
        ) = 'VAT' THEN '33'
        WHEN RIGHT(
            Code_AgenceService_Debiteur,
            3
        ) = 'BLK' THEN '34'
        WHEN RIGHT(
            Code_AgenceService_Debiteur,
            3
        ) = 'ENG' THEN '35'
        WHEN RIGHT(
            Code_AgenceService_Debiteur,
            3
        ) = 'MAS' THEN '36'
        WHEN RIGHT(
            Code_AgenceService_Debiteur,
            3
        ) = 'MAP' THEN '37'
        WHEN RIGHT(
            Code_AgenceService_Debiteur,
            3
        ) = 'ADM' THEN '38'
        WHEN RIGHT(
            Code_AgenceService_Debiteur,
            3
        ) = 'APP' THEN '39'
        WHEN RIGHT(
            Code_AgenceService_Debiteur,
            3
        ) = 'LEV' THEN '40'
        WHEN RIGHT(
            Code_AgenceService_Debiteur,
            3
        ) = 'LR6' THEN '41'
        WHEN RIGHT(
            Code_AgenceService_Debiteur,
            3
        ) = 'LST' THEN '42'
        WHEN RIGHT(
            Code_AgenceService_Debiteur,
            3
        ) = 'LCJ' THEN '43'
        WHEN RIGHT(
            Code_AgenceService_Debiteur,
            3
        ) = 'TSI' THEN '44'
        WHEN RIGHT(
            Code_AgenceService_Debiteur,
            3
        ) = 'SLR' THEN '45'
        WHEN RIGHT(
            Code_AgenceService_Debiteur,
            3
        ) = 'LGR' THEN '46'
        ELSE '0'
    END;

UPDATE Demande_ordre_mission
SET
    agence_debiteur_id = CASE
        WHEN LEFT(Debiteur, 2) = '01' THEN '1'
        WHEN LEFT(Debiteur, 2) = '02' THEN '2'
        WHEN LEFT(Debiteur, 2) = '20' THEN '3'
        WHEN LEFT(Debiteur, 2) = '30' THEN '4'
        WHEN LEFT(Debiteur, 2) = '40' THEN '5'
        WHEN LEFT(Debiteur, 2) = '50' THEN '6'
        WHEN LEFT(Debiteur, 2) = '60' THEN '7'
        WHEN LEFT(Debiteur, 2) = '80' THEN '8'
        WHEN LEFT(Debiteur, 2) = '90' THEN '9'
        WHEN LEFT(Debiteur, 2) = '91' THEN '10'
        WHEN LEFT(Debiteur, 2) = '92' THEN '11'
        ELSE agence_emetteur_id
    END;

UPDATE Demande_ordre_mission
SET
    service_debiteur_id = CASE
        WHEN SUBSTRING(
            Debiteur,
            CHARINDEX ('-', Debiteur) + 1,
            3
        ) = 'NEG' THEN '1'
        WHEN SUBSTRING(
            Debiteur,
            CHARINDEX ('-', Debiteur) + 1,
            3
        ) = 'COM' THEN '2'
        WHEN SUBSTRING(
            Debiteur,
            CHARINDEX ('-', Debiteur) + 1,
            3
        ) = 'ATE' THEN '3'
        WHEN SUBSTRING(
            Debiteur,
            CHARINDEX ('-', Debiteur) + 1,
            3
        ) = 'CSP' THEN '4'
        WHEN SUBSTRING(
            Debiteur,
            CHARINDEX ('-', Debiteur) + 1,
            3
        ) = 'GAR' THEN '5'
        WHEN SUBSTRING(
            Debiteur,
            CHARINDEX ('-', Debiteur) + 1,
            3
        ) = 'FOR' THEN '6'
        WHEN SUBSTRING(
            Debiteur,
            CHARINDEX ('-', Debiteur) + 1,
            3
        ) = 'ASS' THEN '7'
        WHEN SUBSTRING(
            Debiteur,
            CHARINDEX ('-', Debiteur) + 1,
            3
        ) = 'MAN' THEN '8'
        WHEN SUBSTRING(
            Debiteur,
            CHARINDEX ('-', Debiteur) + 1,
            3
        ) = 'LCD' THEN '9'
        WHEN SUBSTRING(
            Debiteur,
            CHARINDEX ('-', Debiteur) + 1,
            3
        ) = 'DIR' THEN '10'
        WHEN SUBSTRING(
            Debiteur,
            CHARINDEX ('-', Debiteur) + 1,
            3
        ) = 'FIN' THEN '11'
        WHEN SUBSTRING(
            Debiteur,
            CHARINDEX ('-', Debiteur) + 1,
            3
        ) = 'PER' THEN '12'
        WHEN SUBSTRING(
            Debiteur,
            CHARINDEX ('-', Debiteur) + 1,
            3
        ) = 'INF' THEN '13'
        WHEN SUBSTRING(
            Debiteur,
            CHARINDEX ('-', Debiteur) + 1,
            3
        ) = 'IMM' THEN '14'
        WHEN SUBSTRING(
            Debiteur,
            CHARINDEX ('-', Debiteur) + 1,
            3
        ) = 'TRA' THEN '15'
        WHEN SUBSTRING(
            Debiteur,
            CHARINDEX ('-', Debiteur) + 1,
            3
        ) = 'APP' THEN '16'
        WHEN SUBSTRING(
            Debiteur,
            CHARINDEX ('-', Debiteur) + 1,
            3
        ) = 'UMP' THEN '17'
        WHEN SUBSTRING(
            Debiteur,
            CHARINDEX ('-', Debiteur) + 1,
            3
        ) = 'ENG' THEN '19'
        WHEN SUBSTRING(
            Debiteur,
            CHARINDEX ('-', Debiteur) + 1,
            3
        ) = 'VAN' THEN '20'
        WHEN SUBSTRING(
            Debiteur,
            CHARINDEX ('-', Debiteur) + 1,
            3
        ) = 'GIR' THEN '21'
        WHEN SUBSTRING(
            Debiteur,
            CHARINDEX ('-', Debiteur) + 1,
            3
        ) = 'THO' THEN '22'
        WHEN SUBSTRING(
            Debiteur,
            CHARINDEX ('-', Debiteur) + 1,
            3
        ) = 'TSI' THEN '23'
        WHEN SUBSTRING(
            Debiteur,
            CHARINDEX ('-', Debiteur) + 1,
            3
        ) = 'LTV' THEN '24'
        WHEN SUBSTRING(
            Debiteur,
            CHARINDEX ('-', Debiteur) + 1,
            3
        ) = 'LFD' THEN '25'
        WHEN SUBSTRING(
            Debiteur,
            CHARINDEX ('-', Debiteur) + 1,
            3
        ) = 'LBV' THEN '26'
        WHEN SUBSTRING(
            Debiteur,
            CHARINDEX ('-', Debiteur) + 1,
            3
        ) = 'MAH' THEN '27'
        WHEN SUBSTRING(
            Debiteur,
            CHARINDEX ('-', Debiteur) + 1,
            3
        ) = 'NOS' THEN '28'
        WHEN SUBSTRING(
            Debiteur,
            CHARINDEX ('-', Debiteur) + 1,
            3
        ) = 'TUL' THEN '29'
        WHEN SUBSTRING(
            Debiteur,
            CHARINDEX ('-', Debiteur) + 1,
            3
        ) = 'AMB' THEN '30'
        WHEN SUBSTRING(
            Debiteur,
            CHARINDEX ('-', Debiteur) + 1,
            3
        ) = 'FLE' THEN '31'
        WHEN SUBSTRING(
            Debiteur,
            CHARINDEX ('-', Debiteur) + 1,
            3
        ) = 'TSD' THEN '32'
        WHEN SUBSTRING(
            Debiteur,
            CHARINDEX ('-', Debiteur) + 1,
            3
        ) = 'VAT' THEN '33'
        WHEN SUBSTRING(
            Debiteur,
            CHARINDEX ('-', Debiteur) + 1,
            3
        ) = 'BLK' THEN '34'
        WHEN SUBSTRING(
            Debiteur,
            CHARINDEX ('-', Debiteur) + 1,
            3
        ) = 'ENG' THEN '35'
        WHEN SUBSTRING(
            Debiteur,
            CHARINDEX ('-', Debiteur) + 1,
            3
        ) = 'MAS' THEN '36'
        WHEN SUBSTRING(
            Debiteur,
            CHARINDEX ('-', Debiteur) + 1,
            3
        ) = 'MAP' THEN '37'
        WHEN SUBSTRING(
            Debiteur,
            CHARINDEX ('-', Debiteur) + 1,
            3
        ) = 'ADM' THEN '38'
        WHEN SUBSTRING(
            Debiteur,
            CHARINDEX ('-', Debiteur) + 1,
            3
        ) = 'APP' THEN '39'
        WHEN SUBSTRING(
            Debiteur,
            CHARINDEX ('-', Debiteur) + 1,
            3
        ) = 'LEV' THEN '40'
        WHEN SUBSTRING(
            Debiteur,
            CHARINDEX ('-', Debiteur) + 1,
            3
        ) = 'LR6' THEN '41'
        WHEN SUBSTRING(
            Debiteur,
            CHARINDEX ('-', Debiteur) + 1,
            3
        ) = 'LST' THEN '42'
        WHEN SUBSTRING(
            Debiteur,
            CHARINDEX ('-', Debiteur) + 1,
            3
        ) = 'LCJ' THEN '43'
        WHEN SUBSTRING(
            Debiteur,
            CHARINDEX ('-', Debiteur) + 1,
            3
        ) = 'TSI' THEN '44'
        WHEN SUBSTRING(
            Debiteur,
            CHARINDEX ('-', Debiteur) + 1,
            3
        ) = 'SLR' THEN '45'
        WHEN SUBSTRING(
            Debiteur,
            CHARINDEX ('-', Debiteur) + 1,
            3
        ) = 'LGR' THEN '46'
        ELSE service_emetteur_id
    END;

UPDATE Demande_ordre_mission
SET
    Code_Statut = 'ANN',
    ID_Statut_Demande = 9
where
    Numero_Ordre_Mission = 'DOM24100448'
