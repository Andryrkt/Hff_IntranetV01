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

UPDATE Demande_Mouvement_Materiel
SET
    Casier_Destinataire = CASE Casier_Destinataire
        WHEN 'PAS UN CHARIOT STAR' THEN '1'
        WHEN 'TANA - STD BY' THEN '2'
        WHEN 'IVATO' THEN '3'
        WHEN 'AMBATOVY - AMSA' THEN '4'
        WHEN 'ATIS - TMV' THEN '5'
        WHEN 'LEVAGE - STD BY' THEN '6'
        WHEN 'MORAMANGA - STD BY' THEN '7'
        WHEN 'PENTA - PORT TMV' THEN '8'
        WHEN 'SERVICE ATELIER' THEN '9'
        WHEN 'AMBATOVY - DMSA' THEN '10'
        WHEN 'TMV - STD BY' THEN '11'
        WHEN 'AMBATOVY - AMSA' THEN '12'
        WHEN 'AMBATOVY - ATE' THEN '13'
        WHEN 'AMBATOVY - DMSA' THEN '14'
        WHEN 'AMBATOVY - STD BY' THEN '15'
        WHEN 'CCIS - PORT TMV' THEN '16'
        WHEN '60 - RN13' THEN '17'
        WHEN 'COLAS - RN6' THEN '18'
        WHEN 'COLAS - TANA' THEN '19'
        WHEN 'COLAS - TMV' THEN '20'
        WHEN 'COLAS- AMBOKATRA' THEN '21'
        WHEN 'FINEXPO STE MARIE' THEN '22'
        WHEN 'FTU - STD BY' THEN '23'
        WHEN 'GALANA - TMV' THEN '24'
        WHEN 'GODET' THEN '25'
        WHEN 'GODET - 8233' THEN '26'
        WHEN 'GODET - 8245' THEN '27'
        WHEN 'LOGISTIQUE' THEN '28'
        WHEN 'LOGISTIQUE TMV' THEN '29'
        WHEN 'PENTA - PORT TMV' THEN '30'
        WHEN 'QMM' THEN '31'
        WHEN 'QMM - CRIBLE 2801' THEN '32'
        WHEN 'QMM - M320D2 - 82117' THEN '33'
        WHEN 'QMM - PORT' THEN '34'
        WHEN 'RENTAL - WBHO' THEN '35'
        WHEN 'SAMBAVA' THEN '36'
        WHEN 'SAMCRETE - AUTORTE' THEN '37'
        WHEN 'SINOHYDRO - NOSY BE' THEN '38'
        WHEN 'STAR - EAU VIVE' THEN '39'
        WHEN 'STAR - EAUV/BCKP' THEN '40'
        WHEN 'STAR - US AMBATOLAM' THEN '41'
        WHEN 'STAR - US AMBTLPY' THEN '42'
        WHEN 'STAR - US AMBTLPY BK' THEN '43'
        WHEN 'STAR - US ANTSI/BE B' THEN '44'
        WHEN 'STAR - US ANTSIRABE' THEN '45'
        WHEN 'STAR - US ANTSIRANA' THEN '46'
        WHEN 'STAR AG - ANDRANOM' THEN '47'
        WHEN 'STAR AG - ANTS/RFT' THEN '48'
        WHEN 'STAR AG - ANTSIRNA' THEN '49'
        WHEN 'STAR AG - ANTSIRABE' THEN '50'
        WHEN 'STAR AG - FNRTSOA' THEN '51'
        WHEN 'STAR AG - MAJUNGA' THEN '52'
        WHEN 'STAR AG - TMV' THEN '53'
        WHEN 'STAR AG - TMT/BCKP' THEN '54'
        WHEN 'STAR AG - TNJO/BCKP' THEN '55'
        WHEN 'STAR AG - TNJO/RFT' THEN '56'
        WHEN 'STAR AG - TNJOMBATO' THEN '57'
        WHEN 'STAR AG - TOLIARA' THEN '58'
        WHEN 'STAR AG- AMBANJA' THEN '59'
        WHEN 'TMV - ATE' THEN '60'
        WHEN 'TMV - STD BY' THEN '61'
        WHEN 'TANA - ATE' THEN '62'
        WHEN 'TANA - STD BY' THEN '63'
        WHEN 'WBHO' THEN '64'
        WHEN 'AMBATOVY - AMSA' THEN '65'
        WHEN 'AMBATOVY - DMSA' THEN '66'
        WHEN 'AMBATOVY - DMSA RN2' THEN '67'
        WHEN 'COLAS - AMBATOBE' THEN '68'
        WHEN 'COLAS - RN6' THEN '69'
        WHEN 'COLAS - TMV' THEN '70'
        WHEN 'JIRAMA' THEN '71'
        WHEN 'JIRAMA (Ex ABL - GE' THEN '72'
        WHEN 'JIRAMA (Ex VPN G2 1)' THEN '73'
        WHEN 'JIRAMA (Ex VPN GE 1)' THEN '74'
        WHEN 'JIRAMA (Ex VVT GE 4)' THEN '75'
        WHEN 'MYRAA - TMV' THEN '76'
        WHEN 'QMM' THEN '77'
        WHEN 'TMV - STD BY' THEN '78'
        WHEN 'TANA - ATE' THEN '79'
        WHEN 'TANA - STD BY' THEN '80'
        WHEN 'UNIMA - TANA' THEN '81'
        WHEN 'STD-EQUI' THEN '82'
        WHEN 'STD-PSO' THEN '83'
        WHEN 'STD-STR' THEN '84'
        WHEN 'ABL - GE2' THEN '85'
        WHEN 'ABL - GE3' THEN '86'
        WHEN 'ABS - GE1' THEN '87'
        WHEN 'ABS - GE2' THEN '88'
        WHEN 'ABS - GE3' THEN '89'
        WHEN 'ABS - GE4' THEN '90'
        WHEN 'ABT - GE1' THEN '91'
        WHEN 'ABT - GE2' THEN '92'
        WHEN 'ADP - GE1' THEN '93'
        WHEN 'ADP - GE2' THEN '94'
        WHEN 'AJB - GE1' THEN '95'
        WHEN 'AJB - GE2' THEN '96'
        WHEN 'AJB - GE3' THEN '97'
        WHEN 'AMB - GE1' THEN '98'
        WHEN 'AMB - GE2' THEN '99'
        WHEN 'AMB - GE3' THEN '100'
        WHEN 'AMB - GE4' THEN '101'
        WHEN 'AMB - GE5' THEN '102'
        WHEN 'AMB - GE6' THEN '103'
        WHEN 'AMB - GE7' THEN '104'
        WHEN 'AMB - GE8' THEN '105'
        WHEN 'BSL - GE1' THEN '106'
        WHEN 'BSL - GE2' THEN '107'
        WHEN 'BSL - GE4' THEN '108'
        WHEN 'BSL-GE3' THEN '109'
        WHEN 'FGN - GE1' THEN '110'
        WHEN 'FGN - GE2' THEN '111'
        WHEN 'FGN - GE3' THEN '112'
        WHEN 'FLP - GE1' THEN '113'
        WHEN 'FLP - GE2' THEN '114'
        WHEN 'FLP - GE3' THEN '115'
        WHEN 'FNR - GE1' THEN '116'
        WHEN 'FNR - GE2' THEN '117'
        WHEN 'FNR - GE3' THEN '118'
        WHEN 'FNR - GE4' THEN '119'
        WHEN 'FNR - GE5' THEN '120'
        WHEN 'FST - GE1' THEN '121'
        WHEN 'FST - GE2' THEN '122'
        WHEN 'FST - GE3' THEN '123'
        WHEN 'MHR - GE1' THEN '124'
        WHEN 'MHR - GE2' THEN '125'
        WHEN 'MHR - GE3' THEN '126'
        WHEN 'MJN - DITA1' THEN '127'
        WHEN 'MJN - DITA2' THEN '128'
        WHEN 'MJN - GE1' THEN '129'
        WHEN 'MJN - GE2' THEN '130'
        WHEN 'MJN - GE3' THEN '131'
        WHEN 'MJN - GE4' THEN '132'
        WHEN 'MJN - GE5' THEN '133'
        WHEN 'MJN - GE6' THEN '134'
        WHEN 'MJN - GE8' THEN '135'
        WHEN 'MJN - PM3' THEN '136'
        WHEN 'MJN - PM4' THEN '137'
        WHEN 'MNK - GE1' THEN '138'
        WHEN 'MNK - GE2' THEN '139'
        WHEN 'MNK - GE3' THEN '140'
        WHEN 'MNK - GE4' THEN '141'
        WHEN 'MNK - PM1' THEN '142'
        WHEN 'MNN - GE1' THEN '143'
        WHEN 'MNN - GE2' THEN '144'
        WHEN 'MNN - GE3' THEN '145'
        WHEN 'MNN - GE4' THEN '146'
        WHEN 'MRV - GE1' THEN '147'
        WHEN 'MRV - GE2' THEN '148'
        WHEN 'MRV - GE3' THEN '149'
        WHEN 'MRV - GE4' THEN '150'
        WHEN 'NSB - GE1' THEN '151'
        WHEN 'NSB - GE2' THEN '152'
        WHEN 'NSB - GE3' THEN '153'
        WHEN 'NSB - GE4' THEN '154'
        WHEN 'SIV - GE1' THEN '155'
        WHEN 'SIV - GE2' THEN '156'
        WHEN 'SIV - GE3' THEN '157'
        WHEN 'STM - GE1' THEN '158'
        WHEN 'STM - GE2' THEN '159'
        WHEN 'STM - GE3' THEN '160'
        WHEN 'STM - GE4' THEN '161'
        WHEN 'TANA - ATE' THEN '162'
        WHEN 'TSD - GE1' THEN '163'
        WHEN 'TSD - GE2' THEN '164'
        WHEN 'TSD - GE3' THEN '165'
        WHEN 'TSD - GE4' THEN '166'
        WHEN 'TSD - PM2' THEN '167'
        WHEN 'TUL - GE10' THEN '168'
        WHEN 'TUL - GE2' THEN '169'
        WHEN 'TUL - GE4' THEN '170'
        WHEN 'TUL - GE5' THEN '171'
        WHEN 'TUL - GE6' THEN '172'
        WHEN 'VGN - GE1' THEN '173'
        WHEN 'VGN - GE2' THEN '174'
        WHEN 'VGN - GE3' THEN '175'
        WHEN 'VPN - GE1' THEN '176'
        WHEN 'VPN - GE2' THEN '177'
        WHEN 'VPN - GE3' THEN '178'
        WHEN 'VTM - GE1' THEN '179'
        WHEN 'VTM - GE2' THEN '180'
        WHEN 'VTM - GE3' THEN '181'
        WHEN 'VVT - GE1' THEN '182'
        WHEN 'VVT - GE2' THEN '183'
        WHEN 'VVT - GE3' THEN '184'
        WHEN 'STE MARIE' THEN '186'
        WHEN 'LMOI - TMV' THEN '187'
        WHEN 'COLAS - TMV' THEN '188'
        WHEN 'HITA - TMV' THEN '189'
        WHEN 'SAMCRETE - AUTORTE' THEN '190'
        WHEN 'TMF - TMV' THEN '191'
        WHEN 'STAR AG - AN/MHR BKP' THEN '194'
        WHEN 'COURSIER' THEN '195'
        WHEN 'DANA MINERALS' THEN '196'
        WHEN 'MARIE' THEN '197'
        WHEN 'HOLCIM - TMV' THEN '198'
        WHEN 'ANGE' THEN '199'
        WHEN 'MAGASIN CAT' THEN '200'
        WHEN 'COLAS - PK13' THEN '201'
        WHEN 'COGECI - TANA' THEN '202'
        WHEN 'NSB - GE6' THEN '203'
        WHEN 'NSB - GE7' THEN '204'
        WHEN 'CSP - TANA' THEN '205'
        WHEN 'CUSTOMER - TANA ' THEN '206'
        WHEN 'NSB - GE5' THEN '207'
        ELSE Casier_Destinataire
    END;