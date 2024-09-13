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