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
        WHEN Code_AgenceService_Sage = 'AB21' THEN '1' 
        WHEN Code_AgenceService_Sage = 'AB51' THEN '1' 
        WHEN Code_AgenceService_Sage = 'AC11' THEN '1' 
        WHEN Code_AgenceService_Sage = 'AC12' THEN '1' 
        WHEN Code_AgenceService_Sage = 'AC14' THEN '1' 
        WHEN Code_AgenceService_Sage = 'AC16' THEN '1' 
        WHEN Code_AgenceService_Sage = 'AG11' THEN '1' 
        WHEN Code_AgenceService_Sage = 'BB21' THEN '1' 
        WHEN Code_AgenceService_Sage = 'BC11' THEN '1' 
        WHEN Code_AgenceService_Sage = 'BC15' THEN '1' 
        WHEN Code_AgenceService_Sage = 'CB21' THEN '1' 
        WHEN Code_AgenceService_Sage = 'CC11' THEN '1' 
        WHEN Code_AgenceService_Sage = 'CC21' THEN '1' 
        WHEN Code_AgenceService_Sage = 'DA11' THEN '1' 
        WHEN Code_AgenceService_Sage = 'DA12' THEN '1' 
        WHEN Code_AgenceService_Sage = 'DA13' THEN '1' 
        WHEN Code_AgenceService_Sage = 'DA14' THEN '1' 
        WHEN Code_AgenceService_Sage = 'DA15' THEN '1' 
        WHEN Code_AgenceService_Sage = 'DA16' THEN '1' 
        WHEN Code_AgenceService_Sage = 'DA17' THEN '1' 
        WHEN Code_AgenceService_Sage = 'DA18' THEN '1' 
        WHEN Code_AgenceService_Sage = 'EB51' THEN '1' 
        WHEN Code_AgenceService_Sage = 'EC11' THEN '1'
        WHEN Code_AgenceService_Sage = 'ED10' THEN '1'
        WHEN Code_AgenceService_Sage = 'FB21' THEN '1'
        WHEN Code_AgenceService_Sage = 'FC11' THEN '1'
        WHEN Code_AgenceService_Sage = 'HB21' THEN '1'
        WHEN Code_AgenceService_Sage = 'HB51' THEN '1'
        WHEN Code_AgenceService_Sage = 'HE11' THEN '1'
        WHEN Code_AgenceService_Sage = 'HE12' THEN '1'
        WHEN Code_AgenceService_Sage = 'MB21' THEN '1'
        WHEN Code_AgenceService_Sage = 'MC11' THEN '1'
        WHEN Code_AgenceService_Sage = 'AB11' THEN '1'
        WHEN Code_AgenceService_Sage = 'AB11' THEN '1'
        WHEN Code_AgenceService_Sage = 'AB11' THEN '1'
        WHEN Code_AgenceService_Sage = 'AB11' THEN '1'
        WHEN Code_AgenceService_Sage = 'AB11' THEN '1'
        WHEN Code_AgenceService_Sage = 'AB11' THEN '1'
        ELSE  
    END