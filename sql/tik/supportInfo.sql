ALTER TABLE Demande_Support_Informatique ADD Date_Fin_Souhaitee DATE

ALTER TABLE Demande_Support_Informatique
ADD heure_creation VARCHAR(5)

ALTER TABLE Demande_Support_Informatique ADD user_id INT

ALTER TABLE Demande_Support_Informatique
ADD agence_emetteur_id INT
ALTER TABLE Demande_Support_Informatique
ADD service_emetteur_id INT
ALTER TABLE Demande_Support_Informatique
ADD agence_debiteur_id INT
ALTER TABLE Demande_Support_Informatique
ADD service_debiteur_id INT

EXEC sp_rename 'Demande_Support_Informatique.Date_Creation',
'date_creation',
'COLUMN';

ALTER TABLE Demande_Support_Informatique
ADD date_modification DATE
ALTER TABLE Demande_Support_Informatique
ALTER COLUMN date_creation DATE

UPDATE applications SET derniere_id = 'TIK24110000' where id = 7