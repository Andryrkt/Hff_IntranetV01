ALTER TABLE Demande_ordre_mission
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
        WHEN Code_AgenceService_Sage = 'BC11' THEN '10'
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
    END