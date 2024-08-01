ALTER TABLE Demande_Mouvement_Materiel
ADD agence_emetteur_id INT
ALTER TABLE Demande_Mouvement_Materiel
ADD service_emetteur_id INT
ALTER TABLE Demande_Mouvement_Materiel
ADD agence_debiteur_id INT
ALTER TABLE Demande_Mouvement_Materiel
ADD service_debiteur_id INT

UPDATE Demande_Mouvement_Materiel
SET
    agence_emetteur_id = CASE
        WHEN LEFT(Agence_Service_Emetteur, 2) = '01' THEN '1'
        WHEN LEFT(Agence_Service_Emetteur, 2) = '02' THEN '2'
        WHEN LEFT(Agence_Service_Emetteur, 2) = '20' THEN '3'
        WHEN LEFT(Agence_Service_Emetteur, 2) = '30' THEN '4'
        WHEN LEFT(Agence_Service_Emetteur, 2) = '40' THEN '5'
        WHEN LEFT(Agence_Service_Emetteur, 2) = '50' THEN '6'
        WHEN LEFT(Agence_Service_Emetteur, 2) = '60' THEN '7'
        WHEN LEFT(Agence_Service_Emetteur, 2) = '80' THEN '8'
        WHEN LEFT(Agence_Service_Emetteur, 2) = '90' THEN '9'
        WHEN LEFT(Agence_Service_Emetteur, 2) = '91' THEN '10'
        WHEN LEFT(Agence_Service_Emetteur, 2) = '92' THEN '11'
        ELSE '0'
    END;

UPDATE Demande_Mouvement_Materiel
SET
    service_emetteur_id = CASE
        WHEN RIGHT(Agence_Service_Emetteur, 3) = 'NEG' THEN '1'
        WHEN RIGHT(Agence_Service_Emetteur, 3) = 'COM' THEN '2'
        WHEN RIGHT(Agence_Service_Emetteur, 3) = 'ATE' THEN '3'
        WHEN RIGHT(Agence_Service_Emetteur, 3) = 'CSP' THEN '4'
        WHEN RIGHT(Agence_Service_Emetteur, 3) = 'GAR' THEN '5'
        WHEN RIGHT(Agence_Service_Emetteur, 3) = 'FOR' THEN '6'
        WHEN RIGHT(Agence_Service_Emetteur, 3) = 'ASS' THEN '7'
        WHEN RIGHT(Agence_Service_Emetteur, 3) = 'MAN' THEN '8'
        WHEN RIGHT(Agence_Service_Emetteur, 3) = 'LCD' THEN '9'
        WHEN RIGHT(Agence_Service_Emetteur, 3) = 'DIR' THEN '10'
        WHEN RIGHT(Agence_Service_Emetteur, 3) = 'FIN' THEN '11'
        WHEN RIGHT(Agence_Service_Emetteur, 3) = 'PER' THEN '12'
        WHEN RIGHT(Agence_Service_Emetteur, 3) = 'INF' THEN '13'
        WHEN RIGHT(Agence_Service_Emetteur, 3) = 'IMM' THEN '14'
        WHEN RIGHT(Agence_Service_Emetteur, 3) = 'TRA' THEN '15'
        WHEN RIGHT(Agence_Service_Emetteur, 3) = 'APP' THEN '16'
        WHEN RIGHT(Agence_Service_Emetteur, 3) = 'UMP' THEN '17'
        WHEN RIGHT(Agence_Service_Emetteur, 3) = 'ENG' THEN '19'
        WHEN RIGHT(Agence_Service_Emetteur, 3) = 'VAN' THEN '20'
        WHEN RIGHT(Agence_Service_Emetteur, 3) = 'GIR' THEN '21'
        WHEN RIGHT(Agence_Service_Emetteur, 3) = 'THO' THEN '22'
        WHEN RIGHT(Agence_Service_Emetteur, 3) = 'TSI' THEN '23'
        WHEN RIGHT(Agence_Service_Emetteur, 3) = 'LTV' THEN '24'
        WHEN RIGHT(Agence_Service_Emetteur, 3) = 'LFD' THEN '25'
        WHEN RIGHT(Agence_Service_Emetteur, 3) = 'LBV' THEN '26'
        WHEN RIGHT(Agence_Service_Emetteur, 3) = 'MAH' THEN '27'
        WHEN RIGHT(Agence_Service_Emetteur, 3) = 'NOS' THEN '28'
        WHEN RIGHT(Agence_Service_Emetteur, 3) = 'TUL' THEN '29'
        WHEN RIGHT(Agence_Service_Emetteur, 3) = 'AMB' THEN '30'
        WHEN RIGHT(Agence_Service_Emetteur, 3) = 'FLE' THEN '31'
        WHEN RIGHT(Agence_Service_Emetteur, 3) = 'TSD' THEN '32'
        WHEN RIGHT(Agence_Service_Emetteur, 3) = 'VAT' THEN '33'
        WHEN RIGHT(Agence_Service_Emetteur, 3) = 'BLK' THEN '34'
        WHEN RIGHT(Agence_Service_Emetteur, 3) = 'ENG' THEN '35'
        WHEN RIGHT(Agence_Service_Emetteur, 3) = 'MAS' THEN '36'
        WHEN RIGHT(Agence_Service_Emetteur, 3) = 'MAP' THEN '37'
        WHEN RIGHT(Agence_Service_Emetteur, 3) = 'ADM' THEN '38'
        WHEN RIGHT(Agence_Service_Emetteur, 3) = 'APP' THEN '39'
        WHEN RIGHT(Agence_Service_Emetteur, 3) = 'LEV' THEN '40'
        WHEN RIGHT(Agence_Service_Emetteur, 3) = 'LR6' THEN '41'
        WHEN RIGHT(Agence_Service_Emetteur, 3) = 'LST' THEN '42'
        WHEN RIGHT(Agence_Service_Emetteur, 3) = 'LCJ' THEN '43'
        WHEN RIGHT(Agence_Service_Emetteur, 3) = 'TSI' THEN '44'
        WHEN RIGHT(Agence_Service_Emetteur, 3) = 'SLR' THEN '45'
        WHEN RIGHT(Agence_Service_Emetteur, 3) = 'LGR' THEN '46'
        ELSE '0'
    END;

