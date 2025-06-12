UPDATE Casier_Materiels_Temporaire
SET
    Agence_Rattacher = CASE
        WHEN Agence_Rattacher = '01' THEN '1'
        WHEN Agence_Rattacher = '02' THEN '2'
        WHEN Agence_Rattacher = '20' THEN '3'
        WHEN Agence_Rattacher = '30' THEN '4'
        WHEN Agence_Rattacher = '40' THEN '5'
        WHEN Agence_Rattacher = '50' THEN '6'
        WHEN Agence_Rattacher = '60' THEN '7'
        WHEN Agence_Rattacher = '80' THEN '8'
        WHEN Agence_Rattacher = '90' THEN '9'
        WHEN Agence_Rattacher = '91' THEN '10'
        WHEN Agence_Rattacher = '92' THEN '11'
        ELSE Agence_Rattacher -- Pour garder les autres valeurs inchangées
    END;

ALTER TABLE Casier_Materiels_Temporaire ADD ID_Statut_Demande INT

ALTER TABLE Casier_Materiels ADD ID_Statut_Demande INT

UPDATE Casier_Materiels
SET
    Nom_Session_Utilisateur = CASE
        WHEN Nom_Session_Utilisateur = 'admin' THEN '23'
        WHEN Nom_Session_Utilisateur = 'ROJO' THEN '12'
        WHEN Nom_Session_Utilisateur = 'MIORA.ENERGIE' THEN '8'
        WHEN Nom_Session_Utilisateur = 'ONY.RAFALIMANANA' THEN '13'
        ELSE Agence_Rattacher -- Pour garder les autres valeurs inchangées
    END;

-- Étape 2 : Changer le type de la colonne
ALTER TABLE Casier_Materiels_Temporaire
ALTER COLUMN Agence_Rattacher INT;

ALTER TABLE Casier_Materiels_Temporaire
ALTER COLUMN Nom_Session_Utilisateur INT;

ALTER TABLE Casier_Materiels
ALTER COLUMN Nom_Session_Utilisateur INT;

ALTER TABLE Casier_Materiels ALTER COLUMN Agence_Rattacher INT;