UPDATE Demande_Mouvement_Materiel
SET
    agence_debiteur_id = CASE
        WHEN LEFT(
            Agence_Service_Destinataire,
            2
        ) = '01' THEN '1'
        WHEN LEFT(
            Agence_Service_Destinataire,
            2
        ) = '02' THEN '2'
        WHEN LEFT(
            Agence_Service_Destinataire,
            2
        ) = '20' THEN '3'
        WHEN LEFT(
            Agence_Service_Destinataire,
            2
        ) = '30' THEN '4'
        WHEN LEFT(
            Agence_Service_Destinataire,
            2
        ) = '40' THEN '5'
        WHEN LEFT(
            Agence_Service_Destinataire,
            2
        ) = '50' THEN '6'
        WHEN LEFT(
            Agence_Service_Destinataire,
            2
        ) = '60' THEN '7'
        WHEN LEFT(
            Agence_Service_Destinataire,
            2
        ) = '80' THEN '8'
        WHEN LEFT(
            Agence_Service_Destinataire,
            2
        ) = '90' THEN '9'
        WHEN LEFT(
            Agence_Service_Destinataire,
            2
        ) = '91' THEN '10'
        WHEN LEFT(
            Agence_Service_Destinataire,
            2
        ) = '92' THEN '11'
        ELSE '0'
    END;

UPDATE Demande_Mouvement_Materiel
SET
    service_debiteur_id = CASE
        WHEN RIGHT(
            Agence_Service_Destinataire,
            3
        ) = 'NEG' THEN '1'
        WHEN RIGHT(
            Agence_Service_Destinataire,
            3
        ) = 'COM' THEN '2'
        WHEN RIGHT(
            Agence_Service_Destinataire,
            3
        ) = 'ATE' THEN '3'
        WHEN RIGHT(
            Agence_Service_Destinataire,
            3
        ) = 'CSP' THEN '4'
        WHEN RIGHT(
            Agence_Service_Destinataire,
            3
        ) = 'GAR' THEN '5'
        WHEN RIGHT(
            Agence_Service_Destinataire,
            3
        ) = 'FOR' THEN '6'
        WHEN RIGHT(
            Agence_Service_Destinataire,
            3
        ) = 'ASS' THEN '7'
        WHEN RIGHT(
            Agence_Service_Destinataire,
            3
        ) = 'MAN' THEN '8'
        WHEN RIGHT(
            Agence_Service_Destinataire,
            3
        ) = 'LCD' THEN '9'
        WHEN RIGHT(
            Agence_Service_Destinataire,
            3
        ) = 'DIR' THEN '10'
        WHEN RIGHT(
            Agence_Service_Destinataire,
            3
        ) = 'FIN' THEN '11'
        WHEN RIGHT(
            Agence_Service_Destinataire,
            3
        ) = 'PER' THEN '12'
        WHEN RIGHT(
            Agence_Service_Destinataire,
            3
        ) = 'INF' THEN '13'
        WHEN RIGHT(
            Agence_Service_Destinataire,
            3
        ) = 'IMM' THEN '14'
        WHEN RIGHT(
            Agence_Service_Destinataire,
            3
        ) = 'TRA' THEN '15'
        WHEN RIGHT(
            Agence_Service_Destinataire,
            3
        ) = 'APP' THEN '16'
        WHEN RIGHT(
            Agence_Service_Destinataire,
            3
        ) = 'UMP' THEN '17'
        WHEN RIGHT(
            Agence_Service_Destinataire,
            3
        ) = 'ENG' THEN '19'
        WHEN RIGHT(
            Agence_Service_Destinataire,
            3
        ) = 'VAN' THEN '20'
        WHEN RIGHT(
            Agence_Service_Destinataire,
            3
        ) = 'GIR' THEN '21'
        WHEN RIGHT(
            Agence_Service_Destinataire,
            3
        ) = 'THO' THEN '22'
        WHEN RIGHT(
            Agence_Service_Destinataire,
            3
        ) = 'TSI' THEN '23'
        WHEN RIGHT(
            Agence_Service_Destinataire,
            3
        ) = 'LTV' THEN '24'
        WHEN RIGHT(
            Agence_Service_Destinataire,
            3
        ) = 'LFD' THEN '25'
        WHEN RIGHT(
            Agence_Service_Destinataire,
            3
        ) = 'LBV' THEN '26'
        WHEN RIGHT(
            Agence_Service_Destinataire,
            3
        ) = 'MAH' THEN '27'
        WHEN RIGHT(
            Agence_Service_Destinataire,
            3
        ) = 'NOS' THEN '28'
        WHEN RIGHT(
            Agence_Service_Destinataire,
            3
        ) = 'TUL' THEN '29'
        WHEN RIGHT(
            Agence_Service_Destinataire,
            3
        ) = 'AMB' THEN '30'
        WHEN RIGHT(
            Agence_Service_Destinataire,
            3
        ) = 'FLE' THEN '31'
        WHEN RIGHT(
            Agence_Service_Destinataire,
            3
        ) = 'TSD' THEN '32'
        WHEN RIGHT(
            Agence_Service_Destinataire,
            3
        ) = 'VAT' THEN '33'
        WHEN RIGHT(
            Agence_Service_Destinataire,
            3
        ) = 'BLK' THEN '34'
        WHEN RIGHT(
            Agence_Service_Destinataire,
            3
        ) = 'ENG' THEN '35'
        WHEN RIGHT(
            Agence_Service_Destinataire,
            3
        ) = 'MAS' THEN '36'
        WHEN RIGHT(
            Agence_Service_Destinataire,
            3
        ) = 'MAP' THEN '37'
        WHEN RIGHT(
            Agence_Service_Destinataire,
            3
        ) = 'ADM' THEN '38'
        WHEN RIGHT(
            Agence_Service_Destinataire,
            3
        ) = 'APP' THEN '39'
        WHEN RIGHT(
            Agence_Service_Destinataire,
            3
        ) = 'LEV' THEN '40'
        WHEN RIGHT(
            Agence_Service_Destinataire,
            3
        ) = 'LR6' THEN '41'
        WHEN RIGHT(
            Agence_Service_Destinataire,
            3
        ) = 'LST' THEN '42'
        WHEN RIGHT(
            Agence_Service_Destinataire,
            3
        ) = 'LCJ' THEN '43'
        WHEN RIGHT(
            Agence_Service_Destinataire,
            3
        ) = 'TSI' THEN '44'
        WHEN RIGHT(
            Agence_Service_Destinataire,
            3
        ) = 'SLR' THEN '45'
        WHEN RIGHT(
            Agence_Service_Destinataire,
            3
        ) = 'LGR' THEN '46'
        ELSE '0'
    END;